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
	echo '<form id="create_html_report" method="post" target="_blank" action="index.php">';
	print_input_hidden ('id', $id);
	print_input_hidden ('sec2', 'operation/incidents/incident_tracking');
	print_input_hidden ('clean_output', 1);
	echo "</form>";
	
	echo '<form id="create_pdf_report" method="post" target="_blank" action="index.php">';
	print_input_hidden ('id', $id);
	print_input_hidden ('sec2', 'operation/incidents/incident_tracking');
	print_input_hidden ('clean_output', 1);
	print_input_hidden ('pdf_output', 1);
	echo '</form>';	
	
	echo "<div style='clear: both; width: 100%; height: 50px; padding-top: 10px'>";
	echo "<div id='button-bar-title'>";
	echo "<ul>";
	echo "<li>";
	echo '<a href="#" onClick="document.getElementById(\'create_html_report\').submit();">'.print_image("images/html.png", true, array("title" => __("HTML report"))).'</a>';
	echo "</li>";
	echo '<li>';
	echo '<a href="#" onClick="document.getElementById(\'create_pdf_report\').submit();">'.print_image("images/page_white_acrobat.png", true, array("title" => __("PDF report"))).'</a>';
	echo '</li>';	
	echo "</ul>";
	echo "</div>";
	echo "</div>";
} else {
	//Clean output we need to print incident title header :)
	
	echo '<h1>'.__('Incident').' #'.$incident["id_incidencia"].' - '.$incident['titulo']."</h1>";
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

$stats = incidents_get_incident_stats($id);

echo "<center>";
echo "<table align=center width='90%'>";
	echo "<tr>";
		echo "<td align=center width='33%'>";
			echo '<strong>'.__('General statistics').'</strong>';
		echo "</td>";
		echo "<td align=center width='33%'>";
			echo '<strong>'.__('Workunits statistics').'</strong>';
		echo "</td>";
		echo "<td align=center width='33%'>";
			echo '<strong>'.__('Activity by user (# WU)'). '</strong>';
		echo "</td>";	
	echo "</tr>";
	
	echo "<tr>";
		echo "<td align='center' style='vertical-align:top'>";
			//Print Incident detail
			echo "<table>";
			echo "<tr>";
			echo "<td><strong>".__("Open")."</strong>:</td>";
			echo "<td style='text-align:right;'>".sprintf(__("%s ago"), human_time_comparation($incident['inicio']))."</td>";
			echo "</tr>";
			echo "<tr>";
			echo "<td><strong>".__("Closed")."</strong>:</td>";
			if ($incident["estado"] == STATUS_CLOSED) {
				echo "<td style='text-align:right;'>".sprintf(__("%s ago"), human_time_comparation($incident['cierre']))."</td>";
			} else {
				echo "<td style='text-align:right;'>".__("Not yet")."</td>";
			}
			echo "</tr>";
			echo "<tr>";
			echo "<td><strong>".__("Last update")."</strong>:</td>";
			echo "<td style='text-align:right;'>".sprintf(__("%s ago"), human_time_comparation($incident['actualizacion']))."</td>";
			echo "</tr>";	
			echo "<tr>";
			echo "<td><strong>".__("Total time spent")."</strong>:</td>";
			echo "<td style='text-align:right;'>".give_human_time($stats[INCIDENT_METRIC_TOTAL_TIME],true,true,true)."</td>";
			echo "</tr>";
			echo "<tr>";
			echo "<td><strong>".__("Time no third people")."</strong>:</td>";
			echo "<td style='text-align:right;'>".give_human_time($stats[INCIDENT_METRIC_TOTAL_TIME_NO_THIRD],true,true,true)."</td>";
			echo "</tr>";
			echo "</table>";
		echo "</td>";
		echo "<td align='center' style='vertical-align:top'>";
			$workunit_count = get_incident_count_workunits ($id);
			echo "<table>";
			if ($workunit_count) {
				$work_hours = get_incident_workunit_hours ($id);
				$workunits = get_incident_workunits ($id);	
				$workunit_data = get_workunit_data ($workunits[0]['id_workunit']);
				echo "<tr>";
				echo "<td><strong>".__("Last work at")."</strong>:</td>";
				echo "<td style='text-align:right;'>".human_time_comparation ($workunit_data['timestamp'])."</td>";
				echo "</tr>";
				echo "<tr>";
				echo "<td><strong>".__("Workunits")."</strong>:</td>";
				echo "<td style='text-align:right;'>".$workunit_count."</td>";
				echo "</tr>";				
				echo "<tr>";
				echo "<td><strong>".__("Time used")."</strong>:</td>";
				echo "<td style='text-align:right;'>".give_human_time($work_hours*3600,true,true,true)."</td>";
				echo "</tr>";
				echo "<tr>";
				echo "<td><strong>".__("Reported by")."</strong>:</td>";
				$name = get_db_value ('nombre_real', 'tusuario', 'id_usuario', $workunit_data['id_user']);
				echo "<td style='text-align:right;'>".$name."</td>";
				echo "</tr>";				
			} else {
				echo "<tr>";
				echo "<td>";
				echo "<em>".__("There are not workunits")."</em>";
				echo "</td>";
				echo "</tr>";
			}
			echo "</table>";
			echo "</td>";
		echo "<td align='center' style='vertical-align:top'>";
			echo "<table>";
			echo "<tr>";
			if ($workunit_count) {
				echo "<td>".graph_incident_user_activity ($id, 200, 150, $ttl)."</td>";
			} else {
				echo "<em>".__("There are not workunits")."</em>";
			}
			echo "</tr>";
			echo "</table>";
		echo "</td>";		
	echo "</tr>";
echo "</table>";
echo "</center>";

//Get incident statistics

echo "<center>";
echo "<table align=center width='90%'>";
	echo "<tr>";
		echo "<td align=center width='33%'>";
			echo '<strong>'.__('Statistics by status').'</strong>';
		echo "</td>";
		echo "<td align=center width='33%'>";
			echo '<strong>'.__('Statistics by group').'</strong>';
		echo "</td>";	
		echo "<td align=center width='33%'>";
			echo '<strong>'.__('Statistics by user').'</strong>';
		echo "</td>";	
	echo "</tr>";
	
	echo "<tr>";
		echo "<td align='center' style='vertical-align:top'>";
			echo "<table>";
			foreach ($stats[INCIDENT_METRIC_STATUS] as $key => $value) {
				$name = get_db_value ('name', 'tincident_status', 'id', $key);
				echo "<tr>";
				echo "<td><strong>".$name."</strong>:</td>";
				echo "<td style='text-align:right;'>".give_human_time($value,true,true,true)."</td>";
				echo "</tr>";
			}
			echo "</table>";
		echo "</td>";
		echo "<td align='center' style='vertical-align:top'>";
			echo "<table>";
			foreach ($stats[INCIDENT_METRIC_GROUP] as $key => $value) {
				$name = get_db_value ('nombre', 'tgrupo', 'id_grupo', $key);
				echo "<tr>";
				echo "<td><strong>".$name."</strong>:</td>";
				echo "<td style='text-align:right;'>".give_human_time($value,true,true,true)."</td>";
				echo "</tr>";
			}
			echo "</table>";
		echo "</td>";	
		echo "<td align='center' style='vertical-align:top'>";
			echo "<table>";
			foreach ($stats[INCIDENT_METRIC_USER] as $key => $value) {
				$name = get_db_value ('nombre_real', 'tusuario', 'id_usuario', $key);
				echo "<tr>";
				echo "<td><strong>".$name."</strong>:</td>";
				echo "<td style='text-align:right;'>".give_human_time($value,true,true,true)."</td>";
				echo "</tr>";
			}
			echo "</table>";
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
