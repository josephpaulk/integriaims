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

global $config;
$id_user = $config["id_user"];

if (check_login() != 0) {
	audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access", "Trying to access monthly report");
	require ("general/noaccess.php");
	exit;
}


// --------------------
// Workunit report
// --------------------
$now = date ('Y-m-d');
$start_date = get_parameter ("start_date", date ('Y-m-d', strtotime ("$now - 3 months")));
$end_date = get_parameter ('end_date', $now);
$user_id = get_parameter ('user_id');

echo "<h1>";
echo __("Full report");
if ($user_id != "") {
	echo " - ";
	echo dame_nombre_real ($user_id);
}
echo  "</h1>";

echo "<form method='post' action='index.php?sec=users&sec2=operation/user_report/report_full'>";
echo "<table class='blank' style='margin-left: 10px' width='90%'>";
echo "<tr><td>";
echo __("Username");
echo "</td><td>";
combo_user_visible_for_me ($user_id, 'user_id', 0, 'PR');
echo "</td><td>";
echo __("Begin date");
print_input_text ('start_date', $start_date, '', 10, 20);	
echo "</td><td>";
echo __("End date");
print_input_text ('end_date', $end_date, '', 10, 20);	
echo "</td><td>";
print_submit_button (__('Show'), 'show_btn', false, 'class="next sub"');
echo "</form>";
echo "</table>";

if ($user_id == "") {
	echo "<h3>";
	echo __("There is no data to show");
	echo "</h3>";
} else {
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
			
			echo "<tr>";
			echo "<td>";
			echo '<a href="index.php?sec=projects&sec2=operation/projects/task&id_project='.$project['id'].'">';
			echo '<strong>'.$project['name'].'</strong>';
			echo "</a>";
			echo "<td>";
			echo $project['sum'];
			echo "<td>";	
			echo $total_project;
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
					$total_task = get_task_workunit_hours ($task_id);
	
					echo "<tr>";
					echo "<td>&nbsp;&nbsp;&nbsp;<img src='images/copy.png'>";
					echo '<a href="index.php?sec=projects&sec2=operation/projects/task_detail&id_project='.$project['id'].'&id_task='.$task['id'].'&operation=view">';
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
	echo "</table>";
}
?>
<script type="text/javascript" src="include/js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
<script type="text/javascript" src="include/js/integria_date.js"></script>

<script type="text/javascript">

$(document).ready (function () {
	configure_range_dates (null);
});
</script>
