<?php 

// INTEGRIA IMS v2.0
// http://www.integriaims.com
// ===========================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2008 Esteban Sanchez, estebans@artica.es
// Copyright (c) 2007-2008 Artica, info@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License
// (LGPL) as published by the Free Software Foundation; version 2

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.

include_once ('config.php');
include_once ('functions_db.php');

/**
 * Check if an App is stored on database.
 *
 *
 * @param string app name.
 */
function check_app ($app_name) {
	return get_db_row('tapp', 'app_name', $app_name);
}

/**
 * Store an app info.
 *
 *
 * @param string app name.
 * @param int app mode: 
 * 		0 -> Autocreated (not policy) 
 * 		1 -> This application should not be used
 * 		2 -> This application is allowed
 */
function add_app ($app_name, $app_mode) {
	$sql = "INSERT INTO tapp (app_name, app_mode) VALUES ('".$app_name."', ".$app_mode.")";
	$res = process_sql ($sql);
	
	return $res;
}

/**
 * Store an app activity info.
 *
 *
 * @param int App id.
 * @param string User id.
 * @param string App name.
 * @param string App extra info (file in editor, url in browser...).
 * @param string Start time.
 * @param string End time.
 * @param string Send time.
 * 
 */
function add_app_activity ($id_app, $id_user, $app_extra, $activity_time, $start_timestamp, $end_timestamp) {
	$sql = "INSERT INTO tapp_activity_data (id_app, id_user, app_extra, activity_time, start_timestamp, end_timestamp, send_timestamp)
			VALUES (".$id_app.", '".$id_user."', '".addslashes($app_extra)."', ".$activity_time.", ".$start_timestamp.", ".$end_timestamp.", UNIX_TIMESTAMP())";
	$res = process_sql ($sql);
	
	return $res;
}

/**
 * Get the last user send datetime.
 *
 *
 * @param string user id.
 * 
 */
function get_lastdate($user){		
		$sql = 'SELECT DATE_FORMAT(FROM_UNIXTIME(send_timestamp), "%Y%m%dT%H:%i:%s") as lastdate FROM tapp_activity_data WHERE id_user = \''.$user.'\' ORDER BY send_timestamp DESC LIMIT 1';
		$lastdate = process_sql($sql);
		if($lastdate == false)
			return false;
		else
			return $lastdate[0]['lastdate'];
}

/**
 * Check the user in the db.
 *
 *
 * @param string user id.
 * 
 */
function usr_db($user){		
		$id_user = get_db_value ('id_usuario', 'tusuario', 'id_usuario', $user);
		if ($user == $id_user) {
			return true;
		}
			return false;
}

/**
 * Check the password of the user in the db.
 *
 *
 * @param string user id.
 * @param string password in MD5.
 * 
 */
function psw_db($user, $psw){		
		if ($psw == dame_password($user)) {
			return true;
		}
			return false;
}

/**
 * Store the activities received into an array.
 *
 *
 * @param array data of activities.
 * 
 */
function add_app_activities($usr, $start_datetime, $end_datetime, $data_in){
	$msg = "Inserted: ";
	
	$cont = 0;
	foreach($data_in as $row){
		//Check if app exists into db
		$app_exists = check_app($row['app_name']);
		// If app not exists, store it
		if($app_exists === false){
			add_app($row['app_name'],0);
			$app_new = check_app($row['app_name']);
			$id_app = $app_new['id'];
		}
		else{
			$id_app = $app_exists['id'];
		}
		
		$return = add_app_activity ($id_app, $usr, $row['app_extra'], $row['activity_time'], $start_datetime, $end_datetime);
		$msg .= $row['app_name']." info ; ";
		
		if($return == false)
			$msg = "Error inserting info";
		$cont = $cont + 1;
	}

	return $msg;
}

/**
 * Return a xmlrpc struct into an array.
 *
 *
 * @param object xmlrpc struct.
 * 
 */
function get_xmlrpcstruct_array($dat){
	$dat->structreset();
	$msg = "";
	$data_in = array ();
	$cont = 0;
	while (list($key1, $dat1) = $dat->structEach())
	{
		while (list($key2, $v) = $dat1->structEach()) {
			$msg .= "$key2 : ".$v->scalarVal()." ; ";
			$data_in[$cont][$key2] = $v->scalarVal();
		}
		$cont = $cont + 1;
	}
	
	return $data_in;
}
?>
