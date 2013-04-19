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

if (! give_acl ($config["id_user"], 0, "VR")) {
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to read a contract");
	require ("general/noaccess.php");
	exit;
}

$manager = give_acl ($config["id_user"], 0, "VM");

$id = (int) get_parameter ('id');
$id_group = get_db_value ('id_group', 'tcontract', 'id', $id);
$get_sla = (bool) get_parameter ('get_sla');
$get_company_name = (bool) get_parameter ('get_company_name');
$new_contract = (bool) get_parameter ('new_contract');
$create_contract = (bool) get_parameter ('create_contract');
$update_contract = (bool) get_parameter ('update_contract');
$delete_contract = (bool) get_parameter ('delete_contract');
$get_group_combo = (bool) get_parameter('get_group_combo');

if ($get_sla) {
	$sla = get_contract_sla ($id, false);
	
	if (defined ('AJAX')) {
		echo json_encode ($sla);
		return;
	}
}

if ($get_company_name) {
	$company = get_contract_company ($id, true);

	if (defined ('AJAX')) {
		echo json_encode (reset($company));
		return;
	}
}

if ($get_group_combo) {
	$group = get_parameter("group");
	$ret = print_select_from_sql ('SELECT id, name FROM tcompany WHERE id_grupo = '.$group.' ORDER BY name',
			'id_company', false, '', '', '', true, false, false);	
			
	echo $ret;
	return;
}

// CREATE
if ($create_contract) {

	$id_group = (int) get_parameter ('id_group');
	if (! give_acl ($config["id_user"], $id_group, "VW")) {
	        audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create a contract");
	        require ("general/noaccess.php");
	        exit;
	}

	$name = (string) get_parameter ('name');
	$contract_number = (string) get_parameter ('contract_number');
	$id_company = (int) get_parameter ('id_company');
	$description = (string) get_parameter ('description');
	$date_begin = (string) get_parameter ('date_begin');
	$date_end = (string) get_parameter ('date_end');
	$private = (int) get_parameter ('private');

	
	$sql = sprintf ('INSERT INTO tcontract (name, contract_number, description, date_begin,
		date_end, id_company, private)
		VALUE ("%s", "%s", "%s", "%s", "%s", %d, %d)',
		$name, $contract_number, $description, $date_begin, $date_end,
		$id_company, $private);

	$id = process_sql ($sql, 'insert_id');
	if ($id === false)
		echo '<h3 class="error">'.__('Could not be created').'</h3>';
	else {
		echo '<h3 class="suc">'.__('Successfully created').'</h3>';
		audit_db ($config['id_user'], $REMOTE_ADDR, "Contract created", "Contract named '$name' has been added");
	}
	$id = 0;
}

// UPDATE
if ($update_contract) { // if modified any parameter
	$id_group = (int) get_parameter ('id_group');
	if (! give_acl ($config["id_user"], $id_group, "VW")) {
			audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to update a contract");
			require ("general/noaccess.php");
			exit;
	}

	$name = (string) get_parameter ('name');
	$contract_number = (string) get_parameter ('contract_number');
	$id_company = (int) get_parameter ('id_company');
	$description = (string) get_parameter ('description');
	$date_begin = (string) get_parameter ('date_begin');
	$date_end = (string) get_parameter ('date_end');
	$private = (int) get_parameter ('private');


	$sql = sprintf ('UPDATE tcontract SET contract_number = "%s",
		description = "%s", name = "%s", date_begin = "%s",
		date_end = "%s", id_company = %d, private = %d WHERE id = %d',
		$contract_number, $description, $name, $date_begin,
		$date_end, $id_company, $private, $id);
	
	$result = process_sql ($sql);
	if ($result === false) {
		echo "<h3 class='error'>".__('Could not be updated')."</h3>";
	} else {
		echo "<h3 class='suc'>".__('Successfully updated')."</h3>";
		audit_db ($config['id_user'], $REMOTE_ADDR, "Contract updated", "Contract named '$name' has been updated");
	}

	$id = 0;
}

// DELETE
if ($delete_contract) {
	$name = get_db_value ('name', 'tcontract', 'id', $id);

	if (! give_acl ($config["id_user"], $id_group, "VM")) {
			audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to delete a contract");
			require ("general/noaccess.php");
			exit;
	}

	$sql = sprintf ('DELETE FROM tcontract WHERE id = %d', $id);
	process_sql ($sql);
	audit_db ($config['id_user'], $REMOTE_ADDR, "Contract deleted", "Contract named '$name' has been deleted");
	echo "<h3 class='suc'>".__('Successfully deleted')."</h3>";
	$id = 0;
}

