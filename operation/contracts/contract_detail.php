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

check_login();

if (! give_acl ($config["id_user"], 0, "IM")) {
		audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation","Trying to create a contract");
		require ("general/noaccess.php");
		exit;
}

$id = (int) get_parameter ('id');
$get_sla = (bool) get_parameter ('get_sla');
$new_contract = (bool) get_parameter ('new_contract');
$create_contract = (bool) get_parameter ('create_contract');
$update_contract = (bool) get_parameter ('update_contract');
$delete_contract = (bool) get_parameter ('delete_contract');

if ($get_sla) {
	$sla = get_contract_sla ($id, false);
	
	if (defined ('AJAX')) {
		echo json_encode ($sla);
		return;
	}
}

// CREATE
if ($create_contract) {
	$name = (string) get_parameter ('name');
	$id_company = (int) get_parameter ('id_company');
	$description = (string) get_parameter ('description');
	$date_begin = (string) get_parameter ('date_begin');
	$date_end = (string) get_parameter ('date_end');
	$id_sla = (int) get_parameter ('id_sla');
	$id_group = (int) get_parameter ('id_group');

	if ($id_group < 2){
		echo "<h3 class='error'>".__('You must specify a valid group')."</h3>";
	} else {
		$sql = sprintf ('INSERT INTO tcontract (name, description, date_begin,
			date_end, id_company, id_sla, id_group)
			VALUE ("%s", "%s", "%s", "%s", %d, %d, %d)',
			$name, $description, $date_begin, $date_end,
			$id_company, $id_sla, $id_group);
	
		$id = process_sql ($sql, 'insert_id');
		if ($id === false)
			echo '<h3 class="error">'.__('Contract cannot be created').'</h3>';
		else {
			echo '<h3 class="suc">'.__('Contract has been created successfully').'</h3>';
			insert_event ("CONTRACT CREATED", $id, 0, $name);
		}
		$id = 0;
	}
}

// UPDATE
if ($update_contract) { // if modified any parameter
	$name = (string) get_parameter ('name');
	$id_company = (int) get_parameter ('id_company');
	$description = (string) get_parameter ('description');
	$date_begin = (string) get_parameter ('date_begin');
	$date_end = (string) get_parameter ('date_end');
	$id_sla = (int) get_parameter ('id_sla');
	$id_group = (int) get_parameter ('id_group');

	if ($id_group < 2) {
		echo "<h3 class='error'>".__('You must specify a valid group')."</h3>";
	} else {
		$sql = sprintf ('UPDATE tcontract SET id_sla = %d, id_group = %d,
			description = "%s", name = "%s", date_begin = "%s",
			date_end = "%s", id_company = %d WHERE id = %d',
			$id_sla, $id_group, $description, $name, $date_begin,
			$date_end, $id_company, $id);
	
		$result = process_sql ($sql);
		if ($result === false) {
			echo "<h3 class='error'>".__('Contract cannot be updated')."</h3>";
		} else {
			echo "<h3 class='suc'>".__('Contract updated ok')."</h3>";
			insert_event ("CONTRACT UPDATED", $id, 0, $name);
		}
	}
	$id = 0;
}

// DELETE
if ($delete_contract) {
	$name = get_db_value ('name', 'tcontract', 'id', $id);
	$sql = sprintf ('DELETE FROM tcontract WHERE id = %d', $id);
	process_sql ($sql);
	insert_event ("CONTRACT DELETED", $id, 0, "$name");
	echo "<h3 class='suc'>".__('Deleted successfully')."</h3>";
	$id = 0;
}

echo "<h2>".__('Contract management')."</h2>";

