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

//Delete black lines on include!
ob_start();

if (!file_exists('../../include/config.php')) {
	require_once ('include/config.php');
	require_once ('include/functions.php');
	require_once ('include/functions_db.php');
	require_once ("include/functions_crm.php");
	$general_error = "general/noaccess.php";
} else {
	require_once ('../../include/config.php');
	require_once ('../../include/functions.php');
	require_once ('../../include/functions_db.php');
	require_once ("../../include/functions_crm.php");
	$general_error = "../../general/noaccess.php";
}

//Delete black lines on include!
ob_end_clean();

session_start();

global $config;

$config["id_user"] = $_SESSION["id_usuario"];

$id_user = $config["id_user"];
$id_attachment = get_parameter ("id_attachment", 0);
$type = get_parameter("type");


if ($type !== "external_release") {
	check_login();
}

//Check ACLs restriction based on type parameter and get data
$data = array();
$fileLocation = "";
switch ($type) {


	case "contact":
		if (! give_acl ($config['id_user'], 0, "CR")){
    		audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access Downloads browser");
    		require ($general_error);
    		exit;
		}
		$data = get_db_row ("tattachment", "id_attachment", $id_attachment);
		$fileLocation = $config["homedir"]."/attachment/".$data["id_attachment"]."_".$data["filename"];
		$last_name = $data["filename"];
		break;
	case "incident":
		$data = get_db_row ("tattachment", "id_attachment", $id_attachment);
		
		if ($data) {
			$id_incident =  $data["id_incidencia"];

			$id_group = get_db_sql ("SELECT id_grupo FROM tincidencia WHERE id_incidencia = $id_incident");

			if (! give_acl ($config['id_user'], $id_group, "IR")){
    			audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access Downloads browser");
    			require ($general_error);
    			exit;
			}
		}
		$fileLocation = $config["homedir"]."/attachment/".$data["id_attachment"]."_".$data["filename"];
		$last_name = $data["filename"];
		break;
	case "release":		
		if (! give_acl($config["id_user"], 0, "KR")) {
    		audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access Downloads browser");
    		require ($general_error);
    		exit;
		}

		$timestamp = date('Y-m-d H:i:s');
		mysql_query ("INSERT INTO tdownload_tracking (id_download, id_user, date) VALUES ($id_attachment, '".$config['id_user']."','$timestamp')");

		$data = get_db_row ("tdownload", "id", $id_attachment );

		$fileLocation = $config["homedir"]."/".$data["location"];
		$short_name = preg_split ("/\//", $data["location"]);
		$last_name = $short_name[sizeof($short_name)-1];
		break;
	case "external_release":

		$timestamp = date('Y-m-d H:i:s');
		mysql_query ("INSERT INTO tdownload_tracking (id_download, id_user, date) VALUES ($id_attachment, 'anonymous','$timestamp')");
		
		$data = get_db_row ("tdownload", "external_id", $id_attachment );
		
		$fileLocation = $config["homedir"]."/".$data["location"];
		$short_name = preg_split ("/\//", $data["location"]);
		$last_name = $short_name[sizeof($short_name)-1];
		break;
	case "workorder":
		$data = get_db_row ("tattachment", "id_attachment", $id_attachment);

		$todo = get_db_row ("ttodo", "id", $data["id_todo"]);

		if (!dame_admin($config["id_user"]) && $todo["assigned_user"] != $config['id_user'] && $todo["created_by_user"] != $config['id_user']) {
			audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access Downloads browser");
    		require ($general_error);
    		exit;
		}

		$fileLocation = $config["homedir"]."/attachment/".$data["id_attachment"]."_".$data["filename"];
		$last_name = $data["filename"];		

		break;
	case "kb":
		$data = get_db_row ("tattachment", "id_attachment", $id_attachment);

		if (! check_kb_item_accessibility($config["id_user"], $id_attachment)) {
			audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access Downloads browser");
			require ($general_error);
			exit;
		}

		$fileLocation = $config["homedir"]."/attachment/".$data["id_attachment"]."_".$data["filename"];
		$last_name = $data["filename"];		

		break;
	case "company":
		$data = get_db_row ("tattachment", "id_attachment", $id_attachment);

		$read_permission = check_crm_acl ('other', 'cr', $config['id_user'], $data["id_company"]);
	
		if (! $read_permission) {
			audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access Downloads browser");
			require ($general_error);
			exit;
		}

		break;

	case "lead":
		
		$data = get_db_row ("tattachment", "id_attachment", $id_attachment);
		$lead = get_db_row ("tlead", "id", $data["id_lead"]);
	
		$user_leads = enterprise_hook('crm_get_user_leads', array($config['id_user'], $lead));
		//The array is not empty just return empty information
		if (!$user_leads[0]) {
			audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access Downloads browser");
			require ($general_error);
			exit;	
		}

		break;
	default:
}

//Compound file path
if ($type == "release" ||$type == "external_release") {
	$fileLocation = $config["homedir"]."/".$data["location"];
	$short_name = preg_split ("/\//", $data["location"]);
	$last_name = $short_name[sizeof($short_name)-1];	
} else {
	$fileLocation = $config["homedir"]."/attachment/".$data["id_attachment"]."_".$data["filename"];
	$last_name = $data["filename"];			
}

//General check to avoid hacking using wrong id of files
if (! $data) {
    audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access Downloads browser");
    require ($general_error);
    exit;
}

session_write_close();

// Allow download file
$mime = returnMIMEType($fileLocation); //We use a custom function because php functions are not reliable for document office

if (file_exists($fileLocation)){
 
	header("Content-Type: $mime;");
	header("Content-Length: " . filesize($fileLocation));
	header('Content-Disposition: attachment; filename="' . $last_name . '"');

	// If it's a large file we don't want the script to timeout, so:
	set_time_limit(0);

	// If it's a large file, readfile might not be able to do it in one go, so:
	$chunksize = 1 * (1024 * 256); // how many bytes per chunk
	if (filesize($fileLocation) > $chunksize) {
		$handle = fopen($fileLocation, 'rb');
    	$buffer = '';
      	while (!feof($handle)) {
          	$buffer = fread($handle, $chunksize);
	      	echo $buffer;
	        ob_flush();
		    flush();
	 	}
		fclose($handle);
	} else {
		readfile($fileLocation);
	}
	exit;
	
} else {
	audit_db("",$config["REMOTE_ADDR"], "File missing","File $id_attachment is missing in disk storage");
	echo __("File is missing in disk storage. Please contact the administrator");
	exit;
}

?>