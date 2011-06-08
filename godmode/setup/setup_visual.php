<?php 

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

// Load global vars
global $config;

check_login ();
	
if (! dame_admin ($config["id_user"])) {
	audit_db ("ACL Violation", $config["REMOTE_ADDR"], "No administrator access", "Trying to access visual setup");
	require ("general/noaccess.php");
	exit;
}

$update = (bool) get_parameter ("update");

if ($update) {
	$config["block_size"] = (int) get_parameter ("block_size", 20);
	$config["fontsize"] = (int) get_parameter ("fontsize", 10);
	$config["font"] = get_parameter ("font", "smallfont.ttf");
	$config["pdffont"] = get_parameter ("pdffont", "code.ttf");
	$config["site_logo"] = get_parameter ("site_logo", "integria_logo.png");
    $config["header_logo"] = get_parameter ("header_logo", "integria_logo_header.png");
	$config["flash_charts"] = get_parameter ("flash_charts", 1);

    update_config_token ("block_size", $config["block_size"]);
    update_config_token ("fontsize", $config["fontsize"]);
    update_config_token ("font", $config["font"]);
    update_config_token ("pdffont", $config["pdffont"]);
    update_config_token ("site_logo", $config["site_logo"]);
    update_config_token ("header_logo", $config["header_logo"]);
    update_config_token ("flash_charts", $config["flash_charts"]);
}

echo "<h2>".__('Visual setup')."</h2>";

$table->width = '90%';
$table->class = 'databox';
$table->colspan = array ();
$table->data = array ();

$table->data[0][0] = print_input_text ("block_size", $config["block_size"], '',
	5, 5, true, __('Block size for pagination'));


function get_font_files () {
	global $config;
	$base_dir = $config['homedir'].'/include/fonts';
	$files = list_files ($base_dir, ".ttf", 1, 0);
	
	$retval = array ();
	foreach ($files as $file) {
		$retval[$config['homedir'].'/include/fonts/'.$file] = $file;
	}
	
	return $retval;
}

$fontlist = get_font_files ();

$table->data[0][1] = print_select ($fontlist, 'font', $config["font"], '', '', '',  true, 0, true, "Font for graphs") ;

$table->data[1][0] = print_select ($fontlist, 'pdffont', $config["pdffont"], '', '', '',  true, 0, true, "Font for PDF") ;

$table->data[1][1] = print_input_text ("fontsize", $config["fontsize"], '',
	3, 5, true, __('Graphics font size'));

function get_image_files () {
	$base_dir = 'images';
	$files = list_files ($base_dir, ".png", 1, 0);
	
	$retval = array ();
	foreach ($files as $file) {
		$retval[$file] = $file;
	}
	
	return $retval;
}

$imagelist = get_image_files ();
$table->data[2][0] = print_select ($imagelist, 'site_logo', $config["site_logo"], '', '', '',  true, 0, true, "Site logo") ;

$table->data[2][1] = print_select ($imagelist, 'header_logo', $config["header_logo"], '', '', '',  true, 0, true, "Header logo") ;

$table->data[3][0] = print_select (array(__('Disabled'),__('Enabled')), "flash_charts", $config["flash_charts"], '','','',true,0,true, __('Flash charts'));

echo "<form name='setup' method='post'>";

print_table ($table);

echo '<div style="width: '.$table->width.'" class="button">';
print_input_hidden ('update', 1);
print_submit_button (__('Update'), 'upd_button', false, 'class="sub upd"');
echo '</div>';
echo '</form>';
?>

<script type="text/javascript">
$(document).ready (function () {
	$("textarea").TextAreaResizer ();
});
</script>
