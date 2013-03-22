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
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access company section");
	require ("general/noaccess.php");
	exit;
}

$manager = give_acl ($config["id_user"], 0, "VM");

$id = (int) get_parameter ('id');
$new_company = (bool) get_parameter ('new_company');
$create_company = (bool) get_parameter ('create_company');
$update_company = (bool) get_parameter ('update_company');
$delete_company = (bool) get_parameter ('delete_company');
$delete_invoice = get_parameter ('delete_invoice', "");

// CREATE
if ($create_company) {
	if (!$manager) {
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create a new company");
		require ("general/noaccess.php");
		exit;
	}
	
	$name = (string) get_parameter ('name');
	$address = (string) get_parameter ('address');
	$fiscal_id = (string) get_parameter ('fiscal_id');
	$comments = (string) get_parameter ('comments');
	$id_company_role = (int) get_parameter ('id_company_role');
	$id_group = (int) get_parameter ("id_group", 0);

	if (! give_acl ($config["id_user"], $id_group, "VW")) {
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create a company without privileges");
		require ("general/noaccess.php");
		exit;
	}

	$sql = sprintf ('INSERT INTO tcompany (name, address, comments, fiscal_id, id_company_role, id_grupo)
			 VALUES ("%s", "%s", "%s", "%s", %d, %d)',
			 $name, $address, $comments, $fiscal_id, $id_company_role, $id_group);

	$id = process_sql ($sql, 'insert_id');
	if ($id === false)
		echo "<h3 class='error'>".__('Could not be created')."</h3>";
	else {
		echo "<h3 class='suc'>".__('Successfully created')."</h3>";
		insert_event ("COMPANY CREATED", $id, 0, $name);
	}
	$id = 0;
}

// UPDATE
if ($update_company) {
	$id_group = (int) get_parameter ("id_group", 0);
	if (! give_acl ($config["id_user"], $id_group, "VW")) {
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to update a company");
		require ("general/noaccess.php");
		exit;
	}

	$name = (string) get_parameter ('name');
	$address = (string) get_parameter ('address');
	$fiscal_id = (string) get_parameter ('fiscal_id');
	$comments = (string) get_parameter ('comments');
	$id_company_role = (int) get_parameter ('id_company_role');

	$sql = sprintf ('UPDATE tcompany SET comments = "%s", name = "%s",
		address = "%s", fiscal_id = "%s", id_company_role = %d, id_grupo = "%s" WHERE id = %d',
		$comments, $name, $address,
		$fiscal_id, $id_company_role, $id_group, $id);

	$result = mysql_query ($sql);
	if ($result === false)
		echo "<h3 class='error'>".__('Could not be updated')."</h3>";
	else {
		echo "<h3 class='suc'>".__('Successfully updated')."</h3>";
		insert_event ("COMPANY", $id, 0, $name);
	}
	$id = 0;
}

// Delete company
if ($delete_company) { // if delete

	$id = (int) get_parameter ('id');
	$name = get_db_value ('name', 'tcompany', 'id', $id);
	$id_group = get_db_value ('id_grupo', 'tcompany', 'id', $id);

	if (! give_acl ($config["id_user"], $id_group, "VM")) {
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to delete a company without privileges");
		require ("general/noaccess.php");
		exit;
	}

	$sql= sprintf ('DELETE FROM tcompany WHERE id = %d', $id);
	process_sql ($sql);
	insert_event ("COMPANY DELETED", $id, 0, $name);
	echo "<h3 class='suc'>".__('Successfully deleted')."</h3>";
	$id = 0;
}

// Delete INVOICE
if ($delete_invoice == 1){
	$id_group = get_db_value ('id_grupo', 'tcompany', 'id', $id);

	if (! give_acl ($config["id_user"], $id_group, "VM")) {
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to delete a invoice without privileges");
		require ("general/noaccess.php");
		exit;
    }
	
	$id_invoice = get_parameter ("id_invoice", "");
	$invoice = get_db_row_sql ("SELECT * FROM tinvoice WHERE id = $id_invoice");
	
	// Do another security check, don't rely on information passed from URL
	
	if (($config["id_user"] = $invoice["id_user"]) OR ($id_task == $invoice["id_task"])){
			// Todo: Delete file from disk
			if ($invoice["id_attachment"] != ""){
				process_sql ("DELETE FROM tattachment WHERE id_attachment = ". $invoice["id_attachment"]);
			}
			process_sql ("DELETE FROM tinvoice WHERE id = $id_invoice");
	}
}

