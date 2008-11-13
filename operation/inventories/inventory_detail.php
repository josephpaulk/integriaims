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

check_login ();

require_once ('include/functions_inventories.php');

$result_msg = '';

$id = (int) get_parameter ('id');
$update = (bool) get_parameter ('update_inventory');
$create = (bool) get_parameter ('create_inventory');
$name = (string) get_parameter ('name');
$description = (string) get_parameter ('description');
$cost = (float) get_parameter ('cost');
$id_product = (int) get_parameter ('id_product');
$id_grupo = (int) get_parameter ('id_grupo');
$id_contract = (int) get_parameter ('id_contract');
$ip_address = (string) get_parameter ('ip_address');
$id_parent = (int) get_parameter ('id_parent');
$id_building = (int) get_parameter ('id_building');
$serial_number = (string) get_parameter ('serial_number');
$part_number = (string) get_parameter ('part_number');
$confirmed = (bool) get_parameter ('confirmed');
$id_sla = (int) get_parameter ('id_sla');
$id_manufacturer = (int) get_parameter ('id_manufacturer');

if ($update) {
	if (! give_acl ($config['id_user'], get_inventory_group ($id), "VW")) {
		// Doesn't have access to this page
		audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to update inventory #".$id);
		include ("general/noaccess.php");
		exit;
	}
	
	$sql = sprintf ('UPDATE tinventory SET name = "%s", description = "%s",
			id_product = %d, id_contract = %d, ip_address = "%s",
			id_parent = %d, id_building = %d, serial_number = "%s",
			part_number = "%s", id_manufacturer = %d, id_sla = %d,
			cost = %f
			WHERE id = %d',
			$name, $description, $id_product, $id_contract, $ip_address,
			$id_parent, $id_building, $serial_number, $part_number,
			$id_manufacturer, $id_sla, $cost, $id);
	$result = process_sql ($sql);
	
	/* Update contacts in inventory */
	update_inventory_contacts ($id, get_parameter ('contacts'));
	
	if ($result !== false) {
		$result_msg = '<h3 class="suc">'.__('Inventory object updated successfuly').'</h3>';
	} else {
		$result_msg = '<h3 class="err">'.__('There was an error updating inventory object').'</h3>';
	}
	
	if (defined ('AJAX')) {
		echo $result_msg;
		return;
	}
}

if ($create) {
	if (! give_acl ($config['id_user'], 0, "IM")) {
		// Doesn't have access to this page
		audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create inventory object");
		include ("general/noaccess.php");
		exit;
	}
	
	$sql = sprintf ('INSERT INTO tinventory (name, description, id_product,
			id_contract, ip_address, id_parent, id_building, serial_number,
			part_number, id_manufacturer, id_sla, cost)
			VALUES ("%s", "%s", %d, %d, "%s", %d, %d, "%s", "%s", %d, %d, %f)',
			$name, $description, $id_product, $id_contract, $ip_address,
			$id_parent, $id_building, $serial_number, $part_number,
			$id_manufacturer, $id_sla, $cost);
	$id = process_sql ($sql, 'insert_id');
	if ($id !== false) {
		$result_msg = '<h3 class="suc">'.__('Inventory object created successfuly').'</h3>';
		/* Update contacts in inventory */
		update_inventory_contacts ($id, get_parameter ('contacts'));
	} else {
		$result_msg = '<h3 class="err">'.__('There was an error creating inventory object').'</h3>';
	}
	
	if (defined ('AJAX')) {
		echo $result_msg;
		return;
	}
	$id = 0;
	$name = "";
	$description = "";
	$id_product = "";
	$id_contract = "";
	$ip_address = "";
	$id_parent = "";
	$id_building = "";
	$serial_number = "";
	$part_number = "";
	$confirmed = false;
	$id_sla = 0;
	$id_manufacturer = 0;
}

/* This is the default permission checking to create an inventory */
$has_permission = give_acl ($config['id_user'], 0, "VW");

