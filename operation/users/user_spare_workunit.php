<?php
// INTEGRIA IMS - the ITIL Management System
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

global $config;

check_login ();

require_once ('include/functions_tasks.php');
require_once ('include/functions_workunits.php');

$operation = (string) get_parameter ("operation");
$now = (string) get_parameter ("givendate", date ("Y-m-d H:i:s"));
$public = (bool) get_parameter ("public", 1);
$id_project = (int) get_parameter ("id_project");
$id_workunit = (int) get_parameter ('id_workunit');
$id_task = (int) get_parameter ("id_task");
// If id_task is set, ignore id_project and get it from the task
if ($id_task) {
	$id_project = get_db_value ('id_project', 'ttask', 'id', $id_task);
}

// Lock Workunit
if ($operation == "lock") {
	$success = lock_task_workunit ($id_workunit);
	if (! $success) {
		audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation",
			"Trying to lock WU $id_workunit without rigths");
		if (!defined ('AJAX'))
			include ("general/noaccess.php");
		return;
	}
	
	$result_output = '<h3 class="suc">'.__('Locked successfully').'</h3>';
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "Work unit locked",
		"Workunit for ".$config['id_user']);
	
	if (defined ('AJAX')) {
		echo '<img src="images/rosette.png" title="'.__('Locked by').' '.$config['id_user'].'" />';
		print_user_avatar ($config['id_user'], true);
		return;
	}
}

if ($id_workunit) {
	$sql = sprintf ('SELECT *
		FROM tworkunit, tworkunit_task
		WHERE tworkunit.id = tworkunit_task.id_workunit
		AND tworkunit.id = %d', $id_workunit);
	$workunit = get_db_row_sql ($sql);
	if ($workunit === false) {
		require ("general/noaccess.php");
		return;
	}
	
	$id_task = $workunit['id_task'];
	$id_project = get_db_value ('id_project', 'ttask', 'id', $id_task);
	$id_user = $workunit['id_user'];
	$duration = $workunit['duration']; 
	$description = $workunit['description'];
	$have_cost = $workunit['have_cost'];
	$id_profile = $workunit['id_profile'];
	$now = $workunit['timestamp'];
	$public = (bool) $workunit['public'];
	$now_date = substr ($now, 0, 10);
	$now_time = substr ($now, 10, 8);
	
	if (!$public && $id_user != $config["id_user"] && ! project_manager_check ($id_project) ) {
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation",
			"Trying to access workunit");
		require ("general/noaccess.php");
		return;
	}
} else {
	$id_user = $config["id_user"];
	$duration = 1; 
	$description = "";
	$id_inventory = array();
	$have_cost = false;
	$public = true;
	$id_profile = "";
	$now_date = date ("Y-m-d");
	$now_time = date ("H:i:s");
	$now = date ("Y-m-d H:i:s");
}

// Insert workunit
if ($operation == 'insert') {
	$duration = (float) get_parameter ("duration");
	$timestamp = (string) get_parameter ("start_date");
	$description = (string) get_parameter ("description");
	$have_cost = (bool) get_parameter ("have_cost");
	$id_task = (int) get_parameter ("id_task", -1);
	$id_profile = (int) get_parameter ("id_profile");
	$public = (bool) get_parameter ("public");
	$split = (bool) get_parameter ("split");
	$id_user = (string) get_parameter ("wu_user", $config['id_user']);
	
	// Multi-day assigment
	if ($split && $duration > $config["hours_perday"]) {
		$forward = (bool) get_parameter ("forward");
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
					$result_output = '<h3 class="suc">'.__('Workunit added').'</h3>';
				} else {
					$result_output = '<h3 class="error">'.__('Problem adding workunit.').'</h3>';
				}
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
				$result_output = '<h3 class="suc">'.__('Workunit added').'</h3>';
				audit_db ($config['id_user'], $config["REMOTE_ADDR"], "Spare work unit added", 
						'Workunit for '.$config['id_user'].' added to Task ID #'.$id_task);
				mail_project (0, $config['id_user'], $id_workunit, $id_task);
			}
		} else {
			$result_output = '<h3 class="error">'.__('Problemd adding workunit.').'</h3>';
		}
	}
	
	if ($id_workunit !== false) {
		add_task_hours ($id_task, $duration);
	}
	
	insert_event ("PWU INSERT", $id_task, 0, $description);
	echo $result_output;
}

