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

include_once ("include/functions_graph.php");

$id = get_parameter("id", false);

if ($id) {
	$incident = get_incident ($id);
	
	if ($incident !== false) {
		$id_grupo = $incident['id_grupo'];
	} else {
		echo "<h1>".__("Incident")."</h1>";
		echo "<h3 class='error'>".__("There is no information for this incident")."</h3>";
		echo "<br>";
		echo "<a style='margin-left: 90px' href='index.php?sec=incidents&sec2=operation/incidents/incident_search'>".__("Try the search form to find the incident")."</a>";
		return;
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

$email_table ="";

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

/**** DASHBOARD RIGHT SIDE ****/

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
$right_side .= "<td>".__("Created on").":</td><td align='right'>".$incident["inicio"]."</td>";
$right_side .= "</tr>";
$right_side .= "<tr>";
$right_side .= "<td>".__("Updated on").":</td><td align='right'>".$incident["actualizacion"]."</td>";
$right_side .= "</tr>";
$right_side .= "<tr>";

if ($incident["cierre"] == "0000-00-00 00:00:00") {
	$close_text = __("Not yet");
} else {
	$close_text = $incident["cierre"];
}

$right_side .= "<td>".__("Closed on").":</td><td align='right'>".$close_text."</td>";
$right_side .= "</tr>";
$right_side .= "</table>";
$right_side .= '</div>';


$right_side .= '<h2 class="incident_dashboard" onclick="toggleDiv (\'incident-sla\')">'.__('SLA information').'</h2>';
$right_side .= '<div id="incident-sla">';
$right_side .= '<table width="97%">';
$right_side .= '<tr>';
$right_side .= "<td style='text-align: center;'>";
$right_side .= __('SLA history compliance for: '); 
$right_side .= "</td>";
$right_side .= "<td align=center>";

$a_day = 24*3600;

$fields = array($a_day => "1 day",
				2*$a_day => "2 days",
				7*$a_day => "1 week",
				14*$a_day => "2 weeks",
				30*$a_day => "1 month");

$period = get_parameter("period", $a_day);
$ttl = 1;

if ($clean_output) {
	$ttl = 2;
}

if ($clean_output) {
	$right_side .= "<strong>".$fields[$period]."</strong>";
} else {
	$right_side .= print_select ($fields, "period", $period, 'reload_sla_slice_graph(\''.$id.'\');', '', '', true, 0, false, false, false, 'width: 75px');
}

$right_side .= "</td>";
$right_side .= "<td colspan=2 style='text-align: center;'>";
$right_side .= __('SLA total compliance (%)'). ': ';
$right_side .= format_numeric (get_sla_compliance_single_id ($id));
$right_side .= "</td>";
$right_side .= "</tr>";
$right_side .= "<tr>";
$right_side .= "<td id=slaSlicebarField colspan=2 style='text-align: center; padding: 1px 2px 1px 5px;'>";
$right_side .= graph_sla_slicebar ($id, $period, 225, 15, $ttl);
$right_side .= "</td>";
$right_side .= "<td colspan=2 style='text-align: center;'>";
$right_side .= graph_incident_sla_compliance ($id, 200, 200, $ttl);
$right_side .= "</td>";	
$right_side .= "<tr>";
$right_side .= "</table>";
$right_side .= "</div>";

$table->data[0][0] = $left_side;
$table->data[0][1] = $right_side;

echo "<div id='indicent-details-view'>";

echo '<h1>'.__('Incident').' #'.$incident["id_incidencia"].' - '.$incident['titulo'];
echo "<div id='button-bar-title'>";
echo "<ul>";
echo "<li>";
echo '<a href="index.php?sec=incidents&sec2=operation/incidents/incident_detail&id='.$id.'">'.__("Edit").'</a>';
echo "</li>";
echo '<li>';
echo '<a href="index.php?sec=incidents&sec2=operation/incidents/incident_dashboard_detail&id='.$id.'&tab=workunits#incident-operations">'.__('Workunits').'</a>';
echo '</li>';
echo '<li>';
echo '<a href="index.php?sec=incidents&sec2=operation/incidents/incident_dashboard_detail&id='.$id.'&tab=files#incident-operations">'.__('Files').'</a>';
echo '</li>';
echo "</ul>";
echo "</div>";
echo "</h1>";

print_table($table);

echo "<a name='incident-operations'></a>";

echo "<div id='tab' class='ui-tabs-panel'>";
$tab = get_parameter("tab", "workunits");

//Print lower menu tab
echo '<ul class="ui-tabs-nav">';

if ($tab === "workunits") {
	echo '<li class="ui-tabs-selected">';
} else {
	echo '<li>';
}
echo '<a href="index.php?sec=incidents&sec2=operation/incidents/incident_dashboard_detail&id='.$id.'&tab=workunits#incident-operations"><span>'.__('Workunits').'</span></a>';
echo '</li>';

if ($tab === "files") {
	echo '<li class="ui-tabs-selected">';
} else {
	echo '<li>';
}
echo '<a href="index.php?sec=incidents&sec2=operation/incidents/incident_dashboard_detail&id='.$id.'&tab=files#incident-operations"><span>'.__('Files').'</span></a>';
echo '</li>';

if ($tab === "tracking") {
	echo '<li class="ui-tabs-selected">';
} else {
	echo '<li>';
}

echo '<a href="index.php?sec=incidents&sec2=operation/incidents/incident_dashboard_detail&id='.$id.'&tab=tracking#incident-operations"><span>'.__('Tracking').'</span></a>';
echo '</li>';

if ($tab === "inventory") {
	echo '<li class="ui-tabs-selected">';
} else {
	echo '<li>';
}

echo '<a href="index.php?sec=incidents&sec2=operation/incidents/incident_dashboard_detail&id='.$id.'&tab=inventory#incident-operations"><span>'.__('Inventory').'</span></a>';
echo '</li>';

if ($tab === "contacts") {
	echo '<li class="ui-tabs-selected">';
} else {
	echo '<li>';
}

echo '<a href="index.php?sec=incidents&sec2=operation/incidents/incident_dashboard_detail&id='.$id.'&tab=contacts#incident-operations"><span>'.__('Contacts').'</span></a>';
echo '</li>';

echo '</ul>';

switch ($tab) {
	case "workunits":
		include("incident_workunits.php");
		break;
	case "files":
		include("incident_files.php");
		break;
	case "inventory":
		include("incident_inventory_detail.php");
		break;
	case "contacts":
		include("incident_inventory_contacts.php");
		break;
	case "tracking":
		include("incident_tracking.php");
		break;
	default:
		break;
}

echo "</div>";

echo "</div>";

?>

<script type="text/javascript">

function reload_sla_slice_graph(id) {

	var period = $('#period').val();
	
	values = Array ();
	values.push ({name: "type",
		value: "sla_slicebar"});
	values.push ({name: "id_incident",
		value: id});
	values.push ({name: "period",
		value: period});
	values.push ({name: "is_ajax",
		value: 1});
	values.push ({name: "width",
		value: 225});		
	values.push ({name: "height",
		value: 15});		

	jQuery.get ('include/functions_graph.php',
		values,
		function (data) {
			$('#slaSlicebarField').html(data);
		},
		'html'
	);
}

</script>
