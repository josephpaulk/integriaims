<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2011 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.


function check_workunit_permission ($id_workunit) {
	global $config;
	
	// Delete workunit with ACL / Project manager check
	$workunit = get_db_row ('tworkunit', 'id', $id_workunit);
	if ($workunit === false)
		return false;
	
	$id_user = $workunit["id_user"];
	$id_task = get_db_value ("id_task", "tworkunit_task", "id_workunit", $workunit["id"]);
	$id_project = get_db_value ("id_project", "ttask", "id", $id_task);
	if ($id_user != $config["id_user"]
		&& ! give_acl ($config["id_user"], 0,"PM")
		&& ! project_manager_check ($id_project))
		return false;
	
	return true;
}

function delete_task_workunit ($id_workunit) {
	global $config;
	
	if (! check_workunit_permission ($id_workunit))
		return false;
	
	$sql = sprintf ('DELETE FROM tworkunit
		WHERE id = %d', $id_workunit);
	process_sql ($sql);
	$sql = sprintf ('DELETE FROM tworkunit_task
		WHERE id_workunit = %d', $id_workunit);
	return (bool) process_sql ($sql);
}

function lock_task_workunit ($id_workunit) {
	global $config;
	
	if (! check_workunit_permission ($id_workunit))
		return false;
	$sql = sprintf ('UPDATE tworkunit SET locked = "%s" WHERE id = %d',
		$config['id_user'], $id_workunit);
	return (bool) process_sql ($sql);
}

function create_workunit ($incident_id, $wu_text, $user, $timeused = 0, $have_cost = 0, $profile = "", $public = 1, $send_email = 1) {
	$fecha = print_mysql_timestamp();
	$sql = sprintf ('UPDATE tincidencia
		SET affected_sla_id = 0, actualizacion = "%s"  
		WHERE id_incidencia = %d', $fecha, $incident_id);
	process_sql ($sql);
	
	incident_tracking ($incident_id, INCIDENT_WORKUNIT_ADDED);
	
	// Add work unit if enabled
	$sql = sprintf ('INSERT INTO tworkunit (timestamp, duration, id_user, description, public)
			VALUES ("%s", %.2f, "%s", "%s", %d)', $fecha, $timeused, $user, $wu_text, $public);
	$id_workunit = process_sql ($sql, "insert_id");
	$sql = sprintf ('INSERT INTO tworkunit_incident (id_incident, id_workunit)
			VALUES (%d, %d)',
			$incident_id, $id_workunit);
	$res = process_sql ($sql);
	
	if ($res !== false) {
		// Email notify to all people involved in this incident
		$email_notify = get_db_value ("notify_email", "tincidencia", "id_incidencia", $incident_id);
		if (($email_notify == 1) AND ($send_email == 1)) {
			mail_incident ($incident_id, $user, $wu_text, $timeused, 10, $public);
		}
	} else {
		//Delete workunit
		$sql = sprintf ('DELETE FROM tworkunit WHERE id = %d',$id_workunit);
		return false;
	}
	
	return true;
}

function create_new_table_multiworkunit ($number=false) {
	global $config;
	
	//If not number return empty
	if (!$number) {
		return;
	}
	
	//Set several global variables
	$now = date ("Y-m-d H:i:s");
	$start_date = substr ($now, 0, 10);
	$wu_user = $config["id_user"];	
	
	echo "<table id='wu_".$number."' class='databox' width='90%'>";
	
	echo "<tr>";
	
	echo "<td colspan=4>";
	echo "<strong>".sprintf(__("Workunit  #%d"),$number)."</strong>";
	echo "</td>";
	
	//If number greater than 1 display a cross to delete workunit
	echo "<td id='del_wu_".$number."' style='text-align:right; padding-right: 10px'>";
	if ($number > 1) {
		echo "<a id='del_wu_".$number."' href='#'><img src='images/cross.png'></a>";
	} else {
		echo "&nbsp;";
	}
	echo "</td>";
	echo "</tr>";
	
	echo "<tr>";
		
	// Show task combo if none was given.
	echo "<td>";	
	
	echo print_input_text ('start_date_'.$number, $start_date, '', 10, 20, true, __("Date"));
	echo"</td>";
	
	echo "<td colspan='2'>";
	echo combo_task_user_participant ($wu_user,true, 0, true, __("Task"), 'id_task_'.$number);
	echo "</td>";
	
	echo "<td>";
	echo combo_roles (true, 'id_profile_'.$number, __("Role"), true);
	echo "</td>";
	
	echo "<td>";
	if (dame_admin ($config['id_user'])) {
	
		$src_code = print_image('images/group.png', true, false, true);
		echo print_input_text_extended ('id_username_'.$number, $wu_user, 'text-id_username', '', 15, 30, false, '',
			array('style' => 'background: url(' . $src_code . ') no-repeat right;'), true, '',__('Username'))
		. print_help_tip (__("Type at least two characters to search"), true);
	}	
	echo "</td>";
	
	echo "</tr>";
	
	echo "<tr>";
	
	echo "<td>";
	echo print_input_text ('duration_'.$number, 4, false, 7, 7, true, __('Time used'));
	echo "</td>";
	
	echo "<td>";
	echo print_checkbox ('have_cost_'.$number, 1, false, true, __('Have cost'));
	echo "</td>";
	
	echo "<td>";
	echo print_checkbox ('public_'.$number, 1, true, true, __('Public'));
	echo "</td>";
	
	echo "<td>";
	echo print_checkbox ('forward_'.$number, 1, false, true, __('Forward'));
	print_help_tip (__('If this checkbox is activated, propagation will be forward instead backward'), true);
	echo "</td>";
 
	echo "<td>";
	echo print_checkbox ('split_'.$number, 1, false, true, __('Split > 1day'));
	echo print_help_tip (__('If workunit added is superior to 8 hours, it will be propagated to previous workday and deduced from the total, until deplete total hours assigned'), true);	
	echo "</td>";
	
	echo "</tr>";
	
	echo "<tr>";
	echo "<td colspan=5>";
	echo print_textarea ('description_'.$number, 4, 30, false, '', true, __('Description'));
	echo "</td>";
	echo "</tr>";
		
	echo "</table>";
}

function create_single_workunit ($number) {
	global $config;
	
	$duration = (float) get_parameter ("duration_".$number);
	$timestamp = (string) get_parameter ("start_date_".$number);
	$description = (string) get_parameter ("description_".$number);
	$have_cost = (bool) get_parameter ("have_cost_".$number);
	$id_task = (int) get_parameter ("id_task_".$number, -1);
	$id_profile = (int) get_parameter ("id_profile_".$number);
	$public = (bool) get_parameter ("public_".$number);
	$split = (bool) get_parameter ("split_".$number);
	$id_user = (string) get_parameter ("id_username_".$number, $config['id_user']);
	$wu_user = $id_user;
	$forward = (bool) get_parameter ("forward_".$number);	
	// Multi-day assigment
	if ($split && $duration > $config["hours_perday"]) {

		$total_days = ceil ($duration / $config["hours_perday"]);
		$total_days_sum = 0;
		$hours_day = 0;
		for ($i = 0; $i < $total_days; $i++) {
			if (! $forward)
				$current_timestamp = calcdate_business_prev ($timestamp, $i);
			else
				$current_timestamp = calcdate_business ($timestamp, $i);
			
			if (($total_days_sum + 8) > $duration)
				$hours_day = $duration - $total_days_sum;
			else 
				$hours_day = $config["hours_perday"];
			$total_days_sum += $hours_day;
			
			$sql = sprintf ('INSERT INTO tworkunit 
				(timestamp, duration, id_user, description, have_cost, id_profile, public) 
				VALUES ("%s", %f, "%s", "%s", %d, %d, %d)',
				$current_timestamp, $hours_day, $id_user, $description,
				$have_cost, $id_profile, $public);
			$id_workunit = process_sql ($sql, 'insert_id');
			if ($id_workunit !== false) {
				$sql = sprintf ('INSERT INTO tworkunit_task 
					(id_task, id_workunit) VALUES (%d, %d)',
					$id_task, $id_workunit);
				$result = process_sql ($sql, 'insert_id');
				if ($result !== false) {
					$result_output = true;
				} else {
					$result_output = false;
				}
			}
			else {
				$result_output = false;
			}
		}
		mail_project (0, $config['id_user'], $id_workunit, $id_task,
			"This is part of a multi-workunit assigment of $duration hours");
	} else {
		// Single day workunit
		$sql = sprintf ('INSERT INTO tworkunit 
				(timestamp, duration, id_user, description, have_cost, id_profile, public) 
				VALUES ("%s", %.2f, "%s", "%s", %d, %d, %d)',
				$timestamp, $duration, $id_user, $description,
				$have_cost, $id_profile, $public);
		$id_workunit = process_sql ($sql, 'insert_id');
		if ($id_workunit !== false) {
			$sql = sprintf ('INSERT INTO tworkunit_task 
					(id_task, id_workunit) VALUES (%d, %d)',
					$id_task, $id_workunit);
			$result = process_sql ($sql, 'insert_id');
			if ($result !== false) {
				$result_output = true;
				audit_db ($config['id_user'], $config["REMOTE_ADDR"], "Spare work unit added", 
						'Workunit for '.$config['id_user'].' added to Task ID #'.$id_task);
				mail_project (0, $config['id_user'], $id_workunit, $id_task);
			}
			else {
				$result_output = false;
			}
		} else {
			$result_output = false;
		}
	}
	
	if ($id_workunit !== false) {
		set_task_completion ($id_task);
	}
	
	insert_event ("PWU INSERT", $id_task, 0, $description);
	
	$return = array("task" => $id_task,
					"date" => $timestamp,
					"role" => $id_profile,
					"cost" => $have_cost,
					"public" => $public,
					"user" => $id_user,
					"duration" => $duration,
					"split" => $split,
					"forward" => $forward,
					"description" => $description,
					"result_out" => $result_output);
	
	return $return;
}

function print_single_workunit_report ($mwur) {
	if ($mwur['result_out']) {
		echo '<h3 class="suc">'.__('Workunit added').'</h3>';

	} else {
		echo '<h3 class="error">'.__('Problemd adding workunit.').'</h3>';
	}

	echo "<table class='databox' width='90%'>";
	echo "<tr>";
	echo "<td>";
	echo "<strong>".sprintf(__("Workunit  #%d"),$mwur['id'])."</strong>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td>";
	echo "<strong>".__("Date").": </strong>";
	echo $mwur['date'];
	echo "</td>";
	echo "<td colspan='2'>";
	echo "<strong>".__("Task").": </strong>";
	$task_name = get_db_value ("name", "ttask", "id", $mwur['task']);
	echo $task_name;
	echo "</td>";
	echo "<td colspan='2'>";
	echo "<strong>".__("Role").": </strong>";
	$role_name = get_db_value ("name", "trole", "id", $mwur['role']);
	echo $role_name;
	echo "</td>";
	echo "<td colspan='2'>";
	echo "<strong>".__("Username").": </strong>";
	echo $mwur['user'];
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td>";
	echo "<strong>".__("Time used").": </strong>";
	echo $mwur['duration'];
	echo " ".__("hours");
	echo "</td>";
	echo "<td>";
	echo "<strong>".__("Have cost").": </strong>";
	echo "</td>";
	echo "<td>";
	echo print_checkbox_extended ("id", "nothing", $mwur['cost'], true, "", "" , true);
	echo "</td>";
	echo "<td>";
	echo "<strong>".__("Public").": </strong>";
	echo "</td>";
	echo "<td>";
	echo print_checkbox_extended ("id", "nothing", $mwur['public'], true, "", "" , true);
	echo "</td>";
	echo "<td>";
	echo "<strong>".__("Forward").": </strong>";
	echo "</td>";
	echo "<td>";
	echo print_checkbox_extended ("id", "nothing", $mwur['forward'], true, "", "" , true);
	echo "</td>";	
	echo "<td>";
	echo "<strong>".__("Split > 1 day").": </strong>";
	echo "</td>";
	echo "<td>";
	echo print_checkbox_extended ("id", "nothing", $mwur['split'], true, "", "" , true);
	echo "</td>";	
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan='5'>";
	echo "<strong>".__("Description")."</strong>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td colspan='4'>";
	echo $mwur['description'];
	echo "</td>";
	echo "</tr>";
	echo "</table>";	
}

