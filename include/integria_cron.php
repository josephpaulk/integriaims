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

$now = date ("Y-m-d H:i:s");
$now_unix = date("U");
$compare_timestamp = date ("Y-m-d H:i:s", $now_unix -  $config["notification_period"]);

// SLA Max Response time check
// ===========================
// For each record in tgroup manager
$groups = get_db_all_rows_in_table ('tgroup_manager');
if ($groups === false)
	$groups = array ();
foreach ($groups as $group) {
	// Take incidents for this group and "new" status
	$sla_min_response_limit = date ("Y-m-d H:i:s", $now_unix - ($group_manager["max_response_hr"] * 3600));
	$sql = sprintf ('SELECT * FROM tincidencia
		WHERE id_grupo = %d
		AND estado = 1 AND inicio < "%s"',
		$group_manager["id_group"],
		$sla_min_response_limit);
	$incidents = get_db_all_rows_sql ($sql);
	if ($incidents === false)
		$incidents = array ();
	foreach ($incidents as $incident) {
		// And now verify that there is no other notification sent in $config["notification_period"]
		$sql = sprtinf ('SELECT COUNT(*)
			FROM tevent
			WHERE type = "SLA_MAX_RESPONSE_NOTIFY"
			AND id_item = %d
			AND timestamp > %s',
			$incident["id_incidencia"], $compare_timestamp);
		$notifies = get_db_sql ($sql);
		
		// There is any notification for this interval, if not, raise one
		if ($notifies != 0)
			continue;
	
		$owner = $incident["id_usuario"];
		$owner_name = get_db_value ('nombre_real', 'tusuario', 'id_usuario', $owner);
		$group_name = get_db_value ('nombre', 'tgrupo', 'id_grupo', $group_manager["id_group"]);
		$destination_mail = get_db_value ('direccion', 'tusuario', 'id_usuario', $owner);

		$url = $config["base_url"].'/index.php?sec=incidents&sec2=operation/incidents/incident_detail&id='.$incident['id_incidencia'];
		$subject = "Incident #".$incident["id_incidencia"]."[".substr($incident["titulo"],0,20)."...] need inmediate status change (SLA Min.Response Time)";
		$text = "Hello $owner_name \n\n";
		$human_notification_period = give_human_time($group_manager["max_response_hr"]*3600);
		$text .= "Our SLA policy for incidents of group '$group_name', incidents should be update this status in less than a specific time. For this group this time is configured to ".$human_notification_period."\n\n";
		$text .= "Please connect as soon as possible and update status for this incident. You also could click on following URL: \n\n$url";
		
		insert_event ('SLA_MAX_RESPONSE_NOTIFY', $incident['id_incidencia']);
		topi_sendmail ($destination_mail, $subject, $text);
	}
}

// SLA Max resolution time check
foreach ($groups as $group) {
	// Take incidents for this group and "new" status
	$sla_limit = date ("Y-m-d H:i:s", $now_unix - ($group_manager["max_resolution_hr"]*3600));
	// if incident is not closed (6 or 7 in status)
	$sql = sprintf ('SELECT * FROM tincidencia
		WHERE id_grupo = %d
		AND estado NOT IN (6,7)
		AND inicio < "%s"',
		$group_manager["id_group"],
		$sla_limit);
	$incidents = get_db_all_rows_sql ($sql);
	if ($incidents === false)
		$incidents = array ();
	foreach ($incidents as $incident) {
		check_incident_sla ($incident['id_incidencia']);
		
		// And now verify that there is no other notification sent in $config["notification_period"]
		$sql = sprintf ('SELECT COUNT(*)
			FROM tevent
			WHERE type = "SLA_MAX_RESOLUTION_NOTIFY"
			AND id_item = %d AND timestamp > "%s"',
			$incident["id_incidencia"], $compare_timestamp);
		$notifies = get_db_sql ($sql);
		// There is any notification for this interval, if not, raise one
		if ($notifies == 0) 
			continue;
	
		$owner = $incident["id_usuario"];
		$owner_name = give_db_sqlfree_field ("SELECT nombre_real FROM tusuario WHERE id_usuario= '$owner'");
		$group_name = give_db_sqlfree_field ("SELECT nombre FROM tgrupo WHERE id_grupo = ". $group_manager["id_group"]);
		$destination_mail = give_db_sqlfree_field ("SELECT direccion FROM tusuario WHERE id_usuario = '$owner'");

		$human_notification_period = give_human_time($group_manager["max_resolution_hr"]*3600);
		$url = $config["base_url"]."/index.php?sec=incidents&sec2=operation/incidents/incident_detail&id=".$incident["id_incidencia"];
		$subject = "Incident #".$incident["id_incidencia"]."[".substr($incident["titulo"],0,20)."...] need  to be closed (SLA Max. Resolution Time)";
		$text = "Hello $owner_name \n\n";
		$text .= "Our SLA policy for incidents of group '$group_name', says that incidents should be closed (with resolution or not) in less than a specific time. For this group this time is configured to ".$human_notification_period."\n\n";
		$text .= "Please connect as soon as possible and close this incident. You also could click on following URL: \n\n$url";
		
		insert_event ("SLA_MAX_RESOLUTION_NOTIFY", $incident["id_incidencia"]);
		topi_sendmail ($destination_mail, $subject, $text);
	}
}


// Nº Max of opened incidents for this group reached


// SLA Max resolution time check
// ===========================
foreach ($groups as $group) {
	$group_responsible = $group_manager["id_user"];
	$group_max_opened = $group_manager["max_active"];
	$owner_name = get_db_value ('nombre_real', 'tusuario', 'id_usuario', $group_responsible);
	$group_name = get_db_value ('nombre', 'tgrupo', 'id_grupo', $group_manager["id_group"]);
	$destination_mail = get_db_value ('direccion', 'tusuario', 'id_usuario', $group_responsible);
	// if incident is not closed (6 or 7 in status)
	$sql = sprintf ('SELECT COUNT(*)
		FROM tincidencia
		WHERE id_grupo = %d
		AND estado NOT IN (6,7)',
		$group_manager["id_group"]);
	$opened_incidents = get_db_sql ($sql);
	$human_notification_period = give_human_time ($config["notification_period"]);
	if ($opened_incidents > $group_max_opened) {
		$sql = sprintf ('SELECT COUNT(*)
			FROM tevent WHERE type = "SLA_MAX_OPEN_NOTIFY"
			AND id_item = %d
			AND timestamp > "%s"',
			$group_manager["id_group"],
			$compare_timestamp);
		$total_incidents = get_db_sql ($sql);
		if ($total_incidents) {
			$url = $config["base_url"]."/index.php?sec=incidents&sec2=operation/incidents/incident";
			$subject = "Too much opened incidents in group $group_name (SLA Max. Opened Incidents)";
			$text = "Hello $owner_name \n\n";
			$text .= "Our SLA policy for incidents of group '$group_name', is that is not possible to have more than $group_max_opened opened incidents. Now you have ".$opened_incidents. " non-closed incidents. Please, connect integria and try to close some incidents (with resolution or not). Next notification will occur in ".$human_notification_period."\n\n";
			$text .= "Please connect INTEGRIA as soon as possible. You also could click on following URL: \n\n$url";
			
			insert_event ("SLA_MAX_OPEN_NOTIFY", $group_manager["id_group"]);
			topi_sendmail ($destination_mail, $subject, $text);
		}
	}
	
}
?>
