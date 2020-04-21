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
$org = $DB->get_record('block_eduvidual_org', array('lpf' => $instance));
if (empty($org->orgid)) {
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

function local_lpf_showbanner() {
    var body = document.querySelector('body');
    var iframe = document.createElement('iframe');
    iframe.setAttribute('id', 'local_lpf_banner');
    iframe.setAttribute('src', '<?php echo $CFG->wwwroot; ?>/local/lpfmigrator/lpfbanner/banner.php');
    iframe.setAttribute('style', 'display: none; position: fixed; bottom: 10px; right: 10px; width: 300px; height: 200px; border: 0px;');
    body.appendChild(iframe);
    setTimeout(local_lpf_fadebanner, 200);
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
