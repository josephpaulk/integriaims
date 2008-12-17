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

include "include/functions_graph.php";
global $config;

check_login ();

// Get our main stuff
$id_project = get_parameter ('id_project', -1);
$id_task = get_parameter ('id_task', -1);
$project_manager = get_db_value ('id_owner', 'tproject', 'id', $id_project);
$operation = (string) get_parameter ('operation');

$hours = 0;
$estimated_cost = 0;

// Get names
if ($id_project)
	$project_name = get_db_value ('name', 'tproject', 'id', $id_project);
else
	$project_name = '';

if ($id_task)
	$task_name = get_db_value ('name', 'ttask', 'id', $id_task);
else
	$task_name = '';

// Init variables
$name = "";
$description = "";
$end = date("Y-m-d");
$start = date("Y-m-d");
$completion = 0;
$priority = 1;
$id_group = 1;
$result_output = "";
$parent = 0;

// ACL Check for this task
// This user is assigned to this task ?

if ( $operation != "create" && ! user_belong_task ($config["id_user"], $id_task)){
	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to task manager without project");
	no_permission();
}

if ($operation == "") {
	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to task manager without project");
	no_permission();
}

// Create task
if ($operation == "insert") {
	$name = get_parameter ('name');
	$start = get_parameter ('start_date', date ("Y-m-d"));
	$end = get_parameter ('end_date', date ("Y-m-d"));
	
	if ($name == '') {
		$operation = 'create';
		$result_output = '<h3 class="error">'.__('Name cannot be empty').'</h3>';
	} elseif (strtotime ($start) > strtotime ($end)) {
		$operation = 'create';
		$result_output = '<h3 class="error">'.__('Begin date cannot be before end date').'</h3>';
	} else {
		$description = (string) get_parameter ('description');
		$priority = (int) get_parameter ('priority');
		$completion = (int) get_parameter ('completion');
		$parent = (int) get_parameter ('parent');
		$hours = (int) get_parameter ('hours');
		$periodicity = (string) get_parameter ('periodicity', 'none');
		$estimated_cost = (int) get_parameter ('estimated_cost');
		$id_group = (int) get_parameter ('group', 1);
	
		$sql = sprintf ('INSERT INTO ttask (id_project, name, description, priority,
			completion, start, end, id_parent_task, id_group, hours, estimated_cost,
			periodicity)
			VALUES (%d, "%s", "%s", %d, %d, "%s", "%s", %d, %d, %d, %f, "%s")',
			$id_project, $name, $description, $priority, $completion, $start, $end,
			$parent, $id_group, $hours, $estimated_cost, $periodicity);
		$id_task = process_sql ($sql, 'insert_id');
		if ($id_task !== false) {
			$result_output = "<h3 class='suc'>".__('Successfully created')."</h3>";
			audit_db ($config['id_user'], $config["REMOTE_ADDR"], "Task added to project", "Task '$name' added to project '$id_project'");
			$operation = "view";
	
			// Show link to continue working with Task
			$result_output .= "<p><h3>";
			$result_output .= "<a href='index.php?sec=projects&sec2=operation/projects/task_detail&id_project=$id_project&id_task=$id_task&operation=view'>";
			$result_output .= __("Continue working with task #").$id_task;
			$result_output .= "</a></h3></p>";
		// Add all users assigned to current project for new task or parent task if has parent
		if ($parent != 0)
			$query1="SELECT * FROM trole_people_task WHERE id_task = $parent";
		else
			$query1="SELECT * FROM trole_people_project WHERE id_project = $id_project";
		$resq1=mysql_query($query1);
		while ($row=mysql_fetch_array($resq1)){
			$id_role_tt = $row["id_role"];
			$id_user_tt = $row["id_user"];
			$sql = "INSERT INTO trole_people_task
			(id_task, id_user, id_role) VALUES
			($id_task, '$id_user_tt', $id_role_tt)";
			mysql_query($sql);
		}
		task_tracking ($id_task, TASK_CREATED);
		project_tracking ($id_project, PROJECT_TASK_ADDED);
	} else {
		$update_mode = 0;
		$create_mode = 1;
		$result_output = "<h3 class='error'>".__('Could not be created')."</h3>";
		}
	}
}

