<?php
// INTEGRIA IMS - the ITIL Management System
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

include_once ("include/functions_graph.php");

// Returns 'date' in the format 'dd/mm/yyyy'
function fix_date ($date, $default='') {
	$date_array = preg_split ('/[\-\s]/', $date);
	if (sizeof($date_array) < 3) {
		return false;
	}

	if ($default != '' && $date_array[0] == '0000') {
		return $default;
	}
		
	return sprintf ('%02d/%02d/%04d', $date_array[2], $date_array[1], $date_array[0]);
}

// Get project tasks
function get_tasks (&$tasks, $project_id, $project_start, $project_end, $parent_id = 0, $depth = 0) {
	global $config;

	$id_user = $config["id_user"];
    $result = mysql_query ('SELECT * FROM ttask 
                            WHERE id_parent_task = ' . $parent_id
                            . ' AND id_project = ' . $project_id);
    if ($result === false) {
    	return;
    }

	$indent = '';
	for ($i = 0; $i < $depth; $i++) {
		$indent .= '>';
	}

    while ($row = mysql_fetch_array ($result)) {
		
		// ACL Check for this task
		// This user is assigned to this task ?	
		if ( user_belong_task ($config["id_user"], $row['id'])){
			$task['id'] = $row['id'];
			$task['name'] = $indent . $row['name'];
			$task['parent'] = $parent_id;
			$task['link'] = 'index.php?sec=projects&sec2=operation/projects/task_detail&id_project=' . $project_id .'&id_task=' . $row['id'] .'&operation=view';
			// start > end
			$task['start'] = fix_date ($row['start'], $project_start);
			$task['end'] = fix_date ($row['end'], $project_end);
			if (date_to_epoch ($task['start']) > date_to_epoch ($task['end'])) {
				$temp = $task['start'];
				$task['start'] = $task['end'];
				$task['end'] = $temp;
			}
			$task['real_start'] = fix_date (get_db_sql ('SELECT MIN(timestamp) FROM tworkunit, tworkunit_task WHERE tworkunit_task.id_workunit = tworkunit.id AND timestamp <> \'0000-00-00 00:00:00\' AND id_task = ' . $row['id']), $task['start']);
			$task['real_end'] = fix_date (get_db_sql ('SELECT MAX(timestamp) FROM tworkunit, tworkunit_task WHERE tworkunit_task.id_workunit = tworkunit.id AND timestamp <> \'0000-00-00 00:00:00\' AND id_task = ' . $row['id']), $task['start']);
			$task['completion'] = $row['completion'];
			array_push ($tasks, $task);
	
			get_tasks (&$tasks, $project_id, $project_start, $project_end, $task['id'], $depth + 1);
		}
    }
}

// Get project milestones
function get_milestones (&$milestones, $project_id, $parent_id = 0, $depth = 0) {
    $result = mysql_query ('SELECT * FROM tmilestone 
                            WHERE id_project = ' . $project_id);
    if ($result === false) {
    	return;
    }
    
    while ($row = mysql_fetch_array ($result)) {
    	$milestone['id'] = $row['id'];
    	$milestone['name'] = $row['name'];
    	$milestone['description'] = $row['description'];
    	$milestone['date'] = fix_date ($row['timestamp']);
    	array_push ($milestones, $milestone);
	}
}
	
// Real start
global $config;

if (!isset($config["base_url"]))
	exit;

// Security checks for this project
	check_login ();

$id_user = $_SESSION['id_usuario'];
$id_project = get_parameter ("id_project", -1);
if ($id_project != -1)
	$project_name = get_db_value ("name", "tproject", "id", $id_project);
else
	$project_name = "";
$clean_output = get_parameter ("clean_output", 0);

if (user_belong_project ($id_user, $id_project) == 0){
	audit_db($id_user, $config["REMOTE_ADDR"], "ACL Violation","Trying to access to task manager of unauthorized project");
	no_permission();
}

echo "<h2>".$project_name." &raquo; ".__('Gantt graph')."</h2>";

$tasks = array ();
$milestones = array ();
$project_start =  fix_date (get_db_value ("start", "tproject", "id", $id_project));
$project_end = fix_date (get_db_value ("end", "tproject", "id", $id_project));

// Get start/end dates for the chart
$from = $project_start;
$to = $project_end;

// Minimum date from project/tasks/workunits
$min_start = fix_date (get_db_sql ('SELECT MIN(start) FROM ttask WHERE start <> \'0000-00-00\' AND id_project = ' . $id_project));

if ($min_start !== false && (date_to_epoch ($min_start) < date_to_epoch ($from) || $from == '00/00/0000')) {
	$from = $min_start;
}

$min_timestamp = fix_date (get_db_sql ('SELECT MIN(timestamp) FROM ttask, tworkunit, tworkunit_task WHERE ttask.id = tworkunit_task.id_task AND tworkunit_task.id_workunit = tworkunit.id AND timestamp <> \'0000-00-00 00:00:00\' AND id_project = ' . $id_project));
if ($min_timestamp !== false && (date_to_epoch ($min_timestamp) < date_to_epoch ($from) || $from == '00/00/0000')) {
	$from = $min_timestamp;
}

// Minimum date from project/tasks/workunits
$max_end = fix_date (get_db_sql ('SELECT MAX(end) FROM ttask WHERE end <> \'0000-00-00\' AND id_project = ' . $id_project));
if ($max_end !== false && date_to_epoch ($max_end) > date_to_epoch ($to)) {
	$to = $max_end;
}

$max_timestamp = fix_date (get_db_sql ('SELECT MAX(timestamp) FROM ttask, tworkunit, tworkunit_task WHERE ttask.id = tworkunit_task.id_task AND tworkunit_task.id_workunit = tworkunit.id AND timestamp <> \'0000-00-00 00:00:00\' AND id_project = ' . $id_project));
if ($max_timestamp !== false && date_to_epoch ($max_timestamp) > date_to_epoch ($to)) {
	$to = $max_timestamp;
}

// Fix undefined start/end dates
if ($from == '00/00/0000') {
	$from = date ('d/m/Y');
}

if ($to == '00/00/0000') {
	$to = date ('d/m/Y');
}

get_tasks (&$tasks, $id_project, $from, $to);
get_milestones (&$milestones, $id_project);

if ($project_start != '00/00/0000') {
	array_push ($milestones, array ('id' => 'start', 'name' => __('Start'), 'date' => $project_start));
}
if ($project_end != '00/00/0000') {
	array_push ($milestones, array ('id' => 'end', 'name' => __('End'), 'date' => $project_end));
}

// Calculate chart width
if ($clean_output) {
	$width = 950;
} else {
	$width = 780;
}

// Calculate chart height
$num_tasks = sizeof ($tasks);
if ($num_tasks > 20) {
	$height = 850;
} else {
	$height = 150 + 35 * $num_tasks;
}

// Print the Gantt chart

print fs_gantt_chart ($project_name, $from, $to, $tasks, $milestones, $width, $height);

//print gantt_graph ($project_name, $from, $to, $tasks, $milestones, $width, $height);

if (!$clean_output) {
	echo "<br><br>";
	echo "<a target='top' href='index.php?sec=projects&sec2=operation/projects/gantt&id_project=$id_project&clean_output=1'>".__('Full screen')."</a>";
}

?>
