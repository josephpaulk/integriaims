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

$enteprise_load = enterprise_include("include/functions_inventory.php");

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
		$id_group = 0;
		if (isset($company['id_grupo']))
			$id_group = $company['id_grupo'];
		if (!give_acl ($config["id_user"], $id_group, "VR") && !get_admin_user ($config["id_user"])) {
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

	include_once("include/functions_crm.php");
	
	/* First try to get only defined contacts */
	$owner = get_db_value('owner', 'tinventory', 'id', $id_inventory);
	
	$owner_info = get_db_row ("tusuario", "id_usuario", $owner);

	$all_contacts = array();

	$contact = array("id" => $owner,
			"type" => "user",
			"id_company" => $owner_info["id_company"],
			"fullname" => $owner_info["nombre_real"],
			"email" => $owner_info["direccion"],
			"phone" => $owner_info["telefono"],
			"mobile" => __("N/A"),
			"position" => __("N/A"),
			"description" => $owner_info["comentarios"],
			"disabled" => $owner_info["disabled"]);

	$all_contacts[$contact["id"]] = $contact;  

	//Get all users associated to the inventory object

	$inv_users = enterprise_hook('inventory_get_users', array($id_inventory, false));	

	if ($inv_users === ENTERPRISE_NOT_HOOK) {
		$inv_users = array();
	}

	foreach ($inv_users as $user) {
       		$contact = array("id" => $user["id_usuario"],
                        "type" => "user",
                        "id_company" => $user["id_company"],
                        "fullname" => $user["nombre_real"],
                        "email" => $user["direccion"],
                        "phone" => $user["telefono"],
                        "mobile" => __("N/A"),
                        "position" => __("N/A"),
                        "description" => $user["comentarios"],
                        "disabled" => $user["disabled"]);

        	$all_contacts[$contact["id"]] = $contact;
	}

	$inv_companies = enterprise_hook('inventory_get_companies', array($id_inventory, false));
	
	if ($inv_companies === ENTERPRISE_NOT_HOOK) {
		$inv_companies = array();
	}
	
	foreach ($inv_companies as $comp) {
		$where_clause = sprintf("WHERE id_company = %d", $comp["id"]);
		$contacts = crm_get_all_contacts ($where_clause);	
		
		if (!$contacts) {
			$contacts = array();
		}

		foreach ($contacts as $contact) {
                        $all_contacts[$contact['id']] = $contact;
                }				
	}

	$contracts = get_inventory_contracts ($id_inventory, false);
	if ($contracts === false)
		return array ();
		
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
			$sql_clause, $config['block_size']);
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
	$table->width = "99%";	
	foreach ($inventories as $inventory) {
		$data = array ();
		
		$id_group = get_inventory_group ($inventory['id']);
		$has_permission = true;
		if (! give_acl ($config['id_user'], $id_group, 'VR'))
			$has_permission = false;
		$contract = get_contract ($inventory['id_contract']);
		$company = get_company ($contract['id_company']);
		
		$data[0] = $inventory['name'];
		if ($has_permission) {
			$table->head[1] = __('Company');
			$table->head[2] = __('Contract');
			if ($inventory['description'])
				$data[0] .= ' '.print_help_tip ($inventory['description'], true, 'tip_info');
			$data[1] = $company['name'];
			$data[2] = $contract['name'];
		}
		
		if (give_acl ($config['id_user'], $id_group, "VW")) {
			$table->head[4] = __('Edit');
			$data[4] = '<a href="index.php?sec=inventory&sec2=operation/inventories/inventory_detail&check_inventory=1&id='.$inventory['id'].'">'.
					'<img src="images/wrench.png" /></a>';
		}
		array_push ($table->data, $data);
	}
}

/*
 * Returns all inventory type fields.
 */ 
