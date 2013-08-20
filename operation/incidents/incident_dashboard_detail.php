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
$table->width = '100%';
$table->class = "none";
$table->size = array ();
$table->size[0] = '50%';
$table->size[1] = '50%';
$table->style = array();
$table->data = array ();
$table->cellspacing = 0;
$table->cellpadding = 0;
$table->style [0] = "vertical-align: top";
$table->style [1] = "vertical-align: top";

$resolution = incidents_get_incident_resolution_text($id);
$priority = incidents_get_incident_priority_text($id);
$priority_image = print_priority_flag_image ($incident['prioridad'], true);
$group = incidents_get_incident_group_text($id);
$status = incidents_get_incident_status_text($id);
$type = incidents_get_incident_type_text($id);

// Get the status color and icon
if ($incident['estado'] < 3) {
	$status_color = STATUS_COLOR_NEW;
	$status_icon = 'status_new';
}
else if ($incident['estado'] < 7) {
	$status_color = STATUS_COLOR_PENDING;
	$status_icon = 'status_pending';
}
else {
	$status_color = STATUS_COLOR_CLOSED;
	$status_icon = 'status_closed';
}

// Details
$incident_details = "<table width='97%' id='details_table'>";
$incident_details .= "<tr>";
$incident_details .= "<td style='color:" . $status_color . ";'>".__("Status")."</td>";
$incident_details .= "<td>".__("Group")."</td>";
$incident_details .= "<td style='color:" . incidents_get_priority_color($incident) . ";'>".__("Priority")."</td>";
$incident_details .= "<td>".__("Resolution")."</td>";
$incident_details .= "<td>".__("Type")."</td>";
$incident_details .= "</tr>";
$incident_details .= "<tr>";
$incident_details .= "<td>" . print_image('images/' . $status_icon . '.png', true) . "</td>";
$incident_details .= "<td>" . print_image('images/group.png', true) . "</td>";
$incident_details .= "<td>" . $priority_image . "</td>";
$incident_details .= "<td>" . print_image('images/resolution.png', true) . "</td>";
$incident_details .= "<td>" . print_image('images/incident.png', true) . "</td>";
$incident_details .= "</tr>";
$incident_details .= "<tr class='bold incident_details_bottom'>";
$incident_details .= "<td style='color:" . $status_color . ";'>".$status."</td>";
$incident_details .= "<td>".$group."</td>";
$incident_details .= "<td style='color:" . incidents_get_priority_color($incident) . ";'>".$priority."</td>";
$incident_details .= "<td>".$resolution."</td>";
$incident_details .= "<td>".$type."</td>";
$incident_details .= "</tr>";
$incident_details .= "</table>";

$left_side = print_container('incident_details', __('Details'), $incident_details, 'no');

/* Description */
$incident_description = clean_output_breaks($incident["descripcion"]);

$left_side .= print_container('incident_description', __('Description'), $incident_description);

// Advanced details
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
	$obj_table = "<td class='advanced_details_icons'>".print_image('images/object.png', true)."</td>";
	$obj_table .= "<td>".__("Objects affected").":</td>";
	$obj_table .= "</tr>";
	$obj_table .= "<tr>";
	$obj_table .= "<td class='advanced_details_icons'></td><td align='right'><b>".$objects."</b></td>";
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

