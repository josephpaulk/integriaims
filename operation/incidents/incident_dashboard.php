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

echo "<h1>";
echo __("Incident overview");
echo "</h1>";

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
$table->style [0] = "vertical-align: top;";
$table->style [1] = "vertical-align: top";

$custom = '<h2 class="incident_dashboard" onclick="toggleDiv (\'incident-custom\')">'.__('Custom search').'</h2>';
$custom .= '<div id="incident-custom">';

$custom_searches = get_db_all_rows_in_table ("tcustom_search");

$custom .= "<table style='margin: 10px auto;'>";

$counter = 0;
$max_per_file = 5;

foreach ($custom_searches as $cs) {
	
	if ($counter == 0) {
		$custom .="<tr>";
	}
	
	$custom .="<td>";
	$custom .= "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_search&saved_searches=".$cs["id"]."'>".$cs["name"]."</a><br>";
	$custom .="</td>";
	
	if ($counter == $max_per_file) {
		$custom .= "</tr>";
		$counter = 0;
	} else {
		$counter++;
	}
}

$custom .= "</table>";

$custom .= "</div>";

$table->colspan[0][0] = 2;
$table->data[0][0] = $custom;


$left_side = '<h2 class="incident_dashboard" onclick="toggleDiv (\'incident-group\')">'.__('Search by group').'</h2>';
$left_side .= '<div id="incident-group">';


$groups = get_user_groups();

$aux_table = "<table>";

foreach ($groups as $key => $grp) {
	
	if ($key != 1) {
		$aux_table .="<tr>";
		
		$incidents = get_incidents(array("id_grupo" => $key));
		
		$aux_table .= "<td>";
		$aux_table .= "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_search&search_status=0&search_id_group=".$key."'>";
		$aux_table .= $grp." (".count($incidents).")";
		$aux_table .= "</a>";
		$aux_table .= "</td>";
		$aux_table .="</tr>";
	}
}

$aux_table .= "</table>";

$left_side .= $aux_table;

$left_side .= '</div>';

$left_side .= '<h2 class="incident_dashboard" onclick="toggleDiv (\'incident-owner\')">'.__('Search by assigned user').'</h2>';
$left_side .= '<div id="incident-owner">';

$rows = get_db_all_rows_sql ("SELECT DISTINCT(id_usuario) FROM tincidencia");

$aux_table = "<table>";

foreach ($rows as $owners) {
	
	if ($key != 1) {
		$aux_table .="<tr>";
		
		$incidents = get_incidents(array("id_usuario" => $owners["id_usuario"]));
		
		$aux_table .= "<td>";
		$aux_table .= "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_search&search_id_user=".$owners["id_usuario"]."'>";
		
		$long_name = get_db_value_filter ("nombre_real", "tusuario", array("id_usuario" => $owners["id_usuario"]));
		
		$aux_table .= $long_name." (".count($incidents).")";
		$aux_table .= "</a>";
		$aux_table .= "</td>";
		$aux_table .="</tr>";
	}
}

$aux_table .= "</table>";

$left_side .= $aux_table;
$left_side .= '</div>';

/**** DASHBOAR RIGHT SIDE ****/

$right_side = '<h2 class="incident_dashboard" onclick="toggleDiv (\'incident-status\')">'.__('Search by status').'</h2>';
$right_side .= '<div id="incident-status">';

$rows = get_db_all_rows_sql ("SELECT id, name FROM tincident_status");

$aux_table = "<table>";

foreach ($rows as $status) {
	
	if ($key != 1) {
		$aux_table .="<tr>";
		
		$incidents = get_incidents(array("estado" => $status["id"]));
		
		$aux_table .= "<td>";
		$aux_table .= "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_search&search_status=".$status["id"]."'>";
		$aux_table .= __($status["name"])." (".count($incidents).")";
		$aux_table .= "</a>";
		$aux_table .= "</td>";
		$aux_table .="</tr>";
	}
}

$aux_table .= "</table>";

$right_side .= $aux_table;
$right_side .= "</div>";

$right_side .= '<h2 class="incident_dashboard" onclick="toggleDiv (\'incident-type\')">'.__('Search by type').'</h2>';
$right_side .= '<div id="incident-type">';

$rows = get_db_all_rows_sql ("SELECT id, name FROM tincident_type");

$aux_table = "<table>";

foreach ($rows as $type) {
	
	if ($key != 1) {
		$aux_table .="<tr>";
		
		$incidents = get_incidents(array("id_incident_type" => $type["id"]));
		
		$aux_table .= "<td>";
		$aux_table .= "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_search&search_status=0&search_id_incident_type=".$type["id"]."'>";
		$aux_table .= $type["name"]." (".count($incidents).")";
		$aux_table .= "</a>";
		$aux_table .= "</td>";
		$aux_table .="</tr>";
	}
}

$aux_table .= "</table>";

$right_side .= $aux_table;
$right_side .= "</div>";

$table->data[1][0] = $left_side;
$table->data[1][1] = $right_side;

print_table($table);

?>
