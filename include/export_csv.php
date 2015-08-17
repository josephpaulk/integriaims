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
$export_csv_audit = get_parameter('export_csv_audit', 0);
$export_csv_tickets = get_parameter('export_csv_tickets', 0);

if ($export_csv_invoices) {
	
	$read = check_crm_acl ('company', 'cr');
	if (!$read) {
		exit;
	}

	$where_clause = get_parameter('where_clause');
	
	$rows = crm_get_all_invoices (clean_output($where_clause));
	if ($rows === false)
		return;
	
	$rows_aux = array();	
	foreach ($rows as $key=>$invoice) {
		$company_name = get_db_value('name', 'tcompany', 'id', $invoice['id_company']);
		$rows_aux[$key]['id'] = $invoice['id'];
		$rows_aux[$key]['id_user'] = $invoice['id_user'];
		$rows_aux[$key]['id_task'] = $invoice['id_task'];
		$rows_aux[$key]['id_company'] = $invoice['id_company'];
		$rows_aux[$key]['company'] = $company_name;
		$rows_aux[$key]['bill_id'] = $invoice['bill_id'];
		$rows_aux[$key]['concept1'] = $invoice['concept1'];
		$rows_aux[$key]['concept2'] = $invoice['concept2'];
		$rows_aux[$key]['concept3'] = $invoice['concept3'];
		$rows_aux[$key]['concept4'] = $invoice['concept4'];
		$rows_aux[$key]['concept5'] = $invoice['concept5'];
		$rows_aux[$key]['amount1'] = $invoice['amount1'];
		$rows_aux[$key]['amount2'] = $invoice['amount2'];
		$rows_aux[$key]['amount3'] = $invoice['amount3'];
		$rows_aux[$key]['amount4'] = $invoice['amount4'];
		$rows_aux[$key]['amount5'] = $invoice['amount5'];
		$rows_aux[$key]['total_amount'] = $invoice['amount1']+$invoice['amount2']+$invoice['amount3']+$invoice['amount4']+$invoice['amount5'];
		$rows_aux[$key]['tax'] = $invoice['tax'];
		$rows_aux[$key]['currency'] = $invoice['currency'];
		$rows_aux[$key]['description'] = $invoice['description'];
		$rows_aux[$key]['id_attachment'] = $invoice['id_attachment'];
		$rows_aux[$key]['locked'] = $invoice['locked'];
		$rows_aux[$key]['locked_id_user'] = $invoice['locked_id_user'];
		$rows_aux[$key]['invoice_create_date'] = $invoice['invoice_create_date'];
		$rows_aux[$key]['invoice_payment_date'] = $invoice['invoice_payment_date'];
		$rows_aux[$key]['status'] = $invoice['status'];
		$rows_aux[$key]['invoice_type'] = $invoice['invoice_type'];
		$rows_aux[$key]['reference'] = $invoice['reference'];
		$rows_aux[$key]['id_language'] = $invoice['id_language'];
		$rows_aux[$key]['internal_note'] = $invoice['internal_note'];
	}
	$rows = $rows_aux;
	
	$filename = clean_output ('invoices_export').'-'.date ("YmdHi");
	
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
	
	$filter = unserialize_in_temp($config["id_user"]);
	$where_clause = $filter["query"];

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
			$aux["object_type_name"] = get_db_value("name", "tobject_type", "id", $r["id_object_type"]);
			$sql = "SELECT * FROM tobject_type_field WHERE id_object_type=".$aux["id_object_type"];

			$all_fields = get_db_all_rows_sql($sql);

			if ($all_fields == false) {
				$all_fields = array();
			}
	
			foreach ($all_fields as $key=>$field) {
				$sql = "SELECT data FROM tobject_field_data WHERE id_object_type_field=".$field['id']. " AND id_inventory=".$aux["id"];
				$data = get_db_value_sql($sql);
				$aux[safe_output($field['label'])] = $data;
			}
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

if ($export_csv_audit) {
	
	$permission = give_acl ($config["id_user"], 0, "IM");
	if (!$permission) {
		exit;
	}
	
	$where_clause = clean_output (get_parameter('where_clause'));
	$date = get_parameter('date');	
	
	$filename = clean_output ('audit_export').'-'.date ("YmdHi");

	$sql = sprintf ('SELECT * FROM tsesion %s ORDER by utimestamp DESC', $where_clause);

	$rows = get_db_all_rows_sql ($sql);
	
	if ($rows === false)
		return;
}

if ($export_csv_tickets) {
	$filter = unserialize_in_temp($config["id_user"]);
	$rows = incidents_search_result ($filter, false, true, false, false, true, false, true);

	if ($rows === false)
		return;	

	$filename = clean_output ('tickets_export').'-'.date ("YmdHi");
}


if (empty($rows))
	die(__('Empty data'));

$csv_head = implode(';', array_keys($rows[0]));
$csv_lines = array();

$search = array();
// Delete \r !!!
$search[] = "&#x0d;";
$search[] = "\r";
// Delete \n !!!
$search[] = "&#x0a;";
$search[] = "\n";
// Delete " !!!
$search[] = '"';
// Delete ' !!!
$search[] = "'";

// Item / data
foreach ($rows as $row) {

	$line = array();

	foreach ($row as $value) {
		$cell = str_replace ($search, " ", safe_output($value));
		// Change ; !!	
		$cell = str_replace (";", ",", $cell);

		$line[] = $cell;
	}

	$line = implode(';', $line);
	
	if ($export_csv_invoices) {
		$line = str_replace (".", ",", $line);
	}

	$csv_lines[] = $line;
}

ob_end_clean();

// CSV Output
header ('Content-Type: text/csv; charset=UTF-8');
header ('Content-Disposition: attachment; filename="'.$filename.'.csv"');

// Header
echo $csv_head . "\n";

$standard_encoding = (bool) $config['csv_standard_encoding'];

// Item / data
foreach ($csv_lines as $line) {
	if ($standard_encoding)
		echo $line . "\n";
	else
		echo mb_convert_encoding($line, 'UTF-16LE', 'UTF-8') . "\n";
}

exit;	
?>
