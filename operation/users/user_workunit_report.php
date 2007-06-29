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

	if (check_login() != 0) {
		audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access", "Trying to access event viewer");
		require ("general/noaccess.php");
		exit;
	}

	$id_user = $_SESSION["id_usuario"];

	// --------------------
	// Workunit report
	// --------------------


	$ahora = date("Y-m-d H:i:s");

	echo "<h3>";
	echo $lang_label["workunit_personal_report"] ." ( ".$id_user. " )";
	echo "</h3>";

	$sql= "SELECT * FROM tworkunit WHERE tworkunit.id_user = '$id_user'";

	echo "<table cellpadding='3' cellspacing='3' border='0' width='800' class='databox_color'>";
	echo "<tr>";
	echo "<th>";
	echo $lang_label["attributes"];
	echo "<th width=400>";
	echo $lang_label["description"];

	$color = 1;
	if ($res = mysql_query($sql)) {
		while ($row=mysql_fetch_array($res)){
			if ($color == 1){
				$tdcolor = "datos";
				$color = 0;
			} else {
				$tdcolor = "datos2";
				$color = 1;
			}
			echo "<tr>";
			echo "<td class='$tdcolor' valign='top' align='left'>";
			echo "<table cellpadding=4 cellspacing=0 align='left'>";
			// Show data

			// Task
			echo "<tr><td><b>".$lang_label["task"]."</b>";
			echo "<td class='$tdcolor' valign='top'>";
			$id_task = give_db_value ("id_task", "tworkunit_task", "id", $row[0]);
			$task_name = give_db_value ("name", "ttask", "id", $id_task);
			if ($task_name == "")
				$task_name = $lang_label["vacations"];
			echo "<b>".$task_name."</b>";
			
			// date
			echo "<tr><td><b>".$lang_label["date"]."</b>";;
			echo "<td class='".$tdcolor."f9' valign='top'>";
			echo $row["timestamp"];
			// TIme used
			echo "<tr><td><b>".$lang_label["worktime"]."</b>";
			echo "<td class='$tdcolor' valign='top'>";
			echo $row["duration"];
			// Role
			echo "<tr><td><b>".$lang_label["role"]."</b>";
			echo "<td class='$tdcolor' valign='top'>";
			echo give_db_value ("name", "trole", "id", $row["id_profile"]);
			// Cost
			echo "<tr><td><b>".$lang_label["cost"]."</b>";
			echo "<td class='$tdcolor' valign='top'>";
			if ($row["have_cost"]){
				$cost = $row["duration"] * give_db_value ("cost", "trole", "id", $row["id_profile"]);
				echo $cost . " &euro;";
			} else
				echo $lang_label["N/A"];
			echo "</table>";
			// description
			echo "<td class='$tdcolor' valign='top'><br>";
			echo substr(clean_output_breaks($row["description"]),0,200);
			echo ".....";
		}
	}
	echo "</table>";
	

?>
