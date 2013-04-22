<?php
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008-2012 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

$id = get_parameter("id", false);

if ($id) {
	$incident = get_incident ($id);
	if ($incident !== false) {
		$id_grupo = $incident['id_grupo'];
	}
}

if (isset($incident)) {
	//Incident creators must see their incidents
	if ((get_external_user($config["id_user"]) && ($incident["id_creator"] != $config["id_user"]))
		|| ($incident["id_creator"] != $config["id_user"]) && !give_acl ($config['id_user'], $id_grupo, "IR")) {
	
	 	// Doesn't have access to this page
		audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to incident  (External user) ".$id);
		include ("general/noaccess.php");
		exit;
	}
}
else if (! give_acl ($config['id_user'], $id_grupo, "IR")) {
	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to incident ".$id);
	include ("general/noaccess.php");
	exit;
}

/* Users affected by the incident */
$table->width = '98%';
$table->class = "none";
$table->size = array ();
$table->size[0] = '50%';
$table->size[1] = '50%';
$table->style = array();
$table->data = array ();
$table->cellspacing = 2;
$table->cellpadding = 2;
$table->style [0] = "vertical-align: top";
$table->style [1] = "vertical-align: top";

$left_side = '<h2 class="incident_dashboard" onclick="toggleDiv (\'incident-details\')">'.__('Details').'</h2>';
$left_side .= '<div id="incident-details">';

$resolution = incidents_get_incident_resolution_text($id);
$priority = incidents_get_incident_priority_text($id);
$group = incidents_get_incident_group_text($id);
$status = incidents_get_incident_status_text($id);
$type = incidents_get_incident_type_text($id);

$left_side .= "<table width='97%'>";
$left_side .= "<tr>";
$left_side .= "<td>".__("Status").":</td><td align='right'>".$status."</td>";
$left_side .= "</tr>";
$left_side .= "<tr>";
$left_side .= "<td>".__("Group").":</td><td align='right'>".$group."</td>";
$left_side .= "</tr>";
$left_side .= "<tr>";
$left_side .= "<td>".__("Priority").":</td><td align='right'>".$priority."</td>";
$left_side .= "</tr>";
$left_side .= "<tr>";
$left_side .= "<td>".__("Resolution").":</td><td align='right'>".$resolution."</td>";
$left_side .= "</tr>";
$left_side .= "<tr>";
$left_side .= "<td>".__("Type").":</td><td align='right'>".$type."</td>";
$left_side .= "</tr>";
$left_side .= "</table>";

//echo incident_details_list ($id, true);

$left_side .= '</div>';

/* Users affected by the incident */



$left_side .= '<h2 class="incident_dashboard" onclick="toggleDiv (\'incident-description\')">'.__('Description').'</h2>';
$left_side .= '<div id="incident-description">';
$left_side .= $incident["descripcion"];
$left_side .= '</div>';

$left_side .= '<h2 class="incident_dashboard" onclick="toggleDiv (\'incident-adv-details\')">'.__('Advanced details').'</h2>';
$left_side .= '<div id="incident-adv-details" style="display: none">';

$editor = get_db_value_filter ("nombre_real", "tusuario", array("id_usuario" => $incident["editor"]));
$creator_group = get_db_value_filter ("nombre", "tgrupo", array("id_grupo" => $incident["id_group_creator"]));

if ($incident["sla_disabled"]) {
	$sla = __("Yes");
}  else {
	$sla = __("No");
}

$task = incidents_get_incident_task_text($id);
$parent = __("Incident")." #".$incident["id_parent"];

$objects = get_inventories_in_incident($id);

if ($objects) {
	$objects = implode(", ", $objects);
	$obj_table = "<td colspan='2'>".__("Objects affected").":</td>";
	$obj_table .= "</tr>";
	$obj_table .= "<tr>";
	$obj_table .= "<td colspan='2' align='right'>".$objects."</td>";
	$obj_table .= "</tr>";
} else {
	$objects = __("None");
	$obj_table = "<td>".__("Objects affected").":</td>";
	$obj_table .= "<td align='right'>".$objects."</td>";
	$obj_table .= "</tr>";	
}

$email_notify = $incident["notify_email"];

if ($email_notify) { 
	$email_notify_text = __("Yes");
} else {
	$email_notify_text = __("No");
}

$emails = $incident["email_copy"];

if ($emails) {
	
	$email_table = "<tr>";
	$email_table .= "<td colspan='2' align='right'>".$emails."</td>";
	$email_table .= "</tr>";
	
}

$left_side .= "<table width='97%'>";
$left_side .= "<tr>";
$left_side .= "<td>".__("Editor").":</td><td align='right'>".$editor."</td>";
$left_side .= "</tr>";
$left_side .= "<tr>";
$left_side .= "<td>".__("Creator group").":</td><td align='right'>".$creator_group."</td>";
$left_side .= "</tr>";
$left_side .= "<tr>";
$left_side .= "<td>".__("Parent incident").":</td><td align='right'>".$parent."</td>";
$left_side .= "</tr>";
$left_side .= "<tr>";
$left_side .= "<td>".__("Task").":</td><td align='right'>".$task."</td>";
$left_side .= "</tr>";
$left_side .= "<tr>";
$left_side .= "<td>".__("SLA disabled").":</td><td align='right'>".$sla."</td>";
$left_side .= "</tr>";
$left_side .= "<tr>";
$left_side .= $obj_table;
$left_side .= "<tr>";
$left_side .= "<td>".__("Nofify changes by email").":</td><td align='right'>".$email_notify_text."</td>";
$left_side .= "</tr>";
$left_side .= $email_table;
$left_side .= "</table>";
$left_side .= '</div>';

