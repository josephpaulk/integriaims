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

echo "<h3>".lang_string ('definedprofiles')."</h3>";
echo '<table width="90%" class="listing">';

$query_del1="SELECT * FROM tprofile";
$resq1=mysql_query($query_del1);
echo "<tr>";

echo "<th width='180px'><font size=1>".lang_string ('profiles');
echo "<th width='40px'><font size=1>IR<a href='#' class='tipp'>&nbsp;<span>".$help_label["IR"]."</span></a>";
echo "<th width='40px'><font size=1>IW<a href='#' class='tipp'>&nbsp;<span>".$help_label["IW"]."</span></a>";
echo "<th width='40px'><font size=1>IM<a href='#' class='tipp'>&nbsp;<span>".$help_label["IM"]."</span></a>";

echo "<th width='40px'><font size=1>UM<a href='#' class='tipp'>&nbsp;<span>".$help_label["UM"]."</span></a>";
echo "<th width='40px'><font size=1>DM<a href='#' class='tipp'>&nbsp;<span>".$help_label["DM"]."</span></a>";
echo "<th width='40px'><font size=1>FM<a href='#' class='tipp'>&nbsp;<span>".$help_label["FM"]."</span></a>";

echo "<th width='40px'><font size=1>AR<a href='#' class='tipp'>&nbsp;<span>".$help_label["AR"]."</span></a>";
echo "<th width='40px'><font size=1>AW<a href='#' class='tipp'>&nbsp;<span>".$help_label["AW"]."</span></a>";
echo "<th width='40px'><font size=1>AM<a href='#' class='tipp'>&nbsp;<span>".$help_label["AM"]."</span></a>";

echo "<th width='40px'><font size=1>PR<a href='#' class='tipp'>&nbsp;<span>".$help_label["PR"]."</span></a>";
echo "<th width='40px'><font size=1>PW<a href='#' class='tipp'>&nbsp;<span>".$help_label["PW"]."</span></a>";
echo "<th width='40px'><font size=1>PM<a href='#' class='tipp'>&nbsp;<span>".$help_label["PM"]."</span></a>";

echo "<th width='40px'><font size=1>TW<a href='#' class='tipp'>&nbsp;<span>".$help_label["TW"]."</span></a>";
echo "<th width='40px'><font size=1>TM<a href='#' class='tipp'>&nbsp;<span>".$help_label["TM"]."</span></a>";

while ($rowdup=mysql_fetch_array($resq1)){
	$id_perfil = $rowdup["id"];
	$nombre=$rowdup["name"];
	
	$ir = $rowdup["ir"];
	$iw = $rowdup["iw"];
	$im = $rowdup["im"];

	$um = $rowdup["um"];
	$dm = $rowdup["dm"];
	$fm = $rowdup["fm"];

	$ar = $rowdup["ar"];
	$aw = $rowdup["aw"];
	$am = $rowdup["am"];

	$pr = $rowdup["pr"];
	$pw = $rowdup["pw"];
	$tw = $rowdup["tw"];
	$tm = $rowdup["tm"];
	$pm = $rowdup["pm"];
	echo "<tr><td>".$nombre;
	
	echo "<td>";
	if ($ir == 1) echo "<img src='images/ok.png' border=0>";
		
	echo "<td>";
	if ($iw == 1) echo "<img src='images/ok.png' border=0>";
		
	echo "<td>";
	if ($im == 1) echo "<img src='images/ok.png' border=0>";

	echo "<td>";
	if ($um == 1) echo "<img src='images/ok.png' border=0>";
		
	echo "<td>";
	if ($dm == 1) echo "<img src='images/ok.png' border=0>";
		
	echo "<td>";
	if ($fm == 1) echo "<img src='images/ok.png' border=0>";
// agenda
	echo "<td>";
	if ($ar == 1) echo "<img src='images/ok.png' border=0>";
		
	echo "<td>";
	if ($aw == 1) echo "<img src='images/ok.png' border=0>";
		
	echo "<td>";
	if ($am == 1) echo "<img src='images/ok.png' border=0>";
// Project
	echo "<td>";
	if ($pr == 1) echo "<img src='images/ok.png' border=0>";
		
	echo "<td>";
	if ($pw == 1) echo "<img src='images/ok.png' border=0>";

	echo "<td>";
	if ($pm == 1) echo "<img src='images/ok.png' border=0>";			

	echo "<td>";
	if ($tw== 1) echo "<img src='images/ok.png' border=0>";

	echo "<td>";
	if ($tm== 1) echo "<img src='images/ok.png' border=0>";

}
echo "</div></table>";
?>
