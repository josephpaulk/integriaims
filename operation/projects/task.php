<?php

// FRITS - the FRee Incident Tracking System
// =========================================
// Copyright (c) 2007 Sancho Lerena, slerena@openideas.info
// Copyright (c) 2007 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
// Load global vars

global $config;


if (check_login() != 0) {
    audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access", "Trying to access event viewer");
    require ("general/noaccess.php");
    exit;
}

$id_user = $config["id_user"];

$id_project = give_parameter_get ("id_project", -1);
if ($id_project != -1)
	$project_name = give_db_value ("name", "tproject", "id", $id_project);
else
	$project_name = "";

if ( $id_project == -1 ){
    // Doesn't have access to this page
    audit_db($id_user, $config["REMOTE_ADDR"], "ACL Violation","Trying to access to task manager withour project");
    include ("general/noaccess.php");
    exit;
}

if (user_belong_project ($id_user, $id_project)==0){
	audit_db($id_user, $config["REMOTE_ADDR"], "ACL Violation","Trying to access to task manager of unauthorized project");
	include ("general/noaccess.php");
	exit;
}

$operation = give_parameter_get ("operation", -1);
if ($operation == "delete") {
	$id_task = give_parameter_get ("id");
	
	if ((dame_admin($id_user)==1) OR (project_manager_check ($id_project) == 1)){
		delete_task ($id_task);
		echo "<h3 class='suc'>".$lang_label["delete_ok"]."</h3>";
		$operation = "";
	} else {
		no_permission();
	}
}

// MAIN LIST OF TASKS

echo "<h2>".$project_name." - ".$lang_label["task_management"]."</h2>";

// -------------
// Show headers
// -------------
echo "<table width='100%' class='databox'>";
echo "<tr>";
echo "<th>".$lang_label["name"];
echo "<th>".lang_string ("pri");
echo "<th>".lang_string ("progress");
echo "<th>".$lang_label["time_used"];
echo "<th>".$lang_label["people"];
//echo "<th>".$lang_label["start"];
echo "<th>".$lang_label["end"];
echo "<th>".lang_string ("files");
echo "<th>".lang_string ("delete");
$color = 1;
show_task_tree ($id_project);
echo "</table>";



if (give_acl($_SESSION["id_usuario"], 0, "IW")==1) {
    echo "<form name='boton' method='POST'  action='index.php?sec=projects&sec2=operation/projects/task_detail&id_project=$id_project&operation=create'>";
    echo "<input type='submit' class='sub next' name='crt' value='".$lang_label["create_task"]."'>";
    echo "</form>";
}


function show_task_row ( $id_project, $row2, $tdcolor, $level = 0){
	echo "<tr>";
	// Task  name
	echo "<td class='$tdcolor' align='left' >";
	for ($ax=0; $ax < $level; $ax++)
		echo "<img src='images/copy.png'>";
	//if ($level > 0)
	//echo "<img src='images/copy.png'>&nbsp;";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/task_detail&id_project=$id_project&id_task=".$row2["id"]."&operation=view'>".$row2["name"]."</a></td>";

	// Priority
	echo "<td class='$tdcolor' align='center'>";
	echo clean_input ($row2["priority"]);

	// Completion
	echo "<td class='$tdcolor' align='center'>";
	//echo clean_input ($row2["completion"]."%");
	echo "<img src='include/functions_graph.php?type=progress&width=70&height=20&percent=".$row2["completion"]."'>";
	
	// Time used
	echo "<td class='$tdcolor' align='center'>";
	echo give_hours_task ( $row2["id"]);

	// People
	echo "<td class='$tdcolor'>";
	echo combo_users_task ($row2["id"]);
	// group
	/*
	echo "<td class='$tdcolor' align='center'>";
	echo "<img src='images/".dame_grupo_icono ( give_db_value ( 'id_group', 'ttask', 'id', $row2["id"]))."'> ";
	echo dame_grupo ( give_db_value ( 'id_group', 'ttask', 'id', $row2["id"]) );
	*/ 	

	// Start
	/*
	echo "<td class='".$tdcolor."f9'>";
	//echo substr($row2["start"],0,10);
	echo human_time_comparation ($row2["start"]);
	*/

	// End
	echo "<td class='".$tdcolor."f9'>";
	// echo substr($row2["end"],0,10);
	$ahora=date("Y/m/d H:i:s");
	if (strtotime($ahora) > strtotime($row2["end"]))
		echo "<font color='red'>";
	else
		echo "<font>";
	echo human_time_comparation ($row2["end"]);
	echo "</font>";
	
	echo "<td class='$tdcolor'>";
	if (give_number_files_task ($row2["id"]) > 0)
		echo " <img src='images/disk.png'>";

	// Delete
	echo "<td class='$tdcolor' align='center'>";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/task&operation=delete&id_project=$id_project&id=".$row2["id"]."' onClick='if (!confirm(\' ".lang_string ("are_you_sure")."\')) return false;'><img src='images/cross.png' border='0'></a>";
	
	
}

function show_task_tree ( $id_project, $level = 0, $parent_task = 0, $color = 0){
	global $config;
	$id_user = $config["id_user"];
	// Simple query, needs to implement group control and ACL checking
	$sql2="SELECT * FROM ttask WHERE id_project = $id_project and id_parent_task = $parent_task"; 
	if ($result2=mysql_query($sql2))    
	while ($row2=mysql_fetch_array($result2)){
		if ($color == 1){
			$tdcolor = "datos";
			$color = 0;
		}
		else {
			$tdcolor = "datos2";
			$color = 1;
		}
		if ((user_belong_task ($id_user, $row2["id"]) == 1)) 
			show_task_row ( $id_project, $row2, $tdcolor, $level );
		show_task_tree ( $id_project, $level+1, $row2["id"], $color);
	}
}
?>