function inventories_get_all_type_field ($id_object_type, $id_inventory=false, $only_selected = false) {
	
	global $config;
	
	$fields = get_db_all_rows_filter('tobject_type_field', array('id_object_type' => $id_object_type));
	
	if ($fields === false) {
		$fields = array();
	}
	
	$all_fields = array();
	foreach ($fields as $id=>$field) {
		
		if ($only_selected) {
			if($field['show_list']) {
				foreach ($field as $key=>$f) {
					if ($key == 'label') {
						$all_fields[$id]['label_enco'] = base64_encode($f);
					}
					$all_fields[$id][$key] = safe_output($f);
					$all_fields[$key]['data'] = "";
				}
			}	
		} else {
		
			foreach ($field as $key=>$f) {
				if ($key == 'label') {
					$all_fields[$id]['label_enco'] = base64_encode($f);
				}
				$all_fields[$id][$key] = safe_output($f);
				$all_fields[$key]['data'] = "";
			}
		}
	}

	if (!$id_inventory) {
		return $all_fields;
	}
	
	foreach ($all_fields as $key => $field) {

		$id_incident_field = $field['id'];
		
		$data = get_db_value_filter('data', 'tobject_field_data', array('id_inventory'=>$id_inventory, 'id_object_type_field' => $id_incident_field), 'AND');
	
		if ($data === false) {
			$all_fields[$key]['data'] = '';
		} else {
			$all_fields[$key]['data'] = safe_output($data);
		}
	}
	
	return $all_fields;
}

/*
 * Returns all external table type fields.
 */ 
