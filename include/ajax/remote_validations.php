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

$search_existing_project = (bool) get_parameter ('search_existing_project');
$search_existing_task = (bool) get_parameter ('search_existing_task');
$search_existing_incident = (bool) get_parameter ('search_existing_incident');
$search_existing_incident_type = (bool) get_parameter ('search_existing_incident_type');
$search_existing_group = (bool) get_parameter ('search_existing_group');
$search_existing_sla = (bool) get_parameter ('search_existing_sla');
$search_existing_object = (bool) get_parameter ('search_existing_object');
$search_existing_object_type = (bool) get_parameter ('search_existing_object_type');
$search_existing_object_type_field = (bool) get_parameter ('search_existing_object_type_field');
$search_existing_manufacturer = (bool) get_parameter ('search_existing_manufacturer');
$search_existing_kb_item = (bool) get_parameter ('search_existing_kb_item');
$search_existing_kb_category = (bool) get_parameter ('search_existing_kb_category');
$search_existing_product_type = (bool) get_parameter ('search_existing_product_type');
$search_existing_download = (bool) get_parameter ('search_existing_download');
$search_existing_file_category = (bool) get_parameter ('search_existing_file_category');

if ($search_existing_project) {
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
	
	// Checks if the incident is in the db
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
	
} elseif ($search_existing_incident_type) {
	require_once ('include/functions_db.php');
	$incident_type_name = get_parameter ('type_name');
	$incident_type_id = get_parameter ('type_id', 0);
	$old_incident_type_name = "";
	
	if ($incident_type_id) {
		$old_incident_type_name = get_db_value("name", "tincident_type", "id", $incident_type_id);
	}
	
	// Checks if the incident type is in the db
	$query_result = get_db_value("name", "tincident_type", "name", $incident_type_name);
	if ($query_result) {
		if ($incident_type_name != $old_incident_type_name) {
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
	
	// Checks if the group is in the db
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
	
} elseif ($search_existing_sla) {
	require_once ('include/functions_db.php');
	$sla_name = get_parameter ('sla_name');
	$sla_id = get_parameter ('sla_id', 0);
	$old_sla_name = "";
	
	if ($sla_id) {
		$old_sla_name = get_db_value("name", "tsla", "id", $sla_id);
	}
	
	// Checks if the sla is in the db
	$query_result = get_db_value("name", "tsla", "name", $sla_name);
	if ($query_result) {
		if ($sla_name != $old_sla_name) {
			// Exists. Validation error
			echo json_encode(false);
			return;
		}
	}
	// Does not exist
	echo json_encode(true);
	return;
	
} elseif ($search_existing_object) {
	require_once ('include/functions_db.php');
	$object_name = get_parameter ('object_name');
	$object_id = get_parameter ('object_id', 0);
	$old_object_name = "";
	
	if ($object_id) {
		$old_object_name = get_db_value("name", "tinventory", "id", $object_id);
	}
	
	// Checks if the object is in the db
	$query_result = get_db_value("name", "tinventory", "name", $object_name);
	if ($query_result) {
		if ($object_name != $old_object_name) {
			// Exists. Validation error
			echo json_encode(false);
			return;
		}
	}
	// Does not exist
	echo json_encode(true);
	return;
	
} elseif ($search_existing_object_type) {
	require_once ('include/functions_db.php');
	$object_type_name = get_parameter ('object_type_name');
	$object_type_id = get_parameter ('object_type_id', 0);
	$old_object_type_name = "";
	
	if ($object_type_id) {
		$old_object_type_name = get_db_value("name", "tobject_type", "id", $object_type_id);
	}
	
	// Checks if the object type is in the db
	$query_result = get_db_value("name", "tobject_type", "name", $object_type_name);
	if ($query_result) {
		if ($object_type_name != $old_object_type_name) {
			// Exists. Validation error
			echo json_encode(false);
			return;
		}
	}
	// Does not exist
	echo json_encode(true);
	return;
	
} elseif ($search_existing_object_type_field) {
	require_once ('include/functions_db.php');
	$object_type_field_name = get_parameter ('object_type_field_name');
	$object_type_id = get_parameter ('object_type_id');
	$object_type_field_id = get_parameter ('object_type_field_id', 0);
	$old_object_type_field_name = "";
	
	if ($object_type_field_id) {
		$old_object_type_field_name = get_db_value("label", "tobject_type_field", "id", $object_type_field_id);
	}
	
	// Checks if the object type field is in the db
	$query_result = get_db_value_filter ("label", "tobject_type_field",
			array('label' => $object_type_field_name, 'id_object_type' => $object_type_id));
	if ($query_result) {
		if ($object_type_field_name != $old_object_type_field_name) {
			// Exists. Validation error
			echo json_encode(false);
			return;
		}
	}
	// Does not exist
	echo json_encode(true);
	return;
	
} elseif ($search_existing_manufacturer) {
	require_once ('include/functions_db.php');
	$manufacturer_name = get_parameter ('manufacturer_name');
	$manufacturer_id = get_parameter ('manufacturer_id', 0);
	$old_manufacturer_name = "";
	
	if ($manufacturer_id) {
		$old_manufacturer_name = get_db_value("name", "tmanufacturer", "id", $manufacturer_id);
	}
	
	// Checks if the manufacturer is in the db
	$query_result = get_db_value("name", "tmanufacturer", "name", $manufacturer_name);
	if ($query_result) {
		if ($manufacturer_name != $old_manufacturer_name) {
			// Exists. Validation error
			echo json_encode(false);
			return;
		}
	}
	// Does not exist
	echo json_encode(true);
	return;
	
} elseif ($search_existing_kb_item) {
	require_once ('include/functions_db.php');
	$kb_item_name = get_parameter ('kb_item_name');
	$kb_item_id = get_parameter ('kb_item_id', 0);
	$old_kb_item_name = "";
	
	if ($kb_item_id) {
		$old_kb_item_name = get_db_value("title", "tkb_data", "id", $kb_item_id);
	}
	
	// Checks if the kb item is in the db
	$query_result = get_db_value("title", "tkb_data", "title", $kb_item_name);
	if ($query_result) {
		if ($kb_item_name != $old_kb_item_name) {
			// Exists. Validation error
			echo json_encode(false);
			return;
		}
	}
	// Does not exist
	echo json_encode(true);
	return;
	
} elseif ($search_existing_kb_category) {
	require_once ('include/functions_db.php');
	$kb_category_name = get_parameter ('kb_category_name');
	$kb_category_id = get_parameter ('kb_category_id', 0);
	$old_kb_category_name = "";
	
	if ($kb_category_id) {
		$old_kb_category_name = get_db_value("name", "tkb_category", "id", $kb_category_id);
	}
	
	// Checks if the kb category is in the db
	$query_result = get_db_value("name", "tkb_category", "name", $kb_category_name);
	if ($query_result) {
		if ($kb_category_name != $old_kb_category_name) {
			// Exists. Validation error
			echo json_encode(false);
			return;
		}
	}
	// Does not exist
	echo json_encode(true);
	return;
	
} elseif ($search_existing_product_type) {
	require_once ('include/functions_db.php');
	$product_type_name = get_parameter ('product_type_name');
	$product_type_id = get_parameter ('product_type_id', 0);
	$old_product_type_name = "";
	
	if ($product_type_id) {
		$old_product_type_name = get_db_value("name", "tkb_product", "id", $product_type_id);
	}
	
	// Checks if the product type is in the db
	$query_result = get_db_value("name", "tkb_product", "name", $product_type_name);
	if ($query_result) {
		if ($product_type_name != $old_product_type_name) {
			// Exists. Validation error
			echo json_encode(false);
			return;
		}
	}
	// Does not exist
	echo json_encode(true);
	return;
	
} elseif ($search_existing_download) {
	require_once ('include/functions_db.php');
	$download_name = get_parameter ('download_name');
	$download_id = get_parameter ('download_id', 0);
	$old_download_name = "";
	
	if ($download_id) {
		$old_download_name = get_db_value("name", "tdownload", "id", $download_id);
	}
	
	// Checks if the download is in the db
	$query_result = get_db_value("name", "tdownload", "name", $download_name);
	if ($query_result) {
		if ($download_name != $old_download_name) {
			// Exists. Validation error
			echo json_encode(false);
			return;
		}
	}
	// Does not exist
	echo json_encode(true);
	return;
	
} elseif ($search_existing_file_category) {
	require_once ('include/functions_db.php');
	$file_category_name = get_parameter ('file_category_name');
	$file_category_id = get_parameter ('file_category_id', 0);
	$old_file_category_name = "";
	
	if ($file_category_id) {
		$old_file_category_name = get_db_value("name", "tdownload_category", "id", $file_category_id);
	}
	
	// Checks if the category is in the db
	$query_result = get_db_value("name", "tdownload_category", "name", $file_category_name);
	if ($query_result) {
		if ($file_category_name != $old_file_category_name) {
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
