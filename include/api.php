<?php
// INTEGRIA IMS v2.1
// http://www.integriaims.com
// ===========================================================
// Copyright (c) 2007-2011 Artica, info@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License
// (LGPL) as published by the Free Software Foundation; version 2

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.

if (!file_exists("config.php")){
	echo "ERROR: Cannot open config.php";
	exit;
}

include_once ("config.php");

require_once ($config["homedir"].'/include/functions_api.php');

//Get the parameters and parse if necesary.
$ip_origin = $_SERVER['REMOTE_ADDR'];
$op = get_parameter('op', false);
$params = get_parameter('params', '');
$token = get_parameter('token', ',');
$user = get_parameter('user', false);
$pass = get_parameter('pass', '');
$return_type = get_parameter('return_type', 'csv');

$api_password = get_db_value_filter('value', 'tconfig', array('token' => 'api_password'));

$correct_login = false;

if (!empty($api_password)) {
	if ($password === $api_password) {
		$correct_login = true;
	}
}
else {
	if (ip_acl_check ($ip_origin)) {
		$correct_login = true;
	}
}

if(!$correct_login) {
	exit;
}

switch ($op){
	case "create_incident":
	{
		$params = explode($token, $params);
		debugPrint($params);
		api_create_incident ($return_type, $id, $params);
		break;
	}
	case "get_incidents":
	{
		$params = explode($token, $params);
		echo api_get_incidents ($return_type, $user, $params);
		break;
	}
	case "update_incident_details":
	{
		$params = explode($token, $params);
		$id_incident = $params[0];
		echo api_update_incident ($return_type, $user, $id_incident, $params);
		break;
	}
	case "get_incident_details":
	{
		echo api_get_incident_details ($return_type, $user, $params);
		break;
	}
	case "get_incident_workunits":
	{
		echo api_get_incident_workunits ($return_type, $user, $params);
		break;
	}
	case "get_incident_files":
	{
		echo api_get_incident_files ($return_type, $user, $params);
		break;
	}
	case "get_incidents_resolutions":
	{
		echo api_get_incidents_resolutions ($return_type, $user);
		break;
	}
	case "get_incidents_status":
	{
		echo api_get_incidents_status ($return_type, $user);
		break;
	}
	case "get_incidents_sources":
	{
		echo api_get_incidents_sources ($return_type, $user);
		break;
	}
	case "get_groups":
	{
		echo api_get_groups ($return_type, $user, $params);
		break;
	}
	case "get_users":
	{
		echo api_get_users ($return_type, $user);
		break;
	}
	case "download_file":
	{
		echo api_download_file ($return_type, $user, $params);
		break;
	}
	case "get_incident_tracking":
	{
		echo api_get_incident_tracking ($return_type, $user, $params);
		break;
	}
	case "get_stats":
	{
		echo api_get_stats ($return_type, $params);
		break;
	}

	default: 
	{
	}
}
?>
