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

// Load global vars

global $config;

check_login ();

enterprise_include ('godmode/usuarios/configurar_usuarios.php');

if (give_acl ($config["id_user"], 0, "UM")) {
	// Init. vars
	$comentarios = "";
	$direccion = "";
	$telefono = "";
	$password = "";
	$id_usuario_mio = "";
	$lang = "";
	$nombre_real = "";
	$nivel = 0;
	// Default is create mode (creacion)
	$modo = "creacion";
	
	if (isset($_GET["borrar_grupo"])) {
			$grupo = get_parameter ('borrar_grupo');
			enterprise_hook ('delete_group');
		}
	}
		
	if (isset($_GET["id_usuario_mio"])){ // if any parameter changed
		$modo = "edicion";
		$id_usuario_mio = get_parameter ("id_usuario_mio", "");
		// Read user data to include in form
		$query1 = "SELECT * FROM tusuario WHERE id_usuario = '".$id_usuario_mio."'";
		$resq1 = mysql_query ($query1);
		$rowdup = mysql_fetch_array ($resq1);
		if (!$rowdup) {
			echo "<h3 class='error'>".__('There was a problem loading user')."</h3>";
			echo "</table>";
			include ("general/footer.php");
			exit;
		}
		else {
			$password=$rowdup["password"];
			$comentarios=$rowdup["comentarios"];
			$direccion=$rowdup["direccion"];
			$telefono=$rowdup["telefono"]; 
			$nivel =$rowdup["nivel"]; 
			$nombre_real=$rowdup["nombre_real"];
			$avatar = $rowdup["avatar"];
			$lang = $rowdup["lang"];
		}
	}
	

	// Edit user
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	if (isset ($_POST["uptbutton"]))  {
      	// We do it

		if (isset ($_POST["pass1"])){

			$nombre_real = get_parameter ("nombre_real");
			$nombre_viejo = get_parameter ("id_usuario_mio");
            $nombre = $nombre_viejo ;
			$password = get_parameter ("pass1");
			$password2 = get_parameter ("pass2");
			$lang = get_parameter ("lang");

			if ($password <> $password2){
				echo "<h3 class='error'>".__('Passwords don\'t match.')."</h3>";
			}
			else {
				if (isset($_POST["nivel"]))
				$nivel = get_parameter ("nivel");
				$direccion = rtrim (get_parameter ("direccion"));
				$telefono = get_parameter ("telefono");
				$comentarios = get_parameter ("comentarios");
				$avatar = get_parameter ("avatar");
				$avatar = substr($avatar, 0, strlen($avatar)-4);

				if (dame_password ($nombre_viejo) != $password){
					$password = md5($password);
					$sql = "UPDATE tusuario SET `lang` = '$lang', nombre_real ='".$nombre_real."', password = '".$password."', telefono ='".$telefono."', direccion ='".$direccion." ', nivel = '$nivel', comentarios = '$comentarios', avatar = '$avatar' WHERE id_usuario = '$nombre_viejo'";
				}
				else 	
					$sql = "UPDATE tusuario SET lang = '$lang', nombre_real ='".$nombre_real."', telefono ='".$telefono."', direccion ='".$direccion." ', nivel = '".$nivel."', comentarios = '".$comentarios."', avatar = '$avatar' WHERE id_usuario = '".$nombre_viejo."'";

				$resq2 = mysql_query($sql);
	
				// Add group / to profile
				// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
				if (isset($_POST["grupo"]))
					if ($_POST["grupo"] <> ""){
						$grupo = $_POST["grupo"];
						$perfil = $_POST["perfil"];
						$id_usuario_edit = $_SESSION["id_usuario"];
						enterprise_hook('associate_userprofile');
					}
				
				$query1="SELECT * FROM tusuario WHERE id_usuario = '".$nombre."'";
				$id_usuario_mio = $nombre;
				$resq1 = mysql_query($query1);
				$rowdup = mysql_fetch_array($resq1);
				$password = $rowdup["password"];
				$comentarios = $rowdup["comentarios"];
				$direccion = $rowdup["direccion"];
				$telefono = $rowdup["telefono"]; 
				$nivel = $rowdup["nivel"];
				$nombre_real = $rowdup["nombre_real"];
				$avatar = $rowdup ["avatar"];
				$lang = $rowdup ["lang"];
				$modo = "edicion";
				echo "<h3 class='suc'>".__('Successfully updated')."</h3>";
			}
		}
		else {
			echo "<h3 class='error'>".__('There was a problem updating user')."</h3>";
		}
	} 

	// Create user
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	if (isset($_GET["nuevo_usuario"])){
		// Get data from POST
		$nombre = get_parameter ("nombre");
		$password = get_parameter ("pass1");
		$password2 = get_parameter ("pass2");
		$nombre_real = get_parameter ("nombre_real");
		$lang = get_parameter ("lang");
		if ($password <> $password2){
			echo "<h3 class='error'>".__('Passwords don\'t match. Please repeat again')."</h3>";
		}
		$direccion = rtrim(get_parameter ("direccion"));
		$telefono = get_parameter ("telefono");
		$comentarios = get_parameter ("comentarios");
		if (isset($_POST["nivel"]))
			$nivel = get_parameter ("nivel",0);
		$password = md5($password);
		$avatar = get_parameter ("avatar");
		$avatar = substr($avatar, 0, strlen($avatar)-4);

		$ahora = date("Y-m-d H:i:s");
		$sql_insert = "INSERT INTO tusuario (id_usuario,direccion,password,telefono,fecha_registro,nivel,comentarios, nombre_real,avatar, lang) VALUES ('".$nombre."','".$direccion."','".$password."','".$telefono."','".$ahora."','".$nivel."','".$comentarios."','".$nombre_real."','$avatar','$lang')";
		$resq1 = mysql_query($sql_insert);
			if (! $resq1)
				echo "<h3 class='error'>".__('Could not be created')."</h3>";
			else {
				echo "<h3 class='suc'>".__('Successfully created')."</h3>";
			}
		$id_usuario_mio = $nombre;
		$modo ="edicion";
	}
	echo "<h2>".__('User management')."</h2>";
	if (isset($_GET["alta"])){
			if ($_GET["alta"]==1){
			echo '<h3>'.__('Create user').'</h3>';
			}
	}
	if (isset($_GET["id_usuario_mio"]) OR isset($_GET["nuevo_usuario"])){
		echo '<h3>'.__('Update user').'</h3>';
	}

