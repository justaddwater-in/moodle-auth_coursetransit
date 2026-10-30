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
 * JS for token modal in CourseTransit.
 *
 * @module     auth_coursetransit/token_modal
 * @copyright  2026 Justaddwater
 * @author     Himanshu Saini
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['require', 'core/modal', 'core/modal_events', 'core/str'], function(require, Modal, ModalEvents, Str) {
    /**
     * Create the token modal using the API available in the current Moodle version.
     *
     * @param {Object} modalConfig Modal title and body configuration.
     * @return {Promise}
     */
    const createTokenModal = function(modalConfig) {
        if (typeof Modal.create === 'function') {
            return Modal.create(modalConfig);
        }

        return new Promise(function(resolve, reject) {
            require(['core/modal_factory'], function(ModalFactory) {
                const modalPromise = ModalFactory.create({
                    type: ModalFactory.types.DEFAULT,
                    title: modalConfig.title,
                    body: modalConfig.body
                });

                modalPromise.done(function(modal) {
                    resolve(modal);
                });
                modalPromise.fail(function(error) {
                    reject(error);
                });
            }, reject);
        });
    };

    return {
        show: function() {
            const tokenElement = document.getElementById('coursetransit-token');

            if (!tokenElement) {
                return;
            }

            const token = tokenElement.dataset.token || '';

            Str.get_strings([
                {
                    key: 'tokenmodaltitle',
                    component: 'auth_coursetransit'
                },
                {
                    key: 'wizard_token_once',
                    component: 'auth_coursetransit'
                },
                {
                    key: 'wizard_token_store',
                    component: 'auth_coursetransit'
                },
                {
                    key: 'wizard_token_important',
                    component: 'auth_coursetransit'
                },
                {
                    key: 'wizard_token_warning',
                    component: 'auth_coursetransit'
                }
            ]).then(function(strings) {
                const modalConfig = {
                    title: strings[0],
                    body: `
                        <div style="
                            display:flex;
                            flex-direction:column;
                            gap:15px;
                        ">
                            <div>
                                <p style="margin-bottom:5px;">${strings[1]}</p>
                                <p style="margin:0; color:#6c757d;">${strings[2]}</p>
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
                                <strong style="color:#dc3545; font-size:14px;">${strings[3]}</strong>
                                <div style="font-weight:600; font-size:14px;">${strings[4]}</div>
                            </div>
                        </div>
                    `
                };

                return createTokenModal(modalConfig);
            }).then(function(modal) {
                modal.show();

                const element = modal.getRoot()[0].querySelector('#token-value');

                if (element) {
                    element.textContent = token;
                }

                modal.getRoot().on(ModalEvents.hidden, function() {
                    const url = new URL(window.location.href);

                    url.searchParams.delete('token');
                    url.searchParams.delete('created');

                    window.history.replaceState({}, document.title, url.pathname);
                });

                return modal;
            }).catch(function() {
                // Keep the page usable if the token modal cannot be displayed.
                return null;
            });
        }
    };
});
