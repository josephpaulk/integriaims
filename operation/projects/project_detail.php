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
 	audit_db("Noauth",$config["REMOTE_ADDR"], "No authenticated access","Trying to access event viewer");
	require ("general/noaccess.php");
	exit;
}

	
$id_user = $_SESSION['id_usuario'];
if (give_acl($id_user, 0, "IR") != 1){
 	// Doesn't have access to this page
	audit_db ($id_user,$config["REMOTE_ADDR"], "ACL Violation","Trying to access to project detail page");
	include ("general/noaccess.php");
	exit;
}


$create_mode = 0;
$name = "";
$description = "";
$end_date = "";
$start_date = "";
$private = 0;
$id_project = -1; // Create mode by default
$result_output = "";

// ---------------
// Update project
// ---------------

if ((isset($_GET["action"])) AND ($_GET["action"]=="update")){
	$id_project = $_POST["id_project"];
 	$id_group = give_parameter_post ('group');
	$user = give_parameter_post ("user");
	$name = give_parameter_post ("name");
	$description = give_parameter_post ('description');
	$start_date = give_parameter_post ('start_date');
	$end_date = give_parameter_post ('end_date');
	$private = give_parameter_post ("private",0);			
	$sql = "UPDATE tproject SET 
			name = '$name',
			description = '$description',
			start = '$start_date',
			end = '$end_date',
			private = '$private',  
			id_owner = '$user', 
			id_group = '$id_group'
			WHERE id = $id_project";
	$result = mysql_query($sql);
	audit_db($user, $REMOTE_ADDR, "Project updated", "Project $name");
	if ($result)
		$result_output = "<h3 class='suc'>".$lang_label["update_ok"]."</h3>";
	else
		$result_output = "<h3 class='error'>".$lang_label["update_error"]."</h3>";
	$_GET["id"] = $id_project;
}

// ---------------------
// Edition / View mode
// ---------------------
$id_project = give_parameter_get ("id", 0);

if ( $id_project != 0){	
	// Obtain group of this incident
	$sql1='SELECT * FROM tproject WHERE id = '.$id_project;
	if (!$result=mysql_query($sql1)){
		audit_db($_SESSION['id_usuario'],$REMOTE_ADDR, "ACL Violation","Trying to access to other project hacking with URL");
		include ("general/noaccess.php");
		exit;  
	}
	$row=mysql_fetch_array($result);

	// Get values

	$name = $row["name"];
	$description = $row["description"];
	$start_date = $row["start"];
	$end_date = $row["end"];
	$group = $row["id_group"];
	$owner = $row["id_owner"];
	$private = $row["private"];

	$task_number =  give_number_tasks ($id_project);
	// SHOW TABS
	echo "<div id='menu_tab'><ul class='mn'>";

	// Main
	echo "<li class='nomn'>";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/project_detail&id=$id_project'><img src='images/application_edit.png' class='top' border=0> ".$lang_label["project"]."</a>";
	echo "</li>";

	// Tasks
	echo "<li class='nomn'>";
	if ($task_number > 0)
		echo "<a href='index.php?sec=projects&sec2=operation/projects/task&id_project=$id_project'><img src='images/page_white_text.png' class='top' border=0> ".$lang_label["tasks"]." ( $task_number ) </a>";		
	else 
		echo "<a href='index.php?sec=projects&sec2=operation/projects/task&id_project=$id_project'><img src='images/page_white_text.png' class='top' border=0> ".$lang_label["tasks"]."</a>";		
	echo "</li>";
	
	// Workunits
	$totalhours =  give_hours_project ($id_project);
	echo "<li class='nomn'>";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/task_workunit&id_project=$id_project&id_task=-1'><img src='images/award_star_silver_1.png' class='top' border=0> ".$lang_label["workunits"]." ($totalhours hr)</a>";
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
} 


// Show result of previous operations (before tabs)
if ($result_output != "")
	echo $result_output;

// Create project form

if (isset($_GET["insert_form"])){
	$email_notify=0;
	$iduser_temp=$_SESSION['id_usuario'];
	$titulo = "";
	$prioridad = 0;
	$id_grupo = 0;
	$grupo = dame_nombre_grupo(1);

	$usuario= $_SESSION["id_usuario"];
	$estado = 0;
	$actualizacion=date("Y/m/d H:i:s");
	$inicio = $actualizacion;
	$id_creator = $iduser_temp;
	$create_mode = 1;
} 

// ********************************************************************************************************
// Show the form
// ********************************************************************************************************

if ($create_mode == 1)
	echo "<form name='projectf' method='POST' action='index.php?sec=projects&sec2=operation/projects/project&action=insert'>";
else
	echo "<form name='projectf' method='POST' action='index.php?sec=projects&sec2=operation/projects/project_detail&action=update'>";

if (isset($id_project)) {
	echo "<input type='hidden' name='id_project' value='".$id_project."'>";
}
 
// --------------------
// Main project table
// --------------------

echo "<h2>".$lang_label["project_management"]." -&gt;";
if ($create_mode = 0){
	echo $lang_label["rev_project"]." # ".$id_inc."</h2>";
} else {
	echo $lang_label["create_project"]."</h2>";
}

echo '<table width=700 class="databox_color" cellpadding=3 cellspacing=3>';

// Name

echo '<tr><td class="datos"><b>'.$lang_label["name"].'</b>';
echo '<td colspan=2 class="datos"><input type="text" name="name" size=50 value="'.$name.'">';

// Private
echo '<td class="datos">';
if ($private == 1)
	echo "<input type=checkbox value=1 name='private' CHECKED>";
else
	echo "<input type=checkbox value=1 name='private'>";
echo " <b>".$lang_label["private"]."</b>";

// start and end date
echo '<tr><td class="datos2"><b>'.$lang_label["start"].'</b>';
echo "<td class='datos2'>";

echo "<input type='text' id='start_date' name='start_date' size=10 value='$start_date'> <img src='images/calendar_view_day.png' onclick='scwShow(scwID(\"start_date\"),this);'> ";
echo '<td class="datos2"><b>'.$lang_label["end"].'</b>';
echo "<td class='datos2'>";
echo "<input type='text' id='end_date' name='end_date' size=10 value='$end_date'> <img src='images/calendar_view_day.png' title='Click Here' alt='Click Here' onclick='scwShow(scwID(\"end_date\"),this);'>";

// Group and owner

echo '<tr><td class="datos"><b>'.$lang_label["group"].'</b>';
echo "<td class='datos'>";
combo_groups ($group);

echo '<td class="datos"><b>'.$lang_label["owner"].'</b>';
echo "<td class='datos'>";
combo_users ($owner);

// Description

echo '<tr><td class="datos2" colspan="4"><textarea name="description" rows="15" cols="85" style="height: 200px">';
	echo $description;
echo "</textarea>";

echo "</table>";

if ($create_mode == 0){
	echo '<input type="submit" class="sub next" name="accion" value="'.$lang_label["in_modinc"].'" border="0">';
} else {
	echo '<input type="submit" class="sub create" name="accion" value="'.$lang_label["create"].'" border="0">';
}
echo "</form>";
echo "</table>";


?>
