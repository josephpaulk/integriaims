<?php

// TOPI 
// ========================================================
// Copyright (c) 2004-2007 Sancho Lerena, slerena@gmail.com

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
	audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access","Trying to access incident viewer");
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
	$texto = $row["descripcion"];
	$inicio = $row["inicio"];
	$actualizacion = $row["actualizacion"];
	$estado = $row["estado"];
	$prioridad = $row["prioridad"];
	$origen = $row["origen"];
	$usuario = $row["id_usuario"];
	$nombre_real = dame_nombre_real($usuario);
	$id_grupo = $row["id_grupo"];
	$id_creator = $row["id_creator"];
	$grupo = dame_nombre_grupo($id_grupo);
	$result_msg = "";
	
	$id_user=$_SESSION['id_usuario'];
	if (give_acl($iduser_temp, $id_grupo, "IR") != 1){
	 	// Doesn't have access to this page
		audit_db($id_user,$REMOTE_ADDR, "ACL Violation","Trying to access to incident ".$id_inc." '".$titulo."'");
		include ("general/noaccess.php");
		exit;
	}
	
	// Delete note
	if (isset($_GET["id_nota"])){
		$note_user = give_note_author ($_GET["id_nota"]);
		if (((give_acl($iduser_temp, $id_grupo, "IM")==1) OR ($note_user == $iduser_temp)) OR ($usuario = $iduser_temp) ) { // Only admins (manage incident) or owners can modify incidents, including their notes
		// But note authors was able to delete this own notes
			$id_nota = $_GET["id_nota"];
			$id_nota_inc = $_GET["id_nota_inc"];
			$query = "DELETE FROM tnota WHERE id_nota = ".$id_nota;
			$query2 = "DELETE FROM tnota_inc WHERE id_nota_inc = ".$id_nota_inc;
			//echo "DEBUG: DELETING NOTE: ".$query."(----)".$query2;
			mysql_query($query);
			mysql_query($query2);
			if (mysql_query($query))
				$result_msg = "<h3 class='suc'>".$lang_label["del_note_ok"]."</h3>";
			incident_tracking ( $id_inc, $id_usuario, 6);
		}
	}


	echo "<div id='menu_tab'><ul class='mn'>";
	
	// Incident main
	echo "<li class='nomn'>";
	echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_detail&id=$id_inc'><img src='images/page_white_text.png' class='top' border=0> ".$lang_label["Incident"]." </a>";
	echo "</li>";

	// Tracking
	echo "<li class='nomn'>";
	echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_tracking&id=$id_inc'><img src='images/eye.png' class='top' border=0> ".$lang_label["tracking"]." </a>";
	echo "</li>";
	
	// Workunits
	$timeused = give_hours_incident ( $id_inc);
	echo "<li class='nomn'>";
	if ($timeused > 0)
		echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_work&id_inc=$id_inc'><img src='images/award_star_silver_1.png' class='top' border=0> ".$lang_label["workunits"]." ($timeused)</a>";
	else
		echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_work&id_inc=$id_inc'><img src='images/award_star_silver_1.png' class='top' border=0> ".$lang_label["workunits"]."</a>";
	echo "</li>";

	// Attach
	$file_number = give_number_files_incident($id_inc);
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

echo $result_msg;
echo "<br>";
$title = $lang_label["in_notas_t1"]." #$id_inc '".give_inc_title($id_inc)."'";
echo "<h3>$title</h3>";

$sql4='SELECT * FROM tworkunit_incident WHERE id_incident = '.$id_inc.' ORDER BY id_workunit ASC';
if ($res4=mysql_query($sql4)){
	while ($row4=mysql_fetch_array($res4)){
		$sql3='SELECT * FROM tworkunit WHERE id = '.$row4["id_workunit"];
echo "DEBUG $sql3";
		$res3=mysql_query($sql3);
		while ($row3=mysql_fetch_array($res3)){
			show_workunit_data ($row3, $title);
		}
	}
} else 
	echo $lang_label["no_data"];

?>