echo "<h2>".__('Contract management')."</h2>";

// FORM (Update / Create)
if ($id | $new_contract) {
	if ($new_contract) {
		if(!$manager) {
			audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create a contract");
			require ("general/noaccess.php");
			exit;
		}
		$name = "";
		$contract_number = "";
		$date_begin = date('Y-m-d');
		$date_end = $date_begin;
		$id_company = get_parameter("id_company", 0);
		$id_group = "1";
		$id_sla = "";
		$description = "";
		$private = 0;
	} else {
        if (!give_acl ($config["id_user"], $id_group, "VR")) {
			audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to update a contract");
			require ("general/noaccess.php");
			exit;
		}
		$contract = get_db_row ("tcontract", "id", $id);
		$name = $contract["name"];
		$contract_number = $contract["contract_number"];
		$id_company = $contract["id_company"];
		$date_begin = $contract["date_begin"];
		$id_group = $contract["id_group"];
		$date_end   = $contract["date_end"];
		$description = $contract["description"];
		$id_sla = $contract["id_sla"];
		$private = $contract["private"];
	}
	
	$table->width = '800px';
	$table->class = 'databox';
	$table->colspan = array ();
	$table->colspan[4][0] = 2;
	$table->data = array ();
	
	if (give_acl ($config["id_user"], $id_group, "VW")) {
		$table->data[0][0] = print_input_text ('name', $name, '', 40, 100, true, __('Contract name'));
		$table->data[0][1] = print_checkbox ('private', '1', $private, true, __('Private')). print_help_tip (__("Private contracts are visible only by users of the same company"), true);
		$table->data[1][0] = print_input_text ('contract_number', $contract_number, '', 40, 100, true, __('Contract number'));
		
			
		$table->data[2][0] = print_input_text ('date_begin', $date_begin, '', 15, 20, true, __('Begin date'));
		$table->data[2][1] = print_input_text ('date_end', $date_end, '', 15, 20, true, __('End date'));

		// TODO: ACL check for company listing.

		$table->data[3][0] = print_select_from_sql ('SELECT id, name FROM tcompany ORDER BY name',
			'id_company', $id_company, '', '', '', true, false, false, __('Company'));
			
		$table->data[3][0] .= "&nbsp;&nbsp;<a href='index.php?sec=customers&sec2=operation/companies/company_detail&id=$id_company'>";
		$table->data[3][0] .= "<img src='images/company.png'></a>";
		
		// I think we should delete this, not used anymore.		

		// $table->data[3][1] = print_select_from_sql ('SELECT id, name FROM tsla ORDER BY name', 'id_sla', $id_sla, '', '', '', true, false, false, __('SLA'));

		$table->data[4][0] = print_textarea ("description", 14, 1, $description, '', true, __('Description'));
	}
	else {
		$table->data[0][0] = "<b>".__('Contract name')."</b><br>$name<br>";
		if($contract_number == '') {
			$contract_number = '<i>-'.__('Empty').'-</i>';
		}		
		$table->data[1][0] = "<b>".__('Contract number')."</b><br>$contract_number<br>";	
		
		$group_name = get_db_value('nombre','tgrupo','id_grupo',$id_group);
		
		
		$table->data[2][0] = "<b>".__('Begin date')."</b><br>$date_begin<br>";
		$table->data[2][1] = "<b>".__('End date')."</b><br>$date_end<br>";

		$company_name = get_db_value('name','tcompany','id',$id_company);

		$table->data[3][0] = "<b>".__('Company')."</b><br>$company_name";
			
		$table->data[3][0] .= "&nbsp;&nbsp;<a href='index.php?sec=customers&sec2=operation/companies/company_detail&id=$id_company'>";
		$table->data[3][0] .= "<img src='images/company.png'></a>";
		
		$sla_name = get_db_value('name','tsla','id',$id_sla);

		$table->data[3][1] = "<b>".__('SLA')."</b><br>$sla_name<br>";
		if($description == '') {
			$description = '<i>-'.__('Empty').'-</i>';
		}		
		$table->data[3][1] = "<b>".__('Description')."</b><br>$description<br>";
	}
	
	echo '<form id="contract_form" method="post" action="index.php?sec=customers&sec2=operation/contracts/contract_detail" onsubmit="return validate_contract_form()">';
	print_table ($table);
	
	if (($id && give_acl ($config["id_user"], $id_group, "VW")) || (!$id && give_acl ($config["id_user"], $id_group, "VM"))) {
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
	}
	echo "</form>";
} else {
	
	// Contract listing
	$search_text = (string) get_parameter ('search_text');
	$search_company_role = (int) get_parameter ('search_company_role');
	$search_date_end = get_parameter ('search_date_end');
	$search_date_begin = get_parameter ('search_date_begin');
	$search_date_begin_beginning = get_parameter ('search_date_begin_beginning');
	$search_date_end_beginning = get_parameter ('search_date_end_beginning');

	$search_params = "search_text=$search_text&search_company_role=$search_company_role&search_date_end=$search_date_end&search_date_begin=$search_date_begin&search_date_begin_beginning=$search_date_begin_beginning&search_date_end_beginning=$search_date_end_beginning";

	$where_clause = " 1 = 1 ";
	
	if ($search_text != "") {
		$where_clause .= sprintf ('AND (id_company IN (SELECT id FROM tcompany WHERE name LIKE "%%%s%%") OR 
			name LIKE "%%%s%%" OR 
			contract_number LIKE "%%%s%%")', $search_text, $search_text, $search_text);
	}
	
	if ($search_company_role) {
		$where_clause .= sprintf (' AND id_company IN (SELECT id FROM tcompany WHERE id_company_role = %d)', $search_company_role);
	}
	
	if ($search_date_end != "") {
		$where_clause .= sprintf (' AND date_end <= "%s"', $search_date_end);
	}
	
	if ($search_date_begin != "") {
		$where_clause .= sprintf (' AND date_end >= "%s"', $search_date_begin);
	}
		
	if ($search_date_end_beginning != "") {
		$where_clause .= sprintf (' AND date_begin <= "%s"', $search_date_end_beginning);
	}
	
	if ($search_date_begin_beginning != "") {
		$where_clause .= sprintf (' AND date_begin >= "%s"', $search_date_begin_beginning);
	}	
	
	$is_admin = get_admin_user ($config['id_user']);
	
	if(!$is_admin) {
		// Check if the contract is public or private and from user company
		$company = get_user_company($config['id_user']);
		if(!empty($company)) {
			$company_id = reset(array_keys($company));
			$where_clause .= sprintf (' AND ((id_company = %d AND private = 1) OR private = 0)', $company_id);
		}
	}
	
	echo '<form action="index.php?sec=customers&sec2=operation/contracts/contract_detail" method="post">';
	
	echo "<table width=80% class='search-table'>";
	echo "<tr>";
	
	echo "<td colspan=2>";
	echo print_input_text ("search_text", $search_text, "", 38, 100, true, __('Search'));
	echo "</td>";
	
	echo "<td colspan=2>";
	echo print_select (get_company_roles (), 'search_company_role',
		$search_id_company, '', __('All'), 0, true, false, false, __('Company roles'));	
	echo "</td>";
	
	echo "</tr>";
	
	echo "<tr>";
	
	echo "<td>";
	echo print_input_text ('search_date_begin_beginning', $search_date_begin_beginning, '', 15, 20, true, __('Beginnig From'));
	echo "<a href='#' class='tip'><span>". __('Date format is YYYY-MM-DD')."</span></a>";
	echo "</td>";
	
	echo "<td>";
	echo print_input_text ('search_date_end_beginning', $search_date_end_beginning, '', 15, 20, true, __('Beginnig To'));
	echo "<a href='#' class='tip'><span>". __('Date format is YYYY-MM-DD')."</span></a>";
	echo "</td>";
	
	echo "<td>";
	echo print_input_text ('search_date_begin', $search_date_begin, '', 15, 20, true, __('Ending From'));
	echo "<a href='#' class='tip'><span>". __('Date format is YYYY-MM-DD')."</span></a>";
	echo "</td>";
	
	echo "<td>";
	echo print_input_text ('search_date_end', $search_date_end, '', 15, 20, true, __('Ending To'));
	echo "<a href='#' class='tip'><span>". __('Date format is YYYY-MM-DD')."</span></a>";	
	echo "</td>";
	
	echo "<td valign=bottom align='right'>";
	echo print_submit_button (__('Search'), "search_btn", false, 'class="sub search"', true);	
	echo "</td>";
	echo "</tr>";
	
	echo "</table>";
	
	echo '</form>';
		
	$contracts = get_contracts(false, "$where_clause ORDER BY date_end DESC");

	$contracts = print_array_pagination ($contracts, "index.php?sec=customers&sec2=operation/contracts/contract_detail&$search_params");

	if ($contracts !== false) {
		
		$table->width = "90%";
		$table->class = "listing";
		$table->cellspacing = 0;
		$table->cellpadding = 0;
		$table->tablealign="left";
		$table->data = array ();
		$table->size = array ();
		$table->style = array ();
		$table->colspan = array ();
		$table->style[3]= "font-size: 8px";
		$table->style[4]= "font-size: 8px";
		$table->style[5]= "font-size: 8px";
		$table->head[0] = __('Name');
		$table->head[1] = __('Contract number');
		$table->head[2] = __('Company');
		$table->head[3] = __('Begin');
		$table->head[4] = __('End');
		if(give_acl ($config["id_user"], $id_group, "VM")) {
			$table->head[5] = __('Privacy');
			$table->head[6] = __('Delete');
		}
		$counter = 0;
		
		foreach ($contracts as $contract) {
			
			$data = array ();
			
			$data[0] = "<a href='index.php?sec=customers&sec2=operation/contracts/contract_detail&id="
				.$contract["id"]."'>".$contract["name"]."</a>";
			$data[1] = $contract["contract_number"];
			$data[2] = "<a href='index.php?sec=customers&sec2=operation/companies/company_detail&id=".$contract["id_company"]."'>";
			$data[2] .= get_db_value ('name', 'tcompany', 'id', $contract["id_company"]);
			$data[2] .= "</a>";
			
			$data[3] = $contract["date_begin"];
			$data[4] = $contract["date_end"] != '0000-00-00' ? $contract["date_end"] : "-";
			
			// TODO: Acl check here if I have access to this contract because I have access (W) to this company.
			if( 1 == 1 ) {
				// Delete
				if($contract["private"]) {
					$data[5] = __('Private');
				}
				else {
					$data[5] = __('Public');
				}
				$data[6] = '<a href="index.php?sec=customers&sec2=operation/contracts/contract_detail&'.$search_params.'&delete_contract=1&id='.$contract["id"].'" onClick="if (!confirm(\''.__('Are you sure?').'\')) return false;"><img src="images/cross.png"></a>';
			}
			array_push ($table->data, $data);
		}	
		print_table ($table);
	}
	
	if($manager) {
		echo '<form method="post" action="index.php?sec=customers&sec2=operation/contracts/contract_detail">';
		echo '<div class="button" style="width: '.$table->width.'">';
		print_submit_button (__('Create'), 'new_btn', false, 'class="sub next"');
		print_input_hidden ('new_contract', 1);
		echo '</div>';
		echo '</form>';
	}
}
?>

