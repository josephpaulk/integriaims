<?php
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2013 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
	<head>
		<title>I N T E G R I A - Installation Wizard</title>
		<meta http-equiv="expires" content="0">
		<meta http-equiv="content-type" content="text/html; charset=UTF-8">
		<meta name="resource-type" content="document">
		<meta name="distribution" content="global">
		<meta name="author" content="Artica Soluciones Tecnológicas ">
		<meta name="copyright" content="This is GPL software. Created by Artica ST">
		<meta name="keywords" content="network, system, GPL, software">
		<meta name="robots" content="index, follow">
		<link rel="icon" href="images/integria.ico" type="image/ico">
		<link rel="stylesheet" href="include/styles/integria_install.css" type="text/css">
	</head>
	<body>
	<div style='height: 10px'>
	</div>
<?php


error_reporting(0);
$integria_version = "v4.0 Build 130822";

function check_extension ( $ext, $label ){
	echo "<tr><td>";
	echo "<img src='images/arrow.gif'> $label";
	echo "</td><td>";
	if (!extension_loaded($ext)) {
		echo "<img src='images/dot_red.gif'>";
		return 1;
	}
	else {
		echo "<img src='images/dot_green.gif'>";
		return 0;
	}
	echo "</td></tr>";
}

function check_include ( $ext, $label ){
	echo "<tr><td>";
	echo "<img src='images/arrow.gif'> $label";
	echo "</td><td>";
	if (!include($ext)) {
		echo "<img src='images/dot_red.gif'>";
		return 1;
	}
	else {
		echo "<img src='images/dot_green.gif'>";
		return 0;
	}
	echo "</td></tr>";
}

function check_writable ($file, $label ){
	echo "<tr><td>";
	echo "<img src='images/arrow.gif'> $label";
	echo "</td><td>";
	clearstatcache;
	if (!is_writable($file)) {
		echo "<img src='images/dot_red.gif'>";
		return 1;
	}
	else {
		echo "<img src='images/dot_green.gif'>";
		return 0;
	}
	echo "</td></tr>";
}

function check_exists ( $file, $label ){
	echo "<tr><td>";
	echo "<img src='images/arrow.gif'> $label";
	echo "</td><td>";
	if (!file_exists ($file)) {
		echo " <img src='images/dot_red.gif'>";
		return 1;
	}
	else {
		echo " <img src='images/dot_green.gif'>";
		return 0;
	}
	echo "</td></tr>";
}

function check_generic ( $ok, $label ){
	echo "<tr><td>";
	echo "<img src='images/arrow.gif'> $label";
	echo "</td><td>";
	if ($ok == 0 ) {
		echo " <img src='images/dot_red.gif'>";
		return 1;
	}
	else {
		echo " <img src='images/dot_green.gif'>";
		return 0;
	}
	echo "</td></tr>";
}

function check_variable ( $var, $value, $label, $mode ){
	echo "<tr><td>";
	echo "<img src='images/arrow.gif'> $label";
	echo "</td><td>";
	if ($mode == 1) {
		if ($var >= $value){
			echo " <img src='images/dot_green.gif'>";
			return 0;
		}
		else {
			echo " <img src='images/dot_red.gif'>";
			return 1;
		}
	}
	elseif ($var == $value) {
			echo " <img src='images/dot_green.gif'>";
			return 0;
	}
	else {
		echo " <img src='images/dot_red.gif'>";
		return 1;
	}
	echo "</td></tr>";
}

function parse_mysql_dump($url){
	if (file_exists($url)) {
		$file_content = file($url);
		$query = "";
		foreach($file_content as $sql_line){
			if(trim($sql_line) != "" && strpos($sql_line, "--") === false) {
				$query .= $sql_line;
				if(preg_match("/;[\040]*\$/", $sql_line)){
					if (!$result = mysql_query($query)) {
					 	echo mysql_error(); //Uncomment for debug
						echo "<i><br>$query<br></i>";
						return 0;
					}
					$query = "";
				}
			}
		}
		return 1;
	}
	else
		return 0;
}
function random_name ($size){
	$temp = "";
	for ($a=0;$a< $size;$a++)
		$temp = $temp. chr(rand(122,97));
	return $temp;
}

