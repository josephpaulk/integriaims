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

if (! give_acl ($config["id_user"], 0, "IR")) {
	audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation","Trying to access Contact");
	require ("general/noaccess.php");
	exit;
}

$id = (int) get_parameter ('id');
$new_contact = (bool) get_parameter ('new_contact');
$create_contact = (bool) get_parameter ('create_contact');
$update_contact = (bool) get_parameter ('update_contact');
$delete_contact = (bool) get_parameter ('delete_contact');
$get_contacts = (bool) get_parameter ('get_contacts');

if ($get_contacts) {
	$contract = get_contract ($id);
	$company = get_company ($contract['id']);
	$contacts = get_company_contacts ($company['id'], false);
	
	echo json_encode ($contacts);
	if (defined ('AJAX'))
		return;
}

// Create
if ($create_contact) {
	$fullname = (string) get_parameter ('fullname');
	$phone = (string) get_parameter ('phone');
	$mobile = (string) get_parameter ('mobile');
	$email = (string) get_parameter ('email');
	$position = (string) get_parameter ('position');
	$id_company = (int) get_parameter ('id_company');
	$disabled = (int) get_parameter ('disabled');
	$description = (string) get_parameter ('description');

	$sql = sprintf ('INSERT INTO tcompany_contact (fullname, phone, mobile,
		email, position, id_company, disabled, description)
		VALUE ("%s", "%s", "%s", "%s", "%s", %d, %d, "%s")',
		$fullname, $phone, $mobile, $email, $position,
		$id_company, $disabled, $description);

	$id = process_sql ($sql, 'insert_id');
	if (defined ('AJAX')) {
		echo json_encode ($id);
		return;
	}
	
	if ($id === false) {
		echo "<h3 class='error'>".__('Could not be created')."</h3>";
	} else {
		echo "<h3 class='suc'>".__('Successfully created')."</h3>";
		insert_event ("CONTACT CREATED", $id, 0, $fullname);
	}
	$id = 0;
}

// Update
if ($update_contact) { // if modified any parameter
	$fullname = (string) get_parameter ('fullname');
	$phone = (string) get_parameter ('phone');
	$mobile = (string) get_parameter ('mobile');
	$email = (string) get_parameter ('email');
	$position = (string) get_parameter ('position');
	$id_company = (int) get_parameter ('id_company');
	$disabled = (int) get_parameter ('disabled');
	$description = (string) get_parameter ('description');

	$sql = sprintf ('UPDATE tcompany_contact
		SET description = "%s", fullname = "%s", phone = "%s",
		mobile = "%s", email = "%s", position = "%s",
		id_company = %d, disabled = %d WHERE id = %d',
		$description, $fullname, $phone, $mobile, $email, $position,
		$id_company, $disabled, $id);

	$result = process_sql ($sql);
	if ($result === false) {
		echo "<h3 class='error'>".__('Could not be updated')."</h3>";
	} else {
		echo "<h3 class='suc'>".__('Successfully updated')."</h3>";
		insert_event ("CONTACT", $id, 0, $fullname);
	}
	$id = 0;
}

// Delete
if ($delete_contact) {
	$fullname = get_db_value  ('fullname', 'tcompany_contact', 'id', $id);
	$sql = sprintf ('DELETE FROM tcompany_contact WHERE id = %d', $id);
	process_sql ($sql);
	insert_event ("CONTACT DELETED", $id, 0, "$fullname");
	echo "<h3 class='suc'>".__('Successfully deleted')."</h3>";
	$id = 0;
}

echo "<h2>".__('Contact management')."</h2>";

