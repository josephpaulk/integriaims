<?php
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008-2010 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;
$result_msg = "";

check_login ();

$id_grupo = (int) get_parameter ('id_grupo');

if (! give_acl ($config['id_user'], $id_grupo, "AR")) {
 	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access agenda of group ".$id_grupo);
	include ("general/noaccess.php");
	exit;
}

$create_item = (bool) get_parameter ('create_item');
$delete_event = (bool) get_parameter ('delete_event');

// Delete event
if ($delete_event) {
	$id = get_parameter ("delete_event", 0);
	$event_user = get_db_value ("id_user", "tagenda", "id", $id);
	
	if ($event_user == $config['id_user'] || dame_admin($config['id_user'])) { 
		// Only admins (manage incident) or owners can modify incidents, including their notes
		$sql = sprintf ('DELETE FROM tagenda WHERE id = %d', $id);
		process_sql ($sql);
	}
	insert_event ("DELETE CALENDAR EVENT", 0, 0, $event_user);
}

// Get parameters for actual Calendar show
$time = time();
$month = get_parameter ("month", date ('n'));
$year = get_parameter ("year", date ('y'));

$today = date ('j');
$days_f = array();
$first_of_month = gmmktime(0,0,0,$month,1,$year);
$days_in_month=gmdate('t',$first_of_month);
$locale = $config["language_code"];

// Calculate PREV button
if ($month == 1){
	$month_p = 12;
	$year_p = $year -1;
} else {
	$month_p = $month -1;
	$year_p = $year;
}

// Calculate NEXT button
if ($month == 12){
	$month_n = 1;
	$year_n = $year +1;
} else {
	$month_n = $month +1;
	$year_n = $year;
}


$start_date = $mydate_sql = date("Y-m-d", time());

$pn = array('&laquo;'=>"index.php?sec=agenda&sec2=operation/agenda/agenda&month=$month_p&year=$year_p", '&raquo;'=>"index.php?sec=agenda&sec2=operation/agenda/agenda&month=$month_n&year=$year_n");

echo '<div align="center">';
echo generate_calendar_agenda ($year, $month, $days_f, 3, NULL, $locale, $pn, $config['id_user']);
echo '</div>';

?>
