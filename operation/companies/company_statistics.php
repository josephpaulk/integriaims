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

include_once ('include/functions_crm.php');
enterprise_include('include/functions_crm.php');

$read = enterprise_hook('crm_check_user_profile', array($config['id_user'], 'cr'));
$write = enterprise_hook('crm_check_user_profile', array($config['id_user'], 'cw'));
$manage = enterprise_hook('crm_check_user_profile', array($config['id_user'], 'cm'));
$enterprise = false;

if ($result === ENTERPRISE_NOT_HOOK) {
	$read = true;
	$write = true;
	$manage = true;
	
} else {
	$enterprise = true;
	if (!$read) {
		include ("general/noaccess.php");
		exit;
	}
}

$search_text = (string) get_parameter ('search_text');	
$search_role = (int) get_parameter ("search_role");
$search_country = (string) get_parameter ("search_country");
$search_manager = (string) get_parameter ("search_manager");
$search_parent = get_parameter ("search_parent");
$search_date_begin = get_parameter ('search_date_begin');
$search_date_end = get_parameter ('search_date_end');
$order_by_activity = (string) get_parameter ("order_by_activity");
$order_by_company = (string) get_parameter ("order_by_company");

echo "<div id='incident-search-content'>";
echo "<h1>".__('Search statistics');
echo "<div id='button-bar-title'>";
echo "<ul>";
echo "<li>";
echo "<a id='search_form_submit' href='index.php?sec=customers&sec2=operation/companies/company_detail&search_text=$search_text&search_role=$search_role&search_country=$search_country&search_manager=$search_manager&search_parent=$search_parent&search_date_begin=$search_date_begin&search_date_end=$search_date_end&order_by_activity=$order_by_activity&order_by_company=$order_by_company'>".print_image("images/go-previous.png", true, array("title" => __("Back to search")))."</a>";
echo "</li>";
echo "</ul>";
echo "</div>";
echo "</h1>";

$where_clause = '';

if ($search_text != "") {
	$where_clause .= sprintf (' AND ( name LIKE "%%%s%%" OR country LIKE "%%%s%%")  ', $search_text, $search_text);
}

if ($search_role != 0) {
	$where_clause .= sprintf (' AND id_company_role = %d', $search_role);
}

if ($search_country != "") { 
	$where_clause .= sprintf (' AND country LIKE "%%s%%" ', $search_country);
}

if ($search_manager != "") {
	$where_clause .= sprintf (' AND manager = "%s" ', $search_manager);
}

if ($search_parent != 0) {
	$where_clause .= sprintf (' AND id_parent = %d ', $search_parent);
}

if ($search_date_begin != "") {
	$where_clause .= " AND `date` >= $search_date_begin";
	$date = true;
}

if ($search_date_end != "") {
	$where_clause .= " AND `date` <= $search_date_end";
	$date = true;
}


$table->class = 'blank';
$table->width = '99%';
$table->data = array ();
$table->style = array ();
$table->colspan = array ();
$table->valign = array ();
$table->colspan[0][0] = 2;
$table->colspan[0][1] = 2;
$table->colspan[0][2] = 2;
$table->colspan[1][0] = 3;
$table->colspan[1][1] = 3;
$table->valign[0] = "top";
$table->valign[1] = "top";

//COUNTRIES
$companies_country = crm_get_total_country($where_clause);

if ($read && $enterprise) {
	$companies_country = crm_get_total_country_acl($where_clause);
}

$companies_country = crm_get_data_country_graph($companies_country);

if ($companies_country !== false) {
	$companies_country_content = pie3d_graph ($config['flash_charts'], $companies_country, 300, 150, __('others'), "", "", $config['font'], $config['fontsize']-1, $ttl);
} else {
	$companies_country_content = __('No data to show');
}

$companies_country_content = '<br><div class="pie_frame">' . $companies_country_content . '</div>';

$table->data[0][0] = print_container('companies_per_county', __('Companies per country'), $companies_country_content, 'no', true, '10px');

//USERS
$companies_user = crm_get_total_user($where_clause);

if ($read && $enterprise) {
	$companies_user = crm_get_user_companies($config['id_user'], $companies_user);
}

$companies_user = crm_get_data_user_graph($companies_user);

if ($companies_user !== false) {
	$companies_user_content = pie3d_graph ($config['flash_charts'], $companies_user, 300, 150, __('others'), "", "", $config['font'], $config['fontsize']-1, $ttl);
} else {
	$companies_user_content = __('No data to show');
}

$companies_user_content = '<br><div class="pie_frame">' . $companies_user_content . '</div>';

$table->data[0][1] = print_container('companies_per_user', __('Users per company'), $companies_user_content, 'no', true, '10px');

// MANAGERS
if ($read && $enterprise) {
	$manager_companies = crm_get_total_managers_acl($where_clause);
} else {
	$manager_companies = crm_get_total_managers($where_clause);
}

$manager_companies = crm_get_data_managers_graph($manager_companies);

if ($owner_companies !== false) {
	$companies_per_manager = pie3d_graph ($config['flash_charts'], $manager_companies, 300, 150, __('others'), "", "", $config['font'], $config['fontsize']-1, $ttl);
} else {
	$companies_per_manager = __('No data to show');
}

$companies_per_manager = '<br><div class="pie_frame">' . $companies_per_manager . '</div>';

$table->data[0][2] = print_container('companies_per_manager', __('Companies per manager'), $companies_per_manager, 'no', true, '10px');

//TOP 10 INVOICING
$companies_invoincing = crm_get_total_invoiced($where_clause);

if ($read && $enterprise) {
	$companies_invoincing = crm_get_user_companies($config['id_user'], $companies_invoincing);
}

if ($companies_invoincing !== false) {
	$companies_invoincing_content = print_table(crm_print_most_invoicing_companies($companies_invoincing), true);
} else {
	$companies_invoincing_content = '<br><div>' . __('No data to show') . '</div>';
}

$table->data[1][0] = print_container('top_10_invoicing', __('Top 10 invoicing'), $companies_invoincing_content, 'no', true, '10px');

//TOP 10 ACTIVITY
$companies_activity = crm_get_total_activity($where_clause);

if ($read && $enterprise) {
	$companies_activity = crm_get_user_companies($config['id_user'], $companies_activity);
}

if ($companies_activity !== false) {
	$companies_activity_content = print_table(crm_print_most_activity_companies($companies_activity), true);
} else {
	$companies_activity_content = '<br><div>' . __('No data to show') . '</div>';
}

$table->data[1][1] = print_container('top_10_activity', __('Top 10 activity'), $companies_activity_content, 'no', true, '10px');

echo '<br>';
print_table($table);

?>
