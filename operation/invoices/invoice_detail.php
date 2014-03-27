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

// Invoice listing
$search_text = (string) get_parameter ('search_text');
$search_invoice_status = (string) get_parameter ('search_invoice_status');
$search_last_date = (int) get_parameter ('search_last_date');
$search_date_begin = get_parameter ('search_date_begin');
$search_date_end = get_parameter ('search_date_end');
$search_invoice_type = (string) get_parameter ('search_invoice_type', 'Submitted');
$search_company_role = (int) get_parameter ('search_company_role');
$search_company_manager = (string) get_parameter ('search_company_manager');

$order_by = get_parameter ('order_by', '');

$search_params = "&search_text=$search_text&search_invoice_status=$search_invoice_status&search_last_date=$search_last_date&search_date_end=$search_date_end&search_date_begin=$search_date_begin&order_by=$order_by&search_invoice_type=$search_invoice_type&search_company_role=$search_company_role&search_company_manager=$search_company_manager";

include_once('include/functions_crm.php');

$read = check_crm_acl ('company', 'cr');
$write = check_crm_acl ('company', 'cw');
$manage = check_crm_acl ('company', 'cm');
if (!$read) {
	include ("general/noaccess.php");
	exit;
}

echo "<h1>".__('Invoice listing');

echo "<div id='button-bar-title'>";
		echo "<ul>";
		echo "<li>";
		echo "<a href='index.php?sec=customers&sec2=operation/invoices/invoice_stats".$search_params."'>" .
			print_image ("images/chart_bar_dark.png", true, array("title" => __("Invoices report"))) .
			"</a>";
		echo "</li>";
		echo "</ul>";
		echo "</div>";

echo "</h1>";

