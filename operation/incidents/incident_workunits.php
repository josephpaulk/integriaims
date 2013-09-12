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


		incident_tracking ($id, INCIDENT_STATUS_CHANGED, 3);
	
		incident_tracking ($id, INCIDENT_USER_CHANGED, $config["id_user"]);

		$metric_values = array(INCIDENT_METRIC_STATUS => 3,
						INCIDENT_METRIC_USER => $config["id_user"]);

		incidents_add_incident_stat ($id, $metric_values);
	} else {
		$sql = sprintf ('UPDATE tincidencia SET affected_sla_id = 0, actualizacion = "%s" WHERE id_incidencia = %d', $timestamp, $id);
	}

	process_sql ($sql);

	create_workunit ($id, $nota, $config["id_user"], $timeused, $have_cost, $profile, $public);

	$result_msg = '<h3 class="suc">'.__('Workunit added successfully').'</h3>';

	echo $result_msg;
}

//Add workunit form
//echo "<h3>".__('Add workunit')."</h3>";

$table->width = '100%';
$table->class = 'integria_form';
$table->colspan = array ();
$table->colspan[1][0] = 6;
$table->colspan[2][0] = 6;
$table->data = array ();
$table->size = array();
$table->style = array();
$table->style[0] = 'vertical-align: top; padding-top: 10px;';
$table->style[1] = 'vertical-align: top; padding-top: 10px;';
$table->style[2] = 'vertical-align: top;';
$table->style[3] = 'vertical-align: top;';
$table->style[4] = 'vertical-align: top;';
$table->style[5] = 'vertical-align: top;';
$table->data[0][0] = print_image('images/calendar_orange.png', true) . '&nbsp' . print_mysql_timestamp(0, "Y-m-d");
$table->data[0][1] = print_image('images/clock_orange.png', true) . '&nbsp' . print_mysql_timestamp(0, "H:i:s");
$table->data[0][2] = combo_roles (1, 'work_profile', __('Profile'), true);
$table->data[0][3] = print_input_text ("duration", $config["iwu_defaultime"], '', 7,  10, true, __('Time used'));
$table->data[0][4] = print_checkbox ('have_cost', 1, false, true, __('Have cost'));
$table->data[0][5] = print_checkbox ('public', 1, true, true, __('Public'));

$table->data[1][0] = print_textarea ('nota', 10, 70, '', "style='resize:none;'", true, __('Description'));

$button = '<div style="width: 100%; text-align: right;">';
$button .= '<span id="sending_data" style="display: none;">' . __('Sending data...') . '<img src="images/spinner.gif" /></span>';
$button .= print_submit_button (__('Add'), 'addnote', false, 'class="sub create"', true);
$button .= print_input_hidden ('insert_workunit', 1, true);
$button .= print_input_hidden ('id', $id, true);
$button .= '</div>';

$table->data[2][0] = $button;

echo '<form id="form-add-workunit" method="post" action="index.php?sec=incidents&sec2=operation/incidents/incident_dashboard_detail&id='.$id.'&tab=workunits#incident-operations">';

echo "<div style='width: 98%;'>";
print_table ($table);
echo "</div>";

echo "</form>";

echo '<ul class="ui-tabs-nav">';
echo '<li class="ui-tabs-title">';
echo "<h2>".__('Workunits')."</h2>";
echo '</li>';
echo '</ul>';

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

echo '<ul class="ui-tabs-nav">';
echo '<li class="ui-tabs-title">';
echo "<h2>".__('Incident details')."</h2>";
echo '</li>';
echo '</ul>';

echo "<div class='incident_details'><p>";
echo clean_output_breaks ($incident["descripcion"]);
echo "</div>";

?>
