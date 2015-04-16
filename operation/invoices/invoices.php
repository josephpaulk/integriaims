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
		if (!$permission) {
			include ("general/noaccess.php");
			exit;
		} elseif (!$write && !$manage && $read) {
			include ("operation/invoices/invoice_view.php");
			return;
		}
	} else {
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access an invoice");
		include ("general/noaccess.php");
		exit;
	}
	
	if (crm_is_invoice_locked ($invoice["id"])) {
		include ("operation/invoices/invoice_view.php");
		return;
	}
}

$upload_file = get_parameter('upload_file', 0);

if ($upload_file) {
	if (isset($_POST['upfile']) && ( $_POST['upfile'] != "" )){ //if file
		$filename= $_POST['upfile'];
		$file_tmp = sys_get_temp_dir().'/'.$filename;
		$size = filesize ($file_tmp);
		$description = get_parameter ("description", "");

		$sql = sprintf("INSERT INTO tattachment (id_invoice, id_usuario, filename, description, timestamp, size) VALUES (%d, '%s', '%s', '%s', '%s', %d)", $id_invoice, $config["id_user"], $filename, $description, date('Y-m-d H:i:s'), $size);
		$id_attach = process_sql ($sql, 'insert_id');

		$filename_encoded = $id_attach . "_" . $filename;
		
		// Copy file to directory and change name
		$file_target = $config["homedir"]."/attachment/".$filename_encoded;

		if (!(copy($file_tmp, $file_target))){
			echo "<h3 class=error>".__("Could not be attached")."</h3>";
		} else {
			// Delete temporal file
			echo "<h3 class=suc>".__("Successfully attached")."</h3>";
			$location = $file_target;
			unlink ($file_tmp);
		}
	}
}

// Delete file
$deletef = get_parameter ("deletef", "");
if ($deletef != ""){
	$file = get_db_row ("tattachment", "id_attachment", $deletef);
	if ( (dame_admin($config["id_user"])) || ($file["id_usuario"] == $config["id_user"]) ){
		$sql = "DELETE FROM tattachment WHERE id_attachment = $deletef";
		process_sql ($sql);	
		$filename = $config["homedir"]."/attachment/". $file["id_attachment"]. "_" . $file["filename"];
		unlink ($filename);
		echo "<h3 class=suc>".__("Successfully deleted")."</h3>";
	}
}


