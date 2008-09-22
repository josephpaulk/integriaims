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


// Load global vars

?>

<script language="javascript">

	/* Function to hide/unhide a specific Div id */
	function toggleDiv (divid){
		if (document.getElementById(divid).style.display == 'none'){
			document.getElementById(divid).style.display = 'block';
		} else {
			document.getElementById(divid).style.display = 'none';
		}
	}
</script>

<?PHP

global $config;

if (check_login() != 0) {
    audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access", "Trying to access event viewer");
    require ("general/noaccess.php");
    exit;
}

// Get main variables and init
$id_task = give_parameter_get ("id_task", -1);
// id_task = -1 is for project people management, different than people task management

$id_project = give_parameter_get ("id_project", 0);
$operation = give_parameter_get ("action");
$result_output = "";


// -----------
// Add user for this task
// -----------
if ($operation == "insert"){
	$role = give_parameter_post ("role",0);
	$user = give_parameter_post ("user");
	// People add for TASK
	if ($id_task != -1){
		$temp_id_user = give_db_value ("id_user", "trole_people_project", "id", $user);
		$temp_id_role = give_db_value ("id_role", "trole_people_project", "id", $user);
		$sql = "INSERT INTO trole_people_task
			(id_task, id_user, id_role) VALUES
			($id_task, '$temp_id_user', '$temp_id_role')";
        task_tracking ( $config["id_user"], $id_task, 18);
	// People add for whole PROJECT
	} else {
		$sql = "INSERT INTO trole_people_project
			(id_project, id_user, id_role) VALUES
			($id_project, '$user', '$role')";
	}
	
	if (mysql_query($sql)){
		$id_task_inserted = mysql_insert_id();
		$result_output = "<h3 class='suc'>".$lang_label["create_ok"]."</h3>";
		if ($id_task != -1){
			audit_db ($config["id_user"], $config["REMOTE_ADDR"], "User/Role added to task", "User $user added to task ".give_db_value ("name", "ttask", "id", $id_task));
		} else {
			audit_db ($config["id_user"], $config["REMOTE_ADDR"], "User/Role added to project", "User $user added to project ".give_db_value ("name", "tproject", "id", $id_project));
		}
		$operation = "view";
	} else {
		$update_mode = 0;
		$create_mode = 1;
		$result_output = "<h3 class='error'>".$lang_label["create_no"]."</h3>";
	}
}

// DELETE Users from this project / task

if ($operation == "delete"){
	$id = give_parameter_get ("id",-1);

	// People delete for TASK
	if ($id_task != -1){
		$sql = "DELETE FROM trole_people_task WHERE id = $id";
        task_tracking ( $config["id_user"], $id_task, 18);
	// People delete for whole PROJECT
	} else {
		$sql = "DELETE FROM trole_people_project WHERE id = $id";
	}
	if (mysql_query($sql)){
		$result_output = "<h3 class='suc'>".$lang_label["delete_ok"]."</h3>";
		$operation = "view";
	}
}


// ---------------------
// Edition / View mode
// ---------------------


echo $result_output;
 
// --------------------
// Main task form table
// --------------------

if ($id_task != -1){
	echo "<h2>".$lang_label["task_people_management"]."</h2>";
	echo "<h3>".give_db_value('name', 'ttask','id',$id_task)."</h3><br>";

	$sql = "SELECT COUNT(*) FROM trole_people_task where id_task = $id_task";
	$result = mysql_query($sql);
	$row=mysql_fetch_array($result);
	if ($row[0] > 0){
		echo "<h3>".$lang_label["assigned_roles"]."</h3>";
		$sql = "SELECT * FROM trole_people_task where id_task = $id_task";
		$result = mysql_query($sql);
		echo "<table width=500 class='listing'>";
		echo "<th>".$lang_label["user"];
		echo "<th>".$lang_label["role"];
		if ($config["id_user"] == give_db_value('id_owner','tproject','id', $id_project) OR
		give_acl ($config["id_user"], give_db_value('id_group','ttask','id', $id_task), "TM"))
			echo "<th>".$lang_label["delete"];
			
		$color = 1;
		while ($row=mysql_fetch_array($result)){
			if ($color == 1){
				$tdcolor = "datos";
				$color = 0;
			}
			else {
				$tdcolor = "datos2";
				$color = 1;
			}
			echo "<tr><td valign='top' class='$tdcolor'>".$row["id_user"];
			echo "<td valign='top' class='$tdcolor'>".give_db_value('name','trole','id',$row["id_role"]);
			if ($config["id_user"] == give_db_value('id_owner','tproject','id', $id_project) OR
			give_acl ($config["id_user"], give_db_value('id_group','ttask','id', $id_task), "TM")){
				echo "<td valign='top' class='$tdcolor' align='center'>";
				echo "<a href='index.php?sec=projects&sec2=operation/projects/people_manager&id_project=$id_project&id_task=$id_task&action=delete&id=".$row["id"]."' onClick='if (!confirm(\' ".$lang_label["are_you_sure"]."\')) return false;'><img src='images/cross.png' border='0'></a>";
			}
		}
		echo "</table>";
	}
} else {

// MAIN PROJECT PEOPLE LIST
	echo "<h2>".$lang_label["project_people_management"]."</h2>";
	echo "<h3>".give_db_value('name', 'tproject','id',$id_project)."</h3><br>";

	if ($config["id_user"] != give_db_value('id_owner','tproject','id', $id_project) AND
		give_acl ($config["id_user"], give_db_value('id_group','ttask','id', $id_task), "PM")!=1){
		audit_db("Project People Management", $config["REMOTE_ADDR"], "Unauthorized access", "Try to access people project management");
    	require ("general/noaccess.php");
    	exit;
	}

	$sql = "SELECT COUNT(*) FROM trole_people_project WHERE id_project = $id_project";
	$result = mysql_query($sql);
	$row=mysql_fetch_array($result);
	if ($row[0] > 0){
		echo "<h3>".$lang_label["assigned_roles"]."</h3>";
		$sql = "SELECT * FROM trole_people_project WHERE id_project = $id_project";
		$result = mysql_query($sql);
		echo "<table width=500 class='listing'>";
		echo "<th>".$lang_label["user"];
		echo "<th>".$lang_label["role"];
		echo "<th>".$lang_label["delete"];
		$color = 1;
		while ($row=mysql_fetch_array($result)){
			if ($color == 1){
				$tdcolor = "datos";
				$color = 0;
			}
			else {
				$tdcolor = "datos2";
				$color = 1;
			}
			echo "<tr><td valign='top' class='$tdcolor'>".$row["id_user"];
			echo "<td valign='top' class='$tdcolor'>".give_db_value('name','trole','id',$row["id_role"]);
			echo "<td valign='top' class='$tdcolor' align='center'>";
			echo "<a href='index.php?sec=projects&sec2=operation/projects/people_manager&id_project=$id_project&id_task=$id_task&action=delete&id=".$row["id"]."' onClick='if (!confirm(\' ".$lang_label["are_you_sure"]."\')) return false;'><img src='images/cross.png' border='0'></a>";
		}
		echo "</table>";
	}
}

