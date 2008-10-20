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

if (check_login() != 0) {
 	audit_db("Noauth",$config["REMOTE_ADDR"], "No authenticated access","Trying to access incident tracking");
	require ("general/noaccess.php");
	exit;
}

$id_grupo = "";
$creacion_incidente = "";

if (isset($_GET["id"])){
	$id_inc = $_GET["id"];
	$iduser_temp=$_SESSION['id_usuario'];
	// Obtain group of this incident
	$sql1 = 'SELECT * FROM tincidencia WHERE id_incidencia = '.$id_inc;
	$result = mysql_query($sql1);
	$row = mysql_fetch_array($result);
	// Get values
	$titulo = $row["titulo"];
	$id_grupo = $row["id_grupo"];
	$id_creator = $row["id_creator"];
	$grupo = dame_nombre_grupo($id_grupo);
	$id_user = $_SESSION['id_usuario'];
	if (give_acl ($iduser_temp, $id_grupo, "IR") != 1){
	 	// Doesn't have access to this page
		audit_db ($id_user,$config["REMOTE_ADDR"], "ACL Violation","Trying to access to incident ".$id_inc." '".$titulo."'");
		include ("general/noaccess.php");
		exit;
	}
} else {
	audit_db($id_user,$config["REMOTE_ADDR"], "ACL Violation","Trying to access to incident ".$id_inc." '".$titulo."'");
	include ("general/noaccess.php");
	exit;
}


// ********************************************************************
// Notes
// ********************************************************************

echo "<h3>".__('Incident'). " #$id_inc - ".give_inc_title ($id_inc)."</h3>";

echo '<table width="80%" class="listing">';

$trackings = get_db_all_rows_field_filter ('tincident_track', 'id_incident', $id_inc);

if ($trackings !== false) {
	$table->width = "90%";
	$table->class = 'listing';
	$table->data = array ();
	$table->head = array ();
	$table->head[0] = __('State');
	$table->head[1] = __('User');
	$table->head[2] = __('Date');
	
	foreach ($trackings as $tracking) {
		$data = array ();
		
		$timestamp = $tracking["timestamp"];
		$state = $tracking["state"];
		$user = $tracking["id_user"];
		$aditional_data = $tracking["id_aditional"];

		switch ($state) {
		case 0:
			$data[0] = __('Incident created');
			break;
		case 1:
			$data[0] = __('Incident updated');
			break;
		case 2:
			$data[0] = __('Incident note added');
			break;
		case 3:
			$data[0] = __('Incident file added');
			break;
		case 4:
			$data[0] = __('Incident note deleted');
			break;
		case 5:
			$data[0] = __('Incident file deleted');
			break;
		case 6:
			$data[0] = __('Incident change priority');
			$data[0] .= " -> ".$tracking["id_aditional"];
			break;
		case 7:
			$data[0] = __('Incident change status');
			$data[0] .= " -> ". give_db_value ('name', 'tincident_status', 'id', $tracking["id_aditional"]);
			break;
		case 8:
			$data[0] = __('Incident change resolution');
			$data[0] .= " -> ".give_db_value ("name", "tincident_resolution", 'id', $tracking["id_aditional"]);
			break;
		case 9:
			$data[0] = __('Incident workunit added');
			break;
		default:
			$data[0] = __('Unknown');
		}
		$data[0] = $description;
		$data[1] = dame_nombre_real ($user);
		$data[2] = $timestamp;
		
		array_push ($table->data, $data);
	}
	print_table ($table);
} else {
	echo __('No data available');
}
?>
