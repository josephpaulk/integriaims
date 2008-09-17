<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.


	echo "<div>";
	$nick = $_SESSION['id_usuario'];
	echo "<h1>" . $lang_label["welcome_title"] . "</h1>";
	echo "<p>";
	echo $lang_label["main_text"];
	echo "</p>";

	// Show last activity from this user
	echo "<h2>" . $lang_label["user_last_activity"] . "</h2>";
	// Show table header
	echo '<table cellpadding="3" cellspacing="3" width="740"><tr>'; 
	echo '<th>' . $lang_label["user"] . '</th>';
	echo '<th>' . $lang_label["action"] . '</th>';
	echo '<th>' . $lang_label["date"] . '</th>';
	echo '<th>' . $lang_label["src_address"] . '</th>';
	echo '<th>' . $lang_label["comments"] . '</th></tr>';

	// Skip offset records
	$query1="SELECT * FROM tsesion WHERE (TO_DAYS(fecha) > TO_DAYS(NOW()) - 7) AND ID_usuario = '" . $nick . "' ORDER BY fecha DESC limit 15";

	$result = mysql_query ($query1);
	$contador = 5; // Max items
	$color = 1;
	while (($row = mysql_fetch_array ($result)) and ($contador > 0)) {
		
		if ($color == 1){
			$tdcolor = "datos";
			$color = 0;
		} else {
			$tdcolor = "datos2";
			$color = 1;
		}
		
		$usuario = $row["ID_usuario"];
		echo '<tr><td class="' . $tdcolor . '">';
		echo '<b class="' . $tdcolor . 'f9">' . $usuario . '</b>';
		echo '<td class="' . $tdcolor . 'f9">';
		echo $row["accion"];
		echo '<td class="' . $tdcolor . 'f9">';
		echo $row["fecha"];
		echo '<td class="' . $tdcolor . 'f9">';
		echo $row["IP_origen"];
		echo '<td class="' . $tdcolor . 'f9">';
		echo $row["descripcion"];
		echo '</tr>';
		
		$contador--;
	}

	echo "</table>";

	// Site news !
	
	$sql_news = "SELECT * FROM tnews ORDER by utimestamp LIMIT 3";
	$news = 0;
	if ($result_news = mysql_query ($sql_news)){
		echo '<h2>' . $lang_label["site_news"] . '</h2>';
		echo '<table width="700"><tr>'; 
		while ($row = mysql_fetch_array ($result_news)) {
			$news = 1;
			echo '<tr><th align="left">';
			echo $lang_label["at"]. " <i>". $row["timestamp"] ."</i> ".$lang_label["user"]. " <b>". $row["author"]."</b> ".$lang_label["says"].":  \"<b>".$row["subject"]."\"</b>";
			echo '<tr><td class=datos>';
			echo clean_output_breaks($row["text"]);
			echo '<td><td class=datos3">';
		}
		echo "</table>";
	} 



?>