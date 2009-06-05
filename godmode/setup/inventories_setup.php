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

check_login ();

if (! dame_admin ($config["id_user"])) {
	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access inventory setup");
	include ("general/noaccess.php");
	return;
}

require_once ('include/functions_inventories.php');

$update = (bool) get_parameter ('update');

if ($update) {
	$config_names = array ('inventory_label_1' => 'generic_1',
		'inventory_label_2' => 'generic_2',
		'inventory_label_3' => 'generic_3',
		'inventory_label_4' => 'generic_4',
		'inventory_label_5' => 'generic_5',
		'inventory_label_6' => 'generic_6',
		'inventory_label_7' => 'generic_7',
		'inventory_label_8' => 'generic_8');
	
	foreach ($config_names as $token => $param) {
		$value = (string) get_parameter ($param);
		if (! isset ($config[$token]))
			process_sql_insert ('tconfig',
				array ('token' => $token, 'value' => $value));
		else
			process_sql_update ('tconfig',
				array ('value' => $value),
				array ('token' => $token));
		$config[$token] = $value;
	}
}
$config['debug'] = 1;
debug ($config);
$labels = get_inventory_generic_labels ();
debug ($labels);
echo '<h3>'.__('Inventories extra fields').'</h3>';

$table->width = '30%';
$table->class = 'databox';
$table->data = array ();

foreach ($labels as $name => $value) {
	$data = array ();
	
	$data[0] = print_input_text ($name, $value, '', 35, 255, true);
	
	array_push ($table->data, $data);
}

echo '<form method="post">';
print_table ($table);

echo '<div style="width: '.$table->width.'" class="button">';
print_input_hidden ('update', 1);
print_submit_button (__('Update'), 'upd_button', false, 'class="sub upd"');
echo '</div>';
echo '</form>';
?>
