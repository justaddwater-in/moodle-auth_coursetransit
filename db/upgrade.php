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
 * Upgrade script for auth_coursetransit.
 *
 * @package    auth_coursetransit
 * @copyright  2025 Justaddwater <contact@justaddwater.in>
 * @author     Himanshu Saini
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Executes upgrade steps for the auth_coursetransit plugin.
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_auth_coursetransit_upgrade($oldversion) {
    global $DB, $CFG;

    require_once($CFG->libdir . '/ddllib.php');

    $dbman = $DB->get_manager();

    // CREATE TABLES.
    if ($oldversion < 2026032300) {
        // Sites table.
        $table = new xmldb_table('auth_coursetransit_sites');

        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $table->add_field('name', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL);
            $table->add_field('domain', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
            $table->add_field('token', XMLDB_TYPE_CHAR, '128', null, XMLDB_NOTNULL);
            $table->add_field('enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, 1);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);

            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('domain_unique', XMLDB_KEY_UNIQUE, ['domain']);
            $table->add_index('token_idx', XMLDB_INDEX_UNIQUE, ['token']);

            $dbman->create_table($table);
        }

        // Site services table.
        $table = new xmldb_table('auth_coursetransit_site_services');

        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $table->add_field('siteid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $table->add_field('functionname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);

            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('site_service_unique', XMLDB_KEY_UNIQUE, ['siteid', 'functionname']);
            $table->add_index('siteid_idx', XMLDB_INDEX_NOTUNIQUE, ['siteid']);

            $dbman->create_table($table);
        }

        // API logs table.

        $table = new xmldb_table('auth_coursetransit_api_logs');

        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $table->add_field('siteid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $table->add_field('functionname', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
            $table->add_field('status', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL);
            $table->add_field('message', XMLDB_TYPE_TEXT, null, null, null);
            $table->add_field('origin', XMLDB_TYPE_CHAR, '255', null, null);
            $table->add_field('ipaddress', XMLDB_TYPE_CHAR, '45', null, null);
            $table->add_field('executiontime', XMLDB_TYPE_NUMBER, '10,5', null, null);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);

            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_index('siteid_idx', XMLDB_INDEX_NOTUNIQUE, ['siteid']);
            $table->add_index('function_idx', XMLDB_INDEX_NOTUNIQUE, ['functionname']);
            $table->add_index('status_idx', XMLDB_INDEX_NOTUNIQUE, ['status']);

            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026032300, 'auth', 'coursetransit');
    }

    // ENABLE WEBSERVICES + CREATE SERVICE.
    if ($oldversion < 2026032400) {
        // Enable webservices.
        set_config('enablewebservices', 1);

        // Enable REST.
        $protocols = explode(',', (string)get_config('core', 'webserviceprotocols'));
        if (!in_array('rest', $protocols)) {
            $protocols[] = 'rest';
            set_config('webserviceprotocols', implode(',', $protocols));
        }

        // Create service.
        if (!$DB->record_exists('external_services', ['shortname' => 'auth_coursetransit'])) {
            $DB->insert_record('external_services', (object)[
                'name'            => 'CourseTransit LMS Service',
                'shortname'       => 'auth_coursetransit',
                'enabled'         => 1,
                'restrictedusers' => 1,
                'timecreated'     => time(),
            ]);
        }

        upgrade_plugin_savepoint(true, 2026032400, 'auth', 'coursetransit');
    }

    return true;
}
