<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2009 Ártica Soluciones Tecnológicas
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
$config["build"]="100712";
$config["version"]="v2.1";
$config["build_version"] = $config["build"];

if (! defined ('ENTERPRISE_DIR'))
	define ('ENTERPRISE_DIR', 'enterprise');

// Detect enterprise version
// NOTE: If you override this value without enterprise code, you will break 
// the code and get several problems!

if (file_exists($config["homedir"]."/".ENTERPRISE_DIR."/include/functions_db.php"))
	$config["enteprise"] = 1;
else
	$config["enteprise"] = 0;



// Read remaining config tokens from DB
if (! mysql_connect ($config["dbhost"], $config["dbuser"], $config["dbpass"])) {
	include ($config["homedir"]."/general/error_databaseconnect.php");
	exit;
}

mysql_select_db ($config["dbname"]);

require_once ($config["homedir"].'/include/functions.php');
require_once ($config["homedir"].'/include/functions_db.php');
require_once ($config["homedir"].'/include/functions_config.php');
require_once ('streams.php');
require_once ('gettext.php');

// Load config from database
load_config();

$l10n = NULL;
if (file_exists ('./include/languages/'.$config['language_code'].'.mo')) {
	$l10n = new gettext_reader (new CachedFileReader ('./include/languages/'.$config['language_code'].'.mo'));
	$l10n->load_tables ();
}

// Set a the system timezone default 
if ((!isset($config["timezone"])) OR ($config["timezone"] == "")){
        $config["timezone"] = "Europe/Berlin";
}


date_default_timezone_set($config["timezone"]);

include_once ($config["homedir"]."/include/functions_html.php");
include_once ($config["homedir"]."/include/functions_form.php");
include_once ($config["homedir"]."/include/functions_calendar.php");

?>
