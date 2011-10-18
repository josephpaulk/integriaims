<?php
// Integria IMS - http://integria.sourceforge.net
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

include "include/functions_graph.php";
require_once ('include/functions_html.php');
require_once ('include/functions_db.php');

global $config;
$id_user = $config["id_user"];

check_login ();

if (defined ('AJAX')) {

	global $config;

	$search_users = (bool) get_parameter ('search_users');
	
	if ($search_users) {
		require_once ('include/functions_db.php');
		
		$id_user = $config['id_user'];
		$string = (string) get_parameter ('q'); /* q is what autocomplete plugin gives */
		
		$filter = array ();
		
		$filter[] = '(nombre COLLATE utf8_general_ci LIKE "%'.$string.'%" OR direccion LIKE "%'.$string.'%" OR comentarios LIKE "%'.$string.'%")';

		$filter[] = 'id_usuario != '.$id_user;
		
		$users = get_user_visible_users ($config['id_user'],"IR", false);
		if ($users === false)
			return;
		
		foreach ($users as $user) {
			echo $user['id_usuario'] . "|" . $user['nombre_real']  . "\n";
		}
		
		return;
 	}
	return;
}

$user_id = get_parameter ('user_id', $config["id_user"]);

if (($user_id != $config["id_user"]) AND (!give_acl ($config["id_user"], 0, "IM")) AND (!give_acl 
($config["id_user"], 0, "PM"))) {
	audit_db("Noauth", $config["REMOTE_ADDR"], "Unauthorized access", "Trying to access full user report");
	require ("general/noaccess.php");
	exit;
}


$now = date ('Y-m-d');
$start_date = get_parameter ("start_date", date ('Y-m-d', strtotime ("$now - 3 months")));
$end_date = get_parameter ('end_date', $now);
$user_id = get_parameter ('user_id', $config["id_user"]);

$total_time = 0;
$total_global = 0;
$incident_time = 0;

echo "<h3>";
echo __("Full report");
if ($user_id != "") {
	echo " &raquo; ";
	echo dame_nombre_real ($user_id);
}

if ($clean_output == 0){
    // link full screen
    echo "&nbsp;&nbsp;<a title='Full screen' href='index.php?sec=users&sec2=operation/user_report/report_full&user_id=$user_id&end_date=$end_date&start_date=$start_date&clean_output=1'>";
    echo "<img src='images/html.png'>";
    echo "</a>";

    // link PDF report
    echo "&nbsp;&nbsp;<a title='PDF report' href='index.php?sec=users&sec2=operation/user_report/report_full&user_id=$user_id&end_date=$end_date&start_date=$start_date&clean_output=1&pdf_output=1'>";
    echo "<img src='images/page_white_acrobat.png'>";
    echo "</a>";
}

echo  "</h3>";

if ($clean_output == 0){
    echo "<form method='post' action='index.php?sec=users&sec2=operation/user_report/report_full'>";
    echo "<table class='blank' style='margin-left: 10px' width='90%'>";
    echo "<tr><td>";
    echo __("Username") ."<br>";
    if (give_acl($config["id_user"], 0, "PM"))
       //combo_user_visible_for_me ($user_id, 'user_id', 0, 'PM');
		echo print_input_text_extended ('user_id', '', 'text-user_id', '', 15, 30, false, '',
			array('style' => 'background: url(' . $src_code . ') no-repeat right;'), true, '', '')
		. print_help_tip (__("Type at least two characters to search"), true);
    else
        echo $config["id_user"];

    echo "</td><td>";
    echo __("Begin date")."<br>";
    print_input_text ('start_date', $start_date, '', 10, 20);	
    echo "</td><td>";
    echo __("End date")."<br>";;
    print_input_text ('end_date', $end_date, '', 10, 20);	
    echo "</td><td valign='bottom'>";
    print_submit_button (__('Show'), 'show_btn', false, 'class="next sub"');
    echo "</form>";
    echo "</table>";
}

