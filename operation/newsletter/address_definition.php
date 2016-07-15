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

if (! give_acl ($config["id_user"], 0, "CN")) {
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access company section");
	require ("general/noaccess.php");
	exit;
}

$manager = give_acl ($config["id_user"], 0, "CN");

$id = (int) get_parameter ('id');
$create = (bool) get_parameter ('create');
$disable = (bool) get_parameter ('disable');
$enable = (bool) get_parameter ('enable');
$delete = (bool) get_parameter ('delete');
$multiple_delete = (bool) get_parameter ('multiple_delete');

// CREATE
if ($create) {
	
	$data = get_parameter ("data");
	$id_newsletter = get_parameter ("id_newsletter"); 
	
	
    	$datetime = date ("Y-m-d H:i:s"); 		
    	$id_group = get_db_sql ("SELECT id_group FROM tnewsletter WHERE id = $id_newsletter");
    
	// Parse chunk data from the textarea
	
	$data = safe_output($data);
	$data_array = preg_split ("/\n/", $data);
	$total = 0;
	$invalid = 0;
	foreach ($data_array as $data_item){
		$data2 = preg_split ("/,/", $data_item);
		$data2[0] = trim($data2[0]);

		// We have parsed data, ok, lets go
		
		if (check_email_address($data2[0])) {

			// It's duped ?
		
			$duped = get_db_sql ("SELECT COUNT(id) FROM tnewsletter_address WHERE id_newsletter = $id_newsletter AND email = '".$data2[0]."'");

			// OK, good data !
			
			if ($duped == 0){
				$total++;
				$sql = sprintf ('INSERT INTO tnewsletter_address (id_newsletter, email, status, name, datetime) VALUES (%d, "%s", "%s", "%s", "%s")', $id_newsletter, $data2[0], 0, $data2[1], $datetime);
				$id = process_sql ($sql, 'insert_id');
			} else {
				$invalid++;
			}	
		} else {
			$invalid++;
		}
		
	}

	echo ui_print_success_message (__('Successfully added')." $total/$invalid ". __("addresses (valid/invalid)"), '', true, 'h3', true);
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "NEWSLETTER ADDRESESS CREATED", "Created newsletter $total");
	
	
	$id = 0;
}

// DISABLE
if ($disable) {

	$id = get_parameter("id", 1);

	$sql = "UPDATE tnewsletter_address SET status = 1 WHERE id = $id";
	$result = mysql_query ($sql);
	if ($result === false)
		echo ui_print_error_message (__('Could not be updated'), '', true, 'h3', true);
	else {
		echo ui_print_success_message (__('Successfully disabled'), '', true, 'h3', true);
	}
	$id = 0;
}
// ENABLE
if ($enable) {

	$id = get_parameter("id", 1);

	$sql = "UPDATE tnewsletter_address SET status = 0 WHERE id = $id";
	$result = mysql_query ($sql);
	if ($result === false)
		echo ui_print_error_message (__('Could not be updated'), '', true, 'h3', true);
	else {
		echo ui_print_success_message (__('Successfully disabled'), '', true, 'h3', true);
	}
	$id = 0;
}


// DELETE
if ($delete) {

	$id = get_parameter("id", 1);
	$email = get_db_sql ("SELECT email FROM tnewsletter_address WHERE id = $id");
	
	$sql = "DELETE FROM tnewsletter_address WHERE id = $id";
	$result = mysql_query ($sql);
	if ($result === false)
		echo ui_print_error_message (__("Could not be deleted"), '', true, 'h3', true);
	else {
		echo ui_print_success_message (__("Successfully deleted"), '', true, 'h3', true);
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "NEWSLETTER ADDRESESS DELETED", "Deleted $email");
	}
	$id = 0;
}

if ($multiple_delete) {
	$ids = (array)get_parameter('delete_multiple', array());
	
	foreach ($ids as $id) {	
		$email = get_db_sql ("SELECT email FROM tnewsletter_address WHERE id = $id");
	
		$sql = "DELETE FROM tnewsletter_address WHERE id = $id";
		$result = mysql_query ($sql);
		if ($result === false)
			break;
		else {
			audit_db ($config["id_user"], $config["REMOTE_ADDR"], "NEWSLETTER ADDRESESS DELETED", "Deleted $email");
		}
	}

	echo ui_print_success_message (__('Successfully multiple deleted'), '', true, 'h3', true);
	$id = 0;
}

// General issue listing

echo "<h2>".__('Newsletter addresses')."</h2>";
echo "<h4>".__('List addresses')."</h4>";

$search_text = (string) get_parameter ('search_text');	
$search_newsletter = (int) get_parameter ("search_newsletter");
$search_status = (int) get_parameter ('search_status',0);
$search_validate = get_parameter('search_validate',2);

if ($search_status != 2) {
	$where_clause = "WHERE status = $search_status ";
} else {
	$where_clause = "WHERE 1=1";
}

if ($search_text != "") {
	$where_clause .= sprintf (' AND email LIKE "%%%s%%" OR name LIKE "%%%s%%"', $search_text, $search_text);
}

if ($search_newsletter > 0 ){
	$where_clause .= " AND id_newsletter = $search_newsletter ";
}

if ($search_validate != 2){
	if ($search_validate == 0) {
		$where_clause .= " AND validated = 1 ";
	} else if ($search_validate == 1){
		$where_clause .= " AND validated = 0 ";
	}
}

$table = new StdClass();
$table->width = '100%';
$table->class = 'search-table';
$table->style = array ();
$table->style[0] = 'font-weight: bold;';
$table->style[2] = 'font-weight: bold;';
$table->data = array ();
$table->data[0][0] = __('Search');

