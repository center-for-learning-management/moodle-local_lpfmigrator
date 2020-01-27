<?php
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
 * @package    local_lpfmigrator
 * @copyright  2020 Zentrum für Lernmanagement (www.lernmanagement.at)
 * @author    Robert Schrenk
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    $settings = new admin_settingpage( 'local_lpfmigrator', get_string('pluginname', 'local_lpfmigrator'));
	$ADMIN->add('localplugins', $settings);
    $settings->add(new admin_setting_configtext('local_lpfmigrator/sqlservers_hosts', get_string('sqlservers:hosts', 'local_lpfmigrator'), '', '', PARAM_TEXT));
    $settings->add(new admin_setting_configtext('local_lpfmigrator/sqlservers_users', get_string('sqlservers:users', 'local_lpfmigrator'), '', '', PARAM_TEXT));
    $settings->add(new admin_setting_configtext('local_lpfmigrator/sqlservers_passwords', get_string('sqlservers:passwords', 'local_lpfmigrator'), '', '', PARAM_TEXT));
    $settings->add(new admin_setting_configtext('local_lpfmigrator/datafolders', get_string('datafolders', 'local_lpfmigrator'), '', '', PARAM_TEXT));
}
