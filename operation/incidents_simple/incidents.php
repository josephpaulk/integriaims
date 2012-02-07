<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2012 Ártica Soluciones Tecnológicas
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

if (! give_acl ($config['id_user'], 0, "IR")) {
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access incident viewer");
	require ("general/noaccess.php");
	exit;
}

// GET ACTION PARAMETERS
$create_incident = get_parameter('create_incident');

if($create_incident) {
	if (!give_acl ($config['id_user'], 0, "IM")) {
		audit_db ($config['id_user'], $config["REMOTE_ADDR"],
			"ACL Forbidden",
			"User ".$config["id_user"]." try to create incident");
		no_permission ();
		exit;
	}
	
	// Read input variables
	$title = get_parameter('title');
	$description = get_parameter('description');

	// Get default variables
	$user_groups = get_user_groups($config['id_user']);
	$group_id = reset(array_keys($user_groups));
	$id_creator = $config["id_user"];
	$sla_disabled = 0;
	$id_task = 0; // N/A
	$origen = 1; // User report
	$estado = 1; // New
	$priority = 2; // Medium
	$resolution = 0; // None
	$id_incident_type = 0; // None
	$email_copy = '';
	$email_notify = 0;
	$id_parent = 0;
	
	$user_responsible = get_group_default_user ($group_id);
	$id_user_responsible = $user_responsible['id_usuario'];
	
	$id_inventory = get_group_default_inventory($group_id, true);
	
	if ($id_parent == 0) {
		$idParentValue = 'NULL';
	}
	else {
		$idParentValue = sprintf ('%d', $id_parent);
	}

	// DONT use MySQL NOW() or UNIXTIME_NOW() because 
	// Integria can override localtime zone by a user-specified timezone.

	$timestamp = print_mysql_timestamp();

	$sql = sprintf ('INSERT INTO tincidencia
			(inicio, actualizacion, titulo, descripcion,
			id_usuario, origen, estado, prioridad,
			id_grupo, id_creator, notify_email, id_task,
			resolution, id_incident_type, id_parent, sla_disabled, email_copy)
			VALUES ("%s", "%s", "%s", "%s", "%s", %d, %d, %d, %d,
			"%s", %d, %d, %d, %d, %s, %d, "%s")', $timestamp, $timestamp,
			$title, $description, $id_user_responsible,
			$origen, $estado, $priority, $group_id, $id_creator,
			$email_notify, $id_task, $resolution, $id_incident_type,
			$idParentValue, $sla_disabled, $email_copy);
			
	$id = process_sql ($sql, 'insert_id');

	if ($id !== false) {
		/* Update inventory objects in incident */
		update_incident_inventories ($id, array($id_inventory));
		if ($config['incident_reporter'] == 1)
			update_incident_contact_reporters ($id, get_parameter ('contacts'));
		
		$result_msg = ui_print_success_message(__('Successfully created').' (id #'.$id.')', '', true);
		$result_msg .= '<h4><a href="index.php?sec=incidents&sec2=operation/incidents_simple/incident&id='.$id.'">'.__('Please click here to continue working with incident #').$id."</a></h4>";

		audit_db ($config["id_user"], $config["REMOTE_ADDR"],
			"Incident created",
			"User ".$config['id_user']." created incident #".$id);
		
		incident_tracking ($id, INCIDENT_CREATED);

		// Email notify to all people involved in this incident
		if ($email_notify) {
			mail_incident ($id, $usuario, "", 0, 1);
		}
	} else {
		$result_msg = ui_print_error_message(__('Could not be created'), '', true);
	}
	
	echo $result_msg;

	// ATTACH A FILE IF IS PROVIDED
	
	$upfile = get_parameter('upfile');
	$file_description = get_parameter('file_description');
	
	if($upfile != '') {
		$filename = get_parameter('upfile');
		$file_description = get_parameter('file_description',__('No description available'));

		$file_temp = sys_get_temp_dir()."/$filename";
		
		$result = attach_incident_file ($id, $file_temp, $file_description);
		
		echo $result;
		
		$active_tab = 'files';
	}
}

echo '<h1>'.__('My incidents').'</h1>';

