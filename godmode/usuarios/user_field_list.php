<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2012 Ártica Soluciones Tecnológicas
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

enterprise_include ('godmode/usuarios/configurar_usuarios.php');

if (! give_acl ($config["id_user"], 0, "UM")) {
	audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation","Trying to access user field list");
	require ("general/noaccess.php");
	exit;
}


$delete = get_parameter("delete");

if ($delete) {
	$id = get_parameter("id");

	$sql = sprintf("DELETE FROM tuser_field WHERE id = %d", $id);

	$res = process_sql($sql);

	if ($res) {

		echo "<h3 class='suc'>".__('Field deleted')."</h3>";
	
	} else {
		echo "<h3 class='error'>".__('There was a problem deleting field')."</h3>";
	}
}

echo "<h2>".__("User fields")."</h2>";
echo "<h4>".__("List fields")."</h4>";
		
$user_fields = get_db_all_rows_sql ("SELECT * FROM tuser_field");

if ($user_fields === false) {
	$user_fields = array ();
}

$table = new StdClass();
$table->width = '100%';
$table->class = 'listing';
$table->data = array ();
$table->head = array();
$table->style = array();
$table->size = array ();
$table->size[0] = '30%';
$table->size[1] = '20%';
$table->size[2] = '30%';

$table->head[0] = __("Name field");
$table->head[1] = __("Type");
$table->head[2] = __("Value");
$table->head[4] = __("Action");

$data = array();

if (!empty($user_fields)) {
	foreach ($user_fields as $field) {
		$url_delete = "index.php?sec=users&sec2=godmode/usuarios/user_field_list&delete=1&id=".$field['id'];
		$url_update = "index.php?sec=users&sec2=godmode/usuarios/user_field_editor&id_field=".$field['id'];
		
		if ($field['label'] == '') {
			$data[0] = '';
		} else {
			$data[0] = $field["label"];
		}
		
		if ($field_type = '') {
			$data[1] = '';
		} else {
			$data[1] = $field["type"];
		}
		
		if ($field["type"] == "combo") {
			$data[2] = $field["combo_value"];
		} else {
			$data[2] = "";
		}
				
		$data[4] = "<a
		href='" . $url_update . "'>
		<img src='images/wrench.png' border=0 /></a>";
		$data[4] .= "<a
		onclick=\"if (!confirm('" . __('Are you sure?') . "')) return false;\" href='" . $url_delete . "'>
		<img src='images/cross.png' border=0 /></a>";
		
		array_push ($table->data, $data);
	}
	print_table($table);
} else {
	echo "<h2 class='error'>".__("No fields")."</h4>";
}

echo "<form id='form-add_field' name=dataedit method=post action='index.php?sec=users&sec2=godmode/usuarios/user_field_editor'>";
	echo '<div style="width: '.$table->width.'; text-align: right;">';
		print_submit_button (__('Add field'), 'create_btn', false, 'class="sub create"', false);
	echo '</div>';
echo "</form>";


?>