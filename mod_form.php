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
 * mod_form.php
 *
 * @package   mod_decision
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("{$CFG->dirroot}/course/moodleform_mod.php");

/**
 * Class mod_decision_mod_form.
 */
class mod_decision_mod_form extends moodleform_mod {
    /**
     * Method definition.
     *
     * @return void Return value.
     */
    public function definition(): void {
        $mform = $this->_form;

        $mform->addElement("header", "general", get_string("general", "form"));
        $mform->addElement("text", "name", get_string("decisionname", "decision"), ["size" => 64]);
        $mform->setType("name", PARAM_TEXT);
        $mform->addRule("name", null, "required", null, "client");

        $this->standard_intro_elements();

        $mform->addElement("textarea", "question", get_string("question", "decision"), ["rows" => 4, "cols" => 64]);
        $mform->setType("question", PARAM_TEXT);
        $mform->addRule("question", null, "required", null, "client");

        $mform->addElement("html", html_writer::tag("h3", get_string("options", "decision")));
        $repeatno = 3;
        if (!empty($this->current->instance)) {
            $repeatno = max(3, count(\mod_decision\repository::get_options((int) $this->current->instance)));
        }

        $repeatarray = [];
        $repeatarray[] = $mform->createElement("hidden", "optionid", 0);
        $repeatarray[] = $mform->createElement("text", "option", get_string("option", "decision"), ["size" => 60]);
        $repeateloptions = [
            "optionid" => ["type" => PARAM_INT],
            "option" => ["type" => PARAM_TEXT],
        ];
        $this->repeat_elements(
            $repeatarray,
            $repeatno,
            $repeateloptions,
            "option_repeats",
            "option_add_fields",
            2,
            get_string("addoption", "decision"),
            true
        );

        $mform->addElement("header", "behaviourheader", get_string("behaviour", "decision"));
        $mform->addElement("advcheckbox", "allowchange", get_string("allowchange", "decision"));
        $mform->setDefault("allowchange", 1);

        $mform->addElement("advcheckbox", "anonymous", get_string("anonymous", "decision"));
        $mform->addHelpButton("anonymous", "anonymous", "decision");

        $resultoptions = [
            0 => get_string("showresultsnever", "decision"),
            1 => get_string("showresultsaftervote", "decision"),
            2 => get_string("showresultsalways", "decision"),
            3 => get_string("showresultsafterclose", "decision"),
        ];
        $mform->addElement("select", "showresults", get_string("showresults", "decision"), $resultoptions);
        $mform->setDefault("showresults", 1);

        $chartoptions = [
            "bar" => get_string("chartbar", "decision"),
            "pie" => get_string("chartpie", "decision"),
        ];
        $mform->addElement("select", "charttype", get_string("defaultchart", "decision"), $chartoptions);
        $mform->setDefault("charttype", "bar");

        $mform->addElement("header", "availabilityheader", get_string("availability"));
        $mform->addElement("date_time_selector", "timeopen", get_string("timeopen", "decision"), ["optional" => true]);
        $mform->addElement("date_time_selector", "timeclose", get_string("timeclose", "decision"), ["optional" => true]);

        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }

    /**
     * Method data_preprocessing.
     *
     * @param mixed $defaultvalues Parameter defaultvalues.
     * @return void Return value.
     */
    public function data_preprocessing(&$defaultvalues): void {
        parent::data_preprocessing($defaultvalues);

        if (empty($this->current->instance)) {
            return;
        }

        $options = \mod_decision\repository::get_options((int) $this->current->instance);
        $defaultvalues["option"] = [];
        $defaultvalues["optionid"] = [];
        foreach ($options as $option) {
            $defaultvalues["option"][] = $option->text;
            $defaultvalues["optionid"][] = $option->id;
        }
    }

    /**
     * Method validation.
     *
     * @param mixed $data Parameter data.
     * @param mixed $files Parameter files.
     * @return array Return value.
     */
    public function validation($data, $files): array {
        $errors = parent::validation($data, $files);
        $options = array_values(array_filter(array_map("trim", $data["option"] ?? []), static fn($value) => $value !== ""));

        if (count($options) < 2) {
            $errors["option[0]"] = get_string("minimumtwooptions", "decision");
        }

        if (!empty($data["timeopen"]) && !empty($data["timeclose"]) && $data["timeclose"] <= $data["timeopen"]) {
            $errors["timeclose"] = get_string("closeafteropen", "decision");
        }

        return $errors;
    }
}
