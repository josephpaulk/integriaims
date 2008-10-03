<?php

// Pandora FMS - the Free monitoring system
// ========================================
// Copyright (c) 2004-2007 Sancho Lerena, slerena@openideas.info
// Copyright (c) 2005-2007 Artica Soluciones Tecnologicas
// Copyright (c) 2004-2007 Raul Mateos Martin, raulofpandora@gmail.com
// Copyright (c) 2006-2007 Jose Navarro jose@jnavarro.net
// Copyright (c) 2006-2007 Jonathan Barajas, jonathan.barajas[AT]gmail[DOT]com

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
// Load global vars

global $config;

if (check_login() != 0) {
 	audit_db ("Noauth", $REMOTE_ADDR, "No authenticated access","Trying to access event viewer");
	require ("general/noaccess.php");
	exit;
}

$id_incident = (int) get_parameter ('id');
$delete_file = (bool) get_parameter ('delete_file');

if (!$id_incident) {
	audit_db ($config['id_user'], $REMOTE_ADDR, "ACL Violation",
		"Trying to access files of incident #".$id_incident);
	include ("general/noaccess.php");
	exit;
}

$id_group = (int) get_db_value ('id_grupo', 'tincidencia', 'id_incidencia', $id_incident);

if (! give_acl ($config["id_user"], $id_group, "IR")) {
 	// Doesn't have access to this page
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation",
		'Trying to access files of incident #'.$id_incident." '".$titulo."'");
	include ("general/noaccess.php");
	exit;
}

echo '<h3>'.__('Incident').' #'.$id_incident.' - '.give_inc_title ($id_incident).'</h3>';

// Files attached to this incident
$files = get_incident_files ($id_incident);
if ($files === false) {
	echo '<h4>'.__('No files were added to the incidence').'</h4>';
	return;
}

$table->class = 'listing';
$table->width = '90%';
$table->data = array ();
$table->align = array ();
$table->align[3] = 'center';
$table->size = array ();
$table->size[3] = '40px';
$table->head = array ();
$table->head[0] = __('Filename');
$table->head[1] = __('Description');
$table->head[2] = __('Size');
if (give_acl ($config['id_user'], $id_group, "IM")) {
	$table->head[3] = __('Delete');
}

foreach ($files as $file) {
	$data = array ();
	
	$data[0] = '<img src="images/disk.png" /><a target="_blank"
		href="attachment/pand'.$file['id_attachment'].'_'.$file['filename'].'">'.
		$file['filename'].'</a>';
	$data[1] = $file["description"];
	$data[2] = byte_convert ($file['size']);

	// Delete attachment
	if (give_acl ($config['id_user'], $id_group, 'IM')) {
		$data[3] = '<a class="delete" id="delete-file-'.$file["id_attachment"].'"
			href="index.php?sec=incidencias&sec2=operation/incidents/incident&id='.
			$id_incident.'&delete_file=1&id_file='.$file["id_attachment"].'">
			<img src="images/cross.png"></a>';
	}
	
	array_push ($table->data, $data);
}

print_table ($table);

?>
