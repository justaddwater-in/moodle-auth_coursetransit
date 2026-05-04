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
 * Settings for CourseTransit authentication plugin.
 *
 * @package    auth_coursetransit
 * @copyright  2025 Justaddwater <contact@justaddwater.in>
 * @author     Himanshu Saini
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/lib.php');
if ($hassiteconfig) {
    // Add under Authentication section.
    $ADMIN->add('authsettings', new admin_externalpage(
        'auth_coursetransit', // MUST match admin_externalpage_setup().
        get_string('pluginname', 'auth_coursetransit'),
        new moodle_url('/auth/coursetransit/index.php')
    ));

    // Settings page content.

    if ($ADMIN->fulltree) {
        global $OUTPUT;

        $templatecontext = [
            'description' => get_string('settingscarddesc', 'auth_coursetransit'),

            'dashboardbutton' => get_string('opendashboard', 'auth_coursetransit'),

            'wizardbutton' => get_string('launchsetupwizard', 'auth_coursetransit'),

            'dashboardurl' => (
                new moodle_url('/auth/coursetransit/index.php')
            )->out(false),

            'wizardurl' => (
                new moodle_url('/auth/coursetransit/wizard.php')
            )->out(false),

            'setupcomplete' => auth_coursetransit_is_setup_complete(),

            'setupincomplete' => !auth_coursetransit_is_setup_complete(),

            'setupwarningtitle' => get_string(
                'setupwarningtitle',
                'auth_coursetransit'
            ),

            'setupwarningdesc' => get_string(
                'setupwarningdesc',
                'auth_coursetransit'
            ),

            'setupcompletetitle' => get_string(
                'setupcompletetitle',
                'auth_coursetransit'
            ),

            'setupcompletedesc' => get_string(
                'setupcompletedesc',
                'auth_coursetransit'
            ),
        ];

        $content = $OUTPUT->render_from_template(
            'auth_coursetransit/settings_card',
            $templatecontext
        );

        $settings->add(new admin_setting_heading(
            'auth_coursetransit_heading',
            '',
            $content
        ));
    }
}
