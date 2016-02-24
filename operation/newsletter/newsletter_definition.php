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

enterprise_include('include/functions_crm.php');

$manager = enterprise_hook ('crm_check_acl_news', array ($config['id_user']));

if ($manager === ENTERPRISE_NOT_HOOK) {	
	$manager = true;	
} else {
	if (!$manager) {
		include ("general/noaccess.php");
		exit;
	}
}


$id = (int) get_parameter ('id');
$create = (bool) get_parameter ('create');
$update = (bool) get_parameter ('update');
$delete = (bool) get_parameter ('delete');
$validate_newsletter = (bool) get_parameter('validate_newsletter', 0);

if ($validate_newsletter) {

	$sql = "SELECT * FROM tnewsletter_address WHERE id_newsletter = $id AND validated = 0";

	$newsletter_emails = get_db_all_rows_sql($sql);

	if ($newsletter_emails === false) {
		$newsletter_emails = array();
	}
	
	$i = 0;
	foreach ($newsletter_emails as $email) {

		$values['validated'] = 1;
		$values['status'] = 0;
		
		process_sql_update('tnewsletter_address', $values, array('id'=>$email['id']));
		$i++;
	}
	
	echo "<h3 class='suc'>".__('Emails validated: ').$i."</h3>";
	$id = 0;
}

// CREATE
if ($create) {
	if (!$manager) {
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create a new newsletter");
		require ("general/noaccess.php");
		exit;
	}
	
	$name = get_parameter("name");
	$id_group = get_parameter("id_group", 1);
	$from_desc = get_parameter("from_desc");
	$from_address = get_parameter("from_address");
	$description = get_parameter("description");
	
	if (! $manager) {
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create a newsletter");
		require ("general/noaccess.php");
		exit;
	}

	$sql = sprintf ('INSERT INTO tnewsletter (name, from_address, from_desc, description, id_group)
			 VALUES ("%s", "%s", "%s", "%s", %d)',
			 $name, $from_address, $from_desc, $description, $id_group);

	$id = process_sql ($sql, 'insert_id');
	if ($id === false)
		echo "<h3 class='error'>".__('Could not be created')."</h3>";
	else {
		echo "<h3 class='suc'>".__('Successfully created')."</h3>";
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "NEWSLETTER CREATED", "Created newsletter $name");
	}
	$id = 0;
}

// UPDATE
if ($update) {
	$id_group = (int) get_parameter ("id_group", 0);
	if (! $manager) {
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to update a newsletter");
		require ("general/noaccess.php");
		exit;
	}

	$id = get_parameter("id", 1);
	$name = get_parameter("name");
	$id_group = get_parameter("id_group", 1);
	$from_desc = get_parameter("from_desc");
	$from_address = get_parameter("from_address");
	$description = get_parameter("description");

	$sql = sprintf ('UPDATE tnewsletter SET description = "%s", name = "%s",
		from_address = "%s", from_desc = "%s", id_group = "%s" WHERE id = %d',
		$description, $name, $from_address, $from_desc, $id_group, $id);

	$result = mysql_query ($sql);
	if ($result === false)
		echo "<h3 class='error'>".__('Could not be updated')."</h3>";
	else {
		echo "<h3 class='suc'>".__('Successfully updated')."</h3>";
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "NEWSLETTER UPDATED", "Updated newsletter $name");
	}
	$id = 0;
}

// DELETE
if ($delete) { // if delete

	$id = (int) get_parameter ('id');
	$name = get_db_value ('name', 'tnewsletter', 'id', $id);
	$id_group = get_db_value ('id_group', 'tnewsletter', 'id', $id);

	if (! $manager) {
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to delete a company without privileges");
		require ("general/noaccess.php");
		exit;
	}

	$sql= sprintf ('DELETE FROM tnewsletter WHERE id = %d', $id);
	process_sql ($sql);
	
	$sql= sprintf ('DELETE FROM tnewsletter_tracking WHERE id_newsletter = %d', $id);
	process_sql ($sql);

	$sql= sprintf ('DELETE FROM tnewsletter_queue_data WHERE id_newsletter = %d', $id);
	process_sql ($sql);
	
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "Newsletter Management", "Deleted newsletter $name");
	echo "<h3 class='suc'>".__('Successfully deleted')."</h3>";
	$id = 0;
}


// General newsletter listing

echo "<h2>".__('Newsletter management')."</h2>";
echo "<br>";
$search_text = (string) get_parameter ('search_text');	
$where_clause = "WHERE 1=1 ";

