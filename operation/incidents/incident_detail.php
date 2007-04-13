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
require("include/config.php");

if (comprueba_login() != 0) {
 	audit_db("Noauth",$REMOTE_ADDR, "No authenticated access","Trying to access event viewer");
	require ("general/noaccess.php");
	exit;
}

if (isset($_GET["id_grupo"]))
	$id_grupo = $_GET["id_grupo"];
else
	$id_grupo = 0;

$id_user=$_SESSION['id_usuario'];
if (give_acl($id_user, $id_grupo, "IR") != 1){
 	// Doesn't have access to this page
	audit_db($id_user,$REMOTE_ADDR, "ACL Violation","Trying to access to incident ".$id_inc." '".$titulo."'");
	include ("general/noaccess.php");
	exit;
}

$id_grupo = "";
$creacion_incidente = "";
$result_msg = "";

// EDITION MODE
if (isset($_GET["id"])){
	$creacion_incidente = 0;
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
	$email_notify=$row["notify_email"];
	$grupo = dame_nombre_grupo($id_grupo);

	// --------
	// Note add
	// --------
	if (isset($_GET["insertar_nota"])){
		$id_inc = entrada_limpia($_POST["id_inc"]);
		$timestamp = entrada_limpia($_POST["timestamp"]);
		$nota = entrada_limpia($_POST["nota"]);
		$id_usuario=$_SESSION["id_usuario"];

		$sql1 = "INSERT INTO tnota (id_usuario,timestamp,nota) VALUES ('".$id_usuario."','".$timestamp."','".$nota."')";
		$res1=mysql_query($sql1);
		if ($res1) 
			$result_msg = "<h3 class='suc'>".$lang_label["create_note_ok"]."</h3>";

		$sql2 = "SELECT * FROM tnota WHERE id_usuario = '".$id_usuario."' AND timestamp = '".$timestamp."'";
		$res2=mysql_query($sql2);
		$row2=mysql_fetch_array($res2);
		$id_nota = $row2["id_nota"];

		$sql3 = "INSERT INTO tnota_inc (id_incidencia, id_nota) VALUES (".$id_inc.",".$id_nota.")";
		$res3=mysql_query($sql3);

		$sql4 = "UPDATE tincidencia SET actualizacion = '".$timestamp."' WHERE id_incidencia = ".$id_inc;
		$res4 = mysql_query($sql4);
		incident_tracking ( $id_inc, $id_usuario, 2);
	}
	
	// -----------
	// Upload file
	// -----------
	if ((give_acl($iduser_temp, $id_grupo, "IW")==1) AND isset($_GET["upload_file"])) {
		if ( $_FILES['userfile']['name'] != "" ){ //if file
			$tipo = $_FILES['userfile']['type'];
			if (isset($_POST["file_description"]))
				$description = $_POST["file_description"];
			else
				$description = "No description available";
			// Insert into database
			$filename= $_FILES['userfile']['name'];
			$filesize = $_FILES['userfile']['size'];

			$sql = " INSERT INTO tattachment (id_incidencia, id_usuario, filename, description, size ) VALUES (".$id_inc.", '".$iduser_temp." ','".$filename."','".$description."',".$filesize.") ";

			mysql_query($sql);
			$id_attachment=mysql_insert_id();
			incident_tracking ( $id_inc, $id_usuario, 3);
			$result_msg="<h3 class='suc'>".$lang_label["file_added"]."</h3>";
			// Copy file to directory and change name
			$nombre_archivo = $attachment_store."attachment/pand".$id_attachment."_".$filename;
echo "Source file ".$_FILES['userfile']['tmp_name'];
echo "<br>";
echo "Destination file $nombre_archivo<br>";
			if (!(copy($_FILES['userfile']['tmp_name'], $nombre_archivo ))){
					echo "<h3 class=error>".$lang_label["attach_error"]."</h3>";
				$sql = " DELETE FROM tattachment WHERE id_attachment =".$id_attachment;
				mysql_query($sql);
			} else {
				// Delete temporal file
				unlink ($_FILES['userfile']['tmp_name']);
			}
		}
	}

	// SHOW TABS
	echo "<div id='menu_tab'><ul class='mn'>";

	// This view
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



} // else Not given id
// Create incident from event... read event data
elseif (isset($_GET["insert_form"])){
		$email_notify=0;
		$iduser_temp=$_SESSION['id_usuario'];
		$titulo = "";
		if (isset($_GET["from_event"])){
			$titulo = return_event_description($_GET["from_event"]);
			$descripcion = "";
			$origen = "Pandora FMS event";
		} else {
			$titulo = "";
			$descripcion = "";
			$origen = "";
		}
		$prioridad = 0;
		$id_grupo = 0;
		$grupo = dame_nombre_grupo(1);

		$usuario= $_SESSION["id_usuario"];
		$estado = 0;
		$actualizacion=date("Y/m/d H:i:s");
		$inicio = $actualizacion;
		$id_creator = $iduser_temp;
		$creacion_incidente = 1;
} else {
	audit_db($id_user,$REMOTE_ADDR, "HACK","Trying to create incident in a unusual way");
	no_permission();

}



