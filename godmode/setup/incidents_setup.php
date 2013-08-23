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
include_once('include/functions_setup.php');

enterprise_include("include/functions_setup.php");

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
print_setup_tabs('incidents', $is_enterprise);

$update = (bool) get_parameter ("update");
$add_day = (bool) get_parameter ("add_day");
$del_day = (bool) get_parameter ("del_day");

if ($add_day) {
	
	$new_day = get_parameter("new_day");
	
	//If new day added then add to list
	if ($new_day) { 
	
		$sql = sprintf("INSERT INTO tholidays (`day`) VALUES ('%s')", $new_day);
	
		process_sql($sql);
	}
}

if ($del_day) {
	$day = get_parameter("day");
	
	$sql = sprintf("DELETE FROM tholidays WHERE `id` = '".$day."'");
	
	process_sql ($sql);
}

if ($update) {
	$status = (array) get_parameter ('status');
	$resolutions = (array) get_parameter ('resolutions');
	$config["working_weekends"] = (int) get_parameter("working_weekends", 0);
	$config["mask_emails"] = (int) get_parameter("mask_emails", 0);
	
	update_config_token ("working_weekends", $config["working_weekends"]);	
	
	update_config_token ("mask_emails", $config["mask_emails"]);	
	
	foreach ($status as $id => $name) {
		$sql = sprintf ('UPDATE tincident_status SET name = "%s"
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

echo '<form method="post">';

$table->width = '99%';
$table->class = 'search-table';
$table->colspan = array ();
$table->data = array ();

$status = get_db_all_rows_in_table ('tincident_status');

foreach ($status as $stat) {
	$data = array ();
	
	$data[0] = print_input_text ('status['.$stat['id'].']', $stat['name'],
		'', 35, 255, true);
	
	array_push ($table->data, $data); 
}

$table_status = print_table ($table,true);

$table->data = array ();

$resolutions = get_db_all_rows_in_table ('tincident_resolution');

foreach ($resolutions as $resolution) {
	$data = array ();
	
	$data[0] = print_input_text ('resolutions['.$resolution['id'].']', $resolution['name'],
		'', 35, 255, true);
	
	array_push ($table->data, $data); 
}

$table_resolutions = print_table ($table, true);

$table->width = '99%';
$table->class = 'search-table listing';
$table->colspan = array ();
$table->data = array ();

$date_table = "<table>";
$date_table .= "<tr>";
$date_table .= "<td>";
$date_table .= "<input id='new_day' type='text' name='new_day' width='15' size='15'>";
$date_table .= "</td>";
$date_table .= "<td>";
$date_table .= "<input type='submit' class='sub create' name='add_day' value='".__("Add")."'>";
$date_table .= "</td>";
$date_table .= "</tr>";
$date_table .= "</table>";

$table->data[0][0] = "";
$table->data[0][1] = "<strong>".__("Holidays")."</strong>";

$table->data[1][0] =print_checkbox ("working_weekends", 1, $config["working_weekends"], 
					true, __("Weekends are working days"));
$table->data[1][1] = $date_table;

$holidays_array = calendar_get_holidays();

if ($holidays_array == false) {
	$holidays = "<center><em>".__("No holidays defined")."</em></center>";
} else {
	
	$holidays = "<table>";
	
	foreach ($holidays_array as $ha) {
		$holidays .= "<tr>";
		$holidays .= "<td>";
		$holidays .= $ha["day"];
		$holidays .= "</td>";
		$holidays .= "<td>";
		$holidays .= "<a href='index.php?sec=godmode&sec2=godmode/setup/incidents_setup&del_day=1&day=".$ha["id"]."'><img src='images/cross.png'></a>";
		$holidays .= "</td>";
		$holidays .= "</tr>";
	}
	
	$holidays .= "</table>";
}

$table->data[1][1] .= $holidays;

$holidays_table = print_table($table, true);


$table_anonym = enterprise_hook('setup_print_incident_anonymize');

if ($table_anonym === ENTERPRISE_NOT_HOOK) {
	$table_anonym = "";
}

echo "<table width='99%' class='search-table-button
'>";
echo "<tr>";
echo "<td><h3>".__('Status')."</h3></td>";
echo "<td><h3>".__('Resolutions')."</h3></td>";
echo "<td><h3>".__("Non-working days")."</h3></td>";
echo "</tr>";
echo "<tr>";
echo "<td style='vertical-align: top; width: 280px'>".$table_status."</td>";
echo "<td style='vertical-align: top; width: 280px'>".$table_resolutions."</td>";
echo "<td style='vertical-align: top;'>".$holidays_table;
echo $table_anonym;
echo "</td>";
echo "</tr>";
echo "<tr><td colspan=3>";
print_input_hidden ('update', 1);
print_submit_button (__('Update'), 'upd_button', false, 'class="sub upd"');
echo "</td></tr>";
echo "</table>";
echo '</form>';
?>

<script type="text/javascript" src="include/js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
<script type="text/javascript" src="include/js/integria_date.js"></script>

<script>
add_datepicker ("#new_day", null);
</script>
