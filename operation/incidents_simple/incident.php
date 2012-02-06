<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2012 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// LOAD GLOBAL VARS
global $config;

// SET GLOBAL VARS
$width = '90%';

// CHECK LOGIN AND ACLs
check_login ();

if (! give_acl ($config['id_user'], 0, "IR")) {
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access incident viewer");
	require ("general/noaccess.php");
	exit;
}

// GET INCIDENT ID
$incident_id = (int) get_parameter('id', 0);

// GET ACTIVE TAB
$active_tab = get_parameter('active_tab', 'details');

if($incident_id == 0) {
	echo '<h3 class="error">'.__('Incident not found').'</h3>';
	exit;
}

// GET ACTION PARAMETERS
$upload_file = get_parameter('upload_file');
$add_workunit = get_parameter('add_workunit');

// ACTIONS
if($upload_file) {
	$filename = get_parameter('upfile');
	$file_description = get_parameter('description',__('No description available'));

	$file_temp = sys_get_temp_dir()."/$filename";
	
	$result = attach_incident_file ($incident_id, $file_temp, $file_description);
	
	echo $result;
	
	$active_tab = 'files';
}

if($add_workunit) {
	$note = get_parameter('note');
	$public = 1;
	$timeused = "0.05";
	
	$result = add_workunit_incident($incident_id, $note, $timeused, $public);
	
	if($result) {
		echo '<h3 class="suc">'.__('Workunit added').'</h3>';
	}
	else {
		echo '<h3 class="error">'.__('There was a problem adding workunit').'</h3>';
	}
	$active_tab = 'workunits';
}

// GET INCIDENT FROM DATABASE
$incident = get_full_incident($incident_id);

// PRINT INCIDENT
echo "<h1>".__('Incident')." #$incident_id - ".$incident['details']['titulo']."</h1>";

// TABS
?>

<ul style="height: 30px;" class="ui-tabs-nav">
	<li class="ui-tabs-selected" id="li_details">
		<a href='javascript:' id='tab_details' class='tab'><span><?php echo __('Details') ?></span></a>
	</li>
	<li class="ui-tabs" id="li_workunits">
		<a href='javascript:' id='tab_workunits' class='tab'><span><?php echo __('Workunits') ?></span></a>
	</li>
	<li class="ui-tabs" id="li_files">
		<a href='javascript:' id='tab_files' class='tab'><span><?php echo __('Files') ?></span></a>
	</li>
</ul>

<?php 

echo "<hr style='width:$width; border:solid 1px #2179B1;'>";

// ACTION BUTTONS
echo "<div style='width:$width;text-align:right;'>";
print_button (__('Add workunit'), 'add_workunit_show', false, '', 'style="margin-top:8px;" class="action_btn sub next"');
print_button (__('Add file'), 'add_file_show', false, '', 'style="margin-top:8px;" class="action_btn sub next"');
echo "</div>";

// ADD WORKUNIT FORM
echo "<div style='width:$width;display:none;' id='form_workunit'>";
echo "<form method='post' action=''>";
print_textarea ('note', 5, 10, '', '', false, __('Workunit'));
print_input_hidden ('add_workunit', 1);
print_input_hidden ('id', $incident_id);
echo "<div style='text-align:right'>";
print_submit_button (__('Add'), 'add_workunit_button', false, 'style="margin-top:4px;" class="sub next"');
echo "</div>";
echo "</form>";
echo "</div>";

// UPLOAD FILE FORM
echo "<div style='width:$width;display:none;' id='form_file'>";
$action = '';
$into_form = print_input_hidden ('id', $incident_id, true);
$into_form .= print_input_hidden ('upload_file', 1, true);
$into_form .= print_textarea ('description', 2, 10, '', '', true, __('Description'));
$into_form .= "<div style='text-align:right;'>";
$into_form .= print_button (__('Upload'), 'add_file', false, '', 'style="margin-top:4px;" class="action_btn sub next"', true);
$into_form .= "</div>";
echo '<b>'.__('File').'</b>';
echo '<br>'.print_input_file_progress($action, $into_form, 'id="form-add-file"', 'sub next', 'button-add_file', true);
echo "</div>";

require('operation/incidents_simple/incident.details.php');
require('operation/incidents_simple/incident.workunits.php');
require('operation/incidents_simple/incident.files.php');

?>

<script type="text/javascript">
$(document).ready (function () {
	$('#button-add_workunit_show').click(function() {
		$('.action_btn').attr('disabled','');
		$('#form_workunit').toggle();
		$('#form_file').hide();
	});
	
	$('#button-add_file_show').click(function() {
		$('.action_btn').attr('disabled','');
		$('#form_file').toggle();
		$('#form_workunit').hide();
	});
	
	// MENU TABS
	
	$('.tab').click(function() {
		type = $(this).attr('id').split('_')[1];
		$('.tab_data').hide();
		$('#'+type+'_data').show();
		$('.ui-tabs-selected').attr('class','ui-tabs');
		$('#li_'+type).attr('class','ui-tabs-selected');
	});
	
	// Load the active tab passed by get
	$('#tab_<?php echo $active_tab; ?>').trigger('click');
});
</script>
