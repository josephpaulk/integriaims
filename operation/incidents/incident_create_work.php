<?PHP

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
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// ADD WORKUNIT CONTROL
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

global $config;
if (check_login() != 0) {
	audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access","Trying to access incident viewer");
	require ("general/noaccess.php");
	exit;
}

$id_incident = (int) get_parameter ("id");
$title = give_db_value ("titulo", "tincidencia", "id_incidencia", $id_incident);
$id_task = give_db_value ("id_task", "tincidencia", "id_incidencia", $id_incident);

if (! give_acl ($config["id_user"], 0, "IR")) {
	return;
}
	
echo "<h3><img src='images/award_star_silver_1.png'>&nbsp;&nbsp;";
echo __('Add workunit')." - $title</h3>";

$now = date ("Y-m-d H:i:s");

echo '<form id="form-add-workunit" method="post" action="index.php?sec=incidents&sec2=operation/incidents/incident_detail">';

echo "<table width='550' class='databox'>";
echo "<tr><td>";
print_input_text ("timestamp", $now, '', 18,  50, false,__('Date'));

echo "<td colspan=2>";
echo "<b>".__('Profile')."</b><br>";
echo combo_roles (1, 'work_profile');

echo "<tr><td>";
print_input_text ("duration", '0', '', 7,  10, false, __('Time used'));

echo "<td>";
print_checkbox ('have_cost', 1, false, false, __('Have cost'));

echo "<td>";
print_checkbox ('public', 1, true, false, __('Public'));

echo '<tr><td colspan="3" class="datos2"><textarea name="nota" rows="15" cols="90">';
echo '</textarea>';
echo "</tr></table>";

echo '<div style="width: 550" class="button">';
print_submit_button (__('Add'), 'addnote', false, 'class="sub next"');
echo '</div>';

print_input_hidden ('insert_workunit', 1);
print_input_hidden ('id', $id_incident);

echo "</form>";
?>
