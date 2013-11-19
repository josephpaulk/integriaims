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

check_login ();

include_once ("include/functions_projects.php");
include_once ("include/functions_tasks.php");
include_once ("include/functions_graph.php");

$create_mode = 0;
$name = "";
$description = "";
$end_date = "";
$start_date = "";
$id_project = -1; // Create mode by default
$result_output = "";
$id_project_group = 0;

$action = (string) get_parameter ('action');
$id_project = (int) get_parameter ('id_project');

$create_project = (bool) get_parameter ('create_project');


$graph_ttl = 1;

if ($pdf_output) {
	$graph_ttl = 2;
}

$section_access = get_project_access ($config['id_user']);
if ($id_project) {
	$project_access = get_project_access ($config['id_user'], $id_project);
}

// ACL - To access to this section, the required permission is PR
if (!$section_access['read']) {
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to project detail section");
	no_permission();
}
// ACL - If creating, the required permission is PW
if ($create_project && !$section_access['write']) {
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create a project");
	no_permission();
}
// ACL - To view an existing project, belong to it is required
if ($id_project && !$project_access['read']) {
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to view a project");
	no_permission();
}

// Edition / View mode
if ($id_project) {
	$project = get_db_row ('tproject', 'id', $id_project);
	
	$name = $project["name"];
	$description = $project["description"];
	$start_date = $project["start"];
	$end_date = $project["end"];
	$owner = $project["id_owner"];
	$id_project_group = $project["id_project_group"];
} 
	
// Main project table


echo "<h1>".__('Project report')." &raquo; " . get_db_value ("name", "tproject", "id", $id_project);

if (!$clean_output) {
	echo "<div id='button-bar-title'>";
	echo "<ul>";
	echo "<li>";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/project_detail&id_project=$id_project'>" .
		print_image ("images/go-previous.png", true, array("title" => __("Back to project editor"))) .
		"</a>";
	echo "</li>";
	$report_image = print_report_image ("index.php?sec=projects&sec2=operation/projects/project_report&id_project=$id_project", __("PDF report"));
	if ($report_image) {
		echo "<li>";
		echo $report_image;
		echo "</li>";
	}
	echo "</ul>";
	echo "</div>";
}
echo "</h1>";

// Right/Left Tables
$table->width = '100%';
$table->class = "none";
$table->size = array ();
$table->size[0] = '50%';
$table->size[1] = '50%';
$table->style = array();
$table->data = array ();
$table->style [0] = "vertical-align: top;";
$table->style [1] = "vertical-align: top";

// Project info
$project_info = '<table class="search-table-button" style="margin-top: 0px;">';

// Name
$project_info .= '<tr><td class="datos" colspan=3><b>'.__('Name').' </b><br>';
$project_info .= $name;

$project_info .= '<td colspan=1>';
//Only show project progress if there is a project created
if ($id_project) {
	$project_info .= '<b>'.__('Current progress').' </b><br>';
	$project_info .= '<span style="vertical-align:bottom">';
	$completion =  format_numeric(calculate_project_progress ($id_project));
	$project_info .= progress_bar($completion, 90, 20, $graph_ttl);
	$project_info .= "</span>";
}
$project_info .= "</td>";
$project_info .= "</tr>";

// start and end date
$project_info .= '<tr><td width="25%"><b>'.__('Start').' </b><br>';
$project_info .= $start_date;

$project_info .= '<td width="25%"><b>'.__('End').' </b><br>';
$project_info .= $end_date;

$id_owner = get_db_value ( 'id_owner', 'tproject', 'id', $id_project);
$project_info .= '<td width="25%">';
$project_info .= "<b>".__('Project manager')." </b><br>";
$project_info .= get_db_value ("nombre_real", "tusuario", "id_usuario", $owner);

$project_info .= '<td width="25%"><b>';
$project_info .= __('Project group') . "</b><br>";

$project_info .= get_db_value ("name", "tproject_group", "id", $id_project_group);

// Description
$project_info .= "<tr><td style='text-align: left;' colspan=4><b>".__("Description")."</b><br>";
$project_info .= $description;
$project_info .= "</td></tr>";

$project_info .= "</table>";

echo print_container('project_info_report', __('Project info'), $project_info, 'no', true, true, "container_simple_title", "container_simple_div");

