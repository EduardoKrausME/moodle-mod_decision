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
 * decision.php
 *
 * @package   mod_decision
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$string['activityclosed'] = 'Voting is closed.';
$string['addoption'] = 'Add 2 more options';
$string['allowchange'] = 'Allow participants to change their choice';
$string['anonymous'] = 'Anonymous report';
$string['anonymous_help'] = 'Participants are still identified internally to prevent duplicate votes and allow vote changes, but their identities are not displayed in the report.';
$string['anonymousreportnotice'] = 'This decision is anonymous in reports. Individual participant choices are not displayed.';
$string['backtoactivity'] = 'Back to activity';
$string['behaviour'] = 'Voting behaviour';
$string['cannotdeletevotedoption'] = 'The option \'\' cannot be removed because it already has responses.';
$string['changernotallowed'] = 'This activity does not allow changing a submitted choice.';
$string['chartbar'] = 'Bars';
$string['chartpie'] = 'Pie';
$string['closeafteropen'] = 'The closing time must be later than the opening time.';
$string['closed'] = 'This decision is closed.';
$string['currentresults'] = 'Current results';
$string['decision:addinstance'] = 'Add a new quick decision';
$string['decision:manage'] = 'Manage quick decision';
$string['decision:submit'] = 'Submit a decision response';
$string['decision:view'] = 'View quick decision';
$string['decision:viewreport'] = 'View decision report';
$string['decisionname'] = 'Decision name';
$string['defaultchart'] = 'Default report chart';
$string['eventresponsesubmitted'] = 'Decision response submitted';
$string['lastchange'] = 'Last change';
$string['live'] = 'Live';
$string['minimumtwooptions'] = 'Add at least two options.';
$string['modulename'] = 'Quick decision';
$string['modulename_help'] = 'Create a short single-choice decision without a correct answer.';
$string['modulenameplural'] = 'Quick decisions';
$string['notopenyet'] = 'This decision is not open yet.';
$string['notopenyetat'] = 'This decision opens at .';
$string['option'] = 'Option';
$string['options'] = 'Options';
$string['participant'] = 'Participant';
$string['pluginadministration'] = 'Quick decision Administration';
$string['pluginname'] = 'Quick decision';
$string['privacy:export:response'] = 'Decision response';
$string['privacy:metadata:decision_responses'] = 'Stores each participant\'s selected option.';
$string['privacy:metadata:decision_responses:decisionid'] = 'The decision activity identifier.';
$string['privacy:metadata:decision_responses:optionid'] = 'The selected option identifier.';
$string['privacy:metadata:decision_responses:timecreated'] = 'When the response was first submitted.';
$string['privacy:metadata:decision_responses:timemodified'] = 'When the response was last changed.';
$string['privacy:metadata:decision_responses:userid'] = 'The user who submitted the response.';
$string['question'] = 'Question';
$string['refreshfailed'] = 'The live report could not be refreshed.';
$string['report'] = 'Live report';
$string['reporttitle'] = 'Live report: ';
$string['responsealreadyrecorded'] = 'Your choice has already been recorded.';
$string['responses'] = 'Responses';
$string['responsesaved'] = 'Your choice was saved.';
$string['showresults'] = 'Show results to participants';
$string['showresultsafterclose'] = 'After the decision closes';
$string['showresultsaftervote'] = 'After voting';
$string['showresultsalways'] = 'Always';
$string['showresultsnever'] = 'Never';
$string['status'] = 'Status';
$string['statusclosed'] = 'Closed';
$string['statusopen'] = 'Open';
$string['statusscheduled'] = 'Scheduled';
$string['submitdecision'] = 'Submit choice';
$string['timeclose'] = 'Close at';
$string['timeopen'] = 'Open from';
$string['totalresponses'] = 'Responses';
$string['viewreport'] = 'View live report';
$string['visualization'] = 'Visualization';
