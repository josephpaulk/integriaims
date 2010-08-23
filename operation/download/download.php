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


session_start();

require_once ('../../include/config.php');
require_once ('../../include/functions.php');
require_once ('../../include/functions_db.php');

global $config;

if (isset($_SESSION["id_usuario"]))
	$config["id_user"] = $_SESSION['id_usuario'];
else {
    audit_db("",$config["REMOTE_ADDR"], "ACL Violation","Trying to access Downloads");
    require ("general/noaccess.php");
    exit;
}

check_login();

if (give_acl($config["id_user"], 0, "KR")==0) {
    audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access Downloads browser");
    require ("general/noaccess.php");
    exit;
}

$download_id = get_parameter ("id", "");
$download = get_db_row ("tdownload", "id", $download_id );

$location = $download["location"];
$id_group = get_db_sql ("SELECT id_group FROM tdownload_category WHERE id = ".$download["id_category"]);
if ($id_group == "")
	$id_group = 0;

$fileLocation = $config["homedir"]."$location";

if (!give_acl($config["id_user"], $id_group, "KR")){
	audit_db("",$config["REMOTE_ADDR"], "ACL Violation","Trying to access a forbidden file");
	echo "File not found";
	exit;
}

if (file_exists($fileLocation)){
	$short_name = preg_split ("/\//", $location);
	$last_name = $short_name[sizeof($short_name)-1];

	$timestamp = date('Y-m-d H:i:s');
	mysql_query ("INSERT INTO tdownload_tracking (id_download, id_user, date) VALUES ($download_id, '".$config['id_user']."','$timestamp')");

	header('Content-type: aplication/octet-stream;');
	header('Content-type: ' . returnMIMEType($fileLocation) . ';');
	header("Content-Length: " . filesize($fileLocation));
	header('Content-Disposition: attachment; filename="' . $last_name . '"');

	// If it's a large file we don't want the script to timeout, so:
	set_time_limit(9000);
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
} else {
	audit_db("",$config["REMOTE_ADDR"], "ACL Violation","Trying to access a non-existant file in disk");
	echo "File not found";
	exit;
}


?>
