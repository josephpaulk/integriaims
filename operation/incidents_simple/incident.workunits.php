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

// CHECK LOGIN AND ACLs
check_login ();

if (! give_acl ($config['id_user'], 0, "IR")) {
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access incident viewer");
	require ("general/noaccess.php");
	exit;
}

// SHOW THE WORKUNITS
$table->class = 'result_table listing';
$table->width = $width;
$table->id = 'incident_search_result_table';
$separator_style = 'border-bottom: 1px solid rgb(204, 204, 204);border-top: 1px solid rgb(204, 204, 204);';
$table->style = array ();


$table->data = array();

$table->head = array ();

$row = 0;

if(empty($incident['workunit'])) {
	$table->colspan[$row][0] = 2;
	$table->data[$row][0] = '<i>'.__('No workunit was done in this incident').'</i>';
}

foreach($incident['workunits'] as $k => $workunit) {
	$table->colspan[$row+$k][0] = 2;
	
	$avatar = get_db_value ("avatar", "tusuario", "id_usuario", $workunit['id_user']);
	
	$wu = '';
	$wu = "<div class='notetitle' style='width:95%'>"; // titulo
	// Show data
	$wu .= "<img src='images/avatars/".$avatar."_small.png'>&nbsp;";
	$wu .= " <a href='index.php?sec=users&sec2=operation/users/user_edit&id=".$workunit['id_user']."'>";
	$wu .= $workunit['id_user'];
	$wu .= "</a>";
	$wu .= ' '.__('said on').' '.$workunit['timestamp'];
	$wu .= "</div>";

	// Body
	$wu .= "<div class='notebody' style='width:95%'>";
	$wu .= clean_output_breaks($workunit['description']);
	$wu .= "</div>";
	
	$table->data[$row+$k][0] = $wu;
}

echo '<div id="workunits_data" class="tab_data" style="display:none">';
print_table($table);
echo '</div>';

unset($table);

?>
