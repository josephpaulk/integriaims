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

if (check_login () != 0) {
	audit_db ($config['id_user'], $REMOTE_ADDR, "ACL Violation", "Trying to access User Management");
	require ("general/noaccess.php");
	exit;
}

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
		$sql = sprintf ('DELETE FROM tusuario_perfil WHERE id_up = %d', $grupo);
		$resq1 = process_sql ($sql);
	}
		
	if (isset($_GET["id_usuario_mio"])){ // if any parameter changed
		$modo = "edicion";
		$id_usuario_mio = give_parameter_get ("id_usuario_mio", "");
		// Read user data to include in form
		$query1 = "SELECT * FROM tusuario WHERE id_usuario = '".$id_usuario_mio."'";
		$resq1 = mysql_query ($query1);
		$rowdup = mysql_fetch_array ($resq1);
		if (!$rowdup) {
			echo "<h3 class='error'>".lang_string ('user_error')."</h3>";
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
	}
	
	// Edit user
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	if (isset ($_POST["edicion"])){		
		// We do it
		if (isset ($_POST["pass1"])){
			$nombre = give_parameter_post ("nombre");
			$nombre_real = give_parameter_post ("nombre_real");
			$nombre_viejo = give_parameter_post ("id_usuario_antiguo");
			$password = give_parameter_post ("pass1");
			$password2 = give_parameter_post ("pass2");
			$lang = give_parameter_post ("lang");

			if ($password <> $password2){
				echo "<h3 class='error'>".lang_string ('pass_nomatch')."</h3>";
			}
			else {
				if (isset($_POST["nivel"]))
				$nivel = give_parameter_post ("nivel");
				$direccion = give_parameter_post ("direccion");
				$telefono = give_parameter_post ("telefono");
				$comentarios = give_parameter_post ("comentarios");
				$avatar = give_parameter_post ("avatar");
				$avatar = substr($avatar, 0, strlen($avatar)-4);

				if (dame_password($nombre_viejo)!=$password){
					$password=md5($password);
					$sql = "UPDATE tusuario SET `lang` = '$lang', nombre_real ='".$nombre_real."', id_usuario ='".$nombre."', password = '".$password."', telefono ='".$telefono."', direccion ='".$direccion." ', nivel = '$nivel', comentarios = '$comentarios', avatar = '$avatar' WHERE id_usuario = '$nombre_viejo'";
				}
				else 	
					$sql = "UPDATE tusuario SET lang = '$lang', nombre_real ='".$nombre_real."', id_usuario ='".$nombre."', telefono ='".$telefono."', direccion ='".$direccion." ', nivel = '".$nivel."', comentarios = '".$comentarios."', avatar = '$avatar' WHERE id_usuario = '".$nombre_viejo."'";
				$resq2=mysql_query($sql);
	
				// Add group / to profile
				// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
				if (isset($_POST["grupo"]))
					if ($_POST["grupo"] <> ""){
						$grupo = $_POST["grupo"];
						$perfil = $_POST["perfil"];
						$id_usuario_edit = $_SESSION["id_usuario"];
						$sql = "INSERT INTO tusuario_perfil (id_usuario,id_perfil,id_grupo,assigned_by) VALUES ('".$nombre."',$perfil,$grupo,'".$id_usuario_edit."')";
						// echo "DEBUG:".$sql;
						$resq2=mysql_query($sql);
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
				echo "<h3 class='suc'>".lang_string ('update_user_ok')."</h3>";
			}
		}
		else {
			echo "<h3 class='error'>".lang_string ('update_user_no')."</h3>";
		}
	} 

	// Create user
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	if (isset($_GET["nuevo_usuario"])){
		// Get data from POST
		$nombre = give_parameter_post ("nombre");
		$password = give_parameter_post ("pass1");
		$password2 = give_parameter_post ("pass2");
		$nombre_real = give_parameter_post ("nombre_real");
		$lang = give_parameter_post ("lang");
		if ($password <> $password2){
			echo "<h3 class='error'>".lang_string ('pass_nomatch')."</h3>";
		}
		$direccion = give_parameter_post ("direccion");
		$telefono = give_parameter_post ("telefono");
		$comentarios = give_parameter_post ("comentarios");
		if (isset($_POST["nivel"]))
			$nivel = give_parameter_post ("nivel");
		$password = md5($password);
		$avatar = give_parameter_post ("avatar");
		$avatar = substr($avatar, 0, strlen($avatar)-4);

		$ahora = date("Y-m-d H:i:s");
		$sql_insert = "INSERT INTO tusuario (id_usuario,direccion,password,telefono,fecha_registro,nivel,comentarios, nombre_real,avatar, lang) VALUES ('".$nombre."','".$direccion."','".$password."','".$telefono."','".$ahora."','".$nivel."','".$comentarios."','".$nombre_real."','$avatar','$lang')";
		$resq1 = mysql_query($sql_insert);
			if (! $resq1)
				echo "<h3 class='error'>".lang_string ('create_user_no')."</h3>";
			else {
				echo "<h3 class='suc'>".lang_string ('create_user_ok')."</h3>";
			}
		$id_usuario_mio = $nombre;
		$modo ="edicion";
	}
	echo "<h2>".lang_string ('user_management')."</h2>";
	if (isset($_GET["alta"])){
			if ($_GET["alta"]==1){
			echo '<h3>'.lang_string ('create_user').'</h3>';
			}
	}
	if (isset($_GET["id_usuario_mio"]) OR isset($_GET["nuevo_usuario"])){
		echo '<h3>'.lang_string ('update_user').'</h3>';
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
?>
<tr>
<td class="datos"><?php echo lang_string ('id_user') ?>
<td class="datos"><input type="text" size=15 name="nombre" value="<?php echo $id_usuario_mio ?>">
<?php
if (isset($avatar)){
	echo "<td class='datos' rowspan=6>";
	echo "<img src='images/avatars/".$avatar.".png' id='avatar_preview'>";
}
?>
<tr><td class="datos2"><?php echo lang_string ('real_name') ?>
<td class="datos2"><input type="text" size=25 name="nombre_real" value="<?php echo $nombre_real ?>">
<tr><td class="datos"><?php echo lang_string ('password') ?>
<td class="datos"><input type="password" name="pass1" value="<?php echo $password ?>">
<tr><td class="datos2"><?php echo lang_string ('password') ?> - <?php echo lang_string ('confirmation') ?>
<td class="datos2"><input type="password" name="pass2" value="<?php echo $password ?>">
<tr><td class="datos">E-Mail
<td class="datos"><input type="text" name="direccion" size="30" value="<?php echo $direccion ?>">


<?PHP
// Avatar
echo "<tr><td>".lang_string("avatar");
echo "<td>";

$ficheros = list_files('images/avatars/', "png", 1, 0, "small");
$avatar_forlist = $avatar . ".png";
echo print_select ($ficheros, "avatar", $avatar_forlist, '', '', 0, true, 0, false, false);	

?>

<tr><td class="datos"><?php echo lang_string ('telefono') ?>
<td class="datos" colspan=2><input type="text" name="telefono" value="<?php echo $telefono ?>">
<tr><td class="datos2"><?php echo lang_string ('global_profile') ?>

<td class="datos2" colspan=2>
<?php if ($nivel == "1"){
	echo lang_string ('administrator').'&nbsp;<input type="radio" class="chk" name="nivel" value="1" checked><a href="#" class="tip">&nbsp;<span>'.$help_label["users_msg1"].'</span></a>&nbsp;';
	echo "&nbsp;&nbsp;";
	echo lang_string ('normal_user').'&nbsp;<input type="radio" class="chk" name="nivel" value="0"><a href="#" class="tip">&nbsp;<span>'.$help_label["users_msg2"].'</span></a>';
} else {
	echo lang_string ('administrator').'&nbsp;<input type="radio" class="chk" name="nivel" value="1"><a href="#" class="tip">&nbsp;<span>'.$help_label["users_msg1"].'</span></a>&nbsp;';
	echo "&nbsp;&nbsp;";
	echo lang_string ('normal_user').'&nbsp;<input type="radio" class="chk" name="nivel" value="0" checked><a href="#" class="tip">&nbsp;<span>'.$help_label["users_msg2"].'</span></a>';
}

echo "<tr>";
echo "<td>";
echo lang_string("Language");
echo "<td>";
print_select_from_sql ("SELECT * FROM tlanguage", "lang", $lang, '', 'Default', '', false, false, true, false);

?>


<tr><td class="datos" colspan="3"><?php echo lang_string ('comments') ?>
<tr><td class="datos2" colspan="3"><textarea name="comentarios" cols="75" rows="3"><?php echo $comentarios ?></textarea>

<?php
if ($modo == "edicion") { // Only show groups for existing users

	// Combo for group
	echo '<input type="hidden" name="edicion" value="1">';
	echo '<input type="hidden" name="id_usuario_antiguo" value="'.$id_usuario_mio.'">';
	
	echo '<tr><td class="datos">'.lang_string ('group_avail').'<td class="datos" colspan=2><select name="grupo" class="w155">';
	echo "<option value=''>".lang_string ('none');
	$sql1='SELECT * FROM tgrupo ORDER BY nombre';
	$result=mysql_query($sql1);
	while ($row=mysql_fetch_array($result)){
		echo "<option value='".$row["id_grupo"]."'>".$row["nombre"];
	}
	echo '</select>';
	
	echo "<tr><td class='datos2'>".lang_string ('profiles');
	echo "<td class='datos2' colspan=2><select name='perfil' class='w155'>";
	$sql1='SELECT * FROM tprofile ORDER BY name';
	$result=mysql_query($sql1);
	while ($row=mysql_fetch_array($result)){
		echo "<option value='".$row["id"]."'>".$row["name"];
	}
	echo '</select>';
	echo "</table>";

	echo "<div class='button' style='width:620px'>";
	echo "<input name='uptbutton' type='submit' class='sub next' value='".__('update')."'>";
	echo "</div><br>";


	


	// Show user profile / groups assigned
	// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
	$sql1='SELECT * FROM tusuario_perfil WHERE id_usuario = "'.$id_usuario_mio.'"';
	$result=mysql_query($sql1);
	
	echo '<h3>'.lang_string ('listGroupUser').'</h3>';
	echo "<table width='620'  class='databox'>";
	if (mysql_num_rows($result)){
		while ($row=mysql_fetch_array($result)){
			echo '<td>';
			echo "<b style='margin-left:10px'>".dame_perfil($row["id_perfil"])."</b> / ";
			echo "<b>".dame_grupo($row["id_grupo"])."</b>";
			echo '<td><a href="index.php?sec=users&sec2=godmode/usuarios/configurar_usuarios&id_usuario_mio='.$id_usuario_mio.'&borrar_grupo='.$row["id_up"].' " onClick="if (!confirm(\' '.lang_string ('are_you_sure').'\')) return false;"><img border=0 src="images/cross.png"></a><tr>';
		}
	}
	else { echo '<tr><td colspan="3">'.lang_string ('no_profile').'</td></tr>';}
}	

	if (isset($_GET["alta"])){
		echo "</table>";
		echo "<div class='button' style='width: 615px' >";
		echo '<input name="crtbutton" type="submit" class="sub create" value="'.lang_string ('create').'">';
		echo '</div>';
	} 
?> 
</form>
</td></tr></table>


<script  type="text/javascript">
$(document).ready (function () {
	$("#avatar").change (function () {
		icon = this.value.substr(0,this.value.length-4);
		$("#avatar_preview").fadeOut ('normal', function () {
			$(this).attr ("src", "images/avatars/"+icon).fadeIn ();
		});
	});
});
</script>

