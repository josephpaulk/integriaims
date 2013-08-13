<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// Load global vars

global $config;

include_once ("include/functions_projects.php");
include_once ("include/functions_graph.php");

check_login ();

$id_project = (int) get_parameter ('id_project');
$id_user = $config["id_user"];

$start_date = get_parameter('start_date');
$end_date = get_parameter('end_date');

// ACL
$project_access = get_project_access ($id_user, $id_project);
if (! $project_access["read"]) {
	// Doesn't have access to this page
	audit_db ($id_user, $config["REMOTE_ADDR"], "ACL Violation",
		"Trying to access to project graph page");
	no_permission ();
}

if ($id_project) {
	echo "<h3>" . __('Time graph') . "</h3>";
	
	echo "<form action='index.php?sec=projects&sec2=operation/projects/project_timegraph&id_project=" . $id_project . "' method='post'>";
	
	echo '<table class="project_overview" border=0>';
	echo '<tr>';
	echo '<td width="25%"><b>'.__('Start').' </b>';
	print_help_tip(__('Empty date is all range time of project'));
	echo '<br>';
	print_input_text ('start_date', $start_date, '', 10, 20);
	
	echo '<td width="25%"><b>'.__('End').' </b>';
	print_help_tip(__('Empty date is all range time of project'));
	echo '<br>';
	print_input_text ('end_date', $end_date, '', 10, 20);
	echo '</tr>';
	echo "</table>";
	
	echo '<div style="width:800px;" class="button">';
	print_input_hidden ('id_project', $id_project);
	print_input_hidden ('action', 'update');
	print_submit_button (__('Update'), 'upd_btn', false, 'class="sub upd"');
	echo '</div>';
	
	echo "</form>";
	?>
	<script type="text/javascript">
		add_ranged_datepicker ("#text-start_date", "#text-end_date", null);
	</script>
	<?php
	
	echo "<div id='time_graph'></div>";
	
	if (empty($start_date)) {
		$start_date = false;
	}
	
	if (empty($end_date)) {
		$end_date = false;
	}
	
	print_project_timegraph($id_project, $start_date, $end_date);
}
