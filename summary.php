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
 * Summary page for CourseTransit authentication plugin.
 *
 * @package    auth_coursetransit
 * @copyright  2025 Justaddwater <contact@justaddwater.in>
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

global $CFG, $DB, $OUTPUT, $PAGE;

$PAGE->set_url('/auth/coursetransit/summary.php');

$PAGE->set_title('CourseTransit');

$PAGE->set_heading('CourseTransit');

/*
 * STATUS CHECKS
 */

// REST enabled.
$protocols = explode(
    ',',
    (string)$CFG->webserviceprotocols
);

$restenabled = in_array(
    'rest',
    $protocols,
    true
);

// Web services enabled.
$webservicesenabled =
    !empty($CFG->enablewebservices);

// Service configured.
$serviceconfigured =
    $DB->record_exists(
        'external_services',
        [
            'shortname' => 'auth_coursetransit',
        ]
    );

// Technical user.
$technicaluserid =
    get_config(
        'auth_coursetransit',
        'technicaluserid'
    );

$technicaluserconfigured =
    !empty($technicaluserid);

// Existing token.
$token =
    auth_coursetransit_get_existing_token();

$tokengenerated =
    !empty($token);

/*
 * LICENSE STATUS
 */

$licensestatus = get_config(
    'auth_coursetransit',
    'license_status'
);

$licenseplan = get_config(
    'auth_coursetransit',
    'license_plan'
);

$licensekey = get_config(
    'auth_coursetransit',
    'license_key'
);

$licensedomain = get_config(
    'auth_coursetransit',
    'license_domain'
);

$licenseexpiry = get_config(
    'auth_coursetransit',
    'license_expiry'
);

$licenselastsync = get_config(
    'auth_coursetransit',
    'license_last_sync'
);

// Mask license key.
$maskedlicensekey = !empty($licensekey)
    ? substr($licensekey, 0, 6)
        . '********'
        . substr($licensekey, -4)
    : 'Not available';

$islicenseactive =
    ($licensestatus === 'active');

// Site counts.
$totalsites = $DB->count_records(
    'auth_coursetransit_sites'
);

$activesites = $DB->count_records(
    'auth_coursetransit_sites',
    ['enabled' => 1]
);

$disabledsites =
    $totalsites - $activesites;

// Template context.
$templatecontext = [

    'tabs' => tabs::get_tabs('summary'),
    // Connection summary.
    'moodleurl' => $CFG->wwwroot,
    'servicename' => 'auth_coursetransit_execute_action',
    'endpoint' => $CFG->wwwroot . '/webservice/rest/server.php',
    'token' =>
        $token
        ? substr($token, 0, 8)
            . '************'
            . substr($token, -4)
        : 'Not generated',

    // Website stats.
    'totalsites' => $totalsites,
    'activesites' => $activesites,
    'disabledsites' => $disabledsites,

    // General status.
    'restenabled' =>
        $restenabled,

    'webservicesenabled' =>
        $webservicesenabled,

    'serviceconfigured' =>
        $serviceconfigured,

    'technicaluserconfigured' =>
        $technicaluserconfigured,

    'tokengenerated' =>
        $tokengenerated,

    // License.
    'licenseactive' =>
        $islicenseactive,

    'licensestatus' =>
        ucfirst(
            $licensestatus ?: 'inactive'
        ),

        'licenseplan' => $licenseplan ?: 'Free',

        'licensekey' =>
            $maskedlicensekey,

        'licensedomain' =>
            $licensedomain
                ?: 'Not linked',

        'licenseexpiry' =>
            $licenseexpiry ?: 'N/A',

        'licenselastsync' =>
            !empty($licenselastsync)
                ? userdate($licenselastsync)
                : 'Never',
];

echo $OUTPUT->header();

echo $OUTPUT->render_from_template(
    'auth_coursetransit/summary',
    $templatecontext
);

echo $OUTPUT->footer();
