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

// Load global vars
global $config;

if (check_login() != 0) {
	audit_db("Noauth",$config["REMOTE_ADDR"], "No authenticated acces","Trying to access incident viewer");
	require ("general/noaccess.php");
	exit;
}

$id_user =$_SESSION["id_usuario"];

echo "<h2>".lang_string ('users_')."</h2>";

echo '<table width="90%" class="listing">';
echo "<th>".lang_string ('user_ID');
echo "<th>".lang_string ('last_contact');
echo "<th>".lang_string ('profile');
echo "<th>".lang_string ('name');
echo "<th>".lang_string ('description');


$resq1=mysql_query("SELECT * FROM tusuario");
while ($rowdup=mysql_fetch_array($resq1)){
	$nombre=$rowdup["id_usuario"];
	$nivel =$rowdup["nivel"];
	$comentarios =$rowdup["comentarios"];
	$fecha_registro =$rowdup["fecha_registro"];
	$avatar = $rowdup["avatar"];
	
	if (user_visible_for_me ($config["id_user"], $rowdup["id_usuario"]) == 1){
		echo "<tr><td><a href='index.php?sec=users&sec2=operation/users/user_edit&ver=".$nombre."'><b>".$nombre."</b></a>";
		echo "<td class='f9'>".$fecha_registro;
		echo "<td>";
		print_user_avatar ($rowdup["id_usuario"], true);
		

		$sql1='SELECT * FROM tusuario_perfil WHERE id_usuario = "'.$nombre.'"';
		$result=mysql_query($sql1);
		echo "<a href='#' class='tip'>&nbsp;<span>";
		if (mysql_num_rows($result)){
			while ($row=mysql_fetch_array($result)){
				echo dame_perfil($row["id_perfil"])."/ ";
				echo dame_grupo($row["id_grupo"])."<br>";
			}
		}
		else { echo lang_string ('no_profile'); }
		echo "</span></a>";
		echo "<td>".substr(clean_output($rowdup["nombre_real"]),0,16);
		echo "<td>".substr(clean_output($comentarios),0,32);
	}
}

echo "</table>";

enterprise_include ("operation/user/user_defined_profiles.php");

?>
