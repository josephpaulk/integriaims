<?php 

// Integria 2.0 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2008 Artica Soluciones Tecnologicas
// Copyright (c) 2008 Esteban Sanchez

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

/**
 * Sends an email to a group.
 *
 * If the group doesn't have an email configured, the email is only sent
 * to the default user.
 *
 * @param int Group id.
 * @param string Email subject.
 * @param string Email body.
 */
function send_group_email ($id_group, $subject, $body) {
	$group = get_db_row ("tgrupo", "id_grupo", $id_group);
	$name = $group['nombre'];
	$email = $group['email'];
	/* If the group has no email, use the email of the risponsable */
	if ($email == '') {
		$email = get_user_email ($group['id_user_default']);
	}
	
	integria_sendmail ($email, $subject, $body);
}
?>
