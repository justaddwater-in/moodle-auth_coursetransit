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

use cache;
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
    $service = $DB->get_record(
        'external_services',
        ['shortname' => 'auth_coursetransit']
    );

    if (!$service) {
        $service = (object) [
            'name' => get_string(
                'coursetransitservice',
                'auth_coursetransit'
            ),
            'shortname' =>
                'auth_coursetransit',
            'enabled' => 1,
            'restrictedusers' => 1,
            'timecreated' => time(),
        ];

        $service->id =
            $DB->insert_record(
                'external_services',
                $service
            );
    } else {
        // Keep service updated.
        $service->name = get_string(
            'coursetransitservice',
            'auth_coursetransit'
        );

        $service->enabled = 1;
        $service->restrictedusers = 1;

        $DB->update_record(
            'external_services',
            $service
        );
    }

    // CRITICAL: Ensure ALL supported functions are enabled ONCE.
    auth_coursetransit_update_services([
        'auth_coursetransit_execute_action',
    ]);

    // Technical user.
    $userid = (int) get_config('auth_coursetransit', 'technicaluserid');
    if (!$userid) {
        throw new moodle_exception(
            'technicalusernotset',
            'auth_coursetransit'
        );
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
        'core_webservice_get_site_info' => get_string('service_getsiteinfo', 'auth_coursetransit'),
        // User-related services.
        'core_user_create_users' => get_string('service_createusers', 'auth_coursetransit'),
        'core_user_delete_users' => get_string('service_deleteusers', 'auth_coursetransit'),
        'core_user_get_users_by_field' => get_string('service_getusersbyfield', 'auth_coursetransit'),
        'core_user_update_users' => get_string('service_updateusers', 'auth_coursetransit'),

        // Course-related services.
        'core_course_get_courses' => get_string('service_getcourses', 'auth_coursetransit'),
        'core_course_get_courses_by_field' => get_string('service_getcoursesbyfield', 'auth_coursetransit'),
        'core_course_get_categories' => get_string('service_getcategories', 'auth_coursetransit'),
        'core_course_get_contents' => get_string('service_getcontents', 'auth_coursetransit'),

        // Enrolment-related services.
        'core_enrol_get_users_courses' => get_string('service_getuserenrolments', 'auth_coursetransit'),
        'core_enrol_get_enrolled_users' => get_string('service_getenrolledusers', 'auth_coursetransit'),
        'enrol_manual_enrol_users' => get_string('service_manualenrol', 'auth_coursetransit'),
        'enrol_manual_unenrol_users' => get_string('service_manualunenrol', 'auth_coursetransit'),
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

    $allowed = [
        'auth_coursetransit_execute_action',
    ];

    $functions = array_intersect(
        $functions,
        $allowed
    );

    $DB->delete_records(
        'external_services_functions',
        ['externalserviceid' => $service->id]
    );

    foreach ($functions as $fn) {
        if (
            !$DB->record_exists(
                'external_functions',
                ['name' => $fn]
            )
        ) {
            continue;
        }

        $DB->insert_record(
            'external_services_functions',
            [
                'externalserviceid' =>
                    $service->id,
                'functionname' => $fn,
            ]
        );
    }
}

/**
 * Create new CourseTransit site.
 *
 * @param string $name Site name.
 * @param string $domain Site domain.
 * @return string Newly generated site-specific web-service token.
 */
function auth_coursetransit_create_site(string $name, string $domain): string {
    global $CFG, $DB;

    require_once($CFG->dirroot . '/webservice/lib.php');
    require_once($CFG->libdir . '/externallib.php');

    $time = time();
    $domain = rtrim($domain, '/');

    if (
        $DB->record_exists(
            'auth_coursetransit_sites',
            ['domain' => $domain]
        )
    ) {
        throw new moodle_exception(
            'sitedomainexists',
            'auth_coursetransit'
        );
    }

    $userid = (int) get_config('auth_coursetransit', 'technicaluserid');
    if (!$userid) {
        throw new moodle_exception(
            'technicalusernotset',
            'auth_coursetransit'
        );
    }

    $service = $DB->get_record(
        'external_services',
        ['shortname' => 'auth_coursetransit'],
        '*',
        MUST_EXIST
    );

    // Ensure the technical user is authorised for the CourseTransit service.
    $ws = new webservice();
    if (
        !$DB->record_exists(
            'external_services_users',
            [
                'externalserviceid' => $service->id,
                'userid' => $userid,
            ]
        )
    ) {
        $ws->add_ws_authorised_user((object) [
            'externalserviceid' => $service->id,
            'userid' => $userid,
        ]);
    }

    $transaction = $DB->start_delegated_transaction();

    $siteid = $DB->insert_record('auth_coursetransit_sites', [
        'name' => $name,
        'domain' => $domain,
        'technicaluserid' => $userid,
        'tokenid' => null,
        'enabled' => 1,
        'timecreated' => $time,
        'timemodified' => $time,
    ]);

    // Every registered website gets its own permanent Moodle web-service token.
    $token = external_generate_token(
        EXTERNAL_TOKEN_PERMANENT,
        $service->id,
        $userid,
        context_system::instance()
    );

    $tokenrecord = $DB->get_record(
        'external_tokens',
        ['token' => $token],
        'id,token',
        MUST_EXIST
    );

    $DB->set_field(
        'auth_coursetransit_sites',
        'tokenid',
        $tokenrecord->id,
        ['id' => $siteid]
    );

    $allservices = array_keys(auth_coursetransit_get_supported_services());
    auth_coursetransit_update_site_services($siteid, $allservices);

    $transaction->allow_commit();

    return $tokenrecord->token;
}

