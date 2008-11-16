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
	echo "<h1>" . __('Welcome to Integria') . "</h1>";
	echo "<p>";
	echo __('This is the Web Management System for Integria. From here you can manage its agents, alerts and incidents. Session will be open while activity exists.');
	echo "</p>";

	// Show last activity from this user
	echo "<h2>" . __('This is your last activity in Integria console') . "</h2>";
	// Show table header
	echo '<table cellpadding="3" cellspacing="3" width="740"><tr>'; 
	echo '<th>' . __('User') . '</th>';
	echo '<th>' . __('Action') . '</th>';
	echo '<th>' . __('Date') . '</th>';
	echo '<th>' . __('Source IP') . '</th>';
	echo '<th>' . __('Comments') . '</th></tr>';

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


?>
