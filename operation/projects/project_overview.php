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

global $REMOTE_ADDR;
global $config;

check_login ();

// MAIN LIST OF PROJECTS GROUPS
echo "<h2>".__("Project overview")."</h2>";

// -------------
// Show headers
// -------------
echo "<table width='740' class='listing'>";
echo "<tr>";
echo "<th>".__ ("Project group");
echo "<th>".__ ("Icon");
echo "<th>".__ ("# Projects");

// -------------
// Show DATA TABLE
// -------------

$sql2="SELECT * FROM tproject_group ORDER by name"; 
if ($result2=mysql_query($sql2))	
while ($row2=mysql_fetch_array($result2)){
	if (give_acl($config["id_user"], 0, "PR") ==1){
		echo "<tr>";
	
		// Project group name
		echo "<td align='left' >";
		echo "<b><a href='index.php?sec=projects&sec2=operation/projects/project&filter_id_project_group=".$row2["id"]."'>".$row2["name"]."</a></b></td>";

		// Project group
		echo "<td>";
		echo "<img src='images/project_groups_small/".$row2["icon"].".png'>";
			
		// Projects inside
		echo "<td>";
		echo get_db_sql ("SELECT COUNT(*) FROM tproject WHERE id_project_group = ".$row2["id"]);		
	}
}
echo "</table>";

?>
