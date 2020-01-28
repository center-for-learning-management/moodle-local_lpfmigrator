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
$PAGE->set_url(new \moodle_url('/local/lpfmigrator/list.php', array()));
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

$sql = "SELECT lli.id,lli.instancename,lli.stage,lli.orgid,beo.lpfgroup,beo.categoryid
            FROM {local_lpfmigrator_instances} lli
            LEFT JOIN {block_eduvidual_org} beo ON lli.orgid=beo.orgid
            ORDER BY lpfgroup ASC, instancename ASC";

$instances = array_values($DB->get_records_sql($sql, array()));
foreach($instances AS &$instance) {
    $instance->stagelabel = get_string('stage_' . $instance->stage, 'local_lpfmigrator');
    /*
    $org = $DB->get_record('block_eduvidual_org', array('lpf' => $instancename));
    $instance->lpfgroup = $org->lpfgroup;
    */
}
echo $OUTPUT->render_from_template('local_lpfmigrator/list', array('instances' => $instances));

echo $OUTPUT->footer();

instance::external_db_closeall();