function inventories_get_all_external_field ($external_table_name, $external_reference_field, $data_id_external_table) {
	
	global $config;

	if (empty($external_table_name)) {
		return false;
	}
	
	$sql_check = "SHOW TABLES LIKE '$external_table_name'";
	$exists = process_sql($sql_check);
	if (!$exists) {
		return false;
	}
	
	
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

function inventories_print_tree ($sql_search, $sql_search_obj_type) {
	global $config;
	global $enteprise_load;
	
	echo '<table class="search-table" style="width:99%">';
	echo "<tr>";
	echo "<td style='text-align: left; padding-right: 40px;' colspan=2>";
	echo "<em style='float: right;'>".__("Using tree view");
	echo print_help_tip (__("Filters only apply <br> to the first level"), true);
	echo "</em>";
	echo "</td>";
	echo "</tr>";
	echo '<tr><td style="width:60%" valign="top">';

	if (!$sql_search_obj_type) {
		$object_types = get_object_types (false, true);
	} else {
		$object_types = get_db_all_rows_sql($sql_search_obj_type);
	}

	$sql_search = base64_encode($sql_search);

	if (empty($object_types)) {
		$object_types = array();
	}	
	
	$elements_type = array();

	foreach ($object_types as $key=>$type) {
		$elements_type[$key]['name'] = $type['name'];
		$elements_type[$key]['img'] = print_image ("images/objects/".$type['icon'], true, array ("style" => 'vertical-align: middle;'));
		$elements_type[$key]['id'] = $type['id'];

	}

	$elements_type[$key+1]['name'] = __('No object type');
	$elements_type[$key+1]['img'] = print_image ("images/objects/box.png", true, array ("style" => 'vertical-align: middle;'));
	$elements_type[$key+1]['id'] = 0;

	echo "<ul style='margin: 0; margin-top: 20px; padding: 0;'>\n";
	$first = true;

	//Clean element based on ACLs
	
	$aux_elems = array();
	foreach ($elements_type as $elem) {

		$count_inventories = 0;

		// if ($enteprise_load !== ENTERPRISE_NOT_HOOK) {
		// 	$count_inventories = inventory_get_count_inventories($elem['id'], base64_decode($sql_search), $config['id_user']); //count
		// } else {
		// 	$count_inventories = inventories_get_count_inventories_for_tree($elem['id'], base64_decode($sql_search)); //count
		// }

		// if ($count_inventories) {
		// 	array_push($aux_elems, $elem);
		// }

		array_push($aux_elems, $elem);
	}

	$elements_type = $aux_elems;

	$i = 0;
	
	$end = 0;

	$margin_left_ref = 23;

	foreach ($elements_type as $element) {

		$lessBranchs = 0;

		if ($element == end($elements_type)) {
			$end = 1;
		}
		
		$img_id = 'tree_image'.$i.'_object_types_'.$element["id"];

		if ($first) {
			
			if ($element != end($elements_type)) {
				
				$img = print_image ("images/tree/first_closed.png", true, array ("style" => 'vertical-align: middle;', "id" => $img_id, "pos_tree" => "0"));
				$first = false;
			}
			else {
				$lessBranchs = 1;
				$img = print_image ("images/tree/one_closed.png", true, array ("style" => 'vertical-align: middle;', "id" => $img_id, "pos_tree" => "1"));
			}
		}
		else {
			if ($element != end($elements_type))
				$img = print_image ("images/tree/closed.png", true, array ("style" => 'vertical-align: middle;', "id" => $img_id, "pos_tree" => "2"));
			else
			{
				$lessBranchs = 1;
				$img = print_image ("images/tree/last_closed.png", true, array ("style" => 'vertical-align: middle;', "id" => $img_id, "pos_tree" => "3"));
			}
		}

		if ($enteprise_load !== ENTERPRISE_NOT_HOOK) {
			$inventories_stock = inventory_get_count_inventories($element['id'], base64_decode($sql_search), $config['id_user'], true); //all inventories to calculate stock
		} else {
			$inventories_stock = inventories_get_count_inventories_for_tree($element['id'], base64_decode($sql_search), true); //all inventories to calculate stock
		}				
		
		// STOCK
		$total_stock = inventories_get_total_stock($inventories_stock);
		$unused_stock = inventories_get_stock($inventories_stock, 'unused');
		$new_stock = inventories_get_stock($inventories_stock, 'new');
		$min_stock = get_db_value('min_stock', 'tobject_type', 'id', $element['id']);
		
		if ($element['id'] == 0) { //no type
			$min_stock = 0;
		}
		
		$color_div = 'no_error_stock';
		if ($total_stock < $min_stock) {
			$color_div = 'error_stock'; 
		}
		
		$id_div = "object_types_".$element['id'];

		echo "<li style='margin: 0px 0px 0px 0px;'>

			<a onfocus='JavaScript: this.blur()' href='javascript: loadTable(\"object_types\",\"" . $element['id'] . "\"," . $lessBranchs . ", \"\" ,\"" . $sql_search .  "\", \"" . $i .  "\", \"" . $end . "\")'>" .
			$img . "&nbsp;" . $element["img"] ."&nbsp;" . safe_output($element['name'])."</a>"."&nbsp;&nbsp;"."($total_stock:$new_stock:$unused_stock:$min_stock)".print_help_tip(__("Total").':'.__("New").':'.__("Unused").':'.__("Min. stock"), true);

		if ($end) {
			echo "<div hiddenDiv='1' loadDiv='0' class='tree_view' id='tree_div" . $i . "_object_types_" . $element["id"] . "'></div>";
		} else {
			echo "<div hiddenDiv='1' loadDiv='0' class='tree_view tree_view_branch' id='tree_div" . $i . "_object_types_" . $element["id"] . "'></div>";
		}
	
		echo "</li>\n";

		$i++;
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
		case 'child':
		case 'child2':
			$info_inventory = get_db_row('tinventory', 'id', $id_item);

			$info_fields = get_db_all_rows_filter('tobject_type_field', array('id_object_type'=>$id_father));
			
			if ($info_inventory !== false) {
				echo '<table border="0" class="listing inv_details_table" style="width:300px; align:center;">';
				
				echo '<tr><th colspan="2" style="font-size:15px; line-height:24px;">';
				echo $info_inventory['name'];
				echo '<a href="index.php?sec=inventory&sec2=operation/inventories/inventory_detail&id='.$id_item.'">';
				echo "<img class='inventory_table_edit' src='images/application_edit_white.png'>";
				echo '</a>';
				echo '</th></tr>';
				echo '</tr>';

				if ($info_inventory['owner'] != '') {
					$owner = $info_inventory['owner'];
					$name_owner = get_db_value('nombre_real', 'tusuario', 'id_usuario', $owner);
				} else {
					$name_owner = '--';
				}
				echo '<tr><td class="datos"><b>'.__('Owner: ').'</b></td>';
				echo '<td class="datos"><b>'.$name_owner.'</b></td>';
				echo '</tr>';

					
				if ($info_inventory['id_parent'] != 0) {
					$parent = $info_inventory['id_parent'];
					$name_parent = get_db_value('name', 'tinventory', 'id', $parent);
				} else {
					$name_parent = '--';
				}
				echo '<tr><td class="datos"><b>'.__('Parent: ').'</b></td>';
				echo '<td class="datos"><b>'.$name_parent.'</b></td>';
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
						
						$sql = "SELECT `data` FROM tobject_field_data WHERE id_inventory=$id_item AND id_object_type_field=".$info['id'];
				
						$value = process_sql($sql);

						echo '<td class="datos"><b>'.$value[0]['data'].'</b></td>';
						echo '</tr>';
						
						if (($info['type'] == 'external') && ($value != false)) {
							
							$all_fields_ext = inventories_get_all_external_field ($info['external_table_name'], $info['external_reference_field'], $info['id']);

							foreach ($all_fields_ext as $key=>$field) {
								echo '<tr><td class="datos"><b>'.$field['label'].': </b></td>';
								echo '<td class="datos"><b>'.$field['data'].'</b></td>';
								echo '</tr>';
							}
						}
					}
				}

				echo '</table>';
				
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
		if ($dat['data'] == $data && $data != '') {
			return false;
		}
	}
	return true;
}