function install_step1() {
	
	global $integria_version;
	
	echo "<div align='center'>
		<div id='wizard' style='height: 400px'> <!-- This has an special height due its minimal size -->
		<div id='install_box'>
		<h2>Welcome to installation Wizard</h2>
		<p>This wizard helps you to quick install Integria in your system.</p>
		<p>In four steps checks all dependencies and make your configuration for a quick installation.</p>";
	
	echo "<table width=100%>";
	$writable = check_writable ( "include", "Checking if ./include is writable");
	if (file_exists("include/config.php"))
		$writable += check_writable ( "include/config.php", "Checking if include/config.php is writable");
	echo "</table>";
	
	echo "<p>For more information, please refer to documentation or visit our free forums at <a href='http://integriaims.com/forums'>http://integriaims.com/forums</a>.<br><br><i>Integria Development team</i><br>";
	
	if (file_exists("include/config.php")) {
		echo "<p><br><img src='images/error.png' valign='bottom'><b> Warning</b> - You already have a config.php file. Configuracion and database would be overwritten if you continue.</b></p>";
	}
	echo "</div>
	<div class='box'>
		<img src='images/integria_white.png'>
		<br><br>
	</div>
	<div class='box'>
		<img src='images/step0.png'>";
	echo "<br><br><font size=1px>$integria_version</font> 
	</div>
	<div id='install_box' style='margin-bottom: 25px;margin-left: 25px;'>";
	if ($writable == 0)
		echo "<a href='install.php?step=1'><img align='right' src='images/arrow_next.png'></a>";
	else
		echo "<p><img src='images/info.png' valign='bottom'><b> Error</b> - You need to setup permissions to be able to write in ./include directory";
		
	echo "</div></div>";
}


function install_step1_licence() {
	global $integria_version;
	
	echo "
	<div align='center'>
	<div id='wizard'>
		<div id='install_box'>";

echo '
<h2>GPL2 Licence terms agreement</h2>
			<p>Integria IMS is an OpenSource software project licensed under the GPL2 licence. Integria IMS includes, as well, another software also licensed under LGPL and BSD licenses. Before continue, <i>you must accept the licence terms.</i>.
			<p>For more information, please refer to our website at http://integriaims.com and contact us if you have any kind of question about the usage of Integria IMS</p>
<p>If you dont accept the licence terms, please, close your browser and delete Integria IMS files.</p>';

if (!file_exists("COPYING")){
		echo "<div class='warn'><b>Licence file 'COPYING' is not present in your distribution. This means you have some 'partial' Pandora FMS distribution. We cannot continue without accepting the licence file.";
		echo "</div>";
	} else {
		echo "<br>";
		echo "<form method=post action='install.php?step=2'>";
		echo "<textarea name='gpl2' cols=60 rows=19>";
		echo file_get_contents ("COPYING");
		echo "</textarea>";
		echo "<p>";
		echo "<input type=submit value='Yes, I accept licence terms'>";
	}
    echo "</div>";
    echo "<div class='box'>
			<img src='images/integria_white.png' alt=''>
			<br><br>
			<font size=1px>".$integria_version."</font>
		</div>
		<div class='box'>
			<img src='images/step1.png' alt=''>
		</div>
		<div id='install_box' style='margin-bottom: 0px;margin-left: 25px; '>";
		echo "</div></div>";
}

function install_step2() {
	global $integria_footertext;
	global $integria_version;

	echo "
	<div align='center'>
	<div id='wizard' >
		<div id='install_box'>"; 
		echo "<h1>Checking software dependencies</h1>";
			echo "<table border='0' width='330' cellpadding='5' cellspacing='5'>";
			$res = 0;
			$res += check_variable(phpversion(),"4.3","PHP version >= 4.3.x",1);
			$res += check_extension("mysql","PHP MySQL extension");
			$res += check_extension("gd","PHP gd extension");	
			$res += check_extension("session","PHP session extension");
			$res += check_extension("mbstring","PHP multibyte extension");
			$res += check_extension("ldap","PHP ldap extension");
			$res += check_extension("gettext","PHP gettext extension");
			$res += check_extension("imap","PHP IMAP extension");
			$res += check_extension("gettext","PHP gettext extension");
			$res += check_extension("Phar","PHP Phar extension");
			//$res += check_include("PEAR.php","PEAR PHP Library");
			$res += check_writable("./include","./include writable by HTTP server");
            $res += check_writable("./attachment/tmp","./attachment/tmp writable by HTTP server");
			echo "</table>
		</div>
		<div class='box'>
			<img src='images/integria_white.png' alt=''>
			<br><br>
			<font size=1px>".$integria_version."</font>
		</div>
		<div class='box'>
			<img src='images/step1.png' alt=''>
		</div>
		<div id='install_box' style='margin-bottom: 0px;margin-left: 25px; '>";
			if ($res > 0) {
				echo "<p><img src='images/info.png'> You have some uncomplete 
				dependencies. Please correct it or this installer 
				could not finish your installation.
				</p>
				Ignore it. <a href='install.php?step=3'>Force install Step #3</a>";
			} else {
				echo "<a href='install.php?step=3'><img align='right' src='images/arrow_next.png' alt=''></a>";
			}
			echo "</div></div>";
}

