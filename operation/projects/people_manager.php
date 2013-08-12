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

global $config;

check_login ();

require_once ('include/functions_db.php');
require_once ('include/functions_user.php');
include_once ("include/functions_projects.php");

// Get main variables and init
$id_task = get_parameter ("id_task", -1);
// id_task = -1 is for project people management, different than people task management

$id_project = get_parameter ("id_project", 0);
$operation = get_parameter ("action");
$result_output = "";

// ACL
if ($id_task == -1) {
	$project_permission = get_project_access ($config["id_user"], $id_project);
	if (!$project_permission["manage"]) {
		audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation","Trying to access to project people manager without permissions");
		no_permission();
	}
} else {
	$task_permission = get_project_access ($config["id_user"], $id_project, $id_task, false, true);
	if (!$task_permission["manage"]) {
		audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation","Trying to access to project people manager without permissions");
		no_permission();
	}
}

// -----------
// Add user for this task
// -----------
if ($operation == "insert"){
	
	$id_role = get_parameter ("role", 0);
	$id_user = get_parameter ("user");
	
	// People add for TASK
	if ($id_task != -1) {
		$temp_id_user = get_db_value ("id_user", "trole_people_project", "id_user", $id_user);
		$temp_id_role = get_db_value('id', 'trole', 'id', $id_role);
		
		$filter['id_role']= $temp_id_role;
		$filter['id_user']= $temp_id_user;
		$filter['id_task']= $id_task;
		
		$result_sql = get_db_value_filter('id_user', 'trole_people_task', $filter);
		
		if ( $result_sql !== false) {
			echo "<h3 class='error'>".__('Not created. Role already exists.')."</h3>";
		}
		else {
			$sql = "INSERT INTO trole_people_task
				(id_task, id_user, id_role) VALUES
				($id_task, '$temp_id_user', '$temp_id_role')";
			task_tracking ($id_task, TASK_MEMBER_ADDED);
			$id_task_inserted = process_sql ($sql, 'insert_id');
			
			if ($id_task_inserted !== false) {
				$result_output = "<h3 class='suc'>".__('Successfully created')."</h3>";
				audit_db ($config["id_user"], $config["REMOTE_ADDR"],
					"User/Role added to task", "User $id_user added to task " . get_db_value ("name", "ttask", "id", $id_task));
			}
			else {
				$update_mode = 0;
				$create_mode = 1;
				$result_output = "<h3 class='error'>".__('Not created. Error inserting data.')."</h3>";
			}
		}
		
	// People add for whole PROJECT
	}
	else {
		$filter['id_role']= $id_role;
		$filter['id_user']= $id_user;
		$filter['id_project']= $id_project;
		
		$result_sql = get_db_value_filter('id_user', 'trole_people_project', $filter);
		if ($result_sql !== false){
			echo "<h3 class='error'>".__('Not created. Role already exists.')."</h3>";
		}
		else {
			$sql = "INSERT INTO trole_people_project
				(id_project, id_user, id_role) VALUES
				($id_project, '$id_user', '$id_role')";
			
			$id_task_inserted = process_sql ($sql, 'insert_id');
		
			if ($id_task_inserted !== false) {
				$result_output = "<h3 class='suc'>".__('Successfully created')."</h3>";
				audit_db ($config["id_user"], $config["REMOTE_ADDR"], "User/Role added to project", "User $id_user added to project ".get_db_value ("name", "tproject", "id", $id_project));
			} else {
				$update_mode = 0;
				$create_mode = 1;
				$result_output = "<h3 class='error'>".__('Not created. Error inserting data.')."</h3>";
			}
		}
	}
}

// DELETE Users from this project / task

