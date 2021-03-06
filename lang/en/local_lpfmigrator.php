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

$string['pluginname'] = 'LPF Migrator';
$string['privacy:metadata'] = 'This plugin does not store any personal data';
$string['cachedef_banners'] = 'Cache for storing banner-status';

$string['access_denied'] = 'Access denied';
$string['admins'] = 'Administrators';
$string['auth'] = 'Authentication';
$string['authinfo:subject'] = 'lernplattform.schule.at/{$a->instancename}: Your Backup!';
$string['authinfo:text'] = '<strong><i>Dear {$a->firstname} {$a->lastname},</i></strong> <br /><br />You receive this e-Mail because you are named as Administrator of the lernplattform.schule.at-Moodleinstance <strong>{$a->instancename}</strong>.<br /><br /><strong>The project lernplattform.schule.at is discontinued in favor of its successor <a href="https://www.eduvidual.at" target="_blank">eduvidual.at</a>.</strong><br /><br /> A backup of all courses has been done automatically. You can restore all courses directly in eduvidual.at according to our <a href="https://www.eduvidual.at/mod/page/view.php?id=74461" target="_blank">tutorial</a> or access this backups at <a href="{$a->wwwroot}/lpf{$a->backupnr}/{$a->instancename}" target="_blank">{$a->wwwroot}/lpf{$a->backupnr}/{$a->instancename}</a> and the username <strong>{$a->instancename}</strong>, password <strong>{$a->path_backup_pwd}</strong>.<br /><br />Please <strong>register at the new, unified Moodle for Austrian schools at <a href="https://www.eduvidual.at" target="_blank">eduvidual.at</a></strong>.<br /><br />If you have any questions so far, please refer to our Website at <a href="https://www.lernmanagement.at" target="_blank">lernmanagement.at</a> or contact us at <a href="mailto:support@lernmanagement.at">support@lernmanagement.at</a>.<br /><br />Kind regards<br /><br />Your team from your<br />Center of Learning Management';
$string['back_to_dashboard'] = 'Back to dashboard';
$string['back_to_list'] = 'Back to list';
$string['backupfolder'] = 'Folder for Backups';
$string['backups_clear'] = 'Remove all backups';
$string['backups_cleared'] = 'All backups removed';
$string['backups_off'] = 'Turn off backups';
$string['backups_on'] = 'Turn on backups';
$string['backups_renamed'] = '{$a->renamed} Backups were renamed';
$string['backupsize'] = 'Backupdir size';
$string['comments'] = 'Comments';
$string['compare'] = 'Compare';
$string['currently_off'] = 'Currently <strong>not</strong> activated';
$string['currently_on'] = 'Currently activated';
$string['datafolders'] = 'Paths of moodle-datadirs (delimited by comma)';
$string['datasize'] = 'Datadir size';
$string['dashboard'] = 'Dashboard';
$string['decrease_stage_first'] = 'Decrease stage first';
$string['details'] = 'Details';
$string['has_courses'] = 'There are {$a->remote} courses in the moodle-database and {$a->backup} ZIP-files in the backup.';
$string['infofilefolder'] = 'Infofile-Folder';
$string['instance_removedata_now'] = 'Remove data from this instance';
$string['instance_removeweb_now'] = 'Remove the webroot now!';
$string['instance_remove_soon'] = 'This instance will be removed soon';
$string['instance_removed'] = 'This instance was removed';
$string['instance_removed_database'] = 'Database was removed';
$string['instance_removed_datadir'] = 'Datadir was removed';
$string['instance_removed_datadir_missing'] = 'Removal of datadir missing';
$string['instance_removed_web'] = 'Webdir was removed manually';
$string['instancename'] = 'Instancename';
$string['list_databases'] = 'List all databases';
$string['log'] = 'Log';
$string['lpfgroup'] = 'LPF-group';
$string['mail_recipients'] = 'Recipients of this message:';
$string['maintenance_off'] = 'Turn off maintenance mode';
$string['maintenance_on'] = 'Turn on maintenance mode';
$string['migrationperiod'] = 'Migrationdate';
$string['modified_successfully'] = 'Successfully modified the following fields:';
$string['notify_admins'] = 'Notify admins';
$string['notify_admins_done'] = 'Admins have been notified!';
$string['notify_admins:subject'] = 'lernplattform.schule.at/{$a->instancename}: Important changes will happen!';
$string['notify_admins:text'] = '<strong><i>Dear {$a->firstname} {$a->lastname},</i></strong> <br /><br />You receive this e-Mail because you are named as Administrator of the lernplattform.schule.at-Moodleinstance <strong>{$a->instancename}</strong>.<br /><br /><strong>The project lernplattform.schule.at is discontinued in favor of its successor <a href="https://www.eduvidual.at" target="_blank">eduvidual.at</a>.</strong><br /><br />Please note that your Moodle instance will be closed down according to individual agreement with one of the Moodle-Administrators of your site, but at 19th of July 2021 at the latest. We will show a notification banner in your Moodle site to ensure, that all users are aware of this. Consequently your Moodle-Instance will be set into maintenance mode at the beginning of the summer holidays. A backup of all courses will be done automatically, and you will be able to access these backup files afterwards.<br /><br />We appreciate if you <strong>register at the new, unified Moodle for Austrian schools at <a href="https://www.eduvidual.at" target="_blank">eduvidual.at</a></strong> and use the meanwhile to accomodate with our new, more sophisticated learning platform.<br /><br />If you have any questions so far, please refer to our Website at <a href="https://www.lernmanagement.at" target="_blank">lernmanagement.at</a> or contact us at <a href="mailto:support@lernmanagement.at">support@lernmanagement.at</a>.<br /><br />Kind regards<br /><br />Your team from your<br />Center of Learning Management';
$string['notify_users_off'] = 'Deactivate user notification';
$string['notify_users_on'] = 'Activate user notification';
$string['open_backup'] = 'Open Backup';
$string['open_lpf'] = 'Open LPF-Moodle';
$string['orgid'] = 'Org-ID';
$string['path_data'] = 'Path of Moodle-Datadir';
$string['path_web'] = 'Path of Moodle-Webdir';
$string['path_backup'] = 'Path of Backup-Dir';
$string['pluginname:settings'] = 'LPF Migrator settings';
$string['proceed_anyway'] = 'Attention, you can proceed anyway, but know what you are doing!';
$string['purge_caches'] = 'Purge caches of remote moodle (recommended)';
$string['read_databases'] = 'Read all databases';
$string['read_databases:success'] = 'Successfully read {$a->read} databases';
$string['removaloptout'] = 'This org opted out from removal';
$string['rename_backup_files'] = 'Rename backup files';
$string['schedule'] = 'Backup-List';
$string['scheduled_instances'] = 'Scheduled instances';
$string['send_authinfo'] = 'Send auth-info to access backup files';
$string['send_authinfo_done'] = 'Admins got auth-info to access backup files';
$string['site_deactivated'] = 'Site deactivated';
$string['site_deactivated:longtext'] = 'This site was deactivated. If you need any further information please contact <a href="mailto:support@lernmanagement.at">support@lernmanagement.at</a>';
$string['sqlservers:hosts'] = 'SQL-Hosts (delimited by comma)';
$string['sqlservers:users'] = 'SQL-Users (delimited by comma)';
$string['sqlservers:passwords'] = 'SQL-Passwords (delimited by comma)';
$string['stage'] = 'Stage';
$string['stage_0'] = 'Not staged';
$string['stage_1'] = 'Notify admins';
$string['stage_2'] = 'Notify users';
$string['stage_3'] = 'Enable Maintenance mode';
$string['stage_4'] = 'Enable Backups';
$string['stage_5'] = 'Perform Review';
$string['stage_6'] = 'Send Auth-Info';
$string['stage_7'] = 'Web Removal';
$string['stage_8'] = 'Data Removal';
$string['stage_9'] = 'Removed';
$string['staging_not_started'] = 'Staging has not started.';
$string['staging_start'] = 'Start staging this instance';
$string['staging_started'] = 'Staging was started';
$string['webroot_not_yet_replaced'] = 'Webroot was not yet replaced';
$string['webroot_replaced'] = 'Webroot has been replaced';
