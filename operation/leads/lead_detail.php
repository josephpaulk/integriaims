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

enterprise_include('include/functions_crm.php');
include_once('include/functions_crm.php');

$read = true;
$write = true;
$manage = true;
$write_permission = true;
$manage_permission = true;
$read_permission = true;
	
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
} 

$id = (int) get_parameter ('id');


if ($id != 0) {
	
	$read_permission = enterprise_hook ('crm_check_acl_lead', array ($config['id_user'], $id));
	$write_permission = enterprise_hook ('crm_check_acl_lead', array ($config['id_user'], $id, true));
	$manage_permission = enterprise_hook ('crm_check_acl_lead', array ($config['id_user'], $id, false, false, true));

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

//TODO (sancho): Implement ACL system depending on company

$new = (bool) get_parameter ('new');
$create = (bool) get_parameter ('create');
$update = (bool) get_parameter ('update');
$delete = (bool) get_parameter ('delete');
$get = (bool) get_parameter ('get');
$close = (bool) get_parameter('close');
$make_owner = (bool) get_parameter ('make_owner');
$offset = get_parameter('offset', 0);

$id_search = (int) get_parameter ('saved_searches');
$create_custom_search = (bool) get_parameter ('save_search');
$delete_custom_search = (bool) get_parameter ('delete_custom_search');

// Create
if ($create) {

	if (!$manage_permission) {
	        audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create a contract");
	        require ("general/noaccess.php");
	        exit;
	}
	
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
	$progress = (string) get_parameter ('progress');
		
	$sql = sprintf ('INSERT INTO tlead (modification, creation, fullname, phone, mobile,
			email, position, id_company, description, company, country, id_language, owner, estimated_sale, id_category, progress)
			VALUE ("%s", "%s","%s", "%s", "%s", "%s", "%s", %d, "%s", "%s", "%s", "%s", "%s", "%s", %d, %d)',
			date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), $fullname, $phone, $mobile, $email, $position,
			$id_company, $description, $company, $country, $id_language, $owner, $estimated_sale, $id_category, $Progress);

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
	$id = false;
	$new = false;

	// Clean up all inputs
	unset ($_POST);
}

// Make owner
if ($make_owner){

	if (!$write_permission) {
	        audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create a contract");
	        require ("general/noaccess.php");
	        exit;
	}
	
	// Get company of current user
	$id_company = get_db_value  ('id_company', 'tusuario', 'id_usuario', $config["id_user"]);
	
	if ($id_company == ""){
		$id_company = 0;
	}

	// Update lead with current user/company to take ownership of the lead.
	$sql = sprintf ('UPDATE tlead
		SET id_company = %d, owner = "%s" WHERE id = %d',
		$id_company,$config["id_user"], $id);
	$result = process_sql ($sql);

	// Add tracking info.
	$datetime =  date ("Y-m-d H:i:s");
	$sql = sprintf ('INSERT INTO tlead_history (id_lead, id_user, timestamp, description) VALUES (%d, "%s", "%s", "%s")', $id, $config["id_user"], $datetime, "Take ownership of lead");
	process_sql ($sql);
	$make_owner = 0;
	
}

// Update
if ($update) { // if modified any parameter
	
	if (!$write_permission) {
	        audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create a contract");
	        require ("general/noaccess.php");
	        exit;
	}
	
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
		date('Y-m-d H:i:s'), $description, $fullname, $phone, $mobile, $email, $position,
		$id_company, $country, $owner, $progress, $id_language, $estimated_sale, $company, $id_category, $id);

	$result = process_sql ($sql);
	if ($result === false) {
		echo "<h3 class='error'>".__('Could not be updated')."</h3>";
	} else {
		echo "<h3 class='suc'>".__('Successfully updated')."</h3>";
		audit_db ($config['id_user'], '', "Lead updated", "Lead named '$fullname' has been updated");

		$datetime =  date ("Y-m-d H:i:s");	

		if ($old_progress != $progress){

			$label = translate_lead_progress($old_progress) . " -> " . translate_lead_progress ($progress);

			$sql = sprintf ('INSERT INTO tlead_history (id_lead, id_user, timestamp, description) VALUES (%d, "%s", "%s", "%s")', $id, $config["id_user"], $datetime, "Lead progress updated. $label");
		} else {
		
			$sql = sprintf ('INSERT INTO tlead_history (id_lead, id_user, timestamp, description) VALUES (%d, "%s", "%s", "%s")', $id, $config["id_user"], $datetime, "Lead updated");
		}

		$result = process_sql ($sql);

	}
	
