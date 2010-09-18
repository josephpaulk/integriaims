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

include_once ("include/functions_reporting.php");

$id_project = (int) get_parameter ('id_project');

if (! $id_project) {// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to task manager without project");
	include ("general/noaccess.php");
	exit;
}

$project = get_db_row ('tproject', 'id', $id_project);

if (! user_belong_project ($config['id_user'], $id_project)) {
	audit_db($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation","Trying to access to task manager of unauthorized project");
	include ("general/noaccess.php");
	exit;
}

$id_task = (int) get_parameter ('id');
$operation = (string) get_parameter ('operation');

if ($operation == 'delete') {
	if (dame_admin ($config['id_user']) || project_manager_check ($id_project)) {
		delete_task ($id_task);
		echo '<h3 class="suc">'.__('Successfully deleted').'</h3>';
		$operation = '';
		task_tracking ($id_task, TASK_DELETED);
	} else {
		no_permission ();
	}
}

if ($operation == 'move') {
	$target_project = get_parameter ("target_project");
	$id_task = get_parameter ("id_task");
	if ((dame_admin($config['id_user'])==1) OR (project_manager_check ($id_project) == 1)){
		$sql = sprintf ('UPDATE ttask
			SET id_project = %d,
			id_parent_task = 0
			WHERE id = %d', $target_project, $id_task);
		process_sql ($sql);
		task_tracking ($id_task, TASK_MOVED);
	} else {
		no_permission ();
	}
}

// MAIN LIST OF TASKS

$search_id_group = (int) get_parameter ('search_id_group');
$search_text = (string) get_parameter ('search_text');

echo '<h2>'.$project['name'].' &raquo; '.__('Task management');
echo "&nbsp;&nbsp;<a title='"._("Report")."'  href='index.php?sec=projects&sec2=operation/projects/task&id_project=$id_project&search_id_group=$search_id_group&search_text=$search_text&clean_output=1'><img src='images/html.png'></a>";
echo '</h2><br>';

$where_clause = ' 1=1 ';
if ($search_text != "")
	$where_clause .= sprintf (' AND name LIKE "%%%s%%" OR description LIKE "%%%s%%"',
		$search_text, $search_text);

if ($search_id_group != 0)
	$where_clause .= sprintf ('( AND id_group = ', $search_id_group);


$table->width = '450px';
$table->class = 'search-table';
$table->style = array ();
$table->style[0] = 'font-weight: bold;';
$table->style[2] = 'font-weight: bold;';
$table->data = array ();
$table->data[0][0] = __('Search');
$table->data[0][1] = print_input_text ("search_text", $search_text, "", 25, 100, true);
$table->data[0][2] = __('Group');
$table->data[0][3] = print_select (get_user_groups (),
	'search_id_group', $search_id_group, '', __('Any'), '0', true);
$table->data[0][4] = print_submit_button (__('Search'), "search_btn", false, 'class="sub search"', true);

echo '<form method="post">';
print_table ($table);
echo '</form>';

unset ($table);

$table->width = '95%';
$table->class = 'listing';
$table->data = array ();
$table->style = array ();
$table->style[0] = 'font-weight: bold';
$table->head = array ();
$table->head[0] = __('Pri');
$table->head[1] = __('Name');
$table->head[2] = __('Progress');
$table->head[3] = __('Estimation');
$table->head[4] = __('Time used');
$table->head[5] = __('Cost');
$table->head[6] = __('People');
$table->head[7] = __('Start');
$table->head[8] = __('End');
$table->align = array ();
$table->align[1] = 'left';
$table->align[2] = 'center';
$table->align[3] = 'center';
$table->align[4] = 'center';
$table->align[9] = 'center';

$table->style[7] = "font-size: 9px";
$table->style[8] = "font-size: 9px";


echo project_activity_graph ($id_project);

// Show headers
echo "<table width='90%' class='listing'>";
echo "<tr>";
$color = 1;

show_task_tree ($table, $id_project, 0, 0, $where_clause);

print_table ($table);

/*
if (give_acl ($config['id_user'], 0, 'PW')) {
	echo '<form method="post" action="index.php?sec=projects&sec2=operation/projects/task_detail">';
	echo '<div class="button" style="width: '.$table->width.'">';
	print_input_hidden ('id_project', $id_project);
	print_input_hidden ('operation', 'create');
	print_submit_button (__('New task'), 'crt_btn', false, 'class="sub next"');
	echo '</div>';
	echo '</form>';
}*/


function show_task_row ($table, $id_project, $task, $level) {
	global $config;
	
	$data = array ();

	// Priority
        $data[0] = print_priority_flag_image ($task['priority'], true);
	
	// Task  name
	$data[1] = '';
	for ($i = 0; $i < $level; $i++)
		$data[1] .= '<img src="images/copy.png" />';
	
	$data[1] .= '<a href="index.php?sec=projects&sec2=operation/projects/task_detail&id_project='.
		$id_project.'&id_task='.$task['id'].'&operation=view">'.
		$task['name'].'</a>';

	
	// Completion
	$data[2] = '<img src="include/functions_graph.php?type=progress&width=70&height=20&percent='.$task["completion"].'">';
	
	// Estimation
	$imghelp = "Estimated hours = ".$task["hours"];
	$taskhours = get_task_workunit_hours ($task["id"]);
	$imghelp .= ", Worked hours = $taskhours";
	$a = round ($task["hours"]);
	$b = round ($taskhours);
	$max = max($a, $b);
	if ($a > 0)
		$data[3] = '<img src="include/functions_graph.php?type=histogram&width=60&mode=2&height=18&a='.$a.'&b='.$b.'&&max='.$max.'" title="'.$imghelp.'">';
	else
		$data[3] = '--';

	// Time used
	$timeuser = get_task_workunit_hours ( $task["id"]);
	$data[4] = $timeuser ? $timeuser : '--';
	
	$wu_incidents = get_incident_task_workunit_hours ($task["id"]);

	
	if ($wu_incidents > 0)
	$data[4] .= " (+ $wu_incidents) ";

	// Costs (client / total)
	$costdata = format_numeric (task_workunit_cost ($task["id"], 1));
	$data[5] = $costdata ? $costdata.' '.$config['currency'] : '--';

	// People
	$data[6] = combo_users_task ($task['id'], 1, true);
	$data[6] .= ' ';
	$data[6] .= get_db_value ('COUNT(DISTINCT(id_user))', 'trole_people_task', 'id_task', $task['id']);

	if ($task["start"] == $task["end"]){
		$data[7] = date ('Y-m-d', strtotime ($task['start']));
		$data[8] = __('Recurrence').': '.get_periodicity ($task['periodicity']);
	} else {
		// Start
		$start = strtotime ($task['start']);
		$end = strtotime ($task['end']);
		$now = time ();
		
		$data[7] = date ('Y-m-d', $start);
		
		if ($task['completion'] == 100) {
			$data[8] = '<span style="color: green">';
		} else {
			if ($now > $end)
				$data[8] = '<span style="color: red">';
			else
				$data[8] = '<span>';
		}
		$data[8] .= date ('Y-m-d', $end);
		$data[8] .= '</span>';
	}

	// Delete
	if (give_acl ($config['id_user'], 0, 'PM')) {
		$table->head[9] = __('Delete');
		$data[9] = '<a href="index.php?sec=projects&sec2=operation/projects/task&operation=delete&id_project='.$id_project.'&id='.$task["id"].'"
			onClick="if (!confirm(\''.__('Are you sure?').'\')) return false;">
			<img src="images/cross.png" /></a>';
	}
	array_push ($table->data, $data);
}

function show_task_tree (&$table, $id_project, $level, $id_parent_task, $where_clause) {
	global $config;
	
	// Simple query, needs to implement group control and ACL checking
	$sql = sprintf ('SELECT * FROM ttask
		WHERE %s
		AND id_project = %d
		AND id_parent_task = %d
		ORDER BY name',
		$where_clause, $id_project, $id_parent_task);
	$tasks = get_db_all_rows_sql ($sql);
	if ($tasks === false)
		return;
	foreach ($tasks as $task) {
		if (user_belong_task ($config['id_user'], $task['id']))
			show_task_row ($table, $id_project, $task, $level);
		show_task_tree ($table, $id_project, $level + 1, $task['id'], $where_clause);
	}
}
?>
<script language="JavaScript" src="include/FusionCharts/FusionCharts.js"></script>