$statuses = get_indicent_status ();
$statuses[-10] = __("Not closed");


$resolutions = get_incident_resolutions ();

// FILTER

// GET FILTER PARAMETERS
$status = get_parameter('status', 0);
$search = get_parameter('search', '');

unset($table);
$table->class = 'result_table';

$table->width = '98%';
$table->data = array();
$table->header = array();

$table->style[0] = 'width:60px;text-align:right;';
$table->style[1] = 'width:150px';
$table->style[2] = 'width:60px;text-align:right;';
$table->style[3] = 'width:150px';
$table->style[4] = 'width:100px';

$table->data[0][0] = "<b>".__('Search')."</b>";
$table->data[0][1] = print_input_text('search',$search,'',20,0,true);
$table->data[0][2] = "<b>".__('Status')."</b>";
$table->data[0][3] = print_select($statuses,'status',$status,'',__('Any'),0,true);
$table->data[0][4] = print_submit_button(__('Filter'), '', false, 'class="sub search"', true);
$table->data[0][5] = '';

echo '<form method="post">';
print_table($table);
echo '</form>';

unset($table);

// INCIDENT LIST

$table->class = 'result_table listing';
$table->width = '98%';
$table->id = 'incident_search_result_table';
$table->head = array ();
$table->head[0] = __('ID');
$table->head[1] = __('Incident');
$table->head[2] = __('Status')."<br /><em>".__('Resolution')."</em>";
$table->head[3] = __('Priority');
$table->head[4] = __('Updated')."<br /><em>".__('Started')."</em>";
if ($config["show_owner_incident"] == 1)
	$table->head[5] = __('Responsible');
$table->style = array ();
$table->style[0] = '';
$table->style[1] = '';
$table->style[2] = 'text-align:center; width: 70px;';
$table->style[3] = 'text-align:center; width: 50px;';
$table->data = array();

$filter = '1 = 1';

if($status > 0) {
	$filter .= sprintf(' AND estado = %d',$status);
}
elseif($status == -10) {
	//Not closed is special status
	//Means not solved(6) and not closed(7)
	$filter .= sprintf(' AND estado != 6 AND estado != 7');
}

if($search != '') {
	$filter .= sprintf(' AND (titulo LIKE "%%%s%%" OR descripcion LIKE "%%%s%%")', $search, $search);
}

$filter .= ' ORDER BY actualizacion DESC';

$incidents = get_incidents($filter);

if (empty($incidents)) {
	$table->colspan[0][0] = 9;
	$table->data[0][0] = __('Nothing was found');
	$incidents = array ();
}

$row = 0;
foreach($incidents as $incident) {	
	$table->data[$row][0] = '#'.$incident['id_incidencia'];
	$table->data[$row][1] = '<a href="index.php?sec=incidents&sec2=operation/incidents_simple/incident&id='.
		$incident['id_incidencia'].'">'.
		$incident['titulo'].'</a>';
		
	$resolution = isset ($resolutions[$incident['resolution']]) ? $resolutions[$incident['resolution']] : __('None');

	$table->data[$row][2] = '<strong>'.$statuses[$incident['estado']].'</strong><br /><em>'.$resolution.'</em>';
	$table->data[$row][3] = print_priority_flag_image ($incident['prioridad'], true);
	$table->data[$row][4] = human_time_comparation ($incident["actualizacion"]);
	$table->data[$row][4] .= '<br /><em>';
	$table->data[$row][4] .=  human_time_comparation ($incident["inicio"]);
	$table->data[$row][4] .= '</em>';
	
	if ($config["show_owner_incident"] == 1) {
		$table->data[$row][5] = $incident['id_usuario'];
	}
	
	if ($incident["estado"] < 3 ) {
		$table->rowclass[$row] = 'red';
	}
	elseif ($incident["estado"] < 6 ) {
		$table->rowclass[$row] = 'yellow';
	}
	else {
		$table->rowclass[$row] = 'green';
	}
	
	$table->rowstyle[$row] = 'border-bottom: 1px solid rgb(204, 204, 204);';
	
	$row++;
}

print_table ($table);

print_table_pager ();

unset($table);

?>

<script type="text/javascript">
</script>