/**
 * Normalize a WordPress site URL to an exact host for token/site consistency checks.
 *
 * This helper does not select or authorize a site. The authenticated web-service
 * token has already identified the registered site; the claimed URL is only
 * checked to ensure the existing WordPress connection is using the token issued
 * for that registered site.
 *
 * @param string $siteurl WordPress site URL or host.
 * @return string|null Normalized lowercase host, or null when invalid.
 */
function auth_coursetransit_normalize_site_host(string $siteurl): ?string {
    $siteurl = trim($siteurl);

    if ($siteurl === '') {
        return null;
    }

    if (!preg_match('~^https?://~i', $siteurl)) {
        $siteurl = 'https://' . $siteurl;
    }

    $host = parse_url($siteurl, PHP_URL_HOST);

    if (!is_string($host) || $host === '') {
        return null;
    }

    return strtolower(rtrim($host, '.'));
}

/**
 * Get the token currently bound to a CourseTransit site.
 *
 * @param int $siteid Site ID.
 * @return string
 */
function auth_coursetransit_get_site_token(int $siteid): string {
    global $DB;

    $site = $DB->get_record(
        'auth_coursetransit_sites',
        ['id' => $siteid],
        'id,tokenid',
        MUST_EXIST
    );

    if (empty($site->tokenid)) {
        return '';
    }

    $token = $DB->get_record(
        'external_tokens',
        ['id' => $site->tokenid],
        'token'
    );

    return $token ? $token->token : '';
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
 * Ensure the native Moodle CourseTransit source-domain profile field exists.
 *
 * Uses Moodle's native user profile field tables instead of modifying the
 * core user table.
 *
 * @return void
 */
function auth_coursetransit_ensure_source_domain_field(): void {
    global $DB;

    $field = $DB->get_record(
        'user_info_field',
        ['shortname' => 'coursetransit_sourcedomain'],
        'id'
    );

    if ($field) {
        return;
    }

    $category = $DB->get_record(
        'user_info_category',
        ['name' => 'CourseTransit'],
        'id'
    );

    if ($category) {
        $categoryid = (int) $category->id;
    } else {
        $categoryid = $DB->insert_record(
            'user_info_category',
            (object) [
                'name' => 'CourseTransit',
                'sortorder' => (int) $DB->get_field_sql(
                    'SELECT COALESCE(MAX(sortorder), 0) + 1
                       FROM {user_info_category}'
                ),
            ]
        );
    }

    $sortorder = (int) $DB->get_field_sql(
        'SELECT COALESCE(MAX(sortorder), 0) + 1
           FROM {user_info_field}
          WHERE categoryid = :categoryid',
        ['categoryid' => $categoryid]
    );

    $DB->insert_record(
        'user_info_field',
        (object) [
            'shortname' => 'coursetransit_sourcedomain',
            'name' => 'CourseTransit Source Domain',
            'datatype' => 'text',
            'description' => 'The registered CourseTransit website that created this user.',
            'descriptionformat' => FORMAT_PLAIN,
            'categoryid' => $categoryid,
            'sortorder' => $sortorder,
            'required' => 0,
            'locked' => 1,
            'visible' => 1,
            'forceunique' => 0,
            'signup' => 0,
            'defaultdata' => '',
            'defaultdataformat' => FORMAT_PLAIN,
            'param1' => '255',
            'param2' => '0',
            'param3' => '0',
            'param4' => '',
            'param5' => '',
        ]
    );
}

/**
 * Get the CourseTransit source-domain profile field.
 *
 * @return object|null Profile field record.
 */
function auth_coursetransit_get_source_domain_field(): ?object {
    global $DB;

    return $DB->get_record(
        'user_info_field',
        ['shortname' => 'coursetransit_sourcedomain'],
        '*'
    ) ?: null;
}

/**
 * Save the registered CourseTransit source domain against a Moodle user.
 *
 * The domain must come from the authenticated registered CourseTransit site.
 *
 * @param int $userid Moodle user ID.
 * @param string $domain Registered CourseTransit domain.
 * @return void
 */
function auth_coursetransit_set_user_source_domain(
    int $userid,
    string $domain
): void {
    global $DB;

    $field = auth_coursetransit_get_source_domain_field();

    if (!$field) {
        return;
    }

    $domain = function_exists('auth_coursetransit_normalize_registered_domain')
        ? auth_coursetransit_normalize_registered_domain($domain)
        : strtolower(rtrim(trim($domain), '.'));

    if (empty($domain)) {
        return;
    }

    $existing = $DB->get_record(
        'user_info_data',
        [
            'userid' => $userid,
            'fieldid' => $field->id,
        ],
        'id'
    );

    if ($existing) {
        $DB->set_field(
            'user_info_data',
            'data',
            $domain,
            ['id' => $existing->id]
        );
        return;
    }

    $DB->insert_record(
        'user_info_data',
        (object) [
            'userid' => $userid,
            'fieldid' => $field->id,
            'data' => $domain,
            'dataformat' => FORMAT_PLAIN,
        ]
    );
}

/**
 * Save the source domain for users returned by the Moodle create-users API.
 *
 * @param mixed $result Result from core_user_create_users.
 * @param string $domain Registered CourseTransit domain.
 * @return void
 */
function auth_coursetransit_set_created_users_source_domain(
    $result,
    string $domain
): void {
    if (is_array($result) && isset($result['users']) && is_array($result['users'])) {
        $result = $result['users'];
    }

    if (!is_array($result)) {
        return;
    }

    foreach ($result as $user) {
        $userid = 0;

        if (is_array($user) && isset($user['id'])) {
            $userid = (int) $user['id'];
        } else if (is_object($user) && isset($user->id)) {
            $userid = (int) $user->id;
        }

        if ($userid > 0) {
            auth_coursetransit_set_user_source_domain($userid, $domain);
        }
    }
}


/**
 * Execute Moodle webservice function internally.
 *
 * @param string $function Function name.
 * @param array $payload Request payload.
 * @return mixed
 */

/**
 * Validate and constrain a CourseTransit action payload before it reaches
 * Moodle's generic external-function dispatcher.
 *
 * The generic gateway is retained for backward compatibility with the
 * WordPress plugin, but privileged operations are subject to explicit,
 * function-specific validation here.
 *
 * @param string $function External function name.
 * @param array $payload Payload to validate and normalize.
 * @param int $siteid Registered CourseTransit site ID.
 * @return void
 */
function auth_coursetransit_validate_action_payload(
    string $function,
    array &$payload,
    int $siteid
): void {
    global $DB;

    // Keep the gateway bounded even for read-only operations.
    if (count($payload) > 50) {
        throw new invalid_parameter_exception(
            'CourseTransit payload contains too many top-level fields.'
        );
    }

    switch ($function) {
        case 'core_user_create_users':
            if (empty($payload['users']) || !is_array($payload['users'])) {
                throw new invalid_parameter_exception(
                    'User creation requires a users array.'
                );
            }

            if (count($payload['users']) > 100) {
                throw new invalid_parameter_exception(
                    'A maximum of 100 users can be created in one request.'
                );
            }

            foreach ($payload['users'] as $index => &$user) {
                if (!is_array($user)) {
                    throw new invalid_parameter_exception(
                        'Invalid user record at index ' . $index . '.'
                    );
                }

                // These fields can grant or alter privileged Moodle identity
                // characteristics and are never accepted from the generic proxy.
                foreach (
                    [
                        'role',
                        'roles',
                        'roleid',
                        'capabilities',
                        'siteadmin',
                        'systemrole',
                        'contextid',
                    ] as $forbidden
                ) {
                    if (array_key_exists($forbidden, $user)) {
                        throw new invalid_parameter_exception(
                            'Field "' . $forbidden .
                            '" is not permitted when creating users.'
                        );
                    }
                }

                // Do not allow the caller to select an arbitrary authentication
                // backend. CourseTransit-created users use Moodle manual auth.
                $user['auth'] = 'manual';

                // Keep password support for existing CourseTransit user-sync
                // flows, but ensure the value is a string and not an oversized
                // arbitrary payload.
                if (array_key_exists('password', $user)) {
                    if (
                        !is_string($user['password'])
                        || strlen($user['password']) > 255
                    ) {
                        throw new invalid_parameter_exception(
                            'Invalid password value.'
                        );
                    }
                }
            }
            unset($user);
            break;

        case 'enrol_manual_enrol_users':
            if (
                empty($payload['enrolments'])
                || !is_array($payload['enrolments'])
            ) {
                throw new invalid_parameter_exception(
                    'Manual enrolment requires an enrolments array.'
                );
            }

            if (count($payload['enrolments']) > 100) {
                throw new invalid_parameter_exception(
                    'A maximum of 100 enrolments can be processed in one request.'
                );
            }

            $studentrole = $DB->get_record(
                'role',
                ['shortname' => 'student'],
                'id',
                MUST_EXIST
            );

            foreach ($payload['enrolments'] as $index => &$enrolment) {
                if (!is_array($enrolment)) {
                    throw new invalid_parameter_exception(
                        'Invalid enrolment record at index ' . $index . '.'
                    );
                }

                if (!isset($enrolment['courseid'], $enrolment['userid'])) {
                    throw new invalid_parameter_exception(
                        'Each enrolment requires courseid and userid.'
                    );
                }

                $courseid = (int) $enrolment['courseid'];
                $userid = (int) $enrolment['userid'];

                if (!$DB->record_exists('course', ['id' => $courseid])) {
                    throw new invalid_parameter_exception(
                        'The requested course does not exist.'
                    );
                }

                if ($courseid === SITEID) {
                    throw new invalid_parameter_exception(
                        'The site home course cannot be used for CourseTransit enrolment.'
                    );
                }

                if (
                    !$DB->record_exists(
                        'user',
                        [
                            'id' => $userid,
                            'deleted' => 0,
                        ]
                    )
                ) {
                    throw new invalid_parameter_exception(
                        'The requested user does not exist.'
                    );
                }

                // CourseTransit may grant only the normal learner role.
                // This removes the audit's teacher/manager privilege-escalation
                // path while preserving normal student enrolment.
                if (
                    isset($enrolment['roleid'])
                    && (int) $enrolment['roleid'] !== (int) $studentrole->id
                ) {
                    throw new invalid_parameter_exception(
                        'CourseTransit may only assign the student role.'
                    );
                }

                $enrolment['roleid'] = (int) $studentrole->id;
            }
            unset($enrolment);
            break;

        case 'enrol_manual_unenrol_users':
            if (
                empty($payload['enrolments'])
                || !is_array($payload['enrolments'])
            ) {
                throw new invalid_parameter_exception(
                    'Manual unenrolment requires an enrolments array.'
                );
            }

            if (count($payload['enrolments']) > 100) {
                throw new invalid_parameter_exception(
                    'A maximum of 100 unenrolments can be processed in one request.'
                );
            }

            foreach ($payload['enrolments'] as $index => $unenrolment) {
                if (
                    !is_array($unenrolment)
                    || !isset($unenrolment['courseid'], $unenrolment['userid'])
                ) {
                    throw new invalid_parameter_exception(
                        'Each unenrolment requires courseid and userid.'
                    );
                }

                $courseid = (int) $unenrolment['courseid'];
                $userid = (int) $unenrolment['userid'];

                if (!$DB->record_exists('course', ['id' => $courseid])) {
                    throw new invalid_parameter_exception(
                        'The requested course does not exist.'
                    );
                }

                if ($courseid === SITEID) {
                    throw new invalid_parameter_exception(
                        'The site home course cannot be used for CourseTransit unenrolment.'
                    );
                }

                if (
                    !$DB->record_exists(
                        'user',
                        [
                            'id' => $userid,
                            'deleted' => 0,
                        ]
                    )
                ) {
                    throw new invalid_parameter_exception(
                        'The requested user does not exist.'
                    );
                }

                // Do not allow the generic proxy to remove teachers/managers
                // from courses. CourseTransit is intended to manage learners.
                $coursecontext = context_course::instance($courseid);
                $roles = get_user_roles($coursecontext, $userid, false);

                foreach ($roles as $role) {
                    if ($role->shortname !== 'student') {
                        throw new invalid_parameter_exception(
                            'CourseTransit cannot unenrol users with teaching or management roles.'
                        );
                    }
                }
            }
            break;

        case 'core_user_delete_users':
            if (empty($payload['userids']) || !is_array($payload['userids'])) {
                throw new invalid_parameter_exception(
                    'User deletion requires a userids array.'
                );
            }

            if (count($payload['userids']) > 100) {
                throw new invalid_parameter_exception(
                    'A maximum of 100 users can be deleted in one request.'
                );
            }

            foreach ($payload['userids'] as $userid) {
                $userid = (int) $userid;
                $user = $DB->get_record(
                    'user',
                    ['id' => $userid, 'deleted' => 0],
                    'id,username'
                );

                if (!$user) {
                    throw new invalid_parameter_exception(
                        'The requested user does not exist.'
                    );
                }

                // Never allow the generic CourseTransit proxy to delete an
                // account that has site-administration capability.
                $systemcontext = context_system::instance();
                if (
                    has_capability(
                        'moodle/site:config',
                        $systemcontext,
                        $userid
                    )
                ) {
                    throw new invalid_parameter_exception(
                        'CourseTransit cannot delete a site administrator account.'
                    );
                }
            }
            break;

        case 'core_user_update_users':
            if (empty($payload['users']) || !is_array($payload['users'])) {
                throw new invalid_parameter_exception(
                    'User update requires a users array.'
                );
            }

            if (count($payload['users']) > 100) {
                throw new invalid_parameter_exception(
                    'A maximum of 100 users can be updated in one request.'
                );
            }

            $systemcontext = context_system::instance();

            foreach ($payload['users'] as $index => $user) {
                if (!is_array($user) || empty($user['id'])) {
                    throw new invalid_parameter_exception(
                        'Each user update requires a valid user id.'
                    );
                }

                $userid = (int) $user['id'];
                $targetuser = $DB->get_record(
                    'user',
                    ['id' => $userid, 'deleted' => 0],
                    'id,username,deleted'
                );

                if (!$targetuser) {
                    throw new invalid_parameter_exception(
                        'The requested user does not exist.'
                    );
                }

                // CourseTransit must never modify a site administrator
                // through the generic user-update proxy. This prevents a
                // privileged caller from changing an administrator account
                // (for example, its password or suspension state).
                if (
                    is_siteadmin($userid)
                    || has_capability(
                        'moodle/site:config',
                        $systemcontext,
                        $userid
                    )
                ) {
                    throw new invalid_parameter_exception(
                        'CourseTransit cannot update a site administrator account.'
                    );
                }

                // Prevent the generic proxy from changing authentication,
                // credentials, account state, or privilege-related properties.
                foreach (
                    [
                        'auth',
                        'password',
                        'suspended',
                        'deleted',
                        'role',
                        'roles',
                        'roleid',
                        'capabilities',
                        'siteadmin',
                        'systemrole',
                        'contextid',
                    ] as $forbidden
                ) {
                    if (array_key_exists($forbidden, $user)) {
                        throw new invalid_parameter_exception(
                            'Field "' . $forbidden .
                            '" is not permitted when updating users.'
                        );
                    }
                }
            }
            break;

        default:
            // Read-only and non-privileged functions still pass through
            // Moodle's own external-function parameter validation.
            break;
    }
}

/**
 * Execute Moodle webservice function internally.
 *
 * @param string $function Function name.
 * @param array $payload Request payload.
 * @return mixed
 */
function auth_coursetransit_execute_action(
    string $function,
    array $payload
) {
    global $CFG;

    require_once($CFG->libdir . '/externallib.php');

    $response = external_api::call_external_function(
        $function,
        $payload
    );

    if (!empty($response['error'])) {
        throw new moodle_exception(
            'apierror',
            'auth_coursetransit',
            '',
            null,
            $response['error']
        );
    }

    return $response['data'];
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
        'auth_coursetransit_services',
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
        'auth_coursetransit_services',
        ['siteid' => $siteid]
    );

    foreach ($functions as $fn) {
        $DB->insert_record('auth_coursetransit_services', [
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

/**
 * Store temporary token in session cache.
 *
 * @param string $token
 * @return void
 */
function auth_coursetransit_set_temp_token(
    string $token
): void {

    $cache = \cache::make(
        'auth_coursetransit',
        'session'
    );

    $cache->set(
        'coursetransit_token',
        $token
    );
}

/**
 * Get temporary token from session cache.
 *
 * Automatically removes token after fetch.
 *
 * @return string
 */
function auth_coursetransit_get_temp_token(): string {

    $cache = \cache::make(
        'auth_coursetransit',
        'session'
    );

    $token = $cache->get(
        'coursetransit_token'
    ) ?: '';

    $cache->delete(
        'coursetransit_token'
    );

    return $token;
}
