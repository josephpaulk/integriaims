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

$id_user = (string) get_parameter ('id');

$user = get_db_row ('tusuario', 'id_usuario', $id_user);
if ($user === false) {
	no_permission ();
	return;
}

if (! user_visible_for_me ($config["id_user"], $id_user)) {
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Forbidden", "User ".$config["id_user"]." tried to access to user detail of '$id_user'");
	no_permission ();
}

$update_user = (bool) get_parameter ('update_user');

$has_permission = false;
if ($id_user == $config['id_user']) {
	$has_permission = true;
} else {
	$groups = get_user_groups ($id_user);
	foreach ($groups as $group) {
		if (give_acl ($config['id_user'], $group['id'], 'UM')) {
			$has_permission = true;
			break;
		}
	}
}

/* Get fields for user */
$email = $user['direccion'];
$phone = $user['telefono'];
$real_name = $user['nombre_real'];
$avatar = $user['avatar'];
$comments = $user['comentarios'];
$lang = $user['lang'];

// Get user ID to modify data of current user.
if ($update_user) {
	if (! $has_permission) {
		audit_db ($_SESSION["id_usuario"], $REMOTE_ADDR, "Security Alert. Trying to modify another user: (".$id_user.") ", "Security Alert");
		no_permission ();
	}
	
	$password = (string) get_parameter ('password');
	$password_confirmation = (string) get_parameter ('password_confirmation');
	$email = (string) get_parameter ('email');
	$phone = (string) get_parameter ('phone');
	$real_name = (string) get_parameter ('real_name');
	$avatar = (string) get_parameter ('avatar');
	$avatar = substr ($avatar, 0, strlen ($avatar) - 4);
	$comments = (string) get_parameter ('comments');
	$lang = (string) get_parameter ('language_code');
	
	$error = false;
	if ($password != '' && md5 ($password) != $user['password']) {
		if ($password != $password_confirmation) {
			echo '<h3 class="error">'.__('Passwords don\'t match').'</h3>';
			$error = true;
		} else {
			// Only when change password
			$sql = sprintf ('UPDATE tusuario
				SET password = MD5("%s")
				WHERE id_usuario = "%s"',
				$password, $id_user);
		}
	} else {
		$sql = sprintf ('UPDATE tusuario
			SET nombre_real = "%s", telefono = "%s", direccion = "%s",
			avatar = "%s", comentarios = "%s", lang = "%s"
			WHERE id_usuario = "%s"',
			$real_name, $phone, $email, $avatar,
			$comments, $lang, $id_user);
	}
	
	if (! $error) {
		$result = process_sql ($sql);
		
		if ($result !== false) {
			echo '<h3 class="suc">'.__('Successfully updated').'</h3>';
		} else {
			echo '<h3 class="error">'.__('Could not be updated').'</h3>';
		}
	}
} 

echo '<h2>'.__('User details').'</h2>';

$table->width = '740px';
$table->class = 'databox';
$table->rowspan = array ();
$table->rowspan[0][2] = 5;
$table->colspan = array ();
$table->colspan[5][0] = 2;
$table->style[0] = 'vertical-align: top';
$table->style[1] = 'vertical-align: top';
$table->style[2] = 'vertical-align: top';
$table->size = array ();
$table->size[2] = '50px';
$table->data = array ();

$table->data[0][0] = print_label (__('User ID'), '', '', true, $id_user);
$table->data[0][1] = '';
$table->data[0][2] = print_label (__('Avatar'), '', '', true);
$table->data[0][2] .= '<img id="avatar-preview" src="images/avatars/'.$avatar.'.png">';

if ($has_permission) {
	$table->data[0][1] = print_input_text ('real_name', $real_name, '', 20, 125, true, __('Real name'));
} else {
	$table->data[0][1] = print_label (__('Real name'), '', '', true, $real_name);
}

if ($has_permission) {
	$table->data[2][0] = print_input_text ('email', $email, '', 20, 60, true, __('Email'));
	$table->data[2][1] = print_input_text ('phone', $phone, '', 20, 40, true, __('Telephone'));
	$table->data[4][0] = print_select_from_sql ("SELECT id_language, name FROM tlanguage ORDER BY name",
		'language_code', $lang, '', __('Default'), '', true, false, false, __('Language'));
	$table->data[5][0] = print_textarea ('comments', 8, 55, $comments, '', true, __('Comments'));
	
	$files = list_files ('images/avatars/', "png", 1, 0, "small");
	$avatar = $avatar.".png";
	$table->data[0][2] .= print_select ($files, "avatar", $avatar, '', '', 0, true);
} else {
	$email = ($email != '') ? $email : __('Not provided');
	$phone = ($phone != '') ? $phone : __('Not provided');
	
	$table->data[2][0] = print_label (__('Email'), '', '', true, $email);
	$table->data[2][1] = print_label (__('Telephone'), '', '', true, $phone);
	if ($user['comentarios'] != '')
		$table->data[3][0] = print_label (__('Comments'), '', '', true, $comments);
}

if ($has_permission) {
	echo '<form method="post" action="index.php?sec=users&sec2=operation/users/user_edit">';
	print_table ($table);
	
	echo '<div class="button" style="width: '.$table->width.'">';
	print_submit_button (__('Update'), 'upd_btn', false, 'class="upd sub"');
	print_input_hidden ('update_user', 1);
	echo '</div>';
	
	$table->data = array ();
	$table->data[0][0] = print_input_password ('password', '', '', 20, 20, true, __('Password'));
	$table->data[0][1] = print_input_password ('password_confirmation', '', '', 20, 20, true, __('Password confirmation'));
	
	echo '<h3>'.__('Change password').'</h3>';
	print_table ($table);
	
	echo '<div class="button" style="width: '.$table->width.'">';
	print_submit_button (__('Update'), 'upd_btn', false, 'class="upd sub"');
	print_input_hidden ('update_user', 1);
	print_input_hidden ('id', $user["id_usuario"]);
	echo '</div>';
	echo '</form>';
} else {
	print_table ($table);
}
?>
<script  type="text/javascript">
$(document).ready (function () {
	$("#avatar").change (function () {
		icon = this.value.substr (0, this.value.length - 4);
		
		$("#avatar-preview").fadeOut ('normal', function () {
			$(this).attr ("src", "images/avatars/"+icon+".png").fadeIn ();
		});
	});
	$('textarea').TextAreaResizer ();
});
</script>
