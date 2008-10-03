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

if (check_login () != 0) {
 	audit_db("Noauth",$config["REMOTE_ADDR"], "No authenticated access","Trying to access event viewer");
	require ("general/noaccess.php");
	exit;
}

$id_grupo = (int) get_parameter ('id_grupo');
$id = (int) get_parameter ('id');

if (give_acl ($config['id_user'], $id_grupo, "IR") != 1){
 	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to incident ".$id);
	include ("general/noaccess.php");
	exit;
}

$id_grupo = 0;
$texto = "";
$create_incident = true;
$result_msg = "";

$action = get_parameter ('action');

if ($action == 'get-info') {
	$incident = get_db_row ('tincidencia', 'id_incidencia', $id);
	
	$incident['hours'] = (int) give_hours_incident ($id);
	
	echo json_encode ($incident);
	if (defined ('AJAX'))
		return;
}

if ($action == 'get-users-list') {
	incident_users_list ($id);
	if (defined ('AJAX'))
		return;
}

if ($action == 'update') {
 	$grupo = get_parameter ('grupo_form');
	$usuario = get_parameter ('usuario_form');

	// Only admins (manage incident) or owners can modify incidents
	if (! give_acl ($config["id_user"], $grupo, "IM")) {
		audit_db ($config['id_user'], $config["REMOTE_ADDR"],"ACL Forbidden","User ".$_SESSION["id_usuario"]." try to update incident");
		echo "<h3 class='error'>".__('upd_incid_no')."</h3>";
		no_permission ();
		exit ();
	}
	$id_author_inc = give_incident_author ($id);
	$titulo = get_parameter ('titulo');
	$description = get_parameter ('description');
	$origen = get_parameter ("incident_origin", 1);
	$priority = get_parameter ('priority_form');
	$estado = get_parameter ('incident_status');
	$group = get_parameter ('grupo_form');
	$email_notify = (bool) get_parameter ("email_notify");
	$epilog = get_parameter ('epilog');
	$resolution = get_parameter ('incident_resolution');
	$id_task = get_parameter ('task_user');
	$id_incident_type = get_parameter ('id_incident_type');

	incident_tracking ($id, $config["id_user"], 1);
	$old_prio = give_inc_priority ($id);
	// 0 - Abierta / Sin notas (Open without notes)
	// 2 - Descartada (Not valid)
	// 3 - Caducada (out of date)
	// 13 - Cerrada (closed)
	if ($old_prio != $priority)
		incident_tracking ($id, $config['id_user'], 8);
	if ($estado == 2)
		incident_tracking ($id, $config['id_user'], 4);
	if ($estado == 3)
		incident_tracking ($id, $config['id_user'], 5);
	if ($estado == 13)
		incident_tracking ($id, $config['id_user'], 10);

	$sql = sprintf ('UPDATE tincidencia SET actualizacion = NOW(),
			titulo = "%s", origen = %d, estado = %d,
			id_grupo = %d, id_usuario = "%s",
			notify_email = %d, prioridad = %d, descripcion = "%s",
			epilog = "%s", id_task = %d, resolution = %d,
			id_incident_type = %d
			WHERE id_incidencia = %d',
			$titulo, $origen, $estado, $grupo, $usuario,
			$email_notify, $priority, $description,
			$epilog, $id_task, $resolution, $id_incident_type,
			$id);
	process_sql ($sql);
	audit_db ($id_author_inc, $config["REMOTE_ADDR"], "Incident updated", "User ".$config['id_user']." incident updated #".$id);

	/* Update inventory objects in incident */
	$sql = sprintf ('DELETE FROM tincident_inventory WHERE id_incident = %d', $id);
	process_sql ($sql);
	$inventories = get_parameter ('inventories');
	foreach ($inventories as $id_inventory) {
		$sql = sprintf ('INSERT INTO tincident_inventory
				VALUES (%d, %d)',
				$id, $id_inventory);
		$result = process_sql ($sql);
	}

	if ($result === false)
		$result_msg = "<h3 class='suc'>".__('upd_incid_no')."</h3>";
	else
		$result_msg = "<h3 class='suc'>".__('upd_incid_ok')."</h3>";

	// Email notify to all people involved in this incident
	if ($email_notify == 1) {
		mail_incident ($id, $usuario, "", 0, 0);
	}

	if (defined ('AJAX')) {
		echo $result_msg;
		return;
	}
}

