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

namespace local_lpfmigrator;

defined('MOODLE_INTERNAL') || die;

class instance {
    /**
     * Stages for indicating progress
     */
    const STAGE_NOT_STAGED = 0;
    const STAGE_MAINTENANCE = 1;
    const STAGE_BACKUPS = 2;
    const STAGE_REVIEW = 3;
    const STAGE_CONFORMED = 4;
    const STAGE_COMPLETED = 5;

    private $id = 0;
    private $host = '';
    private $dbname = '';
    private $instancename = '';
    private $stage = 0;
    private $orgid = 0;
    private $path_data = '';
    private $path_backup = '';
    private $path_backup_pwd = '';

    public static $hosts = array();
    public static $dbcap = 0;

    function __construct($instancename) {
        global $DB;
        $rec = $DB->get_record('local_lpfmigrator_instances', array('instance' => $instancename));
        if (!empty($instance->id)) {
            $this->id = $rec->id;
            $this->host = $rec->host;
            $this->dbname = $rec->dbname;
            $this->instancename = $instancename;
            $this->stage = $rec->stage;
            $this->orgid = $rec->orgid;
            $this->path_data = $rec->path_data;
            $this->path_backup = $rec->path_backup;
            $this->path_backup_pwd = $rec->path_backup_pwd;
        } else {
            $this->path_backup_pwd = substr(str_shuffle(strtolower(sha1(rand() . time() . "www.eduvidual.at"))), 0, 10);
        }
        if (empty($instance->orgid)) {
            $org = $DB->get_record('block_eduvidual_org', array('lpf' => $instancename));
            if (!empty($org->id)) {
                $instance->orgid = $org->orgid;
            }
        }
        if (empty($instance->path_data)) {
            // Determine the moodle-datadir.
            $instance->path_data = instance::get_datadir($instancename);
        }
        if (empty($this->id)) {
            $this->id = $DB->insert_record('local_lpfmigrator_instances', $instance);
        } else {
            $DB->update_record('local_lpfmigrator_instances', $instance);
        }
    }

    /**
     * Determines if the current user can manage this instance.
     */
    public function can_manage_instance() {
        global $DB, $USER;
        if (is_siteadmin()) return true;
        if (!empty($this->orgid)) {
            $org = $DB->get_record('block_eduvidual_org', array('orgid' => $this->orgid));
            if (!empty($org->categoryid)) {
                $catcontext = \context_coursecat::instance($org->categoryid);
                if (!empty($catcontext->id)) {
                    $managerrole = get_config('block_eduvidual', 'defaultorgrolemanager');
                    if (!empty($managerrole)) {
                        $roles = \get_user_roles($catcontext, $USER->id);
                        foreach ($roles AS $role) {
                            if ($role->roleid == $managerrole) return true;
                        }
                    }
                }
            }
        }
    }

    /**
     * Determine the amount of courses on the remote instance.
     */
    private function get_amount_courses() {
        $con = instance::external_db_open($this->instancename);
        $sql = "SELECT COUNT(id) FROM " . $this->instancename . "___course";
        $btr = mysqli_query($con, $sql);
        $row = mysqli_fetch_row($btr);
        return $row[0];
    }

    /**
     * Searches for the moodle-datadir.
     */
    private function get_datadir() {
        $potentialfolders = explode(',', get_config('local_lpfmigrator', 'datafolders'));
        foreach ($potentialfolders AS $potentialfolder) {
            $d = opendir($potentialfolder);
            while (false !== ($f = readdir($d))) {
                if (empty(str_replace('.', '', $f))) continue;
                if ($f == $school) return $potentialfolder;
            }
        }
        return '';
    }

