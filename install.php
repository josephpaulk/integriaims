<?PHP
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



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>I N T E G R I A - Installation Wizard</title>
<meta http-equiv="expires" content="0">
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta name="resource-type" content="document">
<meta name="distribution" content="global">
<meta name="author" content="Sancho Lerena">
<meta name="copyright" content="This is GPL software. Created by Sancho Lerena">
<meta name="keywords" content="network, system, GPL, software">
<meta name="robots" content="index, follow">
<link rel="icon" href="images/integria.ico" type="image/ico">
<link rel="stylesheet" href="include/styles/integria.css" type="text/css">
</head>
<body background='images/backgrounds/background11.jpg'>
<div style='height: 10px'>
</div>
<?PHP


error_reporting(0);

$integria_version = "1.2-dev";

$integria_footertext = "<div id='foot'>
                        <i>Integria is an OpenSource Software project 
                        <a target='_new' href='http://integriaims.com'>integriaims.com</a></i><br>
                        <a href='http://www.artica.es'>(c) Ártica Soluciones Tecnológicas</a><br>
                        </div>";

function check_extension ( $ext, $label ){
	echo "<tr><td>";
	echo "<img src='images/arrow.gif'> $label";
	echo "</td><td>";
	if (!extension_loaded($ext)){
		echo "<img src='images/dot_red.gif'>";
		return 1;
	} else {
		echo "<img src='images/dot_green.gif'>";
		return 0;
	}
	echo "</td></tr>";
}

function check_include ( $ext, $label ){
	echo "<tr><td>";
	echo "<img src='images/arrow.gif'> $label";
	echo "</td><td>";
	if (!include($ext)){
		echo "<img src='images/dot_red.gif'>";
		return 1;
	} else {
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
	} else {
		echo "<img src='images/dot_green.gif'>";
		return 0;
	}
	echo "</td></tr>";
}

function check_exists ( $file, $label ){
	echo "<tr><td>";
	echo "<img src='images/arrow.gif'> $label";
	echo "</td><td>";
	if (!file_exists ($file)){
		echo " <img src='images/dot_red.gif'>";
		return 1;
	} else {
		echo " <img src='images/dot_green.gif'>";
		return 0;
	}
	echo "</td></tr>";
}

function check_generic ( $ok, $label ){
	echo "<tr><td>";
	echo "<img src='images/arrow.gif'> $label";
	echo "</td><td>";
	if ($ok == 0 ){
		echo " <img src='images/dot_red.gif'>";
		return 1;
	} else {
		echo " <img src='images/dot_green.gif'>";
		return 0;
	}
	echo "</td></tr>";
}

function check_variable ( $var, $value, $label, $mode ){
	echo "<tr><td>";
	echo "<img src='images/arrow.gif'> $label";
	echo "</td><td>";
	if ($mode == 1){
		if ($var >= $value){
			echo " <img src='images/dot_green.gif'>";
			return 0;
		} else {
			echo " <img src='images/dot_red.gif'>";
			return 1;
		}
	} elseif ($var == $value){
			echo " <img src='images/dot_green.gif'>";
			return 0;
	} else {
		echo " <img src='images/dot_red.gif'>";
		return 1;
	}
	echo "</td></tr>";
}

function parse_mysql_dump($url){
	if (file_exists($url)){
   		$file_content = file($url);
   		$query = "";
   		foreach($file_content as $sql_line){
			if(trim($sql_line) != "" && strpos($sql_line, "--") === false){
				$query .= $sql_line;
				if(preg_match("/;[\040]*\$/", $sql_line)){
					if (!$result = mysql_query($query))
						return 0;
					$query = "";
				}
			}
		}
		return 1;
	} else {
		return 0;
	}
}

function random_name ($size){
	$temp = "";
	for ($a=0;$a< $size;$a++)
		$temp = $temp. chr(rand(122,97));
	return $temp;
}

function install_step1() {
    global $integria_footertext;
	global $integria_version;

	echo "
	<div align='center' '>
	<h1>Integria IMS $integria_version instalation wizard. Step #1 of 4</h1>
	<div id='wizard' style='height: 430px;'>
		<div id='install_box'>
			<h1>Welcome to Integria $integria_version installation Wizard</h1>
			<p>This wizard helps you to quick install Integria in your system.</p>
			<p>In four steps checks all dependencies and make your configuration for a quick installation.</p>";

		echo "<table width=100%>";
		$writable = check_writable ( "include", "Checking if ./include is writable");
		if (file_exists("include/config.php"))
			$writable += check_writable ( "include/config.php", "Checking if include/config.php is writable");
		echo "</table>";

		echo "<p>For more information, please refer to documentation.</p>
			<i>Integria Development team</i>";

		if (file_exists("include/config.php")){
			echo "<p><img src='images/info.png' valign='bottom'><b> Warning: You already have a config.php file. Configuracion and database would be overwritten if you continue.</b></p>";
		}
		echo "
		</div>
		<div class='box'>
			<img src='images/integria_white.png' border='0'>
			<br><br>
		</div>
		<div class='box'>
			<img src='images/step0.png' border='0'>
		</div>
		<div id='install_box' style='margin-bottom: 25px;margin-left: 25px;'>";
		if ($writable == 0)
			echo "
			<a href='install.php?step=2'><img align='right' src='images/arrow_next.png' border='0'></a>";
		else
			echo "<div class='warn'><b>ERROR:</b>You need to setup permissions to be able to write in ./include directory</div>";


		echo "
			</div>
		</div>

		$integria_footertext
	</div>";
}



