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


// Load globar vars
global $config;
check_login();

if (! give_acl ($config["id_user"], 0, "UM")) {
	audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access User Management");
	require ("general/noaccess.php");
	exit;
}

include_once('include/functions_user.php');

if (isset($_GET["borrar_usuario"])){ // if delete user

	$nombre = safe_input ($_GET["borrar_usuario"]);
	user_delete_user($nombre);
	
}

$offset = get_parameter ("offset", 0);
$search_text = get_parameter ("search_text", "");
$disabled_user = get_parameter ("disabled_user", -1);
$level = get_parameter ("level", -10);
$group = get_parameter ("group", 0);

echo '<h1>'.__('User management') . '</h1>';

$table->id = "table-user_search";
$table->width = "99%";
$table->class = "search-table";
$table->size = array ();
$table->style = array ();
$table->data = array ();

$table->data[0][0] = print_input_text ("search_text", $search_text, '', 15, 0, true, __('Search text'));

$user_status = array();
$user_status[0] = __('Enabled');
$user_status[1] = __('Disabled');
$table->data[0][1] = print_select ($user_status, 'disabled_user', $disabled_user, '', __('Any'), -1, true, 0, false, __('User status'));

$global_profile = array();
$global_profile[-1] = __('External');
$global_profile[0] = __('Standard');
$global_profile[1] = __('Administrator');
$table->data[0][2] = print_select ($global_profile, 'level', $level, '', __('Any'), -10, true, 0, false, __('Global profile'));

$table->data[0][3] = print_select (get_user_groups(), 'group', $group, '', __('Any'), 0, true, 0, false, __('Group'));

$table->data[0][4] = print_submit_button (__('Search'), 'search', false, 'class="sub search"', true);

echo "<form name='bskd' method=post action='index.php?sec=users&sec2=godmode/usuarios/lista_usuarios'>";
print_table ($table);
echo "</form>";


$search = "WHERE 1=1 ";
if ($search_text != "") {
	$search .= " AND (id_usuario LIKE '%$search_text%' OR comentarios LIKE '%$search_text%' OR nombre_real LIKE '%$search_text%' OR direccion LIKE '%$search_text%')";
}

if ($disabled_user > -1) {
	$search .= " AND disabled = $disabled_user";
}

if ($level > -10) {
	$search .= " AND nivel = $level";
}

if ($group > 0) {
	$search .= " AND id_usuario = ANY (SELECT id_usuario FROM tusuario_perfil WHERE id_grupo = $group)";
}

$query1 = "SELECT * FROM tusuario $search ORDER BY id_usuario";

$count = get_db_sql("SELECT COUNT(id_usuario) FROM tusuario $search ");

pagination ($count, "index.php?sec=users&sec2=godmode/usuarios/lista_usuarios&search_text=".$search_text."&disabled_user=".$disabled_user."&level=".$level."&group=".$group, $offset, true);

$sql1 = "$query1 LIMIT $offset, ". $config["block_size"];

echo '<table width="99%" class="listing">';
echo '<th>'.print_checkbox('all_user_checkbox', 1, false, true);
echo '<th title="'.__('Enabled/Disabled').'">'.__('E/D');
echo '<th>'.__('User ID');
echo '<th>'.__('Name');
echo '<th>'.__('Company');
echo '<th>'.__('Last contact');
echo '<th>'.__('Profile');
echo '<th>'.__('Delete');

$resq1 = process_sql($sql1);
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
		$nivel = "<img src='images/group.png' title='".__("Standard user")."'>";
	elseif ($rowdup["nivel"] == 1)
		$nivel = "<img src='images/integria_mini_logo.png' title='".__("Administrator")."'>";
	else
		$nivel = "<img src='images/user_gray.png' title='".__("External user")."'>";

    $disabled = $rowdup["disabled"];	
    $id_company = $rowdup["id_company"];	
	
	echo "<tr><td>";
	echo print_checkbox_extended ("user-".$rowdup["id_usuario"], $rowdup["id_usuario"], false, false, "", "class='user_checkbox'", true);
	
	echo "<td>";
	if ($disabled == 1){
		echo "<img src='images/lightbulb_off.png' title='".__("Disabled")."'> ";
	}
	
	echo "<td>";
	echo "<a href='index.php?sec=users&sec2=godmode/usuarios/configurar_usuarios&update_user=".$nombre."'>".ucfirst($nombre)."</a>";
	
	echo "<td style='font-size:9px'>" . $realname;	
	$company_name = (string) get_db_value ('name', 'tcompany', 'id', $id_company);	
	echo "<td>".$company_name."</td>";


	echo "<td style='font-size:9px'>".human_time_comparation($fecha_registro);
	echo "<td>";
	print_user_avatar ($nombre, true);
	echo "&nbsp;";

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

	echo $nivel;

	echo '<td align="center">';
	echo '<a href="index.php?sec=users&sec2=godmode/usuarios/lista_usuarios&borrar_usuario='.$nombre.'" onClick="if (!confirm(\''.__('Are you sure?').'\')) return false;"><img src="images/cross.png"></a>';
	echo '</td>';
}
echo "</table>";

echo "<div style='width:99%' class='button'>";

echo "<form method=post action='index.php?sec=users&sec2=godmode/usuarios/configurar_usuarios&alta=1'>";
echo "<input type='button' onclick='process_massive_operation(\"enable_users\")' class='sub people' name='en' value='".__('Enable selected')."'>";
echo "<input type='button' onclick='process_massive_operation(\"disable_users\")' class='sub people' name='dis' value='".__('Disable selected')."'>";
echo "<input type='button' onclick='if (confirm(\"".__('Are you sure?')."\")) process_massive_operation(\"delete_users\");' class='sub delete' name='del' value='".__('Delete selected')."'>";
echo "<input type='submit' class='sub create' name='crt' value='".__('Create')."'>";
echo "</form>";
echo "</div>";

?>

<script type="text/javascript" src="include/js/integria_users.js"></script>
<script type="text/javascript" src="include/js/jquery.validation.functions.js"></script>
<script type="text/javascript">
	// Change the state of all the checkbox depending on the checkbox of the header
	$('input[name="all_user_checkbox"]').change(function (event) {
		$('input.user_checkbox').prop('checked', $(this).prop('checked'));
	});
	
	trim_element_on_submit('#text-search_text');
</script>