// Checks if $data exists on an unique field
function inventories_check_no_unique_field($data, $type) {
	
	$sql_unique = "SELECT data FROM tobject_field_data 
				WHERE id_object_type_field IN (
					SELECT id FROM tobject_type_field
					WHERE type='$type' AND unique=1)";
					
	$all_data = get_db_all_rows_sql($sql_unique);
	
	foreach ($all_data as $key => $dat) {
		if ($dat['data'] == $data && $data != '') {
			return false;
		}
	}
	return true;
}

function inventories_link_get_name($id_inventory) {
	
	$name = get_db_value('name', 'tinventory', 'id', $id_inventory);
	
	return $name;
}

function inventories_get_count_inventories_for_tree($id_item, $sql_search = '', $get_inventories = false) {

	if ($id_item == 0) { //no type
		$sql_search .= " AND tinventory.id_object_type IS NULL";
	} else {
		$sql_search .= " AND tinventory.id_object_type = tobject_type.id";
	}

	$cont = get_db_all_rows_sql($sql_search);
	
	if ($cont === false) {
		return 0;
	}
	
	if ($get_inventories) {
		return $cont;
	}
	
	return count($cont);
}


function inventories_show_list($sql_search, $sql_count, $params='', $last_update = 0) {
	global $config;

	$is_enterprise = false;
	if (file_exists ("enterprise/include/functions_inventory.php")) {
		require_once ("enterprise/include/functions_inventory.php");
		$is_enterprise = true;
	}

	$params .="&mode=list";	

	if (!$sql_search) {
		$sql_search = "SELECT * FROM tinventory";
	}
			
	if ($last_update) {
		$sql_search .= " ORDER BY last_update DESC";
	}

	$clean_output = get_parameter("clean_output");

	if ($clean_output) {
		$block_limit = 5000;
	} else {
		$block_limit = $config["block_size"];
	}

	$sql_search .= " LIMIT ".$block_limit;

	$offset = get_parameter("offset", 0);	

	$sql_search .= " OFFSET $offset";

	$inventories_aux = get_db_all_rows_sql($sql_search);
	$count_inv = get_db_value_sql($sql_count);
	
	if ($is_enterprise) {
		$inventories = inventory_get_user_inventories($config['id_user'], $inventories_aux);
	} else {
		$inventories = $inventories_aux;
	}

	$inventories = $inventories_aux;
	if ($inventories === false) {
		echo "<h3 class='error'>".__("Empty inventory")."</h3>";
	} else {
		$result_check = inventories_check_same_object_type_list($inventories);

		$table->id = 'inventory_list';
		$table->class = 'listing';
		$table->width = '100%';
		$table->data = array ();
		$table->head = array ();
		$table->colspan = array();
		$table->head[0] = __('Id');
		$table->head[1] = __('Name');
		$table->head[2] = __('Owner');
		$table->head[3] = __("Parent object");
		$table->head[4] = __('Object type');
		$table->head[5] = __('Manufacturer');
		$table->head[6] = __('Contract');
		
		if ($result_check) {
			
			$res_object_fields = inventories_get_all_type_field ($result_check, false, true);
			
			$i = 6;
			foreach ($res_object_fields as $key => $object_field) {
				
				if (isset($object_field["label"])) {
					$table->head[$i] = $object_field['label'];
					$i++;
				}
			}
			$table->head[$i] = __('More info');
		} else {
			if (!$clean_output) {
				$table->head[7] = __('More info');
			}
		}
		
		$count = $count_inv;

		$url_pag = "index.php?sec=inventory&sec2=operation/inventories/inventory".$params;
		$offset = get_parameter("offset");

		if(!$clean_output) {

			pagination ($count, $url_pag, $offset);
		}

		$idx = 0;

		foreach ($inventories as $key=>$inventory) {
			$data = array();
			if (defined ('AJAX')) {
				$url = "javascript:loadInventory(" . $inventory['id'] . ");";
			} else {
				$url = 'index.php?sec=inventory&sec2=operation/inventories/inventory_detail&id='.$inventory['id'];
			} 
			
			$data[0] = "<a href=".$url.">".$inventory['id']."</a>";
			
			$data[1] = "<a href=".$url.">".$inventory['name'].'</a>';
			
			if ($inventory['owner'] != '')
				$name_owner = get_db_value('nombre_real', 'tusuario', 'id_usuario', $inventory['owner']);
			else 
				$name_owner = '--';
			$data[2] = "<a href=".$url.">".$name_owner.'</a>';

			if ($inventory["id_parent"] != 0)
				$name_parent = get_db_value('name', 'tinventory', 'id', $inventory['id_parent']);
			else 
				$name_parent = '--';
			$data[3] = "<a href=".$url.">".$name_parent.'</a>';
			
			if ($inventory['id_object_type'] != 0)
				$name_object = get_db_value('name', 'tobject_type', 'id', $inventory['id_object_type']);
			else 
				$name_object = '--';
			$data[4] = "<a href=".$url.">".$name_object.'</a>';
			
			if ($inventory['id_manufacturer'] != '')
				$name_manufacturer = get_db_value('name', 'tmanufacturer', 'id', $inventory['id_manufacturer']);
			else 
				$name_manufacturer = '--';
			$data[5] = "<a href=".$url.">".$name_manufacturer.'</a>';
			
			if ($inventory['id_contract'] != '')
				$name_contract = get_db_value('name', 'tcontract', 'id', $inventory['id_contract']);
			else 
				$name_contract = '--';
			$data[6] = "<a href=".$url.">".$name_contract.'</a>';
			
			if ($result_check) {

				$result_object_fields = inventories_get_all_type_field ($result_check, $inventory['id'], true);
				
				$i = 6;
				foreach ($result_object_fields as $k => $ob_field) {
					if (isset($ob_field["label"])) {
						$data[$i] = $ob_field['data'];
						$i++;
					}
				}
				
				if (!$clean_output) {
					$data[$i] = '<a href="javascript: toggleInventoryInfo(' . $inventory['id'] . ')" id="show_info-'.$inventory["id"].'">';
					$data[$i] .= print_image ("images/information.png", true,
						array ("title" => __('Show object type fields')));
					$data[$i] .= '</a>&nbsp;';
				}

			} else {
				if (!$clean_output) {
					$data[7] = '<a href="javascript: toggleInventoryInfo(' . $inventory['id'] . ')" id="show_info-'.$inventory["id"].'">';
					$data[7] .= print_image ("images/information.png", true,
						array ("title" => __('Show object type fields')));
					$data[7] .= '</a>&nbsp;';
				}
				
			}
			$table->rowclass[$idx] = 'inventory_info_' . $inventory["id"];
			
			

				$idx++;
				
				array_push ($table->data, $data);
			
			if (!$clean_output) {

				$data_info = array();
				
				$table_info->width = '98%';
				$table_info->class = 'databox_color_without_line';
				
				$table_info->size = array ();
				$table_info->style = array();
				$table_info->data = array();
				
				$res_obj_fields = inventories_get_all_type_field ($inventory['id_object_type'], $inventory['id'], false);
				
				if (empty($res_obj_fields)) {
					$table_info->data[0][0] = '<b>'.__('No data to show').'</b>';
				} else {
					$j = 0;
					foreach ($res_obj_fields as $k => $ob_field) {
						if (isset($ob_field['label'])) {
							$table_info->data[$j][$j] = '<b>'.$ob_field['label'];
							$table_info->data[$j][$j] .= ' : '.'</b>';
							$table_info->data[$j][$j] .= $ob_field['data'];
							$j++;
						}
					}
				}
				
				$data_info['row_info'] = print_table($table_info, true);
				
				$table_info->colspan[0][0] = 6;
				
				$table->rowclass[$idx] = 'inventory_more_info_' . $inventory["id"];
				$table->rowstyle[$idx] = 'display: none;';
				$table->colspan[$idx]["row_info"] = 8;

		
				
				array_push ($table->data, $data_info);
				
				$idx++;
			}
		}
		
		print_table($table);

		if(!$clean_output) {
			pagination ($count, $url_pag, $offset, true);
		}
	}
}

