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
 * CourseTransit external services.
 *
 * @package    auth_coursetransit
 * @copyright  2025 Justaddwater <contact@justaddwater.in>
 * @author     Himanshu Saini
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = [
    'auth_coursetransit_execute_action' => [
        'classname' => 'auth_coursetransit\external\execute_action',
        'methodname' => 'execute',
        'classpath' => '',
        'description' => get_string('gatewayservice', 'auth_coursetransit'),
        'type' => 'write',
        'capabilities' => 'webservice/rest:use',
        'ajax' => false,
    ],

    'auth_coursetransit_send_telemetry' => [
        'classname'     => 'auth_coursetransit\external\send_telemetry',
        'methodname'    => 'execute',
        'description'   => 'Sends telemetry data to ChargePanda on wizard completion.',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => true,
    ],
];
