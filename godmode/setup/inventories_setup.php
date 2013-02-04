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

check_login ();

if (! dame_admin ($config["id_user"])) {
	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access inventory setup");
	include ("general/noaccess.php");
	return;
}

require_once ('include/functions_inventories.php');

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
echo '<li class="ui-tabs-selected"><a href="index.php?sec=godmode&sec2=godmode/setup/inventories_setup"><span><img src="images/page_white_text.png"  title="'.__('Inventories setup').'"></span></a></li>';
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/setup_mail"><span><img src="images/email.png"  title="'.__('Mail setup').'"></span></a></li>';
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/setup_mailtemplates"><span><img src="images/email_edit.png"  title="'.__('Mail templates setup').'"></span></a></li>';
if ($is_enterprise) {
	echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=enterprise/godmode/usuarios/menu_visibility_manager"><span valign=bottom><img src="images/eye.png" title="'.__('Visibility management').'"></span></a></li>';
}
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/setup_pandora"><span><img src="images/pandora.ico"  title="'.__('Pandora FMS inventory').'"></span></a></li>';
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/setup_auth"><span><img src="images/book_edit.png"  title="'.__('Authentication').'"></span></a></li>';
echo '</ul>';

echo '</div>';

$update = (bool) get_parameter ('update');

if ($update) {
	$config_names = array ('inventory_label_1' => 'generic_1',
		'inventory_label_2' => 'generic_2',
		'inventory_label_3' => 'generic_3',
		'inventory_label_4' => 'generic_4',
		'inventory_label_5' => 'generic_5',
		'inventory_label_6' => 'generic_6',
		'inventory_label_7' => 'generic_7',
		'inventory_label_8' => 'generic_8');
	
	foreach ($config_names as $token => $param) {
		$value = (string) get_parameter ($param);
		if (! isset ($config[$token]))
			process_sql_insert ('tconfig',
				array ('token' => $token, 'value' => $value));
		else
			process_sql_update ('tconfig',
				array ('value' => $value),
				array ('token' => $token));
		$config[$token] = $value;
	}
}

$labels = get_inventory_generic_labels ();

echo '<h3>'.__('Inventories extra fields').'</h3>';

$table->width = '30%';
$table->class = 'databox';
$table->data = array ();

foreach ($labels as $name => $value) {
	$data = array ();
	
	$data[0] = print_input_text ($name, $value, '', 35, 255, true);
	
	array_push ($table->data, $data);
}

echo '<form method="post">';
print_table ($table);

echo '<div style="width: '.$table->width.'" class="button">';
print_input_hidden ('update', 1);
print_submit_button (__('Update'), 'upd_button', false, 'class="sub upd"');
echo '</div>';
echo '</form>';
?>
