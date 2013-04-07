<?PHP
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
require_once ('include/functions_tasks.php');
require_once ('include/functions_workunits.php');
require_once ('include/functions_user.php');


if (check_login() != 0) {
 	audit_db("Noauth",$config["REMOTE_ADDR"], "No authenticated access", "Trying to access event viewer");
	require ("general/noaccess.php");
	exit;
}

$id_grupo = get_parameter ("id_grupo",0);
$id = get_parameter ('id', $config["id_user"]);

$real_user_id = $config["id_user"];

if ((give_acl($real_user_id, $id_grupo, "PR") != 1) AND (give_acl($$real_user_id, $id_grupo, "IR") != 1)){
 	// Doesn't have access to this page
	audit_db($real_user_id,$config["REMOTE_ADDR"], "ACL Violation","Trying to access to user monthly report without projects rights");
	include ("general/noaccess.php");
	exit;
}


$users = get_user_visible_users();

if (($id == "") || (($id != $real_user_id) && !in_array($id, array_keys($users)))) {
		audit_db("Noauth", $config["REMOTE_ADDR"], "No permission access", "Trying to access user workunit report");
		require ("general/noaccess.php");
		exit;
}

// Get parameters for actual Calendar show
$time = time();
$month = get_parameter ( "month", date('n', $time));
$year = get_parameter ( "year", date('y', $time));
$lock_month = get_parameter ("lock_month", "");

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


// Lock workunits for this month

//check_workunit_permission ($id_workunit) 
//lock_task_workunit ($id_workunit) 

if ($lock_month != ""){
	$this_month = date('Y-m-d H:i:s',strtotime("$year-$month-01"));
	$this_month_limit = date('Y-m-d H:i:s',strtotime("$year-$month-31"));
	
	$workunits = get_db_all_rows_sql ("SELECT id FROM tworkunit WHERE id_user='$id' AND locked = '' AND timestamp >= '$this_month' AND timestamp < '$this_month_limit'");

	foreach ($workunits as $workunit) {
		if (check_workunit_permission ($workunit["id"]))
			lock_task_workunit ($workunit["id"]);
	}
}

echo "<h1>".__('Monthly report for')." $id";
// Lock all workunits in this month
echo " <a href='index.php?sec=users&sec2=operation/user_report/monthly&lock_month=$month&month=$month&year=$year&id=$id'>";
echo "<img src='images/rosette.png' border=0 title='". _("Lock all workunits in this month"). "'>";
echo "</a>";

echo "&nbsp;&nbsp;<a href='index.php?sec=users&sec2=operation/user_report/monthly&month=$month&year=$year&id=$id&clean_output=1&pdf_output=1'><img src='images/page_white_acrobat.png'></A>";

echo "</h1>";


echo "<table width=700>";
echo "<tr><td>";
echo "<a href='index.php?sec=users&sec2=operation/user_report/monthly&month=$prev_month&year=$prev_year&id=$id'> ".__('Prev')."</a>";
echo "<td width=85%>";

echo "<form method='post' action='index.php?sec=users&sec2=operation/user_report/monthly&month=$month&year=$year'>";


if (give_acl($config["real_user_id"], 0, "PM")){
 
	$params['input_id'] = 'text-id_username';
	$params['input_name'] = 'id';
	$params['return'] = false;
	$params['return_help'] = false;
	$params['input_value']  = $id;
	user_print_autocomplete_input($params);
	
    echo "&nbsp;";
    print_submit_button (__('Show'), 'show_btn', false, 'class="next sub"');
}

echo "</form>";
echo "</td>";

echo "<td>";
echo "<a href='index.php?sec=users&sec2=operation/user_report/monthly&month=$next_month&year=$next_year&id=$id'> ".__('Next')."</a>";
echo "</td>";
echo "</table>";

// Generate calendar

echo "<div>";
echo generate_work_calendar ($year, $month, $days_f, 3, NULL, 1, "", $id);
echo "</div>";

?>
<script type="text/javascript" src="include/js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
<script type="text/javascript" src="include/js/jquery.autocomplete.js"></script>


<script type="text/javascript">

$(document).ready (function () {
	$("#text-start_date").datepicker ();
	$("#textarea-description").TextAreaResizer ();
	$("#text-id_username").autocomplete ("ajax.php",
		{
			scroll: true,
			minChars: 2,
			extraParams: {
				page: "include/ajax/users",
				search_users: 1,
				id_user: "<?php echo $config['id_user'] ?>"
			},
			formatItem: function (data, i, total) {
				if (total == 0)
					$("#text-id_username").css ('background-color', '#cc0000');
				else
					$("#text-id_username").css ('background-color', '');
				if (data == "")
					return false;
				return data[0]+'<br><span class="ac_extra_field"><?php echo __("Nombre real") ?>: '+data[1]+'</span>';
			},
			delay: 200

		});
});
</script>
