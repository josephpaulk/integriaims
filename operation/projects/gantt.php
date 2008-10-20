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


// gantt php class example and configuration file
// Copyright (C) 2005 Alexandre Miguel de Andrade Souza


// Real start
global $config;

if (!isset($config["base_url"]))
	exit;

// Security checks for this project

if (check_login() != 0) {
    audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access", "Trying to access event viewer");
    require ($config["base_url"]."/general/noaccess.php");
    exit;
}
$id_user = $_SESSION['id_usuario'];
$id_project = give_parameter_get ("id_project", -1);
if ($id_project != -1)
	$project_name = give_db_value ("name", "tproject", "id", $id_project);
else
	$project_name = "";

if ( $id_project == -1 ){
    // Doesn't have access to this page
    	audit_db($id_user, $config["REMOTE_ADDR"], "ACL Violation","Trying to access to task manager withour project");
	require ($config["base_url"]."/general/noaccess.php");
    	exit;
}

if (user_belong_project ($id_user, $id_project)==0){
	audit_db($id_user, $config["REMOTE_ADDR"], "ACL Violation","Trying to access to task manager of unauthorized project");
	require ($config["base_url"]."/general/noaccess.php");
	exit;
}

echo "<h2>".$project_name." - ".__('Gantt graph')."</h2>";

echo "<img src='operation/projects/gantt_graph.php?id_project=$id_project'>";

echo "<br><br>";
echo "<a target='top' href='index.php?sec=projects&sec2=operation/projects/gantt&id_project=$id_project&clean_output=1'>".__('Full screen')."</a>";
?>

