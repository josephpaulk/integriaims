<?php
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2010 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

include $config["homedir"]."/include/functions_graph.php";

$noinfo = 1;

if (!isset($config["id_user"]))
	$config["id_user"] = $_SESSION['id_usuario'];

///////////////
//Get queries to know if there is info or not
//////////////

// NEWS
$sql = "SELECT * FROM tnewsboard  WHERE (`date` > DATE_SUB(CURRENT_TIMESTAMP(), INTERVAL 60 DAY) OR `date` = '0000-00-00 00:00:00') ORDER BY date ASC";

$news = get_db_all_rows_sql ($sql);	

//AGENDA
$now = date('Y-m-d', strtotime("now"));
$now3 = date('Y-m-d', strtotime("now + 3 days"));
$agenda = get_db_sql ("SELECT COUNT(*) FROM tagenda WHERE  (id_user ='".$config["id_user"]."' OR public = 1) AND timestamp > '$now' AND timestamp < '$now3'");

$agenda  += get_db_sql ("SELECT COUNT(tproject.name) FROM trole_people_project, tproject WHERE trole_people_project.id_user = '".$config["id_user"]."' AND trole_people_project.id_project = tproject.id AND tproject.end >= '$now' AND tproject.end <= '$now3'");

$agenda += get_db_sql ("SELECT COUNT(ttask.name) FROM trole_people_task, ttask WHERE trole_people_task.id_user = '".$config["id_user"]."' AND trole_people_task.id_task = ttask.id AND ttask.end >= '$now' AND ttask.end <= '$now3'");	

//TODO
$todo = get_db_sql ("SELECT COUNT(*) FROM ttodo WHERE assigned_user = '".$config["id_user"]."'");

//PROJECTS
$projects = projects_active_user ($config["id_user"]);	

//INCIDENTS
$incidents = incidents_active_user ($config["id_user"]);

$info = false;

if ($news || $agenda || $todo || $projects || $incidents) {
	$info = true;
}