if ($operation_invoices == "add_invoice"){
	
	$filename = get_parameter ('upfile', false);
	$bill_id = get_parameter ("bill_id", "");
	$reference = get_parameter ("reference", "");
	$description = get_parameter ("description", "");
	$concept = array();
	$concept[0] = get_parameter ("concept1", "");
	$concept[1] = get_parameter ("concept2", "");
	$concept[2] = get_parameter ("concept3", "");
	$concept[3] = get_parameter ("concept4", "");
	$concept[4] = get_parameter ("concept5", "");
	$amount = array();
	$amount[0] = (float) get_parameter ("amount1", 0);
	$amount[1] = (float) get_parameter ("amount2", 0);
	$amount[2] = (float) get_parameter ("amount3", 0);
	$amount[3] = (float) get_parameter ("amount4", 0);
	$amount[4] = (float) get_parameter ("amount5", 0);
	$user_id = $config["id_user"];
	$invoice_create_date = get_parameter ("invoice_create_date");
	$invoice_payment_date = get_parameter ("invoice_payment_date");
	$invoice_expiration_date = get_parameter ("invoice_expiration_date");
	$tax = get_parameter ("tax", 0.00);
	$currency = get_parameter ("currency", "EUR");
	$invoice_status = get_parameter ("invoice_status", 'pending');
	$invoice_type = get_parameter ("invoice_type", "Submitted");
	$create_calendar_event = get_parameter('calendar_event');
	$language = get_parameter('id_language', $config['language_code']);
	$internal_note = get_parameter ("internal_note", "");
	
	
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
	amount4, amount5, reference, invoice_type, id_language, internal_note, invoice_expiration_date) VALUES ("%s", "%s", "%d", "%s", "%d", "%s", "%s", "%s", "%s", "%s", "%s",
	"%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s", "%s")', $description, $user_id, $id_company,
	$bill_id, $id_attachment, $invoice_create_date, $invoice_payment_date, $tax, $currency,
	$invoice_status, $concept[0], $concept[1], $concept[2], $concept[3], $concept[4], $amount[0], $amount[1],
	$amount[2], $amount[3], $amount[4], $reference, $invoice_type, $language, $internal_note, $invoice_expiration_date);
	
	$id_invoice = process_sql ($sql, 'insert_id');
	if ($id_invoice !== false) {
		if ($create_calendar_event) { 
			$now = date('Y-m-d H:i:s');
			$time = substr($now, 11, 18);
			$title = __('Reminder: Invoice ').$bill_id.__(' payment date'); 

			$sql_event ="INSERT INTO tagenda (public, alarm, timestamp, id_user,
				title, duration, description)
				VALUES (0, '1440', '$invoice_payment_date $time', '".$config['id_user']."', '$title',
				0, '')";

			$result = process_sql ($sql_event);

		}
		
		$company_name = get_db_value('name', 'tcompany', 'id', $id_company);
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "Invoice created", "Invoice Bill ID: ".$bill_id.", Company: $company_name");
		
		//update last activity
		$datetime =  date ("Y-m-d H:i:s");
		$comments = __("Invoice created by ".$config['id_user']);
		$sql_add = sprintf ('INSERT INTO tcompany_activity (id_company, written_by, date, description) VALUES (%d, "%s", "%s", "%s")', $id_company, $config["id_user"], $datetime, $comments);
		process_sql ($sql_add);
		$sql_activity = sprintf ('UPDATE tcompany SET last_update = "%s" WHERE id = %d', $datetime, $id_company);
		$result_activity = process_sql ($sql_activity);
			
		echo '<h3 class="suc">'.__('Successfully created').'</h3>';
		
	} else {
		echo '<h3 class="error">'.__('There was a problem creating the invoice').'</h3>';
	}
}

if ($operation_invoices == "update_invoice"){
	
	$filename = get_parameter ('upfile', false);
	$reference = get_parameter ("reference", "");
	$bill_id = get_parameter ("bill_id", "");
	$description = get_parameter ("description", "");
	$concept = array();
	$concept[0] = get_parameter ("concept1", "");
	$concept[1] = get_parameter ("concept2", "");
	$concept[2] = get_parameter ("concept3", "");
	$concept[3] = get_parameter ("concept4", "");
	$concept[4] = get_parameter ("concept5", "");
	$amount = array();
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
	$invoice_type = get_parameter ("invoice_type", "Submitted");
	$language = get_parameter('id_language', $config['language_code']);
	$internal_note = get_parameter('internal_note', "");
	$invoice_expiration_date = get_parameter ("invoice_expiration_date");
	
	// Updating the invoice
	$values = array();
	$values['description'] = $description;
	$values['id_user'] = $user_id;
	$values['id_company'] = $id_company;
	$values['reference'] = $reference;
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
	$values['invoice_expiration_date'] = $invoice_expiration_date;
	
	$values['invoice_type'] = $invoice_type;
	$values['id_language'] = $language;
	$values['internal_note'] = $internal_note;
	
	$where = array('id' => $id_invoice);
	
	$ret = process_sql_update ('tinvoice', $values, $where);
	
	if ($ret !== false) {
		$company_name = get_db_value('name', 'tcompany', 'id', $id_company);
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "Invoice updated", "Invoice Bill ID: ".$bill_id.", Company: $company_name");
		
		//update last activity
		$datetime =  date ("Y-m-d H:i:s");
		$comments = __("Invoice ".$id_invoice." updated by ".$config['id_user']);
		$sql_add = sprintf ('INSERT INTO tcompany_activity (id_company, written_by, date, description) VALUES (%d, "%s", "%s", "%s")', $id_company, $config["id_user"], $datetime, $comments);
		process_sql ($sql_add);
		$sql_activity = sprintf ('UPDATE tcompany SET last_update = "%s" WHERE id = %d', $datetime, $id_company);
		$result_activity = process_sql ($sql_activity);
		
		echo '<h3 class="suc">'.__('Successfully updated').'</h3>';
	} else {
		echo '<h3 class="error">'.__('There was a problem updating the invoice').'</h3>';
	}
}

