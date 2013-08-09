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

$left_side = '<div class="incident_container">';
$left_side .= '<h2 id="incident_details" class="incident_dashboard incident_details" onclick="toggleDiv (\'incident-details\')">' . __('Details') . '&nbsp;&nbsp;' . print_image('images/arrow_down.png', true, array('class' => 'arrow_down')) . '</h2>';
$left_side .= '<div id="incident-details">';

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

$left_side .= "<table width='97%' id='details_table'>";
$left_side .= "<tr>";
$left_side .= "<td style='color:" . $status_color . ";'>".__("Status")."</td>";
$left_side .= "<td>".__("Group")."</td>";
$left_side .= "<td style='color:" . incidents_get_priority_color($incident) . ";'>".__("Priority")."</td>";
$left_side .= "<td>".__("Resolution")."</td>";
$left_side .= "<td>".__("Type")."</td>";
$left_side .= "</tr>";
$left_side .= "<tr>";
$left_side .= "<td>" . print_image('images/' . $status_icon . '.png', true) . "</td>";
$left_side .= "<td>" . print_image('images/group.png', true) . "</td>";
$left_side .= "<td>" . $priority_image . "</td>";
$left_side .= "<td>" . print_image('images/resolution.png', true) . "</td>";
$left_side .= "<td>" . print_image('images/incident.png', true) . "</td>";
$left_side .= "</tr>";
$left_side .= "<tr class='bold incident_details_bottom'>";
$left_side .= "<td style='color:" . $status_color . ";'>".$status."</td>";
$left_side .= "<td>".$group."</td>";
$left_side .= "<td style='color:" . incidents_get_priority_color($incident) . ";'>".$priority."</td>";
$left_side .= "<td>".$resolution."</td>";
$left_side .= "<td>".$type."</td>";
$left_side .= "</tr>";
$left_side .= "</table>";

$left_side .= '</div>';
$left_side .= "</div>"; // container

/* Users affected by the incident */
$left_side .= '<div class="incident_container">';
$left_side .= '<h2 id="incident_description" class="incident_dashboard incident_description" onclick="toggleDiv (\'incident-description\')">' . __('Description') . '&nbsp;&nbsp;' . print_image('images/arrow_down.png', true, array('class' => 'arrow_down')) . '</h2>';
$left_side .= '<div id="incident-description">';

$left_side .= clean_output_breaks($incident["descripcion"]);

$left_side .= '</div>';
$left_side .= "</div>"; // container

$left_side .= '<div class="incident_container">';
$left_side .= '<h2 id="incident_adv_details" class="incident_dashboard incident_adv_details" onclick="toggleDiv (\'incident-adv-details\')">' . __('Advanced details') . '&nbsp;&nbsp;' . print_image('images/arrow_right.png', true, array('class' => 'arrow_right')) . '</h2>';
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

$left_side .= "<table class='advanced_details_table alternate'>";
$left_side .= "<tr>";
$left_side .= "<td class='advanced_details_icons'>".print_image('images/editor.png', true)."</td>";
$left_side .= "<td><table><tr><td>".__("Editor").":</td><td align='right'><b>".$editor."</b></td></tr></table></td>";
$left_side .= "</tr>";
$left_side .= "<tr>";
$left_side .= "<td class='advanced_details_icons'>".print_image('images/group.png', true)."</td>";
$left_side .= "<td><table><tr><td>".__("Creator group").":</td><td align='right'><b>".$creator_group."</b></td></tr></table></td>";
$left_side .= "</tr>";
$left_side .= "<tr>";
$left_side .= "<td class='advanced_details_icons'>".print_image('images/incident.png', true)."</td>";
$left_side .= "<td><table><tr><td>".__("Parent incident").":</td><td align='right'><b>".$parent."</b></td></tr></table></td>";
$left_side .= "</tr>";
$left_side .= "<tr>";
$left_side .= "<td class='advanced_details_icons'>".print_image('images/task.png', true)."</td>";
$left_side .= "<td><table><tr><td>".__("Task").":</td><td align='right'><b>".$task."</b></td></tr></table></td>";
$left_side .= "</tr>";
$left_side .= "<tr>";
$left_side .= "<td class='advanced_details_icons'>".print_image('images/sla.png', true)."</td>";
$left_side .= "<td><table><tr><td>".__("SLA disabled").":</td><td align='right'><b>".$sla."</b></td></tr></table></td>";
$left_side .= "</tr>";
$left_side .= "<tr>";
$left_side .= $obj_table;
$left_side .= "<tr>";
$left_side .= "<td class='advanced_details_icons'>".print_image('images/email.png', true)."</td>";
$left_side .= "<td><table><tr><td>".__("Nofify changes by email").":</td><td align='right'><b>".$email_notify_text."</b></td></tr></table></td>";
$left_side .= "</tr>";
$left_side .= $email_table;
$left_side .= "</table>";
$left_side .= '</div>';
$left_side .= "</div>"; // container

