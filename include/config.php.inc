<?PHP
// Begin of automatic config file
$config["dbname"] = "topi";			// MySQL DataBase name
$config["dbuser"] = "root";
$config["dbpassword"] = "4rt1c406";	// DB Password
$config["dbhost"] = "localhost"; // DB Host
$config["homedir"] = "/var/www/topi/";		// Config homedir
$config["base_url"] = "http://192.168.50.2/topi/";			// Base URL
// End of automatic config file
?><?php
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

$config["REMOTE_ADDR"] = getenv ("REMOTE_ADDR");
$config["FOOTER_EMAIL"] = "Please do not respond directly this email, has been automatically created by TOPI (http://topi.sf.net).\n\nThanks for your time and have a nice day\n\n";
$config["HEADER_EMAIL"] = "Hello, \n\nThis is an automated message coming from TOPI\n\n";

// TODO: NEED TO IMPLEMENT IN SETUP THIS TOKENS:
$config["currency"]="€";
$config["hours_perday"] = 8;
$config["sitename"] = "INTEGRIA";

// Do not display any ERROR
//error_reporting(0);

// Display ALL errors
error_reporting(E_ALL);

// Default font used for graphics (a Free TrueType font included with Pandora FMS)
$config["fontpath"] = $config["homedir"]."/reporting/FreeSans.ttf";

include ("config_process.php");
?>
