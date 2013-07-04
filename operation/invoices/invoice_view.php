<?php 

$id_invoice = (int) get_parameter ("id_invoice", -1);
if ($id_invoice == -1) {
	echo __('The invoice number is required');
	return;
}
$invoice = get_db_row ("tinvoice", "id", $id_invoice);
if (!$invoice) {
	echo __('This invoice does not exists');
	return;
}

$company_from = get_user_company ($invoice["id_user"], $only_name = false);
if ($company_from == array())
	exit;
$company_to = get_db_row ("tcompany", "id", $invoice["id_company"]);

$subtotal = get_invoice_ammount ($id_invoice);
$tax = get_invoice_tax ($id_invoice);
//$total = round(get_invoice_ammount ($id_invoice, $with_taxes = true), 2);
$total = round($subtotal + ($subtotal * $tax), 2);

// The template of the invoice view can be changed here
include ("invoice_template.php");

$custom_pdf = true;
$header_logo = $config["invoice_logo"];
$header_logo_alignment = $config["invoice_logo_alignment"];
$header_text = $config["invoice_header"];
$footer_text= $config["invoice_footer"];

?>