/**** DASHBOAR RIGHT SIDE ****/

$right_side = '<h2 class="incident_dashboard" onclick="toggleDiv (\'incident-users\')">'.__('People').'</h2>';
$right_side .= '<div id="incident-users">';

$right_side .= "<table width='97%'>";
$right_side .= "<tr>";

$long_name_creator = get_db_value_filter ("nombre_real", "tusuario", array("id_usuario" => $incident["id_creator"]));

$right_side .= "<td>".__("Creator").":</td><td align='right'>".$long_name_creator."</td>";
$right_side .= "</tr>";
$right_side .= "<tr>";

$long_name_asigned = get_db_value_filter ("nombre_real", "tusuario", array("id_usuario" => $incident["id_usuario"]));

$right_side .= "<td>".__("Asigned user").":</td><td align='right'>".$long_name_asigned."</td>";
$right_side .= "</tr>";

$right_side .= "</table>";
//echo incident_users_list ($id, true);
$right_side .= "</div>";

$right_side .= '<h2 class="incident_dashboard" onclick="toggleDiv (\'incident-dates\')">'.__('Dates').'</h2>';
$right_side .= '<div id="incident-dates">';

$right_side .= "<table width='97%'>";
$right_side .= "<tr>";
$right_side .= "<td>".__("Created").":</td><td align='right'>".$incident["inicio"]."</td>";
$right_side .= "</tr>";
$right_side .= "<tr>";
$right_side .= "<td>".__("Updated").":</td><td align='right'>".$incident["actualizacion"]."</td>";
$right_side .= "</tr>";
$right_side .= "<tr>";

if ($incident["cierre"] == "0000-00-00 00:00:00") {
	$close_text = __("Not yet");
} else {
	$close_text = $incident["cierre"];
}

$right_side .= "<td>".__("Resolved").":</td><td align='right'>".$close_text."</td>";
$right_side .= "</tr>";
$right_side .= "</table>";
$right_side .= '</div>';


$right_side .= '<h2 class="incident_dashboard" onclick="toggleDiv (\'incident-time-tracking\')">'.__('Statistics').'</h2>';
$right_side .= '<div id="incident-time-tracking">';
$right_side .= "<table width='97%'>";
$right_side .= "<tr>";
$right_side .= "<td>".__("Creator").":</td><td align='right'>".$long_name_creator."</td>";
$right_side .= "</tr>";
$right_side .= "<tr>";
$right_side .= "<td>".__("Asigned user").":</td><td align='right'>".$long_name_asigned."</td>";
$right_side .= "</tr>";
$right_side .= "</table>";
$right_side .= "</div>";

$table->data[0][0] .= $left_side;
$table->data[0][1] .= $right_side;

echo "<div id='indicent-details-view'>";

echo '<h1>'.__('Incident').' #'.$incident["id_incidencia"].' - '.$incident['titulo'];

echo "<div class='button-bar-title'>";
echo '<input type="button" id="button-add_workunit_show" name="add_workunit_show" value="Edit" onclick="toggleDiv(\'indicent-details-view\');toggleDiv(\'indicent-details-edit\')" style="margin-top:8px;" class="action_btn sub next">';
echo "<a href='#incident-operations'>".__("Workunits")."</a>";
echo "<a href='#incident-operations'>".__("Files")."</a>";
echo "</div>";

echo '</h1>';

print_table($table);

echo "<a name='incident-operations'></a>";

echo '<ul class="ui-tabs-nav">';
echo '<li id="tabmenu1"><a href="#workunits"><span>'.__('Workunits').'</span></a></li>';
echo '<li id="tabmenu2"><a href="#files"><span>'.__('Files').'</span></a></li>';
echo '</ul>';

echo "<div id='tab1' class='ui-tabs-panel'>";
include("incident_workunits.php");
echo "</div>";
echo "<div id='tab2' class='ui-tabs-panel ui-tabs-hide'>";
include("incident_attach_file.php");
include("incident_files.php");
echo "</div>";

echo "</div>";

echo "<div id='indicent-details-edit' style='display: none'>";
include("incident_detail.php");
echo "</div>";

?>

<script type="text/javascript">

$(document).ready (function () {	
	
	////////Incident dashboard tab interaction///////
	$('#tabmenu1').click (function (e){
		e.preventDefault();//Deletes default behavior
		console.log("tab1");
		//Change CSS tabs
		//tab1 selected
		$('#tabmenu1').addClass("ui-tabs-selected");
		$('#tabmenu1').removeClass("ui-tabs");
		
		//tab2 not selecteed
		$('#tabmenu2').addClass("ui-tabs");
		$('#tabmenu2').removeClass("ui-tabs-selected");
		
		//Show/hide divs
		$('#tab2').addClass("ui-tabs-hide");
		$('#tab1').removeClass("ui-tabs-hide");
	});

	$('#tabmenu2').click (function (e){
		e.preventDefault();//Deletes default behavior
		
		//Change CSS tabs
		//tab2 selected
		$('#tabmenu2').addClass("ui-tabs-selected");
		$('#tabmenu2').removeClass("ui-tabs");
		
		//tab1 not selecteed
		$('#tabmenu1').addClass("ui-tabs");
		$('#tabmenu1').removeClass("ui-tabs-selected");

		//Show/hide divs				
		$('#tab1').addClass("ui-tabs-hide");
		$('#tab2').removeClass("ui-tabs-hide");
	});
});
</script>