if ($action == "insert") {
	$grupo = get_parameter ('grupo_form');
	$usuario = get_parameter ('usuario_form');

	if (! give_acl ($config['id_user'], $grupo, "IW") && $usuario != $config['id_user']) {
		audit_db ($config['id_user'], $config["REMOTE_ADDR"],
			"ACL Forbidden",
			"User ".$config["id_user"]." try to create incident");
		no_permission ();
		exit;
	}

	// Read input variables
	$titulo = get_parameter ('titulo');
	$description =  get_parameter ('description');
	$origen = get_parameter ('incident_origin', 1);
	$priority = get_parameter ('priority_form');
	$id_creator = $config['id_user'];
	$estado = get_parameter ("incident_status");
	$resolution = get_parameter ("incident_resolution");
	$id_task = get_parameter ("task_user");
	$email_notify = (bool) get_parameter ('email_notify');
	$id_incident_type = get_parameter ('id_incident_type');
	
	$sql = sprintf ('INSERT INTO tincidencia
			(inicio, actualizacion, titulo, descripcion,
			id_usuario, origen, estado, prioridad,
			id_grupo, id_creator, notify_email, id_task,
			resolution, id_incident_type)
			VALUES (NOW(), NOW(), "%s", "%s", "%s", %d, %d, %d, %d,
			"%s", %d, %d, %d, %d)',
			$titulo, $description, $usuario,
			$origen, $estado, $priority, $grupo, $id_creator,
			$email_notify, $id_task, $resolution, $id_incident_type);
	$id = process_sql ($sql, 'insert_id');
	if ($id !== false) {
		$inventories = (array) get_parameter ('inventories');

		foreach ($inventories as $id_inventory) {
			$sql = sprintf ('INSERT INTO tincident_inventory
					VALUES (%d, %d)',
					$id, $id_inventory);
			process_sql ($sql);
		}
		$result_msg = "<h3 class='suc'>".__('create_incid_ok')." (id #$id)</h3>";
		$result_msg .= "<h4><a href='index.php?sec=incidents&sec2=operation/incidents/incident&id=$id'>".__("Please click here to continue working with incident #").$id."</a></h4>";

		audit_db ($config["id_user"], $config["REMOTE_ADDR"],
			"Incident created",
			"User ".$config['id_user']." created incident #".$id);
		incident_tracking ($id, $config["id_user"], 0);

		// Email notify to all people involved in this incident
		if ($email_notify) {
			mail_incident ($id, $usuario, "", 0, 1);
		}
	} else {
		$result_msg  = '<h3 class="err">'.__('create_incid_no').'</h3>';
	}
	if (defined ('AJAX')) {
		echo $result_msg;
		return;
	}
	$id = 0; /* Do this to create another one */
}

