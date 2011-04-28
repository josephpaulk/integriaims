<?php

global $config;

check_login ();

require_once ('include/functions_workunits.php');

$id_project = (int) get_parameter ("id_project");
$id_task = (int) get_parameter ("id_task");
$operation = (string) get_parameter ("operation");

if ($id_project > 0 && ! user_belong_project ($config["id_user"], $id_project)) {
	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to view workunit not in this access range");
	no_permission();
}

if ($id_task > 0 && ! user_belong_task ($config["id_user"], $id_task)){
	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to view workunit not in this access range");
	no_permission();
}

if (! $id_project) {
	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation","Trying to access to task manager without project");
	no_permission();
}

// Get names
$project_name = get_db_value ("name", "tproject", "id", $id_project);

$task_name = "";
if ($id_task != 0)
	$task_name = get_db_value ("name", "ttask", "id", $id_task);

// Lock Workunit
if ($operation == "lock") {
	lock_task_workunit ($id_workunit);
}

// ADD / UPDATE Workunit
if ($operation == "workunit") {
	$id_workunit = (int) get_parameter ('id_workunit');
	$insert = false;
	if ($id_workunit == 0) {
		$insert = true;
	}
	$duration = (float) get_parameter ("duration");
	$time = (string) get_parameter ('time');
	$date = (string) get_parameter ('date');
	$timestamp = $date." ".$time;
	$real_timestamp = date ('Y-m-d H:i:s');
	$description = (string) get_parameter ('description');
	$have_cost = (bool) get_parameter ('have_cost');
	$user_role = (int) get_parameter ('work_profile');

	if ($insert) {
		// INSERT
		$sql = sprintf ('INSERT INTO tworkunit (timestamp, duration, id_user,
			description, have_cost, id_profile)
			VALUES ("%s", %.2f, "%s", "%s", %d, %d)',
			$timestamp, $duration, $config['id_user'], $description,
			$have_cost, $user_role);
		$result = process_sql ($sql, 'insert_id');
		$id_workunit = $result;
	} else {
		// UPDATE WORKUNIT
		$sql = sprintf ('UPDATE tworkunit
			SET timestamp = "%s", duration = %.2f, description = "%s",
			have_cost = %d, id_profile = %d
			WHERE id = %d',
			$timestamp, $duration, $description, $have_cost,
			$user_role, $id_workunit);
		$result = process_sql ($sql);
	}
	
	if ($result) {
		$task = get_db_row ('ttask', 'id', $id_task);
		$current_hours = get_task_workunit_hours ($id_task);
		if ($insert) {
			mail_project (0, $config['id_user'], $id_workunit, $id_task);
			$sql = sprintf ('INSERT INTO tworkunit_task (id_task, id_workunit)
				VALUES (%d, %d)',
				$id_task, $id_workunit);
			process_sql ($sql);
			$result_output = '<h3 class="suc">'.__('Workunit added').'</h3>';
			insert_event ("PWU INSERT", 0, 0, $description);
			task_tracking ($id_task, TASK_WORKUNIT_ADDED, $id_workunit);

			/* Autocomplete task progress */

                	if ($task['completion'] < 100) {
	                       /* Get expected task completion, based on worked hours */
       		               $expected_completion = round_number (floor ($current_hours * 100 / $task['hours']));

	                        $current_hours += $duration;
                        	$expected_completion =  round_number (floor ($current_hours * 100 / $task['hours']));
                        	$sql = sprintf ('UPDATE ttask
                                SET completion = %d
                                WHERE id = %d',
                                $expected_completion, $id_task);
                        	process_sql ($sql);
                	}
		} else {
			mail_project (1, $config['id_user'], $id_workunit, $id_task);
			$result_output = '<h3 class="suc">'.__('Workunit updated').'</h3>';
			insert_event ("PWU UPDATED", 0, 0, $description);
		}
		
	} else {
		$result_output = '<h3 class="error">'.__('There was a problem adding workunit').'</h3>';
	}
	$operation = "view";
}

// DELETE Workunit
if ($operation == "delete") {
	$success = delete_task_workunit ($id_workunit);
	if (! $success) {
		audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation",
			"Trying to delete WU $id_workunit without rigths");
		include ("general/noaccess.php");
		return;
	}
	
	$result_output = "<h3 class='suc'>".__('Successfully deleted').'</h3>';
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "Work unit deleted", "Workunit for ".$config['id_user']);
}

// Render
if (isset($result_output))
	echo $result_output;

// Specific task
if ($id_task != 0) { 
	$sql= sprintf ('SELECT tworkunit.id
			FROM tworkunit, tworkunit_task 
			WHERE tworkunit_task.id_task = %d
			AND tworkunit_task.id_workunit = tworkunit.id
			ORDER BY tworkunit.timestamp DESC', $id_task);
	echo '<h3>'.__('Workunit resume');
	echo ' - '.$project_name.' - '.$task_name.'</h3>';
} elseif ($id_project != 0) {
	// Whole project
	$sql = sprintf ('SELECT tworkunit.id
		FROM tworkunit, tworkunit_task, ttask 
		WHERE tworkunit_task.id_task = ttask.id
		AND ttask.id_project = %d
		AND tworkunit_task.id_workunit = tworkunit.id
		ORDER BY tworkunit.timestamp DESC', $id_project);
	echo '<h3>'.__('Workunit resume');
	echo ' - '.$project_name.' - '. __('All tasks').'</h3>';
}

$workunits = get_db_all_rows_sql ($sql);
if ($workunits) {
	foreach ($workunits as $workunit) {
		show_workunit_user ($workunit['id']);
	}
}
?>

<script type="text/javascript">
$(document).ready (function () {
	$(".lock_workunit").click (function () {
		var img = this;
		id = this.id.split ("-").pop ();
		values = Array ();
		values.push ({name: "page", value: "operation/users/user_spare_workunit"});
		values.push ({name: "operation", value: "lock"});
		values.push ({name: "id_workunit", value: id});
		jQuery.post ("ajax.php",
			values,
			function (data, status) {
				$(img).fadeOut (function () {
					$(this).remove ();
				});
				$("#edit-"+id).fadeOut (function () {
					$(this).parent ("td").append (data);
					$(this).remove ();
				});
			},
			"html");
		return false;
	});
	
	$(".delete-workunit").attr ("onclick", "").click (function () {
		if (! confirm ("<?php echo __('Are you sure?')?>"))
			return false;
		var div = $(this).parents ("div.notebody");
		id = this.id.split ("-").pop ();
		values = Array ();
		values.push ({name: "page", value: "operation/users/user_spare_workunit"});
		values.push ({name: "operation", value: "delete"});
		values.push ({name: "id_workunit", value: id});
		jQuery.post ("ajax.php",
			values,
			function (data, status) {
				$(div).prev ("div.notetitle").slideUp (function () {
					$(this).remove ();
				});
				$(div).slideUp (function () {
					$(this).remove ();
				});
			},
			"html");
		return false;
	});
});
</script>
