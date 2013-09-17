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

include_once('include/functions_crm.php');

$get_company_search = get_parameter ('get_company_search', 0);
$get_company_name = get_parameter ('get_company_name', 0);

if ($get_company_name) {
	$id_company = get_parameter('id_company');
	$name = crm_get_company_name($id_company);

	echo safe_output($name);
	return;
}

if ($get_company_search) {
	
	$search = get_parameter('search', 0);
	$search_text = (string) get_parameter ('search_text');	
	$search_role = (int) get_parameter ("search_role");
	$search_country = (string) get_parameter ("search_country");
	$search_manager = (string) get_parameter ("search_manager");
	$search_parent = get_parameter ("search_parent");
	$search_date_begin = (string) get_parameter('search_date_begin');
	$search_date_end = (string)get_parameter('search_date_end');
	$date = false;

	if ($search_date_end == 'undefined') {
		$search_date_end = '';
	}
	
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
	$table->colspan[1][0] = 2;
	
	$table->data[1][2] = print_input_text ('search_date_begin', $search_date_begin, '', 15, 20, true, __('Date from'));
	
	$table->data[1][3] = print_input_text ('search_date_end', $search_date_end, '', 15, 20, true, __('Date to'));
	
	$table->data['button'][0] = "<input type='button' class='sub search' onClick='javascript: loadParamsCompany(\".$search_text.\");' value='".__("Search")."''>";
	$table->colspan['button'][0] = 4;
	
	echo '<form id="form-company_search" method="post" action="index.php?sec=customers&sec2=operation/companies/company_detail">';
		print_table ($table);
	echo '</form>';
	
	$where_clause = '';
	
	if ($search) {

		if ($search_text != "") {
			$where_clause .= sprintf (' AND ( name LIKE "%%%s%%" OR country LIKE "%%%s%%")  ', $search_text, $search_text);
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
	}
	
	$params = "&search_manager=$search_manager&search_text=$search_text&search_role=$search_role&search_country=$search_country&search_parent=$search_parent&search_date_begin=$search_date_begin&search_date_end=$search_date_end";
	
	$companies = crm_get_companies_list($where_clause, $date);

	if ($companies !== false) {
		$table_list->width = "98%";
		$table_list->class = "listing";
		$table_list->data = array ();
		$table_list->style = array ();
		$table_list->colspan = array ();
		$table_list->head[0] = __('Company');
		$table_list->head[1] = __('Role');
		$table_list->head[2] = __('Estimated sale');
		$table_list->head[3] = __('Manager');
		$table_list->head[4] = __('Country');
		$table_list->head[5] = __('Last activity');
		$table_list->head[6] = __('Delete');
		
		foreach ($companies as $company) {

			$data = array ();
			
			$data[0] = "<a href='javascript:loadCompany(" . $company['id'] . ");'>".$company["name"]."</a>";
			$data[1] = get_db_value ('name', 'tcompany_role', 'id', $company["id_company_role"]);

			$sum_leads = get_db_sql ("SELECT COUNT(id) FROM tlead WHERE progress < 100 AND id_company = ".$company["id"]);
			if ($sum_leads > 0) {
				$data[2] .= " ($sum_leads) ";
				$data[2] .= get_db_sql ("SELECT SUM(estimated_sale) FROM tlead WHERE progress < 100 AND id_company = ".$company["id"]);
			} else {
				$data[2] = '';
			}

			$data[3] = $company["manager"];
			$data[4] = $company["country"];
			
			// get last activity date for this company record
			$last_activity = get_db_sql ("SELECT date FROM tcompany_activity WHERE id_company = ". $company["id"]);

			$data[5] = human_time_comparation ($last_activity);

			$data[6] ='<a href="index.php?sec=customers&
							sec2=operation/companies/company_detail'.$params.'&
							delete_company=1&id='.$company['id'].'"
							onClick="if (!confirm(\''.__('Are you sure?').'\'))
							return false;">
							<img src="images/cross.png"></a>';
			
			array_push ($table_list->data, $data);
		}
		print_table ($table_list);
	}
	
	return;
}


?>
