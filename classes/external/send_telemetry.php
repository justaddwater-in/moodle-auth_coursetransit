<?php
// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * External function to handle telemetry consent and transmit data via cURL.
 *
 * @package    auth_coursetransit
 * @copyright  2025 Justaddwater <contact@justaddwater.in>
 * @author     Himanshu Saini
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_coursetransit\external;

defined('MOODLE_INTERNAL') || die();

// Moodle 4.1 uses the legacy external API classes. Alias them into the newer
// namespace only when it is unavailable, so this class can run on both 4.1 and
// Moodle 4.2+ without using deprecated global classes on newer Moodle releases.
global $CFG;
if (!class_exists('\\core_external\\external_api')) {
    require_once($CFG->libdir . '/externallib.php');
    $externalclassaliases = [
        '\\external_api' => '\\core_external\\external_api',
        '\\external_function_parameters' => '\\core_external\\external_function_parameters',
        '\\external_value' => '\\core_external\\external_value',
        '\\external_single_structure' => '\\core_external\\external_single_structure',
    ];

    foreach ($externalclassaliases as $legacyclass => $namespacedclass) {
        if (class_exists($legacyclass) && !class_exists($namespacedclass, false)) {
            class_alias($legacyclass, $namespacedclass);
        }
    }
}

/**
 * External function for telemetry consent and transmission.
 */
class send_telemetry extends \core_external\external_api {
    /**
     * Write a diagnostic line without including personal data or the payload.
     *
     * @param string $message Diagnostic message.
     * @param array $context Safe diagnostic context.
     * @return void
     */
    private static function log_telemetry_diagnostic(string $message, array $context = []): void {
        $suffix = '';
        if ($context) {
            $suffix = ' ' . json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }
        debugging('[CourseTransit telemetry] ' . $message . $suffix, DEBUG_DEVELOPER);
    }

