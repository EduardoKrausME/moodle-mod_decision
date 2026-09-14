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
 * report.php
 *
 * @package   mod_decision
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . "/../../config.php");

$id = required_param("id", PARAM_INT);
$cm = get_coursemodule_from_id("decision", $id, 0, false, MUST_EXIST);
$course = get_course($cm->course);
$decision = $DB->get_record("decision", ["id" => $cm->instance], "*", MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability("mod/decision:viewreport", $context);

$PAGE->set_url("/mod/decision/report.php", ["id" => $cm->id]);
$PAGE->set_title(get_string("report", "decision"));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->requires->js_call_amd("mod_decision/report", "init", [$cm->id, $decision->charttype]);

$results = \mod_decision\repository::get_results($decision->id, empty($decision->anonymous));
foreach ($results["users"] as &$user) {
    $user["timeformatted"] = userdate($user["timemodified"], get_string("strftimedatetimeshort", "langconfig"));
    foreach ($results["options"] as $option) {
        if ((int) $option["id"] === (int) $user["optionid"]) {
            $user["optiontext"] = $option["text"];
            break;
        }
    }
}
unset($user);

$data = [
    "cmid" => $cm->id,
    "question" => format_string($decision->question),
    "total" => $results["total"],
    "results" => $results["options"],
    "anonymous" => !empty($decision->anonymous),
    "users" => $results["users"],
    "hasusers" => !empty($results["users"]),
    "chartbarselected" => $decision->charttype === "bar",
    "chartpieselected" => $decision->charttype === "pie",
    "backurl" => (new moodle_url("/mod/decision/view.php", ["id" => $cm->id]))->out(false),
];

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string("reporttitle", "decision", format_string($decision->name)));
echo $OUTPUT->render_from_template("mod_decision/report", $data);
echo $OUTPUT->footer();
