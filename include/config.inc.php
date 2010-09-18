<?php
//  __         __                        __            __                  
// |__|.-----.|  |_ .-----..-----..----.|__|.---.-.   |__|.--------..-----.
// |  ||     ||   _||  -__||  _  ||   _||  ||  _  |   |  ||        ||__ --|
// |__||__|__||____||_____||___  ||__|  |__||___._|   |__||__|__|__||_____|
//                         |_____|                                         
// ============================================================================
// Copyright (c) 2007-2010 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2010 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

/* Sample tokens
$config["dbname"] = "integria";         // MySQL DataBase name
$config["dbuser"] = "integria";
$config["dbpass"] = "integria"; // DB Password
$config["dbhost"] = "localhost"; // DB Host
$config["homedir"] = "/var/www/integria/";      // Config homedir
$config["base_url"] = "http://mydomain.net/integria";       // Base URL
*/

// TODO: NEED TO IMPLEMENT IN SETUP THIS TOKENS:

$config["REMOTE_ADDR"] = getenv ("REMOTE_ADDR");
$config["FOOTER_EMAIL"] = "Please do not respond directly this email, has been automatically created by Integria (http://integria.sourceforge.net).\n\nThanks for your time and have a nice day\n\n";
$config["HEADER_EMAIL"] = "Hello, \n\nThis is an automated message coming from Integria\n\n";
$config["currency"]="€";
$config["hours_perday"] = 8;
$config["sitename"] = "INTEGRIA";

// Do not display any ERROR
//error_reporting(0);

// Display ALL errors
error_reporting(E_ALL);

// Default font used for graphics (a Free TrueType font included with Pandora FMS)
$config["fontpath"] = $config["homedir"]."/include/FreeSans.ttf";

include ($config["homedir"]."/include/config_process.php");
?>