/*
 * IMPORT INVENTORIES FROM CSV. 
 */
function inventories_load_file ($objects_file) {
	$file_handle = fopen($objects_file, "r");
	global $config;
	
	
	while (!feof($file_handle)) {
		$create = true;
		
		$line = fgets($file_handle);
		
		if (($line == '') || (!isset($line))) {
			continue;
		}
		
		preg_match_all('/(.*),/',$line,$matches);
		$values = explode(',',$line);
		
		$id_object_type = $values[0];
		$owner = $values[1];
		$name = $values[2];
		$public = $values[3];
		$description = $values[4];
		$id_contract = $values[5];
		$id_manufacturer = $values[6];
		$id_parent = $values[7];
		
		$value = array(
			'id_object_type' => $id_object_type,
			'owner' => $owner,
			'name' => $name,
			'public' => $public,
			'description' => $description,
			'id_contract' => $id_contract,
			'id_manufacturer' => $id_manufacturer,
			'id_parent' => $id_parent,
			'last_update' => date ("Y/m/d", get_system_time()));
			
			if ($name == '') {
				echo "<h3 class='error'>" . __ ('Inventory name empty') ."</h3>";
				$create = false;
			} else {
				$inventory_id = get_db_value ('id', 'tinventory', 'name', $name);
				if ($inventory_id != false) {
					echo "<h3 class='error'>" . __ ('Inventory '). $name . __(' already exists') . "</h3>";
					$create = false;
				}
			}
	
			if (($id_contract != 0) && ($id_contract != '')) {
				$exists = get_db_value('id', 'tcontract', 'id', $id_contract);
				
				if (!$exists) {
					echo "<h3 class='error'>" . __ ('Contract ') . $id_contract . __(' doesn\'t exist')."</h3>";
					$create = false;
				}
			}
			
			if (($id_manufacturer != 0) && ($id_manufacturer != '')) {
				$exists = get_db_value('id', 'tmanufacturer', 'id', $id_manufacturer);
				
				if (!$exists) {
					echo "<h3 class='error'>" . __ ('Manufacturer ') . $id_manufacturer . __(' doesn\'t exist')."</h3>";
					$create = false;
				}
			}
			
			if (($id_object_type != 0) && ($id_object_type != '')) {
				$exists_object_type = get_db_value('id', 'tobject_type', 'id', $id_object_type);
				
				if (!$exists_object_type) {
					echo "<h3 class='error'>" . __ ('Object type ') . $id_object_type . __(' doesn\'t exist')."</h3>";
					$create = false;
				} else {
					$all_fields = inventories_get_all_type_field ($id_object_type);
					
					$value_data = array();
					$i = 8;
					$j = 0;
					foreach ($all_fields as $key=>$field) {
						$data = $values[$i];

						switch ($field['type']) {
							case 'combo':
								$combo_val = explode(",", $field['combo_value']);
								$k = array_search($data, $combo_val);
								
								if (!$k) {
									echo "<h3 class='error'>" . __ ('Field ') . $field['label'] . __(' doesn\'t match. Valid values: ').$field['combo_value']."</h3>";
									$create = false;
								}
								
								break;
							case 'numeric':
								$res = is_numeric($data);
								if (!$res) {
									echo "<h3 class='error'>" . __ ('Field ') . $field['label'] . __(' must be numeric')."</h3>";
									$create = false;
								}
								break;
							case 'external':
								$table_ext = $field['external_table_name'];
								$exists_table = get_db_sql ("SHOW TABLES LIKE '$table_ext'");
								
								if (!$exists_table) {
									echo "<h3 class='error'>" . __ ('External table ') . $table_ext . __(' doesn\'t exist')."</h3>";
									$create = false;
								}
								
								$id = $field['external_reference_field'];
								$exists_id = get_db_sql ("SELECT $id FROM $table_ext");
								
								if (!$exists_id) {
									echo "<h3 class='error'>" . __ ('Id ') . $id . __(' doesn\'t exist')."</h3>";
									$create = false;
								}
								break;
						}
						
						if ($field['inherit']) {
							$ok = inventories_check_unique_field($data, $field['type']);
							if (!$ok) {
								echo "<h3 class='error'>" . __ ('Field ') . $field['label'] . __(' must be unique')."</h3>";
								$create = false;
							}
						}
						
						$value_data[$j]['id_object_type_field'] = $field['id'];
						$value_data[$j]['data'] = $data;
						$i++;
						$j++;
					}
				}
			}
			
			if ($create) {
				$result_id  = process_sql_insert('tinventory', $value);
			
				if ($result_id) {
					foreach ($value_data as $k => $val_data) {
						$val_data['id_inventory'] = $result_id;
						process_sql_insert('tobject_field_data', $val_data);
					}
				}
				
			}
	} //end while

	fclose($file_handle);
	echo "<h3 class='info'>" . __ ('File loaded'). "</h3>";
	return;
}

