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
            $table->add_field('enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, 1);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);

            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('domain_unique', XMLDB_KEY_UNIQUE, ['domain']);

            $dbman->create_table($table);
        }

        // Site services table.
        $table = new xmldb_table('auth_coursetransit_services');

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

    // RENAME auth_coursetransit_site_services -> auth_coursetransit_services.
    // The original name is 32 characters, which exceeds the 28-character limit
    // enforced on Moodle versions prior to 4.3. Renaming (rather than
    // drop/recreate) preserves existing site/service assignments.
    if ($oldversion < 2026071301) {
        $oldtable = new xmldb_table('auth_coursetransit_site_services');
        $newtable = new xmldb_table('auth_coursetransit_services');

        if ($dbman->table_exists($oldtable) && !$dbman->table_exists($newtable)) {
            $dbman->rename_table($oldtable, 'auth_coursetransit_services');
        }

        upgrade_plugin_savepoint(true, 2026071301, 'auth', 'coursetransit');
    }

    // Bind each registered site to its own CourseTransit web-service token.
    if ($oldversion < 2026081701) {
        $table = new xmldb_table('auth_coursetransit_sites');

        $field = new xmldb_field('technicaluserid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0, 'domain');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('tokenid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'technicaluserid');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $key = new xmldb_key('token_unique', XMLDB_KEY_UNIQUE, ['tokenid']);
        if (!$dbman->find_key_name($table, $key)) {
            $dbman->add_key($table, $key);
        }

        // Existing installations historically used one technical user/token.
        // Preserve that token for the oldest registered site where possible and
        // issue unique tokens for additional sites. This keeps single-site
        // installations working without trusting payload['siteurl'].
        $userid = (int) get_config('auth_coursetransit', 'technicaluserid');
        $service = $DB->get_record('external_services', ['shortname' => 'auth_coursetransit']);

        if ($userid && $service) {
            require_once($CFG->dirroot . '/webservice/lib.php');
            require_once($CFG->libdir . '/externallib.php');

            $sites = $DB->get_records('auth_coursetransit_sites', null, 'timecreated ASC, id ASC');
            $tokens = $DB->get_records(
                'external_tokens',
                [
                    'externalserviceid' => $service->id,
                    'userid' => $userid,
                ],
                'id ASC',
                'id,token'
            );

            $tokenrecords = array_values($tokens);
            $tokenindex = 0;

            foreach ($sites as $site) {
                if (!empty($site->tokenid)) {
                    continue;
                }

                $tokenrecord = $tokenrecords[$tokenindex] ?? null;
                if (!$tokenrecord) {
                    $token = external_generate_token(
                        EXTERNAL_TOKEN_PERMANENT,
                        $service->id,
                        $userid,
                        context_system::instance()
                    );
                    $tokenrecord = $DB->get_record(
                        'external_tokens',
                        ['token' => $token],
                        'id,token',
                        MUST_EXIST
                    );
                    $tokenrecords[] = $tokenrecord;
                }

                $DB->set_field('auth_coursetransit_sites', 'technicaluserid', $userid, ['id' => $site->id]);
                $DB->set_field('auth_coursetransit_sites', 'tokenid', $tokenrecord->id, ['id' => $site->id]);
                $tokenindex++;
            }
        }

        upgrade_plugin_savepoint(true, 2026081701, 'auth', 'coursetransit');
    }

    // Enforce token-to-site URL consistency without changing the existing
    // WordPress request contract. The authenticated token remains the
    // authoritative identity; payload.siteurl can only prove that the
    // existing WordPress connection is configured for the same registered site.
    if ($oldversion < 2026081702) {
        // No schema change is required. This savepoint records the security
        // behavior change for upgrades from 0.2.1.
        upgrade_plugin_savepoint(true, 2026081702, 'auth', 'coursetransit');
    }

    // Add per-function payload validation for the generic CourseTransit proxy.
    // No database changes are required for this security hardening.
    if ($oldversion < 2026081703) {
        upgrade_plugin_savepoint(true, 2026081703, 'auth', 'coursetransit');
    }

    // Clean up legacy site-services tables left behind by interrupted or
    // older installations. This is a migration only: existing data is copied
    // into the current table and the obsolete table is then removed.
    if ($oldversion < 2026082400) {
        $oldtable = new xmldb_table('auth_coursetransit_site_services');
        $newtable = new xmldb_table('auth_coursetransit_services');

        if ($dbman->table_exists($oldtable)) {
            if (!$dbman->table_exists($newtable)) {
                $dbman->rename_table($oldtable, 'auth_coursetransit_services');
            } else {
                // Both tables exist. Merge only missing mappings so an upgrade
                // never loses existing site/service assignments.
                $legacyrecords = $DB->get_records('auth_coursetransit_site_services');
                foreach ($legacyrecords as $legacyrecord) {
                    if (
                        !$DB->record_exists(
                            'auth_coursetransit_services',
                            [
                                'siteid' => $legacyrecord->siteid,
                                'functionname' => $legacyrecord->functionname,
                            ]
                        )
                    ) {
                        $DB->insert_record(
                            'auth_coursetransit_services',
                            (object) [
                                'siteid' => $legacyrecord->siteid,
                                'functionname' => $legacyrecord->functionname,
                            ]
                        );
                    }
                }

                $dbman->drop_table($oldtable);
            }
        }

        upgrade_plugin_savepoint(true, 2026082400, 'auth', 'coursetransit');
    }

    // Create the native Moodle source-domain profile field for existing
    // installations. This is data setup only and does not alter user records.
    if ($oldversion < 2026083101) {
        require_once($CFG->dirroot . '/auth/coursetransit/lib.php');
        auth_coursetransit_ensure_source_domain_field();

        upgrade_plugin_savepoint(true, 2026083101, 'auth', 'coursetransit');
    }

    return true;
}
