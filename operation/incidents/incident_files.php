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

global $config;

if (check_login() != 0) {
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
	$sql1 = 'SELECT * FROM tincidencia WHERE id_incidencia = '.$id_inc;
	$result = mysql_query($sql1);
	$row = mysql_fetch_array($result);
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

	if (give_acl($config["id_user"], $id_grupo, "IR") != 1){
	 	// Doesn't have access to this page
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation","Trying to access to incident ".$id_inc." '".$titulo."'");
		include ("general/noaccess.php");
		exit;
	}

	// ------------
 	// Delete file
 	// ------------
	if (((give_acl($iduser_temp, $id_grupo, "IM")==1) OR ($usuario == $iduser_temp)) AND isset($_GET["delete_file"])){
		$file_id = $_GET["delete_file"];
		$sql2 = "SELECT * FROM tattachment WHERE id_attachment = ".$file_id;
		$res2=mysql_query($sql2);
		$row2=mysql_fetch_array($res2);
		$filename = $row2["filename"];
		$sql2 = "DELETE FROM tattachment WHERE id_attachment = ".$file_id;
		$res2=mysql_query($sql2);
		unlink ($config["homedir"]."/attachment/pand".$file_id."_".$filename);
		incident_tracking ( $id_inc, $id_usuario, 7);
	}


} else {
	audit_db($id_user,$REMOTE_ADDR, "ACL Violation","Trying to access to incident ".$id_inc." '".$titulo."'");
		include ("general/noaccess.php");
		exit;
}

// ************************************************************
// Files attached to this incident
// ************************************************************

// Attach head if there's attach for this incident
$att_fil=mysql_query("SELECT * FROM tattachment WHERE id_incidencia = ".$id_inc);

echo "<h3>".lang_string ("Incident"). " #$id_inc - ".give_inc_title ($id_inc)."</h3>";

if (mysql_num_rows($att_fil)){
	echo "<table width='750' cellspacing=4 cellpadding=4 class='databox'><tr><th class=datos>".lang_string ('filename');
	echo "<th class=datos>".lang_string ('description');
	echo "<th class=datos>".lang_string ('size');
	echo "<th class=datos>".lang_string ('delete');

	while ($row=mysql_fetch_array ($att_fil)){
		echo "<tr><td class=datos><img src='images/disk.png' border=0 align='top'>  <a target='_new' href='attachment/pand".$row["id_attachment"]."_".$row["filename"]."'>".$row["filename"]."</a>";
		echo "<td class=datos>".$row["description"];
		echo "<td class=datos>".byte_convert ($row["size"]);

		if (give_acl($iduser_temp, $id_grupo, "IM")==1){ // Delete attachment
			echo '<td class=datos align="center"><a href="index.php?sec=incidencias&sec2=operation/incidents/incident_files&id='.$id_inc.'&delete_file='.$row["id_attachment"].'"><img src="images/delete.png" border=0>';
		}

	}
	echo "</table><br>";
} else {
	echo '<h4>'.lang_string ('no_data').'</h4>';
}
?>
