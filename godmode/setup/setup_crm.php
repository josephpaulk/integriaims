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
include_once('include/functions_setup.php');

check_login ();
	
if (! dame_admin ($config["id_user"])) {
	audit_db ("ACL Violation", $config["REMOTE_ADDR"], "No administrator access", "Trying to access setup");
	require ("general/noaccess.php");
	exit;
}

$is_enterprise = false;
if (file_exists ("enterprise/load_enterprise.php")) {
	$is_enterprise = true;
}
	
/* Tabs list */
print_setup_tabs('crm', $is_enterprise);

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

$table->width = '99%';
$table->class = 'search-table-button';
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
$table->data[0][0] = "<h3>".__('Invoice generation parameters')."</h3><br>";

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

$button = print_input_hidden ('update', 1, true);
$button .= print_submit_button (__('Update'), 'upd_button', false, 'class="sub upd"', true);

$table->data['button'][0] = $button;
$table->colspan['button'][0] = 2;

echo '<form name="setup" method="post">';
print_table ($table);
echo '</form>';

?>

<script type="text/javascript">
$(document).ready (function () {
	$("#textarea-invoice_header").TextAreaResizer ();
	$("#textarea-invoice_footer").TextAreaResizer ();
});
</script>
