<?php 
// INTEGRIA IMS v2.0
// http://www.integriaims.com
// ===========================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2008 Esteban Sanchez, estebans@artica.es
// Copyright (c) 2007-2011 Artica, info@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License
// (LGPL) as published by the Free Software Foundation; version 2

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.


/**
 * Filter all the incidents and return a list of matching elements.
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

// Avoid to mess AJAX with Javascript
if(defined ('AJAX')) {
	require_once ($config["homedir"]."/include/functions_graph.php");
}

include_once ($config["homedir"]."/include/graphs/fgraph.php");

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
	$filters['id_user_or_creator'] = isset ($filters['id_user_or_creator']) ? $filters['id_user_or_creator'] : '';
	$filters['first_date'] = isset ($filters['first_date']) ? $filters['first_date'] : '';
	$filters['last_date'] = isset ($filters['last_date']) ? $filters['last_date'] : '';
	
	if (empty ($filters['status']))
		$filters['status'] = implode (',', array_keys (get_indicent_status ()));
	
	// Not closed
	if ($filters["status"] == -10)
		$filters['status'] = "1,2,3,4,5";

	$resolutions = get_incident_resolutions ();
	
	$sql_clause = '';
	if ($filters['priority'] != -1)
		$sql_clause .= sprintf (' AND prioridad = %d', $filters['priority']);
	if ($filters['id_group'] != 1)
		$sql_clause .= sprintf (' AND id_grupo = %d', $filters['id_group']);
	if (! empty ($filters['id_user']))
		$sql_clause .= sprintf (' AND id_usuario = "%s"', $filters['id_user']);
	if (! empty ($filters['id_user_or_creator']))
		$sql_clause .= sprintf (' AND (id_usuario = "%s" OR id_creator = "%s")', $filters['id_user_or_creator'], $filters['id_user_or_creator']);
	if (! empty ($filters['id_incident_type']))
		$sql_clause .= sprintf (' AND id_incident_type = %d', $filters['id_incident_type']);
	if (! empty ($filters['first_date'])) {
		$time = strtotime ($filters['first_date']);
		$sql_clause .= sprintf (' AND inicio >= "%s"', date ("Y-m-d", $time));
	}
	if (! empty ($filters['last_date'])) {
		$time = strtotime ($filters['last_date']);
		if (! empty ($filters['first_date'])) {
			$sql_clause .= sprintf (' AND inicio <= "%s"', date ("Y-m-d", $time));
		} else {
			$time_from = strtotime ($filters['first_date']);
			if ($time_from < $time)
				$sql_clause .= sprintf (' AND inicio <= "%s"',
					date ("Y-m-d", $time));
		}
	}

	// Manage external users
	$return = enterprise_hook ('manage_external');
	if ($return !== ENTERPRISE_NOT_HOOK)
		$sql_clause .= $return;
	
	$sql = sprintf ('SELECT * FROM tincidencia
			WHERE estado IN (%s)
			%s
			AND (titulo LIKE "%%%s%%" OR descripcion LIKE "%%%s%%" OR id_creator LIKE "%%%s%%" OR id_usuario LIKE "%%%s%%")
			ORDER BY actualizacion DESC
			LIMIT %d',
			$filters['status'], $sql_clause, $filters['string'], $filters['string'], $filters['string'],$filters['string'],
			$config['limit_size']);

    // DEBUG
    // echo $sql ." <br>";
    
	$incidents = get_db_all_rows_sql ($sql);
	if ($incidents === false)
		return false;

	$result = array ();
	foreach ($incidents as $incident) {
		// ACL pass if IR for this group or if the user is the incident creator
		if (! give_acl ($config['id_user'], $incident['id_grupo'], 'IR')
			&& ($incident['id_creator'] != $config['id_user']) )
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
 * Copy and insert in database a new file into incident
 *
 * @param int incident id
 * @param string file full path
 * @param string file description
 *
 */
 
