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

//session_start ();

require_once('functions.php');
require_once('functions_crm.php');

session_start();

require_once ("config.php");

global $config;


//connect to database
$conexion = mysql_connect($config['dbhost'], $config['dbuser'], $config['dbpass']);
$select_db = mysql_select_db ($config['dbname'], $conexion);

$export_csv_leads = get_parameter('export_csv_leads', 0);
$export_csv_companies = get_parameter('export_csv_companies', 0);
$export_csv_contacts = get_parameter('export_csv_contacts', 0);
$export_csv_contracts = get_parameter('export_csv_contracts', 0);
$export_csv_invoices = get_parameter('export_csv_invoices', 0);

if ($export_csv_invoices) {
	$read = enterprise_hook('crm_check_user_profile', array($config['id_user'], 'cr'));
	$enterprise = false;

	if ($read !== ENTERPRISE_NOT_HOOK) {
		$enterprise = true;
		if (!$read) {
			exit;
		}
	} 

	$where_clause = get_parameter('where_clause');
	
	$rows = crm_get_all_invoices (clean_output($where_clause));
	
	if ($read && $enterprise) {
		$rows = crm_get_user_invoices($config['id_user'], $rows);
	}
	
	$filename = clean_output ('invoices_export').'-'.date ("YmdHi");

	ob_end_clean();

	// CSV Output

	header ('Content-Disposition: attachment; filename="'.$filename.'.csv"');
	header ('Content-Type: text/css; charset=utf-8');

	$config['mysql_result_type'] = MYSQL_ASSOC;
	
	if ($rows === false)
		return;
}

if ($export_csv_contracts) {
	$read = enterprise_hook('crm_check_user_profile', array($config['id_user'], 'cr'));
	$enterprise = false;

	if ($read !== ENTERPRISE_NOT_HOOK) {
		$enterprise = true;
		if (!$read) {
			exit;
		}
	} 

	$where_clause = get_parameter('where_clause');
	
	$rows = crm_get_all_contracts (clean_output($where_clause));

	if ($read && $enterprise) {
		$rows = crm_get_user_contracts($config['id_user'], $rows);
	}
	
	$filename = clean_output ('contracts_export').'-'.date ("YmdHi");

	ob_end_clean();

	// CSV Output

	header ('Content-Disposition: attachment; filename="'.$filename.'.csv"');
	header ('Content-Type: text/css; charset=utf-8');

	$config['mysql_result_type'] = MYSQL_ASSOC;
	
	if ($rows === false)
		return;
}

if ($export_csv_contacts) {
	$read = enterprise_hook('crm_check_user_profile', array($config['id_user'], 'cr'));
	$enterprise = false;

	if ($read !== ENTERPRISE_NOT_HOOK) {
		$enterprise = true;
		if (!$read) {
			exit;
		}
	} 
	
	$where_clause = get_parameter('where_clause');

	$rows = crm_get_all_contacts (clean_output($where_clause));

	if ($read && $enterprise) {
		$rows = crm_get_user_contacts($config['id_user'], $rows);
	}
	
	$filename = clean_output ('contacts_export').'-'.date ("YmdHi");

	ob_end_clean();

	// CSV Output

	header ('Content-Disposition: attachment; filename="'.$filename.'.csv"');
	header ('Content-Type: text/css; charset=utf-8');

	$config['mysql_result_type'] = MYSQL_ASSOC;
	
	if ($rows === false)
		return;

}

if ($export_csv_companies) {
	$read = enterprise_hook('crm_check_user_profile', array($config['id_user'], 'cr'));
	$enterprise = false;

	if ($read !== ENTERPRISE_NOT_HOOK) {
		$enterprise = true;
		if (!$read) {
			exit;
		}
	} 
	
	$where_clause = get_parameter('where_clause');
	
	
	$filename = clean_output ('company_export').'-'.date ("YmdHi");

	ob_end_clean();

	// CSV Output
	header ('Content-Disposition: attachment; filename="'.$filename.'.csv"');
	header ('Content-Type: text/css; charset=utf-8');

	$config['mysql_result_type'] = MYSQL_ASSOC;
	
	$rows = crm_get_companies_list(clean_output($where_clause));
	
	if ($read && $enterprise) {
		$rows = crm_get_user_companies($config['id_user'], $rows);
	}
	
	if ($rows === false)
		return;
}

if ($export_csv_leads) {
	
	$read = true;
	$read_permission = true;
		
	$read = enterprise_hook('crm_check_user_profile', array($config['id_user'], 'cr'));
	$enterprise = false;

	if ($read !== ENTERPRISE_NOT_HOOK) {
		$enterprise = true;
		if (!$read) {
			//include ("general/noaccess.php");
			exit;
		}
	} 
	
	$id_company = (int) get_parameter ('id_company');
	
	if ($id_company != 0) {
		$read_permission = enterprise_hook ('crm_check_acl_other', array ($config['id_user'], $id));
		
		if ($read_permission === ENTERPRISE_NOT_HOOK) {
			$read_permission = true;		
		} else {
			
			$enterprise = true;
			
			if (!$read_permission) {
				//include ("general/noaccess.php");
				exit;
			}
		}
	}

	// Check if current user have access to this company.
	if ($id_company && ! $read_permission) {
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to lead export");
		require ("general/noaccess.php");
		exit;
	}

	$where_clause = get_parameter('where_clause');

	$filename = clean_output ('lead_export').'-'.date ("YmdHi");

	ob_end_clean();

	// CSV Output

	header ('Content-Disposition: attachment; filename="'.$filename.'.csv"');
	header ('Content-Type: text/css; charset=utf-8');

	$config['mysql_result_type'] = MYSQL_ASSOC;

	$rows = crm_get_all_leads (clean_output($where_clause));
	
	if ($read && $enterprise) {
		$rows = crm_get_user_leads($config['id_user'], $rows);
	}
	
	if ($rows === false)
		return;
}

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