// FORM (Update / Create)
if ($id | $new_contract) {
	if ($new_contract) {
		$name = "";
		$date_begin = date('Y-m-d');
		$date_end = $date_begin;
		$id_company = "";
		$id_group = "2";
		$id_sla = "";
		$description = "";
	} else {
		$contract = get_db_row ("tcontract", "id", $id);
		$name = $contract["name"];
		$id_company = $contract["id_company"];
		$date_begin = $contract["date_begin"];
		$id_group = $contract["id_group"];
		$date_end   = $contract["date_end"];
		$description = $contract["description"];
		$id_sla = $contract["id_sla"];
	}
	
	$table->width = '740px';
	$table->class = 'databox';
	$table->colspan = array ();
	$table->colspan[3][0] = 2;
	$table->data = array ();
	$table->data[0][0] = print_input_text ('name', $name, '', 40, 100, true, __('Contract name'));
	$table->data[0][1] = print_select_from_sql ('SELECT id_grupo, nombre FROM tgrupo WHERE id_grupo > 1 ORDER BY nombre',
		'id_group', $id_group, '', '', '', true, false, false, __('Group'));
	$table->data[1][0] = print_input_text ('date_begin', $date_begin, '', 15, 20, true, __('Begin date'));
	$table->data[1][1] = print_input_text ('date_end', $date_end, '', 15, 20, true, __('End date'));
	$table->data[2][0] = print_select_from_sql ('SELECT id, name FROM tcompany ORDER BY name',
		'id_company', $id_company, '', '', '', true, false, false, __('Company'));
	$table->data[2][1] = print_select_from_sql ('SELECT id, name FROM tsla ORDER BY name',
		'id_sla', $id_sla, '', '', '', true, false, false, __('SLA'));
	$table->data[3][0] = print_textarea ("description", 14, 1, $description, '', true, __('Description'));
	
	echo '<form method="post" action="index.php?sec=inventory&sec2=operation/contracts/contract_detail">';
	print_table ($table);
	echo '<div class="button" style="width: '.$table->width.'">';
	if ($id) {
		print_submit_button (__('Update'), 'update_btn', false, 'class="sub upd"');
		print_input_hidden ('id', $id);
		print_input_hidden ('update_contract', 1);
	} else {
		print_submit_button (__('Create'), 'create_btn', false, 'class="sub next"');
		print_input_hidden ('create_contract', 1);
	}
	echo "</div>";
	echo "</form>";
} else {
	$search_text = (string) get_parameter ('search_text');
	$id_company = (int) get_parameter ('id_company');
	
	$where_clause = "WHERE 1=1";
	if ($search_text != "") {
		$where_clause = sprintf ('AND name LIKE "%%%s%%"', $search_text);
	}
	if ($id_company) {
		$where_clause .= sprintf (' AND id_company = %d', $id_company);
	}
	
	$table->width = '400px';
	$table->class = 'search-table';
	$table->style = array ();
	$table->style[0] = 'font-weight: bold;';
	$table->data = array ();
	$table->data[0][0] = print_input_text ("search_text", $search_text, "", 15, 100, true, __('Search'));
	$table->data[0][1] = print_select (get_companies (), 'id_company', $id_company, '', 'All', 0, true, false, false, __('Company'));
	$table->data[0][2] = print_submit_button (__('Search'), "search_btn", false, 'class="sub search"', true);;
	
	echo '<form method="post" action="index.php?sec=inventory&sec2=operation/contracts/contract_detail">';
	print_table ($table);
	echo '</form>';
	
	$sql = "SELECT * FROM tcontract $where_clause ORDER BY name, id_company";
	$contracts = get_db_all_rows_sql ($sql);
	if ($contracts !== false) {
		
		$table->width = "720px";
		$table->class = "listing";
		$table->cellspacing = 0;
		$table->cellpadding = 0;
		$table->tablealign="left";
		$table->data = array ();
		$table->size = array ();
		$table->style = array ();
		$table->style[0] = 'font-weight: bold';
		$table->colspan = array ();
		$table->head[0] = __('Name');
		$table->head[1] = __('Company');
		$table->head[2] = __('SLA');
		$table->head[3] = __('Group');
		$table->head[4] = __('Begin');
		$table->head[5] = __('End');
		$table->head[6] = __('Delete');
		$counter = 0;
		
		foreach ($contracts as $contract) {
			if (! give_acl ($config["id_user"], $contract["id_group"], "IR"))
				continue;
			$data = array ();
			
			$data[0] = "<a href='index.php?sec=inventory&sec2=operation/contracts/contract_detail&id="
				.$contract["id"]."'>".$contract["name"]."</a>";
			$data[1] = get_db_value ('name', 'tcompany', 'id', $contract["id_company"]);
			$data[2] = get_db_value ('name', 'tsla', 'id', $contract["id_sla"]);
			$data[3] = get_db_value ('nombre', 'tgrupo', 'id_grupo', $contract["id_group"]);
			$data[4] = $contract["date_begin"];
			$data[5] = $contract["date_end"] != '0000-00-00' ? $contract["date_end"] : "-";

			// Delete
			$data[6] = '<a href=index.php?sec=inventory&
						sec2=operation/contracts/contract_detail&
						delete_contract=1&id='.$contract["id"].'"
						onClick="if (!confirm(\''.__('are_you_sure').'\'))
						return false;">
						<img src="images/cross.png"></a>';
			
			array_push ($table->data, $data);
		}	
		print_table ($table);
	}
	
	echo '<form method="post" action="index.php?sec=inventory&sec2=operation/contracts/contract_detail">';
	echo '<div class="button" style="width: '.$table->width.'">';
	print_submit_button (__('Create contract'), 'new_btn', false, 'class="sub next"');
	print_input_hidden ('new_contract', 1);
	echo '</div>';
	echo '</form>';
}

?>
