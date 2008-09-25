<?php
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008 Ártica Soluciones Tecnológicas
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

if (check_login() != 0) {
    audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access", "Trying to access event viewer");
    require ("general/noaccess.php");
    exit;
}

$id_user = $_SESSION["id_usuario"];
$operation = give_parameter_get ("operation");
$ahora = give_parameter_get ("givendate", date("Y-m-d H:i:s"));
$public =  get_parameter ("public", 1);

// -----------
// Workunit
// -----------
if ($operation == "addworkunit"){
	$duration = give_parameter_post ("duration",0);
	if (!is_numeric( $duration))
		$duration = 0;
	$timestamp = give_parameter_post ("workunit_date");
	$description = give_parameter_post ("description");
	$have_cost = give_parameter_post ("have_cost",0);
	$task = give_parameter_post ("task",-1);
	$role = give_parameter_post ("role",0);
    $split = get_parameter ("split",0);
	
	// Multi-day assigment
	if (($split == 1) AND ($duration > $config["hours_perday"])){
	    $forward = get_parameter ("forward",0);
	    $total_days = ceil($duration / $config["hours_perday"]);
	    $total_days_sum = 0; $hours_day = 0;
	    for ($ax=0;$ax < $total_days; $ax++){
	        if ($forward == 0)
        	    $current_timestamp = calcdate_business_prev ($timestamp, $ax);
	        else
                $current_timestamp = calcdate_business ($timestamp, $ax);
            if (($total_days_sum + 8) > $duration)
                $hours_day = $duration - $total_days_sum;
            else 
                $hours_day = $config["hours_perday"];
            $total_days_sum += $hours_day;

        	$sql = "INSERT INTO tworkunit 
        	        (timestamp, duration, id_user, description, have_cost, id_profile, public) 
	                VALUES	('$current_timestamp', $hours_day, '$id_user', '$description',
	                         $have_cost, $role, $public)";
    	    if (mysql_query($sql)){
        	    $id_workunit = mysql_insert_id();
        		$sql2 = "INSERT INTO tworkunit_task 
        		                (id_task, id_workunit) VALUES ($task,   $id_workunit)";
        	    if (mysql_query($sql2))
        	        $result_output = "<h3 class='suc'>".$lang_label["workunit_ok"]."</h3>";
	            else
	                $result_output = "<h3 class='error'>".$lang_label["workunit_no"]."</h3>";
            }
        }
        mail_project (0, $id_user, $id_workunit, $task, "This is part of a multi-workunit assigment of $duration hours");
    
    // Single day workunit
	} else {
    	$sql = "INSERT INTO tworkunit 
    	        (timestamp, duration, id_user, description, have_cost, id_profile, public) 
	             VALUES	('$timestamp', $duration, '$id_user', '$description', $have_cost, $role, $public)";

    	if (mysql_query($sql)){
    		$id_workunit = mysql_insert_id();
    		$sql2 = "INSERT INTO tworkunit_task 
    		        (id_task, id_workunit) VALUES ($task,   $id_workunit)";
    		if (mysql_query($sql2)){
    			$result_output = "<h3 class='suc'>".$lang_label["workunit_ok"]."</h3>";
			    audit_db ($id_user, $config["REMOTE_ADDR"], "Spare work unit added", 
			            "Workunit for $id_user added to Task ID #$task");
                mail_project (0, $id_user, $id_workunit, $task);
	    	}    
    	} else 
    		$result_output = "<h3 class='error'>".$lang_label["workunit_no"]."</h3>";
	}
    insert_event ("PWU INSERT", $task, 0, $description);
	echo $result_output;
    		
}

// --------------------
// Workunit / Note  form
// --------------------
if ($operation != "create"){

	echo "<h3><img src='images/award_star_silver_1.png'> ";
	echo $lang_label["add_spare_workunit"]."</h3>";
	
	echo "<table width='700' class='databox'>";
	echo "<form name='nota' method='post' action='index.php?sec=users&sec2=operation/users/user_spare_workunit&operation=addworkunit'>";

	// Date
	echo "<td><b>".$lang_label["date"]."</b>";
	echo "<td>";
	echo "<input type='text' id='workunit_date' name='workunit_date' size=10 value='".substr($ahora,0,10)."'> <img src='images/calendar_view_day.png' onclick='scwShow(scwID(\"workunit_date\"),this);'> ";

	// Role
	echo "<td>";
	echo "<b>".$lang_label["profile"]."</b>";
	echo "<td>";
	combo_roles(1); // role
	
	// task id - included hard-written "VACATIONS"
	echo "<tr><td>";
	echo "<b>".$lang_label["task"]."</b>";
	echo "<td colspan=3>";
	echo combo_task_user_participant ($id_user, false, 0, false);
	


	// TIme wasted
	echo "<tr><td class='datos'>";
	echo "<b>".$lang_label["time_used"]."</b>";
	echo "<td class='datos'>";
	echo "<input type='text' name='duration' value='0' size='7'>";
    


	// have cost checkbox
	echo "<tr><td>";
	echo "<b>".$lang_label["have_cost"]."</b>";
	echo "<td>";
	echo "<input type='checkbox' name='have_cost' value=1>";


    
	echo "<td>";
	echo "<b>".__("Public");
	echo "<td>";
	print_checkbox ("public", 1, $public, false, false);
    
    
    echo "<tr><td>";
    echo "<b>".__("Forward")."</b>";
    echo "<a href='#' class='tip'>&nbsp;<span>";
    echo lang_string("If this checkbox is activated, propagation will be forward instead backward");
    echo "</span></a>";
	echo "<td>";
	echo "<input type=checkbox name='forward' value=1>";
    


    echo "<td>";
	echo "<b>".__("Split > 1day")."</b>";
    echo "&nbsp;<a href='#' class='tip'>&nbsp;<span>";
    echo lang_string("If workunit added is superior to 8 hours, it will be propagated to previous workday and deduced from the total, until deplete total hours assigned");
    echo "</span></a>";
	echo "<td>";
	echo "<input type=checkbox name='split' value=1>&nbsp;";




	echo "<input type='hidden' name='timestamp' value='".$ahora."'>";
	echo '<tr><td colspan="4" class="datos2"><textarea name="description" rows="15" cols="85">';
	echo '</textarea>';
	echo "</table>";

	echo "<div style='width: 700px' class='button'>";
	echo '<input name="addnote" type="submit" class="sub next" value="'.$lang_label["add"].'">';
	echo "</form></div>";
	
}

?>
