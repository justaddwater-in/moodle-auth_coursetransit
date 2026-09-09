<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Installation script for auth_coursetransit.
 *
 * @package    auth_coursetransit
 * @copyright 2025 Justaddwater
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Perform post-install setup for CourseTransit.
 *
 * Creates the native Moodle user profile field used to record the
 * CourseTransit website that created a user.
 *
 * @return bool
 */
function xmldb_auth_coursetransit_install(): bool {
    global $CFG;

    require_once($CFG->dirroot . '/auth/coursetransit/lib.php');

    auth_coursetransit_ensure_source_domain_field();

    return true;
}
