<?php 

// INTEGRIA IMS v2.0
// http://www.integriaims.com
// ===========================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2008 Esteban Sanchez, estebans@artica.es
// Copyright (c) 2007-2011 Artica, info@artica.es

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
 * @id_group int group of the user who send data
 */
function check_app ($app_name, $id_group) {
	return get_db_row_filter('tapp', array('app_name' => $app_name, 'id_group' => $id_group));
}

/**
 * Check if an App is stored on default apps.
 *
 *
 * @param string app name.
 * @id_group int group of the user who send data
 */
function check_app_default ($app_name) {
	return get_db_row_filter('tapp_default', array('app_name' => $app_name));
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
 * @id_group int group of the user who send data
 * @id_category int id of the category
 */
function add_app ($app_name, $app_mode, $id_group, $id_category) {
	$sql = "INSERT INTO tapp (app_name, app_mode, id_group, id_category) VALUES ('".$app_name."', ".$app_mode.", ".$id_group.", ".$id_category.")";
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
 * Update an app activity info.
 *
 *
 * @param array data of latest activity
 * @param string Continuation start timestamp.
 * @param string Continuation start activity time.
 * 
 */
function merge_app_activity ($data_latest, $cont_end_timestamp, $cont_send_timestamp, $cont_activity_time) {
	// Increase the activity time to merge activities
	$new_activity_time = $data_latest['activity_time'] + $cont_activity_time;
	
	// Merge acrivities setting the new end and send timestamps and increasing the activity time
	$sql = "UPDATE tapp_activity_data 
	SET 
	end_timestamp = ".$cont_end_timestamp.", 
	send_timestamp = ".$cont_send_timestamp.", 
	activity_time = ".$new_activity_time." 
	WHERE 
	id_app = ".$data_latest['id_app']." 
	AND id_user = '".$data_latest['id_user']."' 
	AND app_extra = '".addslashes($data_latest['app_extra'])."' 
	AND activity_time = ".$data_latest['activity_time']." 
	AND start_timestamp = ".$data_latest['start_timestamp']." 
	AND end_timestamp = ".$data_latest['end_timestamp']." 
	AND send_timestamp = ".$data_latest['send_timestamp'];

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
	
	// Get the first found group of the user
	$usr_groups = get_user_groups($usr);
	$id_group = reset(array_keys($usr_groups));
	
	$cont = 0;
	foreach($data_in as $row){
		// All the applications have mode=0 and category=1 by default
		$mode = 0;
		$id_category = 1;
		
		//Check if app exists into db
		$app_exists = check_app($row['app_name'], $id_group);
		// If app not exists, store it
		if($app_exists === false){
			//Check if app exists into default apps
			$app_default = check_app_default($row['app_name']);
			//If exists we get the default mode
			if($app_default !== false){
				$mode = $app_default['app_mode'];
				$id_category = $app_default['id_category'];
			}
			
			add_app($row['app_name'], $mode, $id_group, $id_category);
			$app_new = check_app($row['app_name'], $id_group);

			$id_app = $app_new['id'];
		}
		else{
			$id_app = $app_exists['id'];
		}

		// If the data_in unique in the package, we study the compaction
		if(count($data_in) == 1) {
			// Check if the last activity was the same app
			$app_cont = check_app_continues($usr, $id_app, $row['app_extra'], $start_datetime);
			
			// Check if the last activity was unique in his package too.
			// We now this if the activity time is not lower than current
			// If not, we will not merge any data
			if($app_cont != false && $app_cont[0]['activity_time'] < $row['activity_time']) {
				$app_cont = false;
			}
		}
		else {
			$app_cont = false;
		}

		//  If is not a continuation we insert the data
		if($app_cont === false) {
			// Adding new data
			$return = add_app_activity ($id_app, $usr, $row['app_extra'], $row['activity_time'], $start_datetime, $end_datetime);
			$msg .= $row['app_name']." info ; ";
		}
		else { // If is a continuation we merge the activities
			// Merging data of: ".$app_cont[0]['id_app']." - ".$app_cont[0]['app_extra']
			$return = merge_app_activity ($app_cont[0], $end_datetime, time(), $row['activity_time']);
		}
		
		if($return == false)
			$msg = "Error inserting info";
		$cont = $cont + 1;
	}

	return $msg;
}

/**
 * Check if an activity is continuation of the latest 
 * activity with little time between both.
 *
 *
 * @param string user name.
 * @param string app id.
 * @param string app extra.
 * @param string start datetime of the app.
 */
function check_app_continues ($usr, $id_app, $app_extra, $start_datetime) {
	// Max time between activities in seconds
	// TODO: Get this value from config file or any external source
	$max_time = 2;
	
	$bottom_edge = $start_datetime - $max_time;
	$top_edge = $start_datetime;
	
	$sql = "SELECT * FROM tapp_activity_data WHERE 
	id_user = '$usr' AND 
	id_app = '$id_app' AND 
	app_extra = '".addslashes($app_extra)."' AND 
	end_timestamp >= '$bottom_edge' AND 
	end_timestamp <= '$top_edge'";

	return process_sql($sql);
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