if ($id_project) {
	// Project activity graph
	$project_activity = project_activity_graph ($id_project, 650, 150, true, $graph_ttl, 50, true);
	if ($project_activity) {
		$project_activity = '<div class="graph_frame">' . $project_activity . '</div>';
		echo print_container('project_activity_report', __('Project activity'), $project_activity, 'no', true, true, "container_simple_title", "container_simple_div");
	}
	// Calculation
	$people_inv = get_db_sql ("SELECT COUNT(DISTINCT id_user) FROM trole_people_task, ttask WHERE ttask.id_project=$id_project AND ttask.id = trole_people_task.id_task;");
	$total_hr = get_project_workunit_hours ($id_project);
	$total_planned = get_planned_project_workunit_hours($id_project);
	$total_planned = get_planned_project_workunit_hours($id_project);

	$expected_length = get_db_sql ("SELECT SUM(hours) FROM ttask WHERE id_project = $id_project");
	$pr_hour = get_project_workunit_hours ($id_project, 1);
    $deviation = format_numeric(($pr_hour-$expected_length)/$config["hours_perday"]);
	$total = project_workunit_cost ($id_project, 1);
    $real = project_workunit_cost ($id_project, 0);

	$real = $real + get_incident_project_workunit_cost ($id_project);

	// Labour
	$labour = "<table class='advanced_details_table alternate'>";
	$labour .= "<tr>";
	$labour .= '<td><b>'.__('Total people involved').' </b>';
	$labour .= "</td><td>";
	$labour .= $people_inv;
	$labour .= "</td></tr>";

	$labour .= "<tr>";
	$labour .= '<td><b>'.__('Total workunit (hr)').' </b>';
	$labour .= "</td><td>";
	$labour .= $total_hr . " (".format_numeric ($total_hr/$config["hours_perday"]). " ".__("days"). ")";
	$labour .= "</td></tr>";

	$labour .= "<tr>";
	$labour .= '<td><b>'.__('Planned workunit (hr)').' </b>';
	$labour .= "</td><td>";
	$labour .= $total_planned . " (".format_numeric ($total_planned/$config["hours_perday"]). " ". __("days"). ")";
	$labour .= "</td></tr>";
	
	$labour .= "<tr>";
	$labour .= '<td><b>'.__('Total payable workunit (hr)').' </b>';
	$labour .= "</td><td>";
	if ($pr_hour > 0)
		$labour .= $pr_hour;
	else
		$labour .= __("N/A");
	$labour .= "</td></tr>";
	
	$labour .= "<tr>";
	$labour .= '<td><b>'.__('Project length deviation (days)').' </b>';
	$labour .= "</td><td>";
	$labour .= abs($deviation/8). " ".__('Days');
	$labour .= "</td></tr>";
	$labour .= "</table>";
	
	$left_side .= print_container('project_labour', __('Labour'), $labour);
	
	// People involved
	//Get users with tasks
	$sql = sprintf("SELECT DISTINCT id_user FROM trole_people_task, ttask WHERE ttask.id_project= %d AND ttask.id = trole_people_task.id_task", $id_project);
	
	$users_aux = get_db_all_rows_sql($sql);
	
	if(empty($users_aux)) {
		$users_aux = array();
	}
	
	foreach ($users_aux as $ua) {
		$users_involved[] = $ua['id_user'];
	}
	
	//Delete duplicated items
	if (empty($users_involved)) {
		$users_involved = array();
	}
	else {
		$users_involved = array_unique($users_involved);
	}
	
	$people_involved = "<div style='padding-bottom: 20px;'>";
	foreach ($users_involved as $u) {
		$avatar = get_db_value ("avatar", "tusuario", "id_usuario", $u);
		if ($avatar != "") {
			$people_involved .= "<a href='index.php?sec=users&sec2=enterprise/godmode/usuarios/role_user_global&id_user=".$u."'>";
			$people_involved .= "<img src='images/avatars/".$avatar.".png' width=40 height=40 title='".$u."'/>";
			$people_involved .= "</a>";
		}
	}
	$people_involved .= "</div>";
	
	// Task distribution
	$task_distribution = '<div class="pie_frame">' . graph_workunit_project (350, 150, $id_project, $graph_ttl) . '</div>';
	
	// Budget
	$budget = "<table class='advanced_details_table alternate'>";
	$budget .= "<tr>";
	$budget .= '<td><b>'.__('Project profitability').' </b>';
	$budget .= "</td><td>";
	if ($real > 0) {
		$budget .=  format_numeric(($total/$real)*100) . " %" ;
	} else 
		$budget .= __("N/A");
	$budget .= "</td></tr>";

	$budget .= "<tr>";
	$budget .= '<td><b>'.__('Deviation').' </b>';
	$budget .= "</td><td>";
	$deviation_percent = calculate_project_deviation ($id_project);
	$budget .= $deviation_percent ."%";
	$budget .= "</td></tr>";

	$budget .= "<tr>";
	$budget .= '<td><b>'.__('Project costs').' </b>';
	$budget .= "</td><td>";
	// Costs (client / total)
	$real = project_workunit_cost ($id_project, 0);
	$external = project_cost_invoices ($id_project);
	$total_project_costs = $external + $real;

	$budget .= format_numeric( $total_project_costs) ." ". $config["currency"];
	if ($external > 0)
		$budget .= "<span title='External costs to the project'> ($external)</span>";	
	$budget .= "</td></tr>";
	
	$budget .= "<tr>";
	$budget .= '<td><b>'.__('Charged to customer').' </b>';
	$budget .= "</td><td>";
	$budget .= format_numeric($total) . " ". $config["currency"];
	$budget .= "</td></tr>";
	
	$budget .= "<tr>";
	$budget .= '<td><b>'.__('Average Cost per Hour').' </b>';
	$budget .= "</td><td>";
	if ($total_hr > 0)
		$budget .= format_numeric ($total_project_costs / $total_hr) . " " . $config["currency"];
	else
		$budget .= __("N/A");
	$budget .= "</td></tr>";
	$budget .= "</table>";
	
	// Workload distribution
	$workload_distribution = '<div class="pie_frame">' . graph_workunit_project_user_single (350, 150, $id_project, $graph_ttl) . '</div>';

	//Print containers
	echo print_container('project_labour_report', __('Labour'), $labour, 'no', true, true, "container_simple_title", "container_simple_div");
	echo print_container('project_budget_report', __('Budget'), $budget, 'no', true, true, "container_simple_title", "container_simple_div");
	echo print_container('project_involved_people_report', __('People involved'), $people_involved, 'no', true, true, "container_simple_title", "container_simple_div");
	echo "<br><br><br><br><br><br><br><br><br><br><br><br><br>";
	echo print_container('project_task_distribution_report', __('Task distribution'), $task_distribution, 'no', true, true, "container_simple_title", "container_simple_div");
	echo print_container('project_workload_distribution_report', __('Workload distribution'), $workload_distribution, 'no', true, true, "container_simple_title", "container_simple_div");
	
}

?>