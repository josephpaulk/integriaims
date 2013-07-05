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

enterprise_include('include/functions_crm.php');
include_once('include/functions_crm.php');

$id_company = get_db_value('id_company', 'tinvoice', 'id', $id);

$permission = enterprise_hook ('crm_check_acl_invoice', array ($config['id_user'], $id_company));

$enterprise = false;

if ($read_permission === ENTERPRISE_NOT_HOOK) {
	
	$permission = true;
	
} else {
	
	$enterprise = true;
	if (!$permission) {
		include ("general/noaccess.php");
		exit;
	}
	
}

$get_company_name = (bool) get_parameter ('get_company_name');
$new_contract = (bool) get_parameter ('new_contract');
$delete_contract = (bool) get_parameter ('delete_contract');
$delete_invoice = get_parameter ('delete_invoice', "");

// Delete INVOICE
// ----------------
if ($delete_invoice == 1){
	
	if (!$permission && $enterprise) {
		include ("general/noaccess.php");
		exit;
	}
	
	$id_invoice = get_parameter ("id_invoice", "");
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

echo "<h2>".__('Invoice listing')."</h2>";

	
// Invoice listing
$search_text = (string) get_parameter ('search_text');
$search_date_end = get_parameter ('search_date_end');
$search_date_begin = get_parameter ('search_date_begin');

$search_params = "search_text=$search_text&search_date_end=$search_date_end&search_date_begin=$search_date_begin";

$where_clause = " 1 = 1 AND id_company " . get_filter_by_company_accessibility($config["id_user"]);

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

echo "<table width=80% class='search-table'>";
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
echo "</td>";
echo "</tr>";

echo "</table>";

echo '</form>';

$invoices =  get_db_all_rows_sql ("SELECT * FROM tinvoice WHERE $where_clause ORDER BY invoice_create_date DESC", "");

if ($permission && $enterprise) {
	$invoices = crm_get_user_invoices($config['id_user'], $invoices);
}

$invoices = print_array_pagination ($invoices, "index.php?sec=customers&sec2=operation/invoices/invoice_detail&$search_params");

if ($invoices !== false) {
	
	$table->width = "95%";
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
	$table->head[6] = __('Description');
	$table->head[7] = __('Options');
	$counter = 0;
	
	foreach ($invoices as $invoice) {
		
		$lock_permission = crm_check_lock_permission ($config["id_user"], $invoice["id"]);
		$is_locked = crm_is_invoice_locked ($invoice["id"]);
		$locked_id_user = false;
		if ($is_locked) {
			$locked_id_user = crm_get_invoice_locked_id_user ($invoice["id"]);
		}
		
		$data = array ();
		
		if ($invoice["id_company"] != 0){
			$data[0] = get_db_value ("name", "tcompany", "id", $invoice["id_company"]);
		} else {
			$data[0] = __("N/A");
		}
		
		$data[1] = "<a href='index.php?sec=customers&sec2=operation/companies/company_detail&view_invoice=1&id=".$invoice["id_company"]."&op=invoices&id_invoice=".$invoice["id"]."'>".$invoice["bill_id"]."</a>";
		$data[2] = get_invoice_amount ($invoice["id"]);
		$data[3] = __($invoice["status"]);
		$data[4] = "<span style='font-size: 10px'>".$invoice["invoice_create_date"] . "</span>";
		if ($invoice["status"] == "paid") {
			$data[5] = "<span style='font-size: 10px'>". $invoice["invoice_payment_date"]. "</span>";
		} else {
			$data[5] = __("Not paid");
		}
		$data[6] = __($invoice["description"]);
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
				&delete_invoice=1&id='.$invoice["id_company"].'&id_invoice='.$invoice["id"].'" 
				onClick="if (!confirm(\''.__('Are you sure?').'\')) return false;">
				<img src="images/cross.png" title="'.__('Delete').'"></a>';
		} else {
			if ($locked_id_user) {
				$data[7] .= ' <img src="images/lock.png" title="'.__('Locked by '.$locked_id_user).'">'; // TODO: Change the icon
			}
		}
		
		array_push ($table->data, $data);
	}
	print_table ($table);
}

?>

<script type="text/javascript" src="include/js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>

<script type="text/javascript">
$(document).ready (function () {
	$("#text-search_date_begin").datepicker ({
		beforeShow: function () {
			maxdate = null;
			if ($("#text-search_date_end").datepicker ("getDate") > $(this).datepicker ("getDate"))
				maxdate = $("#text-search_date_end").datepicker ("getDate");
			return {
				maxDate: maxdate
			};
		},
		onSelect: function (datetext) {
			end = $("#text-search_date_end").datepicker ("getDate");
			start = $(this).datepicker ("getDate");
			if (end <= start) {
				pulsate ($("#text-search_date_end"));
			}
		}
	});
	$("#text-search_date_end").datepicker ({
		beforeShow: function () {
			return {
				minDate: $("#text-search_date_begin").datepicker ("getDate")
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


