<?php
// FRITS - the FRee Incident Tracking System
// =========================================
// Copyright (c) 2007 Sancho Lerena, slerena@openideas.info
// Copyright (c) 2007 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
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
	
	$begin_month = "2007-08-01 00:00:00";
	$end_month = "2007-08-30 23:59:59";
	$total_hours = 192;
	$color = 0;
	
	echo "<h3>";
	echo lang_string("Totals for this month"). " - ( $total_hours )";
	echo "</h3>";

	echo '<table cellpadding="4" cellspacing="4" width="100%" class="databox_color">';
	echo "<th>".$lang_label["user_ID"];
	echo "<th>".$lang_label["profile"];
	echo "<th>".lang_string ("total_hours_for_this_month");

	$sql0= "SELECT * FROM tusuario";
	if ($res0 = mysql_query($sql0)) {
		while ($row0=mysql_fetch_array($res0)){
			$nombre = $row0["id_usuario"];
			$avatar = $row0["avatar"];
			$sql= "SELECT SUM(duration) FROM tworkunit WHERE timestamp > '$begin_month' AND timestamp < '$end_month' AND id_user = '$nombre'";
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
			echo "<a href='index.php?sec=users&sec2=operation/users/user_workunit_report&id=$nombre'><b>".$nombre."</b></a>";
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
	echo "</table>";
?>
