<?php

// Integria 2.0 - http://integria.sourceforge.net
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

if (check_login () != 0) {
	audit_db ("Noauth", $config["REMOTE_ADDR"], "No authenticated access","Trying to access incident viewer");
	require ("general/noaccess.php");
	exit;
}

$id_profile = (int) get_parameter ('user_profile_search');
$id_group = (int) get_parameter ('user_group_search');
$search_string = (string) get_parameter ('search_string');
$search_id_contract = (int) get_parameter ('search_id_contract');
$search_id_product = (int) get_parameter ('search_id_product');
$search_id_building = (int) get_parameter ('search_id_building');
$search_ip_address = (string) get_parameter ('search_ip_address');
$search_serial_number = (string) get_parameter ('search_serial_number');
$search_part_number = (string) get_parameter ('search_part_number');
$search = (bool) get_parameter ('search');
$search_id_inventory = (string) get_parameter ('search_id_inventory');
$search_id_company = (int) get_parameter ('search_id_company');

if ($search) {
	$sql_clause = '';
	if ($search_id_contract)
		$sql_clause .= sprintf (' AND id_contract = %d', $search_id_contract);
	if ($search_id_product)
		$sql_clause .= sprintf (' AND id_product = %d', $search_id_product);
	if ($search_id_building)
		$sql_clause .= sprintf (' AND id_building = %d', $search_id_building);
	if ($search_ip_address != '')
		$sql_clause .= sprintf (' AND ip_address LIKE "%%%s%%"', $search_ip_address);
	if ($search_serial_number != '')
		$sql_clause .= sprintf (' AND serial_number LIKE "%%%s%%"', $search_serial_number);
	if ($search_part_number != '')
		$sql_clause .= sprintf (' AND part_number LIKE "%%%s%%"', $search_part_number);
	if ($search_id_inventory != '')
		$sql_clause .= sprintf (' AND id = "%s"', $search_id_inventory);

	$sql = sprintf ('SELECT id, name, description, comments, id_building
			FROM tinventory
			WHERE (name LIKE "%%%s%%" OR description LIKE "%%%s%%")
			%s',
			$search_string, $search_string,
			$sql_clause);
	$inventories = get_db_all_rows_sql ($sql);
	if ($inventories === false) {
		$inventories = array ();
	}
	
	$total_inventories = 0;
	foreach ($inventories as $inventory) {
		if ($search_id_company) {
			$companies = get_inventory_affected_companies ($inventory['id']);
			$found = false;
			foreach ($companies as $company) {
				if ($company['id'] == $search_id_company)
					$found = true;
			}
			if (! $found)
				continue;
		}
	
		echo '<tr id="result-'.$inventory['id'].'">';
		echo '<td><strong>#'.$inventory['id'].'</strong></td>';
		echo '<td>'.$inventory['name'].'</td>';

		$incidents = get_incidents_on_inventory ($inventory['id'], false);
		$total_incidents = sizeof ($incidents);
		
		echo '<td>';
		if ($total_incidents) {
			$actived = 0;
			foreach ($incidents as $incident) {
				if ($incident['estado'] != 7 && $incident['estado'] != 6)
					$actived++;
			}
			echo '<img src="images/info.png" /> <strong>'.$actived.'</strong> / '.$total_incidents;
		}
		echo '</td>';
		
		$companies = get_inventory_affected_companies ($inventory['id'], false);
		echo '<td>';
		if (isset ($companies[0]['name']))
			echo $companies[0]['name'];
		echo '</td>';
		
		$building = get_building ($inventory['id_building']);
		echo '<td>';
		if ($building)
			echo $building['name'];
		echo '</td>';
		
		echo '<td>'.$inventory['description'].'</td>';
		echo '</tr>';
		$total_inventories++;
	}
	
	if ($total_inventories == 0) {
		echo '<tr colspan="4">'.__('No inventory found').'</tr>';
	}
	
	if (defined ('AJAX'))
		return;
}

$table->data = array ();
$table->width = '95%';
$table->style = array ();
$table->style[0] = 'font-weight: bold';
$table->colspan = array ();
//$table->colspan[3][0] = 2;

$table->data[1][0] = print_select (get_products (),
					'search_id_product', $search_id_product,
					'', __('All'), 0, true, false, false,
					__('Product type'));

$table->data[1][1] = print_select (get_contracts (),
			'search_id_contract', $search_id_contract,
			'', __('All'), 0, true, false, false,
			__('Contract'));

$table->data[1][2] = print_select (get_buildings (),
			'search_id_building', $search_id_building,
			'', __('All'), 0, true, false, false,
			__('Building'));

$table->data[2][1] = print_select (get_companies (),
			'search_id_company', $search_id_company,
			'', __('All'), 0, true, false, false,
			__('Company'));

$table->data[3][0] = print_input_text ('search_ip_address', $search_ip_address, '', 20, 255,
			true, __('IP address'));
$table->data[3][1] = print_input_text ('search_serial_number', $search_serial_number, '', 20, 255,
			true, __('Serial number'));
$table->data[3][2] = print_input_text ('search_part_number', $search_part_number, '', 20, 255,
			true, __('Part number'));

$table->data[4][0] = print_input_text ('search_string', $search_string, '', 20, 255,
			true, __('Search string'));

$table->data[4][1] = print_input_text ('search_id_inventory', $search_id_inventory, '', 5, 55,
			true, __('Inventory ID#'));

$table->data[4][2] = print_submit_button (__('Search'), 'search_button',
			false, 'class="sub search"', true);

echo '<div id="inventory_search_result"></div>';

echo '<form id="inventory_search_form" method="post">';
print_table ($table);
print_input_hidden ('search', 1);
echo '</form>';

unset ($table);
$table->class = 'hide result_table listing';
$table->width = '95%';
$table->id = 'inventory_search_result_table';
$table->head = array ();
$table->head[1] = __('ID');
$table->head[2] = __('Name');
$table->head[3] = __('Active Incidents');
$table->head[4] = __('Company');
$table->head[5] = __('Building');
$table->head[6] = __('Title');

print_table ($table);

echo '<div id="inventory-pager" class="hide pager">';
echo '<form>';
echo '<img src="images/control_start_blue.png" class="first" />';
echo '<img src="images/control_rewind_blue.png" class="prev" />';
echo '<input type="text" class="pagedisplay" />';
echo '<img src="images/control_fastforward_blue.png" class="next" />';
echo '<img src="images/control_end_blue.png" class="last" />';
if (defined ('AJAX')) {
	echo '<select class="pagesize" style="display: none">';
	echo '<option selected="selected" value="5">5</option>';
} else {
	echo '<select class="pagesize">';
	echo '<option selected="selected" value="10">10</option>';
	echo '<option value="20">20</option>';
	echo '<option value="30">30</option>';
	echo '<option  value="40">40</option>';
	echo '<option  value="100">100</option>';
	echo '</select>';
}
echo '</select>';
echo '</form>';
echo '</div>';

?>
