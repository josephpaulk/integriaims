<?php

// Integria 1.1 - http://integria.sourceforge.net
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

// Load global vars

global $config;

if (check_login() != 0) {
 	audit_db("Noauth",$config["REMOTE_ADDR"], "No authenticated access","Trying to access event viewer");
	require ("general/noaccess.php");
	exit;
}
	
if (give_acl($config["id_user"], 0, "IR") != 1){
 	// Doesn't have access to this page
	audit_db ($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access to project detail page");
	include ("general/noaccess.php");
	exit;
}


$create_mode = 0;
$name = "";
$description = "";
$end_date = "";
$start_date = "";
$id_project = -1; // Create mode by default
$result_output = "";


// ---------------
// Update project
// ---------------

if ((isset($_GET["action"])) AND ($_GET["action"]=="update")){
	$id_project = $_POST["id_project"];
	$id_owner = give_db_value ( 'id_owner', 'tproject', 'id', $id_project);
	if ((give_acl($config["id_user"], 0, "PW") ==1) OR ($config["id_user"] == $id_owner )) {
		$user = give_parameter_post ("user");
		$name = give_parameter_post ("name");
		$description = give_parameter_post ('description');
		$start_date = give_parameter_post ('start_date');
		$end_date = give_parameter_post ('end_date');
		$sql = "UPDATE tproject SET 
				name = '$name',
				description = '$description',
				start = '$start_date',
				end = '$end_date',
				id_owner = '$user' 
				WHERE id = $id_project";
		$result = mysql_query($sql);
		audit_db($config["id_user"], $config["REMOTE_ADDR"], "Project updated", "Project $name");
		if ($result)
			$result_output = "<h3 class='suc'>".$lang_label["update_ok"]."</h3>";
		else
			$result_output = "<h3 class='error'>".$lang_label["update_no"]."</h3>";
		$_GET["id"] = $id_project;
	} else {
		audit_db ($config["id_user"] ,$config["REMOTE_ADDR"], "ACL Violation","Trying to update an unauthorized Project");
		include ("general/noaccess.php");
	exit;
	}
}

// ---------------------
// Edition / View mode
// ---------------------
$id_project = give_parameter_get ("id_project", 0);

if ( $id_project != 0){	
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
	$owner = $row["id_owner"];
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
	$owner = $config["id_user"];
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
	echo "<form name='projectf' method='POST' action='index.php?sec=projects&sec2=operation/projects/project_detail&action=update&id_project=$id_project'>";

if (isset($id_project)) {
	echo "<input type='hidden' name='id_project' value='".$id_project."'>";
}
 
// --------------------
// Main project table
// --------------------

echo "<h2>".$lang_label["project_management"]." -&gt;";
if ($create_mode == 0){
	echo $lang_label["rev_project"]." </h2><h3>".give_db_value ("name", "tproject", "id", $id_project)."</h3>";
} else {
	echo $lang_label["create_project"]."</h2>";
}

echo '<table width=740 class="databox_color" cellpadding=4 cellspacing=4>';

// Name

echo '<tr><td class="datos"><b>'.$lang_label["name"].'</b>';
echo '<td colspan=2 class="datos"><input type="text" name="name" size=40 value="'.$name.'">';

// start and end date
echo '<tr><td class="datos2"><b>'.$lang_label["start"].'</b>';
echo "<td class='datos2'>";

echo "<input type='text' id='start_date' name='start_date' size=10 value='$start_date'> <img src='images/calendar_view_day.png' onclick='scwShow(scwID(\"start_date\"),this);'> ";
echo '<td class="datos2"><b>'.$lang_label["end"].'</b>';
echo "<td class='datos2'>";
echo "<input type='text' id='end_date' name='end_date' size=10 value='$end_date'> <img src='images/calendar_view_day.png' title='Click Here' alt='Click Here' onclick='scwShow(scwID(\"end_date\"),this);'>";

// Owner

echo '<tr>';
echo '<td class="datos"><b>'.$lang_label["project_manager"].'</b>';
echo "<td class='datos'>";
$id_owner = give_db_value ( 'id_owner', 'tproject', 'id', $id_project);
if ((give_acl($config["id_user"], 0, "PM") ==1) OR ($config["id_user"] == $id_owner )) {
	combo_user_visible_for_me ($id_owner,"user", 0, "PR");
} else {
	echo $id_owner;
}

if ($create_mode == 0){

echo '<td class="datos"><b>'.lang_string ("Current progress").'</b>';
echo "<td class='datos'>";
$completion =  format_numeric(calculate_project_progress ($id_project));
echo "<img src='include/functions_graph.php?type=progress&width=90&height=20&percent=$completion'>";

echo '<tr>';
echo '<td class="datos2"><b>'.lang_string("Total workunit (hr)").'</b>';
echo "<td class='datos2'>";
$total_hr = give_hours_project ($id_project);
echo $total_hr;
echo '<td class="datos2"><b>'.lang_string("Total people involved").'</b>';
echo "<td class='datos2'>";
$people_inv = give_db_sqlfree_field ("SELECT COUNT(DISTINCT id_user) FROM trole_people_task, ttask WHERE ttask.id_project=$id_project AND ttask.id = trole_people_task.id_task;");
echo $people_inv;


echo '<tr>';
echo '<td class="datos"><b>'.lang_string("Total payable workunit (hr)").'</b>';
echo '<td class="datos">';
$pr_hour = give_hours_project ($id_project, 1);
echo $pr_hour;

echo '<td class="datos"><b>'.lang_string("Project profitability").'</b>';
echo '<td class="datos">';
$total = project_workunit_cost ($id_project, 1);
$real = project_workunit_cost ($id_project, 0);
if ($real > 0){
    echo format_numeric(($total/$real)*100);
    echo  " %" ;
}

echo '<tr>';
echo '<td class="datos2"><b>'.lang_string("Project costs").'</b>';
echo "<td class='datos2'>";
echo $real. " ". $config["currency"];
echo '<td class="datos2"><b>'.lang_string("Charged to customer").'</b>';
echo "<td class='datos2'>";
echo $total." ". $config["currency"];


echo '<tr>';
echo '<td class="datos"><b>'.lang_string("Charged cost per hour").'</b>';
echo '<td class="datos">';
if (($people_inv > 0) AND ($total_hr >0))
    echo format_numeric ($total/($total_hr/$people_inv)). " ". $config["currency"];
else
    echo "N/A";
echo '<td class="datos"><b>'.lang_string("Proyect length deviation (days)").'</b>';
echo '<td class="datos">';
$expected_length = give_db_sqlfree_field ("SELECT SUM(hours) FROM ttask WHERE id_project = $id_project");
$deviation = format_numeric(($pr_hour-$expected_length)/$config["hours_perday"]);
echo $deviation. " ".lang_string("days");
}

// Description

echo '<tr><td class="datos2" colspan="4"><textarea name="description" rows="15" cols="90" style="height: 200px">';
	echo $description;
echo "</textarea>";

echo "</table>";
echo '<table width=740 class="databox_color" cellpadding=3 cellspacing=3>';
echo "<tr><td align=right>";

if ((give_acl($config["id_user"], 0, "PM") ==1) OR ($config["id_user"] == $id_owner )) {
    if ($create_mode == 0){
    	echo '<input type="submit" class="sub next" name="accion" value="'.$lang_label["update"].'" border="0">';
    } else {
    	echo '<input type="submit" class="sub create" name="accion" value="'.$lang_label["create"].'" border="0">';
    }
}
echo "</form>";
echo "</table>";

echo "<h3>".lang_string("Project schema")."</h3>";
echo "<img src=include/functions_graph.php?type=project_tree&id_project=$id_project&id_user=$id_user>";
?>
