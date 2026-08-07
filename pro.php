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
 * CourseTransit Pro promotion page.
 *
 * @package    auth_coursetransit
 * @copyright  2025 Justaddwater
 * @author     Himanshu Saini
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(__DIR__ . '/lib.php');

use auth_coursetransit\output\tabs;

// Security.
admin_externalpage_setup('auth_coursetransit');

require_login();

require_capability(
    'moodle/site:config',
    context_system::instance()
);

global $CFG, $OUTPUT, $PAGE;

// Page setup.
$PAGE->set_url('/auth/coursetransit/pro.php');
$PAGE->set_title(get_string('propage_title', 'auth_coursetransit'));
$PAGE->set_heading(get_string('propage_title', 'auth_coursetransit'));

// Render page.
echo $OUTPUT->header();

// Tabs.
echo $OUTPUT->render_from_template(
    'auth_coursetransit/tabs',
    [
        'tabs' => tabs::get_tabs('pro'),
    ]
);

// Pro Page Content.
$contextdata = [
    'buy_pro_url' => 'https://justaddwater.in/products/coursetransit-wordpress-moodle-integration/#pricing',
];

echo $OUTPUT->render_from_template('auth_coursetransit/pro', $contextdata);

echo $OUTPUT->footer();
