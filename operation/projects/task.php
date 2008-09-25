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


if (check_login() != 0) {
    audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access", "Trying to access event viewer");
    require ("general/noaccess.php");
    exit;
}

$id_user = $config["id_user"];

$id_project = give_parameter_get ("id_project", -1);
if ($id_project != -1)
	$project_name = give_db_value ("name", "tproject", "id", $id_project);
else
	$project_name = "";

if ( $id_project == -1 ){
    // Doesn't have access to this page
    audit_db($id_user, $config["REMOTE_ADDR"], "ACL Violation","Trying to access to task manager withour project");
    include ("general/noaccess.php");
    exit;
}

if (user_belong_project ($id_user, $id_project)==0){
	audit_db($id_user, $config["REMOTE_ADDR"], "ACL Violation","Trying to access to task manager of unauthorized project");
	include ("general/noaccess.php");
	exit;
}

$operation = give_parameter_get ("operation", -1);
if ($operation == "delete") {
	$id_task = give_parameter_get ("id");
	
	if ((dame_admin($id_user)==1) OR (project_manager_check ($id_project) == 1)){
		delete_task ($id_task);
		echo "<h3 class='suc'>".$lang_label["delete_ok"]."</h3>";
		$operation = "";
        task_tracking ($id_user, $id_task, 20, 0, 0);
	} else {
		no_permission();
	}
}
elseif ($operation == "move") {
    $target_project = get_parameter ("target_project");
    $id_task = give_parameter_get ("id_task");
    if ((dame_admin($id_user)==1) OR (project_manager_check ($id_project) == 1)){
        $sql = "UPDATE ttask SET id_project = $target_project, id_parent_task = 0 WHERE id = $id_task";
        mysql_query($sql);
        task_tracking ($id_user, $id_task, 19, 0, 0);
    } else {
        no_permission();
    }
}

// MAIN LIST OF TASKS

echo "<h2>".$project_name." - ".$lang_label["task_management"]."</h2>";

$filter_id_group = get_parameter ("filter_id_group", 0);
$filter_freetext = get_parameter ("filter_freetext", "");

$FILTER = " 1=1 ";

if ( $filter_freetext != "")
	$FILTER .= " AND name LIKE '%$filter_freetext%' OR description LIKE '%$filter_freetext%' ";

if ($filter_id_group != 0)
	$FILTER .= " AND id_group = $filter_id_group";



echo "<table width=610>";
	echo "<form method=post action='index.php?sec=projects&sec2=operation/projects/task&id_project=$id_project'>";
	echo "<tr><td>";
	echo lang_string ("Free text search");
	echo "<td>";
	echo print_input_text ("filter_freetext", $filter_freetext, "", 15, 100, false);

	echo "<td>";
	echo lang_string ("Group");
	echo "<td>";
	echo print_select_from_sql ("SELECT * from tgrupo WHERE id_grupo > 1 ORDER BY nombre", "filter_id_group", $filter_id_group, "", lang_string("None"), '0', false, false, true, false); 

	echo "<td>";
	print_submit_button (lang_string("Search"), "enviar", false, "class='sub search'", false);
	echo "</form></td></tr></table>";


// -------------
// Show headers
// -------------
echo "<table width='100%' class='listing'>";
echo "<tr>";
echo "<th class='f9'>".$lang_label["name"];
echo "<th class='f9'>".lang_string ("pri");
echo "<th class='f9'>".lang_string ("progress");
echo "<th class='f9'>".lang_string ("Estimation");
echo "<th class='f9'>".$lang_label["time_used"];
echo "<th class='f9'>".lang_string ("Cost");
echo "<th class='f9'>".$lang_label["people"];

echo "<th>".$lang_label["start"];
echo "<th>".$lang_label["end"];
echo "<th>".lang_string ("delete");
$color = 1;
show_task_tree ($id_project, 0, 0, 0, $FILTER);
echo "</table>";


if (give_acl($config["id_user"], 0, "IW")==1) {
	echo "<table width=100% class='button'>";
	echo "<tr><td align=right>";
    echo "<form name='boton' method='POST'  action='index.php?sec=projects&sec2=operation/projects/task_detail&id_project=$id_project&operation=create'>";
    echo "<input type='submit' class='sub next' name='crt' value='".$lang_label["create_task"]."'>";
    echo "</form>";
	echo "</td></tr></table>";
}


