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
 * CourseTransit API endpoint.
 *
 * @package    auth_coursetransit
 * @copyright  2025 Justaddwater <contact@justaddwater.in>
 * @author     Himanshu Saini
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_DEBUG_DISPLAY', true);
define('AJAX_SCRIPT', true);
define('NO_MOODLE_COOKIES', true);

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/lib.php');

header('Content-Type: application/json');

$starttime = microtime(true);
$siteid    = 0;
$function  = '';

try {
    global $DB;

    // Read & validate JSON.
    $raw  = file_get_contents('php://input');
    $data = json_decode($raw, true);

    if (!is_array($data)) {
        throw new Exception('Invalid JSON payload');
    }

    $token    = trim($data['token'] ?? '');
    $function = trim($data['function'] ?? '');
    $payload  = $data['payload'] ?? [];

    if (!$token || !$function) {
        throw new Exception('Missing required parameters');
    }

    // Validate function whitelist.
    $supported = auth_coursetransit_get_supported_services();

    if (!isset($supported[$function])) {
        throw new Exception('Invalid service requested');
    }

    // Determine request origin.

    $origin = $_SERVER['HTTP_ORIGIN']
        ?? $_SERVER['HTTP_REFERER']
        ?? '';

    if (!$origin) {
        throw new Exception('Request origin not found');
    }

    $originhost = parse_url($origin, PHP_URL_HOST);

    if (!$originhost) {
        throw new Exception('Invalid request origin');
    }

    $originhost = strtolower($originhost);

    // Identify site by token.
    $site = $DB->get_record(
        'auth_coursetransit_sites',
        [
            'token'   => $token,
            'enabled' => 1,
        ],
        '*',
        MUST_EXIST
    );

    $siteid = (int)$site->id;
    $storeddomain = strtolower(trim($site->domain));

    // Validate domain (subdomains allowed).
    $domainallowed = ($originhost === $storeddomain) || str_ends_with($originhost, '.' . $storeddomain);

    if (!$domainallowed) {
        throw new Exception('Request origin not allowed');
    }

    // Check service permission for site.
    if (
        !$DB->record_exists(
            'auth_coursetransit_site_services',
            [
                'siteid'       => $site->id,
                'functionname' => $function,
            ]
        )
    ) {
        throw new Exception('Service not allowed for this site');
    }

    // Execute Moodle Webservice.
    $result = auth_coursetransit_execute_action($function, $payload);

    // LOG SUCCESS.
    auth_coursetransit_log_api_call(
        $siteid,
        $function,
        'success',
        null,
        microtime(true) - $starttime
    );

    echo json_encode([
        'success' => true,
        'data'    => $result,
    ]);
    exit;
} catch (Throwable $e) {
    // LOG ERROR (even if site lookup failed).
    $internalmessage = substr($e->getMessage(), 0, 255);

    auth_coursetransit_log_api_call(
        $siteid,
        $function ?: 'unknown',
        'error',
        $internalmessage,
        microtime(true) - $starttime
    );
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => get_string('apierror', 'auth_coursetransit'),
    ]);
    exit;
}
