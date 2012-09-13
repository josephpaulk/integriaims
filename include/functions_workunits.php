<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2011 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.


function check_workunit_permission ($id_workunit) {
	global $config;
	
	// Delete workunit with ACL / Project manager check
	$workunit = get_db_row ('tworkunit', 'id', $id_workunit);
	if ($workunit === false)
		return false;
	
	$id_user = $workunit["id_user"];
	$id_task = get_db_value ("id_task", "tworkunit_task", "id_workunit", $workunit["id"]);
	$id_project = get_db_value ("id_project", "ttask", "id", $id_task);
	if ($id_user != $config["id_user"]
		&& ! give_acl ($config["id_user"], 0,"PM")
		&& ! project_manager_check ($id_project))
		return false;
	
	return true;
}

function delete_task_workunit ($id_workunit) {
	global $config;
	
	if (! check_workunit_permission ($id_workunit))
		return false;
	
	$sql = sprintf ('DELETE FROM tworkunit
		WHERE id = %d', $id_workunit);
	process_sql ($sql);
	$sql = sprintf ('DELETE FROM tworkunit_task
		WHERE id_workunit = %d', $id_workunit);
	return (bool) process_sql ($sql);
}

function lock_task_workunit ($id_workunit) {
	global $config;
	
	if (! check_workunit_permission ($id_workunit))
		return false;
	$sql = sprintf ('UPDATE tworkunit SET locked = "%s" WHERE id = %d',
		$config['id_user'], $id_workunit);
	return (bool) process_sql ($sql);
}

function create_workunit ($incident_id, $wu_text, $user, $timeused = 0, $have_cost = 0, $profile = "", $public = 1, $send_email = 1) {
	$fecha = print_mysql_timestamp();
	$sql = sprintf ('UPDATE tincidencia
		SET affected_sla_id = 0, actualizacion = "%s"  
		WHERE id_incidencia = %d', $fecha, $incident_id);
	process_sql ($sql);
	
	incident_tracking ($incident_id, INCIDENT_WORKUNIT_ADDED);
	
	// Add work unit if enabled
	$sql = sprintf ('INSERT INTO tworkunit (timestamp, duration, id_user, description, public)
			VALUES ("%s", %.2f, "%s", "%s", %d)', $fecha, $timeused, $user, $wu_text, $public);
	$id_workunit = process_sql ($sql, "insert_id");
	$sql = sprintf ('INSERT INTO tworkunit_incident (id_incident, id_workunit)
			VALUES (%d, %d)',
			$incident_id, $id_workunit);
	$res = process_sql ($sql);
	if ($res !== false) {
		// Email notify to all people involved in this incident
		$email_notify = get_db_value ("notify_email", "tincidencia", "id_incidencia", $incident_id);
		if (($email_notify == 1) AND ($send_email == 1)) {
			mail_incident ($incident_id, $user, $wu_text, $timeused, 10, $public);
		}
	}
}


