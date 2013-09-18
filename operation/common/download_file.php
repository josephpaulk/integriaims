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

require_once ('../../include/config.php');
require_once ('../../include/functions.php');
require_once ('../../include/functions_db.php');

//Delete black lines on include!
ob_end_clean();

session_start();

check_login();

global $config;

$config["id_user"] = $_SESSION["id_usuario"];

$id_user = $config["id_user"];
$id_attachment = get_parameter ("id_attachment", 0);
$type = get_parameter("type");

//Check ACLs restriction based on type parameter and get data
$data = array();
$fileLocation = "";
switch ($type) {


	case "contact":
		if (! give_acl ($config['id_user'], 0, "CR")){
    		audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access Downloads browser");
    		require ("../../general/noaccess.php");
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
    			require ("../../general/noaccess.php");
    			exit;
			}
		}
		$fileLocation = $config["homedir"]."/attachment/".$data["id_attachment"]."_".$data["filename"];
		$last_name = $data["filename"];
		break;
	case "release":
		if (! give_acl($config["id_user"], 0, "KR")) {
    		audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access Downloads browser");
    		require ("../../general/noaccess.php");
    		exit;
		}
		$timestamp = date('Y-m-d H:i:s');
		mysql_query ("INSERT INTO tdownload_tracking (id_download, id_user, date) VALUES ($id_attachment, '".$config['id_user']."','$timestamp')");
		$data = get_db_row ("tdownload", "id", $id_attachment );


		$fileLocation = $config["homedir"]."/".$data["location"];
		$short_name = preg_split ("/\//", $data["location"]);
		$last_name = $short_name[sizeof($short_name)-1];
		break;
	default:
}

//General check to avoid hacking using wrong id of files
if (! $data) {
    audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access Downloads browser");
    require ("../../general/noaccess.php");
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