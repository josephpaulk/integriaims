<?php
// Integria 2.0 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008 Artica Soluciones Tecnologicas
// Copyright (c) 2008 Esteban Sanchez, estebans@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

check_login ();

$id = (int) get_parameter ('id');

if (! give_acl($config["id_user"], 0, "IW")) {
	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to incident #".$id);
	include ("general/noaccess.php");
	return;
}

$title = get_db_value ("titulo", "tincidencia", "id_incidencia", $id);

echo '<div id="upload_result"></div>';

echo "<div id='upload_control'>";

$table->width = '100%';
$table->data = array ();
$table->data[0][0] = print_input_file ('userfile', 40, false, '', true, __('File'));
$table->data[1][0] = print_textarea ('file_description', 8, 1, '', '', true, __('Description'));

if (defined ('AJAX'))
	$action = 'ajax.php?page=operation/incidents/incident_detail';
else
	$action = 'index.php?sec=incidents&sec2=operation/incidents/incident_detail';
echo '<form method="post" action="'.$action.'" id="form-add-file" enctype="multipart/form-data">';
print_table ($table);

echo '<div class="button" style="width: '.$table->width.'">';
print_submit_button (__('Upload'), 'upload', false, 'class="sub next"');
echo '</div>';
print_input_hidden ('id', $id);
print_input_hidden ('upload_file', 1);
echo "</form>";
echo '</div>';
?>
