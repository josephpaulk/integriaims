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

$id_company = get_parameter ("id_company", -1);
$company = get_db_row ('tcompany', 'id', $id_company);
$id_invoice = get_parameter ("id_invoice", -1);
$operation = get_parameter ("operation");

if ($id_company > 0){
	if (! give_acl ($config["id_user"], $company["id_group"], "IR")) {
		// Doesn't have access to this page
		audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to add invoices in a company without access");
		no_permission();
	}
}

if ($id_invoice > 0){
	$invoice = get_db_row ('tinvoice', 'id', $id_invoice);
	
	if (! give_acl ($config["id_user"], $invoice ["id_group"], "IW")) {
		// Doesn't have access to this page
		audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to modify an invoices in a company without access");
		no_permission();
	}
	$bill_id = $invoice["bill_id"];
	$description = $invoice["description"];
	$ammount = $invoice["ammount"];
	$id_attachment = $invoice["id_attachment"];
	$invoice_create_date = $invoice["invoice_create_date"];
	$invoice_payment_date = $invoice["invoice_payment_date"];
	$id_company = $invoice["id_company"];
	
} else {
	$bill_id = "N/A";
	$description = "";
	$ammount = "0.00";
	$id_attachment = "";
	$invoice_create_date = "2011-01-30";
	$invoice_payment_date = "2011-03-30";
}

if ($operation == "add"){
	$filename = get_parameter ('upfile', false);
	$bill_id = get_parameter ("bill_id", "");
	$description = get_parameter ("description", "");
	$ammount = (float) get_parameter ("ammount", 0);
	$user_id = $config["id_user"];
	$invoice_create_date = get_parameter ("invoice_create_date");
	$invoice_payment_date = get_parameter ("invoice_payment_date");
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
	bill_id, ammount, id_attachment, invoice_create_date, invoice_payment_date) VALUES ("%s", "%s", %d, "%s", "%s", %d, "%s", "%s")',
			$description, $user_id, $id_company, $bill_id, $ammount, $id_attachment, $invoice_create_date, $invoice_payment_date);
	
	$ret = process_sql ($sql, 'insert_id');
	if ($ret !== false) {
		echo '<h3 class="suc">'.__('Successfully created').'</h3>';
	} else {
		echo '<h3 class="error">'.__('There was a problem creating the invoice').'</h3>';
	}
	
	$operation = "";
}

if ($operation == "update"){
	$values = array();
	
	$filename = get_parameter ('upfile', false);
	$bill_id = get_parameter ("bill_id", "");
	$description = get_parameter ("description", "");
	$ammount = (float) get_parameter ("ammount", 0);
	$user_id = $config["id_user"];
	$invoice_create_date = get_parameter ("invoice_create_date");
	$invoice_payment_date = get_parameter ("invoice_payment_date");
	
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
	$values['ammount'] = $ammount;
	$values['invoice_create_date'] = $invoice_create_date;
	$values['invoice_payment_date'] = $invoice_payment_date;
	
	$where = array('id' => $id_invoice);
	
	$ret = process_sql_update ('tinvoice', $values, $where);
	
	if ($ret !== false) {
		echo '<h3 class="suc">'.__('Successfully updated').'</h3>';
	} else {
		echo '<h3 class="error">'.__('There was a problem updating the invoice').'</h3>';
	}
	
	$operation = "";
}

if ($operation == ""){

	echo "<h3>";
	if ($id_invoice == "")
		echo __('Add new invoice');
	else
		echo __('Update invoice'). " #$id_invoice";
	echo "</h3>";
	echo "<div id='upload_control'>";
	
	$action = "index.php?sec=customers&sec2=operation/invoices/invoices&id_company=$id_company";
	
	$table->id = 'cost_form';
	$table->width = '90%';
	$table->class = 'listing';
	$table->size = array ();
	$table->data = array ();
	
	$table->data[0][0] = __('Bill ID');
	$table->data[0][1] = print_input_text ('bill_id', $bill_id, '', 25, 100, true);
	
	$table->data[1][0] = __('Ammount');
	$table->data[1][1] = print_input_text ('ammount', $ammount, '', 10, 20, true);
	
	$table->data[2][0] = __('Description');
	$table->data[2][1] = print_input_text ('description', $description, '', 60, 250, true);
	
	$table->data[3][0] = __('Attach a file');
	$table->data[3][1] = '__UPLOAD_CONTROL__';

	$table->data[4][0] = __('Invoice creation date');
	$table->data[4][1] = print_input_text ('invoice_create_date', $invoice_create_date, '', 15, 50, true);

	$table->data[5][0] = __('Invoice effective payment date');
	$table->data[5][1] = print_input_text ('invoice_payment_date', $invoice_payment_date, '', 15, 50, true);

	$into_form = print_table ($table, true);

	$into_form .= '<div class="button" style="width: '.$table->width.'">';
	if ($id_invoice == -1) {
		$into_form .= print_button (__('Add'), "crt", false, '', 'class="sub next"', true);
		$into_form .= print_input_hidden ('operation', "add", true);
		$button_name = "button-crt";
	} else {
		$into_form .= print_input_hidden ('id_invoice', $id_invoice, true);
		$into_form .= print_input_hidden ('operation', "update", true);
		$into_form .= print_button (__('Update'), "upd", false, '', 'class="sub upd"', true);
		$button_name = "button-upd";
	}
	
	$into_form .= print_input_hidden ('id_company', $id_company, true);
	
	$into_form .= "</div>";	
	
	print_input_file_progress($action, $into_form, 'id="form-add-file"', 'sub next', $button_name, false, '__UPLOAD_CONTROL__');

	echo "</div>";
	
}
?>
