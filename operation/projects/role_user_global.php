<?php

// Integria IMS - http://integria.sourceforge.net
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
	exit;
}
	
//~ if (give_acl($config["id_user"], 0, "PM") != 1){
	//~ // Doesn't have access to this page
	//~ audit_db ($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access to project detail page");
	//~ include ("general/noaccess.php");
	//~ exit;
//~ }

$id_user = get_parameter ("id_user", $config["id_user"]);
$id_role = get_parameter ("roles", 0);
$tasks = (array) $_POST["tasks"];

$delete = get_parameter ("delete", 0);
if ($delete){
	$id_task = $delete;
	$sql = "DELETE FROM trole_people_task WHERE id_task = $id_task AND id_user = '$id_user'";
	$resq1=mysql_query($sql);
	echo "<h3 class='suc'>".__ ("Assigment removed succesfully")."</h3>";
}

$add = get_parameter ("add", 0);
if ($add && $id_role) {
	
	foreach ($tasks as $id_task) {
		
		$id_project = get_db_value ('id_project', 'ttask', 'id', $id_task);
		if (!$id_project) {
			$task = get_db_value ('name', 'ttask', 'id', $id_task);
			echo "<h3 class='error'>".__('Error. Task '.$task.' is not assigned to a project.')."</h3>";
			continue; // Does not insert the project and the task
		}
		
		// Project
		$filter = array();
		$filter['id_user']= $id_user;
		$filter['id_project']= $id_project;
		
		$result_sql = get_db_value_filter ('MIN(id_role)', 'trole_people_project', $filter);
		if ($result_sql == false){
			$sql = "INSERT INTO trole_people_project
					(id_project, id_user, id_role) VALUES
					($id_project, '$id_user', '$id_role')";
			
			$result_sql = process_sql ($sql, 'insert_id');
		
			if ($result_sql !== false) {
				$project = get_db_value ('name', 'tproject', 'id', $id_project);
				audit_db ($config["id_user"], $config["REMOTE_ADDR"], "User/Role added to project", "User $id_user added to project $project");
			} else {
				$project = get_db_value ('name', 'tproject', 'id', $id_project);
				$result_output = "<h3 class='error'>".__('Error assigning access to project '.$project.'.')."</h3>";
				continue; // Does not insert the task
			}
		}
		
		// Task
		$filter = array();
		$filter['id_user']= $id_user;
		$filter['id_task']= $id_task;
		
		$result_sql = get_db_value_filter ('MIN(id_role)', 'trole_people_task', $filter);
		if ($result_sql == false){
			$sql = "INSERT INTO trole_people_task
					(id_task, id_user, id_role) VALUES
					($id_task, '$id_user', '$id_role')";
			
			$result_sql = process_sql ($sql, 'insert_id');
			if ($result_sql !== false) {
				$project = get_db_value ('name', 'tproject', 'id', $id_project);
				audit_db ($config["id_user"], $config["REMOTE_ADDR"], "User/Role added to project", "User $id_user added to project $project");
			} else {
				$task = get_db_value ("name", "ttask", "id", $id_task);
				$result_output = "<h3 class='error'>".__('Error assigning access to task '.$task.'.')."</h3>";
			}
		} else {
			
			$sql = "UPDATE trole_people_task
					SET id_role=$id_role
					WHERE id_user='$id_user'
						AND id_task=$id_task";
			
			$result_sql = process_sql ($sql);
			if ($result_sql !== false) {
				$role = get_db_value ("name", "trole", "id", $id_role);
				$task = get_db_value ("name", "ttask", "id", $id_task);
				audit_db ($config["id_user"], $config["REMOTE_ADDR"], "Role of task updated",
					"User $id_user has now the role $role in the task $task");
			} else {
				$task = get_db_value ("name", "ttask", "id", $id_task);
				$result_output = "<h3 class='error'>".__('Error updating the role of the task '.$task.'.')."</h3>";
			}
		}
		
	}
	
}

// Title
echo "<h2>".__("Global assignment");
if ($id_user != "")
	echo " &raquo; ".__("For user"). " ".$id_user;
