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

enterprise_include('include/functions_crm.php');
include_once('include/functions_crm.php');

$id = (int) get_parameter ('id');

$read = enterprise_hook('crm_check_user_profile', array($config['id_user'], 'cr'));
$write = enterprise_hook('crm_check_user_profile', array($config['id_user'], 'cw'));
$manage = enterprise_hook('crm_check_user_profile', array($config['id_user'], 'cm'));
$enterprise = false;

if ($read !== ENTERPRISE_NOT_HOOK) {
	$enterprise = true;
	if (!$read) {
		include ("general/noaccess.php");
		exit;
	}
} else {
	$read = true;
	$write = true;
	$manage = true;
}

if($id != 0) {
	$id_company = get_db_value ('id_company', 'tcompany_contact', 'id', $id);
	
	$read_permission = enterprise_hook ('crm_check_acl_other', array ($config['id_user'], $id_company));
	$write_permission = enterprise_hook ('crm_check_acl_other', array ($config['id_user'], $id_company, true));
	$manage_permission = enterprise_hook ('crm_check_acl_other', array ($config['id_user'], $id_company, false, false, true));

	$enterprise = false;

	if ($read_permission === ENTERPRISE_NOT_HOOK) {
		
		$read_permission = true;
		$write_permission = true;
		$manage_permission = true;
		
	} else {
		
		$enterprise = true;
		
		if (!$read_permission) {
			include ("general/noaccess.php");
			exit;
		}
		
	}	
}

$new_contact = (bool) get_parameter ('new_contact');
$create_contact = (bool) get_parameter ('create_contact');
$update_contact = (bool) get_parameter ('update_contact');
$delete_contact = (bool) get_parameter ('delete_contact');
$get_contacts = (bool) get_parameter ('get_contacts');
$offset = get_parameter ('offset', 0);

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

	if (!$manage) {
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
	if (!$write_permission) {
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
	if (!$manage_permission) {
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

// FORM (Update / Create)
if ($id || $new_contact) {
	if ($new_contact) {
		if (! $manage) {
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
		if (!$read_permission) {
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
	
	$table->width = "99%";
	$table->class = "search-table-button";
	$table->data = array ();
	$table->colspan = array ();
	$table->colspan[0][0] = 4;
	$table->colspan[1][0] = 4;
	$table->colspan[4][0] = 4;
	
	if (($new_contact && $write) || ($id && $write_permission)) {
	
		$table->data[0][0] = print_input_text ("fullname", $fullname, "", 60, 100, true, __('Full name'));
		
		$table->data[1][0] = print_input_text ("email", $email, "", 35, 100, true, __('Email'));
		$table->data[2][0] = print_input_text ("phone", $phone, "", 15, 60, true, __('Phone number'));
		$table->data[2][1] = print_input_text ("mobile", $mobile, "", 15, 60, true, __('Mobile number'));
		$table->data[3][0] = print_input_text ('position', $position, '', 25, 50, true, __('Position'));

		$companies = crm_get_all_companies(true);

		if ($read && $enterprise) {
			$companies = crm_get_user_companies($config['id_user'], $companies);
		}
	
        	$select_comp = array();

        	foreach($companies as $key => $name) {
                	$select_comp[$key] = $name;
        	}
			
        	$table->data[3][1] = print_select ($select_comp, 'id_company', $id_company, '', __("None"), 0, true, false, false, __('Company'));

		//$table->data[3][1] =  print_select ($companies, 'id_company', $id_company, '', '', $nothing_value = '0', true, 0, false,  __('Company'));
			
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
	
	if ($id && $write_permission) {
		$button = print_submit_button (__('Update'), 'update_btn', false, 'class="sub upd"', true);
		$button .= print_input_hidden ('update_contact', 1, true);
		$button .= print_input_hidden ('id', $id, true);
	} else {
		if ($manage) {
			$button = print_submit_button (__('Create'), 'create_btn', false, 'class="sub create"', true);
			$button .= print_input_hidden ('create_contact', 1, true);
		}
	}
	
	$table->data['button'][0] = $button;
	$table->colspan['button'][0] = 2;
	
	echo '<form method="post" id="contact_form">';
	print_table ($table);
	echo "</form>";
	
} 

?>

<script type="text/javascript" src="include/js/jquery.validate.js"></script>
<script type="text/javascript" src="include/js/jquery.validation.functions.js"></script>

<script type="text/javascript">

// Form validation
trim_element_on_submit('#text-search_text');
trim_element_on_submit('#text-fullname');
trim_element_on_submit('#text-email');
validate_form("#contact_form");
var rules, messages;
// Rules: #text-fullname
rules = {
	required: true,
	remote: {
		url: "ajax.php",
        type: "POST",
        data: {
			page: "include/ajax/remote_validations",
			search_existing_contact: 1,
			contact_name: function() { return $('#text-fullname').val() },
			contact_id: "<?php echo $id?>"
        }
	}
};
messages = {
	required: "<?php echo __('Name required')?>",
	remote: "<?php echo __('This contact already exists')?>"
};
add_validate_form_element_rules('#text-fullname', rules, messages);
// Rules: #text-email
rules = {
	required: true,
	email: true,
	remote: {
		url: "ajax.php",
        type: "POST",
        data: {
			page: "include/ajax/remote_validations",
			search_existing_contact_email: 1,
			contact_email: function() { return $('#text-email').val() },
			contact_id: "<?php echo $id?>"
        }
	}
};
messages = {
	required: "<?php echo __('Email required')?>",
	email: "<?php echo __('Invalid email')?>",
	remote: "<?php echo __('This contact email already exists')?>"
};
add_validate_form_element_rules('#text-email', rules, messages);

</script>