/*
	$update = false; // continue editing...

	// Clean up all inputs
	unset ($_POST);
*/
	$id = 0;
}

// Delete
if ($delete) {
	
	//TODO: ACL check here !
	if (!$manage_permission) {
	        audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create a contract");
	        require ("general/noaccess.php");
	        exit;
	}

	$fullname = get_db_value  ('fullname', 'tlead', 'id', $id);
	$sql = sprintf ('DELETE FROM tlead WHERE id = %d', $id);
	process_sql ($sql);
	audit_db ($config['id_user'], $REMOTE_ADDR, "Lead deleted", "Lead named '$fullname' has been deleted");

	$sql = sprintf ('DELETE FROM tlead_activity WHERE id_lead = %d', $id);
	process_sql ($sql);

	$sql = sprintf ('DELETE FROM tlead_history WHERE id_lead = %d', $id);
	process_sql ($sql);

	echo "<h3 class='suc'>".__('Successfully deleted')."</h3>";
	$id = 0; // Force go listing page.
}

// Close
if ($close) {

	//TODO: ACL check here !
	if (!$write_permission) {
	        audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create a contract");
	        require ("general/noaccess.php");
	        exit;
	}

	$sql = sprintf ('UPDATE tlead SET progress = 100 WHERE id = %d', $id);
	process_sql ($sql);

	$datetime =  date ("Y-m-d H:i:s");	
	$sql = sprintf ('INSERT INTO tlead_history (id_lead, id_user, timestamp, description) VALUES (%d, "%s", "%s", "%s")', $id, $config["id_user"], $datetime, "Lead closed");

	echo "<h3 class='suc'>".__('Successfully closed')."</h3>";
	$id = 0;
}

// Filter for custom search
$filter = array ();
$filter['search_text'] = (string) get_parameter ('search_text');
$filter['id_company'] = (int) get_parameter ('id_company_search');
$filter['start_date'] = (string) get_parameter ('start_date_search');
$filter['end_date'] = (string) get_parameter ('end_date_search');
$filter['country'] = (string) get_parameter ('country_search', "");
$filter['id_category'] = (int) get_parameter ('product_search');
$filter['progress_major_than'] = (int) get_parameter ('progress_major_than_search');
$filter['progress_minor_than'] = (int) get_parameter ('progress_minor_than_search');
$filter['owner'] = (string) get_parameter ("owner_search");
$filter['show_100'] = (int) get_parameter ("show_100_search");
$filter['id_language'] = (string) get_parameter ("id_language", "");
$filter['est_sale'] = (int) get_parameter ("est_sale_search", 0);

/* Create a custom saved search*/
if ($create_custom_search && !$id_search) {
	
	$search_name = (string) get_parameter ('search_name');
	
	$result = create_custom_search ($search_name, 'leads', $filter);
	
	if ($result === false) {
		echo '<h3 class="error">'.__('Could not create custom search').'</h3>';
	}
	else {
		echo '<h3 class="suc">'.__('Custom search saved').'</h3>';
	}
}

/* Get a custom search*/
if ($id_search && !$delete_custom_search) {
	
	$search = get_custom_search ($id_search, 'leads');
	
	if ($search) { 
		
		if ($search["form_values"]) {
			
			$filter = unserialize($search["form_values"]);
			
			echo '<h3 class="suc">'.sprintf(__('Custom search "%s" loaded'), $search["name"]).'</h3>';
		}
		else {
			echo '<h3 class="error">'.sprintf(__('Could not load "%s" custom search'), $search["name"]).'</h3>';	
		}
	}
	else {
		echo '<h3 class="error">'.__('Could not load custom search').'</h3>';
	}
}

/* Delete a custom saved search */
if ($id_search && $delete_custom_search) {
	
	$sql = sprintf ('DELETE FROM tcustom_search
		WHERE id_user = "%s"
		AND id = %d',
		$config['id_user'], $id_search);
	$result = process_sql ($sql);
	if ($result === false) {
		echo '<h3 class="error">'.__('Could not delete custom search').'</h3>';
	}
	else {
		$id_search = false;
		echo '<h3 class="suc">'.__('Custom search deleted').'</h3>';
	}
}