?> 
<table width='620' class='databox'>
<?php 
if (isset($_GET["alta"]))
	// Create URL
	echo '<form name="new_user" method="post" action="index.php?sec=users&sec2=godmode/usuarios/configurar_usuarios&nuevo_usuario=1">';
else
	// Update URL
	echo '<form name="user_mod" method="post" action="index.php?sec=users&sec2=godmode/usuarios/configurar_usuarios&id_usuario_mio='.$id_usuario_mio.'">';

echo '<tr>';
echo '<td class="datos">'.__('User ID');
echo '<td class="datos">';

if (isset($_GET["alta"])){
    echo '<input type="text" size=15 name="nombre" value="'.$id_usuario_mio.'">';
    echo '<i>';
    echo __("User cannot have Blank spaces");
    echo '</i>';
} else {
    echo '<i>';
    echo $id_usuario_mio;
    echo "</i>";
}

if (isset($avatar)){
	echo "<td class='datos' rowspan=6>";
	echo "<img src='images/avatars/".$avatar.".png' id='avatar_preview'>";
}

?>
<tr><td class="datos2"><?php echo __('Real name') ?>
<td class="datos2"><input type="text" size=25 name="nombre_real" value="<?php echo $nombre_real ?>">
<tr><td class="datos"><?php echo __('Password') ?>
<td class="datos"><input type="password" name="pass1" value="<?php echo $password ?>">
<tr><td class="datos2"><?php echo __('Password confirmation') ?>
<td class="datos2"><input type="password" name="pass2" value="<?php echo $password ?>">
<tr><td class="datos"><?php echo __('Email') ?>
<td class="datos"><input type="text" name="direccion" size="30" value="<?php echo $direccion ?>">


