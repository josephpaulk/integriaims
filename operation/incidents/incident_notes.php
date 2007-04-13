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
				$result_msg = "<h3 class='suc'>".$lang_label["del_note_ok"];
			incident_tracking ( $id_inc, $id_usuario, 6);
		}
	}


	echo "<div id='menu_tab'><ul class='mn'>";
	
	// Indicent main
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
$sql4='SELECT * FROM tnota_inc WHERE id_incidencia = '.$id_inc;

echo "<h3>".$lang_label["in_notas_t1"]." #$id_inc '".give_inc_title($id_inc)."'</h3>";
		echo $result_msg;
if ($res4=mysql_query($sql4)){
	echo "<table cellpadding='3' cellspacing='3' border='0'>";
	echo "<tr><td>";
	while ($row2=mysql_fetch_array($res4)){
		$sql3='SELECT * FROM tnota WHERE id_nota = '.$row2["id_nota"].' ORDER BY timestamp DESC';
		$res3=mysql_query($sql3);
		while ($row3=mysql_fetch_array($res3)){
			$timestamp = $row3["timestamp"];
			$nota = $row3["nota"];
			$id_usuario_nota = $row3["id_usuario"];
			// Show data
			echo "<tr><td class='datos2' colspan=3>";
			echo '<tr><td rowspan="3"  class="top"><img src="images/note.png"></td><td class="datos" width=40><b>'.$lang_label["author"].': </b><td class="datos">';
			$usuario = $id_usuario_nota;
			$nombre_real = dame_nombre_real($usuario);
			echo $usuario." - (<i><a href='index.php?sec=usuario&sec2=operation/users/user_edit&ver=".$usuario."'>".$nombre_real."</a></i>)";

			// Delete comment, only for admins
			if ((give_acl($iduser_temp, $id_grupo, "IM")==1) OR ($usuario == $iduser_temp)) {
				$myurl="index.php?sec=incidencias&sec2=operation/incidents/incident_notes&id=".$id_inc."&id_nota=".$row2["id_nota"]."&id_nota_inc=".$row2["id_nota_inc"];
				echo '<td rowspan="3" class="top" width="60" align="center"><a href="'.$myurl.'"><img src="images/cross.png" align="bottom" border="0"><br> '.$lang_label["delete"].'</a>';
			}
			echo '<tr><td class="datos2"><b>'.$lang_label["date"].': </b><td class="datos2"><i>'.$timestamp.'</i></td></tr>';
			echo '<tr><td class="datos" valign="top"><b>'.$lang_label["description"]."</b>";
			echo '<td class="datos">';
			echo '<table border="0" cellpadding="5" cellspacing="5" style="width: 450px"><tr><td class="f9" align="justify">';
			echo  clean_output_breaks($nota);
			echo '</table>';
		}
	}
	echo "</table>";
} else 
	echo $lang_label["no_data"];

?>
