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

if (! give_acl ($config["id_user"], 0, "VR")) {
	audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation","Trying to access Contact");
	require ("general/noaccess.php");
	exit;
}

$manager = give_acl ($config["id_user"], 0, "VM");

$id = (int) get_parameter ('id');
if($id != 0) {
	$id_company = get_db_value ('id_company', 'tcompany_contact', 'id', $id);
	$id_group = get_db_value ('id_grupo', 'tcompany', 'id', $id_company);
}
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
	$id_company = (int) get_parameter ('id_company');
	$id_group = get_db_value ('id_grupo', 'tcompany', 'id', $id_company);

	if (! give_acl ($config["id_user"], $id_group, "VM")) {
	       audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation","Trying to create a new contact in a group without access");
	        require ("general/noaccess.php");
	        exit;
	}

	if($company === false) {
		$id = false;
	}
	else {
		$fullname = (string) get_parameter ('fullname');
		$phone = (string) get_parameter ('phone');
		$mobile = (string) get_parameter ('mobile');
		$email = (string) get_parameter ('email');
		$position = (string) get_parameter ('position');
		
		$disabled = (int) get_parameter ('disabled');
		$description = (string) get_parameter ('description');

		$sql = sprintf ('INSERT INTO tcompany_contact (fullname, phone, mobile,
			email, position, id_company, disabled, description)
			VALUE ("%s", "%s", "%s", "%s", "%s", %d, %d, "%s")',
			$fullname, $phone, $mobile, $email, $position,
			$id_company, $disabled, $description);

		$id = process_sql ($sql, 'insert_id');
	}
	
	if (defined ('AJAX')) {
		echo json_encode ($id);
		return;
	}
	
	if ($id === false) {
		echo "<h3 class='error'>".__('Could not be created')."</h3>";
	} else {
		echo "<h3 class='suc'>".__('Successfully created')."</h3>";
		audit_db ($config['id_user'], $REMOTE_ADDR, "Contact created", "Contact named '$fullname' has been added");
	}
	$id = 0;
}

// Update
if ($update_contact) { // if modified any parameter
	if (!give_acl ($config["id_user"], $id_group, "VW")) {
	       audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation","Trying to update a contact in a group without access");
	        require ("general/noaccess.php");
	        exit;
	}

	$fullname = (string) get_parameter ('fullname');
	$phone = (string) get_parameter ('phone');
	$mobile = (string) get_parameter ('mobile');
	$email = (string) get_parameter ('email');
	$position = (string) get_parameter ('position');
	$disabled = (int) get_parameter ('disabled');
	$description = (string) get_parameter ('description');
	$id_company = (int) get_parameter ('id_company');

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
		audit_db ($config['id_user'], '', "Contact updated", "Contact named '$fullname' has been updated");
	}
	$id = 0;
}

// Delete
if ($delete_contact) {
	if (! give_acl ($config["id_user"], $id_group, "VM")) {
	       audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation","Trying to delete a contact in a group without access");
	        require ("general/noaccess.php");
	        exit;
	}

	$fullname = get_db_value  ('fullname', 'tcompany_contact', 'id', $id);
	$sql = sprintf ('DELETE FROM tcompany_contact WHERE id = %d', $id);
	process_sql ($sql);
	audit_db ($config['id_user'], $REMOTE_ADDR, "Contact deleted", "Contact named '$fullname' has been deleted");
	echo "<h3 class='suc'>".__('Successfully deleted')."</h3>";
	$id = 0;
}

echo "<h2>".__('Contact management')."</h2>";

