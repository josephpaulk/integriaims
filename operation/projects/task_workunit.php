<?php

global $config;

if (check_login() != 0) {
    audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access", "Trying to access event viewer");
    require ("general/noaccess.php");
    exit;
}

$id_user = $_SESSION['id_usuario'];
$id_project = give_parameter_get ("id_project", -1);
$id_task = give_parameter_get ("id_task", -1);
$operation = give_parameter_get ("operation", "");
// Get names
if ($id_project != -1)
	$project_name = give_db_value ("name", "tproject", "id", $id_project);
else
	$project_name = "";

if ($id_task != -1)
	$task_name = give_db_value ("name", "ttask", "id", $id_task);
else
	$task_name = "";

if ( $id_project == -1 ){
    // Doesn't have access to this page
    audit_db($id_user, $config["REMOTE_ADDR"], "ACL Violation","Trying to access to task manager withour project");
    include ("general/noaccess.php");
    exit;
}

// ---------------------
// ADD / UPDATE Workunit
// ---------------------

if ($operation == "workunit"){
	$id_workunit = give_parameter_get ("id_workunit",0);
	$duration = give_parameter_post ("duration",0);
	if (!is_numeric( $duration))
		$duration = 0;
	$time = give_parameter_post ("time");
	$date = give_parameter_post ("date");
	$timestamp = $date . " " . $time;
	$real_timestamp = date('Y-m-d H:i:s');
	// TODO: Sanitize timestamp string
	$description = give_parameter_post ("description");
	$have_cost = give_parameter_post ("have_cost",0);
	$user_role = give_parameter_post ("work_profile",0);

	if ($id_workunit == 0) {
		// INSERT
		$sql = "INSERT INTO tworkunit (timestamp, duration, id_user, description, have_cost, id_profile) VALUES
			('$timestamp', $duration, '$id_user', '$description', $have_cost, $user_role)";
	} else {
		// UPDATE WORKUNIT
		$sql = "UPDATE tworkunit set timestamp = '$timestamp', duration = '$duration', description = '$description', have_cost = '$have_cost', id_profile = '$user_role' WHERE id = $id_workunit";
	}
	if (mysql_query($sql)){
		if ($id_workunit == 0)
			$msgtext = "A new workunit has been created by user [$id_user]. Workunit information is: \n\n$description\nHours: $duration (hr)\nTimestamp: $timestamp\nHave cost: $have_cost\n\n";
		else
			$msgtext = "A new workunit has been updated by user [$id_user]. Workunit information is: \n\n$description\nHours: $duration (hr)\nTimestamp: $timestamp\nHave cost: $have_cost\n\n";
		$id_project2 = give_db_value ("id_project", "ttask", "id", $id_task);
		$id_manager = give_db_value ("id_owner", "tproject", "id", $id_project2);
		topi_sendmail (return_user_email($id_manager), "[TOPI] New workunit added to task '$task_name'", $msgtext);
		if ($id_workunit == 0) {
			$id_workunit = mysql_insert_id();
			$sql2 = "INSERT INTO tworkunit_task (id_task, id_workunit) VALUES ($id_task, $id_workunit)";
			if (mysql_query($sql2)){
				$result_output = "<h3 class='suc'>".$lang_label["workunit_ok"]."</h3>";
				audit_db ($id_user, $config["REMOTE_ADDR"], "Work unit added", "Workunit for $id_user added to Task '$task_name'");
			}
		} else {
			$result_output = "<h3 class='suc'>".$lang_label["workunit_ok"]."</h3>";
			audit_db ($id_user, $config["REMOTE_ADDR"], "Work unit updated", "Workunit for $id_user updated for Task '$task_name'");
		}
	
	} else 
		$result_output = "<h3 class='error'>".$lang_label["workunit_no"]."</h3>";
	$operation = "view";
}



// ---------------
// DELETE Workunit
// ---------------

if ($operation == "delete"){
	// Delete workunit with ACL / Project manager check
	$id_workunit = give_parameter_get ("id_workunit");
	$sql = "SELECT * FROM tworkunit WHERE id = $id_workunit";
	if ($res = mysql_query($sql)) 
		$row=mysql_fetch_array($res);
	else
		return;
		
	$id_user_wu = $row["id_user"];
	$id_task_wu = give_db_value ("id_task", "tworkunit_task", "id_workunit", $row["id"]);
	$id_project_wu = give_db_value ("id_project", "ttask", "id", $id_task_wu);
	if (($id_user_wu == $id_user) OR (project_manager_check($id_project) == 1)){
		mysql_query ("DELETE FROM tworkunit where id = '$id_workunit'");
		if (mysql_query ("DELETE FROM tworkunit_task where id_workunit = '$id_workunit'")){
				$result_output = "<h3 class='suc'>".$lang_label["delete_ok"]."</h3>";
				audit_db ($id_user, $config["REMOTE_ADDR"], "Work unit deleted", "Workunit for $id_user");
		} else {
			$result_output = "<h3 class='error'>".$lang_label["delete_no"]."</h3>";
		}
	} else {
		audit_db($id_user, $config["REMOTE_ADDR"], "ACL Violation","Trying to delete WU $id_workunit without rigths");
		include ("general/noaccess.php");
		exit;
	}
}

// -----------------------
// Begin render
// -----------------------

if (isset($result_output))
	echo $result_output;


// Specific task
if ($id_task != -1){ 
	$sql= "SELECT tworkunit.id, tworkunit.id_user 
			FROM tworkunit, tworkunit_task 
			WHERE tworkunit_task.id_task = $id_task AND tworkunit_task.id_workunit = tworkunit.id
			ORDER BY tworkunit.timestamp DESC";
	echo "<h3>".$lang_label["workunit_resume"];
	echo " - ".$project_name." - ".$task_name."</h3>";
}

// Whole project
if ($id_task == -1){
	$sql= "SELECT tworkunit.id, tworkunit.id_user 
			FROM tworkunit, tworkunit_task, ttask 
			WHERE tworkunit_task.id_task = ttask.id AND
					ttask.id_project = $id_project AND 
					tworkunit_task.id_workunit = tworkunit.id
			ORDER BY tworkunit.timestamp DESC";
	echo "<h3>".$lang_label["workunit_resume"];
	echo " - ".$project_name." - ". lang_string ("all_tasks")."</h3>";
}

// Get project for this 
//if ($id_task != -1){
//	$id_project = give_db_value ("id_project", "ttask", "id", $id_task);
//}

if ($res = mysql_query($sql)) {
	while ($row=mysql_fetch_array($res)){
		//if (($row[1] == $id_user) OR (project_manager_check($id_project) == 1)){
			show_workunit_user ($row[0]);
		//}
	}
}

?>
