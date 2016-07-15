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

$manager = enterprise_hook ('crm_check_acl_news', array ($config['id_user']));

if ($manager === ENTERPRISE_NOT_HOOK) {	
	$manager = true;	
} else {
	if (!$manager) {
		include ("general/noaccess.php");
		exit;
	}
}

require_once('include/functions_crm.php');
$merge = get_parameter('merge', 0);

if ($merge) {
	$id_newsletter_source = get_parameter('id_newsletter_source');
	$id_newsletter_destination = get_parameter('id_newsletter_destination');
	
	if ($id_newsletter_source == $id_newsletter_destination) {
		echo ui_print_error_message (__('Source and destination must be different'), '', true, 'h3', true);
	} else {
		echo crm_merge_newsletter_address($id_newsletter_source, $id_newsletter_destination);
	}
}

echo "<h2>".__('Newsletter')."</h2>";
echo "<h4>".__('address merge')."</h4>";

$table = new stdClass();
$table->width = '100%';
$table->class = 'search-table';
$table->colspan = array ();
$table->data = array ();
if(!isset($id_newsletter_source)){
	$id_newsletter_source = '';
}

$table->data[0][0] = print_select_from_sql ('SELECT id, name FROM tnewsletter ORDER BY name',
	'id_newsletter_source', $id_newsletter_source, '', '', '', true, false, false,__('Source'));
if(!isset($id_newsletter_destination)){
	$id_newsletter_destination = '';
}
	
$table->data[1][0] = print_select_from_sql ('SELECT id, name FROM tnewsletter ORDER BY name',
'id_newsletter_destination', $id_newsletter_destination, '', '', '', true, false, false,__('Destination'));


echo '<div class="divform" style="width: '.$table->width.'">';
echo '<form method="post" action="index.php?sec=customers&sec2=operation/newsletter/manage_address">';

$table->data[2][0] = print_submit_button (__('Merge'), 'merge_btn', false, 'class="sub next"' ,true);
$table->data[2][0] .= print_input_hidden ('merge', 1, true);
print_table ($table);
echo '</form>';
echo '</div>';
?>
