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
 * Site form for CourseTransit.
 *
 * @package    auth_coursetransit
 * @copyright  2025 Justaddwater <contact@justaddwater.in>
 * @author     Himanshu Saini
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form to create a new CourseTransit site.
 */
class auth_coursetransit_site_form extends moodleform {
    /**
     * Define form elements.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        $mform->addElement('hidden', 'tab', 'sites');
        $mform->setType('tab', PARAM_ALPHA);

        $mform->addElement(
            'html',
            html_writer::div(
                get_string('wizard_site_description', 'auth_coursetransit'),
                'text-muted mb-4'
            )
        );

        // Site name.
        $mform->addElement(
            'text',
            'name',
            get_string('sitename', 'auth_coursetransit')
        );

        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required');

        $mform->addHelpButton(
            'name',
            'sitename',
            'auth_coursetransit'
        );

        // Site URL.
        $mform->addElement(
            'text',
            'domain',
            get_string('siteurl', 'auth_coursetransit')
        );

        $mform->setType('domain', PARAM_TEXT);
        $mform->addRule('domain', null, 'required');

        $mform->addHelpButton(
            'domain',
            'siteurl',
            'auth_coursetransit'
        );

        // Action button.
        $this->add_action_buttons(
            false,
            get_string('addsite', 'auth_coursetransit')
        );
    }
}
