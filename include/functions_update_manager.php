<?php
// Babel Enterprise- http://babelenterprise.com
// ==================================================
// Copyright (c) 2005-2010 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the  GNU General Public License (GPL)
// as published by the Free Software Foundation; version 2

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

define ('FREE_USER', 'INTEGRIA-FREE');

function get_user_key ($settings) {
	global $config;

	if ($settings->customer_key != FREE_USER) {
		if (! file_exists ($settings->keygen_path)) {
			echo '<h3 class="error">';
			echo __('Keygen file does not exists');
			echo '</h3>';

			return '';
		}
		if (! is_executable ($settings->keygen_path)) {
			echo '<h3 class="error">';
			echo __('Keygen file is not executable');
			echo '</h3>';

			return '';
		}

		$user_key = exec (escapeshellcmd ($settings->keygen_path.
				' '.$settings->customer_key.' '.$config['dbhost'].
				' '.$config['dbuser'].' '.$config['dbpass'].
				' '.$config['dbname']));

		return $user_key;
	}

	/* Free users.
	 We only want to know this for statistics records.
	Feel free to disable this extension if you want.
	*/
	global $babel_build;
	global $babel_version;

	$n = (int) get_db_value ('COUNT(`id_usuario`)', 'tusuario', 'disabled', 0);
	//TODO Set any other count, because the modules in Babel haven't a meaning instead that Pandora FMS modules count.
	//$m = (int) get_db_value ('COUNT(`id`)', 'tagente_modulo', 'disabled', 0);

	$user_key = array ('A' => $n, 'M' => 0, 'B' => $babel_build, 'P' => $babel_version);

	return json_encode ($user_key);
}
?>