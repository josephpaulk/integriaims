<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2012 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

function crm_get_companies_list ($sql_search, $date = false) {
	
	if ($date) {
		$sql = "SELECT tcompany.* FROM tcompany, tcompany_activity
				WHERE tcompany.id = tcompany_activity.id_company $sql_search
				";
	} else {
		$sql = "SELECT tcompany.* FROM tcompany
				WHERE 1=1 $sql_search
				";
	}
			
	$companies = get_db_all_rows_sql($sql);
	
	if ($companies === false) {
		$companies = array();
	}
	
	return $companies;
}

function crm_get_company_name ($id_company) {
	
	$name = get_db_value('name', 'tcompany', 'id', $id_company);
	
	return $name;
}

//CHECK ACLS EXTERNAL USER
function crm_check_acl_external_user ($user, $id_company) {
	
	$user_data = get_db_row ('tusuario', 'id_usuario', $user);
	
	if ($user_data['id_company'] == $id_company) {
		return true;
	}
	return false;
}

?>
