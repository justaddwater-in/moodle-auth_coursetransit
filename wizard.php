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
 * Wizard for setup user for coursetransit site.
 *
 * @package    auth_coursetransit
 * @copyright  2025 Justaddwater <contact@justaddwater.in>
 * @author     Himanshu Saini
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_once(__DIR__ . '/lib.php');
require_once(__DIR__ . '/form.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

$step = optional_param('step', 1, PARAM_INT);

if (get_config('auth_coursetransit', 'setup_complete') && $step !== 3) {
    redirect(new moodle_url('/auth/coursetransit/index.php'));
}

global $SESSION, $OUTPUT, $PAGE;

$PAGE->set_url('/auth/coursetransit/wizard.php', ['step' => $step]);
$PAGE->set_title(get_string('pluginname', 'auth_coursetransit'));
$PAGE->set_heading(get_string('pluginname', 'auth_coursetransit'));

echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('wizard_step', 'auth_coursetransit', $step));

// STEP 1.
if ($step == 1) {
    require_once(__DIR__ . '/form.php');

    $form = new auth_coursetransit_token_form(
        new moodle_url('/auth/coursetransit/wizard.php', ['step' => 1])
    );

    if ($form->is_cancelled()) {
        redirect(new moodle_url('/'));
    } else if ($data = $form->get_data()) {
        auth_coursetransit_set_technical_user((int)$data->userid);
        auth_coursetransit_auto_create_webservice();

        redirect(new moodle_url('/auth/coursetransit/wizard.php', ['step' => 2]));
    }

    echo $OUTPUT->render_from_template('auth_coursetransit/wizard/step1', [
        'title' => get_string('wizard_step_user', 'auth_coursetransit'),
        'form' => $form->render(),
    ]);
}

// STEP 2.
if ($step == 2) {
    require_once(__DIR__ . '/site_form.php');

    $form = new auth_coursetransit_site_form(
        new moodle_url('/auth/coursetransit/wizard.php', ['step' => 2])
    );

    if ($form->is_cancelled()) {
        redirect(new moodle_url('/auth/coursetransit/index.php'));
    } else if ($data = $form->get_data()) {
        $input = trim($data->domain);

        if (!preg_match('~^https?://~i', $input)) {
            $input = 'https://' . $input;
        }

        $host = parse_url($input, PHP_URL_HOST);

        if (!$host) {
            throw new moodle_exception('invalidurl', 'auth_coursetransit');
        }

        $token = auth_coursetransit_create_site(
            trim($data->name),
            strtolower($host)
        );

        $SESSION->coursetransit_token = $token;

        redirect(new moodle_url('/auth/coursetransit/wizard.php', [
            'step' => 3,
        ]));
    }

    echo $OUTPUT->render_from_template('auth_coursetransit/wizard/step2', [
        'title' => get_string('wizard_step_site', 'auth_coursetransit'),
        'form' => $form->render(),
    ]);
}

// STEP 3.
if ($step == 3) {
    // Mark setup completed.
    set_config('setup_complete', 1, 'auth_coursetransit');

    $token = $SESSION->coursetransit_token ?? '';

    unset($SESSION->coursetransit_token);

    if (empty($token)) {
        throw new moodle_exception('invalidtoken', 'auth_coursetransit');
    }
    $PAGE->requires->js_call_amd(
        'auth_coursetransit/token_display',
        'init'
    );
    echo $OUTPUT->render_from_template('auth_coursetransit/wizard/step3', [
        'title' => get_string('wizard_step_token', 'auth_coursetransit'),
        'description' => get_string('wizard_token_store', 'auth_coursetransit'),
        'once_label' => get_string('wizard_token_once', 'auth_coursetransit'),
        'important_label' => get_string('wizard_token_important', 'auth_coursetransit'),
        'important_text' => get_string('wizard_token_warning', 'auth_coursetransit'),
        'token' => $token,
        'button' => get_string('gotodashboard', 'auth_coursetransit'),
        'next' => (new moodle_url('/auth/coursetransit/index.php'))->out(false),
    ]);
}

echo $OUTPUT->footer();
