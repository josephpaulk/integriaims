<?php
// Integria 2.0 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008 Artica Soluciones Tecnologicas
// Copyright (c) 2008 Esteban Sanchez, estebans@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

check_login ();

$id = (int) get_parameter ('id_project');
$project = get_db_row ('tproject', 'id', $id);

if ($project === false || ! user_belong_project ($config["id_user"], $id)) {
	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access project ".$id);
	no_permission();
}

echo '<h2>'.__('Project tracking').' &raquo; '.$project['name'].'</h2>';

$trackings = get_db_all_rows_field_filter ('tproject_track', 'id_project', $id);

if ($trackings !== false) {
	$table->width = "90%";
	$table->class = 'listing';
	$table->data = array ();
	$table->head = array ();
	$table->head[1] = __('Description');
	$table->head[2] = __('User');
	$table->head[3] = __('Date');
	
	foreach ($trackings as $tracking) {
		$data = array ();
		
		$data[0] = get_project_tracking_state ($tracking['state']);
		$data[1] = dame_nombre_real ($tracking['id_user']);
		$data[2] = $tracking['timestamp'];
		
		array_push ($table->data, $data);
	}
	print_table ($table);
} else {
	echo __('No data available');
}

?>
