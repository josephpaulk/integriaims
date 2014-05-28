<?php 

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// Load global vars
global $config;
include_once('include/functions_setup.php');

check_login ();
	
if (! dame_admin ($config["id_user"])) {
	audit_db ("ACL Violation", $config["REMOTE_ADDR"], "No administrator access", "Trying to access setup");
	require ("general/noaccess.php");
	exit;
}

$is_enterprise = false;
if (file_exists ("enterprise/load_enterprise.php")) {
	$is_enterprise = true;
}
	
/* Tabs list */
print_setup_tabs('mail', $is_enterprise);

$update = (bool) get_parameter ("update");

$pending_ok = (bool) get_parameter ("pending_ok");
$pending_delete = (bool) get_parameter ("pending_delete");

if ($pending_ok){
	echo "<h3 class='suc'>".__('Mail queue refreshed')."</h3>";
	process_sql ("UPDATE tpending_mail SET attempts = 0, status = 0 WHERE status = 1");
}

if ($pending_delete){
	echo "<h3 class='suc'>".__('Mail queue deleted')."</h3>";
        process_sql ("DELETE FROM tpending_mail");
}

if ($update) {
	$config["notification_period"] = (int) get_parameter ("notification_period", 86400);
	$config["FOOTER_EMAIL"] = (string) get_parameter ("footer_email", "");
	$config["HEADER_EMAIL"] = (string) get_parameter ("header_email", "");
	$config["mail_from"] = (string) get_parameter ("mail_from");
	$config["smtp_user"] = (string) get_parameter ("smtp_user");
	$config["smtp_pass"] = (string) get_parameter ("smtp_pass");
	$config["smtp_host"] = (string) get_parameter ("smtp_host");
	$config["smtp_port"] = (string) get_parameter ("smtp_port");
	$config["news_smtp_user"] = (string) get_parameter ("news_smtp_user");
	$config["news_smtp_pass"] = (string) get_parameter ("news_smtp_pass");
	$config["news_smtp_host"] = (string) get_parameter ("news_smtp_host");
	$config["news_smtp_port"] = (string) get_parameter ("news_smtp_port");
	$config["pop_user"] = (string) get_parameter ("pop_user");
	$config["pop_pass"] = (string) get_parameter ("pop_pass");
	$config["pop_host"] = (string) get_parameter ("pop_host");
	$config["pop_port"] = (string) get_parameter ("pop_port");
	$config["smtp_queue_retries"] = (int) get_parameter ("smtp_queue_retries", 10);
	$config["max_pending_mail"] = get_parameter ("max_pending_mail", 15);
	$config["batch_newsletter"] = get_parameter ("batch_newsletter", 0);
	$config["news_batch_newsletter"] = get_parameter ("news_batch_newsletter", 0);
	$config["batch_email_validation"] = get_parameter ("batch_email_validation", 0);
	$config["active_validate"] = get_parameter("active_validate", 0);
	
	update_config_token ("HEADER_EMAIL", $config["HEADER_EMAIL"]);
	update_config_token ("FOOTER_EMAIL", $config["FOOTER_EMAIL"]);
	update_config_token ("notification_period", $config["notification_period"]);
	update_config_token ("mail_from", $config["mail_from"]);
	update_config_token ("smtp_port", $config["smtp_port"]);
	update_config_token ("smtp_host", $config["smtp_host"]);
	update_config_token ("smtp_user", $config["smtp_user"]);
	update_config_token ("smtp_pass", $config["smtp_pass"]);
	update_config_token ("news_smtp_port", $config["news_smtp_port"]);
	update_config_token ("news_smtp_host", $config["news_smtp_host"]);
	update_config_token ("news_smtp_user", $config["news_smtp_user"]);
	update_config_token ("news_smtp_pass", $config["news_smtp_pass"]);
	update_config_token ("pop_host", $config["pop_host"]);
	update_config_token ("pop_user", $config["pop_user"]);
	update_config_token ("pop_pass", $config["pop_pass"]);
	update_config_token ("pop_port", $config["pop_port"]);
	update_config_token ("smtp_queue_retries", $config["smtp_queue_retries"]);
	update_config_token ("max_pending_mail", $config["max_pending_mail"]);
	update_config_token ("batch_newsletter", $config["batch_newsletter"]);
	update_config_token ("news_batch_newsletter", $config["news_batch_newsletter"]);
	update_config_token ("batch_email_validation", $config["batch_email_validation"]);
	update_config_token ("active_validate", $config["active_validate"]);
}

