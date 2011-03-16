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

/**
 * Create an incident
 * @param $return_type xml or csv
 * @param $user user who call function
 * @param $params array (title, group, priority, description)
 * @return unknown_type
 */

function api_create_incident ($return_type, $user, $params){
	global $config;

	// $id is the user who create the incident

    // Check we have proper parameters
    if (!isset($params[3]))
        return;

	// If no user, return
    if ($user == "")
        return;

	$group = $params[1];

	if (! give_acl ($user, $group, "IW")){
		audit_db ($user,  $_SERVER['REMOTE_ADDR'],
			"ACL Forbidden from API",
			"User ".$user." try to create incident");
		echo "ERROR: No access to create incident";
		exit;
	}

	// Read input variables
	$title = $params[0];
	$description = $params[3];
	$source = 1; // User report
	$priority = $params[2];
	$id_creator = $user;
	$status = 1; // new
	$resolution = 9; // In process / Pending

	$email_notify = get_db_sql ("select forced_email from tgrupo WHERE id_grupo = $group");
	$owner = get_db_sql ("select id_user_default from tgrupo WHERE id_grupo = $group");
	$id_inventory = get_db_sql ("select id_inventory_default from tgrupo WHERE id_grupo = $group");
	$timestamp = print_mysql_timestamp();

	$sql = sprintf ('INSERT INTO tincidencia
			(inicio, actualizacion, titulo, descripcion,
			id_usuario, origen, estado, prioridad,
			id_grupo, id_creator, notify_email, 
			resolution)
			VALUES ("%s", "%s", "%s", "%s", "%s", %d, %d, %d, %d,
			"%s", %d, %d)', $timestamp, $timestamp, $title, $description, $owner,
			$source, $status, $priority, $group, $id_creator,
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
			mail_incident ($id, $user, "", 0, 1);
		}

	} else {
		echo "ERROR: Cannot create the incident";
	}
	
	exit;

}

function api_get_incidents ($return_type, $user, $params){
	$filter = array();
	
	$filter['string'] = $params[0];
	$filter['status'] = $params[1];
	$filter['id_group'] = $params[2];
	
	// If the user is admin, all the incidents are showed
	if(!get_admin_user ($user)) {
		$filter['id_user_or_creator'] = $user;
	}
	
	$result = filter_incidents ($filter);
	
	if($result === false) {
		return "<xml></xml>";
	}
	
	$ret = '';
	
	if($return_type == 'xml') {
		$ret = "<xml>\n";
	}

	foreach($result as $index => $item) {
		$item['workunits_hours'] = get_incident_workunit_hours ($item['id_incidencia']);
		$item['workunits_count'] = get_incident_count_workunits ($item['id_incidencia']);
		switch($return_type) {
			case "xml":
				$ret .= "<incident>\n";
				foreach($item as $key => $value) {
					if(!is_numeric($key)) {
						$ret .= "<".$key.">".$value."</".$key.">\n";
					}
				}
				$ret .= "</incident>\n";
				break;
			case "csv":
				$ret .= array_to_csv($item);
				break;
		}
	}
	
	if($return_type == 'xml') {
		$ret .= "</xml>\n";
	}

	return $ret;
}

function array_to_csv($array) {
	// output up to 5MB is kept in memory, if it becomes bigger it will automatically be written to a temporary file
	$csv = fopen('php://temp/maxmemory:'. (5*1024*1024), 'r+');

	fputcsv($csv, $array);

	rewind($csv);

	// put it all in a variable
	return stream_get_contents($csv);
}

function api_get_incident_details ($return_type, $user, $id_incident){
	$filter = array();
	
	// If the user is admin, all the incidents are showed
	if(!get_admin_user ($user)) {
		$filter['id_user_or_creator'] = $user;
	}
	
	$filter['id_group'] = 1;
	
	$result = get_incident ($id_incident);
	
	if($result === false) {
		return '';
	}
	
	$ret = '';
	
	switch($return_type) {
		case 'xml':
				$ret = "<xml>\n";

				foreach($result as $key => $value) {
					if(!is_numeric($key)) {
						$ret .= "<".$key.">".$value."</".$key.">\n";
					}
				}
					
				$ret .= "</xml>\n";
				break;
		case 'csv':
				$ret = array_to_csv($result);
				break;
	}
	
	return $ret;
}

function api_update_incident ($return_type, $user, $id_incident, $values){	
	$values = array('titulo' => $values[1], 'descripcion' => $values[2]);
	
	
	process_sql_update ('tincidencia', $values, array('id_incidencia' => $id_incident));
}

