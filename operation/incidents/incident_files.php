<?php

// Integria IMS - http://integriaims.com
// ==================================================
// Copyright (c) 2008-2010 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

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

//user with IR and incident creator see the information
if (! give_acl ($config["id_user"], $incident['id_grupo'], "IR")
	&& ($incident['id_creator'] != $config['id_user'])) {
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

     $link = "operation/incidents/incident_download_file.php?id_attachment=".$file["id_attachment"];

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
		    echo "<td>". '<a class="delete" name="delete_file_'.$file["id_attachment"].'" href="javascript:">
			<img src="images/cross.png"></a>';
	}

}

echo "</table>";

?>

<script type="text/javascript">
$('a[name^="delete_file_"]').click(function() {
	id_attachment = $(this).attr('name').split('_')[2];
	row = $(this).parent().parent();
	
	values = Array ();
	values.push ({name: "page", value: "operation/incidents/incident_detail"});
	values.push ({name: "delete_file", value: 1});
	values.push ({name: "id_attachment", value: id_attachment});
	values.push ({name: "id", value: <?php echo $id_incident; ?>});
	
	jQuery.get ("ajax.php",
		values,
		function (data, status) {
			// If the return is succesfull we hide the deleted file row
			if(data.search('class="error"') == -1) {
				row.hide();
			}
			$(".result").html(data);
		},
		"html"
	);
	return false;

});
</script>
