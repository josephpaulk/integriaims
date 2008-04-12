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

// Load global vars

global $config;

if (check_login() != 0) {
    audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access", "Trying to access event viewer");
    require ("general/noaccess.php");
    exit;
}

$id_user = $_SESSION["id_usuario"];
$operation = give_parameter_get ("operation");

// -----------
// Workunit
// -----------
if ($operation == "addworkunit"){
	$duration = give_parameter_post ("duration",0);
	if (!is_numeric( $duration))
		$duration = 0;
	$timestamp = give_parameter_post ("workunit_date");
	$description = give_parameter_post ("description");
	$have_cost = give_parameter_post ("have_cost",0);
	$user_role = give_parameter_post ("role",0);
	$task = give_parameter_post ("task",-1);
	$role = give_parameter_post ("role",-1);
		
	$sql = "INSERT INTO tworkunit (timestamp, duration, id_user, description, have_cost, id_profile) VALUES	('$timestamp', $duration, '$id_user', '$description', $have_cost, $role)";
	if (mysql_query($sql)){
		$id_workunit = mysql_insert_id();
		$sql2 = "INSERT INTO tworkunit_task (id_task, id_workunit) VALUES ($task, $id_workunit)";
		if (mysql_query($sql2)){
			$result_output = "<h3 class='suc'>".$lang_label["workunit_ok"]."</h3>";
			audit_db ($id_user, $config["REMOTE_ADDR"], "Spare work unit added", "Workunit for $id_user added to Task ID #$task");
            mail_project (0, $id_user, $id_workunit, $task);
		}
	} else 
		$result_output = "<h3 class='error'>".$lang_label["workunit_no"]."</h3>";
		echo $result_output;
}

// --------------------
// Workunit / Note  form
// --------------------
if ($operation != "create"){

	$ahora = date("Y-m-d H:i:s");
	echo "<h3><img src='images/award_star_silver_1.png'> ";
	echo $lang_label["add_spare_workunit"]."</h3>";
	
	echo "<table cellpadding=3 cellspacing=3 border=0 width='700' class='databox_color'>";
	echo "<form name='nota' method='post' action='index.php?sec=users&sec2=operation/users/user_spare_workunit&operation=addworkunit'>";
	echo "<td class='datos' width=140><b>".$lang_label["date"]."</b>";
	echo "<td class='datos'>";

	echo "<input type='text' id='workunit_date' name='workunit_date' size=10 value='".substr($ahora,0,10)."'> <img src='images/calendar_view_day.png' onclick='scwShow(scwID(\"workunit_date\"),this);'> ";

	// Role
	echo "<tr><td class='datos2'>";
	echo "<b>".$lang_label["profile"]."</b>";
	echo "<td class='datos2'>";
	combo_roles(1); // role
	
	// have cost checkbox
	echo "<tr><td class='datos'>";
	echo "<b>".$lang_label["have_cost"]."</b>";
	echo "<td class='datos'>";
	echo "<input type='checkbox' name='have_cost' value=1>";

	// task id - included hard-written "VACATIONS"
	echo "<tr><td class='datos2'>";
	echo "<b>".$lang_label["task"]."</b>";
	echo "<td class='datos2'>";
	combo_task_user_participant ($id_user, 1);

	echo "<tr><td class='datos'>";
	echo "<b>".$lang_label["time_used"]."</b>";
	echo "<td class='datos'>";
	echo "<input type='text' name='duration' value='0' size='7'>";

	echo "<input type='hidden' name='timestamp' value='".$ahora."'>";
	echo '<tr><td colspan="4" class="datos2"><textarea name="description" rows="10" cols="85">';
	echo '</textarea>';
	echo "</table>";
	echo '<input name="addnote" type="submit" class="sub next" value="'.$lang_label["add"].'">';
	echo "</form>";
	
}

?>
