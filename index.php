<?php
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2012 Ártica Soluciones Tecnológicas
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

// Set to 1 to do not check for installer or config file (for development!).
// Activate gives more error information, not useful for production sites
$develop_bypass = 0;

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

require_once ('include/config.php');
require_once ('include/functions.php');
require_once ('include/functions_db.php');
require_once ('include/functions_html.php');
require_once ('include/functions_form.php');
require_once ('include/functions_calendar.php');
require_once ('include/auth/mysql.php');
require_once ('include/functions_db.mysql.php');
require_once ('include/functions_api.php');

$is_enterprise = false;

/* Enterprise support */
if (file_exists ("enterprise/load_enterprise.php")) {
	require_once ("enterprise/load_enterprise.php");
	$is_enterprise = true;
}

if (file_exists ("enterprise/include/functions_login.php")) {
	require_once ("enterprise/include/functions_login.php");
}

// Update user password
$change_pass = get_parameter('renew_password', 0);

if ($change_pass == 1) {
	
	$nick = $_POST["login"];

	//Checks if password has expired
	$check_status = check_pass_status($nick);

	if ($check_status != 0) {
		
		$password_new = (string) get_parameter ('new_password', '');
		$password_confirm = (string) get_parameter ('confirm_new_password', '');
		$id = (string) get_parameter ('login', '');

		$changed_pass = login_update_password_check ($password_new, $password_confirm, $id);

		if ($changed_pass) {
			//$_POST['renew_password'] = 0;
			require ("general/login_page.php");
		} else {
			$expired_pass = true;
		}
	}
}

// Process external download id's
$external_download_id = get_parameter('external_download_id', "");
if ($external_download_id != ""){
	include ("operation/download/download_external.php");
	exit;
}


$html_header = '<!--[if !IE]> -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<![endif]-->
<!--[if IE]>																																									
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<meta http-equiv="X-UA-Compatible" content="IE=9" >
<meta http-equiv="X-UA-Compatible" content="IE=8" >
<html xmlns="http://www.w3.org/1999/xhtml">
<![endif]-->';

// This is a clean and/or PDF output ?
$clean_output = get_parameter ("clean_output", 0);
$pdf_output = get_parameter ("pdf_output", 0);
$pdf_filename = get_parameter ("pdf_filename", "");
$raw_output = get_parameter ("raw_output", 0);
$expired_pass = false;
if (($pdf_output == 1) OR ($raw_output == 1)) {
	// Buffer the following html with PHP so we can store it to a variable later
	ob_start();
    $config["flash_charts"] = 0;
}

echo $html_header;
echo "<title>" . $config["sitename"] . "</title>";

?>

<meta http-equiv="expires" content="never" />
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta name="resource-type" content="document" />
<meta name="distribution" content="global" />
<meta name="website" content="http://integriaims.com" />
<meta name="copyright" content="Artica Soluciones Tecnologicas (c) 2007-2012" />
<meta name="keywords" content="ticketing, management, project, incident, tracking, ITIL" />
<meta name="robots" content="index, follow" />
<link rel="icon" href="images/integria_mini_logo.png" type="image/png" />
<link rel="stylesheet" href="include/styles/integria.css" type="text/css" />
<link rel="stylesheet" href="include/styles/integria_tip.css" type="text/css" />
<link rel="stylesheet" href="include/styles/flora/flora.accordion.css" type="text/css" />
<link rel="stylesheet" href="include/styles/flora/flora.css" type="text/css" />
<link rel="stylesheet" href="include/styles/flora/flora.datepicker.css" type="text/css" />
<link rel="stylesheet" href="include/styles/flora/flora.dialog.css" type="text/css" />
<link rel="stylesheet" href="include/styles/flora/flora.resizable.css" type="text/css" />
<link rel="stylesheet" href="include/styles/flora/flora.slider.css" type="text/css" />
<link rel="stylesheet" href="include/styles/flora/flora.tabs.css" type="text/css" />
<link rel="stylesheet" href="include/styles/flora/flora.core.css" type="text/css" />
<link rel="stylesheet" href="include/styles/flora/flora.theme.css" type="text/css" />
<link rel="stylesheet" href="include/styles/flora/flora.multiselect.css" type="text/css" />
<script type="text/javascript" src="include/js/jquery.js"></script>
<script type="text/javascript" src="include/js/calendar.js"></script>
<script type="text/javascript" src="include/js/integria.js"></script>
<script type="text/javascript" src="include/js/jquery-migrate-1.2.1.js"></script><!-- MIGRATE OLDER JQUERY CODE (TEMPORAL)-->
<script type="text/javascript" src="include/js/jquery.ui.core.js"></script>
<script type="text/javascript" src="include/js/jquery-ui.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.position.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.widget.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.tabs.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.draggable.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.resizable.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.textarearesizer.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.dialog.js"></script>
<script type="text/javascript" src="include/js/jquery.form.js"></script>
<script type="text/javascript" src="include/js/jquery.axuploader.js"></script>
<script type="text/javascript" src="include/js/jquery.validate.js"></script>
<script type="text/javascript" src="include/js/jquery.validation.functions.js"></script>
<script type="text/javascript" src="include/js/d3.v3.js"></script>