function install_step2() {
    global $integria_footertext;
	global $integria_version;

	echo "
	<div align='center'>
	<h1>Integria IMS $integria_version instalation wizard. Step #2 of 4</h1>
	<div id='wizard' style='height: 380px;'>
		<div id='install_box'>";
		echo "<h1>Checking software dependencies</h1>";
			echo "<table border=0 width=330 cellpadding=5 cellspacing=5>";
			$res = 0;
			$res += check_variable(phpversion(),"4.3","PHP version >= 4.3.x",1);
			$res += check_extension("mysql","PHP MySQL extension");
			$res += check_extension("gd","PHP gd extension");	
			$res += check_extension("session","PHP session extension");
			$res += check_include("PEAR.php","PEAR PHP Library");
			$res += check_exists ("/usr/bin/twopi","Graphviz Twopi in /usr/bin/twopi");
			$res += check_extension("gettext","PHP gettext extension");
		//$res += check_include("PEAR.php","PEAR PHP Library");
			$res += check_include("DB.php","PEAR:DB PHP Library");
			$res += check_include("XML/RPC.php","PEAR XML/RPC.php PHP Library");
			$res += check_writable("./include","./include writable by HTTP server");
            $res += check_writable("./attachment/tmp","./attachment/tmp writable by HTTP server");
			echo "</table>
		</div>
		<div class='box'>
			<img src='images/integria_white.png' border='0'' alt=''>
			<br><br>
		</div>
		<div class='box'>
			<img src='images/step1.png' border='0' alt=''>
		</div>
		<div id='install_box' style='margin-bottom: 0px;margin-left: 25px; '>";
			if ($res > 0) {
				echo "<p><img src='images/info.png'> You have some uncomplete 
				dependencies. Please correct it or this installer 
				could not finish your installation.
				</p>
				Ignore it. <a href='install.php?step=3'>Force install Step #3</a>";
			} else {
				echo "<a href='install.php?step=3'><img align='right' src='images/arrow_next.png' border=0 alt=''></a>";
			}
			echo "
		</div>
		</div>
		$integria_footertext
        </div>";
}

