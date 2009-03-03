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

check_login ();

if (! give_acl ($config['id_user'], 0, "VR")) {
	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access inventory search");
	include ("general/noaccess.php");
	exit;
}

$create_custom_search = (bool) get_parameter ('create_custom_search');
$get_custom_search_values = (bool) get_parameter ('get_custom_search_values');
$delete_custom_search = (bool) get_parameter ('delete_custom_search');

/* Create a custom saved search via AJAX */
if ($create_custom_search) {
	$form_values = get_parameter ('form_values');
	$search_name = (string) get_parameter ('search_name');
	
	$result = create_custom_search ($search_name, 'inventories', $form_values);
	
	if ($result === false) {
		echo '<h3 class="error">'.__('Could not create custom search').'</h3>';
	} else {
		echo '<h3 class="suc">'.__('Custom search saved').'</h3>';
	}
	
	if (defined ('AJAX')) {
		return;
	}
}

/* Get a custom search via AJAX */
if ($get_custom_search_values) {
	$id_search = (int) get_parameter ('id_search');
	$search = get_custom_search ($id_search, 'inventories');
	if ($search === false) {
		echo json_encode (false);
		return;
	}
	echo json_encode (unserialize ($search['form_values']));
	return;
}

/* Delete a custom saved search via AJAX */
if ($delete_custom_search) {
	$id_search = (int) get_parameter ('id_search');
	$sql = sprintf ('DELETE FROM tcustom_search
		WHERE id_user = "%s"
		AND id = %d',
		$config['id_user'], $id_search);
	$result = process_sql ($sql);
	if ($result === false) {
		echo '<h3 class="error">'.__('Could not delete custom search').'</h3>';
	} else {
		echo '<h3 class="suc">'.__('Custom search deleted').'</h3>';
	}
	
	if (defined ('AJAX')) {
		return;
	}
}

require_once ('include/functions_inventories.php');

$search = (bool) get_parameter ('search');
$show_stats = (bool) get_parameter ('show_stats');

if ($search) {
	$filter = array ();
	$filter['id_group'] = (int) get_parameter ('user_group_search');
	$filter['string'] = (string) get_parameter ('search_string');
	$filter['id_contract'] = (int) get_parameter ('search_id_contract');
	$filter['id_product'] = (int) get_parameter ('search_id_product');
	$filter['id_building'] = (int) get_parameter ('search_id_building');
	$filter['ip_address'] = (string) get_parameter ('search_ip_address');
	$filter['serial_number'] = (string) get_parameter ('search_serial_number');
	$filter['part_number'] = (string) get_parameter ('search_part_number');
	$filter['id_inventory'] = (string) get_parameter ('search_id_inventory');
	$filter['id_company'] = (int) get_parameter ('search_id_company');
	
	$inventories = filter_inventories ($filter);
	if ($inventories === false) {
		$inventories = array ();
	}
	
	/* Show HTML if show_stats flag is active on HTML request */
	if ($show_stats) {
		/* Add a button to generate HTML reports */
		echo '<form method="post" target="_blank" action="index.php" style="clear: both">';
		foreach ($_POST as $key => $value) {
			print_input_hidden ($key, $value);
		}
		echo '<div style="width:90%; text-align: right;">';
		print_input_hidden ('sec2', 'operation/reporting/inventories_html');
		print_input_hidden ('clean_output', 1);
		print_submit_button (__('HTML report'), 'inventory_report', false,
			'class="sub report"');
		echo '</div></form>';
	
		return;
	}
	
	// Build object tree
	$tree = array ();
	$tree_root = array ();
	foreach ($inventories as $inventory) {
		$id = $inventory['id'];
		$id_parent = $inventory['id_parent'];

		if (! isset ($tree[$id])) {
			$tree[$id] = array ();
		}

		if ($id_parent > 0) {
			if (! isset ($tree[$id_parent])) {
				$tree[$id_parent] = array ();
			}
			
			array_push ($tree[$id_parent], $id);
		} else {
			array_push ($tree_root, $id);
		}
	}

	$short_table = (bool) get_parameter ('short_table');
	$total_inventories = 0;
	foreach ($tree_root as $object) {
		print_inventory_object ($object, $inventories, $tree, true, !$short_table);
		$total_inventories++;
	}
	
	if ($total_inventories == 0) {
		echo '<tr><td colspan="6">'.__('No inventory objects found').'</td></tr>';
	}
	
	if (defined ('AJAX'))
		return;
}

$table->data = array ();
$table->width = '100%';
$table->cellspacing = 2;
$table->cellpadding = 2;
$table->colspan = array ();
$table->rowstyle = array ();
$table->rowstyle[1] = 'display: none';
$table->rowstyle[2] = 'display: none';
$table->rowstyle[3] = 'text-align: right';
$table->colspan = array ();
$table->colspan[3][0] = 3;

$table->data[0][0] = print_input_text ('search_string', '', '', 20, 255,
			true, __('Search string'));

$table->data[0][1] = print_select (get_products (),
					'search_id_product', 0,
					'', __('All'), 0, true, false, false,
					__('Product type'));
					
if (!get_external_user($config["id_user"]))
	$table->data[0][2] = print_select (get_companies (),
			'search_id_company', 0,
			'', __('All'), 0, true, false, false,
			__('Company'));

$table->data[1][0] = print_select (get_buildings (),
			'search_id_building', '',
			'', __('All'), 0, true, false, false,
			__('Building'));
$table->data[1][1] = print_input_text ('search_serial_number', '', '', 20, 255,
			true, __('Serial number'));


$table->data[1][2] = print_select (get_contracts (),
			'search_id_contract', 0,
			'', __('All'), 0, true, false, false,
			__('Contract'));
$table->data[2][0] = print_input_text ('search_part_number', '', '', 20, 255,
			true, __('Part number'));

$table->data[2][1] = print_input_text ('search_ip_address', '', '', 20, 255,
			true, __('IP address'));

$table->data[3][0] = print_submit_button (__('Search'), 'search_button',
			false, 'class="sub search"', true);

echo '<div id="inventory_search_result"></div>';

echo '<form id="inventory_search_form" method="post">';
print_table ($table);
print_input_hidden ('search', 1);
echo '</form>';
echo '<a class="show_advanced_search" href="index.php">'.__('Advanced search').' >></a>';

unset ($table);

echo '<div id="loading" style="display:none">'.__('Loading');
echo '... <img src="images/wait.gif" /></div>';

$table->class = 'hide result_table listing';
$table->width = '100%';
$table->id = 'inventory_search_result_table';
$table->head = array ();
$table->head[1] = __('ID');
$table->head[2] = __('Name');
if (! defined ('AJAX')) {
	$table->head[3] = __('Active Incidents');
}
$table->head[4] = __('Company');
$table->head[5] = __('Building');
$table->head[6] = __('Title');

print_table ($table);

print_table_pager ('inventory-pager');

?>