// FORM (Update / Create)
if ($id || $new_contact) {
	if ($new_contact) {
		$id = 0;
		$fullname = (string) get_parameter ('fullname');
		$phone = (string) get_parameter ('phone');
		$mobile = (string) get_parameter ('mobile');
		$email = (string) get_parameter ('email');
		$position = (string) get_parameter ('position');
		$id_company = (int) get_parameter ('id_company');
		$disabled = (int) get_parameter ('disabled');
		$description = (string) get_parameter ('description');
		$id_contract = (int) get_parameter ('id_contract');
		if ($id_contract) {
			$id_company = (int) get_db_value ('id_company', 'tcontract', 'id', $id_contract);
		}
	} else {
		$contact = get_db_row ("tcompany_contact", "id", $id);
		$fullname = $contact['fullname'];
		$phone = $contact['phone'];
		$mobile = $contact['mobile'];
		$email = $contact['email'];
		$position = $contact['position'];
		$id_company = $contact['id_company'];
		$disabled = $contact['disabled'];
		$description = $contact['description'];
	}
	
	$table->width = "90%";
	$table->class = "databox";
	$table->data = array ();
	$table->colspan = array ();
	$table->colspan[0][0] = 4;
	$table->colspan[1][0] = 4;
	$table->colspan[4][0] = 4;
	
	$table->data[0][0] = print_input_text ("fullname", $fullname, "", 60, 100, true, __('Full name'));
	
	$table->data[1][0] = print_input_text ("email", $email, "", 35, 100, true, __('Email'));
	$table->data[2][0] = print_input_text ("phone", $phone, "", 15, 60, true, __('Phone number'));
	$table->data[2][1] = print_input_text ("mobile", $mobile, "", 15, 60, true, __('Mobile number'));
	$table->data[3][0] = print_input_text ('position', $position, '', 25, 50, true, __('Position'));
	$table->data[3][1] = print_select_from_sql ('SELECT id, name FROM tcompany ORDER BY name',
		'id_company', $id_company, '', __('Select'), 0, true, false, false, __('Company'));
	$table->data[4][0] = print_textarea ("description", 10, 1, $description, '', true, __('Description'));
	
	echo '<form method="post" id="contact_form">';
	print_table ($table);
	
	echo '<div class="button" style="width: '.$table->width.'">';
	if ($id) {
		print_submit_button (__('Update'), 'update_btn', false, 'class="sub upd"', false);
		print_input_hidden ('update_contact', 1);
		print_input_hidden ('id', $id);
	} else {
		print_submit_button (__('Create'), 'create_btn', false, 'class="sub next"', false);
		print_input_hidden ('create_contact', 1);
	}
	echo "</div>";
	echo "</form>";
} else {
	$search_text = (string) get_parameter ('search_text');
	$id_company = (int) get_parameter ('id_company');
	
	$where_clause = "WHERE 1=1";
	if ($search_text != "") {
		$where_clause .= sprintf (' AND fullname LIKE "%%%s%%"', $search_text);
	}
	if ($id_company) {
		$where_clause .= sprintf (' AND id_company = %d', $id_company);
	}

	$table->width = '400px';
	$table->class = 'search-table';
	$table->style = array ();
	$table->style[0] = 'font-weight: bold;';
	$table->data = array ();
	$table->data[0][0] = print_input_text ("search_text", $search_text, "", 15, 100, true, __('Search'));
	$table->data[0][1] = print_select (get_companies (), 'id_company', $id_company, '', 'All', 0, true, false, false, __('Company'));
	$table->data[0][2] = print_submit_button (__('Search'), "search_btn", false, 'class="sub search"', true);;
	
	echo '<form method="post">';
	print_table ($table);
	echo '</form>';

	$sql = "SELECT * FROM tcompany_contact $where_clause ORDER BY id_company, fullname";
	$contacts = get_db_all_rows_sql ($sql);

	$contacts = print_array_pagination ($contacts, "index.php?sec=inventory&sec2=operation/contacts/contact_detail");

	if ($contacts !== false) {
		unset ($table);
		$table->width = "90%";
		$table->class = "listing";
		$table->data = array ();
		$table->size = array ();
		$table->size[3] = '40px';
		$table->style = array ();
		$table->style[] = 'font-weight: bold';
		$table->head = array ();
		$table->head[0] = __('Full name');
		$table->head[1] = __('Company');
		$table->head[2] = __('Email');
		$table->head[3] = __('Delete');
		
		foreach ($contacts as $contact) {
			$data = array ();
			// Name
			$data[0] = "<a href='index.php?sec=inventory&sec2=operation/contacts/contact_detail&id=".
				$contact['id']."'>".$contact['fullname']."</a>";
			$data[1] = get_db_value ('name', 'tcompany', 'id', $contact['id_company']);
			$data[2] = $contact['email'];
			$data[3] = '<a href="index.php?sec=inventory&
						sec2=operation/contacts/contact_detail&
						delete_contact=1&id='.$contact['id'].'"
						onClick="if (!confirm(\''.__('Are you sure?').'\'))
						return false;">
						<img src="images/cross.png"></a>';
			array_push ($table->data, $data);
		}
		print_table ($table);
	}
	
	echo '<form method="post" action="index.php?sec=inventory&sec2=operation/contacts/contact_detail">';
	echo '<div class="button" style="width: '.$table->width.'">';
	print_submit_button (__('Create contact'), 'new_btn', false, 'class="sub next"');
	print_input_hidden ('new_contact', 1);
	echo '</div>';
	echo '</form>';
}
?>
