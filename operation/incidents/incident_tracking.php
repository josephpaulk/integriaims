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
$check_acl = enterprise_hook("incidents_check_incident_acl", array($incident));
$external_check = enterprise_hook("manage_external", array($incident));

if (($check_acl !== ENTERPRISE_NOT_HOOK && !$check_acl) || ($external_check !== ENTERPRISE_NOT_HOOK && !$external_check)) {
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation","Trying to access to ticket #".$id);
	include ("general/noaccess.php");
	exit;
}

//Clean output we need to print incident title header :)
if ($clean_output) {
	echo '<h1 class="ticket_clean_report_title">'.__("Statistics")."</h1>";
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

if (!$stats) {
	echo "<table width='99%'>";
		echo "<tr>";
			echo "<td style='vertical-align:top; width: 33%;'>";
			echo __("There isn't statistics for this ticket");
			echo "</td>";
		echo "</tr>";
	echo "</table>";
} else {

	echo "<table width='99%'>";
		echo "<tr>";
			echo "<td style='vertical-align:top; width: 33%;'>";
				//Print Incident detail
				$incident_detail = "<table class='details_table alternate'>";
				$incident_detail .= "<tr>";
				$incident_detail .= "<td><strong>".__("Open")."</strong>:</td>";
				$incident_detail .= "<td style='text-align:right;'>".sprintf(__("%s ago"), human_time_comparation($incident['inicio']))."</td>";
				$incident_detail .= "</tr>";
				$incident_detail .= "<tr>";
				$incident_detail .= "<td><strong>".__("Closed")."</strong>:</td>";
				if ($incident["estado"] == STATUS_CLOSED) {
					$incident_detail .= "<td style='text-align:right;'>".sprintf(__("%s ago"), human_time_comparation($incident['cierre']))."</td>";
				} else {
					$incident_detail .= "<td style='text-align:right;'>".__("Not yet")."</td>";
				}
				$incident_detail .= "</tr>";
				$incident_detail .= "<tr>";
				$incident_detail .= "<td><strong>".__("Last update")."</strong>:</td>";
				$incident_detail .= "<td style='text-align:right;'>".sprintf(__("%s ago"), human_time_comparation($incident['actualizacion']))."</td>";
				$incident_detail .= "</tr>";	
				$incident_detail .= "<tr>";
				$incident_detail .= "<td><strong>".__("Total time spent")."</strong>:</td>";
				$incident_detail .= "<td style='text-align:right;'>".give_human_time($stats[INCIDENT_METRIC_TOTAL_TIME],true,true,true)."</td>";
				$incident_detail .= "</tr>";
				$incident_detail .= "<tr>";
				$incident_detail .= "<td><strong>".__("Time no third people")."</strong>:</td>";
				$incident_detail .= "<td style='text-align:right;'>".give_human_time($stats[INCIDENT_METRIC_TOTAL_TIME_NO_THIRD],true,true,true)."</td>";
				$incident_detail .= "</tr>";
				$incident_detail .= "</table>";
				
				echo print_container('incident_tracking_detail', __('General statistics'), $incident_detail, 'no', true, '20px');
			echo "</td>";
			echo "<td style='vertical-align:top; width: 33%;'>";
				$workunit_count = get_incident_count_workunits ($id);
				$workunit_detail = "<table class='details_table alternate'>";
				if ($workunit_count) {
					$work_hours = get_incident_workunit_hours ($id);
					$workunits = get_incident_workunits ($id);	
					$workunit_data = get_workunit_data ($workunits[0]['id_workunit']);
					$workunit_detail .= "<tr>";
					$workunit_detail .= "<td><strong>".__("Last work at")."</strong>:</td>";
					$workunit_detail .= "<td style='text-align:right;'>".human_time_comparation ($workunit_data['timestamp'])."</td>";
					$workunit_detail .= "</tr>";
					$workunit_detail .= "<tr>";
					$workunit_detail .= "<td><strong>".__("Workunits")."</strong>:</td>";
					$workunit_detail .= "<td style='text-align:right;'>".$workunit_count."</td>";
					$workunit_detail .= "</tr>";				
					$workunit_detail .= "<tr>";
					$workunit_detail .= "<td><strong>".__("Time used")."</strong>:</td>";
					$workunit_detail .= "<td style='text-align:right;'>".give_human_time($work_hours*3600,true,true,true)."</td>";
					$workunit_detail .= "</tr>";
					$workunit_detail .= "<tr>";
					$workunit_detail .= "<td><strong>".__("Reported by")."</strong>:</td>";
					$name = get_db_value ('nombre_real', 'tusuario', 'id_usuario', $workunit_data['id_user']);
					$workunit_detail .= "<td style='text-align:right;'>".$name."</td>";
					$workunit_detail .= "</tr>";				
				} else {
					$workunit_detail .= "<tr>";
					$workunit_detail .= "<td>";
					$workunit_detail .= "<em>".__("There are not workunits")."</em>";
					$workunit_detail .= "</td>";
					$workunit_detail .= "</tr>";
				}
				$workunit_detail .= "</table>";
				$workunit_detail .= "</td>";
				
				echo print_container('incident_tracking_workunit_detail', __('Workunits statistics'), $workunit_detail, 'no', true, '20px');
			echo "<td style='vertical-align:top; width: 33%;'>";

				if ($workunit_count) {
					$workunit_graphic = graph_incident_user_activity ($id, 200, 150, $ttl);
				} else {
					$workunit_graphic = "<em>".__("There are not workunits")."</em>";
				}
				$workunit_graphic = '<div class="pie_frame">' . $workunit_graphic . '</div>';
				
				echo print_container('incident_tracking_workunit_graphic', __('Activity by user (# WU)'), $workunit_graphic, 'no', true, '20px');

			echo "</td>";		
		echo "</tr>";
	echo "</table>";

	//Get incident statistics
	echo "<table width='99%'>";
		echo "<tr>";
			echo "<td style='vertical-align:top;width: 33%;'>";
				$tracking_status = "<table class='details_table alternate'>";
				foreach ($stats[INCIDENT_METRIC_STATUS] as $key => $value) {
					$name = get_db_value ('name', 'tincident_status', 'id', $key);
					$tracking_status .= "<tr>";
					$tracking_status .= "<td><strong>".$name."</strong>:</td>";
					$tracking_status .= "<td style='text-align:right;'>".give_human_time($value,true,true,true)."</td>";
					$tracking_status .= "</tr>";
				}
				$tracking_status .= "</table>";
				
				echo print_container('incident_tracking_status', __('Statistics by status'), $tracking_status, 'no', true, '20px');
			echo "</td>";
			echo "<td style='vertical-align:top;width: 33%;'>";
				$tracking_group = "<table class='details_table alternate'>";
				foreach ($stats[INCIDENT_METRIC_GROUP] as $key => $value) {
					$name = get_db_value ('nombre', 'tgrupo', 'id_grupo', $key);
					$tracking_group .= "<tr>";
					$tracking_group .= "<td><strong>".$name."</strong>:</td>";
					$tracking_group .= "<td style='text-align:right;'>".give_human_time($value,true,true,true)."</td>";
					$tracking_group .= "</tr>";
				}
				$tracking_group .= "</table>";
				
				echo print_container('incident_tracking_group', __('Statistics by group'), $tracking_group, 'no', true, '20px');
			echo "</td>";	
			echo "<td style='vertical-align:top;width: 33%;'>";
				$tracking_user = "<table class='details_table alternate'>";
				foreach ($stats[INCIDENT_METRIC_USER] as $key => $value) {
					$name = get_db_value ('nombre_real', 'tusuario', 'id_usuario', $key);
					$tracking_user .= "<tr>";
					$tracking_user .= "<td><strong>".$name."</strong>:</td>";
					$tracking_user .= "<td style='text-align:right;'>".give_human_time($value,true,true,true)."</td>";
					$tracking_user .= "</tr>";
				}
				$tracking_user .= "</table>";
				
				echo print_container('incident_tracking_user', __('Statistics by owner'), $tracking_user, 'no', true, '20px');
			echo "</td>";	
		echo "</tr>";
	echo "</table>";
}

$trackings = get_db_all_rows_field_filter ('tincident_track', 'id_incident', $id, 'timestamp DESC');

if ($trackings !== false) {
	unset($table);
	$table->width = "99%";
	$table->class = 'listing';
	$table->data = array ();
	$table->head = array ();
	$table->head[0] = __('Description');
	$table->head[1] = __('User');
	$table->head[2] = __('Date');
	$table->style[2] = "width: 150px";

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
