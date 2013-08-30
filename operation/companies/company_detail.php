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

require_once('include/functions_crm.php');
include_once('include/functions_user.php');
enterprise_include('include/functions_crm.php');

$id = (int) get_parameter ('id');

$read = enterprise_hook('crm_check_user_profile', array($config['id_user'], 'cr'));
$write = enterprise_hook('crm_check_user_profile', array($config['id_user'], 'cw'));
$manage = enterprise_hook('crm_check_user_profile', array($config['id_user'], 'cm'));
$enterprise = false;

if ($read === ENTERPRISE_NOT_HOOK) {
	$read = true;
	$write = true;
	$manage = true;
	$write_permission = true;
	$manage_permission = true;
	$read_permission = true;
} else {
	$enterprise = true;
	if (!$read) {
		include ("general/noaccess.php");
		exit;
	}
}

//ACL EXTERNAL USER (OPEN AND ENTERPRISE)
if (user_is_external($config['id_user'])) {
	$check_external_user = crm_check_acl_external_user($config['id_user'], $id);
	if ($check_external_user) {
		$read_permission = true;
	} else {
		$read_permission = false;
	}
	
	$write_permission = false;
	$manage_permission = false;
	$other_read_permission = false;
	$other_write_permission = false;
	$other_manage_permission = false;
	$invoice_permission = false;
}

if ($id > 0) {
	$other_read_permission = enterprise_hook('crm_check_acl_other', array($config['id_user'], $id));
	
	if ($other_read_permission === ENTERPRISE_NOT_HOOK) {
		$other_read_permission = true;
		$other_write_permission = true;
		$other_manage_permission = true;
		$invoice_permission = true;
	} else {
		$other_write_permission = enterprise_hook('crm_check_acl_other', array($config['id_user'], $id, true));
		$other_manage_permission = enterprise_hook('crm_check_acl_other', array($config['id_user'], $id, false, false, true));
		$invoice_permission = enterprise_hook('crm_check_acl_invoice', array($config['id_user'], $id));
	}
}

$op = (string) get_parameter ("op", "");
$new_company = (bool) get_parameter ('new_company');
$create_company = (bool) get_parameter ('create_company');
$update_company = (bool) get_parameter ('update_company');
$delete_company = (bool) get_parameter ('delete_company');
$delete_invoice = get_parameter ('delete_invoice', 0);
$lock_invoice = get_parameter ('lock_invoice', 0);
$offset = get_parameter ('offset', 0);

// Create OR Update
// ----------------

if (($create_company) OR ($update_company)) {
	
	$name = (string) get_parameter ('name');
	$address = (string) get_parameter ('address');
	$fiscal_id = (string) get_parameter ('fiscal_id', 0);
	$comments = (string) get_parameter ('comments');
	$id_company_role = (int) get_parameter ('id_company_role');
	$country = (string) get_parameter ("country");
	$website = (string) get_parameter ("website");
	$manager = (string) get_parameter ("manager");
	$id_parent = (int) get_parameter ("id_parent", 0);


	if ($create_company){
		
		if (!$manage && $enterprise) {
			include ("general/noaccess.php");
			exit;
		}

		if ($manage && $enterprise) {
			$check_acl = crm_check_acl_hierarchy($config['id_user'], $id);
			
			if ($check_acl) {
				$manage_permission = true;
			} else {
				include ("general/noaccess.php");
				exit;
			}
		}
		
		$sql = "INSERT INTO tcompany (name, address, comments, fiscal_id, id_company_role, website, country, manager, id_parent)
					 VALUES ('$name', '$address', '$comments',  $fiscal_id, $id_company_role, '$website', '$country', '$manager', $id_parent)";

		$id = process_sql ($sql, 'insert_id');

		if ($id === false)
			echo "<h3 class='error'>".__('Could not be created')."</h3>";
		else {
			echo "<h3 class='suc'>".__('Successfully created')."</h3>";
			insert_event ("COMPANY CREATED", $id, 0, $name);
		}
		$id = 0;
	} else {

		// Update company

		if (!$write && $enterprise) {
			include ("general/noaccess.php");
			exit;
		}

		if ($write && $enterprise) {
			$check_acl = crm_check_acl_hierarchy($config['id_user'], $id);
			
			if ($check_acl) {
				$write_permission = true;
			} else {
				include ("general/noaccess.php");
				exit;
			}
		}
		
		$sql = "SELECT `date` FROM tcompany_activity WHERE id_company=$id ORDER BY `date` DESC LIMIT 1";
		$last_update = process_sql ($sql);

		if ($last_update == false) {
			$last_update = '';
		}
		
		$sql = sprintf ('UPDATE tcompany SET manager="%s", id_parent = %d, comments = "%s", name = "%s",
		address = "%s", fiscal_id = "%s", id_company_role = %d, country = "%s", website = "%s", last_update = "%s" WHERE id = %d',
		$manager, $id_parent, $comments, $name, $address,
		$fiscal_id, $id_company_role, $country, $website, $last_update, $id);

		$result = mysql_query ($sql);
		if ($result === false)
			echo "<h3 class='error'>".__('Could not be updated')."</h3>";
		else {
			echo "<h3 class='suc'>".__('Successfully updated')."</h3>";
			insert_event ("COMPANY", $id, 0, $name);
		}
		$id = 0;
	}
}

// Delete company
// ----------------

