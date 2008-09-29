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
 		"Trying to access inventory workunit details");
	require ("general/noaccess.php");
	exit;
}

$id = (int) get_parameter ('id');

echo '<h3>'.__('Workunits done in inventory object').' #'.$id.'</h3>';

$workunits = get_inventory_workunits ($id);
foreach ($workunits as $workunit) {
	$title = get_db_value ('titulo', 'tincidencia', 'id_incidencia', $workunit['id_incident']);
	show_workunit_data ($workunit, $title);
}
?>
