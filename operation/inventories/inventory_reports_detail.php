<?php

// Integria IMS - http://integriaims.com
// ==================================================
// Copyright (c) 2008 Artica Soluciones Tecnologicas
// Copyright (c) 2008 Esteban Sanchez, estebans@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

$export = false;
if (! isset ($config)) {
	$dir = realpath (dirname (__FILE__).'/../..');
	$path = get_include_path ();
	set_include_path ($path.PATH_SEPARATOR.$dir);
	session_start ();
	require_once ('include/config.php');
	$export = (bool) get_parameter ('generate_report');
	if (! $export)
		return;
	$config['id_user'] = isset ($_SESSION['id_usuario']) ? $_SESSION['id_usuario'] : '';
}

check_login ();

require_once ('include/functions_inventories.php');

if (! dame_admin ($config['id_user'])) {
	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access inventory reports");
	include ("general/noaccess.php");
	return;
}

$id = (int) get_parameter ('id');

if ($export) {
	$report = get_db_row ('tinventory_reports', 'id', $id);
	if ($report === false)
		return;
	
	$filename = $report['name'].'-'.date ("YmdHi");
	// We'll be outputting a PDF
	header ('Content-Disposition: attachment; filename="'.$filename.'.csv"');
	$config['mysql_result_type'] = MYSQL_ASSOC;
	$rows = get_db_all_rows_sql (clean_output ($report['sql']));
	if ($rows === false)
		return;
	echo implode (',', array_keys ($rows[0]))."\n";
	foreach ($rows as $row) {
		echo implode (',', $row)."\n";
	}
	
	return;
}

$create = (bool) get_parameter ('create_report');
$update = (bool) get_parameter ('update_report');
$delete = (bool) get_parameter ('delete_report');

$name = (string) get_parameter ('name');
$sql = (string) get_parameter ('sql');

$result_msg = '';
if ($create) {
	$values['name'] = $name;
	$values['sql'] = $sql;
	
	$result = false;
	if (! empty ($values['name']))
		$result = process_sql_insert ('tinventory_reports', $values);
	
	if ($result) {
		$result_msg = '<h3 class="suc">'.__('Successfully created').'</h3>';
		$id = $result;
	} else {
		$result_msg = '<h3 class="error">'.__('Could not be created').'</h3>';
		$id = false;
	}
}

if ($update) {
	$values['name'] = $name;
	$values['sql'] = $sql;
	
	$result = false;
	if (! empty ($values['name']))
		$result = process_sql_update ('tinventory_reports', $values, array ('id' => $id));
	if ($result) {
		$result_msg = '<h3 class="suc">'.__('Successfully updated').'</h3>';
	} else {
		$result_msg = '<h3 class="error">'.__('Could not be updated').'</h3>';
	}
}

if ($id) {
	$report = get_db_row ('tinventory_reports', 'id', $id);
	if ($report === false)
		return;
	$name = $report['name'];
	$sql = $report['sql'];
}

echo "<h2>".__('Inventory reports')."</h2>";

echo $result_msg;

$table->width = '90%';
$table->data = array ();

$table->data[0][0] = print_input_text ('name', $name, '', 40, 255, true, __('Name'));

$table->data[1][0] = print_textarea ('sql', 10, 100, $sql, '', true, __('Report SQL sentence'));

echo '<form method="post">';
print_table ($table);
echo '<div style="width:'.$table->width.'" class="action-buttons button">';
if ($id) {
	print_input_hidden ('update_report', 1);
	print_input_hidden ('id', $id);
	print_submit_button (__('Update'), 'update', false, 'class="sub upd"');
} else {
	print_input_hidden ('create_report', 1);
	print_submit_button (__('Create'), 'create', false, 'class="sub next"');
}
echo '</div>';
echo '</form>';
?>
