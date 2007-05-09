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

global $REMOTE_ADDR;
global $config;


if (check_login() != 0) {
	audit_db("Noauth",$REMOTE_ADDR, "No authenticated acces","Trying to access incident viewer");
	require ("general/noaccess.php");
	exit;
}

$id_usuario =$_SESSION["id_usuario"];
if (give_acl($id_usuario, 0, "IR")!=1) {
	audit_db($id_usuario,$REMOTE_ADDR, "ACL Violation","Trying to access incident viewer");
	require ("general/noaccess.php");
	exit;
}


$accion = "";
// Delete project
if (isset($_GET["quick_delete"])){
	$id_project = $_GET["quick_delete"];
	$sql2="SELECT * FROM tproject WHERE id_project=".$id_project;
	$result2=mysql_query($sql2);
	$row2=mysql_fetch_array($result2);
	if ($row2) {
		$id_author_inc = $row2["id_usuario"];
		if ((give_acl($id_usuario, $row2["id_grupo"], "IM") ==1) OR ($_SESSION["id_usuario"] == $id_author_inc) ){
			delete_project ($id_project);
			echo "<h3 class='suc'>".$lang_label["del_incid_ok"]."</h3>";
			audit_db($id_author_inc,$REMOTE_ADDR,"Project deleted","User ".$id_usuario." deleted project #".$id_project);
		} else {
			audit_db ($id_author_inc,$REMOTE_ADDR,"ACL Forbidden","User ".$_SESSION["id_usuario"]." try to delete project #$id_project");
			echo "<h3 class='error'>".$lang_label["del_incid_no"]."</h3>";
			no_permission();
		}
	}
}


// INSERT PROJECT
if ((isset($_GET["action"])) AND ($_GET["action"]=="insert")){

	$grupo = give_parameter_post ('group');
	$usuario = give_parameter_post ("user");
	if ((give_acl($id_usuario, $grupo, "IM") == 1) OR ($usuario == $id_usuario)) { // Only admins (manage
		// Read input variables
		$name = give_parameter_post ("name");
		$description = give_parameter_post ('description');
		$start_date = give_parameter_post ('start_date');
		$end_date = give_parameter_post ('end_date');
		$private = give_parameter_post ("private",0);
	
		$id_owner = $usuario;
		$sql = " INSERT INTO tproject
			(name, description, id_group, private, start, end, id_owner) VALUES
			('$name', '$description', $grupo, $private, '$start_date', '$end_date', '$id_owner') ";
		if (mysql_query($sql)){
			$id_inc = mysql_insert_id();
			echo "<h3 class='suc'>".$lang_label["create_project_ok"]." ( id #$id_inc )</h3>";
			audit_db ($usuario, $REMOTE_ADDR, "Project created", "User ".$id_usuario." created project '$name'");
		} else {
			echo "<h3 class='err'>".$lang_label["create_project_bad"]." ( id #$id_inc )</h3>";
		}
	} else {
		audit_db($id_usuario, $REMOTE_ADDR, "ACL Forbidden", "User ".$_SESSION["id_usuario"]. " try to create project");
		no_permission();
	}
}


// MAIN LIST OF PROJECTS

echo "<h2>".$lang_label["project_management"];


// -------------
// Show headers
// -------------
echo "<table width='810' class='databox'>";
echo "<tr>";
echo "<th>".$lang_label["name"];
echo "<th>".$lang_label["completion"];
echo "<th>".$lang_label["group"];
echo "<th>".$lang_label["people"];
echo "<th>".$lang_label["tasks"];
echo "<th>".$lang_label["time_used"];
echo "<th>".$lang_label["start"];
echo "<th>".$lang_label["end"];
echo "<th>".$lang_label["delete"];
$color = 1;

// -------------
// Show DATA TABLE
// -------------

// Simple query, needs to implement group control and ACL checking
$sql2="SELECT * FROM tproject"; 
if ($result2=mysql_query($sql2))	
while ($row2=mysql_fetch_array($result2)){
	$id_group = $row2["id_group"];
	if (give_acl($id_usuario, $id_group, "IR") ==1){
		if ($color == 1){
			$tdcolor = "datos";
			$color = 0;
		}
		else {
			$tdcolor = "datos2";
			$color = 1;
		}
		
		echo "<tr>";

		// Project name
		echo "<td class='$tdcolor' align='left' >";
		echo "<b><a href='index.php?sec=projects&sec2=operation/projects/project_detail&id=".$row2["id"]."'>".$row2["name"]."</a></b></td>";

		// Completion
		echo "<td class='$tdcolor' align='center'>";
		$completion =  format_numeric(calculate_project_progress ($row2["id"]));
		echo "<img src='include/functions_graph.php?type=progress&width=90&height=20&percent=$completion'>";
		
		// Group
		echo "<td class='$tdcolor'>".dame_nombre_grupo($row2["id_group"]);
		
		// People
		echo "<td class='$tdcolor'>";
		combo_users_project ($row2["id"]);

		// Tasks
		echo "<td class='$tdcolor'>";
		echo give_number_tasks ($row2["id"]);
		
		// Time wasted
		echo "<td class='$tdcolor'>";
		echo format_numeric(give_hours_project ($row2["id"])). " hr";

		// Start
		echo "<td class='".$tdcolor."f9'>";
		echo substr($row2["start"],0,10);

		// End
		echo "<td class='".$tdcolor."f9'>";
		echo substr($row2["end"],0,10);
		
		if ((give_acl($id_usuario, $id_group, "IM") ==1) OR ($_SESSION["id_usuario"] == $id_author_inc) ){
		// Only incident owners or incident manager
		// from this group can delete incidents
			echo "<td class='$tdcolor' align='center'><a href='index.php?sec=projects&sec2=operation/projects/project&quick_delete=".$row2["id"]."' onClick='if (!confirm(\' ".$lang_label["are_you_sure"]."\')) return false;'><img src='images/cross.png' border='0'></a></td>";
		} else
			echo "<td class='$tdcolor' align='center'>";
	} else {
    echo "  sin acceso";
    } 
}
echo "</table>";



if (give_acl($_SESSION["id_usuario"], 0, "IW")==1) {
	echo "<form name='boton' method='POST'  action='index.php?sec=projects&sec2=operation/projects/project_detail&insert_form'>";
	echo "<input type='submit' class='sub next' name='crt' value='".$lang_label["create_project"]."'>";
	echo "</form>";
}


?>