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

?>