function install_step3() {
	global $integria_version;

	echo "
	<div align='center'>
	<div id='wizard'>
		<div id='install_box'>
			<h1>Database setup</h1>
			<p>
				This wizard will (optionally) create your Integria IMS database, and populate it with data needed to run for first time.
				You need a privileged user to create database schema, this is usually root user. 
				Information about <i>root</i> user will not be used or stored in anywhere. 
			</p>
			<p>
				Please <b>notice</b> that database will be destroyed if already exists!.<br><br>
			</p>
			<div style='padding-left: 30px;'>
			<form method='post' action='install.php?step=4'>
				<div style='margin: 10px; margin-left: 0px;'>MySQL user to create database schema<br></div>
				<input type='text' name='user' value='root'>
				
				<div style='margin: 10px; margin-left: 0px;'>DB Password for this user</div>
				<input type='password' name='pass' value=''>
				
				<div style='margin: 10px; margin-left: 0px;'>DB Hostname of MySQL</div>
				<input type='text' name='host' value='localhost'>
				
				<div style='margin: 10px; margin-left: 0px;'>DB Name (<i>integria</i> by default)</div>
				<input type='text' name='dbname' value='integria'>
				
				
				<div style='padding: 8px;'><input type='checkbox' name='createdb' checked value='1'>  
				Create Database <br>
				</div>
				
				<div style='padding: 8px'><input type='checkbox' name='demodb' checked value='1'>  
				Load demo Database (This will load a sample site)<br>
				</div>
				
				<div style='padding: 8px'><input type='checkbox' name='createuser' checked value='1'> Create Database user 'integria' and give privileges <br>
				</div>
			
				<div style='margin: 10px; margin-left: 0px;'>Full path to HTTP publication directory.<br>
				<span class='f9b'>For example /var/www/integria/</span>
				</div>";

				
				// if windows
				if (PHP_OS == 'WINNT') {
					$PATH = dirname (__FILE__);
					$PATH2 = str_replace('\\','/',$PATH);
					echo "<input type='text' name='path' style='width: 190px;' value='".$PATH2."/'>";
				} else {
					echo "<input type='text' name='path' style='width: 190px;' value='".dirname (__FILE__)."/'>";
				}
				
				echo "
				<div style='margin: 10px; margin-left: 0px;'>Full local URL to Integria<br>
				<span class='f9b'>For example http://10.10.10.1/integria</span>
				</div>				
				<input type='text' name='url' style='width: 250px;'  value='http://".$_SERVER["SERVER_NAME"].dirname ($_SERVER['PHP_SELF']) ."'>
				<br><br>
				
				<div style='margin: 10px; margin-left: 0px;'><input align='right' style='align: right; width:70px; height: 16px;' type='image' src='images/arrow_next.png'  value='Step #4'></div>
			</form>
			</div>
			</div>
			<div class='box'>
				<img src='images/integria_white.png' alt=''>
				<br><br>
				<font size=1px>". $integria_version ."</font>
			</div>
			<div class='box'>
				<img src='images/step2.png' alt=''>
			</div>
		</div>";
}



