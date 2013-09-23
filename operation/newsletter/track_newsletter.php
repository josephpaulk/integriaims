<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2012 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
ob_start();
require_once ('../../include/config.php');
require_once ('../../include/functions.php');
require_once ('../../include/functions_db.php');
ob_end_clean();

global $config;

$id_content = get_parameter("id_content");

//Increase views in one unit
$time = print_mysql_timestamp();

$content = get_db_row ("tnewsletter_content", "id", $id_content);

$newsletter = get_db_row("tnewsletter", "id", $content["id_newsletter"]);

$sql = sprintf("INSERT tnewsletter_tracking (id_newsletter, id_newsletter_content, datetime, status) VALUES (%d, %d, '%s', %d)", 
			$newsletter["id"], $id_content, $time, 2); //Status 2 (!= 0 and != 1) means sent
process_sql($sql);

//Generate a void pixel
header('Content-Type: image/png');
echo base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABAQMAAAAl21bKAAAAA1BMVEUAAACnej3aAAAAAXRSTlMAQObYZgAAAApJREFUCNdjYAAAAAIAAeIhvDMAAAAASUVORK5CYII=');

?>