<script type="text/javascript">
</script>
<!--[if lte IE 7]>
<script type="text/javascript" src="include/js/jquery.bgiframe.js"></script>
<link rel="stylesheet" href="include/styles/integria-ie-fixes.css" type="text/css" />
<![endif]-->

<?php

$login = get_parameter ('login');
$sec = get_parameter ('sec');
$sec2 = get_parameter ('sec2');
$recover = get_parameter('recover','');
$not_show_menu = 0;

if ($clean_output == 1)
    echo '<link rel="stylesheet" href="include/styles/integria_clean.css" type="text/css" />';

// Password recovery
if ($recover != ""){
    require ('general/password_recovery.php');
    exit;
}

// Check request from IP's allowed in the API ACL list. Special request to generate PDF on crontask
$ip_origin = $_SERVER['REMOTE_ADDR'];
if (ip_acl_check ($ip_origin)) {
	// Only to see PDF reports!
	if (($pdf_output == 1) AND ($pdf_filename != "")){
		$scheduled_report_user = get_parameter ("scheduled_report_user","");
		$_SESSION['id_usuario'] = $scheduled_report_user;
	}
}

// Login process
if (! isset ($_SESSION['id_usuario']) && $login) {

	$nick = get_parameter ("nick");
	$pass = get_parameter ("pass");

	$config["auth_error"] = "";

	$nick_in_db = process_user_login ($nick, $pass);
	$is_admin = get_admin_user($nick_in_db);

	if (($nick_in_db !== false) && ($is_admin != 1) && ($is_enterprise) && ($config['enable_pass_policy'])) {

		$blocked = login_check_blocked($nick);

		if ($blocked) {
			echo '<body class="login">';
			require ('general/login_page.php');
			exit;
		}
		//Checks if password has expired
		$check_status = check_pass_status($nick, $pass);

		switch ($check_status) {
			case 1: //first change
			case 2: //pass expired
				$expired_pass = true;
				login_change_password($nick);
				break;
			case 0:
				$expired_pass = false;
				break;
		}
	}
	
	if (($nick_in_db !== false) && $expired_pass) { //login ok and password has expired
		echo '<body class="login">';
		require_once ('general/login_page.php');
		exit;
	} else if (($nick_in_db !== false) && (!$expired_pass)) { //login ok and password has not expired
		unset ($_GET["sec2"]);
		$_GET["sec"] = "general/home";
		logon_db ($nick_in_db, $_SERVER['REMOTE_ADDR']);
		$_SESSION['id_usuario'] = $nick_in_db;
		$config['id_user'] = $nick_in_db;
		if ($sec2 == '') {
			$sec2 = 'general/home';
		}

	} else { //login wrong
		$blocked = false;
		
		if (!$expired_pass) {	
			
			if ($is_admin != 1) {
				if ($is_enterprise)
					$blocked = login_check_blocked($nick);
				else
					$blocked = false;
			}
			
			if (!$blocked) {
				if ($is_enterprise){
					login_check_failed($nick); //Checks failed attempts
				}
				
				$first = substr ($pass, 0, 1);
				$last = substr ($pass, strlen ($pass) - 1, 1);
				$pass = $first . "****" . $last;
				
				if ($expired_pass == false) {
					$login_failed = true;
				} else {
					unset($login_failed);
				}
				echo '<body class="login">';
				require_once ('general/login_page.php');
				exit ("</html>");
			} else {
				echo '<body class="login">';
				require_once ('general/login_page.php');
				exit ("</html>");
			}
		} else { 
			echo '<body class="login">';
			require_once ('general/login_page.php');
			exit ("</html>");
		}
	}
}
else if (! isset ($_SESSION['id_usuario'])) {

	// There is no user connected
	echo '</head>';
	echo '<body class="login">';
	require ('general/login_page.php');
	exit;
}
else {
	// Create id_user variable in $config hash, of ALL pages.
	$config["id_user"] = $_SESSION['id_usuario'];
}

