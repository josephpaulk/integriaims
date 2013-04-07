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

$id = (int) get_parameter ('id');

// TODO: ACL CHECK !!. Check HERE if current user have access to this company.

$new_company = (bool) get_parameter ('new_company');
$create_company = (bool) get_parameter ('create_company');
$update_company = (bool) get_parameter ('update_company');
$delete_company = (bool) get_parameter ('delete_company');
$delete_invoice = get_parameter ('delete_invoice', "");

// Create OR Update
// ----------------

if (($create_company) OR ($update_company)) {

	// TODO: ACL CHECK !!
	
	$name = (string) get_parameter ('name');
	$address = (string) get_parameter ('address');
	$fiscal_id = (string) get_parameter ('fiscal_id');
	$comments = (string) get_parameter ('comments');
	$id_company_role = (int) get_parameter ('id_company_role');
	$id_group = (int) get_parameter ("id_group", 0);
	$country = (string) get_parameter ("country");
	$website = (string) get_parameter ("website");
	$manager = (string) get_parameter ("manager");
	$id_parent = (int) get_parameter ("id_parent", 0);

	if ($create_company){
		$sql = sprintf ('INSERT INTO tcompany (name, address, comments, fiscal_id, id_company_role, id_grupo, website, country, manager, id_parent)
			 VALUES ("%s", "%s", "%s", "%s", %d, %d, "%s", "%s", "%s", %d)',
			 $name, $address, $comments, $fiscal_id, $id_company_role, $id_group, $website, $country, $manager, $id_parent);

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
		$sql = sprintf ('UPDATE tcompany SET manager="%s", id_parent = %d, comments = "%s", name = "%s",
		address = "%s", fiscal_id = "%s", id_company_role = %d, id_grupo = "%s", country = "%s", website = "%s" WHERE id = %d',
		$manager, $id_parent, $comments, $name, $address,
		$fiscal_id, $id_company_role, $id_group, $country, $website, $id);

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

	// TODO: ACL CHECK !!

	$id = (int) get_parameter ('id');
	$name = get_db_value ('name', 'tcompany', 'id', $id);
	

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

	// TODO: ACL CHECK !!
	
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

// Show tabs for a given company
// --------------------------------

if ($id) {

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
}

// EDIT / CREATE FORM

if ((($id > 0) AND ($op=="")) OR ($new_company == 1)) {
	echo '<form method="post" action="index.php?sec=customers&sec2=operation/companies/company_detail">';
	
	echo "<h2>".__('Company details')."</h2>";


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
	}

	// TODO: Make ACL check here. 
	// #1. Check if manager = current user -> OK
	// #2. If have CRM Manager, and current user is parent of this company -> OK
	// #3. If not, NO PERM to Write.

	$writter = 1;

	$table->width = '90%';
	$table->class = "databox";
	$table->data = array ();
	$table->colspan = array ();
	$table->colspan[4][0] = 2;
	$table->colspan[5][0] = 2;

	if ($writter) {
		$table->data[0][0] = print_input_text ('name', $name, '', 40, 100, true, __('Company name'));
		

		if ($id > 0)
			$table->data[0][0] .= "&nbsp;<a href='index.php?sec=customers&sec2=operation/companies/company_detail&id=$id&delete_company=1'><img src='images/cross.png'></a>";

		$table->data[0][1] = print_input_text_extended ('manager', $manager, 'text-user', '', 15, 30, false, '',
		array('style' => 'background: url(' . $src_code . ') no-repeat right;'), true, '', __('Manager'))

	. print_help_tip (__("Type at least two characters to search"), true);

		// TODO: Replace this for a function to get visible compenies for this user
		$sql2 = "SELECT id, name FROM tcompany";

		$table->data[1][0] = print_select_from_sql ($sql2, 'id_parent', $id_parent, '', __("None"), 0, true, false, true, __("Parent company"));
		
		$table->data[2][0] = print_input_text ("fiscal_id", $fiscal_id, "", 15, 100, true, __('Fiscal ID'));
		$table->data[2][1] = print_select_from_sql ('SELECT id, name FROM tcompany_role ORDER BY name',
			'id_company_role', $id_company_role, '', __('Select'), 0, true, false, false, __('Company Role'));

		$table->data[3][0] = print_input_text ("website", $website, "", 30, 100, true, __('Website'));
		$table->data[3][1] = print_input_text ("country", $country, "", 20, 100, true, __('Country'));

		$table->data[4][0] = print_textarea ('address', 3, 1, $address, '', true, __('Address'));
		$table->data[5][0] = print_textarea ("comments", 10, 1, $comments, '', true, __('Comments'));
	}
	
	print_table ($table);
	
	if ($id > 0) {
		echo '<div class="button" style="width: '.$table->width.'">';
		print_submit_button (__('Update'), "update_btn", false, 'class="sub upd"', false);
		print_input_hidden ('update_company', 1);
		print_input_hidden ('id', $id);
		echo "</div>";
	} else {
		echo '<div class="button" style="width: '.$table->width.'">';
		print_submit_button (__('Create'), "create_btn", false, 'class="sub upd"', false);
		echo "</div>";
		print_input_hidden ('create_company', 1);
	}
		
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

	$op2 = get_parameter ("op2", "");
	if ($op2 == "add"){
		$datetime =  date ("Y-m-d H:i:s");
		$comments = get_parameter ("comments", "");
		$sql = sprintf ('INSERT INTO tcompany_activity (id_company, written_by, date, description) VALUES (%d, "%s", "%s", "%s")', $id, $config["id_user"], $datetime, $comments);
		process_sql ($sql, 'insert_id');
	}
	
	// ADD item form
	// TODO: ACL Check

	$manager = 1;

	if($manager) {
		echo '<form method="post" action="index.php?sec=customers&sec2=operation/companies/company_detail&id='.$id.'&op=activities&op2=add">';
		echo "<h3>".__("Add activity")."</h3><p>";
		echo "<textarea name='comments' style='margin-left: 10px; width:94%; height: 150px'>";
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

		// TODO. ACL CHECK
		$manager = 1;

		if ($manager) {
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
	
	// TODO. ACL CHECK
	$manager = 1;

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
		
		// TODO. ACL CHECK
		$manager = 1;

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
	
	$sql = "SELECT * FROM tlead WHERE id_company = $id and progress < 100 ORDER BY estimated_sale DESC,  modification DESC";
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
		
		// TODO. ACL CHECK
		$manager = 1;

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

// No id passed as parameter
	
if ((!$id) AND ($new_company == 0)){

	// Search // General Company listing

	echo "<h2>".__('Company management')."</h2>";
	echo "<br>";
	$search_text = (string) get_parameter ('search_text');	
	$search_role = (int) get_parameter ("search_role");
	$search_country = (string) get_parameter ("search_country");
	$search_manager = (string) get_parameter ("search_manager");

	$where_clause = " 1 = 1 ";

	if ($search_text != "") {
		$where_clause .= sprintf (' AND name LIKE "%%%s%%" ', $search_text);
	}

	if ($search_role != 0){ 
		$where_clause .= sprintf (' AND id_company_role = %d', $search_role);
	}

	if ($search_country != ""){ 
		$where_clause .= sprintf (' AND country LIKE "%%s%%" ', $search_country);
	}

	if ($search_manager != ""){ 
		$where_clause .= sprintf (' AND manager = "%s" ', $search_manager);
	}

	$params = "&search_manager=$search_manager&search_text=$search_text&search_role=$search_role&search_country=$search_country";

	$table->width = '80%';
	$table->class = 'search-table';
	$table->style = array ();
	$table->style[0] = 'font-weight: bold;';
	$table->style[2] = 'font-weight: bold;';
	$table->data = array ();
	$table->data[0][0] = __('Search');
	$table->data[0][1] = print_input_text ("search_text", $search_text, "", 15, 100, true);
	$table->data[0][2] = __('Company Role');
	$table->data[0][3] = print_select_from_sql ('SELECT id, name FROM tcompany_role ORDER BY name',
		'search_role', $search_role, '', __('Select'), 0, true, false, false);
	$table->data[0][4] = __('Country');
	$table->data[0][5] = print_input_text ("search_country", $search_country, "", 10, 100, true);
	
	$table->data[0][4] = __('Manager');
	$table->data[0][5] = print_input_text_extended ('search_manager', $search_manager, 'text-user', '', 15, 30, false, '',	array('style' => 'background: url(' . $src_code . ') no-repeat right;'), true, '', '' )	. print_help_tip (__("Type at least two characters to search"), true);

	$table->data[0][6] = print_submit_button (__('Search'), "search_btn", false, 'class="sub search"', true);

	
	$table->data[0][7] .= "&nbsp;&nbsp;<a href='index.php?sec=customers&sec2=operation/companies/company_export$params&render=1&raw_output=1&clean_output=1'><img title='".__("Export to CSV")."' src='images/binary.gif'></a>";
	
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

	$companies = print_array_pagination ($companies, "index.php?sec=customers&sec2=operation/companies/company_detail$params");

	if ($companies !== false) {
		$table->width = "90%";
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

			if (give_acl ($config["id_user"], $id_group, "VM")) {
				$data[7] ='<a href="index.php?sec=customers&
							sec2=operation/companies/company_detail'.$params.'&
							delete_company=1&id='.$company['id'].'"
							onClick="if (!confirm(\''.__('Are you sure?').'\'))
							return false;">
							<img src="images/cross.png"></a>';
			}
			array_push ($table->data, $data);
		}
		print_table ($table);
	}
	
}

?>

<script type="text/javascript" src="include/js/jquery.autocomplete.js"></script>
<script type="text/javascript" >
$(document).ready (function () {
	$("#textarea-description").TextAreaResizer ();
	$("#text-user").autocomplete ("ajax.php",
		{
			scroll: true,
			minChars: 2,
			extraParams: {
				page: "include/ajax/users",
				search_users: 1,
				id_user: "<?php echo $config['id_user'] ?>",
			},
			formatItem: function (data, i, total) {
				
				if (total == 0)
					$("#text-user").css ('background-color', '#cc0000');
				else
					$("#text-user").css ('background-color', '');
				if (data == "")
					return false;
				return data[0]+'<br><span class="ac_extra_field">('+data[1]+')</span>';
			},
			delay: 200
		});
});
</script>
