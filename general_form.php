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
 * General settings form for CourseTransit authentication plugin.
 *
 * @package    auth_coursetransit
 * @copyright  2025 Justaddwater <contact@justaddwater.in>
 * @author     Himanshu Saini
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class auth_coursetransit_general_form extends moodleform {
    /**
     * Form definition.
     *
     * @return void
     */
    public function definition(): void {

        $mform = $this->_form;

        $customdata = $this->_customdata;

        /*
         * WEB SERVICE SETTINGS
         */

        $mform->addElement(
            'html',
            '
                <div class="mb-4">
                    <h3 class="mb-2">' . get_string('webserviceconfiguration', 'auth_coursetransit') . '
                    </h3>
                    <p class="text-muted">' . get_string('webserviceconfigurationdesc', 'auth_coursetransit') . '
                    </p>
                </div>
            '
        );

        $mform->addElement(
            'advcheckbox',
            'enablerest',
            get_string(
                'enablerestprotocol',
                'auth_coursetransit'
            ),
            get_string(
                'enablerestprotocoldesc',
                'auth_coursetransit'
            )
        );

        $mform->setDefault(
            'enablerest',
            $customdata['enablerest']
        );

        $mform->addElement(
            'advcheckbox',
            'enablewebservices',
            get_string(
                'enablewebservices',
                'auth_coursetransit'
            ),
            get_string(
                'enablewebservicesdesc',
                'auth_coursetransit'
            )
        );

        $mform->setDefault(
            'enablewebservices',
            $customdata['enablewebservices']
        );

        /*
         * AUTO CONFIGURATION
         */

        $status = $customdata['serviceconfigured']
            ? '<span class="badge badge-success">'
                . get_string(
                    'configured',
                    'auth_coursetransit'
                ) .
              '</span>'
            : '<span class="badge badge-warning">'
                . get_string(
                    'notconfigured',
                    'auth_coursetransit'
                ) .
              '</span>';

        $mform->addElement(
            'static',
            'servicestatus',
            get_string(
                'coursetransitwebservice',
                'auth_coursetransit'
            ),
            $status
        );

        if (!$customdata['serviceconfigured']) {
            $mform->addElement(
                'submit',
                'autocreate',
                get_string(
                    'configureautomatically',
                    'auth_coursetransit'
                )
            );
        }

        /*
         * SAVE BUTTONS
         */

        $this->add_action_buttons(
            false,
            get_string(
                'savechanges',
                'auth_coursetransit'
            )
        );
    }
}