function install_step4() {
	$INTEGRIA_config = "include/config.php";
	global $integria_footertext;
	global $integria_version;
	
	if ( (! isset($_POST["user"])) || (! isset($_POST["dbname"])) || (! isset($_POST["host"])) || (! isset($_POST["pass"])) ) {
		$dbpassword = "";
		$dbuser = "";
		$dbhost = "";
		$dbname = "";
	}
	else {
		$dbpassword = $_POST["pass"];
		$dbuser = $_POST["user"];
		$dbhost = $_POST["host"];
		
		if (isset($_POST["demodb"]))
			$demodb = $_POST["demodb"];
		else
			$demodb = 0;
		
		if (isset($_POST["createdb"]))
			$createdb = $_POST["createdb"];
		else
			$createdb = 0;
		if (isset($_POST["createuser"]))
			$createuser = $_POST["createuser"];
		else
			$createuser = 0;
		
		$dbname = $_POST["dbname"];
		if (isset($_POST["url"]))
			$url = $_POST["url"];
		else
			$url = "http://localhost";
		if (isset($_POST["path"]))
			$path = $_POST["path"];
		else
			$path = "/var/www/";
	}
	$everything_ok = 0;
	$step1=0;
	$step2=0;
	$step3=0;
	$step4=0;
	$step5=0;
	$step6=0;
	$step7=0;
	echo "
	<div align='center' class='mt35'>
	<div id='wizard' style=''>
		<div id='install_box' style='float: right;'>
			<h1>Creating database and default configuration file</h1>
			<table style='background: #ffffff;'>";
	if (! mysql_connect ($dbhost,$dbuser,$dbpassword)) {
		check_generic ( 0, "Connection with Database");
	}
	else {
		check_generic ( 1, "Connection with Database");
		// Create schema
		if ($createdb == 1) {
			$step1 = mysql_query ("CREATE DATABASE $dbname");
			check_generic ($step1, "Creating database '$dbname'");
		}
		else $step1 =1;
		if ($step1 == 1) {
			$step2 = mysql_select_db($dbname);
			check_generic ($step2, "Opening database '$dbname'");
			
			$step3 = parse_mysql_dump("integria_db.sql");
			check_generic ($step3, "Creating schema");
			
			// populate database with a blank DB or DEMO database ?
			if ($demodb == 1)
				$step4 = parse_mysql_dump("integria_demo.sql");
			else
				$step4 = parse_mysql_dump("integria_dbdata.sql");
			
			check_generic ($step4, "Populating database");
			
			$random_password = random_name (8);
			if ($createuser==1) {
				$query = 
					"GRANT ALL PRIVILEGES ON $dbname.* to integria@localhost  IDENTIFIED BY '".$random_password."'";
				$step5 = mysql_query ($query);
				mysql_query ("FLUSH PRIVILEGES");
				check_generic ($step5,
					"Established privileges for user 'integria' on database '$dbname', with password <i>'$random_password'</i>");
			}
			else $step5=1;
			
			$step6 = is_writable("include");
			check_generic ($step6, "Write permissions to save config file in './include'");
			
			$cfgin = fopen ("include/config.inc.php", "r");
			$cfgout = fopen ($INTEGRIA_config, "w");
			$config_contents = fread ($cfgin, filesize("include/config.inc.php"));
			
			//---INIT--- CONFIG FILE
			$config_new = '<?php' . "\n".
				'// Begin of automatic config file' . "\n".
				'$config["dbuser"]=';
			if ($createuser == 1) {
				$config_new = $config_new . '"integria";' . "\n".
					'$config["dbpass"]="'.$random_password.'";	// DB Password' . "\n";
			}
			else {
				$config_new = $config_new . '"'.$dbuser.'";' . "\n".
					'$config["dbpass"]="'.$dbpassword.'";	// DB Password' . "\n";
			}
			$config_new = $config_new . "\n" . 
				'$config["dbname"]="'.$dbname.'";    // MySQL DataBase name' . "\n". 
				'$config["dbhost"]="'.$dbhost.'";    // DB Host' . "\n".
				'$config["homedir"]="'.$path.'";		// Config homedir' . "\n".
				'$config["base_url"]="'.$url.'";		// Public URL' . "\n".
				'// End of automatic config file' . "\n".
				'?>';
			//---END--- CONFIG FILE
			
			$step7 = fputs ($cfgout, $config_new);
			$step7 = $step7 + fputs ($cfgout, $config_contents);
			if ($step7 > 0)
				$step7 = 1;
			
			fclose ($cfgin);
			fclose ($cfgout);
			check_generic ($step7, "Created new config file at '".$INTEGRIA_config."'");
		}
		
		mysql_select_db($dbname);
		$step8 = parse_mysql_dump("include/update_manager/sql/update_manager.sql");
		check_generic ($step8, "Populating database with update manager");
		
		if ($step8) {
			mysql_query("UPDATE um_tupdate_settings SET `value` = '" . $dbhost . "' WHERE `key` = 'dbhost';");
			mysql_query("UPDATE um_tupdate_settings SET `value` = '" . $dbname . "' WHERE `key` = 'dbname';");
			mysql_query("UPDATE um_tupdate_settings SET `value` = '" . $random_password . "' WHERE `key` = 'dbpass';");
			mysql_query("UPDATE um_tupdate_settings SET `value` = 'babel' WHERE `key` = 'dbuser';");
			mysql_query("UPDATE um_tupdate_settings SET `value` = '" . dirname ($_SERVER['SCRIPT_FILENAME']) . "' WHERE `key` = 'updating_code_path';");
		}
	}
	
	if (($step8 + $step7 + $step6 + $step5 + $step4 + $step3 + $step2 + $step1) == 8) {
		$everything_ok = 1;
	}
	
	echo "</table></div>
		<div class='box' style='float: left;'>
			<img src='images/integria_white.png' alt=''>
			<br><br>
			<font size=1px>".$integria_version."</font>
		</div>
		
		<div class='box' style='float: left;'>
			<img src='images/step3.png' alt=''>
		</div>
		
		<div id='install_box' style='clear: both; margin-bottom: 25px;margin-left: 25px;'>
		
		<p>";
	
	if ($everything_ok == 1) {
		echo "<a href='install.php?step=5'><img align='right' src='images/arrow_next.png' class=''></a>";
	}
	else {
		echo "<img src='images/info.png'> You get some problems. Installation is not completed. 
			<p>Please correct failures before trying again. All database schemes created in this step have been dropped.</p>";
		
		if (mysql_error() != "")
			echo "<p><img src='images/info.png'> <b>ERROR:</b> ". mysql_error()."</p>";
		
		if ($createdb == 1) {
				mysql_query ("DROP DATABASE $dbname");
		}
	}
	
	echo "</div>
		<div style='clear: both; width: 100%;'></div>
		</div></div>";
}