function api_get_incident_tracking ($return_type, $user, $id_incident){
	$filter = array();
	
	$result = get_incident_tracking($id_incident);
	
	if($result === false) {
		return '';
	}

	$ret = '';
	
	if($return_type == 'xml') {
		$ret = "<xml>\n";
	}

	foreach($result as $index => $item) {
		switch($return_type) {
			case "xml":
				$ret .= "<tracking>\n";
				foreach($item as $key => $value) {
					if(!is_numeric($key)) {
						$ret .= "<".$key.">".$value."</".$key.">\n";
					}
				}
				$ret .= "</tracking>\n";
				break;
			case "csv":
				$ret .= array_to_csv($item);
				break;
		}
	}
	
	if($return_type == 'xml') {
		$ret .= "</xml>\n";
	}
	
	return $ret;
}

function api_get_incident_workunits ($return_type, $user, $id_incident){
	$filter = array();
	
	$workunits = get_incident_workunits ($id_incident);
	
	if($workunits === false) {
		return '';
	}
	
	$result = array();
	foreach($workunits as $wu) {
		$result[$wu['id']] = get_workunit_data($wu['id_workunit']);
	}

	if($result === false) {
		return '';
	}
	
	$ret = '';
	
	if($return_type == 'xml') {
		$ret = "<xml>\n";
	}

	foreach($result as $index => $item) {
		
		switch($return_type) {
			case "xml":
				$ret .= "<workunit>\n";
				foreach($item as $key => $value) {
					if(!is_numeric($key)) {
						$ret .= "<".$key.">".$value."</".$key.">\n";
					}
				}
				$ret .= "</workunit>\n";
				break;
			case "csv":
				$ret .= array_to_csv($item);
				break;
		}
	}
	
	if($return_type == 'xml') {
		$ret .= "</xml>\n";
	}
	
	return $ret;
}

function api_get_incident_files ($return_type, $user, $id_incident){
	$filter = array();
	
	$result = get_incident_files ($id_incident);
	
	if($result === false) {
		return '';
	}

	$ret = '';
	
	if($return_type == 'xml') {
		$ret = "<xml>\n";
	}

	foreach($result as $index => $item) {
		switch($return_type) {
			case "xml":
				$ret .= "<file>\n";
				foreach($item as $key => $value) {
					if(!is_numeric($key)) {
						$ret .= "<".$key.">".$value."</".$key.">\n";
					}
				}
				$ret .= "</file>\n";
				break;
			case "csv":
				$ret .= array_to_csv($item);
				break;
		}
	}
	
	if($return_type == 'xml') {
		$ret .= "</xml>\n";
	}
	
	return $ret;
}

function api_download_file ($return_type, $user, $id_file){
	global $config;

	$data = get_db_row ("tattachment", "id_attachment", $id_file);
	
	$fileLocation = $config["homedir"]."/attachment/".$data["id_attachment"]."_".$data["filename"];
	
	echo base64_encode(file_get_contents($fileLocation));
}

function api_get_incidents_resolutions ($return_type, $user){
	$resolutions = get_db_all_rows_in_table('tincident_resolution');
	
	$ret = '';
	
	if($return_type == 'xml') {
		$ret = "<xml>\n";
	}
	
	foreach($resolutions as $index => $item) {
		switch($return_type) {
			case "xml":
				$ret .= "<resolution>\n";
				foreach($item as $key => $value) {
					if(!is_numeric($key)) {
						$ret .= "<".$key.">".$value."</".$key.">\n";
					}
				}
				$ret .= "</resolution>\n";
				break;
			case "csv":
				$ret .= array_to_csv($item);
				break;
		}
	}
	
	if($return_type == 'xml') {
		$ret .= "</xml>\n";
	}
	
	return $ret;
}

function api_get_incidents_status ($return_type, $user){
	$resolutions = get_db_all_rows_in_table('tincident_status');
	
	$ret = '';
	
	if($return_type == 'xml') {
		$ret = "<xml>\n";
	}
	
	foreach($resolutions as $index => $item) {
		switch($return_type) {
			case "xml":
				$ret .= "<status>\n";
				foreach($item as $key => $value) {
					if(!is_numeric($key)) {
						$ret .= "<".$key.">".$value."</".$key.">\n";
					}
				}
				$ret .= "</status>\n";
				break;
			case "csv":
				$ret .= array_to_csv($item);
				break;
		}
	}
	
	if($return_type == 'xml') {
		$ret .= "</xml>\n";
	}
	
	return $ret;
}

function api_get_incidents_sources ($return_type, $user){
	$resolutions = get_db_all_rows_in_table('tincident_origin');
	
	$ret = '';
	
	if($return_type == 'xml') {
		$ret = "<xml>\n";
	}
	
	foreach($resolutions as $index => $item) {
		switch($return_type) {
			case "xml":
				$ret .= "<source>\n";
				foreach($item as $key => $value) {
					if(!is_numeric($key)) {
						$ret .= "<".$key.">".$value."</".$key.">\n";
					}
				}
				$ret .= "</source>\n";
				break;
			case "csv":
				$ret .= array_to_csv($item);
				break;
		}
	}
	
	if($return_type == 'xml') {
		$ret .= "</xml>\n";
	}
	
	return $ret;
}

