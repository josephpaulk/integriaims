<?php

// Integria IMS - http://integriaims.com
// ==================================================
// Copyright (c) 2008 Artica Soluciones Tecnologicas
// Copyright (c) 2008 Esteban Sanchez, estebans@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

include_once("include/functions_incidents.php");


$get_incidents_search = get_parameter('get_incidents_search', 0);
$get_incident_name = get_parameter('get_incident_name', 0);
$get_contact_search = get_parameter('get_contact_search',0);
$set_priority = get_parameter('set_priority', 0);
$set_resolution = get_parameter('set_resolution', 0);
$set_status = get_parameter('set_status', 0);
$set_owner = get_parameter('set_owner', 0);

if ($get_incidents_search) {
	
	$filter = array ();
	$filter['string'] = (string) get_parameter ('search_string');
	$filter['priority'] = (int) get_parameter ('search_priority', -1);
	$filter['id_group'] = (int) get_parameter ('search_id_group', 1);
	$filter['status'] = (int) get_parameter ('search_status', -10);
	$filter['id_product'] = (int) get_parameter ('search_id_product');
	$filter['id_company'] = (int) get_parameter ('search_id_company');
	$filter['id_inventory'] = (int) get_parameter ('search_id_inventory');
	$filter['serial_number'] = (string) get_parameter ('search_serial_number');
	$filter['sla_fired'] = (bool) get_parameter ('search_sla_fired');
	$filter['id_incident_type'] = (int) get_parameter ('search_id_incident_type');
	$filter['id_user'] = (string) get_parameter ('search_id_user', '');
	$filter['id_incident_type'] = (int) get_parameter ('search_id_incident_type');
	$filter['id_user'] = (string) get_parameter ('search_id_user', '');
	$filter['first_date'] = (string) get_parameter ('search_first_date');
	$filter['last_date'] = (string) get_parameter ('search_last_date');	
	
	$ajax = get_parameter("ajax");
	
	$filter_form = false;
	
	form_search_incident (false, $filter_form);
	
	incidents_search_result($filter,$ajax);
}

if ($get_incident_name) {
	$id = get_parameter("id");
	
	$name = get_db_value ("titulo", "tincidencia", "id_incidencia", $id);
	
	echo $name;
}

if ($get_contact_search) {

	include_once("include/functions_crm.php");

	$read = enterprise_hook('crm_check_user_profile', array($config['id_user'], 'cr'));

	if ($read !== ENTERPRISE_NOT_HOOK) {
		$enterprise = true;
	}

	if (!$read) {
		include ("general/noaccess.php");
		exit;
	}
	
	$search_text = (string) get_parameter ('search_text');
	$id_company = (int) get_parameter ('id_company', 0);
	
	//$where_clause = "WHERE 1=1 AND id_company " .get_filter_by_company_accessibility($config["id_user"]);
	$where_clause = "WHERE 1=1";
	if ($search_text != "") {
		$where_clause .= " AND (fullname LIKE '%$search_text%' OR email LIKE '%$search_text%'
					OR phone LIKE '%$search_text%' OR mobile LIKE '%$search_text%') ";
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
	
	$companies = crm_get_companies_list("", false, "", true);

	$table->data[0][1] = print_select ($companies, 'id_company', $id_company, '', 'All', 0, true, false, false, __('Company'));
	$table->data[0][2] = print_submit_button (__('Search'), "search_btn", false, 'class="sub search"', true);
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
			// Nameif (defined ('AJAX')) {
			$url = "javascript:loadContactEmail(\"".$contact['email']."\");";
			$data[0] = "<a href='".$url."'>".$contact['fullname']."</a>";
			$data[1] = "<a href='".$url."'>".get_db_value ('name', 'tcompany', 'id', $contact['id_company'])."</a>";
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
}

if ($set_priority) {
	$id_ticket = get_parameter('id_ticket');
	$values['prioridad'] = get_parameter ('id_priority');
	
	$result = db_process_sql_update('tincidencia', $values, array('id_incidencia'=>$id_ticket));
}

if ($set_resolution) {
	$id_ticket = get_parameter('id_ticket');
	$values['resolution'] = get_parameter ('id_resolution');
	
	$result = db_process_sql_update('tincidencia', $values, array('id_incidencia'=>$id_ticket));
}

if ($set_status) {
	$id_ticket = get_parameter('id_ticket');
	$values['estado'] = get_parameter ('id_status');
	
	$result = db_process_sql_update('tincidencia', $values, array('id_incidencia'=>$id_ticket));
}

if ($set_owner) {
	$id_ticket = get_parameter('id_ticket');
	$values['id_usuario'] = get_parameter ('id_user');
	
	$exists = get_db_value('id_usuario', 'tusuario', 'id_usuario', $values['id_usuario']);
	
	if ($exists) {
		$result = db_process_sql_update('tincidencia', $values, array('id_incidencia'=>$id_ticket));
	}
}
?>
