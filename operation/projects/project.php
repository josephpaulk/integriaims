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

global $config;
global $REMOTE_ADDR;

check_login ();

include_once ("include/functions_projects.php");
include_once ("include/functions_tasks.php");

$section_permission = get_project_access ($config['id_user']);

if (!$section_permission['read']) {
	audit_db ($config['id_user'], $REMOTE_ADDR, "ACL Violation", "Trying to access project detail");
	require ("general/noaccess.php");
	exit;
}

include_once ("include/functions_graph.php");

$id_project = (int) get_parameter ('id');
$delete_project = (bool) get_parameter ('delete_project');
$view_disabled = (int) get_parameter ('view_disabled');
$disable_project = (bool) get_parameter ('disable_project');
$delete_project = (bool) get_parameter ('delete_project');
$activate_project = (bool) get_parameter ('activate_project');
$action = (string) get_parameter ('action');
$search_id_project_group = (int) get_parameter ('search_id_project_group');
$search_text = (string) get_parameter ('search_text');

if ($id_project) {
	$project_permission = get_project_access ($config['id_user'], $id_project);
}

// Disable project
// ======================
if ($disable_project) {
	
	if (!$project_permission['manage']) {
		audit_db ($config['id_user'], $REMOTE_ADDR, "ACL Forbidden", "User ".$config['id_user']." try to disable project #$id_project");
		require ("general/noaccess.php");
		exit;
	}
	
	$id_owner = get_db_value ('id_owner', 'tproject', 'id', $id_project);
	$sql = sprintf ('UPDATE tproject SET disabled = 1 WHERE id = %d', $id_project);
	process_sql ($sql);
	echo '<h3 class="suc">'.__('Project successfully disabled').'</h3>';
	audit_db ($config['id_user'], $REMOTE_ADDR, "Project disabled", "User ".$config['id_user']." disabled project #".$id_project);
	project_tracking ($id_project, PROJECT_DISABLED);
}

// Reactivate project
// ==================
if ($activate_project) {
	
	if (!$project_permission['manage']) {
		audit_db ($config['id_user'], $REMOTE_ADDR, "ACL Forbidden", "User ".$config['id_user']." try to activate project #$id_project");
		require ("general/noaccess.php");
		exit;
	}
	
	$id_owner = get_db_value ('id_owner', 'tproject', 'id', $id_project);
	$sql = sprintf ('UPDATE tproject SET disabled = 0 WHERE id = %d', $id_project);
	process_sql ($sql);
	echo '<h3 class="suc">'.__('Successfully reactivated').'</h3>';
	audit_db ($config['id_user'], $REMOTE_ADDR, "Project activated", "User ".$config['id_user']." activated project #".$id_project);
	project_tracking ($id_project, PROJECT_ACTIVATED);
}

// Delete
// -----------
if ($delete_project) {
	
	if (!$project_permission['manage']) {
		audit_db ($config['id_user'], $REMOTE_ADDR, "ACL Forbidden", "User ".$config['id_user']." try to delete project #$id_project");
		require ("general/noaccess.php");
		exit;
	}
	
	$id_owner = get_db_value ('id_owner', 'tproject', 'id', $id_project);
	delete_project ($id_project);
	echo '<h3 class="suc">'.__('Successfully deleted').'</h3>';
}

// INSERT PROJECT
if ($action == 'insert') {
	
	if (!$project_permission['write']) {
		audit_db ($config['id_user'], $REMOTE_ADDR, "ACL Forbidden", "User ".$id_user. " try to create project");
		return;
	}

	// Read input variables
	$id_owner = get_parameter ("id_owner", "");
	$name = (string) get_parameter ("name");
	$description = (string) get_parameter ('description');
	$start_date = (string) get_parameter ('start_date');
	$end_date = (string) get_parameter ('end_date');
	$id_project_group = (int) get_parameter ('id_project_group');

	$error_msg = "";
	
	if($id_owner == "") {
		$id_owner = $config['id_user'];
		$owner_exists = true;	
	}
	else {
		$owner_exists = get_user($id_owner);
	}
	if($owner_exists === false) {
		$error_msg  = '<h3 class="error">'.__('Project manager user does not exist').'</h3>';
		$id_project = false;
	}
	else {
		$sql = sprintf ('INSERT INTO tproject
			(name, description, start, end, id_owner, id_project_group)
			VALUES ("%s", "%s", "%s", "%s", "%s", %d)',
			$name, $description, $start_date, $end_date, $id_owner,
			$id_project_group);
		$id_project = process_sql ($sql, 'insert_id');
	}	
	if ($id_project === false) {
		echo '<h3 class="error">'.__('Project cannot be created, problem found.').'</h3>'.$error_msg;
	} else {
		echo '<h3 class="suc">'.__('Successfully created').' #'.$id_project.'</h3>';
		audit_db ($id_owner, $REMOTE_ADDR, "Project created", "User ".$config['id_user']." created project '$name'");
		
		project_tracking ($id_project, PROJECT_CREATED);
		
		// Add this user as profile 1 (project manager) automatically
		$sql = sprintf ('INSERT INTO trole_people_project
			(id_project, id_user, id_role)
			VALUES ("%s", "%s", 1)',
			$id_project, $id_owner, 1);
		process_sql ($sql);		
		// If current user is different than owner, add also current user
		if ($config['id_user'] != $id_owner) {
			$sql = sprintf ('INSERT INTO trole_people_project
				(id_project, id_user, id_role)
				VALUES (%d, "%s", 1)',
				$id_project, $config['id_user']);
			process_sql ($sql);
		}
	}
}

