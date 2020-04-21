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
 * The purpose of this file is serve an informative banner in other Moodle instances
 * that they have to migrate.
 */

header("Content-type: application/javascript");
require('../../../config.php');

?>
/**
* Zentrum für Lernmanagement
* Migrationstool lernplattform.schule.at
**/
<?php

$instance = optional_param('instance', '', PARAM_TEXT);
$inst = $DB->get_record('local_lpfmigrator_instances', array('instancename' => $instance));
$inst = (object) array('id' => 1, 'stage' => 3);
if (empty($inst->id) || $inst->stage < 2) {
    ?>
/**
 * Keine Migration geplant.
**/
    <?php
} else {
    ?>
/**
 * Migration geplant.
**/

window.onload = function() { local_lpf_showbanner() };
var local_lpf_opacity = 1;

function local_lpf_getCookieValue(a) {
   const b = document.cookie.match('(^|;)\\s*' + a + '\\s*=\\s*([^;]+)');
   return b ? b.pop() : '';
}

function local_lpf_showbanner() {
    var wasshown = local_lpf_getCookieValue("local_lpf_bannershown");
    console.error('wasshown', wasshown);
    if (typeof wasshown !== 'undefined') {
        document.cookie = "local_lpf_bannershown=1; expires=Thu, 1 Dec 2000 12:00:00 UTC";
        var body = document.querySelector('body');
        var iframe = document.createElement('iframe');
        iframe.setAttribute('id', 'local_lpf_banner');
        iframe.setAttribute('src', '<?php echo $CFG->wwwroot; ?>/local/lpfmigrator/lpfbanner/banner.php');
        iframe.setAttribute('style', 'display: none; position: fixed; bottom: 10px; right: 10px; width: 300px; height: 200px; border: 0px;');
        body.appendChild(iframe);
        setTimeout(local_lpf_fadebanner, 200);
    }
}

function local_lpf_fadebanner() {
    local_lpf_opacity = local_lpf_opacity - 0.01;
    if (local_lpf_opacity < 0) {
        local_lpf_hidebanner();
    } else {
        document.querySelector('#local_lpf_banner').style.opacity = local_lpf_opacity;
        setTimeout(local_lpf_fadebanner, 200);
    }
}

function local_lpf_hidebanner() {
    document.querySelector('#local_lpf_banner').remove();
}
    <?php
}
