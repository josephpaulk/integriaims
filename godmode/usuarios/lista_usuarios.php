<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.


// Load globar vars
global $config;
check_login();

if (give_acl($config["id_user"], 0, "UM")==0) {
	audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access User Management");
	require ("general/noaccess.php");
	exit;
}
	
if (isset($_GET["borrar_usuario"])){ // if delete user
	$nombre= clean_input ($_GET["borrar_usuario"]);
	// Delete user
	// Delete cols from table tgrupo_usuario

	$query_del1="DELETE FROM tusuario_perfil WHERE id_usuario = '".$nombre."'";
	$query_del2="DELETE FROM tusuario WHERE id_usuario = '".$nombre."'";
	$resq1=mysql_query($query_del1);
	$resq1=mysql_query($query_del2);
	if (! $resq1)
		echo "<h3 class='error'>".$lang_label["delete_user_no"]."</h3>";
	else
		echo "<h3 class='suc'>".$lang_label["delete_user_ok"]."</h3>";
}

echo '<h2>'.__("user_management") . '</h2>';
echo '<table width="550" class="listing">';
echo '<th>'.__("user_ID").'</td>';
echo '<th>'.__("last_contact");
echo '<th>'.__("profile");
echo '<th>'.__("name");
echo '<th>'.__("delete");

$query1="SELECT * FROM tusuario";
$resq1=mysql_query($query1);
// Init vars
$nombre = "";
$nivel = "";
$comentarios = "";
$fecha_registro = "";

while ($rowdup=mysql_fetch_array($resq1)){
	$nombre=$rowdup["id_usuario"];
	$nivel =$rowdup["nivel"];
	$comentarios =$rowdup["nombre_real"];
	$fecha_registro =$rowdup["fecha_registro"];
	$avatar = $rowdup["avatar"];
	
	echo "<tr><td>";
	echo "<a href='index.php?sec=users&sec2=godmode/usuarios/configurar_usuarios&id_usuario_mio=".$nombre."'><b>".$nombre."</b></a>";
	echo "<td>".$fecha_registro;
	echo "<td>";
	print_user_avatar ($nombre, true);
	$sql1='SELECT * FROM tusuario_perfil WHERE id_usuario = "'.$nombre.'"';
	$result=mysql_query($sql1);
	echo "<a href='#' class='tip'>&nbsp;<span>";
	if (mysql_num_rows($result)){
		while ($row=mysql_fetch_array($result)){
			echo dame_perfil($row["id_perfil"])."/ ";
			echo dame_grupo($row["id_grupo"])."<br>";
		}
	}
	else { 
		echo __("no_profile"); 
	}
	echo "</span></a>";
	
	echo "<td>" . $comentarios;
	echo "<td align='center'><a href='index.php?sec=users&sec2=godmode/usuarios/lista_usuarios&borrar_usuario=".$nombre."' onClick='if (!confirm(\' ".__("are_you_sure")."\')) return false;'><img border='0' src='images/cross.png'></a>";
}
echo "</table>";

echo "<div style='width:550px' class='button'>";

echo "<form method=post action='index.php?sec=users&sec2=godmode/usuarios/configurar_usuarios&alta=1'>";
echo "<input type='submit' class='sub create' name='crt' value='".__("create_user")."'>";
echo "</form>";
echo "</div>";

?>