// -----------
// Update task
// -----------
if ($operation == "update") {
	if ($id_task == -1) {
		audit_db($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation","Trying to access to update invalid Task");
		include ("general/noaccess.php");
		exit;
	}
	$name = (string) get_parameter ('name');
	$description = (string) get_parameter ('description');
	$priority = (int) get_parameter ('priority');
	$completion = (int) get_parameter ('completion');
	$parent = (int) get_parameter ('parent');
	$hours = (int) get_parameter ('hours');
	$periodicity = (string) get_parameter ('periodicity', 'none');
	$estimated_cost = (int) get_parameter ('estimated_cost');
	$id_group = (int) get_parameter ('group', 1);
	$start = get_parameter ('start_date', date ("Y-m-d"));
	$end = get_parameter ('end_date', date ("Y-m-d"));

	$sql = sprintf ('UPDATE ttask SET name = "%s", description = "%s",
			priority = %d, completion = %d,
			start = "%s", end = "%s", hours = %d,
			periodicity = "%s", estimated_cost = "%f",
			id_parent_task = %d, id_group = %d
			WHERE id = %d',
			$name, $description, $priority, $completion, $start, $end,
			$hours, $periodicity, $estimated_cost, $parent, $id_group,
			$id_task);
	$result = process_sql ($sql);
	if ($result !== false) {
		$result_output = '<h3 class="suc">'.__('Successfully updated').'</h3>';
		audit_db ($config['id_user'], $config["REMOTE_ADDR"], "Task updated", "Task '$name' updated to project '$id_project'");
		$operation = "view";
		task_tracking ($id_task, TASK_UPDATED);
	} else {
		$result_output = "<h3 class='error'>".__('Could not be updated')."</h3>";
	}
}

// Edition / View mode
if ($operation == "view") {
	$task = get_db_row ('ttask', 'id', $id_task);
	
	// Get values
	$name = $task['name'];
	$description = $task['description'];
	$completion = $task['completion'];
	$priority = $task['priority'];
	$dep_type = $task['dep_type'];
	$start = $task['start'];
	$end = $task['end'];
	$estimated_cost = $task['estimated_cost'];
	$hours = $task['hours'];
	$parent = $task['id_parent_task'];
	$id_group = $task['id_group'];
	$periodicity = $task['periodicity'];
		
} 

echo $result_output;

// ********************************************************************************************************
// Show forms
// ********************************************************************************************************

echo '<h2>'.__('Task management').'</h2>';
if ($operation == "create") {
	$estimated_cost = 0;
	$priority = 0;
	$parent = 0;
	$hours = 0;
	$start = date ("Y-m-d");
	$end = date ("Y-m-d");
	$periodicity = "none";
} else {
	echo '<form method="post" action="index.php?sec=projects&sec2=operation/projects/task_detail">';
	print_input_hidden ('id_project', $id_project);
	print_input_hidden ('id_task', $id_task);
	print_input_hidden ('operation', 'update');
}

$table->width = '90%';
$table->class = 'databox';
$table->rowspan = array ();
$table->colspan = array ();
$table->colspan[0][0] = 2;
$table->colspan[8][0] = 3;
$table->style = array ();
$table->style[0] = 'vertical-align: top';
$table->style[1] = 'vertical-align: top';
$table->style[2] = 'vertical-align: top';
$table->data = array ();
$table->data[0][0] = print_input_text ('name', $name, '', 50, 240, true, __('Name'));

if ($id_task != -1) {
	$table->rowspan[0][2] = 5;

	$image = graph_workunit_task (200, 170, $id_task, 1);
	//$image = '<img src="include/functions_graph.php?type=workunit_task&width=200&height=170&id_task='.$id_task.'">';
	$table->data[0][2] = print_label (__('Workunit distribution'), '', '', true, $image);
}

$sql = sprintf ('SELECT id, name FROM ttask WHERE id_project = %d
	AND id != %d ORDER BY name', $id_project, $id_task, $parent);
$table->data[1][0] = print_select_from_sql ($sql, 'parent', $parent, '', __('None'), 0, true, false, false, __('Parent'));
$table->data[1][1] = print_select (get_priorities (), 'priority', $priority,
	'', '', '', true, false, false, __('Priority'));

$table->data[2][0] = combo_groups_visible_for_me ($config['id_user'], 'group', 0, 'TW', $id_group, true);

$table->data[3][0] = print_input_text ('start_date', $start, '', 15, 15, true, __('Start'));
$table->data[3][1] = print_input_text ('end_date', $end, '', 15, 15, true, __('End'));

$table->data[4][0] = print_select (get_periodicities (), 'periodicity',
	$periodicity, '', __('None'), 'none', true, false, false, __('Recurrence'));

$table->data[5][0] = print_input_text ('hours', $hours, '', 5, 5, true, __('Estimated hours'));

if ($id_task != -1) {
	$table->data[5][1] = print_label (__('Worked hours'), '', '', true, get_task_workunit_hours ($id_task).' '.__('Hours'));

	$incident_wu = get_incident_task_workunit_hours ($id_task);
	if ($incident_wu > 0)
		$table->data[5][1] .= " ( + $incident_wu )";
	

}

$table->data[6][0] = print_input_text ('estimated_cost', $estimated_cost, '', 7,
	11, true, __('Estimated cost'));
$table->data[6][0] .= ' '.$config['currency'];

if ($id_task != -1) {
	$table->data[6][1] = print_label (__('Imputable costs'), '', '', true,
		task_workunit_cost ($id_task, 1).' '.$config['currency']);
	$table->data[6][1] .= print_label (__('Total costs'), '', '', true,
		task_workunit_cost ($id_task, 0).' '.$config['currency']);
	
	$table->rowspan[5][2] = 5;
	
	// Abbreviation for "Estimated"
	$labela = __('Est.');
	$labelb = __('Real');
	$a = round ($hours);
	$b = round (get_task_workunit_hours ($id_task));
	$max = max($a, $b);
	$image = '<img src="include/functions_graph.php?type=histogram&width=200&height=30&a='.$a.'&b='.$b.'&labela='.$labela.'&labelb='.$labelb.'&max='.$max.'" />';
	$table->data[5][2] = print_label (__('Estimated hours'), '', '', true, $image);
	
	$labela = __('Total');
	$labelb = __('Imp');
	$a = round (task_workunit_cost ($id_task, 0));
	$b = round (task_workunit_cost ($id_task, 1));
	$max = max ($a, $b);
	$image = '<img src="include/functions_graph.php?type=histogram&width=200&height=30&a='.$a.'&b='.$b.'&labela='.$labela.'&labelb='.$labelb.'&max='.$max.'" />';
	$table->data[5][2] .= print_label (__('Imputable estimation'), '', '', true, $image);	
	
	$labela = __('Est.');
	$labelb = __('Real');
	$a = $estimated_cost;
	$b = round (task_workunit_cost ($id_task, 1));
	$max = max ($a, $b);
	$image = '<img src="include/functions_graph.php?type=histogram&width=200&height=30&a='.$a.'&b='.$b.'&labela='.$labela.'&labelb='.$labelb.'&max='.$max.'" />';
	$table->data[5][2] .= print_label (__('Cost estimation'), '', '', true, $image);
}

$table->colspan[7][0] = 3;
$table->data[7][0] = print_label (__('Completion'), '', '', true,
	'<div id="slider"><div class="ui-slider-handle"></div></div><span id="completion">'.$completion.'%</span>');
$table->data[7][0] .= print_input_hidden ('completion', $completion, true);
$table->data[8][0] = print_textarea ('description', 8, 30, $description, '',
	true, __('Description'));

if (give_acl ($config["id_user"], $id_group, "TM") || ($config["id_user"] == $project_manager)) {
	echo '<form method="post" action="index.php?sec=projects&sec2=operation/projects/task_detail">';
	
	print_table ($table);
	echo '<div class="button" style="width:'.$table->width.'">';
	if ($operation != "create") {
		print_submit_button (__('Update'), 'update_btn', false, 'class="sub upd"');
		print_input_hidden ('operation', 'update');
	} else {
		print_submit_button (__('Create'), 'create_btn', false, 'class="sub create"');
		print_input_hidden ('operation', 'insert');
	}
	print_input_hidden ('id_project', $id_project);
	echo '</div>';
	echo '</form>';
} else {
	print_table ($table);
}

?>
<script type="text/javascript" src="include/js/jquery.ui.slider.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
<script type="text/javascript" src="include/js/integria_date.js"></script>

<script type="text/javascript">

$(document).ready (function () {
	$("#textarea-description").TextAreaResizer ();
	configure_range_dates (function (datetext) {
		hours_day = <?php echo $config['hours_perday'];?>;
		start_date = $("#text-start_date").datepicker ("getDate"); 
		end_date = $(this).datepicker ("getDate");
		if (end_date < start_date) {
			pulsate (this);
		} else {
			hours = Math.floor ((end_date - start_date) / 86400000 * hours_day);
			$("#text-hours").attr ("value", hours);
		}
	});
	
	$("#slider").slider ({
		min: 0,
		max: 100,
		stepping: 5,
		slide: function (event, ui) {
			$("#completion").empty ().append (ui.value+"%");
		},
		change: function (event, ui) {
			$("#hidden-completion").attr ("value", ui.value);
		}
	});
<?php if ($completion)
	echo '$("#slider").slider ("moveTo", '.$completion.');';
?>
});
</script>
<script language="JavaScript" src="include/FusionCharts/FusionCharts.js"></script>