function attach_incident_file ($id, $file_temp, $file_description) {
	global $config;
	
	$filesize = filesize($file_temp); // In bytes
	$filename = basename($file_temp);

	$sql = sprintf ('INSERT INTO tattachment (id_incidencia, id_usuario,
			filename, description, size)
			VALUES (%d, "%s", "%s", "%s", %d)',
			$id, $config['id_user'], $filename, $file_description, $filesize);

	$id_attachment = process_sql ($sql, 'insert_id');
	
	incident_tracking ($id, INCIDENT_FILE_ADDED);
	
	$result_msg = ui_print_success_message(__('File added'), '', true);
	
	// Email notify to all people involved in this incident
	if ($email_notify == 1) {
		if ($config["email_on_incident_update"] == 1){
			mail_incident ($id, $config['id_user'], 0, 0, 2);
		}
	}
	
	// Copy file to directory and change name
	$file_target = $config["homedir"]."/attachment/".$id_attachment."_".$filename;
	
	if (! copy ($file_temp, $file_target)) {
		$result_msg = ui_print_success_message(__('File cannot be saved. Please contact Integria administrator about this error'), '', true);
		$sql = sprintf ('DELETE FROM tattachment
				WHERE id_attachment = %d', $id_attachment);
		process_sql ($sql);
	} else {
		// Delete temporal file
		unlink ($file_temp);

		// Adding a WU noticing about this
		$note = "Automatic WU: Added a file to this issue. Filename uploaded: ". $filename;
		$public = 1;
		$timeused = "0.05";
		
		add_workunit_incident($id, $note, $timeused, $public);
	}
	
	return $result_msg;
}

/**
 * Update the updatetime of a incident with the current timestamp
 *
 * @param int incident id
 *
 */
 
 function update_incident_updatetime($incident_id) {
		$sql = sprintf ('UPDATE tincidencia SET actualizacion = "%s" WHERE id_incidencia = %d', print_mysql_timestamp(), $incident_id);

		process_sql ($sql);
 }
 
 /**
 * Add a workunit to an incident
 *
 * @param int incident id
 * @param string note of the workunit
 * @param string timeused
 * @param string public
 * @param int incident id
 *
 */
 
function add_workunit_incident($incident_id, $note, $timeused, $public = 1) {
	global $config;
	
	$timestamp = print_mysql_timestamp();
	
	$sql = sprintf ('INSERT INTO tworkunit (timestamp, duration, id_user, description, public) VALUES ("%s", %.2f, "%s", "%s", %d)', $timestamp, $timeused, $config['id_user'], $note, $public);

	$id_workunit = process_sql ($sql, "insert_id");
	
	if($id_workunit === false) {
		return false;
	}
	
	$sql = sprintf ('INSERT INTO tworkunit_incident (id_incident, id_workunit) VALUES (%d, %d)', $incident_id, $id_workunit);
	
	
	$result = process_sql ($sql);
	
	if($result === false) {
		$sql = sprintf ('DELETE FROM tworkunit WHERE id = %d',$id_workunit);
		return false;
	}
	
	// Update the updatetime of the incident
	update_incident_updatetime($incident_id);
	
	return true;
}

/**
 * Return an array with the incidents with a filter
 *
 * @param array List of incidents to get stats.
 * @param array/string filter for the query
 * @param bool only names or all the incidents
 *

 */
 
function get_incidents ($filter = array(), $only_names = false) {
	$all_incidents = get_db_all_rows_filter('tincidencia',$filter,'*');

	if ($all_incidents == false)
		return array ();
	
	global $config;
	$incidents = array ();
	foreach ($all_incidents as $incident) {
		// ACL pass if IR for this group or if the user is the incident creator
		if (give_acl ($config['id_user'], $incident['id_grupo'], 'IR') 
			|| ($incident['id_creator'] == $config['id_user'])) {
				
			if ($only_names) {
				$incidents[$incident['id_incidencia']] = $incident['titulo'];
			} else {
				array_push ($incidents, $incident);
			}
		}
	}
	return $incidents;
}

/**
 * Return an array with the incident details, files and workunits
 *
 * @param array List of incidents to get stats.
 *
 */
 
function get_full_incident ($id_incident, $only_names = false) {
	$full_incident['details'] = get_db_row_filter('tincidencia',array('id_incidencia' => $id_incident),'*');
	$full_incident['files'] = get_incident_files ($id_incident, true);
	if($full_incident['files'] === false) {
		$full_incident['files'] = array();
	}
	$full_incident['workunits'] = get_incident_full_workunits ($id_incident);
	if($full_incident['workunits'] === false) {
		$full_incident['workunits'] = array();
	}
	
	return $full_incident;
}

/**
 * Return an array with the workunits (data included) of an incident
 *
 * @param array List of incidents to get stats.
 *
 */

