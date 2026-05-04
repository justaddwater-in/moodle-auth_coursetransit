// This file is part of Moodle - http://moodle.org/
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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * JS for token modal in course transit.
 *
 * @module     auth_coursetransit/token_modal
 * @copyright  2026 Justaddwater <contact@justaddwater.in>
 * @author     Himanshu Saini
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['core/modal_factory', 'core/modal_events'], function(ModalFactory, ModalEvents) {

    return {
        show: function() {

            const tokenElement = document.getElementById('coursetransit-token');

            if (!tokenElement) {
                return;
            }

            const token = tokenElement.dataset.token || '';
            ModalFactory.create({
                type: ModalFactory.types.DEFAULT,
                title: 'Save Your API Token',
                body: `
                    <div style="display:flex;flex-direction:column;gap:15px;">
                        <div>
                            <p style="margin-bottom:5px;">
                                This API token will be shown <strong>only once</strong>.
                            </p>
                            <p style="margin:0;color:#6c757d;">
                                Please copy and store it securely.
                            </p>
                        </div>

                        <div style="
                            background:#f8f9fa;
                            border:1px solid #dee2e6;
                            border-radius:8px;
                            padding:12px;
                            position:relative;
                        ">
                            <code id="token-value" style="
                                font-size:13px;
                                color:#d63384;
                                word-break:break-all;
                                display:block;
                            "></code>
                        </div>

                        <div style="
                            background:#fff3cd;
                            border:1px solid #ffeeba;
                            border-left:4px solid #dc3545;
                            border-radius:6px;
                            padding:10px;
                        ">
                            <strong style="color:#dc3545;font-size:14px;">
                                ⚠ Important:
                            </strong>
                            <div style="font-weight:600;font-size:14px;">
                                If you lose this token, you will need to regenerate it.
                            </div>
                        </div>
                    </div>
                `
            }).then(function(modal) {

                modal.show();

                const element = modal.getRoot()[0].querySelector('#token-value');

                if (element) {
                    element.textContent = token;
                }

                // Clean URL.
                modal.getRoot().on(ModalEvents.hidden, function() {
                    const url = new URL(window.location.href);
                    url.searchParams.delete('token');
                    url.searchParams.delete('created');

                    window.history.replaceState({}, document.title, url.pathname);
                });

                return modal;
            }).catch(function() {
                // Handle modal creation failure if necessary.
            });
        }
    };
});
