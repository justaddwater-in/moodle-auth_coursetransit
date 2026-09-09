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
 * External function to execute CourseTransit actions.
 *
 * @package    auth_coursetransit
 * @copyright  2025 Justaddwater <contact@justaddwater.in>
 * @author     Himanshu Saini
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_coursetransit\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/auth/coursetransit/lib.php');

use external_api;
use external_function_parameters;
use external_single_structure;
use external_value;
use invalid_parameter_exception;
use context_system;

/**
 * CourseTransit external gateway service.
 *
 * @package    auth_coursetransit
 * @copyright  2025 Justaddwater
 * @author     Himanshu Saini
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class execute_action extends external_api {
    /**
     * Parameters.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters(): external_function_parameters {

        return new external_function_parameters([
            'function' => new external_value(
                PARAM_ALPHANUMEXT,
                get_string('servicefunction', 'auth_coursetransit')
            ),
            'payload' => new external_value(
                PARAM_RAW,
                get_string('payloadjson', 'auth_coursetransit'),
                VALUE_OPTIONAL
            ),
        ]);
    }

    /**
     * Execute action.
     *
     * @param string $function Function name.
     * @param string $payload Payload JSON.
     * @return array
     */
    public static function execute(
        string $function,
        string $payload = '{}'
    ): array {

        global $DB, $USER;

        $starttime = microtime(true);
        $siteid = 0;

        self::validate_parameters(
            self::execute_parameters(),
            [
                'function' => $function,
                'payload' => $payload,
            ]
        );

        // REQUIRED: context validation.
        $context = context_system::instance();

        self::validate_context($context);

        // REQUIRED: capability check.
        require_capability(
            'webservice/rest:use',
            $context
        );

        try {
            // Decode payload.
            $payloadarray = json_decode(
                $payload,
                true
            );

            if (!is_array($payloadarray)) {
                throw new invalid_parameter_exception(
                    get_string('invalidpayloadformat', 'auth_coursetransit')
                );
            }

            // Validate supported service.
            $supported =
                \auth_coursetransit_get_supported_services();

            if (!isset($supported[$function])) {
                throw new invalid_parameter_exception(
                    get_string('invalidservice', 'auth_coursetransit')
                );
            }

            // Resolve the registered site from the authenticated Moodle web-service token.
            // The caller-supplied siteurl is never used for authorization.
            $userid = (int) $USER->id;
            $requesttoken = optional_param(
                'wstoken',
                '',
                PARAM_ALPHANUMEXT
            );

            if (empty($requesttoken)) {
                throw new invalid_parameter_exception(
                    get_string('unauthorizedsite', 'auth_coursetransit')
                );
            }

            // Bind the authenticated token to exactly one registered site.
            // This is the authoritative identity for multi-site installations.
            $tokenrecord = $DB->get_record(
                'external_tokens',
                ['token' => $requesttoken],
                'id,userid'
            );

            if (!$tokenrecord || (int) $tokenrecord->userid !== $userid) {
                throw new invalid_parameter_exception(
                    get_string('unauthorizedsite', 'auth_coursetransit')
                );
            }

            $site = $DB->get_record(
                'auth_coursetransit_sites',
                [
                    'enabled' => 1,
                    'technicaluserid' => $userid,
                    'tokenid' => $tokenrecord->id,
                ]
            );

            if (!$site) {
                throw new invalid_parameter_exception(
                    get_string('unauthorizedsite', 'auth_coursetransit')
                );
            }

            $siteid = (int) $site->id;

            // The token is the authoritative site identity. The existing WordPress
            // siteurl is retained only as a consistency check so a token issued for
            // Site A cannot be configured on Site B and then used with Site B's URL.
            // siteurl never selects a site or grants permissions.
            $claimedsiteurl = trim((string)($payloadarray['siteurl'] ?? ''));
            $claimedhost = \auth_coursetransit_normalize_site_host($claimedsiteurl);
            $registeredhost =
                \auth_coursetransit_normalize_site_host((string)$site->domain);

            if (
                empty($claimedhost)
                || empty($registeredhost)
                || $claimedhost !== $registeredhost
            ) {
                throw new invalid_parameter_exception(
                    get_string('siteurlmismatch', 'auth_coursetransit')
                );
            }

            // Siteurl has now served its consistency check and is not forwarded
            // to the internal Moodle function.
            unset($payloadarray['siteurl']);

            // Apply function-specific security validation before invoking the
            // generic Moodle external-function dispatcher. This keeps the
            // existing WordPress API contract while preventing privileged
            // parameters such as teacher/manager role IDs from reaching
            // privileged Moodle functions.
            \auth_coursetransit_validate_action_payload(
                $function,
                $payloadarray,
                $siteid
            );

            // Check service permission.
            if (
                !$DB->record_exists(
                    'auth_coursetransit_services',
                    [
                        'siteid' => $siteid,
                        'functionname' => $function,
                    ]
                )
            ) {
                throw new invalid_parameter_exception(
                    get_string('servicenotallowed', 'auth_coursetransit')
                );
            }

            // Execute internal action.
            $result =
                \auth_coursetransit_execute_action(
                    $function,
                    $payloadarray
                );

            // Record the authenticated registered site's domain for users
            // created by CourseTransit. The domain comes from the token-bound
            // site record, never from the caller's payload.
            if ($function === 'core_user_create_users') {
                \auth_coursetransit_ensure_source_domain_field();
                \auth_coursetransit_set_created_users_source_domain(
                    $result,
                    (string) $site->domain
                );
            }

            // Log success.
            \auth_coursetransit_log_api_call(
                $siteid,
                $function,
                'success',
                null,
                microtime(true) - $starttime
            );

            return self::clean_returnvalue(
                self::execute_returns(),
                [
                    'success' => true,
                    'is_pro' => false,
                    'data' => json_encode($result),
                ]
            );
        } catch (\Throwable $e) {
            // Log error.
            \auth_coursetransit_log_api_call(
                $siteid,
                $function ?: 'unknown',
                'error',
                substr(
                    $e->getMessage(),
                    0,
                    255
                ),
                microtime(true) - $starttime
            );

            return self::clean_returnvalue(
                self::execute_returns(),
                [
                    'success' => false,
                    'is_pro' => false,
                    'data' => json_encode([
                        'error' => get_string(
                            'apierror',
                            'auth_coursetransit'
                        ),
                        'message' => $e->getMessage(),
                    ]),
                ]
            );
        }
    }

    /**
     * Returns.
     *
     * @return external_single_structure
     */
    public static function execute_returns(): external_single_structure {
        return new external_single_structure([
            'success' => new external_value(
                PARAM_BOOL,
                get_string('success', 'auth_coursetransit')
            ),

            'is_pro' => new external_value(
                PARAM_BOOL,
                'is_pro_false',
            ),

            'data' => new external_value(
                PARAM_RAW,
                get_string('responsejson', 'auth_coursetransit')
            ),
        ]);
    }
}