function api_get_groups ($return_type, $user, $return_group_all){
	
	if($return_group_all) {
		$groups = get_db_all_rows_in_table('tgrupo');
	}
	else {
		$groups = get_db_all_rows_filter('tgrupo', 'id_grupo <> 1');
	}
	
	$groups = get_user_groups($user);
	
	if(!$return_group_all) {
		unset($groups[1]);
	}
	
	$ret = '';
	
	if($return_type == 'xml') {
		$ret = "<xml>\n";
	}
	
	foreach($groups as $index => $item) {
		switch($return_type) {
			case "xml":
				$ret .= "<group>\n";
				$ret .= "<id>".$index."</id>\n";
				$ret .= "<name>".$item."</name>\n";
				$ret .= "</group>\n";
				break;
			case "csv":
				$ret .= array_to_csv($item);
				break;
		}
	}
	
	if($return_type == 'xml') {
		$ret .= "</xml>\n";
	}

	return $ret;
}

function api_get_users ($return_type, $user){	
	$users = get_user_visible_users ($user);

	$ret = '';
	
	if($return_type == 'xml') {
		$ret = "<xml>\n";
	}
	
	foreach($users as $index => $item) {
		switch($return_type) {
			case "xml":
				$ret .= "<id_user>".$index."</id_user>\n";
				break;
			case "csv":
				$ret .= array_to_csv($item);
				break;
		}
	}
	
	if($return_type == 'xml') {
		$ret .= "</xml>\n";
	}

	return $ret;
}

function api_get_stats ($return_type, $param, $token){	
    $filter = array ();
    $param = explode ($token, $param);

    if (isset($param[0]))
        $filter['metric'] = $param[0];
    else 
        return; // No valid metric passed as parameter

    if (isset($param[1]))
        $filter['string'] = $param[1];
    else 
        $filter["string"]= "";

    if (isset($param[2]))
        $filter['status'] = $param[2];
    else 
        $filter["status"] = "1,2,3,4,5,6,7";

    // If we need closed incidents, status is fixed to 5 and 6 status
    if ($filter["metric"] == "closed"){
        $filter["status"] = "6,7";
    }

    if ($filter["metric"] == "avg_life"){
        $filter["status"] = "1,2,3,4,5,6,7";
    }

    if ($filter["metric"] == "avg_scoring"){
        $filter["status"] = "1,2,3,4,5,6,7";
    }

    if (isset($param[3]))
        $filter['id_user'] = $param[3];
    else 
        $filter["id_user"]= "";

    if (isset($param[4]))
        $filter['id_group'] = $param[4];
    else 
        $filter["id_group"]= 1;

    if (isset($param[5]))
        $filter['id_company'] = $param[5];
    else 
        $filter["id_company"]= 0;


    if (isset($param[6]))
        $filter['id_product'] = $param[6];
    else 
        $filter["id_product"]= 0;

    if (isset($param[7]))
        $filter['id_inventory'] = $param[7];
    else 
        $filter["id_inventory"]= 0;

    // No values defined for other filters available but not used:

    $filter['priority'] = (int) get_parameter ('search_priority', -1);
    $filter['serial_number'] = (string) get_parameter ('search_serial_number');
    $filter['id_building'] = (int) get_parameter ('search_id_building');
    $filter['sla_fired'] = (bool) get_parameter ('search_sla_fired');
    $filter['id_incident_type'] = (int) get_parameter ('search_id_incident_type');
    $filter['first_date'] = (string) get_parameter ('search_first_date');
    $filter['last_date'] = (string) get_parameter ('search_last_date');

    $incidents = filter_incidents ($filter);
    $stats = get_incidents_stats ($incidents);

	$ret = '';
	
	if($return_type == 'xml') {
		$ret = "<xml>\n";
        $ret .= "<data>";
	}

    switch ($filter['metric']){
    case "total_incidents": 
        $ret .= $stats["total_incidents"];
        break;
    case "opened": 
        $ret .= $stats["opened"];
        break;
    case "closed": 
        $ret .= $stats["closed"];
        break;
    case "avg_life": 
        $ret .= $stats["avg_life"];
        break;
    case "sla_compliance": 
        $ret .= $stats["sla_compliance"];
        break;
    case "avg_scoring": 
        $ret .= $stats["avg_scoring"];
        break;
    case "avg_worktime": 
        $ret .= $stats["avg_worktime"];
        break;
    }	
	
	if($return_type == 'xml') {
        $ret .= "</data>\n";
		$ret .= "</xml>\n";
	}

	return $ret;
}

?>
