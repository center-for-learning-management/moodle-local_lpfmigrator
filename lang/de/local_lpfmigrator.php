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

$string['pluginname'] = 'LPF Migration';
$string['privacy:metadata'] = 'Dieses Plugin speichert keine personenbezogenen Daten';

$string['access_denied'] = 'Zutritt verweigert';
$string['auth'] = 'Authentifizierung';
$string['back_to_list'] = 'Zurück zur Liste';
$string['backups_off'] = 'Backups abschalten';
$string['backups_on'] = 'Backups einschalten';
$string['comments'] = 'Kommentare';
$string['currently_off'] = 'Derzeit <strong>nicht</strong> aktiv';
$string['currently_on'] = 'Derzeit aktiv';
$string['datafolders'] = 'Pfade der Moodle-Datenverzeichnisse (getrennt mit Beistrichen)';
$string['dashboard'] = 'Dashboard';
$string['decrease_stage_first'] = 'Zuerst die Stufe verringern';
$string['details'] = 'Details';
$string['has_courses'] = 'Es sind {$a->remote} Kurse in der Moodle-Datenbank, und {$a->backup} ZIP-Dateien im Backupverzeichnis.';
$string['instance_removedata_now'] = 'Daten der Moodle-Instanz jetzt löschen';
$string['instance_removeweb_now'] = 'Ich habe gerade das Webverzeichnis manuell entfernt!';
$string['instance_remove_soon'] = 'Diese Moodle-Instanz wird bald gelöscht.';
$string['instance_removed'] = 'Diese Moodle-Instanz wurde gelöscht.';
$string['instance_removed_data'] = 'Datenbank und Datenverzeichnis wurden gelöscht.';
$string['instance_removed_web'] = 'Webverzeichnis wurde manuell gelöscht.';
$string['instancename'] = 'Instanzname';
$string['list_databases'] = 'Alle Datenbanken auflisten';
$string['lpfgroup'] = 'LPF-Gruppe';
$string['mail_recipients'] = 'Empfänger/innen dieser Nachricht:';
$string['maintenance_off'] = 'Wartungsmodus abschalten';
$string['maintenance_on'] = 'Wartungsmodus einschalten';
$string['modified_successfully'] = 'Erfolgreich folgende Datenfelder geändert:';
$string['notify_admins'] = 'Moodle-Administrator/innen benachrichtigen';
$string['notify_admins_done'] = 'Moodle-Administrator/innen wurden benachrichtigt!';
$string['notify_admins:subject'] = 'lernplattform.schule.at/{$a->instancename}: Wichtige Änderungen!';
$string['notify_admins:text'] = '<strong><i>Liebe/r {$a->firstname} {$a->lastname},</i></strong> <br /><br />Sie erhalten diese e-Mail, weil Sie als Administrator/in der Moodle-Instanz unter <i>lernplattform.schule.at/<strong>{$a->instancename}</strong></i> eingetragen sind.<br /><br /><strong>Das Projekt lernplattform.schule.at wird zugunsten des Nachfolgeprojekts <a href="https://www.eduvidual.at" target="_blank">eduvidual.at</a> aufgelassen.</strong><br /><br />Bitte nehmen Sie zur Kenntnis, dass Ihre Moodle-Instanz deshalb im Juli abgeschaltet wird. Anfang Juli wird die Moodle-Instanz zuerst in einen Wartungsmodus versetzt. Anschließend werden automatische Kursbackups angelegt, auf die Sie in weiterer Folge Zugriff erhalten.<br /><br />Wir empfehlen die Zeit bis dahin zu nutzen, um sich in der <strong>neuen, gemeinsamen Moodle-Lernplattform <a href="https://www.eduvidual.at" target="_blank">eduvidual.at</a> zu registrieren und einzurichten.</strong><br /><br />Mehr Informationen finden Sie auf unserer Webseite unter <a href="https://www.lernmanagement.at" target="_blank">lernmanagement.at</a>, und natürlich helfen wir auch gerne per e-Mail unter <a href="mailto:support@lernmanagement.at">support@lernmanagement.at</a> weiter.<br /><br />Mit freundlichen Grüßen<br /><br />Ihr Team vom<br />Zentrum für Lernmanagement';
$string['open_backup'] = 'Backup öffnen';
$string['open_lpf'] = 'Moodle öffnen';
$string['orgid'] = 'Schulkennzahl';
$string['path_data'] = 'Pfad zum Moodle-Datenverzeichnis';
$string['path_web'] = 'Pfad zum Moodle-Webverzeichnis';
$string['path_backup'] = 'Pfad zum Backupverzeichnis';
$string['pluginname:settings'] = 'LPF Migration Einstellungen';
$string['proceed_anyway'] = 'Achtung, Sie können fortsetzen, aber Sie sollten wissen, was Sie da tun!';
$string['purge_caches'] = 'Alle Zwischenspeicher der Moodle-Instanz zurücksetzen (empfohlen)';
$string['read_databases'] = 'Alle Datenbanken einlesen';
$string['read_databases:success'] = 'Erfolgreich {$a->read} Datenbanken eingelesen';
$string['site_deactivated'] = 'Seite deaktiviert';
$string['site_deactivated:longtext'] = 'Diese Seite wurde deaktiviert. Für weitere Informationen kontaktieren Sie bitte <a href="mailto:support@lernmanagement.at">support@lernmanagement.at</a>';
$string['sqlservers:hosts'] = 'SQL-Hosts (getrennt mit Beistrichen)';
$string['sqlservers:users'] = 'SQL-Benutzernamen (getrennt mit Beistrichen)';
$string['sqlservers:passwords'] = 'SQL-Passwörter (getrennt mit Beistrichen)';
$string['stage'] = 'Stufe';
$string['stage_0'] = 'Nicht gestartet';
$string['stage_1'] = 'Benachrichtigen';
$string['stage_2'] = 'Backups aktivieren';
$string['stage_3'] = 'Wartungsmodus aktivieren';
$string['stage_4'] = 'Prüfung des Backups';
$string['stage_5'] = 'Webhost entfernen';
$string['stage_6'] = 'Daten entfernen';
$string['stage_7'] = 'Gelöscht';
$string['staging_not_started'] = 'Abschaltung wurde noch nicht gestartet.';
$string['staging_start'] = 'Starte Abschaltung dieser Instanz';
$string['staging_started'] = 'Abschaltung wurde in Angriff genommen';
