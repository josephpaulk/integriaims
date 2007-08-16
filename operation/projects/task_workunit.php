<?php
// FRITS - the FRee Incident Tracking System
// =========================================
// Copyright (c) 2007 Sancho Lerena, slerena@openideas.info
// Copyright (c) 2007 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
// Load global vars

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

// ------------
// ADD Workunit
// -------------

if ($operation == "workunit"){
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
		
	$sql = "INSERT INTO tworkunit (timestamp, duration, id_user, description, have_cost, id_profile) VALUES
			('$timestamp', $duration, '$id_user', '$description', $have_cost, $user_role)";
	if (mysql_query($sql)){
		$msgtext = "A new workunit has been created by user [$id_user]. Workunit information is: \n\n$description ";
		$id_project2 = give_db_value ("id_project", "ttask", "id", $id_task);
		$id_manager = give_db_value ("id_owner", "tproject", "id", $id_project2);
		topi_sendmail (return_user_email($id_manager), "[TOPI] New workunit added to task '$task_name'", $msgtext);
		//echo "<h1>DEBUG $sql</h1>";
		$id_workunit = mysql_insert_id();
		$sql2 = "INSERT INTO tworkunit_task (id_task, id_workunit) VALUES ($id_task, $id_workunit)";
		if (mysql_query($sql2)){
			$result_output = "<h3 class='suc'>".$lang_label["workunit_ok"]."</h3>";
			audit_db ($id_user, $config["REMOTE_ADDR"], "Work unit added", "Workunit for $id_user added to Task '$task_name'");
		}
	} else 
		$result_output = "<h3 class='error'>".$lang_label["workunit_no"]."</h3>";
	$operation = "view";
}

// ---------------
// DELETE Workunit
// ---------------

if ($operation == "delete"){
	$id_workunit = give_parameter_get ("id_workunit");

	mysql_query ("DELETE FROM tworkunit where id = '$id_workunit'");
	if (mysql_query ("DELETE FROM tworkunit_task where id_workunit = '$id_workunit'")){
			$result_output = "<h3 class='suc'>".$lang_label["delete_ok"]."</h3>";
			audit_db ($id_user, $config["REMOTE_ADDR"], "Work unit deleted", "Workunit for $id_user");
		}
	else
		$result_output = "<h3 class='error'>".$lang_label["delete_no"]."</h3>";
}

// -----------------------
// Begin render
// -----------------------

if (isset($result_output))
	echo $result_output;


// Specific task
if ($id_task != -1){ 
	$sql= "SELECT tworkunit.id
			FROM tworkunit, tworkunit_task 
			WHERE tworkunit_task.id_task = $id_task AND tworkunit_task.id_workunit = tworkunit.id";
	echo "<h3>".$lang_label["workunit_resume"];
	echo " - ".$project_name." - ".$task_name."</h3>";
}

// Whole project
if ($id_task == -1){
	$sql= "SELECT tworkunit.id
			FROM tworkunit, tworkunit_task, ttask 
			WHERE tworkunit_task.id_task = ttask.id AND
					ttask.id_project = $id_project AND 
					tworkunit_task.id_workunit = tworkunit.id";				
	echo "<h3>".$lang_label["workunit_resume"];
	echo " - ".$project_name." - ". lang_string ("all_tasks")."</h3>";
}


if ($res = mysql_query($sql)) {
	while ($row=mysql_fetch_array($res))
		show_workunit_task_data ($row[0], $id_task);
}

?>
