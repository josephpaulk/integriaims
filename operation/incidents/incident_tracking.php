<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// Load global vars

global $config;

include_once ("include/functions_graph.php");

check_login ();

$id_grupo = "";
$creacion_incidente = "";

$id = (int) get_parameter ('id');
$clean_output = get_parameter('clean_output');
if (! $id) {
	require ("general/noaccess.php");
	exit;
}

$incident = get_db_row ('tincidencia', 'id_incidencia', $id);

//user with IR and incident creator see the information
if (! give_acl ($config['id_user'], $incident['id_grupo'], 'IR') 
	&& ($incident['id_creator'] != $config['id_user'])) {
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation","Trying to access to incident #".$id);
	include ("general/noaccess.php");
	exit;
}

/* Add a button to generate HTML reports */
if (!$clean_output) {
	echo '<form method="post" target="_blank" action="index.php" style="clear: both">';

	echo '<div style="width:90%; text-align: right;">';
	print_input_hidden ('id', $id);
	print_input_hidden ('sec2', 'operation/incidents/incident_tracking');
	print_input_hidden ('clean_output', 1);
	print_submit_button (__('HTML report'), 'incident_report', false,
		'class="sub report"');
	echo "</form>";

	/* Add a button to generate HTML reports */
	echo '<form method="post" target="_blank" action="index.php" style="clear: both">';
	print_input_hidden ('id', $id);
	print_input_hidden ('sec2', 'operation/incidents/incident_tracking');
	print_input_hidden ('clean_output', 1);
	print_input_hidden ('pdf_output', 1);
	print_submit_button (__('PDF report'), 'incident_report', false,
		'class="sub pdfreport"');
	echo '</div></form>';
}

$a_day = 24*3600;

$fields = array($a_day => "1 day",
				2*$a_day => "2 days",
				7*$a_day => "1 week",
				14*$a_day => "2 weeks",
				30*$a_day => "1 month");

$period = get_parameter("period", $a_day);
$ttl = 1;

if ($clean_output) {
	$ttl = 2;
}

echo "<center>";
echo "<table align=center>";
	echo "<tr>";
		echo "<td align=center>";
			echo '<strong>'.__('Details for incident').'</strong>';
		echo "</td>";
		echo "<td align=center>";
			echo '<strong>'.__('Activity by user (# WU)'). '</strong>';
		echo "</td>";	
	echo "</tr>";
	
	echo "<tr>";
		echo "<td align=center>";
			//Print Incident detail
			echo incident_details_list ($id,true);
		echo "</td>";
		echo "<td align=center>";
			echo graph_incident_user_activity ($id, 200, 200, $ttl);
		echo "</td>";		
	echo "</tr>";
echo "</table>";
echo "</center>";

$trackings = get_db_all_rows_field_filter ('tincident_track', 'id_incident', $id, 'timestamp DESC');

if ($trackings !== false) {
	$table->width = "90%";
	$table->class = 'listing';
	$table->data = array ();
	$table->head = array ();
	$table->head[1] = __('Description');
	$table->head[2] = __('User');
	$table->head[3] = __('Date');
	
	foreach ($trackings as $tracking) {
		$data = array ();
		
		$data[0] = $tracking['description'];
		$data[1] = dame_nombre_real ($tracking['id_user']);
		$data[2] = $tracking['timestamp'];
		
		array_push ($table->data, $data);
	}
	echo "<center>";
	print_table ($table);
	echo "</center>";
} else {
	echo __('No data available');
}
?>