<script type="text/javascript" src="include/js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>

<script type="text/javascript">
$(document).ready (function () {
	$("#text-date_begin").datepicker ({
		beforeShow: function () {
			return {
				maxDate: $("#text-date_end").datepicker ("getDate")
			};
		}
	});
	$("#text-date_end").datepicker ({
		beforeShow: function () {
			return {
				minDate: $("#text-date_begin").datepicker ("getDate")
			};
		}
	});
	
	$("#id_group").change (function() {
	
		refresh_company_combo();
	});
});

function toggle_advanced_fields () {
	
	$("#advanced_fields").toggle();
}

function refresh_company_combo () {
	
	var group = $("#id_group").val();
	
	values = Array ();
	values.push ({name: "page",
		value: "operation/contracts/contract_detail"});
	values.push ({name: "group",
		value: group});
	values.push ({name: "get_group_combo",
		value: 1});
	jQuery.get ("ajax.php",
		values,
		function (data, status) {
			$("#id_company").remove();
			$("#label-id_company").after(data);
		},
		"html"
	);

}

function validate_contract_form() {
	
	var val = $("#id_company").val();
	var name = $("#text-name").val();
	var error_msg = "";

	if (val == null || name == "") {
		
		var error_textbox = document.getElementById("error_text");
		
		
		if (val == null) {
			console.log("paso");
			error_msg = "<?php echo __("Company no selected")?>";
			pulsate("#id_company");
		} else if (name == "") {
			error_msg = "<?php echo __("Name can't be empty")?>";
			pulsate("#text-name");
		}
		
		if (error_textbox == null) {
			$('#contract_form').prepend("<h3 id='error_text' class='error'>"+error_msg+"</h3>");
		} else {
			$("#error_text").html(error_msg);
		}
		
		pulsate("#error_text");
		
		return false;  
		
	} 
	
	return true;
	
}

</script>


