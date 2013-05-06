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
echo "<li>";
echo "<a id='html_report_submit' href='#'>".print_image("images/html.png", true, array("title" => __("HTML report")))."</a>";
echo "</li>";
echo "<li>";
echo "<a id='pdf_report_submit' href='#' onClick='form.submit();'>".print_image("images/page_white_acrobat.png", true, array("title" => __("PDF report")))."</a>";
echo "</li>";
echo "</ul>";
echo "</div>";
echo "</h1>";

$incidents = filter_incidents ($filter);

if ($incidents == false) {
	echo __('Nothing was found');
	return;
}

/* Add a form to carry filter between statistics and search views */
echo '<form id="search_form" method="post" action="index.php?sec=incidents&sec2=operation/incidents/incident_search&option=search" style="clear: both">';
foreach ($filter as $key => $value) {
	print_input_hidden ("search_".$key, $value);
}
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

print_incidents_stats ($incidents);

?>

<script>
//Configure some actions to send forms
$(document).ready(function () {
	$("#search_form_submit").click(function (event) {
		event.preventDefault();
		$("#search_form").submit();
	});
	
	$("#html_report_submit").click(function (event) {
		event.preventDefault();
		$("#html_report_form").submit();
	});
	
	$("#pdf_report_submit").click(function (event) {
		event.preventDefault();
		$("#pdf_report_form").submit();
	});
});
</script>
