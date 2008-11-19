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

check_login ();
	
if (! dame_admin ($config["id_user"])) {
	audit_db ("ACL Violation", $config["REMOTE_ADDR"], "No administrator access", "Trying to access setup");
	require ("general/noaccess.php");
	exit;
}

$update = (bool) get_parameter ("update");

if ($update) {
	$config["notification_period"] = (int) get_parameter ("notification_period", 86400);
	$config["FOOTER_EMAIL"] = (string) get_parameter ("footer_email", "");
	$config["HEADER_EMAIL"] = (string) get_parameter ("header_email", "");
	$config["mail_from"] = (string) get_parameter ("mail_from");

	
	process_sql ("UPDATE tconfig SET value='".$config["notification_period"]."' WHERE token='notification_period'");
	process_sql ("UPDATE tconfig SET value='".$config["FOOTER_EMAIL"]."' WHERE token='FOOTER_EMAIL'");

	process_sql ("UPDATE tconfig SET value='".$config["HEADER_EMAIL"]."' WHERE token='HEADER_EMAIL'");

	process_sql ("DELETE FROM tconfig WHERE token = 'mail_from'");
	process_sql ("INSERT INTO tconfig (token, value) VALUES ('mail_from', '".$config["mail_from"]."')");
}	

echo "<h2>".__('Mail setup')."</h2>";

$table->width = '90%';
$table->class = 'databox';
$table->colspan = array ();
$table->colspan[5][0] = 2;
$table->colspan[6][0] = 2;
$table->data = array ();

$table->data[2][0] = print_input_text ("notification_period", $config["notification_period"],
	'', 7, 7, true, __('Notification period'));
$table->data[2][0] .= integria_help ("notification_period", true);

$table->data[2][1] = print_input_text ("mail_from", $config["mail_from"], '',
	30, 50, true, __('System mail from address'));


$table->data[5][0] = print_textarea ("header_email", 5, 40, $config["HEADER_EMAIL"],
	'', true, __('Email header'));
$table->data[6][0] = print_textarea ("footer_email", 5, 40, $config["FOOTER_EMAIL"],
	'', true, __('Email footer'));

echo "<form name='setup' method='post'>";

print_table ($table);

echo '<div style="width: '.$table->width.'" class="button">';
print_input_hidden ('update', 1);
print_submit_button (__('Update'), 'upd_button', false, 'class="sub upd"');
echo '</div>';
echo '</form>';
?>

<script type="text/javascript">
$(document).ready (function () {
	$("textarea").TextAreaResizer ();
});
</script>
