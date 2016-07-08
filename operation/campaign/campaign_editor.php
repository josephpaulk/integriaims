<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2012 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;


check_login ();

if (! give_acl ($config["id_user"], 0, "VM")) {
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access campaign management");
	require ("general/noaccess.php");
	exit;
}

$new = get_parameter("new");

$title = "";

$now = time();
$end_time = $now + (24*3600*7); //A week later

$start_date = date("Y-m-d", $now);
$end_date = date("Y-m-d", $end_time);
$description = "";

$campaing = array();


$update = get_parameter("update");

//Update campaign
if ($update) {
	$title = get_parameter("title");
	$start_date = get_parameter("start_date");
	$end_date = get_parameter("end_date");
	$description = get_parameter("description");
	$expenses = get_parameter("expenses");


	$values = array("title" => $title,
				"start_date" => $start_date,
				"end_date" => $end_date,
				"description" => $description,
				"expenses" => $expenses);
	
	$res = process_sql_update ("tcampaign", $values, array("id" => $id));
	
	if ($res) {
		echo ui_print_success_message (__("Campaign updated sucessfully"), '', true, 'h3', true);
	} else {
		echo ui_print_error_message (__("There was a problem updating campaign"), '', true, 'h3', true);
	}
}

//Get campaign information
if ($id) {

	$campaign = get_db_row ("tcampaign", "id", $id);

	$title = $campaign["title"];
	$start_date = $campaign["start_date"];
	$end_date = $campaign["end_date"];
	$description = $campaign["description"];
	$expenses = $campaign["expenses"];

	//Check if campaign exists
	if (!$campaign) {
		echo "<h1>".__("Campaign edition")."</h1>";
		echo ui_print_error_message (__("The campaign doesn't exists"), '', true, 'h3', true);
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access campaign management with wrong id: ".$id);
		return;
	}

}

if ($new) {
	$action = "index.php?sec=customers&sec2=operation/campaign/campaign";
} else {
	//echo '<div class="under_tabs_info">'.__("Campaign").': '.$title.'</div>';
	$action = "index.php?sec=customers&sec2=operation/campaign/campaign&tab=editor&id=".$id;
}
$table = new stdClass();
$table->width = '100%';
$table->class = 'search-table-button';
$table->colspan = array ();
$table->data = array ();

$table->class = "search-table-button";
$table->data = array ();
$table->style = array ();
$table->colspan = array ();
$table->colspan[1][0] = 4;
$table->colspan[2][0] = 4;

$table->data[0][0] = print_input_text ('title', $title, '', 55, 100, true, __('Title'));

$table->data[0][1] = print_input_text ('start_date', $start_date, '', 10, 10, true, __('Start date'));

$table->data[0][2] = print_input_text ('end_date', $end_date, '', 10, 10, true, __('End date'));
if(!isset($expenses)){
	$expenses = '';
}
$table->data[0][3] = print_input_text ('expenses', $expenses, '', 10, 10, true, __('Expenses'));

$table->data[1][0] = print_textarea ('description', 9, 80, $description, false, true, __('Description'));

echo "<form action='".$action."' method='post'>";

print_table ($table);

	echo '<div style="width:100%;">';
		unset($table->data);
		unset($table->head);
		$table->width = '100%';
		$table->class = "button-form";
		if ($new) {
			$table->data[2][0] = print_submit_button (__('Create'), 'create_btn', false, 'class="sub next"', true);
			$table->data[2][0] .= print_input_hidden ('create', 1, true);
		} else {
			$table->data[2][0] = print_submit_button (__('Update'), 'update_btn', false, 'class="sub upd"', true);
			$table->data[2][0] .= print_input_hidden ('update', 1, true);
		}
		print_table ($table);
	echo "</div>";
echo "</form>";

?>

<script>

// Datepicker
add_ranged_datepicker ("#text-start_date", "#text-end_date", null);

</script>