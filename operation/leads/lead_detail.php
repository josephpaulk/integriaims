<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2013 Ártica Soluciones Tecnológicas
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
	audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation","Trying to access leads");
	require ("general/noaccess.php");
	exit;
}

$manager = give_acl ($config["id_user"], 0, "VM");

$id = (int) get_parameter ('id');

//TODO (sancho): Implement ACL system depending on company

$new = (bool) get_parameter ('new');
$create = (bool) get_parameter ('create');
$update = (bool) get_parameter ('update');
$delete = (bool) get_parameter ('delete');
$get = (bool) get_parameter ('get');


// Create
if ($create) {

	$id_company = (int) get_parameter ('id_company');
	$fullname = (string) get_parameter ('fullname');
	$phone = (string) get_parameter ('phone');
	$mobile = (string) get_parameter ('mobile');
	$email = (string) get_parameter ('email');
	$position = (string) get_parameter ('position');
	$company = (string) get_parameter ('company');
	$description = (string) get_parameter ('description');	
	$country = (string) get_parameter ('country');	
	$id_language = (string) get_parameter ('id_language');
	$owner = (string) get_parameter ('owner');
	$estimated_sale = (string) get_parameter ('estimated_sale');
	$id_category = (int) get_parameter ('product');
		
	$sql = sprintf ('INSERT INTO tlead (modification, creation, fullname, phone, mobile,
			email, position, id_company, description, company, country, id_language, owner, estimated_sale)
			VALUE ("%s", "%s","%s", "%s", "%s", "%s", "%s", %d, "%s", "%s", "%s", "%s", "%s", "%s")',
			date('Y-m-d H:m:i'), date('Y-m-d H:m:i'), $fullname, $phone, $mobile, $email, $position,
			$id_company, $description, $company, $country, $id_language, $owner, $estimated_sale);

	$id = process_sql ($sql, 'insert_id');

	$datetime =  date ("Y-m-d H:i:s");
	$sql = sprintf ('INSERT INTO tlead_history (id_lead, id_user, timestamp, description) VALUES (%d, "%s", "%s", "%s")', $id, $config["id_user"], $datetime, "Created lead");
	process_sql ($sql);

	if ($id === false) {
		echo "<h3 class='error'>".__('Could not be created')."</h3>";
	} else {
		echo "<h3 class='suc'>".__('Successfully created')."</h3>";
		audit_db ($config['id_user'], $REMOTE_ADDR, "Lead created", "Lead named '$fullname' has been added");
	}
	$id = 0;
	$create = false;
}

// Update
if ($update) { // if modified any parameter

	$id_company = (int) get_parameter ('id_company');
	$fullname = (string) get_parameter ('fullname');
	$phone = (string) get_parameter ('phone');
	$mobile = (string) get_parameter ('mobile');
	$email = (string) get_parameter ('email');
	$position = (string) get_parameter ('position');
	$company = (string) get_parameter ('company');
	$description = (string) get_parameter ('description');	
	$country = (string) get_parameter ('country');	
	$id_language = (string) get_parameter ('id_language');
	$owner = (string) get_parameter ('owner');
	$progress = (string) get_parameter ('progress');
	$estimated_sale = (string) get_parameter ('estimated_sale');
	$id_category = (int) get_parameter ('product');

	// Detect if it's a progress change

	$old_progress = get_db_value  ('progress', 'tlead', 'id', $id);

	$sql = sprintf ('UPDATE tlead
		SET modification = "%s", description = "%s", fullname = "%s", phone = "%s",
		mobile = "%s", email = "%s", position = "%s",
		id_company = %d, country = "%s", owner = "%s", progress = %d , id_language = "%s", estimated_sale = "%s" , company = "%s", id_category = %d WHERE id = %d',
		date('Y-m-d H:m:i'), $description, $fullname, $phone, $mobile, $email, $position,
		$id_company, $country, $owner, $progress, $id_language, $estimated_sale, $company, $id_category, $id);

	$result = process_sql ($sql);
	if ($result === false) {
		echo "<h3 class='error'>".__('Could not be updated')."</h3>";
	} else {
		echo "<h3 class='suc'>".__('Successfully updated')."</h3>";
		audit_db ($config['id_user'], $REMOTE_ADDR, "Lead updated", "Lead named '$fullname' has been updated");

		$datetime =  date ("Y-m-d H:i:s");	

		if ($old_progress != $progress){

			$label = translate_lead_progress($old_progress) . " -> " . translate_lead_progress ($progress);

			$sql = sprintf ('INSERT INTO tlead_history (id_lead, id_user, timestamp, description) VALUES (%d, "%s", "%s", "%s")', $id, $config["id_user"], $datetime, "Lead progress updated. $label");
		} else {
		
			$sql = sprintf ('INSERT INTO tlead_history (id_lead, id_user, timestamp, description) VALUES (%d, "%s", "%s", "%s")', $id, $config["id_user"], $datetime, "Lead updated");
		}

		$result = process_sql ($sql);

	}
	$id = 0;
	$update = false; // go to list screen

	// Clean up country name
	$country = "";
}