/**** DASHBOARD RIGHT SIDE ****/

$right_side = '<div class="incident_container">';
$right_side .= '<h2 id="incident_users" class="incident_dashboard incident_users" onclick="toggleDiv (\'incident-users\')">' . __('People') . '&nbsp;&nbsp;' . print_image('images/arrow_down.png', true, array('class' => 'arrow_down')) . '</h2>';
$right_side .= '<div id="incident-users">';

$right_side .= "<table style='width: 100%;'>";
$right_side .= "<tr>";

$long_name_creator = get_db_value_filter ("nombre_real", "tusuario", array("id_usuario" => $incident["id_creator"]));
$avatar_creator = get_db_value_filter ("avatar", "tusuario", array("id_usuario" => $incident["id_creator"]));
$right_side .= "<td>";
$right_side .= '<div class="bubble">' . print_image('images/avatars/' . $avatar_creator . '.png', true) . '</div>';
$right_side .= '<span>' . __('Created by') . ':</span><br>' . $long_name_creator;
$right_side .= "</td>";

$long_name_asigned = get_db_value_filter ("nombre_real", "tusuario", array("id_usuario" => $incident["id_usuario"]));
$avatar_asigned = get_db_value_filter ("avatar", "tusuario", array("id_usuario" => $incident["id_usuario"]));

$right_side .= "<td>";
$right_side .= '<div class="bubble">' . print_image('images/avatars/' . $avatar_asigned . '.png', true) . '</div>';
$right_side .= '<span>' . __('Owned by') . ':</span><br>' . $long_name_asigned;
$right_side .= "</td>";

$avatar_closer = get_db_value_filter ("avatar", "tusuario", array("id_usuario" => $incident["closed_by"]));

$right_side .= "<td>";
$right_side .= '<div class="bubble">';
if ($incident["estado"] != STATUS_CLOSED) {
	$long_name_closer = '<em>' . __('Not closed yet') . '</em>';
	$right_side .= print_image('images/avatar_notyet.png', true);
}
else if (empty($incident["closed_by"])) {
	$long_name_closer = '<em>' . __('Unknown') . '</em>';
	$right_side .= print_image('images/avatar_unknown.png', true);
}
else {
	$long_name_closer = get_db_value_filter ("nombre_real", "tusuario", array("id_usuario" => $incident["closed_by"]));
	$right_side .= print_image('images/avatars/' . $avatar_closer  . '.png', true);
}
$right_side .= '</div>';
$right_side .= '<span>' . __('Closed by') . ':</span><br>' . $long_name_closer;
$right_side .= "</td>";


$right_side .= "</tr>";

$right_side .= "</table>";
//echo incident_users_list ($id, true);
$right_side .= "</div>";
$right_side .= "</div>"; // container

$right_side .= '<div class="incident_container">';
$right_side .= '<h2 id="incident_dates" class="incident_dashboard incident_dates" onclick="toggleDiv (\'incident-dates\')">' . __('Dates') . '&nbsp;&nbsp;' . print_image('images/arrow_down.png', true, array('class' => 'arrow_down')) . '</h2>';
$right_side .= '<div id="incident-dates">';

