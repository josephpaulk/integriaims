<?PHP
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
check_login ();

include_once('include/functions_crm.php');

$id_company = get_parameter ("id", -1);
$company = get_db_row ('tcompany', 'id', $id_company);
$id_invoice = get_parameter ("id_invoice", -1);
$operation_invoices = get_parameter ("operation_invoices");

$read = check_crm_acl ('company', 'cr');
$write = check_crm_acl ('company', 'cw');
$manage = check_crm_acl ('company', 'cm');

if ($id_invoice > 0 || $id_company > 0) {
	if ($id_company < 1 && $id_invoice > 0) {
		$id_company = get_db_value ('id_company', 'tinvoice', 'id', $id_invoice);
	}
	if ($id_company > 0) {
		$permission = check_crm_acl ('invoice', '', $config['id_user'], $id_company);
		if (!$permission && !$manage) {
			include ("general/noaccess.php");
			exit;
		} elseif (!$write && !$manage && $read) {
			include ("operation/invoices/invoice_view.php");
			return;
		}
	} else {
		include ("general/noaccess.php");
		exit;
	}
	
	if (crm_is_invoice_locked ($invoice["id"])) {
		include ("operation/invoices/invoice_view.php");
		return;
	}
}

if ($operation_invoices == "add_invoice"){
	
	$filename = get_parameter ('upfile', false);
	$bill_id = get_parameter ("bill_id", "");
	$description = get_parameter ("description", "");
	$concept[0] = get_parameter ("concept1", "");
	$concept[1] = get_parameter ("concept2", "");
	$concept[2] = get_parameter ("concept3", "");
	$concept[3] = get_parameter ("concept4", "");
	$concept[4] = get_parameter ("concept5", "");
	$amount[0] = (float) get_parameter ("amount1", 0);
	$amount[1] = (float) get_parameter ("amount2", 0);
	$amount[2] = (float) get_parameter ("amount3", 0);
	$amount[3] = (float) get_parameter ("amount4", 0);
	$amount[4] = (float) get_parameter ("amount5", 0);
	$user_id = $config["id_user"];
	$invoice_create_date = get_parameter ("invoice_create_date");
	$invoice_payment_date = get_parameter ("invoice_payment_date");
	$tax = get_parameter ("tax", 0.00);
	$currency = get_parameter ("currency", "EUR");
	$invoice_status = get_parameter ("invoice_status", 'pending');
	
	if ($filename != ""){
		$file_temp = sys_get_temp_dir()."/$filename";
		$filesize = filesize($file_temp);
		
		// Creating the attach
		$sql = sprintf ('INSERT INTO tattachment (id_usuario, filename, description, size) VALUES ("%s", "%s", "%s", "%s")',
				$user_id, $filename, $description, $filesize);
		$id_attachment = process_sql ($sql, 'insert_id');
		
		// Copy file to directory and change name
		$file_target = $config["homedir"]."/attachment/".$id_attachment."_".$filename;
			
		if (! copy($file_temp, $file_target)) {
			$result_output = "<h3 class=error>".__('File cannot be saved. Please contact Integria administrator about this error')."</h3>";
			$sql = "DELETE FROM tattachment WHERE id_attachment =".$id_attachment;
			process_sql ($sql);
		} else {
			// Delete temporal file
			unlink ($file_temp);
		}
	} else {
		$id_attachment = 0;
	}
	
	// Creating the cost record
	$sql = sprintf ('INSERT INTO tinvoice (description, id_user, id_company,
	bill_id, id_attachment, invoice_create_date, invoice_payment_date, tax, currency, status,
	concept1, concept2, concept3, concept4, concept5, amount1, amount2, amount3,
	amount4, amount5) VALUES ("%s", "%s", "%d", "%s", "%d", "%s", "%s", "%s", "%s", "%s", "%s",
	"%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s")', $description, $user_id, $id_company,
	$bill_id, $id_attachment, $invoice_create_date, $invoice_payment_date, $tax, $currency,
	$invoice_status, $concept1, $concept2, $concept3, $concept4, $concept5, $amount1, $amount2,
	$amount3, $amount4, $amount5);
	
	$id_invoice = process_sql ($sql, 'insert_id');
	if ($id_invoice !== false) {
		echo '<h3 class="suc">'.__('Successfully created').'</h3>';
	} else {
		echo '<h3 class="error">'.__('There was a problem creating the invoice').'</h3>';
	}
}

