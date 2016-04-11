<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2012 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

check_login ();

enterprise_include('include/functions_crm.php');

$permission = enterprise_hook ('crm_check_acl_news', array ($config['id_user']));

if ($permission === ENTERPRISE_NOT_HOOK) {	
	$permission = true;	
} else {
	if (!$permission) {
		include ("general/noaccess.php");
		exit;
	}
}

$create = get_parameter("create", 0);
$id = get_parameter ("id", 0);

echo "<h2>".__('Newsletter management')."</h2>";
if ($create == 1) {
	
	if (!$permission) {
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create a new newsletter");
		require ("general/noaccess.php");
		exit;
	}
	
	$name = "";
	$id_group = "1";
	$from_desc = "";
	$from_address = "";
	$description = "";
	
	echo "<h4>".__("Newsletter creation")."</h4>";
}
else {
	echo "<h4>".__("Newsletter update")."</h4>";
	$newsletter = get_db_row ("tnewsletter", "id", $id);
	$name = $newsletter["name"];
	$id_group = $newsletter["id_group"];
	$from_desc = $newsletter["from_desc"];
	$from_address = $newsletter["from_address"];
	$description = $newsletter["description"];
}

$table = new StdClass();
$table->width = '100%';
$table->class = 'search-table-button';
$table->colspan = array ();
$table->colspan[3][0] = 2;
$table->data = array ();


$table->data[0][0] = print_input_text ('name', $name, '', 40, 100, true, __('Name'));

$table->data[0][1] = combo_groups_visible_for_me ($config["id_user"], "id_group", 0, "VR", $id_group, true, true);
		
$table->data[1][0] = print_input_text ('from_desc', $from_desc, '', 40, 120, true, __('From description'));
$table->data[1][1] = print_input_text ('from_address', $from_address, '', 35, 120, true, __('From address'));
$table->data[3][0] = print_textarea ("description", 14, 1, $description, '', true, __('Description'));

echo '<form method="post" action="index.php?sec=customers&sec2=operation/newsletter/newsletter_definition">';
print_table ($table);


if ($permission) {
	echo '<div class="button-form" style="width: '.$table->width.'">';

	if ($id) {
			print_submit_button (__('Update'), 'update_btn', false, 'class="sub upd"');
			print_input_hidden ('id', $id);
			print_input_hidden ('update', 1);
	}
	else {
		print_submit_button (__('Create'), 'create_btn', false, 'class="sub next"');
		print_input_hidden ('create', 1);
	}
	echo "</div>";
}
echo "</form>";
