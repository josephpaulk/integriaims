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
	$now = date("Y-m-d");
	$now3 = date('Y-m-d', strtotime("$now - 3 months"));
	$start_date = get_parameter ("start_date", $now3);
	$end_date = get_parameter ("end_date", $now);
	$end_date = get_parameter ("end_date", $now);
	$user_id = get_parameter ("user_id", "");

	echo "<h1>";
	echo __("Full report");
	if ($user_id != ""){
		echo " - ";
		echo dame_nombre_real ($user_id);
	}
	echo  "</h1>";

	echo "<form method='post' action='index.php?sec=users&sec2=operation/user_report/report_full'>";
	echo "<table class='blank' style='margin-left: 10px' width='90%'>";
	echo "<tr><td>";
	echo __("Username");
	echo "</td><td>";
	combo_user_visible_for_me ($user_id, "user_id", 0, "", false, false);
	echo "</td><td>";
	echo __("Begin date");
	print_input_text ('start_date', $start_date, '', 10, 20);	
	echo "</td><td>";
	echo __("End date");
	print_input_text ('end_date', $end_date, '', 10, 20);	
	echo "</td><td>";
	echo "<input type=submit class='next but' value='".__('Show up')."'>";
	echo "</form>";
	echo "</table>";

	if ($user_id == ""){
		echo "<h3>";
		echo __("There is no data to show");
		echo "</h3>";
		return;
	}

	echo '<table width="90%" class="listing">';
	echo "<th>".__('Project');
	echo "<th>".__('User hours');
	echo "<th>".__('Project total');
	echo "<th>".__('%');

	$sql0= "SELECT tproject.id as pid, tproject.name as pname, SUM(tworkunit.duration) as suma FROM tproject, ttask, tworkunit_task, tworkunit WHERE tworkunit.id_user = '$user_id' and tworkunit_task.id_workunit = tworkunit.id AND  tworkunit_task.id_task = ttask.id AND ttask.id_project = tproject.id AND tworkunit.timestamp >= '$start_date' AND tworkunit.timestamp <= '$end_date' GROUP BY tproject.name ";
	if ($res0 = mysql_query($sql0)) {
		while ($row0=mysql_fetch_array($res0)){


			$nombre = $row0["pname"];
			$total_user = $row0["suma"];
			$project_id = $row0["pid"];	
			$total_project = get_project_workunit_hours ($project_id, 0, $start_date, $end_date);
			
			echo "<tr>";
			echo "<td>";
			echo "<a href='index.php?sec=projects&sec2=operation/projects/task&id_project=$project_id'>";
			echo $nombre;
			echo "</a>";
			echo "<td>";
			echo $total_user;
			echo "<td>";	
			echo $total_project;
			echo "<td>";
			echo format_numeric (($total_user /  ($total_project/100)) ). "%";
            
		}
	}
	echo "</table>";
?>
<script type="text/javascript" src="include/js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
<script type="text/javascript" src="include/js/integria_date.js"></script>

<script type="text/javascript">

$(document).ready (function () {
	configure_range_dates (null);
});
</script>
