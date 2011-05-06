<?php

// Integria IMS - http://integriaims.com
// ==================================================
// Copyright (c) 2008-2011 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

check_login ();

require_once ('include/functions_inventories.php');

if (! dame_admin ($config['id_user'])) {
	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access inventory reports");
	include ("general/noaccess.php");
	return;
}

$reports = get_db_all_rows_in_table ('tinventory_reports');
if ($reports === false) {
	echo '<h2 class="error">'.__('No reports were found').'</h2>';
	return;
}

echo '<h2>'.__('Inventory reports').'</h2>';

$delete = (bool) get_parameter ('delete_report');

if ($delete) {
	$id = (int) get_parameter ('id');
	
	$result = process_sql_delete ('tinventory_reports', array ('id' => $id));
	
	if ($result !== false) {
		echo '<h3 class="suc">'.__('Successfully deleted').'</h3>';
	} else {
		echo '<h3 class="error">'.__('Could not be deleted').'</h3>';
	}
}

$table->width = '90%';
$table->class = 'listing';
$table->data = array ();
$table->head = array ();
$table->head[0] = __('Name');
$table->head[1] = __('SQL sentence');
$table->head[2] = '';
$table->head[3] = '';
$table->size = array ();
$table->size[0] = '30%';
$table->size[1] = '70%';
$table->size[2] = '40px';
$table->size[3] = '40px';

foreach ($reports as $report) {
	$data = array ();
	
	$data[0] = '<a href="index.php?sec=inventory&sec2=operation/inventories/inventory_reports_detail&id='.$report['id'].'">';
	$data[0] .= $report['name'];
	$data[0] .= '</a>';
	$data[1] = substr ($report['sql'], 0, 40);
	$data[3] = "<a href='index.php?sec=inventory&sec2=operation/inventories/inventory_reports_detail&render=1&raw_output=1&clean_output=1&id=".$report['id']."'><img src='images/datos.gif'></a>";
	$data[4] = '<form method="post" onsubmit="return confirm (\''.__('Are your sure?').'\')">';
	$data[4] .= print_input_hidden ('delete_report', 1, true);
	$data[4] .= print_input_hidden ('id', $report['id'], true);
	$data[4] .= print_input_image ('delete', 'images/cross.png', 1, '', true);
	$data[4] .= '</form>';
	
	array_push ($table->data, $data);
}

print_table ($table);
?>
