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

function local_lpfmigrator_before_standard_html_head() {
    global $CFG, $DB, $USER;

    if (strpos($_SERVER["SCRIPT_FILENAME"], '/backup/restorefile.php') > 0) {
        $backupfolder = get_config('local_lpfmigrator', 'backupfolder');
        if (!empty($backupfolder)) {
            $context_user = \context_user::instance($USER->id);
            $repo_filesystem = $DB->get_record('repository', array('type' => 'filesystem'));
            $memberships = $DB->get_records('block_eduvidual_orgid_userid', array('userid' => $USER->id, 'role' => 'Manager'));
            foreach ($memberships AS $membership) {
                // Check if there is a backup folder for that org.
                $instances = $DB->get_record('local_lpfmigrator_instances', array('orgid' => $membership->orgid));
                foreach ($instances AS $instance) {
                    $p_instreponame = 'instance_' . $instance->instancename;
                    $p_backup = $backupfolder . DIRECTORY_SEPARATOR . $instance->instancename;
                    $p_repo = $CFG->dataroot . '/repository/' . $p_instreponame;
                    if (dir_exists($p_backup)) {
                        if (!dir_exists($p_repo)) {
                            symlink($p_backup, $p_repo);
                        }
                        // Now there should be this repository!
                        if (dir_exists($p_repo)) {
                            // Add this repo to the current users repositories.
                            $chkinstance = $DB->get_record('repository_instances', array('typeid' => $repo_filesystem->id, 'contextid' => $context_user->id, 'name' => $instance->instancename));
                            if (empty($chkinstance->id)) {
                                $chkinstance = (object) array(
                                    'name' => $instance->instancename,
                                    'typeid' => $repo_filesystem->id,
                                    'userid' => 0,
                                    'contextid' => $context_user->id,
                                    'username' => '',
                                    'password' => '',
                                    'timecreated' => time(),
                                    'timemodified' => time(),
                                    'readonly' => 1,
                                );
                                $chkinstance->id = $DB->insert_record('repository_instances', $chkinstance);
                            }
                            if (!empty($chkinstance->id)) {
                                // Ok, we have that instance - insert configuration.
                                $chkconfiguration = $DB->get_record('repository_config', array('instanceid' => $chkinstance->id, 'name' => 'fs_path'));
                                if (empty($chkconfiguration->id) || $chkconfiguration->value != $p_instreponame) {
                                    if (empty($chkconfiguration->id)) {
                                        $chkconfiguration = (object) array(
                                            'instanceid' => $chkinstance->id,
                                            'name' => 'fs_path',
                                            'value' => $p_instreponame,
                                        );
                                        $chkconfiguration->id = $DB->insert_record('repository_config', $chkconfiguration);
                                    } else {
                                        $DB->set_field('repository_config', 'value', $p_instreponame, array('id' => $chkconfiguration->id));
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    return "";
}