$table->width = '99%';
$table->class = 'search-table-button';
$table->colspan = array ();

$table->data = array ();

$table->data[2][0] = print_input_text ("notification_period", $config["notification_period"],
	'', 7, 7, true, __('Notification period'));
$table->data[2][0] .= integria_help ("notification_period", true);

$table->data[2][1] = print_input_text ("mail_from", $config["mail_from"], '',
	30, 50, true, __('System mail from address'));

$table->colspan[3][0] = 3;
$table->data[3][1] = "<h4>".__("SMTP Parameters"). integria_help ("mailsetup", true). "</h4>";

$table->data[4][0] = print_input_text ("smtp_host", $config["smtp_host"],
	'', 35, 200, true, __('SMTP Host'));

$table->data[4][0] .= print_help_tip (__("Left it blank if you want to use your local mail, instead an external SMTP host"), true);


$table->data[4][1] = print_input_text ("smtp_port", $config["smtp_port"],
	'', 5, 10, true, __('SMTP Port'));

$table->data[5][0] = print_input_text ("smtp_user", $config["smtp_user"],
	'', 25, 200, true, __('SMTP User'));

$table->data[5][1] = print_input_text ("smtp_pass", $config["smtp_pass"],
	'', 25, 200, true, __('SMTP Password'));

$table->data[6][0] = print_input_text ("smtp_queue_retries", $config["smtp_queue_retries"],
        '', 5, 10, true, __('SMTP Queue retries'));

$table->data[6][0] .= print_help_tip (__("This are the number of attempts the mail queue try to send the mail. Should be high (20-30) if your internet connection have frequent downtimes and near zero if its stable"), true);

$table->data[6][1] = print_input_text ("max_pending_mail", $config["max_pending_mail"], '',
        10, 255, true, __('Max pending mail'));
$table->data[6][1] .= print_help_tip (__("Maximum number of queued emails. When this number is exceeded, an alert is activated"), true);

$table->data[7][0] = print_input_text ("batch_newsletter", $config["batch_newsletter"], '',
        4, 255, true, __('Max. emails sent per execution'));


$table->data[7][0] .= print_help_tip (__("This means, in each execution of the batch external process (integria_cron). If you set your cron to execute each hour in each execution of that process will try to send this ammount of emails. If you set the cron to run each 5 min, will try this number of mails."), true);
   
$table->colspan[8][0] = 3;
$table->data[8][1] = "<h4>".__("POP/IMAP Parameters")."</h4>";

$table->data[9][0] = print_input_text ("pop_host", $config["pop_host"],
	'', 25, 30, true, __('POP/IMAP Host'));

$table->data[9][0] .= print_help_tip (__("Use ssl://host.domain.com if want to use IMAP with SSL"), true);


$table->data[9][1] = print_input_text ("pop_port", $config["pop_port"],
	'', 15, 30, true, __('POP/IMAP Port'));	

$table->data[9][1] .= print_help_tip (__("POP3: Port 110, IMAP: Port 143, IMAPS: Port 993, SSL-POP: Port 995"), true);

$table->data[10][0] = print_input_text ("pop_user", $config["pop_user"],
	'', 15, 30, true, __('POP/IMAP User'));

$table->data[10][1] = print_input_text ("pop_pass", $config["pop_pass"], 
	'', 15, 30, true, __('POP/IMAP Password'));
				
$table->data[11][1] = "<h4>".__("Newsletter SMTP Parameters")."</h4>";

$table->data[12][0] = print_input_text ("news_smtp_host", $config["news_smtp_host"],
	'', 35, 200, true, __('SMTP Host'));