// FORM (Update / Create)
if ($id || $new) {
	if ($new) {

		if (!$manage_permission) {
	        audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create a contract");
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
		if (!$write_permission) {
	        audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create a contract");
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
		if ($op == "files")
			echo '<li class="ui-tabs-selected">';
		else
			echo '<li class="ui-tabs">';
		echo '<a href="index.php?sec=customers&sec2=operation/leads/lead_detail&id='.$id.'&op=files"><span>'.__("Files").'</span></a></li>';
		
		echo '<li class="ui-tabs">';
		echo '<a href="index.php?sec=customers&sec2=operation/companies/company_detail&id='.$id_company.'"><span>'.__("Company").'</span></a></li>';
		
		if ($op == "forward")
			echo '<li class="ui-tabs-selected">';
		else
			echo '<li class="ui-tabs">';
		echo '<a href="index.php?sec=customers&sec2=operation/leads/lead_detail&id='.$id.'&op=forward"><span>'.__("Forward lead").'</span></a></li>';
		
		// Show mail tab only on owned leads
		$lead_owner = get_db_value ("owner", "tlead", "id", $id);

		if ($lead_owner == $config["id_user"]){
			if ($op == "mail")
				echo '<li class="ui-tabs-selected">';
			else
				echo '<li class="ui-tabs">';
			echo '<a href="index.php?sec=customers&sec2=operation/leads/lead_detail&id='.$id.'&op=mail"><span>'.__("Mail reply").'</span></a></li>';
		}
		
		if ($op == "history")
			echo '<li class="ui-tabs-selected">';
		else
			echo '<li class="ui-tabs">';
		echo '<a href="index.php?sec=customers&sec2=operation/leads/lead_detail&id='.$id.'&op=history"><span>'.__("Tracking").'</span></a></li>';

		if ($op == "activity")
			echo '<li class="ui-tabs-selected">';
		else
			echo '<li class="ui-tabs">';
		echo '<a href="index.php?sec=customers&sec2=operation/leads/lead_detail&id='.$id.'&op=activity"><span>'.__("Activity").'</span></a></li>';
		
		if ($op == "")
			echo '<li class="ui-tabs-selected">';
		else
			echo '<li class="ui-tabs">';
		echo '<a href="index.php?sec=customers&sec2=operation/leads/lead_detail&id='.$id.'"><span>'.__("Lead details").'</span></a></li>';

		echo '<li class="ui-tabs">';
		echo '<a href="index.php?sec=customers&sec2=operation/leads/lead_detail"><span>'.__("Search").'</span></a></li>';

		echo '<li class="ui-tabs-title">';
		switch ($op) {
			case "activity":
				echo strtoupper(__('Activity'));
				break;
			case "history":
				echo strtoupper(__('Tracking'));
				break;
			case "mail":
				echo strtoupper(__('Mail reply'));
				break;
			case "files":
				echo strtoupper(__('Files'));
				break;
			case "forward":
				echo strtoupper(__('Forward lead'));
				break;
			default:
				echo strtoupper(__('Lead details'));
		}
		echo '</li>';
		
		echo '</ul>';
		
		$name = get_db_value ('fullname', 'tlead', 'id', $id);
		
		echo '<div class="under_tabs_info">' . sprintf(__('Lead #%s: %s'), $id, $name) . '</div>';
	}

	switch ($op) {
		case "activity":
			// Load tab activity
			include "operation/leads/lead_activity.php";
			return;
		case "history":
			// Load tab history/tracking
			include "operation/leads/lead_history.php";
			return;
		case "mail":
			// Load tab mail
			include "operation/leads/lead_mail.php";
			return;
		case "files":
			// Load tab files
			include "operation/leads/lead_files.php";
			return;
		case "forward":
			// Load tab forward
			include "operation/leads/lead_forward.php";
			return;
	}

	$table->width = "99%";
	$table->class = "search-table-button";
	$table->data = array ();
	$table->colspan = array ();
	$table->colspan[8][0] = 4;
	
	if ($write_permission) {
		
		if ($id == 0) {
			echo "<h1>".__('Create lead')."</h1>";
		}

		$table->data[0][0] = print_checkbox ("duplicated_leads", 0, false, true, __('Allow duplicated leads'));
		$table->data[1][0] = print_input_text ("fullname", $fullname, "", 60, 100, true, __('Full name'));
		$table->data[1][1] = print_input_text ("company", $company, "", 60, 100, true, __('Company name'));
		$table->data[2][0] = print_input_text ("email", $email, "", 35, 100, true, __('Email'));
		$table->data[2][1] = print_input_text ("country", $country, "", 35, 100, true, __('Country'));
		$table->data[3][0] = print_input_text ("estimated_sale", $estimated_sale, "", 12, 100, true, __('Estimated sale'));
		$table->data[3][0] .= print_help_tip (__("Use only integer values, p.e: 23000 instead 23,000 or 23,000.00"), true);

		$progress_values = lead_progress_array ();

		$table->data[3][1] = print_select ($progress_values, 'progress', $progress, '', __("None"), 0, true, 0, false, __('Lead progress') );

		$table->data[4][0] = print_input_text ("phone", $phone, "", 15, 60, true, __('Phone number'));
		$table->data[4][1] = print_input_text ("mobile", $mobile, "", 15, 60, true, __('Mobile number'));
		$table->data[5][0] = print_input_text ('position', $position, '', 25, 50, true, __('Position'));
		
		// TODO: Show only companies with access to them


		if ($config["lead_company_filter"] != ""){
			$sql2 = "SELECT id, name FROM tcompany WHERE id_company_role IN ('".$config["lead_company_filter"]."')";
		} else {
			$sql2 = "SELECT id, name FROM tcompany ";
		}
		$sql2 .=  " ORDER by name";

		$companies = process_sql($sql2);
		
		if ($read && $enterprise) {
			$companies = crm_get_user_companies($config['id_user'], $companies, true);
		}
	
		$languages = crm_get_all_languages();
		$table->data[5][1] = print_select ($languages, 'id_language', $id_language, '', __('Select'), '', true, 0, false,  __('Language'));
		
		$table->data[6][0] = print_input_text_extended ('owner', $owner, 'text-user', '', 15, 30, false, '',
			array(), true, '', __("Owner") )
		. print_help_tip (__("Type at least two characters to search"), true);


		// Show delete control if its owned by the user
		if ($id && $config["id_user"] == $owner){
			$table->data[6][0] .= ' <a href="index.php?sec=customers&
							sec2=operation/leads/lead_detail&
							delete=1&id='.$id.'&offset='.$offset.'"
							onClick="if (!confirm(\''.__('Are you sure?').'\'))
							return false;">
							<img src="images/cross.png"></a>';
		}

		// Show "close" control if it's owned by the user
		if ($id && ( ($config["id_user"] == $lead["owner"]) OR (dame_admin($config["id_user"])) ) ) {
                        $table->data[6][0] .= "&nbsp;<a href='index.php?sec=customers&sec2=operation/leads/lead_detail&id=".
                        $id."&close=1'><img src='images/lock.png' title='".__("Close this lead")."'></a>";
                }

		// Show take control is owned by nobody
		if ($owner == "" && $id)
				$table->data[6][0] .=  "<a href='index.php?sec=customers&sec2=operation/leads/lead_detail&id=".
				$id."&make_owner=1'><img src='images/award_star_silver_1.png'></a>";

		$table->data[6][1] =  print_select ($companies, 'id_company', $id_company, '', __("None"), 0, true, 0, false,  __('Managed by'));
		if ($id_company) {
			$table->data[6][1] .= "&nbsp;&nbsp;<a href='index.php?sec=customers&sec2=operation/companies/company_detail&id=$id_company'>";
			$table->data[6][1] .= "<img src='images/company.png'></a>";
		}

		$table->data[7][0] = "<b>". __("Creation / Last update"). "</b><br><span style='font-size: 10px'>";
		$table->data[7][0] .=  "$creation / $modification </span>";

		$table->data[7][1] = combo_kb_products ($id_category, true, 'Product type', true);

		$table->data[8][0] = print_textarea ("description", 10, 1, $description, '', true, __('Description'));
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
	
	if ($id) {
		$button = print_submit_button (__('Update'), 'update_btn', false, 'class="sub upd"', true);
		$button .= print_input_hidden ('update', 1, true);
		$button .= print_input_hidden ('id', $id, true);
	} else {
		$button = print_submit_button (__('Create'), 'create_btn', false, 'class="sub create"', true);
		$button .= print_input_hidden ('create', 1, true);
	}
	
	$table->colspan[count($table->data) + 1][0] = 4;
	$table->data[count($table->data) + 1][0] = $button;
	
	echo '<form method="post" id="lead_form">';
	print_table ($table);
	echo "</form>";

} else {

	// Listing of contacts
	echo "<div id='lead-search-content'>";
	echo "<h1>".__('Lead search');
	echo "<div id='button-bar-title'>";
	echo "<ul>";
	echo "<li>";
	echo "<a id='lead_stats_form_submit' href='javascript: changeAction();'>".print_image ("images/chart_bar_dark.png", true, array("title" => __("Search statistics")))."</a>";
	echo "</li>";
	echo "</ul>";
	echo "</div>";
	echo "</h1>";
	
	// Custom search button
	echo "<div id='button-bar-title' style='margin-right: 12px; padding-bottom: 3px; margin-top: 5px;'>";
	echo "<ul>";
	echo "<li style='padding: 3px;'>";
	echo "<a href='javascript:' onclick='toggleDiv (\"custom_search\")'>".__('Custom search')."</a>";
	echo "</li>";
	echo "</ul>";
	echo "</div>";
	
	//FORM AND TABLE TO MANAGE CUSTOM SEARCHES
	$table = new stdClass;
	$table->id = 'saved_searches_table';
	$table->width = '99%';
	$table->class = 'search-table';
	$table->size = array ();
	$table->style = array ();
	$table->style[0] = 'font-weight: bold';
	$table->style[1] = 'font-weight: bold';
	$table->data = array ();
	$sql = sprintf ('SELECT id, name FROM tcustom_search
					 WHERE id_user = "%s"
						 AND section = "leads"
					 ORDER BY name',
					 $config['id_user']);
	$table->data[0][0] = print_select_from_sql ($sql, 'saved_searches', $id_search, '', __('None'), 0, true, false, true, __('Custom searches'));

	//If a custom search was selected display cross
	if ($id_search) {
		$table->data[0][0] .= '<a href="index.php?sec=customers&sec2=operation/leads/lead_detail&delete_custom_search=1&saved_searches='.$id_search.'">';
		$table->data[0][0] .= '<img src="images/cross.png" title="' . __('Delete') . '"/></a>';
	} else {
		$table->data[0][1] = print_input_text ('search_name', '', '', 10, 20, true, __('Save current search'));
		$table->data[0][2] = print_submit_button (__('Save'), 'save_search', false, 'class="sub save" style="margin-top: 13px;"', true);
	}

	echo '<div id="custom_search" style="display: none;">';
	echo '<form id="form-saved_searches" method="post" action="index.php?sec=customers&sec2=operation/leads/lead_detail">';
	foreach ($filter as $key => $value) {
		if ($key == "search_text") {
			print_input_hidden ("search_text", $value);
		} elseif ($key == "id_language") {
			print_input_hidden ("id_language", $value);
		} else {
			print_input_hidden ($key."_search", $value);
		}
	}
	print_table ($table);
	echo '</form>';
	echo '</div>';
	
	// TODO: Show only leads of my company or my company's children.
	// TODO: Implement ACL check !
	
	if ($id_search) {
		$search_text = $filter['search_text'];
		$id_company = $filter['id_company'];
		$start_date = $filter['start_date'];
		$end_date = $filter['end_date'];
		$country = $filter['country'];
		$id_category = $filter['id_category'];
		$progress_major_than = $filter['progress_major_than'];
		$progress_minor_than = $filter['progress_minor_than'];
		$owner = $filter['owner'];
		$show_100 = $filter['show_100'];
		$id_language = $filter['id_language'];
		$est_sale = $filter['est_sale'];
	} else {
		$search_text = (string) get_parameter ('search_text');
		$id_company = (int) get_parameter ('id_company_search');
		$start_date = (string) get_parameter ('start_date_search');
		$end_date = (string) get_parameter ('end_date_search');
		$country = (string) get_parameter ('country_search');
		$id_category = (int) get_parameter ('product_search');
		$progress_major_than = (int) get_parameter ('progress_major_than_search');
		$progress_minor_than = (int) get_parameter ('progress_minor_than_search');
		$owner = (string) get_parameter ("owner_search", $config["id_user"]);
		$show_100 = (int) get_parameter ("show_100_search");
		$id_language = (string) get_parameter ("id_language", "");
		$est_sale = (int) get_parameter ("est_sale_search", 0);
	}

	$params = "&est_sale_search=$est_sale&id_language_search=$id_language&search_text=$search_text&id_company_search=$id_company&start_date_search=$start_date&end_date_search=$end_date&country_search=$country&id_category_search=$id_category&progress_minor_than_search=$progress_minor_than&progress_major_than_search=$progress_major_than&show_100_search=$show_100&owner_search=$owner";

	$where_group = "";

	if ($show_100){
		$where_clause = "WHERE 1=1 $where_group ";
	} else {
		$where_clause = "WHERE progress < 100 $where_group ";
	}

	if ($est_sale != ""){
		$where_clause .= " AND estimated_sale >= $est_sale ";
	}

	if ($id_language != ""){
		$where_clause .= " AND id_language = '$id_language' ";
	}

	if ($owner != ""){
		$where_clause .= sprintf (' AND owner =  "%s"', $owner);
	}

	if ($search_text != "") {
		$where_clause .= sprintf (' AND fullname LIKE "%%%s%%" OR description LIKE "%%%s%%" OR company LIKE "%%%s%%" or email LIKE "%%%s%%"', $search_text, $search_text, $search_text, $search_text);
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
		$where_clause .= sprintf (' AND progress <= %d ', $progress_minor_than);
	}

	if ($progress_major_than) {
		$where_clause .= sprintf (' AND progress >= %d ', $progress_major_than);
	}

	if ($id_category) {
		$where_clause .= sprintf(' AND id_category = %d ', $id_category);
	}

	echo '<form id="lead_stats_form" action="index.php?sec=customers&sec2=operation/leads/lead_detail" method="post">';		

	$table->class = 'search-table';
	$table->style = array ();
	$table->style[0] = 'font-weight: bold;';
	$table->data = array ();
	$table->width = "99%";

	$table->data[0][0] = print_input_text ("search_text", $search_text, "", 15, 100, true, __('Search'));
	
	if ($config["lead_company_filter"] != ""){
		$sql2 = "SELECT id, name FROM tcompany WHERE id_company_role IN ('".$config["lead_company_filter"]."')";
	} else {
		$sql2 = "SELECT id, name FROM tcompany ";
	}
	$sql2 .=  " ORDER by name";


	$table->data[0][1] = print_input_text_extended ('owner_search', $owner, 'text-user', '', 15, 30, false, '',
			array(), true, '', __('Owner'))

		. print_help_tip (__("Type at least two characters to search"), true);

	$table->data[0][2] =  print_checkbox ("show_100_search", 1, $show_100, true, __("Show finished leads"));


	$table->data[1][0] = print_input_text ("country_search", $country, "", 21, 100, true, __('Country'));

	$table->data[1][1] = print_input_text ("est_sale_search", $est_sale, "", 21, 100, true, __('Estimated Sale'));
	
	$table->data[1][2] = print_submit_button (__('Search'), "search_btn", false, 'class="sub search"', true);
	$table->data[1][2] .= print_button(__('Export to CSV'), '', false, 'window.open(\'include/export_csv.php?export_csv_leads=1&where_clause=' . str_replace('"', "\'", $where_clause) . '\')', 'class="sub csv"', true);
	
	$table_advanced->class = 'search-table';
	$table_advanced->style = array ();
	$table_advanced->style[0] = 'font-weight: bold;';
	$table_advanced->data = array ();
	$table_advanced->width = "99%";
	
	$table_advanced->data[0][0] = print_select ($progress_values, 'progress_major_than_search', $progress_major_than, '', __("None"), 0, true, 0, false, __('Progress equal or above') );


	$table_advanced->data[0][1] = print_select ($progress_values, 'progress_minor_than_search', $progress_minor_than, '', __("None"), 0, true, 0, false, __('Progress equal or below') );


	$table_advanced->data[0][2] = combo_kb_products ($id_category, true, 'Product type', true);

	$table_advanced->data[0][3] = print_select_from_sql ($sql2, 'id_company_search', $id_company, '', __("None"), 0, true, false, true, __("Managed by"));
	
	$table_advanced->data[1][0] = print_input_text ("start_date_search", $start_date, "", 15, 100, true, __('Start date'));
	$table_advanced->data[1][1] = print_input_text ("end_date_search", $end_date, "", 15, 100, true, __('End date'));

	$table_advanced->data[1][2] = print_select_from_sql ('SELECT id_language, name FROM tlanguage ORDER BY name',
	'id_language', $id_language, '', __('Any'), '', true, false, false,
	__('Language'));
	
	$table->data['advanced'][2] = print_container('lead_search_advanced', __('Advanced search'), print_table($table_advanced, true), 'closed', true, false);
	$table->colspan['advanced'][2] = 3;
	
	print_table ($table);
	$table->data = array ();
	
	echo '</form>';

	$leads = crm_get_all_leads ($where_clause);

	if ($read && $enterprise) {
		$leads = crm_get_user_leads($config['id_user'], $leads);
	}

	$leads = print_array_pagination ($leads, "index.php?sec=customers&sec2=operation/leads/lead_detail$params", $offset);

	if ($leads !== false) {
		unset ($table);
		$table->width = "99%";
		$table->class = "listing";
		$table->data = array ();
		$table->size = array ();
		$table->style = array ();
		$table->rowstyle = array ();

		$table->style[0] = 'font-weight: bold';
		$table->head = array ();
		$table->head[0] = __('#');
		$table->head[1] = __('Product');
		$table->head[2] = __('Full name');
		$table->head[3] = __('Managed by');
		$table->head[4] = __('Progress');
		$table->head[5] = __('Est. Sale');
		$table->head[6] = __('L.');
		$table->head[7] = __('Country');
		$table->head[8] = __('Create')."<br>".__('Update');
		$table->head[9] = __('Op');
		$table->size[5] = '80px;';
		$table->size[4] = '130px;';
		$table->size[9] = '40px;';

		foreach ($leads as $lead) {
			$data = array ();
			
			// Detect is the lead is pretty old 
			// Stored in $config["lead_warning_time"] in days, need to calc in secs for this

			$config["lead_warning_time"]= 7; // days
			$config["lead_warning_time"] = $config["lead_warning_time"] * 86400;

			if (calendar_time_diff ($lead["modification"]) > $config["lead_warning_time"] ){
				$style = " background: #ffefef";
			} else {
				$style = "";
			}

			$data[0] = "<b><a href='index.php?sec=customers&sec2=operation/leads/lead_detail&id=".
				$lead['id']."'>#".$lead['id']."</a></b>";


			$data[1] = print_product_icon ($lead['id_category'], true);


 			$data[2] = "<a href='index.php?sec=customers&sec2=operation/leads/lead_detail&id=".
				$lead['id']."'>".$lead['fullname']."</a><br>";
				$data[2] .= "<span style='font-size: 9px'><i>".$lead["company"]."</i></span>";


			$data[3] = "<a href='index.php?sec=customers&sec2=operation/companies/company_detail&id=".$lead['id_company']."'>".get_db_value ('name', 'tcompany', 'id', $lead['id_company'])."</a>";
			if ($lead["owner"] != "")
				$data[3] .= "<br><i>" . $lead["owner"] . "</i>";

			$data[4] = translate_lead_progress ($lead['progress']) . " <i>(".$lead['progress']. "%)</i>";
			
			if ($lead['estimated_sale'] != 0)
				$data[5] = format_numeric($lead['estimated_sale']);
			else
				$data[5] = "--";
		
			$data[6] = "<img src='images/lang/".$lead["id_language"].".png'>"; 
	
			$data[7] =  ucfirst(strtolower($lead['country']));
			$data[8] = "<span style='font-size: 9px' title='". $lead['creation'] . "'>" . human_time_comparation ($lead['creation']) . "</span>";
			$data[8] .= "<br><span style='font-size: 9px'>". human_time_comparation ($lead['modification']). "</span>";

			if ($lead['owner'] == "")
				$data[9] = "<a href='index.php?sec=customers&sec2=operation/leads/lead_detail&id=".
				$lead['id']."&make_owner=1'><img src='images/award_star_silver_1.png' title='".__("Take ownership of this lead")."'></a>&nbsp;";
			else
				$data[9] = "";


			// Close that lead
			if (($config["id_user"] == $lead["owner"]) OR (dame_admin($config["id_user"]))) {
				$data[9] .= "<a href='index.php?sec=customers&sec2=operation/leads/lead_detail&id=".
				$lead['id']."&close=1'><img src='images/lock.png' title='".__("Close this lead")."'></a>";
		
			}

			// Show delete control if its owned by the user
			if (($config["id_user"] == $lead["owner"]) OR (dame_admin($config["id_user"]))) {
				$data[9] .= '&nbsp;<a href="index.php?sec=customers&
								sec2=operation/leads/lead_detail&
								delete=1&id='.$lead["id"].'&offset='.$offset.'"
								onClick="if (!confirm(\''.__('Are you sure?').'\'))
								return false;">
								<img src="images/cross.png"></a>';
			} else {
				if ($lead["owner"] == ""){
					// TODO. Check ACK for CRM Write here
					if ($manage_permission) {
						$data[9] .= '&nbsp;<a href="index.php?sec=customers&
										sec2=operation/leads/lead_detail&
										delete=1&id='.$lead["id"].'"
										onClick="if (!confirm(\''.__('Are you sure?').'\'))
										return false;">
										<img src="images/cross.png"></a>';
					}
				}
			}

			array_push ($table->data, $data);
			array_push ($table->rowstyle, $style);
		}
		print_table ($table);
	}
	
	if ($manage_permission) {
		echo '<form method="post" action="index.php?sec=customers&sec2=operation/leads/lead_detail">';
		echo '<div style="width: '.$table->width.'; text-align: right;">';
		print_submit_button (__('Create'), 'new_btn', false, 'class="sub create"');
		print_input_hidden ('new', 1);
		echo '</div>';
		echo '</form>';
	}
}

?>

<script type="text/javascript" src="include/js/jquery.ui.autocomplete.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
<script type="text/javascript" src="include/js/integria_date.js"></script>
<script type="text/javascript" src="include/js/jquery.validation.functions.js"></script>

<script type="text/javascript" >

add_ranged_datepicker ("#text-start_date_search", "#text-end_date_search", null);

// Form validation
trim_element_on_submit('#text-search_text');
trim_element_on_submit('#text-fullname');
trim_element_on_submit('#text-email');
trim_element_on_submit('#text-from');
trim_element_on_submit('#text-to');
trim_element_on_submit('#text-cco');
trim_element_on_submit('#text-contract_number');

if (<?php echo $id ?> > 0 || <?php echo json_encode($new) ?> == true) {
	validate_form("#lead_form");
	var rules, messages;

	// Rules: #text-fullname
	rules = {
		required: true,
		remote: {
			url: "ajax.php",
			type: "POST",
			data: {
				page: "include/ajax/remote_validations",
				search_existing_lead: 1,
				lead_name: function() { return $('#text-fullname').val() },
				lead_id: "<?php echo $id?>"
			}
		}
	};
	messages = {
		required: "<?php echo __('Name required')?>",
		remote: "<?php echo __('This name already exists')?>"
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
				search_existing_lead_email: 1,
				lead_email: function() { return $('#text-email').val() },
				lead_id: "<?php echo $id?>"
			}
		}
	};
	messages = {
		required: "<?php echo __('Email required')?>",
		email: "<?php echo __('Invalid email')?>",
		remote: "<?php echo __('This lead email already exists')?>"
	};
	add_validate_form_element_rules('#text-email', rules, messages);

	// Rules: #text-estimated_sale
	rules = { number: true };
	messages = { number: "<?php echo __('Invalid number')?>" };
	add_validate_form_element_rules('#text-estimated_sale', rules, messages);

	// Rules: #text-user
	rules = { required: true };
	messages = { required: "<?php echo __('Please, select an user')?>" };
	add_validate_form_element_rules('#text-user', rules, messages);

	// Rules: #id_language
	rules = { required: true };
	messages = { required: "<?php echo __('Please, select a language')?>" };
	add_validate_form_element_rules('#id_language', rules, messages);
}

$(document).ready (function () {
	
	$("#saved_searches").change(function() {
		$("#form-saved_searches").submit();
	});
	
	$("#textarea-description").TextAreaResizer ();
	
	var idUser = "<?php echo $config['id_user'] ?>";
	var onAutocompleteChange = function(event, ui) {
		$.ajax({
			type: "POST",
			url: "ajax.php",
			data: {
				page: "include/ajax/users",
				get_user_company: 1,
				id_user: $('#text-user').val()
			},
			success: function(data) {
				$('#id_company').find('option[value='+data+']').attr("selected",true);
			}
		});
	};
	bindAutocomplete ("#text-user", idUser, false, onAutocompleteChange);
	
	$("#checkbox-duplicated_leads").click(function () {
		changeAllowDuplicatedLeads ();
	});
});

function changeAction() {
	
	var f = document.forms.lead_stats_form;

	f.action = "index.php?sec=customers&sec2=operation/leads/lead_statistics<?php echo $params ?>";
	$("#lead_stats_form").submit();
}

// Add or remove the search of duplicated lead names and emails
function changeAllowDuplicatedLeads () {
	
	var checked = $("#checkbox-duplicated_leads").is(":checked");
	
	if (checked) {
		$('#text-fullname').rules("remove", "remote");
		$('#text-email').rules("remove", "remote");
	} else {
		$('#text-fullname').rules("add", { remote: {
				url: "ajax.php",
				type: "POST",
				data: {
					page: "include/ajax/remote_validations",
					search_existing_lead: 1,
					lead_name: function() { return $('#text-fullname').val() },
					lead_id: "<?php echo $id?>"
				}
			}
		});
		$('#text-email').rules("add", { remote: {
				url: "ajax.php",
				type: "POST",
				data: {
					page: "include/ajax/remote_validations",
					search_existing_lead_email: 1,
					lead_email: function() { return $('#text-email').val() },
					lead_id: "<?php echo $id?>"
				}
			}
		});
	}
}

</script>
