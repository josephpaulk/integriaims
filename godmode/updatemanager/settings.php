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
require_once ('godmode/updatemanager/load_updatemanager.php');

check_login ();

if (! give_acl ($config['id_user'], 0, 'PM')) {
	audit_db ($config['id_user'], $REMOTE_ADDR, "ACL Violation", "Trying to use Open Update Manager extension");
	include ("general/noaccess.php");
	return;
}

$db =& um_db_connect ('mysql', $config['dbhost'], $config['dbuser'],
			$config['dbpass'], $config['dbname']);

$update_settings = (bool) get_parameter ('update_settings');

if ($update_settings) {
	foreach ($_POST['keys'] as $key => $value) {
		um_db_update_setting ($key, $value);
	}
	echo "<h3 class=suc>".lang_string('Update manager settings updated')."</h3>";
}

$settings = um_db_load_settings ();

echo '<h3>'.lang_string('Settings').'</h3>';
echo '<form method="post">';

$table->width = '95%';
$table->data = array ();

$table->data[0][0] = '<strong>'.lang_string('Customer key').'</strong>';
$table->data[0][1] = print_input_text ('keys[customer_key]', $settings->customer_key, '', 40, 255, true);

$table->data[1][0] = '<strong>'.lang_string('Update server host').'</strong>';
$table->data[1][1] = print_input_text ('keys[update_server_host]', $settings->update_server_host, '', 20, 255, true);

$table->data[2][0] = '<strong>'.lang_string('Update server path').'</strong>';
$table->data[2][1] = print_input_text ('keys[update_server_path]', $settings->update_server_path, '', 40, 255, true);

$table->data[3][0] = '<strong>'.lang_string('Update server port').'</strong>';
$table->data[3][1] = print_input_text ('keys[update_server_port]', $settings->update_server_port, '', 5, 5, true);

$table->data[4][0] = '<strong>'.lang_string('Binary input path').'</strong>';
$table->data[4][1] = print_input_text ('keys[updating_binary_path]', $settings->updating_binary_path, '', 40, 255, true);

$table->data[5][0] = '<strong>'.lang_string('Keygen path').'</strong>';
$table->data[5][1] = print_input_text ('keys[keygen_path]', $settings->keygen_path, '', 40, 255, true);

$table->data[6][0] = '<strong>'.lang_string('Proxy server').'</strong>';
$table->data[6][1] = print_input_text ('keys[proxy]', $settings->proxy, '', 40, 255, true);

$table->data[7][0] = '<strong>'.lang_string('Proxy port').'</strong>';
$table->data[7][1] = print_input_text ('keys[proxy_port]', $settings->proxy_port, '', 40, 255, true);

$table->data[8][0] = '<strong>'.lang_string('Proxy user').'</strong>';
$table->data[8][1] = print_input_text ('keys[proxy_user]', $settings->proxy_user, '', 40, 255, true);

$table->data[9][0] = '<strong>'.lang_string('Proxy password').'</strong>';
$table->data[9][1] = print_input_text ('keys[proxy_pass]', $settings->proxy_pass, '', 40, 255, true);

print_table ($table);
echo '<div class="action-buttons" style="width: '.$table->width.'">';
print_input_hidden ('update_settings', 1);
print_submit_button (lang_string('Update'), 'update_button', false, 'class="sub upd"');
echo '</div>';
echo '</form>';

?>
