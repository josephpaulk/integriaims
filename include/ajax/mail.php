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

require_once('include/functions_mail.php');

ob_clean();

$check_transport = (bool) get_parameter('check_transport');

if ($check_transport) {
	$proto = (string) get_parameter('proto');
	$host = (string) get_parameter('host');
	$port = (int) get_parameter('port');
	$user = (string) get_parameter('user');
	$pass = (string) get_parameter('pass');
	
	$transport_conf = array();
	if (!empty($host)) {
		$transport_conf['host'] = $host;
		
		if (!empty($port)) {
			$transport_conf['port'] = $port;
		}
		if (!empty($user)) {
			$transport_conf['user'] = $user;
		}
		if (!empty($pass)) {
			$transport_conf['pass'] = $pass;
		}
		if (!empty($proto)) {
			$transport_conf['proto'] = $proto;
		}
	}
	try {
		// If the transport can connect, it will not throw an exception
		mail_get_transport($transport_conf);
		
		// Success
		echo json_encode(array(
			'result' => true,
			'message' => __('Success')
		));
		return;
	}
	catch (Exception $e) {
		// Failure
		echo json_encode(array(
			'result' => false,
			'message' => $e->getMessage()
		));
		return;
	}
}

exit;

?>