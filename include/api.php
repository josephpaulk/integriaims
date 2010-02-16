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

if (!file_exists("config.php")){
	echo "ERROR: Cannot open config.php";
	exit;
}

include_once ("config.php");
require_once ("functions_api.php");

//Get the parameters and parse if necesary.
$ip_origin = $_SERVER['REMOTE_ADDR'];
$op = get_parameter('op');
$op2 = get_parameter('op2');
$id = get_parameter('id');
$id2 = get_parameter('id2');
$data = get_parameter ('data','');
$data2 = get_parameter ('data2','');

// If no ACL access, just exit.
if (!ip_acl_check ($ip_origin)){
	echo "ERROR: You don't have access";
	exit;
}

switch ($op){

	case "create_incident":
	{
		api_create_incident ($id, $data);
		break;
	}
	default: 
	{
		echo "ERROR: Operation not found";
		exit;
	}
}

?>
