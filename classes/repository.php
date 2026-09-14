<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * repository.php
 *
 * @package   mod_decision
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_decision;

use moodle_exception;

/**
 * Class repository.
 */
class repository {
    /**
     * Method get_options.
     *
     * @param int $decisionid Parameter decisionid.
     * @return array Return value.
     */
    public static function get_options(int $decisionid): array {
        global $DB;
        return array_values($DB->get_records("decision_options", ["decisionid" => $decisionid], "sortorder ASC, id ASC"));
    }

    /**
     * Method sync_options.
     *
     * @param int $decisionid Parameter decisionid.
     * @param array $texts Parameter texts.
     * @param array $ids Parameter ids.
     * @return void Return value.
     */
    public static function sync_options(int $decisionid, array $texts, array $ids): void {
        global $DB;

        $existing = $DB->get_records("decision_options", ["decisionid" => $decisionid]);
        $keptids = [];
        $sortorder = 0;

        foreach ($texts as $index => $text) {
            $text = trim((string) $text);
            if ($text === "") {
                continue;
            }

            $optionid = (int) ($ids[$index] ?? 0);
            if ($optionid && isset($existing[$optionid])) {
                $record = $existing[$optionid];
                $record->text = $text;
                $record->sortorder = $sortorder;
                $DB->update_record("decision_options", $record);
                $keptids[] = $optionid;
            } else {
                $record = (object) [
                    "decisionid" => $decisionid,
                    "text" => $text,
                    "sortorder" => $sortorder,
                ];
                $keptids[] = $DB->insert_record("decision_options", $record);
            }
            $sortorder++;
        }

        foreach ($existing as $optionid => $option) {
            if (in_array((int) $optionid, $keptids, true)) {
                continue;
            }
            if ($DB->record_exists("decision_responses", ["optionid" => $optionid])) {
                throw new moodle_exception("cannotdeletevotedoption", "decision", "", $option->text);
            }
            $DB->delete_records("decision_options", ["id" => $optionid]);
        }
    }

    /**
     * Method get_response.
     *
     * @param int $decisionid Parameter decisionid.
     * @param int $userid Parameter userid.
     * @return ?object Return value.
     */
    public static function get_response(int $decisionid, int $userid): ?object {
        global $DB;
        $record = $DB->get_record("decision_responses", ["decisionid" => $decisionid, "userid" => $userid]);
        return $record ?: null;
    }

    /**
     * Method submit_response.
     *
     * @param object $decision Parameter decision.
     * @param int $optionid Parameter optionid.
     * @param int $userid Parameter userid.
     * @return object Return value.
     */
    public static function submit_response(object $decision, int $optionid, int $userid): object {
        global $DB;

        self::require_open($decision);
        $option = $DB->get_record("decision_options", ["id" => $optionid, "decisionid" => $decision->id], "*", MUST_EXIST);
        $existing = self::get_response((int) $decision->id, $userid);
        $now = time();

        if ($existing) {
            if (empty($decision->allowchange) && (int) $existing->optionid !== $optionid) {
                throw new moodle_exception("changernotallowed", "decision");
            }
            if ((int) $existing->optionid !== $optionid) {
                $existing->optionid = $option->id;
                $existing->timemodified = $now;
                $DB->update_record("decision_responses", $existing);
            }
            return $existing;
        }

        $response = (object) [
            "decisionid" => $decision->id,
            "optionid" => $option->id,
            "userid" => $userid,
            "timecreated" => $now,
            "timemodified" => $now,
        ];
        $response->id = $DB->insert_record("decision_responses", $response);
        return $response;
    }

    /**
     * Method get_results.
     *
     * @param int $decisionid Parameter decisionid.
     * @param bool $includeusers Parameter includeusers.
     * @return array Return value.
     */
    public static function get_results(int $decisionid, bool $includeusers = false): array {
        global $DB;

        $options = self::get_options($decisionid);
        $counts = $DB->get_records_sql_menu(
            "SELECT optionid, COUNT(1) AS responsecount
               FROM {decision_responses}
              WHERE decisionid = :decisionid
           GROUP BY optionid",
            ["decisionid" => $decisionid]
        );
        $total = $DB->count_records("decision_responses", ["decisionid" => $decisionid]);

        $items = [];
        foreach ($options as $option) {
            $count = (int) ($counts[$option->id] ?? 0);
            $items[] = [
                "id" => (int) $option->id,
                "text" => $option->text,
                "count" => $count,
                "percent" => $total > 0 ? round(($count / $total) * 100, 1) : 0.0,
            ];
        }

        $users = [];
        if ($includeusers) {
            $sql = "SELECT r.id, r.optionid, r.userid, r.timemodified, u.firstname, u.lastname
                      FROM {decision_responses} r
                      JOIN {user} u ON u.id = r.userid
                     WHERE r.decisionid = :decisionid
                  ORDER BY r.timemodified DESC";
            foreach ($DB->get_records_sql($sql, ["decisionid" => $decisionid]) as $response) {
                $users[] = [
                    "id" => (int) $response->id,
                    "optionid" => (int) $response->optionid,
                    "userid" => (int) $response->userid,
                    "fullname" => fullname($response),
                    "timemodified" => (int) $response->timemodified,
                ];
            }
        }

        return ["total" => $total, "options" => $items, "users" => $users];
    }

    /**
     * Method is_open.
     *
     * @param object $decision Parameter decision.
     * @param ?int $now Parameter now.
     * @return bool Return value.
     */
    public static function is_open(object $decision, ?int $now = null): bool {
        $now ??= time();
        if (!empty($decision->timeopen) && $now < (int) $decision->timeopen) {
            return false;
        }
        if (!empty($decision->timeclose) && $now > (int) $decision->timeclose) {
            return false;
        }
        return true;
    }

    /**
     * Method status.
     *
     * @param object $decision Parameter decision.
     * @param ?int $now Parameter now.
     * @return string Return value.
     */
    public static function status(object $decision, ?int $now = null): string {
        $now ??= time();
        if (!empty($decision->timeopen) && $now < (int) $decision->timeopen) {
            return "scheduled";
        }
        if (!empty($decision->timeclose) && $now > (int) $decision->timeclose) {
            return "closed";
        }
        return "open";
    }

    /**
     * Method can_show_results.
     *
     * @param object $decision Parameter decision.
     * @param bool $hasresponded Parameter hasresponded.
     * @param ?int $now Parameter now.
     * @return bool Return value.
     */
    public static function can_show_results(object $decision, bool $hasresponded, ?int $now = null): bool {
        $now ??= time();
        return match ((int) $decision->showresults) {
            0 => false,
            1 => $hasresponded,
            2 => true,
            3 => !empty($decision->timeclose) && $now > (int) $decision->timeclose,
            default => false,
        };
    }

    /**
     * Method require_open.
     *
     * @param object $decision Parameter decision.
     * @return void Return value.
     */
    private static function require_open(object $decision): void {
        $status = self::status($decision);
        if ($status === "scheduled") {
            throw new moodle_exception("notopenyet", "decision");
        }
        if ($status === "closed") {
            throw new moodle_exception("closed", "decision");
        }
    }
}
