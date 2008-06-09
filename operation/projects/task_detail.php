<?php

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

if (check_login() != 0) {
    audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access", "Trying to access event viewer");
    require ("general/noaccess.php");
    exit;
}

// Get our main stuff
$id_user = $_SESSION['id_usuario'];
$id_project = give_parameter_get ("id_project", -1);
$id_task = give_parameter_get ("id_task", -1);
$project_manager = give_db_sqlfree_field("SELECT id_owner FROM tproject WHERE id = $id_project");
$operation =  give_parameter_get ("operation", "");

$hours = 0;
$estimated_cost = 0;

// Get names
if ($id_project != 1)
	$project_name = give_db_value ("name", "tproject", "id", $id_project);
else
	$project_name = "";

if ($id_task != 1)
	$task_name = give_db_value ("name", "ttask", "id", $id_task);
else
	$task_name = "";

// Init variables
$name = "";
$description = "";
$end = date("Y-m-d");
$start = date("Y-m-d");
$completion = 0;
$priority = 1;
$id_group = 1;
$result_output = "";
$parent=-1;

if ($operation == ""){
	// Doesn't have access to this page
	audit_db($id_user, $config["REMOTE_ADDR"], "ACL Violation","Trying to access to task manager withour project");
	include ("general/noaccess.php");
	exit;
}

// -----------
// Create task
// -----------
if ($operation == "insert"){
	$name = give_parameter_post ("name");
	$description = give_parameter_post ("description");
	$priority = give_parameter_post ("priority", 0);
	$completion = give_parameter_post ("completion", 0);
	$parent = give_parameter_post ("parent", 0);
	$start = give_parameter_post ("start_date", date("Y-m-d"));
	$hours = give_parameter_post ("hours", 0);
        $estimated_cost = give_parameter_post ("estimated_cost", 0);
	$id_group = give_parameter_post ("group",1);
	$sql = "INSERT INTO ttask
			(id_project, name, description, priority, completion, start,  id_parent_task, id_group, hours, estimated_cost) VALUES
			($id_project, '$name', '$description', '$priority', '$completion', '$start',  '$parent', $id_group, '$hours', '$estimated_cost')";
	if (mysql_query($sql)){
		$id_task = mysql_insert_id();
		$result_output = "<h3 class='suc'>".$lang_label["create_ok"]."</h3>";
		audit_db ($id_user, $config["REMOTE_ADDR"], "Task added to project", "Task '$name' added to project '$id_project'");
		$operation = "view";

        // Add all users assigned to current project for new task or parent task if has parent
        if ($parent != 0)
            $query1="SELECT * FROM trole_people_task WHERE id_task = $parent";
        else
            $query1="SELECT * FROM trole_people_project WHERE id_project = $id_project";
        $resq1=mysql_query($query1);
        while ($row=mysql_fetch_array($resq1)){
            $id_role_tt = $row["id_role"];
            $id_user_tt = $row["id_user"];
            $sql = "INSERT INTO trole_people_task
            (id_task, id_user, id_role) VALUES
            ($id_task, '$id_user_tt', $id_role_tt)";
            mysql_query($sql);
        }
        task_tracking ( $config["id_user"], $id_task, 11, 0, 0);
	} else {
		$update_mode = 0;
		$create_mode = 1;
		$result_output = "<h3 class='error'>".$lang_label["create_no"]."</h3>";
	}
}

// -----------
// Update task
// -----------
if ($operation == "update"){
	if ($id_task == -1){
		audit_db($id_user, $config["REMOTE_ADDR"], "ACL Violation","Trying to access to update invalid Task");
		include ("general/noaccess.php");
		exit;
	}
	$name = give_parameter_post ("name");
	$description = give_parameter_post ("description");
	$priority = give_parameter_post ("priority");
	$completion = give_parameter_post ("completion");
	$parent = give_parameter_post ("parent");
	$start = give_parameter_post ("start_date");
	$hours = give_parameter_post ("hours",0);
        $estimated_cost = give_parameter_post ("estimated_cost",0);
	$id_group = give_parameter_post ("group",1);
	$sql = "UPDATE ttask SET 
			name = '$name',
			description = '$description',
			priority = '$priority',
			completion = '$completion',
			start = '$start',
			hours = '$hours',
            estimated_cost = '$estimated_cost',
			id_parent_task = '$parent',
			id_group = '$id_group'
			WHERE id = $id_task";
	if (mysql_query($sql)){
		$result_output = "<h3 class='suc'>".$lang_label["update_ok"]."</h3>";
		audit_db ($id_user, $config["REMOTE_ADDR"], "Task updated", "Task '$name' updated to project '$id_project'");
		$operation = "view";
        task_tracking ( $config["id_user"], $id_task, 12);
	} else {
		$result_output = "<h3 class='error'>".$lang_label["update_no"]."</h3>";
		echo "DEBUG $sql";
	}
}

