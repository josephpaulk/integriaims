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
	audit_db ("ACL Violation", $config["REMOTE_ADDR"], "No administrator access", "Trying to access setup");
	require ("general/noaccess.php");
	exit;
}


// Update configuration
if ( (isset ($_POST["data"]) AND ($filename != ""))) {
	$data =  unsafe_string (str_replace ("\r\n", "\n", $_POST["data"]));

	$file = fopen ($full_filename, "wb");
	fwrite ($file, $data);
	fclose ($file);
	echo "<h3 class='suc'>".lang_string ('Filesuccessfully updated')."</h3>";
}


function get_template_files () {
	$base_dir = 'include/mailtemplates';
	$files = list_files ($base_dir, ".tpl", 1, 0);
	
	$retval = array ();
	foreach ($files as $file) {
		$retval[$file] = $file;
	}
	
	return $retval;
}

$update = get_parameter ("upd_button","none");
$refresh = get_parameter ("edit_button", "none");
$template = get_parameter ("template", "");
$data = "";


// Load template from disk to textarea
if ($refresh != "none"){
	$full_filename = "include/mailtemplates/".get_parameter("template");
	$data = safe_input (file_get_contents ($full_filename));
}

// Update configuration
if ($update != "none") {
	$data =  unsafe_string (str_replace ("\r\n", "\n", $_POST["template_content"]));
	$file = "include/mailtemplates/".$template;
	$file = fopen ($file, "wb");
	fwrite ($file, $data);
	fclose ($file);
	echo "<h3 class='suc'>".lang_string ('Filesuccessfully updated')."</h3>";
}

echo "<h2>".__('Mail templates setup')."</h2>";

$table->width = '100%';
$table->class = 'databox';
$table->colspan = array ();
$table->colspan[2][0] = 2;
$table->data = array ();

$templatelist = get_template_files ();

$table->data[1][0] = print_select ($templatelist, 'template', $template, '', '', '',  true, 0, true, "Template") ;

$table->data[1][0] .= "&nbsp;&nbsp";
$table->data[1][0] .=  print_submit_button (__('Edit'), 'edit_button', false, 'class="sub upd"', true); 
$table->data[1][0] .= integria_help ("macros", true);

$table->data[2][0] = print_textarea ("template_content", 30, 44, $data,'', true, __('Template contents'));

echo "<form name='setup' method='post'>";

print_table ($table);

echo '<div style="width: '.$table->width.'" class="button">';
print_submit_button (__('Update'), 'upd_button', false, 'class="sub upd"', false);

echo '</div>';
echo '</form>';
?>

<script type="text/javascript">
$(document).ready (function () {
	$("textarea").TextAreaResizer ();
});
</script>