if ($operation_invoices == "update_invoice"){
	$values = array();
	
	$filename = get_parameter ('upfile', false);
	$bill_id = get_parameter ("bill_id", "");
	$description = get_parameter ("description", "");
	$concept[0] = get_parameter ("concept1", "");
	$concept[1] = get_parameter ("concept2", "");
	$concept[2] = get_parameter ("concept3", "");
	$concept[3] = get_parameter ("concept4", "");
	$concept[4] = get_parameter ("concept5", "");
	$amount[0] = (float) get_parameter ("amount1", 0);
	$amount[1] = (float) get_parameter ("amount2", 0);
	$amount[2] = (float) get_parameter ("amount3", 0);
	$amount[3] = (float) get_parameter ("amount4", 0);
	$amount[4] = (float) get_parameter ("amount5", 0);
	
	$user_id = $config["id_user"];
	$invoice_create_date = get_parameter ("invoice_create_date");
	$invoice_payment_date = get_parameter ("invoice_payment_date");
	$tax = get_parameter ("tax", 0.00);
	$currency = get_parameter ("currency", "EUR");
	$invoice_status = get_parameter ("invoice_status", 'pending');

	// If no file input, the file doesnt change
	if ($filename != ""){
		$old_id_attachment = $id_attachment;
		
		$file_temp = sys_get_temp_dir()."/$filename";
		$filesize = filesize($file_temp);
		
		// Creating the attach
		$sql = sprintf ('INSERT INTO tattachment (id_usuario, filename, description, size) VALUES ("%s", "%s", "%s", "%s")',
				$user_id, $filename, $description, $filesize);
		$id_attachment = process_sql ($sql, 'insert_id');
		
		// Copy file to directory and change name
		$file_target = $config["homedir"]."/attachment/".$id_attachment."_".$filename;
			
		if (! copy($file_temp, $file_target)) {
			$result_output = "<h3 class=error>".__('File cannot be saved. Please contact Integria administrator about this error')."</h3>";
			$sql = "DELETE FROM tattachment WHERE id_attachment =".$id_attachment;
			process_sql ($sql);
		} else {
			// Delete temporal file
			unlink ($file_temp);
			$values['id_attachment'] = $id_attachment;
			
			// POSSIBLE FEATURE, DELETE OLD ATTACHMENT IF IS SETTED IN A CHECKBOX OR SOMETHING
			//~ if($old_id_attachment != 0) {
				//~ $old_filename = get_db_value('filename', 'tattachment', 'id_attachment', $old_id_attachment);
				//~ unlink($config["homedir"]."/attachment/".$old_id_attachment."_".$old_filename);
			//~ }
		}
	}
	
	// Updating the invoice
	
	$values['description'] = $description;
	$values['id_user'] = $user_id;
	$values['id_company'] = $id_company;
	$values['bill_id'] = $bill_id;
	$values['concept1'] = $concept[0];
	$values['concept2'] = $concept[1];
	$values['concept3'] = $concept[2];
	$values['concept4'] = $concept[3];
	$values['concept5'] = $concept[4];
	$values['amount1'] = $amount[0];
	$values['amount2'] = $amount[1];
	$values['amount3'] = $amount[2];
	$values['amount4'] = $amount[3];
	$values['amount5'] = $amount[4];
	$values['status'] = $invoice_status;
	$values['tax'] = $tax;
	$values['currency'] = $currency;

	$values['invoice_create_date'] = $invoice_create_date;
	$values['invoice_payment_date'] = $invoice_payment_date;
	
	$where = array('id' => $id_invoice);
	
	$ret = process_sql_update ('tinvoice', $values, $where);
	
	if ($ret !== false) {
		echo '<h3 class="suc">'.__('Successfully updated').'</h3>';
	} else {
		echo '<h3 class="error">'.__('There was a problem updating the invoice').'</h3>';
	}
}

