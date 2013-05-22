<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008-2013 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

$search_project_name = (bool) get_parameter ('search_project_name');
$search_existing_task = (bool) get_parameter ('search_existing_task');
$search_existing_incident = (bool) get_parameter ('search_existing_incident');
$search_existing_type = (bool) get_parameter ('search_existing_type');
$search_existing_group = (bool) get_parameter ('search_existing_group');

if ($search_project_name) {
	require_once ('include/functions_db.php');
	
	$project_name = get_parameter ('project_name');
	$project_id = (int) get_parameter ('project_id');
	$old_project_name = "";
	
	// If edition mode, get the name of editing project
	if ($project_id) {
		$old_project_name = get_db_value("name", "tproject", "id", $project_id);
	}
	
	// Checks if the project is in the db
	if ( $query_result = get_db_value("name", "tproject", "name", $project_name) ) {
		if ($old_project_name == $query_result) {
			// Exists, but is in edition mode
			$result = true;
		} else {
			// Exists. Validation error
			$result = false;
		}
	} else {
		// Does not exist
		$result = true;
	}
	
	echo json_encode($result);
	return;
	
} elseif ($search_existing_task) {
	require_once ('include/functions_db.php');
	
	$project_id = (int) get_parameter ('project_id');
	$operation_type = (string) get_parameter ('type');
	
	if ($operation_type == "create") {
		
		$tasks_names = get_parameter ('task_name');
		$tasks_names = safe_output($tasks_names);
		$tasks_names = preg_split ("/\n/", $tasks_names);
		
		foreach ($tasks_names as $task_name) {
			$task_name = safe_input($task_name);
			$query_result = get_db_value_filter ("name", "ttask",
				array('name' => $task_name, 'id_project' => $project_id));
			if ($query_result) {
				// Exists. Validation error
				json_encode(false);
				return;
			}
		}
		
	} elseif ($operation_type == "view") {
		
		$task_name = get_parameter ('task_name');
		$old_task_id = get_parameter ('task_id');
		
		if (!$project_id) {
			$project_id = get_db_value("id_project", "ttask", "id", $old_task_id);
		}
		// Name of the edited task
		$old_task_name = get_db_value("name", "ttask", "id", $old_task_id);
		
		// Checks if the task is in the db
		$query_result = get_db_value_filter ("name", "ttask",
			array('name' => $task_name, 'id_project' => $project_id));
		if ($query_result) {
			if ($query_result != $old_task_name) {
				// Exists. Validation error
				echo json_encode(false);
				return;
			}
		}
		
	}
	
	// Does not exist or is the edited
	echo json_encode(true);
	return;
	
} elseif ($search_existing_incident) {
	require_once ('include/functions_db.php');
	$incident_name = get_parameter ('incident_name');
	$incident_id = get_parameter ('incident_id', 0);
	$old_incident_name = "";
	
	if ($incident_id) {
		$old_incident_name = get_db_value("titulo", "tincidencia", "id_incidencia", $incident_id);
	}
	
	// Checks if the project is in the db
	$query_result = get_db_value("titulo", "tincidencia", "titulo", $incident_name);
	if ($query_result) {
		if ($incident_name != $old_incident_name) {
			// Exists. Validation error
			echo json_encode(false);
			return;
		}
	}
	// Does not exist
	echo json_encode(true);
	return;
	
} elseif ($search_existing_type) {
	require_once ('include/functions_db.php');
	$type_name = get_parameter ('type_name');
	$type_id = get_parameter ('type_id', 0);
	$old_type_name = "";
	
	if ($type_id) {
		$old_type_name = get_db_value("name", "tincident_type", "id", $type_id);
	}
	
	// Checks if the project is in the db
	$query_result = get_db_value("name", "tincident_type", "name", $type_name);
	if ($query_result) {
		if ($type_name != $old_type_name) {
			// Exists. Validation error
			echo json_encode(false);
			return;
		}
	}
	// Does not exist
	echo json_encode(true);
	return;
	
} elseif ($search_existing_group) {
	require_once ('include/functions_db.php');
	$group_name = get_parameter ('group_name');
	$group_id = get_parameter ('group_id', 0);
	$old_group_name = "";
	
	if ($group_id) {
		$old_group_name = get_db_value("name", "tproject_group", "id", $group_id);
	}
	
	// Checks if the project is in the db
	$query_result = get_db_value("name", "tproject_group", "name", $group_name);
	if ($query_result) {
		if ($group_name != $old_group_name) {
			// Exists. Validation error
			echo json_encode(false);
			return;
		}
	}
	// Does not exist
	echo json_encode(true);
	return;
	
}

?>
