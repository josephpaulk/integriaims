<?php
// Integria 1.0 - http://integria.sourceforge.net
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
		audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access", "Trying to access event viewer");
		require ("general/noaccess.php");
		exit;
	}

	// --------------------
	// Workunit report
	// --------------------
	$now = date("Y-m-d H:i:s");
	$now_year = date("Y");
	$now_month = date("m");

	$week_begin = give_parameter_post ( "working_week");
	echo "<table class='databox' cellpadding=4 cellspacing=4 width='200'>";
	echo "<tr><td>";
	echo "<form method='post' action='index.php?sec=users&sec2=operation/user_report/report_weekly'>";
	working_weeks_combo();
	echo "</td><td>";
	echo "<input type=submit class='next' value='".lang_string("update")."'>";
	echo "</form>";
	echo "</table>";

	if ($week_begin == "")
		$begin_week = first_working_week() . " 00:00:00";
	else
		$begin_week = $week_begin;

	$end_week = date('Y-m-d H:i:s',strtotime("$begin_week + 1 week"));
	$total_hours = 7 * 8; // TODO: subroutine to minus festive days (defined on DB by month)
	$color = 0;
	
	echo "<h3>";
	echo lang_string("Totals for week $begin_week - $end_week "). " - ( $total_hours ".lang_string("hr").")";
	echo "</h3>";

	echo '<table cellpadding="4" cellspacing="4" width="100%" class="databox_color">';
	echo "<th>".$lang_label["user_ID"];
	echo "<th>".$lang_label["profile"];
	echo "<th>".lang_string ("total_hours_for_this_week");

	
	$sql0= "SELECT * FROM tusuario";
	if ($res0 = mysql_query($sql0)) {
		while ($row0=mysql_fetch_array($res0)){
            // Can current user have access to this user ?
            if ((user_visible_for_me ($config["id_user"], $row0["id_usuario"], "IM") == 1) OR 
                (user_visible_for_me ($config["id_user"], $row0["id_usuario"], "PM") == 1)) {
			    $nombre = $row0["id_usuario"];
			    $avatar = $row0["avatar"];
			    $sql= "SELECT SUM(duration) FROM tworkunit WHERE timestamp > '$begin_week' AND timestamp < '$end_week' AND id_user = '$nombre'";
			    if ($res = mysql_query($sql)) {	
				    $row=mysql_fetch_array($res);
			    }
			    if ($color == 1){
				    $tdcolor = "datos";
				    $color = 0;
				    $tip = "tip";
			    }
			    else {
				    $tdcolor = "datos2";
				    $color = 1;
				    $tip = "tip2";
			    }
			    echo "<tr><td class='$tdcolor'>";
			    echo "<a href='index.php?sec=users&sec2=operation/users/user_workunit_report&timestamp_l=$begin_week&timestamp_h=$end_week&id=$nombre'><b>".$nombre."</b></a>";
			    echo "<td class='$tdcolor' width=60>";
			    echo "<img src='images/avatars/".$avatar."_small.png'>";
			    $sql1='SELECT * FROM tusuario_perfil WHERE id_usuario = "'.$nombre.'"';
			    $result1=mysql_query($sql1);
			    echo "<a href='#' class='$tip'>&nbsp;<span>";
			    if (mysql_num_rows($result1)){
				    while ($row1=mysql_fetch_array($result1)){
					    echo dame_perfil($row1["id_perfil"])."/ ";
					    echo dame_grupo($row1["id_grupo"])."<br>";
				    }
			    }
			    else { echo $lang_label["no_profile"]; }
			    echo "</span></a>";
			    echo "<td class='$tdcolor' width=60>";
			    echo $row[0];
            }
		}
	}
	echo "</table>";
?>