$table->data[12][1] = print_input_text ("news_smtp_port", $config["news_smtp_port"],
	'', 5, 10, true, __('SMTP Port'));

$table->data[13][0] = print_input_text ("news_smtp_user", $config["news_smtp_user"],
	'', 25, 200, true, __('SMTP User'));

$table->data[13][1] = print_input_text ("news_smtp_pass", $config["news_smtp_pass"],
	'', 25, 200, true, __('SMTP Password'));


$table->data[14][0] = print_input_text ("news_batch_newsletter", $config["news_batch_newsletter"], '',
        4, 255, true, __('Max. emails sent per execution'));


$table->data[14][0] .= print_help_tip (__("This means, in each execution of the batch external process (integria_cron). If you set your cron to execute each hour in each execution of that process will try to send this ammount of emails. If you set the cron to run each 5 min, will try this number of mails."), true);

$table->data[14][1] = print_input_text ("batch_email_validation", $config["batch_email_validation"], '',
        4, 255, true, __('Newsletter email validation batch'));
$table->data[14][1] .= print_help_tip (__("This means, in each execution of the batch external process (integria_cron) will try to validate this ammount of emails."), true);

$table->data[15][0] =  print_checkbox ("active_validate", 1, $config["active_validate"], true, __('Activate email validation'));

$table->data[16][1] = "<h4>".__("Mail general texts")."</h4>";

$table->colspan[17][0] = 3;
$table->colspan[18][0] = 3;
$table->colspan[19][0] = 3;
$table->data[17][0] = print_textarea ("header_email", 5, 40, $config["HEADER_EMAIL"],
	'', true, __('Email header'));
$table->data[18][0] = print_textarea ("footer_email", 5, 40, $config["FOOTER_EMAIL"],
	'', true, __('Email footer'));

$table->data[19][1] = "<h4>".__("Mail queue control");

$total_pending = get_db_sql ("SELECT COUNT(*) from tpending_mail");

$table->data[19][1] .= " : ". $total_pending . " " .__("mails in queue") . "</h4>";

if ($total_pending > 0) {

	$table->colspan[20][0] = 3;

	$mail_queue = "<div style='height: 250px; overflow-y: auto;'>";
	$mail_queue .= "<table width=100% class=listing>";
	$mail_queue .= "<tr><th>". __("Date"). "<th>" . __("Recipient") . "<th>" . __("Subject") . "<th>" . __("Attempts")."<th>". __("Status")."</tr>";

	$mails = get_db_all_rows_sql ("SELECT * FROM tpending_mail LIMIT 1000");


	foreach ($mails as $mail) {
		$mail_queue .=  "<tr>";
		$mail_queue .=  "<td style='font-size: 9px;'>";
		$mail_queue .=  $mail["date"];
		$mail_queue .=  "<td>";
		$mail_queue .=  $mail["recipient"];
		$mail_queue .=  "<td style='font-size: 9px;'>";
		$mail_queue .=  $mail["subject"];
		$mail_queue .=  "<td>";
		$mail_queue .=  $mail["attempts"];
		if ($mail["status"] == 1)
			$mail_queue .=  "<td>".__("Bad mail");
		else
			$mail_queue .=  "<td>".__("Pending");
		$mail_queue .=  "</tr>";
	}

	$mail_queue .= "<tr></tr></table></div>";

	$table->data[20][0] = $mail_queue;
}

$button = print_input_hidden ('update', 1, true);

$button .= print_submit_button (__("Reactivate pending mails"), 'pending_ok', false, 'class="sub create"', true);
$button .= print_submit_button (__("Delete pending mails"), 'pending_delete', false, 'class="sub delete"', true);
$button .= print_submit_button (__('Update'), 'upd_button', false, 'class="sub upd"', true);

$table->data[21][0] = $button;
$table->colspan[21][0] = 3;

echo "<form name='setup' method='post'>";
print_table ($table);
echo '</form>';
?>

<script type="text/javascript">
$(document).ready (function () {
	$("textarea").TextAreaResizer ();
});
</script>
