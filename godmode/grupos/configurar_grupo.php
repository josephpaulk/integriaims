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

global $config;
check_login();

include("include/functions_user.php");

if (defined ('AJAX')) {

	global $config;

	$search_users = (bool) get_parameter ('search_users');

	if ($search_users) {
		require_once ('include/functions_db.php');
		
		$id_user = $config['id_user'];
		$string = (string) get_parameter ('q'); /* q is what autocomplete plugin gives */
		
		$users = get_user_visible_users ($config['id_user'],"IR", false);
		if ($users === false)
			return;
		
		foreach ($users as $user) {
			if(preg_match('/'.$string.'/', $user['id_usuario']) || preg_match('/'.$string.'/', $user['nombre_real'])) {
				echo $user['id_usuario'] . "|" . $user['nombre_real']  . "\n";
			}
		}
		
		return;
 	}
}

if (! give_acl ($config["id_user"], 0, "UM")) {
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access group management");
	require ("general/noaccess.php");
	exit;
}

// Inic vars

$id = (int) get_parameter ('id');
$name = "";
$icon = "";
$id_user_default = "";
$id_user = "";
$banner = "";
$parent = "";
$forced_email = true;
$soft_limit = 5;
$hard_limit = 20;
$enforce_soft_limit = 1;
$id_sla = 0;

$creacion_grupo = (bool) get_parameter ('creacion_grupo');
	
if ($id) {
	$group = get_db_row ('tgrupo', 'id_grupo', $id);
	if ($group) {
		$name = $group['nombre'];
		$icon = $group['icon'];
		$id_user_default = $group['id_user_default'];
		$banner = $group['banner'];
		$parent = $group['parent'];
		$soft_limit = $group["soft_limit"];
		$hard_limit = $group["hard_limit"];
		$enforce_soft_limit = (bool) $group["enforce_soft_limit"];
		$forced_email = (bool) $group['forced_email'];
		$id_sla = $group["id_sla"];
		$id_user = get_db_value ('id_user_default', 'tgrupo', 'id_grupo', $id);


	} else {
		echo "<h3 class='error'>".__('There was a problem loading group')."</h3>";
		include ("general/footer.php");
		exit;
	}
}

echo '<h2>'.__('Group management').'</h2>';

$table->width = '600px';
$table->class = 'databox';
$table->colspan = array ();
$table->rowspan = array ();
$table->rowspan[0][2] = 5;
$table->data = array ();

/* First row */
$table->data[0][0] = print_input_text ('name', $name, '', 20, 0, true, __('Name'));
$table->data[0][1] = print_checkbox ('forced_email', 1, $forced_email, true, __('Forced email'));

/* Banner preview image is a bit bigger */
$table->data[0][2] = '<span id="banner_preview">';
if ($id && $banner != '') {
	$table->data[0][2] .= ' <img src="images/group_banners/'.$banner.'" />';
}
$table->data[0][2] .= '</span>';

$table->data[2][0] = print_select_from_sql ('SELECT id_grupo, nombre FROM tgrupo ORDER BY nombre',
	'parent', $parent, '', 'None', '', true, false, false, __('Parent'));


//$table->data[2][1] = combo_user_visible_for_me ($id_user_default, "id_user_default", 0, "IR", true, __('Default user'));

$params_creator['input_id'] = 'text-id_user';
$params_creator['input_name'] = 'id_user';
$params_creator['input_value'] = $id_user;
$params_creator['title'] = __('Default user');
$params_creator['return'] = true;
$params_creator['return_help'] = true;
$table->data[2][1] = user_print_autocomplete_input($params_creator);

/*$table->data[2][1] = print_input_text_extended ('id_user', $id_user, 'text-id_user', '', 15, 30, false, '',
			array('style' => 'background: url(' . $src_code . ') no-repeat right;'), true, '', __('Default user'))
		. print_help_tip (__("Type at least two characters to search"), true);*/


$icons = list_files ('images/groups_small/', 'png', 0, true, '');
$table->data[3][0] = print_select ($icons, 'icon', $icon, '', 'None', '', true, false, false, __('Icon'));
$table->data[3][0] .= '&nbsp;&nbsp;<span id="icon_preview">';
if ($id && $icon != '') {
	$table->data[3][0] .= '<img src="images/groups_small/'.$icon.'" />';
}
$table->data[3][0] .= '</span>';

$banners = list_files ('images/group_banners/', 'png', 0, true);
$table->data[3][1] = print_select ($banners, "banner", $banner, '', 'None', '', true, false, false, __('Banner'));

$table->data[4][0] = print_input_text ('soft_limit', $soft_limit, '', 10, 0, true , __('Incident Soft limit'));


$table->data[4][1] = print_checkbox ('enforce_soft_limit', 1, $enforce_soft_limit, true, __('Enforce soft limit'));

$table->data[5][0] = print_input_text ('hard_limit', $hard_limit, '', 10, 0, true , __('Incident Hard limit'));

$table->data[5][1] = print_select_from_sql ("SELECT id, name FROM tsla ORDER BY name",
	'id_sla', $id_sla, '', '', 0, true, false, false, __('Incident SLA'));

echo '<form method="post" action="index.php?sec=users&sec2=godmode/grupos/lista_grupos">';
print_table ($table);
echo '<div class="button" style="width: '.$table->width.'">';

if ($id) {
	print_submit_button (__('Update'), '', false, 'class="sub upd"');
	print_input_hidden ('update_group', 1);
	print_input_hidden ('id', $id);
} else {
	print_submit_button (__('Create'), '', false, 'class="sub next"');
	print_input_hidden ('create_group', 1);
} 
echo '</div></form>';
?>

<script type="text/javascript" src="include/js/jquery.autocomplete.js"></script>

<script type="text/javascript">
$(document).ready (function () {
	$("#icon").change (function () {
		icon = this.value;
		$("#icon_preview").fadeOut ('normal', function () {
			$(this).empty ().append ($(" <img />").attr ("src", "images/groups_small/"+icon))
				.fadeIn ();
		});
	});
	$("#banner").change (function () {
		banner = this.value;
		$("#banner_preview").fadeOut ('normal', function () {
			$(this).empty ().append ($(" <img />").attr ("src", "images/group_banners/"+banner))
				.fadeIn ();
		});
	});

	$("#text-id_user").autocomplete ("ajax.php",
		{
			scroll: true,
			minChars: 2,
			extraParams: {
				page: "godmode/grupos/configurar_grupo",
				search_users: 1,
				id_user: "<?php echo $config['id_user'] ?>"
			},
			formatItem: function (data, i, total) {
				if (total == 0)
					$("#text-id_user").css ('background-color', '#cc0000');
				else
					$("#text-id_user").css ('background-color', '');
				if (data == "")
					return false;
				return data[0]+'<br><span class="ac_extra_field"><?php echo __("Real name") ?>: '+data[1]+'</span>';
			},
			delay: 200

		});

});
</script>
