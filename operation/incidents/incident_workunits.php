<?php

// Integria 1.0 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2008 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

if (check_login ()) {
	audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access","Trying to access incident viewer");
	require ("general/noaccess.php");
	exit;
}

$id_grupo = "";
$creacion_incidente = "";
$id_incident = get_parameter ('id');
$title = '';

if ($id_incident) {
	$iduser_temp=$_SESSION['id_usuario'];
	// Obtain group of this incident
	$sql1='SELECT * FROM tincidencia WHERE id_incidencia = '.$id_incident;
	$result=mysql_query($sql1);
	$row=mysql_fetch_array($result);
	// Get values
	$title = $row["titulo"];
	$texto = $row["descripcion"];
	$inicio = $row["inicio"];
	$actualizacion = $row["actualizacion"];
	$estado = $row["estado"];
	$prioridad = $row["prioridad"];
	$origen = $row["origen"];
	$usuario = $row["id_usuario"];
	$nombre_real = dame_nombre_real($usuario);
	$id_grupo = $row["id_grupo"];
	$id_creator = $row["id_creator"];
	$grupo = dame_nombre_grupo($id_grupo);
	$result_msg = "";
	
	$id_user=$_SESSION['id_usuario'];
	if (give_acl($iduser_temp, $id_grupo, "IR") != 1){
	 	// Doesn't have access to this page
		audit_db($id_user,$REMOTE_ADDR, "ACL Violation","Trying to access to incident ".$id_incident." '".$title."'");
		include ("general/noaccess.php");
		exit;
	}
}

// ********************************************************************
// Workunit view
// ********************************************************************

echo $result_msg;

echo "<h3>".__('Incident'). " #$id_incident - ".give_inc_title ($id_incident)."</h3>";

$workunits = get_incident_workunits ($id_incident);

if ($workunits === false) {
	echo '<h4>'.__('No workunit was done in this incident').'</h4>';
	return;
}

foreach ($workunits as $workunit) {
	$workunit_data = get_workunit_data ($workunit['id_workunit']);
	show_workunit_data ($workunit_data, $title);
}
?>
