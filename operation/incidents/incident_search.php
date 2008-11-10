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

$search_form = (bool) get_parameter ('search_form');
$create_custom_search = (bool) get_parameter ('create_custom_search');
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
	$table->head[4] = __('Status')." - <i>".__('Resolution')."</i>";
	$table->head[5] = __('Priority');
	$table->head[6] = __('Updated')." - <i>".__('Started')."</i>";
	$table->head[7] = __('Flags');
	$table->style = array ();
	$table->style[0] = '';

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

$search_string = (string) get_parameter ('search_string');
$status = (int) get_parameter ('status');
$search_priority = (int) get_parameter ('search_priority', -1);
$search_id_group = (int) get_parameter ('search_id_group', 1);
$search_status = (int) get_parameter ('search_status', 0);
$search_id_product = (int) get_parameter ('search_id_product');
$search_id_company = (int) get_parameter ('search_id_company');
$search_id_inventory = (int) get_parameter ('search_id_inventory');
$search_serial_number = (string) get_parameter ('search_serial_number');
$search_id_building = (int) get_parameter ('search_id_building');
$search_sla_fired = (bool) get_parameter ('search_sla_fired');
$search_id_incident_type = (int) get_parameter ('search_id_incident_type');
$search_id_user = (string) get_parameter ('search_id_user', '');
$search_first_date = (string) get_parameter ('search_first_date');
$search_last_date = (string) get_parameter ('search_last_date');

if ($status == 0)
	$status = implode (',', array_keys (get_indicent_status ()));

$resolutions = get_incident_resolutions ();

$sql_clause = '';
if ($search_priority != -1)
	$sql_clause .= sprintf (' AND prioridad = %d', $search_priority);
if ($search_id_group != 1)
	$sql_clause .= sprintf (' AND id_grupo = %d', $search_id_group);
if ($search_status)
	$sql_clause .= sprintf (' AND estado = %d', $search_status);
if ($search_id_user != '0')
	$sql_clause .= sprintf (' AND id_usuario = "%s"', $search_id_user);
if ($search_id_incident_type)
	$sql_clause .= sprintf (' AND id_incident_type = %d', $search_id_incident_type);
if ($search_first_date != '') {
	$time = strtotime ($search_first_date);
	$sql_clause .= sprintf (' AND inicio >= "%s"', date ("Y-m-d", $time));
}
if ($search_last_date != '') {
	$time = strtotime ($search_last_date);
	$sql_clause .= sprintf (' AND inicio <= "%s"', date ("Y-m-d", $time));
}

$sql = sprintf ('SELECT * FROM tincidencia
		WHERE estado IN (%s)
		%s
		AND (titulo LIKE "%%%s%%" OR descripcion LIKE "%%%s%%")
		ORDER BY actualizacion desc
		LIMIT %d',
		$status, $sql_clause, $search_string, $search_string,
		$config['limit_size']);

$incidents = get_db_all_rows_sql ($sql);
if ($incidents === false) {
	if (!$show_stats)
		echo '<tr><td colspan="8">'.__('Nothing was found').'</td></tr>';
	return;
}

$status = get_indicent_status ();

/* Show stats is a flag to show stats of the incidents on the search */
if ($show_stats) {
	$stat_incidents = array ();
}

