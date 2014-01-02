<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2011 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.


global $config;
check_login ();

include_once ("include/functions_projects.php");
include_once ("include/functions_graph.php");
include_once ("include/functions_tasks.php");

// Get our main stuff
$id_project = get_parameter ('id_project', -1);
$id_task = get_parameter ('id_task', -1);
$operation = (string) get_parameter ('operation');

$hours = 0;
$estimated_cost = 0;


// ACL Check for this task
$project_permission = get_project_access ($config["id_user"], $id_project);
$task_permission = get_project_access ($config["id_user"], $id_project, $id_task, false, true);

if ($operation == "") {
	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to task detail without operation");
	no_permission();
} elseif ($operation == "create" && !manage_any_task($config['id_user'], $id_project)) {
	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create a task without access");
	no_permission();
} elseif ($operation == "insert") {
	$id_parent = (int) get_parameter ('parent');
	if ($id_parent == 0) {
		if (!$project_permission['manage']) {
			// Doesn't have access to this page
			audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to insert a task without access");
			no_permission();
		}
	}
	$task_permission = get_project_access ($config["id_user"], $id_project, $id_parent, false, true);
	if (!$task_permission['manage']) {
		// Doesn't have access to this page
		audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to insert a task without access");
		no_permission();
	}
} elseif ($operation == "update" && !$task_permission['manage']) {
	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to update a task without access");
	no_permission();
} elseif ($operation == "view" && !$task_permission['read']) {
	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to view a task without access");
	no_permission();
}  elseif ($operation == "update" && $id_task == -1) {
	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to update a task without task id");
	no_permission();
}

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
$result_output = "";
$parent = 0;
$count_hours = 1;


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
		$count_hours = (int) get_parameter("count_hours");
	
		$sql = sprintf ('INSERT INTO ttask (id_project, name, description, priority,
			completion, start, end, id_parent_task, hours, estimated_cost,
			periodicity, count_hours)
			VALUES (%d, "%s", "%s", %d, %d, "%s", "%s", %d, %d, %f, "%s", %d)',
			$id_project, $name, $description, $priority, $completion, $start, $end,
			$parent, $hours, $estimated_cost, $periodicity, $count_hours);
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
	// Get current completion
	$current_completion = get_db_value('completion', 'ttask', 'id', $id_task);
	
	$name = (string) get_parameter ('name');
	$description = (string) get_parameter ('description');
	$priority = (int) get_parameter ('priority');
	$completion = (int) get_parameter ('completion');
	$parent = (int) get_parameter ('parent');
	$hours = (int) get_parameter ('hours');
	$periodicity = (string) get_parameter ('periodicity', 'none');
	$estimated_cost = (int) get_parameter ('estimated_cost');
	$start = get_parameter ('start_date', date ("Y-m-d"));
	$end = get_parameter ('end_date', date ("Y-m-d"));
	$count_hours = get_parameter("count_hours");
	
	$sql = sprintf ('UPDATE ttask SET name = "%s", description = "%s",
			priority = %d, completion = %d,
			start = "%s", end = "%s", hours = %d,
			periodicity = "%s", estimated_cost = "%f",
			id_parent_task = %d, count_hours = %d
			WHERE id = %d',
			$name, $description, $priority, $completion, $start, $end,
			$hours, $periodicity, $estimated_cost, $parent, $count_hours,
			$id_task);
	
	if ($id_task != $parent) {
		$result = process_sql ($sql);
	} else {
		$result = false;
	}

	if ($result !== false) {
		$result_output = '<h3 class="suc">'.__('Successfully updated').'</h3>';
		audit_db ($config['id_user'], $config["REMOTE_ADDR"], "Task updated", "Task '$name' updated to project '$id_project'");
		$operation = "view";
		task_tracking ($id_task, TASK_UPDATED);
		
		
		// ONLY recalculate the complete if count hours flag is activated
		if($count_hours) {
			set_task_completion ($id_task);
		}
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
	$periodicity = $task['periodicity'];
	$count_hours = $task['count_hours'];
		
} 

echo $result_output;

// ********************************************************************************************************
// Show forms
// ********************************************************************************************************

echo '<h1>'.__('Task management').'</h1>';

if ($id_task > 0) {
    // Task activity graph
	$task_activity = task_activity_graph ($id_task, 600, 150, true, true);
	if ($task_activity) {
		$table->width = '100%';
		$table->class = 'none';
		$table->style[0] = 'padding-left: 5px';
		$table->colspan = array();
		$table->data = array ();
		$task_activity = '<div class="graph_frame">' . $task_activity . '</div>';
		$table->data[0][0] = print_container('task_activity', __('Task activity'), $task_activity, 'closed');
		print_table ($table);
	}
}

