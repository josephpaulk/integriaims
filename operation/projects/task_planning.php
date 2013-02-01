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
check_login ();

include_once ("include/functions_tasks.php");
include_once ("include/functions_graph.php");

$id_project = (int) get_parameter ('id_project');

if (! $id_project) {// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to task manager without project");
	include ("general/noaccess.php");
	exit;
}

if (! user_belong_project ($config['id_user'], $id_project)) {
	audit_db($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation","Trying to access to task manager of unauthorized project");
	include ("general/noaccess.php");
	exit;
}

$project = get_db_row ('tproject', 'id', $id_project);
$project_manager = get_db_value ('id_owner', 'tproject', 'id', $id_project);
$update = get_parameter("update");
$create = get_parameter("create");
$delete = get_parameter("delete");

//Delete task
if($delete) {
	//Check if admin or project manager before delete the task
	if (dame_admin ($config['id_user']) || project_manager_check ($id_project)) {
		delete_task ($delete);
		echo '<h3 class="suc">'.__('Successfully deleted').'</h3>';
		project_tracking ($id_project, PROJECT_TASK_DELETED);
	} else {
		no_permission ();
	}	
}

//Update tasks
if ($update) {
	//Get all task from DB to know the ids
	$sql = sprintf("SELECT id FROM ttask WHERE id_project = %d", $id_project);
	$task = get_db_all_rows_sql ($sql);
	$succ = 0;
	foreach ($task as $t) {
		
		//Get all post parameters for this task
		$id = $t['id'];
		
		$name = get_parameter("name_$id");
		$owner = get_parameter ("owner_$id");
		$start = get_parameter ("start_$id");
		$end = get_parameter ("end_$id");
		$completion = get_parameter("status_$id");
					
		//hour fields hidden
		$hours = ((strtotime ($end) - strtotime ($start)) / (3600*24)) * $config['hours_perday'];
		$hours = $hours + $config['hours_perday'];
		
		//Check if the date was set properly
		//If there is a problem nothing will be updated
		if (strtotime ($start) > strtotime ($end)) {
			//Check date properly set
			echo '<h3 class="error">'.sprintf(__('Begin date cannot be before end date in task %s'), $name).'</h3>';
			continue;
		}
		
		//Check name not empty
		//If there is a problem nothing will be updated
		if ($name == '') {
			echo '<h3 class="error">'.__('Task name cannot be empty').'</h3>';
			continue;
		}
		
				
		//Update task information
		$sql = sprintf ('UPDATE ttask SET name = "%s", completion = %d,
			start = "%s", end = "%s", hours = %d WHERE id = %d',
			$name, $completion, $start, $end, $hours, $id);
		$result = process_sql ($sql);
		
		//Check owners of this tasks and update them
		// if the task as more than one user don't update the users
		// if the task as only one user then update it!
		$sql = sprintf("SELECT COUNT(*) as num_users FROM trole_people_task where id_task = %d", $id);
		
		$result1 = process_sql($sql);		
		
		$result2 = true;//To avoid strange messages with many task users

		if ($result1[0][0] == 1) {		
			$sql = sprintf("SELECT id_role FROM trole_people_project 
					WHERE id_project = %d AND id_user = '%s'", $id_project, $config['id_user']);
						
			$id_role = process_sql($sql);
			$id_role = $id_role[0]['id_role'];
		
			$sql = sprintf('UPDATE trole_people_task SET id_user = "%s", id_role = %d WHERE id_task = %d', 
							$owner, $id_role, $id);

			$result2 = process_sql($sql);
		}
				
		if (($result !== false) && ($result1 !== false) && ($result2 !== false)) {
			$succ++;
			audit_db ($config['id_user'], $config["REMOTE_ADDR"], "Task updated", "Task '$name' updated to project '$id_project'");
			task_tracking ($id, TASK_UPDATED);
		} else {
			echo "<h3 class='error'>".__('Could not be updated')."</h3>";
		}

	}
	echo '<h3 class="suc">'.sprintf(__('%d tasks successfully updated'), $succ).'</h3>';
}

//Create a new task
if ($create) {

	$tasklist = get_parameter ("tasklist");

	// Massive creation of tasks
	if ($tasklist != ""){

		$tasklist = safe_output ($tasklist);

		$parent = (int) get_parameter ('padre');
		$start = get_parameter ('start_date2', date ("Y-m-d"));
                $end = get_parameter ('end_date2', date ("Y-m-d"));
                $owner = get_parameter('dueno');

                $id_group = (int) get_parameter ('group2', 1);

		$data_array = preg_split ("/\n/", $tasklist);
	        foreach ($data_array as $data_item){
	                $data = trim($data_item);
			if ($data != ""){
				$sql = sprintf ('INSERT INTO ttask (id_project, name, id_group, id_parent_task, start, end) 
                                VALUES (%d, "%s", %d, %d, "%s", "%s")',
                                $id_project, $data, $id_group, $parent, $start, $end);

				$id_task = process_sql ($sql, 'insert_id');
				
	                        if ($id_task) {

        	                        $sql = sprintf("SELECT id_role FROM trole_people_project
                                                        WHERE id_project = %d AND id_user = '%s'", $id_project, $owner);

                	                $id_role = process_sql($sql);
                        	        $role = $id_role[0]['id_role'];
	
        	                        $sql = sprintf('INSERT INTO trole_people_task (id_user, id_role, id_task)
                	                                        VALUES ("%s", %d, %d)', $owner, $role, $id_task);

        	                        $result2 = process_sql($sql);
                	        }

			}
		}

	// Individual creation of a task
	
	} 
}

$project_name =  get_db_value ("name", "tproject", "id", $id_project);

echo "<h2>".__("Task planning")." &raquo; $project_name</h2>";

//Calculate task summary stats!

//Draw task status statistics by hand!
$sql = sprintf("SELECT id, completion FROM ttask WHERE id_project = %d", $id_project);

$res = process_sql($sql);

$verified = 0;
$completed = 0;
$in_process = 0;
$pending = 0;

foreach ($res as $r) {
	if ($r['completion'] < 40) {
		$pending++;
	} else if ($r['completion'] < 90) {
		$in_process++;
	} else if ($r['completion'] < 100) {
		$completed++;
	} else if ($r['completion'] == 100) {
		$verified++;
	}
}

echo "<center>";
echo "<table>";
echo "<tr>";
echo "<td rowspan=2 style='padding-left:20px;padding-right:20px;'>";
	echo "<table>";
	echo "<tr>";
	echo "<strong>";
	echo "<td>";
	echo __("Verified").":";
	echo "</td>";
	echo "<td>";
	echo "<span style='background-color:#B6D7A8;border: 2px solid grey;'>";
	echo "&nbsp;".$verified."&nbsp;";
	echo "</span>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td>";
	echo __("Completed").":";
	echo "</td>";
	echo "<td>";
	echo "<span style='background-color:#A4BCFA;border: 2px solid grey;'>";
	echo "&nbsp;".$completed."&nbsp;";
	echo "</span>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";	
	echo "<td>";
	echo __("In process").":";
	echo "</td>";
	echo "<td>";
	echo "<span style='background-color:#FFE599;border: 2px solid grey;'>";
	echo "&nbsp;".$in_process."&nbsp;";
	echo "</span>";
	echo "</td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td>";
	echo __("Pending").":";
	echo "</td>";
	echo "<td>";
	echo "<span style='background-color:#FFFFFF;border: 2px solid grey;'>";
	echo "&nbsp;".$pending."&nbsp;";
	echo "</span>";
	echo "</td>";
	echo "</strong>";
	echo "</tr>";
	echo "</table>";
echo "</td>";
echo "<td align=center><strong>".__("Hours worked")."</strong></td>";
echo "<td align=center><strong>".__("Summary task status")."</strong></td>";
echo "<td align=center><strong>".__("Task per user")."</strong></td>";
echo "</tr>";
echo "<tr>";
echo "<td align=center style='padding-left:10px;padding-right:10px;'>";
echo graph_workunit_project_user_single(200, 150, $id_project);
echo"</td>";
echo "<td align=center style='padding-left:10px;padding-right:10px;'>";
echo graph_workunit_project_task_status(200, 150, $id_project);
echo"</td>";
echo "<td align=center style='padding-left:10px;padding-right:10px;'>";
echo graph_project_task_per_user(200, 150, $id_project);
echo"</td>";
echo "</tr>";
echo "</table>";
echo "</center>";

//Starting main form for this view
echo "<form method='post' action='index.php?sec=projects&sec2=operation/projects/task_planning&id_project=".$id_project."'>";

//Create button bar
echo "<div style='width:100%;text-align:left;border-spacing:0px;' class='button'>";

echo "<table style='margin:0px; padding:0px;'>";
echo "<tr>";


//Create new task only if PM && TM flags or PW and project manager.
if (give_acl ($config["id_user"], 0, "TM") || give_acl ($config["id_user"], 0, "PM") || (give_acl ($config["id_user"], 0, "PW") && $config["id_user"] == $project_manager)) {
        echo "<td>";
        print_button (__('Add tasks'), 'addmass', false, '', 'class="sub next"');
        echo "</td>";
}

echo "<td>";
print_submit_button (__('Update'), 'update', false, 'class="sub upd"');
echo "</td>";
echo "</tr>";
echo "</table>";

echo "</div>";

//Get project users
$sql = sprintf("SELECT DISTINCT(id_user) FROM trole_people_project WHERE id_project = %d", $id_project);
$users_db = get_db_all_rows_sql ($sql);

foreach ($users_db as $u) {
	$users[$u['id_user']] = $u['id_user'];
}


//Hidden div for task creation. Only for PM flag
//Create new task only if PM && TM flags or PW and project manager.
if (give_acl ($config["id_user"], 0, "TM") || give_acl ($config["id_user"], 0, "PM") || (give_acl ($config["id_user"], 0, "PW") && $config["id_user"] == $project_manager)) {
        echo "<div id='createTaskmass' style='display:none;padding:5px;'>";
	echo "<table><tr><td colspan=4>";
        echo "<strong>".__('Put taskname in each line')."</strong><br>";
	print_textarea ('tasklist', 5, 40);

	echo "<tr>";
	//Group selecting combo
        echo "<td>";
        combo_groups_visible_for_me ($config['id_user'], 'group2', 0, 'TW');
        echo "</td>";

        //Task parent combo
        echo "<td style='width:60'>";
        $sql = sprintf ('SELECT id, name FROM ttask WHERE id_project = %d ORDER BY name', $id_project);
        print_select_from_sql ($sql, 'padre', 0, "\"style='width:250px;'\"", __('None'), 0, false, false, false, __('Parent'));
        echo "</td>";

	echo "<tr>";
	//Start date
        echo "<td>";
        $start = date ("Y-m-d");
        print_input_text_extended ("start_date2", $start, "start_date", '', 7, 15, 0, '', "", false, false, __('Start date'));
        echo "</td>";

        //End date)
        echo "<td>";
        $end = date ("Y-m-d");
        print_input_text_extended ("end_date2", $end, "end_date", '', 7, 15, 0, '', "", false, false, __('End date'));
        echo "</td>";

	echo "<tr>";
	// User assigned by default
        echo "<td>"; 
        print_select ($users, "dueno", $config['id_user'], '', '', 0, false, 0, false, __("Owner"));
        echo "</td>";


	echo "<tr><td colspan=4 align=right>";	
        echo "<br>";
	//Create button
        print_submit_button (__('Create'), 'create', false, 'class="sub create"');

	echo "</table>";
	echo "</div>";
}

//Hidden div for task creation. Only for PM flag
//Create new task only if PM && TM flags or PW and project manager.
if (give_acl ($config["id_user"], 0, "TM") || give_acl ($config["id_user"], 0, "PM") || (give_acl ($config["id_user"], 0, "PW") && $config["id_user"] == $project_manager)) {
	echo "<div id='createTask' style='display:none;padding:5px;'>";
	echo "<strong>".__('Create new task')."</strong><br>";

	echo "<table>";
	echo "<tr>";

	//Tex name field
	echo "<td>"; 
	$name = '';
	print_input_text ('name', $name, '', 50, 240, false, __('Name'));
	echo "</td>";
	
	echo "<td>";
	print_select ($users, "owner", $config['id_user'], '', '', 0, false, 0, false, __("Owner"));
	echo "</td>";

	//Group selecting combo
	echo "<td>";
	combo_groups_visible_for_me ($config['id_user'], 'group', 0, 'TW');
	echo "</td>";

	echo "<tr>";
	//Task parent combo
	echo "<td style='width:60'>";
	$sql = sprintf ('SELECT id, name FROM ttask WHERE id_project = %d ORDER BY name', $id_project);
	print_select_from_sql ($sql, 'parent', 0, "\"style='width:250px;'\"", __('None'), 0, false, false, false, __('Parent'));
	echo "</td>";

	//Start date
	echo "<td>";
	$start = date ("Y-m-d");
	print_input_text_extended ("start_date", $start, "start_date", '', 7, 15, 0, '', "", false, false, __('Start date'));
	echo "</td>";


	//End date)
	echo "<td>";
	$end = date ("Y-m-d");
	print_input_text_extended ("end_date", $end, "end_date", '', 7, 15, 0, '', "", false, false, __('End date'));
	echo "</td>";
	
	//Create button
	echo "<td valign=bottom>";
	print_submit_button (__('Create'), 'create', false, 'class="sub create"');
	echo "</td>";

	echo "</tr>";
	echo "</table>";
	echo "</div>";
}

//Create table and table header.
echo "<table class=listing width=100% cellspacing=0 cellpadding=0 border=0px>";
echo "<thead>";
echo "<tr>";
echo "<th class=header style='text-align:center;'>".__('Task')."</th>";
echo "<th class=header style='text-align:center;'>".__('Owner')."</th>";
echo "<th class=header style='text-align:center;'>".__('Start date')."</th>";
echo "<th class=header style='text-align:center;'>".__('End date')."</th>";
echo "<th class=header style='text-align:center;'>".__('Hours worked')."</th>";
echo "<th class=header style='text-align:center;'>".__('Delay (days)')."</th>";
echo "<th class=header style='text-align:center;'>".__('Status')."</th>";

// Last column (Del) Only for PM flag
if (give_acl ($config['id_user'], 0, 'PM')) {
	echo "<th class=header style='text-align:center;'>".__('Op.')."</th>";
}

echo "</tr>";
echo "</thead>";

//Print table content

echo "<tbody>";
show_task_tree ($table, $id_project, 0, 0, $users);
echo "</tbody>";
echo "</table>";

echo "</form>";

function show_task_row ($table, $id_project, $task, $level, $users) {
	global $config;
	
	$id_task = $task['id'];
	
	// Second column (Task  name)
	$prefix = '';
	for ($i = 0; $i < $level; $i++)
		$prefix .= '<img src="images/small_arrow_right_green.gif" style="position: relative; top: 5px;"> ';
	
	echo "<td>";
	
	echo $prefix.print_input_text ("name_".$id_task, $task['name'], "", 60, 0, true);
	
	echo"</td>";
	
	// Thrid column (Owner)Completion
	echo "<td style='text-align:center;'>";

	$owners = get_db_value ('COUNT(DISTINCT(id_user))', 'trole_people_task', 'id_task', $task['id']);
	
	if ($owners > 1) {
		echo combo_users_task ($task['id'], 1, true);
		echo ' ';
		echo $owners;
	} else {
		$owner_id = get_db_value ('id_user', 'trole_people_task', 'id_task', $task['id']);
		print_select ($users, "owner_".$id_task, $owner_id, '', '', 0, false, 0, true, false, false, 'width: 90px');	
	}
	
	echo "</td>";
	
	// Fourth column (Start date)
	echo "<td style='text-align:center;'>";
	print_input_text_extended ("start_".$id_task, $task['start'], "start_".$id_task, '', 7, 15, 0, '', 'style="font-size:9px;"');
	
	echo "</td>";

	// Fifth column (End date)
	echo "<td style='text-align:center;'>";
	print_input_text_extended ("end_".$id_task, $task['end'], "end_".$id_task, '', 7, 15, 0, '', 'style="font-size:9px;"');
	echo "</td>";
	
	//Worked time based on workunits
	$worked_time = get_task_workunit_hours ($id_task);
	echo "<td style='text-align:left;'>".$worked_time."</td>";
	
	// Sixth column (Delay)
	//If task was completed delay is 0
	if ($task['completion']) {
		$delay = 0;
	} else {
		//If was not completed check for time delay from end to now
		$end = strtotime ($task['end']);
		$now = time ();
		
		$a_day_in_sec = 3600*24;
		
		if ($now > $end) {
			$diff = $now - $end;
			$delay = $diff / $a_day_in_sec;
			$delay = round ($delay, 1);
		} else {
			$delay = 0;
		}
	}
	
	echo "<td style='text-align:left;'>".$delay."</td>";

	// Seventh column (Delay)
	
	//Task status
	/*
	 * 0%-40% = Pending
	 * 41%-90% = In process
	 * 91%-100% = Completed
	 * 100% = Verified
	 * 
	 */
	 
	//Check selected status 
	$selected = 0;
	if ($task['completion'] < 40) {
		$selected = 0;
	} else if ($task['completion'] < 90) {
		$selected = 45;
	} else if ($task['completion'] < 100) {
		$selected = 95;
	} else if ($task['completion'] == 100) {
		$selected = 100;
	}
	
	$fields = array();
	$fields[0] = __("Pending");
	$fields[45] = __("In process");
	$fields[95] = __("Completed");
	$fields[100] = __("Verified");
	
	echo "<td>";	
	
	print_select  ($fields, "status_".$id_task, $selected, '', '', 0, false, 0, true, false, false, "width: 100px;");
	echo"</td>";

	// Last Edit and del column. (Del) Only for PM flag
	//Create new task only if PM && TM flags or PW and project manager.
	echo "<td style='text-align:center;'>";
	echo '<a href="index.php?sec=projects&sec2=operation/projects/task_detail&id_project='.$id_project.'&id_task='.$task['id'].'&operation=view">';
	echo '<img style="margin-right: 6px;" src="images/config.gif">';
	echo '</a>';
	
	if (give_acl ($config["id_user"], 0, "PM")) {
		
		echo '<a href="index.php?sec=projects&sec2=operation/projects/task_planning&id_project='.$id_project.'&delete='.$task["id"].'"
			onClick="if (!confirm(\''.__('Are you sure?').'\')) return false;"><img src="images/cross.png" /></a>';
		echo "</td>";
	}	
}

function show_task_tree (&$table, $id_project, $level, $id_parent_task, $users) {
	global $config;
	
	// Simple query, needs to implement group control and ACL checking
	$sql = sprintf ('SELECT * FROM ttask
		WHERE id_project = %d
		AND id_parent_task = %d
		ORDER BY name', $id_project, $id_parent_task);
	$tasks = get_db_all_rows_sql ($sql);
	
	if ($tasks === false)
		return;

	

	foreach ($tasks as $task) {
		//If user belong to task then create a new row in the table
		if (user_belong_task ($config['id_user'], $task['id'])) {
			//Each tr has the task id as the html id object!
			//Check completion for tr background color
			
			if ($task['completion'] < 40) {
				$color = "#FFFFFF";
			} else if ($task['completion'] < 90) {
				$color = "#FFE599";
			} else if ($task['completion'] < 100) {
				$color = "#A4BCFA";
			} else if ($task['completion'] == 100) {
				$color = "#B6D7A8";
			}
			
			echo "<tr id=".$task['id']." bgcolor='$color'>";
			show_task_row ($table, $id_project, $task, $level, $users);
			echo "</tr>";
		}
		show_task_tree ($table, $id_project, $level + 1, $task['id'], $users);
	}
}

?>

<script type="text/javascript" src="include/js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
<script type="text/javascript" src="include/js/integria_date.js"></script>

<script type="text/javascript">

$(document).ready (function () {
	//Configure calendar dates
	$('input[name^="start"]').datepicker({
		beforeShow: function () {
			maxdate = null;
			name = $(this).attr('name');
			id = name.substr(6);
		
			if ($('input[name^="end_'+id+'"]').datepicker ("getDate") > $(this).datepicker ("getDate"))
				maxdate = $('input[name^="end_'+id+'"]').datepicker ("getDate");
			return {
				maxDate: maxdate
			};
		},
		onSelect: function (datetext) {
			name = $(this).attr('name');
			id = name.substr(6);
			end = $('input[name~="end_'+id+'"]').datepicker ("getDate");
			start = $(this).datepicker ("getDate");
			if (end <= start) {
				pulsate ($('input[name~="end_'+id+'"]'));
			} else {
				console.log("paso1");
				hours_day = <?php echo $config['hours_perday'];?>;
				hours = Math.floor ((end - start) / 86400000 * hours_day);
				
				//Add one day more because the last equations deletes one day
				hours = hours+hours_day;
				
				console.log("hours => "+hours);
				//$("#text-hours").attr ("value", hours);
			}
		}
	});
	
	$('input[name^="end"]').datepicker ({
		
		beforeShow: function () {
			name = $(this).attr('name');
			id = name.substr(4);
			
			return {
				minDate: $('input[name~="start_'+id+'"]').datepicker ("getDate")
			};
		},
		onSelect: function() {
			console.log("paso2");
			name = $(this).attr('name');
			id = name.substr(4);
			end = $(this).datepicker ("getDate");
			start = $('input[name~="start_'+id+'"]').datepicker ("getDate");
			hours_day = <?php echo $config['hours_perday'];?>;
			hours = Math.floor ((end - start) / 86400000 * hours_day);
			
			//Add one day more because the last equations deletes one day
			hours = hours+hours_day;
			
			console.log("hours => "+hours);
			//$("#text-hours").attr ("value", hours);
		}
	});	
	
   	//Toggle create task menu
	$('#button-add').click(function() {
		$('#createTask').toggle();
	});

	//Toggle create mass task menu
        $('#button-addmass').click(function() {
                $('#createTaskmass').toggle();
        });
	

	//Change row color dinamically when status is changed
	$('select[name^="status"]').change(function() {
		name = $(this).attr('name');
		id = name.substr(7)
		color="#FFFFFF";
		completion = $(this).val();
		
		if (completion < 40) {
			color = "#FFFFFF";
		} else if (completion < 90) {
			color = "#FFE599";
		} else if (completion < 100) {
			color = "#A4BCFA";
		} else if (completion == 100) {
			color = "#B6D7A8";
		}
		
		$('#'+id).attr('bgcolor', color);
	});
});



</script>

