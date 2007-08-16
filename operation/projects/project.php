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

$id_user = $config["id_user"];

if (give_acl($id_user, 0, "IR")!=1) {
	audit_db($id_user,$REMOTE_ADDR, "ACL Violation","Trying to access incident viewer");
	require ("general/noaccess.php");
	exit;
}


$accion = "";
// Delete project
if (isset($_GET["quick_delete"])){
	$id_project = $_GET["quick_delete"];
	$id_owner = give_db_value ("id_owner", "tproject", "id", $id_project);
	if ($id_owner == $id_user){
	echo "DEBUG: Borrado temporalmente desactivado hasta que no se implemente une medida de seguridad adicional... me dais un miedo tremendo ! :-) <br>";
		// delete_project ($id_project);
		echo "<h3 class='suc'>".$lang_label["del_incid_ok"]."</h3>";
		audit_db($id_user,$REMOTE_ADDR,"Project deleted","User ".$id_user." deleted project #".$id_project);
	} else {
		audit_db ($id_user,$REMOTE_ADDR,"ACL Forbidden","User ".$id_user." try to delete project #$id_project");
		echo "<h3 class='error'>".$lang_label["del_incid_no"]."</h3>";
		no_permission();
	}
}

// INSERT PROJECT
if ((isset($_GET["action"])) AND ($_GET["action"]=="insert")){
	if (give_acl($config["id_user"], 0, "PW") == 1){
		// Read input variables
		$usuario = give_parameter_post ("user");
		$name = give_parameter_post ("name");
		$description = give_parameter_post ('description');
		$start_date = give_parameter_post ('start_date');
		$end_date = give_parameter_post ('end_date');
		$id_owner = $usuario;
		$sql = " INSERT INTO tproject
			(name, description, start, end, id_owner) VALUES
			('$name', '$description', '$start_date', '$end_date', '$id_owner') ";
		if (mysql_query($sql)){
			$id_inc = mysql_insert_id();
			echo "<h3 class='suc'>".$lang_label["create_project_ok"]." ( id #$id_inc )</h3>";
			audit_db ($usuario, $REMOTE_ADDR, "Project created", "User ".$id_user." created project '$name'");
		} else {
			echo "<h3 class='err'>".$lang_label["create_project_bad"]." ( id #$id_inc )</h3>";
		}
	} else {
		audit_db($id_user, $REMOTE_ADDR, "ACL Forbidden", "User ".$id_user. " try to create project");
		no_permission();
	}
}


// MAIN LIST OF PROJECTS

echo "<h2>".$lang_label["project_management"]."</h2>";


// -------------
// Show headers
// -------------
echo "<table width='680' class='databox'>";
echo "<tr>";
echo "<th>".$lang_label["name"];
echo "<th>".$lang_label["completion"];
echo "<th>".$lang_label["time_used"];
echo "<th width=82>".$lang_label["updated_at"];
$color = 1;

// -------------
// Show DATA TABLE
// -------------

// Simple query, needs to implement group control and ACL checking
$sql2="SELECT * FROM tproject"; 
if ($result2=mysql_query($sql2))	
while ($row2=mysql_fetch_array($result2)){
	if (give_acl($config["id_user"], 0, "PR") ==1){
		if ($color == 1){
			$tdcolor = "datos";
			$color = 0;
		}
		else {
			$tdcolor = "datos2";
			$color = 1;
		}
			
		if (user_belong_project ($id_user, $row2["id"]) != 0){	
			echo "<tr>";

			// Project name
			echo "<td class='$tdcolor' align='left' >";
			echo "<b><a href='index.php?sec=projects&sec2=operation/projects/task&id_project=".$row2["id"]."'>".$row2["name"]."</a></b></td>";
			// Completion
			echo "<td class='$tdcolor' align='center'>";
			$completion =  format_numeric(calculate_project_progress ($row2["id"]));
			echo "<img src='include/functions_graph.php?type=progress&width=90&height=20&percent=$completion'>";
				
			// Time wasted
			echo "<td class='$tdcolor' align='center'>";
			echo format_numeric(give_hours_project ($row2["id"])). " hr";

			// Last update time
			echo "<td class='$tdcolor'_f9 align='center'>";
			echo "Some time ago";

		/*
		// Delete	
		if ((give_acl($config["id_user"], 0, "PW") ==1) AND ($config["id_user"] == $row2["id_owner"] )) {
			echo "<td class='$tdcolor' align='center'><a href='index.php?sec=projects&sec2=operation/projects/project&quick_delete=".$row2["id"]."' onClick='if (!confirm(\' ".$lang_label["are_you_sure"]."\')) return false;'><img src='images/cross.png' border='0'></a></td>";
		} else
			echo "<td class='$tdcolor' align='center'>";
		*/
		}
	}
}
echo "</table>";


if (give_acl($config["id_user"], 0, "PW")==1) {
	echo "<form name='boton' method='POST'  action='index.php?sec=projects&sec2=operation/projects/project_detail&insert_form'>";
	echo "<input type='submit' class='sub next' name='crt' value='".$lang_label["create_project"]."'>";
	echo "</form>";
}


?>
