<?php
// INTEGRIA IMS - the ITIL Management System
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

check_login ();

$id_user = $_SESSION["id_usuario"];
$operation = get_parameter ("operation");
$ahora = get_parameter ("givendate", date("Y-m-d H:i:s"));
$public =  get_parameter ("public", 1);
$id_profile = get_parameter ("work_profile", "");
$description =  get_parameter ("wu_description", "");
$pass_id_project = get_parameter("id_project", "");
$pass_id_task = get_parameter("id_task", "");

// -----------
// Workunit
// -----------
if ($operation == "addworkunit"){
	$duration = get_parameter ("duration",0);
	if (!is_numeric( $duration))
		$duration = 0;
	$timestamp = get_parameter ("start_date");
	$description = get_parameter ("wu_description", "");
	$have_cost = get_parameter ("have_cost",0);
	$task = get_parameter ("task",-1);
	$role = get_parameter ("role",0);
	$split = get_parameter ("split",0);
	$wu_user = get_parameter ("wu_user", $id_user);
	
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
					VALUES	('$current_timestamp', $hours_day, '$wu_user', '$description',
							 $have_cost, $role, $public)";
			if (mysql_query($sql)){
				$id_workunit = mysql_insert_id();
				$sql2 = "INSERT INTO tworkunit_task 
								(id_task, id_workunit) VALUES ($task, $id_workunit)";
				if (mysql_query($sql2))
					$result_output = "<h3 class='suc'>".__('Workunit added')."</h3>";
				else
					$result_output = "<h3 class='error'>".__('Problemd adding workunit.')."</h3>";
			}
		}
		mail_project (0, $id_user, $id_workunit, $task, "This is part of a multi-workunit assigment of $duration hours");
	
	// Single day workunit
	} else {
		$sql = "INSERT INTO tworkunit 
				(timestamp, duration, id_user, description, have_cost, id_profile, public) 
				 VALUES	('$timestamp', $duration, '$wu_user', '$description', $have_cost, $role, $public)";
		if (mysql_query($sql)){
			$id_workunit = mysql_insert_id();
			$sql2 = "INSERT INTO tworkunit_task 
					(id_task, id_workunit) VALUES ($task, $id_workunit)";

			if (mysql_query($sql2)){
				$result_output = "<h3 class='suc'>".__('Workunit added')."</h3>";
				audit_db ($id_user, $config["REMOTE_ADDR"], "Spare work unit added", 
						"Workunit for $id_user added to Task ID #$task");
				mail_project (0, $id_user, $id_workunit, $task);
			}	
		} else 
			$result_output = "<h3 class='error'>".__('Problemd adding workunit.')."</h3>";
	}
	insert_event ("PWU INSERT", $task, 0, $description);
	echo $result_output;
			
}

// --------------------
// Workunit / Note  form
// --------------------
if ($operation != "create"){

	echo "<h3><img src='images/award_star_silver_1.png'> ";

	if ($pass_id_task != ""){
		$task_name = get_db_value ('name', 'ttask', 'id', $pass_id_task);
		echo __('Add workunit').' - '.$task_name.'</h3>';
	} else {
		echo __('Add spare workunit')."</h3>";
	}

	echo "<table width='700' class='databox'>";
	if ($pass_id_task != "")
		echo "<form name='nota' method='post' action='index.php?sec=projects&sec2=operation/users/user_spare_workunit&operation=addworkunit&id_project=$pass_id_project&id_task=$pass_id_task'>";
	else
		echo "<form name='nota' method='post' action='index.php?sec=users&sec2=operation/users/user_spare_workunit&operation=addworkunit'>";
	// Date
	echo "<td><b>".__('Date')."</b>";
	echo "<td>";
	$start_date = substr($ahora,0,10);
	print_input_text ('start_date', $start_date, '', 10, 20);	

	// Role
	echo "<td>";
	echo "<b>".__('Profile')."</b>";
	echo "<td>";
	if (dame_admin($id_user) == 1){
		echo combo_user_task_profile ($pass_id_task, 'work_profile', $id_profile, false, true);
	} else 
		combo_roles(1); // role
	
	// task id - included hard-written "VACATIONS"
	if ($pass_id_task != "")
		echo "<input type='hidden' name='task' value='".$pass_id_task."'>";
	else {
		echo "<tr><td>";
		echo "<b>".__('Task')."</b>";
		echo "<td colspan=3>";
		echo combo_task_user_participant ($id_user, true, 0, false, false);
	}

	// TIme wasted
	echo "<tr><td class='datos'>";
	echo "<b>".__('Time used')."</b>";
	echo "<td class='datos'>";
	echo "<input type='text' name='duration' value='0' size='7'>";
	

	if (dame_admin($id_user) == 1){
		echo "<td>";
		echo "<b>".__('Username')."</b>";
		echo "<td>";
		combo_user_visible_for_me ($config["id_user"], "wu_user", 0, "TW", false, false);
	}


	// have cost checkbox
	echo "<tr><td>";
	echo "<b>".__('Have cost')."</b>";
	echo "<td>";
	echo "<input type='checkbox' name='have_cost' value=1>";


	
	echo "<td>";
	echo "<b>".__('Public');
	echo "<td>";
	print_checkbox ("public", 1, $public, false, false);
	
	
	echo "<tr><td>";
	echo "<b>".__('Forward')."</b>";
	echo "<a href='#' class='tip'>&nbsp;<span>";
	echo __('If this checkbox is activated, propagation will be forward instead backward');
	echo "</span></a>";
	echo "<td>";
	echo "<input type=checkbox name='forward' value=1>";
	


	echo "<td>";
	echo "<b>".__('Split > 1day')."</b>";
	echo "&nbsp;<a href='#' class='tip'>&nbsp;<span>";
	echo __('If workunit added is superior to 8 hours, it will be propagated to previous workday and deduced from the total, until deplete total hours assigned');
	echo "</span></a>";
	echo "<td>";
	echo "<input type=checkbox name='split' value=1>&nbsp;";

	echo "<input type='hidden' name='timestamp' value='".$ahora."'>";
	echo '<tr><td colspan="4">';
	echo print_textarea ('wu_description', 10, 30, "$description", '', true, false);
	echo "</table>";

	echo "<div style='width: 700px' class='button'>";
	echo '<input name="addnote" type="submit" class="sub next" value="'.__('Add').'">';
	echo "</form></div>";
	
}

?>

<script type="text/javascript" src="include/js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>

<script type="text/javascript">

$(document).ready (function () {
	$("#text-start_date").datepicker ();
	$("#textarea-description").TextAreaResizer ();
});
</script>
