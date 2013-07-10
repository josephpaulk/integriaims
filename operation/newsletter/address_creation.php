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

if (! give_acl ($config["id_user"], 0, "VM")) {
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access newsletter management");
	require ("general/noaccess.php");
	exit;
}

$id_newsletter = 0;
$data = "";

echo "<h2>".__("Addresses bulk creation")."</h2>";
	
$table->width = '90%';
$table->class = 'databox';
$table->colspan = array ();
$table->data = array ();

$table->data[1][0] = print_select_from_sql ('SELECT id, name FROM tnewsletter ORDER BY name',
	'id_newsletter', $id_newsletter, '', '', '', true, false, false,__('Newsletter'));

$table->data[2][0] = print_textarea ("data", 15, 1, $data, 'data', true, "<br>".__('Enter data: email, name, one per file'));

echo '<form method="post" action="index.php?sec=customers&sec2=operation/newsletter/address_definition">';
print_table ($table);

echo '<div class="button" style="width: '.$table->width.'">';
print_submit_button (__('Create'), 'create_btn', false, 'class="sub next"');
print_input_hidden ('create', 1);
echo "</div>";
echo "</form>";

?>
