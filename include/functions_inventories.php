<?php 
// Integria 2.0 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2008 Artica Soluciones Tecnologicas
// Copyright (c) 2008 Esteban Sanchez, estebans@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

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
 * id_profile Inventory profile id.
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
	$filters['id_profile'] = isset ($filters['id_profile']) ? $filters['id_profile'] : 0;
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
 * @param id ID of the object.
 * @param inventory Array containing inventory objects.
 * @param tree Inventory object tree.
 * @param show_children Show child nodes.
 * @param show_incidents Show incident statistics.
 * @param depth Call depth, used for indentation.
 */
function print_inventory_object ($id, $inventory, $tree, $show_children = false, $show_incidents = false, $depth = 0) {
	global $config;

	if (! isset ($inventory[$id])) {
		return;
	}

	$object = $inventory[$id];

	if ($object['id_contract']) {
		/* Only check ACLs if the inventory has a contract */
		if (! give_acl ($config['id_user'], get_inventory_group ($object['id']), "VR"))
			continue;
	}

	echo '<tr id="result-'.$object['id'].'">';
	echo '<td><strong>#'.$object['id'].'</strong></td>';
	echo '<td>';
	echo '<span class="indent">';
	for ($i = 0; $i < $depth; $i++) {
		echo '&nbsp;&nbsp;&nbsp;&nbsp;';
	}
	echo '</span>';
	if ($depth > 0) {
		echo '<img src="images/copy.png" />';
	}
	echo $object['name'] . '</td>';
	
	if ($show_incidents) {
		$incidents = get_incidents_on_inventory ($object['id'], false);
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
	}
	$companies = get_inventory_affected_companies ($object['id'], false);
	echo '<td>';
	if (isset ($companies[0]['name']))
		echo $companies[0]['name'];
	echo '</td>';
	
	$building = get_building ($object['id_building']);
	echo '<td>';
	if ($building)
		echo $building['name'];
	echo '</td>';
	
	echo '<td>'.$object['description'].'</td>';
	echo '</tr>';
	
	// Print child objects
	if (! $show_children || ! isset ($tree[$object['id']])) {
		return;
	}

	foreach ($tree[$object['id']] as $child) {
		print_inventory_object ($child, $inventory, $tree, $show_children, $show_incidents, $depth + 1);
	}
}

/**
 * Get the children of the given inventory object.
 *
 * @param id ID of the object.
 *
 * @return array A list of inventory objects.
 */
function get_inventory_children ($id) {
	global $config;
	$result = array ();

	$sql = sprintf ('SELECT * FROM tinventory WHERE id_parent = "'.$id.'"');
	$children = get_db_all_rows_sql ($sql);
	if ($children === false) {
		return false;
	}

	foreach ($children as $child) {
		$result[$child['id']] = $child;
	}
	
	return $result;
}

?>
