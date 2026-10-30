// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * JS for telemetry opt-in handling in CourseTransit settings.
 *
 * @module     auth_coursetransit/settings_telemetry
 * @copyright  2026 Justaddwater <contact@justaddwater.in>
 * @author     Himanshu Saini
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/ajax'], function(Ajax) {
    return {
        init: function(wizardUrl) {
            const launchBtn = document.getElementById('launch-wizard-btn');

            if (!launchBtn) {
                return;
            }

            launchBtn.addEventListener('click', function(e) {
                e.preventDefault();
                launchBtn.classList.add('disabled');
                launchBtn.style.pointerEvents = 'none';
                launchBtn.innerHTML = 'Loading...';

                const checkbox = document.getElementById('coursetransit-telemetry-optin');
                const hasOptedIn = Boolean(checkbox && checkbox.checked);

                Ajax.call([{
                    methodname: 'auth_coursetransit_send_telemetry',
                    args: {optin: hasOptedIn}
                }])[0].then(function(response) {
                    window.location.href = wizardUrl;
                    return response;
                }).catch(function() {
                    window.location.href = wizardUrl;
                    return null;
                });
            });
        }
    };
});
