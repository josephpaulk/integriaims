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

$is_enterprise = false;
if (file_exists ("enterprise/load_enterprise.php")) {
	$is_enterprise = true;
}

/* Tabs code */
echo '<div id="tabs">';

/* Tabs list */
echo '<ul class="ui-tabs-nav">';
echo '<li class="ui-tabs-selected"><a href="index.php?sec=godmode&sec2=godmode/setup/setup"><span><img src="images/cog.png" title="'.__('Setup').'"></span></a></li>';
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
echo '</ul>';

echo '</div>';

$update = (bool) get_parameter ("update");

if ($update) {
	$config["block_size"] = (int) get_parameter ("block_size", 20);
	$config["language_code"] = (string) get_parameter ("language_code", "en");
	$config["no_wu_completion"] = (string) get_parameter ("no_wu_completion", "");
	$config["currency"] = (string) get_parameter ("currency", "€");
	$config["hours_perday"] = (int) get_parameter ("hours_perday", "8");
	$config["sitename"] = (string) get_parameter ("sitename", "Integria IMS");
	$config["limit_size"] = (int) get_parameter ("limit_size");
	$config["autowu_completion"] = (int) get_parameter ("autowu_completion", 0);
	$config["fontsize"] = (int) get_parameter ("fontsize", 10);
	$config["incident_reporter"] = (int) get_parameter ("incident_reporter", 0);
	$config["show_owner_incident"] = (int) get_parameter ("show_owner_incident", 0);
	$config["show_creator_incident"] = (int) get_parameter ("show_creator_incident", 0);
	$config["pwu_defaultime"] = get_parameter ("pwu_defaultime", 4);
	$config["iwu_defaultime"] = get_parameter ("iwu_defaultime", 0.25);
	$config["timezone"] = get_parameter ("timezone", "Europe/Madrid");
	$config["api_acl"] = get_parameter ("api_acl", "*");
	$config["api_password"] = get_parameter ("api_password", "");
	$config["auto_incident_close"] = get_parameter ("auto_incident_close", "72");
	$config["email_on_incident_update"] = get_parameter ("email_on_incident_update", 0);
	$config["site_logo"] = get_parameter ("site_logo", "integria_logo.png");
    $config["header_logo"] = get_parameter ("header_logo", "integria_logo_header.png");
	$config["error_log"] = get_parameter ("error_log", 0);
	$config["flash_charts"] = get_parameter ("flash_charts", 1);
    $config["max_file_size"] = get_parameter ("max_file_size", 1);
    $config["iw_creator_enabled"] = get_parameter ("iw_creator_enabled", 0);
    $config["enable_newsletter"] = get_parameter ("enable_newsletter", 0);
    $config["batch_newsletter"] = get_parameter ("batch_newsletter", 0);
	$config["lead_company_filter"] = get_parameter ("lead_company_filter", "");    
	$config["lead_warning_time"] = get_parameter ("lead_warning_time", "7");    

    if ($is_enterprise) {
		$config["enable_pass_policy"] = get_parameter ("enable_pass_policy", 0);
		$config["pass_size"] = get_parameter ("pass_size", 4);
		$config["pass_needs_numbers"] = get_parameter ("pass_needs_numbers", 0);
		$config["pass_needs_symbols"] = get_parameter ("pass_needs_symbols", 0);
		$config["pass_expire"] = get_parameter ("pass_expire", 0);
		$config["first_login"] = get_parameter ("first_login", 1);
		$config["mins_fail_pass"] = get_parameter ("mins_fail_pass", 5);
		$config["number_attempts"] = get_parameter ("number_attempts", 5);
	}
    $config["want_chat"] = get_parameter ("want_chat", 0); 
    $config["incident_creation_wu"] = get_parameter ("incident_creation_wu", 0);
 
    update_config_token ("timezone", $config["timezone"]);	
    update_config_token ("want_chat", $config["want_chat"]);
    update_config_token ("incident_creation_wu", $config["incident_creation_wu"]);
    update_config_token ("lead_company_filter", $config["lead_company_filter"]);
    update_config_token ("lead_warning_time", $config["lead_warning_time"]);

    //TODO: Change all "process_sqlxxx" for update_config_token in following code:

	process_sql ("UPDATE tconfig SET value='".$config["language_code"]."' WHERE token='language_code'");
	
	process_sql ("UPDATE tconfig SET value='".$config["hours_perday"]."' WHERE token='hours_perday'");
	process_sql ("UPDATE tconfig SET value='".$config["currency"]."' WHERE token='currency'");
	
    update_config_token ("sitename", $config["sitename"]);
    update_config_token ("limit_size", $config["limit_size"]);
    update_config_token ("max_file_size", $config["max_file_size"]);


	process_sql ("DELETE FROM tconfig WHERE token = 'autowu_completion'");
	process_sql ("INSERT INTO tconfig (token, value) VALUES ('autowu_completion', '".$config["autowu_completion"]."')");

	process_sql ("DELETE FROM tconfig WHERE token = 'no_wu_completion'");
	process_sql ("INSERT INTO tconfig (token, value) VALUES ('no_wu_completion', '".$config["no_wu_completion"]."')");

	process_sql ("DELETE FROM tconfig WHERE token = 'incident_reporter'");
	process_sql ("INSERT INTO tconfig (token, value) VALUES ('incident_reporter', '".$config["incident_reporter"]."')");
	
	process_sql ("DELETE FROM tconfig WHERE token = 'show_creator_incident'");
	process_sql ("INSERT INTO tconfig (token, value) VALUES ('show_creator_incident', '".$config["show_creator_incident"]."')");

	process_sql ("DELETE FROM tconfig WHERE token = 'show_owner_incident'");
	process_sql ("INSERT INTO tconfig (token, value) VALUES ('show_owner_incident', '".$config["show_owner_incident"]."')");

	update_config_token ("pwu_defaultime", $config["pwu_defaultime"]);
	update_config_token ("iwu_defaultime", $config["iwu_defaultime"]);
	update_config_token ("api_acl", $config["api_acl"]);
	update_config_token ("api_password", $config["api_password"]);
    update_config_token ("auto_incident_close", $config["auto_incident_close"]);
    update_config_token ("email_on_incident_update", $config["email_on_incident_update"]);
    update_config_token ("error_log", $config["error_log"]);
	update_config_token ("iw_creator_enabled", $config["iw_creator_enabled"]);
    update_config_token ("enable_newsletter", $config["enable_newsletter"]);
    update_config_token ("batch_newsletter", $config["batch_newsletter"]);
    
    if ($is_enterprise) {
		update_config_token ("enable_pass_policy", $config["enable_pass_policy"]);
		update_config_token ("pass_size", $config["pass_size"]);
		update_config_token ("pass_needs_numbers", $config["pass_needs_numbers"]);
		update_config_token ("pass_needs_symbols", $config["pass_needs_symbols"]);
		update_config_token ("pass_expire", $config["pass_expire"]);
		update_config_token ("first_login", $config["first_login"]);
		update_config_token ("mins_fail_pass", $config["mins_fail_pass"]);
		update_config_token ("number_attempts", $config["number_attempts"]);
	}
    
}
// Render SYSTEM language code, not current language.
$config['language_code'] = get_db_value ('value', 'tconfig', 'token', 'language_code');