if ($user_id == "") {
	echo "<h3>";
	echo __("There is no data to show");
	echo "</h3>";
} else {
	echo "<h3>".__("Project report")."</h3>";
	echo '<table width="90%" class="listing">';
	echo "<th>".__('Project');
	echo "<th>".__('User hours');
	echo "<th>".__('Project total');
	echo "<th>".__('%');

	$sql = sprintf ('SELECT tproject.id as id, tproject.name as name, SUM(tworkunit.duration) AS sum
		FROM tproject, ttask, tworkunit_task, tworkunit
		WHERE tworkunit.id_user = "%s"
		AND tworkunit_task.id_workunit = tworkunit.id
		AND tworkunit_task.id_task = ttask.id
		AND ttask.id_project = tproject.id
		AND tworkunit.timestamp >= "%s"
		AND tworkunit.timestamp <= "%s"
		GROUP BY tproject.name',
		$user_id, $start_date, $end_date);
	$projects = get_db_all_rows_sql ($sql);
	
	

	if ($projects) {
		foreach ($projects as $project) {
			$total_project = get_project_workunit_hours ($project['id'], 0, $start_date, $end_date);
			
			echo "<tr style='border-top: 1px solid #ccc'>";
			echo "<td>";
			echo '<a href="index.php?sec=projects&sec2=operation/projects/task&id_project='.$project['id'].'">';
			echo '<strong>'.$project['name'].'</strong>';
			echo "</a>";
			echo "<td>";
			echo $project['sum'];
			$total_time += $project['sum'];
			echo "<td>";	
			echo $total_project;
			$total_global  += $total_project;
			echo "<td>";
			if ($total_project > 0)
				echo format_numeric ($project['sum'] / ($total_project / 100) )."%";
			else
				echo '0%';
			
			$sql = sprintf ('SELECT ttask.id as id, ttask.name as name, SUM(tworkunit.duration) as sum
				FROM tproject, ttask, tworkunit_task, tworkunit
				WHERE tworkunit.id_user = "%s"
				AND tworkunit_task.id_workunit = tworkunit.id
				AND ttask.id_project = %d
				AND tworkunit_task.id_task = ttask.id
				AND ttask.id_project = tproject.id
				AND tworkunit.timestamp >= "%s"
				AND tworkunit.timestamp <= "%s"
				GROUP BY ttask.name',
				$user_id, $project['id'], $start_date, $end_date);
			$tasks = get_db_all_rows_sql ($sql);
			if ($tasks) {
				foreach ($tasks as $task) {
					$total_task = get_task_workunit_hours ($task['id']);
	
					echo "<tr>";
					echo "<td>&nbsp;&nbsp;&nbsp;<img src='images/copy.png'>";
                    echo "<a href='index.php?sec=users&sec2=operation/users/user_workunit_report&timestamp_l=$start_date&timestamp_h=$end_date&id=$user_id&id_task=".$task['id']."'>";

					echo $task['name'];
					echo "</a>";
					echo "<td>";
					echo $task['sum'];
					echo "<td>";	
					echo $total_task;
					echo "<td>";
					if ($total_task > 0)
						echo format_numeric ($task['sum'] / ($total_task / 100))."%";
					else
						echo '0%';
				}
			}
		}
	}
	echo "<tr style='border-top: 2px solid #ccc'>";
	echo "<td><b>".__("Totals")."</b>";
	echo "<td colspan=3>";
	echo $total_time. " (". get_working_days ($total_time). " ".__("Working days").")";
	echo "&nbsp;&nbsp;&nbsp; $total_global (". get_working_days ($total_global). " ".__("Working days").")";

	echo "</table>";

if ($total_time > 0){
	echo "<h3>". __("Project graph report")."</h3>";
	echo graph_workunit_user (800, 350, $user_id, $start_date, $end_date, 1);
	echo "<br><br>";
}

// Incident report

echo "<h3>".__("Incident report")."</h3>";

	$sql = sprintf ('SELECT tincidencia.score as score, tincidencia.id_incidencia as iid, tincidencia.estado as istatus, tincidencia.titulo as title, tincidencia.id_grupo as id_group, tincidencia.id_creator as creator, tincidencia.id_usuario as owner, tincidencia.inicio as date_start, tincidencia.cierre as date_end, SUM(tworkunit.duration) as `suma`  
		FROM tincidencia, tworkunit_incident, tworkunit
		WHERE tworkunit.id_user = "%s"
		AND tworkunit_incident.id_workunit = tworkunit.id
		AND tworkunit_incident.id_incident = tincidencia.id_incidencia  
		AND tworkunit.timestamp >= "%s" 
		AND tworkunit.timestamp <= "%s 23:59:59" 
		GROUP BY title',
		$user_id, $start_date, $end_date);

	$incidencias = get_db_all_rows_sql ($sql);

	if (sizeof($incidencias) == 1){
		echo "<h3>";
		echo __("There is no data to show");
		echo "</h3>";
	} else {
			
		echo '<table width="90%" class="listing">';
		echo "<th>".__('Incident');
		echo "<th>".__('Group');
		echo "<th>".__('Creator')."<br>".__('Owner');
		echo "<th>".__('Status');
		echo "<th>".__('Dates');
		echo "<th>".__('User hours');
		echo "<th>".__('Total hours');
        echo "<th>".__('Score');

		$incident_totals = 0;
		$incident_user = 0;
        $incident_graph = array();

		if ($incidencias) {
			foreach ($incidencias as $incident) {
				// $total_project = get_project_workunit_hours ($project['id'], 0, $start_date, $end_date);
	
                $incident_graph[$incident["title"]] = $incident["suma"];

				echo "<tr>";
				echo "<td>".$incident["title"];
				echo "<td>".dame_grupo($incident["id_group"]);
				echo "<td class=f9>".$incident["creator"]."<br>".$incident["owner"];
				$status = get_indicent_status();
				echo "<td>".$status[$incident["istatus"]];
				echo "<td class=f9>".substr($incident["date_start"],0,11). " <br>" .substr($incident["date_end"],0,11);
				echo "<td>".$incident["suma"];
				$incident_user  += $incident["suma"];
				$this_incident = get_incident_workunit_hours($incident["iid"]);
				echo "<td>". $this_incident;
				$incident_totals +=  $this_incident;
                echo "<td>";
                if (give_acl ($config["id_user"], 0, "IM"))
                    echo $incident["score"];
                else
                    echo "N/A";
	
			}
        	echo "<tr style='border-top: 2px solid #ccc'>";
			echo "<td><b>".__("Totals")."</b>";
			echo "<td colspan=7>";
			echo dame_nombre_real ($user_id). ": ". $incident_user. " ( ". get_working_days ($incident_user). " ".__("Working days"). ")";

			echo "&nbsp;&nbsp;&nbsp;".__('Total'). " : ". $incident_totals." ( ". get_working_days ($incident_totals). " ".__("Working days").")";

            
		}
		echo "</table>";
	}
}

if ($incident_graph){
	echo "<h3>". __("Incident graph report")."</h3>";
    echo "<div>";
    echo pie3d_graph ($config['flash_charts'], $incident_graph, 500, 300, __('others'), "", "", $config['font'], $config['fontsize']);
    echo "</div>";
}


?>
<script type="text/javascript" src="include/js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
<script type="text/javascript" src="include/js/integria_date.js"></script>
<script type="text/javascript" src="include/js/jquery.autocomplete.js"></script>


<script type="text/javascript">

$(document).ready (function () {
	configure_range_dates (null);
	$("#text-user_id").autocomplete ("ajax.php",
		{
			scroll: true,
			minChars: 2,
			extraParams: {
				page: "operation/user_report/report_full",
				search_users: 1,
				id_user: "<?php echo $config['id_user'] ?>"
			},
			formatItem: function (data, i, total) {
				if (total == 0)
					$("#text-user_id").css ('background-color', '#cc0000');
				else
					$("#text-user_id").css ('background-color', '');
				if (data == "")
					return false;
				return data[0]+'<br><span class="ac_extra_field"><?php echo __(" ") ?>: '+data[1]+'</span>';
			},
			delay: 200

		});
});
</script>
