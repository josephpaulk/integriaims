<?php
// Integria IMS - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2012 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2012 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// Load global vars



global $config;
$id_user = $config["id_user"];

check_login ();

include "include/functions_graph.php";
require_once ('include/functions_html.php');
require_once ('include/functions_db.php');
require_once ('include/functions_user.php');

if($pdf_output == 1) {
	$ttl = 2;
}
else {
	$ttl = 1;
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
$user_id = get_parameter ('user_id', "");

$resolution = get_parameter ("incident_resolution", 0);
$id_group = get_parameter ("search_id_group", 0);
$author = get_parameter ("author", "");
$editor = get_parameter ("editor", "");
$status = get_parameter ("search_status", 0);
$wu_reporter = get_parameter ("wu_reporter", "");
$only_projects = get_parameter ("only_projects", 0);
$only_summary = get_parameter ("only_summary", 0);
$id_group_creator = get_parameter ("id_group_creator", 0);


$total_time = 0;
$total_global = 0;
$incident_time = 0;

// If match incident is 0 we do a global scan of tasks, if is 1, tasks will depend on
// results from the incident match. This is set to 1 when select a specific field
// for incident match (origin, resolution, group, author, editor, status, inventory object, or contract

$do_search = 0;
 
if ($only_summary == 1){
    $do_search = 1;
}

if ($wu_reporter != ""){
	$do_search = 1;
}

if ($id_group_creator > 0){
	$do_search = 1;
}


if ($resolution > 0){
	$do_search = 1;
}

if ($editor != ""){
	$do_search = 1;
}

if ($author != ""){
	$do_search = 1;
}

if ($status > 0){
	$do_search = 1;
}

if ($id_group > 1){
	$do_search = 1;
}

if ($user_id != ""){
	$do_search = 1;
}

echo "<h1>";
echo __("Full report");
if ($user_id != "") {
	echo " &raquo; ";
	echo dame_nombre_real ($user_id);
}

if ($clean_output == 0){
    // link full screen
    echo "&nbsp;&nbsp;<a title='Full screen' href='index.php?sec=users&sec2=operation/user_report/report_full&user_id=$user_id&end_date=$end_date&start_date=$start_date&clean_output=1&user_id=$user_id&incident_resolution=$resolution&search_id_group=$id_group&author=$author&editor=$editor&search_status=$status&wu_reporter=$wu_reporter&only_projects=$only_projects'>";
    echo "<img src='images/html.png'>";
    echo "</a>";

    // link PDF report
    echo "&nbsp;&nbsp;<a title='PDF report' href='index.php?sec=users&sec2=operation/user_report/report_full&user_id=$user_id&end_date=$end_date&start_date=$start_date&clean_output=1&pdf_output=1&user_id=$user_id&incident_resolution=$resolution&search_id_group=$id_group&author=$author&editor=$editor&search_status=$status&wu_reporter=$wu_reporter&only_projects=$only_projects'>";
    echo "<img src='images/page_white_acrobat.png'>";
    echo "</a>";
}

echo  "</h1>";

if ($clean_output == 0){
    echo "<form method='post' action='index.php?sec=users&sec2=operation/user_report/report_full'>";
    echo "<table class='search-table-button' style='margin-left: 10px' width='99%'>";


    echo "<tr><td>";
    echo print_label (__("Workunit Reporter"), '', true);

	$params['input_id'] = 'text-user_id4';
	$params['input_name'] = 'wu_reporter';
	$params['return'] = false;
	$params['return_help'] = false;
	$params['input_value'] = $wu_reporter;
	user_print_autocomplete_input($params);
   
    echo "</td><td>"; 
    echo print_label (__("Begin date"), '', true);
    print_input_text ('start_date', $start_date, '', 10, 20);	
    echo "</td><td>";
    echo print_label (__("End date"), '', true);
    print_input_text ('end_date', $end_date, '', 10, 20);	


    echo "<tr><td>";
    echo print_checkbox ('only_projects', 1, $only_projects, true, __('Project search'));


    echo "<td>";
    echo print_checkbox ('only_summary', 1, $only_summary, true, __('Show only summary'));

    echo "<td>";
    echo print_select (get_user_groups (), 'id_group_creator', $id_group_creator, '', __('All'), 1, true, false, false, __('Creator group'));

    echo "<tr><td>";
    echo print_label (__("User"), '', true);

	$params_user['input_id'] = 'text-user_id';
	$params_user['input_name'] = 'user_id';
	$params_user['return'] = false;
	$params_user['return_help'] = false;
	
	user_print_autocomplete_input($params_user);

    echo "<td>";
    echo print_label (__("Incident creator"), '', true);
    
	$params_creator['input_id'] = 'text-user_id2';
	$params_creator['input_name'] = 'author';
	$params_creator['return'] = false;
	$params_creator['return_help'] = false;
	
	user_print_autocomplete_input($params_creator);

    echo "<td>";
    echo print_label (__("Incident editor"), '', true);

	$params_editor['input_id'] = 'text-user_id3';
	$params_editor['input_name'] = 'editor';
	$params_editor['return'] = false;
	$params_editor['return_help'] = false;
	
	user_print_autocomplete_input($params_editor);
	
    echo "<tr><td>";
    echo print_select (get_user_groups (), 'search_id_group', $id_group, '', __('All'), 1, true, false, false, __('Group'));

    echo "<td>";
    echo combo_incident_resolution ($resolution, false, true);

    echo "<tr><td>";
    combo_project_user ($id_project, $config["id_user"], 0, false);

    echo "<td>";
    echo combo_task_user_participant ($config["id_user"], true, 0, true, __('Task'));

    echo "<td>";
    $available_status = get_indicent_status();
    $available_status[-10] = __("Not closed");

    echo print_select ($available_status,
                'search_status', $status,
                '', __('Any'), 0, true, false, true,
                __('Status'));

    
    // TODO: Meter aqui inventario, con un control nuevo, tipo AJAX similar al de los usuarios.

    echo "<tr><td colspan=3 align=right>";
    print_submit_button (__('Show'), 'show_btn', false, 'class="sub zoom"');
    echo "</form>";
    echo "</table>";
}

if ($do_search  == 0){
	echo "<h3>";
	echo __("There is no data to show");
	echo "</h3>";
} else {

    if ($only_projects == 0)
    	echo "<h3>".__("Project report on related incidents")."</h3>";
    else
    	echo "<h3>".__("Project report")."</h3>";

    echo '<table width="99%" class="listing"><tr>';
    if ($only_summary == 0){
	    echo "<th>".__('Project')."</th>";
	    echo "<th>".__('User hours')."</th>";
	    echo "<th>".__('Project total')."</th>";
	    echo "<th>".__('%')."</th>";
	    echo "</tr>";
    }

	$incident_selector = "";
	$task_selector = "";
    
    // Search project data related on an incident match, not regular project info
    if ($only_projects == 0) {

		$sql = "SELECT id_incidencia, id_task FROM tincidencia WHERE 1=1 ";

		if ($user_id != "")
			$sql = $sql . " AND id_usuario = '$user_id' ";

		if ($resolution > 0)
                        $sql = $sql . " AND resolution = $resolution ";

		if ($author != "")
			$sql = $sql . " AND id_creator = '$author' ";

		if ($editor != "")
			$sql = $sql . " AND editor = '$editor' ";

		if ($status > 0)
			$sql = $sql . " AND estado = $status ";
	
		if ($id_group > 1)
			$sql = $sql . " AND id_grupo = $id_group ";

		if ($id_group_creator > 1)
			$sql = $sql . " AND id_group_creator = $id_group_creator ";

		$search_incidents = get_db_all_rows_sql ($sql);

        if ($search_incidents) {
		    $lista_incidencias = " 0";
		    $lista_tareas = " 0";

		    // Get the lists separated
		    foreach ($search_incidents as $i){
			    $lista_incidencias .= ", ". $i[0];
			    $lista_tareas .= ", ".$i[1];
		    }
        } else {

            // There is no match.
            $lista_incidencias = "-1";
            $lista_tareas = "-1";

        }

		if ($lista_incidencias != " 0") {
			$incident_selector = " AND tincidencia.id_incidencia IN ($lista_incidencias) ";
		} 

		if ($lista_tareas != " 0") {
                        $task_selector = " AND ttask.id IN ($lista_tareas) ";
                }

		if ($wu_reporter != "")
                        $user_search = " AND tworkunit.id_user = '".$wu_reporter . "'";
                else
                        $user_search = "";

		$sql = sprintf ('SELECT tproject.id as id, tproject.name as name, SUM(tworkunit.duration) AS sum
                FROM tproject, ttask, tworkunit_task, tworkunit
                WHERE tworkunit_task.id_workunit = tworkunit.id '. $user_search . ' 
                AND tworkunit_task.id_task = ttask.id
                AND ttask.id_project = tproject.id
                AND tworkunit.timestamp >= "%s"
                AND tworkunit.timestamp <= "%s" ' . $task_selector . '
                GROUP BY tproject.name',
                $start_date, $end_date);

    // If it's not an incident match.... search in regular project data

	} else {

		if ($wu_reporter != "")
			$user_search = " AND tworkunit.id_user = '".$wu_reporter . "'";
		else
			$user_search = "";
	
		// ACL CHECK, show all info (user) or only related info for this user (current user) projects

		if ((dame_admin($config["id_user"])) OR ($config["id_user"] == $wu_reporter)) {

			$sql = sprintf ('SELECT tproject.id as id, tproject.name as name, SUM(tworkunit.duration) AS sum
			FROM tproject, ttask, tworkunit_task, tworkunit
			WHERE tworkunit_task.id_workunit = tworkunit.id '. $user_search . '
			AND tworkunit_task.id_task = ttask.id
			AND ttask.id_project = tproject.id
			AND tworkunit.timestamp >= "%s"
			AND tworkunit.timestamp <= "%s"
			GROUP BY tproject.name',
			$start_date, $end_date);
		} else {

			// Show only info on my projects for this user
			// TODO: Move this to enterprise code.

			$sql = sprintf ('SELECT tproject.id as id, tproject.name as name, SUM(tworkunit.duration) AS sum
			FROM tproject, ttask, tworkunit_task, tworkunit
			WHERE tworkunit_task.id_workunit = tworkunit.id '. $user_search . '
			AND tworkunit_task.id_task = ttask.id
			AND ttask.id_project = tproject.id
			AND tworkunit.timestamp >= "%s"
			AND tworkunit.timestamp <= "%s"
			AND tproject.id_owner = "%s" 
			GROUP BY tproject.name',
			$start_date, $end_date, $config["id_user"]);

		}		

	}	

	$projects = get_db_all_rows_sql ($sql);
	
	if ($projects) {
		foreach ($projects as $project) {
			$total_project = get_project_workunit_hours ($project['id'], 0, $start_date, $end_date);
		    $total_time += $project['sum'];
		    $total_global  += $total_project;		
            if ($only_summary == 0){	
			    echo "<tr style='border-top: 1px solid #ccc'>";
			    echo "<td>";
			    echo '<a href="index.php?sec=projects&sec2=operation/projects/task&id_project='.$project['id'].'">';
			    echo '<strong>'.$project['name'].'</strong>';
			    echo "</a>";
			    echo "</td><td>";
			    echo $project['sum'];

			    echo "</td><td>";	
			    echo $total_project;

			    echo "</td><td>";
			    if ($total_project > 0)
				    echo format_numeric ($project['sum'] / ($total_project / 100) )."%";
			    else
				    echo '0%';
			    echo "</td></tr>";
            }
			
			$sql = sprintf ('SELECT ttask.id as id, ttask.name as name, SUM(tworkunit.duration) as sum
				FROM tproject, ttask, tworkunit_task, tworkunit
				WHERE tworkunit_task.id_workunit = tworkunit.id

				AND ttask.id_project = %d '. $user_search . '
				AND tworkunit_task.id_task = ttask.id
				AND ttask.id_project = tproject.id
				AND tworkunit.timestamp >= "%s"
				AND tworkunit.timestamp <= "%s" '. $task_selector .'
				GROUP BY ttask.name',
				$project['id'], $start_date, $end_date);

			$tasks = get_db_all_rows_sql ($sql);
			if ($tasks) {
				foreach ($tasks as $task) {
					$total_task = get_task_workunit_hours ($task['id']);

                    if ($only_summary == 0){	
					    echo "<tr>";
					    echo "<td>&nbsp;&nbsp;&nbsp;<img src='images/copy.png'>";
                        echo "<a href='index.php?sec=users&sec2=operation/users/user_workunit_report&timestamp_l=$start_date&timestamp_h=$end_date&id=$user_id&id_task=".$task['id']."'>";

					    echo $task['name'];
					    echo "</a>";
					    echo "</td><td>";
					    echo $task['sum'];
					    echo "</td><td>";	
					    echo $total_task;
					    echo "</td><td>";
					    if ($total_task > 0)
						    echo format_numeric ($task['sum'] / ($total_task / 100))."%";
					    else
						    echo '0%';
					    echo "</td></tr>";
                    }
				}
			}

            // Now get statistical data about each user work effort (when a user is not provided=

            if ($user_search == ""){

                $sql = sprintf ('SELECT tworkunit.id_user as user_id, SUM(tworkunit.duration) as sum
				    FROM tproject, ttask, tworkunit_task, tworkunit
				    WHERE tworkunit_task.id_workunit = tworkunit.id
				    AND ttask.id_project = %d '. $user_search . '
				    AND tworkunit_task.id_task = ttask.id
				    AND ttask.id_project = tproject.id
				    AND tworkunit.timestamp >= "%s"
				    AND tworkunit.timestamp <= "%s" '. $task_selector .'
				    GROUP BY tworkunit.id_user',
				    $project['id'], $start_date, $end_date);
			    $tasks = get_db_all_rows_sql ($sql);
			    if ($tasks) {
				    foreach ($tasks as $task) {
                        $worker = $task["user_id"];
                        if (!isset( $worker_data[$worker]))
                            $worker_data[$worker] = 0;
                		$worker_data[$worker] = $worker_data[$worker] + $task["sum"];    
                    }
                }
            }
		}
	}

	echo "<tr style='border-top: 2px solid #ccc'>";
	echo "<td><b>".__("Totals")."</b></td>";
	echo "<td colspan=3>";
	echo $total_time. " (". format_numeric (get_working_days ($total_time)). " ".__("Working days").")";
	echo "&nbsp;&nbsp;&nbsp; $total_global (". format_numeric (get_working_days ($total_global)). " ".__("Working days").")";

	echo "</td></tr></table>";

	if ($total_time > 0){
		echo "<h3>". __("Project graph report")."</h3>";
        echo "<div>";
		echo graph_workunit_user (800, 350, $user_id, $start_date, $end_date, $ttl);
		echo "</div><br>";

        if ($user_search == ""){
            echo "<h3>". __("Worktime per person")."</h3>";
    		echo "<div>";
            echo pie3d_graph ($config['flash_charts'], $worker_data, 500, 320, __('others'), "", "", $config['font'], $config['fontsize']-1, $ttl);
    		echo "</div>";
        }
	}

	// Incident report

	echo "<h3>".__("Incident report")."</h3>";


	if ($wu_reporter != ""){
		$user_search = " AND tworkunit.id_user = '$wu_reporter' ";
	} else {
		$user_search = "";
	}

	$sql = sprintf ('SELECT tincidencia.id_incidencia as id_incidencia, tincidencia.score as score, tincidencia.resolution, tincidencia.id_incidencia as iid, tincidencia.estado as istatus, tincidencia.titulo as title, tincidencia.id_grupo as id_group, tincidencia.id_group_creator as id_group_creator, tincidencia.id_creator as creator, tincidencia.id_usuario as owner, tincidencia.inicio as date_start, tincidencia.cierre as date_end, tincidencia.id_task as taskid,  SUM(tworkunit.duration) as `suma`  
		FROM tincidencia, tworkunit_incident, tworkunit
		WHERE tworkunit_incident.id_workunit = tworkunit.id '. $user_search .'
		AND tworkunit_incident.id_incident = tincidencia.id_incidencia  
        AND tworkunit.timestamp >= "%s" 
		AND tworkunit.timestamp <= "%s 23:59:59"'. $incident_selector .'
		GROUP BY title', $start_date, $end_date);

	$incidencias = get_db_all_rows_sql ($sql);

	if (sizeof($incidencias) == 0){
		echo "<h3>";
		echo __("There is no data to show");
		echo "</h3>";
	} else {
			
		echo '<table width="99%" class="listing"><tr>';
        if ($only_summary == 0) {
		    echo "<th>".__('#')."</th>";
		    echo "<th>".__('Incident'). "<br>".__("Task")."</th>";
		    echo "<th>".__('Group')."<i><br>".__("Creator group")."</i></th>";
		    echo "<th>".__('Owner')."<i><br>".__('Creator')."</i></th>";
		    echo "<th>".__('Status')."<br>". __("Resolution")."</th>";
		    echo "<th>".__('Date')."</th>";
		    echo "<th>".__('User'). " ".__("vs"). "<br>". __("Total hours")."</th>";
		    echo "<th>".__('Score')."</th>";
		    echo "<th>".__('SLA Compliance')."</th>";
		    echo "</tr>";
        }

		$incident_totals = 0;
		$incident_user = 0;
        $incident_count = 0;
		$incident_graph = array();

		if ($incidencias) {
			foreach ($incidencias as $incident) {
	
    	        $incident_count++;

                // Build data for graphs
				$incident_graph[$incident["title"]] = $incident["suma"];

                $grupo = substr(safe_output(dame_grupo($incident["id_group"])),0,15);
                $grupo_src = substr(safe_output(dame_grupo($incident["id_group_creator"])),0,15);

                if (!isset( $incident_group_data[$grupo]))
                    $incident_group_data[$grupo] = 0;
        		$incident_group_data[$grupo] = $incident_group_data[$grupo] + 1;    

                if (!isset( $incident_group_data2[$grupo_src]))
                    $incident_group_data2[$grupo_src] = 0;
                $incident_group_data2[$grupo_src] = $incident_group_data2[$grupo_src] + 1;

                if ($only_summary == 1)
                    continue;

				echo "<tr>";
				echo "<td>";

				echo "<b>#".$incident["id_incidencia"]."</b>";
				echo "<td><b>";
				echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident&id=".$incident["id"]."'>";

				echo $incident["title"] . "</a><br></b><i>";

                echo "<a href='index.php?sec=projects&sec2=operation/projects/task_detail&id_task=".$incident["taskid"]."&operation=view'>".get_db_sql("select name from ttask where id = ".$incident["taskid"]). "</a></i>";
                echo "</td>";

				echo "<td>".dame_grupo($incident["id_group"])."<br><i>";
                echo dame_grupo($incident["id_group_creator"]);
                echo "</i></td>";

				echo "<td class=f9>";
                echo $incident["owner"]."<br>";
                echo "<i>".$incident["creator"]."</i>";
                echo "</td>";
                // Status and resolution
				$status = get_indicent_status();
				echo "<td>".$status[$incident["istatus"]];
				echo "<br>";
				echo render_resolution ($incident["resolution"]); 
				echo "</td>";

                // Date
				echo "<td class=datos width=80 style='font-size: 9px'>";
                echo substr($incident["date_start"],0,11). "<br>";
                echo substr($incident["date_end"],0,11)."</td>";

                // User vs Total wu hours
				echo "<td>".$incident["suma"]."<br>";
				$incident_user  += $incident["suma"];
				$this_incident = get_incident_workunit_hours($incident["iid"]);
				echo $this_incident."</td>";
				$incident_totals +=  $this_incident;
				
                // Score
                echo "<td>";
				if (give_acl ($config["id_user"], 0, "IM"))
                    if ($incident["score"] != 0)
    					echo $incident["score"];
                    else
                        echo "-";
				else
					echo "N/A";
				echo "</td>";
                
                // SLA Compliance    
                echo "<td>";
                echo format_numeric (get_sla_compliance_single_id ($incident["iid"]));
                echo " %";
    			echo "</td></tr>";
			}

			echo "<tr style='border-top: 2px solid #ccc'>";
			echo "<td><b>".__("Totals")."</b></td>";
			echo "<td colspan=8>";

            echo "<b>".__("Number of incidents"). " </b>: ". $incident_count;
            echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>";
			echo __('Total worktime'). " </b>: ". $incident_totals.__("hr")." ( ". format_numeric(get_working_days ($incident_totals)). " ".__("Working days").")";
			
		}
		echo "</table>";
		
		if ($incident_graph){
			echo "<h3>". __("Incident graph report")."</h3>";
			echo "<div>";
			echo pie3d_graph ($config['flash_charts'], $incident_graph, 500, 280, __('others'), "", "", $config['font'], $config['fontsize'], $ttl);
			echo "</div>";

    		echo "<h3>". __("Incident by group")."</h3>";
			echo "<div>";
    	    echo pie3d_graph ($config['flash_charts'], $incident_group_data, 500, 280, __('others'), "", "", $config['font'], $config['fontsize']-1, $ttl);
			echo "</div>";

    		echo "<h3>". __("Incident by creator group")."</h3>";
			echo "<div>";
            echo pie3d_graph ($config['flash_charts'], $incident_group_data2, 500, 280, __('others'), "", "", $config['font'], $config['fontsize']-1, $ttl);
			echo "</div>";
		}

	}
}

?>

<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
<script type="text/javascript" src="include/js/integria_date.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.autocomplete.js"></script>


<script type="text/javascript">

add_ranged_datepicker ("#text-start_date", "#text-end_date", null);

$(document).ready (function () {

	var idUser = "<?php echo $config['id_user'] ?>";
	
	bindAutocomplete ("#text-user_id", idUser);
	bindAutocomplete ("#text-user_id2", idUser);
	bindAutocomplete ("#text-user_id3", idUser);
	bindAutocomplete ("#text-user_id4", idUser);
	
});
</script>
