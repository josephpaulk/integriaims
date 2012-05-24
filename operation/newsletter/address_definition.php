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
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access company section");
	require ("general/noaccess.php");
	exit;
}

$manager = give_acl ($config["id_user"], 0, "VM");

$id = (int) get_parameter ('id');
$create = (bool) get_parameter ('create');
$disable = (bool) get_parameter ('disable');
$delete = (bool) get_parameter ('delete');

// CREATE
if ($create) {
	if (!$manager) {
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create a new newsletter");
		require ("general/noaccess.php");
		exit;
	}
	
	$data = get_parameter ("data");
	$id_newsletter = get_parameter ("id_newsletter"); 
	
	
    $datetime = date ("Y-m-d H:i:s"); 		
    $id_group = get_db_sql ("SELECT id_group FROM tnewsletter WHERE id = $id_newsletter");
    
	if (! give_acl ($config["id_user"], $id_group, "VM")) {
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create a newsletter");
		require ("general/noaccess.php");
		exit;
	}

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

	echo "<h3 class='suc'>".__('Successfully added')." $total/$invalid ". __("addresses (valid/invalid)"). "</h3>";
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "NEWSLETTER ADDRESESS CREATED", "Created newsletter $total");
	
	
	$id = 0;
}

// DISABLE
if ($disable) {

	$id = get_parameter("id", 1);

	$sql = "UPDATE tnewsletter_address SET status = 1 WHERE id = $id";
	$result = mysql_query ($sql);
	if ($result === false)
		echo "<h3 class='error'>".__('Could not be updated')."</h3>";
	else {
		echo "<h3 class='suc'>".__('Successfully disabled')."</h3>";
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
		echo "<h3 class='error'>".__('Could not be deleted')."</h3>";
	else {
		echo "<h3 class='suc'>".__('Successfully deleted')."</h3>";
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "NEWSLETTER ADDRESESS DELETED", "Deleted $email");
	}
	$id = 0;
}

// General issue listing

echo "<h2>".__('Newsletter addresses management')."</h2>";
echo "<br>";
$search_text = (string) get_parameter ('search_text');	
$where_clause = "WHERE 1=1 ";

if ($search_text != "") {
	$where_clause .= sprintf ('AND email LIKE "%%%s%%" OR name LIKE "%%%s%%"', $search_text, $search_text);
}

$table->width = '90%';
$table->class = 'search-table';
$table->style = array ();
$table->style[0] = 'font-weight: bold;';
$table->style[2] = 'font-weight: bold;';
$table->data = array ();
$table->data[0][0] = __('Search');
$table->data[0][1] = print_input_text ("search_text", $search_text, "", 25, 100, true);
$table->data[0][4] = print_submit_button (__('Search'), "search_btn", false, 'class="sub search"', true);

echo '<form method="post" action="">';
print_table ($table);
echo '</form>';

$sql = "SELECT * FROM tnewsletter_address $where_clause ORDER BY datetime DESC";

$issues = get_db_all_rows_sql ($sql);

$issues = print_array_pagination ($issues, "index.php?sec=customers&sec2=operation/newsletter/address_definition&search_text='$search_text");

if ($issues !== false) {
	$table->width = "90%";
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
	if(give_acl ($config["id_user"], $id_group, "VM")) {
		$table->head[5] = __('Disable/Delete');
	}

	
	foreach ($issues as $issue) {
		$data = array ();
		
		$newsletter_name = get_db_value ('name', 'tnewsletter', 'id', $issue["id_newsletter"]);
		
		$data[0] = "<a href='index.php?sec=customers&sec2=operation/newsletter/newsletter_creation&id=".$issue["id_newsletter"]."'>$newsletter_name</a>";
		
		$data[1] = $issue["email"];
		$data[2] = $issue["name"];
		
		if ($issue["status"] == 0)
			$data[3] = __("Valid");
		elseif ($issue["status"] == 1)
			$data[3] = __("Disabled");	
		else
			$data[3] = __("Other");	

		$data[4] = $issue["datetime"];
	
		if(give_acl ($config["id_user"], $id_group, "VM")) {
			$data[5] ='<a href="index.php?sec=customers&sec2=operation/newsletter/address_definition&
						disable=1&id='.$issue['id'].'"
						onClick="if (!confirm(\''.__('Are you sure?').'\'))
						return false;">
						<img src="images/info.png" title="Disable"></a>';
						
			$data[5] .='&nbsp;<a href="index.php?sec=customers&sec2=operation/newsletter/address_definition&
						delete=1&id='.$issue['id'].'"
						onClick="if (!confirm(\''.__('Are you sure?').'\'))
						return false;">
						<img src="images/cross.png" title="Delete"></a>';
		}
		array_push ($table->data, $data);
	}
	print_table ($table);
}

if($manager) {
	echo '<form method="post" action="index.php?sec=customers&sec2=operation/newsletter/issue_creation&create=1">';
	echo '<div class="button" style="width: '.$table->width.'">';
	print_submit_button (__('Create'), 'new_btn', false, 'class="sub next"');
	echo '</div>';
	echo '</form>';
}


?>
