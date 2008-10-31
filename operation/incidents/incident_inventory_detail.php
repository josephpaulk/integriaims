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
$table->head[0] = __('Name');
$table->align[4] = 'center';
$table->align[5] = 'center';
$table->size = array ();
$table->size[4] = '40px';
$table->size[5] = '40px';
$table->data = array ();

echo "<h3>".__('Incident'). " #$id_incident - ".get_incident_title ($id_incident)."</h3>";

if (count ($inventories) == 0) {
	echo '<h4>'.__('There\'s no inventory objects associated to this incident').'</h4>';
	return;
}

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

print_table ($table);

?>
