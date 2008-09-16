<?php

// Integria 1.1 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2008 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// Load global vars

global $config;

if (check_login() != 0) {
 	audit_db("Noauth",$config["REMOTE_ADDR"], "No authenticated access","Trying to access event viewer");
	require ("general/noaccess.php");
	exit;
}

$id_grupo = (int) get_parameter ('id_grupo');

if (give_acl ($config['id_user'], $id_grupo, "IR") != 1){
 	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to incident ".$id_inc." '".$titulo."'");
	include ("general/noaccess.php");
	exit;
}

$id_grupo = 0;
$texto = "";
$create_incident = true;
$result_msg = "";

$id = get_parameter ('id');
$action = get_parameter ('action');

if ($action == 'get-info') {
	$incident = get_db_row ('tincidencia', 'id_incidencia', $id);

	$incident['hours'] = (int) give_hours_incident ($id);

	echo json_encode ($incident);
	if (defined ('AJAX'))
		return;
}

if ($action == 'update') {
	$id_inc = get_parameter ('id_inc');
 	$grupo = get_parameter ('grupo_form');
	$usuario = get_parameter ('usuario_form');

	// Only admins (manage incident) or owners can modify incidents
	if (! give_acl ($config["id_user"], $grupo, "IM")) {
		audit_db ($config['id_user'], $config["REMOTE_ADDR"],"ACL Forbidden","User ".$_SESSION["id_usuario"]." try to update incident");
		echo "<h3 class='error'>".lang_string ('upd_incid_no')."</h3>";
		no_permission ();
		exit ();
	}
	$id_author_inc = give_incident_author ($id_inc);
	$titulo = get_parameter ('titulo');
	$descripcion = get_parameter ('descripcion');
	$origen = get_parameter ("incident_origin", 1);
	$prioridad = get_parameter ('prioridad_form');
	$estado = get_parameter ('incident_status');
	$group = get_parameter ('grupo_form');
	$email_notify = (bool) get_parameter ("email_notify");
	$epilog = get_parameter ('epilog');
	$descripcion = get_parameter ('descripcion');
	$resolution = get_parameter ('incident_resolution');
	$id_task = get_parameter ('task_user');

	incident_tracking ( $id_inc, $config["id_user"], 1);
	$old_prio = give_inc_priority ($id_inc);
	// 0 - Abierta / Sin notas (Open without notes)
	// 2 - Descartada (Not valid)
	// 3 - Caducada (out of date)
	// 13 - Cerrada (closed)
	if ($old_prio != $prioridad)
		incident_tracking ($id_inc, $config['id_user'], 8);
	if ($estado == 2)
		incident_tracking ($id_inc, $config['id_user'], 4);
	if ($estado == 3)
		incident_tracking ($id_inc, $config['id_user'], 5);
	if ($estado == 13)
		incident_tracking ($id_inc, $config['id_user'], 10);

	$sql = sprintf ('UPDATE tincidencia SET actualizacion = NOW(),
			titulo = "%s", origen = %d, estado = %d,
			id_grupo = %d, id_usuario = "%s",
			notify_email = %d, prioridad = %d, descripcion = "%s",
			epilog = "%s", id_task = %d, resolution = %d
			WHERE id_incidencia = %d',
			$titulo, $origen, $estado, $grupo, $usuario,
			$email_notify, $prioridad, $descripcion,
			$epilog, $id_task, $resolution, $id_inc);
	$result = process_sql ($sql, 'insert_id');
	audit_db ($id_author_inc, $config["REMOTE_ADDR"], "Incident updated", "User ".$config['id_user']." incident updated #".$id_inc);

	/* Update inventory objects in incident */
	$sql = sprintf ('DELETE FROM tincident_inventory WHERE id_incident = %d', $id_inc);
	process_sql ($sql);
	$inventories = get_parameter ('inventories');
	foreach ($inventories as $id_inventory) {
		$sql = sprintf ('INSERT INTO tincident_inventory
				VALUES (%d, %d)',
				$id_inc, $id_inventory);
		process_sql ($sql);
	}

	if ($result === false)
		$result_msg = "<h3 class='suc'>".lang_string ('upd_incid_no')."</h3>";
	else
		$result_msg = "<h3 class='suc'>".lang_string ('upd_incid_ok')."</h3>";
	$_GET["id"] = $id_inc; // HACK

	// Email notify to all people involved in this incident
	if ($email_notify == 1) {
		mail_incident ($id_inc, $usuario, "", 0, 0);
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
	$descripcion =  get_parameter ('descripcion');
	$texto = $descripcion; // to view in textarea after insert
	$origen = get_parameter ('incident_origin', 1);
	$prioridad = get_parameter ('prioridad_form');
	$id_creator = $config['id_user'];
	$estado = get_parameter ("incident_status");
	$resolution = get_parameter ("incident_resolution");
	$id_task = get_parameter ("task_user");
	$email_notify = (bool) get_parameter ('email_notify');

	$sql = sprintf ('INSERT INTO tincidencia
			(inicio, actualizacion, titulo, descripcion,
			id_usuario, origen, estado, prioridad,
			id_grupo, id_creator, notify_email, id_task,
			resolution)
			VALUES (NOW(), NOW(), "%s", "%s", "%s", %d, %d, %d, %d,
			"%s", %d, %d, %d)',
			$titulo, $descripcion, $usuario,
			$origen, $estado, $prioridad, $grupo, $id_creator,
			$email_notify, $id_task, $resolution);
	$id_inc = process_sql ($sql, 'insert_id');
	if ($id_inc !== false) {
		$inventories = (array) get_parameter ('inventories');

		foreach ($inventories as $id_inventory) {
			$sql = sprintf ('INSERT INTO tincident_inventory
					VALUES (%d, %d)',
					$id_inc, $id_inventory);
			process_sql ($sql);
		}
		$_GET["id"] = $id_inc; // HACK
		$result_msg  = "<h3 class='suc'>".lang_string ('create_incid_ok')." (id #$id_inc)</h3>";
		audit_db ($config["id_user"], $config["REMOTE_ADDR"],
			"Incident created",
			"User ".$config['id_user']." created incident #".$id_inc);
		incident_tracking ( $id_inc, $config["id_user"], 0);

		// Email notify to all people involved in this incident
		if ($email_notify) {
			mail_incident ($id_inc, $usuario, "", 0, 1);
		}
	} else {
		$result_msg  = '<h3 class="err">'.lang_string ('create_incid_no').'</h3>';
	}
	if (defined ('AJAX')) {
		echo $result_msg;
		return;
	}
}

// Edit / Visualization MODE - Get data from database
if ($id) {
	$create_incident = false;
	$id_inc = $_GET["id"];
	$iduser_temp=$_SESSION['id_usuario'];
	// Obtain group of this incident
	$sql = sprintf ('SELECT * FROM tincidencia
			WHERE id_incidencia = %d', $id_inc);
	$result = mysql_query ($sql);
	$row = mysql_fetch_array ($result);
	// Get values
	$titulo = $row["titulo"];
	$texto = $row["descripcion"];
	$inicio = $row["inicio"];
	$actualizacion = $row["actualizacion"];
	$estado = $row["estado"];
	$prioridad = $row["prioridad"];
	$origen = $row["origen"];
	$usuario = $row["id_usuario"];
	$nombre_real = dame_nombre_real($usuario);
	$id_grupo = $row["id_grupo"];
	$id_creator = $row["id_creator"];
	$email_notify=$row["notify_email"];
	$resolution = $row["resolution"];
	$epilog = $row["epilog"];
	$id_task = $row["id_task"];
	$id_incident_linked = $row["id_incident_linked"];
	$grupo = dame_nombre_grupo($id_grupo);

	// Aditional ACL check on read incident
	if (give_acl ($config["id_user"], $id_grupo, "IR") == 0) { // Only admins
		audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Forbidden","User ".$config["id_user"]." try to access to an unauthorized incident ID #id_inc");
		no_permission();
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Workunit ADD
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	if (isset($_GET["insert_workunit"])){
		$id_inc = get_parameter ("id_inc");
		$timestamp = get_parameter ("timestamp");
		$nota = get_parameter ("nota");
		$workunit = get_parameter ("workunit",0);
		$timeused = get_parameter ("duration",0);
		$timeused = number_format($timeused, 2);
		$have_cost = get_parameter ("have_cost",0);
		$profile = get_parameter ("work_profile",0);

		$sql4 = "UPDATE tincidencia SET actualizacion = '".$timestamp."' WHERE id_incidencia = ".$id_inc;
		$res4 = mysql_query($sql4);

		incident_tracking ( $id_inc, $config['id_user'], 2);

		// Add work unit if enabled
		$sql = "INSERT INTO tworkunit (timestamp, duration, id_user, description) VALUES ('$timestamp', '$timeused', '".$config['id_user']."', '$nota')";
		$res5 = mysql_query($sql);
		$id_workunit = mysql_insert_id();
		$sql1 = "INSERT INTO tworkunit_incident (id_incident, id_workunit) VALUES ($id_inc, $id_workunit)";
		$res6 = mysql_query($sql1);
		if ($res6) {
			$result_msg = "<h3 class='suc'>".lang_string ('create_work_ok')."</h3>";
			// Email notify to all people involved in this incident
			if ($email_notify == 1){
				mail_incident ($id_inc, $config['id_user'], $nota, $timeused, 10);
			}
		}
	}

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Upload file
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	if ((give_acl ($iduser_temp, $id_grupo, "IW")==1) AND isset($_GET["upload_file"])) {
		if ( $_FILES['userfile']['name'] != "" ){ //if file
			$tipo = $_FILES['userfile']['type'];
			if (isset($_POST["file_description"]))
				$description = $_POST["file_description"];
			else
				$description = "No description available";
			// Insert into database
			$filename= $_FILES['userfile']['name'];
			$filesize = $_FILES['userfile']['size'];

			$sql = " INSERT INTO tattachment (id_incidencia, id_usuario, filename, description, size ) VALUES (".$id_inc.", '".$iduser_temp." ','".$filename."','".$description."',".$filesize.") ";

			mysql_query ($sql);
			$id_attachment=mysql_insert_id();
			incident_tracking ( $id_inc, $config['id_user'], 3);
			$result_msg="<h3 class='suc'>".lang_string ('file_added')."</h3>";
			// Email notify to all people involved in this incident
			if ($email_notify == 1){
				mail_incident ($id_inc, $iduser_temp, 0, 0, 2);
			}
			// Copy file to directory and change name
			$nombre_archivo = $config["homedir"]."attachment/pand".$id_attachment."_".$filename;

			if (!(copy($_FILES['userfile']['tmp_name'], $nombre_archivo ))){
					$result_msg = "<h3 class=error>".lang_string ('attach_error')."</h3>";
				$sql = " DELETE FROM tattachment WHERE id_attachment =".$id_attachment;
				mysql_query($sql);
			} else {
				// Delete temporal file
				unlink ($_FILES['userfile']['tmp_name']);
			}
		}
	}
} else {
	$iduser_temp = $config['id_user'];
	$titulo = "";
	$titulo = "";
	$descripcion = "";
	$origen = 0;
	$prioridad = 2;
	$id_grupo =0;
	$grupo = dame_nombre_grupo (1);

	$usuario= $config["id_user"];
	$estado = 1;
	$resolution = 9;
	$id_task = 0;
	$epilog = "";
	$id_creator = $iduser_temp;
	$email_notify = 0;
}
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Show the form
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

$default_responsable = "";
if (! isset ($id_inc)) {
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

if (isset ($id_inc)) {
	echo "<h1>".lang_string ('incident')." #$id_inc</h1>";
} else {
	echo "<h2>".lang_string ('create_incident')."</h2>";
}

echo '<div id="result">'.$result_msg.'</div>';

$table->width = "90%";
$table->class = "databox_color";
$table->size = array ();
$table->size[0] = '20%';
$table->size[1] = '40%';
$table->size[2] = '20%';
$table->size[3] = '20%';
$table->style = array ();
$table->style[0] = 'font-weight: bold';
$table->style[2] = 'font-weight: bold';
$table->data = array ();
$table->cellspacing = 2;
$table->cellpadding = 2;
$table->colspan = array ();
$table->colspan[0][2] = 2;
$table->colspan[5][1] = 3;
$table->colspan[6][0] = 4;
$table->colspan[7][0] = 4;
$table->colspan[8][0] = 4;

$table->data[0][0] = lang_string ('incident');
$table->data[0][1] = print_input_text ('titulo', $titulo, '', 40, 100, true);

if ($has_permission)
	$disabled = false;
else
	$disabled = true;

$table->data[0][2] = print_checkbox_extended ('email_notify', 1, $email_notify,
						$disabled, '', '', true);
$table->data[0][2] .= lang_string ('email_notify');
$table->data[0][2] .= print_help_tip (lang_string ('email_notify_help'), true);

$table->data[1][0] = lang_string ('Priority');
if ($disabled) {
	$table->data[1][1] = $prioridad;
} else {
	$table->data[1][1] = print_select (get_indicent_priorities (),
					'prioridad_form', $prioridad, '', '',
					'', true, false, false);
}

$table->data[1][2] = lang_string ('Status');

$actual_only = false;
$disabled = false;
if (! $has_permission)
	$disabled = true;

if ($has_permission)
	$actual_only = true;

$table->data[1][3] = combo_incident_status ($estado, $disabled, $actual_only, true);

$table->data[2][0] = lang_string ('assigned_user');
if ($has_permission) {
	$disabled = false;
	if ($default_responsable != "") {
		$disabled = true;
	}
	$table->data[2][1] = print_button (dame_nombre_real ($usuario), 'usuario_name',
					$disabled, '', '', true);
	$table->data[2][1] .= print_input_hidden ('usuario_form', $usuario, true);
	$table->data[2][1] .= print_help_tip (lang_string ('incident_user_help'), true);
} else {
	$table->data[2][1] = print_input_hidden ('usuario_form', $usuario, true);
	$table->data[2][1] .= $usuario;
}

$table->data[2][2] = lang_string ('Creator');
$table->data[2][3] = $id_creator." (<em>".dame_nombre_real ($id_creator)."</em>)";

$table->data[3][0] = lang_string ('Source');
$table->data[3][1] = combo_incident_origin ($origen, $disabled, true);

$table->data[3][2] = lang_string ('group');
if ($has_permission) {
	$table->data[3][3] = combo_groups_visible_for_me ($iduser_temp, "grupo_form", 0, "IW", $id_grupo, true);
} else {
	$table->data[3][3] = dame_nombre_grupo ($id_grupo);
}

$table->data[4][0] = lang_string ('resolution');
$table->data[4][1] = combo_incident_resolution ($resolution, $disabled, true);
$table->data[4][2] = lang_string ('task');
$table->data[4][3] = combo_task_user ($id_task, $config["id_user"], 0, $disabled, true);

$table->data[5][0] = lang_string ('Affected inventory');

if ($create_incident) {
	$table->data[5][1] = print_select (array (), 'incident_inventories', NULL,
					'', '', '', true, 5);
	$table->data[5][1] .= print_button (lang_string ("Add inventory object"),
					'search_inventory', false, '', '', true);
	$table->data[5][1] .= print_button (lang_string ("Remove selected object"),
					'delete_inventory', false, '', '', true);
} else {
	$inventories = get_inventories_in_incident ($id_inc);
	$table->data[5][1] = print_select ($inventories, 'incident_inventories',
						NULL, '', '', '',
						true, 5, false);

	if ($has_permission) {
		$table->data[5][1] .= print_button (lang_string ("Add inventory object"),
					'search_inventory', false, '', '', true);
		$table->data[5][1] .= print_button (lang_string ("Remove inventory object"),
					'delete_inventory', false, '', '', true);
		$inventories = (array) get_db_all_rows_sql ($sql);
		foreach ($inventories as $inventory_id => $inventory_name) {
			$table->data[5][1] .= print_input_hidden ("inventories[]",
								$inventory_id, true, 'selected-inventories');
		}
	}
}
$disabled_str = $disabled ? 'readonly' : '';
$table->data[6][0] = lang_string ('Description').'<br />';
$table->data[6][0] .= print_textarea ('descripcion', 15, 80, $texto, $disabled_str, true);

if ($estado == 5) {
	$table->data[7][0] = lang_string ('resolution_epilog');
	$table->data[8][0] = print_textarea ('epilog', 15, 100, $epilog, $disabled_str, true);
}

echo "<form id='incident_status_form' method='POST' action='index.php?sec=incidents&sec2=operation/incidents/incident_detail'>";

print_table ($table);

echo '<div class="action-buttons" style="width: '.$table->width.'">';
if ($create_incident) {
	print_input_hidden ('action', 'insert');
	if (give_acl ($config["id_user"], 0, "IW")) {
		print_submit_button (lang_string ('create'), 'accion', false, 'class="sub create"');
	}
} else {
	print_input_hidden ('id', $id_inc);
	print_input_hidden ('action', 'update');
	if ($has_permission) {
		print_submit_button (lang_string ('update'), 'accion', false, 'class="sub next"');
	}
}

if (isset ($id_inc)) {
	print_input_hidden ('id_inc', $id_inc);
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
