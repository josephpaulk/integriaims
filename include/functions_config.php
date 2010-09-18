<?php
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008-2010 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.



function load_config(){

	global $config;
	require_once ($config["homedir"].'/include/functions_db.php');

	$configs = get_db_all_rows_in_table ('tconfig');

	if ($configs === false) {
		include ($config["homedir"]."/general/error_invalidconfig.php");
		exit;
	}

	foreach ($configs as $c) {
		$config[$c["token"]] = $c["value"];
	}

	if (!isset ($config["block_size"]))
		$config["block_size"] = 25;

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

	if (!isset ($config["incident_reporter"]))
		$config['incident_reporter'] = 0;

	if (!isset ($config["show_owner_incident"]))
		$config["show_owner_incident"] = 1;
	
	if (!isset ($config["show_creator_incident"]))
		$config["show_creator_incident"] = 1;

	if (!isset ($config["smtp_host"])){
		$config["smtp_host"] = "localhost";
	}

	if (!isset ($config["iwu_defaultime"])){
		$config["iwu_defaultime"] = "0.25";
	}

	if (!isset ($config["pwu_defaultime"])){
		$config["pwu_defaultime"] = "4";
	}

	if (!isset ($config["timezone"])){
		$config["timezone"] = "Europe/Madrid";
	}

	if (!isset ($config["api_acl"])){
		$config["api_acl"] = "127.0.0.1";
	}

	if (!isset ($config["auto_incident_close"])){
		$config["auto_incident_close"] = "72";
	}

	if (isset ($_SESSION['id_usuario']))
		$config['language_code'] = get_db_value ('lang', 'tusuario', 'id_usuario', $_SESSION['id_usuario']);

	if (empty ($config["language_code"])) {
		$config['language_code'] = get_db_value ('value', 'tconfig', 'token', 'language_code');
	
		if (isset ($_POST['language_code']))
			$config['language_code'] = $_POST['language_code'];
	}	

	// Mail address used to send mails
	if (!isset ($config["mail_from"]))
		$config["mail_from"] = "integria@localhost";

    if (!isset ($config["site_logo"])){
         $config["site_logo"] = "integria_logo.png";
    }

    if (!isset ($config["header_logo"])){
         $config["header_logo"] = "integria_logo_header.png";
    }

    if (!isset ($config["email_on_incident_update"])){
		$config["email_on_incident_update"] = 0;
    }

	if (!isset ($config["error_log"])){
		$config["error_log"] = 1;
    }
	
	if (!isset ($config["sql_query_limit"]))
		$config["sql_query_limit"] = 1500;

}

?>
