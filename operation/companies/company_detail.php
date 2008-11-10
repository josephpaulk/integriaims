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

if (! give_acl ($config["id_user"], 0, "IM")) {
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access company section");
	require ("general/noaccess.php");
	exit;
}

$id = (bool) get_parameter ('id');
$new_company = (bool) get_parameter ('new_company');
$create_company = (bool) get_parameter ('create_company');
$update_company = (bool) get_parameter ('update_company');
$delete_company = (bool) get_parameter ('delete_company');

// CREATE
if ($create_company) {
	$name = (string) get_parameter ('name');
	$address = (string) get_parameter ('address');
	$fiscal_id = (string) get_parameter ('fiscal_id');
	$comments = (string) get_parameter ('comments');
	$id_company_role = (int) get_parameter ('id_company_role');

	$sql = sprintf ('INSERT INTO tcompany (name, address, comments, fiscal_id, id_company_role)
			 VALUES ("%s", "%s", "%s", "%s", %d)',
			 $name, $address, $comments, $fiscal_id, $id_company_role);

	$id = process_sql ($sql, 'insert_id');
	if ($id === false)
		echo "<h3 class='error'>".__('Company cannot be created')."</h3>";
	else {
		echo "<h3 class='suc'>".__('Company has been created successfully')."</h3>";
		insert_event ("COMPANY CREATED", $id, 0, $name);
	}
	$id = 0;
}

// UPDATE
if ($update_company) {
	$name = (string) get_parameter ('name');
	$address = (string) get_parameter ('address');
	$fiscal_id = (string) get_parameter ('fiscal_id');
	$comments = (string) get_parameter ('comments');
	$id_company_role = (int) get_parameter ('id_company_role');

	$sql = sprintf ('UPDATE tcompany SET comments = "%s", name = "%s",
		address = "%s", fiscal_id = "%s", id_company_role = %d WHERE id = %d',
		$comments, $name, $address,
		$fiscal_id, $id_company_role, $id);

	$result = mysql_query ($sql);
	if ($result === false)
		echo "<h3 class='error'>".__('Company cannot be updated')."</h3>";
	else {
		echo "<h3 class='suc'>".__('Company updated ok')."</h3>";
		insert_event ("COMPANY", $id, 0, $name);
	}
	$id = 0;
}

// DELETE
// ==================
if ($delete_company) { // if delete
	$id = (int) get_parameter ('delete');
	$name = get_db_value ('name', 'tcompany', 'id', $id);
	$sql= sprintf ('DELETE FROM tcompany WHERE id = %d', $id);
	process_sql ($sql);
	insert_event ("COMPANY DELETED", $id, 0, $name);
	echo "<h3 class='suc'>".__('Deleted successfully')."</h3>";
	$id = 0;
}


echo "<h2>".__('Company management')."</h2>";

// FORM (Update / Create)
if ($id || $new_company) {
	if ($new_company) {
		$name = "";
		$address = "";
		$comments = "";
		$id_company_role = "";
		$fiscal_id = "";
	} else {
		$company = get_db_row ('tcompany', 'id', $id);
		$name = $company['name'];
		$address = $company['address'];
		$comments = $company['comments'];
		$id_company_role = $company['id_company_role'];
		$fiscal_id = $company['fiscal_id'];
	}
	
	$table->width = '90%';
	$table->class = "databox";
	$table->data = array ();
	$table->colspan = array ();
	$table->colspan[0][0] = 2;
	$table->colspan[2][0] = 2;
	$table->colspan[3][0] = 2;
	
	$table->data[0][0] = print_input_text ('name', $name, '', 60, 100, true, __('Company name'));
	$table->data[1][0] = print_input_text ("fiscal_id", $fiscal_id, "", 10, 100, true, __('Fiscal ID'));
	$table->data[1][1] = print_select_from_sql ('SELECT id, name FROM tcompany_role ORDER BY name',
		'id_company_role', $id_company_role, '', 'Select', 0, true, false, false, __('Company Role'));

	$table->data[2][0] = print_textarea ('address', 3, 1, $address, '', true, __('Address'));
	$table->data[3][0] = print_textarea ("comments", 10, 1, $comments, '', true, __('Comments'));
	
	echo '<form method="post" action="index.php?sec=inventory&sec2=operation/companies/company_detail">';
	print_table ($table);
	echo '<div class="button" style="width: '.$table->width.'">';
	if ($id) {
		print_submit_button (__('Update'), "update_btn", false, 'class="sub upd"', false);
		print_input_hidden ('update_company', 1);
		print_input_hidden ('id', $id);
	} else {
		print_input_hidden ('create_company', 1);
		print_submit_button (__('Create'), "create_btn", false, 'class="sub next"', false);
	}
	echo "</div>";
	echo '</form>';
} else {
	$search_text = (string) get_parameter ('search_text');
	
	$where_clause = "";
	if ($search_text != "") {
		$where_clause = sprintf ('WHERE name LIKE "%%%s%%"', $search_text);
	}

	$table->width = '400px';
	$table->class = 'search-table';
	$table->style = array ();
	$table->style[0] = 'font-weight: bold;';
	$table->data = array ();
	$table->data[0][0] = __('Search');
	$table->data[0][1] = print_input_text ("search_text", $search_text, "", 25, 100, true);
	$table->data[0][2] = print_submit_button (__('Search'), "search_btn", false, 'class="sub search"', true);
	
	echo '<form method="post" action="index.php?sec=inventory&sec2=operation/companies/company_detail">';
	print_table ($table);
	echo '</form>';

	$sql = "SELECT * FROM tcompany $where_clause ORDER BY name";
	$companies = get_db_all_rows_sql ($sql);
	
	if ($companies !== false) {
		$table->width = "90%";
		$table->class = "listing";
		$table->data = array ();
		$table->style = array ();
		$table->style[0] = 'font-weight: bold';
		$table->colspan = array ();
		$table->head[0] = __('Company');
		$table->head[1] = __('Role');
		$table->head[2] = __('Contracts');
		$table->head[3] = __('Contacts');
		$table->head[4] = __('Delete');
		
		foreach ($companies as $company) {
			$data = array ();
			
			$data[0] = "<a href='index.php?sec=inventory&sec2=operation/companies/company_detail&id=".
				$company["id"]."'>".$company["name"]."</a>";
			$data[1] = get_db_sql("SELECT name FROM tcompany_role WHERE id = ".$company["id_company_role"]);
			$data[2] = '<a href="index.php?sec=inventory&sec2=operation/contracts/contract_detail&search_id_company='.
				$company['id'].'"><img src="images/maintab.gif"></a>';
			$data[3] = '<a href="index.php?sec=inventory&sec2=operation/contacts/contact_detail&id_company='.
				$company['id'].'"><img src="images/group.png"></a>';
			$data[4] ='<a href="index.php?sec=inventory&
						sec2=operation/companies/company_detail&
						delete_company=1&id='.$company['id'].'"
						onClick="if (!confirm(\''.__('Are you sure?').'\'))
						return false;">
						<img src="images/cross.png"></a>';
			array_push ($table->data, $data);
		}
		print_table ($table);
	}
	
	echo '<form method="post" action="index.php?sec=inventory&sec2=operation/companies/company_detail">';
	echo '<div class="button" style="width: '.$table->width.'">';
	print_submit_button (__('Create company'), 'new_btn', false, 'class="sub next"');
	print_input_hidden ('new_company', 1);
	echo '</div>';
	echo '</form>';
}

?>