if ($delete_company) { // if delete

	$id = (int) get_parameter ('id');
	$name = get_db_value ('name', 'tcompany', 'id', $id);

	if (!$manage && $enterprise) {
		include ("general/noaccess.php");
		exit;
	}

	if ($manage && $enterprise) {
		$check_acl = crm_check_acl_hierarchy($config['id_user'], $id);
		
		if ($check_acl) {
			$manage_permission = true;
		} else {
			include ("general/noaccess.php");
			exit;
		}
	}

	$sql= sprintf ('DELETE FROM tcompany WHERE id = %d', $id);

	process_sql ($sql);
	insert_event ("COMPANY DELETED", $id, 0, $name);
	echo "<h3 class='suc'>".__('Successfully deleted')."</h3>";
	$id = 0;

	// Delete contacts for that company
	$sql= sprintf ('DELETE FROM tcompany_contact WHERE id_company = %d', $id);
	process_sql ($sql);

	// Delete invoices for that company
	$sql= sprintf ('DELETE FROM tinvoice WHERE id_company = %d', $id);
	process_sql ($sql);	

}

// Delete INVOICE
// ----------------

if ($delete_invoice == 1){
	
	if (!$invoice_permission && $enterprise) {
		include ("general/noaccess.php");
		exit;
	}
	
	$id_invoice = get_parameter ("id_invoice", "");
	$invoice = get_db_row_sql ("SELECT * FROM tinvoice WHERE id = $id_invoice");
	
	// Do another security check, don't rely on information passed from URL
	
	if ($invoice["id"] && !crm_is_invoice_locked ($invoice["id"])) {
	//if (($config["id_user"] = $invoice["id_user"]) OR ($id_task == $invoice["id_task"])){ // TODO: Check this
		// Todo: Delete file from disk
		if ($invoice["id_attachment"] != ""){
			process_sql ("DELETE FROM tattachment WHERE id_attachment = ". $invoice["id_attachment"]);
		}
		process_sql ("DELETE FROM tinvoice WHERE id = $id_invoice");
	}
}

// Lock/Unlock INVOICE
// ----------------
if ($lock_invoice == 1){
	
	$id_invoice = get_parameter ("id_invoice", "");
	
	if ($id_invoice) {
		if (!crm_check_lock_permission ($config["id_user"], $id_invoice)) {
			include ("general/noaccess.php");
			exit;
		}
		
		crm_change_invoice_lock ($config["id_user"], $id_invoice);
		clean_cache_db();
	}
}

// Show tabs for a given company
// --------------------------------

if ($id) {

	$op = get_parameter ("op", "");
	
	echo '<ul style="height: 30px;" class="ui-tabs-nav">';
	if ($op == "files")
		echo '<li class="ui-tabs-selected">';
	else
		echo '<li class="ui-tabs">';
	echo '<a href="index.php?sec=customers&sec2=operation/companies/company_detail&id='.$id.'&op=files"><span>'.__("Files").'</span></a></li>';
	
	/*
		if ($op == "inventory")
			echo '<li class="ui-tabs-selected">';
		else
			echo '<li class="ui-tabs">';
		echo '<a href="index.php?sec=customers&sec2=operation/companies/company_detail&id='.$id.'&op=inventory"><span>'.__("Inventory").'</span></a></li>';

	*/
	
	if ($op == "invoices")
		echo '<li class="ui-tabs-selected">';
	else
		echo '<li class="ui-tabs">';
	echo '<a href="index.php?sec=customers&sec2=operation/companies/company_detail&id='.$id.'&op=invoices"><span>'.__("Invoices").'</span></a></li>';
	
	if ($op == "leads")
		echo '<li class="ui-tabs-selected">';
	else
		echo '<li class="ui-tabs">';
	echo '<a href="index.php?sec=customers&sec2=operation/companies/company_detail&id='.$id.'&op=leads"><span>'.__("Leads").'</span></a></li>';
	
	if ($op == "contracts")
		echo '<li class="ui-tabs-selected">';
	else
		echo '<li class="ui-tabs">';
	echo '<a href="index.php?sec=customers&sec2=operation/companies/company_detail&id='.$id.'&op=contracts"><span>'.__("Contracts").'</span></a></li>';
	
	if ($op == "contacts")
		echo '<li class="ui-tabs-selected">';
	else
		echo '<li class="ui-tabs">';
	echo '<a href="index.php?sec=customers&sec2=operation/companies/company_detail&id='.$id.'&op=contacts"><span>'.__("Contacts").'</span></a></li>';

	if ($op == "activities")
		echo '<li class="ui-tabs-selected">';
	else
		echo '<li class="ui-tabs">';
	echo '<a href="index.php?sec=customers&sec2=operation/companies/company_detail&id='.$id.'&op=activities"><span>'.__("Activity").'</span></a></li>';
	
	if ($op == "")
		echo '<li class="ui-tabs-selected">';
	else
		echo '<li class="ui-tabs">';
	echo '<a href="index.php?sec=customers&sec2=operation/companies/company_detail&id='.$id.'"><span>'.__("Company details").'</span></a></li>';
	
	echo '<li class="ui-tabs">';
	echo '<a href="index.php?sec=customers&sec2=operation/companies/company_detail"><span>'.__("Search").'</span></a></li>';

	echo '<li class="ui-tabs-title">';
	switch ($op) {
		case "activities":
			echo strtoupper(__('Activities'));
			break;
		case "files":
			echo strtoupper(__('Files'));
			break;
		case "invoices":
			echo strtoupper(__('Invoices'));
			break;
		case "leads":
			echo strtoupper(__('Leads'));
			break;
		case "contracts":
			echo strtoupper(__('Contracts'));
			break;
		case "contacts":
			echo strtoupper(__('Contacts'));
			break;
		default:
			echo strtoupper(__('Company details'));
	}
	echo '</li>';
		
	echo '</ul>';

	$company = get_db_row ('tcompany', 'id', $id);
	
	echo '<div class="under_tabs_info">' . sprintf(__('Company: %s'), $company['name']) . '</div>';
}

