<?php
// Babel Enterprise
// ============================================
// Copyright (c) 2008 Artica Soluciones Tecnologicas, http://www.artica.es
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation for version 2.
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

// Load global vars
require_once ("include/config.php");

global $config;

$config["dbtype"] = "mysql";

// Session check
check_login ();

if (! give_acl ($config['id_user'], 0, 'PM')) {
	audit_db ($config['id_user'], $REMOTE_ADDR, "ACL Violation", "Trying to use Open Update Manager extension");
	include ("general/noaccess.php");
	return;
}

require("include/update_manager/lib/libupdate_manager.php");

$db =& um_db_connect ('mysql', $config['dbhost'], $config['dbuser'],
			$config['dbpass'], $config['dbname']);

$update_settings = (bool) get_parameter ('update_settings');

if ($update_settings) {
	foreach ($_POST['keys'] as $key => $value) {
		um_db_update_setting ($key, $value);
	}
	echo "<h3 class=suc>".__('Update manager settings updated')."</h3>";
}

$settings = um_db_load_settings ();
if ($settings->updating_code_path == '') {
	$settings->updating_code_path = $config['homedir'];
	um_db_update_setting ('updating_code_path', $config['homedir']);
}

echo '<h2>'.__('Update Manager Settings').'</h2>';

$table->width = '90%';
$table->data = array ();

$table->data[0][0] = print_input_text ('keys[customer_key]',
	$settings->customer_key, '', 40, 255, true, __('Customer key'));

$table->data[1][0] = print_input_text ('keys[update_server_host]',
	$settings->update_server_host, '', 20, 255, true, __('Update server host'));

$table->data[1][1] = print_input_text ('keys[update_server_port]',
	$settings->update_server_port, '', 5, 5, true, __('Update server port'));

$table->data[2][0] = print_input_text ('keys[update_server_path]',
	$settings->update_server_path, '', 40, 255, true, __('Update server path'));

$table->data[3][0] = print_input_text ('keys[updating_code_path]',
	$settings->updating_code_path, '', 40, 255, true, __('Input path'));

$table->data[4][0] = print_input_text ('keys[keygen_path]',
	$settings->keygen_path, '', 40, 255, true, __('Keygen path'));

$table->data[5][0] = print_input_text ('keys[proxy]', $settings->proxy, '',
	40, 255, true, __('Proxy server'));

$table->data[5][1] = print_input_text ('keys[proxy_port]', $settings->proxy_port,
	'', 40, 255, true, __('Proxy port'));

$table->data[6][0] = print_input_text ('keys[proxy_user]',
	$settings->proxy_user, '', 40, 255, true, __('Proxy user'));
$table->data[6][1] = print_input_text ('keys[proxy_pass]', $settings->proxy_pass,
	'', 40, 255, true, __('Proxy password'));

echo '<form method="post">';
print_table ($table);
echo '<div class="button" style="width: '.$table->width.'">';
print_input_hidden ('update_settings', 1);
print_submit_button (__('Update'), 'update_button', false, 'class="sub upd"');
echo '</div>';
echo '</form>';

?>
