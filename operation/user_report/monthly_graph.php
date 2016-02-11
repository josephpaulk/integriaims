<?PHP
// Integria 1.0 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2011 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

include_once("include/functions_graph.php");
global $config;

check_login ();

$id_grupo = get_parameter ("id_grupo",0);
$id_user=$config['id_user'];

if ((give_acl($id_user, $id_grupo, "PR") != 1) AND (give_acl($id_user, $id_grupo, "IR") != 1)) {
 	// Doesn't have access to this page
	audit_db($id_user,$config["REMOTE_ADDR"], "ACL Violation","Trying to access to user report without projects access or Incident access permissions");
	include ("general/noaccess.php");
	exit;
}

$id = get_parameter ("id", $config["id_user"]);

$users = get_user_visible_users();

if (($id != "") && ($id != $id_user) && in_array($id, array_keys($users))){
	if (give_acl($id_user, 0, "PW"))
		$id_user = $id;
	else {
		audit_db("Noauth", $config["REMOTE_ADDR"], "No permission access", "Trying to access user workunit report");
		require ("general/noaccess.php");
		exit;
	}
	
}

// Get parameters for actual Calendar show
$time = time();
$month = get_parameter ( "month", date('n', $time));
$year = get_parameter ( "year", date('y', $time));

$today = date('j',$time);
$days_f = array();
$first_of_month = gmmktime(0,0,0,$month,1,$year);
$days_in_month= gmdate('t',$first_of_month);
$locale = $config["language_code"];

$prev_month = $month -1;
$prev_year = $year;
if ($prev_month == 0){
	$prev_month = 12;
	$prev_year = $prev_year -1;
}

$next_month = $month + 1;
$next_year = $year;
if ($next_month == 13){
	$next_month = 1;
	$next_year = $next_year +1;
}
$day = date('d', strtotime("now"));

$from_one_month = "$prev_year-$prev_month-$day";
$next_one_month = "$next_year-$next_month-$day";

echo "<h1>".__('Monthly report for')." $id_user</h1>";
echo "<table class=search-table width=100%>";
echo "<tr><td style='text-align: center;'>";
echo "<a href='index.php?sec=users&sec2=operation/user_report/monthly_graph&month=$prev_month&year=$prev_year&id=$id_user'><img src='images/control_rewind_blue.png' title='" . __('Prev') . "'> </a>";
echo "<span style='font-size: 18px;'>".$year."/".$month."</span>";
echo "<a href='index.php?sec=users&sec2=operation/user_report/monthly_graph&month=$next_month&year=$next_year&id=$id_user'> <img src='images/control_fastforward_blue.png' title='" . __('Next') . "'></a>";
echo "</table>";

// Show graphs
//$from_one_month = date('Y-m-d', strtotime("now - 1 month"));
echo "<table style='width: 100%; padding: 0px'>";
echo "<tr><td class=datos>";
$workunit_by_task = '<br><div class="pie_frame">' . graph_workunit_user (750, 270, $id_user, $from_one_month, 0) . '</div>';

echo print_container('month_report_workunit_by_task', __('Workunit by task'), $workunit_by_task, 'no', true, '10px');

echo "<tr><td class=datos>";

$workunit_by_project = '<br><div class="pie_frame">' . graph_workunit_project_user (750, 270, $id_user, $from_one_month, 0, true) . '</div>'; 
echo print_container('month_report_workunit_by_project', __('Workunit by project'), $workunit_by_project, 'no', true, '10px');

echo "</table>";

?>
