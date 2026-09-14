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
 * provider.php
 *
 * @package   mod_decision
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_decision\privacy;

use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;

/**
 * Class provider.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * Method get_metadata.
     *
     * @param collection $collection Parameter collection.
     * @return collection Return value.
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table("decision_responses", [
            "decisionid" => "privacy:metadata:decision_responses:decisionid",
            "optionid" => "privacy:metadata:decision_responses:optionid",
            "userid" => "privacy:metadata:decision_responses:userid",
            "timecreated" => "privacy:metadata:decision_responses:timecreated",
            "timemodified" => "privacy:metadata:decision_responses:timemodified",
        ], "privacy:metadata:decision_responses");
        return $collection;
    }

    /**
     * Method get_contexts_for_userid.
     *
     * @param int $userid Parameter userid.
     * @return contextlist Return value.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $sql = "SELECT ctx.id
                  FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid AND ctx.contextlevel = :contextlevel
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modname
                  JOIN {decision} d ON d.id = cm.instance
                  JOIN {decision_responses} r ON r.decisionid = d.id
                 WHERE r.userid = :userid";
        $contextlist->add_from_sql($sql, [
            "contextlevel" => CONTEXT_MODULE,
            "modname" => "decision",
            "userid" => $userid,
        ]);
        return $contextlist;
    }

    /**
     * Method export_user_data.
     *
     * @param approved_contextlist $contextlist Parameter contextlist.
     * @return void Return value.
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            $cm = get_coursemodule_from_id("decision", $context->instanceid, 0, false, MUST_EXIST);
            $response = $DB->get_record("decision_responses", ["decisionid" => $cm->instance, "userid" => $userid]);
            if (!$response) {
                continue;
            }
            $option = $DB->get_record("decision_options", ["id" => $response->optionid], "id, text", MUST_EXIST);
            $data = (object) [
                "option" => $option->text,
                "timecreated" => transform::datetime($response->timecreated),
                "timemodified" => transform::datetime($response->timemodified),
            ];
            writer::with_context($context)->export_data([get_string("privacy:export:response", "decision")], $data);
        }
    }

    /**
     * Method delete_data_for_all_users_in_context.
     *
     * @param \context $context Parameter context.
     * @return void Return value.
     */
    public static function delete_data_for_all_users_in_context(\context $context): void {
        global $DB;

        if ($context->contextlevel !== CONTEXT_MODULE) {
            return;
        }
        $cm = get_coursemodule_from_id("decision", $context->instanceid);
        if ($cm) {
            $DB->delete_records("decision_responses", ["decisionid" => $cm->instance]);
        }
    }

    /**
     * Method delete_data_for_user.
     *
     * @param approved_contextlist $contextlist Parameter contextlist.
     * @return void Return value.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        $userid = $contextlist->get_user()->id;
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel !== CONTEXT_MODULE) {
                continue;
            }
            $cm = get_coursemodule_from_id("decision", $context->instanceid);
            if ($cm) {
                $DB->delete_records("decision_responses", ["decisionid" => $cm->instance, "userid" => $userid]);
            }
        }
    }
}
