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

global $config;

check_login();

$get_group_details = (bool) get_parameter ('get_group_details');
$id = (int) get_parameter ('id');

if ($get_group_details) {
	if (! give_acl ($config["id_user"], $id, "IR"))
		return;
	
	$default_user = get_db_value ('id_user_default', 'tgrupo', 'id_grupo', $id);
	$real_name = get_db_value ('nombre_real', 'tusuario', 'id_usuario', $default_user);
	$group = array ();
	$group['forced_email'] = get_db_value ('forced_email', 'tgrupo', 'id_grupo', $id);
	$group['user_real_name'] = $real_name;
	$group['id_user_default'] = $default_user;
	echo json_encode ($group);
	if (defined ('AJAX'))
		return;
}

if (! give_acl ($config["id_user"], 0, "UM")) {
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access group management");
	require ("general/noaccess.php");
	exit;
}

$create_group = (bool) get_parameter ('create_group');
$update_group = (bool) get_parameter ('update_group');
$delete_group = (bool) get_parameter ('delete_group');

// Create group
if ($create_group) {
	$name = (string) get_parameter ('name');
	$icon = (string) get_parameter ('icon');
    $parent = (int) get_parameter ('parent');
	$soft_limit = (int) get_parameter ('soft_limit');
	$hard_limit = (int) get_parameter ('hard_limit');
	$enforce_soft_limit = (bool) get_parameter ('enforce_soft_limit');
	$id_inventory = (int) get_parameter("id_inventory", 0);
	$banner = (string) get_parameter ('banner');
	$forced_email = (bool) get_parameter ('forced_email');
	$id_user_default = (string) get_parameter ('id_user');
	$id_sla = (int) get_parameter ("id_sla");
	$autocreate_user = (int) get_parameter("autocreate_user", 0);
	$grant_access = (int) get_parameter("grant_access", 0);
	$send_welcome = (int) get_parameter("send_welcome", 0);
	$default_company = (int) get_parameter("default_company", 0);
	$welcome_email = (string) get_parameter ('welcome_email', "");
	$email_queue = (string) get_parameter ('email_queue', "");
	$default_profile = (int) get_parameter ('default_profile', 0);
	$user_level = (int) get_parameter ('user_level', 0);
	$simple_mode = (int) get_parameter ('simple_mode', 0);
	$incident_type = (int) get_parameter ('incident_type', 0);	

	$sql = sprintf ('INSERT INTO tgrupo (nombre, icon, forced_email, banner, id_user_default, 
					soft_limit, hard_limit, enforce_soft_limit, id_sla, parent, id_inventory_default,
					autocreate_user, grant_access, send_welcome, default_company, welcome_email, 
					email_queue, default_profile, nivel, simple_mode, id_incident_type) 
					VALUES ("%s", "%s", %d, "%s", "%s", "%s", "%s", %d, %d, "%s", %d, %d, 
					%d, %d, %d, "%s", "%s", %d, %d, %d, %d)', 
						$name, $icon, $forced_email, $banner, $id_user_default, $soft_limit, $hard_limit, 
						$enforce_soft_limit, $id_sla, $parent, $id_inventory, $autocreate_user, $grant_access,
						$send_welcome, $default_company, $welcome_email, $email_queue, $default_profile, 
						$user_level, $simple_mode, $incident_type);
						
	$id = process_sql ($sql, 'insert-id');	
	if ($id === false)
		echo '<h3 class="error">'.__('There was a problem creating group').'</h3>';
	else {
		echo '<h3 class="suc">'.__('Successfully created').'</h3>'; 
	}
	$id = 0;
}

