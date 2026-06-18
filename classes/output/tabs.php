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
 * Class to manage dashboard tabs.
 *
 * @package    auth_coursetransit
 * @copyright  2025 Justaddwater <contact@justaddwater.in>
 * @author     Himanshu Saini
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_coursetransit\output;

/**
 * Class to manage dashboard tabs.
 *
 * @package    auth_coursetransit
 * @copyright  2025 Justaddwater
 * @author     Himanshu Saini
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class tabs {
    /**
     * Dashboard tabs.
     *
     * @param string $current
     * @return array
     */
    public static function get_tabs(
        string $current
    ): array {

        return [

            [
                'label' => get_string('dashboard', 'auth_coursetransit'),
                'url' => (new \moodle_url('/auth/coursetransit/index.php'))->out(false),
                'active' => $current === 'dashboard',
            ],

            [
                'label' => get_string('configuration', 'auth_coursetransit'),
                'url' => (new \moodle_url('/auth/coursetransit/general.php'))->out(false),
                'active' => $current === 'configuration',
            ],

            [
                'label' => get_string('summary', 'auth_coursetransit'),
                'url' => (new \moodle_url('/auth/coursetransit/summary.php'))->out(false),
                'active' => $current === 'summary',
            ],
        ];
    }
}
