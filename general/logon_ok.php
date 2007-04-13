<?php

// FRITS - the FRee Incident Tracking System
// =========================================
// Copyright (c) 2007 Sancho Lerena, slerena@openideas.info
// Copyright (c) 2007 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

	echo "<div class='jus'>";
	$nick = $_SESSION['id_usuario'];
	echo "<h1>" . $lang_label["welcome_title"] . "</h1>";
	echo "<p>";
	echo $lang_label["main_text"];
	echo "</p>";

	echo "<div id='activity'>";
	// Show last activity from this user
	echo "<h2>" . $lang_label["user_last_activity"] . "</h2>";
	// Show table header
	echo '<table cellpadding="3" cellspacing="3" width="700"><tr>'; 
	echo '<th>' . $lang_label["user"] . '</th>';
	echo '<th>' . $lang_label["action"] . '</th>';
	echo '<th class="w130">' . $lang_label["date"] . '</th>';
	echo '<th>' . $lang_label["src_address"] . '</th>';
	echo '<th class="w200">' . $lang_label["comments"] . '</th></tr>';

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

	echo "<tr><td colspan='5'><div class='raya'></div>";
	echo "</td></tr></table>";
	echo "</div>"; // activity

// Private messages pending to read !

	$sql='SELECT COUNT(*) FROM tmensajes WHERE id_usuario_destino="' . $nick . '" AND estado="FALSE";';
	$resultado = mysql_query ($sql);
	$row = mysql_fetch_array ($resultado);
	if ($row["COUNT(*)"] != 0){
		
		echo '<div style="margin-left: 8px">' . $lang_label["new_message_bra"];
		echo '<b><a href="index.php?sec=messages&sec2=operation/messages/message">';
		echo $row["COUNT(*)"] . '</b> <img src="images/mail.gif" border="0">';
		echo $lang_label["new_message_ket"] . '</a></div>';
	}

	// Site news !
	echo '<h2>' . $lang_label["site_news"] . '</h2>';
	echo '<table cellpadding="3" cellspacing="3" width="720"><tr>'; 


	$sql_news = "SELECT * FROM tnews ORDER by utimestamp LIMIT 3";
	
	$news = 0;
	if ($result_news = mysql_query ($sql_news)){
		while ($row = mysql_fetch_array ($result_news)) {
			$news = 1;
			echo '<tr><th align="left">';
			echo $lang_label["at"]. " <i>". $row["timestamp"] ."</i> ".$lang_label["user"]. " <b>". $row["author"]."</b> ".$lang_label["says"].":  \"<b>".$row["subject"]."\"</b>";
			echo '<tr><td class=datos>';
			echo clean_output_breaks($row["text"]);
			echo '<td><td class=datos3">';
		}
	} else {
		echo $lang_label["no_news"];
	}
	echo "</table>";

	// Site stats
	echo '<h2 class="mgb25">' . $lang_label["stat_title"] . '</h2>';

	echo '<table cellpadding="3" cellspacing="3" width="500"><tr>'; 
	$query1 = "SELECT COUNT(id_usuario) FROM tusuario";
	$result = mysql_query ($query1);
	$row = mysql_fetch_array ($result);
	echo "<tr><td class=datos>";
	echo '<span class="users">';
	echo $lang_label["there_are"] ."<b>". $row[0] . '</b> ' . $lang_label["user_defined"];
	echo '</span>';

	echo "<tr><td class=datos>";
	$query1 = "SELECT COUNT(id_incidencia) FROM tincidencia";
	$result = mysql_query ($query1);
	$row = mysql_fetch_array ($result);
	echo '<span class="agents">';
	echo $lang_label["there_are"] . "<b>".$row[0]."</b> ". $lang_label["incidents"];
	echo '</span>';
	

	echo "</table>";
	echo '</div>'; // class "jus"


?>