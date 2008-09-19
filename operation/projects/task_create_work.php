<?PHP

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


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// ADD WORK UNIT CONTROL ( TASK )
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

global $config;
if (check_login() != 0) {
	audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access","Trying to access task workunit assignment");
	require ("general/noaccess.php");
	exit;
}

$id_project = get_parameter_get ("id_project", -1);
$id_workunit = get_parameter_get ("id_workunit", -1);
$id_task = get_parameter_get ("id_task", -1);
$task_name = get_db_value ("name", "ttask", "id", $id_task);

//if (($id_project != -1) && ($id_workunit != -1)) OR ($id_workunit {
if ($id_workunit != -1){
	$row = get_db_row ("tworkunit", "id", $id_workunit);
	$id_user = $row["id_user"];
	$duration =$row["duration"]; 
	$description = $row["description"];
	$have_cost = $row["have_cost"];
	$id_profile = $row["id_profile"];
	$ahora = $row["timestamp"];
	$ahora_date = substr($ahora,0,10);
	$ahora_time = substr($ahora,10,8);
	
	
} else {
	$id_user = $config["id_user"];
	$duration = 1; 
	$description = "";
	$have_cost = 0;
	$id_profile = "";
	$ahora_date = date("Y-m-d");
	$ahora_time = date("H:i:s");
	$ahora = date("Y-m-d H:i:s");
}

if ((project_manager_check($id_project) == 1) OR ($id_user = $config["id_user"])) {
	echo "<h3><img src='images/award_star_silver_1.png'>&nbsp;&nbsp;";
	if ($id_workunit != -1)
		echo lang_string ("update_workunit")." - $task_name</h3>";
	else
		echo $lang_label["add_workunit"]." - $task_name</h3>";

	echo "<table cellpadding=4 cellspacing=4 border=0 width='700' class='databox_color' >";
	// Insert or edit mode ?
	if ($id_workunit != -1){
		// Update
		echo "<form name='nota' method='post' action='index.php?sec=projects&sec2=operation/projects/task_workunit&operation=workunit&id_task=$id_task&id_project=$id_project&id_workunit=$id_workunit'>";
	} else { // insert
		echo "<form name='nota' method='post' action='index.php?sec=projects&sec2=operation/projects/task_workunit&operation=workunit&id_task=$id_task&id_project=$id_project'>";
	}
	echo "<tr><td class='datos' width='140'><b>".$lang_label["date"]."</b></td>";
	echo "<td class='datos'>";

	echo "<input type='text' id='date' name='date' size=10 value='$ahora_date'> <img src='images/calendar_view_day.png' onclick='scwShow(scwID(\"date\"),this);'> ";

	echo "&nbsp;&nbsp;";
	echo "<input type='text' name='time' size=10 value='$ahora_time'>";

	echo "<tr><td class='datos2'  width='140'>";
	echo "<b>".$lang_label["profile"]."</b>";
	echo "<td class='datos2'>";
	echo combo_user_task_profile ($id_task,"work_profile",$id_profile, $id_user);
	echo "&nbsp;&nbsp;";
	if ($have_cost == 1)
		echo "<input type='checkbox' name='have_cost' value=1 checked>";
	else
		echo "<input type='checkbox' name='have_cost' value=1>";
	echo "&nbsp;&nbsp;";
	echo "<b>".$lang_label["have_cost"]."</b>";

	echo "<tr><td class='datos'>";
	echo "<b>".$lang_label["time_used"]."</b>";
	echo "<td class='datos'>";
	echo "<input type='text' name='duration' value='$duration' size='7'>"." ".lang_string("hr");
	
	echo '<tr><td colspan="2" class="datos2"><textarea name="description" style="height: 250px; width: 100%;">';
	echo $description;
	echo '</textarea>';
	echo "</tr></table>";
	echo "<table width=710>";
	echo "<tr><td align=right>";
	if ($id_workunit != -1)
		echo '<input name="addnote" type="submit" class="sub upd" value="'.$lang_label["update"].'">';
	else
		echo '<input name="addnote" type="submit" class="sub next" value="'.$lang_label["add"].'">';
	echo "</td></tr></table>";
	echo "</form>";
}
?>
