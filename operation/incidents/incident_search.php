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

require_once ('include/functions_incidents.php');

$search_form = (bool) get_parameter ('search_form');
$create_custom_search = (bool) get_parameter ('create_custom_search');
$delete_custom_search = (bool) get_parameter ('delete_custom_search');
$get_custom_search_values = (bool) get_parameter ('get_custom_search_values');

/* Create a custom saved search via AJAX */
if ($create_custom_search) {
	$form_values = get_parameter ('form_values');
	$search_name = (string) get_parameter ('search_name');
	
	$result = create_custom_search ($search_name, 'incidents', $form_values);
	
	if ($result === false) {
		echo '<h3 class="error">'.__('Could not create custom search').'</h3>';
	} else {
		echo '<h3 class="suc">'.__('Custom search saved').'</h3>';
	}
	
	if (defined ('AJAX')) {
		return;
	}
}

/* Get a custom search via AJAX */
if ($get_custom_search_values) {
	$id_search = (int) get_parameter ('id_search');
	$search = get_custom_search ($id_search, 'incidents');
	if ($search === false) {
		echo json_encode (false);
		return;
	}
	echo json_encode (unserialize ($search['form_values']));
	return;
}

/* Delete a custom saved search via AJAX */
if ($delete_custom_search) {
	$id_search = (int) get_parameter ('id_search');
	$sql = sprintf ('DELETE FROM tcustom_search
		WHERE id_user = "%s"
		AND id = %d',
		$config['id_user'], $id_search);
	$result = process_sql ($sql);
	if ($result === false) {
		echo '<h3 class="error">'.__('Could not delete custom search').'</h3>';
	} else {
		echo '<h3 class="suc">'.__('Custom search deleted').'</h3>';
	}
	
	if (defined ('AJAX')) {
		return;
	}
}

/* Show search form via AJAX */
if ($search_form) {
	form_search_incident ();

	$table->class = 'hide result_table listing';
	$table->width = '100%';
	$table->id = 'incident_search_result_table';
	$table->head = array ();
	$table->head[0] = "Id";
	$table->head[1] = __('SLA');
	$table->head[2] = __('Incident');
	$table->head[3] = __('Group');
	$table->head[4] = __('Status')."<br><i>".__('Resolution')."</i>";
	$table->head[5] = __('Priority');
	$table->head[6] = __('Updated')."<br><i>".__('Started')."</i>";
	$table->head[7] = __('Work');
	$table->head[8] = __('Flags');
	$table->style = array ();
	$table->style[0] = '';
	// Dont work
	$table->rowstyle[4] = "font-size: 4px;";

	print_table ($table);

	echo '<div id="pager" class="hide pager">';
	echo '<form>';
	echo '<img src="images/control_start_blue.png" class="first" />';
	echo '<img src="images/control_rewind_blue.png" class="prev" />';
	echo '<input type="text" class="pager pagedisplay" size=5 />';
	echo '<img src="images/control_fastforward_blue.png" class="next" />';
	echo '<img src="images/control_end_blue.png" class="last" />';
	echo '<select class="pager pagesize" style="display:none">';
	echo '<option selected="selected" value="5">5</option>';
	echo '</select>';
	echo '</form>';
	echo '</div>';
	
	if (defined ('AJAX')) {
		return;
	}
}

$show_stats = (bool) get_parameter ('show_stats');

$filter = array ();
$filter['string'] = (string) get_parameter ('search_string');
$filter['status'] = (int) get_parameter ('status');
$filter['priority'] = (int) get_parameter ('search_priority', -1);
$filter['id_group'] = (int) get_parameter ('search_id_group', 1);
$filter['status'] = (int) get_parameter ('search_status', 0);
$filter['id_product'] = (int) get_parameter ('search_id_product');
$filter['id_company'] = (int) get_parameter ('search_id_company');
$filter['id_inventory'] = (int) get_parameter ('search_id_inventory');
$filter['serial_number'] = (string) get_parameter ('search_serial_number');
$filter['id_building'] = (int) get_parameter ('search_id_building');
$filter['sla_fired'] = (bool) get_parameter ('search_sla_fired');
$filter['id_incident_type'] = (int) get_parameter ('search_id_incident_type');
$filter['id_user'] = (string) get_parameter ('search_id_user', '');
$filter['id_incident_type'] = (int) get_parameter ('search_id_incident_type');
$filter['id_user'] = (string) get_parameter ('search_id_user', '');
$filter['first_date'] = (string) get_parameter ('search_first_date');
$filter['last_date'] = (string) get_parameter ('search_last_date');

$incidents = filter_incidents ($filter);
if ($incidents === false) {
	if (!$show_stats)
		echo '<tr><td colspan="8">'.__('Nothing was found').'</td></tr>';
	return;
}

/* Show HTML if show_stats flag is active on HTML request */
if ($show_stats) {
	print_incidents_stats ($incidents);
	
	return;
}

$statuses = get_indicent_status ();

foreach ($incidents as $incident) {
	/* We print the rows directly, because it will be used in a sortable
	   jQuery table and it only needs the rows */

	if ($incident["estado"] < 3 )
		$tr_status = 'class="red"';
	elseif ($incident["estado"] < 6 )
		$tr_status = 'class="yellow"';
	else
		$tr_status = 'class="green"';

	echo '<tr '.$tr_status.' id="indicent-'.$incident['id_incidencia'].'"';

	echo " style='border-bottom: 1px solid #ccc;' >";
	echo '<td width=30><strong>#'.$incident['id_incidencia'].'</strong></td>';
	
	// SLA Fired ?? 
	if ($incident["affected_sla_id"] != 0)
		echo '<td width=25><img src="images/exclamation.png" border=0></td>';
	else
		echo '<td></td>';
	
	echo '<td>'.$incident['titulo'].'</td>';
	echo '<td>'.get_db_value ("nombre", "tgrupo", "id_grupo", $incident['id_grupo']).'</td>';
	$resolution = isset ($resolutions[$incident['resolution']]) ? $resolutions[$incident['resolution']] : __('None');

	echo '<td class="f9"><strong>'.$statuses[$incident['estado']].'</strong><br><em>'.$resolution.'</em></td>';
	
	echo '<td>'.print_priority_flag_image ($incident['prioridad'], true).'</td>';
	
	echo '<td class="f9">'.human_time_comparation ($incident["actualizacion"]).'<br><i>';
	echo human_time_comparation ($incident["inicio"]).'</i></td>';
	
	/* Workunits */
	echo '<td>';
	$timeused = get_incident_wokunit_hours ($incident["id_incidencia"]);
	$incident_wu = $in_wu = get_incident_count_workunits ($incident["id_incidencia"]);
	if ($incident_wu > 0) {
		echo '<img src="images/award_star_silver_1.png" />'.$timeused;
	}
	echo '</td>';
	
	/* Get special details about the incident */
	echo '<td>';
	$people = people_involved_incident ($incident["id_incidencia"]);
	print_help_tip (implode ('&nbsp;', $people), false, 'tip_people');
	
	/* Files */
	$files = get_number_files_incident ($incident["id_incidencia"]);
	if ($files)
		echo '&nbsp;<img src="images/disk.png"
			title="'.$files.' '.__('Files').'" />';
	
	/* Mail notification */
	$mail_check = get_db_value ('notify_email', 'tincidencia',
				'id_incidencia', $incident["id_incidencia"]);
	if ($mail_check > 0)
		echo '&nbsp;<img src="images/email_go.png"
			title="'.__('Mail notification').'" />';
	echo '</td>';
	
	echo '</tr>';
}
?>
