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

$filter = json_decode(safe_output(get_parameter ('filter')), true);

$incidents = filter_incidents ($filter);
if ($incidents === false) {
	if (! $show_stats)
		echo '<tr><td colspan="8">'.__('Nothing was found').'</td></tr>';
	return;
}

echo "<h1>".__('Search statistics')."</h1>";

print_incidents_stats ($incidents);

/* Add a button to generate HTML reports */
echo '<form method="post" target="_blank" action="index.php" style="clear: both">';
foreach ($filter as $key => $value) {
	print_input_hidden ($key, $value);
}
echo '<div style="width:90%; text-align: right;">';
print_input_hidden ('sec2', 'operation/reporting/incidents_html');
print_input_hidden ('clean_output', 1);
print_submit_button (__('HTML report'), 'incident_report', false,
	'class="sub report"');
echo '</div></form>';

?>
