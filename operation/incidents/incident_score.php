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

$accion = "";
global $config;

check_login ();

if (! give_acl ($config['id_user'], 0, "IR")) {
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access incident viewer");
	require ("general/noaccess.php");
	exit;
}

// Take input parameters
$id = (int) get_parameter ('id');
$score = (int) get_parameter ('score', 0);

if (is_numeric($id))
	$incident = get_db_row("tincidencia", "id_incidencia", $id);

// Security checks
if (!isset($incident)){
	echo "<h3 class='error'>".__("Invalid incident ID")."</h3>";
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "Incident score hack", "Trying to access incident score on a invalid incident");
	no_permission();
	return;
}

if ($incident["id_creator"] != $config["id_user"]){
	echo "<h3 class='error'>".__("Non authorized incident score review")."</h3>";
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "Incident score hack", "Trying to access incident score on a non-authorship incident");
	no_permission();
	return;
}

if (($incident["estado"] !=6) AND ($incident["estado"] != 7)){
	echo "<h3 class='error'>".__("Incident cannot be scored until be closed")."</h3>";
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "Incident score hack", "Trying to access incident score before closing incident");
	no_permission();
	return;
}

// Score it !
$sql = "UPDATE tincidencia SET score = $score WHERE id_incidencia = $id";
process_sql ($sql);

echo "<h1>".__("Incident scoring")."</h1>";
echo "<br><br>";
echo __("Thanks for your feedback, this help us to keep improving our job");


