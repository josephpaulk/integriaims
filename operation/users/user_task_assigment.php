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
    
if (give_acl($config["id_user"], 0, "PR") != 1){
    // Doesn't have access to this page
    audit_db ($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access to project detail page");
    include ("general/noaccess.php");
    exit;
}

$id_user = get_parameter ("id_user", $config["id_user"]);

if (($id_user != $config["id_user"]) AND (give_acl($config["id_user"], 0, "PM") != 1)){
	$id_user = $config["id_user"];
}

$sql = "SELECT ttask.id, ttask.name, tproject.name, ttask.completion, tproject.id, ttask.id FROM trole_people_task, ttask, tproject WHERE trole_people_task.id_user = '$id_user' AND trole_people_task.id_task = ttask.id AND ttask.id_project = tproject.id AND tproject.disabled = 0 AND ttask.completion < 100 ORDER BY ttask.completion DESC";

    echo "<h2>".__('Global task assignment')." ".__('For user'). " '".$id_user. "' ".print_user_avatar($id_user, true,true)."</h2>";

if (give_acl($config["id_user"], 0, "PM") == 1){
	echo "<form name='xx' method=post action='index.php?sec=users&sec2=operation/users/user_task_assigment'>";
	
	echo "<table style='margin-left: 15px;' class=blank>";
	echo "<tr><td>";
	// Show user
	combo_user_visible_for_me ($config["id_user"], "id_user", 0, "PR");
	echo "<td>";
	print_submit_button (__('Go'), 'sub_btn', false, 'class="upd sub"');
	//echo "<input type=submit value=go class='sub upd'>";
    echo "</form></table>";
}

    echo "<table  class='listing' width=800>";
    echo "<th>".__('Project');
    echo "<th>".__('Task');
    echo "<th>".__('Progress');
    echo "<th>".__('Worked hours');
    echo "<th>".__('Last update');
    $result=mysql_query($sql);
    
    while ($row=mysql_fetch_array($result)){
        echo "<tr>";
        echo "<td>".$row[2];
	$id_proj = $row[4];
        $id_task = $row[5];
        echo "<td><a href='index.php?sec=projects&sec2=operation/projects/task_detail&id_project=$id_proj&id_task=$id_task&operation=view'>".$row[1]."</a>";
		
		echo "<td >";
		echo "<img src='include/functions_graph.php?type=progress&width=70&height=20&percent=".$row[3]."'>";
        
		echo "<td>".get_task_workunit_hours_user ($row[0], $id_user);
        $wutime = get_db_sql ("SELECT timestamp FROM tworkunit_task, tworkunit WHERE tworkunit.id_user = '$id_user' AND tworkunit_task.id_task = ".$row[0]." AND tworkunit.id = tworkunit_task.id_workunit order by timestamp desc LIMIT 1");

        echo "<td class='f9'>".$wutime;

    }
    echo "</table>";


?>
