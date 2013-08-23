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

require_once ('include/functions_inventories.php');
require_once ('include/functions_incidents.php');

$id_incident = (int) get_parameter ('id');

$inventories = get_inventories_in_incident ($id_incident, false);

$table->class = 'listing';
$table->width = '90%';
$table->head = array ();
$table->head[0] = __('Inventory object');
$table->head[1] = __('Company');
$table->head[2] = __('Contact');
$table->head[3] = __('Details');
$table->head[4] = __('Edit');
$table->size = array ();
$table->size[3] = '40px';
$table->size[4] = '40px';
$table->align[3] = 'center';
$table->align[4] = 'center';
$table->data = array ();

if (count ($inventories) == 0) {
	echo '<h4>'.__('There are no contacts associated to this incident').'</h4>';
	return;
}

$companies = array ();
foreach ($inventories as $inventory) {
	$contacts = get_inventory_contacts ($inventory['id'], false);
	
	foreach ($contacts as $contact) {
		$data = array ();
		
		$data[0] = $inventory['name'];
		$data[1] = get_db_value  ('name', 'tcompany', 'id', $contact['id_company']);
		$data[2] = $contact['fullname'];
		$details = '';
		if ($contact['phone'] != '')
			$details .= '<strong>'.__('Phone number').'</strong>: '.$contact['phone'].'<br />';
		if ($contact['mobile'] != '')
			$details .= '<strong>'.__('Mobile phone').'</strong>: '.$contact['mobile'].'<br />';
		if ($contact['position'] != '')
			$details .= '<strong>'.__('Position').'</strong>: '.$contact['position'].'<br />';
		$data[3] = print_help_tip ($details, true, 'tip_view');

		if ($contact["type"] == "user") {
			$data[4] = '<a href="index.php?sec=users&sec2=godmode/usuarios/configurar_usuarios&update_user='.$contact['id'].'">'.
                                '<img src="images/setup.gif" /></a>';
		} else {
			$data[4] = '<a href="index.php?sec=inventory&sec2=operation/contacts/contact_detail&id='.$contact['id'].'">'.
				'<img src="images/setup.gif" /></a>';
		}
		
		array_push ($table->data, $data);
	}
}

//Add editor, creator, closed by, owner


print_table ($table);

$table->data = array ();
$table->head = array ();
$table->head[0] = __('Company');
$table->head[1] = __('Contact');
$table->head[2] = __('Details');
$table->head[3] = __('Edit');

if ($config['incident_reporter'] == 1){
	$contacts = get_incident_contact_reporters ($id_incident); 

	foreach ($contacts as $contact) {
		$data = array ();
		
		$data[0] = get_db_value  ('name', 'tcompany', 'id', $contact['id_company']);
		$data[1] = $contact['fullname'];
		$details = '';
		if ($contact['phone'] != '')
			$details .= '<strong>'.__('Phone number').'</strong>: '.$contact['phone'].'<br />';
		if ($contact['mobile'] != '')
			$details .= '<strong>'.__('Mobile phone').'</strong>: '.$contact['mobile'].'<br />';
		if ($contact['position'] != '')
			$details .= '<strong>'.__('Position').'</strong>: '.$contact['position'].'<br />';
		$data[2] = print_help_tip ($details, true, 'tip_view');
		$data[3] = '<a href="index.php?sec=inventory&sec2=operation/contacts/contact_detail&id='.$contact['id'].'">'.
				'<img src="images/setup.gif" /></a>';
		array_push ($table->data, $data);
	}

	echo '<h4>'.__('Contacts who reported this incident').'</h4>';
	print_table ($table);
}

?>