$crontask = get_db_sql ("SELECT `value` FROM tconfig WHERE `token` = 'crontask'");

echo "<h2>".__('General setup')."</h2>";

if ($crontask == "")
	echo "<h2 class=error>".__("Crontask not installed. Please check documentation!")."</h2>";
else
	echo "<h4>".__("Last execution for crontask at"). " ".$crontask."</h4>";



$table->width = '90%';
$table->class = 'databox';
$table->colspan = array ();
$table->data = array ();

$incident_reporter_options[0] = __('Disabled');
$incident_reporter_options[1] = __('Enabled');

$table->data[0][0] = print_select_from_sql ('SELECT id_language, name FROM tlanguage ORDER BY name',
	'language_code', $config['language_code'], '', '', '', true, false, false,
	__('Language'));

$table->data[0][1] = print_input_text ("no_wu_completion", $config["no_wu_completion"], '',
	20, 500, true, __('No WU completion users'));
$table->data[0][1] .= integria_help ("no_wu_completion", true);

$table->data[1][0] = print_select ($incident_reporter_options, "email_on_incident_update", $config["email_on_incident_update"], '','','',true, 0, true, __('Send email on every incident update'));


$table->data[1][0] .= print_help_tip (__("Enabling this, you will get emails on file attachs also. If left disabled, you only get notifications only in major events on incidents"), true);

$table->data[1][1] = print_input_text ("limit_size", $config["limit_size"], '',
	5, 5, true, __('Max. Incidents by search'));
$table->data[1][1] .= integria_help ("limit_size", true);

$table->data[2][0] = print_input_text ("autowu_completion", $config["autowu_completion"],
	'', 7, 7, true, __('Auto WU Completion (days)'));
$table->data[2][0] .= integria_help ("autowu_completion", true);

$table->data[2][1] = print_input_text ("hours_perday", $config["hours_perday"], '',
	5, 5, true, __('Work hours per day'));
