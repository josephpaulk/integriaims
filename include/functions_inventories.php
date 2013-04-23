<?php 

// INTEGRIA IMS v2.0
// http://www.integriaims.com
// ===========================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2008 Esteban Sanchez, estebans@artica.es
// Copyright (c) 2007-2008 Artica, info@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License
// (LGPL) as published by the Free Software Foundation; version 2

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.


function get_inventories ($only_names = true, $exclude_id = false) {
	if ($exclude_id) {
		$sql = sprintf ('SELECT * FROM tinventory WHERE id != %d', $exclude_id);
		$inventories = get_db_all_rows_sql ($sql);
	} else {
		$inventories = get_db_all_rows_in_table ('tinventory');
	}
	if ($inventories == false)
		return array ();
	
	if ($only_names) {
		$retval = array ();
		foreach ($inventories as $inventory) {
			$retval[$inventory['id']] = $inventory['name'];
		}
		return $retval;
	}
	
	return $inventories;
}

function get_inventory ($id_inventory) {
	return get_db_row ('tinventory', 'id', $id_inventory);
}

function get_inventory_name ($id) {
	return (string) get_db_value ('name', 'tinventory', 'id', $id);
}

function get_inventories_in_incident ($id_incident, $only_names = true) {
	$sql = sprintf ('SELECT tinventory.* FROM tincidencia, tincident_inventory, tinventory
			WHERE tincidencia.id_incidencia = tincident_inventory.id_incident
			AND tinventory.id = tincident_inventory.id_inventory
			AND tincidencia.id_incidencia = %d', $id_incident);
	$all_inventories = get_db_all_rows_sql ($sql);
	if ($all_inventories == false)
		return array ();
	
	global $config;
	$inventories = array ();
	foreach ($all_inventories as $inventory) {
		if (! give_acl ($config['id_user'], get_inventory_group ($inventory['id']), 'VR')) {
			$inventory['name'] = $inventory['name'];
		}
		array_push ($inventories, $inventory);
	}
	
	if ($only_names) {
		$result = array ();
		foreach ($inventories as $inventory) {
			$result[$inventory['id']] = $inventory['name'];
		}
		return $result;
	}
	return $inventories;
}

function get_inventories_in_company ($id_company, $only_names = true) {
	$sql = sprintf ('SELECT tinventory.* FROM tcontract, tinventory
			WHERE tinventory.id_contract = tcontract.id
			AND tcontract.id_company = %d', $id_company);
	$all_inventories = get_db_all_rows_sql ($sql);
	if ($all_inventories == false)
		return array ();
	
	global $config;
	$inventories = array ();
	foreach ($all_inventories as $inventory) {
		if (! give_acl ($config['id_user'], get_inventory_group ($inventory['id']), 'VR')) {
			$inventory['name'] = $inventory['name'];
		}
		array_push ($inventories, $inventory);
	}
	
	if ($only_names) {
		$result = array ();
		foreach ($inventories as $inventory) {
			$result[$inventory['id']] = $inventory['name'];
		}
		return $result;
	}
	return $inventories;
}

function get_inventory_contracts ($id_inventory, $only_names = true) {
	$sql = sprintf ('SELECT tcontract.* FROM tinventory, tcontract
			WHERE tinventory.id_contract = tcontract.id
			AND tinventory.id = %d', $id_inventory);
	$contracts = get_db_all_rows_sql ($sql);
	if ($contracts == false)
		return array ();
	
	if ($only_names) {
		$result = array ();
		foreach ($contracts as $contract) {
			$result[$contract['id']] = $contract['name'];
		}
		return $result;
	}
	return $contracts;
}

function get_inventory_group ($id_inventory, $only_id = true) {
	$sql = sprintf ('SELECT tgrupo.%s FROM tinventory, tcontract, tgrupo
			WHERE tinventory.id_contract = tcontract.id
			AND tcontract.id_group = tgrupo.id_grupo
			AND tinventory.id = %d',
			($only_id ? "id_grupo" : "*"),
			$id_inventory);
	if ($only_id)
		return (int) get_db_sql ($sql);
	return get_db_row_sql ($sql);
}

function get_inventory_affected_companies ($id_inventory, $only_names = true) {
	$sql = sprintf ('SELECT tcompany.* FROM tinventory, tcontract, tcompany
			WHERE tinventory.id_contract = tcontract.id
			AND tcontract.id_company = tcompany.id
			AND tinventory.id = %d', $id_inventory);
	$companies = get_db_all_rows_sql ($sql);
	if ($companies == false)
		return array ();
	
	if ($only_names) {
		$result = array ();
		foreach ($companies as $company) {
			$result[$company['id']] = $company['name'];
		}
		return $result;
	}
	return $companies;
}

function get_incident ($id_incident) {
	return get_db_row ('tincidencia', 'id_incidencia', $id_incident);
}

function get_incident_slas ($id_incident, $only_names = true) {
	$sql = sprintf ('SELECT tsla.*
		FROM tinventory, tsla, tincident_inventory
		WHERE tinventory.id_sla = tsla.id
		AND tincident_inventory.id_inventory = tinventory.id
		AND tincident_inventory.id_incident = %d', $id_incident);
	$slas = get_db_all_rows_sql ($sql);
	if ($slas == false)
		return array ();
	
	if ($only_names) {
		$result = array ();
		foreach ($slas as $sla) {
			$result[$sla['id']] = $sla['name'];
		}
		return $result;
	}
	return $slas;
}


function get_company ($id_company) {
	return get_db_row ('tcompany', 'id', $id_company);
}

function get_companies ($only_names = true, $filter = false) {
	global $config;
	
	$companies = get_db_all_rows_filter ('tcompany', $filter);
	if ($companies === false)
		return array ();

	$names = array ();
	foreach ($companies as $k => $company) {
		if (!give_acl ($config["id_user"], $company['id_grupo'], "VR") && !get_admin_user ($config["id_user"])) {
			continue;
		}
		$names[$company['id']] = $company['name'];
	}

	asort ($names);
	
	if($only_names) {
		return $names;
	}

	$retval = array();
	$company_keys = array_keys($names);
	foreach($companies as $company) {
		if(in_array($company['id'],$company_keys)) {
			$retval[] = $company;
		}
	}

	return $retval;
}

function get_company_roles ($only_names = true) {
	$companies = get_db_all_rows_in_table ('tcompany_role');
	if ($companies === false)
		return array ();
	
	if ($only_names) {
		$retval = array ();
		foreach ($companies as $company) {
			$retval[$company['id']] = $company['name'];
		}
		return $retval;
	}
	
	return $companies;
}


function get_contract ($id_contract) {
	return get_db_row ('tcontract', 'id', $id_contract);
}

function get_contracts ($only_names = true, $filter = false) {
	global $config;

	$contracts = get_db_all_rows_filter ('tcontract', $filter);
	if ($contracts === false)
		return array ();

	$names = array ();
	foreach ($contracts as $k => $contract) {
		if (!give_acl ($config["id_user"], $contract['id_group'], "VR") && !get_admin_user ($config["id_user"])) {
			continue;
		}
		$names[$contract['id']] = $contract['name'];
	}

	asort ($names);
	
	if($only_names) {
		return $names;
	}

	$retval = array();
	$contract_keys = array_keys($names);
	foreach($contracts as $contract) {
		if(in_array($contract['id'],$contract_keys)) {
			$retval[] = $contract;
		}
	}

	return $retval;
}

function get_products ($only_names = true) {
	$products = get_db_all_rows_in_table ('tkb_product');
	if ($products === false)
		return array ();
	
	if ($only_names) {
		$retval = array ();
		foreach ($products as $product) {
			$retval[$product['id']] = $product['name'];
		}
		return $retval;
	}
	
	return $products;
}

function get_company_contacts ($id_company, $only_names = true) {
	$sql = sprintf ('SELECT * FROM tcompany_contact
			WHERE id_company = %d', $id_company);
	$contacts = get_db_all_rows_sql ($sql);
	if ($contacts == false)
		return array ();
	
	if ($only_names) {
		$result = array ();
		foreach ($contacts as $contact) {
			$result[$contact['id']] = $contact['name'];
		}
		return $result;
	}
	return $contacts;
}

/**
 * Get all the contacts relative to an inventory object.
 *
 * There are two ways to get the list. By default, all the contacts in the
 * company that has the inventory contract will be returned. Anyway, if the
 * contacts list was changed manually when updating or creating the
 * inventory object, then these are the contacts of the object.
 *
 * @param int Inventory id.
 * @param bool Whether to return only contact names (default) or all the fields.
 *
 * @return array List of contacts relative to an inventory object.
 */
function get_inventory_contacts ($id_inventory, $only_names = false) {
	global $config;
	
	/* First try to get only defined contacts */
	$sql = sprintf ('SELECT tcompany_contact.*
		FROM tcompany_contact, tinventory_contact
		WHERE tcompany_contact.id = tinventory_contact.id_company_contact
		AND tinventory_contact.id_inventory = %d',
		$id_inventory);
	$contacts = get_db_all_rows_sql ($sql);
	if ($contacts !== false) {
		if (! $only_names)
			return $contacts;
		
		$retval = array ();
		foreach ($contacts as $contact) {
			$retval[$contact['id']] = $contact['fullname'];
		}
		return $retval;
	}
	
	$contracts = get_inventory_contracts ($id_inventory, false);
	if ($contracts === false)
		return array ();
	
	$all_contacts = array ();
	
	foreach ($contracts as $contract) {
		$company = get_company ($contract['id_company']);
		if ($company === false)
			continue;
		if (! give_acl ($config['id_user'], $contract['id_group'], "IR"))
			continue;
		
		$contacts = get_company_contacts ($company['id'], false);
		foreach ($contacts as $contact) {
			if (isset ($all_contacts[$contact['id']]))
				continue;
			
			$all_contacts[$contact['id']] = $contact;
		}
	}
	
	if (! $only_names)
		return $all_contacts;
	
	$retval = array ();
	foreach ($all_contacts as $contact) {
		$retval[$contact['id']] = $contact['fullname'];
	}
	return $retval;
}

/**
 * Update contacts in an inventory object.
 *
 * @param int Inventory id to update.
 * @param array List of company contacts ids.
 */
function update_inventory_contacts ($id_inventory, $contacts) {
	error_reporting (0);
	$where_clause = '';
	
	if (empty ($contacts)) {
		$contacts = array (0);
	}
	$where_clause = sprintf ('AND id_company_contact NOT IN (%s)',
		implode (',', $contacts));
	$sql = sprintf ('DELETE FROM tinventory_contact
		WHERE id_inventory = %d %s',
		$id_inventory, $where_clause);
	process_sql ($sql);
	foreach ($contacts as $id_contact) {
		$sql = sprintf ('INSERT INTO tinventory_contact
				VALUES (%d, %d)',
				$id_inventory, $id_contact);
		process_sql ($sql);
	}
}

/**
 * Filter all the inventories and return a list of matching elements.
 *
 * This function only return the inventories that can be accessed for the
 * current user with VR permission.
 *
 * @param array Key-value array of parameters to filter. It can handle this fields:
 *
 * string String to find in inventory title
 * serial_number Inventory serial number.
 * part_number Inventory part number.
 * ip_address Inventory IP address.
 * id_group Inventory group id.
 * id_contract Inventory contract id.
 * id_product Inventory product id.
 * id_building Inventory building id.
 * id_company Inventory company id (relative to the contract).
 *
 * @return array A list of matching inventories. False if no matches.
 */
function filter_inventories ($filters) {
	global $config;
	
	/* Set default values if none is set */
	$filters['string'] = isset ($filters['string']) ? $filters['string'] : '';
	$filters['serial_number'] = isset ($filters['serial_number']) ? $filters['serial_number'] : '';
	$filters['part_number'] = isset ($filters['part_number']) ? $filters['part_number'] : '';
	$filters['ip_address'] = isset ($filters['ip_address']) ? $filters['ip_address'] : '';
	$filters['id_group'] = isset ($filters['id_group']) ? $filters['id_group'] : 0;
	$filters['id_contract'] = isset ($filters['id_contract']) ? $filters['id_contract'] : 0;
	$filters['id_product'] = isset ($filters['id_product']) ? $filters['id_product'] : 0;
	$filters['id_building'] = isset ($filters['id_building']) ? $filters['id_building'] : 0;
	$filters['id_company'] = isset ($filters['id_company']) ? $filters['id_company'] : 0;
	
	$sql_clause = '';
	if ($filters['id_contract'])
		$sql_clause .= sprintf (' AND id_contract = %d', $filters['id_contract']);
	if ($filters['id_product'])
		$sql_clause .= sprintf (' AND id_product = %d', $filters['id_product']);
	if ($filters['id_building'])
		$sql_clause .= sprintf (' AND id_building = %d', $filters['id_building']);
	if ($filters['ip_address'] != '')
		$sql_clause .= sprintf (' AND ip_address LIKE "%%%s%%"', $filters['ip_address']);
	if ($filters['serial_number'] != '')
		$sql_clause .= sprintf (' AND serial_number LIKE "%%%s%%"', $filters['serial_number']);
	if ($filters['part_number'] != '')
		$sql_clause .= sprintf (' AND part_number LIKE "%%%s%%"', $filters['part_number']);
	
	$sql = sprintf ('SELECT id, name, description, comments, id_building, id_contract, id_parent
			FROM tinventory
			WHERE (name LIKE "%%%s%%" OR description LIKE "%%%s%%")
			%s LIMIT %d',
			$filters['string'], $filters['string'],
			$sql_clause, $config['limit_size']);
	$all_inventories = get_db_all_rows_sql ($sql);
	if ($all_inventories === false)
		return false;
	
	$short_table = (bool) get_parameter ('short_table');
	$total_inventories = 0;
	$inventories = array ();
	foreach ($all_inventories as $inventory) {
		if ($inventory['id_contract']) {
			/* Only check ACLs if the inventory has a contract */
			if (! give_acl ($config['id_user'], get_inventory_group ($inventory['id']), "VR"))
				continue;
		}
		
		if ($filters['id_company']) {
			$companies = get_inventory_affected_companies ($inventory['id'], false);
			$found = false;
			foreach ($companies as $company) {
				if ($company['id'] == $filters['id_company'])
					$found = true;
			}
			if (! $found)
				continue;
		}
		$inventories[$inventory['id']] = $inventory;
	}
	
	if (sizeof ($inventories) == 0)
		return false;
	return $inventories;
}

/**
 * Prints the details of an inventory object and, optionally, its children. 
 *
 * @param int ID of the object.
 * @param array Array containing inventory objects.
 * @param array Inventory object tree.
 * @param bool Show child nodes.
 * @param bool Show incident statistics.
 * @param int Call depth, used for indentation.
 * @param bool Whether to return an output string or echo now (optional, echo by default).
 */
function print_inventory_object ($id, $inventory, $tree, $show_children = false, $show_incidents = false, $depth = 0, $return = false) {
	global $config;
	
	$output = '';
	
	if (! isset ($inventory[$id])) {
		return '';
	}
	
	$object = $inventory[$id];
	
	if ($object['id_contract']) {
		/* Only check ACLs if the inventory has a contract */
		if (! give_acl ($config['id_user'], get_inventory_group ($object['id']), "VR"))
			return '';
	}
	
	$output .= '<tr id="result-'.$object['id'].'">';
	$output .= '<td><strong>#'.$object['id'].'</strong></td>';
	$output .= '<td>';
	if ($depth > 0) {
		$output .= '<span class="indent">';
		for ($i = 0; $i < $depth; $i++) {
			$output .= '&nbsp;&nbsp;&nbsp;&nbsp;';
		}
		$output .= '</span>';
		$output .= '<img src="images/copy.png" />';
	}
	$output .= $object['name'] . '</td>';
	
	if ($show_incidents) {
		$incidents = get_incidents_on_inventory ($object['id'], false);
		$total_incidents = sizeof ($incidents);
		$output .= '<td>';
		if ($total_incidents) {
			$actived = 0;
			foreach ($incidents as $incident) {
				if ($incident['estado'] != 7 && $incident['estado'] != 6)
					$actived++;
			}
			$output .= '<img src="images/info.png" /> <strong>'.$actived.'</strong> / '.$total_incidents;
		}
		$output .= '</td>';
	}
	$companies = get_inventory_affected_companies ($object['id'], false);
	$output .= '<td>';
	if (isset ($companies[0]['name']))
		$output .= $companies[0]['name'];
	$output .= '</td>';
	
	$building = get_building ($object['id_building']);
	$output .= '<td>';
	if ($building)
		$output .= $building['name'];
	$output .= '</td>';
	
	$output .= '<td>'.$object['description'].'</td>';
	$output .= '</tr>';
	
	// Print child objects
	if (! $show_children || ! isset ($tree[$object['id']])) {
		if ($return)
			return $output;
		echo $output;
		return;
	}

	foreach ($tree[$object['id']] as $child) {
		$output .= print_inventory_object ($child, $inventory, $tree,
			$show_children, $show_incidents, $depth + 1, true);
	}
	
	if ($return)
		return $output;
	echo $output;
}

/**
 * Get the children of the given inventory object.
 *
 * @param int ID of the object.
 *
 * @return array A list of inventory objects.
 */
function get_inventory_children ($id) {
	global $config;
	$result = array ();

	$sql = sprintf ('SELECT * FROM tinventory WHERE id_parent = %d', $id);
	$children = get_db_all_rows_sql ($sql);
	if ($children === false) {
		return false;
	}

	foreach ($children as $child) {
		$result[$child['id']] = $child;
	}
	
	return $result;
}

/**
 * Print a table with statistics of a list of inventories.
 *
 * @param array List of inventories to get stats.
 * @param bool Whether to return an output string or echo now (optional, echo by default).
 *
 * @return Inventories stats if return parameter is true. Nothing otherwise
 */
function print_inventory_stats ($inventories, $return = false) {
	$output = '';
	
	$total = sizeof ($inventories);
	$inventory_incidents = 0;
	$inventory_opened = 0; 
	foreach ($inventories as $inventory) {
		$incidents = get_incidents_on_inventory ($inventory['id'], false);
		if (sizeof ($incidents) == 0)
			continue;
		$inventory_incidents++;
		foreach ($incidents as $incident) {
			if ($incident['estado'] != 7 && $incident['estado'] != 6) {
				$inventory_opened++;
				break;
			}
		}
	}
	
	$incidents_pct = 0;
	if ($total != 0) {
		$incidents_pct = format_numeric ($inventory_incidents / $total * 100);
		$incidents_opened_pct = format_numeric ($inventory_opened / $total * 100);
	}
	
	$table->width = '50%';
	$table->class = 'float_left blank';
	$table->style = array ();
	$table->style[1] = 'vertical-align: top';
	$table->rowspan = array ();
	$table->rowspan[0][1] = 3;
	$table->data = array ();
	
	$table->data[0][0] = print_label (__('Total objects'), '', '', true, $total);
	$data = array(__('With incidents') => $inventory_incidents, __('Without incidents')=> $total - $inventory_incidents);
	$table->data[0][1] = pie3d_chart ($config['flash_charts'], $data, 200, 150);
	$table->data[1][0] = print_label (__('Total objects with incidents'), '', '', true,
		$inventory_incidents.' ('.$incidents_pct.'%)');
	$table->data[2][0] = print_label (__('Total objects with opened incidents'),
		'', '', true, $inventory_opened.' ('.$incidents_opened_pct.'%)');
	
	$output .= print_table ($table, true);
	
	if ($return)
		return $output;
	echo $output;
}

function get_inventory_generic_labels () {
	global $config;
	
	$labels = array ();
	
	$labels['generic_1'] = isset ($config['inventory_label_1']) ? $config['inventory_label_1'] : lang_string ('Field #').'1';
	$labels['generic_2'] = isset ($config['inventory_label_2']) ? $config['inventory_label_2'] : lang_string ('Field #').'2';
	$labels['generic_3'] = isset ($config['inventory_label_3']) ? $config['inventory_label_3'] : lang_string ('Field #').'3';
	$labels['generic_4'] = isset ($config['inventory_label_4']) ? $config['inventory_label_4'] : lang_string ('Field #').'4';
	$labels['generic_5'] = isset ($config['inventory_label_5']) ? $config['inventory_label_5'] : lang_string ('Field #').'5';
	$labels['generic_6'] = isset ($config['inventory_label_6']) ? $config['inventory_label_6'] : lang_string ('Field #').'6';
	$labels['generic_7'] = isset ($config['inventory_label_7']) ? $config['inventory_label_7'] : lang_string ('Field #').'7';
	$labels['generic_8'] = isset ($config['inventory_label_8']) ? $config['inventory_label_8'] : lang_string ('Field #').'8';
	
	return $labels;
}

function fill_inventories_table($inventories, &$table) {
	global $config;
	
	foreach ($inventories as $inventory) {
		$data = array ();
		
		$id_group = get_inventory_group ($inventory['id']);
		$has_permission = true;
		if (! give_acl ($config['id_user'], $id_group, 'VR'))
			$has_permission = false;
		$contract = get_contract ($inventory['id_contract']);
		$company = get_company ($contract['id_company']);
		$sla = get_sla ($inventory['id_sla']);
		
		$data[0] = $inventory['name'];
		if ($has_permission) {
			$table->head[1] = __('Company');
			$table->head[2] = __('Contract');
			$table->head[3] = __('SLA');
			$table->head[4] = __('Details');
			if ($inventory['description'])
				$data[0] .= ' '.print_help_tip ($inventory['description'], true, 'tip_info');
			$data[1] = $company['name'];
			$data[2] = $contract['name'];
			$data[3] = $sla['name'];
			$sla_description = '<strong>'.__('Minimun response').'</strong>: '.$sla['min_response'].'<br />'.
				'<strong>'.__('Maximum response').'</strong>: '.$sla['max_response'].'<br />'.
				'<strong>'.__('Maximum incidents').'</strong>: '.$sla['max_incidents'].'<br />';
			$data[3] .= print_help_tip ($sla_description, true);
		
			$details = '';
			if ($inventory['ip_address'] != '')
				$details .= '<strong>'.__('IP address').'</strong>: '.$inventory['ip_address'].'<br />';
			if ($inventory['serial_number'] != '')
				$details .= '<strong>'.__('Serial number').'</strong>: '.$inventory['serial_number'].'<br />';
			if ($inventory['part_number'] != '')
				$details .= '<strong>'.__('Part number').'</strong>: '.$inventory['part_number'].'<br />';
			if ($inventory['comments'] != '')
				$details .= '<strong>'.__('Comments').'</strong>: '.$inventory['Comments'].'<br />';
			if ($inventory['id_building'] != 0) {
				$building = get_building ($inventory['id_building']);
				$details .= '<strong>'.__('Building').'</strong>: '.$building['name'].'<br />';
			}
			$data[4] = print_help_tip ($details, true, 'tip_view');
		}
		
		if (give_acl ($config['id_user'], $id_group, "VW")) {
			$table->head[5] = __('Edit');
			$data[5] = '<a href="index.php?sec=inventory&sec2=operation/inventories/inventory&id='.$inventory['id'].'">'.
					'<img src="images/setup.gif" /></a>';
		}
		array_push ($table->data, $data);
	}
}

/*
 * Returns all inventory type fields.
 */ 
function inventories_get_all_type_field ($id_object_type, $id_inventory) {
	
	global $config;
	
	$fields = get_db_all_rows_filter('tobject_type_field', array('id_object_type' => $id_object_type));
	
	if ($fields === false) {
		$fields = array();
	}
	
	$all_fields = array();
	foreach ($fields as $id=>$field) {
		foreach ($field as $key=>$f) {

			if ($key == 'label') {
				$all_fields[$id]['label_enco'] = base64_encode($f);
			}
			$all_fields[$id][$key] = safe_output($f);
		}
	}

	foreach ($all_fields as $key => $field) {

		$id_incident_field = $field['id'];
		
		$data = get_db_value_filter('data', 'tobject_field_data', array('id_inventory'=>$id_inventory, 'id_object_type_field' => $id_incident_field), 'AND');
	
		if ($data === false) {
			$all_fields[$key]['data'] = '';
		} else {
			$all_fields[$key]['data'] = $data;
		}
	}
	
	return $all_fields;
}

/*
 * Returns all external table type fields.
 */ 
function inventories_get_all_external_field ($external_table_name, $external_reference_field, $data_id_external_table) {
	
	global $config;

	$sql_ext = "SHOW COLUMNS FROM ".$external_table_name;
	$external_data = get_db_all_rows_sql($sql_ext);
				
	$sql = "SELECT * FROM $external_table_name WHERE $external_reference_field=$data_id_external_table";
	$fields_ext = get_db_row_sql($sql);

	if ($fields_ext === false) {
		$fields_ext = array();
	}

	$fields = array();
	foreach ($external_data as $key=>$ext) {
		$fields[$ext['Field']] = $ext['Field'];
	}
	
	$all_fields_ext = array();
	$i = 0;
	foreach ($fields_ext as $key => $val) {
		
		if (($key != $external_reference_field) && (array_key_exists($key, $fields))) {
			$all_fields_ext[$i]['label_enco'] =  base64_encode($key);
			$all_fields_ext[$i]['label'] = safe_output($key);
			$all_fields_ext[$i]['data'] = safe_output($val);
			$i++;
		}
	}

	return $all_fields_ext;
}

function inventories_print_tree ($sql_search = false) {
	global $config;
	
	echo '<table class="databox" style="width:98%">';
	echo '<tr><td style="width:60%" valign="top">';
	
	if ($sql_search != false) {
		$sql = "SELECT tobject_type.* FROM tinventory, tobject_type
			WHERE tinventory.id_object_type = tobject_type.id $sql_search
			GROUP BY tobject_type.`name`";

		$object_types = get_db_all_rows_sql($sql);
	} else {
		$object_types = get_object_types (false);
	}

	$sql_search = base64_encode($sql_search);
	
	if (empty($object_types)) {
		echo __("No object types");
		
		echo '</td></tr>';
		echo '</table>';
	} else {

		$elements_type = array();
		foreach ($object_types as $key=>$type) {
		
			$elements_type[$key]['name'] = $type['name'];
			$elements_type[$key]['img'] = print_image ("images/objects/".$type['icon'], true, array ("style" => 'vertical-align: middle;'));
			$elements_type[$key]['id'] = $type['id'];

		}

		echo "<ul style='margin: 0; margin-top: 20px; padding: 0;'>\n";
		$first = true;
		
		foreach ($elements_type as $element) {
			$lessBranchs = 0;

			if ($first) {
				if ($element != end($elements_type)) {

					$img = print_image ("images/tree/first_closed.png", true, array ("style" => 'vertical-align: middle;', "id" => "tree_image_". $element['id'], "pos_tree" => "0"));
					$first = false;
				}
				else {

					$lessBranchs = 1;
					$img = print_image ("images/tree/one_closed.png", true, array ("style" => 'vertical-align: middle;', "id" => "tree_image_". $element['id'], "pos_tree" => "1"));
				}
			}
			else {
				if ($element != end($elements_type))
					$img = print_image ("images/tree/closed.png", true, array ("style" => 'vertical-align: middle;', "id" => "tree_image_". $element['id'], "pos_tree" => "2"));
				else
				{
					$lessBranchs = 1;
					$img = print_image ("images/tree/last_closed.png", true, array ("style" => 'vertical-align: middle;', "id" => "tree_image_". $element['id'], "pos_tree" => "3"));
				}
			}

			$count_inventories = inventories_get_count_inventories_for_tree($element['id'], base64_decode($sql_search));
			if ($count_inventories != 0) {
				echo "<li style='margin: 0px 0px 0px 0px;'>
					<a onfocus='JavaScript: this.blur()' href='javascript: loadTable(\"object_types\",\"" . $element['id'] . "\", " . $lessBranchs . ", \"\" ,\"" . $sql_search .  "\")'>" .
					$img . $element['img'] ."&nbsp;" . safe_output($element['name']) . "&nbsp;($count_inventories)"."</a>";
				
				echo "<div hiddenDiv='1' loadDiv='0' style='margin: 0px; padding: 0px;' class='tree_view' id='tree_div_". $element['id'] . "'></div>";
				echo "</li>\n";
			}
		}
	}
	
	echo "</ul>\n";
	echo '</td>';
	echo '<td style="width:40%" valign="top">';
	echo '<div id="cont" style="position:relative; top:10px;">&nbsp;</div>';
	echo '</td></tr>';
	echo '</table>';
	
	return;
}

function inventories_printTable($id_item, $type, $id_father) {
	global $config;

	switch ($type) {
		
		case 'inventory':
			$info_inventory = get_db_row('tinventory', 'id', $id_item);
			$info_fields = get_db_all_rows_filter('tobject_type_field', array('id_object_type'=>$id_father));
			
			if ($info_inventory !== false) {
				echo '<table cellspacing="2" cellpadding="2" border="0" class="databox" style="width:50%; align:center;">';
				
				if ($info_inventory['owner'] != '') {
					$owner = $info_inventory['owner'];
					$name_owner = get_db_value('nombre_real', 'tusuario', 'id_usuario', $owner);
				} else {
					$name_owner = '--';
				}
				echo '<tr><td class="datos"><b>'.__('Owner: ').'</b></td>';
				echo '<td class="datos"><b>'.$name_owner.'</b></td>';
				echo '</tr>';
				
				if ($info_inventory['id_manufacturer'] != 0) {
					$manufacturer = $info_inventory['id_manufacturer'];
					$name_manufacturer = get_db_value('name', 'tmanufacturer', 'id', $info_inventory['id_manufacturer']);
				} else {
					$name_manufacturer = '--';
				}
				echo '<tr><td class="datos"><b>'.__('Manufacturer: ').'</b></td>';
				echo '<td class="datos"><b>'.$name_manufacturer.'</b></td>';
				echo '</tr>';
				
				if ($info_inventory['id_contract'] != 0) {
					$contract = $info_inventory['id_contract'];
					$name_contract = get_db_value('name', 'tcontract', 'id', $info_inventory['id_manufacturer']);
				} else {
					$name_contract = '--';
				}
				echo '<tr><td class="datos"><b>'.__('Contract: ').'</b></td>';
				echo '<td class="datos"><b>'.$name_contract.'</b></td>';
				echo '</tr>';
				
				if ($info_fields !== false) {

					foreach ($info_fields as $key=>$info) {

						echo '<tr><td class="datos"><b>'.$info['label'].': </b></td>';
						
						$sql = "SELECT `data` FROM tobject_field_data WHERE id_inventory=$id_item AND id=".$info['id'];
				
						$value = process_sql($sql);

						echo '<td class="datos"><b>'.$value[0]['data'].'</b></td>';
						echo '</tr>';
						
						if (($info['type'] == 'external') && ($value != false)) {
							
							$all_fields_ext = inventories_get_all_external_field ($info['external_table_name'], $info['external_reference_field'], $value[0]['data']);

							foreach ($all_fields_ext as $key=>$field) {
								echo '<tr><td class="datos"><b>'.$field['label'].': </b></td>';
								echo '<td class="datos"><b>'.$field['data'].'</b></td>';
								echo '</tr>';
							}
						}
					}
				}

				echo '</table>';
				
				echo '<form id="edit_tree" method="post" action="index.php?sec=inventory&sec2=operation/inventories/inventory_detail&id='.$id_item.'">';
				echo '<div style="width:50%" class="action-buttons button">';
					print_input_hidden ('search', 1);
					print_submit_button (__('Edit'), 'edit', false, 'class="sub next"');
				echo '</div>';
				echo '</form>';
				
				echo '</div>';
			}
		break;
	}
	return;
}

function inventories_check_unique_field($data, $type) {
	
	$sql_unique = "SELECT data FROM tobject_field_data 
				WHERE id_object_type_field IN (
					SELECT id FROM tobject_type_field
					WHERE type='$type')";
					
	$all_data = get_db_all_rows_sql($sql_unique);
	
	foreach ($all_data as $key => $dat) {
		if ($dat['data'] == $data) {
			return false;
		}
	}
	return true;
}

function inventories_link_get_name($id_inventory) {
	
	$name = get_db_value('name', 'tinventory', 'id', $id_inventory);
	
	return $name;
}

function inventories_get_count_inventories_for_tree($id_item, $sql_search = '') {

	$sql = "SELECT tinventory.`id`, tinventory.`name` FROM tinventory, tobject_type
			WHERE `id_object_type`=$id_item
			AND tinventory.id_object_type = tobject_type.id $sql_search";
	
	$cont = get_db_all_rows_sql($sql);
	
	if ($cont === false) {
		return 0;
	}
	
	return count($cont);
}

?>
