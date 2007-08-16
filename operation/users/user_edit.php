<?php

// Pandora - the Free monitoring system
// ====================================
// Copyright (c) 2004-2006 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2005-2006 Artica Soluciones Tecnol�icas S.L, info@artica.es
// Copyright (c) 2004-2006 Raul Mateos Martin, raulofpandora@gmail.com
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

// Load global vars
global $config;

if (check_login() == 0) {
	
	$view_mode = 0;
	$id_usuario = $_SESSION["id_usuario"];
	
	if (isset ($_GET["ver"])){ // Only view mode, 
		$id_ver = $_GET["ver"]; // ID given as parameter
		if ($id_usuario == $id_ver)
			$view_mode = 0;
		else
			$view_mode = 1;
	}

	$query1="SELECT * FROM tusuario WHERE id_usuario = '".$id_ver."'";
	$resq1=mysql_query($query1);
	$rowdup=mysql_fetch_array($resq1);
	$nombre=$rowdup["id_usuario"];
	
	// Get user ID to modify data of current user.

	if (isset ($_GET["modificado"])){
		// Se realiza la modificaci�
		if (isset ($_POST["pass1"])){
			if ( isset($_POST["nombre"]) && ($_POST["nombre"] != $_SESSION["id_usuario"])) {
				audit_db($_SESSION["id_usuario"],$REMOTE_ADDR,"Security Alert. Trying to modify another user: (".$_POST['nombre'].") ","Security Alert");
				no_permission;
			}
				
			// $nombre = $_POST["nombre"]; // Don't allow change name !!
			$pass1 = clean_input ($_POST["pass1"]);
			$pass2 = clean_input($_POST["pass2"]);
			$direccion = clean_input($_POST["direccion"]);
			$telefono = clean_input($_POST["telefono"]);
			$nombre_real = clean_input($_POST["nombre_real"]);
			$avatar = give_parameter_post ("avatar");
			
			if ($pass1 != $pass2) {
				echo "<h3 class='error'>".$lang_label["pass_nomatch"]."</h3>";
			}
			else {
				echo "<h3 class='suc'>".$lang_label["update_user_ok"]."</h3>";
			}
			//echo "<br>DEBUG para ".$nombre;
			//echo "<br>Comentarios:".$comentarios;	
			$comentarios = clean_input($_POST["comentarios"]);
			if (dame_password($nombre)!=$pass1){
				// Only when change password
				$pass1=md5($pass1);
				$sql = "UPDATE tusuario SET nombre_real = '".$nombre_real."', password = '".$pass1."', telefono ='".$telefono."', direccion ='".$direccion." ', comentarios = '".$comentarios."' WHERE id_usuario = '".$nombre."'";
			}
			else 
				$sql = "UPDATE tusuario SET nombre_real = '".$nombre_real."', telefono ='".$telefono."', direccion ='".$direccion." ', comentarios = '".$comentarios."' WHERE id_usuario = '".$nombre."'";
			$resq2=mysql_query($sql);
			
			// Ahora volvemos a leer el registro para mostrar la info modificada
			// $id is well known yet
			$query1="SELECT * FROM tusuario WHERE id_usuario = '".$id_ver."'";
			$resq1=mysql_query($query1);
			$rowdup=mysql_fetch_array($resq1);
			$nombre=$rowdup["id_usuario"];			
		}
		else {
			echo "<h3 class='error'>".$lang_label["pass_nomatch"]."</h3>";
		}
	} 
		echo "<h2>".$lang_label["user_edit_title"]."</h3>";

	// Si no se obtiene la variable "modificado" es que se esta visualizando la informacion y
	// preparandola para su modificacion, no se almacenan los datos
	
	$nombre=$rowdup["id_usuario"];
	if ($view_mode == 0)
		$password=$rowdup["password"];
	else 	
		$password="This is not a good idea :-)";
	
	$comentarios=$rowdup["comentarios"];
	$direccion=$rowdup["direccion"];
	$telefono=$rowdup["telefono"];
	$nombre_real=$rowdup["nombre_real"];
	$avatar = $rowdup ["avatar"];

	?>
	<table border=0 cellpadding="3" cellspacing="3" width="500" class="databox_color";>
	<?php 
	if ($view_mode == 0) 
		echo '<form name="user_mod" method="post" action="index.php?sec=usuarios&sec2=operation/users/user_edit&ver='.$id_usuario.'&modificado=1">';
	else 	
		echo '<form name="user_mod" method="post" action="">';
	?>
	<tr><td class="datos"><?php echo $lang_label["id_user"] ?>
	<td class="datos"><input class=input type="text" name="nombre" value="<?php echo $nombre ?>" disabled>
	<?PHP
	if (isset($avatar)){
		echo "<td class='datos' rowspan=3>";
		echo "<img src='images/avatars/".$avatar.".png'>";
	}
 	?>
	<tr><td class="datos2"><?php echo $lang_label["real_name"] ?>
	<td class="dato2s"><input class=input type="text" name="nombre_real" value="<?php echo $nombre_real ?>">
	<tr><td class="datos"><?php echo $lang_label["password"] ?>
	<td class="datos"><input class=input type="password" name="pass1" value="<?php echo $password ?>">
	<tr><td class="datos2"><?php echo $lang_label["password"]; echo " ".$lang_label["confirmation"]?>
	<td class="datos2" colspan=2><input class=input type="password" name="pass2" value="<?php echo $password ?>">
	<tr><td class="datos">E-Mail
	<td class="datos" colspan=2><input class=input type="text" name="direccion" size="40" value="<?php echo $direccion ?>">

	<?PHP
	// Avatar
	echo "<tr><td class='datos2'>".lang_string("avatar");
	echo "<td class='datos2' colspan=2><select name='avatar'>";
	if ($avatar!=""){
		echo '<option>'.$avatar;
	}
	$ficheros = list_files('images/avatars/', "",0, 0);
	$a=0;
	while (isset($ficheros[$a])){
		if ((strpos($ficheros[$a],"small") == 0) && (strlen($ficheros[$a])>4))
			echo "<option>".substr($ficheros[$a],0,strlen($ficheros[$a])-4);
		$a++;
	}
	echo '</select>';
	?>
	
	<tr><td class="datos"><?php echo $lang_label["telefono"] ?>
	<td class="datos" colspan=2><input class=input type="text" name="telefono" value="<?php echo $telefono ?>">
	<tr><td class="datos2" colspan="3"><?php echo $lang_label["comments"] ?>
	<tr><td class="datos" colspan="3"><textarea name="comentarios" cols="55" rows="4"><?php echo $comentarios ?></textarea>
	</table>
<?php
	// Don't delete this!!
	if ($view_mode ==0){
		echo "<input name='uptbutton' type='submit' class='sub upd' value='".$lang_label["update"]."'>";
	}
	
	echo '<h3>'.$lang_label["listGroupUser"].'</h3>';
	echo "<table width='500' cellpadding='3' cellspacing='3' class='databox_color'>";
	$sql1='SELECT * FROM tusuario_perfil WHERE id_usuario = "'.$nombre.'"';
	$result=mysql_query($sql1);
	if (mysql_num_rows($result)){
		$color=1;
		while ($row=mysql_fetch_array($result)){
			if ($color == 1){
				$tdcolor = "datos";
				$color = 0;
				}
			else {
				$tdcolor = "datos2";
				$color = 1;
			}
			echo '<td class="'.$tdcolor.'">';
			echo "<b>".dame_perfil($row["id_perfil"])."</b> / ";
			echo "<b>".dame_grupo($row["id_grupo"])."</b><tr>";	
		}
	}
	else { 
		echo '<tr><td class="red" colspan="3">'.$lang_label["no_profile"]; 
	}

	echo '</form></td></tr></table> ';

} // fin pagina

?>