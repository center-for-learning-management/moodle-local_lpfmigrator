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

function xmldb_local_lpfmigrator_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2020012800) {
        $table = new xmldb_table('local_lpfmigrator_instances');
        $field = new xmldb_field('path_web', XMLDB_TYPE_CHAR, '250', null, null, null, null, 'path_backup_pwd');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    if ($oldversion < 2020012900) {
        $table = new xmldb_table('local_lpfmigrator_instances');
        $field = new xmldb_field('comments', XMLDB_TYPE_TEXT, null, null, null, null, null, 'path_web');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    if ($oldversion < 2020013000) {
        $table = new xmldb_table('local_lpfmigrator_instances');
        $field = new xmldb_field('adminusers', XMLDB_TYPE_TEXT, null, null, null, null, null, 'comments');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    if ($oldversion < 2020020100) {
        $table = new xmldb_table('local_lpfmigrator_instances');
        $field = new xmldb_field('datasize', XMLDB_TYPE_INT, '10', null, null, null, null, 'adminusers');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    return true;
}