// View company details / Update
if ($id) {
	$writter = give_acl ($config["id_user"], $id_group, "VW");
	
	$op = get_parameter ("op", "");
	
	echo '<ul style="height: 30px;" class="ui-tabs-nav">';
	echo '<li class="ui-tabs">';
	echo '<a href="index.php?sec=customers&sec2=operation/companies/company_detail"><span>'.__("Search").'</span></a></li>';

	if ($op == "")
		echo '<li class="ui-tabs-selected">';
	else
		echo '<li class="ui-tabs">';
	echo '<a href="index.php?sec=customers&sec2=operation/companies/company_detail&id='.$id.'"><span>'.__("Company").'</span></a></li>';

	if ($op == "contacts")
		echo '<li class="ui-tabs-selected">';
	else
		echo '<li class="ui-tabs">';
	echo '<a href="index.php?sec=customers&sec2=operation/companies/company_detail&id='.$id.'&op=contacts"><span>'.__("Contacts").'</span></a></li>';

	if ($op == "contracts")
		echo '<li class="ui-tabs-selected">';
	else
		echo '<li class="ui-tabs">';
	echo '<a href="index.php?sec=customers&sec2=operation/companies/company_detail&id='.$id.'&op=contracts"><span>'.__("Contracts").'</span></a></li>';
	
	if ($op == "leads")
		echo '<li class="ui-tabs-selected">';
	else
		echo '<li class="ui-tabs">';
	echo '<a href="index.php?sec=customers&sec2=operation/companies/company_detail&id='.$id.'&op=leads"><span>'.__("Leads").'</span></a></li>';

	if ($op == "invoices")
		echo '<li class="ui-tabs-selected">';
	else
		echo '<li class="ui-tabs">';
	echo '<a href="index.php?sec=customers&sec2=operation/companies/company_detail&id='.$id.'&op=invoices"><span>'.__("Invoices").'</span></a></li>';

/*
	if ($op == "inventory")
		echo '<li class="ui-tabs-selected">';
	else
		echo '<li class="ui-tabs">';
	echo '<a href="index.php?sec=customers&sec2=operation/companies/company_detail&id='.$id.'&op=inventory"><span>'.__("Inventory").'</span></a></li>';

*/

	if ($op == "activities")
		echo '<li class="ui-tabs-selected">';
	else
		echo '<li class="ui-tabs">';
	echo '<a href="index.php?sec=customers&sec2=operation/companies/company_detail&id='.$id.'&op=activities"><span>'.__("Activities").'</span></a></li>';

	echo '</ul>';

	// Raya horizontal
	echo '<div id="ui-tabs-1" class="ui-tabs-panel" style="display: block;"></div>';

	$company = get_db_row ('tcompany', 'id', $id);
	$id_group = $company['id_grupo'];
	
	if (! give_acl ($config["id_user"], $id_group, "VR")) {
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access a company detail");
		require ("general/noaccess.php");
		exit;
	}
		
	// View/Edit company details
	if ($op == ""){
		echo '<form method="post" action="index.php?sec=customers&sec2=operation/companies/company_detail">';
		echo "<h2>".__('Company details')."</h2>";

		$name = $company['name'];
		$address = $company['address'];
		$comments = $company['comments'];
		$id_company_role = $company['id_company_role'];
		$fiscal_id = $company['fiscal_id'];
	
		$table->width = '90%';
		$table->class = "databox";
		$table->data = array ();
		$table->colspan = array ();
		$table->colspan[2][0] = 2;
		$table->colspan[3][0] = 2;
	
		if($writter) {
			$table->data[0][0] = print_input_text ('name', $name, '', 40, 100, true, __('Company name'));
			
			if (dame_admin($config["id_user"]))
				$table->data[0][0] .= "&nbsp;<a href='index.php?sec=customers&sec2=operation/companies/company_detail&id=$id&delete_company=1'><img src='images/cross.png'></a>";

			$table->data[0][1] = combo_groups_visible_for_me ($config["id_user"], "id_group", 0, "VR", $id_group, true, true);
			
			
			$table->data[1][0] = print_input_text ("fiscal_id", $fiscal_id, "", 10, 100, true, __('Fiscal ID'));
			$table->data[1][1] = print_select_from_sql ('SELECT id, name FROM tcompany_role ORDER BY name',
				'id_company_role', $id_company_role, '', __('Select'), 0, true, false, false, __('Company Role'));

			$table->data[2][0] = print_textarea ('address', 3, 1, $address, '', true, __('Address'));
			$table->data[3][0] = print_textarea ("comments", 10, 1, $comments, '', true, __('Comments'));
		}
		else {
			$table->data[0][0] = "<b>".__('Company name')."</b><br>$name<br>";
			
			$group_name = get_db_value('nombre','tgrupo','id_grupo',$id_group);
			$table->data[0][1] = "<b>".__('Group')."</b><br>$group_name<br>";
			if($fiscal_id == '') {
				$fiscal_id = '<i>-'.__('Empty').'-</i>';
			}		
			$table->data[1][0] = "<b>".__('Fiscal ID')."</b><br>$fiscal_id<br>";
			$role_name = get_db_value('name','tcompany_role','id',$id_company_role);
			if($role_name == '') {
				$role_name = '<i>-'.__('Empty').'-</i>';
			}
			$table->data[1][1] = "<b>".__('Company Role')."</b><br>$role_name<br>";
			if($address == '') {
				$address = '<i>-'.__('Empty').'-</i>';
			}
			$table->data[2][0] = "<b>".__('Address')."</b><br>$address<br>";
			if($comments == '') {
				$comments = '<i>-'.__('Empty').'-</i>';
			}
			$table->data[3][0] = "<b>".__('Comments')."</b><br>$comments<br>";
		}
		
		print_table ($table);
		
		if ($writter) {
			echo '<div class="button" style="width: '.$table->width.'">';
			print_submit_button (__('Update'), "update_btn", false, 'class="sub upd"', false);
			print_input_hidden ('update_company', 1);
			print_input_hidden ('id', $id);
			echo "</div>";
		}
			
		echo '</form>';	
	}

	// ACTIVITIES

	elseif ($op == "activities") {

		$op2 = get_parameter ("op2", "");
		if ($op2 == "add"){
			$datetime =  date ("Y-m-d H:i:s");
			$comments = get_parameter ("comments", "");
			$sql = sprintf ('INSERT INTO tcompany_activity (id_company, written_by, date, description) VALUES (%d, "%s", "%s", "%s")', $id, $config["id_user"], $datetime, $comments);
			process_sql ($sql, 'insert_id');
		}
		
		// ADD item form
		if($manager) {
			echo '<form method="post" action="index.php?sec=customers&sec2=operation/companies/company_detail&id='.$id.'&op=activities&op2=add">';
			echo "<h3>".__("Add activity")."</h3><p>";
			echo "<textarea name='comments' style='margin-left: 10px; width:94%; height: 50px'>";
			echo "</textarea>";

			echo '<div class="button" style="margin-left: 10px; width: 92%;">';
			print_submit_button (__('Add activity'), "create_btn", false, 'class="sub next"', false);
			echo "</div>";
			echo '</form>';
		}

		$sql = "SELECT * FROM tcompany_activity WHERE id_company = $id ORDER BY date DESC";

		$activities = get_db_all_rows_sql ($sql);
		$activities = print_array_pagination ($activities, "index.php?sec=customers&sec2=operation/companies/company_detail&id=$id&op=activities");

		if ($activities !== false) {	
			foreach ($activities as $activity) {
//				if (! give_acl ($config["id_user"], $company["id_group"], "IR"))
//					continue;


	echo "<div class='notetitle'>"; // titulo

	$timestamp = $activity["date"];
	$nota = $activity["description"];
	$id_usuario_nota = $activity["written_by"];

	$avatar = get_db_value ("avatar", "tusuario", "id_usuario", $id_usuario_nota);

	// Show data
	echo "<img src='images/avatars/".$avatar."_small.png'>&nbsp;";
	echo " <a href='index.php?sec=users&sec2=operation/users/user_edit&id=$id_usuario_nota'>";
	echo $id_usuario_nota;
	echo "</a>";
	echo " ".__("said on $timestamp");
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
		$contracts = get_contracts(false, "id_company = $id ORDER BY name");
		$contracts = print_array_pagination ($contracts, "index.php?sec=customers&sec2=operation/companies/company_detail&id=$id&op=contracts");

		if ($contracts !== false) {
		
			$table->width = "90%";
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
				if (! give_acl ($config["id_user"], $contract["id_group"], "VR"))
					continue;
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

			if($manager) {
				echo '<form method="post" action="index.php?sec=customers&sec2=operation/contracts/contract_detail&id_company='.$id.'">';
				echo '<div class="button" style="width: '.$table->width.'">';
				print_submit_button (__('Create'), 'new_btn', false, 'class="sub next"');
				print_input_hidden ('new_contract', 1);
				echo '</div>';
				echo '</form>';
			}
		}
	}

	// CONTACT LISTING

	elseif ($op == "contacts") {
		$name = get_db_value ('name', 'tcompany', 'id', $id);
		$id_group = get_db_sql ("SELECT id_grupo FROM tcompany WHERE id = $id");
		
		if (! give_acl ($config["id_user"], $id_group, "VR")) {
			audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access a contact detail");
			require ("general/noaccess.php");
			exit;
		}
		
		echo '<h3>'.__('Contact details for company') . " : ". $name . "</h3>";

		$table->class = 'listing';
		$table->width = '90%';
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
		
		if($manager) {
			echo '<form method="post" action="index.php?sec=customers&sec2=operation/contacts/contact_detail&id_company='.$id.'">';
			echo '<div class="button" style="width: '.$table->width.'">';
			print_submit_button (__('Create'), 'new_btn', false, 'class="sub next"');
			print_input_hidden ('new_contact', 1);
			echo '</div>';
			echo '</form>';
		}
	} // end of contact view

	// INVOICES LISTING

	elseif ($op == "invoices") {

		$id_group = get_db_sql ("SELECT id_grupo FROM tcompany WHERE id = $id");
		
		if (! give_acl ($config["id_user"], $id_group, "VR")) {
			audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access a invoice listing");
			require ("general/noaccess.php");
			exit;
		}

		$sql = "SELECT * FROM tinvoice WHERE id_company = $id ORDER BY invoice_create_date";
		$invoices = get_db_all_rows_sql ($sql);
		$invoices = print_array_pagination ($invoices, "index.php?sec=customers&sec2=operation/companies/company_detail&id=$id&op=invoices");

		if ($invoices !== false) {
		
			$table->width = "90%";
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
			$table->head[2] = __('Ammount');
			$table->head[3] = __('Creation');
			$table->head[4] = __('Payment');
			$table->head[5] = __('File');
			$table->head[6] = __('Upload by');
			if(give_acl ($config["id_user"], $id_group, "VM")) {
				$table->head[7] = __('Delete');
			}
			$counter = 0;
		
			$company = get_db_row ('tcompany', 'id', $id);
		
			foreach ($invoices as $invoice) {
				
				if (! give_acl ($config["id_user"], $company["id_group"], "IR"))
					continue;
				$data = array ();
			
				$data[0] = "<a href='index.php?sec=customers&sec2=operation/invoices/invoices&id_invoice="
					.$invoice["id"]."'>".$invoice["bill_id"]."</a>";
				$data[1] = "<a href='index.php?sec=customers&sec2=operation/invoices/invoices&id_invoice="
					.$invoice["id"]."'>".$invoice["description"]."</a>";
				$data[2] = format_numeric ($invoice["ammount"]);
				$data[3] = $invoice["invoice_create_date"];
				$data[4] = $invoice["invoice_payment_date"];
									
				$filename = get_db_sql ("SELECT filename FROM tattachment WHERE id_attachment = ". $invoice["id_attachment"]);
		
				$data[5] = 	"<a href='".$config["base_url"]."/attachment/".$invoice["id_attachment"]."_".$filename."'>$filename</a>";
				
				$data[6] = $invoice["id_user"];
				
				if(give_acl ($config["id_user"], $id_group, "VM")) {
				$data[7] = "<a href='index.php?sec=customers&sec2=operation/companies/company_detail&id=$id&op=invoices&delete_invoice=1&id_invoice=".$invoice["id"]."'><img src='images/cross.png'></a>";
				}
				
				array_push ($table->data, $data);
			}	
			print_table ($table);
			
			if($manager) {
				echo '<form method="post" action="index.php?sec=customers&sec2=operation/invoices/invoices&id_company='.$id.'">';
				echo '<div class="button" style="width: '.$table->width.'">';
				print_submit_button (__('Create'), 'new_btn', false, 'class="sub next"');
				print_input_hidden ('new_invoice', 1);
				echo '</div>';
				echo '</form>';
			}
			
		}
	}


	// Leads listing

	elseif ($op == "leads") {
		
		$sql = "SELECT * FROM tlead WHERE id_company = $id ORDER BY modification DESC";
		$invoices = get_db_all_rows_sql ($sql);
		$invoices = print_array_pagination ($invoices, "index.php?sec=customers&sec2=operation/companies/company_detail&id=$id&op=leads");

		if ($invoices !== false) {
		
			$table->width = "90%";
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
			
			if($manager) {
				echo '<form method="post" action="index.php?sec=customers&sec2=operation/leads/lead_detail">';
				echo '<div class="button" style="width: '.$table->width.'">';
				print_submit_button (__('Create'), 'new_btn', false, 'class="sub next"');
				print_input_hidden ('new', 1);
				echo '</div>';
				echo '</form>';
			}
			
		}
	}



} elseif ($new_company) {
	// New company
	echo "<h2>".__('Company management')."</h2>";
		
	if(!$manager) {
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create a contract");
		require ("general/noaccess.php");
		exit;
	}

	$name = "";
	$address = "";
	$comments = "";
	$id_company_role = "";
	$fiscal_id = "";

	$table->width = '90%';
	$table->class = "databox";
	$table->data = array ();
	$table->colspan = array ();
	$table->colspan[2][0] = 2;
	$table->colspan[3][0] = 2;
	
	$table->data[0][0] = print_input_text ('name', $name, '', 40, 100, true, __('Company name'));
	
	// Todo: Show only groups with access
	
	$table->data[0][1] = combo_groups_visible_for_me ($config["id_user"], "id_group", 0, "VR", $id_group, true, true);
	
	$table->data[1][0] = print_input_text ("fiscal_id", $fiscal_id, "", 10, 100, true, __('Fiscal ID'));
	$table->data[1][1] = print_select_from_sql ('SELECT id, name FROM tcompany_role ORDER BY name',
		'id_company_role', $id_company_role, '', __('Select'), 0, true, false, false, __('Company Role'));

	$table->data[2][0] = print_textarea ('address', 3, 1, $address, '', true, __('Address'));
	$table->data[3][0] = print_textarea ("comments", 10, 1, $comments, '', true, __('Comments'));
	
	echo '<form method="post" action="index.php?sec=customers&sec2=operation/companies/company_detail">';

	print_table ($table);
	echo '<div class="button" style="width: '.$table->width.'">';

	print_input_hidden ('create_company', 1);
	print_submit_button (__('Create'), "create_btn", false, 'class="sub next"', false);
	echo "</div>";
	echo '</form>';
 
} else {

	// Search // General Company listing

	echo "<h2>".__('Company management')."</h2>";
	echo "<br>";
	$search_text = (string) get_parameter ('search_text');	
	$search_role = (string) get_parameter ("search_role");
	
	//~ if (!get_admin_user($config["id_user"])){
		//~ $group_filter = get_user_groups_for_sql ($config["id_user"], "VR");
		//~ $where_clause = " 1=1 AND id_grupo IN $group_filter ";	
	//~ } else {
		//~ $where_clause = " 1=1";	
	//~ }
	
	
	if ($search_text != "") {
		$where_clause .= sprintf ('1=1 AND name LIKE "%%%s%%"', $search_text);
	}

	if ($search_role != 0){ 
		$where_clause .= sprintf ('1=1 AND id_company_role = %d', $search_role);
	}


	$table->width = '90%';
	$table->class = 'search-table';
	$table->style = array ();
	$table->style[0] = 'font-weight: bold;';
	$table->style[2] = 'font-weight: bold;';
	$table->data = array ();
	$table->data[0][0] = __('Search');
	$table->data[0][1] = print_input_text ("search_text", $search_text, "", 25, 100, true);
	$table->data[0][2] = __('Company Role');
	$table->data[0][3] = print_select_from_sql ('SELECT id, name FROM tcompany_role ORDER BY name',
		'search_role', $search_role, '', __('Select'), 0, true, false, false);
	$table->data[0][4] = print_submit_button (__('Search'), "search_btn", false, 'class="sub search"', true);
	
	echo '<form method="post" action="index.php?sec=customers&sec2=operation/companies/company_detail">';
	print_table ($table);
	echo '</form>';

	//~ $sql = "SELECT * FROM tcompany $where_clause ORDER BY name";
	//~ $companies = get_db_all_rows_sql ($sql);

	//~ echo $where_clause;

	if ($where_clause == "")
		$where_clause .= " 1=1 ORDER BY name";
	else
		$where_clause .= " ORDER BY name";

	$companies = get_companies(false, $where_clause);

	$companies = print_array_pagination ($companies, "index.php?sec=customers&sec2=operation/companies/company_detail&search_tect='$search_text&search_role=$search_role");

	if ($companies !== false) {
		$table->width = "90%";
		$table->class = "listing";
		$table->data = array ();
		$table->style = array ();
		$table->colspan = array ();
		$table->head[0] = __('Company');
		$table->head[1] = __('Role');
		$table->head[2] = __('Contracts');
		$table->head[3] = __('Contacts');
		$table->head[4] = __('Incidents');
		$table->head[5] = __('Invoices (totals)');
		$table->head[6] = __('Leads');

		if(give_acl ($config["id_user"], $id_group, "VM")) {
			$table->head[7] = __('Delete');
		}
		foreach ($companies as $company) {
			$data = array ();
			
			$data[0] = "<a href='index.php?sec=customers&sec2=operation/companies/company_detail&id=".
				$company["id"]."'>".$company["name"]."</a>";
			$data[1] = get_db_value ('name', 'tcompany_role', 'id', $company["id_company_role"]);
			$data[2] = '<a href="index.php?sec=customers&sec2=operation/companies/company_detail&op=contracts&id='.
				$company['id'].'"><img src="images/maintab.gif"></a>';
			$sum_contratos = get_db_sql ("SELECT COUNT(id) FROM tcontract WHERE id_company = ".$company["id"]);
			if ($sum_contratos > 0)
				$data[2] .= " ($sum_contratos)";
				
			$data[3] = '<a href="index.php?sec=customers&sec2=operation/companies/company_detail&op=contacts&id='.
				$company['id'].'"><img src="images/group.png"></a>';
			$sum_contactos = get_db_sql ("SELECT COUNT(id) FROM tcompany_contact WHERE id_company = ".$company["id"]);
			if ($sum_contactos > 0)
				$data[3] .= " ($sum_contactos)";
												
			$data[4] = '<form method="post" action="index.php?sec=incidents&sec2=operation/incidents/incident">';
			$data[4] .= print_input_hidden ('search_id_company', $company['id'], true);
			$data[4] .= print_input_image ('btn', 'images/bug.png', 1, '', true);
			$data[4] .= '</form>';
			$data[5] = "<a href='index.php?sec=customers&sec2=operation/companies/company_detail&id=".$company["id"]."&op=invoices'>";
			$data[5] .= format_numeric (company_invoice_total ($company["id"]));
			$data[5] .= "</a>";
			
			$data[6] .= "<a href=index.php?sec=customers&sec2=operation/leads/lead_detail&id_company=".$company["id"]."><img src='images/icon_lead.png'></a>";

			$sum_leads = get_db_sql ("SELECT COUNT(id) FROM tlead WHERE progress < 100 AND id_company = ".$company["id"]);
			if ($sum_leads > 0) {
				$data[6] .= " ($sum_leads) ";
				$data[6] .= get_db_sql ("SELECT SUM(estimated_sale) FROM tlead WHERE progress < 100 AND id_company = ".$company["id"]);
			}

			if (give_acl ($config["id_user"], $id_group, "VM")) {
				$data[7] ='<a href="index.php?sec=customers&
							sec2=operation/companies/company_detail&
							delete_company=1&id='.$company['id'].'"
							onClick="if (!confirm(\''.__('Are you sure?').'\'))
							return false;">
							<img src="images/cross.png"></a>';
			}
			array_push ($table->data, $data);
		}
		print_table ($table);
	}
	
	if($manager) {
		echo '<form method="post" action="index.php?sec=customers&sec2=operation/companies/company_detail">';
		echo '<div class="button" style="width: '.$table->width.'">';
		print_submit_button (__('Create'), 'new_btn', false, 'class="sub next"');
		print_input_hidden ('new_company', 1);
		echo '</div>';
		echo '</form>';
	}
}

?>
