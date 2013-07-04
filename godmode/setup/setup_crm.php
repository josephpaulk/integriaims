<?php 

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2013 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// Load global vars
global $config;

check_login ();
	
if (! dame_admin ($config["id_user"])) {
	audit_db ("ACL Violation", $config["REMOTE_ADDR"], "No administrator access", "Trying to access setup");
	require ("general/noaccess.php");
	exit;
}

/* Tabs code */
echo '<div id="tabs">';

/* Tabs list */
echo '<ul class="ui-tabs-nav">';
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/setup"><span><img src="images/cog.png" title="'.__('Setup').'"></span></a></li>';
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/setup_visual"><span><img src="images/chart_bar.png" title="'.__('Visual setup').'"></span></a></li>';
if ($is_enterprise) {
	echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=enterprise/godmode/setup/setup_password"><span valign=bottom><img src="images/lock.png" title="'.__('Password policy').'"></span></a></li>';
}
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/incidents_setup"><span><img src="images/bug.png" title="'.__('Incident setup').'"></span></a></li>';
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/setup_mail"><span><img src="images/email.png"  title="'.__('Mail setup').'"></span></a></li>';
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/setup_mailtemplates"><span><img src="images/email_edit.png"  title="'.__('Mail templates setup').'"></span></a></li>';
if ($is_enterprise) {
	echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=enterprise/godmode/usuarios/menu_visibility_manager"><span valign=bottom><img src="images/eye.png" title="'.__('Visibility management').'"></span></a></li>';
}
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/setup_pandora"><span><img src="images/pandora.ico"  title="'.__('Pandora FMS inventory').'"></span></a></li>';
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/setup_auth"><span><img src="images/book_edit.png"  title="'.__('Authentication').'"></span></a></li>';
echo '<li class="ui-tabs-selected"><a href="index.php?sec=godmode&sec2=godmode/setup/setup_crm"><span><img src="images/page_white_text.png"  title="'.__('CRM setup').'"></span></a></li>';
echo '</ul>';

$update = (bool) get_parameter ("update");

if ($update) {
	$config["invoice_logo"] = (string) get_parameter ("invoice_logo");
	$config["invoice_logo_alignment"] = (string) get_parameter ("invoice_logo_alignment");
	$config["invoice_header"] = (string) get_parameter ("invoice_header");
	$config["invoice_footer"] = (string) get_parameter ("invoice_footer");
	$config["invoice_tax_name"] = (string) get_parameter ("invoice_tax_name");
	
	update_config_token ("invoice_logo", $config["invoice_logo"]);
	update_config_token ("invoice_logo_alignment", $config["invoice_logo_alignment"]);
	update_config_token ("invoice_header", $config["invoice_header"]);
	update_config_token ("invoice_footer", $config["invoice_footer"]);
	update_config_token ("invoice_tax_name", $config["invoice_tax_name"]);
}

echo "<h2>".__('CRM setup')."</h2>";

$table->width = '90%';
$table->class = 'databox';
$table->colspan = array ();
$table->data = array ();

// Gets all .png, .jpg and .gif files from "images" directory
// and returns an array with their names
function get_image_files () {
	$base_dir = 'images';
	$files = list_files ($base_dir, ".png", 1, 0);
	$files = array_merge($files, list_files ($base_dir, ".jpg", 1, 0));
	$files = array_merge($files, list_files ($base_dir, ".gif", 1, 0));
	
	$retval = array ();
	foreach ($files as $file) {
		$retval[$file] = $file;
	}
	
	return $retval;
}

$imagelist = get_image_files ();

$table->colspan[0][0] = 2;
$table->data[0][0] = "<h3>".__('Invoice generation parameters')."</h3>";

$table->data[1][0] = print_select ($imagelist, 'invoice_logo', $config["invoice_logo"], '', __('None'), 'none',  true, 0, true, __('Invoice header logo'));
$table->data[1][0] .= print_help_tip (__('You can submit your own logo in "images" folder using the file uploader'), true);

$alignment = array ();
$alignment["center"] = "center";
$alignment["left"] = "left";
$alignment["right"] = "right";
$table->data[1][1] = print_select ($alignment, 'invoice_logo_alignment', $config["invoice_logo_alignment"], '', '', '',  true, 0, true, __('Invoice header logo alignment'));

$table->colspan[2][0] = 2;
$table->data[2][0] = print_textarea ('invoice_header', 5, 40, $config["invoice_header"], '', true, __('Invoice header'));

$table->colspan[3][0] = 2;
$table->data[3][0] = print_textarea ('invoice_footer', 5, 40, $config["invoice_footer"], '', true, __('Invoice footer'));

$table->data[4][0] = print_input_text ('invoice_tax_name', $config["invoice_tax_name"], '', 10, 10, true, __('Invoice tax name'));
$table->data[4][0] .= print_help_tip (__('For example: VAT'), true);

echo '<form name="setup" method="post">';

print_table ($table);

echo '<div style="width: '.$table->width.'" class="button">';
print_input_hidden ('update', 1);
print_submit_button (__('Update'), 'upd_button', false, 'class="sub upd"');
echo '</div>';
echo '</form>';

?>
