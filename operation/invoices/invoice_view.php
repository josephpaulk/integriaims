<?php

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
if (!isset($invoice_permission)) {
	enterprise_include('include/functions_crm.php');
	$invoice_permission = enterprise_hook('crm_check_acl_invoice', array($config['id_user'], $invoice['id_company']));
}
if ($invoice_permission === ENTERPRISE_NOT_HOOK) {
	$invoice_permission = true;
}
if (!$invoice_permission) {
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to an invoice view without permission");
	no_permission();
} // ACL

$company_from = get_user_company ($invoice["id_user"], $only_name = false);
if ($company_from == array())
	exit;
$company_to = get_db_row ("tcompany", "id", $invoice["id_company"]);

$amount = get_invoice_amount ($id_invoice);
$tax = get_invoice_tax ($id_invoice);
$tax_amount = $amount * ($tax/100);
//$total = round(get_invoice_amount ($id_invoice, $with_taxes = true), 2);
$total = round($amount + $tax_amount, 2);

// The template of the invoice view can be changed here
include ("invoice_template.php");

$custom_pdf = true;
$header_logo = $config["invoice_logo"];
$header_logo_alignment = $config["invoice_logo_alignment"];
$header_text = $config["invoice_header"];
$footer_text= $config["invoice_footer"];

?>
