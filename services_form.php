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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form to manage enabled services for CourseTransit.
 *
 * @package    auth_coursetransit
 * @copyright  2025 Justaddwater <contact@justaddwater.in>
 * @author     Himanshu Saini
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_coursetransit_services_form extends moodleform {
    /**
     * Define form elements.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;
        $mform->disable_form_change_checker();

        $siteid    = $this->_customdata['siteid'] ?? 0;
        $enabled   = $this->_customdata['enabled'] ?? [];
        $supported = auth_coursetransit_get_supported_services();

        $mform->addElement('hidden', 'siteid', $siteid);
        $mform->setType('siteid', PARAM_INT);

        // User services.
        $mform->addElement('header', 'userservices', get_string('userservices', 'auth_coursetransit'));
        $this->add_service_checkboxes($mform, [
            'core_user_create_users',
            'core_user_delete_users',
            'core_user_get_users_by_field',
            'core_user_update_users',
        ], $supported, $enabled);

        // Course services.
        $mform->addElement('header', 'courseservices', get_string('courseservices', 'auth_coursetransit'));
        $this->add_service_checkboxes($mform, [
            'core_course_get_courses',
            'core_course_get_courses_by_field',
            'core_course_get_categories',
            'core_course_get_contents',
        ], $supported, $enabled);

        // Enrolment services.
        $mform->addElement('header', 'enrolmentservices', get_string('enrolmentservices', 'auth_coursetransit'));
        $this->add_service_checkboxes($mform, [
            'core_enrol_get_users_courses',
            'core_enrol_get_enrolled_users',
            'enrol_manual_enrol_users',
            'enrol_manual_unenrol_users',
        ], $supported, $enabled);

        $buttonarray = [];

        // Save button (primary).
        $buttonarray[] = $mform->createElement(
            'submit',
            'submitbutton',
            get_string('savechanges', 'auth_coursetransit')
        );

        // Back button (secondary).
        $buttonarray[] = $mform->createElement(
            'button',
            'backbutton',
            get_string('backtosites', 'auth_coursetransit'),
            [
                'type' => 'button',
                'onclick' => "window.location.href='" . new moodle_url('/auth/coursetransit/index.php') . "'",
            ]
        );

        // Add buttons group.
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        $mform->closeHeaderBefore('buttonar');
    }

    /**
     * Add service checkboxes to the form.
     *
     * @param MoodleQuickForm $mform Form instance.
     * @param array $functions List of function names.
     * @param array $supported Supported services.
     * @param array $enabled Enabled services.
     * @return void
     */
    private function add_service_checkboxes(
        MoodleQuickForm $mform,
        array $functions,
        array $supported,
        array $enabled
    ): void {

        foreach ($functions as $function) {
            if (!isset($supported[$function])) {
                continue;
            }

            $mform->addElement(
                'checkbox',
                "services[$function]",
                '',
                $supported[$function],
            );

            if (in_array($function, $enabled, true)) {
                $mform->setDefault("services[$function]", 1);
            }
        }
    }
}
