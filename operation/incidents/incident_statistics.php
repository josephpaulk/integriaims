<?php

// Integria IMS - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2011-2011 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
global $config;

check_login ();

echo "<div id='incident-search-content'>";
echo "<h1>".__('Search statistics');
echo "<div id='button-bar-title'>";
echo "<ul>";
echo "<li>";
echo "<a id='search_form_submit' href='#'>".print_image("images/go-previous.png", true, array("title" => __("Back to search")))."</a>";
echo "</li>";
$report_image = print_report_image ("javascript:submit_form()", __("PDF report"), "pdf_report_submit");
if ($report_image) {
	echo "<li>";
	echo $report_image;
	echo "</li>";
}
echo "</ul>";
echo "</div>";
echo "</h1>";
echo "<br>";

$filter['limit'] = 0;
$incidents = filter_incidents ($filter);
unset($filter['limit']);

/* Add a form to carry filter between statistics and search views */
echo '<form id="search_form" method="post" action="index.php?sec=incidents&sec2=operation/incidents/incident_search&option=search" style="clear: both">';
foreach ($filter as $key => $value) {
	print_input_hidden ("search_".$key, $value);
}
print_input_hidden ("offset", get_parameter("offset"));
echo "</form>";

/* Add a form to generate HTML reports */
echo '<form id="html_report_form" method="post" target="_blank" action="index.php" style="clear: both">';
foreach ($filter as $key => $value) {
	print_input_hidden ($key, $value);
}

print_input_hidden ('sec2', 'operation/reporting/incidents_html');
print_input_hidden ('clean_output', 1);
echo "</form>";

/* Add a form to generate HTML reports */
echo '<form id="pdf_report_form" method="post" target="_blank" action="index.php" style="clear: both">';
foreach ($filter as $key => $value) {
	print_input_hidden ($key, $value);
}

print_input_hidden ('sec2', 'operation/reporting/incidents_html');
print_input_hidden ('clean_output', 1);
print_input_hidden ('pdf_output', 1);
echo '</div></form>';


if ($incidents == false) {
	echo __('Nothing was found');
	//return;
} else {
	print_incidents_stats ($incidents);
}

?>
