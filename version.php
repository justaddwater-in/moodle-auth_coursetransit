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
 * Plugin version and other meta-data are defined here.
 *
 * @package     auth_coursetransit
 * @copyright  2025 Justaddwater <contact@justaddwater.in>
 * @author     Himanshu Saini
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'auth_coursetransit';
$plugin->release = '2.0.0';
$plugin->version = 2026100101;
$plugin->requires = 2022112800; // Moodle 4.1.0.
$plugin->maturity = MATURITY_STABLE;

/*
 * VERSION NOTE
 * ------------
 * 2026091600 is deliberately higher than 2026090801, the last all-in-one
 * "Pro" build that also shipped as component auth_coursetransit. Sites still
 * running that build can therefore upgrade to this free plugin; Moodle would
 * refuse a lower number as a downgrade.
 *
 * Pro is now a separate component (local_coursetransitpro) with an independent
 * version line, so this number no longer has to outrun anything.
 */