if ($id) {
	$group = get_inventory_group ($id);
	if (! give_acl ($config['id_user'], $group, "VR")) {
		// Doesn't have access to this page
		audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access inventory #".$id);
		include ("general/noaccess.php");
		exit;
	}
	
	/* If editing, the permission checks is now specific for this object */
	$has_permission = give_acl ($config['id_user'], $group, "VW");
	
	$inventory = get_db_row ('tinventory', 'id', $id);
	$name = $inventory['name'];
	$description = $inventory['description'];
	$id_product = $inventory['id_product'];
	$id_contract = $inventory['id_contract'];
	$ip_address = $inventory['ip_address'];
	$id_parent = $inventory['id_parent'];
	$id_building = $inventory['id_building'];
	$serial_number = $inventory['serial_number'];
	$part_number = $inventory['part_number'];
	$confirmed = false;
	$id_sla = $inventory['id_sla'];
	$id_manufacturer = $inventory['id_manufacturer'];
	$cost = $inventory['cost'];
}

if (! $id) {
	if (! defined ('AJAX'))
		echo "<h2>".__('Create inventory object')."</h2>";
}

$table->class = 'databox';
$table->width = '90%';
$table->data = array ();
$table->colspan = array ();
$table->colspan[4][0] = 3;
$table->colspan[5][0] = 3;

/* First row */
if ($has_permission) {
	$table->data[0][0] = print_input_text ('name', $name, '', 40, 128, true,
		__('Name'));
} else {
	$table->data[0][0] = print_label (__('Name'), '', '', true, $name);
}
$table->data[0][1] = print_checkbox_extended ('confirmed', 1, $confirmed,
	! $has_permission, '', '', true, __('Confirmed'));

$products = get_products ();
if ($has_permission) {
	$table->data[0][2] = print_select ($products, 'id_product', $id_product,
		'', __('None'), 0, true, false, false,
		__('Product type'));
} else {
	$product = isset ($products[$id_product]) ? $products[$id_product] : __('Not set');
	$table->data[0][2] = print_label (__('Name'), '', '', true, $product);
}
$table->data[0][2] .= print_product_icon ($id_product, true);

/* Second row */
$contracts = get_contracts ();
$slas = get_slas ();
$manufacturers = get_manufacturers ();
if ($has_permission) {
	$table->data[1][0] = print_select ($contracts, 'id_contract', $id_contract,
		'', __('None'), 0, true, false, false, __('Contract'));
	$table->data[1][1] = print_select ($slas, 'id_sla', $id_sla,
		'', __('None'), 0, true, false, false, __('SLA'));
	$table->data[1][2] = print_select ($manufacturers, 'id_manufacturer',
		$id_manufacturer, '', __('None'), 0, true, false, false, __('Manufacturer'));
} else {
	$contract = isset ($contracts[$id_contract]) ? $contracts[$id_contract] : __('Not set');
	$sla = isset ($slas[$id_sla]) ? $slas[$id_sla] : __('Not set');
	$manufacturer = isset ($manufacturers[$id_manufacturer]) ? $manufacturers[$id_manufacturer] : __('Not set');
	$table->data[1][0] = print_label (__('Contract'), '', '', true, $contract);
	$table->data[1][1] = print_label (__('SLA'), '', '', true, $sla);
	$table->data[1][2] = print_label (__('Manufacturer'), '', '', true, $manufacturer);
}

/* Third row */
$buildings = get_buildings ();
if ($has_permission) {
	$parent_name = $id_parent ? get_inventory_name ($id_parent) : __('Search parent');
	$table->data[2][0] = print_button ($parent_name,
				'parent_search', false, '', 'class="dialogbtn"',
				true, __('Parent object'));
	if ($id_parent)
		$table->data[2][0] .= '<a href="index.php?sec=inventory&sec2=operation/inventories/inventory&id='.$id_parent.'"><img src="images/go.png" /></a>';
	
	$table->data[2][0] .= print_input_hidden ('id_parent', $id_parent, true);
	$table->data[2][1] = print_select ($buildings, 'id_building', $id_building,
		'', __('None'), 0, true, false, false, __('Building'));
	$table->data[2][2] = print_input_text ('cost', $cost, '', 5, 15,
				true, __('Cost'));
} else {
	$parent_name = $id_parent ? get_inventory_name ($id_parent) : __('Not set');
	$building = isset ($buildings[$id_building]) ? $buildings[$id_building] : __('Not set');
	
	$table->data[2][0] = print_label (__('Parent object'), '', '', true, $parent_name);
	if ($id_parent)
		$table->data[2][0] .= '<a href="index.php?sec=inventory&sec2=operation/inventories/inventory&id='.$id_parent.'"><img src="images/go.png" /></a>';
	$table->data[2][1] = print_label (__('Building'), '', '', true, $building);
	$table->data[2][2] = print_label (__('Cost'), '', '', true, $cost.' '.$config['currency']);
}

