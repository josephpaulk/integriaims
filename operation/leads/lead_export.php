<?php
// Integria IMS - http://integriaims.com
// ==================================================
// Copyright (c) 2008-2011 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

check_login ();

$id_company = (int) get_parameter ('id_company');

// Check if current user have access to this company.
if ($id_company && ! check_company_acl ($config["id_user"], $id_company, "CR")) {
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to lead export");
	require ("general/noaccess.php");
	exit;
}

$search_text = (string) get_parameter ('search_text');
$start_date = (string) get_parameter ('start_date');
$end_date = (string) get_parameter ('end_date');
$country = (string) get_parameter ('country');
$id_category = (int) get_parameter ('product');
$progress_major_than = (int) get_parameter ('progress_major_than');
$progress_minor_than = (int) get_parameter ('progress_minor_than');
$owner = (string) get_parameter ("owner");
$show_100 = (int) get_parameter ("show_100");

$params = "&search_text=$search_text&id_company=$id_company&start_date=$start_date&end_date=$end_date&country=$country&id_category=$id_category&progress_minor_than=$progress_minor_than&progress_major_than=$progress_major_than&show_100=$show_100&owner=$owner";


if ($show_100){
	$where_clause = "WHERE 1=1 $where_group ";
} else {
	$where_clause = "WHERE progress < 100 $where_group ";
}


if ($owner != ""){
	$where_clause .= sprintf (' AND owner =  "%s"', $owner);
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
	$where_clause .= sprintf (' AND progress <= %d ', $progress_minor_than);
}

if ($progress_major_than) {
	$where_clause .= sprintf (' AND progress >= %d ', $progress_major_than);
}

if ($id_category) {
	$where_clause .= sprintf(' AND id_category = %d ', $id_category);
}


$filename = clean_output ('lead_export').'-'.date ("YmdHi");

ob_end_clean();

// CSV Output

header ('Content-Disposition: attachment; filename="'.$filename.'.csv"');
header ('Content-Type: text/css; charset=utf-8');

$config['mysql_result_type'] = MYSQL_ASSOC;

$sql = "SELECT tlead.id, owner, id_company as Managed_by, fullname, email, tlead.phone, tlead.mobile, position, company, tlead.country, tlead.description, tlead.creation, tlead.progress, estimated_sale, modification, id_category FROM tlead $where_clause  ORDER BY modification  DESC";

$rows = get_db_all_rows_sql (clean_output ($sql));
if ($rows === false)
	return;

// Header
echo safe_output (implode (',', array_keys ($rows[0])))."\n";

// Item / data
foreach ($rows as $row) {

	// Delete \r !!!
	$row = str_replace ("&#x0d;", " ",  $row);

	// Delete \n !!
	$row = str_replace ("&#x0a;", " ",  $row);

	// Delete , !!	
	$row = str_replace (",", " ",  $row);

	$buffer = safe_output (implode (',', $row))."\n";
	// Delete " !!!

	$buffer = str_replace ('"', " ",  $buffer);

	// Delete ' !!!
	$buffer = str_replace ("'", " ",  $buffer);

	echo $buffer;
}
exit;

?>
