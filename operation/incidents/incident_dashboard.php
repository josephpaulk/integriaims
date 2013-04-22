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
echo __("Custom searches");
echo "</h1>";


$custom_searches = get_db_all_rows_in_table ("tcustom_search");

foreach ($custom_searches as $cs) {
	echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_search&saved_searches=".$cs["id"]."'>".$cs["name"]."</a><br>";
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

$left_side = '<h2 class="incident_dashboard" onclick="toggleDiv (\'incident-details\')">'.__('By resolution').'</h2>';
$left_side .= '<div id="incident-details">';
$left_side .= "TODO";
$left_side .= '</div>';

$left_side .= '<h2 class="incident_dashboard" onclick="toggleDiv (\'incident-details\')">'.__('By owner').'</h2>';
$left_side .= '<div id="incident-details">';
$left_side .= "TODO";
$left_side .= '</div>';

/**** DASHBOAR RIGHT SIDE ****/

$right_side = '<h2 class="incident_dashboard" onclick="toggleDiv (\'incident-users\')">'.__('By status').'</h2>';
$right_side .= '<div id="incident-users">';
$right_side .= "TODO";
$right_side .= "</div>";

$right_side .= '<h2 class="incident_dashboard" onclick="toggleDiv (\'incident-users\')">'.__('By type').'</h2>';
$right_side .= '<div id="incident-users">';
$right_side .= "TODO";
$right_side .= "</div>";

$table->data[0][0] = $left_side;
$table->data[0][1] = $right_side;

print_table($table);

?>
