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
 * Custom admin setting that renders the CourseTransit setup card in place
 * of an input control, so the same server-rendered markup appears both on
 * the post-install/upgrade "New settings" review page and on the normal
 * plugin settings page — no JS injection required.
 *
 * @package     auth_coursetransit
 * @copyright  2025 Justaddwater <contact@justaddwater.in>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace auth_coursetransit;

/**
 * Renders the CourseTransit setup/welcome card as an admin setting.
 */
class admin_setting_welcome_card extends \admin_setting {
    /**
     * Constructor.
     *
     * @param string $name Setting name (e.g. 'auth_coursetransit/setup_wizard_field').
     */
    public function __construct($name) {
        parent::__construct($name, '', '', 1);
    }

    /**
     * Reads the stored value, if any.
     *
     * @return mixed
     */
    public function get_setting() {
        return $this->config_read($this->name);
    }

    /**
     * Stores a fixed value so Moodle stops treating this as an
     * outstanding "new setting" after the first save.
     *
     * @param mixed $data Posted form data (unused).
     * @return string Empty string on success.
     */
    public function write_setting($data) {
        $this->config_write($this->name, 1);
        return '';
    }

    /**
     * Renders the card. Returning raw markup here (rather than calling
     * format_admin_setting()) means no label/input row is added — only
     * the card itself, the same way admin_setting_heading works.
     *
     * @param mixed $data Current setting value (unused).
     * @param string $query Search query (unused).
     * @return string HTML.
     */
    public function output_html($data, $query = '') {
        // ADDED $PAGE global here to inject JavaScript.
        global $OUTPUT, $PAGE;

        // Isolate the wizard URL so it can be passed to the JS module.
        $wizardurl = (new \moodle_url('/auth/coursetransit/wizard.php'))->out(false);
        $setupcomplete = auth_coursetransit_is_setup_complete();

        $context = [
            'description' => get_string('settingscarddesc', 'auth_coursetransit'),

            'wizardbutton' => get_string('launchsetupwizard', 'auth_coursetransit'),
            'wizardurl' => $wizardurl,
            'setupwarningtitle' => get_string('setupwarningtitle', 'auth_coursetransit'),
            'setupwarningdesc' => get_string('setupwarningdesc', 'auth_coursetransit'),

            'dashboardbutton' => get_string('opendashboard', 'auth_coursetransit'),
            'dashboardurl' => (new \moodle_url('/auth/coursetransit/index.php'))->out(false),
            'setupcompletetitle' => get_string('setupcompletetitle', 'auth_coursetransit'),
            'setupcompletedesc' => get_string('setupcompletedesc', 'auth_coursetransit'),

            'setupcomplete' => $setupcomplete,
            'setupincomplete' => !$setupcomplete,
        ];

        // It passes the $wizardurl to the init() function of the JS module.
        if (!$setupcomplete) {
            $PAGE->requires->js_call_amd('auth_coursetransit/settings_telemetry', 'init', [$wizardurl]);
        }

        return $OUTPUT->render_from_template('auth_coursetransit/settings_card', $context);
    }
}