load_menu_visibility();
?><script>var lang = {
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

<?php
// Log off
$logout = (bool) get_parameter ('logout');
if ($logout) {
	echo '</head>';
	echo '<body>';
	$_REQUEST = array ();
	$_GET = array ();
	$_POST = array ();
	echo '<body class="login">';
	require ('general/login_page.php');
	$iduser = $_SESSION["id_usuario"];
	logoff_db ($iduser, $config["REMOTE_ADDR"]);
	unset($_SESSION["id_usuario"]);
	exit;
}

// Common code for all operations
echo '</head>';
echo '<body>';

// http://es2.php.net/manual/en/ref.session.php#64525
// Session locking concurrency speedup!
$session_id = session_id();
session_write_close ();

// Special pages, which doesn't use sidemenu
if (($sec2 == "") OR ($sec2 == "general/home")) {
	$not_show_menu = 1;
}

// Clean output (for reporting or raw output
if ($clean_output == 0) {
?>
	<div id="wrap">
		<div id="header">
			<?php require ("general/header.php"); ?>
		</div>

	<!--
	<div id="menu">
	<?php require ("operation/main_menu.php"); ?>
	</div>
	-->
	

        <!-- This magic is needed to have it working in IE6.x and Firefox 4.0 -->
        <!-- DO NOT USE CSS HERE -->

        <table width=100% cellpadding=0 cellspacing=0 border=0 style='margin: 0px; padding: 0px'>
	<tr>

	<?php

        // Avoid render left menu for some special places (like home).
        if ($not_show_menu == 0){
			echo '<td valign=top style="width: 150px;">';
			echo '<div id="sidebar">';
			require ("operation/side_menu.php"); 
			if (give_acl ($config["id_user"], 0, "AR"))
				require ("operation/tool_menu.php");
			echo '</div></td>';
		}
	?>
	
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

	if ($custom_pdf) {
		$pdfObject->setMetadata(safe_output("Invoice", 'Integria IMS', 'Integria IMS', __("Integria IMS invoice")));
		
		$header = '<div align="'.$header_logo_alignment.'"><img src="'.$config["homedir"]."/images/".$header_logo.'"></div>';
		$header .= '<br><p align="center">'.$header_text.'</p>';
		
		$pdfObject->setHeaderHTML($header, true);
		$pdfObject->setFooterHTML($footer_text, true, true, true);
	} else {
		$pdfObject->setMetadata(safe_output("Integria IMS PDF Report", 'Integria IMS Report', 'Integria IMS', __("Automated Integria IMS report")));

		$pdfObject->setFooterHTML("Integria IMS Report", true);
		$pdfObject->setHeaderHTML("<p align=right style='border-bottom: 1px solid #666;'> Integria IMS Report - ".date("D F d, Y H:i:s", $time).'</p>', true);
	}
	
	// Clean all html entities before render to PDF
	$html = safe_output($html);
	
	$pdfObject->addHTML($html);
	
	if ($pdf_filename != "")
		$pdfObject->writePDFfile ( $config["homedir"]."/attachment/tmp/".$pdf_filename);
	else
		$pdfObject->showPDF();

    // Dirty thing, just for testing, do not use it
    // system ("rm /tmp/integria_graph_serialize_*");

}

if (($raw_output == 0) AND ($pdf_output == 0)){
    echo '</body></html>';
}
?>

