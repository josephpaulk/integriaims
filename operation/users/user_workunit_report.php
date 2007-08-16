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
	$id_user = $config["id_user"];
	
	if (check_login() != 0) {
		audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access", "Trying to access event viewer");
		require ("general/noaccess.php");
		exit;
	}

	// --------------------
	// Workunit report
	// --------------------
	$ahora = date("Y-m-d H:i:s");

	echo "<h3>";
	echo $lang_label["workunit_personal_report"] ." ( ".$id_user. " )";
	echo "</h3>";
	$sql= "SELECT * FROM tworkunit WHERE tworkunit.id_user = '$id_user'";

	if ($res = mysql_query($sql)) {
		while ($row=mysql_fetch_array($res))
			show_workunit_user ($row[0]);
	}	

?>
