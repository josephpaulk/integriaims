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
}

?>
