<?php

// Integria 1.1 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2008 Artica Soluciones Tecnologicas
// Copyright (c) 2007-2008 Esteban Sanchez, estebans@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

if (check_login () != 0) {
 	audit_db ("Noauth", $config["REMOTE_ADDR"], "No authenticated access",
 		"Trying to access inventory viewer");
	require ("general/noaccess.php");
	exit;
}

$id = (int) get_parameter ('id');

echo '<h3>'.__('Contract details on inventory object').' #'.$id.'</h3>';

$contracts = get_inventory_contracts ($id, false);

foreach ($contracts as $contract) {
	echo '<strong>'.__('Contract').'</strong>';
	echo ': '.$contract['name'].'<br />';
	echo '<strong>'.__('SLA').'</strong>';
	echo ': '.get_db_value ('name', 'tsla', 'id', $contract['id_sla']).'<br />';
	echo '<strong>'.__('Company').'</strong>';
	echo ': '.get_db_value ('name', 'tcompany', 'id', $contract['id_company']).'<br />';
	echo '<strong>'.__('Date begin').'</strong>';
	echo ': '.$contract['date_begin'].'<br />';
	echo '<strong>'.__('Date end').'</strong>';
	echo ': '.$contract['date_end'];
	echo '<p />';
}

?>