// EDIT / CREATE FORM

if ((($id > 0) AND ($op=="")) OR ($new_company == 1)) {

	$manage = enterprise_hook('crm_check_user_profile', array($config['id_user'], 'cm'));
	$manage_permission = true;
	
	if ($new_company == 1) {
		if ($manage !== ENTERPRISE_NOT_HOOK) {
			if ($manage) {
				$manage_permission = true;
			} else {

				$manage_permission = false;
			}
		} 	
	} else {
		$check_acl = enterprise_hook ('crm_check_acl_company', array ($config['id_user'], $id));
		
		if ($check_acl !== ENTERPRISE_NOT_HOOK) {
			if ($check_acl) {
				if ($read) {
					$read_permission = true;
				} else {
					$read_permission = false;
				}
				if ($manage) {
					$manage_permission = true;
				} else {
					$manage_permission = false;
				}
				if ($write) {
					$write_permission = true;
				} else {
					$write_permission = false;
				}
			} else {
				include ("general/noaccess.php");
				exit;
			}
		}
	}
	
	$disabled_write = false;
	
	if (($id > 0) AND ($op=="") AND $enterprise) {
		if (!$write_permission && $read_permission) {
			$disabled_write = true;
		}
	}
	
	echo '<form id="form-company_detail" method="post" action="index.php?sec=customers&sec2=operation/companies/company_detail">';
	
	if($new_company) {
		echo "<h1>".__('New company')."</h1>";
	}

	if ($op == "") { 
		$name = $company['name'];
		$address = $company['address'];
		$comments = $company['comments'];
		$country = $company['country'];
		$address = $company['address'];
		$website = $company["website"];
		$id_company_role = $company['id_company_role'];
		$fiscal_id = $company['fiscal_id'];
		$id_parent = $company["id_parent"];
		$manager = $company["manager"];
		$last_update = $company["last_update"];
	} else {
		$name = "";
		$address = "";
		$comments = "";
		$id_company_role = "";
		$fiscal_id = "";
		$country = "";
		$website = "";
		$manager = $config["id_user"];
		$id_parent = 0;
		$last_update = '';
	}

	// TODO: Make ACL check here. 
	// #1. Check if manager = current user -> OK
	// #2. If have CRM Manager, and current user is parent of this company -> OK
	// #3. If not, NO PERM to Write.

	$writter = 1;

	$table->width = '99%';
	$table->class = "search-table-button";
	$table->data = array ();
	$table->colspan = array ();
	$table->colspan[4][0] = 2;
	$table->colspan[5][0] = 2;

	if ($writter) {
		$table->data[0][0] = print_input_text ('name', $name, '', 40, 100, true, __('Company name'), $disabled_write);
		

		if ($id > 0 && $manage_permission) {
			$table->data[0][0] .= "&nbsp;<a href='index.php?sec=customers&sec2=operation/companies/company_detail&id=$id&delete_company=1'><img src='images/cross.png'></a>";
		}

	
		$table->data[0][1] = print_input_text_extended ('manager', $manager, 'text-user', '', 15, 30, $disabled_write, '',
		array(), true, '', __('Manager'))

	. print_help_tip (__("Type at least two characters to search"), true);

		// TODO: Replace this for a function to get visible compenies for this user
		$sql2 = "SELECT id, name FROM tcompany";

		$parent_name = $id_parent ? crm_get_company_name($id_parent) : __("None");
		
		$table->data[1][0] = print_input_text_extended ("parent_name", $parent_name, "text-parent_name", '', 20, 0, $disabled_write, "show_company_search('','','','','','')", "class='company_search'", true, false,  __('Parent company'));
		$table->data[1][0] .= print_input_hidden ('id_parent', $id_parent, true);
		
		$table->data[1][1] = print_input_text ("last_update", $last_update, "", 15, 100, true, __('Last update'), $disabled_write);
		
		$table->data[2][0] = print_input_text ("fiscal_id", $fiscal_id, "", 15, 100, true, __('Fiscal ID'));
		$table->data[2][1] = print_select_from_sql ('SELECT id, name FROM tcompany_role ORDER BY name',
			'id_company_role', $id_company_role, '', __('Select'), 0, true, false, false, __('Company Role'), $disabled_write);

		$table->data[3][0] = print_input_text ("website", $website, "", 30, 100, true, __('Website'), $disabled_write);
		$table->data[3][1] = print_input_text ("country", $country, "", 20, 100, true, __('Country'), $disabled_write);

		$table->data[4][0] = print_textarea ('address', 3, 1, $address, '', true, __('Address'));
		$table->data[5][0] = print_textarea ("comments", 10, 1, $comments, '', true, __('Comments'));
	}
	
	if ($id > 0) {
		if ($write_permission) {
			$button = print_submit_button (__('Update'), "update_btn", false, 'class="sub upd"', true);
			$button .= print_input_hidden ('update_company', 1, true);
			$button .= print_input_hidden ('id', $id, true);
		}
	} else {
		if ($manage_permission) {
			$button = print_submit_button (__('Create'), "create_btn", false, 'class="sub upd"', true);
			$button .= print_input_hidden ('create_company', 1, true);
		}
	}
	
	$table->data[6][0] = $button;
	$table->colspan[6][0] = 2;
	
	print_table ($table);
	echo '</form>';	
}

