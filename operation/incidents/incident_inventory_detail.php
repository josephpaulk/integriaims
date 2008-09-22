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
$table->head[0] = lang_string ('Name');
$table->head[1] = lang_string ('Description');
$table->head[2] = lang_string ('View');
$table->align[2] = 'center';
$table->data = array ();

echo "<h3>".give_inc_title ($id_incident)."</h3>";

if (count ($inventories) == 0) {
	echo '<h4>'.lang_string ('There\'s no inventory objects associated to this incident').'</h4>';
	return;
}

foreach ($inventories as $incident) {
	$data = array ();
	
	$data[0] = $incident['name'];
	$data[1] = $incident['description'];
	$data[2] = '<a href="index.php?sec=inventory&sec2=operation/inventories/inventory_detail&id='.$incident['id'].'">'.
			'<img border="0" src="images/zoom.png" /></a>';
	
	array_push ($table->data, $data);
}

echo 'TODO: Mostrar SLA specific';
print_table ($table);

?>