    /**
     * Closes all connections to external databases.
     * Should be run in all scripts at the end.
     */
    private static function external_db_closeall() {
        foreach (self::$hosts AS $i => $host) {
            mysqli_close($host);
        }
    }
    /**
     * Establishes a connection to an external database.
     */
    private static function external_db_open($instancename) {
        global $DB;
        $instance = $DB->get_record('local_lpfmigrator_instances', array('instancename' => $instancename));
        if (empty($instance->id)) return;
        if (!isset(self::$hosts[$instance->host])) {
            $hosts = explode(',', get_config('local_lpfmigrator', 'sqlservers_hosts'));
            $users = explode(',', get_config('local_lpfmigrator', 'sqlservers_users'));
            $passwords = explode(',', get_config('local_lpfmigrator', 'sqlservers_passwords'));
            $hostsid = array_search($instance->host, $hosts);
            $user = (count($users) > 1) ? $users[$hostsid] : $users[0];
            $pass = (count($passwords) > 1) ? $passwords[$hostsid] : $passwords[0];
            self::$hosts[$instance->host] = new mysqli($host, $user, $pass);
        }
        return self::$hosts[$instance->host];
    }
    /**
     * Enables or disables backups to specific path.
     * @param to 1 to enable, 0 to disable.
     */
    private function set_backup_config($to) {
        global $DB;
        $path = explode('/', $this->path_data);
        $this->path_backup = $path[0] . '/' . $path[1] . '/backup/' . $this->instancename;
        mkdir($path[0] . '/' . $path[1] . '/backup');
        mkdir($path[0] . '/' . $path[1] . '/backup/' . $this->instancename);
        $DB->set_field('local_lpfmigrator_instances', 'path_backup', $this->path_backup, array('instancename' => $this->instancename));
        $con = instance::external_db_open($this->instancename);
        if (!empty($to)) {
            $fields = array(
                'backup_auto_active' => 1,
                'backup_auto_weekdays' => 1111111,
                'backup_auto_hour' => date("H"),
                'backup_auto_minute' => date("i")+1,
                'backup_auto_storage' => 1,
                'backup_auto_destination' => $this->path_backup,
                'backup_auto_delete_days' => 0,
                'backup_auto_min_kept' => 1,
                'backup_shortname' => 1,
                'backup_auto_skip_hidden' => 0,
                'backup_auto_skip_modif_days' => 0,
                'backup_auto_skip_modif_prev' => 0,
                // Data included
                'backup_auto_users' => 1,
                'backup_auto_role_assignments' => 1,
                'backup_auto_activities' => 1,
                'backup_auto_blocks' => 1,
                'backup_auto_filters' => 1,
                'backup_auto_comments' => 1,
                'backup_auto_badges' => 1,
                'backup_auto_calendarevents' => 1,
                'backup_auto_userscompletion' => 1,
                'backup_auto_logs' => 0,
                'backup_auto_histories' => 0,
                'backup_auto_questionbank' => 1,
                'backup_auto_groups' => 1,
                'backup_auto_competencies' => 1,
            );
            $pwdfile = $this->path_backup . DIRECTORY_SEPARATOR . '.htpasswd';
            $accfile = $this->path_backup . DIRECTORY_SEPARATOR . '.htaccess';
            $auth = implode("\n", array(
                "AuthType\tBasic",
                "AuthName\t\"Auth for " . $this->instancename . "\"",
                "AuthUserFile $pwdfile",
                "Require valid-user",
                "Options +Indexes",
            ));
            file_put_contents($pwdfile, $this->instancename . ':' . crypt($this->path_backup_pwd));
            file_put_contents($accfile, $auth);

            foreach ($fields AS $field => $value) {
                $sql =  "UPDATE " . $this->instancename . "___config_plugins SET value='$value' WHERE plugin='backup' AND name='$field'";
                mysqli_query($con, $sql);
                $sqls[] = $sql;
            }
            $sql = "UPDATE " . $this->instancename . "___task_scheduled SET lastruntime=0,nextruntime=" . time() . ",minute='" . date("i") . "',hour='" . date("H") . "',day='" . date("d") . "',month='" . date("m") . "',dayofweek='*',faildelay=0,customised=1,disabled=0 WHERE classname LIKE '%automated_backup_task'";
            mysqli_query($con, $sql);
            $sqls[] = $sql;
        } else {
            $sql = "UPDATE " . $this->instancename . "___config_plugins SET VALUE='0' WHERE plugin='backup' AND name='backup_auto_active'";
            mysqli_query($con, $sql);
            $sqls[] = $sql;
        }
        return $sqls;
    }

    /**
     * Enable or disable maintenance mode of a certain instance.
     * @param to 1 means enable, 0 means disable.
     */
    private function set_maintenance_mode($to) {
        global $OUTPUT;
        if (!empty($to)) {
            file_put_contents(
                $this->path_data . DIRECTORY_SEPARATOR . 'climaintenance.html',
                $OUTPUT->render_from_template('local_lpfmigrator/climaintenance', array())
            );
        } else {
            unlink($this->path_data . DIRECTORY_SEPARATOR . 'climaintenance.html');
        }

        $con = instance::external_db_open($this->instancename);
        $sql = "UPDATE " . $this->instancename . "___config SET value='$to' WHERE name='maintenance_enabled'";
        mysqli_query($con, $sql);
        return true;
    }
    /**
     * Set stage.
     * @param stage to set.
     */
    private function set_stage($stage) {
        global $DB;
        $this->stage = $stage;
        $DB->set_field('local_lpfmigrator_instances', 'stage', $stage, array('instancename' => $this->instancename));
    }
}
