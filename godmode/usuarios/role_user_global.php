<?php

// Integria 1.1 - http://integria.sourceforge.net
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

// Load global vars

global $config;

if (check_login() != 0) {
    audit_db("Noauth",$config["REMOTE_ADDR"], "No authenticated access","Trying to access event viewer");
    require ("general/noaccess.php");
    exit;
}
    
if (give_acl($config["id_user"], 0, "PM") != 1){
    // Doesn't have access to this page
    audit_db ($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access to project detail page");
    include ("general/noaccess.php");
    exit;
}

$id_user = get_parameter ("id_user", $config["id_user"]);

$delete = get_parameter ("delete", 0);
if ($delete != 0){
    $id_task = $delete;
    $sql = "DELETE FROM trole_people_task WHERE id_task = $id_task AND id_user = '$id_user'";
    $resq1=mysql_query($sql);
    echo "<h3 class='suc'>".__('Assigment removed succesfully')."</h3>";
}


echo "<form name='xx' method=post action='index.php?sec=users&sec2=godmode/usuarios/role_user_global'>";
// Show user
combo_user_visible_for_me ($id_user, "id_user", 0, "PR");
echo "<input type=submit value=go class='sub upd'>";


echo "</form>";

if ($id_user != ""){

    $sql = "SELECT ttask.id, ttask.name, tproject.name, trole_people_task.id_role, tproject.id FROM trole_people_task, ttask, tproject WHERE trole_people_task.id_user = '$id_user' AND trole_people_task.id_task = ttask.id AND ttask.id_project = tproject.id AND tproject.disabled = 0 ORDER BY tproject.name";

    echo "<h2>".__('Global task assignment')."</h2>";
    echo "<h3>".__('For user'). " ".$id_user."</h3>";
    echo "<table cellpadding=4 cellspacing=4 class=databox_color width=700>";
    echo "<th>".__('Project');
    echo "<th>".__('Task');
    echo "<th>".__('Role');
    echo "<th>".__('WU');
    echo "<th>".__('Delete');
    $result=mysql_query($sql);
    $color=1;
    while ($row=mysql_fetch_array($result)){
        if ($color == 1){
            $tdcolor = "datos";
            $color = 0;
        }
        else {
            $tdcolor = "datos2";
            $color = 1;
        }
        echo "<tr>";
        echo "<td class=$tdcolor>".$row[2];
        echo "<td class=$tdcolor><b><a href='index.php?sec=projects&sec2=operation/projects/task_detail&id_project=".$row[4]."&id_task=".$row[0]."&operation=view'>".$row[1]."</a></b>";
        echo "<td class=$tdcolor>".get_db_sql ("SELECT name FROM trole WHERE id = ".$row[3]);
        echo "<td class=$tdcolor>".get_task_workunit_hours_user ($row[0], $id_user);
        echo '<td class="'.$tdcolor.'" align="center"><a href="index.php?sec=users&sec2=godmode/usuarios/role_user_global&id_user='.$id_user.'&delete='.$row[0].'" onClick="if (!confirm(\' '.__('Are you sure?').'\')) return false;"><img border=0 src="images/cross.png"></a>';
    }
    echo "</table>";
}

?>
