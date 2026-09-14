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
 * view.php
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
require_capability("mod/decision:view", $context);

$PAGE->set_url("/mod/decision/view.php", ["id" => $cm->id]);
$PAGE->set_title(format_string($decision->name));
$PAGE->set_heading(format_string($course->fullname));

$event = \mod_decision\event\course_module_viewed::create([
    "objectid" => $decision->id,
    "context" => $context,
]);
$event->add_record_snapshot("course", $course);
$event->add_record_snapshot("course_modules", $cm);
$event->add_record_snapshot("decision", $decision);
$event->trigger();

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

if (optional_param("submitdecision", 0, PARAM_BOOL)) {
    require_sesskey();
    require_capability("mod/decision:submit", $context);
    $optionid = required_param("optionid", PARAM_INT);
    $response = \mod_decision\repository::submit_response($decision, $optionid, $USER->id);

    $submitevent = \mod_decision\event\response_submitted::create([
        "objectid" => $response->id,
        "context" => $context,
        "relateduserid" => $USER->id,
        "other" => ["optionid" => $optionid],
    ]);
    $submitevent->trigger();

    redirect(
        new moodle_url("/mod/decision/view.php", ["id" => $cm->id]),
        get_string("responsesaved", "decision"),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

$response = \mod_decision\repository::get_response($decision->id, $USER->id);
$options = \mod_decision\repository::get_options($decision->id);
$status = \mod_decision\repository::status($decision);
$isopen = $status === "open";
$cansubmit = has_capability("mod/decision:submit", $context);
$canchange = !$response || !empty($decision->allowchange);
$showresults = \mod_decision\repository::can_show_results($decision, (bool) $response);

$templateoptions = [];
foreach ($options as $option) {
    $templateoptions[] = [
        "id" => $option->id,
        "text" => format_string($option->text),
        "selected" => $response && (int) $response->optionid === (int) $option->id,
    ];
}

$data = [
    "cmid" => $cm->id,
    "actionurl" => (new moodle_url("/mod/decision/view.php", ["id" => $cm->id]))->out(false),
    "question" => format_string($decision->question),
    "options" => $templateoptions,
    "canvote" => $cansubmit && $isopen && $canchange,
    "hasresponse" => (bool) $response,
    "chosenoption" => "",
    "allowchange" => !empty($decision->allowchange),
    "statusopen" => $status === "open",
    "statusscheduled" => $status === "scheduled",
    "statusclosed" => $status === "closed",
    "opensat" => !empty($decision->timeopen) ? userdate($decision->timeopen) : "",
    "closesat" => !empty($decision->timeclose) ? userdate($decision->timeclose) : "",
    "sesskey" => sesskey(),
    "showresults" => $showresults,
    "reporturl" => (new moodle_url("/mod/decision/report.php", ["id" => $cm->id]))->out(false),
    "canviewreport" => has_capability("mod/decision:viewreport", $context),
];

if ($response) {
    foreach ($options as $option) {
        if ((int) $option->id === (int) $response->optionid) {
            $data["chosenoption"] = format_string($option->text);
            break;
        }
    }
}

if ($showresults) {
    $results = \mod_decision\repository::get_results($decision->id, false);
    $data["total"] = $results["total"];
    $data["results"] = $results["options"];
}

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($decision->name));
if (trim((string) $decision->intro) !== "") {
    echo $OUTPUT->box(format_module_intro("decision", $decision, $cm->id), "generalbox mod_introbox", "decisionintro");
}
echo $OUTPUT->render_from_template("mod_decision/view", $data);
echo $OUTPUT->footer();
