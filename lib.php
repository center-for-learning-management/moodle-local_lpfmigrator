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
            $instanceprefix = 'instance_';
            $context_user = \context_user::instance($USER->id);
            $repo_filesystem = $DB->get_record('repository', array('type' => 'filesystem'));
            $memberships = $DB->get_records_sql("SELECT * FROM {local_eduvidual_orgid_userid} WHERE userid=? AND (role=? OR role=?)", array($USER->id, 'Manager', 'Teacher'));
            $my_confirmed_instances = array();
            foreach ($memberships AS $membership) {
                // Check if there is a backup folder for that org.
                $instances = $DB->get_records('local_lpfmigrator_instances', array('orgid' => $membership->orgid));
                foreach ($instances AS $instance) {
                    // For security reasons - we must have an instancename!
                    if (empty($instance->instancename)) continue;
                    $p_instreponame = $instanceprefix . $instance->instancename;
                    $p_backup = $backupfolder . DIRECTORY_SEPARATOR . $instance->instancename;
                    $p_repo = $CFG->dataroot . '/repository/' . $p_instreponame;
                    if (is_dir($p_backup)) {
                        if (!is_dir($p_repo)) {
                            symlink($p_backup, $p_repo);
                        }
                        // Now there should be this repository!
                        if (is_dir($p_repo)) {
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
                                $my_confirmed_instances[] = $chkinstance->id;
                                // Ok, we have that instance - insert configuration.
                                $chkconfiguration = $DB->get_record('repository_instance_config', array('instanceid' => $chkinstance->id, 'name' => 'fs_path'));
                                if (empty($chkconfiguration->id) || $chkconfiguration->value != $p_instreponame) {
                                    if (empty($chkconfiguration->id)) {
                                        $chkconfiguration = (object) array(
                                            'instanceid' => $chkinstance->id,
                                            'name' => 'fs_path',
                                            'value' => $p_instreponame,
                                        );
                                        $chkconfiguration->id = $DB->insert_record('repository_instance_config', $chkconfiguration);
                                    } else {
                                        $DB->set_field('repository_config', 'value', $p_instreponame, array('id' => $chkconfiguration->id));
                                    }
                                }
                            }
                        }
                    }
                }
            }
            // Now check if there are any instances we should not have access to.
            $allinstances = $DB->get_records('repository_instances', array('typeid' => $repo_filesystem->id, 'contextid' => $context_user->id));
            foreach ($allinstances AS $allinstance) {
                $chkconfig = $DB->get_record('repository_instance_config', array('instanceid' => $allinstance->id, 'name' => 'fs_path'));
                if (!empty($chkconfig->id) && substr($chkconfig->value, 0, strlen($instanceprefix)) == $instanceprefix) {
                    if (!in_array($allinstance->id, $my_confirmed_instances)) {
                        // Remove this instance.
                        $DB->delete_records('repository_instance_config', array('instanceid' => $allinstance->id));
                        $DB->delete_records('repository_instances', array('id' => $allinstance->id));
                    }
                }
            }
        }
    }

    return "";
}
