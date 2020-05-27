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
 * The purpose of this file is that moodle-admins can get an info about their site
 * and can use the trigger to optout from removal.
 */

namespace local_lpfmigrator;

require('../../config.php');
require_once(__DIR__ . '/locallib.php');

$sorgid = optional_param('sorgid', 0, PARAM_INT);
$sinstance = optional_param('sinstance', '', PARAM_ALPHANUM);

require_login();
$PAGE->set_url(new \moodle_url('/local/lpfmigrator/info.php', array('sorgid' => $sorgid, 'sinstance' => $sinstance)));
$PAGE->set_context(\context_system::instance());
$PAGE->set_heading(get_string('pluginname', 'local_lpfmigrator'));
$PAGE->set_title(get_string('pluginname', 'local_lpfmigrator'));
$PAGE->requires->css('/local/lpfmigrator/main.css');

echo $OUTPUT->header();

echo $OUTPUT->render_from_template('local_lpfmigrator/info_form', array('sorgid' => $sorgid, 'sinstance' => $sinstance));
if (!empty($sorgid) || !empty($sinstance)) {
    $sql = "SELECT *
                FROM {local_lpfmigrator_instances}
                WHERE orgid=?
                    OR instancename LIKE ?";
    // Attention, orgid 0 would potentially reveal all instances that are not assigned to a particular org!
    $instances = $DB->get_records_sql($sql, array(($sorgid > 0) ? $sorgid : -1, $sinstance));
    foreach ($instances AS $inst) {
        $org = $DB->get_record('block_eduvidual_org', array('lpf' => $inst->instancename));
        if (!empty($org->id)) {
            $inst->lpfgroup = $org->lpfgroup;
        }
        $inst->ismanager = false;
        if (!empty($inst->orgid)) {
            $role = $DB->get_record('block_eduvidual_orgid_userid', array('orgid' => $inst->orgid, 'userid' => $USER->id));
            $inst->ismanager = is_siteadmin() || !empty($role->role) && $role->role == 'Manager';
        }
        $inst->lpfmigrationdate = ($inst->lpfgroup == 'C') ? 'Sommer 2020' : 'Sommer 2021';
        $optout = optional_param('optout_' . $inst->instancename, 0, PARAM_INT);
        if (!empty($optout) && $inst->ismanager) {
            if ($optout == 1) {
                $DB->set_field('local_lpfmigrator_instances', 'removaloptout', 1, array('instancename' => $inst->instancename));
                echo $OUTPUT->render_from_template('local_lpfmigrator/alert', array(
                    'content' => 'Der Opt-Out wurde erfolgreich gespeichert. Das bedeutet, dass wir Ihre Instanz nach der erfolgten Archivierung <strong>nicht</strong> löschen werden!',
                    'type' => 'success'
                ));
            }
            if ($optout == -1) {
                $DB->set_field('local_lpfmigrator_instances', 'removaloptout', 0, array('instancename' => $inst->instancename));
                echo $OUTPUT->render_from_template('local_lpfmigrator/alert', array(
                    'content' => 'Der Opt-Out wurde erfolgreich zurückgezogen. Das bedeutet, dass wir Ihre Instanz nach der erfolgten Archivierung <strong>löschen werden</strong>!',
                    'type' => 'warning'
                ));
            }
        } elseif (!empty($optout)) {
            // We tried an optout, but are not allowed to do so!
            echo $OUTPUT->render_from_template('local_lpfmigrator/alert', array(
                'content' => 'Es ist Ihnen nicht gestattet für diese Moodle-Instanz das Optout zu beantragen!',
                'type' => 'danger'
            ));
        }
        echo $OUTPUT->render_from_template('local_lpfmigrator/info_instance', $inst);

    }

}



echo $OUTPUT->footer();
