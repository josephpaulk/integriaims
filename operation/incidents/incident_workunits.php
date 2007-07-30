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
}

// ********************************************************************
// Workunit view
// ********************************************************************

echo $result_msg;

echo "<h1>".$lang_label["workunit_resume"]."</h1>";
echo "<h3>";
echo "#".$id_inc." ".give_inc_title($id_inc);
echo "</h3>";


$sql4='SELECT * FROM tworkunit_incident WHERE id_incident = '.$id_inc.' ORDER BY id_workunit ASC';
if ($res4=mysql_query($sql4)){
	while ($row4=mysql_fetch_array($res4)){
		$sql3='SELECT * FROM tworkunit WHERE id = '.$row4["id_workunit"];
		$res3=mysql_query($sql3);
		while ($row3=mysql_fetch_array($res3)){
			show_workunit_data ($row3, $title);
		}
	}
} else 
	echo $lang_label["no_data"];

?>