// ********************************************************************************************************
// ********************************************************************************************************
// Show the form
// ********************************************************************************************************

if ($creacion_incidente == 0)
	echo "<form name='accion_form' method='POST' action='index.php?sec=incidencias&sec2=operation/incidents/incident&action=update'>";
else
	echo "<form name='accion_form' method='POST' action='index.php?sec=incidencias&sec2=operation/incidents/incident&action=insert'>";

if (isset($id_inc)) {
	echo "<input type='hidden' name='id_inc' value='".$id_inc."'>";
}

// --------------------
// Main incident table
// --------------------

echo "<h2>".$lang_label["incident_manag"]." -&gt;";
if (isset($id_inc)) {
	echo $lang_label["rev_incident"]." # ".$id_inc."</h2>";
} else {
	echo $lang_label["create_incident"]."</h2>";
}

echo $result_msg;

echo '<table cellpadding=3 cellspacing=3 border=0 width=700>';
if ((give_acl($iduser_temp, $id_grupo, "IM")==1) OR ($usuario == $iduser_temp))
	echo '<tr><td class="lb" rowspan="6" width="5"><td class="datos"><b>'.$lang_label["incident"].'</b><td colspan=2 class="datos"><input type="text" name="titulo" size=50 value="'.$titulo.'">';
else
	echo '<tr><td class="lb" rowspan="6" width="5"><td class="datos"><b>'.$lang_label["incident"].'</b><td colspan=2 class="datos"><input type="text" name="titulo" size=50 value="'.$titulo.'" readonly>';

if ((give_acl($iduser_temp, $id_grupo, "IM")==1) OR ($usuario == $iduser_temp))
	$emdis="";
else
	$emdis="DISABLED";
echo '<td class="datos"> ';
if ($email_notify == 1)
	echo "<input $emdis type=checkbox value=1 name='email_notify' CHECKED>";
else
	echo "<input $emdis type=checkbox value=1 name='email_notify'>";
echo $lang_label["email_notify"];

echo '<tr><td class="datos2"><b>'.$lang_label["in_openedwhen"].'</b>';
echo "<td class='datos2' <i>".$inicio."</i>";
echo '<td class="datos2"><b>'.$lang_label["updated_at"].'</b>';
echo "<td class='datos2'><i>".$actualizacion."</i>";
echo '<tr><td class="datos"><b>'.$lang_label["in_openedby"].'</b><td class="datos">';


if (give_acl($id_user, $id_grupo, "IM")==1) {
	echo "<select name='usuario_form' class='w200'>";
	echo "<option>".$usuario;
	
	$sql_1="SELECT * FROM tusuario_perfil WHERE id_usuario = '$id_usuario'";
	$result_1=mysql_query($sql_1);
	
	while ($row_1=mysql_fetch_array($result_1)){
		$sql_2="SELECT * FROM tusuario_perfil WHERE id_grupo = ".$row_1["id_grupo"];
		$result_2=mysql_query($sql_2);
		while ($row_2=mysql_fetch_array($result_2)){
			if (give_acl($row_2["id_usuario"], $row_2["id_grupo"], "IR")==1)
				if ($row_2["id_usuario"] != $usuario)
					echo "<option>".$row_2["id_usuario"];	
		}
	}
	echo "</select>";
}
else {
	echo "<input type=hidden name='usuario_form' value='".$usuario."'>";
	echo $usuario;
}
// Tipo de estado
// 0 - New incident
// 1 - Active incident (accepted)
// 2 - Descartada / Not valid
// 3 - Caducada / Outdated
// 13 - Cerrada / Closed

