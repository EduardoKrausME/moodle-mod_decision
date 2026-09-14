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
 * get_results.php
 *
 * @package   mod_decision
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_decision\external;

use context_module;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use mod_decision\repository;

/**
 * Class get_results.
 */
class get_results extends external_api {
    /**
     * Method execute_parameters.
     *
     * @return external_function_parameters Return value.
     */
    public static function execute_parameters(): external_function_parameters {
        return new external_function_parameters([
            "cmid" => new external_value(PARAM_INT, "Course module ID"),
        ]);
    }

    /**
     * Method execute.
     *
     * @param int $cmid Parameter cmid.
     * @return array Return value.
     */
    public static function execute(int $cmid): array {
        global $DB;

        ["cmid" => $cmid] = self::validate_parameters(self::execute_parameters(), ["cmid" => $cmid]);
        $cm = get_coursemodule_from_id("decision", $cmid, 0, false, MUST_EXIST);
        $context = context_module::instance($cm->id);
        self::validate_context($context);
        require_capability("mod/decision:viewreport", $context);

        $decision = $DB->get_record("decision", ["id" => $cm->instance], "*", MUST_EXIST);
        $results = repository::get_results((int) $decision->id, empty($decision->anonymous));

        foreach ($results["users"] as &$user) {
            $user["timeformatted"] = userdate($user["timemodified"], get_string("strftimedatetimeshort", "langconfig"));
        }
        unset($user);

        return $results;
    }

    /**
     * Method execute_returns.
     *
     * @return external_single_structure Return value.
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            "total" => new external_value(PARAM_INT, "Total responses"),
            "options" => new external_multiple_structure(new external_single_structure([
                "id" => new external_value(PARAM_INT, "Option ID"),
                "text" => new external_value(PARAM_TEXT, "Option text"),
                "count" => new external_value(PARAM_INT, "Response count"),
                "percent" => new external_value(PARAM_FLOAT, "Percentage"),
            ])),
            "users" => new external_multiple_structure(new external_single_structure([
                "id" => new external_value(PARAM_INT, "Response ID"),
                "optionid" => new external_value(PARAM_INT, "Option ID"),
                "userid" => new external_value(PARAM_INT, "User ID"),
                "fullname" => new external_value(PARAM_TEXT, "Full name"),
                "timemodified" => new external_value(PARAM_INT, "Modified timestamp"),
                "timeformatted" => new external_value(PARAM_TEXT, "Formatted timestamp"),
            ])),
        ]);
    }
}