// ---------------------
// Edition / View mode
// ---------------------
if ($operation == "view"){
	$sql1='SELECT * FROM ttask WHERE id = '.$id_task;
	if (!$result = mysql_query($sql1)){
        audit_db ($_SESSION['id_usuario'], $config["REMOTE_ADDR"], "ACL Violation","Trying to access to other task hacking with URL");
        include ("general/noaccess.php");
        exit;
    }
	$row=mysql_fetch_array($result);
	// Get values
	$name = clean_input ($row["name"]);
	$description = $row["description"];
	$completion = clean_input ($row["completion"]);
	$priority = clean_input ($row["priority"]);
	$dep_type = clean_input ($row["dep_type"]);
	$start = clean_input ($row["start"]);
	$estimated_cost = clean_input ($row["estimated_cost"]);
    $hours = clean_input ($row["hours"]);
	$parent = clean_input ($row["id_parent_task"]);
	$id_group = clean_input ($row["id_group"]);
        
} 

echo $result_output;

// ********************************************************************************************************
// Show forms
// ********************************************************************************************************

if ($operation == "create")
	echo "<form name='projectf' method='POST' action='index.php?sec=projects&sec2=operation/projects/task_detail&operation=insert&id_project=$id_project'>";
else
	echo "<form name='projectf' method='POST' action='index.php?sec=projects&sec2=operation/projects/task_detail&operation=update&id_project=$id_project&id_task=$id_task'>";
 
// --------------------
// Main task form table
// --------------------

echo "<h2>".$lang_label["task_management"]." -&gt;";

if ($operation != "create"){
	echo $lang_label["rev_task"]." ( $project_name )</h2>";
} else {
	echo $lang_label["create_task"]." ( $project_name )</h2>";
}

echo '<table border=0 width=750 class="databox_color" cellpadding=4 cellspacing=4>';

// Name
echo '<tr><td class="datos"><b>'.$lang_label["name"].'</b>';
echo '<td class="datos"><input type="text" name="name" size=30 value="'.$name.'">';

// Workunit distribution graph
echo "<td rowspan=6>";
echo '<table border=0 class="databox_color" cellpadding=3 cellspacing=3>';
echo "<tr><td>";
echo "<i>Workunit distribution</i><br>";
echo "<img src='include/functions_graph.php?type=workunit_task&width=200&height=170&id_task=$id_task'>";
echo "</table>";

// Parent task
echo "<tr>";
echo '<td class="datos2">';
echo "<b>".lang_string ("Parent task")."</b> ";
echo '<td class="datos2">';
echo '<select name="parent">';

if ($parent > 0)
	echo "<option value='$parent'>".give_db_value ("name", "ttask", "id", $parent);

echo "<option value=0>".$lang_label["none"];
$query1="SELECT * FROM ttask WHERE id_project = $id_project and id != $id_task and id != $parent";
$resq1=mysql_query($query1);
while ($row=mysql_fetch_array($resq1)){
	echo "<option value='".$row["id"]."'>".substr($row["name"],0,20);
}echo "</select>";

// Priority
echo "<tr>";
echo '<td class="datos">';
echo '<b>'.$lang_label["priority"].'</b>';
echo '<td class="datos">';
echo "<select name='priority'>";
if ($priority != "")
	echo "<option value='$priority'>".render_priority ($priority);
for ($ax=0; $ax < 5; $ax++){
	echo "<option value='$ax'>".render_priority ($ax);
}
echo "</select>";


// start date
echo '<tr><td class="datos2"><b>'.$lang_label["start"].'</b>';
echo "<td class='datos2'>";
echo "<input type='text' id='start_date' name='start_date' size=10 value='$start'> <img src='images/calendar_view_day.png' onclick='scwShow(scwID(\"start_date\"),this);'> ";

// Estimated hours
echo '<tr><td class="datos"><b>'.lang_string ("Estimated hours").'</b>';
echo "<td class='datos'>";
echo "<input type='text' name='hours' size=5 value='$hours'>";