function install_step5() {
	global $integria_version;
	
	echo "
	<div align='center' class='mt35'>
	<div id='wizard' style='height: 300px;'>
		<div id='install_box'>
			<h1>Installation complete</h1>
			<p>This installer will try to rename itself to 'install_renamed.php'. You should delete it  manually for security before trying to access to your Integria installation.
<br><br>
				<b>Please don't forget to install manually the Integria Scheduler</b> (crontab)
in order to run Integria IMS properly. Check out the documentation on how to do it.
<br>
			<p><a href='index.php'><b>Click here to access Integria</b></A>, Use the user '<b>admin</b>' with password '</b>integria</b>' to enter and change the password as soon as possible.</p>
		</div>
		<div class='box'>
			<img src='images/integria_white.png'></a>
			<br><br>			
			<font size=1px>".$integria_version."</font>
		</div>
		<div class='box'>
			<img src='images/step4.png'><br>
		</div>
	</div></div>";
	chmod ('include/config.php', 0600);
	rename ('install.php', 'install_renamed.php');
}

// ---------------
// Main page code
// ---------------

if (! isset ($_GET["step"])){
	install_step1 ();
}
else {
	$step = (int) $_GET["step"];
	switch ($step) {
    	case 1: 
        	install_step1_licence();
        	break;
	case 2:
		install_step2();
		break;
	case 3:
		install_step3();
		break;
	case 4:
		install_step4();
		break;
	case 5:
		install_step5();
		break;
	}
}

// Show footer
?>

<div id='footer' style='width: 100%;'>
       <i>Integria IMS <?php echo $integria_version; ?> <br> This is an OpenSource Software project at
       <a target='_new' href='http://integriaims.com'>http://integriaims.com</a></i><br>
       <a href='http://www.artica.es'>(c) Ártica Soluciones Tecnológicas</a><br>
</div>
</div></body></html>
