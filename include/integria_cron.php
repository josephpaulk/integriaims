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

include ("config.php");
require_once ($config["homedir"].'/include/functions_calendar.php');

$config["id_user"] = 'System';
$now = time ();
$compare_timestamp = date ("Y-m-d H:i:s", $now -  $config["notification_period"]);
$human_notification_period = give_human_time ($config["notification_period"]);


/**
 * This check is executed once per day and can do several different subtasks
 * like email notify ending tasks or projects, events for this day, etc
 *
 */

function run_daily_check () {
	$current_date = date ("Y-m-d h:i:s");

	// Create a mark in event log
	process_sql ("INSERT INTO tevent (type, timestamp) VALUES ('DAILY_CHECK', '$current_date') ");

	// Do checks
	run_calendar_check();
	run_project_check();
	run_task_check();
}


/**
 * This check notify user by mail if in current day there agenda items 
 *
 */

function run_calendar_check () {
	global $config;

	$now = date ("Y-m-d");
	$events = get_event_date ($now, 1, "_ANY_");

	foreach ($events as $event){
		list ($timestamp, $event_data, $user_event) = split ("\|", $event);
		$user = get_db_row ("tusuario", "id_usuario", $user_event);
		$nombre = $user['nombre_real'];
		$email = $user['direccion'];
		$mail_description = "There is an Integria agenda event planned for today at ($timestamp): \n\n$event_data\n\n";
		integria_sendmail ($email, "[".$config["sitename"]."] Calendar event for today ",  $mail_description );
	}
}

/**
 * This check notify user by mail if in current day there are ending projects
 *
 */

function run_project_check () {
	global $config;

	$now = date ("Y-m-d");
	$projects = get_project_end_date ($now, 0, "_ANY_");

	foreach ($projects as $project){
		list ($pname, $idp, $pend, $owner) = split ("\|", $project);
		$user = get_db_row ("tusuario", "id_usuario", $owner);
		$nombre = $user['nombre_real'];
		$email = $user['direccion'];
		$mail_description = "There is an Integria project ending today ($pend): $pname. \n\nUse this link to review the project information:\n";
		$mail_description .= $config["base_url"]. "/index.php?sec=projects&sec2=operation/projects/task&id_project=$idp\n";

		integria_sendmail ($email, "[".$config["sitename"]."] Project ends today ($pname)",  $mail_description );
	}
}


/**
 * This check notify user by mail if in current day there are ending tasks
 *
 */

function run_task_check () {
	global $config;

	$now = date ("Y-m-d");
	$baseurl = 
	$tasks = get_task_end_date_by_user ($now);
	
	foreach ($tasks as $task){
		list ($tname, $idt, $tend, $pname, $user) = split ("\|", $task);
		$user_row = get_db_row ("tusuario", "id_usuario", $user);
		$nombre = $user_row['nombre_real'];
		$email = $user_row['direccion'];
		
		$mail_description = "There is a task ending today ($tend) : $pname / $tname \n\nUse this link to review the project information:\n";
		$mail_description .= $config["base_url"]. "/index.php?sec=projects&sec2=operation/projects/task_detail&id_task=$idt&operation=view\n";

		integria_sendmail ($email, "[".$config["sitename"]."] Task ends today ($tname)",  $mail_description );
	}
}



/**
 * Check if daily task has been executed
 *
 */
function check_daily_task () {
	$current_date = date ("Y-m-d");
	$current_date .= " 23:59:59";
	$result = get_db_sql ("SELECT COUNT(id) FROM tevent WHERE type = 'DAILY_CHECK' AND timestamp < '$current_date'");
	if ($result > 0)
		return 0; // Daily check has been executed yet
	else
		return 1; // need to run daily check
}

/**
 * Check an SLA min response value on an incident and send emails if needed.
 *
 * @param $incident Incident array to check
 */
function check_sla_min ($incident) {
	check_incident_sla_min_response ($incident['id_incidencia']);
}

/**
 * Check an SLA max response value on an incident and send emails if needed.
 *
 * @param $incident Incident array to check
 */
function check_sla_max ($incident) {
	check_incident_sla_max_response ($incident['id_incidencia']);
}

$incidents = get_db_all_rows_sql ('SELECT * FROM tincidencia
	WHERE sla_disabled = 0 AND estado NOT IN (6,7)');
if ($incidents === false)
	$incidents = array ();
foreach ($incidents as $incident) {
	check_sla_min ($incident);
	check_sla_max ($incident);
}

$slas = get_slas ();
foreach ($slas as $sla) {
	$sql = sprintf ('SELECT id FROM tinventory WHERE id_sla = %d', $sla['id']);
	$inventories = get_db_all_rows_sql ($sql);
	if ($inventories === false)
		$inventories = array ();
	
	foreach ($inventories as $inventory) {
		$sql = sprintf ('SELECT tincidencia.id_incidencia
			FROM tincidencia, tincident_inventory
			WHERE tincidencia.id_incidencia = tincident_inventory.id_incident
			AND tincident_inventory.id_inventory = %d
			AND estado NOT IN (6,7)', $inventory['id']);
		
		$opened_incidents = get_db_sql ($sql);
		if ($opened_incidents <= $sla['max_incidents']) 
			continue;
		
		/* There are too many open incidents */
		$sql = sprintf ('UPDATE tincidencia
			SET affected_sla_id = %d
			WHERE id_incidencia = %d',
			$id_sla,
			$incident['id_incidencia']);
	}
}

// Daily check
if (check_daily_task() == 1)
	run_daily_check();

?>