<?PHP
// Avatar
echo "<tr><td>".__('Avatar');
echo "<td>";

$ficheros = list_files('images/avatars/', "png", 1, 0, "small");
$avatar_forlist = $avatar . ".png";
echo print_select ($ficheros, "avatar", $avatar_forlist, '', '', 0, true, 0, false, false);	

?>

<tr><td class="datos"><?php echo __('Telephone') ?>
<td class="datos" colspan=2><input type="text" name="telefono" value="<?php echo $telefono ?>">
<tr><td class="datos2"><?php echo __('Global profile') ?>

<td class="datos2" colspan=2>
<?php if ($nivel == 1){
	echo __('Administrator').'&nbsp;<input type="radio" class="chk" name="nivel" value="1" checked>';
	echo "&nbsp;&nbsp;";
	echo __('Standard user').'&nbsp;<input type="radio" class="chk" name="nivel" value="0">';
	echo "&nbsp;&nbsp;";
	echo __('External user').'&nbsp;<input type="radio" class="chk" name="nivel" value="-1">';

	
} elseif ($nivel == 0) {
	echo __('Administrator').'&nbsp;<input type="radio" class="chk" name="nivel" value="1">';
	echo "&nbsp;&nbsp;";
	echo __('Standard user').'&nbsp;<input type="radio" class="chk" name="nivel" value="0" checked>';
	echo "&nbsp;&nbsp;";
	echo __('External user').'&nbsp;<input type="radio" class="chk" name="nivel" value="-1">';
} else {
	echo __('Administrator').'&nbsp;<input type="radio" class="chk" name="nivel" value="1">';
	echo "&nbsp;&nbsp;";
	echo __('Standard user').'&nbsp;<input type="radio" class="chk" name="nivel" value="0">';
	echo "&nbsp;&nbsp;";
	echo __('External user').'&nbsp;<input type="radio" class="chk" name="nivel" value="-1" checked>';
}


echo "<tr>";
echo "<td>";
echo __('Language');
echo "<td>";
print_select_from_sql ("SELECT * FROM tlanguage", "lang", $lang, '', 'Default', '', false, false, true, false);

?>

<tr><td class="datos" colspan="3"><?php echo __('Comments') ?>
<tr><td class="datos2" colspan="3"><textarea name="comentarios" cols="75" rows="3">
<?php echo $comentarios ?>
</textarea>

<?php


echo '<tr><td class="datos2">'.__('Total incidents');
echo '<td class="datos2"><b>';
echo get_db_sql ("SELECT COUNT(*) FROM tincidencia WHERE id_creator = '".$id_usuario_mio."'");
echo "</b></td></tr>";

if ($modo == "edicion") { // Only show groups for existing users

	enterprise_hook ('manage_profiles');
	echo "</table>";

	echo "<div class='button' style='width:620px'>";
	echo "<input name='uptbutton' type='submit' class='sub next' value='".__('Update')."'>";
	echo "</div><br>";
}	

enterprise_hook ('show_delete_profiles');

if (isset($_GET["alta"])){
	echo "</table>";
	echo "<div class='button' style='width: 615px' >";
	echo '<input name="crtbutton" type="submit" class="sub create" value="'.__('Create').'">';
	echo '</div>';
} 

	
?> 
</form>
</td></tr></table>


<script  type="text/javascript">
$(document).ready (function () {
	$("#avatar").change (function () {
		icon = this.value;
		$("#avatar_preview").fadeOut ('normal', function () {
			$(this).attr ("src", "images/avatars/"+icon).fadeIn ();
		});
	});
});
</script>


