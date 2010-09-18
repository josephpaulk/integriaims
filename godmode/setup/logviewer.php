<?php
// INTEGRIA - the ITIL Management System
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


global $config;
check_login();

if (give_acl($config["id_user"], 0, "FM")==0) {
    audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access log viewer");
    require ("general/noaccess.php");
    exit;
}

$file_name = $config["homedir"]."/integria.log";

if (!file_exists($file_name)){
	echo "<h2 class=error>".__("Cannot find file"). "(".$file_name;
	echo ")</h1>";
}  else {
	if (filesize ($file_name) > 512000) {
		echo "<h2 class=error>".__("File is too large (> 500KB)"). "(".$file_name;
		echo ")</h1>";
	} else {
		$data = file_get_contents ($file_name);			
		echo "<h2>$file_name (".format_numeric(filesize ($file_name)/1024)." KB) </h2>";
		echo "<textarea style='width: 100%; height: 500px;' name='$file_name'>";
		echo $data;
		echo "</textarea><br><br>";
	}
}

?>
