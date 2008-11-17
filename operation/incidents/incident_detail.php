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

require_once ('include/functions_incidents.php');

$id_grupo = (int) get_parameter ('id_grupo');
$id = (int) get_parameter ('id');

if ($id) {
	$incident = get_incident ($id);
	if ($incident !== false) {
		$id_grupo = $incident['id_grupo'];
	}
}

$check_incident = (bool) get_parameter ('check_incident');
if ($check_incident) {
	if ($incident !== false && give_acl ($config['id_user'], $id_grupo, "IR"))
		echo 1;
	else
		echo 0;
	if (defined ('AJAX'))
		return;
}

if (! give_acl ($config['id_user'], $id_grupo, "IR")) {
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

if ($action == 'get-details-list') {
	incident_details_list ($id);
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
	$user = get_parameter ('usuario_form');

	// Only admins (manage incident) or owners can modify incidents
	if (! give_acl ($config["id_user"], $grupo, "IW")) {
		audit_db ($config['id_user'], $config["REMOTE_ADDR"],"ACL Forbidden","User ".$_SESSION["id_usuario"]." try to update incident");
		echo "<h3 class='error'>".__('There was a problem updating incident')."</h3>";
		no_permission ();
		exit ();
	}
	$id_author_inc = get_incident_author ($id);
	$titulo = get_parameter ('titulo');
	$description = get_parameter ('description');
	$origen = get_parameter ("incident_origin", 1);
	$priority = get_parameter ('priority_form');
	$estado = get_parameter ('incident_status');
	$group = get_parameter ('grupo_form');
	$email_notify = (bool) get_parameter ("email_notify");
	$epilog = get_parameter ('epilog');
	$resolution = get_parameter ('incident_resolution');
	$id_task = (int) get_parameter ('task_user');
	$id_incident_type = get_parameter ('id_incident_type');
	$id_parent = (int) get_parameter ('id_parent');
	
	$old_incident = get_incident ($id);
	
	$tracked = false;
	if ($old_incident['prioridad'] != $priority) {
		incident_tracking ($id, INCIDENT_PRIORITY_CHANGED, $priority);
		$tracked = true;
	} 
	if ($old_incident['estado'] != $estado) {
		incident_tracking ($id, INCIDENT_STATUS_CHANGED, $estado);
		$tracked = true;
	}
	if ($old_incident['resolution'] != $resolution) {
		incident_tracking ($id, INCIDENT_RESOLUTION_CHANGED);
		$tracked = true;
	}
	if ($old_incident['id_usuario'] != $user) {
		incident_tracking ($id, INCIDENT_USER_CHANGED, $user);
		$tracked = true;
	}
	incident_tracking ($id, INCIDENT_UPDATED);
	
	$sql = sprintf ('UPDATE tincidencia SET actualizacion = NOW(),
			titulo = "%s", origen = %d, estado = %d,
			id_grupo = %d, id_usuario = "%s",
			notify_email = %d, prioridad = %d, descripcion = "%s",
			epilog = "%s", id_task = %d, resolution = %d,
			id_incident_type = %d, id_parent = %d
			WHERE id_incidencia = %d',
			$titulo, $origen, $estado, $grupo, $user,
			$email_notify, $priority, $description,
			$epilog, $id_task, $resolution, $id_incident_type,
			$id_parent, $id);
	$result = process_sql ($sql);
	audit_db ($id_author_inc, $config["REMOTE_ADDR"], "Incident updated", "User ".$config['id_user']." incident updated #".$id);

	/* Update inventory objects in incident */
	update_incident_inventories ($id, get_parameter ('inventories'));
	
	if ($result === false)
		$result_msg = "<h3 class='error'>".__('There was a problem updating incident')."</h3>";
	else
		$result_msg = "<h3 class='suc'>".__('Incident successfully updated')."</h3>";

	// Email notify to all people involved in this incident
	if ($email_notify == 1) {
		mail_incident ($id, $user, "", 0, 0);
	}

	if (defined ('AJAX')) {
		echo $result_msg;
		return;
	}
}

if ($action == "insert") {
	$grupo = (int) get_parameter ('grupo_form');
	$usuario = (string) get_parameter ('usuario_form');

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
	$id_task = (int) get_parameter ("task_user");
	$email_notify = (bool) get_parameter ('email_notify');
	$id_incident_type = get_parameter ('id_incident_type');
	$id_parent = (int) get_parameter ('id_parent');
	
	$sql = sprintf ('INSERT INTO tincidencia
			(inicio, actualizacion, titulo, descripcion,
			id_usuario, origen, estado, prioridad,
			id_grupo, id_creator, notify_email, id_task,
			resolution, id_incident_type, id_parent)
			VALUES (NOW(), NOW(), "%s", "%s", "%s", %d, %d, %d, %d,
			"%s", %d, %d, %d, %d, %d)',
			$titulo, $description, $usuario,
			$origen, $estado, $priority, $grupo, $id_creator,
			$email_notify, $id_task, $resolution, $id_incident_type,
			$id_parent);
	$id = process_sql ($sql, 'insert_id');
	if ($id !== false) {
		/* Update inventory objects in incident */
		update_incident_inventories ($id, get_parameter ('inventories'));
		
		$result_msg = '<h3 class="suc">'.__('Incident successfully created').' (id #'.$id.')</h3>';
		$result_msg .= '<h4><a href="index.php?sec=incidents&sec2=operation/incidents/incident&id='.$id.'">'.__('Please click here to continue working with incident #').$id."</a></h4>";

		audit_db ($config["id_user"], $config["REMOTE_ADDR"],
			"Incident created",
			"User ".$config['id_user']." created incident #".$id);
		
		incident_tracking ($id, INCIDENT_CREATED);

		// Email notify to all people involved in this incident
		if ($email_notify) {
			mail_incident ($id, $usuario, "", 0, 1);
		}
	} else {
		$result_msg  = '<h3 class="err">'.__('Incident could not be created').'</h3>';
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
	
	$incident = get_db_row ('tincidencia', 'id_incidencia', $id);
	// Get values
	$titulo = $incident["titulo"];
	$description = $incident["descripcion"];
	$inicio = $incident["inicio"];
	$actualizacion = $incident["actualizacion"];
	$estado = $incident["estado"];
	$priority = $incident["prioridad"];
	$origen = $incident["origen"];
	$usuario = $incident["id_usuario"];
	$nombre_real = dame_nombre_real($usuario);
	$id_grupo = $incident["id_grupo"];
	$id_creator = $incident["id_creator"];
	$email_notify=$incident["notify_email"];
	$resolution = $incident["resolution"];
	$epilog = $incident["epilog"];
	$id_task = $incident["id_task"];
	$id_parent = $incident["id_parent"];
	$affected_sla_id = $incident["affected_sla_id"];
	$sla_disabled = false; /* TODO */
	$id_incident_type = $incident['id_incident_type'];
	$grupo = dame_nombre_grupo($id_grupo);

	// Aditional ACL check on read incident
	if (give_acl ($config["id_user"], $id_grupo, "IR") == 0) { // Only admins
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Forbidden","User ".$config["id_user"]." try to access to an unauthorized incident ID #id_inc");
		no_permission ();
	}
	
	// Workunit ADD
	$insert_workunit = (bool) get_parameter ('insert_workunit');
	if ($insert_workunit) {
		$timestamp = get_parameter ("timestamp");
		$nota = get_parameter ("nota");
		$timeused = (float) get_parameter ('duration');
		$have_cost = (int) get_parameter ('have_cost');
		$profile = (int) get_parameter ('work_profile');
		$public = (bool) get_parameter ('public');

		$sql = sprintf ('UPDATE tincidencia SET actualizacion = "%s"
				WHERE id_incidencia = %d', $timestamp, $id);
		process_sql ($sql);

		incident_tracking ($id, INCIDENT_WORKUNIT_ADDED);

		// Add work unit if enabled
		$sql = sprintf ('INSERT INTO tworkunit (timestamp, duration, id_user, description, public)
				VALUES ("%s", %.2f, "%s", "%s", %d)',
				$timestamp, $timeused, $config['id_user'], $nota, $public);
		$id_workunit = process_sql ($sql, "insert_id");
		$sql = sprintf ('INSERT INTO tworkunit_incident (id_incident, id_workunit)
				VALUES (%d, %d)',
				$id, $id_workunit);
		$res = process_sql ($sql);
		if ($res !== false) {
			$result_msg = '<h3 class="suc">'.__('Workunit added successfully').'</h3>';
			// Email notify to all people involved in this incident
			if ($email_notify == 1) {
				mail_incident ($id, $config['id_user'], $nota, $timeused, 10, $public);
			}
		}
		
		if (defined ('AJAX')) {
			echo $result_msg;
			return;
		}
	}

	// Upload file
	$upload_file = (bool) get_parameter ('upload_file');
	if (give_acl ($config['id_user'], $id_grupo, "IW") && $upload_file) {
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
					$id, $config['id_user'], $filename, $file_description, $filesize);

			$id_attachment = process_sql ($sql, 'insert_id');
			incident_tracking ($id, INCIDENT_FILE_ADDED);
			$result_msg = '<h3 class="suc">'.__('File added').'</h3>';
			// Email notify to all people involved in this incident
			if ($email_notify == 1) {
				mail_incident ($id, $config['id_user'], 0, 0, 2);
			}
			
			// Copy file to directory and change name
			$filename = $config["homedir"]."/attachment/pand".$id_attachment."_".$filename;
			
			if (! copy ($_FILES['userfile']['tmp_name'], $filename)) {
				$result_msg = '<h3 class="error">'.__('File cannot be saved. Please contact Integria administrator about this error').'</h3>';
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
		if (give_acl ($config['id_user'], $id_grupo, "IM")) {
			$id_attachment = get_parameter ('id_attachment');
			$filename = get_db_value ('filename', 'tattachment',
				'id_attachment', $id_attachment);
			$sql = sprintf ('DELETE FROM tattachment WHERE id_attachment = %d',
				$id_attachment);
			process_sql ($sql);
			$result_msg = '<h3 class="suc">'.__('File deleted successfuly').'</h3>';
			if (!unlink ($config["homedir"].'attachment/pand'.$id_attachment.'_'.$filename))
				$result_msg = '<h3 class="error">'.__('File could not be deleted').'</h3>';
			incident_tracking ($id, INCIDENT_FILE_REMOVED);
			
		} else {
			$result_msg = '<h3 class="error">'.__('You have no permission').'</h3>';
		}
		
		if (defined ('AJAX')) {
			echo $result_msg;
			return;
		}
	}
} else {
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
	$resolution = 0;
	$id_task = 0;
	$epilog = "";
	$id_creator = $config['id_user'];
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
	$number_group = get_db_sql ("SELECT COUNT(id_grupo) FROM tusuario_perfil WHERE id_usuario = '$usuario'");
	// Take first group defined for this user
	$default_id_group = get_db_sql ("SELECT id_grupo FROM tusuario_perfil WHERE id_usuario = '$usuario' LIMIT 1");
	// if have only one group, select default user and email for this group
	$email_notify = false;
}
$has_permission = (give_acl ($config['id_user'], $id_grupo, "IM")  || ($usuario == $config['id_user']));

if ($id) {
	echo "<h1>".__('Incident')." #$id";
	
	/* Delete incident */
	if ($has_permission) {
		echo '<form name="delete_incident_form" class="delete" method="post" action="index.php?sec=incident&sec2=operation/incidents/incident">';
		print_input_hidden ('quick_delete', $id, false);
		echo '<input type="image" class="cross" src="images/cross.png" title="' . __('Delete') .'">';
		echo '</form>';
	}
	if ($affected_sla_id != 0) {
		echo '&nbsp;<img src="images/exclamation.png" border=0 valign=top title="'.__('SLA Fired').'">';
	}
	echo "</h1>";
} else {
	if (! defined ('AJAX'))
		echo "<h2>".__('Create incident')."</h2>";
}

echo '<div class="result">'.$result_msg.'</div>';

$table->width = '90%';
$table->class = 'databox_color';
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

if ($has_permission) {
	$table->data[0][0] = print_input_text ('titulo', $titulo, '', 40, 100, true, __('Title'));
} else {
	$table->data[0][0] = print_label (__('Title'), '', '', true, $titulo);
}
$table->data[0][1] = print_checkbox_extended ('sla_disabled', 0, $sla_disabled,
	$disabled, '', '', true, __('SLA disabled'));
$table->data[0][2] = print_checkbox_extended ('email_notify', 1, $email_notify,
	$disabled, '', '', true, __('Notify changes by email'));

$table->data[1][0] = combo_incident_status ($estado, $disabled, $actual_only, true);

if ($disabled) {
	$table->data[1][1] = print_label (__('Priority'), '', '', true,
		render_priority ($priority));
} else {
	$table->data[1][1] = print_select (get_priorities (),
		'priority_form', $priority, '', '',
		'', true, false, false, __('Priority'));
}

$table->data[1][1] .= print_priority_flag_image ($priority, true);

$table->data[1][2] = combo_incident_resolution ($resolution, $disabled, true);
$parent_name = $id_parent ? (__('Incident').' #'.$id_parent) : __('None');

$table->data[1][3] = print_button ($parent_name, 'search_parent', $disabled, '',
			'class="dialogbtn"', true, __('Parent incident'));
$table->data[1][3] .= print_input_hidden ('id_parent', $id_parent, true);

// Show link to go parent incident
if ($id_parent)
	$table->data[1][3] .= '<a href="index.php?sec=incidents&sec2=operation/incidents/incident&id='.$id_parent.'"><img src="images/go.png" /></a>';

$table->data[2][0] = combo_incident_origin ($origen, $disabled, true);
$table->data[2][1] = combo_incident_types ($id_incident_type, $disabled, true);
$table->data[2][2] = combo_task_user ($id_task, $config["id_user"], $disabled, false, true);

if ($has_permission) {
	$table->data[4][0] = combo_groups_visible_for_me ($config['id_user'], "grupo_form", 0, "IW", $id_grupo, true);
} else {
	$table->data[4][0] = print_label (__('Group'), '', '', true, dame_nombre_grupo ($id_grupo));
}

if ($has_permission) {
	$table->data[4][1] = print_button (dame_nombre_real ($usuario), 'usuario_name',
		false, '', 'class="dialogbtn"', true, __('Assigned user'));
	$table->data[4][1] .= print_input_hidden ('usuario_form', $usuario, true);
	$table->data[4][1] .= print_help_tip (__('User assigned here is user that will be responsible to manage incident. If you are opening an incident and want to be resolved by someone different than yourself, please assign to other user'), true);
} else {
	$table->data[4][1] = print_input_hidden ('usuario_form', $usuario, true, __('Assigned user'));
	$table->data[4][1] .= print_label (__('Assigned user'), '', '', true,
		dame_nombre_real ($usuario));
}

if ($create_incident) {
	$id_inventory = (int) get_parameter ('id_inventory');
	$inventories = array ();
	
	if ($id_inventory) {
		if (! give_acl ($config['id_user'], $id_inventory, "VR")) {
			audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation",
				"Trying to access inventory #".$id);
		} else {
			$inventories[$id_inventory] = get_db_value ('name', 'tinventory',
				'id', $id_inventory);
		}
	}
	
	$table->data[4][2] = print_select ($inventories, 'incident_inventories', NULL,
					'', '', '', true, false, false, __('Objects affected'));
	$table->data[4][2] .= print_button (__('Add'),
					'search_inventory', false, '', 'class="dialogbtn"', true);
	$table->data[4][2] .= print_button (__('Remove'),
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
		
	}
}

foreach ($inventories as $inventory_id => $inventory_name) {
	$table->data[4][2] .= print_input_hidden ("inventories[]",
						$inventory_id, true, 'selected-inventories');
}

$disabled_str = $disabled ? 'readonly="1"' : '';
$table->data[5][0] = print_textarea ('description', 9, 80, $description, $disabled_str,
		true, __('Description'));

$table->data[6][0] = print_textarea ('epilog', 5, 80, $epilog, $disabled_str,
		true, __('Resolution epilog'));

if ($estado != 6 && $estado != 7) {
	$table->rowstyle[6] = 'display: none';
} 

if ($has_permission){
	echo '<form id="incident_status_form" method="post">';

	print_table ($table);

	echo '<div style="width:'.$table->width.'" class="button">';
	if ($create_incident) {
		print_input_hidden ('action', 'insert');
		if (give_acl ($config["id_user"], 0, "IW")) {
			print_submit_button (__('Create'), 'accion', false, 'class="sub next"');
		}
	} else {
		print_input_hidden ('id', $id);
		print_input_hidden ('action', 'update');
		if ($has_permission) {
			print_submit_button (__('Update'), 'accion', false, 'class="sub upd"');
		}
	}
	echo '</div>';
	echo "</form>";
} else {
	print_table ($table);
}

/* Javascript is only shown in normal mode */
if (! defined ('AJAX')) :
?>

<script type="text/javascript" src="include/js/jquery.metadata.js"></script>
<script type="text/javascript" src="include/js/jquery.tablesorter.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
<script type="text/javascript" src="include/js/jquery.tablesorter.pager.js"></script>
<script type="text/javascript" src="include/js/integria_incident_search.js"></script>
<script  type="text/javascript">
$(document).ready (function () {
	/* First parameter indicates to add AJAX support to the form */
	configure_incident_form (false);
	$("#grupo_form").change ();
});
</script>

<?php endif; ?>
