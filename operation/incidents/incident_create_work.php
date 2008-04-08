<?PHP

// Integria 1.0 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2008 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// ADD WORKUNIT CONTROL
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

global $config;
if (check_login() != 0) {
	audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access","Trying to access incident viewer");
	require ("general/noaccess.php");
	exit;
}

$id_inc = give_parameter_get("id",-1);
$title = give_db_value ("titulo", "tincidencia", "id_incidencia", $id_inc);
$id_task = give_db_value ("id_task", "tincidencia", "id_incidencia", $id_inc);

if (give_acl($config["id_user"], 0, "IR")==1){
	
	echo "<h3><img src='images/award_star_silver_1.png'>&nbsp;&nbsp;";
	echo $lang_label["add_workunit"]." - $title</h3>";

	$ahora=date("Y/m/d H:i:s");

	echo "<table cellpadding=3 cellspacing=3 border=0 width='700' class='databox_color' >";
	echo "<form name='nota' method='post' action='index.php?sec=incidents&sec2=operation/incidents/incident_detail&insert_workunit=1&id=".$id_inc."'>";
	echo "<input type='hidden' name='timestamp' value='".$ahora."'>";
	echo "<input type='hidden' name='id_inc' value='".$id_inc."'>";
	echo "<tr><td class='datos' width='140'><b>".$lang_label["date"]."</b></td>";
	echo "<td class='datos'>".$ahora;

	echo "<tr><td class='datos2'  width='140'>";
	echo "<b>".$lang_label["profile"]."</b>";
	echo "<td class='datos2'>";
	echo combo_roles (1, 'work_profile');

	echo "&nbsp;&nbsp;";
	echo "<input type='checkbox' name='have_cost' value=1>";
	echo "&nbsp;&nbsp;";
	echo "<b>".$lang_label["have_cost"]."</b>";

	echo "<tr><td class='datos'>";
	echo "<b>".$lang_label["time_used"]."</b>";
	echo "<td class='datos'>";
	echo "<input type='text' name='duration' value='0' size='7'>";
	
	echo '<tr><td colspan="2" class="datos2"><textarea name="nota" rows="8" cols="90">';
	echo '</textarea>';
	echo "</tr></table>";
	echo '<input name="addnote" type="submit" class="sub next" value="'.$lang_label["add"].'">';
	echo "</form>";
}



?>