foreach ($incidents as $incident) {
	if (! give_acl ($config['id_user'], $incident['id_grupo'], 'IR'))
		continue;
	
	$inventories = get_inventories_in_incident ($incident['id_incidencia'], false);
	
	/* Check aditional searching clauses */
	if ($search_sla_fired && $incident['affected_sla_id'] == 0) {
		continue;
	}
	
	if ($search_id_inventory) {
		$found = false;
		foreach ($inventories as $inventory) {
			if ($inventory['id'] == $search_id_inventory) {
				$found = true;
				break;
			}
		}
		
		if (! $found)
			continue;
	}
	
	if ($search_serial_number != '') {
		$found = false;
		foreach ($inventories as $inventory) {
			if (strcasecmp ($inventory['serial_number'], $search_serial_number)) {
				$found = true;
				break;
			}
		}
		
		if (! $found)
			continue;
	}
	
	if ($search_id_building) {
		$found = false;
		foreach ($inventories as $inventory) {
			if ($inventory['id_building'] == $search_id_building) {
				$found = true;
				break;
			}
		}
		
		if (! $found)
			continue;
	}
	
	if ($search_id_product) {
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
	
	if ($show_stats) {
		array_push ($stat_incidents, $incident);
		/* Continue to avoid showing any results. Stats HTML are show at
		the bottom of this file. */
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
	if ($incident["affected_sla_id"] != 0)
		echo '<td><img src="images/exclamation.png" border=0></td>';
	else
		echo '<td></td>';

	echo '<td>'.$incident['titulo'].'</td>';
	echo '<td>'.get_db_value ("nombre", "tgrupo", "id_grupo", $incident['id_grupo']).'</td>';
	$resolution = isset ($resolutions[$incident['resolution']]) ? $resolutions[$incident['resolution']] : __('None');
	echo '<td><strong>'.$status[$incident['estado']].'</strong> - <em>'.$resolution.'</em></td>';


	echo '<td>'.print_priority_flag_image ($incident['prioridad'], true).'</td>';
	echo '<td>'.human_time_comparation ($incident["actualizacion"]).' / <i>';
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

/* Show HTML if show_stats flag is active on HTML request */
if ($show_stats) {
	$total = sizeof ($stat_incidents);
	$opened = 0;
	$total_hours = 0;
	$total_lifetime = 0;
	$max_lifetime = 0;
	$oldest_incident = false;
	foreach ($stat_incidents as $incident) {
		if ($incident['estado'] != 6 && $incident['estado'] != 7) {
			$opened++;
		} elseif ($incident['actualizacion'] != '0000-00-00 00:00:00') {
			$lifetime = get_db_value ('actualizacion - inicio',
				'tincidencia', 'id_incidencia', $incident['id_incidencia']);
			if ($lifetime > $max_lifetime) {
				$oldest_incident = $incident;
				$max_lifetime = $lifetime;
			}
			$total_lifetime += $lifetime;
		}
		$workunits = get_incident_workunits ($incident['id_incidencia']);
		$hours = 0;
		foreach ($workunits as $workunit) {
			$hours += get_db_value ('duration', 'tworkunit', 'id', $workunit['id_workunit']);
		}
		$total_hours += $hours;
	}
	$closed = $total - $opened;
	$opened_pct = 0;
	$mean_work = 0;
	$mean_lifetime = 0;
	if ($total != 0) {
		$opened_pct = format_numeric ($opened / $total * 100);
		$mean_work = format_numeric ($total_hours / $total, 2);
	}
	
	if ($closed != 0) {
		$mean_lifetime = (int) ($total_lifetime / $closed) / 60;
	}
	$table->width = '400px';
	$table->class = 'databox';
	$table->rowspan = array ();
	$table->rowspan[0][1] = 2;
	$table->colspan = array ();
	$table->colspan[3][0] = 2;
	$table->style = array ();
	$table->style[2] = 'vertical-align: top';
	$table->data = array ();
	
	$table->data[0][0] = print_label (__('Total incicents'), '', '', true, $total);
	$data = implode (',', array ($opened, $total - $opened));
	$legend = implode (',', array (__('Opened'), __('Closed')));
	$table->data[0][1] = '<img src="include/functions_graph.php?type=pipe&width=200&height=100&data='.$data.'&legend='.$legend.'" />';
	$table->data[1][0] = print_label (__('Opened'), '', '', true,
		$opened.' ('.$opened_pct.'%)');
	$table->data[2][0] = print_label (__('Mean life time'), '', '', true,
		give_human_time ($mean_lifetime));
	$table->data[2][1] = print_label (__('Mean work time'), '', '', true,
		$mean_work.' '.__('Hours'));
	
	if ($oldest_incident) {
		$link = '<a href="index.php?sec=incidents&sec2=operation/incidents/incident&id='.
			$oldest_incident['id_incidencia'].'">'.$oldest_incident['titulo'].'</a>';
		$table->data[3][0] = print_label (__('Longest closed incident'), '', '', true,
			$link);
	}
	
	print_table ($table);
}
?>
