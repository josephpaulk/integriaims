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

require_once ('include/functions_incidents.php');
require_once ('include/functions_workunits.php');


global $config;

$search_users = (bool) get_parameter ('search_users');
$search_users_role = (bool) get_parameter ('search_users_role');

if ($search_users) {
	require_once ('include/functions_db.php');
	
	$id_user = $config['id_user'];
	$string = (string) get_parameter ('q'); /* q is what autocomplete plugin gives */
	$users = get_user_visible_users ($config['id_user'],"IR", false);
	
	if ($users === false)
		return;
	
	foreach ($users as $user) {
		if(preg_match('/'.$string.'/i', $user['id_usuario']) || preg_match('/'.$string.'/i', $user['nombre_real'])|| preg_match('/'.$string.'/i', $user['num_employee'])) {
			echo $user['id_usuario'] . "|" . $user['nombre_real']  . "\n";
		}
	}
	
	return;
}

if ($search_users_role) {
	require_once ('include/functions_db.php');
	
	$id_project = (int) get_parameter ('id_project');
	$id_user = $config['id_user'];
	$string = (string) get_parameter ('q'); /* q is what autocomplete plugin gives */
	
	$users = get_users_project ($id_project);
	
	if ($users === false)
		return;

	foreach ($users as $user) {
		if(preg_match('/'.$string.'/i', $user['id_user'])) {
			echo $user['id_user'] . "/" . get_db_value ("name","trole","id",$user["id_role"]). "\n";
		}
	}
		
	return;
}

?>
 	
