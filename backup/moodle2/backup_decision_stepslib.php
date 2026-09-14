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
 * backup_decision_stepslib.php
 *
 * @package   mod_decision
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * backup_decision_activity_structure_step
 */
class backup_decision_activity_structure_step extends backup_activity_structure_step {
    /**
     * Method define_structure.
     *
     * @return mixed Return value.
     */
    protected function define_structure() {
        $userinfo = $this->get_setting_value("userinfo");

        $decision = new backup_nested_element("decision", ["id"], [
            "name", "intro", "introformat", "question", "timeopen", "timeclose",
            "allowchange", "showresults", "anonymous", "charttype", "timecreated", "timemodified",
        ]);
        $options = new backup_nested_element("options");
        $option = new backup_nested_element("option", ["id"], ["text", "sortorder"]);
        $responses = new backup_nested_element("responses");
        $response = new backup_nested_element("response", ["id"], [
            "userid", "timecreated", "timemodified",
        ]);

        $decision->add_child($options);
        $options->add_child($option);
        $option->add_child($responses);
        $responses->add_child($response);

        $decision->set_source_table("decision", ["id" => backup::VAR_ACTIVITYID]);
        $option->set_source_table("decision_options", ["decisionid" => backup::VAR_PARENTID], "sortorder ASC, id ASC");
        if ($userinfo) {
            $response->set_source_table("decision_responses", ["optionid" => backup::VAR_PARENTID]);
        }

        $response->annotate_ids("user", "userid");
        $decision->annotate_files("mod_decision", "intro", null);

        return $this->prepare_activity_structure($decision);
    }
}
