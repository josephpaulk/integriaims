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

/**
 * Filter all the incidents.
 *
 * This function only return the incidents that can be accessed for the current
 * user with IR permission.
 *
 * @param array Key-value array of parameters to filter. It can handle this fields:
 *
 * string String to find in incident title.
 * status Status to search.
 * priority Priority to search.
 * id_group Incident group
 * id_product Incident affected product
 * id_company Incident affected company
 * id_inventory Incident affected inventory object
 * serial_number Incident affected inventory object's serial number
 * id_building Incident affected inventory object in a building
 * sla_fired Wheter the SLA was fired or not
 * id_incident_type Incident type
 * id_user Incident risponsable user
 * first_date Begin range date (range start)
 * last_date Begin range date (range end)
 *
 * @return array A list of matching incidents. False if no matches.
 */
function filter_incidents ($filters) {
	global $config;
	
	/* Set default values if none is set */
	$filters['string'] = isset ($filters['string']) ? $filters['string'] : '';
	$filters['status'] = isset ($filters['status']) ? $filters['status'] : 0;
	$filters['priority'] = isset ($filters['priority']) ? $filters['priority'] : -1;
	$filters['id_group'] = isset ($filters['id_group']) ? $filters['id_group'] : -1;
	$filters['id_product'] = isset ($filters['id_product']) ? $filters['id_product'] : 0;
	$filters['id_company'] = isset ($filters['id_company']) ? $filters['id_company'] : 0;
	$filters['id_inventory'] = isset ($filters['id_inventory']) ? $filters['id_inventory'] : 0;
	$filters['serial_number'] = isset ($filters['serial_number']) ? $filters['serial_number'] : '';
	$filters['id_building'] = isset ($filters['id_building']) ? $filters['id_building'] : 0;
	$filters['sla_fired'] = isset ($filters['sla_fired']) ? $filters['sla_fired'] : false;
	$filters['id_incident_type'] = isset ($filters['id_incident_type']) ? $filters['id_incident_type'] : 0;
	$filters['id_user'] = isset ($filters['id_user']) ? $filters['id_user'] : '';
	$filters['first_date'] = isset ($filters['first_date']) ? $filters['first_date'] : '';
	$filters['last_date'] = isset ($filters['last_date']) ? $filters['last_date'] : '';
	
	if ($filters['status'] == 0)
		$filters['status'] = implode (',', array_keys (get_indicent_status ()));
	
	$resolutions = get_incident_resolutions ();
	
	$sql_clause = '';
	if ($filters['priority'] != -1)
		$sql_clause .= sprintf (' AND prioridad = %d', $filters['priority']);
	if ($filters['id_group'] != 1)
		$sql_clause .= sprintf (' AND id_grupo = %d', $filters['id_group']);
	if ($filters['id_user'] != '0')
		$sql_clause .= sprintf (' AND id_usuario = "%s"', $filters['id_user']);
	if ($filters['id_incident_type'])
		$sql_clause .= sprintf (' AND id_incident_type = %d', $filters['id_incident_type']);
	if ($filters['first_date'] != '') {
		$time = strtotime ($filters['first_date']);
		$sql_clause .= sprintf (' AND inicio >= "%s"', date ("Y-m-d", $time));
	}
	if ($filters['last_date'] != '') {
		$time = strtotime ($filters['last_date']);
		$sql_clause .= sprintf (' AND inicio <= "%s"', date ("Y-m-d", $time));
	}

	$sql = sprintf ('SELECT * FROM tincidencia
			WHERE estado IN (%s)
			%s
			AND (titulo LIKE "%%%s%%" OR descripcion LIKE "%%%s%%")
			ORDER BY actualizacion desc
			LIMIT %d',
			$filters['status'], $sql_clause, $filters['string'], $filters['string'],
			$config['limit_size']);
	
	$incidents = get_db_all_rows_sql ($sql);
	if ($incidents === false)
		return false;
	
	$result = array ();
	foreach ($incidents as $incident) {
		if (! give_acl ($config['id_user'], $incident['id_grupo'], 'IR'))
			continue;
	
		$inventories = get_inventories_in_incident ($incident['id_incidencia'], false);
	
		/* Check aditional searching clauses */
		if ($filters['sla_fired'] && $incident['affected_sla_id'] == 0) {
			continue;
		}
	
		if ($filters['id_inventory']) {
			$found = false;
			foreach ($inventories as $inventory) {
				if ($inventory['id'] == $filters['id_inventory']) {
					$found = true;
					break;
				}
			}
		
			if (! $found)
				continue;
		}
	
		if ($filters['serial_number'] != '') {
			$found = false;
			foreach ($inventories as $inventory) {
				if (strcasecmp ($inventory['serial_number'], $filters['serial_number'])) {
					$found = true;
					break;
				}
			}
		
			if (! $found)
				continue;
		}
	
		if ($filters['id_building']) {
			$found = false;
			foreach ($inventories as $inventory) {
				if ($inventory['id_building'] == $filters['id_building']) {
					$found = true;
					break;
				}
			}
		
			if (! $found)
				continue;
		}
	
		if ($filters['id_product']) {
			$found = false;
			foreach ($inventories as $inventory) {
				if ($inventory['id_product'] == $filters['id_product']) {
					$found = true;
					break;
				}
			}
		
			if (! $found)
				continue;
		}
	
		if ($filters['id_company']) {
			$found = false;
			foreach ($inventories as $inventory) {
				$companies = get_inventory_affected_companies ($inventory['id'], false);
				foreach ($companies as $company) {
					if ($company['id'] == $filters['id_company']) {
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
		
		array_push ($result, $incident);
	}
	
	return $result;
}

/**
 * Print a table with statistics of a list of incidents.
 *
 * @param array List of incidents to get stats.
 * @param bool Whether to return an output string or echo now (optional, echo by default).
 *
 * @return Incidents stats if return parameter is true. Nothing otherwise
 */
function print_incidents_stats ($incidents, $return = false) {
	$output = '';

	$total = sizeof ($incidents);
	$opened = 0;
	$total_hours = 0;
	$total_lifetime = 0;
	$max_lifetime = 0;
	$oldest_incident = false;
	foreach ($incidents as $incident) {
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
	
	// Get incident SLA compliance
	$sla_compliance = get_sla_compliance ();

	$table->width = '40%';
	$table->class = 'float_left blank';
	$table->rowspan = array ();
	$table->rowspan[0][1] = 2;
	$table->colspan = array ();
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
	$table->data[3][0] = print_label (__('SLA compliance'), '', '', true,
		format_numeric ($sla_compliance) .' '.__('%'));
	
	if ($oldest_incident) {
		$link = '<a href="index.php?sec=incidents&sec2=operation/incidents/incident&id='.
			$oldest_incident['id_incidencia'].'">'.$oldest_incident['titulo'].'</a>';
		$table->data[3][1] = print_label (__('Longest closed incident'), '', '', true,
			$link);
	}
	
	$output .= print_table ($table, true);
	unset ($table);

	// Find the 5 most active users (more hours worked)
	$most_active_users = get_most_active_users (5);
	
	$users_label = '';
	foreach ($most_active_users as $user) {
		$users_label .= '<a href="index.php?sec=users&sec2=operation/users/user_edit&id='.
			$user['id_user'].'">'.$user['id_user']."</a> (".$user['worked_hours'].
			" ".__('Hr').") <br />";
	}

	// Find the 5 most active incidents (more worked hours)
	$most_active_incidents = get_most_active_incidents (5);
	$incidents_label = '';
	foreach ($most_active_incidents as $incident) {
		$incidents_label .= '<a class="incident_link" id="incident_link_'.
			$incident['id_incidencia'].'"
			href="index.php?sec=incidents&sec2=operation/incidents/incident&id='.$incident['id_incidencia'].'">'.
			$incident['titulo']."</a> (".$incident['worked_hours']." ".
			__('Hr').") <br />";
	}
	
	$table->width = '60%';
	$table->class = 'float_left blank';
	$table->style = array ();
	$table->style[0] = 'vertical-align: top; margin-right: 15px;';
	$table->style[1] = 'vertical-align: top';
	$table->data = array ();
	$table->data[0][0] = print_label (__('Most active users'), '', '', true, $users_label);
	$table->data[0][1] = print_label (__('Most active incidents'), '', '', true, $incidents_label);
	
	$output .= print_table ($table, true);
	
	if ($return)
		return $output;
	echo $output;
}

/**
 * Update affected inventory objects in an incident.
 *
 * @param int Incident id to update.
 * @param array List of affected inventory objects ids.
 */
function update_incident_inventories ($id_incident, $inventories) {
	error_reporting (0);
	$where_clause = '';
	
	if (empty ($inventories)) {
		$inventories = array (0);
	}
	$where_clause = sprintf ('AND id_inventory NOT IN (%s)',
		implode (',', $inventories));
	
	$sql = sprintf ('DELETE FROM tincident_inventory
		WHERE id_incident = %d %s',
		$id_incident, $where_clause);
	process_sql ($sql);
	foreach ($inventories as $id_inventory) {
		$sql = sprintf ('INSERT INTO tincident_inventory
			VALUES (%d, %d)',
			$id_incident, $id_inventory);
		$tmp = process_sql ($sql);
		if ($tmp !== false)
			incident_tracking ($id_inventory, INCIDENT_INVENTORY_ADDED,
				$id_inventory);
	}
}

?>
