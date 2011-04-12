<?php
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008-2010 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

include_once ("functions_calendar.php");
include_once ("graphs/fgraph.php");

function incident_activity_graph ($id_incident){
	global $config;

    $incident = get_db_row ("tincidencia", "id_incidencia", $id_incident);

    $start_unixdate = strtotime ($incident["inicio"]);
    $end_unixdate = strtotime ("now");
    $period = $end_unixdate - $start_unixdate;
    $resolution = 10;
    
	$interval = (int) ($period / $resolution);

	echo __("Each bar is"). " ". human_time_description_raw($interval);

	$data = get_db_all_rows_sql ("SELECT * FROM tincident_track WHERE id_incident = $id_incident ORDER BY timestamp ASC");

	if ($data === false) {
		$data = array ();
	}

   	$min_necessary = 1;

	// Check available data
	if (count ($data) < $min_necessary) {
		return;
	}

	// Set initial conditions
	$chart = array();
	$names = array();
	$chart2 = array();

	// Calculate chart data
	for ($i = 0; $i < $resolution; $i++) {
		$timestamp = $start_unixdate + ($interval * $i);
		$total = 0;
		$j = 0;

		while (isset ($data[$j])){
            $dftime = strtotime($data[$j]['timestamp']);

			if ($dftime >= $timestamp && $dftime < ($timestamp + $interval)) {
				$total++;
			}
			$j++;
		} 

    	$time_format = "y-m-d H:i";
        $timestamp_human = clean_flash_string (date($time_format, $timestamp));
		$chart2[$timestamp_human] = $total;
   	}

	echo vbar_graph ($config['flash_charts'], $chart2, 650, 300);
}

function project_activity_graph ($id_project){
	global $config;

    $incident = get_db_row ("tproject", "id", $id_project);

    $start_unixdate = strtotime ($incident["start"]);
    $end_unixdate = strtotime ("now");
    $period = $end_unixdate - $start_unixdate;
    $resolution = 10;
    
	$interval = (int) ($period / $resolution);

	echo __("Each bar is"). " ". human_time_description_raw($interval);

	$data = get_db_all_rows_sql ("SELECT tworkunit.duration as duration, 
            tworkunit.timestamp as timestamp  FROM tworkunit, tworkunit_task, ttask 
			WHERE tworkunit_task.id_task = ttask.id
			AND ttask.id_project = $id_project
			AND tworkunit_task.id_workunit = tworkunit.id
ORDER BY timestamp ASC");

	if ($data === false) {
		$data = array ();
	}

   	$min_necessary = 1;

	// Check available data
	if (count ($data) < $min_necessary) {
		return;
	}

	// Set initial conditions
	$chart = array();
	$names = array();
	$chart2 = array();

	// Calculate chart data
	for ($i = 0; $i < $resolution; $i++) {
		$timestamp = $start_unixdate + ($interval * $i);
		$total = 0;
		$j = 0;

		while (isset ($data[$j])){
            $dftime = strtotime($data[$j]['timestamp']);

			if ($dftime >= $timestamp && $dftime < ($timestamp + $interval)) {
				$total += ($data[$j]['duration']);
			}
			$j++;
		} 

    	$time_format = "M d H:i";
        $timestamp_human = clean_flash_string (date($time_format, $timestamp));
		$chart2[$timestamp_human]['graph'] = $total;
   	}
   	
   	$colors['graph']['color'] = "#2179B1";
   	$colors['graph']['border'] = "#000";
   	$colors['graph']['alpha'] = 100;

	echo vbar_graph ($config['flash_charts'], $chart2, 650, 300, $colors);
}

// TODO: Move to functions_graph.php
function task_activity_graph ($id_task){
	global $config;

    $task = get_db_row ("ttask", "id", $id_task);

    $start_unixdate = strtotime ($task["start"]);
    $end_unixdate = strtotime ("now");
    $period = $end_unixdate - $start_unixdate;
    $resolution = 10;
    
	$interval = (int) ($period / $resolution);

	echo __("Each bar is"). " ". human_time_description_raw($interval);

	$data = get_db_all_rows_sql ("SELECT tworkunit.duration as duration, 
            tworkunit.timestamp as timestamp  FROM tworkunit, tworkunit_task, ttask 
			WHERE tworkunit_task.id_task = $id_task
			AND tworkunit_task.id_workunit = tworkunit.id GROUP BY tworkunit.id  ORDER BY timestamp ASC");


	if ($data === false) {
		$data = array ();
	}

   	$min_necessary = 1;

	// Check available data
	if (count ($data) < $min_necessary) {
		return;
	}

	// Set initial conditions
	$chart = array();
	$names = array();
	$chart2 = array();

	// Calculate chart data
	for ($i = 0; $i < $resolution; $i++) {
		$timestamp = $start_unixdate + ($interval * $i);
		$total = 0;
		$j = 0;

		while (isset ($data[$j])){
            $dftime = strtotime($data[$j]['timestamp']);

			if ($dftime >= $timestamp && $dftime < ($timestamp + $interval)) {
				$total += ($data[$j]['duration']);
			}
			$j++;
		} 

    	$time_format = "M d";
        $timestamp_human = clean_flash_string (date($time_format, $timestamp));
		$chart2[$timestamp_human] = $total;
   	}
   	
   	$colors['1day']['color'] = "#2179B1";
   	$colors['1day']['border'] = "#000";
   	$colors['1day']['alpha'] = 100;

	foreach($chart2 as $key => $ch) { 
		$chart3[$key]['1day'] = $ch;
	}
	
	$legend = array();
		
	$xaxisname = __('Days');
	$yaxisname = __('Hours in project');
	
	echo vbar_graph ($config['flash_charts'], $chart3, 650, 300, $colors, $legend, $xaxisname, $yaxisname);
}


?>
