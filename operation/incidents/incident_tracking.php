<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// Load global vars

global $config;

include_once ("include/functions_reporting.php");

check_login ();

$id_grupo = "";
$creacion_incidente = "";

$id = (int) get_parameter ('id');

if (! $id) {
	require ("general/noaccess.php");
	exit;
}

$incident = get_db_row ('tincidencia', 'id_incidencia', $id);

if (! give_acl ($config['id_user'], $incident['id_grupo'], 'IR')) {
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation","Trying to access to incident #".$id);
	include ("general/noaccess.php");
	exit;
}

echo '<h3>'.__('Incident'). ' #'.$id.' - '.$incident['titulo'].'</h3>';

echo incident_activity_graph ($incident["id_incidencia"]);


$trackings = get_db_all_rows_field_filter ('tincident_track', 'id_incident', $id);

if ($trackings !== false) {
	$table->width = "90%";
	$table->class = 'listing';
	$table->data = array ();
	$table->head = array ();
	$table->head[1] = __('Description');
	$table->head[2] = __('User');
	$table->head[3] = __('Date');
	
	foreach ($trackings as $tracking) {
		$data = array ();
		
		$data[0] = $tracking['description'];
		$data[1] = dame_nombre_real ($tracking['id_user']);
		$data[2] = $tracking['timestamp'];
		
		array_push ($table->data, $data);
	}
	print_table ($table);
} else {
	echo __('No data available');
}
?>
<script language="JavaScript" src="include/graphs/FusionCharts/FusionCharts.js"></script>
