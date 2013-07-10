<?php

// Integria IMS - http://integriaims.com
// ==================================================
// Copyright (c) 2008 Artica Soluciones Tecnologicas
// Copyright (c) 2008 Esteban Sanchez, estebans@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

include_once ('include/functions.php');
include_once ('include/functions_ui.php');

$get_alerts = get_parameter ('get_alerts', 0);

if ($get_alerts) {
	
	$check_cron = check_last_cron_execution ();
	$check_emails = check_email_queue ();
	$minutes_last_exec = check_last_cron_execution (true);
	$queued_emails = check_email_queue (true);
	
	$alerts = '';
	
	if ($minutes_last_exec == '') {
		$alerts .= ui_print_error_message(__('Crontask not installed. Please check documentation!'), '', '', 'h4');
	}
	if (!$check_cron) {
		$alerts .= ui_print_error_message (__('Last time Crontask was executed was ').$minutes_last_exec.__(' minutes ago'), '', '', 'h4'); 
	}
	if (!$check_emails) {
		$alerts .= ui_print_error_message(__('Too many pending mail in mail queue: ').$queued_emails.('. Check SMTP configuration'), '', '', 'h4'); 
	}
	
	echo $alerts;
	return;
}

?>
