<?php
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008-2010 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.


global $config;

check_login ();

if (defined ('AJAX')) {

	global $config;

	$search_users = (bool) get_parameter ('search_users');
	
	if ($search_users) {
		require_once ('include/functions_db.php');
		
		$id_user = $config['id_user'];
		$string = (string) get_parameter ('q'); /* q is what autocomplete plugin gives */
		
		$filter = array ();
		
		$filter[] = '(nombre COLLATE utf8_general_ci LIKE "%'.$string.'%" OR direccion LIKE "%'.$string.'%" OR comentarios LIKE "%'.$string.'%")';

		$filter[] = 'id_usuario != '.$id_user;
		
		$users = get_user_visible_users ($config['id_user'],"IR", false);
		if ($users === false)
			return;
		
		foreach ($users as $user) {
			echo $user['id_usuario'] . "|" . $user['nombre_real']  . "\n";
		}
		
		return;
 	}
	return;
}

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
	if ($incident !== false && give_acl ($config['id_user'], $id_grupo, "IR")){
		if ((get_external_user($config["id_user"])) AND ($incident["id_creator"] != $config["id_user"]))
			echo 0;
		else
			echo 1;
	}
	else
		echo 0;
	if (defined ('AJAX'))
		return;
}

if (! give_acl ($config['id_user'], $id_grupo, "IR")) 
{
 	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to incident ".$id);
	include ("general/noaccess.php");
	exit;
}

