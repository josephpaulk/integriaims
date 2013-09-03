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

require_once ('include/functions_calendar.php');
require_once ('include/functions_workunits.php');


global $config;

$get_events = (bool) get_parameter ('get_events');

if ($get_events) {

	$start_date = get_parameter("start_date");
	$end_date = get_parameter("end_date");

	$events = calendar_get_events_agenda($start_date, $end_date, $pn, $config['id_user']);

	$events_result = array();

	//Clean name output
	foreach ($events as $ev) {
		$ev["name"] = safe_output($ev["name"]);
		array_push($events_result, $ev);
	}
	
	echo json_encode($events_result);
	
	return;
}

?>
 	