if ((give_acl($iduser_temp, $id_grupo, "IM")==1) OR ($usuario == $iduser_temp))
	echo '<td class="datos"><b>'.$lang_label["status"].'</b><td class="datos"><select name="estado_form" class="w135">';
else
	echo '<td class="datos"><b>'.$lang_label["status"].'</b><td class="datos"><select disabled name="estado_form" class="w135">';

if ($creacion_incidente == 0){
	switch ( $estado ){
		case 0: echo '<option value="0">'.$lang_label["in_state_0"]; break;
		case 1: echo '<option value="1">'.$lang_label["in_state_1"]; break;
		case 2: echo '<option value="2">'.$lang_label["in_state_2"]; break;
		case 3: echo '<option value="3">'.$lang_label["in_state_3"]; break;
		case 13: echo '<option value="13">'.$lang_label["in_state_13"]; break;
	}
	//echo '<option value="0">'.$lang_label["in_state_0"]; // No possible to setup new state again!
	echo '<option value="1">'.$lang_label["in_state_1"];
	echo '<option value="2">'.$lang_label["in_state_2"];
	echo '<option value="3">'.$lang_label["in_state_3"];
	echo '<option value="13">'.$lang_label["in_state_13"];
	echo '</select>';
} else {
	echo '<option value="0">'.$lang_label["in_state_0"];
	echo '</select>';
}

// Only owner could change source or user with Incident management privileges
if ((give_acl($iduser_temp, $id_grupo, "IM")==1) OR ($usuario == $iduser_temp))
	echo '<tr><td class="datos2"><b>'.$lang_label["source"].'</b><td class="datos2"><select name="origen_form" class="w135">';
else
	echo '<tr><td class="datos2"><b>'.$lang_label["source"].'</b><td class="datos2"><select disabled name="origen_form" class="w135">';

// Fill combobox with source (origen)
if ($origen != "")
	echo "<option value='".$origen."'>".$origen;
$sql1='SELECT * FROM torigen ORDER BY origen';
$result=mysql_query($sql1);
while ($row2=mysql_fetch_array($result)){
	echo "<option value='".$row2["origen"]."'>".$row2["origen"];
}
echo "</select>";

// Group combo
if ((give_acl($iduser_temp, $id_grupo, "IM")==1) OR ($usuario == $iduser_temp))
	echo '<td class="datos2"><b>'.$lang_label["group"].'</b><td class="datos2"><select name="grupo_form" class="w135">';
else
	echo '<td class="datos2"><b>'.$lang_label["group"].'</b><td class="datos2"><select disabled name="grupo_form" class="w135">';
if ($id_grupo != 0)
	echo "<option value='".$id_grupo."'>".$grupo;
$sql1='SELECT * FROM tgrupo ORDER BY nombre';
$result=mysql_query($sql1);
while ($row=mysql_fetch_array($result)){
	if (give_acl($iduser_temp, $row["id_grupo"], "IR")==1)
		echo "<option value='".$row["id_grupo"]."'>".$row["nombre"];
}

echo '</select><tr>';
if ((give_acl($iduser_temp, $id_grupo, "IM")==1) OR ($usuario == $iduser_temp))
	echo '<td class="datos"><b>'.$lang_label["priority"].'</b><td class="datos"><select name="prioridad_form" class="w135">';
else
	echo '<td class="datos"><b>'.$lang_label["priority"].'</b><td class="datos"><select disabled name="prioridad_form" class="w135">';

switch ( $prioridad ){
	case 0: echo '<option value="0">'.$lang_label["informative"]; break;
	case 1: echo '<option value="1">'.$lang_label["low"]; break;
	case 2: echo '<option value="2">'.$lang_label["medium"]; break;
	case 3: echo '<option value="3">'.$lang_label["serious"]; break;
	case 4: echo '<option value="4">'.$lang_label["very_serious"]; break;
	case 10: echo '<option value="10">'.$lang_label["maintenance"]; break;
}

