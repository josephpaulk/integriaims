<?php

// Integria IMS - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2008 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

check_login ();

$id_grupo = "";
$creacion_incidente = "";
$id_incident = (int) get_parameter ('id');
$title = '';

require_once ('include/functions_workunits.php');

if (!$id_incident) {
	return;
}

// Obtain group of this incident
$incident = get_incident ($id_incident);

$result_msg = '';

//user with IR and incident creator see the information
if (! give_acl ($config['id_user'], $incident['id_grupo'], 'IR')
	&& ($incident['id_creator'] != $config['id_user'])) {
	audit_db ($id_user,$REMOTE_ADDR, "ACL Violation","Trying to access to incident #".$id_incident);
	include ("general/noaccess.php");
	exit;
}


// Workunit ADD
$insert_workunit = (bool) get_parameter ('insert_workunit');
if ($insert_workunit) {
	$timestamp = print_mysql_timestamp();
	$nota = get_parameter ("nota");
	$timeused = (float) get_parameter ('duration');
	$have_cost = (int) get_parameter ('have_cost');
	$profile = (int) get_parameter ('work_profile');
	$public = (bool) get_parameter ('public');

	// Adding a new workunit to a incident in NEW status
	// Status go to "Assigned" and Owner is the writer of this Workunit
	if (($incident["estado"] == 1) AND ($incident["id_creator"] != $config['id_user'])){
		$sql = sprintf ('UPDATE tincidencia SET id_usuario = "%s", estado = 3,  affected_sla_id = 0, actualizacion = "%s" WHERE id_incidencia = %d', $config['id_user'], $timestamp, $id);
	} else {
		$sql = sprintf ('UPDATE tincidencia SET affected_sla_id = 0, actualizacion = "%s" WHERE id_incidencia = %d', $timestamp, $id);
	}

	process_sql ($sql);

	create_workunit ($id, $nota, $config["id_user"], $timeused, $have_cost, $profile, $public);

	$result_msg = '<h3 class="suc">'.__('Workunit added successfully').'</h3>';

	echo $result_msg;
}

//Add workunit form
echo "<h3>".__('Add workunit')."</h3>";

$now =  print_mysql_timestamp();

$table->width = '100%';
$table->colspan = array ();
$table->colspan[1][0] = 5;
$table->data = array ();
$table->size = array();
$table->style = array();
$table->data[0][0] = "<i>$now</i>";
$table->data[0][1] = combo_roles (1, 'work_profile', __('Profile'), true);
$table->data[0][2] = print_input_text ("duration", $config["iwu_defaultime"], '', 7,  10, true, __('Time used'));
$table->data[0][3] = print_checkbox ('have_cost', 1, false, true, __('Have cost'));
$table->data[0][4] = print_checkbox ('public', 1, true, true, __('Public'));

$table->data[1][0] = print_textarea ('nota', 10, 70, '', "style='resize:none;'", true, __('Description'));

echo '<form id="form-add-workunit" method="post" action="index.php?sec=incidents&sec2=operation/incidents/incident_dashboard_detail&id='.$id.'&tab=workunits#incident-operations">';

echo "<div style='width: 80%;margin: 0 auto;'>";
print_table ($table);

echo '<div style="width: 100%" class="button">';
echo '<span id="sending_data" style="display: none;">' . __('Sending data...') . '<img src="images/spinner.gif" /></span>';
print_submit_button (__('Add'), 'addnote', false, 'class="sub next"');
print_input_hidden ('insert_workunit', 1);
print_input_hidden ('id', $id);
echo '</div>';
echo "</form>";

echo "</div>";

echo "<h3>".__('Workunits')."</h3>";

// Workunit view
$workunits = get_incident_workunits ($id_incident);

if ($workunits === false) {
	echo '<h4>'.__('No workunit was done in this incident').'</h4>';
	return;
}

foreach ($workunits as $workunit) {
	$workunit_data = get_workunit_data ($workunit['id_workunit']);
	show_workunit_data ($workunit_data, $title);
}
echo "<h3>".__("Incident details")."</h3>";
echo "<div style='margin-left: 10px; margin-top: 20px; width:90%; padding: 10px; border: 1px solid #ccc'><p>";
echo clean_output_breaks ($incident["descripcion"]);
echo "</div>";

?>
