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

include ("config.php");

$config["id_user"] = 'System';
$now = time ();
$compare_timestamp = date ("Y-m-d H:i:s", $now -  $config["notification_period"]);
$human_notification_period = give_human_time ($config["notification_period"]);

/**
 * Check an SLA min response value on an incident and send emails if needed.
 *
 * @param $incident Incident array to check
 */
function check_sla_min ($incident) {
	check_incident_sla_min_response ($incident['id_incidencia']);
}

/**
 * Check an SLA max response value on an incident and send emails if needed.
 *
 * @param $incident Incident array to check
 */
function check_sla_max ($incident) {
	check_incident_sla_max_response ($incident['id_incidencia']);
}

$incidents = get_db_all_rows_sql ('SELECT * FROM tincidencia
	WHERE sla_disabled = 0 AND estado NOT IN (6,7)');
if ($incidents === false)
	$incidents = array ();
foreach ($incidents as $incident) {
	check_sla_min ($incident);
	check_sla_max ($incident);
}

$slas = get_slas ();
foreach ($slas as $sla) {
	$sql = sprintf ('SELECT id FROM tinventory WHERE id_sla = %d', $sla['id']);
	$inventories = get_db_all_rows_sql ($sql);
	if ($inventories === false)
		$inventories = array ();
	
	foreach ($inventories as $inventory) {
		$sql = sprintf ('SELECT tincidencia.id_incidencia
			FROM tincidencia, tincident_inventory
			WHERE tincidencia.id_incidencia = tincident_inventory.id_incident
			AND tincident_inventory.id_inventory = %d
			AND estado NOT IN (6,7)', $inventory['id']);
		
		$opened_incidents = get_db_sql ($sql);
		if ($opened_incidents <= $sla['max_incidents']) 
			continue;
		
		/* There are too many open incidents */
		$sql = sprintf ('UPDATE tincidencia
			SET affected_sla_id = %d
			WHERE id_incidencia = %d',
			$id_sla,
			$incident['id_incidencia']);
	}
}
?>