    /**
     * Build a short, redacted summary of the endpoint response for diagnostics.
     *
     * @param string $response Raw response body.
     * @param array $sensitivevalues Values that must be redacted from the summary.
     * @return string Safe response summary.
     */
    private static function get_response_summary(string $response, array $sensitivevalues = []): string {
        if ($response === '') {
            return 'empty response body';
        }

        $decoded = json_decode($response, true);
        if (is_array($decoded)) {
            $summary = [];
            foreach (['success', 'status', 'code', 'message', 'error'] as $key) {
                if (isset($decoded[$key]) && (is_scalar($decoded[$key]) || $decoded[$key] === null)) {
                    $summary[$key] = (string)$decoded[$key];
                }
            }
            if ($summary) {
                $text = json_encode($summary, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            } else {
                $text = 'JSON response without status/message fields';
            }
        } else {
            $text = strip_tags(substr($response, 0, 500));
        }

        // Avoid writing personal identifiers or URLs to server logs.
        $text = preg_replace('/[A-Z0-9._%+-]+@[A-Z0-9.-]+\\.[A-Z]{2,}/i', '[redacted-email]', $text);
        $text = preg_replace('~https?://[^\\s"<>]+~i', '[redacted-url]', $text);
        foreach ($sensitivevalues as $sensitivevalue) {
            if (is_string($sensitivevalue) && $sensitivevalue !== '') {
                $text = str_ireplace($sensitivevalue, '[redacted]', $text);
            }
        }
        $text = preg_replace('/\\s+/', ' ', (string)$text);

        return substr($text, 0, 500);
    }

    /**
     * Define the parameters accepted by the external function.
     *
     * @return \core_external\external_function_parameters
     */
    public static function execute_parameters() {
        return new \core_external\external_function_parameters([
            'optin' => new \core_external\external_value(PARAM_BOOL, 'Whether the user opted in to telemetry'),
        ]);
    }

    /**
     * Execute the telemetry data transmission.
     *
     * @param bool $optin Whether the administrator consented to telemetry.
     * @return array
     */
    public static function execute($optin) {
        global $CFG, $USER;

        $params = self::validate_parameters(self::execute_parameters(), ['optin' => $optin]);
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:config', $context);

        // Persist the administrator's choice even when the external endpoint is unavailable.
        set_config('telemetry_optin', $params['optin'], 'auth_coursetransit');

        if (!$params['optin']) {
            self::log_telemetry_diagnostic('Consent is false; external request intentionally skipped.');
            return [
                'status' => true,
                'message' => 'Telemetry preference saved; no client information was sent ' .
                    'because consent was not granted.',
            ];
        }

        self::log_telemetry_diagnostic('Consent is true; preparing telemetry request.', [
            'moodle_release' => (string)$CFG->release,
            'php_version' => PHP_VERSION,
        ]);

        $pluginversion = get_config('auth_coursetransit', 'version');
        if (empty($pluginversion)) {
            // The installed-version config should normally exist; fall back to the disk version.
            $plugin = new \stdClass();
            require($CFG->dirroot . '/auth/coursetransit/version.php');
            $pluginversion = isset($plugin->version) ? $plugin->version : 'unknown';
            self::log_telemetry_diagnostic('Installed plugin version config was missing; used disk version.', [
                'disk_version_available' => isset($plugin->version),
            ]);
        }

        $payload = [
            'client_type' => 'moodle',
            'plugin' => 'coursetransit',
            'domain' => $CFG->wwwroot,
            'first_name' => $USER->firstname,
            'last_name' => $USER->lastname,
            'email' => $USER->email,
            'company' => '',
            'plugin_version' => $pluginversion,
            'platform_version' => $CFG->release,
            'php_version' => phpversion(),
        ];

        $endpoint = 'https://store.justaddwater.in/api/client-info';
        $encodedpayload = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($encodedpayload === false) {
            self::log_telemetry_diagnostic('Payload JSON encoding failed.', [
                'json_error' => json_last_error_msg(),
            ]);
            return [
                'status' => false,
                'message' => 'Telemetry payload encoding failed. See server logs for ' .
                    'CourseTransit telemetry diagnostics.',
            ];
        }

        try {
            // Use PHP's native cURL extension instead of Moodle's \curl wrapper.
            // TLS certificate and hostname verification remain enabled.
            if (!function_exists('curl_init')) {
                self::log_telemetry_diagnostic(
                    'PHP cURL extension is unavailable; telemetry request was not sent.',
                    [
                        'moodle_release' => (string)$CFG->release,
                        'php_version' => PHP_VERSION,
                        'curl_extension_loaded' => extension_loaded('curl'),
                    ]
                );

                return [
                    'status' => false,
                    'message' => 'The PHP cURL extension is not enabled on this server. Enable it to send telemetry.',
                ];
            }

            $curlhandle = curl_init($endpoint);
            if ($curlhandle === false) {
                self::log_telemetry_diagnostic('PHP curl_init() failed.');
                return [
                    'status' => false,
                    'message' => 'Could not initialize PHP cURL. See server logs for CourseTransit telemetry diagnostics.',
                ];
            }

            curl_setopt_array($curlhandle, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $encodedpayload,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CONNECTTIMEOUT => 5,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_FOLLOWLOCATION => false,
            ]);

            $response = curl_exec($curlhandle);
            $curlerrno = curl_errno($curlhandle);
            $curlerror = curl_error($curlhandle);
            $curlinfo = curl_getinfo($curlhandle);
            $httpstatus = isset($curlinfo['http_code']) ? (int)$curlinfo['http_code'] : 0;
            curl_close($curlhandle);

            $responsesummary = self::get_response_summary(
                (string)($response === false ? '' : $response),
                [
                    $CFG->wwwroot,
                    $USER->firstname,
                    $USER->lastname,
                    $USER->email,
                ]
            );

            // Log transport metadata and a redacted response summary, never the payload.
            self::log_telemetry_diagnostic('Endpoint response received.', [
                'endpoint_host' => 'store.justaddwater.in',
                'http_status' => $httpstatus,
                'curl_errno' => $curlerrno,
                'response_summary' => $responsesummary,
            ]);

            if ($response === false || $curlerrno !== 0) {
                self::log_telemetry_diagnostic('Native PHP cURL transport failed.', [
                    'curl_errno' => $curlerrno,
                    'curl_error' => substr(strip_tags((string)$curlerror), 0, 250),
                    'http_status' => $httpstatus,
                ]);
                return [
                    'status' => false,
                    'message' => 'Telemetry transport failed (PHP cURL error ' . $curlerrno . '). ' .
                        'See server logs for CourseTransit telemetry diagnostics.',
                ];
            }

            if ($httpstatus < 200 || $httpstatus >= 300) {
                return [
                    'status' => false,
                    'message' => 'Telemetry endpoint returned HTTP ' . $httpstatus . '. ' .
                        'See server logs for CourseTransit telemetry diagnostics.',
                ];
            }

            $decodedresponse = json_decode((string)$response, true);
            if (is_array($decodedresponse)) {
                $explicitfailure = (isset($decodedresponse['success']) && $decodedresponse['success'] === false)
                    || (isset($decodedresponse['status']) && (
                        $decodedresponse['status'] === false ||
                        in_array(
                            strtolower((string)$decodedresponse['status']),
                            ['error', 'failed', 'failure', 'rejected'],
                            true
                        )
                    ))
                    || (isset($decodedresponse['error']) &&
                        $decodedresponse['error'] !== '' && $decodedresponse['error'] !== false);

                if ($explicitfailure) {
                    self::log_telemetry_diagnostic(
                        'Endpoint returned an application-level failure despite a successful HTTP status.',
                        [
                            'http_status' => $httpstatus,
                            'response_summary' => $responsesummary,
                        ]
                    );
                    return [
                        'status' => false,
                        'message' => 'Telemetry endpoint rejected client information. ' .
                            'See server logs for CourseTransit telemetry diagnostics.',
                    ];
                }
            }

            return [
                'status' => true,
                'message' => 'Telemetry endpoint returned HTTP ' . $httpstatus . '.',
            ];
        } catch (\Throwable $exception) {
            self::log_telemetry_diagnostic('Unexpected exception while sending telemetry.', [
                'exception_class' => get_class($exception),
                'exception_message' => self::get_response_summary(
                    $exception->getMessage(),
                    [$CFG->wwwroot, $USER->firstname, $USER->lastname, $USER->email]
                ),
            ]);

            return [
                'status' => false,
                'message' => 'Telemetry request failed unexpectedly. See server logs for ' .
                    'CourseTransit telemetry diagnostics.',
            ];
        }
    }

    /**
     * Define the return structure of the external function.
     *
     * @return \core_external\external_single_structure
     */
    public static function execute_returns() {
        return new \core_external\external_single_structure([
            'status' => new \core_external\external_value(PARAM_BOOL, 'Whether the request was successful'),
            'message' => new \core_external\external_value(PARAM_TEXT, 'Diagnostic result message'),
        ]);
    }
}
