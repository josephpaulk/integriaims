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

// Load global vars

global $config;

include_once ("include/functions_projects.php");

check_login ();

$id_project = (int) get_parameter ('id_project');
$id_user = $config["id_user"];

// ACL
$project_access = get_project_access ($id_user, $id_project);
if (! $project_access["read"]) {
 	// Doesn't have access to this page
	audit_db ($id_user, $config["REMOTE_ADDR"], "ACL Violation","Trying to access to project graph page");
	no_permission ();
}

if ($id_project) {
	echo "<h3>".__('Project schema')."</h3>";
	echo '<img src="include/functions_graph.php?type=project_tree&id_project='.$id_project.'&id_user='.$id_user.'">';
}
