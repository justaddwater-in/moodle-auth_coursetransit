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
 * CourseTransit Free/Pro integration helper.
 *
 * This class keeps the shared CourseTransit navigation owned by the Free
 * plugin while allowing the Pro add-on to extend it when installed.
 *
 * @package    auth_coursetransit
 * @copyright  2025 Justaddwater
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_coursetransit\local;

/**
 * Detect and expose the optional CourseTransit Pro add-on.
 */
class pro {
    /**
     * Return whether the CourseTransit Pro add-on is installed.
     *
     * We deliberately use Moodle's plugin configuration instead of checking
     * for files on disk. This makes the helper safe when Pro is not installed.
     *
     * @return bool True when Pro is installed.
     */
    public static function is_installed(): bool {
        return !empty(get_config('auth_coursetransitpro', 'version'));
    }

    /**
     * Return the product name used by the shared CourseTransit UI.
     *
     * When the Pro add-on is installed, the shared UI is branded as
     * CourseTransit Pro. Otherwise it remains CourseTransit.
     *
     * @return string Product name.
     */
    public static function get_product_name(): string {
        if (self::is_installed()) {
            return get_string('pluginname', 'auth_coursetransitpro');
        }

        return get_string('pluginname', 'auth_coursetransit');
    }

    /**
     * Return the shared License tab supplied by the Pro add-on.
     *
     * @return array<string, mixed> License tab definition.
     */
    public static function get_license_tab(): array {
        return [
            'label' => get_string('license', 'auth_coursetransitpro'),
            'url' => (new \moodle_url('/auth/coursetransitpro/license.php'))->out(false),
            'active' => false,
        ];
    }
}
