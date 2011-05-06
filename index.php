<?php
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2010 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.


// Integria uses icons from famfamfam, licensed under CC Atr. 2.5
// Silk icon set 1.3 (cc) Mark James, http://www.famfamfam.com/lab/icons/silk/
// Integria uses Pear Image::Graph code
// Integria shares much of it's code with project Babel Enterprise and Pandora FMS,
// also a Free Software Project coded by some of the people who makes Integria.

// Set this to 0 on production enviroments
$develop_bypass = 1;

if ($develop_bypass != 1) {

	// If no config file, automatically try to install
	if (! file_exists("include/config.php")) {
		// Check for installer presence
		if (! file_exists("install.php")) {
			include "general/error_noconfig.php";
			exit;
		}
		include ("install.php");
		exit;
	}

	// Check for installer presence
	if (file_exists ("install.php")) {
		include "general/error_install.php";
		exit;
	}

	if (! is_readable ("include/config.php")) {
		include "general/error_perms.php";
		exit;
	}
	// Check perms for config.php
	$perms = fileperms ('include/config.php');

	if (! ($perms & 0600) && ! ($perms & 0660) && ! ($perms & 0640)) {
		include "general/error_perms.php";
		exit;
	}
}


require_once ('include/config.php');
require_once ('include/functions.php');
require_once ('include/functions_db.php');
require_once ('include/functions_html.php');
require_once ('include/functions_form.php');
require_once ('include/functions_calendar.php');

/* Enterprise support */
if (file_exists ("enterprise/load_enterprise.php")) {
        require_once ("enterprise/load_enterprise.php");
}

$html_header = '<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html><head>';

// This is a clean and/or PDF output ?
$clean_output = get_parameter ("clean_output", 0);
$pdf_output = get_parameter ("pdf_output", 0);
$raw_output = get_parameter ("raw_output", 0);

if (($pdf_output == 1) OR ($raw_output == 1)) {
	// Buffer the following html with PHP so we can store it to a variable later
	ob_start();
    $config["flash_charts"] = 0;
}

echo $html_header;
echo "<title>" . $config["sitename"] . "</title>";

?>

<meta http-equiv="expires" content="0" />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta name="resource-type" content="document" />
<meta name="distribution" content="global" />
<meta name="author" content="Sancho Lerena <slerena@gmail.com>" />
<meta name="website" content="http://integriaims.com" />
<meta name="copyright" content="Artica Soluciones Tecnologicas (c) 2007-2011" />
<meta name="keywords" content="ticketing, management, project, incident, tracking, ITIL" />
<meta name="robots" content="index, follow" />
<link rel="icon" href="images/integria.ico" type="image/ico" />
<link rel="stylesheet" href="include/styles/integria.css" type="text/css" />
<link rel="stylesheet" href="include/styles/integria_tip.css" type="text/css" />
<link rel="stylesheet" href="include/styles/flora/flora.all.css" type="text/css" media="screen" title="Flora (Default)">
<script type="text/javascript" src="include/js/calendar.js"></script>
<script type="text/javascript" src="include/js/integria.js"></script>
<script type="text/javascript" src="include/js/jquery.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.core.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.tabs.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.draggable.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.resizable.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.dialog.js"></script>
<script type="text/javascript" src="include/js/jquery.form.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.textarearesizer.js"></script>
<script type="text/javascript">

var lang = {
	"Are you sure?" : "<?php echo __('Are you sure?')?>",
	"Added" : "<?php echo __('Added')?>",
	"Search inventory object" : "<?php echo __('Search inventory object')?>",
	"Already added" : "<?php echo __('Already added')?>",
	"Added" : "<?php echo __('Added')?>",
	"Search parent incident" : "<?php echo __('Search parent incident')?>",
	"User search" : "<?php echo __('User search')?>",
	"There's no affected inventory object" : "<?php echo __('There\'s no affected inventory object')?>",
	"There's no affected object" : "<?php echo __('There\'s no affected object')?>",
	"Create incident" : "<?php echo __('Create incident')?>",
	"Add workunit" : "<?php echo __('Add workunit')?>",
	"Submitting" : "<?php echo __('Submitting')?>",
	"Upload file" : "<?php echo __('Upload file')?>",
	"Search contact" : "<?php echo __('Search contact')?>",
	"Create contact" : "<?php echo __('Create contact')?>",
	"Search parent inventory" : "<?php echo __('Search parent inventory')?>"
};
</script>
<!--[if lte IE 7]>
<script type="text/javascript" src="include/js/jquery.bgiframe.js"></script>
<link rel="stylesheet" href="include/styles/integria-ie-fixes.css" type="text/css" />
<![endif]-->

<?php

$login = (bool) get_parameter ('login');
$sec = get_parameter ('sec');
$sec2 = get_parameter ('sec2');

// Password recovery
$recover = get_parameter('recover','');
if ($recover != ""){
    require ('general/password_recovery.php');
    exit;
}

