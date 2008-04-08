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

global $config;

check_login();

if (give_acl($config["id_user"], 0, "UM")==0) {
    audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access User Management");
    require ("general/noaccess.php");
    exit;
}

$id_user = $config["id_user"];

   
	if (isset($_POST["crear_grupo"])){ // Create group
		$nombre = get_parameter ("nombre","");
        $id_grupo = get_parameter ("id_grupo",0);
        $icon = get_parameter ("icon","");
		$sql_insert="INSERT INTO tgrupo (nombre, icon) 
		VALUES ('".$nombre."', '".$icon."') ";
		$result=mysql_query($sql_insert);	
		if (! $result)
			echo "<h3 class='error'>".$lang_label["create_group_no"]."</h3>";
		else {
			echo "<h3 class='suc'>".$lang_label["create_group_ok"]."</h3>"; 
			$id_grupo = mysql_insert_id();
		}
	}

	if (isset($_POST["update_grupo"])){ // if modified any parameter
		$nombre = get_parameter ("nombre","");
		$id_grupo = get_parameter ("id_grupo",0);
		$icon = get_parameter ("icon","");
	    $sql_update ="UPDATE tgrupo 
		SET nombre = '".$nombre."', icon = '".$icon."' 
		WHERE id_grupo = '".$id_grupo."'";
		$result=mysql_query($sql_update);
		if (! $result)
			echo "<h3 class='error'>".$lang_label["modify_group_no"]."</h3>";
		else
			echo "<h3 class='suc'>".$lang_label["modify_group_ok"]."</h3>";
	}
	
	if (isset($_GET["borrar_grupo"])){ // if delete
		$id_borrar_modulo = get_parameter ("id_grupo",0);
		
		// First delete from tagente_modulo
		$sql_delete= "DELETE FROM tgrupo WHERE id_grupo = ".$id_borrar_modulo;
		$result=mysql_query($sql_delete);
		if (! $result)
			echo "<h3 class='error'>".$lang_label["delete_group_no"]."</h3>"; 
		else
			echo "<h3 class='suc'>".$lang_label["delete_group_ok"]."</h3>";
	}
	echo "<h2>".$lang_label["group_management"]."</h2>";	
	echo "
		<h3>".$lang_label["definedgroups"]."</a></h3>";

	echo "<table cellpadding=3 cellspacing=3 width=400 class='databox'>";
	echo "<th>".$lang_label["icon"]."</th>";
	echo "<th>".$lang_label["group_name"]."</th>";
	echo "<th>".$lang_label["parent"]."</th>";
	echo "<th>".$lang_label["delete"]."</th>";
	$sql1='SELECT * FROM tgrupo ORDER BY nombre';
	$result=mysql_query($sql1);
	$color=0;
	while ($row=mysql_fetch_array($result)){
		if ($color == 1){
			$tdcolor = "datos";
			$color = 0;
			}
		else {
			$tdcolor = "datos2";
			$color = 1;
		}
		if ($row["id_grupo"] != 1){
			echo "
			<tr>
				<td class='$tdcolor' align='center'>
				<img src='images/groups_small/".$row["icon"].".png'
				border='0'>
				</td>
				<td class='$tdcolor'>
				<b><a href='index.php?sec=users&
				sec2=godmode/grupos/configurar_grupo&
				id_grupo=".$row["id_grupo"]."'>".$row["nombre"]."</a>
				</b></td>
				<td class='$tdcolor'>
				".dame_nombre_grupo ($row["parent"])."
				</td>
				<td class='$tdcolor' align='center'>";
				if ($row["id_grupo"] > 1){
                    echo "<a href='index.php?sec=users&
				    sec2=godmode/grupos/lista_grupos&
				    id_grupo=".$row["id_grupo"]."&
				    borrar_grupo=".$row["id_grupo"]."' 
				    onClick='if (!confirm(\' ".$lang_label["are_you_sure"]."\')) 
				    return false;'>
				    <img border='0' src='images/cross.png'></a>";
                }
				echo "</td></tr>";
		}
	}
	echo "<tr><td colspan='4' align='right'>";
	echo "<form method=post action='index.php?sec=users&
	sec2=godmode/grupos/configurar_grupo&creacion_grupo=1'>";
	echo "<input type='submit' class='sub next' name='crt' value='".$lang_label["create_group"]."'>";
	echo "</form></td></tr></table>";


?>