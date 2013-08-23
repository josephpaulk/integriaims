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

check_login ();

// Get start date of first incident to use it on filters
$first_start = get_db_value_sql ("SELECT UNIX_TIMESTAMP(inicio) FROM tincidencia ORDER BY inicio ASC");

if (!empty($first_start)) {
	$first_start = date ("Y-m-d", $first_start);
}

echo "<h1>";
echo __("Incidents overview");
echo "</h1>";

/* Users affected by the incident */
$table->width = '100%';
$table->class = "none";
$table->size = array ();
$table->size[0] = '50%';
$table->size[1] = '50%';
$table->style = array();
$table->data = array ();
$table->style [0] = "vertical-align: top;";
$table->style [1] = "vertical-align: top";

$custom = '';

$custom_searches = get_db_all_rows_filter ("tcustom_search", array("id_user" => $config["id_user"]));

$counter = 0;
$max_per_file = 5;

if ($custom_searches === false) {
		$custom .= "<table style='margin: 10px auto;'>";
        $custom .= "<tr>";
        $custom .= "<td>";
        $custom .= "<em>".__("There aren't custom search defined for this user")."</em>";
        $custom .= "</td>";
        $custom .= "</tr>";
		$custom .= "</table>";
} else {
	foreach ($custom_searches as $cs) {
		$custom .="<div class='custom_search'>";
		$custom .= "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_search&saved_searches=".$cs["id"]."'>".$cs["name"]."</a><br>";
		$custom .="</div>";
	}
	$custom .= "<div style='clear:both;'></div>";
}

$table->colspan[0][0] = 2;
$table->data[0][0] = print_container('incident_custom_search', __('Custom search'), $custom);

$groups = get_user_groups();

asort($groups);

$search_by_group = "<table>";

// Remove group All for this filter
unset($groups[1]);

$count = 0;
foreach ($groups as $key => $grp) {
	
	if ($count % 2 == 0) {
		$search_by_group .= "<tr>";
	}
		
	$incidents = get_incidents(array("id_grupo" => $key));
	
	$search_by_group .= "<td>";
	$search_by_group .= "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_search&search_status=0&search_first_date=" . $first_start . "&search_id_group=".$key."'>";
	$search_by_group .= $grp." (".count($incidents).")";
	$search_by_group .= "</a>";
	$search_by_group .= "</td>";
		
	if ($count % 2 != 0) {
		$search_by_group .= "</tr>";
	}
	
	$count++;
}

$search_by_group .= "</table>";

$left_side = print_container('incident_search_by_group', __('Search by group'), $search_by_group);

$rows = get_db_all_rows_sql ("SELECT DISTINCT(ti.id_usuario), tu.avatar 
								FROM tincidencia ti, tusuario tu 
								WHERE tu.id_usuario = ti.id_usuario 
								ORDER BY ti.id_usuario ASC");

$search_by_owner = "<table>";

if (!$rows) {

	$search_by_owner .="<tr>";
	$search_by_owner .="<td>";
	$search_by_owner .="<em>".__("There aren't owners defined");
	$search_by_owner .="</td>";
	$search_by_owner .="</tr>";

} else {
	foreach ($rows as $key => $owners) {
	
		if ($key % 4 == 0) {
			$search_by_owner .= "<tr>";
		}
		
		$incidents = get_incidents(array("id_usuario" => $owners["id_usuario"]));
	
		$search_by_owner .= "<td>";
		$search_by_owner .= '<div class="bubble_little">' . print_image('images/avatars/' . $owners["avatar"] . '.png', true) . '</div>';
		$search_by_owner .= "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_search&search_first_date=" . $first_start . "&search_id_user=".$owners["id_usuario"]."'>";
	
		$long_name = get_db_value_filter ("nombre_real", "tusuario", array("id_usuario" => $owners["id_usuario"]));
	
		$search_by_owner .= $long_name." (".count($incidents).")";
		$search_by_owner .= "</a>";
		$search_by_owner .= "</td>";
		
		if ($key % 4 == 3) {
			$search_by_owner .= "</tr>";
		}
	}
}

$search_by_owner .= "</table>";

$left_side .= print_container('incident_search_by_owner', __('Search by owner'), $search_by_owner);

$rows = get_db_all_rows_sql ("SELECT DISTINCT(prioridad) as priority, count(*) as count FROM tincidencia");

$search_by_priority = "<table class='search_by_priority'>";

$search_by_priority .="<tr>";

for ($i = 0; $i<=5; $i++) {
	// Change the priority code to database code
	if($i == 0) {
		$db_priority = 10;
	}
	else {
		$db_priority = $i-1;
	}
	
	$search_by_priority .= "<td style='background: " . incidents_get_priority_color(array("prioridad" => $i)) . ";'>";
	$search_by_priority .= "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_search&search_first_date=" . $first_start . "&search_priority=".$db_priority."'>";
		
	$search_by_priority .= $i;
	$search_by_priority .= "</a>";
	$search_by_priority .= "</td>";
}

$search_by_priority .="</tr>";

$search_by_priority .= "</table>";

$left_side .= print_container('incident_search_by_priority', __('Search by priority'), $search_by_priority);

/**** DASHBOAR RIGHT SIDE ****/

$rows = get_db_all_rows_sql ("SELECT id, name FROM tincident_status");

$search_by_status = "<table>";

foreach ($rows as $key => $status) {
	
	if ($key % 2 == 0) {
		$search_by_status .= "<tr>";
	}
		$incidents = get_incidents(array("estado" => $status["id"]));
		
		$search_by_status .= "<td>";
		$search_by_status .= "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_search&search_first_date=" . $first_start . "&search_status=".$status["id"]."'>";
		$search_by_status .= __($status["name"])." (".count($incidents).")";
		$search_by_status .= "</a>";
		$search_by_status .= "</td>";
		
	if ($key % 2 != 0) {
		$search_by_status .= "</tr>";
	}
}

$search_by_status .= "</table>";

$right_side = print_container('incident_search_by_status', __('Search by status'), $search_by_status);

$rows = get_db_all_rows_sql ("SELECT id, name FROM tincident_type ORDER BY name ASC");

$search_by_type = "<table>";

if (!$rows) {
	$search_by_type .="<tr>";
	$search_by_type .="<td>";
	$search_by_type .="<em>".__("There aren't incident types defined")."</em>";
	$search_by_type .="</td>";
	$search_by_type .="</tr>";

} else {
	$count = 0;
	foreach ($rows as $type) {
		if ($count % 2 == 0) {
			$search_by_type .= "<tr>";
		}
				
		$incidents = get_incidents(array("id_incident_type" => $type["id"]));
	
		$search_by_type .= "<td>";
		$search_by_type .= "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_search&search_status=0&search_first_date=" . $first_start . "&search_id_incident_type=".$type["id"]."'>";
		$search_by_type .= $type["name"]." (".count($incidents).")";
		$search_by_type .= "</a>";
		$search_by_type .= "</td>";
		
		if ($count % 2 != 0) {
			$search_by_type .= "</tr>";
		}		
		$count++;
	}
}
$search_by_type .= "</table>";

$right_side .= print_container('incident_search_by_type', __('Search by type'), $search_by_type);

$table->data[1][0] = $left_side;
$table->data[1][1] = $right_side;

print_table($table);

?>

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
