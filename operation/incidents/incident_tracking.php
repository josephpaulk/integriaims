<?php

// TOPI
// ========================================
// Copyright (c) 2006-2007 Sancho Lerena, slerena@openideas.info

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
 	audit_db("Noauth",$config["REMOTE_ADDR"], "No authenticated access","Trying to access event viewer");
	require ("general/noaccess.php");
	exit;
}


$id_grupo = "";
$creacion_incidente = "";


if (isset($_GET["id"])){
	$id_inc = $_GET["id"];
	$iduser_temp=$_SESSION['id_usuario'];
	// Obtain group of this incident
	$sql1 = 'SELECT * FROM tincidencia WHERE id_incidencia = '.$id_inc;
	$result = mysql_query($sql1);
	$row = mysql_fetch_array($result);
	// Get values
	$titulo = $row["titulo"];
	$id_grupo = $row["id_grupo"];
	$id_creator = $row["id_creator"];
	$grupo = dame_nombre_grupo($id_grupo);
	$id_user = $_SESSION['id_usuario'];
	if (give_acl ($iduser_temp, $id_grupo, "IR") != 1){
	 	// Doesn't have access to this page
		audit_db ($id_user,$config["REMOTE_ADDR"], "ACL Violation","Trying to access to incident ".$id_inc." '".$titulo."'");
		include ("general/noaccess.php");
		exit;
	}


} else {
	audit_db($id_user,$config["REMOTE_ADDR"], "ACL Violation","Trying to access to incident ".$id_inc." '".$titulo."'");
	include ("general/noaccess.php");
	exit;
}


// ********************************************************************
// Notes
// ********************************************************************
$cabecera=0;
$sql4='SELECT * FROM tincident_track WHERE id_incident= '.$id_inc;


$color = 0;
echo "<h1>".$lang_label["incident_tracking"]."</h1>";
echo "<h3>";
echo give_inc_title($id_inc);
echo "</h3>";

echo "<table cellpadding='3' cellspacing='3' border='0' width=600>";

if ($res4=mysql_query($sql4)){
	echo "<tr><th>".$lang_label["state"]."<th>".$lang_label["user"]."<th  width='80'>".$lang_label["timestamp"];
	while ($row2=mysql_fetch_array($res4)){
		$timestamp = $row2["timestamp"];
		$state = $row2["state"];
		$user = $row2["id_user"];
		$aditional_data = $row2["id_aditional"];
		
		if ($color == 1){
			$tdcolor = "datos";
			$color = 0;
		} else {
			$tdcolor = "datos2";
			$color = 1;
		}
		
		echo '<tr><td class="' . $tdcolor . '">';

		switch($state){
			case 0: $descripcion = $lang_label["incident_creation"];
				break;
			case 1: $descripcion = $lang_label["incident_updated"];
				break;
			case 2: $descripcion = $lang_label["incident_note_added"];
				break;
			case 3: $descripcion = $lang_label["incident_file_added"];
				break;
			case 4: $descripcion = $lang_label["incident_note_deleted"];
				break;
			case 5: $descripcion = $lang_label["incident_file_deleted"];
				break;
			case 6: $descripcion = $lang_label["incident_change_priority"];
				break;
			case 7: $descripcion = $lang_label["incident_change_status"];
				break;
			case 8: $descripcion = $lang_label["incident_change_resolution"];
				break;
			case 9: $descripcion = $lang_label["incident_workunit_added"];
				break;
 			default: $descripcion = $lang_label["unknown"];
		}
		if ($state == 6)
			$descripcion .= " -> ".$aditional_data;
	
		if ($state == 7)
			$descripcion .= " -> ". give_db_value ("name", "tincident_status", "id", $aditional_data);
	
		if ($state == 8)
			$descripcion .= " -> ".give_db_value ("name", "tincident_resolution", "id", $aditional_data);

		echo $descripcion;
		echo '<td class="' . $tdcolor . '">';
		$nombre_real = dame_nombre_real($user);
		echo " $nombre_real";
		echo '<td class="' . $tdcolor . '">';
		echo $timestamp;
	}
echo "</table>"; 
} else
	echo $lang_label["no_data"];

?>