if ($operation == "delete") {
	$success = delete_task_workunit ($id_workunit);
	if (! $success) {
		audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation",
			"Trying to delete WU $id_workunit without rigths");
		include ("general/noaccess.php");
		return;
	}
	
	$result_output = '<h3 class="suc">'.__('Successfully deleted').'</h3>';
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "Work unit deleted", "Workunit for ".$config['id_user']);
	
	if (defined ('AJAX'))
		return;
}

// Edit workunit
if ($operation == 'update') {
	$duration = (float) get_parameter ("duration");
	$timestamp = (string) get_parameter ("start_date");
	$description = (string) get_parameter ("description");
	$have_cost = (bool) get_parameter ("have_cost");
	$id_profile = (int) get_parameter ("id_profile");
	$public = (bool) get_parameter ("public");
	$id_user = (string) get_parameter ("wu_user", $config['id_user']);
	
	// UPDATE WORKUNIT
	$sql = sprintf ('UPDATE tworkunit
		SET timestamp = "%s", duration = %.2f, description = "%s",
		have_cost = %d, id_profile = %d, public = %d
		WHERE id = %d',
		$timestamp, $duration, $description, $have_cost,
		$id_profile, $public, $id_workunit);
	$result = process_sql ($sql);
	mail_project (1, $config['id_user'], $id_workunit, $id_task);
	$result_output = '<h3 class="suc">'.__('Workunit updated').'</h3>';
	insert_event ("PWU UPDATED", 0, 0, $description);
}

echo "<h3><img src='images/award_star_silver_1.png'> ";

if ($id_workunit) {
	echo __('Update workunit');
} else {
	echo __('Add workunit');
}
if ($id_task) {
	echo ' - ';
	echo get_db_value ('name', 'ttask', 'id', $id_task);
}
echo '</h3>';

$table->class = 'databox';
$table->width = '90%';
$table->data = array ();
$table->colspan = array ();
$table->colspan[5][0] = 3;

$start_date = substr ($now, 0, 10);
$table->data[0][0] = print_input_text ('start_date', $start_date, '', 10, 20,
	true, __('Date'));

// Profile or role
if (dame_admin ($config['id_user'])) {
	$table->data[0][1] = combo_roles (true, 'role', __('Role'), true);
} else {
	$table->data[0][1] = combo_user_task_profile ($id_task, 'id_profile',
		$id_profile, false, true);
}

// Show task combo if none was given.
if (! $id_task) {
	$table->colspan[1][0] = 3;
	$table->data[1][0] = combo_task_user_participant ($config['id_user'],
		true, 0, true, __('Task'));
}

// Time used
$table->data[2][0] = print_input_text ('duration', $duration, '', 7, 7,
	true, __('Time used'));

if (dame_admin ($config['id_user'])) {
	$table->colspan[2][1] = 3;
	$table->data[2][1] = combo_user_visible_for_me ($id_user,
		'wu_user', 0, "TW", true, __('Username'));
}

// Various checkboxes
$table->data[3][0] = print_checkbox ('have_cost', 1, $have_cost, true,
	__('Have cost'));
$table->data[3][1] = print_checkbox ('public', 1, $public, true, __('Public'));

if (! $id_workunit) {
	$table->data[4][0] = print_checkbox ('forward', 1, false, true,
		__('Forward'));
	$table->data[4][0] .= print_help_tip (__('If this checkbox is activated, propagation will be forward instead backward'),
		true);

	$table->data[4][1] = print_checkbox ('split', 1, false, true,
		__('Split > 1day'));
	$table->data[4][1] .= print_help_tip (__('If workunit added is superior to 8 hours, it will be propagated to previous workday and deduced from the total, until deplete total hours assigned'),
		true);
}
$table->data[5][0] = print_textarea ('description', 10, 30, $description,
	'', true, __('Description'));

echo '<form method="post">';
print_table ($table);

echo '<div style="width: '.$table->width.'" class="button">';
if ($id_workunit) {
	print_input_hidden ('operation', 'update');
	print_input_hidden ('id_workunit', $id_workunit);
	print_submit_button (__('Update'), 'btn_upd', false, 'class="sub upd"');
} else {
	print_input_hidden ('operation', 'insert');
	print_submit_button (__('Add'), 'btn_add', false, 'class="sub next"');
}
print_input_hidden ('timestamp', $now);
if ($id_task) {
	/* This is useful when adding a workunit to a task */
	print_input_hidden ('id_task', $id_task);
	print_input_hidden ('id_project', $id_project);
}
echo '</div>';
echo '</form>';
?>

<script type="text/javascript" src="include/js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>

<script type="text/javascript">

$(document).ready (function () {
	$("#text-start_date").datepicker ();
	$("#textarea-description").TextAreaResizer ();
});
</script>
