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


include_once ('include/functions_fsgraph.php');

// Returns 'date' in the format 'dd/mm/yyyy'
function fix_date ($date, $default='') {
	$date = substr($date,0,12);
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
function get_tasks (&$tasks, $id_user, $start, $end, $not_finished, $not_recurrent) {
	global $config;

	$sql = "SELECT r.name as project_name, t.completion, t.id_project, 
	t.id, t.name, t.start, t.end 
	FROM tproject as r, ttask as t, trole_people_task as p 
	WHERE p.id_task = t.id AND p.id_user = '$id_user' AND (
	(t.start > '$start' AND t.end < '$end') OR (t.start < '$start' AND t.end > '$start') OR (t.end > '$end' AND t.start < '$end')) ";

	if ($not_recurrent == 0)
		$sql .= " AND periodicity = 'none' ";

	$sql .= " AND t.id_project = r.id AND r.disabled = 0 ";
	
	if ($not_finished == 0)
		$sql .= " AND t.completion < 100 ";

	$sql .= " GROUP BY p.id_task, t.id";

	$rows = get_db_all_rows_sql ($sql);

    if ($rows === false) {
    	return;
    }
	
	$start_formatted = fix_date ($start);
	$end_formatted = fix_date ($end);

	foreach ($rows as $task){
			$task['parent'] = 0;
			$task['link'] = 'index.php?sec=projects&sec2=operation/projects/task_detail&id_project=' . $task['id_project'].'&id_task=' . $task['id'] .'&operation=view';
			// start > end
			$task['start'] = fix_date ($task['start'], $start_formatted);
			$task['end'] = fix_date ($task['end'], $end_formatted);

			if (date_to_epoch ($task['start']) < date_to_epoch ($start_formatted))
				$task['start']  = $start_formatted;

			if (date_to_epoch ($task['end']) > date_to_epoch ($end_formatted))
				$task['end']  = $end_formatted;

			if (date_to_epoch ($task['start']) > date_to_epoch ($task['end'])) {
				$temp = $task['start'];
				$task['start'] = $task['end'];
				$task['end'] = $temp;
			}

			$task['real_start'] = fix_date (get_db_sql ("SELECT MIN(timestamp) FROM tworkunit, tworkunit_task WHERE tworkunit.id_user = '$id_user' AND tworkunit_task.id_workunit = tworkunit.id AND ( (timestamp > '$start' AND timestamp < '$end') OR (timestamp < '$start' AND timestamp > '$start') OR (timestamp > '$end' AND timestamp < '$end')) 
			AND id_task = " . $task['id']), $task['start']);

			if ($task['real_start'] != '')			
				if (date_to_epoch ($start_formatted) > date_to_epoch($task['real_start']))
					$task['real_start'] = $start_formatted;


			$task['real_end'] = fix_date (get_db_sql ("SELECT MAX(timestamp) FROM tworkunit, tworkunit_task WHERE tworkunit.id_user = '$id_user' AND tworkunit_task.id_workunit = tworkunit.id AND ( (timestamp > '$start' AND timestamp < '$end') OR (timestamp < '$start' AND timestamp > '$start') OR (timestamp > '$end' AND timestamp < '$end')) 
			AND id_task = " . $task['id']), $task['start']);

			if ($task["real_end"] != '')
				if (date_to_epoch ($end_formatted) < date_to_epoch($task['real_end']))
					$task['real_end'] = $end_formatted;

			array_push ($tasks, $task);
	}
}

//get_milestones (&$milestones, $id_user, $from, $to);
// Get project milestones
function get_milestones (&$milestones, $id_user, $from, $to) {
    $milestones_row = get_db_all_rows_sql ("SELECT pro.name, m.name, m.id, m.description, m.timestamp FROM tmilestone as m, 
	trole_people_project as p, tproject as pro
	WHERE m.id_project = p.id_project AND 
	pro.id = m.id_project AND 
	p.id_user = '$id_user' AND 
	timestamp > '$from' AND timestamp < '$to'
	GROUP BY p.id_project, m.id ;");

    if ($milestones_row === false) {
    	return;
    }
    
	foreach ($milestones_row as $row){
    	$milestone['id'] = $row['id'];
    	$milestone['name'] = clean_flash_string ($row[0]. " / ".$row[1]);
    	$milestone['description'] = $row['description'];
    	$milestone['date'] = fix_date ($row['timestamp']);
    	array_push ($milestones, $milestone);
	}
}
	
// Real start
global $config;

// Security checks for this project
	check_login ();

$clean_output = get_parameter ("clean_output", 0);

$now = date ('Y-m-d');
$start_date = get_parameter ("start_date", date ('Y-m-d', strtotime ("$now - 1 months")));
$end_date = get_parameter ("end_date", date ('Y-m-d', strtotime ("$now + 1 months")));
$user_id = get_parameter ('user_id', $config["id_user"]);
$not_finished = get_parameter ("not_finished", 0);
$not_recurrent = get_parameter ("not_recurrent", 0);

$total_time = 0;
$total_global = 0;
$incident_time = 0;

echo "<h3>";
echo __("Full graph report for user");
if ($user_id != "") {
	echo " &raquo; ";
	$realname = dame_nombre_real ($user_id);
	echo $realname;
}

// link full screen
echo "&nbsp;&nbsp;<a title='Full screen' href='index.php?sec=users&sec2=operation/user_report/report_full_graph&user_id=$user_id&end_date=$end_date&start_date=$start_date&clean_output=1'>";
echo "<img src='images/html.png'>";
echo "</a>";

echo  "</h3>";

echo "<form method='post' action='index.php?sec=users&sec2=operation/user_report/report_full_graph'>";
echo "<table class='blank' style='margin-left: 10px' width='90%'>";
echo "<tr><td>";
echo __("Username") ."<br>";
combo_user_visible_for_me ($user_id, 'user_id', 0, 'PR');
echo "</td><td>";
echo __("Begin date")."<br>";
print_input_text ('start_date', $start_date, '', 10, 20);	
echo "</td><td>";
echo __("End date")."<br>";;
print_input_text ('end_date', $end_date, '', 10, 20);

echo "</td><td>";
echo __("Show finished")."<br>";;
print_checkbox ('not_finished', 1, $not_finished, false, false);

echo "</td><td>";
echo __("Show recurrent")."<br>";;
print_checkbox ('not_recurrent', 1, $not_recurrent, false, false);

echo "</td><td valign='bottom'>";
print_submit_button (__('Show'), 'show_btn', false, 'class="next sub"');
echo "</form>";
echo "</table>";

// Build of graph


$tasks = array ();
$milestones = array ();
$project_start =  fix_date ($start_date);
$project_end = fix_date ($end_date);
$project_name = __("Tasks assigned to")." ".$realname;

get_tasks (&$tasks, $user_id, $start_date, $end_date, $not_finished, $not_recurrent);
get_milestones (&$milestones, $user_id, $start_date, $end_date);

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
print fs_gantt_chart ($project_name, $project_start, $project_end, $tasks, $milestones, $width, $height);

if (!$clean_output) {
	echo "<br><br>";
	echo "<a target='top' href='index.php?sec=users&sec2=operation/user_report/report_full_graph&start_date=$start_date&end_date=$end_date&id_user=$user_id&clean_output=1'>".__('Full screen')."</a>";
}

?>

<script language="JavaScript" src="include/FusionCharts/FusionCharts.js"></script>
