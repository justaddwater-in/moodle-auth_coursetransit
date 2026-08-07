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

        global $DB;

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

            // Validate site URL from payload.
            $siteurl = trim(
                $payloadarray['siteurl'] ?? ''
            );

            if (empty($siteurl)) {
                throw new invalid_parameter_exception(
                    get_string('missingsiteurl', 'auth_coursetransit')
                );
            }

            // Normalize URL.
            if (!preg_match('~^https?://~i', $siteurl)) {
                $siteurl = 'https://' . $siteurl;
            }

            $originhost = parse_url(
                $siteurl,
                PHP_URL_HOST
            );

            if (!$originhost) {
                throw new invalid_parameter_exception(
                    get_string('invalidsiteurl', 'auth_coursetransit')
                );
            }

            $originhost = strtolower(
                trim($originhost)
            );

            // Find matching registered site.
            $site = null;

            $registeredsites = $DB->get_records(
                'auth_coursetransit_sites',
                ['enabled' => 1]
            );

            foreach ($registeredsites as $registeredsite) {
                $storeddomain = strtolower(
                    trim($registeredsite->domain)
                );

                $allowed =
                    ($originhost === $storeddomain)
                    || preg_match(
                        '/\.' .
                        preg_quote(
                            $storeddomain,
                            '/'
                        ) .
                        '$/',
                        $originhost
                    );

                if ($allowed) {
                    $site = $registeredsite;
                    break;
                }
            }

            if (!$site) {
                throw new invalid_parameter_exception(
                    get_string('unauthorizedsite', 'auth_coursetransit')
                );
            }

            $siteid = (int) $site->id;

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

            // Remove internal payload fields.
            unset(
                $payloadarray['siteurl']
            );

            // Execute internal action.
            $result =
                \auth_coursetransit_execute_action(
                    $function,
                    $payloadarray
                );

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
                get_string(
                    'success',
                    'auth_coursetransit'
                )
            ),

            'data' => new external_value(
                PARAM_RAW,
                get_string(
                    'responsejson',
                    'auth_coursetransit'
                )
            ),
        ]);
    }
}
