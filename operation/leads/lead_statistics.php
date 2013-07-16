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
$id_company = (int) get_parameter ('id_company');
$start_date = (string) get_parameter ('start_date');
$end_date = (string) get_parameter ('end_date');
$country = (string) get_parameter ('country');
$id_category = (int) get_parameter ('product');
$progress_major_than = (int) get_parameter ('progress_major_than');
$progress_minor_than = (int) get_parameter ('progress_minor_than');
$owner = (string) get_parameter ("owner");
$show_100 = (int) get_parameter ("show_100");
$id_language = (string) get_parameter ("id_language", "");
$est_sale = (string) get_parameter ("est_sale", "");
	
echo "<div id='incident-search-content'>";
echo "<h2>".__('Search statistics');
echo "<div id='button-bar-title'>";
echo "<ul>";
echo "<li>";
echo "<a id='search_form_submit' href='index.php?sec=customers&sec2=operation/leads/lead_detail&search_text=$search_text&id_company=$id_company&start_date=$start_date&end_date=$end_date&country=$country&id_category=$id_category&progress_major_than=$progress_major_than&progress_minor_than=$progress_minor_than&owner=$owner&show_100=$show_100&id_language=$id_language&est_sale=$est_sale'>".print_image("images/go-previous.png", true, array("title" => __("Back to search")))."</a>";
echo "</li>";
echo "</ul>";
echo "</div>";
echo "</h2>";

$where_clause = '';

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

$table->class = 'blank';
$table->width = '98%';
$table->data = array ();
$table->style = array ();
$table->valign = array ();
$table->valign[0] = "top";
$table->valign[1] = "top";

//COUNTRIES
$leads_country = crm_get_total_leads_country($where_clause);

if ($read && $enterprise) {
	$leads_country = crm_get_user_leads($config['id_user'], $leads_country);
}
$leads_country = crm_get_data_lead_country_graph($leads_country);

$table->data[0][0] = "<br><h3>".__('Leads per country')."</h3>";
if ($leads_country !== false) {
	$table->data[0][0] .= pie3d_graph ($config['flash_charts'], $leads_country, 300, 150, __('others'), "", "", $config['font'], $config['fontsize']-1, $ttl);
} else {
	$table->data[0][0] .= __('No data to show');
}

//USERS
$leads_user = crm_get_total_leads_user($where_clause);

if ($read && $enterprise) {
	$leads_user = crm_get_user_leads($config['id_user'], $leads_user);
}
$leads_user = crm_get_data_lead_user_graph($leads_user);

$table->data[0][1] = "<br><h3>".__('Users per lead')."</h3>";
if ($leads_user !== false) {
	$table->data[0][1] .= pie3d_graph ($config['flash_charts'], $leads_user, 300, 150, __('others'), "", "", $config['font'], $config['fontsize']-1, $ttl);
} else {
	$table->data[0][1] .= __('No data to show');
}

//TOP 10 ESTIMATED SALES
$leads_sales = crm_get_total_sales_lead($where_clause);

if ($read && $enterprise) {
	$leads_sales = crm_get_user_leads($config['id_user'], $leads_sales);
}

$table->data[1][0] = "<br><h3>".__('Top 10 estimated sales')."</h3>";
if ($leads_sales !== false) {
	$table->data[1][0] .= print_table(crm_print_estimated_sales_leads($leads_sales), true);
} else {
	$table->data[1][0] .= __('No data to show');
}

//NEW LEADS
$leads_creation = crm_get_total_leads_creation($where_clause);

if ($read && $enterprise) {
	$leads_creation = crm_get_user_leads($config['id_user'], $leads_creation);
}

$leads_creation = crm_get_data_lead_creation_graph($leads_creation);

$table->data[1][1] = "<br><h3>".__('New leads')."</h3><br>";
if ($leads_creation !== false) {
	$table->data[1][1] .= area_graph(false, $leads_creation, 500, 300, "#2179B1", '', '', '');
} else {
	$table->data[1][1] .= __('No data to show');
}

print_table($table);

?>
