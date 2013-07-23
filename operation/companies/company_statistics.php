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

echo "<div id='incident-search-content'>";
echo "<h2>".__('Search statistics');
echo "<div id='button-bar-title'>";
echo "<ul>";
echo "<li>";
echo "<a id='search_form_submit' href='index.php?sec=customers&sec2=operation/companies/company_detail&search_text=$search_text&search_role=$search_role&search_country=$search_country&search_manager=$search_manager&search_parent=$search_parent&search_date_begin=$search_date_begin&search_date_end=$search_date_end'>".print_image("images/go-previous.png", true, array("title" => __("Back to search")))."</a>";
echo "</li>";
echo "</ul>";
echo "</div>";
echo "</h2>";

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
$table->width = '98%';
$table->data = array ();
$table->style = array ();
$table->valign = array ();
$table->valign[0] = "top";
$table->valign[1] = "top";

//COUNTRIES
$companies_country = crm_get_total_country($where_clause);

if ($read && $enterprise) {
	$companies_country = crm_get_total_country_acl($where_clause);
}

$companies_country = crm_get_data_country_graph($companies_country);

$table->data[0][0] = "<br><h3>".__('Companies per country')."</h3>";
if ($companies_country !== false) {
	$table->data[0][0] .= pie3d_graph ($config['flash_charts'], $companies_country, 300, 150, __('others'), "", "", $config['font'], $config['fontsize']-1, $ttl);
} else {
	$table->data[0][0] .= __('No data to show');
}

//USERS
$companies_user = crm_get_total_user($where_clause);

if ($read && $enterprise) {
	$companies_user = crm_get_user_companies($config['id_user'], $companies_user);
}

$companies_user = crm_get_data_user_graph($companies_user);

$table->data[0][1] = "<br><h3>".__('Users per company')."</h3>";
if ($companies_user !== false) {
	$table->data[0][1] .= pie3d_graph ($config['flash_charts'], $companies_user, 300, 150, __('others'), "", "", $config['font'], $config['fontsize']-1, $ttl);
} else {
	$table->data[0][1] .= __('No data to show');
}

//TOP 10 INVOICING
$companies_invoincing = crm_get_total_invoiced($where_clause);

if ($read && $enterprise) {
	$companies_invoincing = crm_get_user_companies($config['id_user'], $companies_invoincing);
}

$table->data[1][0] = "<br><h3>".__('Top 10 invoicing')."</h3>";
if ($companies_invoincing !== false) {
	$table->data[1][0] .= print_table(crm_print_most_invoicing_companies($companies_invoincing), true);
} else {
	$table->data[1][0] .= __('No data to show');
}

//TOP 10 ACTIVITY
$companies_activity = crm_get_total_activity($where_clause);

if ($read && $enterprise) {
	$companies_activity = crm_get_user_companies($config['id_user'], $companies_activity);
}

$table->data[1][1] = "<br><h3>".__('Top 10 activity')."</h3>";
if ($companies_activity !== false) {
	$table->data[1][1] .= print_table(crm_print_most_activity_companies($companies_activity), true);
} else {
	$table->data[1][1] .= __('No data to show');
}

print_table($table);

?>
