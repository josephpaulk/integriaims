<?php

// Pandora FMS - the Free monitoring system
// ========================================
// Copyright (c) 2004-2007 Sancho Lerena, slerena@openideas.info
// Copyright (c) 2005-2007 Artica Soluciones Tecnologicas
// Copyright (c) 2004-2007 Raul Mateos Martin, raulofpandora@gmail.com
// Copyright (c) 2006-2007 Jose Navarro jose@jnavarro.net
// Copyright (c) 2006-2007 Jonathan Barajas, jonathan.barajas[AT]gmail[DOT]com

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
require("include/config.php");

if (comprueba_login() != 0) {
 	audit_db("Noauth",$REMOTE_ADDR, "No authenticated access","Trying to access event viewer");
	require ("general/noaccess.php");
	exit;
}


$id_grupo = "";
$creacion_incidente = "";


if (isset($_GET["id"])){
	$id_inc = $_GET["id"];
	$iduser_temp=$_SESSION['id_usuario'];
	// Obtain group of this incident
	$sql1='SELECT * FROM tincidencia WHERE id_incidencia = '.$id_inc;
	$result=mysql_query($sql1);
	$row=mysql_fetch_array($result);
	// Get values
	$titulo = $row["titulo"];
	$id_grupo = $row["id_grupo"];
	$id_creator = $row["id_creator"];
	$grupo = dame_nombre_grupo($id_grupo);
	$id_user=$_SESSION['id_usuario'];
	if (give_acl($iduser_temp, $id_grupo, "IR") != 1){
	 	// Doesn't have access to this page
		audit_db($id_user,$REMOTE_ADDR, "ACL Violation","Trying to access to incident ".$id_inc." '".$titulo."'");
		include ("general/noaccess.php");
		exit;
	}

	echo "<div id='menu_tab'><ul class='mn'>";

	// Incident main
	echo "<li class='nomn'>";
	echo "<a href='index.php?sec=incidencias&sec2=operation/incidents/incident_detail&id=$id_inc'><img src='images/page_white_text.png' class='top' border=0> ".$lang_label["Incident"]." </a>";
	echo "</li>";

	// Tracking
	echo "<li class='nomn'>";
	echo "<a href='index.php?sec=incidencias&sec2=operation/incidents/incident_tracking&id=$id_inc'><img src='images/eye.png' class='top' border=0> ".$lang_label["tracking"]." </a>";
	echo "</li>";

	
	// Attach
	$file_number = give_number_files($id_inc);
	if ($file_number > 0){
		echo "<li class='nomn'>";
		echo "<a href='index.php?sec=incidencias&sec2=operation/incidents/incident_files&id=$id_inc'><img src='images/disk.png' class='top' border=0> ".$lang_label["Attachment"]." ($file_number) </a>";
		echo "</li>";
	}

	// Notes
	$note_number = dame_numero_notas($id_inc);
	if ($note_number > 0){
		echo "<li class='nomn'>";
		echo "<a href='index.php?sec=incidencias&sec2=operation/incidents/incident_notes&id=$id_inc'><img src='images/note.png' class='top' border=0> ".$lang_label["Notes"]." ($note_number) </a>";
		echo "</li>";
	}


	echo "</ul>";
	echo "</div>";
	echo "<div style='height: 25px'> </div>";

} else {
	audit_db($id_user,$REMOTE_ADDR, "ACL Violation","Trying to access to incident ".$id_inc." '".$titulo."'");
		include ("general/noaccess.php");
		exit;
}


// ********************************************************************
// Notes
// ********************************************************************
$cabecera=0;
$sql4='SELECT * FROM tincident_track WHERE id_incident= '.$id_inc;


$color = 0;
echo "<h3>".$lang_label["incident_tracking"]."</h3>";
echo "<table cellpadding='3' cellspacing='3' border='0' width=600>";

if ($res4=mysql_query($sql4)){
	echo "<tr><th>".$lang_label["state"]."<th>".$lang_label["user"]."<th  width='80'>".$lang_label["timestamp"];
	while ($row2=mysql_fetch_array($res4)){
		$timestamp = $row2["timestamp"];
		$state = $row2["state"];
		$user = $row2["id_user"];

		if ($color == 1){
			$tdcolor = "datos";
			$color = 0;
		} else {
			$tdcolor = "datos2";
			$color = 1;
		}
		
		echo '<tr><td class="' . $tdcolor . '">';
		switch($state){
		case 0: echo $lang_label["incident_creation"];
			break;
		case 1: echo $lang_label["incident_updated"];
			break;
		case 2: echo $lang_label["incident_note_added"];
			break;
		case 3: echo $lang_label["incident_file_added"];
			break;
		case 4: echo $lang_label["incident_change_status_to_not_valid"];
			break;
		case 5: echo $lang_label["incident_change_status_to_outofdate"];
			break;
		case 6: echo $lang_label["incident_note_deleted"];
			break;
		case 7: echo $lang_label["incident_file_deleted"];
			break;
		case 8: echo $lang_label["incident_change_priority"];
			break;
		case 10: echo $lang_label["incident_closed"];
			break;
		}
		echo '<td class="' . $tdcolor . '">';
		echo $user;
		$nombre_real = dame_nombre_real($user);
		echo " <i>( $nombre_real )</i>";
		echo '<td class="' . $tdcolor . '">';
		echo $timestamp;
	}
echo "</table>"; 
} else
	echo $lang_label["no_data"];

?>
