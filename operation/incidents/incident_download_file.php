<?php
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008-2012 Ártica Soluciones Tecnológicas
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
require_once ('../../include/functions_incidents.php');

session_start();

global $config;

$config["id_user"] = $_SESSION['id_usuario'];

$id_user = $config["id_user"];
$id_attachment = get_parameter ("id_attachment", 0);

check_login();

$data = get_db_row ("tattachment", "id_attachment", $id_attachment);

if (!isset($data)){
    echo "No valid attach id";
    exit;
}

$id_incident =  $data["id_incidencia"];

$id_group = get_db_sql ("SELECT id_grupo FROM tincidencia WHERE id_incidencia = $id_incident");

if (! give_acl ($config['id_user'], $id_group, "IR")){
    echo "You dont have access to that file - Code #$id_incident";
    exit;
}

session_write_close();

// Allow download file

$fileLocation = $config["homedir"]."/attachment/".$data["id_attachment"]."_".$data["filename"];

$last_name = $data["filename"];

if (file_exists($fileLocation)){
	
	// Just redirect it, this file could be BIG and problematic.

	header("Location: ".$config["base_url"]."/attachment/".$data["id_attachment"]."_".$data["filename"]);
	return;
	
} else {
	echo "File is missing in disk storage. Please contact the administrator";
	exit;
}


?>