if ($id_invoice > 0){
	
	$invoice = get_db_row ('tinvoice', 'id', $id_invoice);
	$reference = $invoice["reference"];
	$bill_id = $invoice["bill_id"];
	$description = $invoice["description"];
	$concept = array();
	$concept[0] = $invoice["concept1"];
	$concept[1] = $invoice["concept2"];
	$concept[2] = $invoice["concept3"];
	$concept[3] = $invoice["concept4"];
	$concept[4] = $invoice["concept5"];
	$amount = array();
	$amount[0] = $invoice["amount1"];
	$amount[1] = $invoice["amount2"];
	$amount[2] = $invoice["amount3"];
	$amount[3] = $invoice["amount4"];
	$amount[4] = $invoice["amount5"];
	$id_attachment = $invoice["id_attachment"];
	$invoice_create_date = $invoice["invoice_create_date"];
	$invoice_payment_date = $invoice["invoice_payment_date"];
	$invoice_expiration_date = $invoice["invoice_expiration_date"];
	$id_company = $invoice["id_company"];
	$tax = $invoice["tax"];
	$currency = $invoice["currency"];
	$invoice_status = $invoice["status"];
	$invoice_type = $invoice['invoice_type'];
	$language = $invoice['id_language'];
	$internal_note = $invoice['internal_note'];

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
	$reference = "";
	$description = "";
	$id_attachment = "";
	$invoice_create_date = date("Y-m-d");
	$invoice_payment_date = "";
	$invoice_expiration_date = "";
	$tax = 0;
	$currency = "EUR";
	$invoice_status = "pending";
	$invoice_type = "Submitted";
	$language = $config['language_code'];
	$internal_note = "";
}

echo "<h3>";
if ($id_invoice == "-1") {
	echo __('Add new invoice');
}
else {
	echo __('Update invoice'). " #$id_invoice";
	echo ' <a href="index.php?sec=users&amp;sec2=operation/invoices/invoice_view
				&amp;id_invoice='.$id_invoice.'&amp;clean_output=1&amp;pdf_output=1&language='.$language.'">
				<img src="images/page_white_acrobat.png" title="'.__('Export to PDF').'"></a>';
	if ($lock_permission) {
		echo ' <a href="?sec=customers&sec2=operation/companies/company_detail
			&lock_invoice=1&id='.$id_company.'&op=invoices&id_invoice='.$id_invoice.'" 
			onClick="if (!confirm(\''.__('Are you sure?').'\')) return false;">
			<img src="images/lock_open.png" title="'.__('Lock').'"></a>';
	}
	echo " <a href='#' onClick='javascript: show_validation_delete(\"delete_company_invoice\",".$id_invoice.",".$id_company.");'><img src='images/cross.png' title='".__('Delete')."'></a>";
}
echo "</h3>";

$table->id = 'cost_form';
$table->width = '98%';
$table->class = 'search-table';
$table->colspan = array ();
$table->size = array ();
$table->data = array ();

if ($id_company > 0) {
	$company_name = get_db_value ("name", "tcompany", "id", $id_company);
	$table->data[0][0] = print_input_text ('company_name', $company_name, '', 50, 100, true, __('Company'), true);
	$table->data[0][0] .= "<input type=hidden name='id' value='$id_company'>";
} else {
	// $table->data[0][0] = print_select ($companies, 'id', 0, '', '', 0, true, 0, true, __('Company'));
	$params = array();
	$params['input_id'] = 'id';
	$params['input_name'] = 'id';
	$params['title'] = __('Company');
	$params['return'] = true;
	$table->data[0][0] = print_company_autocomplete_input($params);
}

$invoice_types = array('Submitted'=>'Submitted', 'Received'=>'Received');
$table->data[0][1] = print_select ($invoice_types, 'invoice_type', $invoice_type, '','', 0, true, false, false, __('Type'));

$table->data[1][0] = print_input_text ('reference', $reference, '', 25, 100, true, __('Reference'));

$table->data[1][1] = print_input_text ('bill_id', $bill_id, '', 25, 100, true, __('Bill ID'));

