<?php

// Integria 1.0 - http://integria.sourceforge.net
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

?>

<h2><?php echo $lang_label["user_management"] ?></h2>
<h3><?php echo $lang_label["users"] ?></h3>
 
<table cellpadding=3 cellspacing=3 width=550 class='databox'>
<th class="w80"><?php echo $lang_label["user_ID"]?>
<th><?php echo $lang_label["last_contact"]?>
<th><?php echo $lang_label["profile"]?>
<th><?php echo $lang_label["name"]?>
<th width=30><?php echo $lang_label["delete"]?>

<?php
$query1="SELECT * FROM tusuario";
$resq1=mysql_query($query1);
// Init vars
$nombre = "";
$nivel = "";
$comentarios = "";
$fecha_registro = "";
$color=1;

while ($rowdup=mysql_fetch_array($resq1)){
	$nombre=$rowdup["id_usuario"];
	$nivel =$rowdup["nivel"];
	$comentarios =$rowdup["nombre_real"];
	$fecha_registro =$rowdup["fecha_registro"];
	$avatar = $rowdup["avatar"];
	if ($color == 1){
		$tdcolor = "datos";
		$color = 0;
	}
	else {
		$tdcolor = "datos2";
		$color = 1;
	}
	echo "<tr><td class='$tdcolor'>";
	echo "<a href='index.php?sec=users&sec2=godmode/usuarios/configurar_usuarios&id_usuario_mio=".$nombre."'><b>".$nombre."</b></a>";
	echo "<td class='$tdcolor'>".$fecha_registro;
	echo "<td class='$tdcolor'>";
	echo "<img src='images/avatars/".$avatar."_small.png'>";
	$sql1='SELECT * FROM tusuario_perfil WHERE id_usuario = "'.$nombre.'"';
	$result=mysql_query($sql1);
	echo "<a href='#' class='tip'>&nbsp;<span>";
	if (mysql_num_rows($result)){
		while ($row=mysql_fetch_array($result)){
			echo dame_perfil($row["id_perfil"])."/ ";
			echo dame_grupo($row["id_grupo"])."<br>";
		}
	}
	else { echo $lang_label["no_profile"]; }
	echo "</span></a>";
	
	echo "<td class='$tdcolor'>".$comentarios;
	echo "<td class='$tdcolor' align='center'><a href='index.php?sec=users&sec2=godmode/usuarios/lista_usuarios&borrar_usuario=".$nombre."' onClick='if (!confirm(\' ".$lang_label["are_you_sure"]."\')) return false;'><img border='0' src='images/cross.png'></a>";
}
echo "</table>";

echo "<form method=post action='index.php?sec=users&sec2=godmode/usuarios/configurar_usuarios&alta=1'>";
echo "<input type='submit' class='sub create' name='crt' value='".$lang_label["create_user"]."'>";
echo "</form>";

?>
