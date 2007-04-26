<?php

// FRITS - the FRee Incident Tracking System
// =========================================
// Copyright (c) 2007 Sancho Lerena, slerena@openideas.info
// Copyright (c) 2007 Artica Soluciones Tecnologicas

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
require "include/config.php";
require "include/functions_form.php";

if (comprueba_login() != 0) {
 	audit_db("Noauth",$REMOTE_ADDR, "No authenticated access","Trying to access event viewer");
	require ("general/noaccess.php");
	exit;
}

if (isset($_GET["id_grupo"]))
	$id_grupo = $_GET["id_grupo"];
else
	$id_grupo = 0;

$id_user = $_SESSION['id_usuario'];
if (give_acl($id_user, $id_grupo, "IR") != 1){
 	// Doesn't have access to this page
	audit_db($id_user,$REMOTE_ADDR, "ACL Violation","Trying to access to project detail page");
	include ("general/noaccess.php");
	exit;
}

$create_mode = 0;
$name = "";
$description = "";
$end_date = "";
$start_date = "";
$private = 0;
$id_project = -1; // Create mode by default

// Edition / View mode

if (isset($_GET["id"])){
	$creacion_incidente = 0;
	$id_project = $_GET["id"];
	$iduser_temp=$_SESSION['id_usuario'];
	// Obtain group of this incident
	$sql1='SELECT * FROM tproject WHERE id = '.$id_project;
	if (!$result=mysql_query($sql1)){
        audit_db($id_user_temp,$REMOTE_ADDR, "ACL Violation","Trying to access to other project hacking with URL");
        include ("general/noaccess.php");
        exit;  
    }   
	$row=mysql_fetch_array($result);
	// Get values
	$name = $row["name"];
	$description = $row["description"];
	$start_date = $row["start"];
    $end_date = $row["end"];
    $group = $row["id_group"];
    $owner = $row["id_owner"];
    $private = $row["private"];
 
	// SHOW TABS
	echo "<div id='menu_tab'><ul class='mn'>";

	// Main
	echo "<li class='nomn'>";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/project_detail&id=$id_project'><img src='images/application_edit.png' class='top' border=0> ".$lang_label["project"]."</a>";
	echo "</li>";

	// Tasks
	echo "<li class='nomn'>";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/task&id_project=$id_project'><img src='images/page_white_text.png' class='top' border=0> ".$lang_label["tasks"]." () </a>";
	echo "</li>";

	// Tracking
	echo "<li class='nomn'>";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/tracking&id=$id_project'><img src='images/eye.png' class='top' border=0> ".$lang_label["tracking"]." </a>";
	echo "</li>";
	
	// People
	echo "<li class='nomn'>";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/people_manager&id=$id_project'><img src='images/user_suit.png' class='top' border=0> ".$lang_label["people"]." </a>";
	echo "</li>";
	
	echo "</ul>";
	echo "</div>";
	echo "<div style='height: 25px'> </div>";
} 

// Create project form

elseif (isset($_GET["insert_form"])){
		$email_notify=0;
		$iduser_temp=$_SESSION['id_usuario'];
		$titulo = "";
		$prioridad = 0;
		$id_grupo = 0;
		$grupo = dame_nombre_grupo(1);

		$usuario= $_SESSION["id_usuario"];
		$estado = 0;
		$actualizacion=date("Y/m/d H:i:s");
		$inicio = $actualizacion;
		$id_creator = $iduser_temp;
		$create_mode = 1;
} else {
	audit_db ($id_user, $REMOTE_ADDR, "HACK", "Trying to create/access project in a unusual way");
	no_permission();

}

// ********************************************************************************************************
// Show the form
// ********************************************************************************************************

if ($create_mode = 1)
	echo "<form name='projectf' method='POST' action='index.php?sec=projects&sec2=operation/projects/project&action=insert'>";
else
	echo "<form name='projectf' method='POST' action='index.php?sec=projects&sec2=operation/projects/project&action=update'>";

if (isset($id_project)) {
	echo "<input type='hidden' name='id_project' value='".$id_project."'>";
}
 
// --------------------
// Main project table
// --------------------

echo "<h2>".$lang_label["project_management"]." -&gt;";
if ($create_mode = 0){
	echo $lang_label["rev_project"]." # ".$id_inc."</h2>";
} else {
	echo $lang_label["create_project"]."</h2>";
}

echo '<table width=700 class="databox_color" cellpadding=3 cellspacing=3>';

// Name

echo '<tr><td class="datos"><b>'.$lang_label["name"].'</b>';
echo '<td colspan=2 class="datos"><input type="text" name="name" size=50 value="'.$name.'">';

// Private
echo '<td class="datos">';
if ($private == 1)
	echo "<input type=checkbox value=1 name='private' CHECKED>";
else
	echo "<input type=checkbox value=1 name='private'>";
echo " <b>".$lang_label["private"]."</b>";

// start and end date
echo '<tr><td class="datos2"><b>'.$lang_label["start"].'</b>';
echo "<td class='datos2'>";
//echo "<input type='text' id='start_date' onclick='scwShow(this,this);' name='start_date' size=10 value='$start_date'> 
echo "<input type='text' id='start_date' name='start_date' size=10 value='$start_date'> <img src='images/calendar_view_day.png' onclick='scwShow(scwID(\"start_date\"),this);'> ";
echo '<td class="datos2"><b>'.$lang_label["end"].'</b>';
echo "<td class='datos2'>";
echo "<input type='text' id='end_date' name='end_date' size=10 value='$end_date'> <img src='images/calendar_view_day.png' title='Click Here' alt='Click Here' onclick='scwShow(scwID(\"end_date\"),this);'>";

// Group and owner

echo '<tr><td class="datos"><b>'.$lang_label["group"].'</b>';
echo "<td class='datos'>";
combo_groups ();

echo '<td class="datos"><b>'.$lang_label["owner"].'</b>';
echo "<td class='datos'>";
combo_users ();

// Description

echo '<tr><td class="datos2" colspan="4"><textarea name="description" rows="15" cols="85" style="height: 200px">';
	echo $description;
echo "</textarea>";

echo "</table>";

if ($create_mode == 0){
	echo '<input type="submit" class="sub next" name="accion" value="'.$lang_label["in_modinc"].'" border="0">';
	
} else {
	echo '<input type="submit" class="sub create" name="accion" value="'.$lang_label["create"].'" border="0">';
}
echo "</form>";
echo "</table>";


?>
