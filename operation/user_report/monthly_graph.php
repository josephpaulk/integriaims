<?PHP
// Integria 1.0 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2008 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

if (check_login() != 0) {
 	audit_db("Noauth",$config["REMOTE_ADDR"], "No authenticated access","Trying to access event viewer");
	require ("general/noaccess.php");
	exit;
}

$id_grupo = get_parameter ("id_grupo",0);
$id_user=$config['id_user'];
if (give_acl($id_user, $id_grupo, "PR") != 1){
 	// Doesn't have access to this page
	audit_db($id_user,$config["REMOTE_ADDR"], "ACL Violation","Trying to access to user report without projects rights");
	include ("general/noaccess.php");
	exit;
}
$id = get_parameter ("id","");
if (($id != "") && ($id != $id_user)){
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
$days_in_month=gmdate('t',$first_of_month);
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
echo "<h3>".$year."/".$month."</h3>";
echo "<table class=blank width=700>";
echo "<tr><td>";
echo "<a href='index.php?sec=users&sec2=operation/user_report/monthly_graph&month=$prev_month&year=$prev_year&id=$id_user'> ".__('Prev')."</a>";
echo "<td width=85%>";
echo "&nbsp;";
echo "<td>";
echo "<a href='index.php?sec=users&sec2=operation/user_report/monthly_graph&month=$next_month&year=$next_year&id=$id_user'> ".__('Next')."</a>";
echo "</table>";

// Show graphs
//$from_one_month = date('Y-m-d', strtotime("now - 1 month"));
echo "<div>";
echo "<table width=750 class='blank'>";
echo "<tr><td class=datos>";
echo "<h3>".__('Workunit by task')."</h3>";
echo "<tr><td class=datos>";
echo "<img src='include/functions_graph.php?type=workunit_user&width=650&height=350&id_user=$id_user&date_from=$from_one_month'>";
echo "<tr><td class=datos>";
echo "<h3>".__('Workunit by project')."</h3>";
echo "<tr><td class=datos>";
echo "<img src='include/functions_graph.php?type=workunit_project_user&width=650&height=350&id_user=$id_user&date_from=$from_one_month'>";
echo "</table>";
echo "</div>";



?>
