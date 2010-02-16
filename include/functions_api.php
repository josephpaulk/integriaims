<?php
// INTEGRIA IMS v2.1
// http://www.integriaims.com
// ===========================================================
// Copyright (c) 2007-2010 Artica, info@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License
// (LGPL) as published by the Free Software Foundation; version 2

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.

/**
 * Check a IP and see if it's on the ACL validation list
 * @param $ip
 * @return unknown_type
 */
function ip_acl_check ($ip) {
	global $config;
	
	//If set * in the list ACL return true 
	if (preg_match("/\*/",$config['api_acl']))
		return true;

	if (preg_match("/$ip/",$config['api_acl']))
		return true;
	else
		return false;
}

function api_create_incident ($id, $data){
	global $config;


	// $id is the user who create the incident
	// $data is the incident data, codified with | to split the different fields.

	// $data field order:
	// Subject/Title | Group(id) | Priority(#) | Incident Description 

	$parameters = preg_split("/\|/", $data);
	
	$grupo = $parameters[1];
	$usuario = $id;

	if (! give_acl ($usuario, $grupo, "IW")){
		audit_db ($usuario,  $_SERVER['REMOTE_ADDR'],
			"ACL Forbidden from API",
			"User ".$usuario." try to create incident");
		echo "ERROR: No access to create incident";
		exit;
	}

	// Read input variables
	$titulo = $parameters[0];
	$description = $parameters[3];
	$origen = 1; // User report
	$priority = $parameters[2];
	$id_creator = $usuario;
	$estado = 1; // new
	$resolution = 9; // In process / Pending

	$email_notify = get_db_sql ("select forced_email from tgrupo WHERE id_grupo = $grupo");
	$owner = get_db_sql ("select id_user_default from tgrupo WHERE id_grupo = $grupo");
	$id_inventory = get_db_sql ("select id_inventory_default from tgrupo WHERE id_grupo = $grupo");

	$sql = sprintf ('INSERT INTO tincidencia
			(inicio, actualizacion, titulo, descripcion,
			id_usuario, origen, estado, prioridad,
			id_grupo, id_creator, notify_email, 
			resolution)
			VALUES (NOW(), NOW(), "%s", "%s", "%s", %d, %d, %d, %d,
			"%s", %d, %d)',
			$titulo, $description, $owner,
			$origen, $estado, $priority, $grupo, $id_creator,
			$email_notify, $resolution);

	$id = process_sql ($sql, 'insert_id');
	if ($id !== false) {

		$inventories = array();
		$inventories[0] = $id_inventory;

		/* Update inventory objects in incident */
		update_incident_inventories ($id, $inventories);

		echo "OK";

		audit_db ($id_creator, $_SERVER['REMOTE_ADDR'],
			"Incident created (From API)",
			"User ".$id_creator." created incident #".$id);
		
		incident_tracking ($id, INCIDENT_CREATED);

		// Email notify to all people involved in this incident
		if ($email_notify) {
			mail_incident ($id, $usuario, "", 0, 1);
		}

	} else {
		echo "ERROR: Cannot create the incident";
	}
	
	exit;

}

?>
