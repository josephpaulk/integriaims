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
print_setup_tabs('maintenance', $is_enterprise);

$update = (bool) get_parameter ("update");

if ($update) {
	$config["max_days_events"] = (int) get_parameter ("max_days_events", 30);
	$config["max_days_incidents"] = (int) get_parameter ("max_days_incidents", 365);
	$config["max_days_wu"] = (int) get_parameter ("max_days_wu", 365);
	$config["max_days_wo"] = (int) get_parameter ("max_days_wo", 365);
	$config["max_days_audit"] = (int) get_parameter ("max_days_audit", 15);
	$config["max_days_session"] = (int) get_parameter ("max_days_session", 7);
	$config["max_days_workflow_events"] = (int) get_parameter ("max_days_workflow_events", 900);
	
	update_config_token ("max_days_events", $config["max_days_events"]);
	update_config_token ("max_days_incidents", $config["max_days_incidents"]);
	update_config_token ("max_days_wu", $config["max_days_wu"]);
	update_config_token ("max_days_wo", $config["max_days_wo"]);
	update_config_token ("max_days_audit", $config["max_days_audit"]);
	update_config_token ("max_days_session", $config["max_days_session"]);
	update_config_token ("max_days_workflow_events", $config["max_days_workflow_events"]);
}

$table->width = '99%';
$table->class = 'search-table-button';
$table->colspan = array ();
$table->data = array ();

$table->data[0][0] = print_input_text ("max_days_events", $config["max_days_events"], '', 4, 4, true, __('Days to delete events'));
$table->data[0][0] .= integria_help ("old_events", true);
$table->data[0][1] = print_input_text ("max_days_incidents", $config["max_days_incidents"], '', 4, 4, true, __('Days to delete tickets'));
$table->data[0][1] .= integria_help ("old_incidents", true);

$table->data[1][0] = print_input_text ("max_days_wu", $config["max_days_wu"], '', 4, 4, true, __('Days to delete work units'));
$table->data[1][0] .= integria_help ("old_wu", true);
$table->data[1][1] = print_input_text ("max_days_wo", $config["max_days_wo"], '', 4, 4, true, __('Days to delete work orders'));
$table->data[1][1] .= integria_help ("old_wo", true);

$table->data[2][0] = print_input_text ("max_days_audit", $config["max_days_audit"], '', 4, 4, true, __('Days to delete audit data'));
$table->data[2][0] .= integria_help ("old_audit", true);
$table->data[2][1] = print_input_text ("max_days_session", $config["max_days_session"], '', 4, 4, true, __('Days to delete sessions'));
$table->data[2][1] .= integria_help ("old_sessions", true);

$table->data[3][0] = print_input_text ("max_days_workflow_events", $config["max_days_workflow_events"], '', 4, 4, true, __('Days to delete workflow events'));
$table->data[3][0] .= integria_help ("old_workflow_events", true);

$button = print_input_hidden ('update', 1, true);
$button .= print_submit_button (__('Reset to default'), 'reset_button', false, 'class="sub upd"', true);
$button .= print_submit_button (__('Update'), 'upd_button', false, 'class="sub upd"', true);

$table->data['button'][0] = $button;
$table->colspan['button'][0] = 2;

echo '<form id="setup_maintenance" name="setup" method="post">';
print_table ($table);
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
		$("#text-max_days_workflow_events").val("");
	});
});
</script>
