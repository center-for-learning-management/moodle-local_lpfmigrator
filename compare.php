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
 * The purpose of this file is to show a single instance.
 * site admins can manage, eduvidual-managers can view.
 */

namespace local_lpfmigrator;

require('../../config.php');
require_once(__DIR__ . '/locallib.php');

$id = required_param('id', PARAM_INT);

require_login();
$PAGE->set_url(new \moodle_url('/local/lpfmigrator/compare.php', array('id' => $id)));
$PAGE->set_context(\context_system::instance());
$PAGE->set_heading(get_string('pluginname', 'local_lpfmigrator'));
$PAGE->set_title(get_string('pluginname', 'local_lpfmigrator'));
$PAGE->requires->css('/local/lpfmigrator/main.css');

$rec = $DB->get_record('local_lpfmigrator_instances', array('id' => $id));
$instance = new instance($rec->instancename);

echo $OUTPUT->header();

if (!$instance->can_manage_instance()) {
    echo $OUTPUT->render_from_template('local_lpfmigrator/alert', array(
        'type' => 'danger',
        'content' => get_string('access_denied', 'local_lpfmigrator'),
        'url' => new \moodle_url('/my', array()),
    ));
    echo $OUTPUT->footer();
    die();
}

$instanceo = $instance->as_object();
// Next line would not be necessary, but we force an update of this data by calling it.
$instanceo->adminusers = $instance->adminusers();
$instanceo->wwwroot = $CFG->wwwroot;

$instanceo->courses_remote = $instance->get_list_courses_remote();
$instanceo->courses_backup = $instance->get_list_courses_backup();

foreach ($instanceo->courses_remote as &$cremote) {
    $cremote->backups = $instanceo->courses_backup[$cremote->id];
}

echo $OUTPUT->render_from_template('local_lpfmigrator/compare', $instanceo);

echo $OUTPUT->footer();

instance::external_db_closeall();
