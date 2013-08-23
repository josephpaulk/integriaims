<?php
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008-2012 Ártica Soluciones Tecnológicas
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

require_once ('include/functions_incidents.php');
require_once ('include/functions_user.php');

if (defined ('AJAX')) {
	
	global $config;
	
	$show_type_fields = (bool) get_parameter('show_type_fields', 0);
 	
 	if ($show_type_fields) {
		$id_incident_type = get_parameter('id_incident_type');
		$id_incident = get_parameter('id_incident');		
		$fields = incidents_get_all_type_field ($id_incident_type, $id_incident);
	
		echo json_encode($fields);
		return;
	}
}

$id_grupo = (int) get_parameter ('id_grupo');
$id = (int) get_parameter ('id');
$id_task = (int) get_parameter ('id_task');

if ($id) {
	$incident = get_incident ($id);
	if ($incident !== false) {
		$id_grupo = $incident['id_grupo'];
	}
}

$check_incident = (bool) get_parameter ('check_incident');

if ($check_incident) {
	// IR and incident creator can see the incident
	if ($incident !== false && (give_acl ($config['id_user'], $id_grupo, "IR")
		|| ($incident["id_creator"] == $config["id_user"]))){
	
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

if (isset($incident)) {
	//Incident creators must see their incidents
	if ((get_external_user($config["id_user"]) && ($incident["id_creator"] != $config["id_user"]))
		|| ($incident["id_creator"] != $config["id_user"]) && !give_acl ($config['id_user'], $id_grupo, "IR")) {
	
	 	// Doesn't have access to this page
		audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to incident  (External user) ".$id);
		include ("general/noaccess.php");
		exit;
	}
}
else if (! give_acl ($config['id_user'], $id_grupo, "IR")) {
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

// Delete incident
$quick_delete = get_parameter("quick_delete");
if ($quick_delete) {
	$id_inc = $quick_delete;
	$sql2="SELECT * FROM tincidencia WHERE id_incidencia=".$id_inc;
	$result2=mysql_query($sql2);
	$row2=mysql_fetch_array($result2);
	if ($row2) {
		$id_author_inc = $row2["id_usuario"];
		$email_notify = $row2["notify_email"];
		if (give_acl ($config['id_user'], $row2["id_grupo"], "IM") || $config['id_user'] == $id_author_inc) {
			borrar_incidencia($id_inc);

			echo "<h3 class='suc'>".__('Incident successfully deleted')."</h3>";
			audit_db($config["id_user"], $config["REMOTE_ADDR"], "Incident deleted","User ".$config['id_user']." deleted incident #".$id_inc);
		} else {
			audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Forbidden","User ".$config['id_user']." try to delete incident");
			echo "<h3 class='error'>".__('There was a problem deleting incident')."</h3>";
			no_permission();
		}
	}
}


if ($action == 'update') {
	// Number of loop in the massive operations. No received in not massive ones
	$massive_number_loop = get_parameter ('massive_number_loop', -1);
	
	$old_incident = get_incident ($id);
	
	$user = get_parameter('id_user');
	
	$grupo = get_parameter ('grupo_form', $old_incident['id_grupo']);
	
	// Only admins (manage incident) or owners can modify incidents
	if ((! give_acl ($config["id_user"], $grupo, "IW")) AND (! give_acl ($config["id_user"], $grupo, "IW"))) {
		audit_db ($config['id_user'], $config["REMOTE_ADDR"],"ACL Forbidden","User ".$_SESSION["id_usuario"]." try to update incident");
		echo "<h3 class='error'>".__('There was a problem updating incident')."</h3>";
		no_permission ();
		exit ();
	}
	
	$id_author_inc = get_incident_author ($id);
	$titulo = get_parameter ('titulo', $old_incident['titulo']);
	$sla_disabled = (bool) get_parameter ('sla_disabled'); //Get SLA given on submit
	$description = get_parameter ('description', $old_incident['descripcion']);
	$priority = get_parameter ('priority_form', $old_incident['prioridad']);
	$estado = get_parameter ('incident_status', $old_incident['estado']);
	$email_notify = (bool) get_parameter ('email_notify', $old_incident['notify_email']);
	$epilog = get_parameter ('epilog', $old_incident['epilog']);
	$resolution = get_parameter ('incident_resolution', $old_incident['resolution']);
	$id_task = (int) get_parameter ('id_task', $old_incident['id_task']);
	$id_incident_type = get_parameter ('id_incident_type', $old_incident['id_incident_type']);
	$id_parent = (int) get_parameter ('id_parent');
	$id_creator = get_parameter ('id_creator', $old_incident['id_creator']);
	$email_copy = get_parameter ('email_copy', '');
	$closed_by = get_parameter ('closed_by', $old_incident['closed_by']);

	if ($id_incident_type != 0) {
		$sql_label = "SELECT `label` FROM `tincident_type_field` WHERE id_incident_type = $id_incident_type";
		$labels = get_db_all_rows_sql($sql_label);
		
		if ($labels === false) {
			$labels = array();
		}
	
		foreach ($labels as $label) {
			$values['data'] = safe_output(get_parameter (base64_encode($label['label'])));
			$id_incident_field = get_db_value_filter('id', 'tincident_type_field', array('id_incident_type' => $id_incident_type, 'label'=> $label['label']), 'AND');
			$values['id_incident_field'] = $id_incident_field;
			$values['id_incident'] = $id;
			
			$exists_id = get_db_value_filter('id', 'tincident_field_data', array('id_incident' => $id, 'id_incident_field'=> $id_incident_field), 'AND');
			if ($exists_id) 
				process_sql_update('tincident_field_data', $values, array('id_incident_field' => $id_incident_field, 'id_incident' => $id), 'AND');
			else
				process_sql_insert('tincident_field_data', $values);
		}
	}
	
	//Add traces and statistic information
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
		incident_tracking ($id, INCIDENT_RESOLUTION_CHANGED, $resolution);
		$tracked = true;
	}
	if ($old_incident['id_usuario'] != $user) {
		incident_tracking ($id, INCIDENT_USER_CHANGED, $user);
		$tracked = true;
	}
	if ($old_incident["id_grupo"] != $grupo) {
		incident_tracking ($id, INCIDENT_GROUP_CHANGED, $grupo);
		$tracked = true;
	}
		
	if($tracked == false) {
		incident_tracking ($id, INCIDENT_UPDATED);
	}
	
	
	$metric_values = array(INCIDENT_METRIC_STATUS => $estado,
							INCIDENT_METRIC_USER => $user,
							INCIDENT_METRIC_GROUP => $grupo);
	
	incidents_add_incident_stat ($id, $metric_values);
	
	if ($sla_disabled == 1)
		$sla_man = ", sla_disabled = 1 ";
	else 
		$sla_man = ", sla_disabled = 0";
	
	if ($id_parent == 0) {
		$idParentValue = 'NULL';
	}
	else {
		$idParentValue = sprintf ('%d', $id_parent);
	}
	$timestamp = print_mysql_timestamp();
	
	$sql = sprintf ('UPDATE tincidencia SET email_copy = "%s", actualizacion = "%s",
			  id_creator = "%s",
			titulo = "%s", estado = %d,
			id_grupo = %d, id_usuario = "%s", closed_by = "%s",
			notify_email = %d, prioridad = %d, descripcion = "%s",
			epilog = "%s", id_task = %d, resolution = %d,
			id_incident_type = %d, id_parent = %s, affected_sla_id = 0 %s 
			WHERE id_incidencia = %d', $email_copy, $timestamp, $id_creator, 
			$titulo, $estado, $grupo, $user, $closed_by,
			$email_notify, $priority, $description,
			$epilog, $id_task, $resolution, $id_incident_type,
			$idParentValue, $sla_man, $id);
	$result = process_sql ($sql);
	
	// When close incident set close date to current date
	if (($estado == 7)){
		$sql = sprintf ('UPDATE tincidencia SET cierre = "%s" 
			WHERE id_incidencia = %d',$timestamp, $id);
		$result = process_sql ($sql);
	}


	audit_db ($id_author_inc, $config["REMOTE_ADDR"], "Incident updated", "User ".$config['id_user']." incident updated #".$id);

	$old_incident_inventories = array_keys(get_inventories_in_incident($id));
	
	$incident_inventories = get_parameter("inventories");
		
	/* Update inventory objects in incident */
	update_incident_inventories ($id, get_parameter ('inventories', $incident_inventories));
	
	if ($result === false)
		$result_msg = "<h3 class='error'>".__('There was a problem updating incident')."</h3>";
	else
		$result_msg = "<h3 class='suc'>".__('Incident successfully updated')."</h3>";

	// Email notify to all people involved in this incident
	if ($email_notify == 1) {
		if (($estado == 7) OR ($config["email_on_incident_update"] == 1)){
            if (($estado == 7))
    			mail_incident ($id, $user, "", 0, 5);
            else
    			mail_incident ($id, $user, "", 0, 0);
		}
	}

}

if ($action == "insert") {
	$grupo = (int) get_parameter ('grupo_form');

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
	$priority = get_parameter ('priority_form');
	$id_creator = get_parameter ('id_creator', $config["id_user"]);
	$estado = get_parameter ("incident_status");
	$resolution = get_parameter ("incident_resolution");
	$id_task = (int) get_parameter ("id_task");
	$email_notify = (bool) get_parameter ('email_notify');
	$id_incident_type = get_parameter ('id_incident_type');
	$sla_disabled = (bool) get_parameter ("sla_disabled");
	$id_parent = (int) get_parameter ('id_parent');
	$email_copy = get_parameter ("email_copy", "");
	
	//Get notify flag from group if the user doesn't has IM flag
	if (! give_acl ($config['id_user'], $id_grupo, "IW")) {
			$email_notify = get_db_value("forced_email", "tgrupo", "id_grupo", $grupo);
	}
	
	// If user is not provided, is the currently logged user
	$usuario = get_parameter ("id_user", $config['id_user']);
	
	$closed_by = get_parameter ("closed_by", '');

	// Redactor user is ALWAYS the currently logged user entering the incident. Cannot change. Never.
	$editor = $config["id_user"];

    $id_group_creator = get_parameter ("id_group_creator", $grupo);

	$creator_exists = get_user($id_creator);
	$user_exists = get_user($usuario);


	if($creator_exists === false) {
		$result_msg  = '<h3 class="error">'.__('Creator user does not exist').'</h3>';
	}
	else if($user_exists === false) {
		$result_msg  = '<h3 class="error">'.__('Owner user does not exist').'</h3>';
	}
	else {
	
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
				id_usuario, closed_by, estado, prioridad,
				id_grupo, id_creator, notify_email, id_task,
				resolution, id_incident_type, id_parent, sla_disabled, email_copy, editor, id_group_creator)
				VALUES ("%s", "%s", "%s", "%s", "%s", "%s", %d, %d, %d,
				"%s", %d, %d, %d, %d, %s, %d, "%s", "%s", "%s")', $timestamp, $timestamp,
				$titulo, $description, $usuario, $closed_by,
				$estado, $priority, $grupo, $id_creator,
				$email_notify, $id_task, $resolution, $id_incident_type,
				$idParentValue, $sla_disabled, $email_copy, $editor, $id_group_creator);	

		$id = process_sql ($sql, 'insert_id');

		if ($id !== false) {
			/* Update inventory objects in incident */
			update_incident_inventories ($id, get_parameter ('inventories'));
			
			$result_msg = '<h3 class="suc">'.__('Successfully created').' (id #'.$id.')</h3>';
			$result_msg .= '<h4><a href="index.php?sec=incidents&sec2=operation/incidents/incident_dashboard_detail&id='.$id.'">'.__('Please click here to continue working with incident #').$id."</a></h4>";

			audit_db ($config["id_user"], $config["REMOTE_ADDR"],
				"Incident created",
				"User ".$config['id_user']." created incident #".$id);
				
			//Add traces and statistic information	
			incident_tracking ($id, INCIDENT_STATUS_CHANGED, $estado);
	
			incident_tracking ($id, INCIDENT_USER_CHANGED, $usuario);
			
			incident_tracking ($id, INCIDENT_GROUP_CHANGED, $id_group_creator);
						
			incident_tracking ($id, INCIDENT_CREATED);
			
			//Add first incident statistics
			$metric_values = array (INCIDENT_METRIC_STATUS => $estado,
									INCIDENT_METRIC_USER => $usuario,
									INCIDENT_METRIC_GROUP => $id_group_creator);
	
			incidents_add_incident_stat ($id, $metric_values);

			// Create automatically a WU with the editor ?
			if ($config["incident_creation_wu"] == 1){
				$wu_text = __("WU automatically created by the editor on the incident creation.");
				// Do not send mail in this WU
				create_workunit ($id, $wu_text, $editor, $config["iwu_defaultime"], 0, "", 1, 0);
			}


			// Email notify to all people involved in this incident
			if ($email_notify) {
				mail_incident ($id, $usuario, "", 0, 1);
			}
			
			//insert data to incident type fields
			if ($id_incident_type != 0) {
				$sql_label = "SELECT `label` FROM `tincident_type_field` WHERE id_incident_type = $id_incident_type";
				$labels = get_db_all_rows_sql($sql_label);
			
				if ($labels === false) {
					$labels = array();
				}
				
				foreach ($labels as $label) {

					$id_incident_field = get_db_value_filter('id', 'tincident_type_field', array('id_incident_type' => $id_incident_type, 'label'=> $label['label']), 'AND');
					
					$values_insert['id_incident'] = $id;
					$values_insert['data'] = get_parameter (base64_encode($label['label']));
					$values_insert['id_incident_field'] = $id_incident_field;
					$id_incident_field = get_db_value('id', 'tincident_type_field', 'id_incident_type', $id_incident_type);
					process_sql_insert('tincident_field_data', $values_insert);
				}
			}
	
		} else {
			$result_msg  = '<h3 class="error">'.__('Could not be created').'</h3>';
		}
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
	
	//We could have several problems with cache on incident update
	clean_cache_db();
	
	$incident = get_db_row ('tincidencia', 'id_incidencia', $id);
	// Get values
	$titulo = $incident["titulo"];
	$description = $incident["descripcion"];
	$inicio = $incident["inicio"];
	$actualizacion = $incident["actualizacion"];
	$estado = $incident["estado"];
	$priority = $incident["prioridad"];
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
	$editor = $incident["editor"];
    $id_group_creator = $incident["id_group_creator"];
    $closed_by = $incident["closed_by"];

	$grupo = dame_nombre_grupo($id_grupo);
        $score = $incident["score"];

	// Aditional ACL check on read incident
	if ((give_acl ($config["id_user"], $id_grupo, "IR") == 0) 
		&& ($incident['id_creator'] != $config['id_user'])) { // Only admins and incident creators allowed
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Forbidden","User ".$config["id_user"]." try to access to an unauthorized incident ID #id_inc");
		no_permission ();
	}

} else {
	$titulo = "";
	$description = "";
	$priority = 2;
	$id_grupo =0;
	$grupo = dame_nombre_grupo (1);
	$id_parent = 0;
	$usuario= $config["id_user"];
	$estado = 1;
	$resolution = 0;
    $score = 0;
	$epilog = "";
	$id_creator = $config['id_user'];
	
	//Email notify default value is the same that forced_email group field
	$email_notify = get_db_value("forced_email", "tgrupo", "id_grupo", $id_group);
	
	if($email_notify) {
		$email_notify = 1;
	} else {
		$email_notify = 0;
	}
	$sla_disabled = 0;
	$id_incident_type = 0;
	$affected_sla_id = 0;
    $email_copy = "";
	$editor = $config["id_user"];
    $id_group_creator = 0;
    $closed_by= "";

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
}

//The user with IW flag or the incident owner can modify all data from the incident.
$has_permission = (give_acl ($config['id_user'], $id_grupo, "IW")  || ($usuario == $config['id_user']));
$has_im  = give_acl ($config['id_user'], $id_grupo, "IM");
$has_iw = give_acl ($config['id_user'], $id_grupo, "IW");

if ($id) {	
	
	echo "<h1>";
	if ($affected_sla_id != 0) {
		echo '<img src="images/exclamation.png" border=0 valign=top title="'.__('SLA Fired').'">&nbsp;&nbsp;';
	}

	echo __('Incident').' #'.$id.' - '.$incident['titulo'];
	
    if (give_acl($config["id_user"], 0, "IM")){
        if ($incident["score"] > 0){
            echo "( ".__("Scoring");
            echo " ". $incident["score"]. "/10 )";
        }
    }
	
	echo "<div id='button-bar-title'>";
	echo "<ul>";
	echo '<li>';
	echo '<a href="index.php?sec=incidents&sec2=operation/incidents/incident_dashboard_detail&id='.$id.'">'.print_image("images/go-previous.png", true, array("title" => __("Back to incident")))."</a>";
	echo '</li>';
	
	/* Delete incident */
	if ($has_im) {
		echo "<li>";
		echo '<form id="delete_incident_form" name="delete_incident_form" class="delete action" method="post" action="index.php?sec=incidents&sec2=operation/incidents/incident_detail">';
		print_input_hidden ('quick_delete', $id, false);
		echo '<a href="#" id="detele_incident_submit_form">'.print_image("images/cross.png", true, array("title" => __("Delete"))).'</a>';
		echo '</form>';
		echo "</li>";
		
	}
	
	//KB only appears for closed status
	if (give_acl ($config['id_user'], $id_grupo, "KW") && ($incident["estado"] == 7)) {
		echo "<li>";
		echo '<form id="kb_form" name="kb_form" ';
		echo 'class="action" method="post" action="index.php?sec=kb&sec2=operation/kb/manage_data&create=1">';
		print_input_hidden ('id_incident', $id, false);
		echo '<a href="#" id="kb_form_submit">'.__("Add to KB").'</a>';
		echo '</form>';
		echo "</li>";
	}	
	
	echo "</ul>";
	echo "</div>";	

	echo "</h1>";
	
	
	
    // Score this incident  
    if ($id){
		if (($incident["score"] == 0) AND (($incident["id_creator"] == $config["id_user"]) AND ( 
    	($incident["estado"] == 7)))) {
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
		echo "<h1>".__('Create incident')."</h1>";
}

echo '<div class="result">'.$result_msg.'</div>';
$table->width = '98%';
$table->class = 'search-table-button';
$table->id = "incident-editor";
$table->size = array ();
$table->size[0] = '25%';
$table->size[1] = '25%';
$table->size[2] = '25%';
$table->head = array();
$table->style = array();
$table->data = array ();
$table->cellspacing = 2;
$table->cellpadding = 2;
$table->colspan = array ();
$table->colspan[0][0] = 2;

if ($has_permission) {
	$table->data[0][0] = print_input_text ('titulo', $titulo, '', 55, 100, true, __('Title'));
} else {
	$table->data[0][0] = print_label (__('Title'), '', '', true, $titulo);
}

//Get group if was not defined
if($id_grupo==0) {
	$id_grupo_incident = get_db_value("id_grupo", "tusuario_perfil", "id_usuario", $config['id_user']);
	
	//If no group assigned use ALL by default
	if (!$id_grupo_incident) {
		$id_grupo_incident = 1;
	}
	
} else {
	$id_grupo_incident = $id_grupo;
}

if ($has_im) {
	$table->data[0][2] = combo_groups_visible_for_me ($config['id_user'], "grupo_form", 0, "IW", $id_grupo_incident, true) . "<div id='group_spinner'></div>";
} else {
	$table->data[0][2] = print_label (__('Group'), '', '', true, dame_nombre_grupo ($id_grupo_incident));
	$table->data[0][2] .= "<input type='hidden' id=grupo_form name=grupo_form value=$id_grupo_incident>";
}

$disabled = false;

if ($disabled) {
	$table->data[1][0] = print_label (__('Priority'), '', '', true,
		$priority);
} else {
	$table->data[1][0] = print_select (get_priorities (),
		'priority_form', $priority, '', '',
		'', true, false, false, __('Priority'));
}

$table->data[1][0] .= '&nbsp;'. print_priority_flag_image ($priority, true);

if ($has_im)
	$table->data[1][1] = combo_incident_resolution ($resolution, $disabled, true);
else {
	$table->data[1][1] = print_label (__('Resolution'), '','',true, render_resolution($resolution));
	$table->data[1][1] .= print_input_hidden ('incident_resolution', $resolution, true);
}

/*
if (!$has_im){
	$table->data[1][2] = print_label (__('Status'), '','',true, render_status($estado));
	$table->data[1][2] .= print_input_hidden ('incident_status', $estado, true);
}
*/

$table->data[1][2] = combo_incident_status ($estado, $disabled, 0, true);

//If IW creator enabled flag is up the user can change creatro also.
if ($has_im || ($has_iw && $config['iw_creator_enabled'])){

	$params_creator['input_id'] = 'text-id_creator';
	$params_creator['input_name'] = 'id_creator';
	$params_creator['input_value'] = $id_creator;
	$params_creator['title'] = 'Creator';
	$params_creator['return'] = true;
	$params_creator['return_help'] = true;
	$table->data[2][0] = user_print_autocomplete_input($params_creator);
	
} else {
	$table->data[2][0] = "<input type='hidden' name=id_creator value=$id_creator>";
}

if ($has_im) {
	$src_code = print_image('images/group.png', true, false, true);
	
	if ($create_incident) 
		$assigned_user_for_this_incident = get_db_value("id_user_default", "tgrupo", "id_grupo", $id_grupo_incident);
	else
		$assigned_user_for_this_incident = $usuario;
	
	$params_assigned['input_id'] = 'text-id_user';
	$params_assigned['input_name'] = 'id_user';
	$params_assigned['input_value'] = $assigned_user_for_this_incident;
	$params_assigned['title'] = 'Owner';
	$params_assigned['help_message'] = "User assigned here is user that will be responsible to manage incident. If you are opening an incident and want to be resolved by someone different than yourself, please assign to other user";
	$params_assigned['return'] = true;
	$params_assigned['return_help'] = true;
	
	$table->data[2][1] = user_print_autocomplete_input($params_assigned);
} else {
	// Enterprise only
	if (($create_incident) AND ($config["enteprise"] == 1)){
		$assigned_user_for_this_incident = get_default_user_for_incident ($usuario);
		$table->data[2][1] = print_input_hidden ('id_user', $assigned_user_for_this_incident, true, __('Owner'));
		$table->data[2][1] .= print_label (__('Owner'), '', '', true,
		dame_nombre_real ($assigned_user_for_this_incident));	
		
	} else {
		$table->data[2][1] = print_input_hidden ('id_user', $usuario, true, __('Owner'));
		$table->data[2][1] .= print_label (__('Owner'), '', '', true,
		dame_nombre_real ($usuario));
	}
}

// closed by
if (!$create_incident){
	$params_closed['input_id'] = 'text-closed_by';
	$params_closed['input_name'] = 'closed_by';
	$params_closed['input_value'] = $closed_by;
	$params_closed['title'] = __('Closed by');
	$params_closed['help_message'] = __("User assigned here is user that will be responsible to close incident.");
	$params_closed['return'] = true;
	$params_closed['return_help'] = true;

	//Only print closed by option when incident status is closed
	if ($incident["estado"] == STATUS_CLOSED) {
		$table->data[2][2] = "<div id='closed_by_wrapper'>";
	} else {
		$table->data[2][2] = "<div id='closed_by_wrapper' style='display: none'>";
	}
	$table->data[2][2] .= user_print_autocomplete_input($params_closed);
	$table->data[2][2] .= "</div>";
}

$types = get_incident_types ();
$table->data[3][0] = print_label (__('Incident type'), '','',true);

//Disabled incident type if any, type changes not allowed
if ($id_incident_type == 0) {
	$disabled_itype = false;
} else {
	$disabled_itype = true;
}
$table->data[3][0] .= print_select($types, 'id_incident_type', $id_incident_type, 'show_incident_type_fields();', 'Select', '', true, 0, true, false, $disabled_itype);

$table->colspan[4][0] = 3;		
//$table->data[4][0] = "<tr id='row_show_type_fields' colspan='4'></tr>";
$table->data[4][0] = "";

//////TABLA ADVANCED
$table_advanced->width = '98%';
$table_advanced->class = 'search-table';
$table_advanced->size = array ();
$table_advanced->size[0] = '25%';
$table_advanced->size[1] = '25%';
$table_advanced->size[2] = '25%';
$table_advanced->style = array();
$table_advanced->data = array ();


// Table for advanced controls
$table_advanced->data[0][0] = print_label (__('Editor'), '', '', true, $editor);

if ($has_im){
	$table_advanced->data[0][1] = print_checkbox_extended ('sla_disabled', 1, $sla_disabled,
	        false, '', '', true, __('SLA disabled'));

	$table_advanced->data[0][2] = print_checkbox_extended ('email_notify', 1, $email_notify,
                false, '', '', true, __('Notify changes by email'));

} else {
	$table_advanced->data[1][1] = print_input_hidden ('email_notify', 1, true);
	$table_advanced->data[1][2] = print_input_hidden ('sla_disabled', 0, true);
}

$parent_name = $id_parent ? (__('Incident').' #'.$id_parent) : __('None');

if ($has_im) {
	$table_advanced->data[2][0] = print_input_text ('search_parent', $parent_name, '', 10, 100, true, __('Parent incident'));
	$table_advanced->data[2][0] .= print_input_hidden ('id_parent', $id_parent, true);
	$table_advanced->data[2][0] .= print_image("images/cross.png", true, array("onclick" => "clean_parent_field()", "style" => "cursor: pointer"));
}

// Show link to go parent incident
if ($id_parent)
	$table_advanced->data[2][0] .= '&nbsp;<a target="_blank" href="index.php?sec=incidents&sec2=operation/incidents/incident_dashboard_detail&id='.$id_parent.'"><img src="images/go.png" /></a>';

// Task
if ($has_im) { 
        $table_advanced->data[2][1] = combo_task_user_participant ($config["id_user"], 0, $id_task, true, __("Task"));
} else {
	$table_advanced->data[2][1] = print_label (__("Task"), "label-id", 'text', true);
	$table_advanced->data[2][1] .= "<i>".get_db_value ('name', 'ttask', 'id', $id_task)."</i>";
}


if ($id_task > 0){
	$id_project = get_db_value ("id_project", "ttask", "id", $id_task);
	$table_advanced->data[2][1] .= "&nbsp;<a href='index.php?sec=projects&sec2=operation/projects/task_detail&id_project=$id_project&id_task=$id_task&operation=view'>";
	$table_advanced->data[2][1] .= "<img src='images/bricks.png'></a>";
}

$table_advanced->data[2][2] = print_input_text ('email_copy', $email_copy,"",20,500, true, __("Additional email addresses"));

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
		
		$table_advanced->data[3][1] = print_select ($inventories, 'incident_inventories', NULL,
						'', '', '', true, false, false, __('Objects affected'));

		$table_advanced->data[3][1] .= "&nbsp;&nbsp;<a href='javascript: incident_show_inventory_search(\"\",\"\",\"\",\"\",\"\",\"\");'>" . print_image('images/add.png', true, array('title' => __('Add'))) . "</a>";

		$table_advanced->data[3][1] .= "&nbsp;&nbsp;<a href='javascript: removeInventory();'>" . print_image('images/cross.png', true, array('title' => __('Remove'))) . "</a>";
} else {
	$inventories = get_inventories_in_incident ($id);
	
	$table_advanced->data[3][1] = print_select ($inventories, 'incident_inventories',
						NULL, '', '', '',
						true, false, false, __('Objects affected'));

		$table_advanced->data[3][1] .= "&nbsp;&nbsp;<a href='javascript: incident_show_inventory_search(\"\",\"\",\"\",\"\",\"\",\"\");'>" . print_image('images/add.png', true, array('title' => __('Add'))) . "</a>";

		$table_advanced->data[3][1] .= "&nbsp;&nbsp;<a href='javascript: removeInventory();'>" . print_image('images/cross.png', true, array('title' => __('Remove'))) . "</a>";
}

foreach ($inventories as $inventory_id => $inventory_name) {
	$table_advanced->data[3][1] .= print_input_hidden ("inventories[]",
						$inventory_id, true, 'selected-inventories');
}


if (($has_im) && ($create_incident)){
    $table_advanced->data[3][2] =  print_label (__('Creator group'), '', '', true, ""); 
	$table_advanced->data[3][2] .= combo_groups_visible_for_me ($config['id_user'], "id_group_creator", false, "IW", true, __("Creator group"), false, false);
	$table->colspan[1][0] = 2;
} else {
	//Only show if there is information to show ;)
	if ($id_group_creator) {
		$table_advanced->data[3][2] = print_label (__('Creator group'), '', '', true, dame_nombre_grupo ($id_group_creator));
	}
}
// END TABLE ADVANCED

$table->colspan['row_advanced'][0] = 4;
$table->data['row_advanced'][0] = print_container('advanced_parameters_incidents_form', __('Advanced parameters'), print_table($table_advanced, true), 'closed', true, false);


$table->colspan[9][0] = 4;
$table->colspan[10][0] = 4;
$disabled_str = $disabled ? 'readonly="1"' : '';
$table->data[9][0] = print_textarea ('description', 9, 80, $description, $disabled_str,
		true, __('Description'));

// This is never shown in create form

if (!$create_incident){

	//Show or hidden epilog depending on incident status
	if ($incident["estado"] != STATUS_CLOSED) {		
		$table->data[10][0] = "<div id='epilog_wrapper' style='display: none;'>";
	} else {
		$table->data[10][0] = "<div id='epilog_wrapper'>";
	}
		
	$table->data[10][0] .= print_textarea ('epilog', 5, 80, $epilog, $disabled_str,	true, __('Resolution epilog'));
	
	$table->data[10][0] .= "</div>";
}

if ($create_incident) {
	$button = print_input_hidden ('action', 'insert', true);
	if (give_acl ($config["id_user"], 0, "IW")) {
		$button .= print_submit_button (__('Create'), 'accion', false, 'class="sub create"', true);
	}
} else {
	$button = print_input_hidden ('id', $id, true);
	$button .= print_input_hidden ('action', 'update', true);
	if ($has_permission) {
		$button .= print_submit_button (__('Update'), 'accion', false, 'class="sub upd"', true);
	}
}

$table->colspan[count($table->data)+1][0] = 4;
$table->data[count($table->data)+1][0] = $button;

if ($has_permission){
	echo '<form id="incident_status_form" method="post">';
	print_table ($table);
	echo "</form>";
} else {
	print_table ($table);
}

//id_incident hidden
echo '<div id="id_incident_hidden" style="display:none;">';
	print_input_text('id_incident_hidden', $id);
echo '</div>';

echo "<div class= 'dialog ui-dialog-content' id='inventory_search_modal'></div>";

echo "<div class= 'dialog ui-dialog-content' id='parent_search_window'></div>";
?>

<script type="text/javascript" src="include/js/jquery.metadata.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
<script type="text/javascript" src="include/js/integria_incident_search.js"></script>
<script type="text/javascript" src="include/js/integria_inventory.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.autocomplete.js"></script>


<script  type="text/javascript">

$(document).ready (function () {
	
	//Verify incident limit on view display and on group change
	var id_incident = <?php echo $id?>;
	var id_user = $("#text-id_user").val();
	var id_group = $("#grupo_form").val();
	
	//Only check incident on creation (where there is no id)
	if (id_incident != 0) {
		
		incident_limit("#submit-accion", id_user, id_group);
	}
	
	$("#grupo_form").change (function () {
		incident_limit("#submit-accion", id_user, id_group);
		
		var group = $("#grupo_form").val();
		
		var group_info = get_group_info(group);
		
		if (group_info.forced_email != "0") {
			$("#checkbox-email_notify").prop("checked", true);
		} else {
			$("#checkbox-email_notify").prop("checked", false);
		}
		
		$("#text-id_user").val(group_info.id_user_default);
		
	});
	
	/*Open parent search popup*/
	$("#text-search_parent").focus(function () {
		parent_search_form('');
	});
	
	//Validate form
	$("#incident_status_form").submit(function () {
		var title = $("#text-titulo").val();
		var creator = $("#text-id_creator").val();
		var assigned_user = $("#text-id_user").val();
		var closed_by = $("#text-closed_by").val();
		
		//Restore borders color
		$("#text-titulo").css("border-color", "#9A9B9D");
		$("#text-id_creator").css("border-color", "#9A9B9D");
		$("#text-id_user").css("border-color", "#9A9B9D");
		$("#text-closed_by").css("border-color", "#9A9B9D");
		
		//Validate fields
		if (title == "") {
			$("#text-titulo").css("border-color", "red");
			$("div.result").html("<h3 class='error'><?php echo __("Title field is empty")?></h3>");
			window.scrollTo(0,0);
			return false;
		}
		
		if (creator == "") {
			$("#text-id_creator").css("border-color", "red");
			$("div.result").html("<h3 class='error'><?php echo __("Creator field is empty")?></h3>");
			window.scrollTo(0,0);
			return false;
		}
		
		if (assigned_user == "") {
			$("#text-id_user").css("border-color", "red");
			$("div.result").html("<h3 class='error'><?php echo __("Owner field is empty")?></h3>");
			window.scrollTo(0,0);
			return false;
		}
		
		var status = $("#incident_status").val();
		
		//If closed not empty closed by
		if (status == 7) {
			
			if (closed_by == "") {
				$("#text-closed_by").css("border-color", "red");
				$("div.result").html("<h3 class='error'><?php echo __("Closed by field is empty")?></h3>");
				window.scrollTo(0,0);
				return false;
			}
		}
				
		return true;
		
	});
		
	//JS to create KB artico from incident
	$("#kb_form_submit").click(function (event) {
		event.preventDefault();
		$("#kb_form").submit();
	});
	
	//JS to delete incident
	$("#detele_incident_submit_form").click(function (event) {
		event.preventDefault();
		
		//Show confirm dialog
		var res = confirm("<?php echo __("Are you sure?");?>");
		if (res) {
			$("#delete_incident_form").submit();
		}
	});
	
	//Show hide epilog field
	$("#incident_status").change(function () {
		
		var status = $(this).val();
		
		//Display epilog and closed by field
		if (status == 7) {
			$("#epilog_wrapper").show();
			$("#closed_by_wrapper").show();
		} else {
			$("#epilog_wrapper").hide();
			$("#closed_by_wrapper").hide();
		}
	});
	
	if ($("#id_incident_type").val() != "0") {
		show_incident_type_fields();
	}
			
	var idUser = "<?php echo $config['id_user'] ?>";
	
	bindAutocomplete("#text-id_creator", idUser);
	
	bindAutocomplete("#text-id_user", idUser);
	
	bindAutocomplete("#text-closed_by", idUser);
	
	$("#tgl_incident_control").click(function() {
		 fila = document.getElementById('incident-editor-row_advanced-0');
		  if (fila.style.display != "none") {
			fila.style.display = "none"; //ocultar fila 
		  } else {
			fila.style.display = ""; //mostrar fila 
		  }
	});
	
	$("#priority_form").change (function () {
		var level = this.value;
		var color;
		var name;
		
		switch (level) {
			case "10":
				color = "blue";
				name = "maintenance";
				break;
			case "0":
				color = "gray";
				name = "informative";
				break;
			case "1":
				color = "green";
				name = "low";
				break;
			case "2":
				color = "yellow";
				name = "medium";
				break;
			case "3":
				color = "orange";
				name = "serious";
				break;
			case "4":
				color = "red";
				name = "critical";
				break;
			default:
				color = "blue";
		}
		$(".priority-color").fadeOut('normal', function () {
			$(this).attr ("src", "images/priority_"+name+".png").fadeIn();
		});
	});
		
});


function loadInventory(id_inventory) {
	
	$('#incident_status_form').append ($('<input type="hidden" value="'+id_inventory+'" class="selected-inventories" name="inventories[]" />'));

	$("#inventory_search_modal").dialog('close');

	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/inventories&get_inventory_name=1&id_inventory="+ id_inventory,
		dataType: "text",
		success: function (name) {
			$('#incident_inventories').append($('<option></option>').html(name).attr("value", id_inventory));
		}
	});
}

function removeInventory() {

	s= $("#incident_inventories").prop ("selectedIndex");

	selected_id = $("#incident_inventories").children (":eq("+s+")").attr ("value");
	$("#incident_inventories").children (":eq("+s+")").remove ();
	$(".selected-inventories").each (function () {
		if (this.value == selected_id)
			$(this).remove ();
	});
		
	//idInventory = $('#incident_inventories').val();
	//$("#incident_inventories").find("option[value='" + idInventory + "']").remove();
	
	
	
}


// Form validation
trim_element_on_submit('#text-titulo');
trim_element_on_submit('#text-email_copy');
validate_form("#incident_status_form");
var rules, messages;
// Rules: #text-titulo
rules = {
	required: true,
	remote: {
		url: "ajax.php",
        type: "POST",
        data: {
			page: "include/ajax/remote_validations",
			search_existing_incident: 1,
			incident_name: function() { return $('#text-titulo').val() },
			incident_id: "<?php echo $id_incident?>"
        }
	}
};
messages = {
	required: "<?php echo __('Title required')?>",
	remote: "<?php echo __('This incident already exists')?>"
};
add_validate_form_element_rules('#text-titulo', rules, messages);

// Rules: text-email_copy
rules = { email: true };
messages = { email: "<?php echo __('Invalid email')?>" };
add_validate_form_element_rules('#text-email_copy', rules, messages);

</script>

<?php //endif; ?>
