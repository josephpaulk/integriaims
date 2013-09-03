<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2013 Ártica Soluciones Tecnológicas
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


$id = (int) get_parameter ('id');
$id_invoice = get_parameter ("id_invoice", "");
$offset = get_parameter ('offset', 0);

enterprise_include('include/functions_crm.php');
include_once('include/functions_crm.php');

$manage = enterprise_hook('crm_check_user_profile', array($config['id_user'], 'cm'));

if ($manage !== ENTERPRISE_NOT_HOOK) {
	if (!$manage) {
		include ("general/noaccess.php");
		exit;
	}
}

echo "<h1>".__('Invoice listing')."</h1>";

if ($id_invoice) {
	$id_company = get_db_value('id_company', 'tinvoice', 'id', $id);

	$permission = enterprise_hook ('crm_check_acl_invoice', array ($config['id_user'], $id_company));

	$enterprise = false;
	$permission = true;

	if ($permission !== ENTERPRISE_NOT_HOOK) {
		$enterprise = true;
		if (!$permission) {
			include ("general/noaccess.php");
			exit;
		}
	}
}

$get_company_name = (bool) get_parameter ('get_company_name');
$new_contract = (bool) get_parameter ('new_contract');
$delete_contract = (bool) get_parameter ('delete_contract');
$delete_invoice = get_parameter ('delete_invoice', "");
$lock_invoice = get_parameter ('lock_invoice', "");

// Delete INVOICE
// ----------------
if ($delete_invoice == 1 && $id_invoice){
	
	if (!$permission && $enterprise) {
		include ("general/noaccess.php");
		exit;
	}
	
	$invoice = get_db_row_sql ("SELECT * FROM tinvoice WHERE id = $id_invoice");
	
	// Do another security check, don't rely on information passed from URL
	
	if ($invoice["id"] && !crm_is_invoice_locked ($invoice["id"])) {
	//if (($config["id_user"] = $invoice["id_user"]) OR ($id_task == $invoice["id_task"])){ // TODO: Check this
			// Todo: Delete file from disk
			if ($invoice["id_attachment"] != ""){
				process_sql ("DELETE FROM tattachment WHERE id_attachment = ". $invoice["id_attachment"]);
			}
			process_sql ("DELETE FROM tinvoice WHERE id = $id_invoice");
	}
}

// Lock/Unlock INVOICE
// ----------------
if ($lock_invoice == 1 && $id_invoice){
	
	if (!crm_check_lock_permission ($config["id_user"], $id_invoice)) {
		include ("general/noaccess.php");
		exit;
	}
	
	crm_change_invoice_lock ($config["id_user"], $id_invoice);
	clean_cache_db();
}
	
// Invoice listing
$search_text = (string) get_parameter ('search_text');
$search_date_end = get_parameter ('search_date_end');
$search_date_begin = get_parameter ('search_date_begin');

$search_params = "search_text=$search_text&search_date_end=$search_date_end&search_date_begin=$search_date_begin";

//$where_clause = " 1 = 1 AND id_company " . get_filter_by_company_accessibility($config["id_user"]);
$where_clause = " 1 = 1 ";

if ($search_text != "") {
	$where_clause .= sprintf ('AND (id_company IN (SELECT id FROM tcompany WHERE name LIKE "%%%s%%") OR 
		bill_id LIKE "%%%s%%" OR 
		description LIKE "%%%s%%")', $search_text, $search_text, $search_text);
}


if ($search_date_end != "") {
	$where_clause .= sprintf (' AND invoice_create_date <= "%s"', $search_date_end);
}

if ($search_date_begin != "") {
	$where_clause .= sprintf (' AND invoice_create_date >= "%s"', $search_date_begin);
}


echo '<form method="post">';

echo "<table width=99% class='search-table'>";
echo "<tr>";

echo "<td colspan=2>";
echo print_input_text ("search_text", $search_text, "", 38, 100, true, __('Search'));
echo "</td>";

echo "<td>";
echo print_input_text ('search_date_begin', $search_date_begin, '', 15, 20, true, __('From'));
echo "<a href='#' class='tip'><span>". __('Date format is YYYY-MM-DD')."</span></a>";
echo "</td>";

echo "<td>";
echo print_input_text ('search_date_end', $search_date_end, '', 15, 20, true, __('To'));
echo "<a href='#' class='tip'><span>". __('Date format is YYYY-MM-DD')."</span></a>";	
echo "</td>";

echo "<td valign=bottom align='right'>";
echo print_submit_button (__('Search'), "search_btn", false, 'class="sub search"', true);
echo print_button(__('Export to CSV'), '', false, 'window.open(\'' . "include/export_csv.php?export_csv_invoices=1&where_clause=$where_clause" . '\')', 'class="sub csv"', true);
echo "</td>";
echo "</tr>";

echo "</table>";