if ($incident["cierre"] == "0000-00-00 00:00:00") {
	$close_text = __("Not yet");
} else {
	$close_text = $incident["cierre"];
}

$right_side .= "<table width='97%' style='text-align: center;' id='incidents_dates_square'>";
$right_side .= "<tr>";
$right_side .= "<td>".__("Created on").":</td>";
$right_side .= "<td>".__("Updated on").":</td>";
$right_side .= "<td>".__("Closed on").":</td>";
$right_side .= "</tr>";
$right_side .= "<tr>";
$right_side .= "<td id='created_on' class='mini_calendar'>";

$created_timestamp = strtotime($incident["inicio"]);
$created_on = "<table><tr><th>" . strtoupper(date('M\' y', $created_timestamp)) . "</th></tr>";
$created_on .= "<tr><td class='day'>" . date('d', $created_timestamp) . "</td></tr>";
$created_on .= "<tr><td class='time'>" . print_image('images/cal_clock_grey.png', true) . ' ' . date('H:i:s', $created_timestamp) . "</td></tr></table>";

$right_side .= $created_on . "</td>";
$right_side .= "<td id='updated_on' class='mini_calendar'>";

$updated_timestamp = strtotime($incident["actualizacion"]);
$updated_on = "<table><tr><th>" . strtoupper(date('M\' y', $updated_timestamp)) . "</th></tr>";
$updated_on .= "<tr><td class='day'>" . date('d', $updated_timestamp) . "</td></tr>";
$updated_on .= "<tr><td class='time'>" . print_image('images/cal_clock_orange.png', true) . ' ' . date('H:i:s', $updated_timestamp) . "</td></tr></table>";

$right_side .= $updated_on . "</td>";
$right_side .= "</td>";
$right_side .= "<td id='closed_on' class='mini_calendar'>";

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

$right_side .= $closed_on . "</td>";
$right_side .= "</td>";
$right_side .= "</tr>";
$right_side .= "</table>";

$right_side .= '</div>';
$right_side .= "</div>"; // container

$right_side .= '<div class="incident_container">';
$right_side .= '<h2 id="incident_sla" class="incident_dashboard incident_sla" onclick="toggleDiv (\'incident-sla\')">' . __('SLA information') . '&nbsp;&nbsp;' . print_image('images/arrow_down.png', true, array('class' => 'arrow_down')) . '</h2>';
$right_side .= '<div id="incident-sla">';

if ($incident["sla_disabled"]) {
	$right_side .= '<table width="97%">';
	$right_side .= '<tr>';
	$right_side .= "<td style='text-align: center;'>";
	$right_side .= "<em>".__("SLA disabled")."</em>";
	$right_side .= "</td>";
	$right_side .= "</tr>";
	$right_side .= "</table>";
} else {
	$right_side .= '<table width="97%" style="border-spacing: 10px;">';
	$right_side .= '<tr>';
	$right_side .= "<td>";
	$right_side .= __('SLA history compliance for: '); 
	$right_side .= "</td>";
	$right_side .= "<td style='vertical-align: bottom;'>";

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
	$right_side .= "<td colspan=2 style='text-align: center; width: 50%;'>";
	$right_side .= __('SLA total compliance (%)'). ': ';
	$right_side .= format_numeric (get_sla_compliance_single_id ($id));
	$right_side .= "</td>";
	$right_side .= "</tr>";
	$right_side .= "<tr>";
	$right_side .= "<td id=slaSlicebarField colspan=2 style='text-align: center; padding: 1px 2px 1px 5px;'>";
	$right_side .= graph_sla_slicebar ($id, $period, 155, 15, $ttl);
	$right_side .= "</td>";
	$right_side .= "<td colspan=2 style='text-align: center;' class='pie_frame'>";
	$right_side .= graph_incident_sla_compliance ($id, 155, 170, $ttl);
	$right_side .= "</td>";	
	$right_side .= "<tr>";
	$right_side .= "</table>";
}
$right_side .= "</div>";
$right_side .= "</div>"; // container

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
