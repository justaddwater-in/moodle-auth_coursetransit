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

use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_value;
use core_external\external_single_structure;

/**
 * External function to handle telemetry consent and transmit data via cURL.
 */
class send_telemetry extends external_api {
    /**
     * Define the parameters accepted by the external function.
     *
     * @return external_function_parameters
     */
    public static function execute_parameters() {
        return new external_function_parameters([
            'optin' => new external_value(PARAM_BOOL, 'Whether the user opted in to telemetry'),
        ]);
    }

    /**
     * Execute the telemetry data transmission.
     *
     * @param bool $optin
     * @return array
     */
    public static function execute($optin) {
        global $CFG, $USER;

        // Validate parameters.
        $params = self::validate_parameters(self::execute_parameters(), ['optin' => $optin]);

        // Ensure the user has the correct capability to configure the site.
        $context = \context_system::instance();
        self::validate_context($context);
        require_capability('moodle/site:config', $context);

        // Save the user's choice to the plugin config.
        set_config('telemetry_optin', $params['optin'], 'auth_coursetransit');

        // If the user opted in, send the data using standard PHP cURL.
        if ($params['optin']) {
            $payload = [
                'client_type'      => 'moodle',
                'plugin'           => 'coursetransit',
                'domain'           => $CFG->wwwroot,
                'first_name'       => $USER->firstname,
                'last_name'        => $USER->lastname,
                'email'            => $USER->email,
                'company'          => '',
                'plugin_version'   => get_config('auth_coursetransit', 'version'),
                'platform_version' => $CFG->release,
                'php_version'      => phpversion(),
            ];

            $endpoint = 'https://store.justaddwater.in/api/client-info';

            $curl = new \curl();

            $curl->post(
                $endpoint,
                json_encode($payload),
                [
                    'CURLOPT_TIMEOUT' => 10,
                    'CURLOPT_CONNECTTIMEOUT' => 5,
                    'CURLOPT_HTTPHEADER' => [
                        'Content-Type: application/json',
                        'Accept: application/json',
                    ],
                ]
            );
        }

        return [
            'status' => true,
            'message' => 'Telemetry preference saved successfully.',
        ];
    }

    /**
     * Define the return structure of the external function.
     *
     * @return external_single_structure
     */
    public static function execute_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_BOOL, 'Success status'),
            'message' => new external_value(PARAM_TEXT, 'Status message'),
        ]);
    }
}
