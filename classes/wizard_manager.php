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
 * Wizard Manager functions for setup wizard.
 *
 * @package    auth_coursetransit
 * @copyright  2025 Justaddwater <contact@justaddwater.in>
 * @author     Himanshu Saini
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_coursetransit;

/**
 * Wizard Manager for auth_coursetransit plugin.
 *
 * @package    auth_coursetransit
 * @copyright  2025 Justaddwater <contact@justaddwater.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class wizard_manager {
    /**
     * Get current wizard step from request.
     *
     * @return int Current step number.
     */
    public static function get_step() {
        return optional_param('step', 1, PARAM_INT);
    }

    /**
     * Get next step number.
     *
     * @param int $step Current step.
     * @return int Next step.
     */
    public static function next($step) {
        return $step + 1;
    }

    /**
     * Mark setup as completed.
     *
     * @return void
     */
    public static function complete() {
        set_config('setup_complete', 1, 'auth_coursetransit');
    }

    /**
     * Check if setup is completed.
     *
     * @return bool True if setup is complete.
     */
    public static function is_complete() {
        return get_config('auth_coursetransit', 'setup_complete');
    }
}
