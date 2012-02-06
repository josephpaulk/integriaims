<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2012 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// CHECK LOGIN AND ACLs
check_login ();

if (! give_acl ($config['id_user'], 0, "IR")) {
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access incident viewer");
	require ("general/noaccess.php");
	exit;
}

// SHOW THE DETAILS
$table->class = 'result_table listing';
$table->width = $width;
$table->id = 'incident_search_result_table';
$separator_style = 'border-bottom: 1px solid rgb(204, 204, 204);border-top: 1px solid rgb(204, 204, 204);';
$table->style = array ();
$table->data = array();

$table->rowstyle[0] = $separator_style;
$table->rowstyle[1] = $separator_style;
$table->rowstyle[2] = $separator_style;
$table->rowstyle[3] = $separator_style;
$table->rowstyle[4] = $separator_style;

$table->colspan[4][0] = 2;
$table->colspan[5][0] = 2;

$table->head = array ();

$table->data[0][1] = "<b><h3>".__('Status')."</h3></b>";
$table->data[0][2] = "<b><h3>".__('Responsible')."</h3></b>";

$statuses = get_indicent_status ();

$table->data[1][1] = $statuses[$incident['details']['estado']];
$table->data[1][2] = $incident['details']['id_usuario'];

$table->data[2][1] = "<b><h3>".__('Creation')."</h3></b>";
$table->data[2][2] = "<b><h3>".__('Last update')."</h3></b>";

$table->data[3][1] = $incident['details']['inicio'];
$table->data[3][2] = $incident['details']['actualizacion'];

$table->data[4][0] = "<b><h3>".__('Description')."</h3></b>";

if($incident['details']['descripcion'] == '') {
	$incident['details']['descripcion'] = '<i>'.__('No description').'</i>';
}
else {
	$incident['details']['descripcion'] = str_replace("\n",'<br>',safe_output($incident['details']['descripcion']));
}

$table->data[5][0] = $incident['details']['descripcion'];

echo '<div id="details_data" class="tab_data">';
print_table($table);
echo '</div>';

unset($table);

?>