// Login process
if (! isset ($_SESSION['id_usuario']) && $login) {
	$nick = get_parameter ("nick");
	$pass = get_parameter ("pass");
	
	$user = get_db_row ('tusuario', 'id_usuario', $nick);
	
	// For every registry
	if ($user !== false && $user['password'] == md5 ($pass)) {
		// Login OK
		// Nick could be uppercase or lowercase (select in MySQL
		// is not case sensitive)
		// We get DB nick to put in PHP Session variable,
		// to avoid problems with case-sensitive usernames.
		$nick = $user["id_usuario"];
		
		update_user_contact ($nick);
		logon_db ($nick, $config["REMOTE_ADDR"]);
		$_SESSION['id_usuario'] = $nick;
		$config["id_user"]= $nick;
		
		if ($sec2 == '') {
			$sec2 = 'general/home';
		}
	} else {
		// User not known
		unset ($_GET["sec2"]);
		echo '</head>';
		echo '<body bgcolor="#ffffff">';
		$first = substr ($pass, 0, 1);
		$last = substr ($pass, strlen ($pass) - 1, 1);
		$pass = $first . "****" . $last;
		audit_db ($nick, $config["REMOTE_ADDR"], "Logon Failed",
			"Invalid username: ".$nick." / ".$pass);
		
		$login_failed = true;
		require ('general/login_page.php');
		exit;
	}
} elseif (! isset ($_SESSION['id_usuario'])) {
	// There is no user connected
	echo '</head>';
	echo '<body bgcolor="#ffffff">';
	require ('general/login_page.php');
	exit;
} else {
	// Create id_user variable in $config hash, of ALL pages.
	$config["id_user"] = $_SESSION['id_usuario'];
}

// Log off
$logout = (bool) get_parameter ('logout');
if ($logout) {
	echo '</head>';
	echo '<body bgcolor="#ffffff">';
	$_REQUEST = array ();
	$_GET = array ();
	$_POST = array ();
	require ('general/login_page.php');
	$iduser = $_SESSION["id_usuario"];
	logoff_db ($iduser, $config["REMOTE_ADDR"]);
	session_unregister ("id_usuario");
	exit;
}

// Common code for all operations
echo '</head>';
echo '<body>';

// http://es2.php.net/manual/en/ref.session.php#64525
// Session locking concurrency speedup!
$session_id = session_id();
session_write_close ();
?>

<?php
if ($clean_output == 0) {
?>
	<div id="wrap">
		<div id="header">
			<?php require ("general/header.php"); ?>
		</div>

		<div id="menu">
			<?php require ("operation/main_menu.php"); ?>
		</div>

        <!-- This magic is needed to have it working in IE6.x and Firefox 4.0 -->
        <!-- DO NOT USE CSS HERE -->

        <table width=100% cellpadding=0 cellspacing=0 border=0>
        <tr><td width=180 valign=top>
			<div id="sidebar">
			<?php 
				require ("operation/side_menu.php"); 
				if (give_acl ($config["id_user"], 0, "AR"))
					require ("operation/tool_menu.php");
			?>
			</div>
        </td>
        <td valign=top>
			<div id="main">
			<?php
				
				// Check for problems
				if (!is_writable("attachment")){
					echo "<h3 class='error'>".__('Attachment directory is not writtable by HTTP Server')."</h3>";
					echo '<p>';
					echo __('Please check that {HOMEDIR}/attachment directory has write rights for HTTP server');
					echo "</p>";
				}
			
				if (!is_writable("attachment/tmp")){
					echo "<h3 class='error'>".__('Temporal directory is not writtable by HTTP Server')."</h3>";
					echo '<p>';
					echo __('Please check that {HOMEDIR}/attachment/tmp directory has write rights for HTTP server');
					echo "</p>";
				}

				// Page loader / selector
				if ($sec2 != "") {
					if (file_exists ($sec2.".php")) {
						require ($sec2.".php");
					} else {
						echo "<h3 class='error'>".__('Page not found')."</h3>";
					}
				} else {
					require ("general/home.php");  //default
				}
			?>
			</div>

		</td></tr></table>
	<!-- wrap ends here -->
	</div>

	<!-- footer starts here -->
	<div id="footer">
		<?php require("general/footer.php") ?>
	</div>
	<!-- footer ends here -->

<?php // end of clean output
} else {
	// clean output
	if ($sec2 != "") {
		if (file_exists ($sec2.".php")) {
			require ($sec2.".php");
		} else {
			echo "<br><b class='error'>".__('Page not found')."</b>";
		}
	} else
		require ("general/home.php");  //default
}

if ($pdf_output == 1){

    // Get current date time
    if (isset($_SERVER['REQUEST_TIME'])) {
		$time = $_SERVER['REQUEST_TIME'];
	} else {
		$time = time();
	}

	// Now collect the output buffer into a variable

	$html = ob_get_contents();
    $html .= "</body></html>";

    // Parse HTML and fix a few entries which makes problems with MPDF like <label> tag
    $html = str_replace ( "</label>" , "</label></b><br>" , $html);
    $html = str_replace ( "<label" , "<b><label" , $html);

	ob_end_clean();

	include("include/pdf_translator.php");

	$pdfObject = new PDFTranslator();

  // Set font from font defined in report
	$pdfObject->custom_font = $config["pdffont"];
	$pdfObject->setMetadata(safe_output("Integria IMS PDF Report", 'Integria IMS Report', 'Integria IMS', __("Automated Integria IMS report")));

	$pdfObject->setFooterHTML("Integria IMS Report", true);
	$pdfObject->setHeaderHTML("<p align=right style='border-bottom: 1px solid #666;'> Integria IMS Report - ".date("D F d, Y H:i:s", $time).'</p>', true);

	$pdfObject->addHTML($html);
	$pdfObject->showPDF();

    // Dirty thing, just for testing, do not use it
    // system ("rm /tmp/integria_graph_serialize_*");

}

if (($raw_output == 0) AND ($pdf_output == 0)){
    echo '
    <!-- Dialog helper div -->
    <div id="dialog" class="dialog"></div>
    </body>
    </html>';
}
?>
