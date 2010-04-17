<?php

// Integria 2.0 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

global $config;

check_login ();

$id_incident = (int) get_parameter ('id');
$delete_file = (bool) get_parameter ('delete_file');

if (!$id_incident) {
	audit_db ($config['id_user'], $REMOTE_ADDR, "ACL Violation",
		"Trying to access files of incident #".$id_incident);
	include ("general/noaccess.php");
	exit;
}

$incident = get_db_row ('tincidencia', 'id_incidencia', $id_incident);

if (! give_acl ($config["id_user"], $incident['id_grupo'], "IR")) {
 	// Doesn't have access to this page
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation",
		'Trying to access files of incident #'.$id_incident." '".$titulo."'");
	include ("general/noaccess.php");
	exit;
}

echo '<h3>'.__('Incident').' #'.$id_incident.' - '.$incident['titulo'].'</h3>';

echo '<div class="result"></div>';

// Files attached to this incident
$files = get_incident_files ($id_incident);
if ($files === false) {
	echo '<h4>'.__('No files were added to the incidence').'</h4>';
	return;
}

echo "<table class=listing cellpadding=4 cellspacing=4 width=90%>";
echo "<tr>";
echo "<th>".__('Filename');
echo "<th>".__('Timestamp');
echo "<th>".__('Description');
echo "<th>".__('Size');

if (give_acl ($config['id_user'], $incident['id_grupo'], "IM")) {
	echo "<th>".__('Delete');
}

foreach ($files as $file) {

     $link = $config["base_url"]."/operation/incidents/incident_download_file.php?id_attachment=".$file["id_attachment"];

     $real_filename = $config["homedir"]."/attachment/".$file["id_attachment"]."_".rawurlencode ($file["filename"]);    

    echo "<tr>";
    echo "<td valign=top>";
	echo '<a target="_blank" href="'.$link.'">'. $file['filename'].'</a>';

    $stat = stat ($real_filename);
    echo "<td valign=top class=f9>".date ("Y-m-d H:i:s", $stat['mtime']);

    echo "<td valign=top class=f9>". $file["description"];
    echo "<td valign=top>". byte_convert ($file['size']);

	// Delete attachment
	if (give_acl ($config['id_user'], $incident['id_grupo'], 'IM')) {
		    echo "<td>". '<a class="delete" id="delete-file-'.$file["id_attachment"].'"
			href="ajax.php?page=operation/incidents/incident_detail&id='.
			$id_incident.'&delete_file=1&id_attachment='.$file["id_attachment"].'">
			<img src="images/cross.png"></a>';
	}

}

echo "</table>";

?>
