<?php
// Integria 1.0 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2008 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// Load global vars

global $config;
$id_user = $config["id_user"];

check_login ();

$id_workunit = get_parameter ("id_workunit", -1);
$id = get_parameter ("id");
$operation = get_parameter ("operation");
if (($id != "") && ($id != $id_user)) {
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
				$result_output = "<h3 class='suc'>".__('Deleted successfully')."</h3>";
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
echo __('Workunit personal report');
if ($timestamp_l != "" AND $timestamp_h != "")
	echo " : ".$timestamp_l. " -&gt;".$timestamp_h;
echo "</h3>";
if ($id_workunit != -1){
	$sql= "SELECT * FROM tworkunit WHERE tworkunit.id = $id_workunit";
} else {
	if ($timestamp_l != "" && $timestamp_h != "")
		$sql= "SELECT * FROM tworkunit WHERE tworkunit.id_user = '$id_user' AND timestamp >= '$timestamp_l' AND timestamp < '$timestamp_h' ORDER BY timestamp DESC";
	else 
		$sql= "SELECT * FROM tworkunit WHERE tworkunit.id_user = '$id_user' ORDER BY timestamp DESC";
}
// TODO: Add granularity check to show only data from projects where ACL is active for current user
if ($res = mysql_query($sql)) {
	while ($row=mysql_fetch_array($res)) 
		if ($id_workunit != -1)
			show_workunit_user ($row[0], 1);
		else
			show_workunit_user ($row[0]);
}

?>
