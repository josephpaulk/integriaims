<?php

// Integria 2.0 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2008 Artica Soluciones Tecnologicas
// Copyright (c) 2008 Esteban Sanchez

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// Load global vars

check_login ();

/* This page only works in AJAX */
if (! defined ('AJAX'))
	return;

$search_string = (string) get_parameter ('search_string');
$status = (int) get_parameter ('status');
$search_priority = (int) get_parameter ('search_priority', -1);
$search_id_group = (int) get_parameter ('search_id_group', 1);
$search_status = (int) get_parameter ('search_status', 0);
$search_id_product = (int) get_parameter ('search_id_product', 0);
$search_id_company = (int) get_parameter ('search_id_company', 0);

if ($status == 0)
	$status = implode (',', array_keys (get_indicent_status ()));

$resolution = get_incident_resolution();

$sql_clause = '';
if ($search_priority != -1)
	$sql_clause .= sprintf (' AND prioridad = %d', $search_priority);
if ($search_id_group != 1)
	$sql_clause .= sprintf (' AND id_grupo = %d', $search_id_group);
if ($search_status)
	$sql_clause .= sprintf (' AND estado = %d', $search_status);

$sql = sprintf ('SELECT * FROM tincidencia
		WHERE estado IN (%s)
		%s
		AND (titulo LIKE "%%%s%%" OR descripcion LIKE "%%%s%%")',
		$status, $sql_clause, $search_string, $search_string);

$incidents = get_db_all_rows_sql ($sql);
if ($incidents === false) {
	echo '<tr><td>'.lang_string ('Nothing was found').'</td></tr>';
	return;
}

$status = get_indicent_status ();

foreach ($incidents as $incident) {
	/* Check aditional searching clauses */
	if ($search_id_product) {
		$inventories = get_inventories_in_incident ($incident['id_incidencia'], false);
		$found = false;
		foreach ($inventories as $inventory) {
			if ($inventory['id_product'] == $search_id_product) {
				$found = true;
				break;
			}
		}
		
		if (! $found)
			continue;
	}
	
	if ($search_id_company) {
		$inventories = get_inventories_in_incident ($incident['id_incidencia'], false);
		
		$found = false;
		foreach ($inventories as $inventory) {
			$companies = get_inventory_affected_companies ($inventory['id'], false);
			foreach ($companies as $company) {
				if ($company['id'] == $search_id_company) {
					$found = true;
					break;
				}
			}
			if ($found)
				break;
		}
		
		if (! $found)
			continue;
	}
	
	/* We print the rows directly, because it will be used in a sortable
	   jQuery table and it only needs the rows */

	if ($incident["estado"] < 3 )
		$tr_status = 'class="red"';
	elseif ($incident["estado"] < 6 )
		$tr_status = 'class="yellow"';
	else
		$tr_status = 'class="green"';

	echo '<tr '.$tr_status.' id="indicent-'.$incident['id_incidencia'].'">';

	echo '<td><strong>#'.$incident['id_incidencia'].'</strong></td>';

	// SLA Fired ?? 
	$sla = rand  ( 0 , 1 ); // Not real check, only to render something
	if ($sla == 0)
		echo '<td><img src="images/exclamation.png" border=0></td>';
	else
		echo '<td></td>';

	echo '<td>'.$incident['titulo'].'</td>';
	echo '<td>'.get_db_value ("nombre", "tgrupo", "id_grupo", $incident['id_grupo']).'</td>';
	echo '<td><strong>'.$status[$incident['estado']].'</strong> - <i>'. $resolution[$incident['resolution']].'</i></td>';


	echo '<td>'.print_priority_flag_image ($incident['prioridad'], true).'</td>';
	echo '<td class="f9">'.human_time_comparation ($incident["actualizacion"]).' / <i>';
	echo human_time_comparation ($incident["inicio"]).'</i></td>';

	/* Get special details about the incident */
	echo '<td class="f9">';
	$people = people_involved_incident ($incident["id_incidencia"]);
	print_help_tip (implode ('&nbsp;', $people), false, 'tip_people');

	/* Files */
	$files = give_number_files_incident ($incident["id_incidencia"]);
	if ($files)
		echo '&nbsp;<img src="images/disk.png"
			title="'.$files.' '.lang_string ('Files').'" />';

	/* Mail notification */
	$mail_check = get_db_value ('notify_email', 'tincidencia',
				'id_incidencia', $incident["id_incidencia"]);
	if ($mail_check > 0)
		echo '&nbsp;<img src="images/email_go.png"
			title="'.lang_string ('Mail notification').'" />';

	/* Workunits */
	$timeused = give_hours_incident ($incident["id_incidencia"]);;
	$incident_wu = $in_wu = give_wu_incident ($incident["id_incidencia"]);
	if ($incident_wu > 0) {
		echo '&nbsp;<img src="images/award_star_silver_1.png" valign="bottom">'.$timeused;
	}
	echo '</td>';

	echo '</tr>';
}
?>
