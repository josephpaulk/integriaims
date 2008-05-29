<?php

// Integria 1.1 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2008 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// Load global vars

global $config;

if (check_login() != 0) {
 	audit_db("Noauth",$config["REMOTE_ADDR"], "No authenticated access","Trying to access event viewer");
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
	audit_db($id_user,$config["REMOTE_ADDR"], "ACL Violation","Trying to access to incident ".$id_inc." '".$titulo."'");
	include ("general/noaccess.php");
	exit;
}

$id_grupo = "";
$creacion_incidente = "";
$result_msg = "";

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// UPDATE incident - Get data from form
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
if ((isset($_GET["action"])) AND ($_GET["action"]=="update")){
	$id_inc = $_POST["id_inc"];
 	$grupo = clean_input ($_POST['grupo_form']);
	$usuario= clean_input ($_POST["usuario_form"]);
	if ((give_acl($config["id_user"], $grupo, "IM")==1) OR ($usuario == $config["id_user"])) { // Only admins (manage incident) or owners can modify incidents
		$id_author_inc = give_incident_author($id_inc);
		$titulo = clean_input ($_POST["titulo"]);
		$descripcion = clean_input ($_POST['descripcion']);
		$origen = give_parameter_post ("incident_origin",1);
		$prioridad = clean_input ($_POST['prioridad_form']);
		$estado = clean_input ($_POST["incident_status"]);
		$ahora=date("Y/m/d H:i:s");
		$group = clean_input ($_POST["grupo_form"]);

		if (isset($_POST["email_notify"]))
			$email_notify= give_parameter_post ("email_notify");
		else
			$email_notify = 0;
		$epilog = give_parameter_post ("epilog","");
		$descripcion =  give_parameter_post ('descripcion');
		$resolution =  give_parameter_post ("incident_resolution");
		$id_task =  give_parameter_post ("task_user");
		
		incident_tracking ( $id_inc, $config["id_user"], 1);
		$old_prio = give_inc_priority ($id_inc);
		// 0 - Abierta / Sin notas (Open without notes)
		// 2 - Descartada (Not valid)
		// 3 - Caducada (out of date)
		// 13 - Cerrada (closed)
		if ($old_prio != $prioridad)
			incident_tracking ( $id_inc, $id_usuario, 8);		
		if ($estado == 2)
			incident_tracking ( $id_inc, $id_usuario, 4);	
		if ($estado == 3)
			incident_tracking ( $id_inc, $id_usuario, 5);
		if ($estado == 13)
			incident_tracking ( $id_inc, $id_usuario, 10);
			
		$sql = "UPDATE tincidencia 
				SET actualizacion = '$ahora', titulo = '$titulo', 
				origen= '$origen', estado = '$estado', id_grupo = '$grupo', 
				id_usuario = '$usuario', notify_email = $email_notify, 
				prioridad = '$prioridad', descripcion = '$descripcion', 
				epilog = '$epilog', id_task = $id_task, resolution = '$resolution' , id_grupo = $group
				WHERE id_incidencia = ".$id_inc;
		$result=mysql_query($sql);
		audit_db($id_author_inc,$config["REMOTE_ADDR"],"Incident updated","User ".$id_usuario." deleted updated #".$id_inc);
		if ($result)
			$result_msg = "<h3 class='suc'>".$lang_label["upd_incid_ok"]."</h3>";
		else
			$result_msg = "<h3 class='suc'>".$lang_label["upd_incid_no"]."</h3>";
		$_GET["id"] = $id_inc; // HACK

		// Email notify to all people involved in this incident
		if ($email_notify == 1){
            mail_incident ($id_inc, $usuario, "", 0, 0);
		}

	} else {
		audit_db($id_usuario,$config["REMOTE_ADDR"],"ACL Forbidden","User ".$_SESSION["id_usuario"]." try to update incident");
		echo "<h3 class='error'>".$lang_label["upd_incid_no"]."</h3>";
		no_permission();
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// INSERT incident - Get data from form
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
if ((isset($_GET["action"])) AND ($_GET["action"]=="insert")){
	$grupo = clean_input ($_POST['grupo_form']);
	$usuario= clean_input ($_POST["usuario_form"]);
	if ((give_acl($id_usuario, $grupo, "IW") == 1) OR ($usuario == $id_usuario)) { // Only admins (manage
		// Read input variables
		$titulo = clean_input ($_POST['titulo']);
		$inicio = date("Y/m/d H:i:s");
		$descripcion = clean_input ($_POST['descripcion']);
		$texto = $descripcion; // to view in textarea after insert
		$origen = give_parameter_post ("incident_origin",1);
		$prioridad = clean_input ($_POST['prioridad_form']);
		$actualizacion = $inicio;
		$id_creator = $id_usuario;
		$estado = clean_input ($_POST["incident_status"]);
		$resolution = clean_input ($_POST["incident_resolution"]);
		$id_task =  give_parameter_post ("task_user");
		if (isset($_POST["email_notify"]))
			$email_notify=clean_input ($_POST["email_notify"]);
		else
			$email_notify = 0;
		
		$sql = "INSERT INTO tincidencia (inicio, actualizacion, titulo , descripcion, id_usuario, origen, estado, prioridad, id_grupo, id_creator, notify_email, id_task, resolution) VALUES ('$inicio','$actualizacion', '$titulo', '$descripcion', '$usuario', '$origen', '$estado', '$prioridad', '$grupo', '$id_creator', $email_notify, $id_task, $resolution)";
		if (mysql_query($sql)){
			$id_inc=mysql_insert_id();
			$_GET["id"] = $id_inc; // HACK
			$result_msg  = "<h3 class='suc'>".$lang_label["create_incid_ok"]." ( id #$id_inc )</h3>";
			audit_db($config["id_user"],$config["REMOTE_ADDR"],"Incident created","User ".$id_usuario." created incident #".$id_inc);
			incident_tracking ( $id_inc, $config["id_user"], 0);

			// Email notify to all people involved in this incident
			if ($email_notify == 1){
                mail_incident ($id_inc, $usuario, "", 0, 1);
			}
		}
		
	} else {
		audit_db($id_usuario,$config["REMOTE_ADDR"],"ACL Forbidden","User ".$config["id_user"]." try to create incident");
		no_permission();
	}
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Edit / Visualization MODE - Get data from database
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
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
	$resolution = $row["resolution"];
	$epilog = $row["epilog"];
	$id_task = $row["id_task"];
	$id_incident_linked = $row["id_incident_linked"]; 
	$grupo = dame_nombre_grupo($id_grupo);

    // Aditional ACL check on read incident
    if (give_acl($config["id_user"], $id_grupo, "IR") == 0) { // Only admins
        audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Forbidden","User ".$config["id_user"]." try to access to an unauthorized incident ID #id_inc");
        no_permission();
    }

	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Workunit ADD
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	if (isset($_GET["insert_workunit"])){
		$id_inc = give_parameter_post ("id_inc");
		$timestamp = give_parameter_post ("timestamp");
		$nota = give_parameter_post ("nota");
		$workunit = give_parameter_post ("workunit",0);
		$timeused = give_parameter_post ("duration",0);
		$timeused = number_format($timeused, 2);
		$id_usuario=$_SESSION["id_usuario"];
		$have_cost = give_parameter_post ("have_cost",0);
		$profile = give_parameter_post ("work_profile",0);
		
		$sql4 = "UPDATE tincidencia SET actualizacion = '".$timestamp."' WHERE id_incidencia = ".$id_inc;
		$res4 = mysql_query($sql4);
		
		incident_tracking ( $id_inc, $id_usuario, 2);

		// Add work unit if enabled
		$sql = "INSERT INTO tworkunit (timestamp, duration, id_user, description) VALUES ('$timestamp', '$timeused', '$id_usuario', '$nota')";
		$res5 = mysql_query($sql);
		$id_workunit = mysql_insert_id();
		$sql1 = "INSERT INTO tworkunit_incident (id_incident, id_workunit) VALUES ($id_inc, $id_workunit)";
		$res6 = mysql_query($sql1);
		if ($res6) {
			$result_msg = "<h3 class='suc'>".$lang_label["create_work_ok"]."</h3>";
			// Email notify to all people involved in this incident
			if ($email_notify == 1){ 
                mail_incident ($id_inc, $id_usuario, $nota, $timeused, 10);
			}
		}
	}
	
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	// Upload file
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
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
			// Email notify to all people involved in this incident
			if ($email_notify == 1){ 
				mail_incident ($id_inc, $iduser_temp, 0, 0, 2);
			}
			// Copy file to directory and change name
			$nombre_archivo = $config["homedir"]."attachment/pand".$id_attachment."_".$filename;

			if (!(copy($_FILES['userfile']['tmp_name'], $nombre_archivo ))){
					$result_msg = "<h3 class=error>".$lang_label["attach_error"]."</h3>";
				$sql = " DELETE FROM tattachment WHERE id_attachment =".$id_attachment;
				mysql_query($sql);
			} else {
				// Delete temporal file
				unlink ($_FILES['userfile']['tmp_name']);
			}
		}
	}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Prepare the insertion data
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
} elseif (isset($_GET["insert_form"])){
		$iduser_temp=$_SESSION['id_usuario'];
		$titulo = "";
		$titulo = "";
		$descripcion = "";
		$origen = 0;
		$prioridad = 2;
		$id_grupo =0;
		$grupo = dame_nombre_grupo(1);

		$usuario= $config["id_user"];
		$estado = 1;
		$resolution = 9;
		$id_task = 0;
		$epilog = "";
		$actualizacion=date("Y/m/d H:i:s");
		$inicio = $actualizacion;
		$id_creator = $iduser_temp;
		$creacion_incidente = 1;
		$email_notify = 0;

} else {
	audit_db($id_user,$config["REMOTE_ADDR"], "HACK","Trying to create incident in a unusual way");
	no_permission();
	exit;
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Show the form
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if ($creacion_incidente == 0)
	echo "<form name='accion_form' method='POST' action='index.php?sec=incidents&sec2=operation/incidents/incident_detail&action=update&id=$id_inc'>";
else
	echo "<form name='accion_form' method='POST' action='index.php?sec=incidents&sec2=operation/incidents/incident_detail&action=insert'>";

if (isset($id_inc)) {
	echo "<input type='hidden' name='id_inc' value='".$id_inc."'>";
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Main incident table
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

if (isset($id_inc)) {
	echo "<h1>".$lang_label["incident"]." # $id_inc</h1>";
} else {
	echo "<h2>".$lang_label["create_incident"]."</h2>";
}

echo $result_msg;

echo '<table width=740 class="databox_color" cellpadding=2 cellspacing=2 >';

// CREATE
$default_responsable  = "";
if (!isset($id_inc)){
    // How many groups has this user ?
    $number_group = give_db_sqlfree_field ("SELECT COUNT(id_grupo) FROM tusuario_perfil WHERE id_usuario = '$usuario'");
    // Take first group defined for this user
    $default_id_group = give_db_sqlfree_field ("SELECT id_grupo FROM tusuario_perfil WHERE id_usuario = '$usuario' LIMIT 1");
    // if have only one group, select default user and email for this group 
    if ($number_group == 1){
        $default_responsable = give_db_sqlfree_field ("SELECT id_user FROM tgroup_manager WHERE id_group = $default_id_group");
        $email_notify = give_db_sqlfree_field ("SELECT forced_email FROM tgroup_manager WHERE id_group = $default_id_group");
    } 
}

// Title and email notify
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
if ((give_acl($iduser_temp, $id_grupo, "IM")==1) OR ($usuario == $iduser_temp))
	echo '<tr><td class="datos"><b>'.$lang_label["incident"].'</b><td colspan=2 class="datos"><input type="text" name="titulo" size=50 value="'.$titulo.'">';
else
	echo '<tr><td class="datos"><b>'.$lang_label["incident"].'</b><td colspan=2 class="datos"><input type="text" name="titulo" size=50 value="'.$titulo.'" readonly>';

if ((give_acl($iduser_temp, $id_grupo, "IM")==1) OR ($usuario == $iduser_temp))
	$emdis="";
else
	$emdis="DISABLED";

echo '<td class="datos"> ';
if ($email_notify == 1)
	echo "<input $emdis type=checkbox value=1 name='email_notify' CHECKED>";
else
	echo "<input $emdis type=checkbox value=1 name='email_notify'>";

echo "&nbsp;&nbsp;<b>".$lang_label["email_notify"];
echo "</b> <a href='#' class='tip'>&nbsp;<span>";
echo $lang_label["email_notify_help"];
echo "</span></a>";

// Priority combo
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
if ((give_acl($iduser_temp, $id_grupo, "IM")==1) OR ($usuario == $iduser_temp)){
	echo '<tr><td class="datos2"><b>'.$lang_label["priority"].'</b>';
	echo '<td class="datos2"><select name="prioridad_form">';
} else {
	echo '<tr><td class="datos2"><b>'.$lang_label["priority"].'</b>';
	echo '<td class="datos2"><select disabled name="prioridad_form">';
}

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


// Incident STATUS combo
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
echo '<td class="datos2"><b>'.$lang_label["status"].'</b><td class="datos2">';
// Status combo
if ((give_acl($config["id_user"], $id_grupo, "IM")==1) OR ($usuario == $config["id_user"]) ){
	if ($creacion_incidente == 0){
		echo combo_incident_status ($estado, 0, 0);
	} else {
		echo combo_incident_status ($estado, 0, 1);
	}
} else {
	echo combo_incident_status ($estado, 1, 0);
}

// User and owner
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
echo '<tr><td class="datos"><b>'.$lang_label["assigned_user"].'</b><td class="datos">';
if ((give_acl($config["id_user"], $id_grupo, "IM")==1) OR ($creacion_incidente == 1)) {
    if ($default_responsable != ""){
        echo "<input type=hidden name='usuario_form' value='$default_responsable'>";
        echo dame_nombre_real($default_responsable);
    }
//	    combo_user_visible_for_me ($default_responsable,"usuario_form", 0, "IR");
	else
    	combo_user_visible_for_me ($usuario,"usuario_form", 0, "IR");
	
echo "<a href='#' class='tip'>&nbsp;<span>";
echo $lang_label["incident_user_help"];
echo "</span></a>";
}
else {
	echo "<input type=hidden name='usuario_form' value='".$usuario."'>";
	echo $usuario;
}
echo "<td class='datos'><b>Creator</b><td class='datos'>".$id_creator." ( <i>".dame_nombre_real($id_creator)." </i>)";


// Origin combo
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
echo '<tr><td class="datos2"><b>'.$lang_label["source"].'</b><td class="datos2">';
// Only owner could change source or user with Incident management privileges
if ((give_acl($config["id_user"], $id_grupo, "IM")==1) OR ($usuario == $config["id_user"]))
	echo combo_incident_origin ($origen, 0);
else
	echo combo_incident_origin ($origen, 1);
	

// Group combo
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

echo '<td class="datos2"><b>'.$lang_label["group"].'</b><td class="datos2">';
if ((give_acl($iduser_temp, $id_grupo, "IM")==1) OR ($usuario == $iduser_temp)){
    combo_groups_visible_for_me ($iduser_temp, "grupo_form", 0, "IW", $id_grupo);
} else { // Only show current group
    echo give_db_sqlfree_field ("SELECT nombre FROM tgrupo WHERE id_grupo = ".$id_grupo);
}

// Incident Resolution combo
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
echo '<tr><td class="datos"><b>'.$lang_label["resolution"].'</b><td class="datos">';
// Status combo
if ((give_acl($iduser_temp, $id_grupo, "IM")==1) OR ($usuario == $iduser_temp)){
	echo combo_incident_resolution ($resolution, 0);
} else {
	echo combo_incident_resolution ($resolution, 1);
}

// Incident linked to a task
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
echo '<td class="datos"><b>'.$lang_label["task"].'</b><td class="datos">';
if ((give_acl($iduser_temp, $id_grupo, "IM")==1) OR ($usuario == $iduser_temp)){
	echo combo_task_user ($id_task, $config["id_user"], 0);
} else 
	echo combo_task_user ($id_task, $config["id_user"], 1);



// Description Textarea
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
if ((give_acl($iduser_temp, $id_grupo, "IM")==1) OR ($usuario == $iduser_temp))
	echo '<tr><td class="datos2" colspan="4"><textarea name="descripcion" rows="15" cols="100">';
else
	echo '<tr><td class="datos2" colspan="4"><textarea readonly name="descripcion" rows="15" cols="100">';
if (isset($texto)) {
	echo $texto;
}
echo "</textarea>";

// Epilog
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
if (((give_acl($iduser_temp, $id_grupo, "IM")==1) OR ($usuario == $iduser_temp)) AND ($estado > 5)){
	echo "<tr><td class='datos' colspan='4'><b>".$lang_label["resolution_epilog"]."</b>";
	echo '<tr><td class="datos2" colspan="4"><textarea name="epilog" rows="3" cols="100">';
	if (isset($epilog)) {
		echo $epilog;
	}
	echo "</textarea>";
} elseif ($estado > 5) {
    echo "<tr><td class='datos' colspan='4'><b>".$lang_label["resolution_epilog"]."</b>";
    echo '<tr><td class="datos2" colspan="4"><textarea readonly name="epilog" rows="3" cols="100">';
    if (isset($epilog)) {
        echo $epilog;
    }
    echo "</textarea>";
}

echo "</table>";

// UPDATE / INSERT BUTTON
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
if ($creacion_incidente == 0){
	if ((give_acl($config["id_user"], $id_grupo, "IM")==1) OR ($usuario == $config["id_user"])){
		echo '<input type="submit" class="sub next" name="accion" value="'.$lang_label["in_modinc"].'" border="0">';
	}
} else {
	if (give_acl($config["id_user"], 0, "IW")) {
		echo '<input type="submit" class="sub create" name="accion" value="'.$lang_label["create"].'" border="0">';
	}
}
echo "</form>";
echo "</table>";

?>
