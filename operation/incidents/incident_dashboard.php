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
echo __("Incident overview");
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

$custom = '<div class="incident_container">';
$custom .= '<h2 id="incident_custom_search" class="incident_dashboard incident_custom_search" onclick="toggleDiv (\'incident-custom\')">' . __('Custom search') . '&nbsp;&nbsp;' . print_image('images/arrow_down.png', true, array('class' => 'arrow_down')) . '</h2>';
$custom .= '<div id="incident-custom">';

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

$custom .= "</div>";
$custom .= "</div>"; // container

$table->colspan[0][0] = 2;
$table->data[0][0] = $custom;

$left_side = '<div class="incident_container">';
$left_side .= '<h2 id="incident_search_by_group" class="incident_dashboard incident_search_by_group" onclick="toggleDiv (\'incident-group\')">' . __('Search by group') . '&nbsp;&nbsp;' . print_image('images/arrow_down.png', true, array('class' => 'arrow_down')) . '</h2>';
$left_side .= '<div id="incident-group">';


$groups = get_user_groups();

asort($groups);

$aux_table = "<table>";

// Remove group All for this filter
unset($groups[1]);

$count = 0;
foreach ($groups as $key => $grp) {
	
	if ($count % 2 == 0) {
		$aux_table .= "<tr>";
	}
		
	$incidents = get_incidents(array("id_grupo" => $key));
	
	$aux_table .= "<td>";
	$aux_table .= "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_search&search_status=0&search_first_date=" . $first_start . "&search_id_group=".$key."'>";
	$aux_table .= $grp." (".count($incidents).")";
	$aux_table .= "</a>";
	$aux_table .= "</td>";
		
	if ($count % 2 != 0) {
		$aux_table .= "</tr>";
	}
	
	$count++;
}

$aux_table .= "</table>";

$left_side .= $aux_table;

$left_side .= '</div>';
$left_side .= '</div>'; // container

$left_side .= '<div class="incident_container">';
$left_side .= '<h2 id="incident_search_by_owner" class="incident_dashboard incident_search_by_owner" onclick="toggleDiv (\'incident-owner\')">' . __('Search by owner') . '&nbsp;&nbsp;' . print_image('images/arrow_down.png', true, array('class' => 'arrow_down')) . '</h2>';
$left_side .= '<div id="incident-owner">';

