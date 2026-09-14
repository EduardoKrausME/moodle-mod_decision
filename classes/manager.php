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
 * manager.php
 *
 * @package   mod_decision
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_decision;

/**
 * Class manager.
 */
class manager {
    /**
     * Method add_instance.
     *
     * @param object $data Parameter data.
     * @return int Return value.
     */
    public static function add_instance(object $data): int {
        global $DB;

        $transaction = $DB->start_delegated_transaction();
        $record = self::prepare_record($data);
        $record->timecreated = time();
        $id = $DB->insert_record("decision", $record);
        repository::sync_options($id, $data->option ?? [], $data->optionid ?? []);
        $transaction->allow_commit();
        return $id;
    }

    /**
     * Method update_instance.
     *
     * @param object $data Parameter data.
     * @return bool Return value.
     */
    public static function update_instance(object $data): bool {
        global $DB;

        $transaction = $DB->start_delegated_transaction();
        $record = self::prepare_record($data);
        $record->id = (int) $data->instance;
        $DB->update_record("decision", $record);
        repository::sync_options($record->id, $data->option ?? [], $data->optionid ?? []);
        $transaction->allow_commit();
        return true;
    }

    /**
     * Method delete_instance.
     *
     * @param int $id Parameter id.
     * @return bool Return value.
     */
    public static function delete_instance(int $id): bool {
        global $DB;

        if (!$DB->record_exists("decision", ["id" => $id])) {
            return false;
        }

        $transaction = $DB->start_delegated_transaction();
        $DB->delete_records("decision_responses", ["decisionid" => $id]);
        $DB->delete_records("decision_options", ["decisionid" => $id]);
        $DB->delete_records("decision", ["id" => $id]);
        $transaction->allow_commit();
        return true;
    }

    /**
     * Method prepare_record.
     *
     * @param object $data Parameter data.
     * @return \stdClass Return value.
     */
    private static function prepare_record(object $data): \stdClass {
        $record = new \stdClass();
        $record->course = (int) $data->course;
        $record->name = trim($data->name);
        $record->intro = $data->intro ?? "";
        $record->introformat = (int) ($data->introformat ?? FORMAT_HTML);
        $record->question = trim($data->question);
        $record->timeopen = (int) ($data->timeopen ?? 0);
        $record->timeclose = (int) ($data->timeclose ?? 0);
        $record->allowchange = empty($data->allowchange) ? 0 : 1;
        $record->showresults = (int) ($data->showresults ?? 1);
        $record->anonymous = empty($data->anonymous) ? 0 : 1;
        $record->charttype = in_array(($data->charttype ?? "bar"), ["bar", "pie"], true) ? $data->charttype : "bar";
        $record->timemodified = time();
        return $record;
    }
}
