<?php

global $config;

check_login ();

$id_project = get_parameter ("id_project", 0);
$id_task = get_parameter ("id_task", 0);
$operation = get_parameter ("operation", "");
// Get names
if ($id_project != 0)
	$project_name = get_db_value ("name", "tproject", "id", $id_project);
else
	$project_name = "";

if ($id_task != 0)
	$task_name = get_db_value ("name", "ttask", "id", $id_task);
else
	$task_name = "";

if (! $id_project) {
	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation","Trying to access to task manager withour project");
	echo "ASDSADSA";
	include ("general/noaccess.php");
	exit;
}

// Lock Workunit
if ($operation == "lock"){
	$id_workunit = get_parameter ('id_workunit');
	$id_task = get_db_value ("id_task", "tworkunit_task", "id_workunit", $id_workunit);
	$id_group = get_db_value ("id_group", "ttask", "id", $id_task);
	$sql = sprintf ('UPDATE tworkunit SET locked = "%s" WHERE id = %d',
		$config['id_user'], $id_workunit);
	process_sql ($sql);
}

// ADD / UPDATE Workunit
if ($operation == "workunit") {
	$id_workunit = (int) get_parameter ("id_workunit");
	$duration = (int) get_parameter ("duration");
	$time = get_parameter ('time');
	$date = get_parameter ('date');
	$timestamp = $date." ".$time;
	$real_timestamp = date ('Y-m-d H:i:s');
	// TODO: Sanitize timestamp string
	$description = get_parameter ('description');
	$have_cost = (bool) get_parameter ('have_cost');
	$user_role = (int) get_parameter ('work_profile');

	if ($id_workunit == 0) {
		// INSERT
		$sql = sprintf ('INSERT INTO tworkunit (timestamp, duration, id_user,
			description, have_cost, id_profile)
			VALUES ("%s", %d, "%s", "%s", %d, %d)',
			$timestamp, $duration, $config['id_user'], $description,
			$have_cost, $user_role);
		$result = process_sql ($sql, 'insert_id');
	} else {
		// UPDATE WORKUNIT
		$sql = sprintf ('UPDATE tworkunit
			SET timestamp = "%s", duration = %f, description = "%s",
			have_cost = %d, id_profile = %d
			WHERE id = %d',
			$timestamp, $duration, $description, $have_cost,
			$user_role, $id_workunit);
		$result = process_sql ($sql);
	}
	
	if ($result) {
		$id_project2 = get_db_value ("id_project", "ttask", "id", $id_task);
		$id_manager = get_db_value ("id_owner", "tproject", "id", $id_project2);
		if ($id_workunit == 0) {
			$id_workunit = $result;
			mail_project (0, $config['id_user'], $id_workunit, $id_task);
			$sql = sprintf ('INSERT INTO tworkunit_task (id_task, id_workunit)
				VALUES (%d, %d)',
				$id_task, $id_workunit);
			$result = process_sql ($sql);
			if ($result) {
				$result_output = '<h3 class="suc">'.__('Workunit added').'</h3>';
				insert_event ("PWU INSERT", 0, 0, $description);
			}
			task_tracking ($id_task, TASK_WORKUNIT_ADDED, $id_workunit);
		} else {
			mail_project (1, $config['id_user'], $id_workunit, $id_task);
			$result_output = '<h3 class="suc">'.__('Workunit added').'</h3>';
			insert_event ("PWU UPDATED", 0, 0, $description);
		}
	} else {
		$result_output = '<h3 class="error">'.__('Problemd adding workunit.').'</h3>';
	}
	$operation = "view";
}

// DELETE Workunit
if ($operation == "delete"){
	// Delete workunit with ACL / Project manager check
	$id_workunit = get_parameter ("id_workunit");
	$sql = "SELECT * FROM tworkunit WHERE id = $id_workunit";
	if ($res = mysql_query($sql)) 
		$row=mysql_fetch_array($res);
	else
		return;
		
	$id_user_wu = $row["id_user"];
	$id_task_wu = get_db_value ("id_task", "tworkunit_task", "id_workunit", $row["id"]);
	$id_project_wu = get_db_value ("id_project", "ttask", "id", $id_task_wu);
	if ($id_user_wu == $config["id_user"] 
		|| give_acl ($config["id_user"], 0,"PM")
		|| project_manager_check ($id_project)) {
				
		mysql_query ("DELETE FROM tworkunit where id = '$id_workunit'");
		if (mysql_query ("DELETE FROM tworkunit_task where id_workunit = '$id_workunit'")){
				$result_output = "<h3 class='suc'>".__('Deleted successfully').'</h3>';
				audit_db ($config['id_user'], $config["REMOTE_ADDR"], "Work unit deleted", "Workunit for ".$config['id_user']);
		} else {
			$result_output = "<h3 class='error'>".__('Not deleted. Error deleting data').'</h3>';
		}
	} else {
		audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to delete WU $id_workunit without rigths");
		include ("general/noaccess.php");
		exit;
	}
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
