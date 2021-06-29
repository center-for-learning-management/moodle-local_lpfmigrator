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

/**
 * The purpose of this file is to read all available databases.
 */

namespace local_lpfmigrator;

require('../../config.php');
require_once(__DIR__ . '/locallib.php');

require_login();
$PAGE->set_url(new \moodle_url('/local/lpfmigrator/readdb.php', array()));
$PAGE->set_context(\context_system::instance());
$PAGE->set_heading(get_string('pluginname', 'local_lpfmigrator'));
$PAGE->set_title(get_string('pluginname', 'local_lpfmigrator'));
$PAGE->requires->css('/local/lpfmigrator/main.css');

echo $OUTPUT->header();
if (!is_siteadmin()) {
    echo $OUTPUT->render_from_template('local_lpfmigrator/alert', array(
        'type' => 'danger',
        'content' => get_string('access_denied', 'local_lpfmigrator'),
        'url' => new \moodle_url('/my', array()),
    ));
    echo $OUTPUT->footer();
    die();
}


$hosts = explode(',', get_config('local_lpfmigrator', 'sqlservers_hosts'));
$users = explode(',', get_config('local_lpfmigrator', 'sqlservers_users'));
$passwords = explode(',', get_config('local_lpfmigrator', 'sqlservers_passwords'));

$read = 0;

foreach ($hosts AS $i => $host) {
    echo "<p class=\"alert alert-warning\">Reading host $host</p>";
    $databases = array();
    $user = (count($users) > 1) ? $users[$i] : $users[0];
    $pass = (count($passwords) > 1) ? $passwords[$i] : $passwords[0];
    $db = new \mysqli($host, $user, $pass);
    $result = mysqli_query($db, 'SHOW DATABASES');
    $exclude = array('information_schema', 'mysql', 'performance_schema');
    while ($row = mysqli_fetch_row($result)) {
        if (!in_array($row[0], $exclude) && substr($row[0], 0, 3) == "em_") {
            $databases[] = $row[0];
        }
    }
    foreach ($databases AS $z => $database) {
        //mysqli_select_db($db, $database);
        $instancename = substr($database, 3);
        $instance = new instance($instancename);
        $_id = $instance->id();
        echo "<p class=\"alert alert-info\">=> Reading database $database as instancename $instancename and id $_id</p>";
        if (!empty($instance->id())) {
            // Enrich data with host-information.
            $instance->host($host);
            $instance->dbname($database);
            $instance->adminusers();
            $read++;
        }
    }
    mysqli_close($db);
}
echo $OUTPUT->render_from_template('local_lpfmigrator/alert', array(
    'content' => get_string('read_databases:success', 'local_lpfmigrator', array('read' => $read)),
    'type' => 'success',
    'url' => $CFG->wwwroot . '/local/lpfmigrator/list.php',
));

echo $OUTPUT->footer();
