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
$update = (bool) get_parameter ('update');
$delete = (bool) get_parameter ('delete');

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
	
	if (! give_acl ($config["id_user"], $id_group, "VM")) {
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
	if (! give_acl ($config["id_user"], $id_group, "VM")) {
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

	if (! give_acl ($config["id_user"], $id_group, "VM")) {
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to delete a company without privileges");
		require ("general/noaccess.php");
		exit;
	}

	$sql= sprintf ('DELETE FROM tnewsletter WHERE id = %d', $id);
	process_sql ($sql);
	insert_event ("NEWSLETTER DELETED", $id, 0, $name);
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
	$table->style[0] = 'font-weight: bold';
	$table->colspan = array ();
	$table->head[0] = __('Name');
	$table->head[1] = __('# of editions');
	$table->head[2] = __('Group');
	$table->head[3] = __('Addresses');
	$table->head[4] = __('Last edition');
	if(give_acl ($config["id_user"], $id_group, "VM")) {
		$table->head[5] = __('Delete');
	}
	foreach ($newsletters as $newsletter) {
		$data = array ();
		
		$data[0] = "<a href='index.php?sec=customers&sec2=operation/newsletter/newsletter_creation&id=".
			$newsletter["id"]."'>".$newsletter["name"]."</a>";

		$data[1] = get_db_sql ("SELECT COUNT(id) FROM tnewsletter_content WHERE id_newsletter = ".$newsletter["id"]);	
		$data[2] = dame_nombre_grupo($newsletter["id_group"]);
		
		$data[3] = get_db_sql ("SELECT COUNT(id) FROM tnewsletter_address WHERE id_newsletter = ".$newsletter["id"]);
	
		$data[4] = get_db_sql ("SELECT MAX(datetime) FROM tnewsletter_content WHERE id_newsletter = ".$newsletter["id"]);
		
		$data[5] .= "</a>";
		if(give_acl ($config["id_user"], $id_group, "VM")) {
			$data[6] ='<a href="index.php?sec=customers&sec2=operation/newsletter/newsletter_definition&
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