echo '<h2>'.__('Project management').'</h2>';

$table->width = '90%';
$table->class = 'search-table';
$table->style = array ();
$table->style[0] = 'font-weight: bold;';
$table->style[2] = 'font-weight: bold;';
$table->data = array ();
$table->data[0][0] = __('Search');
$table->data[0][1] = print_input_text ("search_text", $search_text, "", 25, 100, true);
$table->data[0][2] = __('Group');
$table->data[0][3] = print_select_from_sql ("SELECT * FROM tproject_group", "search_id_project_group", $search_id_project_group, '', __("Any"), '0', true, false, true, false);
$table->data[0][4] = print_submit_button (__('Search'), "search_btn", false, 'class="sub search"', true);

echo '<form method="post">';
print_table ($table);
echo '</form>';

unset ($table);

$table->width = '99%';
$table->class = 'listing';
$table->style = array ();
$table->style[0] = '';
$table->align = array ();
$table->align[7] = 'center';
$table->head = array ();
$table->head[0] = __('Name');
// PG: Abbreviation for "Project group"
$table->head[1] = __ ('PG');
$table->head[2] = __('Manager');
$table->head[3] = __('Completion');
$table->head[4] = __('Updated');
$table->head[5] = '';
$table->data = array ();

$where_clause = "";
if ($search_text != "")
	$where_clause .= sprintf (" AND (tproject.name LIKE '%%%s%%' OR tproject.description LIKE '%%%s%%')",
		$search_text, $search_text);

$sql = get_projects_query ($config['id_user'], $where_clause, $view_disabled);
$new = true;

while ($project = get_db_all_row_by_steps_sql ($new, $result, $sql)) {
	
	$new = false;
	
	$project_permission = get_project_access ($config['id_user'], $project['id']);
	if (!$project_permission['read']) {
		continue;
	}
	$data = array ();
	
	// Project name
	$data[0] = '<a href="index.php?sec=projects&sec2=operation/projects/project_detail&id_project='.$project['id'].'">'.$project['name'].'</a>';
	
	$data[1] = '';
	// Project group
	if ($project['id_project_group']) {
		$icon = get_db_value ('icon', 'tproject_group', 'id', $project['id_project_group']);
		$name = get_db_value ('name', 'tproject_group', 'id', $project['id_project_group']);
		
		$data[1] = '<a href=index.php?sec=projects&sec2=operation/projects/project&filter_id_project_group='.$project["id_project_group"].'">';
		$data[1] .= '<img src="images/project_groups_small/'.$icon.'" title="'.$name.'">';
		$data[1] .= '</a>';
	}

	$data[2] = $project["id_owner"];

	if ($project["start"] == $project["end"]) {
		$data[3] = '<img src="images/comments.png"> '.__('Unlimited');
	} else {
		$completion = format_numeric (calculate_project_progress ($project['id']));
		$data[3] = progress_bar($completion, 90, 20);
	}

	// Last update time
	$sql = sprintf ('SELECT tworkunit.timestamp
		FROM ttask, tworkunit_task, tworkunit
		WHERE ttask.id_project = %d
		AND ttask.id = tworkunit_task.id_task
		AND tworkunit_task.id_workunit = tworkunit.id
		ORDER BY tworkunit.timestamp DESC LIMIT 1',
		$project['id']);
	$timestamp = get_db_sql ($sql);
	if ($timestamp != "")
		$data[4] = "<span style='font-size: 10px'>".human_time_comparation ($timestamp)."</span>";
	else
		$data[4] = __('Never');
	
	$data[5] = '';
	// Disable or delete
	if ($project['id'] != -1 && $project_permission['manage']) {
		if ($view_disabled == 0) {
			$table->head[5] = __('Archive');
			$data[5] = '<a href="index.php?sec=projects&sec2=operation/projects/project&disable_project=1&id='.$project['id'].'" 
				onClick="if (!confirm(\''.__('Are you sure?').'\')) return false;">
				<img src="images/cross.png" /></a>';
		} elseif ($project['disabled'] && $project_permission['manage']) {
			$table->head[5] = __('Delete/Unarchive');
			$data[5] = '<a href="index.php?sec=projects&sec2=operation/projects/project&view_disabled=1&delete_project=1&id='.$project['id'].'"
				onClick="if (!confirm(\''.__('Are you sure?').'\')) return false;">
				<img src="images/cross.png" /></a> ';
			$data[5] .= '<a href="index.php?sec=projects&sec2=operation/projects/project&view_disabled=1&activate_project=1&id='.$project['id'].'">
				<img src="images/play.gif" /></a>';
		}
	}
	
	array_push ($table->data, $data);
}

if(empty($table->data)) {
	echo '<h3 class="error">'.__('No projects found').'</h3>';
}
else {
	print_table ($table);
}
?>

<script type="text/javascript" src="include/js/jquery.validation.functions.js"></script>
<script type="text/javascript">
trim_element_on_submit("#text-search_text");
</script>
