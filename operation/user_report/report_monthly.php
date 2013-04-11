<?php
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008-2011 Ártica Soluciones Tecnológicas
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
		audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access", 
        "Trying to access monthly report");
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
	echo "<table class='blank'>";
	echo "<tr><td>";
    echo "<a href='index.php?sec=users&sec2=operation/user_report/report_monthly&working_month=$prev_month&working_year=$prev_year'><img src='images/control_rewind_blue.png'> ".__('Prev')."</a> ";
	echo "</td><td>";
	echo "<form method='post' action='index.php?sec=users&sec2=operation/user_report/report_monthly'>";
	echo "<select name='working_month'>";
	echo "<option value='$working_month'>".getmonth($working_month);
	for ($ax=1; $ax <= $now_month; $ax++){
                echo "<option value='$ax'>".getmonth($ax);
    }
	echo "</select>";
	echo "</td><td>";
    echo "<a href='index.php?sec=users&sec2=operation/user_report/report_monthly&working_month=$next_month&working_year=$next_year'>".__('Next')." <img src='images/control_fastforward_blue.png'></a> ";
	echo "</td><td>";
	echo __('Filter');
	echo "</td><td>";
	$search = get_parameter ("search", '');
	
	print_input_text ('search', $search, '', 15);
	echo "</td><td>";
	echo "<input type=submit class='sub next' value='".__('Update')."'>";
	echo "</form>";
	echo "</table>";
	
   	$values = get_user_visible_users ($config['id_user'], "UM", true, true, false, $search);

	if(empty($values) && $search == '') {
		$values[$config['id_user']] = $config['id_user'];
	}
	
	$offset = get_parameter('offset', 0);

	echo "<table class='blank'><tr><td>";
        pagination (count($values), "index.php?sec=users&sec2=operation/user_report/report_monthly", $offset);
	echo "</td></tr></table>";

	echo '<table width="99%" class="listing">';
	echo "<th>".__('Profile');
	echo "<th>".__('User ID');
	echo "<th>".__('Fullname');
	echo "<th>".__('Company');
	echo "<th>".__('Reports');
	echo "<th>".__('Total hours for this month');
	echo "<th>".__('Avg. Scoring');
	
	$min = $offset;
	$max = $offset+$config['block_size']-1;
	$i = 0;
	foreach ($values as $key => $value){

		if($i < $min || $i > $max) {
			$i++;
			continue;
		}
		$i++;

		$row0 = get_db_row ("tusuario", "id_usuario", $key);
		if ($row0){
			$nombre = $row0["id_usuario"];
			$avatar = $row0["avatar"];

	                // Get total hours for this month
			$sql= "SELECT SUM(duration) FROM tworkunit WHERE timestamp > '$begin_month' AND timestamp < '$end_month' AND id_user = '$nombre'";
			if ($res = mysql_query($sql)) {	
				$row=mysql_fetch_array($res);
			}
			    
			echo "<tr><td>";
                
            echo "<a href='index.php?sec=users&sec2=operation/users/user_edit&id=$nombre' class='tip'>&nbsp;<span>";
            $usuario = get_db_row ("tusuario", "id_usuario", $nombre);
			echo "<b>".$usuario["nombre_real"] . "</b><br>";
			echo "<i>".$usuario["comentarios"] . "</i><br>";

			// TODO - Move this to enterprise code.

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
			echo "</td>";
			echo "<td>";

			if (give_acl ($config["id_user"], 0, "UM")){
				echo "<a href='index.php?sec=users&sec2=godmode/usuarios/configurar_usuarios&update_user=$nombre'>";
			}

			if (strlen(safe_output($nombre)) > 21)
				echo "".ucfirst(substr(safe_output($nombre),0,21))."..</b>";
			else
				echo ucfirst($nombre)."</b>";

			if (give_acl ($config["id_user"], 0, "IM")){
				echo "</a>";
			}
			
			echo "<td style='font-size:9px'>".$usuario["nombre_real"]."</td>";

			
			$company_name = (string) get_db_value ('name', 'tcompany', 'id', $usuario['id_company']);
			
			echo "<td style='font-size:9px'>".$company_name."</td>";

			echo "<td>";

            // Full report			
            echo "<a href='index.php?sec=users&sec2=operation/user_report/report_full&only_projects=1&wu_reporter=$nombre'>";
            echo "<img title='".__("Full report")."' src='images/page_white_stack.png'>";
            echo "</a>";
		    
            // Workunit report (detailed)
    	    echo "&nbsp;&nbsp;";
			echo "<a href='index.php?sec=users&sec2=operation/users/user_workunit_report&timestamp_l=$begin_month&timestamp_h=$end_month&id=$nombre'>";
			echo "<img border=0 title='".__("Workunit report")."' src='images/page_white_text.png'></A>";

            // Clock to calendar montly report for X user
	        echo "&nbsp;&nbsp;";
    	    echo "<a href='index.php?sec=users&sec2=operation/user_report/monthly&month=$working_month&year=$working_year&id=$nombre'><img src='images/clock.png' title='".__("Montly calendar report")."' border=0></a>";

            // Graph stats montly report for X user
    	    echo "&nbsp;&nbsp;";
            echo "<a href='index.php?sec=users&sec2=operation/user_report/monthly_graph&month=$working_month&year=$working_year&id=$nombre'><img src='images/chart_bar.png' title='".__("Montly report")."' border=0></a></center>";
      
	// WO report for X user
            echo "&nbsp;&nbsp;";
            echo "<a href='index.php?sec=projects&sec2=operation/workorders/wo&owner=$nombre'><img src='images/paste_plain.png' title='".__("Workorders")."' border=0></a></center></td>";
			
		// Total hours this month
			echo "<td  >";
			echo $row[0];
            
			// Total charged hours this month
			/*
		            echo "<td  >";
		            $tempsum = get_db_sql ("SELECT SUM(duration) FROM tworkunit WHERE have_cost = 1 AND id_user = '$nombre' AND timestamp > '$begin_month' AND timestamp <= '$end_month'");
		            if ($tempsum != "")
		                echo $tempsum. " hr";
		            else
		                echo "--";
			*/

		        // Average incident scoring
			echo "<td>";
			$tempsum = get_db_sql ("SELECT SUM(score) FROM tincidencia WHERE id_usuario = '$nombre' AND actualizacion > '$begin_month' AND actualizacion <= '$end_month' AND score > 0 ");


            if ($tempsum != "")
                echo format_numeric($tempsum). "/10";
            else
                echo "--";
        }
	}

	echo "</table>";
?>