if ($search_text != "") {
	$where_clause .= sprintf ('AND name LIKE "%%%s%%"', $search_text);
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

$sql = "SELECT * FROM tnewsletter $where_clause ORDER BY name";
$newsletters = get_db_all_rows_sql ($sql);

$newsletters = print_array_pagination ($newsletters, "index.php?sec=customers&sec2=operation/newsletter/operation/newsletter/newsletter_definition&search_text='$search_text");

if ($newsletters !== false) {
	$table->width = "90%";
	$table->class = "listing";
	$table->data = array ();
	$table->style = array ();
	$table->style[0] = 'font-weight: bold; font-size: 14px';
	$table->style[1] = 'font-weight: bold';
	$table->colspan = array ();
	$table->head[0] = __('ID');
	$table->head[1] = __('Name');
	$table->head[2] = __('Total addresses');
	$table->head[3] = __('Subscribe Link');
	$table->head[4] = __('Unsubscribe Link');
	$table->head[5] = __('Validated addr.');
	$table->head[6] = __('Invalid addr.');
	$table->style[5] = "text-aling: center; vertical-align: middle";
	$table->head[7] = __('Pending validation');
	if(give_acl ($config["id_user"], $id_group, "CN")) {
		$table->head[8] = __('Action');
	}
	foreach ($newsletters as $newsletter) {
		$data = array ();

		$data[0] = $newsletter["id"];
		
		$data[1] = "<a href='index.php?sec=customers&sec2=operation/newsletter/newsletter_creation&id=".
			$newsletter["id"]."'>".$newsletter["name"]."</a>";
		
		$data[2] = get_db_sql ("SELECT COUNT(id) FROM tnewsletter_address WHERE id_newsletter = ".$newsletter["id"]);
		
		$data[3] = "<a href='".$config["base_url"]."include/newsletter.php?operation=subscribe&id=".$newsletter["id"]."'>".__("Full form")."</a><br>";

		$data[3] .= "<a href='".$config["base_url"]."include/newsletter.php?operation=subscribe&id=".$newsletter["id"]."&clean=1'>".__("Clean form")."</a>";

		$data[4] = "<a href='".$config["base_url"]."include/newsletter.php?operation=desubscribe&id=".$newsletter["id"]."'>".__("Full form")."</a><br>";

		$data[4] .= "<a href='".$config["base_url"]."include/newsletter.php?operation=desubscribe&id=".$newsletter["id"]."&clean=1'>".__("Clean form")."</a>";
		
		$validated_addr = get_db_sql ("SELECT COUNT(id) FROM tnewsletter_address WHERE id_newsletter = ".$newsletter["id"] . " AND validated = 1 AND status = 0");
		$data[5] = "<a href='index.php?sec=customers&sec2=operation/newsletter/address_definition&search_status=0&search_validate=0&search_newsletter=".
			$newsletter["id"]."'>".$validated_addr."</a>";
		
		$invalid_addr = get_db_sql ("SELECT COUNT(id) FROM tnewsletter_address WHERE id_newsletter = ".$newsletter["id"] . " AND validated = 1 AND status = 1");
		$data[6] = "<a href='index.php?sec=customers&sec2=operation/newsletter/address_definition&search_status=1&search_validate=0&search_newsletter=".
			$newsletter["id"]."'>".$invalid_addr."</a>";
		
		$pending_validation = get_db_sql ("SELECT COUNT(id) FROM tnewsletter_address WHERE id_newsletter = ".$newsletter["id"] . " AND validated = 0");
		$data[7] = "<a href='index.php?sec=customers&sec2=operation/newsletter/address_definition&search_validate=1&search_newsletter=".
			$newsletter["id"]."'>".$pending_validation."</a>";
		
		$data[8] ='<a href="index.php?sec=customers&sec2=operation/newsletter/newsletter_definition&
						validate_newsletter=1&id='.$newsletter['id'].'" 
						onClick="if (!confirm(\''.__('Are you sure?').'\'))
						return false;">
						<img src="images/accept.png" title="Forced email validation of pending addresses" ></a>';
		
		if(give_acl ($config["id_user"], $id_group, "CN")) {
			$data[8] .='<a href="index.php?sec=customers&sec2=operation/newsletter/newsletter_definition&
						delete=1&id='.$newsletter['id'].'"
						onClick="if (!confirm(\''.__('Are you sure?').'\'))
						return false;">
						<img src="images/cross.png"></a>';
		}
		array_push ($table->data, $data);
	}
	print_table ($table);
}

if($manager) {
	echo '<form method="post" action="index.php?sec=customers&sec2=operation/newsletter/newsletter_creation&create=1">';
	echo '<div class="button" style="width: '.$table->width.'">';
	print_submit_button (__('Create'), 'new_btn', false, 'class="sub next"');
	echo '</div>';
	echo '</form>';
}

?>
