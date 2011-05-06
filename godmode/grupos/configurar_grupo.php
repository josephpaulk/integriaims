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
$banner = "";
$parent = "";
$forced_email = true;
$soft_limit = 5;
$hard_limit = 20;
$enforce_soft_limit = 1;
$id_inventory_default = 0;

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
		$id_inventory_default = $group["id_inventory_default"];

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


$table->data[2][1] = combo_user_visible_for_me ($id_user_default, "id_user_default", 0, "IR", true, __('Default user'));


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

$table->data[5][1] = print_select_from_sql ("SELECT id, name FROM tinventory ORDER BY name",
	'id_inventory_default', $id_inventory_default, '', '', 0, true, false, false, __('Default inventory object'));

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
});
</script>