echo "</h2><br>";

// Controls
echo "<form name='xx' method=post action='index.php?sec=projects&sec2=operation/projects/role_user_global'>";

// Select user
$table->id = "cost_form";
$table->width = "250px";
$table->class = "blank";
$table->data = array ();

$table->data[0][0] = print_input_text_extended ('id_user', $id_user, 'text-id_user', '', 15, 30, false, '',
		array('style' => 'background: url(' . $src_code . ') no-repeat right;'), true, '')
		. print_help_tip (__("Type at least two characters to search"), true);
$table->data[0][1] = print_submit_button (__('Go'), 'sub_btn', false, 'class="upd sub"', true);

print_table ($table);

echo "</form>";

// Form to give project/task access
echo "<form name='form-access' method=post action='index.php?sec=projects&sec2=operation/projects/role_user_global&add=1&id_user=$id_user'>";
$table->id = "cost_form";
$table->width = "90%";
$table->class = "databox";
$table->data = array ();

$table->data[0][0] = combo_task_user_participant ($config['id_user'], false, '', true, __('Tasks'), 'tasks[]', '', true);
$table->data[0][1] = combo_roles (false, "roles", __('Role'), true);
$table->data[0][2] = print_submit_button (__('Add'), 'sub_btn', false, 'class="upd sub"', true);

print_table ($table);
echo "</form>";

$sql = "SELECT ttask.id as tid, ttask.name as tname, tproject.name as pname, trole_people_task.id_role as id_role, tproject.id as pid
		FROM trole_people_task, ttask, tproject
		WHERE trole_people_task.id_user = '$id_user'
			AND trole_people_task.id_task = ttask.id
			AND ttask.id_project = tproject.id
			AND tproject.disabled = 0
		ORDER BY tproject.name, ttask.name";


echo "<table class='listing' width='90%'>";
echo "<th>".__("Project");
echo "<th>".__("Task");
echo "<th>".__("Role");
echo "<th>".__("WU");
echo "<th>".__("WU/Tsk");
echo "<th>".__("Delete");
$new = true;
$color=1;
while ($row = get_db_all_row_by_steps_sql($new, $result, $sql)) {
	$new = false;
	echo "<tr>";
	echo "<td>";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/project_detail&id_project=".$row['pid']."'>".$row["pname"]."</a>";
	echo "<td><b><a href='index.php?sec=projects&sec2=operation/projects/task_detail&id_project=".$row['pid']."&id_task=".$row['tid']."&operation=view'>".$row['tname']."</a></b>";
	echo "<td>".get_db_sql ("SELECT name FROM trole WHERE id = ".$row["id_role"]);
	echo "<td>".get_task_workunit_hours_user ($row["tid"], $id_user);
	echo "<td>".get_task_workunit_hours ($row["tid"]);
	echo "<td align='center'><a href='index.php?sec=projects&sec2=operation/projects/role_user_global&id_user=".$id_user."&delete=".$row['tid']."' onClick='if (!confirm('".__('Are you sure?')."')) return false;'><img border=0 src='images/cross.png'></a>";
}
echo "</table>";

?>

<script type="text/javascript" src="include/js/jquery.ui.autocomplete.js"></script>
<script type="text/javascript" src="include/js/jquery.multiselect.js"></script>

<script type="text/javascript">

$(document).ready (function () {
	$("#textarea-description").TextAreaResizer ();
	
	var idUser = "<?php echo $config['id_user'] ?>";
	
	bindAutocomplete ('#text-id_user', idUser);
	
	$("#tasks\\[\\]").multiselect({
		noneSelectedText: "<?php echo __('Select options') ?>",
		selectedText: "# <?php echo __('selected') ?>",
		checkAllText: "<?php echo __('Check all') ?>",
		uncheckAllText: "<?php echo __('Uncheck all') ?>"
	});
	
	$("#roles").multiselect({
		multiple: false,
		header: false,
		noneSelectedText: "<?php echo __('Select an option') ?>",
		selectedList: 1
	});
	
	
});
</script>
