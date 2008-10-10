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

check_login ();

if (! give_acl ($config["id_user"], 0, "IM")) {
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access company section");
	require ("general/noaccess.php");
	exit;
}

$id = (int) get_parameter ('id');
$new_type = (bool) get_parameter ('new_type');
$create_type = (bool) get_parameter ('create_type');
$update_type = (bool) get_parameter ('update_type');
$delete_type = (bool) get_parameter ('delete_type');

// CREATE
if ($create_type) {
	$name = (string) get_parameter ('name');
	$description = (string) get_parameter ('description');
	$id_wizard = (int) get_parameter ('id_wizard');

	$sql = sprintf ('INSERT INTO tincident_type (`name`, `description`, `id_wizard`)
		VALUE ("%s", "%s", %d)',
		$name, $description, $id_wizard);
	
	$id = process_sql ($sql, 'insert-id');
	if ($id === false) {
		echo '<h3 class="error">'.__('Incident type cannot be created').'</h3>';
	} else {
		echo '<h3 class="suc">'.__('Incident type has been created successfully').'</h3>';
		insert_event ("INCIDENT TYPE CREATED", $id, 0, $name);
	}
	$id = 0;
}

// UPDATE
if ($update_type) {
	$name = (string) get_parameter ('name');
	$description = (string) get_parameter ('description');
	$id_wizard = (int) get_parameter ('id_wizard');

	$sql = sprintf ('UPDATE tincident_type
	SET description = "%s", name = "%s", id_wizard = %d
	WHERE id = %d',
	$description, $name, $id_wizard, $id);
	
	$result = process_sql ($sql);
	if ($result === false)
		echo '<h3 class="error">'.__('Incident type cannot be updated').'</h3>';
	else {
		echo '<h3 class="suc">'.__('Incident type updated ok').'</h3>';
		insert_event ("INCIDENT TYPE", $id, 0, $name);
	}
	$id = 0;
}

// DELETE
if ($delete_type) {
	$name = get_db_value ('name', 'tincident_type', 'id', $id);
	$sql = sprintf ('DELETE FROM tincident_type WHERE id = %d', $id);
	process_sql ($sql);
	insert_event ("INCIDENT TYPE DELETED", $id, 0, $name);
	echo '<h3 class="suc">'.__('Deleted successfully').'</h3>';
	$id = 0;
}

echo '<h2>'.__('Incident types').'</h2>';

// FORM (Update / Create)
if ($id || $new_type) {
	if ($new_type) {
		$id = 0;
		$name = "";
		$description = "";
		$id_wizard = "";
	} else {
		$type = get_db_row ('tincident_type', 'id', $id);
		$name = $type['name'];
		$description = $type['description'];
		$id_wizard = $type['id_wizard'];
	}
	
	$table->width = '740px';
	$table->width = "720px";
	$table->class = "databox";
	$table->data = array ();
	$table->colspan = array ();
	$table->colspan[1][0] = 2;
	
	$table->data[0][0] = print_input_text ('name', $name, '', 30, 100, true, __('Type name'));
	$table->data[0][1] = print_select_from_sql ('SELECT id, name FROM twizard ORDER BY name',
		'id_wizard', $id_wizard, '', 'Select', 0, true, false, false, __('Wizard'));
	$table->data[1][0] = print_textarea ('description', 14, 1, $description, '', true, __('Description'));
	
	echo '<form method="post" action="index.php?sec=incidents&sec2=operation/incidents/type_detail">';
	print_table ($table);
	
	echo '<div class="button" style="width: '.$table->width.'">';
	if ($id) {
		print_submit_button (__('Update'), 'update_btn', false, 'class="sub upd"', false);
		print_input_hidden ('update_type', 1);
		print_input_hidden ('id', $id);
	} else {
		print_submit_button (__('Create'), 'create_btn', false, 'class="sub next"', false);
		print_input_hidden ('create_type', 1);
	}
	echo '</div>';
	echo '</form>';
} else {
	$search_text = (string) get_parameter ('search_text');
	
	$where_clause = '';
	if ($search_text != "") {
		$where_clause .= sprintf ('WHERE name LIKE "%%%s%%"', $search_text);
	}

	$table->width = '400px';
	$table->class = 'search-table';
	$table->style = array ();
	$table->style[0] = 'font-weight: bold;';
	$table->data = array ();
	$table->data[0][0] = __('Search');
	$table->data[0][1] = print_input_text ("search_text", $search_text, "", 25, 100, true);
	$table->data[0][2] = print_submit_button (__('Search'), "search_btn", false, 'class="sub search"', true);;
	
	echo '<form method="post" action="index.php?sec=inventory&sec2=operation/types/type_detail">';
	print_table ($table);
	echo '</form>';

	$sql = "SELECT * FROM tincident_type $where_clause ORDER BY name";
	$types = get_db_all_rows_sql ($sql);
	
	if ($types !== false) {
		$table->width = '720px';
		$table->class = 'listing';
		$table->data = array ();
		$table->size = array ();
		$table->size[2] = '40px';
		$table->align = array ();
		$table->align[2] = 'center';
		$table->style = array ();
		$table->style[0] = 'font-weight: bold';
		$table->head[0] = __('Name');
		$table->head[1] = __('Wizard');
		$table->head[2] = __('Delete');
		
		foreach ($types as $type) {
			$data = array ();
			
			$data[0] = '<a href="index.php?sec=incidents&sec2=operation/incidents/type_detail&id='.
				$type['id'].'">'.$type['name'].'</a>';
			$data[1] = get_db_value ('name', 'tincident_type', 'id', $type['id_wizard']);
			$data[2] = '<a href="index.php?sec=incidents&
						sec2=operation/incidents/type_detail&
						delete_type=1&id='.$type['id'].'"
						onClick="if (!confirm(\''.__('are_you_sure').'\'))
						return false;">
						<img src="images/cross.png"></a>';
			array_push ($table->data, $data);
		}
		print_table ($table);
	}
	
	echo '<form method="post" action="index.php?sec=incidents&sec2=operation/incidents/type_detail">';
	echo '<div class="button" style="width: '.$table->width.'">';
	print_submit_button (__('Create type'), 'new_btn', false, 'class="sub next"');
	print_input_hidden ('new_type', 1);
	echo '</div>';
	echo '</form>';
}

?>