$incident_adv_details .= "<table class='advanced_details_table alternate'>";
$incident_adv_details .= "<tr>";
$incident_adv_details .= "<td class='advanced_details_icons'>".print_image('images/editor.png', true)."</td>";
$incident_adv_details .= "<td><table><tr><td>".__("Editor").":</td><td align='right'><b>".$editor."</b></td></tr></table></td>";
$incident_adv_details .= "</tr>";
$incident_adv_details .= "<tr>";
$incident_adv_details .= "<td class='advanced_details_icons'>".print_image('images/group.png', true)."</td>";
$incident_adv_details .= "<td><table><tr><td>".__("Creator group").":</td><td align='right'><b>".$creator_group."</b></td></tr></table></td>";
$incident_adv_details .= "</tr>";
$incident_adv_details .= "<tr>";
$incident_adv_details .= "<td class='advanced_details_icons'>".print_image('images/incident.png', true)."</td>";
$incident_adv_details .= "<td><table><tr><td>".__("Parent incident").":</td><td align='right'><b>".$parent."</b></td></tr></table></td>";
$incident_adv_details .= "</tr>";
$incident_adv_details .= "<tr>";
$incident_adv_details .= "<td class='advanced_details_icons'>".print_image('images/task.png', true)."</td>";
$incident_adv_details .= "<td><table><tr><td>".__("Task").":</td><td align='right'><b>".$task."</b></td></tr></table></td>";
$incident_adv_details .= "</tr>";
$incident_adv_details .= "<tr>";
$incident_adv_details .= "<td class='advanced_details_icons'>".print_image('images/sla.png', true)."</td>";
$incident_adv_details .= "<td><table><tr><td>".__("SLA disabled").":</td><td align='right'><b>".$sla."</b></td></tr></table></td>";
$incident_adv_details .= "</tr>";
$incident_adv_details .= "<tr>";
$incident_adv_details .= $obj_table;
$incident_adv_details .= "<tr>";
$incident_adv_details .= "<td class='advanced_details_icons'>".print_image('images/email.png', true)."</td>";
$incident_adv_details .= "<td><table><tr><td>".__("Nofify changes by email").":</td><td align='right'><b>".$email_notify_text."</b></td></tr></table></td>";
$incident_adv_details .= "</tr>";
$incident_adv_details .= $email_table;
$incident_adv_details .= "</table>";

$left_side .= print_container('incident_adv_details', __('Advanced details'), $incident_adv_details);

/**** DASHBOARD RIGHT SIDE ****/

// People
$incident_users .= "<table style='width: 100%;'>";
$incident_users .= "<tr>";

$long_name_creator = get_db_value_filter ("nombre_real", "tusuario", array("id_usuario" => $incident["id_creator"]));
$avatar_creator = get_db_value_filter ("avatar", "tusuario", array("id_usuario" => $incident["id_creator"]));
$incident_users .= "<td>";
$incident_users .= '<div class="bubble">' . print_image('images/avatars/' . $avatar_creator . '.png', true) . '</div>';
$incident_users .= '<span>' . __('Created by') . ':</span><br>' . $long_name_creator;
$incident_users .= "</td>";

$long_name_asigned = get_db_value_filter ("nombre_real", "tusuario", array("id_usuario" => $incident["id_usuario"]));
$avatar_asigned = get_db_value_filter ("avatar", "tusuario", array("id_usuario" => $incident["id_usuario"]));

$incident_users .= "<td>";
$incident_users .= '<div class="bubble">' . print_image('images/avatars/' . $avatar_asigned . '.png', true) . '</div>';
$incident_users .= '<span>' . __('Owned by') . ':</span><br>' . $long_name_asigned;
$incident_users .= "</td>";

$avatar_closer = get_db_value_filter ("avatar", "tusuario", array("id_usuario" => $incident["closed_by"]));

$incident_users .= "<td>";
$incident_users .= '<div class="bubble">';
if ($incident["estado"] != STATUS_CLOSED) {
	$long_name_closer = '<em>' . __('Not closed yet') . '</em>';
	$incident_users .= print_image('images/avatar_notyet.png', true);
}
else if (empty($incident["closed_by"])) {
	$long_name_closer = '<em>' . __('Unknown') . '</em>';
	$incident_users .= print_image('images/avatar_unknown.png', true);
}
else {
	$long_name_closer = get_db_value_filter ("nombre_real", "tusuario", array("id_usuario" => $incident["closed_by"]));
	$incident_users .= print_image('images/avatars/' . $avatar_closer  . '.png', true);
}
$incident_users .= '</div>';
$incident_users .= '<span>' . __('Closed by') . ':</span><br>' . $long_name_closer;
$incident_users .= "</td>";


$incident_users .= "</tr>";

$incident_users .= "</table>";

$right_side = print_container('incident_users', __('People'), $incident_users);

// Incident dates
if ($incident["cierre"] == "0000-00-00 00:00:00") {
	$close_text = __("Not yet");
} else {
	$close_text = $incident["cierre"];
}

$incident_dates .= "<table width='97%' style='text-align: center;' id='incidents_dates_square'>";
$incident_dates .= "<tr>";
$incident_dates .= "<td>".__("Created on").":</td>";
$incident_dates .= "<td>".__("Updated on").":</td>";
$incident_dates .= "<td>".__("Closed on").":</td>";
$incident_dates .= "</tr>";
$incident_dates .= "<tr>";
$incident_dates .= "<td id='created_on' class='mini_calendar'>";