// FORM (Update / Create)
if ($id || $new_contact) {
	if ($new_contact) {
		if (! give_acl ($config["id_user"], $id_group, "VM")) {
			audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation","Trying to create a contact in a group without access");
			require ("general/noaccess.php");
			exit;
		}
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
		if (! give_acl ($config["id_user"], $id_group, "VR")) {
			audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation","Trying to access a contact in a group without access");
			require ("general/noaccess.php");
			exit;
		}
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
	
	if (give_acl ($config["id_user"], $id_group, "VW")) {
		$table->data[0][0] = print_input_text ("fullname", $fullname, "", 60, 100, true, __('Full name'));
		
		$table->data[1][0] = print_input_text ("email", $email, "", 35, 100, true, __('Email'));
		$table->data[2][0] = print_input_text ("phone", $phone, "", 15, 60, true, __('Phone number'));
		$table->data[2][1] = print_input_text ("mobile", $mobile, "", 15, 60, true, __('Mobile number'));
		$table->data[3][0] = print_input_text ('position', $position, '', 25, 50, true, __('Position'));
		
		// TODO: Show only companies with access to them
		$table->data[3][1] = print_select_from_sql ('SELECT id, name FROM tcompany ORDER BY name',
			'id_company', $id_company, '', '', '', true, false, false, __('Company'));
			
		$table->data[3][1] .= "&nbsp;&nbsp;<a href='index.php?sec=customers&sec2=operation/companies/company_detail&id=$id_company'>";
		$table->data[3][1] .= "<img src='images/company.png'></a>";
		
		$table->data[4][0] = print_textarea ("description", 10, 1, $description, '', true, __('Description'));
	}
	else {
		if($fullname == '') {
			$fullname = '<i>-'.__('Empty').'-</i>';
		}		
		$table->data[0][0] = "<b>".__('Full name')."</b><br>$fullname<br>";
		if($email == '') {
			$email = '<i>-'.__('Empty').'-</i>';
		}		
		$table->data[1][0] = "<b>".__('Email')."</b><br>$email<br>";
		if($phone == '') {
			$phone = '<i>-'.__('Empty').'-</i>';
		}		
		$table->data[2][0] = "<b>".__('Phone number')."</b><br>$phone<br>";
		if($mobile == '') {
			$mobile = '<i>-'.__('Empty').'-</i>';
		}		
		$table->data[2][1] = "<b>".__('Mobile number')."</b><br>$mobile<br>";
		if($position == '') {
			$position = '<i>-'.__('Empty').'-</i>';
		}		
		$table->data[3][0] = "<b>".__('Position')."</b><br>$position<br>";
		
		$company_name = get_db_value('name','tcompany','id',$id_company);

		$table->data[3][1] = "<b>".__('Company')."</b><br>$company_name";
			
		$table->data[3][1] .= "&nbsp;&nbsp;<a href='index.php?sec=customers&sec2=operation/companies/company_detail&id=$id_company'>";
		$table->data[3][1] .= "<img src='images/company.png'></a>";
		
		if($description == '') {
			$description = '<i>-'.__('Empty').'-</i>';
		}		
		$table->data[4][0] = "<b>".__('Description')."</b><br>$description<br>";
	}
	
	echo '<form method="post" id="contact_form">';
	print_table ($table);

	if (give_acl ($config["id_user"], $id_group, "VW")) {
	
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
	}

	echo "</form>";
	
} else {
	$where_group = "";	
	
	$search_text = (string) get_parameter ('search_text');
	$id_company = (int) get_parameter ('id_company');
	
	$where_clause = "WHERE 1=1 ";
	if ($search_text != "") {
		$where_clause .= " AND (fullname LIKE '%$search_text%' OR email LIKE '%$search_text%') ";
	}
	if ($id_company) {
		$where_clause .= sprintf (' AND id_company = %d', $id_company);
	}
	$params = "&search_text=$search_text&id_company=$id_company";

	$table->width = '550px';
	$table->class = 'search-table';
	$table->style = array ();
	$table->style[0] = 'font-weight: bold;';
	$table->data = array ();
	$table->data[0][0] = print_input_text ("search_text", $search_text, "", 15, 100, true, __('Search'));
	$table->data[0][1] = print_select (get_companies (), 'id_company', $id_company, '', 'All', 0, true, false, false, __('Company'));
	$table->data[0][2] = print_submit_button (__('Search'), "search_btn", false, 'class="sub search"', true);
	
	$table->data[0][2] .= "&nbsp;&nbsp;<a href='index.php?sec=customers&sec2=operation/contacts/contact_export&param=$params&render=1&raw_output=1&clean_output=1'><img title='".__("Export to CSV")."' src='images/binary.gif'></a>";

	echo '<form method="post">';
	print_table ($table);
	echo '</form>';

	$sql = "SELECT * FROM tcompany_contact $where_clause ORDER BY id_company, fullname";

	
	$contacts = get_db_all_rows_sql ($sql);

	$contacts = print_array_pagination ($contacts, "index.php?sec=customers&sec2=operation/contacts/contact_detail&params=$params");

	if ($contacts !== false) {
		unset ($table);
		$table->width = "90%";
		$table->class = "listing";
		$table->data = array ();
		$table->size = array ();
		$table->size[3] = '40px';
		$table->style = array ();
		// $table->style[] = 'font-weight: bold';
		$table->head = array ();
		$table->head[0] = __('Full name');
		$table->head[1] = __('Company');
		$table->head[2] = __('Email');
		if(give_acl ($config["id_user"], 0, "VM")) {
			$table->head[3] = __('Delete');
		}
		
		foreach ($contacts as $contact) {
			$data = array ();
			// Name
			$data[0] = "<a href='index.php?sec=customers&sec2=operation/contacts/contact_detail&id=".
				$contact['id']."'>".$contact['fullname']."</a>";
			$data[1] = "<a href='index.php?sec=customers&sec2=operation/companies/company_detail&id=".$contact['id_company']."'>".get_db_value ('name', 'tcompany', 'id', $contact['id_company'])."</a>";
			$data[2] = $contact['email'];
			if(give_acl ($config["id_user"], 0, "VM")) {
				$data[3] = '<a href="index.php?sec=customers&
							sec2=operation/contacts/contact_detail&
							delete_contact=1&id='.$contact['id'].'"
							onClick="if (!confirm(\''.__('Are you sure?').'\'))
							return false;">
							<img src="images/cross.png"></a>';
			}	
			array_push ($table->data, $data);
		}
		print_table ($table);
	}
}	
	if($manager) {
		echo '<form method="post" action="index.php?sec=customers&sec2=operation/contacts/contact_detail">';
		echo '<div class="button" style="width: '.$table->width.'">';
		print_submit_button (__('Create'), 'new_btn', false, 'class="sub next"');
		print_input_hidden ('new_contact', 1);
		echo '</div>';
		echo '</form>';
	}
}
?>
