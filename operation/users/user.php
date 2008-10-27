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

if (check_login() != 0) {
	audit_db("Noauth",$config["REMOTE_ADDR"], "No authenticated acces","Trying to access incident viewer");
	require ("general/noaccess.php");
	exit;
}

$id_user =$_SESSION["id_usuario"];

echo "<h2>".__('Integria users')."</h2>";

$table->width = '90%';
$table->class = 'listing';
$table->data = array ();
$table->head = array ();
$table->head[0] = __('UserID');
$table->head[1] = __('Last contact');
$table->head[2] = __('Profile');
$table->head[3] = __('Name');
$table->head[4] = __('Description');

$users = get_user_visible_users (0, "IR", false);
foreach ($users as $user) {
	$data = array ();
	
	$data[0] = '<a href="index.php?sec=users&sec2=operation/users/user_edit&id='
		.$user['id_usuario'].'"><strong>'.$user['id_usuario'].'</strong></a>';
	$data[1] = $user['fecha_registro'];
	$data[2] = print_user_avatar ($user['id_usuario'], true, true);
	
	$profiles = get_db_all_rows_field_filter ('tusuario_perfil', 'id_usuario', $user['id_usuario']);
	$data[2] .= "<a href='#' class='tip'>&nbsp;<span>";
	if ($profiles !== false) {
		foreach ($profiles as $profile) {
			$data[2] .= dame_perfil ($profile["id_perfil"])."/ ";
			$data[2] .= dame_grupo ($profile["id_grupo"])."<br />";
		}
	} else {
		$data[2] .= __('This user doesn\'t have any assigned profile/group');
	}
	$data[2] .= "</span></a>";
	$data[3] = $user['nombre_real'];
	$data[4] = $user['comentarios'];
	
	array_push ($table->data, $data);
}

print_table ($table);

echo "</table>";

enterprise_include ("operation/user/user_defined_profiles.php");
?>
