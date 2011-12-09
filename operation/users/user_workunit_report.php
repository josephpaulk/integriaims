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

// Load global vars

global $config;

require_once ('include/functions_tasks.php');
require_once ('include/functions_workunits.php');

$id_user = $config["id_user"];

check_login ();

$id_workunit = get_parameter ("id_workunit", -1);
$id = get_parameter ("id");

// Optional search: by task
$id_task = get_parameter ("id_task", 0);

$operation = get_parameter ("operation");

$users = get_user_visible_users();

if (($id != "") && ($id != $id_user) && in_array($id, array_keys($users))) {
	if (give_acl($id_user, 0, "PW"))
		$id_user = $id;
	else {
		audit_db("Noauth", $config["REMOTE_ADDR"], "No permission access", "Trying to access user workunit report");
				require ("general/noaccess.php");
				exit;
	}
	
}

$timestamp_l = get_parameter ("timestamp_l");
$timestamp_h = get_parameter ("timestamp_h");

// ---------------
// Lock Workunit
// ---------------

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

// ---------------
// DELETE Workunit
// ---------------

if ($operation == "delete"){
	// Delete workunit with ACL / Project manager check
	$id_workunit = get_parameter ("id_workunit");
	$sql = "SELECT * FROM tworkunit WHERE id = $id_workunit";
	if ($res = mysql_query($sql)) 
		$row=mysql_fetch_array($res);
	else
		return;
	
	$id_user_wu = $row["id_user"];
	if (($id_user_wu == $config["id_user"]) OR (give_acl($config["id_user"], 0,"PM") ==1 ) OR (project_manager_check($id_project) == 1)){
		mysql_query ("DELETE FROM tworkunit where id = '$id_workunit'");
		if (mysql_query ("DELETE FROM tworkunit_task where id_workunit = '$id_workunit'")){
				$result_output = "<h3 class='suc'>".__('Successfully deleted')."</h3>";
				audit_db ($id_user, $config["REMOTE_ADDR"], "Work unit deleted", "Workunit for $id_user");
		} else {
			$result_output = "<h3 class='error'>".__('Not deleted. Error deleting data')."</h3>";
		}
	} else {
		audit_db($id_user, $config["REMOTE_ADDR"], "ACL Violation","Trying to delete WU $id_workunit without rigths");
		include ("general/noaccess.php");
		exit;
	}
}

// --------------------
// Workunit report
// --------------------

$ahora = date("Y-m-d H:i:s");
if ($timestamp_h == "")
	$timestamp_h == $ahora ;
echo "<h3>";

echo __('Workunit personal report for user');
echo " '". dame_nombre_real($id_user). "'.";

echo "<a href='index.php?sec=users&sec2=operation/users/user_workunit_report&timestamp_l=$timestamp_l&timestamp_h=$timestamp_h&id=$id_user&id_task=$id_task&clean_output=1'>";
echo "<img src='images/html.png'>";
echo "</A>";

echo "<br>";
echo __("Between dates");
echo ": ";
if ($timestamp_l != "" AND $timestamp_h != "")
	echo " : ".$timestamp_l. " ".__("to")." ".$timestamp_h;

if ($id_task != 0)
    echo "<br>".__("Task"). " : ".get_db_sql("SELECT name FROM ttask WHERE id = $id_task");

echo "</h3>";
if ($id_workunit != -1){
	$sql= "SELECT * FROM tworkunit WHERE tworkunit.id = $id_workunit";
} else {
    if ($id_task == 0){
	    if ($timestamp_l != "" && $timestamp_h != "")
		    $sql= "SELECT * FROM tworkunit WHERE tworkunit.id_user = '$id_user' AND timestamp >= '$timestamp_l' AND timestamp <= '$timestamp_h' ORDER BY timestamp DESC";
	    else 
		    $sql= "SELECT * FROM tworkunit WHERE tworkunit.id_user = '$id_user' ORDER BY timestamp DESC";
    } else {
        if ($timestamp_l != "" && $timestamp_h != "")
		    $sql= "SELECT * FROM tworkunit, tworkunit_task WHERE tworkunit.id_user = '$id_user' AND timestamp >= '$timestamp_l' AND timestamp <= '$timestamp_h' AND tworkunit_task.id_task = $id_task AND tworkunit_task.id_workunit = tworkunit.id ORDER BY timestamp DESC";
	    else 
		    $sql= "SELECT * FROM tworkunit, tworkunit_task WHERE tworkunit.id_user = '$id_user' AND tworkunit_task.id_task = $id_task AND tworkunit_task.id_workunit = tworkunit.id ORDER BY timestamp DESC";
    }
}

$sql = safe_output ($sql);

$alldata = get_db_all_rows_sql ($sql);
foreach ($alldata as $row){ 
	
	if ($row["id"] != -1)
        	show_workunit_user ($row[0], 1);
	else 
		show_workunit_user ($row[0]);
}

?>
