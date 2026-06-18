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
 * CourseTransit general settings page.
 *
 * @package    auth_coursetransit
 * @copyright  2025 Justaddwater
 * @author     Himanshu Saini
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/general_form.php');

use auth_coursetransit\output\tabs;

// Security.
admin_externalpage_setup('auth_coursetransit');

require_login();

require_capability(
    'moodle/site:config',
    context_system::instance()
);

global $CFG, $DB, $OUTPUT, $PAGE;

// Page setup.
$PAGE->set_url('/auth/coursetransit/general.php');
$PAGE->set_title(get_string('pluginname', 'auth_coursetransit'));
$PAGE->set_heading(get_string('pluginname', 'auth_coursetransit'));

// Current REST protocols.
$protocols = explode(
    ',',
    (string)$CFG->webserviceprotocols
);

// Check if service already exists.
$serviceconfigured = $DB->record_exists(
    'external_services',
    [
        'shortname' => 'auth_coursetransit',
    ]
);

// Create form.
$form = new auth_coursetransit_general_form(
    null,
    [
        'enablerest' => in_array(
            'rest',
            $protocols,
            true
        ),

        'enablewebservices' =>
            !empty($CFG->enablewebservices),

        'serviceconfigured' =>
            $serviceconfigured,
    ]
);

// Form submit.
if ($data = $form->get_data()) {
    /*
     * Enable / Disable web services.
     */

    set_config(
        'enablewebservices',
        !empty($data->enablewebservices)
    );

    /*
     * Enable / Disable REST protocol.
     */

    $protocols = explode(
        ',',
        (string)$CFG->webserviceprotocols
    );

    if (!empty($data->enablerest)) {
        if (!in_array('rest', $protocols, true)) {
            $protocols[] = 'rest';
        }
    } else {
        $protocols = array_diff(
            $protocols,
            ['rest']
        );
    }

    set_config(
        'webserviceprotocols',
        implode(',', $protocols)
    );

    /*
     * Auto configure CourseTransit service.
     */

    if (!empty($data->autocreate)) {
        auth_coursetransit_auto_create_webservice();
    }

    redirect(
        new moodle_url(
            '/auth/coursetransit/general.php'
        ),
        get_string(
            'settingsupdated',
            'auth_coursetransit'
        ),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// Render page.
echo $OUTPUT->header();

// Tabs.
echo $OUTPUT->render_from_template(
    'auth_coursetransit/tabs',
    [
        'tabs' => tabs::get_tabs(
            'configuration'
        ),
    ]
);

// Form.
echo html_writer::start_div(
    'card border mb-4'
);

echo html_writer::start_div(
    'card-body'
);

$form->display();

echo html_writer::end_div();
echo html_writer::end_div();

echo $OUTPUT->footer();
