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

global $config;

check_login();

if (! give_acl ($config["id_user"], 0, "IM")) {
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation","Trying to access company role management");
	require ("general/noaccess.php");
	exit;
}

$id = (int) get_parameter ('id');
$new_role = (bool) get_parameter ('new_role');
$create_role = (bool) get_parameter ('create_role');
$update_role = (bool) get_parameter ('update_role');
$delete_role = (bool) get_parameter ('delete_role');

// CREATE
if ($create_role) {
	$name = (string) get_parameter ("name");
	$description = (string) get_parameter ("description");
	$sql = sprintf ('INSERT INTO tcompany_role (name, description)
		VALUE ("%s", "%s")', $name, $description);
	$id = process_sql ($sql, 'insert_id');
	if ($id === false) {
		echo "<h3 class='error'>".__('Company role cannot be created')."</h3>";
	} else {
		echo "<h3 class='suc'>".__('Company role has been created successfully')."</h3>";
		insert_event ("COMPANY ROLE CREATED", $id, 0, $name);
	}
	$id = 0;
}

// UPDATE
if ($update_role) {
	$name = (string) get_parameter ('name');
	$description = (string) get_parameter ('description');

	$sql = sprintf ('UPDATE tcompany_role
		SET description = "%s", name = "%s" WHERE id = %d',
		$description, $name, $id);

	$result = process_sql ($sql);
	if ($result === false)
		echo "<h3 class='error'>".__('Company role cannot be updated')."</h3>";
	else {
		echo "<h3 class='suc'>".__('Company role updated ok')."</h3>";
		insert_event ("COMPANY ROLE", $id, 0, $name);
	}
	$id = 0;
}

// DELETE
if ($delete_role) {
	$name = get_db_value ('name', 'tcompany_role', 'id', $id);
	$sql = sprintf ('DELETE FROM tcompany_role WHERE id = %d', $id);
	$result = process_sql ($sql);
	insert_event ("COMPANY ROLE DELETED", $id, 0, $name);
	echo "<h3 class='suc'>".__('Deleted successfully')."</h3>";
	$id = 0;
}

echo "<h2>".__('Company role management')."</h2>";

// FORM (Update / Create)
if ($id || $new_role) {
	if ($new_role) {
		$name = '';
		$description = '';
	} else {
		$role = get_db_row ('tcompany_role', 'id', $id);
		$name = $role['name'];
		$description = $role['description'];
	}
	
	$table->width = '720px';
	$table->class = 'databox';
	$table->data = array ();
	$table->colspan = array ();
	
	$table->data[0][0] = print_input_text ("name", $name, "", 60, 100, true, __('Role name'));
	$table->data[1][0] = print_textarea ('description', 14, 1, $description, '', true, __('Description'));
	
	echo '<form method="post" action="index.php?sec=inventory&sec2=operation/companies/company_role">';
	print_table ($table);
	echo '<div class="button" style="width: '.$table->width.'">';
	if ($id) {
		print_submit_button (__('Update'), "update_btn", false, 'class="sub upd"', false);
		print_input_hidden ('update_role', 1);
		print_input_hidden ('id', $id);
	} else {
		print_input_hidden ('create_role', 1);
		print_submit_button (__('Create'), "create_btn", false, 'class="sub next"', false);
	}
	echo "</div>";
	echo '</form>';
} else {
	$search_text = (string) get_parameter ('search_text');
	
	$where_clause = "";
	if ($search_text != "") {
		$where_clause = sprintf ('WHERE name LIKE "%%%s%%"
			OR description LIKE "%%%s%%"', $search_text, $search_text);
	}

	$table->width = '400px';
	$table->class = 'search-table';
	$table->style = array ();
	$table->style[0] = 'font-weight: bold;';
	$table->data = array ();
	$table->data[0][0] = __('Search');
	$table->data[0][1] = print_input_text ("search_text", $search_text, "", 25, 100, true);
	$table->data[0][2] = print_submit_button (__('Search'), "search_btn", false, 'class="sub search"', true);;
	
	echo '<form method="post" action="index.php?sec=inventory&sec2=operation/companies/company_role">';
	print_table ($table);
	echo '</form>';
	
	$sql = "SELECT * FROM tcompany_role $where_clause ORDER BY name";
	$roles = get_db_all_rows_sql ($sql);

	if ($roles !== false) {

		$table->width = "720";
		$table->class = "listing";
		$table->data = array ();
		$table->size = array ();
		$table->style = array ();
		$table->style[0] = 'font-weight: bold';
		$table->head[0] = __('Name');
		$table->head[1] = __('Description');
		$table->head[2] = __('Delete');
		
		foreach ($roles as $role) {
			$data = array ();
			
			$data[0] = "<a href='index.php?sec=inventory&sec2=operation/companies/company_role&id=".
				$role["id"]."'>".$role["name"]."</a>";
			$data[1] = substr ($role["description"], 0, 70)."...";
			$data[3] = '<a href="index.php?sec=inventory&
						sec2=operation/companies/company_role&
						delete_role=1&id='.$role['id'].'"
						onClick="if (!confirm(\''.__('Are you sure?').'\'))
						return false;">
						<img src="images/cross.png"></a>';
			array_push ($table->data, $data);
		}
		print_table ($table);
	}
	
	echo '<form method="post" action="index.php?sec=inventory&sec2=operation/companies/company_role">';
	echo '<div class="button" style="width: '.$table->width.'">';
	print_submit_button (__('Create role'), 'new_btn', false, 'class="sub next"');
	print_input_hidden ('new_role', 1);
	echo '</div>';
}
?>
