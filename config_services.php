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
 * Configure services for CourseTransit site.
 *
 * @package    auth_coursetransit
 * @copyright  2025 Justaddwater <contact@justaddwater.in>
 * @author     Himanshu Saini
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/services_form.php');

admin_externalpage_setup('auth_coursetransit');

require_login();
require_capability('moodle/site:config', context_system::instance());

global $DB, $OUTPUT, $PAGE;

// Params.
$siteid = required_param('siteid', PARAM_INT);

// Fetch site.
$site = $DB->get_record('auth_coursetransit_sites', ['id' => $siteid], '*', MUST_EXIST);

// Form.
$form = new auth_coursetransit_services_form(
    null,
    [
        'siteid'  => $siteid,
        'enabled' => auth_coursetransit_get_site_services($siteid),
    ]
);

// Cancel.
if ($form->is_cancelled()) {
    redirect(new moodle_url('/auth/coursetransit/index.php'));
}

// Submit.
if ($data = $form->get_data()) {
    $selected = [];

    if (!empty($data->services)) {
        foreach ($data->services as $fn => $val) {
            if ($val) {
                $selected[] = $fn;
            }
        }
    }

    auth_coursetransit_update_site_services($siteid, $selected);

    redirect(
        new moodle_url('/auth/coursetransit/index.php'),
        get_string('servicesupdated', 'auth_coursetransit'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// Render.
$PAGE->set_url('/auth/coursetransit/config_services.php', ['siteid' => $siteid]);
$PAGE->set_title(get_string('services_title', 'auth_coursetransit'));
$PAGE->set_heading(get_string('services_title', 'auth_coursetransit'));

echo $OUTPUT->header();
echo html_writer::div(
    $OUTPUT->heading(get_string('services_title', 'auth_coursetransit'), 2),
    'mb-3'
);
$form->display();
echo $OUTPUT->footer();