if ($operation == "create") {
	$estimated_cost = 0;
	$priority = 0;
	$parent = 0;
	$hours = 8;
	$start = date ("Y-m-d");
	$end = date ("Y-m-d");
	$periodicity = "none";
}

$table->width = '100%';
$table->class = 'search-table-button';
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
	$table->rowspan[0][2] = 4;

	$image = graph_workunit_task (200, 170, $id_task);

	// Small hack to have a better graph here
	$image = "<div style='border: 1px solid #cfcfcf; background: #ffffff'>" . $image . "</div>";
	$table->data[0][2] = print_label (__('Workunit distribution'), '', '', true, $image);
}

if ($project_permission['manage'] || $operation == "view") {
	$combo_none = __('None');
} else {
	$combo_none = false;
}
$table->data[1][0] = combo_task_user_manager ($config['id_user'], $parent, true, __('Parent'), 'parent', $combo_none, false, $id_project, $id_task);
$table->data[1][1] = print_select (get_priorities (), 'priority', $priority,
	'', '', '', true, false, false, __('Priority'));

$table->data[3][0] = print_input_text ('start_date', $start, '', 15, 15, true, __('Start'));
$table->data[3][1] = print_input_text ('end_date', $end, '', 15, 15, true, __('End'));

$table->data[4][0] = print_select (get_periodicities (), 'periodicity',
	$periodicity, '', __('None'), 'none', true, false, false, __('Recurrence'));
	
$table->data[4][1] = print_checkbox_extended ('count_hours', 1, $count_hours,
	        false, '', '', true, __('Completion based on hours'))
	        .print_help_tip (__("Calculated task completion using workunits inserted by project members, if not it uses Completion field of this form"), true);

$table->data[5][0] = print_input_text ('hours', $hours, '', 5, 5, true, __('Estimated hours'));

if ($id_task != -1) {
	$worked_time =  get_task_workunit_hours ($id_task);
	$table->data[5][1] = print_label (__('Worked hours'), '', '', true, $worked_time.' '.__('Hrs'));

	$subtasks = task_duration_recursive ($id_task);
	if ($subtasks > 0)
		$table->data[5][1] .= "<span title='Subtasks WU/Hr'> ($subtasks)</span>";

	$incident_wu = get_incident_task_workunit_hours ($id_task);
	if ($incident_wu > 0)
		$table->data[5][1] .= "<span title='Incident'>($incident_wu)</span>";
}

$table->data[6][0] = print_input_text ('estimated_cost', $estimated_cost, '', 7,
	11, true, __('Estimated cost'));
$table->data[6][0] .= ' '.$config['currency'];

$external_cost = 0;
$external_cost = task_cost_invoices ($id_task);

$table->data[6][0] .= print_label (__("External costs"), '', '', true);
$table->data[6][0] .= $external_cost . " " . $config["currency"];

