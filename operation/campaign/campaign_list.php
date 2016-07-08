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

$delete = get_parameter("del");
$create = get_parameter("create");
if ($delete) {
	$res = process_sql_delete ("tcampaign", array("id" => $delete));

	if ($res) {
		echo ui_print_success_message (__("Campaign deleted sucessfully"), '', true, 'h3', true);
	} else {
		echo ui_print_error_message (__("There was a problem deleting campaign"), '', true, 'h3', true);
	}
}

if ($create) {
	$title = get_parameter("title");
	$description = get_parameter("description");
	$start_date = get_parameter("start_date");
	$end_date = get_parameter("end_date");
	$expenses = get_parameter("expenses");

	//$campaign = 
	$values = array("title" => $title,
					"start_date" => $start_date,
					"end_date" => $end_date,
					"description" => $description,
					"expenses" => $expenses);

	$id = process_sql_insert ("tcampaign", $values);	

	if ($id) {
		echo ui_print_success_message (__("Campaign created sucessfully"), '', true, 'h3', true);
	} else {
		echo ui_print_error_message (__("There was a problem creating campaign"), '', true, 'h3', true);
	}	
}

$campaigns = crm_get_campaigns();
echo '<div class="divresult">';
if (!$campaigns) {
	echo ui_print_error_message (__("There aren't campaigns"), '', true, 'h3', true);
} else {
	$table = new stdClass();
	$table->width = '98%';
	$table->class = 'databox';
	$table->colspan = array ();
	$table->data = array ();

	$table->class = "listing";
	$table->data = array ();
	$table->style = array ();
	$table->colspan = array ();
	$table->head[0] = __('ID');
	$table->head[1] = __('Name');
	$table->head[2] = __('Start date');
	$table->head[3] = __('End date');
	$table->head[4] = __('Delete');

	$data = array();
	foreach($campaigns as $camp) {
		$aux_data = array();

		$link = "index.php?sec=customers&sec2=operation/campaign/campaign&tab=editor&id=".$camp["id"];

		$aux_data[0] = "<a href='".$link."'>".$camp["id"]."</a>";
		$aux_data[1] = "<a href='".$link."'>".$camp["title"]."</a>";

		$date_array = explode(" ", $camp["start_date"]);
		$clean_date = $date_array[0];

		$aux_data[2] = $clean_date;

		$date_array = explode(" ", $camp["end_date"]);
		$clean_date = $date_array[0];

		$aux_data[3] = $clean_date;
		$aux_data[4] = "<a href='index.php?sec=customers&sec2=operation/campaign/campaign&del=".$camp["id"]."'><img src='images/cross.png'></a>";

		array_push($data, $aux_data);
	}

	$table->data = $data;

	print_table ($table);

}
echo "</div>";

echo '<div class="divform">';
	echo "<form action='index.php?sec=customers&sec2=operation/campaign/campaign&tab=editor' method='post'>";
		echo "<table class='search-table'><tr><td>";
			print_submit_button (__('Create'), 'create_btn', false, 'class="sub next"');
			print_input_hidden ('new', 1);
		echo "</td></tr></table>";
	echo "</form>";
echo "</div>";
?>