// Role / task assigment
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Only project owner or Project ADMIN could modify
if ($id_task != -1){
	if ($config["id_user"] == give_db_value('id_owner','tproject','id', $id_project) OR
		give_acl ($config["id_user"], give_db_value('id_group','ttask','id', $id_task), "TM")){
		
        // Task people manager editor
        // ===============================
		echo "<h3>".$lang_label["roletask_assignment"]."</h3>";
		echo "<form method='post' action='index.php?sec=projects&sec2=operation/projects/people_manager&id_project=$id_project&id_task=$id_task&action=insert'>";
		echo "<table cellpadding=4 cellspacing=4 width=500 class='databox_color'>";

		echo "<tr>";
		echo "<td valign='top' class='datos2'>";
		echo $lang_label["user"]. "/".$lang_label["role"];
		echo "<td valign='top' class='datos2'>";
		echo combo_users_project($id_project);
		echo "</table>";
		echo "<table class='button' width=510>";
		echo "<tr><td align='right'>";
		echo "<input type=submit class='sub next' value='".$lang_label["update"]."'>";
		echo "</table>";
	}
} else {
    // PROYECT PEOPLE MANAGER editor
    // ===============================
	if ($config["id_user"] != give_db_value('id_owner','tproject','id', $id_project) AND
		give_acl ($config["id_user"], give_db_value('id_group','ttask','id', $id_task), "PM")!=1){
		audit_db("Project People Management", $config["REMOTE_ADDR"], "Unauthorized access", "Try to access people project management");
    	require ("general/noaccess.php");
    	exit;
	}

	echo "<h3>".$lang_label["role_project_assignment"]."</h3>";
	echo "<form method='post' action='index.php?sec=projects&sec2=operation/projects/people_manager&id_project=$id_project&id_task=$id_task&action=insert'>";
	echo "<table width=500 class='databox_color'>";

	echo "<tr><td valign='top' class='datos2'>";
	echo $lang_label["role"];
	echo "<td valign='top' class='datos2'>";
	echo combo_roles ();

	echo "<td valign='top' class='datos2'>";
	echo $lang_label["user"];
	echo "<td valign='top' class='datos2'>";
    combo_user_visible_for_me ($config["id_user"], "user", 0, "PR");
	echo "</table>";
		
	echo "<table class='button' width=500>";
	echo "<tr><td align='right'>";
	echo "<input type=submit class='sub next' value='".$lang_label["update"]."'>";
	echo "</table>";
}

// Role informational table
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

?>
		<h3><img src='images/award_star_silver_1.png'>&nbsp;&nbsp;
		<a href="javascript:;" onmousedown="toggleDiv('arole');">
	<?PHP

echo " ".$lang_label["available_roles"]."</a></h3>";
echo "<div id='arole' style='display:none'>";
echo "<table cellpadding=4 cellspacing=4 width=700 class='databox_color'>";
echo "<th>".$lang_label["name"];
echo "<th>".$lang_label["description"];
echo "<th>".$lang_label["cost"];
$sql1='SELECT * FROM trole ORDER BY name';
$result=mysql_query($sql1);
$color=1;
while ($row=mysql_fetch_array($result)){
	if ($color == 1){
		$tdcolor = "datos";
		$color = 0;
	}
	else {
		$tdcolor = "datos2";
		$color = 1;
	}
	echo "<tr><td valign='top' class='$tdcolor'><b>".$row["name"]."</b>";
	echo '<td valign="top" class="'.$tdcolor.'">'.$row["description"];
	echo '<td valign="top" class="'.$tdcolor.'" align="center">'.$row["cost"];
}
echo "</table></div>";



?>
