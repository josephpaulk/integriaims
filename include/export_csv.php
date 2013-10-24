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

require_once ("config.php");

global $config;

$config["id_user"] = $_SESSION["id_usuario"];
$config['mysql_result_type'] = MYSQL_ASSOC;

require_once('functions.php');
require_once('functions_crm.php');


//connect to database
$conexion = mysql_connect($config['dbhost'], $config['dbuser'], $config['dbpass']);
$select_db = mysql_select_db ($config['dbname'], $conexion);

$export_csv_leads = get_parameter('export_csv_leads', 0);
$export_csv_companies = get_parameter('export_csv_companies', 0);
$export_csv_contacts = get_parameter('export_csv_contacts', 0);
$export_csv_contracts = get_parameter('export_csv_contracts', 0);
$export_csv_invoices = get_parameter('export_csv_invoices', 0);
$export_csv_inventory = get_parameter('export_csv_inventory', 0);

if ($export_csv_invoices) {
	
	$read = check_crm_acl ('company', 'cr');
	if (!$read) {
		exit;
	}

	$where_clause = get_parameter('where_clause');
	
	$rows = crm_get_all_invoices (clean_output($where_clause));
	
	$filename = clean_output ('invoices_export').'-'.date ("YmdHi");
	
	if ($rows === false)
		return;
}

if ($export_csv_contracts) {
	
	$read = check_crm_acl ('company', 'cr');
	if (!$read) {
		exit;
	}

	$where_clause = get_parameter('where_clause');
	
	$rows = crm_get_all_contracts (clean_output($where_clause));

	if ($read && $enterprise) {
		$rows = crm_get_user_contracts($config['id_user'], $rows);
	}
	
	$filename = clean_output ('contracts_export').'-'.date ("YmdHi");
	
	if ($rows === false)
		return;
}

if ($export_csv_contacts) {
	
	$read = check_crm_acl ('company', 'cr');
	if (!$read) {
		exit;
	}
	
	$where_clause = get_parameter('where_clause');

	$rows = crm_get_all_contacts (clean_output($where_clause));
	
	$filename = clean_output ('contacts_export').'-'.date ("YmdHi");
	
	if ($rows === false)
		return;

}

if ($export_csv_companies) {
	
	$read = check_crm_acl ('company', 'cr');
	if (!$read) {
		exit;
	}
	
	$where_clause = get_parameter('where_clause');
	$date = get_parameter('date');	
	
	$filename = clean_output ('company_export').'-'.date ("YmdHi");

	$rows = crm_get_companies_list(clean_output($where_clause), $date);
	
	if ($rows === false)
		return;
}

if ($export_csv_leads) {
	
	$read = check_crm_acl ('company', 'cr');
	if (!$read) {
		exit;
	}

	$where_clause = get_parameter('where_clause');

	$filename = clean_output ('lead_export').'-'.date ("YmdHi");

	$rows = crm_get_all_leads (clean_output($where_clause));
	
	if ($rows === false)
		return;
}

if ($export_csv_inventory) {
	$where_clause = get_parameter('where_clause');
	
	$rows = get_db_all_rows_sql(clean_output($where_clause));

	if ($rows === false)
		return;	

	$filename = clean_output ('inventory_export').'-'.date ("YmdHi");	

	$aux_rows = array();

	//Add additional information to raw csv
	foreach ($rows as $r) {
		$aux = array();

		$aux["id"] = $r["id"];
		$aux["name"] = $r["name"];

		$aux["id_object_type"] = $r["id_object_type"];
		$aux["object_type_name"] = "";
		
		if ($aux["id_object_type"]) {
			$aux["object_type_name"] = get_db_value("name", "tobject_type", "id", $r["id_object_type"]);;
		}

		$aux["description"] = $r["description"];

		$aux["id_contract"] = $r["id_contract"];
		$aux["contract_name"] = "";

		if ($aux["id_contract"]) {
			$aux["contract_name"] = get_db_value("name", "tcontract", "id", $r["id_contract"]);
		}

		$aux["id_manufacturer"] = $r["id_manufacturer"];
		$aux["manufacturer_name"] = "";

		if ($aux["id_manufacturer"]) {
			$aux["manufacturer_name"] = get_db_value("name", "tmanufacturer", "id", $r["id_manufacturer"]);
		}

		$aux["id_parent"] = $r["id_parent"];
		$aux["parent_name"] = "";

		if ($aux["id_parent"]) {
			$aux["parent_name"] = get_db_value("name", "tinventory", "id", $r["id_parent"]);
		}

		$aux["owner"] = $r["owner"];
		$aux["public"] = $r["public"];
		$aux["show_list"] = $r["show_list"];
		$aux["last_update"] = $r["last_update"];
		$aux["status"] = $r["status"];
		$aux["receipt_date"] = $r["receipt_date"];
		$aux["issue_date"] = $r["issue_date"];

		array_push($aux_rows, $aux);
	}

	$rows = $aux_rows;
}

ob_end_clean();

// CSV Output
header ('Content-Disposition: attachment; filename="'.$filename.'.csv"');
header ('Content-Type: text/css; charset=utf-8');	

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
