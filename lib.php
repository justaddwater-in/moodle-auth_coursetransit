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
 * Library functions for CourseTransit.
 *
 * @package    auth_coursetransit
 * @copyright  2025 Justaddwater <contact@justaddwater.in>
 * @author     Himanshu Saini
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use webservice;
/**
 * Minimal LMSACE-style webservice + token creation.
 *
 * @return string Generated or existing token
 */
function auth_coursetransit_auto_create_webservice(): string {
    global $CFG, $DB;

    require_once($CFG->dirroot . '/webservice/lib.php');
    require_once($CFG->dirroot . '/lib/externallib.php');

    // Enable web services.
    set_config('enablewebservices', 1);

    // Enable REST.
    $protocols = explode(',', (string) $CFG->webserviceprotocols);
    if (!in_array('rest', $protocols, true)) {
        $protocols[] = 'rest';
        set_config('webserviceprotocols', implode(',', $protocols));
    }

    // Get or create service.
    $service = $DB->get_record('external_services', ['shortname' => 'auth_coursetransit']);
    if (!$service) {
        $service = (object) [
            'name' => 'CourseTransit LMS Service',
            'shortname' => 'auth_coursetransit',
            'enabled' => 1,
            'restrictedusers' => 1,
            'timecreated' => time(),
        ];
        $service->id = $DB->insert_record('external_services', $service);
    }

    // CRITICAL: Ensure ALL supported functions are enabled ONCE.
    auth_coursetransit_update_services(
        array_keys(auth_coursetransit_get_supported_services())
    );

    // Technical user.
    $userid = (int) get_config('auth_coursetransit', 'technicaluserid');
    if (!$userid) {
        throw new moodle_exception('Technical user not configured');
    }

    // Authorise user.
    $ws = new webservice();
    if (
        !$DB->record_exists('external_services_users', [
            'externalserviceid' => $service->id,
            'userid' => $userid,
        ])
    ) {
        $ws->add_ws_authorised_user((object) [
            'externalserviceid' => $service->id,
            'userid' => $userid,
        ]);
    }

    // Token reuse.
    if (
        $token = $DB->get_record('external_tokens', [
            'externalserviceid' => $service->id,
            'userid' => $userid,
        ])
    ) {
        return $token->token;
    }

    return external_generate_token(
        EXTERNAL_TOKEN_PERMANENT,
        $service->id,
        $userid,
        context_system::instance()
    );
}

/**
 * Get existing webservice token WITHOUT creating one.
 */
function auth_coursetransit_get_existing_token(): string {
    global $DB;

    $service = $DB->get_record('external_services', [
        'shortname' => 'auth_coursetransit',
    ]);

    if (!$service) {
        return '';
    }

    $userid = (int) get_config('auth_coursetransit', 'technicaluserid');
    if (!$userid) {
        return '';
    }

    if (
        $token = $DB->get_record('external_tokens', [
            'externalserviceid' => $service->id,
            'userid' => $userid,
        ])
    ) {
        return $token->token;
    }

    return '';
}
/**
 * Returns the list of Moodle webservice functions
 * that CourseTransit LMS is allowed to expose.
 *
 * IMPORTANT:
 * - This is a strict SECURITY WHITELIST
 * - Only functions listed here can ever be enabled
 *
 * @return array functionname => label
 */
function auth_coursetransit_get_supported_services(): array {
    return [
        'core_webservice_get_site_info' => 'Get site info',
        // User-related services.
        'core_user_create_users' => 'Create users',
        'core_user_delete_users' => 'Delete users',
        'core_user_get_users_by_field' => 'Get users by field (email, id)',
        'core_user_update_users' => 'Update users',

        // Course-related services.
        'core_course_get_courses' => 'Get courses',
        'core_course_get_courses_by_field' => 'Get courses by field',
        'core_course_get_categories' => 'Get course categories',
        'core_course_get_contents' => 'Get course contents',

        // Enrolment-related services.
        'core_enrol_get_users_courses' => 'Get user enrolments',
        'core_enrol_get_enrolled_users' => 'Get enrolled users',
        'enrol_manual_enrol_users' => 'Manually enrol users',
        'enrol_manual_unenrol_users' => 'Manually unenrol users',
    ];
}

/**
 * Get currently enabled webservice functions for CourseTransit LMS service.
 *
 * @param int $serviceid External service ID
 * @return array List of function names
 */
function auth_coursetransit_get_enabled_services(int $serviceid): array {
    global $DB;

    return $DB->get_fieldset_select(
        'external_services_functions',
        'functionname',
        'externalserviceid = :sid',
        ['sid' => $serviceid]
    );
}

/**
 * Set technical user for CourseTransit.
 *
 * @param int $userid User ID.
 * @return void
 */
function auth_coursetransit_set_technical_user(int $userid): void {
    set_config('technicaluserid', $userid, 'auth_coursetransit');
}

/**
 * Update enabled webservice functions.
 *
 * @param array $functions List of function names.
 * @return void
 */
function auth_coursetransit_update_services(array $functions): void {
    global $DB;

    $service = $DB->get_record(
        'external_services',
        ['shortname' => 'auth_coursetransit'],
        '*',
        MUST_EXIST
    );

    $supported = array_keys(auth_coursetransit_get_supported_services());
    $functions = array_intersect($functions, $supported);

    $DB->delete_records(
        'external_services_functions',
        ['externalserviceid' => $service->id]
    );

    foreach ($functions as $fn) {
        $DB->insert_record('external_services_functions', [
            'externalserviceid' => $service->id,
            'functionname' => $fn,
        ]);
    }
}

