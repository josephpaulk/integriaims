<?php
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2010 Ártica Soluciones Tecnológicas
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

include_once("include/functions_profile.php");

check_login ();
	
if (! dame_admin ($config["id_user"])) {
	audit_db ("ACL Violation", $config["REMOTE_ADDR"], "No administrator access", "Trying to access setup");
	require ("general/noaccess.php");
	exit;
}

$update = (bool) get_parameter ("update");

if ($update) {

	$config['auth_methods'] = get_parameter ("auth_methods", "ldap");
	$config['autocreate_remote_users'] = (int) get_parameter ("autocreate_remote_users", 0);
	$config['default_remote_profile'] = get_parameter ("default_remote_profile", 0);
	$config['default_remote_group'] = get_parameter ("default_remote_group", 0);
	$config['autocreate_blacklist'] = (string) get_parameter ("autocreate_blacklist", "");
	$config['ldap_server'] = (string) get_parameter ("ldap_server", "localhost");
	$config['ldap_port'] = (int) get_parameter ("ldap_port", 389);
	$config['ldap_version'] = get_parameter ("ldap_version", 3); //int??
	$config['ldap_start_tls'] = (int) get_parameter ("start_tls", 0);
	$config['ldap_base_dn'] = get_parameter ("ldap_base_dn", "ou=People,dc=example,dc=com");
	$config['ldap_login_attr'] = (string) get_parameter ("ldap_login_attr", "uid");
	
	update_config_token ("auth_methods", $config["auth_methods"]);
	update_config_token ("autocreate_remote_users", $config["autocreate_remote_users"]);
	update_config_token ("default_remote_profile", $config["default_remote_profile"]);
	update_config_token ("default_remote_group", $config["default_remote_group"]);
	update_config_token ("autocreate_blacklist", $config["autocreate_blacklist"]);
	update_config_token ("ldap_server", $config["ldap_server"]);
	update_config_token ("ldap_port", $config["ldap_port"]);
	update_config_token ("ldap_version", $config["ldap_version"]);
	update_config_token ("ldap_start_tls", $config["ldap_start_tls"]);
	update_config_token ("ldap_base_dn", $config["ldap_base_dn"]);
	update_config_token ("ldap_login_attr", $config["ldap_login_attr"]);
}

echo "<h2>".__('Authentication configuration')."</h2>";

$disabled = false;

$table->width = '90%';
$table->class = 'databox';
$table->colspan = array ();
$table->data = array ();

$auth_methods = array ('mysql' => __('Local Integria'), 'ldap' => __('LDAP'));
$table->data[0][0] = print_select ($auth_methods, "auth_methods", 'ldap', '','','',true, 0, true, "Authentication method");

$table->data[1][0] = __('Autocreate remote users');
$table->data[2][0] =  __('Yes').'&nbsp;'.print_radio_button ('autocreate_remote_users', 1, '', $config['autocreate_remote_users'], true, '', '', '').'&nbsp;&nbsp;';
$table->data[2][0] .= __('No').'&nbsp;'.print_radio_button ('autocreate_remote_users', 0, '', $config['autocreate_remote_users'], true, '', '', '');

if ($config['autocreate_remote_users'] == 0){
	$disabled = true;
}
$profile_list = profile_get_profiles ();
if ($profile_list === false) {
	$profile_list = array ();
}	
$table->data[3][0] = print_select ($profile_list, "default_remote_profile", $config['default_remote_profile'], '','','',true, 0, true, "Autocreate profile", $disabled);

$group_list = group_get_groups ();
if ($group_list === false) {
	$group_list = array ();
}	
$table->data[4][0] = print_select ($group_list, "default_remote_group", $config['default_remote_group'], '','','',true, 0, true, "Autocreate group", $disabled);

$table->data[5][0] = print_input_text ("autocreate_blacklist", $config['autocreate_blacklist'], '',
	60, 500, true, __('Autocreate blacklist'), $disabled);
$table->data[5][0] .= integria_help ("autocreate_blacklist", true);

$table->data[6][0] = print_input_text ("LDAP_server", $config['ldap_server'], '',
	10, 50, true, __('LDAP server'));
	
$table->data[7][0] = print_input_text ("LDAP_port", $config['ldap_port'], '',
	10, 50, true, __('LDAP port'));
	
$ldap_version = array (1 => 'LDAPv1', 2 => 'LDAPv2', 3 => 'LDAPv3');
$table->data[8][0] = print_select ($ldap_version, "ldap_version", $config['ldap_version'], '','','',true, 0, true, "LDAP version");

