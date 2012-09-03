<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2012 Ártica Soluciones Tecnológicas
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

if (! give_acl ($config["id_user"], 0, "UM")) {
	audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation","Trying to access user edition");
	require ("general/noaccess.php");
	exit;
}

// Init. vars
$comentarios = "";
$direccion = "";
$telefono = "";
$password = "";
$update_user = "";
$lang = "";
$nombre_real = "";
$nivel = 0;
$disabled = 0;
$simple_mode = 0;
$id_company = 0;
// Default is create mode (creacion)
$modo = "creacion";

if (isset($_GET["borrar_grupo"])) {
	$grupo = get_parameter ('borrar_grupo');
	enterprise_hook ('delete_group');
}

$action = get_parameter("action", "edit");
$alta = get_parameter("alta");

///////////////////////////////
// LOAD USER VALUES
///////////////////////////////
if (($action == 'edit' || $action == 'update') && !$alta) {
	$modo = "edicion";
	$update_user = get_parameter ("update_user", "");
	// Read user data to include in form
	$sql = "SELECT * FROM tusuario WHERE id_usuario = '".$update_user."'";
	$rowdup = get_db_row_sql ($sql);

	if ($rowdup === false) {
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
		$disabled = $rowdup["disabled"];
		$simple_mode = $rowdup["simple_mode"];
		$id_company = $rowdup["id_company"];
	}
}


///////////////////////////////
// UPDATE USER
///////////////////////////////
if ($action == 'update')  {
	if (isset ($_POST["pass1"])) {
		$nombre_real = get_parameter ("nombre_real");
		$nombre_viejo = get_parameter ("update_user");
		$nombre = $nombre_viejo ;
		$password = get_parameter ("pass1");
		$password2 = get_parameter ("pass2");
		$lang = get_parameter ("lang");
		$disabled = get_parameter ("disabled");
		$simple_mode = get_parameter ("simple_mode");
		$id_company = get_parameter ("id_company");
		if ($password <> $password2){
			echo "<h3 class='error'>".__('Passwords don\'t match.')."</h3>";
		}
		else {
			if (isset($_POST["nivel"])) {
				$nivel = get_parameter ("nivel");
			}
			$direccion = trim (ascii_output(get_parameter ("direccion")));
			$telefono = get_parameter ("telefono");
			$comentarios = get_parameter ("comentarios");
			$avatar = get_parameter ("avatar");
			$avatar = substr($avatar, 0, strlen($avatar)-4);

			if (dame_password ($nombre_viejo) != $password){
				$password = md5($password);
				$sql = "UPDATE tusuario SET disabled= $disabled, `lang` = '$lang', nombre_real ='".$nombre_real."', password = '".$password."', telefono ='".$telefono."', direccion ='".$direccion."', nivel = '$nivel', comentarios = '$comentarios', avatar = '$avatar', id_company = '$id_company', simple_mode = '$simple_mode' WHERE id_usuario = '$nombre_viejo'";
			}
			else {
				$sql = "UPDATE tusuario SET disabled= $disabled, lang = '$lang', nombre_real ='".$nombre_real."', telefono ='".$telefono."', direccion ='".$direccion."', nivel = '".$nivel."', comentarios = '".$comentarios."', avatar = '$avatar', id_company = '$id_company', simple_mode = '$simple_mode' WHERE id_usuario = '".$nombre_viejo."'";
			}
			
			$resq2 = process_sql($sql);

			// Add group / to profile
			// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
			if (isset($_POST["grupo"])) {
				if ($_POST["grupo"] <> "") {
					$grupo = $_POST["grupo"];
					$perfil = $_POST["perfil"];
					$id_usuario_edit = $_SESSION["id_usuario"];
					$res = enterprise_hook('associate_userprofile');
					if($res === false) {
						echo "<h3 class='error'>".__('There was a problem assigning user profile')."</h3>";
					}
				}
			}
			$modo = "edicion";
			echo "<h3 class='suc'>".__('Successfully updated')."</h3>";
		}
	}
	else {
		echo "<h3 class='error'>".__('There was a problem updating user')."</h3>";
	}
} 

///////////////////////////////
// CREATE USER
///////////////////////////////
if ($action == 'create'){
	// Get data from POST
	$nombre = strtolower(get_parameter ("nombre"));
	$password = get_parameter ("pass1");
	$password2 = get_parameter ("pass2");
	$nombre_real = get_parameter ("nombre_real");
	$lang = get_parameter ("lang");
	if ($password <> $password2){
		echo "<h3 class='error'>".__('Passwords don\'t match. Please repeat again')."</h3>";
	}
	$direccion = rtrim(get_parameter ("direccion"));
	$telefono = get_parameter ("telefono");
	$id_company = get_parameter ("id_company");
	$simple_mode = get_parameter ("simple_mode");
	$comentarios = get_parameter ("comentarios");
	if (isset($_POST["nivel"])) {
		$nivel = get_parameter ("nivel",0);
	}
	$password = md5($password);
	$avatar = get_parameter ("avatar");
	$avatar = substr($avatar, 0, strlen($avatar)-4);
	$disabled = get_parameter ("disabled");
		
	$ahora = date("Y-m-d H:i:s");
	$sql_insert = "INSERT INTO tusuario (id_usuario,direccion,password,telefono,fecha_registro,nivel,comentarios, nombre_real,avatar, lang, disabled, id_company, simple_mode) VALUES ('".$nombre."','".$direccion."','".$password."','".$telefono."','".$ahora."','".$nivel."','".$comentarios."','".$nombre_real."','$avatar','$lang','$disabled','$id_company','$simple_mode')";
	
	$resq1 = process_sql($sql_insert);
		if (! $resq1)
			echo "<h3 class='error'>".__('Could not be created')."</h3>";
		else {
			echo "<h3 class='suc'>".__('Successfully created')."</h3>";
		}
	$update_user = $nombre;
	$modo ="edicion";
}