function install_step3() {
	global $integria_footertext;
	global $integria_version;

	echo "
	<div align='center''>
	<h1>Integria $integria_version instalation wizard. Step #3 of 4 </h1>
	<div id='wizard' style='height: 670px;'>
		<div id='install_box'>
			<h1>Environment and database setup</h1>
			<p>
			This wizard will create your Integria database, and populate it with data needed to run for first time.
			You need a privileged user to create database schema, this is usually root user. 
			Information about <i>root</i> user will not be used or stored in anywhere. 
			</p>
			<p>
			<b>NOTICE</b> that database will be destroyed if already exists!.
			</p>
			<div style='padding-left: 30px'>
			<form method='post' action='install.php?step=4'>
				<div>DB User with privileges on MySQL</div>
				<input class='login' type='text' name='user' value='root'>

				<div>DB Password for this user</div>
				<input class='login' type='password' name='pass' value=''>
				
				<div>DB Hostname of MySQL</div>
				<input class='login' type='text' name='host' value='localhost'>

				<div>DB Name (<i>integria</i> by default)</div>
				<input class='login' type='text' name='dbname' value='integria'>
				
		
				<div><input type='checkbox' name='createdb' checked value='1'>  
				Create Database <br>
				</div>
		
				<div><input type='checkbox' name='createuser' checked value='1'> Create Database user 'integria' and give privileges <br>
				</div>		
			
				<div>Full path to HTTP publication directory.<br>
				<span class='f9b'>For example /var/www/integria</span>
				</div>
				<input class='login' type='text' name='path' style='width: 190px;' value='".dirname (__FILE__)."'>

				<div>Full local URL to Integria<br>
				<span class='f9b'>For example /integria</span>
				</div>
				<input class='login' type='text' name='url' style='width: 250px;'  value='".dirname ($_SERVER['PHP_SELF'])."'>
				
				<div><input align='right' style='align: right; width:70px; height: 16px;' type='image' src='images/arrow_next.png'  value='Step #4'></div>
			</form>
			</div>
			</div>
			<div class='box'>
				<img src='images/integria_white.png' border='0' alt=''>
				<br><br>
			</div>
			<div class='box'>
				<img src='images/step2.png' border='0' alt=''>
			</div>
		</div>
		$integria_footertext
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
	} else {
		$dbpassword = $_POST["pass"];
		$dbuser = $_POST["user"];
		$dbhost = $_POST["host"];
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
			$path = "/var/www";
	}
	$everything_ok = 0;
	$step1=0;
	$step2=0;
	$step3=0;
	$step4=0; $step5=0; $step6=0; $step7=0;
	echo "
	<div align='center' class='mt35'>
	<h1>Integria IMS $integria_version instalation wizard. Step #4 of 4 </h1>
	<div id='wizard' style='height: 410px;'>
		<div id='install_box'>
			<h1>Creating database and default configuration file</h1>
			<table>";
			if (! mysql_connect ($dbhost,$dbuser,$dbpassword)) {
				check_generic ( 0, "Connection with Database");
			} else {
				check_generic ( 1, "Connection with Database");
				// Create schema
				if ($createdb == 1){
					$step1 = mysql_query ("CREATE DATABASE $dbname");
					check_generic ($step1, "Creating database '$dbname'");
				} else 
					$step1 =1;
				if ($step1 == 1){
					$step2 = mysql_select_db($dbname);
					check_generic ($step2, "Opening database '$dbname'");
	
					$step3 = parse_mysql_dump("integria_db.sql");
					check_generic ($step3, "Creating schema");
			
					$step4 = parse_mysql_dump("integria_dbdata.sql");
					check_generic ($step4, "Populating database");
	
					$random_password = random_name (8);
					if ($createuser==1){
						$query = 
						"GRANT ALL PRIVILEGES ON $dbname.* to integria@localhost  IDENTIFIED BY '".$random_password."'";
						$step5 = mysql_query ($query);
						mysql_query ("FLUSH PRIVILEGES");
						check_generic ($step5, "Established privileges for user 'integria' on database '$dbname', with password <i>'$random_password'</i>");
					} else
						$step5=1;
	
					$step6 = is_writable("include");
					check_generic ($step6, "Write permissions to save config file in './include'");
						
					$cfgin = fopen ("include/config.inc.php","r");
					$cfgout = fopen ($INTEGRIA_config,"w");
					$config_contents = fread ($cfgin, filesize("include/config.inc.php"));
	
					$config_new = '<?PHP
// Begin of automatic config file
$config["dbuser"]=';
if ($createuser==1){
	$config_new = $config_new . '"integria";
$config["dbpass"]="'.$random_password.'";	// DB Password
';
} else { 
	$config_new = $config_new . '"'.$dbuser.'";
$config["dbpass"]="'.$dbpassword.'";	// DB Password
';
}
$config_new = $config_new . '
$config["dbname"]="'.$dbname.'";    // MySQL DataBase name
$config["dbhost"]="'.$dbhost.'";    // DB Host
$config["homedir"]="'.$path.'";		// Config homedir
$config["base_url"]="'.$url.'";		// Base URL
// End of automatic config file
?>';
					$step7 = fputs ($cfgout, $config_new);
					$step7 = $step7 + fputs ($cfgout, $config_contents);
					if ($step7 > 0)
						$step7 = 1;
					fclose ($cfgin);
					fclose ($cfgout);
					check_generic ($step7, "Created new config file at '".$INTEGRIA_config."'");
				}
			}
			if (($step7 + $step6 + $step5 + $step4 + $step3 + $step2 + $step1) == 7) {
				$everything_ok = 1;
			}
		echo "</table></div>
		<div class='box'>
			<img src='images/integria_white.png' border='0' alt=''>
			<br><br>
		</div>
		
		<div class='box'>
			<img src='images/step3.png' border='0' alt=''>
		</div>
		
		<div id='install_box' style='margin-bottom: 25px;margin-left: 25px;'><p>";
			if ($everything_ok == 1) {
				echo "<a href='install.php?step=5'><img align='right' src='images/arrow_next.png' border=0 class=''></a>";
			} else {
				echo "<img src='images/info.png'> You get some problems. Installation is not completed. 
				<p>Please correct failures before trying again. All database schemes created in this step have been dropped.</p>";

				if (mysql_error() != "")
					echo "<p><img src='images/info.png'> <b>ERROR:</b> ". mysql_error()."</p>";
				if ($createdb == 1){
					mysql_query ("DROP DATABASE $dbname");
				}
			}		
		echo "
		</div>
	</div>
	$integria_footertext
</div>";
}

function install_step5() {
    global $integria_footertext;
	global $integria_version;

	echo "
	<div align='center' class='mt35'>
	<h1>Integria IMS $integria_version instalation wizard. Finished</h1>
	<div id='wizard' style='height: 300px;'>
		<div id='install_box'>
			<h1>Installation complete</h1>
			<p>You now must delete manually this installer for security, ('install.php') before trying to access to your Integria installation.
			<p>Don't forget to check <a href='http://integriaims.com'>http://integriaims.com</a> for updates.
			<p><a href='index.php'>Click here to access Integria</A></p>
		</div>
		<div class='box'>
			<img src='images/integria_white.png' border='0'></a>
			<br><br>			
		</div>
		<div class='box'>
			<img src='images/step4.png' border='0'><br>
		</div>
	</div>
	$integria_footertext
</div>";
	chmod ('include/config.php', 0600);
	// unlink ('install.php');
}


// ---------------
// Main page code
// ---------------

if (! isset ($_GET["step"])){
	install_step1 ();
} else {
	$step = (int) $_GET["step"];
	switch ($step) {
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

?>
