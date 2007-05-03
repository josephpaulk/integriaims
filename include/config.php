<?PHP
// Begin of automatic config file
$config["dbname"] = "frits";			// MySQL DataBase name
$config["dbuser"] = "root";
$config["dbpassword"] = "none";	// DB Password
$config["dbhost"] = "localhost"; // DB Host
$config["homedir"] = "/var/www/frits/";		// Config homedir
$config["base_url"] = "http://localhost/frits";			// Base URL
// End of automatic config file
?><?php
// FRITS - the FRee Incident Tracking System
// =========================================
// Copyright (c) 2007 Sancho Lerena, slerena@openideas.info
// Copyright (c) 2007 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
// This is the base config file

$config["build_version"]="PC070501"; //PCyymmdd
$config["version"]="v0.9dev";
$config["REMOTE_ADDR"] = getenv ("REMOTE_ADDR");

// Do not display any ERROR
//error_reporting(0);

// Display ALL errors
error_reporting(E_ALL);

// Default font used for graphics (a Free TrueType font included with Pandora FMS)
$config["fontpath"] = $config["homedir"]."/reporting/FreeSans.ttf";

// Read remaining config tokens from DB
if (! mysql_connect($config["dbhost"],$config["dbuser"],$config["dbpassword"])){ 

//Non-persistent connection. If you want persistent conn change it to mysql_pconnect()
	exit ('<html><head><title>FRITS Error</title>
	<link rel="stylesheet" href="./include/styles/main.css" type="text/css">
	</head><body><div align="center">
	<div id="db_f">
		<div>
		<a href="index.php"><img src="images/logo_frits.gif" border="0"></a>
		</div>
	<div id="db_ftxt">
		<h1 id="db_fh1" class="error">FRITS Error DB-001</h1>
		Cannot connect with Database, please check your database setup in the 
		<b>./include/config.php</b> file and read documentation.<i><br><br>
		Probably any of your user/database/hostname values are incorrect or 
		database is not running.</i><br><br><font class="error">
		<b>MySQL ERROR:</b> '. mysql_error().'</font>
		<br>&nbsp;
	</div>
	</div></body></html>');
}
mysql_select_db($config["dbname"]);
$result2 = mysql_query("SELECT * FROM tconfig");
while ($row2 = mysql_fetch_array($result2)){
	switch ($row2["token"]) {
		case "language_code": $config["language_code"] = $row2["value"];
						break;
		case "block_size": $config["block_size"] = $row2["value"];
						break;
		case "days_purge": $config["days_purge"] = $row2["value"];
						break;
		case "bgimage": $config["bgimage"] = $row2["value"];
						break;
	}
}
if ($config["language_code"] == 'ast_es') {
	$config["help_code"]='ast';
} else 
	$config["help_code"] = substr($config["language_code"],0,2);

?>