$created_timestamp = strtotime($incident["inicio"]);
$created_on = "<table><tr><th>" . strtoupper(date('M\' y', $created_timestamp)) . "</th></tr>";
$created_on .= "<tr><td class='day'>" . date('d', $created_timestamp) . "</td></tr>";
$created_on .= "<tr><td class='time'>" . print_image('images/cal_clock_grey.png', true) . ' ' . date('H:i:s', $created_timestamp) . "</td></tr></table>";

$incident_dates .= $created_on . "</td>";
$incident_dates .= "<td id='updated_on' class='mini_calendar'>";

$updated_timestamp = strtotime($incident["actualizacion"]);
$updated_on = "<table><tr><th>" . strtoupper(date('M\' y', $updated_timestamp)) . "</th></tr>";
$updated_on .= "<tr><td class='day'>" . date('d', $updated_timestamp) . "</td></tr>";
$updated_on .= "<tr><td class='time'>" . print_image('images/cal_clock_orange.png', true) . ' ' . date('H:i:s', $updated_timestamp) . "</td></tr></table>";

$incident_dates .= $updated_on . "</td>";
$incident_dates .= "</td>";
$incident_dates .= "<td id='closed_on' class='mini_calendar'>";

if ($incident["estado"] == STATUS_CLOSED) {
	$closed_timestamp = strtotime($incident["cierre"]);
	$closed_on = "<table><tr><th>" . strtoupper(date('M\' y', $closed_timestamp)) . "</th></tr>";
	$closed_on .= "<tr><td class='day'>" . date('d', $closed_timestamp) . "</td></tr>";
	$closed_on .= "<tr><td class='time'>" . print_image('images/cal_clock_darkgrey.png', true) . ' ' . date('H:i:s', $closed_timestamp) . "</td></tr></table>";
}
else {
	$closed_on = "<table><tr><th>?</th></tr>";
	$closed_on .= "<tr><td class='day not_yet'>" . strtoupper(__('Not yet')) . "</td></tr>";
	$closed_on .= "</table>";
}

$incident_dates .= $closed_on . "</td>";
$incident_dates .= "</td>";
$incident_dates .= "</tr>";
$incident_dates .= "</table>";

$right_side .= print_container('incident_dates', __('Dates'), $incident_dates);

// SLA information
if ($incident["sla_disabled"]) {
	$incident_sla .= '<table width="97%">';
	$incident_sla .= '<tr>';
	$incident_sla .= "<td style='text-align: center;'>";
	$incident_sla .= "<em>".__("SLA disabled")."</em>";
	$incident_sla .= "</td>";
	$incident_sla .= "</tr>";
	$incident_sla .= "</table>";
} else {
	$incident_sla .= '<table width="97%" style="border-spacing: 10px;">';
	$incident_sla .= '<tr>';
	$incident_sla .= "<td>";
	$incident_sla .= __('SLA history compliance for: '); 
	$incident_sla .= "</td>";
	$incident_sla .= "<td style='vertical-align: bottom;'>";

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
		$incident_sla .= "<strong>".$fields[$period]."</strong>";
	} else {
		$incident_sla .= print_select ($fields, "period", $period, 'reload_sla_slice_graph(\''.$id.'\');', '', '', true, 0, false, false, false, 'width: 75px');
	}

	$incident_sla .= "</td>";
	$incident_sla .= "<td colspan=2 style='text-align: center; width: 50%;'>";
	$incident_sla .= __('SLA total compliance (%)'). ': ';
	$incident_sla .= format_numeric (get_sla_compliance_single_id ($id));
	$incident_sla .= "</td>";
	$incident_sla .= "</tr>";
	$incident_sla .= "<tr>";
	$incident_sla .= "<td id=slaSlicebarField colspan=2 style='text-align: center; padding: 1px 2px 1px 5px;'>";
	$incident_sla .= graph_sla_slicebar ($id, $period, 155, 15, $ttl);
	$incident_sla .= "</td>";
	$incident_sla .= "<td colspan=2 style='text-align: center;' class='pie_frame'>";
	$incident_sla .= graph_incident_sla_compliance ($id, 155, 170, $ttl);
	$incident_sla .= "</td>";	
	$incident_sla .= "<tr>";
	$incident_sla .= "</table>";
}

$right_side .= print_container('incident_sla', __('SLA information'), $incident_sla);

$table->data[0][0] = $left_side;
$table->data[0][1] = $right_side;

echo "<div id='indicent-details-view'>";

