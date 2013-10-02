<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2011 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;
enterprise_include ('include/functions_agenda.php', true);

function agenda_get_entry_permission ($id_user, $id_entry) {
	
	$return = enterprise_hook ('agenda_get_entry_permission_extra', array($id_user, $id_entry));
	
	if ($return !== ENTERPRISE_NOT_HOOK)
		return $return;
	return true;
}

?>