$table->data[2][1] .= integria_help ("hours_perday", true);

$table->data[3][0] = print_input_text ("sitename", $config["sitename"], '',
	30, 50, true, __('Sitename'));
$table->data[3][1] = print_input_text ("currency", $config["currency"], '',
	3, 3, true, __('Currency'));

$table->data[4][0] = print_input_text ("iwu_defaultime", $config["iwu_defaultime"], '',
	5, 5, true, __('Incident WU Default time'));
$table->data[4][1] = print_input_text ("pwu_defaultime", $config["pwu_defaultime"], '',
	5, 5, true, __('Project WU Default time'));

$error_log_options[0] = __('Disabled');
$error_log_options[1] = __('Enabled');
$table->data[5][0] = print_select ($error_log_options, "error_log", $config["error_log"], '','','',true,0,true, __('Error log'));

$table->data[5][0] .= print_help_tip (__("This errorlog is on /integria.log"), true);


$table->data[5][1] = print_select ($incident_reporter_options, "incident_reporter", $config["incident_reporter"], '','','',true,0,true, __('Incident reporter'));

$table->data[5][1] .= print_help_tip (__("Enabling this, you will be able to add aditional contacts to each incident. These contacts will receive email notifications if email notification is enabled, but cannot do anything"), true);

$table->data[6][0] = print_select ($incident_reporter_options, "show_owner_incident", $config["show_owner_incident"], '','','',true,0,true, __('Show incident owner'));

$table->data[6][1] = print_select ($incident_reporter_options, "show_creator_incident", $config["show_creator_incident"], '','','',true,0,true, __('Show incident creator'));

$table->data[10][0] = print_input_text ("timezone", $config["timezone"], '',
	15, 30, true, __('Timezone for integria'));

$table->data[10][1] = print_input_text ("auto_incident_close", $config["auto_incident_close"], '',
	10, 10, true, __('Auto incident close'));
$table->data[10][1] .= integria_help ("auto_incident_close", true);

$table->data[11][0] = print_input_text ("api_acl", $config["api_acl"], '',
	30, 255, true, __('List of IP with access to API'));
	
$table->data[11][0] .= print_help_tip (__("List of IP (separated with commas which can access to the integria API. Use * for any address (INSECURE!)"), true);

$table->data[11][1] = print_input_text ("api_password", $config["api_password"], '',
	30, 255, true, __('API password'));


$table->data[12][0] = print_input_text ("max_file_size", $config["max_file_size"], '',
	10, 255, true, __('Max. Upload file size'));
	
$table->data[12][1] =  print_checkbox ("iw_creator_enabled", 1, $config["iw_creator_enabled"], 
					true, __('Enable IW to change creator'));
					
$table->data[12][1] .= print_help_tip (__("Enabling this, any user with IW will be able to change the creator of an incident. This is disabled by default to be ITIL compliant"), true);			
		
$newsletter_options[0] = __('Disabled');
$newsletter_options[1] = __('Enabled');
$table->data[13][0] = print_select ($newsletter_options, "enable_newsletter", $config["enable_newsletter"], '','','',true,0,true, __('Enable newsletter'));


$table->data[13][1] = print_input_text ("batch_newsletter", $config["batch_newsletter"], '',
	4, 255, true, __('Max. emails sent per execution'));
	
$table->data[13][0] .= print_help_tip (__("Enable this option to activate the newsletter feature of Integria IMS"), true);

$table->data[13][1] .= print_help_tip (__("This means, in each execution of the batch external process (integria_cron). If you set your cron to execute each hour in each execution of that process will try to send this ammount of emails. If you set the cron to run each 5 min, will try this number of mails."), true);


$newsletter_options[0] = __('Disabled');
$newsletter_options[1] = __('Enabled');

$table->data[14][1] = print_select ($newsletter_options, "want_chat", $config["want_chat"], '','','',true, 0, true, __('Enable incident chat window'));


$table->data[14][0] = print_select ($newsletter_options, "incident_creation_wu", $config["incident_creation_wu"], '','','',true, 0, true, __('Editor adds a WU on incident creation'));


$table->data[15][0] = print_input_text ("lead_company_filter", $config["lead_company_filter"], '',
	20, 255, true, __('Lead company filter IDs'));

$table->data[15][0] .= print_help_tip (__("Use this to filter what company roles you want to show you as valid companies to attach a Lead, for example: 1,34,4 or just one, line: 12"), true);

$table->data[15][1] = print_input_text ("lead_warning_time", $config["lead_warning_time"], '',
	5, 255, true, __('Days to warn on inactive leads'));

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
