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

include_once ("include/functions_graph.php");

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

// MAIN LIST OF TASKS

$search_id_group = (int) get_parameter ('search_id_group');
$search_text = (string) get_parameter ('search_text');

echo '<h2>'.$project['name'].' &raquo; '.__('Task list');
echo "&nbsp;&nbsp;<a title='"._("Report")."'  href='index.php?sec=projects&sec2=operation/projects/csvexport&id_project=$id_project&pdf_output=1&clean_output=1'><img src='images/page_white_acrobat.png'></a>";
echo '</h2><br>';

// Simple query, needs to implement group control and ACL checking
$sql = "SELECT * FROM ttask WHERE id_project = $id_project and id_parent_task = 0
		ORDER BY completion DESC";

$tasks = get_db_all_rows_sql ($sql);
if ($tasks === false)
return;

echo "<table width=700 class=listing cellpadding=4 cellspacing=4>";
echo "<tr><th class='listing'>Name</th><th>Hours</th><th>Completion</th><th>Start / End</th><th>People</th></tr>";

foreach ($tasks as $task) {
	if (user_belong_task ($config['id_user'], $task['id'])){
	    render_task($task);
	}
}

function render_task ($task, $parent = 0){
        echo "<tr>";
		echo "<td>";
        if ($parent != 0)
            echo "<img src='images/copy.png'>";

		echo $task["name"];
	
		echo "<td><b>";
        echo get_task_workunit_hours ($task["id"]);
		echo "</b> / ".$task["hours"];
	
		echo "<td>";
        echo progress_bar($task["completion"], 70, 20);

		echo "<td style='font-size: 9px'>";
		echo $task["start"];
        echo " / ";
		echo $task["end"];

        echo "<td>";
        echo get_db_value ('COUNT(DISTINCT(id_user))', 'trole_people_task', 'id_task', $task['id']);

		echo "</tr>";

        // Simple query, needs to implement group control and ACL checking

        $sql = "SELECT * FROM ttask WHERE id_parent_task = ".$task["id"]." ORDER BY completion DESC";
        $tasks = get_db_all_rows_sql ($sql);
        if ($tasks === false)
            return;

        foreach ($tasks as $task2) {
        	if (user_belong_task ($config['id_user'], $task2['id'])){
        	    render_task($task2, $task["id"]);
            }
        }
}

echo "</table>";
?>