$table->data[1][0] = print_input_text ("search_text", $search_text, "", 25, 100, true);
$newsletters = get_db_all_rows_sql("SELECT id, name FROM tnewsletter");
if ($newsletters == false) {
	$newsletters = array();
}

$newsletter_values = array();
foreach ($newsletters as $news) {
	$newsletter_values[$news['id']] = $news['name'];
}

$newsletter_values[0] = __('Any');

$table->data[2][0] = print_select ($newsletter_values, 
			"search_newsletter", $search_newsletter, '', '', '',
				true, 0, false, __("Newsletter"));

$status_values[0] = __('Show enabled addresses');
$status_values[1] = __('Show disabled addresses');
$status_values[2] = __('Any');

$table->data[3][0] = print_select ($status_values, "search_status",
			$search_status, '', '', '', true, 0, false, __("Status"));

$validated_values[0] = __('Validated');
$validated_values[1] = __('Pending');
$validated_values[2] = __('Any');

$table->data[4][0] = print_select ($validated_values, "search_validate",
			$search_validate, '', '', '', true, 0, false, __("Validate"));
$table->data[5][0] = print_submit_button (__('Search'), "search_btn", false, 'class="sub search"', true);

echo "<div class='divform'>";
echo '<form method="post" action="">';
print_table ($table);
echo '</form>';

echo '<form method="post" action="index.php?sec=customers&sec2=operation/newsletter/address_creation&create=1">';
	unset($table->data);
	$table->data[1][0] = print_submit_button (__('Create'), 'new_btn', false, 'class="sub next"', true);
	print_table ($table);
echo '</form>';
echo '</div>';


$sql = "SELECT * FROM tnewsletter_address $where_clause ORDER BY datetime DESC";
$issues = get_db_all_rows_sql ($sql);

echo "<div class='divresult'>";
if ($issues !== false) {
	$count_addresses = count($issues);
	echo '<h5>'.__('Total addresses: ').$count_addresses.'</h5>';

	$issues = print_array_pagination ($issues, 
			"index.php?sec=customers&sec2=operation/newsletter/address_definition&search_text=$search_text&search_status=$search_status&search_newsletter=$search_newsletter");
	
	$table = new StdClass();
	$table->width = "100%";
	$table->class = "listing";
	$table->data = array ();
	$table->style = array ();
	$table->style[0] = 'font-weight: bold';
	$table->colspan = array ();
	$table->head[0] = __('Newsletter');
	$table->head[1] = __('Email');
	$table->head[2] = __('Name');
	$table->head[3] = __('Status');
	$table->head[4] = __('Date');
	$table->head[5] = __('Validated');
	if(give_acl ($config["id_user"], $id_group, "CN")) {
		$table->head[6] = __('Disable/Delete');
		$table->head[7] = print_checkbox_extended('all_delete', 0, false, false,'check_all_checkboxes();', '', true, false);
	}

	
	foreach ($issues as $issue) {
		$data = array ();
		
		$newsletter_name = get_db_value ('name', 'tnewsletter', 'id', $issue["id_newsletter"]);
		
		$data[0] = "<a href='index.php?sec=customers&sec2=operation/newsletter/newsletter_creation&id=".$issue["id_newsletter"]."'>$newsletter_name</a>";
		
		$data[1] = $issue["email"];
		$data[2] = $issue["name"];
		
		if ($issue["status"] == 0)
			$data[3] = __("Enabled");
		elseif ($issue["status"] == 1)
			$data[3] = __("Disabled");	
		else
			$data[3] = __("Other");	

		$data[4] = $issue["datetime"];
		
		if ($issue["validated"]) {
			$data[5] = __("Validated");
		} else {
			$data[5] = __("Pending");
		}
	
		if(give_acl ($config["id_user"], $id_group, "CN")) {
			if ($issue["status"] == 1)
				$data[6] ='<a href="index.php?sec=customers&sec2=operation/newsletter/address_definition&
						enable=1&id='.$issue['id'].'"
						onClick="if (!confirm(\''.__('Are you sure?').'\'))
						return false;">
						<img src="images/lightbulb_off.png" title="'.__("Enabled").'"></a>';
			else
				$data[6] ='<a href="index.php?sec=customers&sec2=operation/newsletter/address_definition&
						disable=1&id='.$issue['id'].'"
						onClick="if (!confirm(\''.__('Are you sure?').'\'))
						return false;">
						<img src="images/lightbulb.png" title="'.__("Disabled").'"></a>';
						
			$data[6] .='&nbsp;<a href="index.php?sec=customers&sec2=operation/newsletter/address_definition&
						delete=1&id='.$issue['id'].'"
						onClick="if (!confirm(\''.__('Are you sure?').'\'))
						return false;">
						<img src="images/cross.png" title="Delete"></a>';
						
			$data[7] = print_checkbox_extended ('delete_multiple[]', $issue['id'], false, false, '', 'class="check_delete"', true);
		}
		array_push ($table->data, $data);
	}
	if($manager) {
		echo "<form method='post' action='index.php?sec=customers&sec2=operation/newsletter/address_definition&multiple_delete=1&search_text=$search_text&search_newsletter=$search_newsletter&search_status=$search_status&search_validate=$search_validate'>";
		print_table ($table);
		echo '<div class="button-form" style="width: '.$table->width.'">';
		print_submit_button (__('Delete selected items'), 'new_btn', false, 'class="sub delete"');
		echo '</div>';
		echo '</form>';
	}
	else {
		print_table ($table);
	}
}
else
	echo ui_print_error_message (__("No data to show"), '', true, 'h3', true);
echo "</div>";



?>

<script type="text/javascript">
function check_all_checkboxes() {
	if ($("input[name=all_delete]").attr('checked')) {
		$(".check_delete").attr('checked', true);
	}
	else {
		$(".check_delete").attr('checked', false);
	}
}
</script>
