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
    echo "<h3 class='suc'>".lang_string ("Assigment removed succesfully")."</h3>";
}


echo "<form name='xx' method=post action='index.php?sec=users&sec2=godmode/usuarios/role_user_global'>";
// Show user
combo_user_visible_for_me ($id_user, "id_user", 0, "PR");
echo "<input type=submit value=go class='sub upd'>";


echo "</form>";

if ($id_user != ""){

    $sql = "SELECT ttask.id, ttask.name, tproject.name, trole_people_task.id_role FROM trole_people_task, ttask, tproject WHERE trole_people_task.id_user = '$id_user' AND trole_people_task.id_task = ttask.id AND ttask.id_project = tproject.id AND tproject.disabled = 0";

    echo "<h2>".lang_string ("Global task assignment")."</h2>";
    echo "<h3>".lang_string ("For user"). " ".$id_user."</h3>";
    echo "<table cellpadding=4 cellspacing=4 class=databox_color width=700>";
    echo "<th>".lang_string ("Project");
    echo "<th>".lang_string ("Task");
    echo "<th>".lang_string ("Role");
    echo "<th>".lang_string ("WU");
    echo "<th>".$lang_label["delete"];
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
        echo "<td class=$tdcolor>".$row[1];
        echo "<td class=$tdcolor>".give_db_sqlfree_field ("SELECT name FROM trole WHERE id = ".$row[3]);
        echo "<td class=$tdcolor>".give_wu_task_user ($row[0], $id_user);
        echo '<td class="'.$tdcolor.'" align="center"><a href="index.php?sec=users&sec2=godmode/usuarios/role_user_global&id_user='.$id_user.'&delete='.$row[0].'" onClick="if (!confirm(\' '.$lang_label["are_you_sure"].'\')) return false;"><img border=0 src="images/cross.png"></a>';
    }
    echo "</table>";
}

?>