if ($bill_id == ""){ // let's show the latest Invoice ID generated in the system
	$last_invoice_generated = get_db_sql ("SELECT bill_id FROM tinvoice ORDER by invoice_create_date DESC LIMIT 1");
	//$table->data[1][1] .= "<span style='font-size: 9px'> ". __("Last generated ID: "). $last_invoice_generated . "</span>";
	$table->data[1][1] .= "<div id='last_id'><span style='font-size: 9px'> ". __("Last generated ID: "). $last_invoice_generated . "</span></div>";
}

$invoice_status_ar = array();
$invoice_status_ar['pending']= __("Pending");
$invoice_status_ar['paid']= __("Paid");
$invoice_status_ar['canceled']= __("Canceled");
$table->data[2][0] = print_select ($invoice_status_ar, 'invoice_status',
	$invoice_status, '','', 0, true, false, false, __('Invoice status'));

$table->data[2][1] = print_input_text ('invoice_create_date', $invoice_create_date, '', 15, 50, true, __('Invoice creation date'));
$table->data[3][0] = print_input_text ('invoice_payment_date', $invoice_payment_date, '', 15, 50, true,__('Invoice effective payment date'));

if ($id_invoice != -1) {
	$disabled = true;
} else {
	$disabled = false;
}

if ($id_invoice == -1) {
	$table->data[3][0] .= print_checkbox_extended ('calendar_event', 1, '', false, '', '', true, __('Create calendar event'));
}

$table->data[3][1] = print_input_text ('invoice_expiration_date', $invoice_expiration_date, '', 15, 50, true,__('Invoice expiration date'));

$table->data[4][0] = print_select_from_sql ('SELECT id_language, name FROM tlanguage ORDER BY name', 'id_language', 
	$language, '', '', '', true, false, false, __('Language'));

$table->data[5][0] = "<h4>".__('Concept')."</h4>";
$table->data[5][1] = "<h4>".__('Amount')."</h4>";
$table->data[6][0] = print_input_text ('concept1', $concept[0], '', 60, 250, true);
$table->data[6][1] = print_input_text ('amount1', $amount[0], '', 10, 20, true);
$table->data[7][0] = print_input_text ('concept2', $concept[1], '', 60, 250, true);
$table->data[7][1] = print_input_text ('amount2', $amount[1], '', 10, 20, true);
$table->data[8][0] = print_input_text ('concept3', $concept[2], '', 60, 250, true);
$table->data[8][1] = print_input_text ('amount3', $amount[2], '', 10, 20, true);
$table->data[9][0] = print_input_text ('concept4', $concept[3], '', 60, 250, true);
$table->data[9][1] = print_input_text ('amount4', $amount[3], '', 10, 20, true);
$table->data[10][0] = print_input_text ('concept5', $concept[4], '', 60, 250, true);
$table->data[10][1] = print_input_text ('amount5', $amount[4], '', 10, 20, true);

$table->data[11][0] = print_input_text ('tax', $tax, '', 5, 20, true, __('Taxes (%)'));
$table->data[11][1] = print_input_text ('currency', $currency, '', 3, 3, true, __('Currency'));

if ($id_invoice != -1) {
	$amount = get_invoice_amount ($id_invoice);
	$tax = get_invoice_tax ($id_invoice);
	$tax_amount = $amount * ($tax/100);
	$total = round($amount + $tax_amount, 2);
	
	$table->data[12][0] = print_label(__('Total amount: ').format_numeric($total,2).' '.$invoice['currency'], 'total_amount', 'text', true);
	$table->data[12][1] = print_label(__('Total amount without taxes: ').format_numeric($amount,2).' '.$invoice['currency'], 'total_amount_without_taxes', 'text', true);
}

$table->colspan[14][0] = 2;
$table->data[14][0] = print_textarea ('description', 5, 40, $description, '', true, __('Description'));

$table->colspan[15][0] = 2;
$table->data[15][0] = print_textarea ('internal_note', 5, 40, $internal_note, '', true, __('Internal note'));

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

