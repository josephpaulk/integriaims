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

// Load global vars

	global $config;
	check_login ();

	require_once('include/functions_user.php');

    $days_f = array();
    $date = date('Y-m-d');


	// --------------------
	// Workunit report (yearly)
	// --------------------
//	$now = date("Y-m-d H:i:s");
	$year = date("Y");

	$year = get_parameter ("year", $year);
	$prev_year = $year -1 ;
	$next_year = $year +1 ;	


	$id_user_show = get_parameter ("id_user", $config["id_user"]);

    if (($id_user_show != $config["id_user"]) AND (!give_acl($config["id_user"], 0, "PM"))){
    	// Doesn't have access to this page
    	audit_db($id_user, $config["REMOTE_ADDR"], "ACL Violation","Trying to access to another user yearly report without proper rights");
    	include ("general/noaccess.php");
		exit;
	}


	// Extended ACL check for project manager
	// TODO - Move to enteprrise, encapsulate in a general function
	$users = get_user_visible_users();

	if (($id_user_show == "") || (($id_user_show != $config["id_user"]) && !in_array($id_user_show, array_keys($users)))) {
			audit_db("Noauth", $config["REMOTE_ADDR"], "No permission access", "Trying to access user workunit report");
			require ("general/noaccess.php");
			exit;
	}


    echo "<h3>".__('Annual report for user')." ". $id_user_show;
    
	if ($clean_output == 0){
		// link full screen
		echo "&nbsp;&nbsp;<a title='Full screen' href='index.php?sec=users&sec2=operation/user_report/report_annual&user_id=$user_id&year=$year&clean_output=1'>";
		echo "<img src='images/html.png'>";
		echo "</a>";

		// link PDF report
		echo "&nbsp;&nbsp;<a title='PDF report' href='index.php?sec=users&sec2=operation/user_report/report_annual&user_id=$user_id&year=$year&clean_output=1&pdf_output=1'>";
		echo "<img src='images/page_white_acrobat.png'>";
		echo "</a>";
	}
	
	echo "</h3>";

	echo "<table cellpadding=4 cellspacing=4 class='blank' style='margin-left: 10px'>";
	echo "<tr><td>";

	if($pdf_output == 0) {
		// Prev. year
		echo "<a href='index.php?sec=users&sec2=operation/user_report/report_annual&year=$prev_year&id_user=$id_user_show&clean_output=$clean_output'> ".__('Prev')."</a>";
		echo "</td>";
	}
			
	echo "<td>";
	echo "<h2>$year</h2>";
	echo "</td>";

	if (give_acl($config["id_user"], 0, "PM") && $pdf_output == 0){		
	
        echo "<form name='xx' method=post action='index.php?sec=users&sec2=operation/user_report/report_annual'>";
        
        echo "<input type='hidden' name='year' value='$year'>";
        
        echo "<td>";
        // Show user
		$params['input_id'] = 'text-id_user';
		$params['input_name'] = 'id_user';
		$params['return'] = false;
		$params['return_help'] = false;
		$params['input_value'] = $id_user_show;
		user_print_autocomplete_input($params);
		
	    echo "</td>";	
        		
	    echo "<td>";
	    print_submit_button (__('Go'), 'sub_btn', false, 'class="upd sub"');
	    echo "</td>";	
	}
	
	if($pdf_output == 0) {
		// Next. year
		echo "<td>";
		echo "<a href='index.php?sec=users&sec2=operation/user_report/report_annual&year=$next_year&id_user=$id_user_show&clean_output=$clean_output'> ".__('Next')."</a>";
		echo "</td>";	
	}
    echo "</form></table>";


    echo "<table class='button' width=100%><tr>";
    echo "<td>".__('Vacations days');
    echo "<td style='background-color: #FFFF80;'>";
    echo get_user_vacations ($id_user_show, $year). "</td>";
    
    echo "<td>";
    echo __('Days worked (projects)');
    echo "<td style='background-color: #98FF8B;'>";
    echo get_user_worked_days ($id_user_show, $year). "</td>";

    echo "<td>";
    echo __('Days worked (incidents)');
    echo "<td style='background-color: #FF7BFE;'>";
    echo get_user_incident_worked_days ($id_user_show, $year). "</td>";


    echo "<td>";
    echo __('Other');
	echo "<td style='background-color: #FFE053;'>";
    echo get_user_other ($id_user_show, $year);

    echo "</table>";
    
    echo "<table>";
    echo "<tr>";
    for ($ax = 1; $ax < 13; $ax++){
        if (fmod($ax-1,3) == 0)
            echo "<tr>";
        echo "<td valign=top>";
        
        $this_month = date('Y-m-d H:i:s',strtotime("$year-$ax-01"));
		$this_month_limit = date('Y-m-d H:i:s',strtotime("$year-$ax-31"));
	
		$work_hours = get_db_sql ("SELECT SUM(duration) FROM tworkunit WHERE id_user='$id_user_show' AND locked = '' AND timestamp >= '$this_month' AND timestamp < '$this_month_limit'");
	
		if ($work_hours == "")
			$work_hours = 0;	
        
        $locked_hours = get_db_sql ("SELECT SUM(duration) FROM tworkunit WHERE id_user='$id_user_show' AND locked != '' AND timestamp >= '$this_month' AND timestamp < '$this_month_limit'");
        
		if ($locked_hours == "")
			$locked_hours = 0;	

			
		echo __("Total") . " : " . $work_hours;
		echo " - ";
		echo __("Locked"). " : " . $locked_hours;        
 
        echo generate_small_work_calendar ($year, $ax, $days_f, 3, 0, "en", $id_user_show);	
    }
    echo "</table>";

?>

<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.autocomplete.js"></script>
<script type="text/javascript" src="include/js/integria_date.js"></script>

<script type="text/javascript">

// Datepicker
add_datepicker ("#text-start_date", null);

$(document).ready (function () {
	$("#textarea-description").TextAreaResizer ();
	
	var idUser = "<?php echo $config['id_user'] ?>";
	
	bindAutocomplete ("#text-id_user", idUser);	
	
});
</script>

