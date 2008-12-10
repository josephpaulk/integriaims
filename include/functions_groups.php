<?php 

// INTEGRIA IMS v2.0
// http://www.integriaims.com
// ===========================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2008 Esteban Sanchez, estebans@artica.es
// Copyright (c) 2007-2008 Artica, info@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License
// (LGPL) as published by the Free Software Foundation; version 2

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.

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
