<?php

// INTEGRIA - OpenSource Management for the Enterprise
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007 Sancho Lerena, slerena@gmail.com

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

if (!isset($config["id_user"]))
    $config["id_user"] = $_SESSION['id_usuario'];

	echo "<table width=100% border=0>";
	
	// Show Agenda items
	echo "<tr><td>";
	echo "<h1>".$lang_label["agenda"]."</h1>";
	echo "<div align='center' style='height: 160px; width: 130px; padding: 0 0 0 0; margin: 0 0 0 0;'>";
	echo "<a href='index.php?sec=agenda&sec2=operation/agenda/agenda'><img src='images/calendar.png' border=0></A></div>";
	echo "<td valign='top'><br><b>";
    echo lang_string("Events for next three days");
    echo "<hr width=500 size=1>";
    echo "</b><br><br>";
    $now = date('Y-m-d', strtotime("now"));
    $now3 = date('Y-m-d', strtotime("now + 3 days"));
    $sql_2 = "SELECT * FROM tagenda WHERE (id_user ='".$config["id_user"]."' OR public = 1) AND timestamp > '$now' AND timestamp < '$now3' ORDER BY timestamp ASC";
    $result_2 = mysql_query($sql_2);
    while ($row_2 = mysql_fetch_array($result_2)){
        echo $row_2["timestamp"]." - ".$row_2["content"];
        echo "<br>";
    }
    


	// Show Todo items
	echo "<tr><td>";
	echo "<h1>".$lang_label["todo"]."</h1>";
	echo "<div align='center' style='height: 160px; width: 130px; padding: 0 0 0 0; margin: 0 0 0 0;'>";
	echo "<a href='index.php?sec=todo&sec2=operation/todo/todo'><img src='images/todo.png' border=0></a></div>";
    echo "<td valign='top'><br><b>";
    echo lang_string("Todo active you have")." : ".todos_active_user ($config["id_user"])."</b><br>";
    echo "<hr width=500 size=1><br><br>";
    $sql_2 = "SELECT * FROM ttodo WHERE assigned_user = '".$config["id_user"]."' ORDER BY priority DESC limit 5";
    $result_2 = mysql_query($sql_2);
    while ($row_2 = mysql_fetch_array($result_2)){
        echo $row_2["timestamp"]." - ".substr($row_2["name"],0,55);
        echo "<br>";
    }
    echo "...";

	// Show Projects items
	echo "<tr><td>";
	echo "<h1>".$lang_label["projects"]."</h1>";
	echo "<div align='center' style='height: 160px; width: 130px; padding: 0 0 0 0; margin: 0 0 0 0;'>";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/project'><img src='images/project.png' border=0></a></div>";
	
    echo "<td valign='top'><br><b>";
    echo lang_string("Projects active you have")." : ".projects_active_user ($config["id_user"]);
    echo "<hr width=500 size=1>";
    echo "<br>";
    $from_one_month = date('Y-m-d', strtotime("now - 1 month"));
    echo "<img src='include/functions_graph.php?type=workunit_project_user&width=350&height=210&id_user=".$config["id_user"]."&date_from=$from_one_month'>";

	// Show Incident items
	echo "<tr><td>";
	echo "<h1>".$lang_label["incidents"]."</h1>";
	echo "<div align='center' style='height: 160px; width: 130px; padding: 0 0 0 0; margin: 0 0 0 0;'>";
	echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident'><img src='images/incidents.png' border=0></A></div>";
    echo "<td valign='top'><br><b>";
    echo lang_string("Incidents active you have")." : ".incidents_active_user ($config["id_user"]);
    echo "<hr width=500 size=1>";
    echo "</b><br><br>";
     $sql_2 = "SELECT * FROM tincidencia WHERE id_usuario = '".$config["id_user"]."' AND estado IN (1,2,3,4,5) ORDER BY actualizacion DESC limit 5";
    $result_2 = mysql_query($sql_2);
    while ($row_2 = mysql_fetch_array($result_2)){
        echo $row_2["actualizacion"]." - ".substr($row_2["titulo"],0,55);
        echo "<br>";
    }
    echo "...";

	echo "</table>";

?>