$rows = get_db_all_rows_sql ("SELECT DISTINCT(ti.id_usuario), tu.avatar 
								FROM tincidencia ti, tusuario tu 
								WHERE tu.id_usuario = ti.id_usuario 
								ORDER BY ti.id_usuario ASC");

$aux_table = "<table>";

if (!$rows) {

	$aux_table .="<tr>";
	$aux_table .="<td>";
	$aux_table .="<em>".__("There aren't owners defined");
	$aux_table .="</td>";
	$aux_table .="</tr>";

} else {
	foreach ($rows as $key => $owners) {
	
		if ($key % 4 == 0) {
			$aux_table .= "<tr>";
		}
		
		$incidents = get_incidents(array("id_usuario" => $owners["id_usuario"]));
	
		$aux_table .= "<td>";
		$aux_table .= '<div class="bubble_little">' . print_image('images/avatars/' . $owners["avatar"] . '.png', true) . '</div>';
		$aux_table .= "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_search&search_first_date=" . $first_start . "&search_id_user=".$owners["id_usuario"]."'>";
	
		$long_name = get_db_value_filter ("nombre_real", "tusuario", array("id_usuario" => $owners["id_usuario"]));
	
		$aux_table .= $long_name." (".count($incidents).")";
		$aux_table .= "</a>";
		$aux_table .= "</td>";
		
		if ($key % 4 == 3) {
			$aux_table .= "</tr>";
		}
	}
}

$aux_table .= "</table>";

$left_side .= $aux_table;
$left_side .= '</div>';
$left_side .= '</div>'; // container

$left_side .= '<div class="incident_container">';
$left_side .= '<h2 id="incident_search_by_priority" class="incident_dashboard incident_search_by_priority" onclick="toggleDiv (\'incident-priority\')">' . __('Search by priority') . '&nbsp;&nbsp;' . print_image('images/arrow_down.png', true, array('class' => 'arrow_down')) . '</h2>';
$left_side .= '<div id="incident-priority">';

$rows = get_db_all_rows_sql ("SELECT DISTINCT(prioridad) as priority, count(*) as count FROM tincidencia");

$aux_table = "<table class='search_by_priority'>";

$aux_table .="<tr>";

for ($i = 0; $i<=5; $i++) {
	// Change the priority code to database code
	if($i == 0) {
		$db_priority = 10;
	}
	else {
		$db_priority = $i-1;
	}
	
	$aux_table .= "<td style='background: " . incidents_get_priority_color(array("prioridad" => $i)) . ";'>";
	$aux_table .= "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_search&search_first_date=" . $first_start . "&search_priority=".$db_priority."'>";
		
	$aux_table .= $i;
	$aux_table .= "</a>";
	$aux_table .= "</td>";
}

$aux_table .="</tr>";

$aux_table .= "</table>";

$left_side .= $aux_table;
$left_side .= '</div>';
$left_side .= '</div>'; // container

/**** DASHBOAR RIGHT SIDE ****/

$right_side = '<div class="incident_container">';
$right_side .= '<h2 id="incident_search_by_status" class="incident_dashboard incident_search_by_status" onclick="toggleDiv (\'incident-status\')">' . __('Search by status') . '&nbsp;&nbsp;' . print_image('images/arrow_down.png', true, array('class' => 'arrow_down')) . '</h2>';
$right_side .= '<div id="incident-status">';

$rows = get_db_all_rows_sql ("SELECT id, name FROM tincident_status");

$aux_table = "<table>";

foreach ($rows as $key => $status) {
	
	if ($key % 2 == 0) {
		$aux_table .= "<tr>";
	}
		$incidents = get_incidents(array("estado" => $status["id"]));
		
		$aux_table .= "<td>";
		$aux_table .= "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_search&search_first_date=" . $first_start . "&search_status=".$status["id"]."'>";
		$aux_table .= __($status["name"])." (".count($incidents).")";
		$aux_table .= "</a>";
		$aux_table .= "</td>";
		
	if ($key % 2 != 0) {
		$aux_table .= "</tr>";
	}
}

$aux_table .= "</table>";

$right_side .= $aux_table;
$right_side .= "</div>";
$right_side .= "</div>"; // container

$right_side .= '<div class="incident_container">';
$right_side .= '<h2 id="incident_search_by_type" class="incident_dashboard incident_search_by_type" onclick="toggleDiv (\'incident-type\')">' . __('Search by type') . '&nbsp;&nbsp;' . print_image('images/arrow_down.png', true, array('class' => 'arrow_down')) . '</h2>';
$right_side .= '<div id="incident-type">';

$rows = get_db_all_rows_sql ("SELECT id, name FROM tincident_type ORDER BY name ASC");

$aux_table = "<table>";

if (!$rows) {
	$aux_table .="<tr>";
	$aux_table .="<td>";
	$aux_table .="<em>".__("There aren't incident types defined")."</em>";
	$aux_table .="</td>";
	$aux_table .="</tr>";

} else {
	$count = 0;
	foreach ($rows as $type) {
		if ($count % 2 == 0) {
			$aux_table .= "<tr>";
		}
				
		$incidents = get_incidents(array("id_incident_type" => $type["id"]));
	
		$aux_table .= "<td>";
		$aux_table .= "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_search&search_status=0&search_first_date=" . $first_start . "&search_id_incident_type=".$type["id"]."'>";
		$aux_table .= $type["name"]." (".count($incidents).")";
		$aux_table .= "</a>";
		$aux_table .= "</td>";
		
		if ($count % 2 != 0) {
			$aux_table .= "</tr>";
		}		
		$count++;
	}
}
$aux_table .= "</table>";

$right_side .= $aux_table;
$right_side .= "</div>";
$right_side .= "</div>"; // container

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