/* Fourth row */
if ($has_permission) {
	$table->data[3][0] = print_input_text ('serial_number', $serial_number, '',
		20, 250, true, __('Serial number'));
	$table->data[3][1] = print_input_text ('part_number', $part_number, '',
		20, 250, true, __('Part number'));
	$table->data[3][2] = print_input_text ('ip_address', $ip_address, '',
		15, 60, true, __('IP address'));
} else {
	$serial_number = ($serial_number != '') ? $serial_number : __('Not set');
	$part_number = ($part_number != '') ? $part_number : __('Not set');
	$ip_address = ($ip_address != '') ? $ip_address : __('Not set');
	
	$table->data[3][0] = print_label (__('Serial number'), '', '', true, $serial_number);
	$table->data[3][1] = print_label (__('Part number'), '', '', true, $part_number);
	$table->data[3][2] = print_label (__('IP address'), '', '', true, $ip_address);
}

/* Fifth row */
if ($id) {
	$contacts = get_inventory_contacts ($id, true);
} else {
	$contacts = array ();
}
$table->data[4][0] = print_select ($contacts, 'inventory_contacts', NULL,
	'', '', '', true, false, false, __('Contacts'));
$table->data[4][0] .= print_button (__('Add'),
	'search_contact', false, '', 'class="dialogbtn"', true);
$table->data[4][0] .= print_button (__('Remove'),
	'delete_contact', false, '', 'class="dialogbtn"', true);

foreach ($contacts as $contact_id => $contact_name) {
	$table->data[4][0] .= print_input_hidden ("contacts[]",
						$contact_id, true, 'selected-contacts');
}

/* Sixth row */
$disabled_str = ! $has_permission ? 'readonly="1"' : '';
$table->data[5][0] = print_textarea ('description', 15, 100, $description,
	$disabled_str, true, __('Description'));

echo '<div class="result">'.$result_msg.'</div>';

if ($has_permission) {	
	/* Delete object */
	echo '<form name="delete_object_form" method="post" action="index.php?sec=inventory&sec2=operation/inventories/inventory">';
	print_input_hidden ('quick_delete', $id, false);
	echo '<input type="image" class="cross" src="images/cross.png" title="' . __('Delete') .'">';
	echo '</form>';

	echo '<form method="post" id="inventory_status_form">';
	print_table ($table);


	echo '<div style="width:'.$table->width.'" class="action-buttons button">';
	if ($id) {
		print_input_hidden ('update_inventory', 1);
		print_input_hidden ('id', $id);
		print_submit_button (__('Update'), 'update', false, 'class="sub upd"');
	} else {
		print_input_hidden ('create_inventory', 1);
		print_submit_button (__('Create'), 'create', false, 'class="sub next"');
	}
	echo '</div>';
	echo '</form>';
} else {
	print_table ($table);
}
if (! defined ('AJAX')):
?>

<script type="text/javascript" src="include/js/jquery.metadata.js"></script>
<script type="text/javascript" src="include/js/jquery.tablesorter.js"></script>
<script type="text/javascript" src="include/js/jquery.tablesorter.pager.js"></script>
<script type="text/javascript" src="include/js/integria_incident_search.js"></script>

<script type="text/javascript">
$(document).ready (function () {
	configure_inventory_form (false);
});

</script>
<?php endif; ?>
