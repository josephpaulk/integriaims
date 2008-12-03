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

enterprise_include ('operation/user/user.php');

$id_user =$_SESSION["id_usuario"];

echo "<h2>".__('Integria users')."</h2>";

$table->width = '90%';
$table->class = 'listing';
$table->data = array ();
$table->head = array ();
$table->head[0] = __('User ID');
$table->head[1] = __('Last contact');
$table->head[2] = __('Profile');
$table->head[3] = __('Name');
$table->head[4] = __('Description');


$users = get_user_visible_users (0, "IR", false);

$users = print_array_pagination ($users, "index.php?sec=users&sec2=operation/users/user");

foreach ($users as $user) {
	$data = array ();
	
	$data[0] = '<a href="index.php?sec=users&sec2=operation/users/user_edit&id='
		.$user['id_usuario'].'"><strong>'.$user['id_usuario'].'</strong></a>';
	$data[1] = human_time_comparation ($user['fecha_registro']);
	$data[2] = print_user_avatar ($user['id_usuario'], true, true);
	$profiles = enterprise_hook ('get_user_profiles', array ($user['id_usuario']));
	if ($profiles !== ENTERPRISE_NOT_HOOK) {
		$data[2] .= $profiles;
	}
	
	$data[3] = $user['nombre_real'];
	$data[4] = substr($user['comentarios'],0,45);
	
	array_push ($table->data, $data);
}
echo "<div>";
print_table ($table);
echo "</div>";

enterprise_hook ('print_profiles_table');
?>
