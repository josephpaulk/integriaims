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

// Load global vars
global $config;

check_login ();
	
if (! dame_admin ($config["id_user"])) {
	audit_db ("ACL Violation", $config["REMOTE_ADDR"], "No administrator access", "Trying to access setup");
	require ("general/noaccess.php");
	exit;
}

/* Tabs code */
echo '<div id="tabs">';

/* Tabs list */
echo '<ul class="ui-tabs-nav">';
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/setup"><span><img src="images/cog.png" title="'.__('Setup').'"></span></a></li>';
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/setup_visual"><span><img src="images/chart_bar.png" title="'.__('Visual setup').'"></span></a></li>';
if ($is_enterprise) {
	echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=enterprise/godmode/setup/setup_password"><span valign=bottom><img src="images/lock.png" title="'.__('Password policy').'"></span></a></li>';
}
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/incidents_setup"><span><img src="images/bug.png" title="'.__('Incident setup').'"></span></a></li>';
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/setup_mail"><span><img src="images/email.png"  title="'.__('Mail setup').'"></span></a></li>';
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/setup_mailtemplates"><span><img src="images/email_edit.png"  title="'.__('Mail templates setup').'"></span></a></li>';
if ($is_enterprise) {
	echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=enterprise/godmode/usuarios/menu_visibility_manager"><span valign=bottom><img src="images/eye.png" title="'.__('Visibility management').'"></span></a></li>';
}
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/setup_pandora"><span><img src="images/pandora.ico"  title="'.__('Pandora FMS inventory').'"></span></a></li>';
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/setup_auth"><span><img src="images/book_edit.png"  title="'.__('Authentication').'"></span></a></li>';
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/setup_crm"><span><img src="images/page_white_text.png"  title="'.__('CRM setup').'"></span></a></li>';
echo '<li class="ui-tabs-selected"><a href="index.php?sec=godmode&sec2=godmode/setup/setup_maintenance"><span><img src="images/objects/trash.png"  title="'.__('Old data maintenance').'"></span></a></li>';
echo '</ul>';

$update = (bool) get_parameter ("update");

if ($update) {
	$config["max_days_events"] = (int) get_parameter ("max_days_events", 30);
	$config["max_days_incidents"] = (int) get_parameter ("max_days_incidents", 365);
	$config["max_days_wu"] = (int) get_parameter ("max_days_wu", 365);
	$config["max_days_wo"] = (int) get_parameter ("max_days_wo", 365);
	$config["max_days_audit"] = (int) get_parameter ("max_days_audit", 15);
	$config["max_days_session"] = (int) get_parameter ("max_days_session", 7);
	
	update_config_token ("max_days_events", $config["max_days_events"]);
	update_config_token ("max_days_incidents", $config["max_days_incidents"]);
	update_config_token ("max_days_wu", $config["max_days_wu"]);
	update_config_token ("max_days_wo", $config["max_days_wo"]);
	update_config_token ("max_days_audit", $config["max_days_audit"]);
	update_config_token ("max_days_session", $config["max_days_session"]);
}

echo "<h2>".__('Old data maintenance')."</h2>";

$table->width = '90%';
$table->class = 'databox';
$table->colspan = array ();
$table->data = array ();

$table->data[0][0] = print_input_text ("max_days_events", $config["max_days_events"], '', 4, 4, true, __('Days to delete events'));
$table->data[0][0] .= integria_help ("old_events", true);
$table->data[0][1] = print_input_text ("max_days_incidents", $config["max_days_incidents"], '', 4, 4, true, __('Days to delete  incidents'));
$table->data[0][1] .= integria_help ("old_incidents", true);

$table->data[1][0] = print_input_text ("max_days_wu", $config["max_days_wu"], '', 4, 4, true, __('Days to delete work units'));
$table->data[1][0] .= integria_help ("old_wu", true);
$table->data[1][1] = print_input_text ("max_days_wo", $config["max_days_wo"], '', 4, 4, true, __('Days to delete work orders'));
$table->data[1][1] .= integria_help ("old_wo", true);

$table->data[2][0] = print_input_text ("max_days_audit", $config["max_days_audit"], '', 4, 4, true, __('Days to delete audit data'));
$table->data[2][0] .= integria_help ("old_audit", true);
$table->data[2][1] = print_input_text ("max_days_session", $config["max_days_session"], '', 4, 4, true, __('Days to delete sessions'));
$table->data[2][1] .= integria_help ("old_sessions", true);

echo '<form id="setup_maintenance" name="setup" method="post">';

print_table ($table);

echo '<div style="width: '.$table->width.'" class="button">';
print_input_hidden ('update', 1);
print_submit_button (__('Reset to default'), 'reset_button', false, 'class="sub upd"');
print_submit_button (__('Update'), 'upd_button', false, 'class="sub upd"');
echo '</div>';
echo '</form>';

?>

<script type="text/javascript">
$(document).ready (function () {
	$("#submit-reset_button").click(function() {
		$("#text-max_days_events").val("");
		$("#text-max_days_incidents").val("");
		$("#text-max_days_wu").val("");
		$("#text-max_days_wo").val("");
		$("#text-max_days_audit").val("");
		$("#text-max_days_session").val("");
	});
});
</script>