echo '<h1>'.__('Incident').' #'.$incident["id_incidencia"].' - '.$incident['titulo'];
echo "<div id='button-bar-title'>";
echo "<ul>";

//Only incident manager and user with IR flag which are owners and admin can edit incidents
if (get_admin_user($config['id_user']) || (give_acl ($config['id_user'], $id_grupo, "IW") 
	&& ($incident["id_usuario"] == $config["id_user"])) || give_acl ($config['id_user'], $id_grupo, "IM")) {
	echo "<li>";
	echo '<a href="index.php?sec=incidents&sec2=operation/incidents/incident_detail&id='.$id.'">'.print_image("images/application_edit.png", true, array("title" => __("Edit"))).'</a>';
	echo "</li>";
}
echo '<li>';
echo '<a href="index.php?sec=incidents&sec2=operation/incidents/incident_dashboard_detail&id='.$id.'&tab=workunits#incident-operations">'.print_image("images/award_star_silver_1.png", true, array("title" => __('Workunits'))).'</a>';
echo '</li>';
echo '<li>';
echo '<a href="index.php?sec=incidents&sec2=operation/incidents/incident_dashboard_detail&id='.$id.'&tab=files#incident-operations">'.print_image("images/disk.png", true, array("title" => __('Files'))).'</a>';
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
	echo '<li class="ui-tabs">';
}
echo '<a href="index.php?sec=incidents&sec2=operation/incidents/incident_dashboard_detail&id='.$id.'&tab=workunits#incident-operations"><span>'.__('Workunits').'</span></a>';
echo '</li>';

if ($tab === "files") {
	echo '<li class="ui-tabs-selected">';
} else {
	echo '<li class="ui-tabs">';
}
echo '<a href="index.php?sec=incidents&sec2=operation/incidents/incident_dashboard_detail&id='.$id.'&tab=files#incident-operations"><span>'.__('Files').'</span></a>';
echo '</li>';

if ($tab === "tracking") {
	echo '<li class="ui-tabs-selected">';
} else {
	echo '<li class="ui-tabs">';
}

echo '<a href="index.php?sec=incidents&sec2=operation/incidents/incident_dashboard_detail&id='.$id.'&tab=tracking#incident-operations"><span>'.__('Tracking').'</span></a>';
echo '</li>';

if ($tab === "inventory") {
	echo '<li class="ui-tabs-selected">';
} else {
	echo '<li class="ui-tabs">';
}

echo '<a href="index.php?sec=incidents&sec2=operation/incidents/incident_dashboard_detail&id='.$id.'&tab=inventory#incident-operations"><span>'.__('Inventory').'</span></a>';
echo '</li>';

if ($tab === "contacts") {
	echo '<li class="ui-tabs-selected">';
} else {
	echo '<li class="ui-tabs">';
}

echo '<a href="index.php?sec=incidents&sec2=operation/incidents/incident_dashboard_detail&id='.$id.'&tab=contacts#incident-operations"><span>'.__('Contacts').'</span></a>';
echo '</li>';

echo '<li class="ui-tabs-title">';
switch ($tab) {
	case "workunits":
		echo "<h2>".__('Add workunit')."</h2>";
		break;
	case "files":
		echo "<h2>".__('Add file')."</h2>";
		break;
	case "inventory":
		echo "<h2>".__('Inventory objects')."</h2>";
		break;
	case "contacts":
		echo "<h2>".__('Contacts grouped by inventory')."</h2>";
		break;
	case "tracking":
		echo "<h2>".__('Tracking')."</h2>";
		break;
	default:
		break;
}
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

<script type="text/javascript" src="include/js/integria_incident_search.js"></script>

<script type="text/javascript">
$('.incident_container h2').click(function() {
	var arrow = $('#' + $(this).attr('id') + ' img').attr('src');
	var arrow_class = $('#' + $(this).attr('id') + ' img').attr('class');
	var new_arrow = '';
	
	if (arrow_class == 'arrow_down') {
		new_arrow = arrow.replace(/_down/gi, "_right");
		$('#' + $(this).attr('id') + ' img').attr('class', 'arrow_right')
	}
	else {
		new_arrow = arrow.replace(/_right/gi, "_down");
		$('#' + $(this).attr('id') + ' img').attr('class', 'arrow_down')
	}
	
	$('#' + $(this).attr('id') + ' img').attr('src', new_arrow);
});

</script>
