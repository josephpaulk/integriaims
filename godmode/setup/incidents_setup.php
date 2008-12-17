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

$update = (bool) get_parameter ("update");

echo "<h2>".__('Incidents setup')."</h2>";

if ($update) {
	$status = (array) get_parameter ('status');
	$origins = (array) get_parameter ('origins');
	$resolutions = (array) get_parameter ('resolutions');
	
	foreach ($status as $id => $name) {
		$sql = sprintf ('UPDATE tincident_status SET name = "%s"
			WHERE id = %d',
			$name, $id);
		process_sql ($sql);
	}
	
	foreach ($origins as $id => $name) {
		$sql = sprintf ('UPDATE tincident_origin SET name = "%s"
			WHERE id = %d',
			$name, $id);
		process_sql ($sql);
	}
	
	foreach ($resolutions as $id => $name) {
		$sql = sprintf ('UPDATE tincident_resolution SET name = "%s"
			WHERE id = %d',
			$name, $id);
		process_sql ($sql);
	}
	echo '<h3 class="suc">'.__('Updated successfuly').'</h3>';
}


echo '<h3>'.__('Status').'</h3>';

echo '<form method="post">';

$table->width = '30%';
$table->class = 'databox';
$table->colspan = array ();
$table->data = array ();

$status = get_db_all_rows_in_table ('tincident_status');

foreach ($status as $stat) {
	$data = array ();
	
	$data[0] = print_input_text ('status['.$stat['id'].']', $stat['name'],
		'', 35, 255, true);
	
	array_push ($table->data, $data); 
}

print_table ($table);

echo '<h3>'.__('Origins').'</h3>';

$table->data = array ();

$origins = get_db_all_rows_in_table ('tincident_origin');

foreach ($origins as $origin) {
	$data = array ();
	
	$data[0] = print_input_text ('origins['.$origin['id'].']', $origin['name'],
		'', 35, 255, true);
	
	array_push ($table->data, $data); 
}

print_table ($table);

echo '<h3>'.__('Resolutions').'</h3>';

$table->data = array ();

$resolutions = get_db_all_rows_in_table ('tincident_resolution');

foreach ($resolutions as $resolution) {
	$data = array ();
	
	$data[0] = print_input_text ('resolutions['.$resolution['id'].']', $resolution['name'],
		'', 35, 255, true);
	
	array_push ($table->data, $data); 
}

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