echo "<h2>".__('User management')."</h2>";
if (isset($_GET["alta"])){
	if ($_GET["alta"]==1){
		echo '<h3>'.__('Create user').'</h3>';
	}
}

if (isset($_GET["update_user"]) OR isset($_GET["nuevo_usuario"])){
	echo '<h3>'.__('Update user').'</h3>';
}

?> 
<table width='720' class='databox'>
<?php 
if (isset($_GET["alta"]))
	// Create URL
	echo '<form name="new_user" method="post" action="index.php?sec=users&sec2=godmode/usuarios/configurar_usuarios&nuevo_usuario=1">';
else
	// Update URL
	echo '<form name="user_mod" method="post" action="index.php?sec=users&sec2=godmode/usuarios/configurar_usuarios&update_user='.$update_user.'">';

echo '<tr>';
echo '<td class="datos">'.__('User ID');
echo '<td class="datos" colspan=5>';

if (isset($_GET["alta"])){
    echo '<input type="text" size=15 name="nombre" id="nombre" value="'.$update_user.'">';
    print_help_tip (__("User cannot have Blank spaces", false));
} else {
    echo '<i>';
    echo $update_user;
    echo "</i>";
}

if (isset($avatar)){
	echo "<td class='datos' rowspan=6>";
	echo "<img src='images/avatars/".$avatar.".png' id='avatar_preview'>";
}

echo '<tr><td class="datos2">'. __('Activation');
echo '<td class="datos2">';
if($disabled) {
	$active_chk = '';
	$disabled_chk = ' checked';
}
else {
	$active_chk = ' checked';
	$disabled_chk = '';
}

echo __('Enabled').'&nbsp;<input type="radio" class="chk" name="disabled" value="0"'.$active_chk.'>';
echo "&nbsp;&nbsp;";
echo __('Disabled').'&nbsp;<input type="radio" class="chk" name="disabled" value="1"'.$disabled_chk.'>';
    
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

<tr><td class="datos"><?php echo __('Company') ?>
<td class="datos" colspan=2><?php print_select (get_companies (), 'id_company', $id_company, '', __('None'), 0, false); ?>

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

print_help_tip (__("External users cannot work inside a group, will show only it's own data. Standard users works with the ACL system, and administrators have full access to everything", false));

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
echo get_db_sql ("SELECT COUNT(*) FROM tincidencia WHERE id_creator = '".$update_user."'");
echo "</b></td></tr>";

echo '<tr><td class="datos2">'.__('Reports');
echo '<td class="datos2">';

$now = date("Y-m-d H:i:s");
$now_year = date("Y");
$now_month = date("m");

$working_month = get_parameter ("working_month", $now_month);
$working_year = get_parameter ("working_year", $now_year);

// Full report			
echo "<a href='index.php?sec=users&sec2=operation/user_report/report_full&only_projects=1&wu_reporter=$update_user'>";
echo "<img title='".__("Full report")."' src='images/page_white_stack.png'>";
echo "</a>";

// Workunit report (detailed)
echo "&nbsp;&nbsp;";
echo "<a href='index.php?sec=users&sec2=operation/users/user_workunit_report&timestamp_l=$begin_month&timestamp_h=$end_month&id=$update_user'>";
echo "<img border=0 title='".__("Workunit report")."' src='images/page_white_text.png'></A>";

// Clock to calendar montly report for X user
echo "&nbsp;&nbsp;";
echo "<a href='index.php?sec=users&sec2=operation/user_report/monthly&month=$working_month&year=$working_year&id=$update_user'><img src='images/clock.png' title='".__("Montly calendar report")."' border=0></a>";

// Graph stats montly report for X user
echo "&nbsp;&nbsp;";
echo "<a href='index.php?sec=users&sec2=operation/user_report/monthly_graph&month=$working_month&year=$working_year&id=$update_user'><img src='images/chart_bar.png' title='".__("Montly report")."' border=0></a></center></td>";
      


echo '<tr><td class="datos2">'. __('Simple mode');
echo '<td class="datos2">';

if($simple_mode) {
	$active_chk = ' checked';
	$disabled_chk = '';
}
else {
	$active_chk = '';
	$disabled_chk = ' checked';
}

echo __('Enabled').'&nbsp;<input type="radio" class="chk" name="simple_mode" value="1"'.$active_chk.'>';
echo "&nbsp;&nbsp;";
echo __('Disabled').'&nbsp;<input type="radio" class="chk" name="simple_mode" value="0"'.$disabled_chk.'>';
echo '</tr>';
echo '<tr>';
if ($modo == "edicion") { // Only show groups for existing users
	enterprise_hook ('manage_profiles');
	echo "</table>";

	echo "<div class='button' style='width:720px'>";
	print_input_hidden ('action', 'update');
	echo "<input name='uptbutton' type='submit' class='sub next' value='".__('Update')."'>";
	echo "</div><br>";
}	

enterprise_hook ('show_delete_profiles');

if (isset($_GET["alta"])){
	echo "</table>";
	echo "<div class='button' style='width: 720px' >";
	echo '<input name="crtbutton" type="submit" class="sub create" value="'.__('Create').'">';
	print_input_hidden ('action', 'create');
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
	
	inputControl("nombre");
});
</script>