// Files
// ~~~~~~~~~
elseif ($op == "files") {
	include ("operation/companies/company_files.php");
}

// Activities
// ~~~~~~~~~
elseif ($op == "activities") {

	if (!$other_read_permission) {
		include ("general/noaccess.php");
		exit;
	}
	
	$op2 = get_parameter ("op2", "");
	if ($op2 == "add"){
		
		if (!$other_write_permission) {
			include ("general/noaccess.php");
			exit;
		}
	
		$datetime =  date ("Y-m-d H:i:s");
		$comments = get_parameter ("comments", "");
		
		if ($comments != "") {
			$sql = sprintf ('INSERT INTO tcompany_activity (id_company, written_by, date, description) VALUES (%d, "%s", "%s", "%s")', $id, $config["id_user"], $datetime, $comments);
			process_sql ($sql, 'insert_id');
		} else {
			echo "<h3 class='error'>".__('Error adding activity. Empty activity')."</h3>";
		}
	}
	
	// ADD item form
	// TODO: ACL Check

	if ($other_write_permission) {

		$company_name = get_db_sql ("SELECT name FROM tcompany WHERE id = $id");

		$table->width = "99%";
		$table->class = "search-table-button";
		$table->data = array ();
		$table->size = array ();
		$table->style = array ();
		
		$table->data[0][0] = "<h3>".__("Add activity")."</h3>";
		$table->data[1][0] = "<textarea name='comments' style='width:98%; height: 210px'></textarea>";
		$table->data[2][0] = print_submit_button (__('Add activity'), "create_btn", false, 'class="sub next"', true);
	
		echo '<form method="post" action="index.php?sec=customers&sec2=operation/companies/company_detail&id='.$id.'&op=activities&op2=add">';
		print_table($table);
		echo '</form>';
	}

	$contacts = crm_get_all_contacts (sprintf(" WHERE id_company = %d", $id));

	$sql = "SELECT * FROM tcompany_contact CC, tcontact_activity C WHERE CC.id_company = $id AND CC.id = C.id_contact ORDER BY C.creation DESC";

	$act_contacts = get_db_all_rows_sql($sql);

	$sql = "SELECT * FROM tcompany_activity WHERE id_company = $id ORDER BY date DESC";

	$activities = get_db_all_rows_sql ($sql);

	$activities = array_merge($activities, $act_contacts);
	
	$activities = print_array_pagination ($activities, "index.php?sec=customers&sec2=operation/companies/company_detail&id=$id&op=activities");

	if ($activities !== false) {	

		$aux_activities = array();

		foreach ($activities as $key => $act) {

			if (isset($act["date"])) {
				$aux_activities[$key] = $act["date"];
			} else {
				$aux_activities[$key] = $act["creation"];
			}
		}

		arsort($aux_activities);

		foreach ($aux_activities as $key=>$date) {

			$activity = $activities[$key];

			echo "<div class='notetitle'>"; // titulo

			if (isset($activity["id_contact"])) {
				$type = "group";
				$title = __("Contact");
				$timestamp = $activity["creation"];
				$contact_name = "&nbsp;&nbsp;".__("on contact").":&nbsp;".get_db_value("fullname", "tcompany_contact", "id", $activity["id_contact"]);
			} else {
				$type = "company";
				$title = __("Company");
				$timestamp = $activity["date"];
				$contact_name = "";
			}

			$nota = $activity["description"];
			$id_usuario_nota = $activity["written_by"];

			$avatar = get_db_value ("avatar", "tusuario", "id_usuario", $id_usuario_nota);

			// Show data
			echo '<img src="images/'.$type.'.png"/ width="22px" title="'.$title.'">';
			echo "<img src='images/avatars/".$avatar.".png' class='avatar_small'>&nbsp;";
			echo " <a href='index.php?sec=users&sec2=operation/users/user_edit&id=$id_usuario_nota'>";
			echo $id_usuario_nota;
			echo "</a>";
			echo " ".__("said on $timestamp");
			echo $contact_name;
			echo "</div>";

			// Body
			echo "<div class='notebody'>";
			echo clean_output_breaks($nota);
			echo "</div>";
		}
	}
}

// CONTRACT LISTING

elseif ($op == "contracts") {
	
	if (!$other_read_permission) {
		include ("general/noaccess.php");
		exit;
	}
	
	$contracts = get_contracts(false, "id_company = $id ORDER BY name");
	
	if ($other_read_permission && $enterprise) {
		$contracts = crm_get_user_contracts($config['id_user'], $contracts);
	}
	
	$contracts = print_array_pagination ($contracts, "index.php?sec=customers&sec2=operation/companies/company_detail&id=$id&op=contracts");

	if ($contracts !== false) {
	
		$table->width = "99%";
		$table->class = "listing";
		$table->cellspacing = 0;
		$table->cellpadding = 0;
		$table->tablealign="left";
		$table->data = array ();
		$table->size = array ();
		$table->style = array ();
		$table->style[0] = 'font-weight: bold';
		$table->colspan = array ();
		$table->head[0] = __('Name');
		$table->head[1] = __('Contract number');
		$table->head[2] = __('Company');
		$table->head[3] = __('SLA');
		$table->head[4] = __('Group');
		$table->head[5] = __('Begin');
		$table->head[6] = __('End');
		$counter = 0;
	
		foreach ($contracts as $contract) {
			
			$data = array ();
		
			$data[0] = "<a href='index.php?sec=customers&sec2=operation/contracts/contract_detail&id="
				.$contract["id"]."'>".$contract["name"]."</a>";
			$data[1] = $contract["contract_number"];
			$data[2] = get_db_value ('name', 'tcompany', 'id', $contract["id_company"]);
			$data[3] = get_db_value ('name', 'tsla', 'id', $contract["id_sla"]);
			$data[4] = get_db_value ('nombre', 'tgrupo', 'id_grupo', $contract["id_group"]);
			$data[5] = $contract["date_begin"];
			$data[6] = $contract["date_end"] != '0000-00-00' ? $contract["date_end"] : "-";
		
			array_push ($table->data, $data);
		}	
		print_table ($table);


		if ($other_manage_permission) {
			
			echo '<form method="post" action="index.php?sec=customers&sec2=operation/contracts/contract_detail&id_company='.$id.'">';
			echo '<div style="width: '.$table->width.'; text-align: right;">';
			print_submit_button (__('Create'), 'new_btn', false, 'class="sub next"');
			print_input_hidden ('new_contract', 1);
			echo '</div>';
			echo '</form>';
		}
	}
}

