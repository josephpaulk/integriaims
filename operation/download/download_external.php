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


$download_id = $external_download_id;
$download = get_db_row ("tdownload", "external_id", $download_id );
$location = $download["location"];
$fileLocation = $config["homedir"]."$location";

/*TODO: ACL checking here */

if (($download["id"] > 0) AND (file_exists($fileLocation))){

	$short_name = preg_split ("/\//", $location);
	$last_name = $short_name[sizeof($short_name)-1];

	$timestamp = date('Y-m-d H:i:s');
	mysql_query ("INSERT INTO tdownload_tracking (id_download, id_user, date) VALUES ($download_id, 'anonymous','$timestamp')");

	# If file is too big (>80MB) do a redirect
	if (filesize($fileLocation) >80000000){
		header("Location: ".$config["base_url"]."/".$location);
		return;
	} 

	header('Content-type: aplication/octet-stream;');
	header('Content-type: ' . returnMIMEType($fileLocation) . ';');
	header("Content-Length: " . filesize($fileLocation));
	header('Content-Disposition: attachment; filename="' . $last_name . '"');

	// If it's a large file we don't want the script to timeout, so:
	set_time_limit(90000);
	// If it's a large file, readfile might not be able to do it in one go, so:
	$chunksize = 1 * (1024 * 256); // how many bytes per chunk
	if (filesize($fileLocation) > $chunksize) {
		$handle = fopen($fileLocation, 'rb');
    		$buffer = '';
      		while (!feof($handle)) {
          		$buffer = fread($handle, $chunksize);
	      		echo $buffer;
	          	ob_flush();
		      	flush();
	 	}
		fclose($handle);
	} else {
		readfile($fileLocation);
	}
} else {
	audit_db("",$config["REMOTE_ADDR"], "ACL Violation","Trying to access a non-existant file in disk");
	echo "File not found";
	exit;
}