// Edit / Visualization MODE - Get data from database
if ($id) {
	$create_incident = false;
	$iduser_temp=$_SESSION['id_usuario'];
	// Obtain group of this incident
	$sql = sprintf ('SELECT * FROM tincidencia
			WHERE id_incidencia = %d', $id);
	$result = mysql_query ($sql);
	$row = mysql_fetch_array ($result);
	// Get values
	$titulo = $row["titulo"];
	$description = $row["descripcion"];
	$inicio = $row["inicio"];
	$actualizacion = $row["actualizacion"];
	$estado = $row["estado"];
	$priority = $row["prioridad"];
	$origen = $row["origen"];
	$usuario = $row["id_usuario"];
	$nombre_real = dame_nombre_real($usuario);
	$id_grupo = $row["id_grupo"];
	$id_creator = $row["id_creator"];
	$email_notify=$row["notify_email"];
	$resolution = $row["resolution"];
	$epilog = $row["epilog"];
	$id_task = $row["id_task"];
	$id_parent = $row["id_parent"];
	$affected_sla_id = $row["affected_sla_id"];
	$sla_disabled = false; /* TODO */
	$id_incident_type = $row['id_incident_type'];
	$grupo = dame_nombre_grupo($id_grupo);

	// Aditional ACL check on read incident
	if (give_acl ($config["id_user"], $id_grupo, "IR") == 0) { // Only admins
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Forbidden","User ".$config["id_user"]." try to access to an unauthorized incident ID #id_inc");
		no_permission ();
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Workunit ADD
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	$insert_workunit = (bool) get_parameter ('insert_workunit');
	if ($insert_workunit) {
		$timestamp = get_parameter ("timestamp");
		$nota = get_parameter ("nota");
		$workunit = get_parameter ("workunit",0);
		$timeused = get_parameter ("duration",0);
		$timeused = number_format ($timeused, 2);
		$have_cost = get_parameter ("have_cost",0);
		$profile = get_parameter ("work_profile",0);
		$public = get_parameter ("public", 1);

		$sql = sprintf ('UPDATE tincidencia SET actualizacion = "%s"
				WHERE id_incidencia = %d', $timestamp, $id);
		process_sql ($sql);

		incident_tracking ($id, $config['id_user'], 2);

		// Add work unit if enabled
		$sql = sprintf ('INSERT INTO tworkunit (timestamp, duration, id_user, description, public)
				VALUES ("%s", "%s", "%s", "%s", "$s")',
				$timestamp, $timeused, $config['id_user'], $nota, $public);
		$id_workunit = process_sql ($sql, "insert_id");
		$sql = sprintf ('INSERT INTO tworkunit_incident (id_incident, id_workunit)
				VALUES (%d, %d)',
				$id, $id_workunit);
		$res = process_sql ($sql);
		if ($res !== false) {
			$result_msg = "<h3 class='suc'>".__('create_work_ok')."</h3>";
			// Email notify to all people involved in this incident
			if ($email_notify == 1) {
				mail_incident ($id, $config['id_user'], $nota, $timeused, 10);
			}
		}
		
		if (defined ('AJAX')) {
			echo $result_msg;
			return;
		}
	}

	// Upload file
	$upload_file = (bool) get_parameter ('upload_file');
	if ((give_acl ($iduser_temp, $id_grupo, "IW") == 1) && $upload_file) {
		$result_msg = '<h3 class="err">'.__('No file was attached').'</h3>';
		/* if file */
		if ($_FILES['userfile']['name'] != "" && $_FILES['userfile']['error'] == 0) {
			$file_description = get_parameter ("file_description",
					__('No description available'));
			
			// Insert into database
			$filename= $_FILES['userfile']['name'];
			$filesize = $_FILES['userfile']['size'];

			$sql = sprintf ('INSERT INTO tattachment (id_incidencia, id_usuario,
					filename, description, size)
					VALUES (%d, "%s", "%s", "%s", %d)',
					$id, $iduser_temp, $filename, $file_description, $filesize);

			$id_attachment = process_sql ($sql, 'insert_id');
			incident_tracking ($id, $config['id_user'], 3);
			$result_msg = '<h3 class="suc">'.__('File added').'</h3>';
			// Email notify to all people involved in this incident
			if ($email_notify == 1) {
				mail_incident ($id, $iduser_temp, 0, 0, 2);
			}
			
			// Copy file to directory and change name
			$filename = $config["homedir"]."attachment/pand".$id_attachment."_".$filename;
			
			if (! copy ($_FILES['userfile']['tmp_name'], $filename)) {
				$result_msg = '<h3 class="error">'.__('attach_error').'</h3>';
				$sql = sprintf ('DELETE FROM tattachment
						WHERE id_attachment = %d', $id_attachment);
				process_sql ($sql);
			} else {
				// Delete temporal file
				unlink ($_FILES['userfile']['tmp_name']);
			}
		}  else {
			switch ($_FILES['userfile']['error']) {
			case 1:
				$result_msg = '<h3 class="error">'.__('File is too big').'</h3>';
				break;
			case 3:
				$result_msg = '<h3 class="error">'.__('File was partially uploaded. Please try again').'</h3>';
				break;
			case 4:
				$result_msg = '<h3 class="error">'.__('No file was uploaded').'</h3>';
				break;
			default:
				$result_msg = '<h3 class="error">'.__('Generic upload error').'(Code: '.$_FILES['userfile']['error'].')</h3>';
			}
		}
		
		if (defined ('AJAX')) {
			echo $result_msg;
			return;
		}
	}
	
	// Delete file
	$delete_file = (bool) get_parameter ('delete_file');
	if ($delete_file) {
		if (give_acl ($config['id_user'], $id_group, "IM")) {
			$id_file = get_parameter ('id_file');
			$filename = get_db_value ('filename', 'tattachment',
				'id_attachment', $id_file);
			$sql = sprintf ('DELETE FROM tattachment WHERE id_attachment = %d',
				$id_file);
			process_sql ($sql);
			unlink ($config["homedir"].'/attachment/pand'.$id_file.'_'.$filename);
			incident_tracking ($id_incident, $id_usuario, 7);
		}
	
		if (defined ('AJAX'))
			return;
	}
	
} else {
	$iduser_temp = $config['id_user'];
	$titulo = "";
	$titulo = "";
	$description = "";
	$origen = 0;
	$priority = 2;
	$id_grupo =0;
	$grupo = dame_nombre_grupo (1);
	$id_parent = 0;
	$usuario= $config["id_user"];
	$estado = 1;
	$resolution = 9;
	$id_task = 0;
	$epilog = "";
	$id_creator = $iduser_temp;
	$email_notify = 0;
	$sla_disabled = false;
	$id_incident_type = 0;
	$affected_sla_id = 0;
}
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Show the form
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

$default_responsable = "";
if (! $id) {
	// How many groups has this user ?
	$number_group = give_db_sqlfree_field ("SELECT COUNT(id_grupo) FROM tusuario_perfil WHERE id_usuario = '$usuario'");
	// Take first group defined for this user
	$default_id_group = give_db_sqlfree_field ("SELECT id_grupo FROM tusuario_perfil WHERE id_usuario = '$usuario' LIMIT 1");
	// if have only one group, select default user and email for this group
	if ($number_group == 1){
		$default_responsable = give_db_sqlfree_field ("SELECT id_user FROM tgroup_manager WHERE id_group = $default_id_group");
		$email_notify = give_db_sqlfree_field ("SELECT forced_email FROM tgroup_manager WHERE id_group = $default_id_group");
	}
}
$has_permission = (give_acl ($iduser_temp, $id_grupo, "IM")  || ($usuario == $iduser_temp));

if ($id) {
	echo "<h1>".__('incident')." #$id";
	if ($affected_sla_id != 0){
		echo '&nbsp;&nbsp;&nbsp;<img src="images/exclamation.png" border=0 valign=top title="'.__('SLA Fired').'">';
	}
	echo "</h1>";

} else {
	if (! defined ('AJAX'))
		echo "<h2>".__('create_incident')."</h2>";
}

echo '<div class="result">'.$result_msg.'</div>';

$table->width = "700px";
$table->class = "databox_color";
$table->id = "incident-editor";
$table->size = array ();
$table->size[0] = '25%';
$table->size[1] = '25%';
$table->size[2] = '25%';
$table->size[3] = '25%';
$table->style = array ();
$table->data = array ();
$table->cellspacing = 2;
$table->cellpadding = 2;
$table->colspan = array ();
$table->colspan[0][0] = 2;
$table->colspan[4][2] = 2; 
$table->colspan[5][0] = 4;
$table->colspan[6][0] = 4;
$table->colspan[2][2] = 2;
$disabled = !$has_permission;
$actual_only = !$has_permission;

$table->data[0][0] = print_input_text ('titulo', $titulo, '', 40, 100, true, __('Title'));

$table->data[0][1] = print_checkbox_extended ('sla_disabled', 0, $sla_disabled,
						$disabled, '', '', true, __('SLA disabled'));
$table->data[0][2] = print_checkbox_extended ('email_notify', 1, $email_notify,
						$disabled, '', '', true, __('email_notify'));
//$table->data[1][2] .= print_help_tip (__('email_notify_help'), true);

$table->data[1][0] = combo_incident_status ($estado, $disabled, $actual_only, true);

if ($disabled) {
	$table->data[1][1] = $priority;
} else {
	$table->data[1][1] = print_select (get_indicent_priorities (),
					'priority_form', $priority, '', '',
					'', true, false, false, __('Priority'));
}

$table->data[1][1] .= print_priority_flag_image ($priority, true);


$table->data[1][2] = combo_incident_resolution ($resolution, $disabled, true);
$parent_name = $id_parent ? get_inventory_name ($id_parent) : __('Search parent');

$table->data[1][3] = print_button ($parent_name, 'search_parent', $disabled, '',
			'class="dialogbtn"', true, __('Parent incident'));
$table->data[1][3] .= print_input_hidden ('id_parent', $id_parent, true);

// Show link to go parent incident
if ($id_parent > 0)
	$table->data[1][3] .= "<a href='index.php?sec=incidents&sec2=operation/incidents/incident&id=$id_parent'><img src='images/go.png' border=0></a>";

$table->data[2][0] = combo_incident_origin ($origen, $disabled, true);
$table->data[2][1] = print_select (get_incident_types (), 'id_incident_type',
			$id_incident_type, '', __('None'), 0, true, false, true, __('Type'));
$table->data[2][2] = combo_task_user ($id_task, $config["id_user"], 0, $disabled, true);


if ($has_permission) {
	$table->data[4][0] = combo_groups_visible_for_me ($iduser_temp, "grupo_form", 0, "IW", $id_grupo, true);
} else {
	$table->data[4][0] = print_label (__('Group'), '', '', true);
	$table->data[4][0] = dame_nombre_grupo ($id_grupo);
}

if ($has_permission) {
	$disabled = false;
	if ($default_responsable != "") {
		$disabled = true;
	}
	$table->data[4][1] = print_button (dame_nombre_real ($usuario), 'usuario_name',
					$disabled, '', 'class="dialogbtn"', true, __('assigned_user'));
	$table->data[4][1] .= print_input_hidden ('usuario_form', $usuario, true);
	$table->data[4][1] .= print_help_tip (__('incident_user_help'), true);
} else {
	$table->data[4][1] = print_input_hidden ('usuario_form', $usuario, true, __('assigned_user'));
	$table->data[4][1] .= $usuario;
}

if ($create_incident) {
	$table->data[4][2] = print_select (array (), 'incident_inventories', NULL,
					'', '', '', true, false, false, __('Objects affected'));
	$table->data[4][2] .= print_button (__("Add"),
					'search_inventory', false, '', 'class="dialogbtn"', true);
	$table->data[4][2] .= print_button (__("Remove"),
					'delete_inventory', false, '', 'class="dialogbtn"', true);
} else {
	$inventories = get_inventories_in_incident ($id);
	$table->data[4][2] = print_select ($inventories, 'incident_inventories',
						NULL, '', '', '',
						true, false, false, __('Objects affected'));

	if ($has_permission) {
		$table->data[4][2] .= print_button (__('Add'),
					'search_inventory', false, '', 'class="dialogbtn"', true);
		$table->data[4][2] .= print_button (__('Remove'),
					'delete_inventory', false, '', 'class="dialogbtn"', true);
		foreach ($inventories as $inventory_id => $inventory_name) {
			$table->data[4][2] .= print_input_hidden ("inventories[]",
								$inventory_id, true, 'selected-inventories');
		}
	}
}

$disabled_str = $disabled ? 'readonly' : '';
$table->data[5][0] = print_textarea ('description', 14, 80, $description, $disabled_str,
		true, __('Description'));

$table->data[6][0] = print_textarea ('epilog', 5, 80, $epilog, $disabled_str,
		true, __('resolution_epilog'));

if ($estado != 6 && $estado != 7) {
	$table->rowstyle[6] = 'display: none';
} 

echo "<form id='incident_status_form' method='POST' action='index.php?sec=incidents&sec2=operation/incidents/incident_detail'>";

print_table ($table);

echo '<div style="width:'.$table->width.'" class="button">';
if ($create_incident) {
	print_input_hidden ('action', 'insert');
	if (give_acl ($config["id_user"], 0, "IW")) {
		print_submit_button (__('create'), 'accion', false, 'class="sub create"');
	}
} else {
	print_input_hidden ('id', $id);
	print_input_hidden ('action', 'update');
	if ($has_permission) {
		print_submit_button (__('update'), 'accion', false, 'class="sub next"');
	}
}
echo '</div>';
echo "</form>";

/* Javascript is only shown in normal mode */
if (! defined ('AJAX')) :
?>

<script type="text/javascript" src="include/js/jquery.metadata.js"></script>
<script type="text/javascript" src="include/js/jquery.tablesorter.js"></script>
<script type="text/javascript" src="include/js/jquery.tablesorter.pager.js"></script>
<script type="text/javascript" src="include/js/integria_incident_search.js"></script>
<script  type="text/javascript">
$(document).ready (function () {
	/* First parameter indicates to add AJAX support to the form */
	configure_incident_form (false);
});
</script>

<?php endif; ?>