if ($id_invoice > 0){
	
	$invoice = get_db_row ('tinvoice', 'id', $id_invoice);
	$bill_id = $invoice["bill_id"];
	$description = $invoice["description"];
	$concept[0] = $invoice["concept1"];
	$concept[1] = $invoice["concept2"];
	$concept[2] = $invoice["concept3"];
	$concept[3] = $invoice["concept4"];
	$concept[4] = $invoice["concept5"];
	$amount[0] = $invoice["amount1"];
	$amount[1] = $invoice["amount2"];
	$amount[2] = $invoice["amount3"];
	$amount[3] = $invoice["amount4"];
	$amount[4] = $invoice["amount5"];
	$id_attachment = $invoice["id_attachment"];
	$invoice_create_date = $invoice["invoice_create_date"];
	$invoice_payment_date = $invoice["invoice_payment_date"];
	$id_company = $invoice["id_company"];
	$tax = $invoice["tax"];
	$currency = $invoice["currency"];
	$invoice_status = $invoice["status"];

} else {
	
	if ($id_company > 0) {
		$permission = check_crm_acl ('invoice', '', $config['id_user'], $id_company);
		if (!$permission) {
			include ("general/noaccess.php");
			exit;
		}
	}
	if (!$write && !$manage) {
		include ("general/noaccess.php");
		exit;
	}
	
	$bill_id = "";
	$description = "";
	$id_attachment = "";
	$invoice_create_date = "";
	$invoice_payment_date = "";
	$tax = 0;
	$currency = "EUR";
	$invoice_status = "pending";
}

echo "<h3>";
if ($id_invoice == "-1") {
	echo __('Add new invoice');
}
else {
	echo __('Update invoice'). " #$id_invoice";
	echo ' <a href="index.php?sec=users&amp;sec2=operation/invoices/invoice_view
				&amp;id_invoice='.$id_invoice.'&amp;clean_output=1&amp;pdf_output=1">
				<img src="images/page_white_acrobat.png" title="'.__('Export to PDF').'"></a>';
	if ($lock_permission) {
		echo ' <a href="?sec=customers&sec2=operation/companies/company_detail
			&lock_invoice=1&id='.$id_company.'&op=invoices&id_invoice='.$id_invoice.'" 
			onClick="if (!confirm(\''.__('Are you sure?').'\')) return false;">
			<img src="images/lock_open.png" title="'.__('Lock').'"></a>';
	}
	echo ' <a href="?sec=customers&sec2=operation/companies/company_detail
		&delete_invoice=1&id='.$id_company.'&op=invoices&id_invoice='.$id_invoice.'" 
		onClick="if (!confirm(\''.__('Are you sure?').'\')) return false;">
		<img src="images/cross.png" title="'.__('Delete').'"></a>';
}
echo "</h3>";

$table->id = 'cost_form';
$table->width = '98%';
$table->class = 'databox';
$table->colspan = array ();
$table->size = array ();
$table->data = array ();

if ($id_company > 0) {
	$company_name = get_db_value ("name", "tcompany", "id", $id_company);
	$table->colspan[0][0] = 2;
	$table->data[0][0] = print_input_text ('company_name', $company_name, '', 100, 100, true, __('Company'), true);
	$table->data[0][0] .= print_input_hidden ('id', $id_company);
} else {
	$table->colspan[0][0] = 2;
	if ($manage) {
		$table->data[0][0] = print_select (get_companies(), 'id', $id_company, '', '', 0, true, 0, true, __('Company'));
	} else {
		$sql = "SELECT id, name FROM tcompany WHERE manager='".$config['id_user']."'";
		$table->data[0][0] = print_select_from_sql ($sql, 'id', $id_company, '', '', 0, true, 0, true, __('Company'));
	}
}

$table->data[1][0] = print_input_text ('bill_id', $bill_id, '', 25, 100, true, __('Bill ID'));

$invoice_status_ar = array();
$invoice_status_ar['pending']= __("Pending");
$invoice_status_ar['paid']= __("Paid");
$invoice_status_ar['cancel']= __("Cancelled");
$table->data[1][1] = print_select ($invoice_status_ar, 'invoice_status',
	$invoice_status, '','', 0, true, false, false, __('Invoice status'));

$table->data[2][0] = print_input_text ('invoice_create_date', $invoice_create_date, '', 15, 50, true, __('Invoice creation date'));
$table->data[2][1] = print_input_text ('invoice_payment_date', $invoice_payment_date, '', 15, 50, true,__('Invoice effective payment date'));

