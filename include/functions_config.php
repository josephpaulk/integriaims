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
		$config['fontsize'] = 7;

	if (!isset ($config["incident_reporter"]))
		$config['incident_reporter'] = 0;

	if (!isset ($config["show_owner_incident"]))
		$config["show_owner_incident"] = 1;
	
	if (!isset ($config["show_creator_incident"]))
		$config["show_creator_incident"] = 1;

	if (!isset ($config["smtp_host"])){
		$config["smtp_host"] = "localhost";
	}

	if (!isset ($config["audit_delete_days"])){
        $config["audit_delete_days"] = 45;
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

	if (empty ($config["language_code"])) {
		$config['language_code'] = get_db_value ('value', 'tconfig', 'token', 'language_code');
	
		if (isset ($_POST['language_code']))
			$config['language_code'] = $_POST['language_code'];
	}	

	if (!isset ($config["flash_charts"])) {
		$config["flash_charts"] = true;
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

	if (!isset ($config["pdffont"]))
		$config["pdffont"] = $config["homedir"]."/include/fonts/FreeSans.ttf";

	if (!isset ($config["font"])){
		$config["font"] = $config["homedir"]."/include/fonts/smallfont.ttf";
	}
	
	// Activity auditing setup
	
	if (!isset ($config["audit_category_default"])) {
		$config["audit_category_default"] = 1;
	}

    if (!isset($config["max_file_size"])) {
        $config["max_file_size"] = "50M";
    }

    ini_set("post_max_size",$config["max_file_size"]);
    ini_set("upload_max_filesize",$config["max_file_size"]);

}

function load_menu_visibility() {
	global $show_projects;
	global $show_incidents;
	global $show_inventory;
	global $show_kb;
	global $show_file_releases;
	global $show_people;
	global $show_todo;
	global $show_agenda;
	global $show_setup;
	global $show_box;

	// Get visibility permissions to sections
	$show_projects = enterprise_hook ('get_menu_section_access', array ('projects'));
	if($show_projects == ENTERPRISE_NOT_HOOK) {
		$show_projects = MENU_FULL;
	}
	$show_incidents = enterprise_hook ('get_menu_section_access', array ('incidents'));
	if($show_incidents == ENTERPRISE_NOT_HOOK) {
		$show_incidents = MENU_FULL;
	}
	$show_inventory = enterprise_hook ('get_menu_section_access', array ('inventory'));
	if($show_inventory == ENTERPRISE_NOT_HOOK) {
		$show_inventory = MENU_FULL;
	}
	$show_kb = enterprise_hook ('get_menu_section_access', array ('kb'));
	if($show_kb == ENTERPRISE_NOT_HOOK) {
		$show_kb = MENU_FULL;
	}
	$show_file_releases = enterprise_hook ('get_menu_section_access', array ('file_releases'));
	if($show_file_releases == ENTERPRISE_NOT_HOOK) {
		$show_file_releases = MENU_FULL;
	}
	$show_people = enterprise_hook ('get_menu_section_access', array ('people'));
	if($show_people == ENTERPRISE_NOT_HOOK) {
		$show_people = MENU_FULL;
	}
	$show_todo = enterprise_hook ('get_menu_section_access', array ('todo'));
	if($show_todo == ENTERPRISE_NOT_HOOK) {
		$show_todo = MENU_FULL;
	}
	$show_agenda = enterprise_hook ('get_menu_section_access', array ('agenda'));
	if($show_agenda == ENTERPRISE_NOT_HOOK) {
		$show_agenda = MENU_FULL;
	}
	$show_setup = enterprise_hook ('get_menu_section_access', array ('setup'));
	if($show_setup == ENTERPRISE_NOT_HOOK) {
		$show_setup = MENU_FULL;
	}
	$sec = get_parameter('sec', '');

	$show_box = ($sec == "projects" && $show_projects == MENU_FULL) || 
				($sec == "incidents" && $show_inciedents == MENU_FULL) || 
				($sec == "inventory" && $show_inventory == MENU_FULL) || 
				($sec == "kb" && $show_kb == MENU_FULL) || 
				($sec == "download" && $show_file_releases == MENU_FULL) || 
				($sec == "users" && $show_people == MENU_FULL) || 
				($sec == "todo" && $show_todo == MENU_FULL) || 
				($sec == "agenda" && $show_agenda == MENU_FULL) || 
				($sec == "godmode" && $show_setup == MENU_FULL) || dame_admin($config['id_user']);
}

?>