if ($id_invoice || $id) {
	
	if ($id_invoice) {
		$id_company = get_db_value('id_company', 'tinvoice', 'id', $id_invoice);
	} elseif ($id) {
		$id_company = get_db_value('id_company', 'tinvoice', 'id_company', $id);
	}

	$permission = check_crm_acl ('invoice', '', $config['id_user'], $id_company);
	if (!$permission) {
		include ("general/noaccess.php");
		exit;
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
	
	$invoice = get_db_row_sql ("SELECT * FROM tinvoice WHERE id = $id_invoice");
	
	if ($invoice["id"] && !crm_is_invoice_locked ($invoice["id"])) {
		// Todo: Delete the invoice files from disk
		if ($invoice["id_attachment"] != ""){
			process_sql ("DELETE FROM tattachment WHERE id_attachment = ". $invoice["id_attachment"]);
		}
		$res = process_sql ("DELETE FROM tinvoice WHERE id = $id_invoice");
		if ($res > 0) {
			$company_name = get_db_value('name', 'tcompany', 'id', $invoice['id_company']);
			audit_db ($config["id_user"], $config["REMOTE_ADDR"], "Invoice deleted", "Invoice Bill ID: ".$invoice['bill_id'].", Company: $company_name");
		}
	}
}

// Lock/Unlock INVOICE
// ----------------
if ($lock_invoice == 1 && $id_invoice) {
	
	$locked = crm_is_invoice_locked ($id_invoice);
	$res = crm_change_invoice_lock ($config["id_user"], $id_invoice);
	
	if ($res === -1) { // -1 equals to false permission to lock or unlock the invoice
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to lock/unlock an invoice");
		include ("general/noaccess.php");
		exit;
	} else {
		$invoice = get_db_row_sql ("SELECT * FROM tinvoice WHERE id = $id_invoice");
		$company_name = get_db_value('name', 'tcompany', 'id', $invoice['id_company']);
		
		if ($locked && $res === 0) { // The invoice was locked and now is unlocked
			audit_db ($config["id_user"], $config["REMOTE_ADDR"], "Invoice unlocked", "Invoice Bill ID: ".$invoice['bill_id'].", Company: $company_name");
		} elseif (!$locked && $res === 1) { // The invoice was unlocked and now is locked
			audit_db ($config["id_user"], $config["REMOTE_ADDR"], "Invoice locked", "Invoice Bill ID: ".$invoice['bill_id'].", Company: $company_name");
		}
		clean_cache_db();
	}
}

$where_clause = " 1 = 1 ";

if ($search_text != "") {
	$where_clause .= sprintf ('AND (id_company IN (SELECT id FROM tcompany WHERE name LIKE "%%%s%%") OR 
		bill_id LIKE "%%%s%%" OR 
		description LIKE "%%%s%%")', $search_text, $search_text, $search_text);
}
if ($search_invoice_status != "") {
	$where_clause .= sprintf (' AND status = "%s"', $search_invoice_status);
}

// last_date is in days
if ($search_last_date) {
	$last_date_seconds = $search_last_date * 24 * 60 * 60;
	$search_date_begin = date('Y-m-d H:i:s', time() - $last_date_seconds);
	//$search_date_end = date('Y-m-d H:i:s');
	$search_date_end = "";
}

if ($search_date_begin != "") {
	$where_clause .= sprintf (' AND invoice_create_date >= "%s"', $search_date_begin);
}
if ($search_date_end != "") {
	$where_clause .= sprintf (' AND invoice_create_date <= "%s"', $search_date_end);
}
if ($search_invoice_type != "") {
	$where_clause .= sprintf (' AND invoice_type = "%s"', $search_invoice_type);
}
if ($search_company_role > 0) {
	$where_clause .= sprintf (' AND id_company IN (SELECT id FROM tcompany WHERE id_company_role = %d)', $search_company_role);
}
if ($search_company_manager != "") {
	$where_clause .= sprintf (' AND id_company IN (SELECT id FROM tcompany WHERE manager = "%s")', $search_company_manager);
}

if ($clean_output == 0){

	echo '<form method="post">';

	$table = new stdClass();
	$table->id = 'invoices_table';
	$table->width = '99%';
	$table->class = 'search-table-button';
	$table->size = array();
	$table->style = array();
	$table->style[1] = 'vertical-align:top;';
	$table->colspan[2][0] = 4;
	$table->rowspan[0][1] = 2;
	$table->data = array();

	$table->data[0][0] = print_input_text ("search_text", $search_text, "", 30, 100, true, __('Search'));

	$sql = 'SELECT id, name FROM tcompany_role ORDER BY name';
	$table->data[1][0] = print_select_from_sql ($sql, 'search_company_role', $search_company_role, '', __('Any'), 0, true, false, false, __('Company Role'));
	
	$table->data[0][1] = get_last_date_control ($search_last_date, 'search_last_date', __('Date'), $search_date_begin, 'search_date_begin', __('From'), $search_date_end, 'search_date_end', __('To'));
	
	$invoice_types = array('Submitted'=>'Submitted', 'Received'=>'Received');
	$table->data[0][2] = print_select ($invoice_types, 'search_invoice_type', $search_invoice_type, '','', 0, true, 0, false, __('Invoice type'), false, 'width:150px;');
	$table->data[1][2] = print_input_text_extended ('search_company_manager', $search_company_manager, 'text-search_company_manager', '', 20, 50, false, '', array(), true, '', __("Manager") )
		. print_help_tip (__("Type at least two characters to search"), true);

	$invoice_status_ar = array();
	$invoice_status_ar['pending'] = __("Pending");
	$invoice_status_ar['paid'] = __("Paid");
	$invoice_status_ar['cancel'] = __("Cancelled");
	$table->data[0][3] = print_select ($invoice_status_ar, 'search_invoice_status', $search_invoice_status, '', __("Any"), '', true, 0, false, __('Invoice status'), false, 'width:150px;');
	
	$table->data[2][0] = print_submit_button (__('Search'), "search_btn", false, 'class="sub search"', true);
	$where_clause = str_replace(array("\r", "\n"), '', $where_clause);
	$table->data[2][0] .= print_button(__('Export to CSV'), '', false, 'window.open(\'include/export_csv.php?export_csv_invoices=1&where_clause=' . str_replace('"', "\'", $where_clause) . '\')', 'class="sub csv"', true);
	$table->data[2][0] .= print_report_button ("index.php?sec=customers&sec2=operation/invoices/invoice_detail&$search_params", __('Export to PDF')."&nbsp;");

	print_table($table);
	
	echo '</form>';
}

$invoices = crm_get_all_invoices ($where_clause, $order_by);

// NO pagination for PDF output
if ($clean_output == 1)
	$config["block_size"] = 5000;

$invoices = print_array_pagination ($invoices, "index.php?sec=customers&sec2=operation/invoices/invoice_detail$search_params");

if ($invoices != false) {
	
		$url_id_order = 'index.php?sec=customers&sec2=operation/invoices/invoice_detail'.$search_params.'&order_by=bill_id';
		$url_create_order = 'index.php?sec=customers&sec2=operation/invoices/invoice_detail'.$search_params.'&order_by=invoice_create_date';
		switch ($order_by) {
			case "bill_id":
				$id_img = "&nbsp;<a href='$url_id_order'><img src='images/arrow_down_orange.png'></a>";
				$date_img = "&nbsp;<a href='$url_create_order'><img src='images/block_orange.png'></a>";
				break;
			case "invoice_create_date":
				$date_img = "&nbsp;<a href='$url_create_order'><img src='images/arrow_down_orange.png'></a>";
				$id_img = "&nbsp;<a href='$url_id_order'><img src='images/block_orange.png'></a>";
				break;
			case '':
				$id_img = "&nbsp;<a href='$url_id_order'><img src='images/block_orange.png'></a>";
				$date_img = "&nbsp;<a href='$url_create_order'><img src='images/arrow_down_orange.png'></a>";
		}
	
	$table = new stdClass();
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
	$table->head[1] = __('ID').$id_img;
	$table->head[2] = __('Amount');
	$table->head[3] = __('Currency');
	$table->head[4] = __('Status');
	$table->head[5] = __('Creation').$date_img;
	$table->head[6] = __('Payment');
	if ($clean_output == 0)
		$table->head[7] = __('Options');
	$counter = 0;

	$total=array();
	
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
		$id_title = $invoice["concept1"];
		$data[1] = "<a title='$id_title' href='index.php?sec=customers&sec2=operation/companies/company_detail&view_invoice=1&id=".$invoice["id_company"]."&op=invoices&id_invoice=".$invoice["id"]."'>".$invoice["bill_id"]."</a>";
		$partial = get_invoice_amount ($invoice["id"]);
		
		if (isset($total[$invoice["currency"]]))
			$total[$invoice["currency"]] = $total[$invoice["currency"]] + $partial;
		else
			$total[$invoice["currency"]] = $partial;

		$data[2] = format_numeric($partial);

		$tax = get_invoice_tax ($invoice["id"]);
		$tax_amount = get_invoice_amount ($invoice["id"]) * (1 + $tax/100);
		if (($tax != 0) && ($clean_output == 0))
			$data[2] .= print_help_tip (__("With taxes"). ": ". format_numeric($tax_amount), true);

		$data[3] = strtoupper ($invoice["currency"]);
		$data[4] = __($invoice["status"]);
		$data[5] = "<span style='font-size: 10px'>".$invoice["invoice_create_date"] . "</span>";
		$data[6] = "<span style='font-size: 10px'>".$invoice["invoice_payment_date"]. "</span>";

		if ($clean_output == 0){

			$data[7] = '';
			if ($invoice['invoice_type'] == 'Submitted') {
				$data[7] = '<a href="index.php?sec=users&amp;sec2=operation/invoices/invoice_view
				&amp;id_invoice='.$invoice["id"].'&amp;clean_output=1&amp;pdf_output=1">
				<img src="images/page_white_acrobat.png" title="'.__('Export to PDF').'"></a>';
			}
		
			if ($lock_permission) {
			
				if ($is_locked) {
					$lock_image = 'lock.png';
					$title = __('Unlock');
				} else {
					$lock_image = 'lock_open.png';
					$title = __('Lock');
				}
				$data[7] .= ' <a href="?sec=customers&sec2=operation/invoices/invoice_detail
				&lock_invoice=1&id='.$invoice["id_company"].'&id_invoice='.$invoice["id"].'&offset='.$offset.$search_params.'" 
				onClick="if (!confirm(\''.__('Are you sure?').'\')) return false;">
				<img src="images/'.$lock_image.'" title="'.$title.'"></a>';
			}
			if (!$is_locked) {
				$data[7] .= ' <a href="?sec=customers&sec2=operation/invoices/invoice_detail
				&delete_invoice=1&id='.$invoice["id_company"].'&id_invoice='.$invoice["id"].'
				&offset='.$offset.$search_params.'" onClick="if (!confirm(\''.__('Are you sure?').'\'))
				return false;"><img src="images/cross.png" title="'.__('Delete').'"></a>';
			} else {
				if ($locked_id_user) {
					$data[7] .= ' <img src="images/administrator_lock.png" width="18" height="18" 
					title="'.__('Locked by '.$locked_id_user).'">';
				}
			}
		}
	
		array_push ($table->data, $data);
	}
	print_table ($table);

	if ($total)
	echo __("Subtotals for each currency: ");
	foreach ($total as $key => $value) {
		echo "- $key : ". format_numeric ($value);
	}

} else {
	echo "<h3 class='no_result'>".__("No invoices")."</h3>";
}


if (($write || $manage) AND ($clean_output == 0)) {
	echo '<form method="post" action="index.php?sec=customers&sec2=operation/invoices/invoices">';
	echo '<div class="button" style="width: '.$table->width.'">';
	print_submit_button (__('Create'), 'new_btn', false, 'class="sub next"');
	print_input_hidden ('new_invoice', 1);
	echo '</div>';
	echo '</form>';
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

	var idUser = "<?php echo $config['id_user'] ?>";
	bindAutocomplete("#text-search_company_manager", idUser);
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