// CONTACT LISTING

elseif ($op == "contacts") {
	
	if (!$other_read_permission) {
		include ("general/noaccess.php");
		exit;
	}
	
	$name = get_db_value ('name', 'tcompany', 'id', $id);
		
	$table->class = 'listing';
	$table->width = '99%';
	$table->head = array ();
	$table->head[0] = __('Contact');
	$table->head[1] = __('Email');
	$table->head[2] = __('Position');
	$table->head[3] = __('Details');
	
	$table->size = array ();
	$table->data = array ();
	
	$contacts = get_db_all_rows_sql ("SELECT * FROM tcompany_contact WHERE id_company = $id");

	if ($contacts === false)
		$contacts = array ();
		
	if ($other_read_permission && $enterprise) {
		$contacts = crm_get_user_contacts($config['id_user'], $contacts);
	}

	foreach ($contacts as $contact) {
		$data = array ();
		$data[0] = '<a href="index.php?sec=customers&sec2=operation/contacts/contact_detail&id='.$contact['id'].'">'.$contact['fullname']."</a>";
		$data[1] = $contact['email'];
		$details = '';
		if ($contact['phone'] != '')
			$details .= '<strong>'.__('Phone number').'</strong>: '.$contact['phone'].'<br />';
		if ($contact['mobile'] != '')
			$details .= '<strong>'.__('Mobile phone').'</strong>: '.$contact['mobile'].'<br />';
		$data[2] = $contact['position'];
		$data[3] = print_help_tip ($details, true, 'tip_view');
		array_push ($table->data, $data);			
	}
	print_table ($table);
	
	if ($other_manage_permission) {
		echo '<form method="post" action="index.php?sec=customers&sec2=operation/contacts/contact_detail&id_company='.$id.'">';
		echo '<div style="width: '.$table->width.'; text-align: right;">';
		print_submit_button (__('Create'), 'new_btn', false, 'class="sub next"');
		print_input_hidden ('new_contact', 1);
		echo '</div>';
		echo '</form>';
	}
} // end of contact view

// INVOICES LISTING

