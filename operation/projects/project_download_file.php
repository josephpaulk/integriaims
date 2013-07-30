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

require_once ('../../include/config.php');
require_once ('../../include/functions.php');
require_once ('../../include/functions_db.php');

session_start();

global $config;

$config["id_user"] = $_SESSION['id_usuario'];
$id_user = $config["id_user"];

check_login();

$id_attachment = get_parameter ("id_attachment", 0);

$data = get_db_row ("tattachment", "id_attachment", $id_attachment);

if (!isset($data)){
    echo "No valid attach id";
    exit;
}

$id_task = $data["id_task"];

// ACL
$task_access = get_project_access_extra ($config["id_user"], 0, $id_task, false, true);
if (! $task_access["read"]) {
	// Doesn't have access to this page
	audit_db($id_user, $config["REMOTE_ADDR"], "ACL Violation","Trying to access to pro without task permission");
	no_permission();
}

// Allow download file

$fileLocation = $config["homedir"]."/attachment/".$data["id_attachment"]."_".rawurlencode ($data["filename"]);
$fileLocation_unencoded = $config["homedir"]."/attachment/".$data["id_attachment"]."_".$data["filename"];

$last_name = $data["filename"];

if (file_exists($fileLocation_unencoded)){
	header('Content-type: aplication/octet-stream;');
	header('Content-type: ' . returnMIMEType($fileLocation) . ';');
	header("Content-Length: " . filesize($fileLocation));
	header('Content-Disposition: attachment; filename="' . $last_name . '"');
	readfile($fileLocation_unencoded);

} else {
	echo "File is missing in disk storage. Please contact the administrator";
	exit;
}


?>
