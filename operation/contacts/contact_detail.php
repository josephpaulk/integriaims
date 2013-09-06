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

$op = get_parameter("op", "details");

if ($id == 0) {

	echo "<h1>".__('Contact management')."</h1>";

}

if ($id != 0) {
	echo '<ul style="height: 30px;" class="ui-tabs-nav">';

	if ($op == "files")
                echo '<li class="ui-tabs-selected">';
        else   
                echo '<li class="ui-tabs">';
        echo '<a href="index.php?sec=customers&sec2=operation/contacts/contact_detail&id='.$id.'&op=files"><span>'.__("Files").'</span></a></li>';

        if ($op == "inventory")
                echo '<li class="ui-tabs-selected">';
        else
                echo '<li class="ui-tabs">';
        echo '<a href="index.php?sec=customers&sec2=operation/contacts/contact_detail&id='.$id.'&op=inventory"><span>'.__("Inventory").'</span></a></li>';

        if ($op == "incidents")
                echo '<li class="ui-tabs-selected">';
        else
                echo '<li class="ui-tabs">';
        echo '<a href="index.php?sec=customers&sec2=operation/contacts/contact_detail&id='.$id.'&op=incidents"><span>'.__("Incidents").'</span></a></li>';

	if ($op == "activity")
                echo '<li class="ui-tabs-selected">';
        else   
                echo '<li class="ui-tabs">';
        echo '<a href="index.php?sec=customers&sec2=operation/contacts/contact_detail&id='.$id.'&op=activity"><span>'.__("Activity").'</span></a></li>';

        if ($op == "details")
                echo '<li class="ui-tabs-selected">';
        else   
                echo '<li class="ui-tabs">';
        echo '<a href="index.php?sec=customers&sec2=operation/contacts/contact_detail&id='.$id.'&op=details"><span>'.__("Contact details").'</span></a></li>';

        echo '<li class="ui-tabs-title">';
        switch ($op) {
		case "files":
			echo strtoupper(__("Files"));
			break;
		case "activity":
			echo strtoupper(__("Activity"));
			break;
                case "details":
                        echo strtoupper(__('Contact details'));
                        break;
                case "incidents":
                        echo strtoupper(__('Incidents'));
                        break;
                case "inventory":
                        echo strtoupper(__('Inventory'));
                        break;
                default:
                        echo strtoupper(__('Details'));
        }

        echo '</li>';

        echo '</ul>';

        $contact = get_db_row ('tcompany_contact', 'id', $id);

        echo '<div class="under_tabs_info">' . sprintf(__('Contact: %s'), $contact['fullname']) . '</div>';

}

switch ($op) {
	case "incidents":
		include("contact_incidents.php");
		break;
	case "inventory":
		include("contact_inventory.php");
		break;
	case "details":
		include("contact_manage.php");
		break;
	case "files":
		include("contact_files.php");
		break;
	case "activity": 
		include("contact_activity.php");
		break;	
	default:
		include("contact_manage.php");
}


if ($id == 0 && !$new_contact) {
	if (!$read) {
		include ("general/noaccess.php");
		exit;
	}
	
	$search_text = (string) get_parameter ('search_text');
	$id_company = (int) get_parameter ('id_company', 0);
	
	$where_clause = "WHERE 1=1";
	if ($search_text != "") {
		$where_clause .= " AND (fullname LIKE '%$search_text%' OR email LIKE '%$search_text%' OR phone LIKE '%$search_text%' OR mobile LIKE '%$search_text%') ";
	}

	if ($id_company) {
		$where_clause .= sprintf (' AND id_company = %d', $id_company);
	}
	$params = "&search_text=$search_text&id_company=$id_company";

	$table->width = '99%';
	$table->class = 'search-table';
	$table->style = array ();
	$table->style[0] = 'font-weight: bold;';
	$table->data = array ();
	$table->data[0][0] = print_input_text ("search_text", $search_text, "", 15, 100, true, __('Search'));
	
        $companies = crm_get_all_companies(true);

        if ($read && $enterprise) {
        	$companies = crm_get_user_companies($config['id_user'], $companies);
        }

        $select_comp = array();
                
        foreach($companies as $id => $name) {
        	$select_comp[$id] = $name;
	}	

	$table->data[0][1] = print_select ($select_comp, 'id_company', $id_company, '', 'All', 0, true, false, false, __('Company'));
	$table->data[0][2] = print_submit_button (__('Search'), "search_btn", false, 'class="sub search"', true);
	// Delete new lines from the string
	$where_clause = str_replace(array("\r", "\n"), '', $where_clause);
	$table->data[0][3] = print_button(__('Export to CSV'), '', false, 'window.open(\'include/export_csv.php?export_csv_contacts=1&where_clause=' . str_replace("'", "\'", $where_clause) . '\')', 'class="sub csv"', true);
	echo '<form id="contact_search_form" method="post">';
	print_table ($table);
	echo '</form>';

	$contacts = crm_get_all_contacts ($where_clause);

	if ($read && $enterprise) {
		$contacts = crm_get_user_contacts($config['id_user'], $contacts);
	}

	$contacts = print_array_pagination ($contacts, "index.php?sec=customers&sec2=operation/contacts/contact_detail&params=$params", $offset);

	if ($contacts !== false) {
		unset ($table);
		$table->width = "99%";
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
		if($manage_permission) {
			$table->head[3] = __('Delete');
		}
		
		foreach ($contacts as $contact) {
			$data = array ();
			// Name
			$data[0] = "<a href='index.php?sec=customers&sec2=operation/contacts/contact_detail&id=".
				$contact['id']."'>".$contact['fullname']."</a>";
			$data[1] = "<a href='index.php?sec=customers&sec2=operation/companies/company_detail&id=".$contact['id_company']."'>".get_db_value ('name', 'tcompany', 'id', $contact['id_company'])."</a>";
			$data[2] = $contact['email'];
			if($manage_permission) {
				$data[3] = '<a href="index.php?sec=customers&
							sec2=operation/contacts/contact_detail&
							delete_contact=1&id='.$contact['id'].'&offset='.$offset.'"
							onClick="if (!confirm(\''.__('Are you sure?').'\'))
							return false;">
							<img src="images/cross.png"></a>';
			}	
			array_push ($table->data, $data);
		}
		print_table ($table);
	}	

	//Show create button only when contact list is displayed
	if($manage && !$id && !$new_contact) {
		echo '<form method="post" action="index.php?sec=customers&sec2=operation/contacts/contact_detail">';
		echo '<div style="width: '.$table->width.'; text-align: right;">';
		print_submit_button (__('Create'), 'new_btn', false, 'class="sub create"');
		print_input_hidden ('new_contact', 1);
		echo '</div>';
		echo '</form>';
	}
}
?>
