<?php
// INTEGRIA IMS 
// http://www.integriaims.com
// ===========================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2008 Artica, info@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License
// (LGPL) as published by the Free Software Foundation; version 2

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.

/* Original POP3 code by bastien koert */
/* Taken from http://www.weberdev.com/get_example-4015.html */

global $config;

include_once ($config["homedir"]."/include/functions_workunits.php");

function incident_limits_reached ($id_group, $id_user) {
	
	$group = get_db_row_filter('tgrupo', array('id_grupo' => $id_group));
	//soft limit is open incidents.
	//hard limit is count all incidents.

	if (($group['hard_limit'] == 0) && ($group['soft_limit'] == 0)) {
		return false;
	} else {
		$countOpen = get_db_all_rows_sql('SELECT COUNT(*) AS c
			FROM tincidencia WHERE estado IN (1,2,3,4,5) AND id_grupo = ' . $id_group . ' AND id_creator = "' . $id_user . '"');
		$countAll = get_db_all_rows_sql('SELECT COUNT(*) AS c
			FROM tincidencia WHERE id_grupo = ' . $id_group . ' AND id_creator = "' . $id_user . '"');
		$countOpen = $countOpen[0]['c'];
		$countAll = $countAll[0]['c'];
		
		if (($group['hard_limit'] != 0) && ($group['hard_limit'] <= $countAll)) {
			return true;
		} else if (($group['soft_limit'] != 0) && ($group['soft_limit'] <= $countOpen)) {
			
			if ($group['enforce_soft_limit'] == 0) {
				return false;
			}
			else {
				return true;
			}
		} else {
			return false;
		
		}
	}
}

function create_incident_bymail ($user_mail, $title, $description) {
	//Get some important variables	
	$id_creator = get_db_value("id_usuario", "tusuario", "direccion", $user_mail);
	
	$group_id = get_db_value ("id_grupo", "tusuario_perfil", "id_usuario", $id_creator);


	if (!$id_creator) {
		return;
	}
	
	//Firt CHECK ticket LIMITS!
	$limit_reached = incident_limits_reached ($group_id, $id_creator);

	if ($limit_reached) {
		return;
	}
	
	//Check user ACL
	if (! give_acl ($id_creator, $group_id, "IW")) {
		return;
	}
	
	// Set default variables
	$sla_disabled = 0;
	$id_task = 0; // N/A
	$origen = 1; // User report
	$estado = 1; // New
	$priority = 2; // Medium
	$resolution = 0; // None
	$id_incident_type = 0; // None
	$email_copy = '';
	$email_notify = 0;
	$id_parent = 'NULL';
	
	$user_responsible = get_group_default_user ($group_id);
	$id_user_responsible = $id_creator;

	if ($user_responsible) {
		$id_user_responsible = $user_responsible['id_usuario'];
	}
	
	$id_inventory = get_group_default_inventory($group_id, true);
	
	// DONT use MySQL NOW() or UNIXTIME_NOW() because 
	// Integria can override localtime zone by a user-specified timezone.

	$timestamp = print_mysql_timestamp();

	$sql = sprintf ('INSERT INTO tincidencia
			(inicio, actualizacion, titulo, descripcion,
			id_usuario, origen, estado, prioridad,
			id_grupo, id_creator, notify_email, id_task,
			resolution, id_incident_type, id_parent, sla_disabled, email_copy)
			VALUES ("%s", "%s", "%s", "%s", "%s", %d, %d, %d, %d,
			"%s", %d, %d, %d, %d, %s, %d, "%s")', $timestamp, $timestamp,
			$title, $description, $id_user_responsible,
			$origen, $estado, $priority, $group_id, $id_creator,
			$email_notify, $id_task, $resolution, $id_incident_type,
			$id_parent, $sla_disabled, $email_copy);
			
	$id = process_sql ($sql, 'insert_id');

	if ($id !== false) {
		// Update inventory objects in incident
		update_incident_inventories ($id, array($id_inventory));
		if ($config['incident_reporter'] == 1)
			update_incident_contact_reporters ($id, get_parameter ('contacts'));
		
		audit_db ($config["id_user"], $config["REMOTE_ADDR"],
			"Incident created",
			"User ".$config['id_user']." created incident #".$id);
		
		incident_tracking ($id, INCIDENT_CREATED);

		// Email notify to all people involved in this incident
		if ($email_notify) {
			mail_incident ($id, $usuario, "", 0, 1);
		}
	}
}

// **************************************************************
//  message_parse : Do the Integria processing POP mail
// **************************************************************
function message_parse ($subject, $body, $from) {
	global $config;
	
	$matches = array();
	// Get the TicketID code, for example: [TicketID#2/bfd5d] 
	if (preg_match("/TicketID\#([0-9]+)\/([a-z0-9]+)\/([a-zA-Z0-9]+)/", $subject, $matches)){
		$ticket_id = $matches[1];
		$ticket_id_code = $matches[2];
		$user = $matches[3];
		if (substr(md5($ticket_id . $config["smtp_pass"]. $user),0,5) == $ticket_id_code){
			// echo "TICKET ID #$ticket_id VALIDATED !!<br>";
			
			create_workunit ($ticket_id, $body, $user, 0,  0, "", 1);
		}
	}

	// Get the NEW ticket subject, for example: [NEW/My Group] Ticket title	
	if (preg_match("/NEW (.+)/", $subject, $matches)) { 
		
		create_incident_bymail ($from, $matches[1], $body);
	}
	
	$result = array();
	$result["subject"] = $subject;
	$result["body"] = $body;
	return ($result);
} 

?>
