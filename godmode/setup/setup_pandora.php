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

enterprise_include("include/functions_setup.php");
	
if (! dame_admin ($config["id_user"])) {
	audit_db ("ACL Violation", $config["REMOTE_ADDR"], "No administrator access", "Trying to access visual setup");
	require ("general/noaccess.php");
	exit;
}

$is_enterprise = false;
if (file_exists ("enterprise/load_enterprise.php")) {
	$is_enterprise = true;
}
	
/* Tabs list */
print_setup_tabs('inventory', $is_enterprise);

$labels = get_inventory_generic_labels ();

$update = (bool) get_parameter ("update");

if ($update) {
	$config["pandora_url"] = get_parameter ("pandora_url");
	$config["pandora_api_password"] = get_parameter ("pandora_api_password");
	$config["pandora_user"] = get_parameter ("pandora_user");
	$config["pandora_pass"] = get_parameter ("pandora_pass");
	$config["default_contract"] = get_parameter ("default_contract");
	$config["duplicate_inventory_name"] = get_parameter ("duplicate_inventory_name");

    $config["remote_inventory_type"] = (int) get_parameter("remote_inventory_type", 0);
	$config["inventory_default_owner"] = (string) get_parameter("inventory_default_owner", "");
	
	$companies = get_parameter("companies", explode(',',$config["inventory_default_companies"]));

	$config["inventory_default_companies"] = join(',',$companies);
		
	$users = get_parameter("users", explode(',',$config["inventory_default_users"])); 

	$config["inventory_default_users"] = join(',', $users);

    update_config_token ("remote_inventory_type", $config["remote_inventory_type"]);
	update_config_token ("inventory_default_owner", $config["inventory_default_owner"]);

	update_config_token ("inventory_default_companies", $config["inventory_default_companies"]);
	update_config_token ("inventory_default_users", $config["inventory_default_users"]);
	update_config_token ("duplicate_inventory_name", $config["duplicate_inventory_name"]);

	foreach($labels as $k => $lab) {
		$config["pandora_$k"] = get_parameter ("pandora_$k");
		update_config_token ("pandora_$k", $config["pandora_$k"]);
	}
	
    update_config_token ("pandora_url", $config["pandora_url"]);
    update_config_token ("pandora_api_password", $config["pandora_api_password"]);
	update_config_token ("pandora_user", $config["pandora_user"]);
    update_config_token ("pandora_pass", $config["pandora_pass"]); 
    update_config_token ("default_contract", $config["default_contract"]);

	echo ui_print_success_message (__('Successfully updated'), '', true, 'h3', true);
}

$table = new StdClass();
$table->width = '100%';
$table->class = 'search-table-button';
$table->colspan = array ();
$table->data = array ();

$table->data[0][0] = print_input_text ("pandora_url", $config["pandora_url"], '',
	30, 100, true, __('Pandora FMS URL'));

$table->data[0][1] = print_input_text ("pandora_api_password", $config["pandora_api_password"], '', 
	30, 100,  true, __('Pandora FMS API password')) ;
	
$table->data[1][0] = print_input_text ("pandora_user", $config["pandora_user"], '',
	30, 100, true, __('Pandora FMS User'));

$table->data[1][1] = print_input_text ("pandora_pass", $config["pandora_pass"], '', 
	30, 100,  true, __('Pandora FMS User password')) ;	

$contracts = get_contracts();
$table->data[2][0] = print_select ($contracts, 'default_contract', $config["default_contract"], '', __('Select'), '',  true, 0, true, __('Default Contract')) ;

$table->data[2][1] = print_checkbox ("duplicate_inventory_name", 1, $config["duplicate_inventory_name"], true, __('Allow inventory objects with the same name'));

$button = print_input_hidden ('update', 1, true);
$button .= print_submit_button (__('Update'), 'upd_button', false, 'class="sub upd"', true);

$table_remote_inventory = enterprise_hook('setup_print_remote_inventory_type');

if ($table_remote_inventory !== ENTERPRISE_NOT_HOOK) {
	$table->data[3][0] = $table_remote_inventory;
	$table->colspan[3][0] = 2;
}

echo "<form name='setup' method='post' id='inventory_status_form'>";
print_table ($table);

	echo "<div class='button-form'>";
		echo $button;
	echo "</div>";
echo '</form>';
?>

<script type="text/javascript" src="include/js/integria_inventory.js"></script>
<script type="text/javascript" src="include/js/jquery.validation.functions.js"></script>
<script type="text/javascript">
$(document).ready (function () {
	$("textarea").TextAreaResizer ();

	var idUser = "<?php echo $config['id_user'] ?>";
	bindAutocomplete ("#text-inventory_default_owner", idUser);
});
// id_user
validate_user ("#inventory_status_form", "#text-inventory_default_owner", "<?php echo __('Invalid user')?>");
</script>
