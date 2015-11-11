<?php

global $config;
check_login ();

$res = include_once($config["homedir"].'include/functions_crm.php');

$id_invoice = (int) get_parameter ("id_invoice", -1);
if ($id_invoice == -1) {
	echo '<h3>'.__('The invoice number is required'),'</h3>';
	return;
}
$invoice = get_db_row ("tinvoice", "id", $id_invoice);
if (!$invoice) {
	echo '<h3>'.__('This invoice does not exists').'</h3>';
	return;
}

// ACL
if (!isset($permission)) {
	$id_company = get_db_value("id_company", "tinvoice", "id", $id_invoice);
	$permission = check_crm_acl ('invoice', '', $config['id_user'], $id_company);
}
if (!$permission) {
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to an invoice view without permission");
	no_permission();
} // ACL


$company_to = get_db_row ("tcompany", "id", $invoice["id_company"]);

$amount = get_invoice_amount ($id_invoice);
$tax = get_invoice_tax ($id_invoice);
$discount_before = get_invoice_discount_before ($id_invoice);
$discount_after = get_invoice_discount_after ($id_invoice);

$before_amount = $amount * ($discount_before/100);
$total_before = round($amount - $before_amount, 2);
$tax_amount = $total_before * ($tax/100);
$total_before_tax = round($total_before + $tax_amount, 2);
$after_amount = ($total_before_tax) * ($discount_after/100);
$total = round(($total_before_tax) - $after_amount, 2);

$custom_pdf = true;
$pdf_filename = "invoice_".$invoice["bill_id"].".pdf";
$header_logo = "images/".$config["invoice_logo"];
$header_text = $config["invoice_header"];
$footer_text= $config["invoice_footer"];

// The template of the invoice view can be changed here
include ("invoice_template.php");

?>
