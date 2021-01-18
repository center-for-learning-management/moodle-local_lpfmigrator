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
 * The purpose of this file is to move a bunch of instances to another stage.
 */

namespace local_lpfmigrator;

require('../../config.php');
require_once(__DIR__ . '/locallib.php');

require_login();
$PAGE->set_url(new \moodle_url('/local/lpfmigrator/bulk.php', array()));
$PAGE->set_context(\context_system::instance());
$PAGE->set_heading(get_string('pluginname', 'local_lpfmigrator'));
$PAGE->set_title(get_string('pluginname', 'local_lpfmigrator'));
$PAGE->requires->css('/local/lpfmigrator/style/main.css');

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

$movefromstage = optional_param('fromstage', '', PARAM_INT);
$movetostage = optional_param('tostage', '', PARAM_INT);
$reallydo = optional_param('reallydo', '', PARAM_BOOL);

echo $OUTPUT->render_from_template('local_lpfmigrator/bulkstep', array(
    'movefromstage' . $movefromstage => true,
    'movetostage' . $movetostage => true,
));

if (!empty($movefromstage) && !empty($movetostage)) {
    if (empty($reallydo)) {
        echo "<p class=\"alert alert-warning\">This is a dry-run</p>";
    } else {
        echo "<p class=\"alert alert-danger\">WE REALLY DO THIS!</p>";
    }

    $sql = "SELECT id,instancename,stage,orgid,path_data
                FROM {local_lpfmigrator_instances}
                WHERE stage=?";

    $instances = array_values($DB->get_records_sql($sql, array($movefromstage)));
    ?>
    <table class="generaltable">
        <thead>
            <tr>
                <th>Instance</th>
                <?php
                for ($a = $movefromstage; $a <= $movetostage; $a++) {
                    ?><th><?php echo get_string('stage_' . $a, 'local_lpfmigrator'); ?></th><?php
                }
                ?>
                <th>Refresh</th>
            </tr>
        </thead>
        <tbody>
    <?php

    foreach ($instances AS $inst) {
        ?>
        <tr>
            <td><?php echo $inst->instancename; ?></td>
        <?php
        $instance = new instance($inst->instancename);
        $reloadcache = false;
        for ($a = $movefromstage; $a <= $movetostage; $a++) {
            ?>
            <td>
            <?php
            if ($instance->stage() <= $a) {
                switch ($a) {
                    case instance::STAGE_SEND_AUTHINFO:
                        if ($reallydo) {
                            $instance->send_authinfo();
                            $instance->stage(instance::STAGE_REMOVALWEB);
                        }
                        echo "info sent";
                    break;
                    case instance::STAGE_REMOVALWEB:
                        if ($reallydo) {
                            $instance->set_replacewebroot();
                            $instance->stage(instance::STAGE_REMOVALDATA);
                        }
                        echo "webroot removed";
                    break;
                    case instance::STAGE_REMOVALDATA:
                        if ($reallydo) {
                            $instance->remove_datadir();
                            $instance->remove_database();
                            $instance->stage(instance::STAGE_COMPLETED);
                        }
                        echo "data removed";
                    break;
                }
            } else {
                echo "-";
            }
            ?></td><?php
        }
        if (!empty($reloadcache)) {
            if ($reallydo) {
                $instance->refresh_cache();
            }
            echo "<td>1</td>";
        } else {
            echo "<td>-</td>";
        }
        ?></tr><?php
        flush();
    }
    ?></table><?php
}


echo $OUTPUT->footer();

instance::external_db_closeall();
