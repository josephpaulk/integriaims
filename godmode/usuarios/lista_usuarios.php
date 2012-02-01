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

if (! give_acl ($config["id_user"], 0, "UM")) {
	audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access User Management");
	require ("general/noaccess.php");
	exit;
}
	
if (isset($_GET["borrar_usuario"])){ // if delete user
	$nombre = safe_input ($_GET["borrar_usuario"]);
	
	// Delete user
	// Delete cols from table tgrupo_usuario

	if ($config["enteprise"] == 1){
		$query_del1 = "DELETE FROM tusuario_perfil WHERE id_usuario = '".$nombre."'";
		$resq1 = mysql_query($query_del1);
	}

	if ($config["enteprise"] == 1){
        $query_del2 = "DELETE FROM tapp_activity_data WHERE id_user = '".$nombre."'";
		$resq2 = mysql_query($query_del2);    
    }

	$query_del2 = "DELETE FROM tusuario WHERE id_usuario = '".$nombre."'";
	$resq1 = mysql_query($query_del2);

	if ((! $resq1) OR (! $resq2))
		echo "<h3 class='error'>".__('Could not be deleted')."</h3>";
	else
		echo "<h3 class='suc'>".__('Successfully deleted')."</h3>";
}

$offset = get_parameter ("offset", 0);
$search_text = get_parameter ("search_text", "");

echo '<h2>'.__('User management') . '</h2>';

echo "<table class='blank'><form name='bskd' method=post action='index.php?sec=users&sec2=godmode/usuarios/lista_usuarios'>";
echo "<td>";
echo __('Search text');
echo "<td>";
print_input_text ("search_text", $search_text, '', 15, 0, false);
echo "<td>";
print_submit_button ('Search', '', false, '', false, false);
echo "</table></form>";


$search = "WHERE 1=1 ";
if ($search_text != "")
	$search .= " AND (id_usuario LIKE '%$search_text%' OR comentarios LIKE '%$search_text%' OR nombre_real LIKE  '%$search_text' OR direccion LIKE  '%$search_text')";
$query1 = "SELECT * FROM tusuario $search ORDER BY id_usuario";

$count = get_db_sql("SELECT COUNT(id_usuario) FROM tusuario $search ");

pagination ($count, "index.php?sec=users&sec2=godmode/usuarios/lista_usuarios&search_text=$search_text", $offset);

$sql1 = "$query1 LIMIT $offset, ". $config["block_size"];


echo '<table width="90%" class="listing">';
echo '<th>'.__('User ID').'</td>';
echo '<th>'.__('Name');
echo '<th>'.__('Last contact');
echo '<th>'.__('Profile');
echo '<th>'.__('Level');
echo '<th>'.__('Disabled');
echo '<th>'.__('Delete');

$resq1=process_sql($sql1);
// Init vars
$nombre = "";
$nivel = "";
$comentarios = "";
$fecha_registro = "";

foreach($resq1 as $rowdup){
	$nombre=$rowdup["id_usuario"];
	$nivel =$rowdup["nivel"];
	$realname =$rowdup["nombre_real"];
	$fecha_registro =$rowdup["fecha_registro"];
	$avatar = $rowdup["avatar"];
	if ($rowdup["nivel"] == 0)
		$nivel = __("Standard user");
	elseif ($rowdup["nivel"] == 1)
		$nivel = __("Administrator");
	else
		$nivel = __("External user");

    $disabled = $rowdup["disabled"];	

	echo "<tr><td>";
	echo "<a href='index.php?sec=users&sec2=godmode/usuarios/configurar_usuarios&update_user=".$nombre."'><b>".$nombre."</b></a>";
	echo "<td>" . $realname;
	echo "<td style='font-size:9px'>".$fecha_registro;
	echo "<td>";
	print_user_avatar ($nombre, true);
	if ($config["enteprise"] == 1){
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
			echo __('This user doesn\'t have any assigned profile/group'); 
		}
		echo "</span></a>";
	}
	echo "<td>" . $nivel;

    if ($disabled == 1)
    	echo "<td><b><i>".__("Disabled")."</i></b>";
    else
    	echo "<td>";

	echo '<td align="center">';
	echo '<a href="index.php?sec=users&sec2=godmode/usuarios/lista_usuarios&borrar_usuario='.$nombre.'" onClick="if (!confirm(\''.__('Are you sure?').'\')) return false;"><img src="images/cross.png"></a>';
	echo '</td>';
}
echo "</table>";

echo "<div style='width:90%' class='button'>";

echo "<form method=post action='index.php?sec=users&sec2=godmode/usuarios/configurar_usuarios&alta=1'>";
echo "<input type='submit' class='sub create' name='crt' value='".__('Create')."'>";
echo "</form>";
echo "</div>";

?>
