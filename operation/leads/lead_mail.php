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

check_login ();


if (($id == "") OR ($id == 0))
	return;

$id_template = get_parameter ("id_template");

$lead = get_db_row('tlead','id',$id);

$user = get_db_row("tusuario", "id_usuario", $config["id_user"]);
$template = get_db_row("tcrm_template", "id", $id_template);
$id_company = $lead["id_company"];

$from = get_parameter ("from", $user["direccion"]);
$to = get_parameter ("to", $lead["email"]);
$subject = get_parameter ("subject", $template["subject"]);
$mail = get_parameter ("mail", $template["description"]);
$send = (int) get_parameter ("send",0);
$cco = get_parameter ("cco", "");

// Send mail
if ($send) {
	if (($subject != "") AND ($from != "") AND ($to != "")) {
		echo "<h3 class='suc'>".__('Mail queued')."</h3>";

		integria_sendmail ($to, $subject, $mail, false, "", $from, true);

		if ($cco != "")
			integria_sendmail ($cco, $subject, $mail, false, "", $from, true);

		// Lead update
		if ($lead["progress"] == 0 ){
			//Update lead progress is was on 0%
			$sql = sprintf ('UPDATE tlead SET modification = "%s", progress = %d WHERE id = %d',
		date('Y-m-d H:i:s'), 10, $id);
		} else {
			$sql = sprintf ('UPDATE tlead SET modification = "%s" WHERE id = %d',
		date('Y-m-d H:i:s'), $id);
		}
		process_sql ($sql);		

		// Update tracking
		$sql = sprintf ('INSERT INTO tlead_history (id_lead, id_user, timestamp, description) VALUES (%d, "%s", "%s", "%s")', $id, $config["id_user"], $datetime, "Send mail from CRM");
		process_sql ($sql);

		// Update activity
		$datetime =  date ("Y-m-d H:i:s");
		$comments = __("Send email from CRM"). "&#x0d;&#x0a;".__("Subject"). " : ". $subject . "&#x0d;&#x0a;" . $mail; // this adds &#x0d;&#x0a; 
		$sql = sprintf ('INSERT INTO tlead_activity (id_lead, written_by, creation, description) VALUES (%d, "%s", "%s", "%s")', $id, $config["id_user"], $datetime, $comments);
		process_sql ($sql);

	} else {
		echo "<h3 class='error'>".__('Could not be created')."</h3>";
	}
}


// Mark with case ID
$subject = "[#$id] " . $subject;

// Replace mail macros
/*_DEST_NAME_ -> Lead fullname
_DEST_EMAIL_ -> Lead email
_SRC_NAME_ -> Current user fullname
_SRC_EMAIL_ -> Current user email
*/

$mail = str_replace ("_DEST_NAME_", $lead["fullname"], $mail);
$mail = str_replace ("_DEST_EMAIL_", $lead["email"], $mail);
$mail = str_replace ("_SRC_NAME_", $user["nombre_real"], $mail);
$mail = str_replace ("_SRC_EMAIL_", $user["direccion"], $mail);

$sql = "SELECT id, name FROM tcrm_template WHERE id_language = '". $lead["id_language"]. "' ORDER BY name DESC";

$id_template = (int) get_parameter ("id_template");


// Show form with available templates for this useraco
echo '<form method="post" id="lead_mail_filter">';
echo "<table width=300px>";
echo "<tr><td valign=top> ";
echo print_select_from_sql ($sql, 'id_template', $id_template, '', __("None"), 0, true, false, true, __("CRM Template to use"));
echo "</td><td valign=bottom>";
print_submit_button (__('Apply'), 'apply_btn', false, 'class="sub upd"', false);
print_input_hidden ('id', $id);
echo "</td></tr></table>";
echo "</form>";


$table->width = "75%";
$table->class = "databox";
$table->data = array ();
$table->size = array ();
$table->style = array ();
$table->style[0] = 'font-weight: bold';

$table->colspan[2][0] = 3;
$table->colspan[1][0] = 3;

$table->data[0][0] = print_input_text ("from", $from, "", 30, 100, true, __('From'));
$table->data[0][1] = print_input_text ("to", $to, "", 30, 100, true, __('To'));
$table->data[0][2] = print_input_text ("cco", $cco, "", 30, 100, true, __('Send a copy to'));
$table->data[1][0] = print_input_text ("subject", $subject, "", 80, 100, true, __('Subject'));
$table->data[2][0] = print_textarea ("mail", 10, 1, $mail, 'style="height:350px;"', true, __('E-mail'));

echo '<form method="post" id="lead_mail_go">';
print_table ($table);
echo '<div class="button" style="width: '.$table->width.'">';
print_submit_button (__('Send email'), 'apply_btn', false, 'class="sub upd"', false);
print_input_hidden ('id', $id);
print_input_hidden ('send', 1);
echo "</div></form>";

?>

<script type="text/javascript" src="include/js/jquery.validate.js"></script>
<script type="text/javascript" src="include/js/jquery.validation.functions.js"></script>

<script type="text/javascript" >

validate_form("#lead_mail_go");
// Rules: #text-from
rules = {
	required: true,
	email: true
};
messages = {
	required: "<?=__('Email from required')?>",
	email: "<?=__('Invalid email')?>"
};
add_validate_form_element_rules('#text-from', rules, messages);
// Rules: #text-to
rules = {
	required: true,
	email: true
};
messages = {
	required: "<?=__('Email to required')?>",
	email: "<?=__('Invalid email')?>"
};
add_validate_form_element_rules('#text-to', rules, messages);
// Rules: #text-cco
rules = {
	email: true
};
messages = {
	email: "<?=__('Invalid email')?>"
};
add_validate_form_element_rules('#text-cco', rules, messages);

</script>
