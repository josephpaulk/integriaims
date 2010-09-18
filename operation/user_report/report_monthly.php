<?php
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008-2010 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

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
	$now = date("Y-m-d H:i:s");
	$now_year = date("Y");
	$now_month = date("m");

	$working_month = get_parameter ("working_month", $now_month);
	$working_year = get_parameter ("working_year", $now_year);


	$begin_month = "$working_year-$working_month-01 00:00:00";
	$end_month = "$working_year-$working_month-31 23:59:59";
	$total_days = working_days ( $working_month, $working_year);
	$total_hours = $total_days * 8;
	$color = 0;


    $prev_month = $working_month -1;
    $prev_year = $working_year;
    if ($prev_month == 0){
	    $prev_month = 12;
	    $prev_year = $prev_year -1;
    }

    $next_month = $working_month + 1;
    $next_year = $working_year;
    if ($next_month == 13){
	    $next_month = 1;
	    $next_year = $next_year +1;
    }

	echo "<h2>";
	echo getmonth($working_month). " / ". $working_year. " &raquo; ".__('Totals for this month'). " &raquo; ($total_hours)";
	echo "</h2>";

    echo "<br>";
	echo "<table class='blank' style='margin-left: 10px' >";
	echo "<tr><td>";
    echo "<a href='index.php?sec=users&sec2=operation/user_report/report_monthly&working_month=$prev_month&working_year=$prev_year'><img src='images/control_rewind_blue.png'> ".__('Prev')."</a> ";
	echo "</td><td>";
	echo "<form method='post' action='index.php?sec=users&sec2=operation/user_report/report_monthly'>";
	echo '<select name="working_month">';
	echo "<option value='$working_month'>".getmonth($working_month);
	for ($ax=1; $ax <= $now_month; $ax++){
                echo "<option value='$ax'>".getmonth($ax);
        }

	echo "</select>";
	echo "</td><td>";
    echo "<a href='index.php?sec=users&sec2=operation/user_report/report_monthly&working_month=$next_month&working_year=$next_year'><img src='images/control_fastforward_blue.png'> ".__('Prev')."</a> ";
	echo "</td><td>";
	echo "<input type=submit class='next' value='".__('Update')."'>";
	echo "</form>";
	echo "</table>";


	echo '<table width="99%" class="listing">';
	echo "<th>".__('User ID');
	echo "<th>".__('Workunit report');
	echo "<th>".__('Calendar view');
    echo "<th>".__('Graph overview');
	echo "<th>".__('Total hours for this month');
    echo "<th>".__('Charged this month');
    echo "<th>".__('Avg. Scoring');

	$sql0= "SELECT * FROM tusuario";
	if ($res0 = mysql_query($sql0)) {
		while ($row0=mysql_fetch_array($res0)){

            // Can current user have access to this user ?
            if (($row0["id_usuario"] == $config["id_user"]) OR (give_acl($config["id_user"], 0, "IM")) OR (give_acl($config["id_user"], 0, "UM"))) {
			    $nombre = $row0["id_usuario"];
			    $avatar = $row0["avatar"];

                // Get total hours for this month
			    $sql= "SELECT SUM(duration) FROM tworkunit WHERE timestamp > '$begin_month' AND timestamp < '$end_month' AND id_user = '$nombre'";
			    if ($res = mysql_query($sql)) {	
				    $row=mysql_fetch_array($res);
			    }
			    
			    echo "<tr><td>";
                
                echo "<a href='#' class='tip'>&nbsp;<span>";
                $usuario = get_db_row ("tusuario", "id_usuario", $nombre);
				echo "<b>".$usuario["nombre_real"] . "</b><br>";
				echo "<i>".$usuario["comentarios"] . "</i><br>";
				if ($config["enteprise"] == 1){
					echo "<font size=1px>";
					$sql1='SELECT * FROM tusuario_perfil WHERE id_usuario = "'.$nombre.'"';
					$result1=mysql_query($sql1);
				
					if (mysql_num_rows($result1)){
						while ($row1=mysql_fetch_array($result1)){
							echo dame_perfil($row1["id_perfil"])."/ ";
							echo dame_grupo($row1["id_grupo"])."<br>";
						}
					}
					else { 
						echo __('This user doesn\'t have any assigned profile/group'); 
					}
					
	            
				}
				echo "</font></span></a>";
				if (strlen($nombre) > 12)
					echo " <b>".substr($nombre,0,12)."..</b>";
				else
					echo " <b>".$nombre."</b>";
				
				
                // Workunit report (detailed)
			    echo "<td><center>";
                echo "<a href='index.php?sec=users&sec2=operation/users/user_workunit_report&timestamp_l=$begin_month&timestamp_h=$end_month&id=$nombre'>";
                echo "<img border=0 src='images/page_white_text.png'></A></center></td>";

                // Clock to calendar montly report for X user
			    echo "<td  ><center>";
			    echo "<a href='index.php?sec=users&sec2=operation/user_report/monthly&month=$working_month&year=$working_year&id=$nombre'><img src='images/clock.png' border=0></a></center></td>";
    
                // Graph stats montly report for X user
                echo "<td ><center>";
                echo "<a href='index.php?sec=users&sec2=operation/user_report/monthly_graph&month=$working_month&year=$working_year&id=$nombre'><img src='images/chart_bar.png' border=0></a></center></td>";
                // Total hours this month
			    echo "<td  >";
			    echo $row[0];
                
                // Total charged hours this month
                echo "<td  >";
                $tempsum = get_db_sql ("SELECT SUM(duration) FROM tworkunit WHERE have_cost = 1 AND id_user = '$nombre' AND timestamp > '$begin_month' AND timestamp <= '$end_month'");
                if ($tempsum != "")
                    echo $tempsum. " hr";
                else
                    echo "--";

                // Average incident scoring
                echo "<td>";
                $tempsum = get_db_sql ("SELECT SUM(score) FROM tincidencia WHERE id_usuario = '$nombre' AND actualizacion > '$begin_month' AND actualizacion <= '$end_month' AND score > 0 ");


                if ($tempsum != "")
                    echo format_numeric($tempsum). "/10";
                else
                    echo "--";
            }
		}
	}
	echo "</table>";
?>