if (isset($incident))
	if ((get_external_user($config["id_user"])) AND ($incident["id_creator"] != $config["id_user"])) {
	 	// Doesn't have access to this page
		audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to incident  (External user) ".$id);
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
	// Number of loop in the massive operations. No received in not massive ones
	$massive_number_loop = get_parameter ('massive_number_loop', -1);

	$old_incident = get_incident ($id);

	$user = get_parameter ('usuario_form', $old_incident['id_usuario']);
 	$grupo = get_parameter ('grupo_form', $old_incident['id_grupo']);

	// Only admins (manage incident) or owners can modify incidents
	if ((! give_acl ($config["id_user"], $grupo, "IW")) AND (! give_acl ($config["id_user"], $grupo, "IM"))) {
		audit_db ($config['id_user'], $config["REMOTE_ADDR"],"ACL Forbidden","User ".$_SESSION["id_usuario"]." try to update incident");
		echo "<h3 class='error'>".__('There was a problem updating incident')."</h3>";
		no_permission ();
		exit ();
	}

	$id_author_inc = get_incident_author ($id);
	$titulo = get_parameter ('titulo', $old_incident['titulo']);
	$sla_disabled = (bool) get_parameter ('sla_disabled', $old_incident['sla_disabled']);
	$description = get_parameter ('description', $old_incident['descripcion']);
	$origen = get_parameter ('incident_origin', $old_incident['origen']);
	$priority = get_parameter ('priority_form', $old_incident['prioridad']);
	$estado = get_parameter ('incident_status', $old_incident['estado']);
	$email_notify = (bool) get_parameter ('email_notify', $old_incident['notify_email']);
	$epilog = get_parameter ('epilog', $old_incident['epilog']);
	$resolution = get_parameter ('incident_resolution', $old_incident['resolution']);
	$id_task = (int) get_parameter ('task_user', $old_incident['id_task']);
	$id_incident_type = get_parameter ('id_incident_type', $old_incident['id_incident_type']);
	$id_parent = (int) get_parameter ('id_parent', $old_incident['id_parent']);
	$id_creator = get_parameter ('id_creator', $old_incident['id_creator']);
	$email_copy = get_parameter ('email_copy', $old_incident['email_copy']);


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

	if ($sla_disabled == 1)
		$sla_man = ", sla_disabled = 1 ";
	else 
		$sla_man = "";
		
	if ($id_parent == 0) {
		$idParentValue = 'NULL';
	}
	else {
		$idParentValue = sprintf ('%d', $id_parent);
	}
	$timestamp = print_mysql_timestamp();


	$sql = sprintf ('UPDATE tincidencia SET email_copy = "%s", actualizacion = "%s",
			  id_creator = "%s",
			titulo = "%s", origen = %d, estado = %d,
			id_grupo = %d, id_usuario = "%s",
			notify_email = %d, prioridad = %d, descripcion = "%s",
			epilog = "%s", id_task = %d, resolution = %d,
			id_incident_type = %d, id_parent = %s, affected_sla_id = 0 %s 
			WHERE id_incidencia = %d', $email_copy, $timestamp, $id_creator, 
			$titulo, $origen, $estado, $grupo, $user,
			$email_notify, $priority, $description,
			$epilog, $id_task, $resolution, $id_incident_type,
			$idParentValue, $sla_man, $id);
	$result = process_sql ($sql);

    // When close incident set close date to current date
    if (($estado == 6) OR ($estado == 7)){
        $sql = sprintf ('UPDATE tincidencia SET cierre = "%s" 
			WHERE id_incidencia = %d',$timestamp, $id);
        $result = process_sql ($sql);
    }


	audit_db ($id_author_inc, $config["REMOTE_ADDR"], "Incident updated", "User ".$config['id_user']." incident updated #".$id);

	$old_incident_inventories = array_keys(get_inventories_in_incident($id));
	
	/* Update inventory objects in incident */
	update_incident_inventories ($id, get_parameter ('inventories', $old_incident_inventories));

	if ($config['incident_reporter'] == 1)
		update_incident_contact_reporters ($id, get_parameter ('contacts'));
	
	if ($result === false)
		$result_msg = "<h3 class='error'>".__('There was a problem updating incident')."</h3>";
	else
		$result_msg = "<h3 class='suc'>".__('Incident successfully updated')."</h3>";

	// Email notify to all people involved in this incident
	if ($email_notify == 1) {
        if (($estado == 7) OR ($estado ==6) OR ($config["email_on_incident_update"] == 1)){
            if (($estado == 7) OR ($estado ==6))
    			mail_incident ($id, $user, "", 0, 5);
            else
    			mail_incident ($id, $user, "", 0, 0);
		}
	}

	if (defined ('AJAX')) {
		if($massive_number_loop != -1) {
			echo $massive_number_loop;
		}
		else {
			echo $result_msg;
		}
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
	$id_creator = get_parameter ('id_creator', $config["id_user"]);
	$estado = get_parameter ("incident_status");
	$resolution = get_parameter ("incident_resolution");
	$id_task = (int) get_parameter ("task_user");
	$email_notify = (bool) get_parameter ('email_notify');
	$id_incident_type = get_parameter ('id_incident_type');
	$sla_disabled = (bool) get_parameter ("sla_disabled");
	$id_parent = (int) get_parameter ('id_parent');
	$email_copy = get_parameter ("email_copy", "");

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
			$titulo, $description, $usuario,
			$origen, $estado, $priority, $grupo, $id_creator,
			$email_notify, $id_task, $resolution, $id_incident_type,
			$idParentValue, $sla_disabled, $email_copy);

	$id = process_sql ($sql, 'insert_id');

	if ($id !== false) {
		/* Update inventory objects in incident */
		update_incident_inventories ($id, get_parameter ('inventories'));
		if ($config['incident_reporter'] == 1)
			update_incident_contact_reporters ($id, get_parameter ('contacts'));
		
		$result_msg = '<h3 class="suc">'.__('Successfully created').' (id #'.$id.')</h3>';
		$result_msg .= '<h4><a href="index.php?sec=incidents&sec2=operation/incidents/incident&id='.$id.'">'.__('Please click here to continue working with incident #').$id."</a></h4>";

		audit_db ($config["id_user"], $config["REMOTE_ADDR"],
			"Incident created",
			"User ".$config['id_user']." created incident #".$id);
		
//		incident_tracking ($id, INCIDENT_CREATED);

		// Email notify to all people involved in this incident
		if ($email_notify) {
			mail_incident ($id, $usuario, "", 0, 1);
		}
	} else {
		$result_msg  = '<h3 class="err">'.__('Could not be created').'</h3>';
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
	$sla_disabled = $incident["sla_disabled"];
	$affected_sla_id = $incident["affected_sla_id"];
	$id_incident_type = $incident['id_incident_type'];
    $email_copy = $incident["email_copy"];

	$grupo = dame_nombre_grupo($id_grupo);
    $score = $incident["score"];

	// Aditional ACL check on read incident
	if (give_acl ($config["id_user"], $id_grupo, "IR") == 0) { // Only admins
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Forbidden","User ".$config["id_user"]." try to access to an unauthorized incident ID #id_inc");
		no_permission ();
	}
	
	// Workunit ADD
	$insert_workunit = (bool) get_parameter ('insert_workunit');
	if ($insert_workunit) {
//		$timestamp = (string) get_parameter ("timestamp");
		$timestamp = print_mysql_timestamp();
		$nota = get_parameter ("nota");
		$timeused = (float) get_parameter ('duration');
		$have_cost = (int) get_parameter ('have_cost');
		$profile = (int) get_parameter ('work_profile');
		$public = (bool) get_parameter ('public');

        // Adding a new workunit to a incident in NEW status
        // Status go to "Assigned" and Owner is the writer of this Workunit
        if (($incident["estado"] == 1) AND ($incident["id_creator"] != $config['id_user'])){
            $sql = sprintf ('UPDATE tincidencia SET id_usuario = "%s", estado = 3,  affected_sla_id = 0, actualizacion = "%s" WHERE id_incidencia = %d', $config['id_user'], $timestamp, $id);
        } else {
            $sql = sprintf ('UPDATE tincidencia SET affected_sla_id = 0, actualizacion = "%s" WHERE id_incidencia = %d', $timestamp, $id);
        }

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
                if ($config["email_on_incident_update"] == 1){
    				mail_incident ($id, $config['id_user'], 0, 0, 2);
                }
			}
			
			// Copy file to directory and change name
			$short_filename = $filename;
			$filename = $config["homedir"]."/attachment/".$id_attachment."_".$filename;
			
			if (! copy ($_FILES['userfile']['tmp_name'], $filename)) {
				$result_msg = '<h3 class="error">'.__('File cannot be saved. Please contact Integria administrator about this error').'</h3>';
				$sql = sprintf ('DELETE FROM tattachment
						WHERE id_attachment = %d', $id_attachment);
				process_sql ($sql);
			} else {
				// Delete temporal file
				unlink ($_FILES['userfile']['tmp_name']);

	                        // Adding a WU noticing about this
	                        $nota = "Automatic WU: Added a file to this issue. Filename uploaded: ". $short_filename;
         	                $public = 1;
				$timestamp = print_mysql_timestamp();
				$timeused = "0.05";
	                        $sql = sprintf ('INSERT INTO tworkunit (timestamp, duration, id_user, description, public) VALUES ("%s", %.2f, "%s", "%s", %d)', $timestamp, $timeused, $config['id_user'], $nota, $public);

	                        $id_workunit = process_sql ($sql, "insert_id");
				$sql = sprintf ('INSERT INTO tworkunit_incident (id_incident, id_workunit) VALUES (%d, %d)', $id, $id_workunit);
				process_sql ($sql);
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
			$result_msg = '<h3 class="suc">'.__('Successfully deleted').'</h3>';
			if (!unlink ($config["homedir"].'attachment/'.$id_attachment.'_'.$filename))
				$result_msg = '<h3 class="error">'.__('Could not be deleted').'</h3>';
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
    $score = 0;
	$epilog = "";
	$id_creator = $config['id_user'];
	$email_notify = 0;
	$sla_disabled = 0;
	$id_incident_type = 0;
	$affected_sla_id = 0;
    $email_copy = "";

}
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Show the form
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

$default_responsable = "";
if (! $id) {
	if ($config["enteprise"] == 1){
		// How many groups has this user ?
		$number_group = get_db_sql ("SELECT COUNT(id_grupo) FROM tusuario_perfil WHERE id_usuario = '$usuario'");
		// Take first group defined for this user
		$default_id_group = get_db_sql ("SELECT id_grupo FROM tusuario_perfil WHERE id_usuario = '$usuario' LIMIT 1");
	} else {
		$default_id_group = 1;
		$number_group = 1;
	}
	// if have only one group, select default user and email for this group
	$email_notify = false;
}
$has_permission = (give_acl ($config['id_user'], $id_grupo, "IM")  || ($usuario == $config['id_user']));
$has_manage_permission = give_acl ($config['id_user'], $id_grupo, "IM");


if ($id) {
	echo "<h1>";
	if ($affected_sla_id != 0) {
		echo '<img src="images/exclamation.png" border=0 valign=top title="'.__('SLA Fired').'">&nbsp;&nbsp;';
	}

	echo __('Incident')." #$id"."&nbsp;&nbsp;";

	/* Delete incident */
	if ($has_permission) {
		echo '<form name="delete_incident_form" class="delete action" method="post" action="index.php?sec=incidents&sec2=operation/incidents/incident">';
		print_input_hidden ('quick_delete', $id, false);
		echo '<input type="image" class="action" src="images/cross.png" title="' . __('Delete') .'">';
		echo '</form>';
	}
	if (give_acl ($config['id_user'], $id_grupo, "KW")) {
		echo '<form name="kb_form" ';
		if ($estado != 6) {
			echo 'style="display:none" ';
		}
		echo 'class="action" method="post" action="index.php?sec=kb&sec2=operation/kb/manage_data&create=1">';
		print_input_hidden ('id_incident', $id, false);
		echo '<input type="image" class="action" src="images/star.png" title="' . __('Add to KB') .'">';
		echo '</form>';
	}

    if (give_acl($config["id_user"], 0, "IM")){
        if ($incident["score"] > 0){
            echo "( ".__("Scoring");
            echo " ". $incident["score"]. "/10 )";
        }
    }

	echo "</h1>";

    // Score this incident  
    if ($id){
        if (($incident["score"] == 0) AND (($incident["id_creator"] == $config["id_user"]) AND ( 
    	($incident["estado"] ==6) OR ($incident["estado"] == 7)))) {
            echo "<form method=post action=index.php?sec=incidents&sec2=operation/incidents/incident_score&id=$id>";
            echo "<table width=98% cellpadding=4 cellspacing=4><tr><td>";
            echo "<img src='images/award_star_silver_1.png' width=32>&nbsp;";
            echo "</td><td>";
            echo __('Please, help to improve the service and give us a score for the resolution of this incident. People assigned to this incident will not view directly your scoring.');
            echo "</td><td>";
            echo "<select name=score>";
            echo "<option value=10>".__("Very good, excellent !")."</option>";
            echo "<option value=8>".__("Good, very satisfied.")."</option>";
            echo "<option value=6>".__("It's ok, but could be better.")."</option>";
            echo "<option value=5>".__("Average. Not bad, not good.")."</option>";
            echo "<option value=4>".__("Bad, you must to better")."</option>";
            echo "<option value=2>".__("Very bad")."</option>";
            echo "<option value=1>".__("Horrible, you need to change it.")."</option>";
            echo "</select>";
            echo "</td><td>";
            print_submit_button (__('Score'), 'accion', false, 'class="sub next"');
            echo "</td></tr></table>";
            echo "</form>";
    	}
    }

} else {
	if (! defined ('AJAX'))
		echo "<h2>".__('Create incident')."</h2>";
}

echo '<div class="result">'.$result_msg.'</div>';

$table->width = '98%';
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

if ($config['incident_reporter'] == 0){
    $table->colspan[4][2] = 2; 
}
$table->colspan[5][0] = 4;
$table->colspan[6][0] = 4;
$table->colspan[7][0] = 4;

$disabled = !$has_permission;
$actual_only = !$has_permission;

if ($has_permission) {
	$table->data[0][0] = print_input_text ('titulo', $titulo, '', 40, 100, true, __('Title'));
} else {
	$table->data[0][0] = print_label (__('Title'), '', '', true, $titulo);
}


if ($has_manage_permission){
	$table->data[0][1] = print_checkbox_extended ('sla_disabled', 1, $sla_disabled,
	        $disabled, '', '', true, __('SLA disabled'));

	$table->data[0][2] = print_checkbox_extended ('email_notify', 1, $email_notify,
	                $disabled, '', '', true, __('Notify changes by email'));

// DEBUG
	$table->data[0][2] .= print_input_text ('email_copy', $email_copy,"",20,500, true);

	$table->data[1][0] = combo_incident_status ($estado, $disabled, $actual_only, true);

} else {
	$table->data[0][2] = print_input_hidden ('email_notify', 1, true);
	$table->data[0][1] = print_input_hidden ('sla_disabled', 0, true);

	$table->data[1][0] = print_label (__('Status'), '','',true, render_status($estado));
	$table->data[1][0] .= print_input_hidden ('incident_status', $estado, true);
}

if ($disabled) {
	$table->data[1][1] = print_label (__('Priority'), '', '', true,
		render_priority ($priority));
} else {
	$table->data[1][1] = print_select (get_priorities (),
		'priority_form', $priority, '', '',
		'', true, false, false, __('Priority'));
}

$table->data[1][1] .= '&nbsp;'. print_priority_flag_image ($priority, true);

if ($has_manage_permission)
	$table->data[1][2] = combo_incident_resolution ($resolution, $disabled, true);
else {
	$table->data[1][2] = print_label (__('Resolution'), '','',true, render_resolution($resolution));
	$table->data[1][2] .= print_input_hidden ('incident_resolution', $resolution, true);
}


$parent_name = $id_parent ? (__('Incident').' #'.$id_parent) : __('None');

$table->data[1][3] = print_button ($parent_name, 'search_parent', $disabled, '',
			'class="dialogbtn"', true, __('Parent incident'));
$table->data[1][3] .= print_input_hidden ('id_parent', $id_parent, true);

// Show link to go parent incident
if ($id_parent)
	$table->data[1][3] .= '&nbsp;<a href="index.php?sec=incidents&sec2=operation/incidents/incident&id='.$id_parent.'"><img src="images/go.png" /></a>';

$table->data[2][0] = combo_incident_origin ($origen, $disabled, true);
$table->data[2][1] = combo_incident_types ($id_incident_type, $disabled, true);
$table->data[2][2] = combo_task_user ($id_task, $config["id_user"], $disabled, false, true);

if ($config['incident_reporter'] == 1){

	if ($id) {
		$contacts = get_incident_contact_reporters ($id, true);
	} else {
		$contacts = array ();
	}

	$table->data[4][3] = print_select ($contacts, 'select_contacts', NULL,
					'', '', '', true, false, false, __('Reporters'));
	if ($has_permission || $create_incident) {
		$table->data[4][3] .= print_button (__('Add'),
						'search_contact', false, '', 'class="dialogbtn"', true);
		$table->data[4][3] .= print_button (__('Remove'),
						'delete_contact', false, '', 'class="dialogbtn"', true);
		foreach ($contacts as $contact_id => $contact_name) {
			$table->data[4][3] .= print_input_hidden ("contacts[]",
								$contact_id, true, 'selected-contacts');
		}
	}
}

if ($id_task > 0){
	$id_project = get_db_value ("id_project", "ttask", "id", $id_task);
	$table->data[2][2] .= "&nbsp;<a href='index.php?sec=projects&sec2=operation/projects/task_detail&id_project=$id_project&id_task=$id_task&operation=view'>";
	$table->data[2][2] .= "<img src='images/bricks.png'></a>";
}


// Incident creator. Only can be changed by an admin
if (get_db_value_filter('nivel', 'tusuario', array('id_usuario' => $config['id_user'])) == 1) {
     $enabled = true;
}
else {
     $enabled = false;
}
if ($enabled){
	$src_code = print_image('images/group.png', true, false, true);
	$table->data[2][3] = print_input_text_extended ('id_creator', '', 'text-id_creator', '', 15, 30, false, '',
			array('style' => 'background: url(' . $src_code . ') no-repeat right;'), true, '', __('Creator'))
		. print_help_tip (__("Type at least two characters to search"), true);
} else {
	$table->data[2][3] = "<input type='hidden' name=id_creator value=$id_creator>";
}

if ($has_permission) {
	$table->data[4][0] = combo_groups_visible_for_me ($config['id_user'], "grupo_form", 0, "IW", $id_grupo, true) . "<div id='group_spinner'></div>";
} else {
	$table->data[4][0] = print_label (__('Group'), '', '', true, dame_nombre_grupo ($id_grupo));
	$table->data[4][0] .= "<input type='hidden' name=grupo_form value=$id_grupo>";
}

// Only users with manage permission can change auto-assigned user (that information comes from group def.)
if ($has_manage_permission) {
	$src_code = print_image('images/group.png', true, false, true);
	$table->data[4][1] = print_input_text_extended ('id_user', 'Default Admin', 'text-id_user', '', 15, 30, false, '',
			array('style' => 'background: url(' . $src_code . ') no-repeat right;'), true, '', __('Assigned user'))
		. print_help_tip (__("User assigned here is user that will be responsible to manage incident. If you are opening an incident and want to be resolved by someone different than yourself, please assign to other user"), true);
} else {
	// Enterprise only
	if (($create_incident) AND ($config["enteprise"] == 1)){
		$assigned_user_for_this_incident = get_default_user_for_incident ($usuario);
		$table->data[4][1] = print_input_hidden ('usuario_form', $assigned_user_for_this_incident, true, __('Assigned user'));
		$table->data[4][1] .= print_label (__('Assigned user'), '', '', true,
		dame_nombre_real ($assigned_user_for_this_incident));	

	} else {
		$table->data[4][1] = print_input_hidden ('usuario_form', $usuario, true, __('Assigned user'));
		$table->data[4][1] .= print_label (__('Assigned user'), '', '', true,
		dame_nombre_real ($usuario));
	}
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
	} else {
	/* Default inventory items for this user:

		* ONLY FOR ENTERPRISE *

		- After choosing group (AJAX) get the default inventory item for that group *TODO
		$default_inventory = get_db_sql ("SELECT id_inventory FROM tinventory WHERE name grupo WHERE id_grupo = XXX"); 

		$inventories[$default_inventory] =  get_db_value ('name', 'tinventory', 'id', $default_inventory);
	*/

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

// This is never shown in create form

if (!$create_incident){
	$table->data[6][0] = print_textarea ('epilog', 5, 80, $epilog, $disabled_str,	true, __('Resolution epilog'));
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
<script type="text/javascript" src="include/js/jquery.autocomplete.js"></script>
<script  type="text/javascript">
$(document).ready (function () {
	/* First parameter indicates to add AJAX support to the form */
	configure_incident_form (false);
	//$("#grupo_form").change ();
	
	$("#text-id_creator").autocomplete ("ajax.php",
		{
			scroll: true,
			minChars: 2,
			extraParams: {
				page: "operation/incidents/incident_detail",
				search_users: 1,
				id_user: "<?php echo $config['id_user'] ?>"
			},
			formatItem: function (data, i, total) {
				if (total == 0)
					$("#text-id_creator").css ('background-color', '#cc0000');
				else
					$("#text-id_creator").css ('background-color', '');
				if (data == "")
					return false;
				return data[0]+'<br><span class="ac_extra_field"><?php echo __(" ") ?>: '+data[1]+'</span>';
			},
			delay: 200

		});
		
		$("#text-id_user").autocomplete ("ajax.php",
		{
			scroll: true,
			minChars: 2,
			extraParams: {
				page: "operation/incidents/incident_detail",
				search_users: 1,
				id_user: "<?php echo $config['id_user'] ?>"
			},
			formatItem: function (data, i, total) {
				if (total == 0)
					$("#text-id_user").css ('background-color', '#cc0000');
				else
					$("#text-id_user").css ('background-color', '');
				if (data == "")
					return false;
				return data[0]+'<br><span class="ac_extra_field"><?php echo __(" ") ?>: '+data[1]+'</span>';
			},
			delay: 200

		});
});
</script>

<?php endif; ?>
