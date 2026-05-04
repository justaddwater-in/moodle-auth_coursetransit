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
 * Form to select API user for CourseTransit.
 *
 * @package    auth_coursetransit
 * @copyright  2025 Justaddwater <contact@justaddwater.in>
 * @author     Himanshu Saini
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_coursetransit_token_form extends moodleform {
    /**
     * Define the form elements.
     *
     * @return void
     */
    public function definition() {
        $mform = $this->_form;

        // Technical user selector.
        $admins  = get_admins();
        $options = [];

        foreach ($admins as $admin) {
            $options[$admin->id] = fullname($admin);
        }

        $mform->addElement(
            'autocomplete',
            'userid',
            get_string('selectapiuser', 'auth_coursetransit'),
            $options,
            [
                'multiple' => false,
            ]
        );

        $mform->addRule('userid', null, 'required');
        $mform->setType('userid', PARAM_INT);
        $mform->addHelpButton('userid', 'apiuser', 'auth_coursetransit');

        // Actions.
        $this->add_action_buttons(false, get_string('savechanges', 'auth_coursetransit'));
    }
}
