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
    const STAGE_NOTIFY_ADMINS = 1;
    const STAGE_BACKUPS = 2;
    const STAGE_MAINTENANCE = 3;
    const STAGE_REVIEWED = 4;
    const STAGE_REMOVALWEB = 5;
    const STAGE_REMOVALDATA = 6;
    const STAGE_COMPLETED = 7;

    private $dbname = '';
    private $host = '';
    private $id = 0;
    private $instancename = '';
    private $lpfgroup = '';
    private $orgid = 0;
    private $path_data = '';
    private $path_backup = '';
    private $path_backup_pwd = '';
    private $stage = 0;

    public static $hosts = array();
    public static $dbcap = 0;

    function __construct($instancename) {
        global $DB;
        $rec = $DB->get_record('local_lpfmigrator_instances', array('instancename' => $instancename));
        if (!empty($rec->id)) {
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
            $this->instancename = $instancename;
            $this->path_backup_pwd = substr(str_shuffle(strtolower(sha1(rand() . time() . "www.eduvidual.at"))), 0, 10);
        }
        $org = $DB->get_record('block_eduvidual_org', array('lpf' => $instancename));
        if (!empty($org->id)) {
            $this->lpfgroup = $org->lpfgroup;
            if (empty($this->orgid)) {
                $this->orgid = $org->orgid;
            }
        }

        if (empty($this->path_data)) {
            // Determine the moodle-datadir.
            $this->path_data = self::get_datadir($instancename);
        }
        if (empty($this->path_web)) {
            // We make an educated guess. Sometimes this does not work.
            $serverno = ($this->host == 'mdsql01.bmb.gv.at') ? 3 : 4;
            $this->path_web = 'https://www' . $serverno . '.lernplattform.schule.at/' . $this->instancename;
        }
        if (empty($this->id)) {
            $this->id = $DB->insert_record('local_lpfmigrator_instances', $this->as_object());
        } else {
            $DB->update_record('local_lpfmigrator_instances', $this->as_object());
        }
    }
    public function as_object() {
        $backupnr = 1;
        if (substr($this->path_backup, 0, 13) == '/data/moodle2') $backupnr = 2;
        if (substr($this->path_backup, 0, 13) == '/data/moodle3') $backupnr = 3;
        if (substr($this->path_backup, 0, 13) == '/data/moodle4') $backupnr = 4;
        if (substr($this->path_backup, 0, 13) == '/data/moodle5') $backupnr = 5;
        return (object) array(
            'id' => $this->id,
            'instancename' => $this->instancename,
            'host' => $this->host,
            'dbname' => $this->dbname,
            'stage' => $this->stage,
            'orgid' => $this->orgid,
            'lpfgroup' => $this->lpfgroup,
            'path_data' => $this->path_data,
            'path_web' => $this->path_web,
            'path_backup' => $this->path_backup,
            'path_backup_pwd' => $this->path_backup_pwd,
            'servernr' => ($this->host == 'mdsql01.bmb.gv.at') ? 3 : 4,
            'backupnr' => $backupnr,
        );
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
                        $roles = get_user_roles($catcontext, $USER->id);
                        foreach ($roles AS $role) {
                            if ($role->roleid == $managerrole) return true;
                        }
                    }
                }
            }
        }
    }
    /**
     * Gets or sets the host.
     */
    public function dbname($dbname = "") {
        global $DB;
        if (!empty($dbname)) {
            $this->dbname = $dbname;
            $DB->set_field('local_lpfmigrator_instances', 'dbname', $this->dbname, array('instancename' => $this->instancename));
        }
        return $this->dbname;
    }

    /**
     * Closes all connections to external databases.
     * Should be run in all scripts at the end.
     */
    public static function external_db_closeall() {
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
            $host = $hosts[$hostsid];
            $user = (count($users) > 1) ? $users[$hostsid] : $users[0];
            $pass = (count($passwords) > 1) ? $passwords[$hostsid] : $passwords[0];
            self::$hosts[$instance->host] = new \mysqli($host, $user, $pass);
            mysqli_select_db(self::$hosts[$instance->host], $instance->dbname);
        }
        return self::$hosts[$instance->host];
    }

    /**
     * Determine the amount of courses in our backup dir.
     */
    public function get_amount_courses_backup() {
        $cnt = 0;
        $d = opendir($this->path_backup);
        while(($f = readdir($d))) {
            if (substr($f, 0, 1) == '.') continue;
            $cnt++;
        }
        return $cnt;
    }
    /**
     * Determine the amount of courses on the remote instance.
     */
    public function get_amount_courses_remote() {
        $con = instance::external_db_open($this->instancename);
        $sql = "SELECT COUNT(id) FROM " . $this->instancename . "___course";
        $btr = mysqli_query($con, $sql);
        $row = mysqli_fetch_row($btr);
        return !empty($row[0]) ? $row[0] : 0;
    }

    /**
     * Searches for the moodle-datadir.
     */
    private function get_datadir($instancename) {
        $potentialfolders = explode(',', get_config('local_lpfmigrator', 'datafolders'));
        foreach ($potentialfolders AS $potentialfolder) {
            $d = opendir($potentialfolder);
            while (false !== ($f = readdir($d))) {
                if (empty(str_replace('.', '', $f))) continue;
                if ($f == $instancename) return $potentialfolder . DIRECTORY_SEPARATOR . $f;
            }
        }
        return '';
    }
    /**
     * Gets all potential stages and marks the current on with field "selected".
     */
    public function get_stages() {
        return array(
            array('is0' => true, 'value' => self::STAGE_NOT_STAGED, 'label' => get_string('stage_' . self::STAGE_NOT_STAGED, 'local_lpfmigrator'), 'selected' => ($this->stage == self::STAGE_NOT_STAGED), 'completed' => ($this->stage > self::STAGE_NOT_STAGED)),
            array('is1' => true, 'value' => self::STAGE_NOTIFY_ADMINS, 'label' => get_string('stage_' . self::STAGE_NOTIFY_ADMINS, 'local_lpfmigrator'), 'selected' => ($this->stage == self::STAGE_NOTIFY_ADMINS), 'completed' => ($this->stage > self::STAGE_NOTIFY_ADMINS)),
            array('is2' => true, 'value' => self::STAGE_BACKUPS, 'label' => get_string('stage_' . self::STAGE_BACKUPS, 'local_lpfmigrator'), 'selected' => ($this->stage == self::STAGE_BACKUPS), 'completed' => ($this->stage > self::STAGE_BACKUPS)),
            array('is3' => true, 'value' => self::STAGE_MAINTENANCE, 'label' => get_string('stage_' . self::STAGE_MAINTENANCE, 'local_lpfmigrator'), 'selected' => ($this->stage == self::STAGE_MAINTENANCE), 'completed' => ($this->stage > self::STAGE_MAINTENANCE)),
            array('is4' => true, 'value' => self::STAGE_REVIEWED, 'label' => get_string('stage_' . self::STAGE_REVIEWED, 'local_lpfmigrator'), 'selected' => ($this->stage == self::STAGE_REVIEWED), 'completed' => ($this->stage > self::STAGE_REVIEWED)),
            array('is5' => true, 'value' => self::STAGE_REMOVALWEB, 'label' => get_string('stage_' . self::STAGE_REMOVALWEB, 'local_lpfmigrator'), 'selected' => ($this->stage == self::STAGE_REMOVALWEB), 'completed' => ($this->stage > self::STAGE_REMOVALWEB)),
            array('is6' => true, 'value' => self::STAGE_REMOVALDATA, 'label' => get_string('stage_' . self::STAGE_REMOVALDATA, 'local_lpfmigrator'), 'selected' => ($this->stage == self::STAGE_REMOVALDATA), 'completed' => ($this->stage > self::STAGE_REMOVALDATA)),
            array('is7' => true, 'value' => self::STAGE_COMPLETED, 'label' => get_string('stage_' . self::STAGE_COMPLETED, 'local_lpfmigrator'), 'completed' => ($this->stage == self::STAGE_COMPLETED)),
        );
    }
    /**
     * Determines if backups are on.
     */
    public function has_backups_enabled() {
        $con = instance::external_db_open($this->instancename);
        $sql = "SELECT id,name,value FROM " . $this->instancename . "___config_plugins WHERE name='backup_auto_destination'";
        $btr = mysqli_query($con, $sql);
        $row = mysqli_fetch_row($btr);
        $destination = $row[2];

        $sql = "SELECT id,name,value FROM " . $this->instancename . "___config_plugins WHERE name='backup_auto_storage'";
        $btr = mysqli_query($con, $sql);
        $row = mysqli_fetch_row($btr);
        $storage = $row[2];

        $sql = "SELECT id,name,value FROM " . $this->instancename . "___config_plugins WHERE name='backup_auto_active'";
        $btr = mysqli_query($con, $sql);
        $row = mysqli_fetch_row($btr);
        $active = $row[2];

        return ($destination == $this->path_backup() && $storage == 1 && $active == 1);
    }
    /**
     * Determines if maintenance mode is on.
     */
    public function has_maintenance_enabled() {
        if (file_exists($this->path_data . DIRECTORY_SEPARATOR . 'climaintenance.html')) return true;
        /*
        $con = instance::external_db_open($this->instancename);
        $sql = "SELECT id,name,value FROM " . $this->instancename . "___config WHERE name='maintenance_enabled'";
        $btr = mysqli_query($con, $sql);
        $row = mysqli_fetch_row($btr);
        return $row[2];
        */
    }
    /**
     * Gets or sets the host.
     */
    public function host($host = "-") {
        global $DB;
        if ($host != "-") {
            $this->host = $host;
            $DB->set_field('local_lpfmigrator_instances', 'host', $this->host, array('instancename' => $this->instancename));
        }
        return $this->host;
    }
    /**
     * Returns the id.
     */
    public function id() {
        return $this->id;
    }
    /**
     * Gets or sets the lpfgroup.
     */
    public function lpfgroup($lpfgroup="-") {
        global $DB;
        if ($lpfgroup != "-" && !empty($this->orgid)) {
            $this->lpfgroup = $lpfgroup;
            $DB->set_field('block_eduvidual_org', 'lpfgroup', $this->lpfgroup, array('orgid' => $this->orgid));
        }
        return $this->lpfgroup;
    }
    /**
     * Sends a notification to the admins of the moodle-instance.
     */
    public function notify_admins() {
        global $CFG, $OUTPUT;
        $con = instance::external_db_open($this->instancename);
        $sql = "SELECT id,name,value FROM " . $this->instancename . "___config WHERE name='siteadmins'";
        $btr = mysqli_query($con, $sql);
        $row = mysqli_fetch_row($btr);

        $sql = "SELECT id,firstname,lastname,email
                    FROM $this->instancename___user
                    WHERE id IN (" . $row[2] . ")
                        AND deleted=0 AND suspended=0
                        AND email NOT LIKE '%noreply%'";
        $btr = mysqli_query($con, $sql);
        $tousers = array();
        while($row = mysqli_fetch_row($btr)) {
            $tousers[] = array('firstname' => $row[1], 'lastname' => $row[2], 'email' => $row[3]);
        }
        $tousers[] = array('firstname' => 'Julia', 'lastname' => 'Laßnig', 'email' => 'julia.lassnig@lernmangement.at');
        $tousers[] = array('firstname' => 'Robert', 'lastname' => 'Schrenk', 'email' => 'robert.schrenk@lernmanagement.at');
        foreach($tousers AS $u) {
            $touser = new \stdClass();
            $touser->email = $u['email'];
            $touser->firstname = $u['firstname'];
            $touser->lastname = $u['lastname'];
            $touser->maildisplay = true;
            $touser->mailformat = 1; // 0 (zero) text-only emails, 1 (one) for HTML/Text emails.
            $touser->id = -99; // invalid userid, as the user has no userid in our moodle.
            $touser->firstnamephonetic = "";
            $touser->lastnamephonetic = "";
            $touser->middlename = "";
            $touser->alternatename = "";

            $fromuser = \core_user::get_support_user();

            $instanceo = $this->as_object();

            $messagecontent = get_string('notify_admins:text', 'local_lpfmigrator', array('firstname' => $touser->firstname, 'instancename' => $this->instancename, 'lastname' => $touser->lastname));
            $messagehtml = $OUTPUT->render_from_template('local_lpfmigrator/notify_admins_mail', array('content' => $messagecontent, 'users' => $tousers));
            $messagetext = html_to_text($messagehtml);
            $subject = get_string('notify_admins:subject' , 'local_lpfmigrator', array('instancename' => $this->instancename));

            email_to_user($touser, $fromuser, $subject, $messagetext, $messagehtml, "", true);
        }
    }

    /**
     * Gets or sets the orgid.
     */
    public function orgid($orgid = 0) {
        global $DB;
        if (!empty($orgid)) {
            $this->orgid = $orgid;
            $DB->set_field('local_lpfmigrator_instances', 'orgid', $this->orgid, array('instancename' => $this->instancename));
        }
        return $this->orgid;
    }
    /**
     * Gets or sets the path_backup.
     */
    public function path_backup($path_backup = "-") {
        global $DB;
        if ($path_backup != "-") {
            $this->path_backup = $path_backup;
            $DB->set_field('local_lpfmigrator_instances', 'path_backup', $this->path_backup, array('instancename' => $this->instancename));
        }
        return $this->path_backup;
    }
    /**
     * Gets or sets the path_data.
     */
    public function path_data($path_data = "-") {
        global $DB;
        if ($path_data != "-") {
            $this->path_data = $path_data;
            $DB->set_field('local_lpfmigrator_instances', 'path_data', $this->path_data, array('instancename' => $this->instancename));
        }
        return $this->path_data;
    }
    /**
     * Gets or sets the path_web.
     */
    public function path_web($path_web = "-") {
        global $DB;
        if ($path_web != "-") {
            $this->path_web = $path_web;
            $DB->set_field('local_lpfmigrator_instances', 'path_web', $this->path_web, array('instancename' => $this->instancename));
        }
        return $this->path_web;
    }
    /**
     * Remove the database.
     */
    public function remove_database() {
        $con = instance::external_db_open($this->instancename);
        $sql = "DROP DATABASE `em_" . $this->instancename . "`";
    }
    /**
     * Remove the database.
     */
    public function remove_datadir() {
        if (!empty($this->path_data)) {
            $this->recursiveRemove($this->path_data);
        }
    }
    /**
     * Recursively remove everything.
     */
    private function recursiveRemove($dir) {
        $structure = glob(rtrim($dir, "/").'/*');
        if (is_array($structure)) {
            foreach($structure as $file) {
                if (is_dir($file)) $this->recursiveRemove($file);
                elseif (is_file($file)) unlink($file);
            }
        }
        rmdir($dir);
    }
    /**
     * Enables or disables backups to specific path.
     * @param to 1 to enable, 0 to disable.
     */
    public function set_backup_config($to) {
        global $DB;
        $path = explode('/', $this->path_data);
        $this->path_backup = $path[0] . '/' . $path[1] . '/' . $path[2] . '/backup/' . $this->instancename;
        mkdir($path[0] . '/' . $path[1] . '/' . $path[2] . '/backup');
        mkdir($path[0] . '/' . $path[1] . '/' . $path[2] . '/backup/' . $this->instancename);
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
    public function set_maintenance_mode($to) {
        // Convert from boolean to int.
        $to = ($to) ? 1 : 0;
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
    public function stage($stage = -1) {
        global $DB;
        if ($stage > -1) {
            $this->stage = $stage;
            $DB->set_field('local_lpfmigrator_instances', 'stage', $stage, array('instancename' => $this->instancename));
        }
        return $this->stage;
    }
}
