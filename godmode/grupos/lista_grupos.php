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

	$banner = (string) get_parameter ('banner');
	$forced_email = (bool) get_parameter ('forced_email');
	$id_user_default = (string) get_parameter ('id_user_default');
	$id_inventory_default = (int) get_parameter ("id_inventory_default");

	$sql = sprintf ('INSERT INTO tgrupo (nombre, icon, forced_email, banner, id_user_default, soft_limit, hard_limit, enforce_soft_limit, id_inventory_default, parent) VALUES ("%s", "%s", %d, "%s", "%s", "%s", "%s", %d, %d, "%s")', $name, $icon, $forced_email, $banner, $id_user_default, $soft_limit, $hard_limit, $enforce_soft_limit, $id_inventory_default, $parent);
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
	$id_user_default = (string) get_parameter ('id_user_default');
	$soft_limit = (int) get_parameter ('soft_limit');
	$hard_limit = (int) get_parameter ('hard_limit');
	$enforce_soft_limit = (bool) get_parameter ('enforce_soft_limit');
	$id_inventory_default = get_parameter ("id_inventory_default");

	$sql = sprintf ('UPDATE tgrupo
		SET parent = %d, nombre = "%s", icon = "%s", forced_email = %d, 
		banner = "%s", id_user_default = "%s", soft_limit = %d, hard_limit = %d, enforce_soft_limit = %d, id_inventory_default = %d WHERE id_grupo = %d', $parent, $name, $icon, $forced_email, $banner, $id_user_default, $soft_limit, $hard_limit, $enforce_soft_limit, $id_inventory_default, $id);

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

$groups = get_db_all_rows_in_table ('tgrupo', 'nombre');

$groups = print_array_pagination ($groups, "index.php?sec=users&sec2=operation/users/user");

if ($groups === false)
	$groups = array ();
    foreach ($groups as $group) {

	$data = array ();
	
	$data[0] = '';
	if ($group['icon'] != '')
		$data[0] = '<img src="images/groups_small/'.$group['icon'].'" />';
	$data[1] = '<a href="index.php?sec=users&sec2=godmode/grupos/configurar_grupo&id='.
		$group['id_grupo'].'">'.$group['nombre'].'</a>';
	$data[2] = dame_nombre_grupo ($group["parent"]);
	$data[3] = '<a href="index.php?sec=users&
			sec2=godmode/grupos/lista_grupos&
			id_grupo='.$group["id_grupo"].'&
			delete_group=1&id='.$group["id_grupo"].
			'" onClick="if (!confirm(\''.__('Are you sure?').'\')) 
			return false;">
			<img src="images/cross.png"></a>';
	array_push ($table->data, $data);
}
print_table ($table);


echo '<form method="post" action="index.php?sec=users&sec2=godmode/grupos/configurar_grupo">';
echo '<div class="button" style="width: '.$table->width.'">';
print_submit_button (__('Create'), 'create_btn', false, 'class="sub next"');
echo '</div>';
echo '</form>';


?>
