<?php

// FRITS - the FRee Incident Tracking System
// =========================================
// Copyright (c) 2007 Sancho Lerena, slerena@openideas.info
// Copyright (c) 2007 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
// Load global vars
require "include/config.php";
require "include/functions_form.php";

if (comprueba_login() != 0) {
    audit_db("Noauth",$REMOTE_ADDR, "No authenticated access","Trying to access event viewer");
    require ("general/noaccess.php");
    exit;
}

$id_user = $_SESSION['id_usuario'];

if (!isset($_GET["id_project"])){
    // Doesn't have access to this page
    audit_db($id_user, $REMOTE_ADDR, "ACL Violation","Trying to access to task manager withour project");
    include ("general/noaccess.php");
    exit;
}


$id_project = $_GET["id_project"];


// SHOW TABS
echo "<div id='menu_tab'><ul class='mn'>";

// Main
echo "<li class='nomn'>";
echo "<a href='index.php?sec=projects&sec2=operation/projects/project_detail&id=$id_project'><img src='images/application_edit.png' class='top' border=0> ".$lang_label["project"]."</a>";
echo "</li>";

// Tasks
echo "<li class='nomn'>";
echo "<a href='index.php?sec=projects&sec2=operation/projects/task&id_project=$id_project'><img src='images/page_white_text.png' class='top' border=0> ".$lang_label["tasks"]." () </a>";
echo "</li>";

// Tracking
echo "<li class='nomn'>";
echo "<a href='index.php?sec=projects&sec2=operation/projects/tracking&id=$id_project'><img src='images/eye.png' class='top' border=0> ".$lang_label["tracking"]." </a>";
echo "</li>";

// People
echo "<li class='nomn'>";
echo "<a href='index.php?sec=projects&sec2=operation/projects/people_manager&id=$id_project'><img src='images/user_suit.png' class='top' border=0> ".$lang_label["people"]." </a>";
echo "</li>";

echo "</ul>";
echo "</div>";
echo "<div style='height: 25px'> </div>";
 
// MAIN LIST OF TASKS

echo "<h2>".$lang_label["task_management"];

// -------------
// Show headers
// -------------
echo "<table width='810' class='databox'>";
echo "<tr>";
echo "<th>".$lang_label["name"];
echo "<th>".$lang_label["completion"];
echo "<th>".$lang_label["group"];
echo "<th>".$lang_label["people"];
echo "<th>".$lang_label["tasks"];
echo "<th>".$lang_label["start"];
echo "<th>".$lang_label["end"];
echo "<th>".$lang_label["delete"];
$color = 1;

// -------------
// Show DATA TABLE
// -------------

// Simple query, needs to implement group control and ACL checking
$sql2="SELECT * FROM ttask"; 
if ($result2=mysql_query($sql2))    
while ($row2=mysql_fetch_array($result2)){
    $id_group = $row2["id_group"];
    if (give_acl($id_usuario, $id_group, "IR") ==1){
        if ($color == 1){
            $tdcolor = "datos";
            $color = 0;
        }
        else {
            $tdcolor = "datos2";
            $color = 1;
        }
        
        echo "<tr>";

        // Project name
        echo "<td class='$tdcolor' align='left' >";
        echo "<a href='index.php?sec=projects&sec2=operation/projects/project_detail&id=".$row2["id"]."'>".$row2["name"]."</a></td>";

        // Completion
        echo "<td class='$tdcolor' align='center'>";
        echo "50%";
        
        // Group
        echo "<td class='$tdcolor'>".dame_nombre_grupo($row2["id_group"]);
        
        // People
        echo "<td class='$tdcolor'>";
        combo_users_project ($row2["id"]);

        // Tasks
        echo "<td class='$tdcolor'>";

        // Start
        echo "<td class='".$tdcolor."f9'>";
        echo substr($row2["start"],0,10);

        // End
        echo "<td class='".$tdcolor."f9'>";
        echo substr($row2["end"],0,10);
        
        if ((give_acl($id_usuario, $id_group, "IM") ==1) OR ($_SESSION["id_usuario"] == $id_author_inc) ){
        // Only incident owners or incident manager
        // from this group can delete incidents
            echo "<td class='$tdcolor' align='center'><a href='index.php?sec=projects&sec2=operation/projects/project&quick_delete=".$row2["id"]."' onClick='if (!confirm(\' ".$lang_label["are_you_sure"]."\')) return false;'><img src='images/cross.png' border='0'></a></td>";
        } else
            echo "<td class='$tdcolor' align='center'>";
    } else {
    echo "  sin acceso";
    } 
}
echo "</table>";



if (give_acl($_SESSION["id_usuario"], 0, "IW")==1) {
    echo "<form name='boton' method='POST'  action='index.php?sec=projects&sec2=operation/projects/project_detail&insert_form'>";
    echo "<input type='submit' class='sub next' name='crt' value='".$lang_label["create_project"]."'>";
    echo "</form>";
}


?>