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
$id_task = give_parameter_get ("id_task", -1);

// Get names
if ($id_project != 1)
	$project_name = give_db_value ("name", "tproject", "id", $id_project);
else
	$project_name = "";

if ($id_task != 1)
	$task_name = give_db_value ("name", "ttask", "id", $id_task);
else
	$task_name = "";

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

// Tracking
echo "<li class='nomn'>";
echo "<a href='index.php?sec=projects&sec2=operation/projects/tracking&id=$id_project'><img src='images/eye.png' class='top' border=0> ".$lang_label["tracking"]." </a>";
echo "</li>";

// People
echo "<li class='nomn'>";
echo "<a href='index.php?sec=projects&sec2=operation/projects/people_manager&id=$id_project'><img src='images/user_suit.png' class='top' border=0> ".$lang_label["people"]." </a>";
echo "</li>";

echo "</ul>";
echo "</div>";
echo "<div style='height: 25px'> </div>";
 
// MAIN LIST OF TASKS

// Specific task
if ($id_task != -1){ 
	$sql= "SELECT tworkunit.timestamp, tworkunit.duration, tworkunit.id_user, tworkunit.description 
			FROM tworkunit, tworkunit_task 
			WHERE tworkunit_task.id_task = $id_task AND tworkunit_task.id_workunit = tworkunit.id";
	echo "<h3>".$lang_label["workunit_resume"];
	echo " - ".$lang_label["task"]." - ".$task_name."</h3>";
	echo "<table cellpadding='3' cellspacing='3' border='0' width=800 class='databox_color'>";
	echo "<tr><th>"; 
	echo $lang_label["timestamp"];
	echo "<th>"; 
	echo $lang_label["user"];
	echo "<th>"; 
	echo $lang_label["time_used"];
	echo "<th>"; 
	echo $lang_label["description"];
}

// Whole project
if ($id_task == -1){
	$sql= "SELECT tworkunit.timestamp, tworkunit.duration, tworkunit.id_user, tworkunit.description, ttask.id  
			FROM tworkunit, tworkunit_task, ttask 
			WHERE 	tworkunit_task.id_task = ttask.id AND 
					ttask.id_project = $id_project AND 
					tworkunit_task.id_workunit = tworkunit.id";

	echo "<h3>".$lang_label["workunit_resume"];
	echo " - ".$lang_label["project"]." - ".$project_name."</h3>";
	echo "<table cellpadding='3' cellspacing='3' border='0' width='800' class='databox_color'>";
	echo "<tr><th>"; 
	echo $lang_label["task"];
	echo "<th>"; 
	echo $lang_label["timestamp"];
	echo "<th>"; 
	echo $lang_label["user"];
	echo "<th>"; 
	echo $lang_label["time_used"];
	echo "<th>"; 
	echo $lang_label["description"];
}

$color = 1;
if ($res = mysql_query($sql)) {
	while ($row=mysql_fetch_array($res)){
		if ($color == 1){
			$tdcolor = "datos";
			$color = 0;
		} else {
			$tdcolor = "datos2";
			$color = 1;
		}

		// Show data
		if ($id_task == -1){
			echo "<tr><td class='$tdcolor' valign='top'>";
			echo $row["id"];
			echo "<td class='$tdcolor' valign='top'>";
			echo $row["timestamp"];
		} else {
			echo "<tr><td class='$tdcolor' valign='top'>";
			echo $row["timestamp"];
		}
		echo "<td class='$tdcolor' valign='top'>";
		echo $row["id_user"];

		echo "<td class='$tdcolor' valign='top'>";
		echo $row["duration"];

		echo "<td class='$tdcolor' valign='top'>";
		echo clean_output_breaks($row["description"]);
	}
}
echo "</table>";


?>
