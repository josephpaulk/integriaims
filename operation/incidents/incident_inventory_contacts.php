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

$id_incident = (int) get_parameter ('id');

$inventories = get_inventories_in_incident ($id_incident, false);

$table->class = 'listing';
$table->width = '90%';
$table->head = array ();
$table->head[0] = lang_string ('Inventory object');
$table->head[1] = lang_string ('Company');
$table->head[2] = lang_string ('Contact');
$table->head[3] = lang_string ('Details');
$table->head[4] = lang_string ('Edit');
$table->size = array ();
$table->size[4] = '40px';
$table->size[5] = '40px';
$table->align[3] = 'center';
$table->align[4] = 'center';
$table->data = array ();

echo "<h3>".lang_string ("Incident"). " #$id_incident - ".give_inc_title ($id_incident)."</h3>";

if (count ($inventories) == 0) {
	echo '<h4>'.lang_string ('There are no contacts associated to this incident').'</h4>';
	return;
}

$companies = array ();
foreach ($inventories as $inventory) {
	$contracts = get_inventory_contracts ($inventory['id'], false);
	foreach ($contracts as $contract) {
		$company = get_company ($contract['id_company']);
		if ($company === false)
			continue;
		$contacts = get_company_contacts ($company['id'], false);
		
		foreach ($contacts as $contact) {
			$data = array ();
		
			$data[0] = $inventory['name'];
			$data[1] = $company['name'];
			$data[2] = $contact['fullname'];
			$details = '';
			if ($contact['phone'] != '')
				$details .= '<strong>'.__('Phone number').'</strong>: '.$contact['phone'].'<br />';
			if ($contact['mobile'] != '')
				$details .= '<strong>'.__('Mobile phone').'</strong>: '.$contact['mobile'].'<br />';
			if ($contact['position'] != '')
				$details .= '<strong>'.__('Position').'</strong>: '.$contact['position'].'<br />';
			$data[3] = print_help_tip ($details, true, 'tip_view');
			$data[4] = '<a href="index.php?sec=inventory&sec2=operation/contacts/contact_detail&id='.$contact['id'].'">'.
					'<img src="images/setup.gif" /></a>';
			array_push ($table->data, $data);
		}
	}
}

print_table ($table);

?>
