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

$sorgid = trim(optional_param('sorgid', '', PARAM_ALPHANUM));
$sinstance = optional_param('sinstance', '', PARAM_ALPHANUM);
// Remove parts from instance-name, that we do not want.
// Because of type "PARAM_ALPHANUM" the value can not contain slashes or other symbols
$sinstance = str_replace("httpswww3lernplattformschuleat", "", $sinstance);
$sinstance = str_replace("httpswww4lernplattformschuleat", "", $sinstance);
$sinstance = str_replace("httpwww3lernplattformschuleat", "", $sinstance);
$sinstance = str_replace("httpwww4lernplattformschuleat", "", $sinstance);
$sinstance = trim($sinstance);

require_login();
$PAGE->set_url(new \moodle_url('/local/lpfmigrator/info.php', array('sorgid' => $sorgid, 'sinstance' => $sinstance)));
$PAGE->set_context(\context_system::instance());
$PAGE->set_heading("Umstieg des Bundes-Moodle");
$PAGE->set_title("Umstieg des Bundes-Moodle");
$PAGE->requires->css('/local/lpfmigrator/style/main.css');

echo $OUTPUT->header();

echo $OUTPUT->render_from_template('local_lpfmigrator/info_form', array(
    'showtab_when' => (!empty($sorgid) || !empty($sinstance)),
    'sinstance' => $sinstance,
    'sorgid' => $sorgid,
));
if (!empty($sorgid) || !empty($sinstance)) {
    $sql = "SELECT *
                FROM {local_lpfmigrator_instances}
                WHERE orgid=?
                    OR instancename LIKE ?";
    // Attention, orgid 0 would potentially reveal all instances that are not assigned to a particular org!
    $instances = $DB->get_records_sql($sql, array(($sorgid > 0) ? $sorgid : -1, $sinstance));
    foreach ($instances AS $inst) {
        $inst->sorgid = $sorgid;
        $inst->sinstance = $sinstance;
        $sql = "SELECT * FROM {local_eduvidual_org}
                    WHERE lpf LIKE ? OR orgid=?";
        $org = $DB->get_record_sql($sql, array($inst->instancename, $inst->orgid));

        $inst->lpfgroup = '';
        if (!empty($org->id)) {
            $inst->lpfgroup = $org->lpfgroup;
        }
        $inst->ismanager = false;
        $inst->stagetxt = get_string('stage_' . $inst->stage, 'local_lpfmigrator');
        switch($inst->stage) {
            case 0: $inst->stagetxt = 'Migration nicht gestartet'; break;
            case 1: $inst->stagetxt = 'Schulleitung / Moodle-Admins wurden informiert'; break;
            case 2: $inst->stagetxt = 'Nutzer/innen wurden informiert (Banner)'; break;
            case 3: $inst->stagetxt = 'Wartungsmodus wird vorbereitet'; break;
            case 4: $inst->stagetxt = 'Backup wird vorbereitet'; break;
            case 5: $inst->stagetxt = 'Backup wurde abgeschlossen und geprüft'; break;
            case 6: $inst->stagetxt = 'Logindaten für Sicherung wurden zugestellt'; break;
            case 7: $inst->stagetxt = 'Webverzeichnis werden bald auf statische Seite umgestellt'; break;
            case 8: $inst->stagetxt = 'Datenbank und Datenverzeichnis werden bald gelöscht'; break;
            case 9: $inst->stagetxt = 'Migration abgeschlossen'; break;
        }

        if (!empty($inst->orgid)) {
            $role = $DB->get_record('local_eduvidual_orgid_userid', array('orgid' => $inst->orgid, 'userid' => $USER->id));
            $inst->ismanager = is_siteadmin() || !empty($role->role) && $role->role == 'Manager';
        }
        $inst->lpfmigrationdate = ($inst->lpfgroup == 'C') ? 'Sommer 2020' : 'Sommer 2021';
        $optout = optional_param('optout_' . $inst->instancename, 0, PARAM_INT);
        if (!empty($optout) && $inst->ismanager) {
            if ($optout == 1) {
                $inst->removaloptout = 1;
                $DB->set_field('local_lpfmigrator_instances', 'removaloptout', $inst->removaloptout, array('instancename' => $inst->instancename));
                echo $OUTPUT->render_from_template('local_lpfmigrator/alert', array(
                    'content' => 'Der Opt-Out wurde erfolgreich gespeichert. Das bedeutet, dass wir Ihre Instanz nach der erfolgten Archivierung <strong>nicht</strong> löschen werden! Bitte löschen Sie alle Kurse, die älter als 2 Jahre sind, sobald Sie von uns die Zugriffsdaten für Ihr Backup erhalten haben (planmäßig im Juli).',
                    'type' => 'success'
                ));
            }
            if ($optout == -1) {
                $inst->removaloptout = 0;
                $DB->set_field('local_lpfmigrator_instances', 'removaloptout', $inst->removaloptout, array('instancename' => $inst->instancename));
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
