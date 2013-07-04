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

// Checks if an invoice is locked. Returns 1 if is locked, 0 if not
// and false in case of error in the query.
function crm_is_invoice_locked ($id_invoice) {
	$locked = get_db_value('locked', 'tinvoice', 'id', $id_invoice);
	
	return $locked;
}

// Checks the id of the user that locked the invoice. Returns the id
// of the user in case of success or false in the case of the invoice
// does not exist or is not locked.
function crm_get_invoice_locked_id_user ($id_invoice) {
	if (is_invoice_locked ($id_invoice))
		return false;
	$user = get_db_value('locked_id_user', 'tinvoice', 'id', $id_invoice);
	
	return $user;
}

/**
 * Function to check if the user can lock the invoice.
 * NOT FULLY IMPLEMENTED IN OPENSOURCE version
 * Please visit http://integriaims.com for more information
*/
function crm_check_lock_permission ($id_user, $id_invoice) {
	
	$return = enterprise_hook ('crm_check_lock_permission_extra', array ($id_user, $id_invoice));
	if ($return !== ENTERPRISE_NOT_HOOK)
		return $return;
	return true;
}

// Changes the lock state of an invoice. Returns -1 if the user have
// not permission to do this or the new lock state in case of success.
function crm_change_invoice_lock ($id_user, $id_invoice) {
	
	if (check_lock_permission ($id_user, $id_invoice)) {
		
		$lock_status = crm_is_invoice_locked ($id_invoice);
		if ($lock_status === 1) {
			$values = array ('locked' => 0, 'locked_id_user' => NULL);
			$where = array ('id' => $id_invoice);
			if (process_sql_update ('tinvoice', $values, $where))
				return 0;
			return 1;
		} elseif ($lock_status === 0) {
			$values = array ('locked' => 1, 'locked_id_user' => $id_user);
			$where = array ('id' => $id_invoice);
			if (process_sql_update ('tinvoice', $values, $where))
				return 1;
			return 0;
		}
	}
	
	return -1;
}

?>
