<?php

// Integria 1.0 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2008 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// gantt php class example and configuration file
// Copyright (C) 2005 Alexandre Miguel de Andrade Souza


// Real start

include_once ("../../include/config.php");
global $config;

require '../../include/gantt.php';


// I like recursion :)

function add_task_child (&$definitions, $id_task, &$task_counter, &$task_array, $id_project, $project_begin, $project_end, $dependency_counter, $parent_counter){

    $sql="SELECT * FROM ttask WHERE id_parent_task = $id_task AND id_project = $id_project";
    if ($result=mysql_query($sql))    
    while ($row=mysql_fetch_array($result)){
        $task_counter++;
    	$task_id = $row["id"];
        $task_array[$task_id]=$task_counter;
        $task_name = remove_locale_chars ($row["name"]);
    	$task_parent = $id_task;

		if (isset($task_array[$id_task]))
	        $parent_counter_id = $task_array[$id_task];
		else
			$parent_counter_id= 0;

		if ($id_task != 0){
            $definitions['dependency_planned'][$dependency_counter]['type']= 'END_TO_START';
		    $definitions['dependency_planned'][$dependency_counter]['phase_from']= $parent_counter;
		    $definitions['dependency_planned'][$dependency_counter]['phase_to'] = $task_counter;
        }
	
	    $task_progress = $row["completion"];
    	$task_begin =  strtotime ($row["start"]);
    	$task_end = strtotime ($row["end"]);

    	$task_work_end =  strtotime (give_db_sqlfree_field ("SELECT MAX(tworkunit.timestamp) FROM tworkunit_task, tworkunit WHERE tworkunit.id = tworkunit_task.id_workunit AND tworkunit_task.id_task = $task_id"));
    	$task_work_begin =  strtotime (give_db_sqlfree_field ("SELECT MIN(tworkunit.timestamp) FROM tworkunit_task, tworkunit WHERE tworkunit.id = tworkunit_task.id_workunit AND tworkunit_task.id_task = $task_id"));
    	if ($task_work_begin == "")
    		$task_work_begin = $project_begin;
    	if ($task_work_end == "")
    		$task_work_end = $project_begin;

	    // Sanity checks for dates of projects and max/min of work units
	    if ($task_begin < $project_begin)
		    $task_begin = $project_begin;
	    if ($task_end > $project_end)
		    $task_end = $project_end;
	    if ($task_work_begin < $project_begin)
		    $task_work_begin = $project_begin;
	    if ($task_work_end > $project_end)
		    $task_work_end = $project_end;

	    $definitions['groups']['group'][0]['phase'][$task_counter] = $task_counter;
	    $definitions['planned']['phase'][$task_counter]['name'] = $task_name;
	    $definitions['planned']['phase'][$task_counter]['start'] = $task_begin;
	    $definitions['planned']['phase'][$task_counter]['end'] = $task_end;
	    $definitions['progress']['phase'][$task_counter]['progress']=$task_progress;
	    $definitions['real']['phase'][$task_counter]['start'] = $task_work_begin;
	    $definitions['real']['phase'][$task_counter]['end'] = $task_work_end;
        add_task_child (&$definitions, $row["id"], &$task_counter, &$task_array, $id_project, $project_begin, $project_end, $dependency_counter, $task_counter);
        if ($id_task != 0)
            $dependency_counter++;
    }

}

$dependency_counter=0;
// Get data about this project
$id_user = $_SESSION['id_usuario'];
$id_project = give_parameter_get ("id_project", -1);
if ($id_project != -1){
	$project_name = give_db_value ("name", "tproject", "id", $id_project);
	$project_begin =  give_db_value ("start", "tproject", "id", $id_project);
	$project_end = give_db_value ("end", "tproject", "id", $id_project);
	$project_scale = (strtotime ($project_end) - strtotime ($project_begin)) / 86400;
	if ($project_scale < 46)
		$project_scale_option ="d";
	elseif (($project_scale > 45) AND ($project_scale < 90))
		$project_scale_option ="w";
	else
		$project_scale_option ="m";

	// scale for month
	if (($project_scale_option == "m") AND ($project_scale > 300))
		$project_scale_month = '2';
	elseif (($project_scale_option == "m") AND ($project_scale > 200) AND ($project_scale < 300))
		$project_scale_month = '3';
	elseif (($project_scale_option == "m") AND ($project_scale < 200))
		$project_scale_month = '4';
	else
		$project_scale_month = 5;

} else
	$project_name = "";

//generic  definitions to graphic, you dont need to change this. Only if you want
$definitions['planned']['y'] = 0;
$definitions['planned']['height']= 8;
$definitions['planned_adjusted']['y'] = 19;
$definitions['planned_adjusted']['height']= 9;
$definitions['real']['y']=18;
$definitions['real']['height']=6;
$definitions['img_bg_color'] = array (227,233,233);
$definitions['title_bg_color'] = array(2, 125, 206);
$definitions['title_color'] = array(255, 255, 255);
//$definitions['milestone']['title_bg_color'] = array(204, 204, 230);
$definitions['today']['color']=array(35, 196, 255);

