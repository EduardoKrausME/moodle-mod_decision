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
 * lib.php
 *
 * @package   mod_decision
 * @copyright 2026 Eduardo Kraus {@link https://eduardokraus.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * decision_supports
 *
 * @param $feature
 * @return string|true|null
 */
function decision_supports($feature) {
    return match ($feature) {
        FEATURE_MOD_INTRO => true,
        FEATURE_SHOW_DESCRIPTION => true,
        FEATURE_COMPLETION_TRACKS_VIEWS => true,
        FEATURE_BACKUP_MOODLE2 => true,
        FEATURE_MOD_PURPOSE => MOD_PURPOSE_COLLABORATION,
        default => null,
    };
}

/**
 * decision_add_instance
 *
 * @param $data
 * @param $mform
 * @return int
 */
function decision_add_instance($data, $mform = null): int {
    return \mod_decision\manager::add_instance($data);
}

/**
 * decision_update_instance
 *
 * @param $data
 * @param $mform
 * @return bool
 */
function decision_update_instance($data, $mform = null): bool {
    return \mod_decision\manager::update_instance($data);
}

/**
 * decision_delete_instance
 *
 * @param $id
 * @return bool
 */
function decision_delete_instance($id): bool {
    return \mod_decision\manager::delete_instance($id);
}
