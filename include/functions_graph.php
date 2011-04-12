<?PHP


// INTEGRIA IMS v2.0
// http://www.integriaims.com
// ===========================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2008 Esteban Sanchez, estebans@artica.es
// Copyright (c) 2007-2011 Artica, info@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License
// (LGPL) as published by the Free Software Foundation; version 2

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.

// If is called from index
if (file_exists("include/config.php")) {
	include_once ("include/config.php");
	include_once("include/graphs/fgraph.php");
	include_once ("include/functions_calendar.php");
} // If is called through url
elseif (file_exists("config.php")) {
	include_once ("config.php");
	include_once ("graphs/fgraph.php");
	include_once ("functions_calendar.php");
}

// ===============================================================================
// Draw a simple pie graph with incidents, by assigned user
// ===============================================================================

function incident_peruser ($width, $height) {
	require_once ("../include/config.php");
	
	$res = mysql_query("SELECT * FROM tusuario");
	while ($row=mysql_fetch_array($res)) {
		$id_user = $row["id_usuario"];
		$datos = get_db_sqlf ("SELECT COUNT(id_usuario) FROM tincidencia WHERE id_usuario = '$id_user'");
		if ($datos > 0) {
			$data[] = $datos;
			$legend[] = $id_user;
		} 
	} 
	if (isset($data))
		generic_pie_graph ($width, $height, $data, $legend);
	else 
		graphic_error();
}

// ===============================================================================
// Draw a simple pie graph with reported workunits for a specific TASK
// ===============================================================================

