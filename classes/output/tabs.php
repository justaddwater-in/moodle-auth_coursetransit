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
 * Class to manage the shared CourseTransit dashboard tabs.
 *
 * Free owns this navigation. When the Pro add-on is installed, the same
 * navigation automatically changes from "Upgrade to Pro" to "License".
 * This makes Free and Pro behave as one product with two components.
 *
 * @package    auth_coursetransit
 * @copyright  2025 Justaddwater
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_coursetransit\output;

use auth_coursetransit\local\pro;

/**
 * Shared CourseTransit navigation.
 */
class tabs {
    /**
     * Dashboard tabs.
     *
     * @param string $current Current tab.
     * @return array<int, array<string, mixed>>
     */
    public static function get_tabs(string $current): array {
        $tabs = [
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

        if (pro::is_installed()) {
            $licensetab = pro::get_license_tab();
            $licensetab['active'] = $current === 'license';
            $tabs[] = $licensetab;
        } else {
            $tabs[] = [
                'label' => '⚡ ' . get_string('tabpro', 'auth_coursetransit'),
                'url' => (new \moodle_url('/auth/coursetransit/pro.php'))->out(false),
                'active' => $current === 'pro',
            ];
        }

        return $tabs;
    }
}
