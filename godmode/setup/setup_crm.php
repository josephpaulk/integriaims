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
	$config["lead_company_filter"] = get_parameter ("lead_company_filter", "");
	$config["lead_warning_time"] = get_parameter ("lead_warning_time", "7");
	
	update_config_token ("invoice_logo", $config["invoice_logo"]);
	update_config_token ("invoice_logo_alignment", $config["invoice_logo_alignment"]);
	update_config_token ("invoice_header", $config["invoice_header"]);
	update_config_token ("invoice_footer", $config["invoice_footer"]);
	update_config_token ("invoice_tax_name", $config["invoice_tax_name"]);
	update_config_token ("lead_company_filter", $config["lead_company_filter"]);
	update_config_token ("lead_warning_time", $config["lead_warning_time"]);


	//Update lead progress names
	$progress["0"] = get_parameter("progress_0");
	$progress["20"] = get_parameter("progress_20");
	$progress["40"] = get_parameter("progress_40");
	$progress["60"] = get_parameter("progress_60");
	$progress["80"] = get_parameter("progress_80");
	$progress["100"] = get_parameter("progress_100");
	$progress["101"] = get_parameter("progress_101");
	$progress["102"] = get_parameter("progress_102");
	$progress["200"] = get_parameter("progress_200");

	foreach ($progress as $key => $value) {
		process_sql_update ('tlead_progress', array ('name' => $value), array ('id' => $key));
	}
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

$table->data[2][0] = print_input_text ('invoice_tax_name', $config["invoice_tax_name"], '', 10, 10, true, __('Invoice tax name'));
$table->data[2][0] .= print_help_tip (__('For example: VAT'), true);

$table->colspan[3][0] = 2;
$table->data[3][0] = print_textarea ('invoice_header', 5, 40, $config["invoice_header"], '', true, __('Invoice header'));

$table->colspan[4][0] = 2;
$table->data[4][0] = print_textarea ('invoice_footer', 5, 40, $config["invoice_footer"], '', true, __('Invoice footer'));

$table->colspan[5][0] = 2;
$table->data[5][0] = "<h3>".__('Lead parameters')."</h3><br>";

$table->data[6][0] = print_input_text ("lead_company_filter", $config["lead_company_filter"], '',
	20, 255, true, __('Lead company filter IDs'));

$table->data[6][1] = print_input_text ("lead_warning_time", $config["lead_warning_time"], '',
	5, 255, true, __('Days to warn on inactive leads'));
	
$table->colspan[7][0] = 2;
$table->data[7][0] = "<h3>".__('Lead progress defintion')."</h3><br>";

$progress_values = lead_progress_array ();

$table->colspan[8][0] = 2;
$table->colspan[9][0] = 2;
$table->colspan[10][0] = 2;
$table->colspan[11][0] = 2;
$table->colspan[12][0] = 2;
$table->colspan[13][0] = 2;
$table->colspan[14][0] = 2;
$table->colspan[15][0] = 2;
$table->colspan[16][0] = 2;

$closed_lead_tip = print_help_tip (__('This status means that lead is closed'), true);

$table->data[8][0] = print_input_text ('progress_0', $progress_values["0"], '', 50, 100, true);
$table->data[9][0] = print_input_text ('progress_20', $progress_values["20"], '', 50, 100, true);
$table->data[10][0] = print_input_text ('progress_40', $progress_values["40"], '', 50, 100, true);
$table->data[11][0] = print_input_text ('progress_60', $progress_values["60"], '', 50, 100, true);
$table->data[12][0] = print_input_text ('progress_80', $progress_values["80"], '', 50, 100, true);
$table->data[13][0] = print_input_text ('progress_100', $progress_values["100"], '', 50, 100, true).$closed_lead_tip;
$table->data[14][0] = print_input_text ('progress_101', $progress_values["101"], '', 50, 100, true).$closed_lead_tip;
$table->data[15][0] = print_input_text ('progress_102', $progress_values["102"], '', 50, 100, true).$closed_lead_tip;
$table->data[16][0] = print_input_text ('progress_200', $progress_values["200"], '', 50, 100, true).$closed_lead_tip;


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
