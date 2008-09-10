<?php

// Integria 2.0 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008 Artica Soluciones Tecnologicas
// Copyright (c) 2008 Esteban Sanchez

// This program is free software; you can redistribute it and/or
// modify it under the terms of the  GNU Lesser General Public License
// as published by the Free Software Foundation; version 2

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

if ((! file_exists("include/config.php")) || (! is_readable("include/config.php"))) {
	exit;
}
error_reporting (E_ALL);
require_once ('include/config.php');
require_once ('include/functions.php');
require_once ('include/functions_db.php');
require_once ('include/functions_html.php');
require_once ('include/functions_form.php');
require_once ('include/functions_calendar.php');

// Real start
session_start();

// Check user
check_login ();

define ('AJAX', true);

$page = (string) get_parameter ('page');
$page .= '.php';
$config["id_user"] = $_SESSION["id_usuario"];
session_write_close ();
if (file_exists ($page)) {
	require_once ($page);
} else {
	echo "<br><b class='error'>Sorry! I can't find the page $page!</b>";
}
?>