echo '</form>';

$invoices = crm_get_all_invoices ($where_clause);

if ($permission && $enterprise) {
	$invoices = crm_get_user_invoices($config['id_user'], $invoices);
}

$invoices = print_array_pagination ($invoices, "index.php?sec=customers&sec2=operation/invoices/invoice_detail&$search_params");

if ($invoices !== false) {
	
	$table->width = "99%";
	$table->class = "listing";
	$table->cellspacing = 0;
	$table->cellpadding = 0;
	$table->tablealign="left";
	$table->data = array ();
	$table->size = array ();
	$table->style = array ();
	$table->colspan = array ();
	$table->head[0] = __('Company');
	$table->head[1] = __('ID');
	$table->head[2] = __('Amount');
	$table->head[3] = __('Status');
	$table->head[4] = __('Creation');
	$table->head[5] = __('Payment');
	$table->head[6] = __('Desc.');
	$table->head[7] = __('Options');
	$counter = 0;
	
	foreach ($invoices as $invoice) {
		
		$is_locked = crm_is_invoice_locked ($invoice["id"]);
		$lock_permission = crm_check_lock_permission ($config["id_user"], $invoice["id"]);
		$locked_id_user = false;
		if ($is_locked) {
			$locked_id_user = crm_get_invoice_locked_id_user ($invoice["id"]);
		}
		
		$data = array ();
		
		if ($invoice["id_company"] != 0){
			$company_name = get_db_value ("name", "tcompany", "id", $invoice["id_company"]);
			$data[0] = "<a href='index.php?sec=customers&sec2=operation/companies/company_detail&view_invoice=1&id=".$invoice["id_company"]."&op=invoices&id_invoice=".$invoice["id"]."'>".$company_name."</a>";
		} else {
			$data[0] = __("N/A");
		}
		
		$data[1] = "<a href='index.php?sec=customers&sec2=operation/companies/company_detail&view_invoice=1&id=".$invoice["id_company"]."&op=invoices&id_invoice=".$invoice["id"]."'>".$invoice["bill_id"]."</a>";
		$data[2] = get_invoice_amount ($invoice["id"]) ." ". strtoupper ($invoice["currency"]);
		$data[3] = __($invoice["status"]);
		$data[4] = "<span style='font-size: 10px'>".$invoice["invoice_create_date"] . "</span>";
		if ($invoice["status"] == "paid") {
			$data[5] = "<span style='font-size: 10px'>". $invoice["invoice_payment_date"]. "</span>";
		} else {
			$data[5] = __("Not paid");
		}

		// Description could be huge, so is a bad idea to show up in a listing. We put in a hint,
		// but to avoid user moving over all tip icons, show icon only if have something inside.

		if ($invoice["description"] != ""){
			$data[6] = "<a href='#' class='tip'><span>";
			$data[6] .= $invoice["description"];
			$data[6] .= "</span></a>";
		} else {
			$data[6] = "";
		}

		$data[7] = '<a href="index.php?sec=users&amp;sec2=operation/invoices/invoice_view
			&amp;id_invoice='.$invoice["id"].'&amp;clean_output=1&amp;pdf_output=1">
			<img src="images/page_white_acrobat.png" title="'.__('Export to PDF').'"></a>';
		if ($lock_permission) {
			
			if ($is_locked) {
				$lock_image = 'lock.png';
				$title = __('Unlock');
			} else {
				$lock_image = 'lock_open.png';
				$title = __('Lock');
			}
			$data[7] .= ' <a href="?sec=customers&sec2=operation/invoices/invoice_detail
				&lock_invoice=1&id='.$invoice["id_company"].'&id_invoice='.$invoice["id"].'" 
				onClick="if (!confirm(\''.__('Are you sure?').'\')) return false;">
				<img src="images/'.$lock_image.'" title="'.$title.'"></a>';
		}
		if (!$is_locked) {
			$data[7] .= ' <a href="?sec=customers&sec2=operation/invoices/invoice_detail
				&delete_invoice=1&id='.$invoice["id_company"].'&id_invoice='.$invoice["id"].'
				&offset='.$offset.'" onClick="if (!confirm(\''.__('Are you sure?').'\'))
				return false;"><img src="images/cross.png" title="'.__('Delete').'"></a>';
		} else {
			if ($locked_id_user) {
				$data[7] .= ' <img src="images/administrator_lock.png" width="18" height="18" 
				title="'.__('Locked by '.$locked_id_user).'">';
			}
		}
		
		array_push ($table->data, $data);
	}
	print_table ($table);
}

?>

<script type="text/javascript" src="include/js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
<script type="text/javascript" src="include/js/integria_date.js"></script>

<script type="text/javascript">

add_ranged_datepicker ("#text-search_date_begin", "#text-search_date_end", null);
	
$(document).ready (function () {
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


