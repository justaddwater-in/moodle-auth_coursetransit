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
 * CourseTransit site management page.
 *
 * @package    auth_coursetransit
 * @copyright  2025 Justaddwater <contact@justaddwater.in>
 * @author     Himanshu Saini
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/site_form.php');

require_once($CFG->libdir . '/adminlib.php');

// Security.
admin_externalpage_setup('auth_coursetransit');

require_login();
require_capability('moodle/site:config', context_system::instance());

global $DB, $OUTPUT, $PAGE;

if (!auth_coursetransit_is_setup_complete()) {
    redirect(new moodle_url('/auth/coursetransit/wizard.php'));
}

$PAGE->set_url('/auth/coursetransit/index.php');
$PAGE->set_title(get_string('pluginname', 'auth_coursetransit'));
$PAGE->set_heading(get_string('pluginname', 'auth_coursetransit'));

// Params.
$action = optional_param('action', '', PARAM_ALPHA);
$created = optional_param('created', 0, PARAM_INT);

$tokenparam = auth_coursetransit_get_temp_token();

$showmodal = ($created && !empty($tokenparam));

// DELETE SITE.
if ($action === 'delete') {
    require_sesskey();

    $id = required_param('id', PARAM_INT);

    $DB->get_record('auth_coursetransit_sites', ['id' => $id], '*', MUST_EXIST);

    $DB->delete_records('auth_coursetransit_site_services', ['siteid' => $id]);
    $DB->delete_records('auth_coursetransit_sites', ['id' => $id]);

    redirect(
        new moodle_url('/auth/coursetransit/index.php'),
        get_string('sitedeleted', 'auth_coursetransit'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}


// SITE FORM.
$siteform = new auth_coursetransit_site_form();

if ($siteform && $siteform->is_submitted() && $siteform->is_validated()) {
    $data = $siteform->get_data();

    $input = trim($data->domain);

    if (!preg_match('~^https?://~i', $input)) {
        $input = 'https://' . $input;
    }

    $host = parse_url($input, PHP_URL_HOST);

    if (!$host) {
        throw new moodle_exception(
            'invalidsiteurl',
            'auth_coursetransit'
        );
    }

    // Normalize domain.
    $domain = strtolower($host);

    // Check if domain already exists.
    $existing = $DB->record_exists(
        'auth_coursetransit_sites',
        ['domain' => $domain]
    );

    if ($existing) {
        redirect(
            new moodle_url('/auth/coursetransit/index.php'),
            get_string('sitedomainexists', 'auth_coursetransit'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }

    // Create site.
    auth_coursetransit_create_site(
        trim($data->name),
        $domain
    );

    auth_coursetransit_set_temp_token(auth_coursetransit_get_existing_token());
    redirect(
        new moodle_url('/auth/coursetransit/index.php', [
            'created' => 1,
        ]),
        get_string('sitecreated', 'auth_coursetransit'),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

// SITE LIST.
$sites = [];

foreach (auth_coursetransit_get_sites() as $site) {
    $deleteurl = new moodle_url('/auth/coursetransit/index.php', [
        'action'  => 'delete',
        'id'      => $site->id,
        'sesskey' => sesskey(),
    ]);

    $deleteaction = $OUTPUT->action_link(
        $deleteurl,
        '',
        new confirm_action(get_string('deletesiteconfirm', 'auth_coursetransit')),
        ['class' => 'btn btn-link p-0'],
        new pix_icon('t/delete', '')
    );

    $configurl = new moodle_url('/auth/coursetransit/config_services.php', [
        'siteid' => $site->id,
    ]);

    $configaction = $OUTPUT->action_link(
        $configurl,
        '',
        null,
        ['class' => 'btn btn-link p-0'],
        new pix_icon('i/settings', get_string('configure', 'auth_coursetransit'))
    );

    $sites[] = [
        'name'         => $site->name,
        'domain'       => $site->domain,
        'configaction' => $configaction,
        'deleteaction' => $deleteaction,
    ];
}

// TEMPLATE.
$templatecontext = [
    'sites'    => $sites,
    'formhtml' => $siteform ? $siteform->render() : '',
    'showmodal'  => $showmodal,
    'token'      => $tokenparam,
];

if ($showmodal) {
    $PAGE->requires->js_call_amd(
        'auth_coursetransit/token_modal',
        'show'
    );
}

// RENDER.
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('auth_coursetransit/layout', $templatecontext);
echo $OUTPUT->footer();