$table->data[3][0] = "<h4>".__('Concept')."</h4>";
$table->data[3][1] = "<h4>".__('Amount')."</h4>";
$table->data[4][0] = print_input_text ('concept1', $concept[0], '', 60, 250, true);
$table->data[4][1] = print_input_text ('amount1', $amount[0], '', 10, 20, true);
$table->data[5][0] = print_input_text ('concept2', $concept[1], '', 60, 250, true);
$table->data[5][1] = print_input_text ('amount2', $amount[1], '', 10, 20, true);
$table->data[6][0] = print_input_text ('concept3', $concept[2], '', 60, 250, true);
$table->data[6][1] = print_input_text ('amount3', $amount[2], '', 10, 20, true);
$table->data[7][0] = print_input_text ('concept4', $concept[3], '', 60, 250, true);
$table->data[7][1] = print_input_text ('amount4', $amount[3], '', 10, 20, true);
$table->data[8][0] = print_input_text ('concept5', $concept[4], '', 60, 250, true);
$table->data[8][1] = print_input_text ('amount5', $amount[4], '', 10, 20, true);

$table->data[9][0] = print_input_text ('tax', $tax, '', 5, 20, true, __('Taxes (%)'));
$table->data[9][1] = print_input_text ('currency', $currency, '', 3, 3, true, __('Currency'));

$table->colspan[10][0] = 2;
$table->data[10][0] = print_textarea ('description', 5, 40, $description, '', true, __('Description'));

$table->colspan[11][0] = 2;
$table->data[11][0] = print_input_file ('upfile', 20, false, '', true, __('Attachment'));

echo '<form id="form-invoice" method="post" enctype="multipart/form-data"
action="index.php?sec=customers&sec2=operation/companies/company_detail
&view_invoice=1&op=invoices&id_invoice='.$id_invoice.'">';

print_table ($table);
echo '<div class="button" style="width:'.$table->width.';">';
if ($id_invoice != -1) {
	print_submit_button (__('Update'), 'button-upd', false, 'class="sub upd"');
	print_input_hidden ('id', $id);
	print_input_hidden ('operation_invoices', "update_invoice");
} else {
	print_submit_button (__('Add'), 'button-crt', false, 'class="sub next"');
	print_input_hidden ('operation_invoices', "add_invoice");
}
echo '</div>';
echo '</form>';

?>

<script type="text/javascript" src="include/js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
<script type="text/javascript" src="include/js/jquery.validate.js"></script>
<script type="text/javascript" src="include/js/jquery.validation.functions.js"></script>
<script type="text/javascript" src="include/js/integria_date.js"></script>

<script type="text/javascript">
	
// Datepicker
add_ranged_datepicker ("#text-invoice_create_date", "#text-invoice_payment_date", null);

// Form validation
trim_element_on_submit('#text-bill_id');

validate_form("#form-invoice");
var rules, messages;
// Rules: #text-bill_id
rules = {
	required: true,
	remote: {
		url: "ajax.php",
        type: "POST",
        data: {
          page: "include/ajax/remote_validations",
          search_existing_invoice: 1,
          bill_id: function() { return $('#text-bill_id').val() },
          invoice_id: <?php echo $id_invoice; ?>
        }
	}
};
messages = {
	required: "<?php echo __('Bill ID required'); ?>",
	remote: "<?php echo __('This bill ID already exists'); ?>"
};
add_validate_form_element_rules('#text-bill_id', rules, messages);
// Rules: #text-tax
rules = { number: true };
messages = { number: "<?php echo __('Invalid number')?>" };
add_validate_form_element_rules('#text-tax', rules, messages);
// Rules: input[name="amount1"]
rules = { number: true };
messages = { number: "<?php echo __('Invalid number')?>" };
add_validate_form_element_rules('input[name="amount1"]', rules, messages);
// Rules: input[name="amount2"]
rules = { number: true };
messages = { number: "<?php echo __('Invalid number')?>" };
add_validate_form_element_rules('input[name="amount2"]', rules, messages);
// Rules: input[name="amount3"]
rules = { number: true };
messages = { number: "<?php echo __('Invalid number')?>" };
add_validate_form_element_rules('input[name="amount3"]', rules, messages);
// Rules: input[name="amount4"]
rules = { number: true };
messages = { number: "<?php echo __('Invalid number')?>" };
add_validate_form_element_rules('input[name="amount4"]', rules, messages);
// Rules: input[name="amount5"]
rules = { number: true };
messages = { number: "<?php echo __('Invalid number')?>" };
add_validate_form_element_rules('input[name="amount5"]', rules, messages);

</script>