//check if all inventories has same object type
function inventories_check_same_object_type_list($inventories) {
	$i = 0;
	foreach ($inventories as $key => $inventory) {
		if ($i == 0) {
			$id_object = $inventory['id_object_type'];
		}
		
		if ($inventory['id_object_type'] != $id_object) {

			return false;
		}
		$i++;	
	}
	
	return $id_object;
}

/**
 * Get all types of objects
 *
 */
 
function inventories_get_inventory_status () {	
	$inventory_status = array();
	
	$inventory_status['new'] = __('New');
	$inventory_status['inuse'] = __('In use');
	$inventory_status['unused'] = __('Unused');
	$inventory_status['issued'] = __('Removed');
	
	return $inventory_status;
}

/*
 * Total stock = new + in use + unused
 */
function inventories_get_total_stock ($inventories) {
	$count = 0;

	if (!$inventories) {
		return $count;
	}

	foreach ($inventories as $key=>$inventory) {
		$inv_status = get_db_value('status', 'tinventory', 'id', $inventory['id']);
		if ($inv_status != 'issued') {
			$count++;
		}
	}
	return $count;
}

function inventories_get_stock ($inventories, $status='new') {
	$count = 0;

	if (!$inventories) {
		return $count;
	}

	foreach ($inventories as $key=>$inventory) {
		$inv_status = get_db_value('status', 'tinventory', 'id', $inventory['id']);
		if ($inv_status == $status) {
			$count++;
		}
	}
	return $count;
}