$table->data[9][0] = __('Start TLS');
$table->data[10][0] =  __('Yes').'&nbsp;'.print_radio_button ('ldap_start_tls', 1, '', $config['ldap_start_tls'], true, '', '', '').'&nbsp;&nbsp;';
$table->data[10][0] .= __('No').'&nbsp;'.print_radio_button ('ldap_start_tls', 0, '', $config['ldap_start_tls'], true, '', '', '');


$table->data[11][0] = print_input_text ("ldap_base_dn", $config['ldap_base_dn'], '',
	60, 50, true, __('Base DN'));
	
$table->data[12][0] = print_input_text ("ldap_login_attr", $config['ldap_login_attr'], '',
	60, 50, true, __('Login attribute'));




// Hide LDAP configuration options
/*for ($i = 6; $i <= 11; $i++) {
	$table->rowstyle[$i] = $config['auth_methods'] == 'ldap' ? '' : 'display: none;';
	$table->rowclass[$i] = 'ldap';
}*/

echo "<form name='setup_auth' method='post'>";

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

$('#radiobtn0001').change (function (){
	$('#default_remote_profile').attr('disabled', '');
	$('#default_remote_group').attr('disabled', '');
	$('#text-autocreate_blacklist').attr('disabled', '');
});
	
$('#radiobtn0002').change (function (){
	$('#default_remote_profile').attr('disabled', 'disabled');
	$('#default_remote_group').attr('disabled', 'disabled');
	$('#text-autocreate_blacklist').attr('disabled', 'disabled');
});

$('#auth_methods').change (function (){
	var auth_method = $("#auth_methods").val ();
	if (auth_method == 'mysql'){
		
		$('#table1-9-0').css('display', 'none');
		$('#radiobtn0001').parent().css('display', 'none');
		$('#radiobtn0001').css('display', 'none');
		$('#radiobtn0002').css('display', 'none');
		$('#default_remote_profile').parent().css('display', 'none');
		$('#default_remote_profile').css('display', 'none');
		$('#default_remote_group').parent().css('display', 'none');
		$('#default_remote_group').css('display', 'none');
		$('#text-autocreate_blacklist').parent().css('display', 'none');
		$('#text-autocreate_blacklist').css('display', 'none');
		$('#text-LDAP_server').parent().css('display', 'none');
		$('#text-LDAP_server').css('display', 'none');
		$('#text-LDAP_port').parent().css('display', 'none');
		$('#text-LDAP_port').css('display', 'none');
		$('#ldap_version').parent().css('display', 'none');
		$('#ldap_version').css('display', 'none');
		$('#ldap_start_tls').parent().css('display', 'none');
		$('#ldap_start_tls').css('display', 'none');
		$('#text-ldap_base_dn').parent().css('display', 'none');
		$('#text-ldap_base_dn').css('display', 'none');
		$('#text-ldap_login_attr').parent().css('display', 'none');
		$('#text-ldap_login_attr').css('display', 'none');
		$('#table1-1-0').css('display', 'none');
		$('#radiobtn0003').parent().css('display', 'none');
		$('#radiobtn0003').css('display', 'none');
		$('#radiobtn0004').css('display', 'none');
		
	} else {
		
		$('#table1-9-0').css('display', '');
		$('#radiobtn0001').parent().css('display', '');
		$('#radiobtn0001').css('display', '');
		$('#radiobtn0002').css('display', '');
		$('#default_remote_profile').parent().css('display', '');
		$('#default_remote_profile').css('display', '');
		$('#default_remote_group').parent().css('display', '');
		$('#default_remote_group').css('display', '');
		$('#text-autocreate_blacklist').parent().css('display', '');
		$('#text-autocreate_blacklist').css('display', '');
		$('#text-LDAP_server').parent().css('display', '');
		$('#text-LDAP_server').css('display', '');
		$('#text-LDAP_port').parent().css('display', '');
		$('#text-LDAP_port').css('display', '');
		$('#ldap_version').parent().css('display', '');
		$('#ldap_version').css('display', '');
		$('#ldap_start_tls').parent().css('display', '');
		$('#ldap_start_tls').css('display', '');
		$('#text-ldap_base_dn').parent().css('display', '');
		$('#text-ldap_base_dn').css('display', '');
		$('#text-ldap_login_attr').parent().css('display', '');
		$('#text-ldap_login_attr').css('display', '');
		$('#table1-1-0').css('display', '');
		$('#radiobtn0003').parent().css('display', '');
		$('#radiobtn0003').css('display', '');
		$('#radiobtn0004').css('display', '');
	}
});


</script>
