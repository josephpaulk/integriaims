<?PHP
// Integria 1.0 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2008 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

check_login ();

$id_grupo = get_parameter ("id_grupo",0);
$id_user=$config['id_user'];
if (give_acl($id_user, $id_grupo, "PR") != 1){
 	// Doesn't have access to this page
	audit_db($id_user,$config["REMOTE_ADDR"], "ACL Violation","Trying to access to user report without projects rights");
	include ("general/noaccess.php");
	exit;
}
$id = get_parameter ("id","");
if (($id != "") && ($id != $id_user)){
	if (give_acl($id_user, 0, "PW"))
		$id_user = $id;
	else {
		audit_db("Noauth", $config["REMOTE_ADDR"], "No permission access", "Trying to access user workunit report");
		require ("general/noaccess.php");
		exit;
	}
	
}

// Get parameters for actual Calendar show
$timestamp_l = get_parameter ( "timestamp_l","");
$timestamp_h = get_parameter ( "timestamp_h","");

echo "<h1>".__('Weekly report for')." $id_user</h1>";
echo "<h3>".$timestamp_l." -&gt;".$timestamp_h."</h3>";

echo "<div>";
echo "<table width=750 class='blank' >";
echo "<tr><td class=datos>";
echo "<h3>".__('Workunit by task')."</h3>";
echo "<tr><td class=datos>";
echo "<img src='include/functions_graph.php?type=workunit_user&width=650&height=350&id_user=$id_user&date_from=$timestamp_l'>";
echo "<tr><td class=datos>";
echo "<h3>".__('Workunit by project')."</h3>";
echo "<tr><td class=datos>";
echo "<img src='include/functions_graph.php?type=workunit_project_user&width=650&height=350&id_user=$id_user&date_from=$timestamp_h'>";
echo "</table>";
echo "</div>";



?>