if ($operation == "delete"){
	
	$id = get_parameter ("id",-1);

	// People delete for TASK
	if ($id_task != -1){
		$sql = "DELETE FROM trole_people_task WHERE id = $id";
		task_tracking ($id_task, TASK_MEMBER_DELETED);
	// People delete for whole PROJECT
	} else {
		$sql = "DELETE FROM trole_people_project WHERE id = $id";
	}
	if (mysql_query($sql)){
		$result_output = "<h3 class='suc'>".__('Successfully deleted')."</h3>";
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

if ($id_task != -1) {
	echo "<h2>".__('Task human resources management')." &raquo; ".get_db_value('name', 'ttask','id',$id_task)."</h2>";

	$sql = "SELECT COUNT(*) FROM trole_people_task where id_task = $id_task";
	$result = mysql_query($sql);
	$row=mysql_fetch_array($result);
	if ($row[0] > 0){
		echo "<h3>".__('Assigned roles')."</h3>";
		$sql = "SELECT * FROM trole_people_task where id_task = $id_task";
		$result = mysql_query($sql);
		echo "<table width=500 class='listing'>";
		echo "<th>".__('User');
		echo "<th>".__('Role');
		echo "<th>".__('Total work time (Hrs)');

		if ($task_permission["manage"]) {
			echo "<th>".__('Delete');
		}
		
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
			echo "<td valign='top' class='$tdcolor'>".get_db_value('name','trole','id',$row["id_role"]);

            echo "<td valign=top>";
            echo "<a href='index.php?sec=projects&sec2=operation/projects/task_workunit&id_project=$id_project&id_task=$id_task&id_user=".$row["id_user"]."'><b>";
            echo get_task_workunit_hours_user ($id_task, $row["id_user"]);
            echo "</a></b></td>";

			if ($task_permission["manage"]) {
				echo "<td valign='top' class='$tdcolor' align='center'>";
				echo "<a href='index.php?sec=projects&sec2=operation/projects/people_manager&id_project=$id_project&id_task=$id_task&action=delete&id=".$row["id"]."' onClick='if (!confirm(\' ".__('Are you sure?')."\')) return false;'><img src='images/cross.png' border='0'></a>";
			}
		}
		echo "</table>";
	}
} else {

	// MAIN PROJECT PEOPLE LIST
	echo "<h2>".__('Project people management')." &raquo; ".get_db_value('name', 'tproject','id',$id_project)."</h2>";
	
	$sql = "SELECT COUNT(*) FROM trole_people_project WHERE id_project = $id_project";
	$result = mysql_query($sql);
	$row=mysql_fetch_array($result);
	if ($row[0] > 0){
		echo "<h3>".__('Assigned roles')."</h3>";
		$sql = "SELECT * FROM trole_people_project WHERE id_project = $id_project";
		$result = mysql_query($sql);
		echo "<table width=500 class='listing'>";
		echo "<th>".__('User');
		echo "<th>".__('Role');
		echo "<th>".__('Total work time (Hrs)');
		if ($project_permission["manage"]) {
			echo "<th>".__('Delete');
		}
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
			echo "<td valign='top' class='$tdcolor'>".get_db_value('name','trole','id',$row["id_role"]);
            echo "<td valign=top>";
            echo "<a href='index.php?sec=projects&sec2=operation/projects/task_workunit&id_project=$id_project&id_user=".$row["id_user"]."'><b>";
            echo get_project_workunits_hours_user ($id_project, $row["id_user"]);
            echo "</b></td>";
			
			if ($project_permission["manage"]) {
				echo "<td valign='top' class='$tdcolor' align='center'>";
				echo "<a href='index.php?sec=projects&sec2=operation/projects/people_manager&id_project=$id_project&id_task=$id_task&action=delete&id=".$row["id"]."' onClick='if (!confirm(\' ".__('Are you sure?')."\')) return false;'><img src='images/cross.png' border='0'></a>";
			}
		}
		echo "</table>";
	}
}

// Role / task assigment
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Only project owner or Project ADMIN could modify
if ($id_task != -1){
	
	// Task people manager editor
	// ===============================
	echo "<h3>".__('Role/Group assignment')."</h3>";
	echo "<form method='post' action='index.php?sec=projects&sec2=operation/projects/people_manager&id_project=$id_project&id_task=$id_task&action=insert'>";
	echo "<table cellpadding=4 cellspacing=4 width=500 class='databox_color'>";

	echo "<tr><td valign='top' class='datos2'>";
	echo __('Role');
	echo "<td valign='top' class='datos2'>";
	echo combo_roles ();

	echo "<td valign='top' class='datos2'>";
	echo __('User');
	echo "<td valign='top' class='datos2'>";
	$params['input_id'] = 'text-user';
	$params['input_name'] = 'user';
	$params['return'] = false;
	$params['return_help'] = false;
	
	user_print_autocomplete_input($params);
	
	echo "</table>";
	echo "<table class='button' width=510>";
	echo "<tr><td align='right'>";
	echo "<input type=submit class='sub next' value='".__('Update')."'>";
	echo "</table>";
} else {
	// PROYECT PEOPLE MANAGER editor
	// ===============================
	echo "<h3>".__('Project role assignment')."</h3>";
	echo "<form method='post' action='index.php?sec=projects&sec2=operation/projects/people_manager&id_project=$id_project&id_task=$id_task&action=insert'>";
	echo "<table width=500 class='databox_color'>";

	echo "<tr><td valign='top' class='datos2'>";
	echo __('Role');
	echo "<td valign='top' class='datos2'>";
	echo combo_roles ();

	echo "<td valign='top' class='datos2'>";
	echo __('User  ');
	
	$params['input_id'] = 'text-user';
	$params['input_name'] = 'user';
	$params['return'] = false;
	$params['return_help'] = false;
	
	user_print_autocomplete_input($params);
	
	echo "</table>";
		
	echo "<table class='button' width=500>";
	echo "<tr><td align='right'>";
	echo "<input type=submit class='sub next' value='".__('Update')."'>";
	echo "</table>";
}

// Role informational table
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

?>
		<h3><img src='images/award_star_silver_1.png'>&nbsp;&nbsp;
		<a href="javascript:;" onmousedown="toggleDiv('arole');">
	<?PHP

echo " ".__('Available roles')."</a></h3>";
echo "<div id='arole' style='display:none'>";
echo "<table cellpadding=4 cellspacing=4 width=700 class='databox_color'>";
echo "<th>".__('Name');
echo "<th>".__('Description');
echo "<th>".__('Cost');
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

<script type="text/javascript" src="include/js/jquery.ui.autocomplete.js"></script>

<script type="text/javascript" >
$(document).ready (function () {
	$("#textarea-description").TextAreaResizer ();
	
	var idUser = "<?php echo $config['id_user'] ?>";
	var idProject = "<?php echo $id_project ?>";
	
	bindAutocomplete ("#text-user", idUser);
	bindAutocomplete ("#text-user_role", idUser, idProject);
		
});
</script>