echo '<option value="0">'.$lang_label["informative"];
echo '<option value="1">'.$lang_label["low"];
echo '<option value="2">'.$lang_label["medium"];
echo '<option value="3">'.$lang_label["serious"];
echo '<option value="4">'.$lang_label["very_serious"];
echo '<option value="10">'.$lang_label["maintenance"];

echo "<td class='datos'><b>Creator</b><td class='datos'>".$id_creator." ( <i>".dame_nombre_real($id_creator)." </i>)";

if ((give_acl($iduser_temp, $id_grupo, "IM")==1) OR ($usuario == $iduser_temp))
	echo '</select><tr><td class="datos2" colspan="4"><textarea name="descripcion" rows="15" cols="85">';
else
	echo '</select><tr><td class="datos2" colspan="4"><textarea readonly name="descripcion" rows="15" cols="85">';
if (isset($texto)) {echo $texto;}
echo "</textarea>";

echo '<tr><td colspan="5"><div class="raya"></div></td></tr>';
echo "<tr><td colspan='5' align='left'>";
// Only if user is the used who opened incident or (s)he is admin

$iduser_temp=$_SESSION['id_usuario'];

if ($creacion_incidente == 0){
	if ((give_acl($iduser_temp, $id_grupo, "IM")==1) OR ($usuario == $iduser_temp)){
		echo '<input type="submit" class="sub next" name="accion" value="'.$lang_label["in_modinc"].'" border="0">';
	}
} else {
	if (give_acl($iduser_temp, $id_grupo, "IW")) {
		echo '<input type="submit" class="sub next" name="accion" value="'.$lang_label["create"].'" border="0">';
	}
}
echo "</form>";
echo "</table>";

// ----------------
// ADD NOTE CONTROL
// ----------------
if ($creacion_incidente == 0){
 
	$ahora=date("Y/m/d H:i:s");
	echo "<h3>".$lang_label["note_title"]."</h3>";
	echo "<table cellpadding=3 cellspacing=3 border=0 width='700'>";
	echo "<form name='nota' method='post' action='index.php?sec=incidencias&sec2=operation/incidents/incident_detail&insertar_nota=1&id=".$id_inc."'>";
	echo "<tr><td class='lb' rowspan='2' width='5'>";
	echo "<td class='datos'><b>".$lang_label["date"]."</b>";
	echo "<td class='datos'>".$ahora;
	echo "<input type='hidden' name='timestamp' value='".$ahora."'>";
	echo "<input type='hidden' name='id_inc' value='".$id_inc."'>";
	echo '<tr><td colspan="4" class="datos2"><textarea name="nota" rows="6" cols="85">';
	echo '</textarea>';
	echo '<tr><td colspan="4"><div class="raya"></div></td></tr>';
	echo '<tr><td colspan="4" align="left"><input name="addnote" type="submit" class="sub next" value="'.$lang_label["add"].'">';
}
echo "</tr></table><br>";
echo "</form>";

if ($creacion_incidente == 0){
// Upload control
	if (give_acl($iduser_temp, $id_grupo, "IW")==1){

		echo "<table cellpadding=3 cellspacing=3 border=0 width='700'>";
		echo "<tr><td colspan='3'><b>".$lang_label["attachfile"]."</b>";
		echo "<tr><td class='lb' rowspan='2' width='5'>";
		echo '<td class="datos">'.$lang_label["filename"].'</td><td class="datos">';
		echo '<form method="post" action="index.php?sec=incidencias&sec2=operation/incidents/incident_detail&id='.$id_inc.'&upload_file=1" enctype="multipart/form-data">';
		echo '<input type="file" name="userfile" value="userfile" class="sub" size="40">';
		echo '<tr><td class="datos2">'.$lang_label["description"].'</td><td class="datos2" colspan=3><input type="text" name="file_description" size=47>';
		echo '<tr><td colspan="4"><div class="raya"></div></td></tr>';
		echo '<tr><td colspan="4" align="left"><input type="submit" name="upload" value="'.$lang_label["upload"].'" class="sub next">';
		echo "</form>";
		echo '</td></tr></table><br>';
	}

	


	echo "</table>";
} // create mode

?>