function get_incident_full_workunits ($id_incident) {
	$workunits = get_db_all_rows_sql ("SELECT tworkunit.* FROM tworkunit, tworkunit_incident WHERE
		tworkunit.id = tworkunit_incident.id_workunit AND tworkunit_incident.id_incident = $id_incident
		ORDER BY id_workunit DESC");
	if ($workunits === false)
		return array ();
	return $workunits;
}

/**
 * Return an array with statistics of a given list of incidents.
 *
 * @param array List of incidents to get stats.
 #
 *

 */
function get_incidents_stats ($incidents) {
    global $config;

	$total = sizeof ($incidents);
	$opened = 0;
	$total_hours = 0;
	$total_lifetime = 0;
	$max_lifetime = 0;
	$oldest_incident = false;
    $scoring_sum = 0;
    $scoring_valid = 0;

	if ($incidents === false)
		$incidents = array ();
	foreach ($incidents as $incident) {
		if ($incident['estado'] != 6 && $incident['estado'] != 7) {
			$opened++;
		} elseif ($incident['actualizacion'] != '0000-00-00 00:00:00') {
			$lifetime = get_db_value ('UNIX_TIMESTAMP(actualizacion)  - UNIX_TIMESTAMP(inicio)',
				'tincidencia', 'id_incidencia', $incident['id_incidencia']);
			if ($lifetime > $max_lifetime) {
				$oldest_incident = $incident;
				$max_lifetime = $lifetime;
			}
			$total_lifetime += $lifetime;
		}

        // Scoring avg.
        if ($incident["score"] > 0){
            $scoring_valid++;
            $scoring_sum = $scoring_sum + $incident["score"];
        }          
		$hours = get_incident_workunit_hours  ($incident['id_incidencia']);
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
	
    // Get avg. scoring
    if ($scoring_valid > 0){
        $scoring_avg = $scoring_sum / $scoring_valid;
    } else 
        $scoring_avg = "N/A";

	// Get incident SLA compliance
	$sla_compliance = get_sla_compliance ($incidents);

    $data = array();

    $data ["total_incidents"] = $total;
    $data ["opened"] = $opened;
    $data ["closed"] = $total - $opened;
    $data ["avg_life"] = $mean_lifetime;
    $data ["avg_worktime"] = $mean_work;
    $data ["sla_compliance"] = $sla_compliance;
    $data ["avg_scoring"] = $scoring_avg;

    return $data;
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

    global $config;
    
	require_once ($config["homedir"]."/include/functions_graph.php");    
    
	$pdf_output = (int)get_parameter('pdf_output', 0);
	$ttl = $pdf_output+1;
	
	// Necessary for flash graphs
	include_flash_chart_script();

	// TODO: Move this function to function_graphs to encapsulate flash
	// chart script inclusion or make calls to functions_graph when want 
	// print a flash chart	

	$output = '';
	
	$total = sizeof ($incidents);
	$opened = 0;
	$total_hours = 0;
	$total_lifetime = 0;
	$max_lifetime = 0;
	$oldest_incident = false;
	$scoring_sum = 0;
	$scoring_valid = 0;

	if ($incidents === false)
		$incidents = array ();
	foreach ($incidents as $incident) {
		
		if ($incident['estado'] != 6 && $incident['estado'] != 7) {
			$opened++;
		} elseif ($incident['actualizacion'] != '0000-00-00 00:00:00') {
			$lifetime = get_db_value ('UNIX_TIMESTAMP(actualizacion)  - UNIX_TIMESTAMP(inicio)',
				'tincidencia', 'id_incidencia', $incident['id_incidencia']);

			if ($lifetime > $max_lifetime) {
				$oldest_incident = $incident;
				$max_lifetime = $lifetime;
			}
			$total_lifetime += $lifetime;
		}

        	// Scoring avg.
	        if ($incident["score"] > 0){
	            $scoring_valid++;
	            $scoring_sum = $scoring_sum + $incident["score"];
	        }
            
		$hours = get_incident_workunit_hours  ($incident['id_incidencia']);
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
		$mean_lifetime = (int) ($total_lifetime / $closed);
	}
	
    // Get avg. scoring
    if ($scoring_valid > 0){
        $scoring_avg = format_numeric($scoring_sum / $scoring_valid);
    } else 
        $scoring_avg = "N/A";

	// Get incident SLA compliance
	$sla_compliance = get_sla_compliance ($incidents);
		
	//Create second table
    
	//Get only id from incident filter array
	//used for filter in some functions
	$incident_id_array = array();
	
	foreach ($incidents as $inc) {
		array_push($incident_id_array, $inc['id_incidencia']);
	}
	
	// Find the 5 most active users (more hours worked)
	$most_active_users = get_most_active_users (5, $incident_id_array);
	
	$users_label = '';
	foreach ($most_active_users as $user) {
		$users_data[$user['id_user']] = $user['worked_hours'];
		$users_label .= '<a href="index.php?sec=users&sec2=operation/users/user_edit&id='.
			$user['id_user'].'">'.$user['id_user']."</a> (".$user['worked_hours'].
			" ".__('Hr').") <br />";
	}
	
	if(empty($most_active_users)) {
		$users_label = graphic_error(false);
		$users_label .= "<br/>N/A";
	}
	else {
		$users_label .= "<br/>".pie3d_graph ($config['flash_charts'], $users_data, 300, 150, __('others'), "", "", $config['font'], $config['fontsize'], $ttl);
	}
	
	// Find the 5 most active incidents (more worked hours)
	$most_active_incidents = get_most_active_incidents (5, $incident_id_array);
	$incidents_label = '';
	foreach ($most_active_incidents as $incident) {
		$incidents_data['#'.$incident['id_incidencia']] = $incident['worked_hours'];
		$incidents_label .= '<a class="incident_link" id="incident_link_'.
			$incident['id_incidencia'].'"
			href="index.php?sec=incidents&sec2=operation/incidents/incident&id='.$incident['id_incidencia'].'">'.
			'#'.$incident['id_incidencia'].': '.$incident['titulo']."</a> (".$incident['worked_hours']." ".
			__('Hr').") <br />";
	}
	
	if(empty($most_active_incidents)) {
		$incidents_label = graphic_error(false);
		$incidents_label .= "<br/>N/A";
	}
	else {
		$incidents_label .= "<br/>".pie3d_graph ($config['flash_charts'], $incidents_data, 300, 150, __('others'), "", "", $config['font'], $config['fontsize'], $ttl);
	}
	
	$submitter_label = "";
	$top5_submitters = get_most_incident_creators(5, $incident_id_array);
	foreach ($top5_submitters as $submitter){
		$submitter_data[$submitter["id_creator"]] = $submitter["total"];
		$submitter_label .= $submitter["id_creator"]." ( ".$submitter["total"]. " )<br>";
	}
	
	if(empty($top5_submitters)) {
		$submitter_label = graphic_error(false);
		$submitter_label .= "<br/>N/A";
	}
	else {
		$submitter_label .= "<br/>".pie3d_graph ($config['flash_charts'], $submitter_data, 300, 150, __('others'), "", "", $config['font'], $config['fontsize'], $ttl);
	}
	
	$scoring_label ="";
	$top5_scoring = get_best_incident_scoring (5, $incident_id_array);
	
	foreach ($top5_scoring as $submitter){
		$scoring_data[$submitter["id_usuario"]] = $submitter["total"];
		$scoring_label .= $submitter["id_usuario"]." ( ".$submitter["total"]. " )<br>";
	}
	
	if(empty($top5_scoring)) {
		$scoring_label .= "<br/>N/A";
	}
	else {
		$scoring_label .= "<br/>".pie3d_graph ($config['flash_charts'], $scoring_data, 300, 150, __('others'), "", "", $config['font'], $config['fontsize'], $ttl);
	}
	
	$most_user_assigned = get_most_users_assigned(5, $incident_id_array);
	
	foreach ($most_user_assigned as $submitter){
		$user_assigned_data[$submitter["id_usuario"]] = $submitter["total"];
		$user_assigned_label .= $submitter["id_usuario"]." ( ".$submitter["total"]. " )<br>";
	}
	
	if(empty($most_user_assigned)) {
		$user_assigned_label .= "<br/>N/A";
	}
	else {
		$user_assigned_label .= "<br/>".pie3d_graph ($config['flash_charts'], $user_assigned_data, 300, 150, __('others'), "", "", $config['font'], $config['fontsize'], $ttl);
	}
	
	// Show graph with incident priorities
	foreach ($incidents as $incident) {
		if (!isset( $incident_data[render_priority($incident["prioridad"])]))
			 $incident_data[render_priority($incident["prioridad"])] = 0;

		$incident_data[render_priority($incident["prioridad"])] = $incident_data[render_priority($incident["prioridad"])] + 1; 
	}

	// Show graph with incidents by group
	foreach ($incidents as $incident) {
			$grupo = substr(safe_output(dame_grupo($incident["id_grupo"])),0,15);

	if (!isset( $incident_group_data[$grupo]))
			 $incident_group_data[$grupo] = 0;

		$incident_group_data[$grupo] = $incident_group_data[$grupo] + 1;                                 

	}
	
	//Print first table	
	echo "<h3>".__("Incident statistics")."</h3>";

    $output = "<table class=blank width=80% cellspacing=4 cellpadding=0 border=0 >";
    $output .= "<tr>";
    $output .= "<td colspan=2 valign=top align=center>";
		$output .= "<table class=listing width=80% border=1 cellspacing=4 cellpadding=0 border=0 >";
		$output .= "<tr>";
		$output .= "<th align=center>".__('Total incidents')."</th>";
		$output .= "<th align=center>".__('Avg. life time')."</th>";
		$output .= "</tr><tr>";
		$output .= "<td  align=center>";
		$output .= $total;
		$output .= "</td><td align=center>";
		$output .= give_human_time ($mean_lifetime);
		$output .= "</td>";
		$output .= "<tr>";
		$output .= "<th align=center>";
		$output .= __('Avg. work time');
		$output .= "</th>";
		$output .= "<th align=center>";
		$output .= __('Avg. Scoring');
		$output .= "</th>";
		$output .= "</tr><tr>";
		$output .= "<tr>";
		$output .= "<td align=center>".$mean_work.' '.__('Hours')."</td>";
		$output .= "<td align=center>".$scoring_avg."</td>";
		$output .= "</tr></table>";
	$output .= "</td>";
    $output .= "<td valign=top>";
    $output .= print_label (__('Top 5 active incidents'), '', '', true, $incidents_label);
    $output .= "</td></tr>";
    $output .= "<tr>";
	$output .= "<td valign=top>";
	$data = array (__('Open') => $opened, __('Closed') => $total - $opened);
    $output .= print_label (__('Open'), '', '', true, $opened.' ('.$opened_pct.'%)');
    $output .= pie3d_graph ($config['flash_charts'], $data, 300, 150, __('others'), "", "", $config['font'], $config['fontsize'], $ttl);
    $output .= "</td>";    
	$output .= "<td valign=top>";
	$output .= print_label (__('SLA compliance'), '', '', true, format_numeric ($sla_compliance) .' '.__('%'));
    $output .= graph_incident_statistics_sla_compliance($incidents, 300, 150, $ttl);    
	$output .= "</td>";
	$output .= "<td valign=top>";
	$output .= print_label (__('Incidents by priority'), '', '', true);
	$output .= "<br/>".pie3d_graph ($config['flash_charts'], $incident_data, 300, 150, __('others'), "", "", $config['font'], $config['fontsize'], $ttl);
	$output .= "</td>";  
    $output .= "</tr></table>";
	echo $output;	
	
	//Print second table
    echo "<h3>".__("User statistics")."</h3>";
    
    $output = "<table class=blank width=80% cellspacing=4 cellpadding=0 border=0 >";
    $output .= "<tr>";	
	$output .= "<td valign=top>";
	$output .= print_label (__('Top 5 active users'), '', '', true, $users_label);
	$output .= "</td>";
	$output .= "<td>";
	$output .= print_label (__('Top 5 user with open tickets'), '', '', true, $user_assigned_label);	
	$output .= "</td>";
	$output .= "<td valign=top>";
	$output .= print_label (__('Top 5 average scoring by user'), '', '', true, $scoring_label);
	$output .= "</td></tr>";
	$output .= "<tr><td valign=top>";
	$output .= print_label (__('Top 5 incident submitters'), '', '', true, $submitter_label );
	$output .= "</td><td valign=top>";
	$output .= print_label (__('Incidents by group'), '', '', true);
	$output .= "<br/>".pie3d_graph ($config['flash_charts'], $incident_group_data, 300, 150, __('others'), "", "", $config['font'], $config['fontsize']-1, $ttl);
	$output .= "</td>";
	
	if ($oldest_incident) {

        $oldest_incident_time = get_incident_workunit_hours  ($oldest_incident["id_incidencia"]);
		$link = '<a href="index.php?sec=incidents&sec2=operation/incidents/incident&id='.
			$oldest_incident['id_incidencia'].'">Incident #'.$oldest_incident['id_incidencia']. " : ".$oldest_incident['titulo']. "</a>";
            $output .= "<td valign=top>";
            $output .= print_label (__('Longest closed incident'), '', '', true,
			$link);
            $output .= "<br>".__("Worktime hours"). " : ".$oldest_incident_time;
            $output .= "<br>".__("Lifetime"). " : ".give_human_time($max_lifetime);
            $output .= "</td>";
	}
	
	$output .= "</tr></table>";
	
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
			incident_tracking ($id_incident, INCIDENT_INVENTORY_ADDED,
				$id_inventory);
	}
}

/**
 * Update contact reporters in an incident.
 *
 * @param int Incident id to update.
 * @param array List of contacts ids.
 */
function update_incident_contact_reporters ($id_incident, $contacts) {
	error_reporting (0);
	$where_clause = '';
	
	if (empty ($contacts)) {
		$contacts = array (0);
	}
	$where_clause = sprintf ('AND id_contact NOT IN (%s)',
		implode (',', $contacts));
	
	$sql = sprintf ('DELETE FROM tincident_contact_reporters
		WHERE id_incident = %d %s',
		$id_incident, $where_clause);
	process_sql ($sql);
	foreach ($contacts as $id_contact) {
		$sql = sprintf ('INSERT INTO tincident_contact_reporters
			VALUES (%d, %d)',
			$id_incident, $id_contact);
		$tmp = process_sql ($sql);
		if ($tmp !== false)
			incident_tracking ($id_incident, INCIDENT_CONTACT_ADDED,
				$id_contact);
	}
}

/**
 * Get all the contacts who reported a incident
 *
 * @param int Incident id.
 * @param bool Wheter to return only the contact names (indexed by id) or all
 * the data.
 *
 * @return array An array with all the contacts who reported the incident. Empty
 * array if none was set.
 */
function get_incident_contact_reporters ($id_incident, $only_names = false) {
	$sql = sprintf ('SELECT tcompany_contact.*
		FROM tcompany_contact, tincident_contact_reporters
		WHERE tcompany_contact.id = tincident_contact_reporters.id_contact
		AND id_incident = %d', $id_incident);
	$contacts = get_db_all_rows_sql ($sql);
	if ($contacts === false)
		return array ();
	
	if ($only_names) {
		$retval = array ();
		foreach ($contacts as $contact) {
			$retval[$contact['id']] = $contact['fullname'];
		}
		return $retval;
	}
	
	return $contacts;
}


/**
* Return total hours assigned to incident
*
* $id_inc       integer         ID of incident
**/

function get_incident_workunit_hours ($id_incident) {
        global $config;
        $sql = sprintf ('SELECT SUM(tworkunit.duration) 
                        FROM tworkunit, tworkunit_incident, tincidencia 
                        WHERE tworkunit_incident.id_incident = tincidencia.id_incidencia
                        AND tworkunit_incident.id_workunit = tworkunit.id
                        AND tincidencia.id_incidencia = %d', $id_incident);

        return (float) get_db_sql ($sql);
}


/**
 * Return the last entered WU in a given incident
 *
 * @param int Incident id
 *
 * @return array WU structure
 */

function get_incident_lastworkunit ($id_incident) {
        $workunits = get_incident_workunits ($id_incident);
        if (!isset($workunits[0]['id_workunit']))
            return;
        $workunit_data = get_workunit_data ($workunits[0]['id_workunit']);
        return $workunit_data;
}


function mail_incident ($id_inc, $id_usuario, $nota, $timeused, $mode, $public = 1){
	global $config;

	$row = get_db_row ("tincidencia", "id_incidencia", $id_inc);
	$group_name = get_db_sql ("SELECT nombre FROM tgrupo WHERE id_grupo = ".$row["id_grupo"]);
	$titulo =$row["titulo"];
	$description = wordwrap(ascii_output($row["descripcion"]), 70, "\n");
	$prioridad = render_priority($row["prioridad"]);
	$nota = wordwrap($nota, 75, "\n");

	$estado = render_status ( $row["estado"]);
	$resolution = render_resolution ($row["resolution"]);
	$create_timestamp = $row["inicio"];
	$update_timestamp = $row["actualizacion"];
	$usuario = $row["id_usuario"];
	$creator = $row["id_creator"];
    $email_copy = $row["email_copy"];

	// Send email for owner and creator of this incident
	$email_creator = get_user_email ($creator);
	$company_creator = get_user_company ($creator, true);
	if(empty($company_creator)) {
		$company_creator = "";
	}
	else {
		$company_creator = " (".reset($company_creator).")";
	}
	
	$email_owner = get_user_email ($usuario);
	$company_owner = get_user_company ($usuario, true);
	if(empty($company_owner)) {
		$company_owner = "";
	}
	else {
		$company_owner = " (".reset($company_owner).")";
	}
  
	$MACROS["_sitename_"] = $config["sitename"];
	$MACROS["_fullname_"] = dame_nombre_real ($usuario);
	$MACROS["_username_"] = $usuario;
	$MACROS["_incident_id_"] = $id_inc;
	$MACROS["_incident_title_"] = $titulo;
	$MACROS["_creation_timestamp_"] = $create_timestamp;
	$MACROS["_update_timestamp_"] = $update_timestamp;
	$MACROS["_group_"] = $group_name ;
	$MACROS["_author_"] = dame_nombre_real ($creator).$company_creator;
	$MACROS["_owner_"] = dame_nombre_real ($usuario).$company_owner;
	$MACROS["_priority_"] = $prioridad ;
	$MACROS["_status_"] = $estado;
	$MACROS["_resolution_"] = $resolution;
	$MACROS["_time_used_"] = $timeused;
	$MACROS["_incident_main_text_"] = $description;
	$MACROS["_access_url_"] = $config["base_url"]."/index.php?sec=incidents&sec2=operation/incidents/incident&id=$id_inc";

	// Resolve code for its name
	switch ($mode){
	case 10: // Add Workunit
		//$subject = "[".$config["sitename"]."] Incident #$id_inc ($titulo) has a new workunit from [$id_usuario]";
		$company_wu = get_user_company ($id_usuario, true);
		if(empty($company_wu)) {
			$company_wu = "";
		}
		else {
			$company_wu = " (".reset($company_wu).")";
		}
		$MACROS["_wu_user_"] = dame_nombre_real ($id_usuario).$company_wu;
		$MACROS["_wu_text_"] = $nota ;
		$text = template_process ($config["homedir"]."/include/mailtemplates/incident_update_wu.tpl", $MACROS);
		$subject = template_process ($config["homedir"]."/include/mailtemplates/incident_subject_new_wu.tpl", $MACROS);
		break;
	case 0: // Incident update
		$text = template_process ($config["homedir"]."/include/mailtemplates/incident_update.tpl", $MACROS);
		$subject = template_process ($config["homedir"]."/include/mailtemplates/incident_subject_update.tpl", $MACROS);
		break;
	case 1: // Incident creation
		$text = template_process ($config["homedir"]."/include/mailtemplates/incident_create.tpl", $MACROS);
		$subject = template_process ($config["homedir"]."/include/mailtemplates/incident_subject_create.tpl", $MACROS);
		break;
	case 2: // New attach
		$text = template_process ($config["homedir"]."/include/mailtemplates/incident_update.tpl", $MACROS);
		$subject = template_process ($config["homedir"]."/include/mailtemplates/incident_subject_attach.tpl", $MACROS);
		break;
	case 3: // Incident deleted 
		$text = template_process ($config["homedir"]."/include/mailtemplates/incident_update.tpl", $MACROS);
		$subject = template_process ($config["homedir"]."/include/mailtemplates/incident_subject_delete.tpl", $MACROS);
		break;
    case 5: // Incident closed
		$text = template_process ($config["homedir"]."/include/mailtemplates/incident_close.tpl", $MACROS);
		$subject = template_process ($config["homedir"]."/include/mailtemplates/incident_subject_close.tpl", $MACROS);
        break;
   }
		
		
	// Create the TicketID for have a secure reference to incident hidden 
	// in the message. Will be used for POP automatic processing to add workunits
	// to the incident automatically.

	$msg_code = "TicketID#$id_inc";
	$msg_code .= "/".substr(md5($id_inc . $config["smtp_pass"] . $row["id_usuario"]),0,5);
	$msg_code .= "/" . $row["id_usuario"];;

	integria_sendmail ($email_owner, $subject, $text, false, $msg_code);

    // Send a copy to each address in "email_copy"

    if ($email_copy != ""){
        $emails = explode (",",$email_copy);
        foreach ($emails as $em){
        	integria_sendmail ($em, $subject, $text, false, "");
        }
    }

	// Incident owner
	if ($email_owner != $email_creator){

    	$msg_code = "TicketID#$id_inc";
	$msg_code .= "/".substr(md5($id_inc . $config["smtp_pass"] . $row["id_creator"]),0,5);
    	$msg_code .= "/".$row["id_creator"];

	integria_sendmail ($email_creator, $subject, $text, false, $msg_code);
    }	
	if ($public == 1){
		// Send email for all users with workunits for this incident
		$sql1 = "SELECT DISTINCT(tusuario.direccion), tusuario.id_usuario FROM tusuario, tworkunit, tworkunit_incident WHERE tworkunit_incident.id_incident = $id_inc AND tworkunit_incident.id_workunit = tworkunit.id AND tworkunit.id_user = tusuario.id_usuario";
		if ($result=mysql_query($sql1)) {
			while ($row=mysql_fetch_array($result)){
				if (($row[0] != $email_owner) AND ($row[0] != $email_creator)){
                    
                    $msg_code = "TicketID#$id_inc";
            	    $msg_code .= "/".substr(md5($id_inc . $config["smtp_pass"] .  $row[1]),0,5);
                	$msg_code .= "/". $row[1];

					integria_sendmail ( $row[0], $subject, $text, false, $msg_code);
                }
			}
		}

        // Send email to incident reporters associated to this incident
        if ($config['incident_reporter'] == 1){
        	$contacts = get_incident_contact_reporters ($id_inc , true);
			if ($contats)
            foreach ($contacts as $contact) {
                $contact_email = get_db_sql ("SELECT email FROM tcompany_contact WHERE fullname = '$contact'");
                integria_sendmail ($contact_email, $subject, $text, false, $msg_code);
            }
	    }
    }
}

function people_involved_incident ($id_inc){
	global $config;
	$row0 = get_db_row ("tincidencia", "id_incidencia", $id_inc);
	$people = array();

	array_push ($people, $row0["id_creator"]);
	 if (!in_array($row0["id_usuario"], $people)) {	
		array_push ($people, $row0["id_usuario"]);
	}
 
	// Take all users with workunits for this incident
	$sql1 = "SELECT DISTINCT(tusuario.id_usuario) FROM tusuario, tworkunit, tworkunit_incident WHERE tworkunit_incident.id_incident = $id_inc AND tworkunit_incident.id_workunit = tworkunit.id AND tworkunit.id_user = tusuario.id_usuario";
	if ($result=mysql_query($sql1)) {
		while ($row=mysql_fetch_array($result)){
			if (!in_array($row[0], $people))
				array_push ($people, $row[0]);
		}
	}

	return $people;
}

// Return TRUE if User has access to that incident

function user_belong_incident ($user, $id_inc) {
    return in_array($user, people_involved_incident ($id_inc));
}


/** 
 * Returns the n top creator users (users who create a new incident).
 *
 * @param lim n, number of users to return.
 */
function get_most_incident_creators ($lim, $incident_filter = false) {
	
	
	$sql = 'SELECT id_creator, count(*) AS total FROM tincidencia ';
	
	if ($incident_filter) {
		$filter_clause = join(",", $incident_filter);
		$sql .= ' WHERE id_incidencia IN ('.$filter_clause.') ';
	}
	
	$sql .= ' GROUP by id_creator ORDER BY total DESC LIMIT '. $lim;
	
	$most_creators = get_db_all_rows_sql ($sql);
	if ($most_creators === false) {
		return array ();
	}

	return $most_creators;
}

/** 
 * Returns the n top incident owner by scoring (users with best scoring).
 *
 * @param lim n, number of users to return.
 */
function get_best_incident_scoring ($lim, $incident_filter=false) {
	
	
	$sql = 'SELECT id_usuario, AVG(score) AS total FROM tincidencia';

	$filter_clause = '';	
		
	if ($incident_filter) {
		
		$filter_clause = join(",", $incident_filter);
		$sql .= ' WHERE id_incidencia IN ('.$filter_clause.')';
	}
	
	$sql .= ' GROUP by id_usuario ORDER BY total DESC LIMIT '. $lim;

	$most_creators = get_db_all_rows_sql ($sql);

	$all_zero = true;
	
	foreach ($most_creators as $mc) {
		if ($mc['total'] != 0) {
			$all_zero = false;
			break;
		}
	}
	
	if ($most_creators === false || $all_zero) {
		
		return array ();
	}

	return $most_creators;
}

?>
