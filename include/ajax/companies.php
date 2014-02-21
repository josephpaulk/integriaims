<?php
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008-2012 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

$search_companies = (bool) get_parameter ('search_companies');
$get_company_id = (bool) get_parameter ('get_company_id');

if ($search_companies) {
	require_once ('include/functions_db.php');
	require_once('include/functions_crm.php');

	$id_user = (string) get_parameter ('id_user', $config['id_user']);
	$string = (string) get_parameter ('term'); // term is what autocomplete plugin gives
	$type = (string) get_parameter ('type');
	$filter = (string) get_parameter ('filter'); // complements the main filter
	if ($filter) {
		$filter = safe_output($filter);
	}

	$where_clause = sprintf (' AND (tcompany.id = %d
									OR tcompany.name LIKE "%%%s%%"
									OR tcompany.country LIKE "%%%s%%"
									OR tcompany.manager LIKE "%%%s%%") ', $string, $string, $string, $string);
	$companies = crm_get_companies_list($where_clause . $filter, false, "ORDER BY name", true);

	if (!$companies) {
		return;
	}
	
	$result = array();
	
	foreach ($companies as $id => $name) {
		switch ($type) {
			case 'invoice':
				if (check_crm_acl('invoice', '', $id_user, $id)) {
					array_push($result, array("label" => safe_output($name), "value" => $id));
				}
				break;
			
			default:
				array_push($result, array("label" => safe_output($name), "value" => $id));
				break;
		}
	}
	
	echo json_encode($result);
	return;
}

if ($get_company_id) {
	require_once ('include/functions_db.php');
	require_once('include/functions_crm.php');

	$id_user = (string) get_parameter ('id_user', $config['id_user']);
	$company_name = (string) get_parameter ('company_name');
	
	$result = get_db_value("id", "tcompany", "name", $company_name);
	
	echo json_encode($result);
	return;
}

?>