$definitions['real']['hachured_color']=array(244,0, 0);//red

$definitions['workday_color'] = array(255, 255, 255	); //white -> default color of the grid
$definitions['grid_color'] = array(218, 218, 218);
$definitions['groups']['color'] = array(0, 0, 0);//black
$definitions['groups']['bg_color'] = array(204,210,210);
$definitions['planned']['color']=array(0, 0, 240);//green
$definitions['planned_adjusted']['color']=array(0, 0, 204); //blue
$definitions['real']['color']=array(255, 255,255);//while
$definitions['progress']['color']=array(111,255,55); // white
$definitions['progress']['y']=10; // relative vertical position in pixels -> progress
$definitions['progress']['height']=5; 
$definitions['dependency_color']['END_TO_START']=array(0, 0, 0);//black
$definitions['dependency_color']['START_TO_START']=array(0, 0, 0);//black
$definitions['dependency_color']['END_TO_END']=array(0, 0, 0);//black
$definitions['dependency_color']['START_TO_END']=array(0, 0, 0);//black
$definitions['planned']['legend'] = 'INITIAL PLANNING';
$definitions['planned_adjusted']['legend'] = lang_string ("Planning");
$definitions['real']['legend'] = lang_string ("Work reported");
$definitions['progress']['legend'] = lang_string ("Progress");
$definitions['milestone']['legend'] = lang_string ("Milestone");
$definitions['today']['legend'] = lang_string ("Today");
$definitions['today']['pixels'] = 10; //set the number of pixels to line interval
$definitions['limit']['cell']['m'] = $project_scale_month / 1.2; // size of cells (each day)
$definitions['limit']['cell']['w'] = '8'; // size of cells (each day)
$definitions['limit']['cell']['d'] = '20';// size of cells (each day)
$definitions['grid']['x'] = 120; // initial position of the grix (x)
$definitions['grid']['y'] = 40; // initial position of the grix (y)
$definitions['row']['height'] = 40; // height of each row

$definitions['legend']['y'] = 50; // initial position of legent (height of image - y)
$definitions['legend']['x'] = 200; // distance between two cols of the legend
$definitions['legend']['y_'] = 20; //distance between the image bottom and legend botton
$definitions['legend']['ydiff'] = 20; //diference between lines of legend
$definitions['text_font'] = 2; //define the font to text -> 1 to 4 (gd fonts)
$definitions['title_font'] = 5;  //define the font to title -> 1 to 4 (gd fonts)
$definitions['milestones']['color'] = array(225, 0, 0);
$definitions['progress']['bar_type']='planned';

//global definitions to graphic
// change to you project data/needshttp://artica.es/integria/operation/projects/gantt_graph.php?id_project=2
$definitions['title_string'] = $project_name;
$definitions['title_y'] = 10;
$definitions['locale'] = "iso-8859-15";
$definitions['limit']['detail'] = $project_scale_option;

$definitions['limit']['start'] = strtotime($project_begin);; //these settings will define the size of

$definitions['limit']['end'] = strtotime($project_end);; //graphic and time limits
$definitions['today']['data']= strtotime("now"); //time();//draw a line in this date

// use loops to define these variables with database data

// you need to set groups to graphic be created
$definitions['groups']['group'][0]['name'] = lang_string("Full project");
$definitions['groups']['group'][0]['start'] = strtotime($project_begin);
$definitions['groups']['group'][0]['end'] = strtotime($project_end);

$definitions["not_show_groups"] = false;

$task_counter = -1;
$dependency_counter = 0;
$project_begin = strtotime($project_begin);
$project_end = strtotime($project_end);
$parent_counter = 0;
add_task_child (&$definitions, 0, &$task_counter, &$task_array, $id_project, $project_begin, $project_end, $dependency_counter, $parent_counter);

// milestones
$milestone_counter = 0;
$sql="SELECT * FROM tmilestone WHERE id_project = $id_project"; 
if ($result=mysql_query($sql))    
while ($row=mysql_fetch_array($result)){
	$ms_name = remove_locale_chars ($row["name"]);
	$ms_timestamp= strtotime ($row["timestamp"]);
	$definitions['milestones']['milestone'][$milestone_counter]['data']= $ms_timestamp;
	$definitions['milestones']['milestone'][$milestone_counter]['title']= $ms_name;
	$definitions['groups']['group'][0]['milestone'][$milestone_counter]=$milestone_counter; //need to set a group to show
	$milestone_counter++;
}

new gantt($definitions);


?>
