# moodle-local_lpfmigrator
This is moodle-plugin is intended to assist the migration of lernplattform.schule.at to eduvidual.at


## Stages

1. Not staged
    Every instance is staged as ''not staged'' at first. You have to start staging before any action can be taken.

2. Notify admins
    Moodle Siteadmins get informed by email that their instance will close down.

3. Notify users
    A banner is injected into the moodle site so that all users can see that there is something going on. This is achieved by placing the file "eduvidualnotifybanner.html" in the datadir.
    We modified to config.php of all moodle sites to look at this file and inject it.

4. Maintenance mode
    The maintenance mode is indicated by placing the file "eduvidualmaintenance.html" in the datadir. We modified to config.php of all moodle sites to look at this file and show it.

5. Backups
    A backup directory is automatically set based on the datadir (uses the same harddrive).
    Backups are getting scheduled and the name of the instance is inserted in a textfile called "scheduledbackups.txt" in the first datadir that was configured. A cronjob on mdcli01.bmb.gv.at looks at new lines in this textfield and starts backups. All actions are logged to a file in the first datadir called log_''instancename''.log.

6. Backups reviewed
    A human being has to review that backups have been made.

7. Remove Webroot
    An entry with the name of the webroot is inserted in a textfile called "scheduledwebrootremovals01.txt" and "scheduledwebrootremovals02.txt". A cronjob on both machines, mdweb01.bmb.gv.at and mdweb02.bmb.gv.at, looks at entries in these files. Once found it unlinks the current webroot and links to another webroot that only shows an informative page to users.

8. Remove Data
    Now we are getting serious. The database is deleted directly by PHP. The removal of the Moodle datadir is commanded to the mdcli01.bmb.gv.at-Server by injecting the path of the datadir into the textfile "scheduledremovals.txt" in the first datadir that was configured. We will only keep what is in the backup dir.

9. Send info to admins
    Send a notification to admins that the backup has been done and the moodle site was removed. An information is included where they can find and how they can access the backup files.

10. Completed
