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

include $config["homedir"]."/include/functions_graph.php";


if (!isset($config["id_user"]))
	$config["id_user"] = $_SESSION['id_usuario'];

	echo '<table width="100%" cellspacing=0 cellpadding=0 border=0>';


    // ==============================================================
	// Show Newsboard
    // ==============================================================

	$sql = "SELECT * FROM tnewsboard  WHERE `date` > DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL 31 DAY) ORDER BY date ASC";
    $news = get_db_all_rows_sql ($sql);
    if ($news) {

        echo "<tr><td>";
	    echo "<h1>".__('System newsboard')."</h1>";
	    echo "<div align='center' style='height: 160px; width: 130px; padding: 0 0 0 0; margin: 0 0 0 0;'>";
	    echo "<img src='images/warning.png'></div>";
	    echo "<td valign='top'><br><b>";
	    echo __('Latest system news (30 days)');
	    echo '<hr width="100%" size="1">';
	    echo "</b>";

        foreach ($news as $news_item) {
        	echo "<b>".$news_item["title"]."</b>, <i>".$news_item["date"]."</i>";
		    echo "<br>";
            echo $news_item["content"];
		    echo "<br><br>";
	    }
    }
    
    // ==============================================================
	// Show Agenda items
    // ==============================================================

	$now = date('Y-m-d', strtotime("now"));
	$now3 = date('Y-m-d', strtotime("now + 3 days"));
	$agenda = get_db_sql ("SELECT COUNT(*) FROM tagenda WHERE  (id_user ='".$config["id_user"]."' OR public = 1) AND timestamp > '$now' AND timestamp < '$now3'");

	$agenda  += get_db_sql ("SELECT COUNT(tproject.name) FROM trole_people_project, tproject WHERE trole_people_project.id_user = '".$config["id_user"]."' AND trole_people_project.id_project = tproject.id AND tproject.end >= '$now' AND tproject.end <= '$now3'");

	$agenda += get_db_sql ("SELECT COUNT(ttask.name) FROM trole_people_task, ttask WHERE trole_people_task.id_user = '".$config["id_user"]."' AND trole_people_task.id_task = ttask.id AND ttask.end >= '$now' AND ttask.end <= '$now3'");

	if ($agenda > 0){
		echo "<tr><td>";
		echo "<h1>".__('Agenda')."</h1>";
		echo "<div align='center' style='height: 160px; width: 130px; padding: 0 0 0 0; margin: 0 0 0 0;'>";
		echo "<a href='index.php?sec=agenda&sec2=operation/agenda/agenda'><img src='images/calendar.png'></a></div>";
		echo "<td valign='top'><br><b>";
		echo __('Events for next three days');
		echo '<hr width="100%" size="1">';
		echo "</b>";
		$sql_2 = "SELECT * FROM tagenda WHERE (id_user ='".$config["id_user"]."' OR public = 1) AND timestamp > '$now' AND timestamp < '$now3' ORDER BY timestamp ASC";
		$result_2 = mysql_query($sql_2);
		while ($row_2 = mysql_fetch_array($result_2)){
			echo "<b>".__("Agenda event")." </b>(".$row_2["timestamp"].") - ".$row_2["content"];
			echo "<br>";
		}

		// Search for Project end in this date
		$sql = "SELECT tproject.name as pname, tproject.end as pend, tproject.id as idp FROM trole_people_project, tproject WHERE trole_people_project.id_user = '".$config["id_user"]."' AND trole_people_project.id_project = tproject.id AND tproject.end >= '$now' AND tproject.end <= '$now3' group by idp";
		$res = mysql_query ($sql);
		while ($row=mysql_fetch_array ($res)){
			$pname = $row["pname"];
			$idp = $row["idp"];
			$pend = $row["pend"];
			echo "<b>".__("Project end"). "</b> (".$pend."): ";
			echo "<a href='index.php?sec=projects&sec2=operation/projects/task&id_project=$idp'>";
			echo $pname;
			echo "</a>";
			echo "<br>";
		}

		// Search for Task end in this date
		$sql = "SELECT ttask.name as tname, ttask.end as tend, ttask.id as idt FROM trole_people_task, ttask WHERE trole_people_task.id_user = '".$config["id_user"]."' AND trole_people_task.id_task = ttask.id AND ttask.end >= '$now' AND ttask.end <= '$now3' group by idt";
		$res = mysql_query ($sql);
		while ($row=mysql_fetch_array ($res)){
			$tname = $row["tname"];
			$idt = $row["idt"];
			$tend = $row["tend"];
			echo "<b>".__("Task end"). "</b> (". $tend."): ";
			echo "<a href='index.php?sec=projects&sec2=operation/projects/task_detail&id_task=$idt&operation=view'>";
			echo $tname;
			echo "</a>";
			echo "<br>";
		}

	}


    // ==============================================================
	// Show Todo items
    // ==============================================================

	$todo = get_db_sql ("SELECT COUNT(*) FROM ttodo WHERE assigned_user = '".$config["id_user"]."'");
	if ($todo > 0){
		echo "<tr><td>";
		echo "<h1>".__('To-Do')."</h1>";
		echo "<div align='center' style='height: 160px; width: 130px; padding: 0 0 0 0; margin: 0 0 0 0;'>";
		echo "<a href='index.php?sec=todo&sec2=operation/todo/todo'><img src='images/todo.png'></a></div>";
		echo "<td valign='top'><br><b>";
		echo __('Todo active you have').": ".todos_active_user ($config["id_user"])."</b><br>";
		echo '<hr width="100%" size="1">';
		$sql_2 = "SELECT * FROM ttodo WHERE assigned_user = '".$config["id_user"]."' ORDER BY priority DESC limit 5";
		$result_2 = mysql_query($sql_2);
		while ($row_2 = mysql_fetch_array($result_2)){
			echo $row_2["timestamp"]." - ".substr($row_2["name"],0,55);
			echo "<br>";
		}
	}

    // ==============================================================
	// Show Incident items
    // ==============================================================

	$incidents = incidents_active_user ($config["id_user"]);
	if ($incidents > 0){
		echo "<tr><td>";
		echo "<h1>".__('Incidents')."</h1>";
		echo "<div align='center' style='height: 160px; width: 130px; padding: 0 0 0 0; margin: 0 0 0 0;'>";
		echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident'><img src='images/incidents.png'></a></div>";
		echo "<td valign='top'><br><b>";
		echo __('Incidents active you have').": ".incidents_active_user ($config["id_user"]);
		echo '<hr width="100%" size="1">';
		echo "</b>";
		 $sql_2 = "SELECT * FROM tincidencia WHERE id_creator = '".$config["id_user"]."' OR id_usuario = '".$config["id_user"]."' AND estado IN (1,2,3,4,5) ORDER BY actualizacion DESC limit 5";
		$result_2 = mysql_query($sql_2);
		if ($result_2){
			echo "<table width=100% class=listing>";
			echo "<tr><th>"._("Status")."</th><th>".__("Priority")."</th><th>".__("Updated")."</th><th>".__("Incident")."</th><th>".__("Last workunit by")."</th></tr>";
		}
		while ($row_2 = mysql_fetch_array($result_2)){
			$idi = $row_2["id_incidencia"];
			echo "<tr><td>";
			echo render_status($row_2["estado"]);
			echo "<td>";
			print_priority_flag_image ($row_2['prioridad']);
			echo "<td>";
			echo human_time_comparation ($row_2["actualizacion"]);
			echo "<td>";
			echo "<i>(#".$row_2["id_incidencia"].") </i>";
			echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident&id=$idi'>";
			echo "<b>&nbsp;".$row_2["titulo"];
			echo "</b></a>";
			echo "</td>";
			echo "<td>";
			$last_wu = get_incident_lastworkunit ($idi);
			echo $last_wu["id_user"];

			echo "</td></tr>";
		}
		if (isset($row_2))
			echo "</table>";
	}

// ==============================================================
	// Show Projects items
    // ==============================================================

	$projects = projects_active_user ($config["id_user"]);
	if ($projects > 0){
		echo "<tr><td>";
		echo "<h1>".__('Projects')."</h1>";
		echo "<div align='center' style='height: 160px; width: 130px; padding: 0 0 0 0; margin: 0 0 0 0;'>";
		echo "<a href='index.php?sec=projects&sec2=operation/projects/project'><img src='images/project.png'></a></div>";


		echo "<td valign='top'><br><b>";
		echo __('Projects active you have').": ".projects_active_user ($config["id_user"]);
		echo '<hr width="100%" size="1">';
		echo "<br>";
		$from_one_month = date('Y-m-d', strtotime("now - 1 month"));

		echo graph_workunit_project_user (600, 200, $config["id_user"], $from_one_month,0, 1);

	}

	echo "</table>";

?>
<script language="JavaScript" src="include/FusionCharts/FusionCharts.js"></script>
