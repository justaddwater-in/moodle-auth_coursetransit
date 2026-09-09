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
 * Uninstall script for CourseTransit.
 *
 * Removes only data and objects owned by the CourseTransit authentication
 * plugin. Normal upgrades do not call this script, so existing data is
 * preserved during version upgrades.
 *
 * @package    auth_coursetransit
 * @copyright 2025 Justaddwater <contact@justaddwater.in>
 * @author     Himanshu Saini
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Uninstall CourseTransit and remove all plugin-owned persistent data.
 *
 * @return bool
 */
function xmldb_auth_coursetransit_uninstall(): bool {
    global $DB;

    // Remove CourseTransit external-service records and their dependent
    // functions, authorised users and tokens. These records are owned by this
    // plugin because they use the CourseTransit service shortname.
    $services = $DB->get_records(
        'external_services',
        ['shortname' => 'auth_coursetransit'],
        '',
        'id'
    );

    foreach ($services as $service) {
        $DB->delete_records(
            'external_services_functions',
            ['externalserviceid' => $service->id]
        );
        $DB->delete_records(
            'external_services_users',
            ['externalserviceid' => $service->id]
        );
        $DB->delete_records(
            'external_tokens',
            ['externalserviceid' => $service->id]
        );
        $DB->delete_records(
            'external_services',
            ['id' => $service->id]
        );
    }

    // Remove the CourseTransit source-domain user profile field and its data.
    // Do not remove unrelated profile fields or categories.
    $field = $DB->get_record(
        'user_info_field',
        ['shortname' => 'coursetransit_sourcedomain'],
        'id,categoryid'
    );

    if ($field) {
        $DB->delete_records(
            'user_info_data',
            ['fieldid' => $field->id]
        );
        $DB->delete_records(
            'user_info_field',
            ['id' => $field->id]
        );

        // Remove the CourseTransit category only when it is empty. This avoids
        // deleting profile fields that may belong to another plugin.
        if (
            !$DB->record_exists(
                'user_info_field',
                ['categoryid' => $field->categoryid]
            )
        ) {
            $DB->delete_records(
                'user_info_category',
                ['id' => $field->categoryid]
            );
        }
    }

    // Drop current and known legacy CourseTransit tables. Moodle's XMLDB
    // manager is used instead of raw DROP TABLE SQL for database portability.
    $dbman = $DB->get_manager();
    $tables = [
        'auth_coursetransit_api_logs',
        'auth_coursetransit_services',
        'auth_coursetransit_site_services',
        'auth_coursetransit_sites',
    ];

    foreach ($tables as $tablename) {
        $table = new xmldb_table($tablename);
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }
    }

    // Remove all plugin configuration values.
    unset_all_config_for_plugin('auth_coursetransit');

    // Purge the plugin's session cache so tokens/settings from an old
    // installation cannot survive a reinstall.
    cache_helper::purge_by_definition('auth_coursetransit', 'session');

    return true;
}