// Update group
if ($update_group) {
	$name = (string) get_parameter ('name');
	$icon = (string) get_parameter ('icon');
	$parent = (int) get_parameter ('parent');
	$banner = (string) get_parameter ('banner');
	$forced_email = (bool) get_parameter ('forced_email');
	$id_user_default = (string) get_parameter ('id_user');
	$soft_limit = (int) get_parameter ('soft_limit');
	$hard_limit = (int) get_parameter ('hard_limit');
	$enforce_soft_limit = (bool) get_parameter ('enforce_soft_limit');
	$id_sla = get_parameter ("id_sla");
	$id_inventory = (int) get_parameter("id_inventory", 0);
	$autocreate_user = (int) get_parameter("autocreate_user", 0);
	$grant_access = (int) get_parameter("grant_access", 0);
	$send_welcome = (int) get_parameter("send_welcome", 0);
	$default_company = (int) get_parameter("default_company", 0);
	$welcome_email = (string) get_parameter ('welcome_email', "");
	$email_queue = (string) get_parameter ('email_queue', "");
	$default_profile = (int) get_parameter ('default_profile', 0);
	$user_level = (int) get_parameter ('user_level', 0);
	$simple_mode = (int) get_parameter ('simple_mode', 0);
	$incident_type = (int) get_parameter ('incident_type', 0);
	
	$sql = sprintf ('UPDATE tgrupo
		SET parent = %d, nombre = "%s", icon = "%s", forced_email = %d, 
		banner = "%s", id_user_default = "%s", soft_limit = %d, hard_limit = %d, 
		enforce_soft_limit = %d, id_sla = %d, id_inventory_default = %d, 
		autocreate_user = %d, grant_access = %d, send_welcome = %d,
		default_company = %d, welcome_email = "%s", email_queue = "%s", 
		default_profile = %d, nivel = %d, simple_mode = %d, id_incident_type = %d
		WHERE id_grupo = %d',
		 $parent, $name, $icon, $forced_email, $banner, $id_user_default, 
		 $soft_limit, $hard_limit, $enforce_soft_limit, $id_sla, $id_inventory, 
		 $autocreate_user, $grant_access, $send_welcome, $default_company, 
		 $welcome_email, $email_queue, $default_profile,$user_level, $simple_mode, 
		 $incident_type, $id);

	$result = process_sql ($sql);

	if ($result === false)
		echo '<h3 class="error">'.__('There was a problem modifying group').'</h3>';
	else
		echo '<h3 class="suc">'.__('Successfully updated').'</h3>';
}

// Delete group
if ($delete_group) {
	$sql = sprintf ('DELETE FROM tgrupo WHERE id_grupo = %d', $id);
	$result = process_sql ($sql);
	if ($result === false) {
		echo '<h3 class="error">'.__('There was a problem deleting group').'</h3>'; 
	} else {
		echo '<h3 class="suc">'.__('Successfully deleted').'</h3>';
	}
}

echo '<h2>'.__('Group management').'</h2>';

$offset = get_parameter ("offset", 0);
$search_text = get_parameter ("search_text", "");

echo "<table class='blank'><form name='bskd' method=post action='index.php?sec=users&sec2=godmode/grupos/lista_grupos'>";
echo "<td>";
echo __('Search text');
echo "<td>";
print_input_text ("search_text", $search_text, '', 15, 0, false);
echo "<td>";
print_submit_button (__('Search'), '', false, 'class="sub next"', false, false);
echo "</table></form>";

$table->width = '90%';
$table->class = 'listing';
$table->head = array ();
$table->head[0] = __('Icon');
$table->head[1] = __('Name');
$table->head[2] = __('Parent');
$table->head[3] = __('Delete');
$table->data = array ();
$table->align = array ();
$table->align[3] = 'center';
$table->style = array ();
$table->style[1] = 'font-weight: bold';
$table->size = array ();
$table->size[3] = '40px';

$groups = get_db_all_rows_sql ("SELECT * FROM tgrupo WHERE nombre LIKE '%$search_text%' ORDER BY nombre");

$groups = print_array_pagination ($groups, "index.php?sec=users&sec2=godmode/grupos/lista_grupos");

if ($groups === false)
	$groups = array ();
    foreach ($groups as $group) {
	$data = array ();
	
	$data[0] = '';
	if ($group['icon'] != '')
		$data[0] = '<img src="images/groups_small/'.$group['icon'].'" />';
		
	if ($group["id_grupo"] != 1) {
		$data[1] = '<a href="index.php?sec=users&sec2=godmode/grupos/configurar_grupo&id='.
			$group['id_grupo'].'">'.$group['nombre'].'</a>';
	} else {
		$data[1] = $group["nombre"];
	}
	$data[2] = dame_nombre_grupo ($group["parent"]);
	
	//Group "all" is special not delete and no update
	if ($group["id_grupo"] != 1) {
		$data[3] = '<a href="index.php?sec=users&
				sec2=godmode/grupos/lista_grupos&
				id_grupo='.$group["id_grupo"].'&
				delete_group=1&id='.$group["id_grupo"].
				'" onClick="if (!confirm(\''.__('Are you sure?').'\')) 
				return false;">
				<img src="images/cross.png"></a>';
	} else {
		$data[3] = "";
	}
	array_push ($table->data, $data);
}
print_table ($table);


echo '<form method="post" action="index.php?sec=users&sec2=godmode/grupos/configurar_grupo">';
echo '<div class="button" style="width: '.$table->width.'">';
print_submit_button (__('Create'), 'create_btn', false, 'class="sub next"');
echo '</div>';
echo '</form>';


?>

<script type="text/javascript" src="include/js/jquery.validation.functions.js"></script>

<script type="text/javascript">
trim_element_on_submit('#text-search_text');
</script>
