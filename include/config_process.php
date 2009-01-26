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

// Integria version
$config["build"]="081210";
$config["version"]="v2.0beta1";
$config["build_version"] = $config["build"];

if (! defined ('ENTERPRISE_DIR'))
	define ('ENTERPRISE_DIR', 'enterprise');

// Detect enterprise version
if (file_exists($config["homedir"]."/".ENTERPRISE_DIR."/include/functions_db.php"))
	$config["enteprise"] = 1;
else
	$config["enteprise"] = 0;


// Read remaining config tokens from DB
if (! mysql_connect ($config["dbhost"], $config["dbuser"], $config["dbpass"])) {
	//Non-persistent connection. If you want persistent conn change it to mysql_pconnect()
	exit ('<html><head><title>Integria Error</title>
		<link rel="stylesheet" href="'.$config["base_url"].'/include/styles/integria.css" type="text/css">
		</head><body><div align="center">
		<div id="db_f">
		<div>
		<a href="index.php"><img src="'.$config["base_url"].'/images/integria_white.png" border="0"></a>
		</div>
		<div id="db_ftxt">
		<h1 id="log_f" class="error">Integria Error DB-001</h1>
		Cannot connect with Database, please check your database setup in the 
		<b>./include/config.php</b> file and read documentation.<i><br><br>
		Probably any of your user/database/hostname values are incorrect or 
		database is not running.</i><br><br><font class="error">
		<b>MySQL ERROR:</b> '. mysql_error().'</font>
		<br>&nbsp;
		</div>
		</div></body></html>');
}

mysql_select_db ($config["dbname"]);

require_once ($config["homedir"].'/include/functions.php');
require_once ($config["homedir"].'/include/functions_db.php');

$configs = get_db_all_rows_in_table ('tconfig');
if ($configs === false) {
	exit ('<html><head><title>Integria Error</title>
		<link rel="stylesheet"  href="'.$config["base_url"].'/include/styles/integria.css" type="text/css">
		</head><body><div align="center">
		<div id="db_f">
		<div>
		<a href="index.php"><img src="'.$config["base_url"].'/images/integria_white.png" border="0"></a>
		</div>
		<div id="db_ftxt">
		<h1 id="log_f" class="error">Integria Error DB-002</h1>
		Cannot load configuration variables. Please check your database setup in the
		<b>./include/config.php</b> file and read documentation.<i><br><br>
		Probably database schema is created but there are no data inside it or you have a problem with DB access credentials.
		</i><br>
		</div>
		</div></body></html>');
}

foreach ($configs as $c) {
	$config[$c["token"]] = $c["value"];
}

require_once ('streams.php');
require_once ('gettext.php');

if (isset ($_SESSION['id_usuario']))
	$config['language_code'] = get_db_value ('lang', 'tusuario', 'id_usuario', $_SESSION['id_usuario']);

if (empty ($config["language_code"])) {
	$config['language_code'] = get_db_value ('value', 'tconfig', 'token', 'language_code');
	
	if (isset ($_POST['language_code']))
		$config['language_code'] = $_POST['language_code'];
}
$l10n = NULL;
if (file_exists ('./include/languages/'.$config['language_code'].'.mo')) {
	$l10n = new gettext_reader (new CachedFileReader ('./include/languages/'.$config['language_code'].'.mo'));
	$l10n->load_tables ();
}

if (!isset($config["notification_period"]))
	$config["notification_period"] = "86400";

if (!isset ($config["autowu_completion"]))
	$config["autowu_completion"] = "0";

if (!isset ($config["no_wu_completion"]))
	$config["no_wu_completion"] = "";

if (!isset ($config["FOOTER_EMAIL"]))
	$config["FOOTER_EMAIL"] = __('Please do NOT answer this email, it has been automatically created by Integria (http://integria.sourceforge.net).');

if (!isset ($config["HEADER_EMAIL"]))
	$config["HEADER_EMAIL"] = "Hello, \n\nThis is an automated message coming from Integria\n\n";

if (!isset ($config["currency"]))
	$config["currency"] = "€";

if (!isset ($config["hours_perday"]))
	$config["hours_perday"] = 8;

if (!isset ($config["limit_size"]))
	$config["limit_size"] = 1000;

if (!isset ($config["sitename"]))
	$config["sitename"] = "INTEGRIA";

if (!isset ($config["fontsize"]))
	$config['fontsize'] = 10;

// Mail address used to send mails
if (!isset ($config["mail_from"]))
	$config["mail_from"] = "integria@localhost";

include_once ($config["homedir"]."/include/functions.php");
include_once ($config["homedir"]."/include/functions_html.php");
include_once ($config["homedir"]."/include/functions_form.php");
include_once ($config["homedir"]."/include/functions_calendar.php");

?>