if ($id_invoice != -1) { 
	echo '</div>';
	echo '</form>';

	echo '<br>';
	echo '<ul class="ui-tabs-nav">';
	echo '<li class="ui-tabs-selected"><span>'.__('Files').'</span></li>';
	echo '<li class="ui-tabs-title">' . __('Files') . '</h1></li>';
	echo '</ul>';
	echo '<br>';

	$target_directory = 'attachment';
	$action = "index.php?sec=customers&sec2=operation/companies/company_detail&id=$id_company&id_invoice=$id_invoice&op=invoices&view_invoice=1&upload_file=1";				
	$into_form = "<input type='hidden' name='directory' value='$target_directory'><b>Description</b>&nbsp;<input type=text name=description size=60>";
	print_input_file_progress($action,$into_form,'','sub upload');	


	// List of invoice attachments
	$sql = "SELECT * FROM tattachment WHERE id_invoice = $id_invoice ORDER BY timestamp DESC";
	$files = get_db_all_rows_sql ($sql);
	$files = print_array_pagination ($files, "index.php?sec=customers&sec2=operation/companies/company_detail&id=$id_company&id_invoice=$id_invoice&op=invoices&view_invoice=1");

	if ($files !== false) {
		unset ($table);
		$table->width = "99%";
		$table->class = "listing";
		$table->data = array ();
		$table->size = array ();
		$table->style = array ();
		$table->rowstyle = array ();

		$table->head = array ();
		$table->head[0] = __('Filename');
		$table->head[1] = __('Description');
		$table->head[2] = __('Size');
		$table->head[3] = __('Date');
		$table->head[4] = __('Ops.');

		foreach ($files as $file) {
			$data = array ();
			
			$data[0] = "<a href='operation/common/download_file.php?id_attachment=".$file["id_attachment"]."&type=company'>".$file["filename"] . "</a>";
			$data[1] = $file["description"];
			$data[2] = format_numeric($file["size"]);
			$data[3] = $file["timestamp"];

			// Todo. Delete files owner and admins only
			if ( (dame_admin($config["id_user"])) || ($file["id_usuario"] == $config["id_user"]) ){
				$data[4] = "<a href='index.php?sec=customers&sec2=operation/companies/company_detail&id=$id_company&id_invoice=$id_invoice&op=invoices&view_invoice=1&deletef=".$file["id_attachment"]."'><img src='images/cross.png'></a>";
			}

			array_push ($table->data, $data);
			array_push ($table->rowstyle, $style);
		}
		print_table ($table);

	} else {
		echo "<h3>". __('There is no files attached for this invoice')."</h3>";
	}
}
?>

<script type="text/javascript" src="include/js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
<script type="text/javascript" src="include/js/jquery.validate.js"></script>
<script type="text/javascript" src="include/js/jquery.validation.functions.js"></script>
<script type="text/javascript" src="include/js/integria_date.js"></script>

<script type="text/javascript" src="include/js/agenda.js"></script>

<script type="text/javascript">
	
// Datepicker
add_ranged_datepicker ("#text-invoice_create_date", "#text-invoice_payment_date", null);
add_ranged_datepicker ("#text-invoice_payment_date", "#text-invoice_expiration_date", null);

// Form validation
trim_element_on_submit('#text-bill_id');

validate_form("#form-invoice");
var rules, messages;

if (<?php echo json_encode((int)$id_company) ?> <= 0) {
	// Rules: #id
	rules = { required: true };
	messages = { required: "<?php echo __('Company required'); ?>" };
	add_validate_form_element_rules('#id', rules, messages);
}

// Rules: #text-bill_id
rules = {
	required: true,
	remote: {
		url: "ajax.php",
		type: "POST",
		data: {
			page: "include/ajax/remote_validations",
			search_existing_invoice: 1,
			invoice_type: function() { return $('#invoice_type').val() },
			bill_id: function() { return $('#text-bill_id').val() },
			invoice_id: <?php echo $id_invoice ?>
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


$(document).ready (function () {

	var idUser = "<?php echo $config['id_user'] ?>";
	if (<?php echo json_encode((int)$id_company) ?> <= 0) {
		bindCompanyAutocomplete ('id', idUser, 'invoice');
	}

	$("#invoice_type").click (function () {
		if ($("#invoice_type").val() == 'Received') {
			$("#last_id").css('display', 'none');
		} else {
			$("#last_id").css('display', '');
		}
	});
});
</script>