// Delete
if ($delete) {
	
	$fullname = get_db_value  ('fullname', 'tlead', 'id', $id);
	$sql = sprintf ('DELETE FROM tlead WHERE id = %d', $id);
	process_sql ($sql);
	audit_db ($config['id_user'], $REMOTE_ADDR, "Lead deleted", "Lead named '$fullname' has been deleted");

	$sql = sprintf ('DELETE FROM tlead_activity WHERE id_lead = %d', $id);
	process_sql ($sql);

	$sql = sprintf ('DELETE FROM tlead_history WHERE id_lead = %d', $id);
	process_sql ($sql);

	echo "<h3 class='suc'>".__('Successfully deleted')."</h3>";
	$id = 0;
}

echo "<h2>".__('Lead management')."</h2>";

// FORM (Update / Create)
if ($id || $new) {
	if ($new) {
		if (! give_acl ($config["id_user"], 0, "VM")) {
			audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation","Trying to create a lead without access");
			require ("general/noaccess.php");
			exit;
		}
		$id = 0;

		$id_company = (int) get_parameter ('id_company');
		$fullname = (string) get_parameter ('fullname');
		$phone = (string) get_parameter ('phone');
		$mobile = (string) get_parameter ('mobile');
		$email = (string) get_parameter ('email');
		$position = (string) get_parameter ('position');
		$company = (string) get_parameter ('company');
		$description = (string) get_parameter ('description');	
		$country = (string) get_parameter ('country');	
		$id_language = (string) get_parameter ('id_language');
		$owner = (string) get_parameter ('owner');
		$progress = (string) get_parameter ('progress');
		$estimated_sale = (string) get_parameter ('estimated_sale');
		$id_category = (int) get_parameter ('product');

	} else {

		// TODO (slerena): implement ACL here based on company or something :)

		if (! give_acl ($config["id_user"], 0, "VR")) {
			audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation","Trying to access a contact in a group without access");
			require ("general/noaccess.php");
			exit;
		}

		$lead = get_db_row ("tlead", "id", $id);
		$id_company = $lead['id_company'];
		$fullname = $lead['fullname'];
		$phone = $lead['phone'];
		$mobile = $lead['mobile'];
		$email = $lead['email'];
		$position = $lead['position'];
		$company = $lead['company'];
		$description = $lead['description'];	
		$country = $lead['country'];	
		$id_language = $lead['id_language'];
		$owner = $lead['owner'];
		$progress = $lead['progress'];
		$estimated_sale = $lead['estimated_sale'];
		$creation = $lead["creation"];
		$modification = $lead["modification"];
		$id_category = $lead["id_category"];

	}
	
	// Show tabs
	if ($id) {

		$op = get_parameter ("op", "");
		
		echo '<ul style="height: 30px;" class="ui-tabs-nav">';
		echo '<li class="ui-tabs">';
		echo '<a href="index.php?sec=customers&sec2=operation/leads/lead_detail"><span>'.__("Search").'</span></a></li>';

		if ($op == "")
			echo '<li class="ui-tabs-selected">';
		else
			echo '<li class="ui-tabs">';
		echo '<a href="index.php?sec=customers&sec2=operation/leads/lead_detail&id='.$id.'"><span>'.__("Lead").'</span></a></li>';

		if ($op == "activity")
			echo '<li class="ui-tabs-selected">';
		else
			echo '<li class="ui-tabs">';
		echo '<a href="index.php?sec=customers&sec2=operation/leads/lead_detail&id='.$id.'&op=activity"><span>'.__("Activity").'</span></a></li>';

		if ($op == "history")
			echo '<li class="ui-tabs-selected">';
		else
			echo '<li class="ui-tabs">';
		echo '<a href="index.php?sec=customers&sec2=operation/leads/lead_detail&id='.$id.'&op=history"><span>'.__("Tracking").'</span></a></li>';

		echo '<li class="ui-tabs">';
		echo '<a href="index.php?sec=customers&sec2=operation/companies/company_detail&id='.$id_company.'"><span>'.__("Company").'</span></a></li>';


		echo '</ul>';

		// Raya horizontal
		echo '<div id="ui-tabs-1" class="ui-tabs-panel" style="display: block;"></div>';
	}

	// Load tab activity
	if ($op == "activity"){
		include "operation/leads/lead_activity.php";
		return;
	}

	// Load tab history/tracking
	if ($op == "history"){
		include "operation/leads/lead_history.php";
		return;
	}

	$table->width = "90%";
	$table->class = "databox";
	$table->data = array ();
	$table->colspan = array ();
	$table->colspan[7][0] = 4;
	
	if (give_acl ($config["id_user"], 0, "VW")) {
		$table->data[0][0] = print_input_text ("fullname", $fullname, "", 60, 100, true, __('Full name'));
		$table->data[0][1] = print_input_text ("company", $company, "", 60, 100, true, __('Company name'));
		$table->data[1][0] = print_input_text ("email", $email, "", 35, 100, true, __('Email'));
		$table->data[1][1] = print_input_text ("country", $country, "", 35, 100, true, __('Country'));
		$table->data[2][0] = print_input_text ("estimated_sale", $estimated_sale, "", 12, 100, true, __('Estimated sale'));
	
		$progress_values = lead_progress_array ();

		$table->data[2][1] = print_select ($progress_values, 'progress', $progress, '', __("None"), 0, true, 0, false, __('Lead progress') );

		$table->data[3][0] = print_input_text ("phone", $phone, "", 15, 60, true, __('Phone number'));
		$table->data[3][1] = print_input_text ("mobile", $mobile, "", 15, 60, true, __('Mobile number'));
		$table->data[4][0] = print_input_text ('position', $position, '', 25, 50, true, __('Position'));
		
		// TODO: Show only companies with access to them


		if ($config["lead_company_filter"] != ""){
			$sql2 = "SELECT id, name FROM tcompany WHERE id_company_role IN ('".$config["lead_company_filter"]."')";
		} else {
			$sql2 = "SELECT id, name FROM tcompany ";
		}
		$sql2 .=  " ORDER by name";

		$table->data[4][1] = print_select_from_sql ($sql2, 'id_company', $id_company, '', __("None"), 0, true, false, true, __("Managed by"));

		$table->data[4][1] .= "&nbsp;&nbsp;<a href='index.php?sec=customers&sec2=operation/companies/company_detail&id=$id_company'>";
		$table->data[4][1] .= "<img src='images/company.png'></a>";
		
		$table->data[5][0] = print_input_text ('owner', $owner, '', 15, 15, true, __('Owner'));

		$table->data[5][1] = print_select_from_sql ('SELECT id_language, name FROM tlanguage ORDER BY name',
	'id_language', $id_language, '', '', '', true, false, false,
	__('Language'));

		$table->data[6][0] = "<b>". __("Creation / Last update"). "</b><br><span style='font-size: 10px'>";
		$table->data[6][0] .=  "$creation / $modification </span>";

		$table->data[6][1] = combo_kb_products ($id_category, true, 'Product type', true);

		$table->data[7][0] = print_textarea ("description", 10, 1, $description, '', true, __('Description'));
	}
	else {
		if($fullname == '') {
			$fullname = '<i>-'.__('Empty').'-</i>';
		}		
		$table->data[0][0] = "<b>".__('Full name')."</b><br>$fullname<br>";
		if($email == '') {
			$email = '<i>-'.__('Empty').'-</i>';
		}		

		$table->data[0][1] = "<b>".__('Company')."</b><br>$company<br>";

		$table->data[1][0] = "<b>".__('Email')."</b><br>$email<br>";
		if($phone == '') {
			$phone = '<i>-'.__('Empty').'-</i>';
		}		

		$table->data[1][1] = "<b>".__('Country')."</b><br>$country<br>";

		$table->data[2][0] = "<b>".__('Est. Sale')."</b><br>$estimated_sale<br>";
				
		$table->data[3][0] = "<b>".__('Phone number')."</b><br>$phone<br>";

		$table->data[3][1] = "<b>".__('Mobile number')."</b><br>$mobile<br>";
		
		if($position == '') {
			$position = '<i>-'.__('Empty').'-</i>';
		}		
		$table->data[4][0] = "<b>".__('Position')."</b><br>$position<br>";
		
		$company_name = get_db_value('name','tcompany','id',$id_company);

		$table->data[4][1] = "<b>".__('Company')."</b><br>$company_name";
			
		$table->data[4][1] .= "&nbsp;&nbsp;<a href='index.php?sec=customers&sec2=operation/companies/company_detail&id=$id_company'>";
		$table->data[4][1] .= "<img src='images/company.png'></a>";

		$table->data[5][0] = "<b>".__('Owner')."</b><br>$owner<br>";
		$table->data[5][1] = "<b>".__('Language')."</b><br>$id_language<br>";
	
		if($description == '') {
			$description = '<i>-'.__('Empty').'-</i>';
		}		
		$table->data[6][0] = "<b>".__('Description')."</b><br>$description<br>";
	}
	
	echo '<form method="post" id="lead_form">';
	print_table ($table);

	if (give_acl ($config["id_user"], $id_group, "VW")) {
	
		echo '<div class="button" style="width: '.$table->width.'">';
		if ($id) {
			print_submit_button (__('Update'), 'update_btn', false, 'class="sub upd"', false);
			print_input_hidden ('update', 1);
			print_input_hidden ('id', $id);
		} else {
			print_submit_button (__('Create'), 'create_btn', false, 'class="sub next"', false);
			print_input_hidden ('create', 1);
		}
		echo "</div>";
	}

	echo "</form>";

} else {

	// Listing of contacts
	
	// TODO: Show only leads of my company or my company's children.

	$search_text = (string) get_parameter ('search_text');
	$id_company = (int) get_parameter ('id_company');
	$start_date = (string) get_parameter ('start_date');
	$end_date = (string) get_parameter ('end_date');
	$country = (string) get_parameter ('country');
	$id_category = (int) get_parameter ('product');

	$progress_major_than = (string) get_parameter ('progress_major_than');
	$progress_minor_than = (string) get_parameter ('progress_minor_than');

	$show_100 = (int) get_parameter ("show_100");

	if ($show_100){
		$where_clause = "WHERE 1=1 $where_group ";
	} else {
		$where_clause = "WHERE progress < 100 $where_group ";
	}

	if ($search_text != "") {
		$where_clause .= sprintf (' AND fullname LIKE "%%%s%%" OR description LIKE "%%%s%%" OR company LIKE "%%%s%%"', $search_text, $search_text, $search_text);
	}

	if ($id_company) {
		$where_clause .= sprintf (' AND id_company = %d', $id_company);
	}

	if ($start_date) {
		$where_clause .= sprintf (' AND creation >= "%s"', $start_date);
	}

	if ($end_date) {
		$where_clause .= sprintf (' AND creation <= "%s"', $end_date);
	}

	if ($country) {
		$where_clause .= sprintf (' AND country LIKE "%%%s%%"', $country);
	}

	if ($progress_minor_than) {
		$where_clause .= sprintf (' AND progress < %d ', $progress_minor_than);
	}

	if ($progress_major_than) {
		$where_clause .= sprintf (' AND progress < %d ', $progress_major_than);
	}

	if ($id_category) {
		$where_clause .= sprintf(' AND id_category = %d ', $id_category);
	}

	$table->class = 'search-table';
	$table->style = array ();
	$table->style[0] = 'font-weight: bold;';
	$table->data = array ();
	$table->data[0][0] = print_input_text ("search_text", $search_text, "", 15, 100, true, __('Search'));
	

	if ($config["lead_company_filter"] != ""){
		$sql2 = "SELECT id, name FROM tcompany WHERE id_company_role IN ('".$config["lead_company_filter"]."')";
	} else {
		$sql2 = "SELECT id, name FROM tcompany ";
	}
	$sql2 .=  " ORDER by name";

	$table->data[0][1] = print_select_from_sql ($sql2, 'id_company', $id_company, '', __("None"), 0, true, false, true, __("Managed by"));

	$table->data[0][2] = print_input_text ("country", $country, "", 21, 100, true, __('Country'));

	$table->data[1][0] = print_input_text ("progress_major_than", $progress_major_than, "", 3, 100, true, __('Progress > '));
	$table->data[1][0] .= " ". print_input_text ("progress_minor_than", $progress_minor_than, "", 3, 100, true, __('Progress < '));

	$table->data[1][1] = combo_kb_products ($id_category, true, 'Product type', true);

	$table->data[1][2] =  print_checkbox ("show_100", 1, $show_100, true, __("Show finished leads"));

	$table->data[2][0] = print_input_text ("start_date", $start_date, "", 15, 100, true, __('Start date'));
	$table->data[2][1] = print_input_text ("end_date", $end_date, "", 15, 100, true, __('End date'));
	$table->data[2][2] = print_submit_button (__('Search'), "search_btn", false, 'class="sub search"', true);
	
	echo '<form action="index.php?sec=customers&sec2=operation/leads/lead_detail" method="post">';
	print_table ($table);
	echo '</form>';

	$sql = "SELECT * FROM tlead $where_clause ORDER BY creation DESC";

	$leads = get_db_all_rows_sql ($sql);

	$leads = print_array_pagination ($leads, "index.php?sec=customers&sec2=operation/leads/lead_detail");

	if ($leads !== false) {
		unset ($table);
		$table->width = "95%";
		$table->class = "listing";
		$table->data = array ();
		$table->size = array ();
		$table->style = array ();
		$table->style[0] = 'font-weight: bold';
		$table->head = array ();
		$table->head[0] = __('Full name');
		$table->head[1] = __('Product');
		$table->head[2] = __('Managed by');
		$table->head[3] = __('Progress');
		$table->head[4] = __('Estimated sale');
		$table->head[5] = __('Country');
		$table->head[6] = __('Date');
		$table->head[7] = __('Last update');

		$table->size[4] = '100px;';
		$table->style[6] = 'font-size: 9px;';
		$table->style[7] = 'font-size: 9px;';

		if(give_acl ($config["id_user"], 0, "VM")) {
			$table->head[8] = __('Delete');
		}
		
		foreach ($leads as $lead) {
			$data = array ();
			// Name
			$data[0] = "<a href='index.php?sec=customers&sec2=operation/leads/lead_detail&id=".
				$lead['id']."'>".$lead['fullname']."</a>";

			$data[1] = get_db_value ('name', 'tkb_product', 'id', $lead['id_category']);

			$data[2] = "<a href='index.php?sec=customers&sec2=operation/companies/company_detail&id=".$lead['id_company']."'>".get_db_value ('name', 'tcompany', 'id', $lead['id_company'])."</a>";
			$data[3] = translate_lead_progress ($lead['progress']) . " (".$lead['progress']. "%)";
			$data[4] = format_numeric($lead['estimated_sale']);
			$data[5] = $lead['country'];
			$data[6] = human_time_comparation ($lead['creation']);
			$data[7] = human_time_comparation ($lead['modification']);

			if(give_acl ($config["id_user"], $id_group, "VM")) {
				$data[8] = '<a href="index.php?sec=customers&
							sec2=operation/leads/lead_detail&
							delete=1&id='.$lead['id'].'"
							onClick="if (!confirm(\''.__('Are you sure?').'\'))
							return false;">
							<img src="images/cross.png"></a>';
			}
			array_push ($table->data, $data);
		}
		print_table ($table);
	}
	
	if ($manager) {
		echo '<form method="post" action="index.php?sec=customers&sec2=operation/leads/lead_detail">';
		echo '<div class="button" style="width: '.$table->width.'">';
		print_submit_button (__('Create'), 'new_btn', false, 'class="sub next"');
		print_input_hidden ('new', 1);
		echo '</div>';
		echo '</form>';
	}
}

?>