elseif ($op == "invoices") {

	if (!$invoice_permission) {
		include ("general/noaccess.php");
		exit;
	}
	
	$new_invoice = get_parameter("new_invoice", 0);
	$operation_invoices = get_parameter ("operation_invoices", "");
	$view_invoice = get_parameter("view_invoice", 0);
	
	$company_name = get_db_sql ("SELECT name FROM tcompany WHERE id = $id");

	if (($operation_invoices != "") OR ($new_invoice != 0) OR ($view_invoice != 0 ) ){
				
		$id_invoice = get_parameter ("id_invoice", -1);
		if ($id_invoice) {
			$is_locked = crm_is_invoice_locked ($id_invoice);
			$lock_permission = crm_check_lock_permission ($config["id_user"], $id_invoice);
		}
		
		if ($new_invoice == 0 && $is_locked) {
			
			$locked_id_user = crm_get_invoice_locked_id_user ($id_invoice);
			// Show an only readable invoice
			echo "<h3>". __("Invoice #"). $id_invoice;
			echo ' ('.__('Locked by ').$locked_id_user.')';
			echo ' <a href="index.php?sec=users&amp;sec2=operation/invoices/invoice_view
					&amp;id_invoice='.$id_invoice.'&amp;clean_output=1&amp;pdf_output=1">
					<img src="images/page_white_acrobat.png" title="'.__('Export to PDF').'"></a>';
			if ($lock_permission) {
				
				if ($is_locked) {
					$lock_image = 'lock.png';
					$title = __('Unlock');
				} else {
					$lock_image = 'lock_open.png';
					$title = __('Lock');
				}
				echo ' <a href="?sec=customers&sec2=operation/companies/company_detail
					&lock_invoice=1&id='.$id.'&op=invoices&id_invoice='.$id_invoice.'" 
					onClick="if (!confirm(\''.__('Are you sure?').'\')) return false;">
					<img src="images/'.$lock_image.'" title="'.$title.'"></a>';
			}
			echo "</h3>";
			include ("operation/invoices/invoice_view.php");
		}
		else {
			// Show edit/insert invoice
			include ("operation/invoices/invoices.php");
		}
	}

	// Operation_invoice changes inside the previous include

	if (($operation_invoices == "") AND ($new_invoice == 0) AND ($view_invoice == 0)) {
	
		$sql = "SELECT * FROM tinvoice WHERE id_company = $id ORDER BY invoice_create_date";
		$invoices = get_db_all_rows_sql ($sql);
		
		if ($invoice_permission && $enterprise) {
			$invoices = crm_get_user_invoices($config['id_user'], $invoices);
		}
		
		$invoices = print_array_pagination ($invoices, "index.php?sec=customers&sec2=operation/companies/company_detail&id=$id&op=invoices");
		
		if ($invoices !== false) {
		
			$table->width = "98%";
			$table->class = "listing";
			$table->cellspacing = 0;
			$table->cellpadding = 0;
			$table->tablealign="left";
			$table->data = array ();
			$table->size = array ();
			$table->style = array ();
			$table->style[0] = 'font-weight: bold';
			$table->colspan = array ();
			$table->head[0] = __('ID');
			$table->head[1] = __('Description');
			$table->head[2] = __('Amount');
			$table->head[3] = __('Status');
			$table->head[4] = __('Creation');
			$table->head[5] = __('Payment');
			//$table->head[] = __('File');
			//$table->head[] = __('Upload by');
			$table->head[6] = __('Options');
			
			$counter = 0;
		
			$company = get_db_row ('tcompany', 'id', $id);
		
			foreach ($invoices as $invoice) {
				
				$lock_permission = crm_check_lock_permission ($config["id_user"], $invoice["id"]);
				$is_locked = crm_is_invoice_locked ($invoice["id"]);
				$locked_id_user = false;
				if ($is_locked) {
					$locked_id_user = crm_get_invoice_locked_id_user ($invoice["id"]);
				}
				
				$data = array ();
			
				$url = "index.php?sec=customers&sec2=operation/companies/company_detail&view_invoice=1&id=".$id."&op=invoices&id_invoice=". $invoice["id"];

				$data[0] = "<a href='$url'>".$invoice["bill_id"]."</a>";
				$data[1] = "<a href='$url'>".$invoice["description"]."</a>";
				$data[2] = get_invoice_amount ($invoice["id"]) ." ". strtoupper ($invoice["currency"]);
				$data[3] = __($invoice["status"]);
				$data[4] = "<span style='font-size: 10px'>".$invoice["invoice_create_date"]. "</span>";
				if ($invoice["status"] == "paid") {
					$data[5] = "<span style='font-size: 10px'>". $invoice["invoice_payment_date"]. "</span>";
				} else {
					$data[5] = __("Not paid");
				}
				
				//$filename = get_db_sql ("SELECT filename FROM tattachment WHERE id_attachment = ". $invoice["id_attachment"]);
				//$data[] = "<a href='".$config["base_url"]."/attachment/".$invoice["id_attachment"]."_".$filename."'>$filename</a>";
				//$data[] = $invoice["id_user"];
				
				$data[6] = '<a href="index.php?sec=users&amp;sec2=operation/invoices/invoice_view
					&amp;id_invoice='.$invoice["id"].'&amp;clean_output=1&amp;pdf_output=1">
					<img src="images/page_white_acrobat.png" title="'.__('Export to PDF').'"></a>';
				if ($lock_permission) {
					
					if ($is_locked) {
						$lock_image = 'lock.png';
						$title = __('Unlock');
					} else {
						$lock_image = 'lock_open.png';
						$title = __('Lock');
					}
					$data[6] .= ' <a href="?sec=customers&sec2=operation/companies/company_detail
						&lock_invoice=1&id='.$invoice["id_company"].'&op=invoices&id_invoice='.$invoice["id"].'" 
						onClick="if (!confirm(\''.__('Are you sure?').'\')) return false;">
						<img src="images/'.$lock_image.'" title="'.$title.'"></a>';
				}
				if (!$is_locked) {
					$data[6] .= ' <a href="?sec=customers&sec2=operation/companies/company_detail
						&delete_invoice=1&id='.$id.'&op=invoices&id_invoice='.$invoice["id"].'
						&offset='.$offset.'" onClick="if (!confirm(\''.__('Are you sure?').'\'))
						return false;"><img src="images/cross.png" title="'.__('Delete').'"></a>';
				} else {
					if ($locked_id_user) {
						$data[6] .= ' <img src="images/administrator_lock.png" width="18" height="18"
						title="'.__('Locked by '.$locked_id_user).'">';
					}
				}
				
				array_push ($table->data, $data);
			}	
			print_table ($table);
			

			if ($invoice_permission) {
		
				echo '<form method="post" action="index.php?sec=customers&sec2=operation/companies/company_detail&id='.$id.'&op=invoices">';
				echo '<div class="button" style="width: '.$table->width.'">';
				print_submit_button (__('Create'), 'new_btn', false, 'class="sub next"');
				print_input_hidden ('new_invoice', 1);
				echo '</div>';
				echo '</form>';
			}
		}
	} 
}

// Leads listing

elseif ($op == "leads") {
	
	if (!$other_read_permission) {
		include ("general/noaccess.php");
		exit;
	}
	
	$sql = "SELECT * FROM tlead WHERE id_company = $id and progress < 100 ORDER BY estimated_sale DESC,  modification DESC";
	$invoices = get_db_all_rows_sql ($sql);
	
	if ($other_read_permission && $enterprise) {
		$invoices = crm_get_user_leads($config['id_user'], $invoices);
	}
		
	$invoices = print_array_pagination ($invoices, "index.php?sec=customers&sec2=operation/companies/company_detail&id=$id&op=leads");
	
	$company_name = get_db_sql ("SELECT name FROM tcompany WHERE id = $id");
	
	if ($invoices !== false) {
	
		$table->width = "99%";
		$table->class = "listing";
		$table->cellspacing = 0;
		$table->cellpadding = 0;
		$table->tablealign="left";
		$table->data = array ();
		$table->size = array ();
		$table->style = array ();
		$table->style[0] = 'font-weight: bold';
		$table->colspan = array ();
		
		$table->head[0] = __('Fullname');
		$table->head[1] = __('Owner');
		$table->head[2] = __('Company');
		$table->head[3] = __('Updated at');
		$table->head[4] = __('Country');
		$table->head[5] = __('Progress');
		$table->head[6] = __('Estimated sale');
		$counter = 0;
			
		foreach ($invoices as $invoice) {	
			$data = array ();
		
			$data[0] = "<a href='index.php?sec=customers&sec2=operation/leads/lead_detail&id="
				.$invoice["id"]."'>".$invoice["fullname"]."</a>";

			$data[1] = $invoice["owner"];
			$data[2] = $invoice["company"];
			$data[3] = $invoice["modification"];
			$data[4] = $invoice["country"];
			$data[5] = $invoice["progress"];		
			$data[6] = format_numeric ($invoice["estimated_sale"]);
			
			array_push ($table->data, $data);
		}	
		print_table ($table);
		
		if ($other_manage_permission) {
			echo '<form method="post" action="index.php?sec=customers&sec2=operation/leads/lead_detail">';
			echo '<div style="width: '.$table->width.'; text-align: right;">';
			print_submit_button (__('Create'), 'new_btn', false, 'class="sub next"');
			print_input_hidden ('new', 1);
			echo '</div>';
			echo '</form>';
		}
		
	}
}

// No id passed as parameter
	
if ((!$id) AND ($new_company == 0)){
	
	if (!$read) {
		include ("general/noaccess.php");
		exit;
	}

	// Search // General Company listing
	echo "<div id='inventory-search-content'>";
	echo "<h1>".__('Company management');
	echo "<div id='button-bar-title'>";
	echo "<ul>";
	echo "<li>";
	echo "<a id='company_stats_form_submit' href='javascript: changeAction();'>".print_image ("images/chart_bar_dark.png", true, array("title" => __("Search statistics")))."</a>";
	echo "</li>";
	echo "</ul>";
	echo "</div>";
	echo "</h1>";

	$search_text = (string) get_parameter ("search_text");	
	$search_role = (int) get_parameter ("search_role");
	$search_country = (string) get_parameter ("search_country");
	$search_manager = (string) get_parameter ("search_manager");
	$search_parent = (int) get_parameter ("search_parent");
	$search_date_begin = (string) get_parameter("search_date_begin");
	$search_date_end = (string) get_parameter("search_date_end");
	
	$date = false;
	
	if ($search_text != "") {
		$where_clause .= sprintf (' AND ( name LIKE "%%%s%%" OR country LIKE "%%%s%%")  ', $search_text, $search_text);
	}

	if ($search_role != 0){ 
		$where_clause .= sprintf (' AND id_company_role = %d', $search_role);
	}

	if ($search_country != ""){ 
		$where_clause .= sprintf (' AND country LIKE "%%%s%%" ', $search_country);
	}

	if ($search_manager != ""){ 
		$where_clause .= sprintf (' AND manager = "%s" ', $search_manager);
	}
	
	if ($search_parent != 0){ 
		$where_clause .= sprintf (' AND id_parent = %d ', $search_parent);
	}
	
	if ($search_date_begin != "") { 
		$where_clause .= " AND `date` >= $search_date_begin";
		$date = true;
	}

	if ($search_date_end != ""){ 
		$where_clause .= " AND `date` <= $search_date_end";
		$date = true;
	}

	$params = "&search_manager=$search_manager&search_text=$search_text&search_role=$search_role&search_country=$search_country&search_parent=$search_parent&search_date_begin=$search_date_begin&search_date_end=$search_date_end";

	$table->width = '99%';
	$table->class = 'search-table-button';
	$table->style = array ();
	$table->data = array ();
	$table->data[0][0] = print_input_text ("search_text", $search_text, "", 15, 100, true, __('Search'));
	$table->data[0][1] = print_select_from_sql ('SELECT id, name FROM tcompany_role ORDER BY name',
		'search_role', $search_role, '', __('Select'), 0, true, false, false, __('Company Role'));
	$table->data[0][2] = print_input_text ("search_country", $search_country, "", 10, 100, true, __('Country'));
	$table->data[0][3] = print_input_text_extended ('search_manager', $search_manager, 'text-user', '', 15, 30, false, '',	array(), true, '', __('Manager'))	. print_help_tip (__("Type at least two characters to search"), true);

	$table->data[1][0] = print_select_from_sql ('SELECT id, name FROM tcompany ORDER BY name',
		'search_parent', $search_parent, '', __('Select'), 0, true, false, false, __('Parent'));
	$table->data[1][1] = print_input_text ('search_date_begin', $search_date_begin, '', 15, 20, true, __('Date from'));
	$table->data[1][2] = print_input_text ('search_date_end', $search_date_end, '', 15, 20, true, __('Date to'));
		
	$buttons = print_submit_button (__('Search'), "search_btn", false, 'class="sub search"', true);
	$buttons .= print_button(__('Export to CSV'), '', false, 'window.open(\'' . 'include/export_csv.php?export_csv_companies=1&where_clause=' . str_replace('"', "'", $where_clause) . '&date=' . $date . '\')', 'class="sub csv"', true);
		
	$table->data[2][0] = $buttons;
	$table->colspan[2][0] = 4;

	echo '<form method="post" id="company_stats_form" action="index.php?sec=customers&sec2=operation/companies/company_detail">';
	print_table ($table);
	echo '</form>';

	$companies = crm_get_companies_list($where_clause, $date);
	
	if ($read && $enterprise) {
		$companies = crm_get_user_companies($config['id_user'], $companies);
	}
	
	$companies = print_array_pagination ($companies, "index.php?sec=customers&sec2=operation/companies/company_detail$params", $offset);

	if ($companies !== false) {

		$table->width = "99%";
		$table->class = "listing";
		$table->data = array ();
		$table->style = array ();
		$table->colspan = array ();
		$table->head[0] = __('Company');
		$table->head[1] = __('Role');
		$table->head[2] = __('Contracts');
		$table->head[3] = __('Leads');
		$table->head[4] = __('Manager');
		$table->head[5] = __('Country');
		$table->head[6] = __('Last activity');
		$table->head[7] = __('Delete');
		
		foreach ($companies as $company) {
			
			$check_acl = enterprise_hook ('crm_check_acl_hierarchy', array ($config['id_user'], $company['id']));
	
			if ($check_acl !== ENTERPRISE_NOT_HOOK) {
		
				if ($check_acl) {
					if ($read) {
						$read_permission = true;
					} else {
						$read_permission = false;
					}
					if ($manage) {
						$manage_permission = true;
					} else {
						$manage_permission = false;
					}
					if ($write) {
						$write_permission = true;
					} else {
						$write_permission = false;
					}
				}
		
			} else {
				$read_permission = true;
				$write_permission = true;
				$manage_permission = true;
			}
	
			$data = array ();
			
			$data[0] = "<a href='index.php?sec=customers&sec2=operation/companies/company_detail&id=".
				$company["id"]."'>".$company["name"]."</a>";
			$data[1] = get_db_value ('name', 'tcompany_role', 'id', $company["id_company_role"]);
			$data[2] = '<a href="index.php?sec=customers&sec2=operation/companies/company_detail&op=contracts&id='.
				$company['id'].'"><img src="images/invoice.png"></a>';
			$sum_contratos = get_db_sql ("SELECT COUNT(id) FROM tcontract WHERE id_company = ".$company["id"]);
			if ($sum_contratos > 0)
				$data[2] .= " ($sum_contratos)";
				
											
			$data[3] = "<a href=index.php?sec=customers&sec2=operation/leads/lead_detail&id_company=".$company["id"]."><img src='images/icon_lead.png'></a>";

			$sum_leads = get_db_sql ("SELECT COUNT(id) FROM tlead WHERE progress < 100 AND id_company = ".$company["id"]);
			if ($sum_leads > 0) {
				$data[3] .= " ($sum_leads) ";
				$data[3] .= get_db_sql ("SELECT SUM(estimated_sale) FROM tlead WHERE progress < 100 AND id_company = ".$company["id"]);
			}

			$data[4] = $company["manager"];
			$data[5] = $company["country"];
			
			// get last activity date for this company record
			$last_activity = get_db_sql ("SELECT date FROM tcompany_activity WHERE id_company = ". $company["id"]);

			$data[6] = human_time_comparation ($last_activity);

			if ($manage_permission) {
				$data[7] ='<a href="index.php?sec=customers&
								sec2=operation/companies/company_detail'.$params.'&
								delete_company=1&id='.$company['id'].'&offset='.$offset.'"
								onClick="if (!confirm(\''.__('Are you sure?').'\'))
								return false;">
								<img src="images/cross.png"></a>';
			} else {
				$data[7] = '';
			}
			
			array_push ($table->data, $data);
		}
		print_table ($table);
	}
	
}

echo "<div class= 'dialog ui-dialog-content' id='company_search_window'></div>";

?>

<script type="text/javascript" src="include/js/jquery.ui.autocomplete.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
<script type="text/javascript" src="include/js/integria_crm.js"></script>
<script type="text/javascript" src="include/js/integria_date.js"></script>

<script type="text/javascript" >
	
add_ranged_datepicker ("#text-search_date_begin", "#text-search_date_end", null);
	
$(document).ready (function () {
	$("#textarea-description").TextAreaResizer ();
	
	var idUser = "<?php echo $config['id_user'] ?>";
	
	bindAutocomplete ('#text-user', idUser);
	
	// Form validation
	trim_element_on_submit('#text-search_text');
	trim_element_on_submit('#text-name');
	trim_element_on_submit('#text-fiscal_id');
	validate_form("#form-company_detail");
	var rules, messages;
	// Rules: #text-name
	rules = {
		required: true,
		remote: {
			url: "ajax.php",
			type: "POST",
			data: {
				page: "include/ajax/remote_validations",
				search_existing_company: 1,
				company_name: function() { return $('#text-name').val() },
				company_id: "<?php echo $id?>"
			}
		}
	};
	messages = {
		required: "<?php echo __('Name required')?>",
		remote: "<?php echo __('This company already exists')?>"
	};
	add_validate_form_element_rules('#text-name', rules, messages);
	// Rules: #text-fiscal_id
	rules = {
		//required: true,
		remote: {
			url: "ajax.php",
			type: "POST",
			data: {
				page: "include/ajax/remote_validations",
				search_existing_fiscal_id: 1,
				fiscal_id: function() { return $('#text-fiscal_id').val() },
				company_id: "<?php echo $id?>"
			}
		}
	};
	messages = {
		//required: "<?php echo __('Fiscal ID required')?>",
		remote: "<?php echo __('This fiscal id already exists')?>"
	};
	add_validate_form_element_rules('#text-fiscal_id', rules, messages);
	
});

function changeAction() {
	
	var f = document.forms.company_stats_form;

	f.action = "index.php?sec=customers&sec2=operation/companies/company_statistics";
	$("#company_stats_form").submit();
}

</script>