function graph_workunit_task ($width, $height, $id_task) {
	global $config;
	$data = array();
	$legend = array();

	$res = mysql_query("SELECT SUM(duration) as duration, id_user FROM tworkunit, tworkunit_task
					WHERE tworkunit_task.id_task = $id_task AND 
					tworkunit_task.id_workunit = tworkunit.id 
					GROUP BY id_user ORDER BY duration DESC");

	$data = NULL;

	while ($row = mysql_fetch_array($res)) {
		$data[$row[1]] = $row[0];
	}
		
	if ($data == NULL) {
		echo __("There is no data to show");
	} else {
		return pie3d_graph($config['flash_charts'], $data, $width, $height, __('other'));
	}
}

// ===============================================================================
// Draw a simple pie graph with reported workunits for a specific PROJECT
// ===============================================================================

function graph_workunit_project ($width, $height, $id_project) {
	global $config;
	$data = array();

	$res = mysql_query("SELECT SUM(duration), ttask.name
					FROM tworkunit, tworkunit_task, ttask, tproject  
					WHERE tproject.id = '$id_project' AND 
					tworkunit.id = tworkunit_task.id_workunit AND 
					tworkunit_task.id_task = ttask.id AND
					tproject.id = ttask.id_project 
					GROUP BY ttask.name ORDER BY SUM(duration) DESC");

	$data = NULL;
	while ($row = mysql_fetch_array($res)) {
		$data[$row[1]] = $row[0];
	}
		
	if ($data == NULL) {
		echo __("There is no data to show");
	} else {
		return pie3d_graph($config['flash_charts'], $data, $width, $height, __('other'));
	}
}

// ===============================================================================
// Draw a simple pie graph with reported workunits for a specific PROJECT, showing
// time by each user.
// ===============================================================================

function graph_workunit_project_user_single ($width, $height, $id_project) {
	global $config;
	$data = array();

	$res = mysql_query("SELECT SUM(duration), tworkunit.id_user 
					FROM tworkunit, tworkunit_task, ttask, tproject  
					WHERE tproject.id = $id_project AND 
					tworkunit.id = tworkunit_task.id_workunit AND 
					tworkunit_task.id_task = ttask.id AND
					tproject.id = ttask.id_project 
					GROUP BY tworkunit.id_user ORDER BY SUM(duration) DESC");
	$data = NULL;
				
	while ($row = mysql_fetch_array($res)) {
		$data[$row[1]] = $row[0];
	}
		
	if ($data == NULL) {
		echo __("There is no data to show");
	} else {
		return pie3d_graph($config['flash_charts'], $data, $width, $height, __('other'));
	}
}

// ===============================================================================
// Draw a simple pie graph with reported workunits for a specific USER, per TASK/PROJECT
// ===============================================================================

function graph_workunit_user ($width, $height, $id_user, $date_from, $date_to = 0) {
	global $config;

	if ($date_to == 0) {
		$date_to = date("Y-m-d", strtotime("$date_from + 30 days"));
	}

	$res = mysql_query("SELECT SUM(duration), id_task, timestamp, ttask.name, tproject.name 
					FROM tworkunit, tworkunit_task, ttask, tproject  
					WHERE tworkunit.id_user = '$id_user' AND 
					tworkunit.id = tworkunit_task.id_workunit AND 
					tworkunit.timestamp > '$date_from' AND 
					tworkunit.timestamp < '$date_to' AND
					tworkunit_task.id_task = ttask.id AND
					tproject.id = ttask.id_project 
					GROUP BY id_task ORDER BY SUM(duration) DESC");
	$data = NULL;

	while ($row = mysql_fetch_array($res)) {
		$data[substr(clean_flash_string ($row[3]),0,25)]['graph'] = $row[0];
	}

	if ($data == NULL) {
		echo __("There is no data to show");
	} else {
		$colors['graph']['fine'] = true;
   	
		return hbar_graph($config['flash_charts'], $data, $width, $height, $colors);
	}
}


// ===============================================================================
// Draw a simple pie graph with reported workunits for a specific USER, per TASK/PROJECT
// ===============================================================================

function graph_workunit_project_user ($width, $height, $id_user, $date_from, $date_to = 0) {
	global $config;

	$data= array();
	$legend = array();

	if ($date_to == 0) {
		$date_to = date("Y-m-d", strtotime("$date_from + 30 days"));
	}

	$res = mysql_query("SELECT SUM(duration), tproject.name 
					FROM tworkunit, tworkunit_task, ttask, tproject  
					WHERE tworkunit.id_user = '$id_user' AND 
					tworkunit.id = tworkunit_task.id_workunit AND 
					tworkunit.timestamp >= '$date_from' AND 
					tworkunit.timestamp <= '$date_to' AND
					tworkunit_task.id_task = ttask.id AND
					tproject.id = ttask.id_project 
					GROUP BY tproject.name ORDER BY SUM(duration) DESC");
	$data = NULL;
	
	while ($row = mysql_fetch_array($res)) {
		$data[clean_flash_string ($row[1])]['graph'] = $row[0];
	}
	
	if ($data == NULL) {
		echo __("There is no data to show");
	} else {
		$colors['graph']['fine'] = true;
		
		echo hbar_graph($config['flash_charts'], $data, $width, $height, $colors);
	}
}

// ===============================================================================
// ===============================================================================
// ===============================================================================


function graphic_error ($flow = true) {
	global $config;
	if($flow) {
		Header('Content-type: image/png');
		$imgPng = imageCreateFromPng($config["homedir"].'/images/error.png');
		imageAlphaBlending($imgPng, true);
		imageSaveAlpha($imgPng, true);
		imagePng($imgPng);
	}
	else {
		return print_image('images/error.png', true);
	}
}

// ***************************************************************************
// Draw a dynamic progress bar using GDlib directly
// ***************************************************************************

function progress_bar ($progress, $width, $height) {
	global $config;
	
	$out_of_lim_str = __("Out of limits");
	$title = "";
	return "<img src='include/graphs/fgraph.php?graph_type=progressbar&width=".$width."&height=".$height."&progress=".$progress."&out_of_lim_str=".$out_of_lim_str."&title=".$title."&font=".$config['fontpath']."' />";
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

function histogram_2values($valuea, $valueb, $labela = "a", $labelb = "b", $mode = 1, $width = 200, $height = 30, $title = "") {
	global $config;
	
	$data = array();
	$data[$labela] = $valuea;
	$data[$labelb] = $valueb;		

	$data_json = json_encode($data);
	
	$max = max($valuea, $valueb);

	return "<img src='include/graphs/fgraph.php?graph_type=histogram&width=".$width."&height=".$height."&data=".$data_json."&max=".$max."&mode=".$mode."&title=".$title."&font=".$config['fontpath']."' />";
}

function project_tree ($id_project, $id_user) {
	include ("../include/config.php");
	$config["id_user"] = $id_user;
	if (user_belong_project ($id_user, $id_project)==0) {
		audit_db($id_user, $config["REMOTE_ADDR"], "ACL Violation","Trying to access to task manager of unauthorized project");
		include ($config["homedir"]."/general/noaccess.php");
		exit;
	}

	if ($id_project != -1)
		$project_name = get_db_value ("name", "tproject", "id", $id_project);
	else
		$project_name = "";

	$dotfilename = $config["homedir"]. "/attachment/tmp/$id_user.dot";
	$pngfilename = $config["homedir"]. "/attachment/tmp/$id_user.project.png";
	$dotfile = fopen ($dotfilename, "w");

	$total_task = 0;
	$sql2="SELECT * FROM ttask WHERE id_project = $id_project"; 
	if ($result2=mysql_query($sql2))	
	while ($row2=mysql_fetch_array($result2)) {
		if ((user_belong_task ($id_user, $row2["id"]) == 1)) {
			$task[$total_task] = $row2["id"];
			$task_name[$total_task] = $row2["name"];
			$task_parent[$total_task] = $row2["id_parent_task"];
			$task_workunit[$total_task] = get_task_workunit_hours ($row2["id"]);
			$total_task++;
		}
	}
	
	
	fwrite ($dotfile, "digraph Integria {\n");
	fwrite ($dotfile, "	  ranksep=2.0;\n");
	fwrite ($dotfile, "	  ratio=auto;\n");
	fwrite ($dotfile, "	  size=\"9,12\";\n");
	fwrite ($dotfile, "	  node[fontsize=".$config['fontsize']."];\n");
	fwrite ($dotfile, '	  project [label="'. wordwrap($project_name,12,'\\n').'",shape="ellipse", style="filled", color="grey"];'."\n");
	for ($ax=0; $ax < $total_task; $ax++) {
		fwrite ($dotfile, 'TASK'.$task[$ax].' [label="'.wordwrap($task_name[$ax],12,'\\n').'"];');
		fwrite ($dotfile, "\n");
	}
	
	// Make project first parent task relation visible
	for ($ax=0; $ax < $total_task; $ax++) {
		if ($task_parent[$ax] == 0) {
			fwrite ($dotfile, 'project -> TASK'.$task[$ax].';');
			fwrite ($dotfile, "\n");
		}
	}
	// Make task-subtask parent task relation visible
	for ($ax=0; $ax < $total_task; $ax++) {
		if ($task_parent[$ax] != 0) {
			fwrite ($dotfile, 'TASK'.$task_parent[$ax].' -> TASK'.$task[$ax].';');
			fwrite ($dotfile, "\n");
		}
	}
	
	fwrite ($dotfile,"}");
	fwrite ($dotfile, "\n");
	
	// exec ("twopi -Tpng $dotfilename -o $pngfilename");
	exec ("twopi -Tpng $dotfilename -o $pngfilename");
	Header('Content-type: image/png');
	$imgPng = imageCreateFromPng($pngfilename);
	imageAlphaBlending($imgPng, true);
	imageSaveAlpha($imgPng, true);
	imagePng($imgPng);
	//unlink ($pngfilename);
	//unlink ($dotfilename);
}

function all_project_tree ($id_user, $completion, $project_kind) {
	include ("../include/config.php");
	$config["id_user"] = $id_user;

	$dotfilename = $config["homedir"]. "/attachment/tmp/$id_user.all.dot";
	$pngfilename = $config["homedir"]. "/attachment/tmp/$id_user.projectall.png";
	$mapfilename = $config["homedir"]. "/attachment/tmp/$id_user.projectall.map";
	$dotfile = fopen ($dotfilename, "w");


	fwrite ($dotfile, "digraph Integria {\n");
	fwrite ($dotfile, "	  ranksep=1.8;\n");
	fwrite ($dotfile, "	  ratio=auto;\n");
	fwrite ($dotfile, "	  size=\"9,9\";\n");
	fwrite ($dotfile, 'URL="'.$config["base_url"].'/index.php?sec=projects&sec2=operation/projects/project_tree";'."\n");

	fwrite ($dotfile, "	  node[fontsize=".$config['fontsize']."];\n");
	fwrite ($dotfile, "	  me [label=\"$id_user\", style=\"filled\", color=\"yellow\"]; \n");

	$total_project = 0;
	$total_task = 0;
	if ($project_kind == "all")
		$sql1="SELECT * FROM tproject WHERE disabled = 0"; 
	else
		$sql1="SELECT * FROM tproject WHERE disabled = 0 AND end != '0000-00-00 00:00:00'"; 
	if ($result1=mysql_query($sql1))	
	while ($row1=mysql_fetch_array($result1)) {
		if ((user_belong_project ($id_user, $row1["id"],1 ) == 1)) {
			$project[$total_project] = $row1["id"];
			$project_name[$total_project] = $row1["name"];
			if ($completion < 0)
				$sql2="SELECT * FROM ttask WHERE id_project = ".$row1["id"]; 
			elseif ($completion < 101)
				$sql2="SELECT * FROM ttask WHERE completion < $completion AND id_project = ".$row1["id"]; 
			else
				$sql2="SELECT * FROM ttask WHERE completion = 100 AND id_project = ".$row1["id"]; 
			if ($result2=mysql_query($sql2))
			while ($row2=mysql_fetch_array($result2)) {
				if ((user_belong_task ($id_user, $row2["id"],1) == 1)) {
					$task[$total_task] = $row2["id"];
					$task_name[$total_task] = $row2["name"];
					$task_parent[$total_task] = $row2["id_parent_task"];
					$task_project[$total_task] = $project[$total_project];
					$task_workunit[$total_task] = get_task_workunit_hours ($row2["id"]);
					$task_completion[$total_task] = $row2["completion"];
					$total_task++;
				}
			}
			$total_project++;
		}
	}
	// Add project items
	for ($ax=0; $ax < $total_project; $ax++) {
		fwrite ($dotfile, 'PROY'.$project[$ax].' [label="'.wordwrap($project_name[$ax],12,'\\n').'", style="filled", color="grey", URL="'.$config["base_url"].'/index.php?sec=projects&sec2=operation/projects/task&id_project='.$project[$ax].'"];');
		fwrite ($dotfile, "\n");
	}
	// Add task items
	for ($ax=0; $ax < $total_task; $ax++) {

		$temp = 'TASK'.$task[$ax].' [label="'.wordwrap($task_name[$ax],12,'\\n').'"';
		if ($task_completion[$ax] < 10)
			$temp .= 'color="red"';
		elseif ($task_completion[$ax] < 100)
			$temp .= 'color="yellow"';
		elseif ($task_completion[$ax] == 100)
			$temp .= 'color="green"';
		$temp .= "URL=\"".$config["base_url"]."/index.php?sec=projects&sec2=operation/projects/task_detail&id_project=".$task_project[$ax]."&id_task=".$task[$ax]."&operation=view\"";
		$temp .= "];";
		fwrite ($dotfile, $temp);


	
		fwrite ($dotfile, "\n");
	}

	// Make project attach to user "me"
	for ($ax=0; $ax < $total_project; $ax++) {
		fwrite ($dotfile, 'me -> PROY'.$project[$ax].';');
		fwrite ($dotfile, "\n");
		
	}

	// Make project first parent task relation visible
	for ($ax=0; $ax < $total_task; $ax++) {
		if ($task_parent[$ax] == 0) {
			fwrite ($dotfile, 'PROY'.$task_project[$ax].' -> TASK'.$task[$ax].';');
			fwrite ($dotfile, "\n");
		}
	}

	
	// Make task-subtask parent task relation visible
	for ($ax=0; $ax < $total_task; $ax++) {
		if ($task_parent[$ax] != 0) {
			fwrite ($dotfile, 'TASK'.$task_parent[$ax].' -> TASK'.$task[$ax].';');
			fwrite ($dotfile, "\n");
		}
	}
	
	fwrite ($dotfile,"}");
	fwrite ($dotfile, "\n");
	// exec ("twopi -Tpng $dotfilename -o $pngfilename");

	exec ("twopi -Tcmapx -o$mapfilename -Tpng -o$pngfilename $dotfilename");

	Header('Content-type: image/png');
	$imgPng = imageCreateFromPng($pngfilename);
	imageAlphaBlending($imgPng, true);
	imageSaveAlpha($imgPng, true);
	imagePng($imgPng);
	require ($mapfilename);
	//unlink ($pngfilename);
	unlink ($dotfilename);
}

// ****************************************************************************
//   MAIN Code
//   parse get parameters
// ****************************************************************************


if (isset($_GET["id_audit"]))
	$id_audit = $_GET["id_audit"];
else
	$id_audit = 0;
if (isset($_GET["id_group"]))
	$id_group = $_GET["id_group"];
else
	$id_group = 0;
if (isset($_GET["period"]))
	$period = $_GET["period"];
else
	$period = 129600; // Month
if (isset($_GET["width"]))
	$width= $_GET["width"];
else 
	$width= 280;
if (isset($_GET["height"]))
	$height= $_GET["height"];
else
	$height= 50;

$id_user = get_parameter ("id_user", "");
$id_project = get_parameter ("id_project",0);
$graphtype = get_parameter ("graphtype",0);
$completion = get_parameter ("completion",0);
$project_kind = get_parameter ("project_kind","");
$id_task = get_parameter ("id_task",0);
$max = get_parameter ("max" , 0);
$min = get_parameter ("min" , 0);
$labela = get_parameter("labela" , "");
$labelb = get_parameter ("labelb" , "");
$valuea = get_parameter ("a" , 0);
$valueb = get_parameter ("b" , 0);
$valuec = get_parameter ("c" , 0);
$lite = get_parameter ("lite" , 0);
$date_from = get_parameter ( "date_from", 0);
$date_to   = get_parameter ( "date_to", 0);
$mode = get_parameter ( "mode", 1);
$percent = get_parameter ( "percent", 0);
$days = get_parameter ( "days", 0);
$type= get_parameter ("type", "");
$background = get_parameter ("background", "#ffffff");

if ($type == "incident_a")
	incident_peruser ($width, $height);
elseif ($type == "workunit_task")
	graph_workunit_task($width, $height, $id_task);
elseif ($type == "workunit_user")
	graph_workunit_user ($width, $height, $id_user, $date_from);
elseif ($type == "workunit_project_user")
	graph_workunit_project_user ($width, $height, $id_user, $date_from, $date_to);
elseif ($type == "project_tree")
	project_tree ($id_project, $id_user);
elseif ($type == "all_project_tree")
	all_project_tree ($id_user, $completion, $project_kind);

// Always at the end of the funtions_graph
include_flash_chart_script();
?>