if ($id_task != -1) {
	$table->data[6][1] = print_label (__('Imputable costs'), '', '', true,
		task_workunit_cost ($id_task, 1).' '.$config['currency']);
		
		
	$incident_cost = get_incident_task_workunit_cost ($id_task);
	if ($incident_cost > 0)
		$incident_cost_label = "<span title='".__("Incident costs")."'> ($incident_cost) </span>";
	else
		$incident_cost_label = "";
		
	$total_cost = $external_cost + task_workunit_cost ($id_task, 0) + $incident_cost;
	
	if ($total_cost != 0) {
		$roles_task = get_db_all_rows_sql("SELECT distinct(id_role), name FROM trole_people_task, trole 
			WHERE id_task = $id_task AND id_role = trole.id");
		if ($roles_task != 0) {
			$output_total = '';
			foreach ($roles_task as $role) {
				$total_role = projects_get_cost_task_by_profile ($id_task, $role['id_role']);
				if ($total_role) {
					$output_total .= __($role['name'])." = ".format_numeric($total_role)." ". $config["currency"]."\n";
				}
			}
		}
	} else {
			$output_total = __('No cost');	
	}

	$table->data[6][1] .= print_label (__('Total costs').print_help_tip($output_total, true), '', '', true,
		$total_cost . $incident_cost_label. $config['currency']);
	
	$avg_hr_cost = format_numeric ($total_cost / $worked_time, 2);
	$table->data[6][1] .= print_label (__('Average Cost per hour'), '', '', true,
		$avg_hr_cost .' '.$config['currency']);
	
	$table->rowspan[5][2] = 5;
	
	// Abbreviation for "Estimated"
	$labela = __('Est.');
	$labelb = __('Real');
	$a = round ($hours);
	$b = round (get_task_workunit_hours ($id_task));

	$image = histogram_2values($a, $b, $labela, $labelb);
	$table->data[5][2] = print_label (__('Estimated hours'), '', '', true, $image);
	
	$labela = __('Total');
	$labelb = __('Imp');
	$a = round (task_workunit_cost ($id_task, 0));
	$b = round (task_workunit_cost ($id_task, 1));
	$image = histogram_2values($a, $b, $labela, $labelb);
	$table->data[5][2] .= print_label (__('Imputable estimation'), '', '', true, $image);	
	
	$labela = __('Est.');
	$labelb = __('Real');
	$a = $estimated_cost;
	$b = round (task_workunit_cost ($id_task, 1));
	$image = histogram_2values($a, $b, $labela, $labelb);
	$table->data[5][2] .= print_label (__('Cost estimation'), '', '', true, $image);
}

$table->colspan[7][0] = 3;
$table->data[7][0] = print_label (__('Completion'), '', '', true,
	'<div id="slider"><div class="ui-slider-handle"></div></div><span id="completion">'.$completion.'%</span>');
$table->data[7][0] .= print_input_hidden ('completion', $completion, true);
$table->data[8][0] = print_textarea ('description', 8, 30, $description, '',
	true, __('Description'));
	
$button = '';

if (($operation != "create" && $task_permission['manage']) || $operation == "create") {
	if ($operation != "create") {
		$button .= print_submit_button (__('Update'), 'update_btn', false, 'class="sub upd"', true);
		$button .= print_input_hidden ('operation', 'update', true);
	} else {
		$button .= print_submit_button (__('Create'), 'create_btn', false, 'class="sub create"', true);
		$button .= print_input_hidden ('operation', 'insert', true);
	}
	$button .= print_input_hidden ('id_project', $id_project, true);
	$button .= print_input_hidden ('id_task', $id_task, true);
}

$table->data['button'][0] = $button;
$table->colspan['button'][0] = 3;

echo '<form id="form-task_detail" method="post" action="index.php?sec=projects&sec2=operation/projects/task_detail">';
print_table ($table);
echo '</form>';

?>
<script type="text/javascript" src="include/js/jquery.ui.slider.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
<script type="text/javascript" src="include/js/integria_date.js"></script>
<script type="text/javascript" src="include/js/jquery.validate.js"></script>
<script type="text/javascript" src="include/js/jquery.validation.functions.js"></script>

<script type="text/javascript">

// Datepicker
add_ranged_datepicker ("#text-start_date", "#text-end_date", function (datetext) {
	hours_day = <?php echo $config['hours_perday'];?>;
	start_date = $("#text-start_date").datepicker ("getDate"); 
	end_date = $(this).datepicker ("getDate");
	if (end_date < start_date) {
		pulsate (this);
	} else {
		hours = Math.floor ((end_date - start_date) / 86400000 * hours_day);
		hours = hours + hours_day;
		$("#text-hours").attr ("value", hours);
	}
});

$(document).ready (function () {
	$("#textarea-description").TextAreaResizer ();
	
	$("#slider").slider ({
		min: 0,
		max: 100,
		stepping: 1,
		value: <?php echo $completion?>,
		slide: function (event, ui) {
			$("#completion").empty ().append (ui.value+"%");
		},
		change: function (event, ui) {
			$("#hidden-completion").attr ("value", ui.value);
		}
	});
});


// Form validation
trim_element_on_submit('#text-name');
validate_form("#form-task_detail");
var rules, messages;
// Rules: #text-name
rules = {
	required: true,
	remote: {
		url: "ajax.php",
        type: "POST",
        data: {
			page: "include/ajax/remote_validations",
			search_existing_task: 1,
			type: "view",
			task_name: function() { return $('#text-name').val() },
			task_id: <?php echo $id_task?>,
			project_id: <?php echo $id_project?>
        }
	}
};
messages = {
	required: "<?php echo __('Name required')?>",
	remote: "<?php echo __('This task already exists')?>"
};
add_validate_form_element_rules('#text-name', rules, messages);

</script>