// Real hours 
echo '<tr><td class="datos2">';
echo "<b>".lang_string("Worked hours").": </b> ";
echo "<td class='datos2'><i>";
echo give_hours_task ($id_task);
echo " </i>".lang_string ("hr");

// Estimated cost
echo '<tr><td class="datos"><b>'.lang_string ("Estimated cost").'</b>';
echo "<td class='datos'>";
echo "<input type='text' name='estimated_cost' size=7 value='$estimated_cost'>";
echo " ".$config["currency"];

// Cost estimation graph
echo "<td rowspan=5>";
echo '<table border=0 class="databox_color" cellpadding=1 cellspacing=4>';
echo "<tr><td>";
echo "<i>".lang_string("Cost estimation")."</i>";
echo "<tr><td>";
$labela=lang_string("Est.");
$labelb=lang_string("Real");
$a = $estimated_cost;
$b = round (task_workunit_cost ($id_task, 1));
$max = maxof($a, $b);
echo "<img src='include/functions_graph.php?type=histogram&width=200&height=30&a=$a&b=$b&labela=$labela&labelb=$labelb&max=$max'>";

// Imputable cost graph
echo "<tr><td>";
echo "<i>".lang_string("Imputable costs")."</i>";
echo "<tr><td>";
$labela=lang_string("Tot");
$labelb=lang_string("Imp");
$a = round (task_workunit_cost ($id_task, 0));
$b = round (task_workunit_cost ($id_task, 1));
$max = maxof($a, $b);
echo "<img src='include/functions_graph.php?type=histogram&width=200&height=30&a=$a&b=$b&labela=$labela&labelb=$labelb&max=$max'>";

// Task hours graph
echo "<tr><td>";
echo "<i>".lang_string("Estimated hours")."</i>";
echo "<tr><td>";
$labela=lang_string("Est.");
$labelb=lang_string("Real");
$a = round ($hours);
$b = round (give_hours_task ($id_task));
$max = maxof($a, $b);
echo "<img src='include/functions_graph.php?type=histogram&width=200&height=30&a=$a&b=$b&labela=$labela&labelb=$labelb&max=$max'>";
echo "</table>";


// Real costs (imputable)
echo '<tr><td class="datos2">';
echo "<b>".lang_string ("Imputable costs")."</b>";
echo "<td class='datos2'><i>";
echo task_workunit_cost ($id_task, 1);
echo " </i>".$config["currency"];

// Real costs (total)
echo '<tr><td class="datos">';
echo "<b>".lang_string ("Total costs")."</b>";
echo "<td class='datos'><i>";
echo task_workunit_cost ($id_task, 0);
echo " </i>".$config["currency"];

// Group for this task
echo '<tr><td class="datos2"><b>'.$lang_label["group"].'</b>';
echo '<td class="datos2">';
echo combo_groups($id_group, "TW");


// Completion
echo '<tr><td class="datos"><b>'.$lang_label["completion"].'</b>';
echo '<td class="datos">';
echo "<select name='completion'>";
if ($completion != "")
	echo '<option value='.$completion.'>'.$completion."%";
echo "<option value=0> 0%";
echo "<option value=10> 10%";
echo "<option value=20> 20%";
echo "<option value=30> 30%";
echo "<option value=40> 40%";
echo "<option value=50> 50%";
echo "<option value=60> 60%";
echo "<option value=70> 70%";
echo "<option value=80> 80%";
echo "<option value=90> 90%";
echo "<option value=100> 100%";
echo "</select>";

// Description

echo '<tr><td class="datos2" colspan="3">';
echo '<b>'.lang_string("Description").'</b>';
echo '<tr><td class="datos2" colspan="3">
<textarea name="description" style="height: 350px; width: 100%;">';
	echo $description;
echo "</textarea>";
echo "</td></tr>";
echo "</table>";

if ((give_acl($config["id_user"], $id_group, "TM") ==1) OR ($config["id_user"] == $project_manager )) {
    echo "<table width=760>";
    echo "<tr><td align=right>";
    if ($operation != "create")
	    echo '<input type="submit" class="sub next" name="accion" value="'.$lang_label["update"].'" border="0">';
    else 
	    echo '<input type="submit" class="sub create" name="accion" value="'.$lang_label["create"].'" border="0">';
    
    echo "</form>";
    echo "</table>";
}

?>
