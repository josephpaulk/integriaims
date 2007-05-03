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
require "include/functions_form.php";

if (check_login() != 0) {
    audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access", "Trying to access event viewer");
    require ("general/noaccess.php");
    exit;
}

$id_user = $_SESSION['id_usuario'];

$id_project = give_parameter_get ("id_project", -1);
if ( $id_project == -1 ){
    // Doesn't have access to this page
    audit_db($id_user, $config["REMOTE_ADDR"], "ACL Violation","Trying to access to task manager withour project");
    include ("general/noaccess.php");
    exit;
}


// SHOW TABS
echo "<div id='menu_tab'><ul class='mn'>";

// Main
echo "<li class='nomn'>";
echo "<a href='index.php?sec=projects&sec2=operation/projects/project_detail&id=$id_project'><img src='images/application_edit.png' class='top' border=0> ".$lang_label["project"]."</a>";
echo "</li>";

// Tasks
echo "<li class='nomn'>";
echo "<a href='index.php?sec=projects&sec2=operation/projects/task&id_project=$id_project'><img src='images/page_white_text.png' class='top' border=0> ".$lang_label["tasklist"]."</a>";
echo "</li>";

// Workunits
echo "<li class='nomn'>";
echo "<a href='index.php?sec=projects&sec2=operation/projects/task_workunit&id_project=$id_project&id_task=-1'><img src='images/award_star_silver_1.png' class='top' border=0> ".$lang_label["workunits"]."</a>";
echo "</li>";

// Tracking
echo "<li class='nomn'>";
echo "<a href='index.php?sec=projects&sec2=operation/projects/tracking&id=$id_project'><img src='images/eye.png' class='top' border=0> ".$lang_label["tracking"]." </a>";
echo "</li>";

// Files
echo "<li class='nomn'>";
echo "<a href='index.php?sec=projects&sec2=operation/projects/task_files&id_project=$id_project'><img src='images/disk.png' class='top' border=0> ".$lang_label["files"]." </a>";
echo "</li>";

// People
echo "<li class='nomn'>";
echo "<a href='index.php?sec=projects&sec2=operation/projects/people_manager&id=$id_project'><img src='images/user_suit.png' class='top' border=0> ".$lang_label["people"]." </a>";
echo "</li>";

echo "</ul>";
echo "</div>";
echo "<div style='height: 25px'> </div>";
 
// MAIN LIST OF TASKS

echo "<h2>".$lang_label["task_management"];

// -------------
// Show headers
// -------------
echo "<table width='810' class='databox'>";
echo "<tr>";
echo "<th>".$lang_label["name"];
echo "<th>".$lang_label["prio"];
echo "<th>".$lang_label["completion"];
echo "<th>".$lang_label["time_used"];
echo "<th>".$lang_label["people"];
echo "<th>".$lang_label["start"];
echo "<th>".$lang_label["end"];
echo "<th>".$lang_label["delete"];
$color = 1;

// -------------
// Show DATA TABLE
// -------------
$id_project = give_parameter_get ("id_project",-1);


if ($id_project == -1){
    require ("general/noaccess.php");
    exit;
}

// Simple query, needs to implement group control and ACL checking
$sql2="SELECT * FROM ttask WHERE id_project = $id_project"; 
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
	
	echo "<tr>";

	// Task  name
	echo "<td class='$tdcolor' align='left' >";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/task_detail&id_project=$id_project&id_task=".$row2["id"]."&operation=view'>".$row2["name"]."</a></td>";

	// Priority
	echo "<td class='$tdcolor' align='center'>";
	echo clean_input ($row2["priority"]);

	// Completion
	echo "<td class='$tdcolor' align='center'>";
	//echo clean_input ($row2["completion"]."%");
	echo "<img src='include/functions_graph.php?type=progress&width=90&height=20&percent=".$row2["completion"]."'>";
	
	// Time used
	echo "<td class='$tdcolor' align='center'>";
	echo give_hours_task ( $row2["id"]);
	
	// People
	echo "<td class='$tdcolor'>";
	combo_users_project ($row2["id"]);

	// Start
	echo "<td class='".$tdcolor."f9'>";
	echo substr($row2["start"],0,10);

	// End
	echo "<td class='".$tdcolor."f9'>";
	echo substr($row2["end"],0,10);
	
	// Delete	
	echo "<td class='$tdcolor' align='center'>";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/task&quick_delete=".$row2["id"]."' onClick='if (!confirm(\' ".$lang_label["are_you_sure"]."\')) return false;'><img src='images/cross.png' border='0'></a>";
	if (give_number_files_task ($row2["id"]) > 0)
		echo " <img src='images/disk.png'>";

}
echo "</table>";

if (give_acl($_SESSION["id_usuario"], 0, "IW")==1) {
    echo "<form name='boton' method='POST'  action='index.php?sec=projects&sec2=operation/projects/task_detail&id_project=$id_project&operation=create'>";
    echo "<input type='submit' class='sub next' name='crt' value='".$lang_label["create_task"]."'>";
    echo "</form>";
}


?>