function print_inventory_tabs($selected_tab, $id, $inventory_name) {
	$details_class = $tracking_class = $contacts_class = $incidents_class = $relationship_class = "ui-tabs";
	
	switch ($selected_tab) {
		case 'details':
			$details_class = 'ui-tabs-selected';
			$title = strtoupper(__('Inventory object details'));
			break;
		case 'tracking':
			$tracking_class = 'ui-tabs-selected';
			$title = strtoupper(__('Tracking'));
			break;
		case 'contacts':
			$contacts_class = 'ui-tabs-selected';
			$title = strtoupper(__('Contacts'));
			break;
		case 'incidents':
			$incidents_class = 'ui-tabs-selected';
			$title = strtoupper(__('Incidents'));
			break;
		case 'relationships':
			$relationship_class = 'ui-tabs-selected';
			$title = strtoupper(__('Relationships'));
			break;
	}
	
	echo '<ul class="ui-tabs-nav">';
	echo '<li class="' . $tracking_class . '"><a href="index.php?sec=inventory&sec2=operation/inventories/inventory_tracking&id=' . $id . '"><span>'.__('Tracking').'</span></a></li>';
	echo '<li class="' . $contacts_class . '"><a href="index.php?sec=inventory&sec2=operation/inventories/inventory_contacts&id=' . $id . '"><span>'.__('Contacts').'</span></a></li>';
	echo '<li class="' . $incidents_class . '"><a href="index.php?sec=inventory&sec2=operation/inventories/inventory_incidents&id=' . $id . '"><span>'.__('Incidents').'</span></a></li>';
	echo '<li class="' . $relationship_class . '"><a href="index.php?sec=inventory&sec2=operation/inventories/inventory_relationship&id=' . $id . '"><span>'.__('Relationships').'</span></a></li>';
	echo '<li class="' . $details_class . '"><a href="index.php?sec=inventory&sec2=operation/inventories/inventory_detail&id=' . $id . '"><span>'.__('Details').'</span></a></li>';
	echo '<li class="ui-tabs-title">' . $title . '</h1></li>';
	echo '</ul>';
	
	echo '<div class="under_tabs_info">' . sprintf(__('Inventory object #%s: %s'), $id, $inventory_name) . '</div><br>';
}
?>