function show_task_row ( $id_project, $row2, $tdcolor, $level = 0){
    global $config;

	echo "<tr>";
	// Task  name
	echo "<td class='$tdcolor' align='left' >";
	for ($ax=0; $ax < $level; $ax++)
		echo "<img src='images/copy.png'>";
	//if ($level > 0)
	//echo "<img src='images/copy.png'>&nbsp;";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/task_detail&id_project=$id_project&id_task=".$row2["id"]."&operation=view'>".$row2["name"]."</a></td>";

	// Priority
	echo "<td class='$tdcolor' align='center'>";
    switch ( $row2["priority"] ){
        case 0: echo "<img src='images/flag_white.png' title='Informative'>"; break; // Informative
        case 1: echo "<img src='images/flag_green.png' title='Low'>"; break; // Low
        case 2: echo "<img src='images/flag_yellow.png' title='Medium'>"; break; // Medium
        case 3: echo "<img src='images/flag_orange.png' title='Serious'>"; break; // Serious
        case 4: echo "<img src='images/flag_red.png' title='Very serious'>"; break; // Very serious
        case 10: echo "<img src='images/flag_blue.png' title='Maintance'>"; break; // Maintance
    }

	// Completion
	echo "<td class='$tdcolor' align='center'>";
	//echo clean_input ($row2["completion"]."%");
	echo "<img src='include/functions_graph.php?type=progress&width=70&height=20&percent=".$row2["completion"]."'>";

    // Estimation
    echo "<td class='$tdcolor' align='center'>";
    $imghelp = "Estimated hours = ".$row2["hours"];
    $taskhours = give_hours_task ($row2["id"]);
    $imghelp .= "\nWorked hours = $taskhours";
    $a = round ($row2["hours"]);
    $b = round ($taskhours);
    $max = maxof($a, $b);
    if ($a > 0)
        echo "<img src='include/functions_graph.php?type=histogram&width=60&mode=2&height=18&a=$a&b=$b&&max=$max' title='$imghelp'>";
    else
        echo "--";

    // Time used
    echo "<td class='$tdcolor' align='center'>";
    $timeuser = give_hours_task ( $row2["id"]);
    if ($timeuser > 0)
        echo $timeuser;
    else
        echo "--";

    // Costs (client / total)
    echo "<td class='".$tdcolor."f9' align='center'>";
    $costdata = format_numeric (task_workunit_cost ($row2["id"], 1));
    if ($costdata > 0){
    	echo $costdata;
	    echo $config["currency"];
    } else {
    	echo "--";
    }
   

    // People
    echo "<td class='$tdcolor'>";
    echo combo_users_task ($row2["id"],1);
    echo "&nbsp;";
    echo give_db_sqlfree_field ("SELECT COUNT(DISTINCT (id_user)) FROM trole_people_task WHERE id_task =".$row2["id"]);


	if ($row2["start"] == $row2["end"]){
		echo "<td colspan=2>";
		echo __("Periodicity");
		echo "&nbsp;";
		echo $row2["periodicity"];
	} else {
		// Start
		echo "<td class='".$tdcolor."f9'>";
		echo substr($row2["start"],0,10);
		// End
		echo "<td class='".$tdcolor."f9'>";
		$ahora=date("Y/m/d H:i:s");
		$endtime = $row2["end"];
	
		if ($row2["completion"] == 100){
			echo "<font color='green'>";
		} else {
			if (strtotime($ahora) > strtotime($endtime))
				echo "<font color='red'>";
			else
			echo "<font>";
		}
		// echo human_time_comparation ($endtime);
			echo $endtime;
		echo "</font>";
	}

	// Delete
	echo "<td class='$tdcolor' align='center'>";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/task&operation=delete&id_project=$id_project&id=".$row2["id"]."' onClick='if (!confirm(\' ".lang_string ("are_you_sure")."\')) return false;'><img src='images/cross.png' border='0'></a>";
	
	
}

function show_task_tree ( $id_project, $level = 0, $parent_task = 0, $color = 0, $FILTER = " 1=1 "){
	global $config;
	$id_user = $config["id_user"];
	// Simple query, needs to implement group control and ACL checking
	$sql2 = "SELECT * FROM ttask WHERE $FILTER AND id_project = $id_project and id_parent_task = $parent_task ORDER BY name"; 
	if ($result2=mysql_query($sql2))    
	while ($row2=mysql_fetch_array($result2)){
		if ($color == 1){
			$tdcolor = "datos";
			$color = 0;
		}
		else {
			$tdcolor = "datos2";
			$color = 1;
		}
		if ((user_belong_task ($id_user, $row2["id"]) == 1)) 
			show_task_row ( $id_project, $row2, $tdcolor, $level );
		show_task_tree ( $id_project, $level+1, $row2["id"], $color, $FILTER);
	}
}
?>
