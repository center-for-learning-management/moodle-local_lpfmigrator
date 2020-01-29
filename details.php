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
$PAGE->set_url(new \moodle_url('/local/lpfmigrator/details.php', array('id' => $id)));
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

if (is_siteadmin()) {
    // Check for modified data.
    $orgid = optional_param('orgid', -1, PARAM_INT);
    $lpfgroup = optional_param('lpfgroup', '-', PARAM_ALPHANUM);
    $path_data = optional_param('path_data', '-', PARAM_TEXT);
    $path_web = optional_param('path_web', '-', PARAM_TEXT);
    $path_backup = optional_param('path_backup', '-', PARAM_TEXT);
    $stage = optional_param('stage', -1, PARAM_INT);
    $comments = optional_param('comments', '-', PARAM_TEXT);
    $changed = array();
    if ($orgid > -1 && $orgid != $instance->orgid()) {
        $instance->orgid($orgid);
        $changed[] = get_string('orgid', 'local_lpfmigrator');
    }
    if ($lpfgroup != '-' && $lpfgroup != $instance->lpfgroup()) {
        $instance->lpfgroup($lpfgroup);
        $changed[] = get_string('lpfgroup', 'local_lpfmigrator');
    }
    if ($path_data != '-' && $path_data != $instance->path_data()) {
        $instance->path_data($path_data);
        $changed[] = get_string('path_data', 'local_lpfmigrator');
    }
    if ($path_web != '-' && $path_web != $instance->path_web()) {
        $instance->path_web($path_web);
        $changed[] = get_string('path_web', 'local_lpfmigrator');
    }
    if ($path_backup != '-' && $path_backup != $instance->path_backup()) {
        $instance->path_backup($path_backup);
        $changed[] = get_string('path_backup', 'local_lpfmigrator');
    }
    if ($stage > -1 && $stage != $instance->stage()) {
        $instance->stage($stage);
        $changed[] = get_string('stage', 'local_lpfmigrator');
    }
    if ($comments != '-' && $comments != $instance->comments()) {
        $instance->comments($comments);
        $changed[] = get_string('comments', 'local_lpfmigrator');
    }

    // Check for actions.
    $startstaging = optional_param('startstaging', '', PARAM_ALPHANUM);
    if (!empty($startstaging) && $startstaging == "on" && $instance->stage() == instance::STAGE_NOT_STAGED) {
        $instance->stage(instance::STAGE_NOTIFY_ADMINS);
        $changed[] = get_string('stage_' . instance::STAGE_NOT_STAGED, 'local_lpfmigrator');
    }
    $notifyadmins = optional_param('notifyadmins', '', PARAM_ALPHANUM);
    if (!empty($notifyadmins) && $notifyadmins == "on" && $instance->stage() == instance::STAGE_NOTIFY_ADMINS) {
        $instance->notify_admins();
        $instance->stage(instance::STAGE_NOTIFY_USERS);
        $changed[] = get_string('stage_' . instance::STAGE_NOTIFY_ADMINS, 'local_lpfmigrator');
    }
    $notifyusers = optional_param('notifyusers', '', PARAM_ALPHANUM);
    if (!empty($notifyusers) && $instance->stage() == instance::STAGE_NOTIFY_USERS) {
        $to = ($notifyusers == 'on');
        $instance->set_usernotifybanner($to);
        $instance->stage(instance::STAGE_MAINTENANCE);
        $changed[] = get_string('stage_' . instance::STAGE_NOTIFY_USERS, 'local_lpfmigrator');
    }
    $maintenance = optional_param('maintenance', '', PARAM_ALPHANUM);
    if (!empty($maintenance) && $instance->stage() == instance::STAGE_MAINTENANCE) {
        $to = ($maintenance == 'on');
        $instance->set_maintenance_mode($to);
        if ($to) $instance->stage(instance::STAGE_BACKUPS);
        $changed[] = get_string('stage_' . instance::STAGE_MAINTENANCE, 'local_lpfmigrator');
    }
    $backups = optional_param('backups', '', PARAM_ALPHANUM);
    if (!empty($backups) && $instance->stage() == instance::STAGE_BACKUPS) {
        $to = ($backups == 'on');
        $instance->set_backup_config($to);
        if ($to) $instance->stage(instance::STAGE_REVIEWED);
        $changed[] = get_string('stage_' . instance::STAGE_BACKUPS, 'local_lpfmigrator');
    }
    $review = optional_param('review', '', PARAM_ALPHANUM);
    if (!empty($review) && $review == "on" && $instance->stage() == instance::STAGE_REVIEWED) {
        $instance->stage(instance::STAGE_REMOVALWEB);
        $changed[] = get_string('stage_' . instance::STAGE_REVIEWED, 'local_lpfmigrator');
    }
    $removeweb = optional_param('removeweb', '', PARAM_ALPHANUM);
    if (!empty($removeweb) && $removeweb == "on" && $instance->stage() == instance::STAGE_REMOVALWEB) {
        $instance->stage(instance::STAGE_REMOVALDATA);
        $changed[] = get_string('stage_' . instance::STAGE_REMOVALWEB, 'local_lpfmigrator');
    }
    $removedata = optional_param('removedata', '', PARAM_ALPHANUM);
    if (!empty($removedata) && $removedata == "on" && $instance->stage() == instance::STAGE_REMOVALDATA) {
        $instance->remove_datadir();
        $instance->remove_database();
        $instance->stage(instance::STAGE_COMPLETED);
        $changed[] = get_string('stage_' . instance::STAGE_REMOVALDATA, 'local_lpfmigrator');
    }

    if (count($changed) > 0) {
        echo $OUTPUT->render_from_template('local_lpfmigrator/modified_successfully', array('changed' => $changed));
    }
}


$instanceo = $instance->as_object();
$instanceo->editable = is_siteadmin();
$instanceo->stages = instance::get_stages($instance);
$instanceo->courses_remote = $instance->get_amount_courses_remote();
$instanceo->courses_backup = $instance->get_amount_courses_backup();
$instanceo->courses_equals = ($instanceo->courses_remote == $instanceo->courses_backup);
$instanceo->has_backups_enabled = $instance->has_backups_enabled();
$instanceo->has_maintenance_enabled = $instance->has_maintenance_enabled();
$instanceo->has_notifyusers_enabled = $instance->has_notifyusers_enabled();
$instanceo->sealed = $instance->stage() == instance::STAGE_COMPLETED;
$instanceo->wwwroot = $CFG->wwwroot;

echo $OUTPUT->render_from_template('local_lpfmigrator/details', $instanceo);

echo $OUTPUT->footer();

instance::external_db_closeall();
