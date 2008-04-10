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
	
	if (check_login() != 0) {
		audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access", "Trying to access event viewer");
		require ("general/noaccess.php");
		exit;
	}

	$id_workunit = give_parameter_get ("id_workunit", -1);
	$id = give_parameter_get ("id");
	
	if (($id != "") && ($id != $id_user)) {
		if (give_acl($id_user, 0, "PW"))
			$id_user = $id;
		else {
			audit_db("Noauth", $config["REMOTE_ADDR"], "No permission access", "Trying to access user workunit report");
	                require ("general/noaccess.php");
        	        exit;
		}
		
	}

	$timestamp_l = give_parameter_get ("timestamp_l");
	$timestamp_h = give_parameter_get ("timestamp_h");

	// --------------------
	// Workunit report
	// --------------------
	$ahora = date("Y-m-d H:i:s");
    if ($timestamp_h == "")
        $timestamp_h == $ahora ;
	echo "<h3>";
	echo $lang_label["workunit_personal_report"];
	if ($timestamp_l != "" AND $timestamp_h != "")
		echo " : ".$timestamp_l. " -&gt;".$timestamp_h;
	echo "</h3>";
	if ($id_workunit != -1){
		$sql= "SELECT * FROM tworkunit WHERE tworkunit.id = $id_workunit";
	} else {
		if ($timestamp_l != "" AND $timestamp_h != "")
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