/**
 * Create new CourseTransit site.
 *
 * @param string $name Site name.
 * @param string $domain Site domain.
 * @return string Generated token.
 */
function auth_coursetransit_create_site(string $name, string $domain): string {
    global $DB;

    $token = bin2hex(random_bytes(32));
    $time = time();

    $domain = rtrim($domain, '/');

    $siteid = $DB->insert_record('auth_coursetransit_sites', [
        'name' => $name,
        'domain' => $domain,
        'token' => $token,
        'enabled' => 1,
        'timecreated' => $time,
        'timemodified' => $time,
    ]);

    $allservices = array_keys(auth_coursetransit_get_supported_services());
    auth_coursetransit_update_site_services($siteid, $allservices);

    return $token;
}

/**
 * Get all registered CourseTransit sites.
 *
 * @return array
 */
function auth_coursetransit_get_sites(): array {
    global $DB;
    return $DB->get_records('auth_coursetransit_sites', null, 'timecreated DESC');
}

/**
 * Execute Moodle webservice function internally.
 *
 * @param string $function Function name.
 * @param array $payload Request payload.
 * @return mixed
 */
function auth_coursetransit_execute_action(string $function, array $payload) {
    global $CFG, $DB;

    require_once($CFG->dirroot . '/webservice/lib.php');
    require_once($CFG->dirroot . '/webservice/rest/locallib.php');

    // 1. Fetch service
    $service = $DB->get_record(
        'external_services',
        ['shortname' => 'auth_coursetransit'],
        '*',
        MUST_EXIST
    );

    // 2. Get technical user
    $userid = (int) get_config('auth_coursetransit', 'technicaluserid');
    if (!$userid) {
        throw new moodle_exception('technicalusernotset', 'auth_coursetransit');
    }

    // 3. Get EXISTING token (never regenerate)
    $token = $DB->get_record('external_tokens', [
        'externalserviceid' => $service->id,
        'userid' => $userid,
    ], '*', MUST_EXIST);

    // 4. Create REST server (AUTH METHOD is mandatory)
    $server = new webservice_rest_server(WEBSERVICE_AUTHMETHOD_PERMANENT_TOKEN);

    $backuppost = $_POST;
    $backupget  = $_GET;

    // 5. Simulate REST request (THIS IS THE KEY PART)
    $_POST = array_merge([
        'wstoken' => $token->token,
        'wsfunction' => $function,
        'moodlewsrestformat' => 'json',
    ], $payload);

    $_GET = []; // Safety.

    ob_start();
    $server->run();
    $response = ob_get_clean();

    // Restore original POST and GET data.
    $_POST = $backuppost;
    $_GET = $backupget;

    return json_decode($response, true);
}

/**
 * Get enabled services for a specific site.
 *
 * @param int $siteid Site ID.
 * @return array
 */
function auth_coursetransit_get_site_services(int $siteid): array {
    global $DB;

    return $DB->get_fieldset_select(
        'auth_coursetransit_site_services',
        'functionname',
        'siteid = :siteid',
        ['siteid' => $siteid]
    );
}

/**
 * Update allowed services for a site.
 *
 * @param int $siteid Site ID.
 * @param array $functions List of function names.
 * @return void
 */
function auth_coursetransit_update_site_services(int $siteid, array $functions): void {
    global $DB;

    $functions[] = 'core_webservice_get_site_info';

    $supported = array_keys(auth_coursetransit_get_supported_services());
    $functions = array_intersect($functions, $supported);
    $functions = array_values(array_unique($functions));

    $DB->delete_records(
        'auth_coursetransit_site_services',
        ['siteid' => $siteid]
    );

    foreach ($functions as $fn) {
        $DB->insert_record('auth_coursetransit_site_services', [
            'siteid' => $siteid,
            'functionname' => $fn,
        ]);
    }
}

/**
 * Logs API call details.
 *
 * @param int $siteid Site ID.
 * @param string $function Function name.
 * @param string $status Status (success or error).
 * @param string|null $message Optional message.
 * @param float|null $executiontime Execution time in seconds.
 * @return void
 */
function auth_coursetransit_log_api_call(
    int $siteid,
    string $function,
    string $status,
    ?string $message = null,
    ?float $executiontime = null
): void {
    global $DB;

    $origin = $_SERVER['HTTP_ORIGIN']
        ?? $_SERVER['HTTP_REFERER']
        ?? null;

    $ip = $_SERVER['REMOTE_ADDR'] ?? null;

    $DB->insert_record('auth_coursetransit_api_logs', [
        'siteid' => $siteid,
        'functionname' => $function,
        'status' => $status, // Success or error.
        'message' => $message,
        'origin' => $origin,
        'ipaddress' => $ip,
        'executiontime' => $executiontime,
        'timecreated' => time(),
    ]);
}

/**
 * Check if plugin setup is complete.
 *
 * @return bool
 */
function auth_coursetransit_is_setup_complete(): bool {
    return (bool) get_config('auth_coursetransit', 'setup_complete');
}
