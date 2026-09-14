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
 * restore_decision_stepslib.php
 *
 * @package   mod_decision
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * restore_decision_activity_structure_step
 */
class restore_decision_activity_structure_step extends restore_activity_structure_step {
    /**
     * Method define_structure.
     *
     * @return array Return value.
     */
    protected function define_structure(): array {
        $paths = [];
        $paths[] = new restore_path_element("decision", "/activity/decision");
        $paths[] = new restore_path_element("decision_option", "/activity/decision/options/option");
        if ($this->get_setting_value("userinfo")) {
            $paths[] = new restore_path_element("decision_response", "/activity/decision/options/option/responses/response");
        }
        return $this->prepare_activity_structure($paths);
    }

    /**
     * Method process_decision.
     *
     * @param mixed $data Parameter data.
     * @return void Return value.
     */
    protected function process_decision($data): void {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();
        $newitemid = $DB->insert_record("decision", $data);
        $this->apply_activity_instance($newitemid);
        $this->set_mapping("decision", $oldid, $newitemid, true);
    }

    /**
     * Method process_decision_option.
     *
     * @param mixed $data Parameter data.
     * @return void Return value.
     */
    protected function process_decision_option($data): void {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->decisionid = $this->get_new_parentid("decision");
        $newitemid = $DB->insert_record("decision_options", $data);
        $this->set_mapping("decision_option", $oldid, $newitemid);
    }

    /**
     * Method process_decision_response.
     *
     * @param mixed $data Parameter data.
     * @return void Return value.
     */
    protected function process_decision_response($data): void {
        global $DB;

        $data = (object) $data;
        $data->decisionid = $this->get_new_parentid("decision");
        $data->optionid = $this->get_new_parentid("decision_option");
        $data->userid = $this->get_mappingid("user", $data->userid);
        if (!$data->userid) {
            return;
        }
        $DB->insert_record("decision_responses", $data);
    }

    /**
     * Method after_execute.
     *
     * @return void Return value.
     */
    protected function after_execute(): void {
        $this->add_related_files("mod_decision", "intro", null);
    }
}