if ($info) {

	echo '<table class="landing_table">';
		
	echo "<tr>";
	echo "<th>";
	echo "<img class='landing_title_logo' src='images/error.png'/>";
	echo "<span class='landing_title'>";
	echo __('System newsboard');
	echo "</span>";
	echo "<span class='landing_subtitle'>";
	echo __('News of last 30 days');
	echo "</span>";
	echo "</th>";
	echo "<th>";
	echo "<img class='landing_title_logo' src='images/time.gif'/>";
	echo "<span class='landing_title'>";
	echo __("Agenda");
	echo "</span>";
	echo "<span class='landing_subtitle'>";
	echo __('First 5 events for next three days');
	echo "</span>";
	echo "<a href='index.php?sec=agenda&sec2=operation/agenda/agenda'>";
	echo "<img class='much_more' src='images/add.png'>";
	echo "</a>";
	echo "</th>";	
	echo "</tr>";

	// ==============================================================
	// Show Newsboard
	// ==============================================================

	echo "<tr>";
	echo "<td rowspan=3>";

	if ($news) {
		echo "<div class='landing_news landing_content'>";
		foreach ($news as $news_item) {
			echo "<span class='landing_news_title'>".$news_item["title"]."</span>";
			
			if ($news_item["date"] === "0000-00-00 00:00:00") {
				echo "<img class='landing_news_note' src='images/nota.gif'>";
			} else {
				echo ", <i>".substr($news_item["date"],0,10)."</i>";
			}
			echo "<hr><div style='margin-right: 20px; margin-left: 10px; margin-top: 10px; text-align: justify;'>";
			echo clean_output_breaks ($news_item["content"]);
			echo "<br><br></div>";
		}
		echo "</div>";
	} else {
		echo "<div class='landing_empty'>";
		echo __("There aren't news in the system");
		echo "</div>";
	}
		
	echo "</td>";

	echo "<td>";
	echo "<div class='landing_content'>";
	// ==============================================================
	// Show Agenda items
	// ==============================================================

	if ($agenda > 0){


		$time_array = array();
		$text_array = array();
		$type_array = array();
		$data = array();
		$count = 0;
		
		//Get agenda events
		$sql_2 = "SELECT * FROM tagenda WHERE (id_user ='".$config["id_user"]."' OR public = 1) AND timestamp > '$now' AND timestamp < '$now3' ORDER BY timestamp ASC";
		$result_2 = mysql_query($sql_2);
			
		while ($r = mysql_fetch_array($result_2)) {
			$time_array[$count] = $r["timestamp"];
			$text_array[$count] = $r["content"];
			$type_array[$count] = "agenda";
			$count++;
		}

		// Search for Project end in this date
		$sql = "SELECT tproject.name as pname, tproject.end as pend, tproject.id as idp FROM trole_people_project, tproject WHERE trole_people_project.id_user = '".$config["id_user"]."' AND trole_people_project.id_project = tproject.id AND tproject.end >= '$now' AND tproject.end <= '$now3' group by idp";
		$res = mysql_query ($sql);
		while ($row=mysql_fetch_array ($res)){
			$pname = $row["pname"];
			$idp = $row["idp"];
			
			$content = "<a href='index.php?sec=projects&sec2=operation/projects/task&id_project=$idp'>".$pname."</a>";
			
			$time_array[$count] = $row["pend"];
			$text_array[$count] = $content;
			$type_array[$count] = "project";
			$count++;
		}

		// Search for Task end in this date
		$sql = "SELECT ttask.name as tname, ttask.end as tend, ttask.id as idt FROM trole_people_task, ttask WHERE trole_people_task.id_user = '".$config["id_user"]."' AND trole_people_task.id_task = ttask.id AND ttask.end >= '$now' AND ttask.end <= '$now3' group by idt";
		$res = mysql_query ($sql);
		while ($row=mysql_fetch_array ($res)){
			$tname = $row["tname"];
			$idt = $row["idt"];
			$tend = $row["tend"];
			
			$content = "<a href='index.php?sec=projects&sec2=operation/projects/task_detail&id_task=$idt&operation=view'>".$tname."</a>";
			
			$time_array[$count] = $row["tend"];
			$text_array[$count] = $content;
			$type_array[$count] = "task";
			$count++;
		}
		
		//Sort time array and print only first five entries :)
		asort($time_array);
		
		echo "<p class='landing_text_list'>";
		
		$print_counter = 0;
		foreach ($time_array as $key => $time) {
			
			$type_name = "";
			switch ($type_array[$key]) {
				case "agenda":
					$type_name = __("Agenda event");
					break;
				case "project":
					$type_name = __("Project end");
					break;
				case "task":
					$type_name = __("Task end");
					break;
			}
			
			echo "<b>".$type_name."</b> - [".$time."] ".$text_array[$key]."<br>";
			
			$print_counter++;
			if ($print_counter == 5) {
				break;
			}
		}
		
		echo "</p>";
		
	} else {
		echo "<div class='landing_empty'>";
		echo __("There aren't meetings in your agenda");
		echo "</div>";
	}
	echo "</div>";
	echo "</td>";
	echo "</tr>";

	// ==============================================================
	// Show Todo items
	// ==============================================================
	echo "<tr>";
	echo "<th>";
	echo "<img class='landing_title_logo' src='images/todo.png'/>";
	echo "<span class='landing_title'>";
	echo __('To-Do');
	echo "</span>";
	echo "<span class='landing_subtitle'>";
	echo __('Total active TO-DOs').": ".todos_active_user ($config["id_user"]);
	echo "</span>";
	echo "<a href='index.php?sec=todo&sec2=operation/todo/todo'>";
	echo "<img class='much_more' src='images/add.png'>";
	echo "</a>";
	echo "</th>";
	echo "</tr>";

	echo "<tr>";
	echo "<td>";
	echo "<div class='landing_content'>";
	if ($todo > 0){
		
		echo "<p class='landing_text_list'><ul>";
		$sql_2 = "SELECT * FROM ttodo WHERE assigned_user = '".$config["id_user"]."' ORDER BY priority DESC limit 5";
		$result_2 = mysql_query($sql_2);
		while ($row_2 = mysql_fetch_array($result_2)){
			echo "<a href='index.php?sec=todo&sec2=operation/todo/todo&operation=update&id=".$row_2["id"]."'>";
			echo "<li>".substr($row_2["name"],0,55);
			echo "</a>";
			echo "<br>";
		}
		
		echo "</ul></p>";
	} else {
		echo "<div class='landing_empty'>";
		echo __("There aren't TO-DOs");
		echo "</div>";
	}
	echo "</div>";
	echo "</td>";
	echo "</tr>";

	echo "<tr>";
	echo "<th>";
	echo "<img class='landing_title_logo' src='images/reporting.png'/>";
	echo "<span class='landing_title'>";
	echo __('Projects');
	echo "</span>";
	echo "<span class='landing_subtitle'>";
	echo __("Total active projects").": ";
	echo projects_active_user ($config["id_user"]);
	echo "</span>";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/project'>";
	echo "<img class='much_more' src='images/add.png'>";
	echo "</a>";
	echo "</th>";

	echo "<th>";
	echo "<img class='landing_title_logo' src='images/incidents.png'/>";
	echo "<span class='landing_title'>";
	echo __('Incidents');
	echo "</span>";
	echo "<span class='landing_subtitle'>";
	echo __('Total active incidents').": ".incidents_active_user ($config["id_user"]);
	echo "</span>";
	if($simple_mode) {
		echo "<a href='index.php?sec=incidents&sec2=operation/incidents_simple/incidents'>";
		echo "<img class='much_more' src='images/add.png'>";
		echo "</a>";
	}
	else {
		echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident'>";
		echo "<img class='much_more' src='images/add.png'>";
		echo "</a>";
	}
	echo "</th>";
	echo "<tr>";


	echo "<td>";
	// ==============================================================
	// Show Projects items
	// ==============================================================
	
	if ($projects > 0){
	
		$from_one_month = date('Y-m-d', strtotime("now - 1 month"));

		$graph_result = graph_workunit_project_user (600, 200, $config["id_user"], $from_one_month,0, 1);

		//If there is an error in graph the graph functions returns a string
		echo "<div class='landing_empty'>";
		echo $graph_result;
		echo "</div>";		
		
	} else {
	
		echo "<div class='landing_empty'>";
		echo __("There aren't active projects");
		echo "</div>";	
	}
	echo "</td>";

	// ==============================================================
	// Show Incident items
	// ==============================================================

	echo "<td>";
	echo "<div class='landing_content'>";
	if ($incidents > 0){
		
		$sql_2 = "SELECT * FROM tincidencia WHERE (id_creator = '".$config["id_user"]."' OR id_usuario = '".$config["id_user"]."') AND estado IN (1,2,3,4,5) ORDER BY actualizacion DESC limit 5";
		
		$result_2 = mysql_query($sql_2);
		if ($result_2){
			echo "<table width=100% class='landing_incidents'>";
			echo "<tr><th>"._("Status")."</th><th>".__("Priority")."</th><th>".__("Updated")."</th><th>".__("Incident")."</th><th>".__("Last WU")."</th></tr>";
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
			if($simple_mode) {
				echo "<a href='index.php?sec=incidents&sec2=operation/incidents_simple/incident&id=$idi'>";
			}
			else {
				echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident&id=$idi'>";
			}
			echo $row_2["titulo"];
			echo "</b></a>";
			echo "</td>";
			echo "<td style='font-size: 10px'>";
			$last_wu = get_incident_lastworkunit ($idi);
			echo $last_wu["id_user"];

			echo "</td></tr>";
		}
		if (isset($row_2))
			echo "</table>";
	} else {
		echo "<div class='landing_empty'>";
		echo __("There aren't active incidents");
		echo "</div>";		
	}
	echo "</div>";
	echo "</td>";
	echo "</tr>";

	echo "</table>";

} else {

	 if (give_acl ($config["id_user"], 0, "AR")){
		include "operation/agenda/agenda.php";
	 } else {
		echo "<h1>". __("Welcome to Integria")."</h1>";
	 }

}
?>


<script>
//Animate content
$(document).ready(function (){
	$(".landing_content").hide();
	$(".landing_content").slideDown('slow');
});
</script>
