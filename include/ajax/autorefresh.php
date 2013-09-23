<?php
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008-2012 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

$get_seconds = (bool) get_parameter ('get_seconds');
$set_seconds = (bool) get_parameter ('set_seconds');

if ($get_seconds) {
	
	$token = (string) get_parameter ('token');
	
	$seconds = $config[$token];
	
	if (!$seconds) {
		$seconds = 0;
	}
	
	echo json_encode($seconds);
	return;
}

if ($set_seconds) {
	
	$token = (string) get_parameter ('token');
	$seconds = (int) get_parameter ('seconds');
	
	if ($token) {
		update_config_token ($token, $seconds);
	}
	
	return;
}
?>
