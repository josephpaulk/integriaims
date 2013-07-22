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

include_once ("include/functions_graph.php");
include_once ("include/functions_tasks.php");
include_once ("include/functions_projects.php");

$graph_ttl = 1;

if ($pdf_output) {
	$graph_ttl = 2;
}

if (defined ('AJAX')) {
	
	global $config;
	
	$print_subtree = get_parameter('print_subtree', 0);
	
	$id_project = get_parameter ('id_project');
	$id_item = get_parameter ('id_item');
	$branches_json = get_parameter('branches_json');
	$branches = json_decode ($branches_json, true);
	$id_father = get_parameter('id_father');
	$sql_search = base64_decode(get_parameter('sql_search', ''));
	
	if ($print_subtree) {
		
		$sql_tasks = "SELECT t.*
					  FROM ttask t
					  WHERE t.id_parent_task=$id_item
						  AND t.id>0
						  AND t.id_project=$id_project
						  $sql_search
					  ORDER BY t.name";
		
		$sql_tasks_count = "SELECT COUNT(*)
							FROM ttask t
							WHERE t.id_parent_task=$id_item
								AND t.id>0
								AND t.id_project=$id_project
								$sql_search";
		
		if (dame_admin($config['id_user'])) {
			$sql_wo = "SELECT *
					   FROM ttodo
					   WHERE id_task=$id_item
						   AND progress<>1
						   AND progress<>2
					   ORDER BY name";
			$sql_wo_count = "SELECT COUNT(*)
							 FROM ttodo
							 WHERE id_task=$id_item
								 AND progress<>1
								 AND progress<>2";
		} else {
			$sql_wo = "SELECT *
					   FROM ttodo
					   WHERE id_task=$id_item
						  AND (assigned_user='".$config['id_user']."'
							  OR created_by_user='".$config['id_user']."')
						  AND progress<>1
						  AND progress<>2
					   ORDER BY name";
			$sql_wo_count = "SELECT COUNT(*)
							 FROM ttodo
							 WHERE id_task=$id_item
								AND (assigned_user='".$config['id_user']."'
									OR created_by_user='".$config['id_user']."')
								AND progress<>1
								AND progress<>2";
		}
		
		$countRows = process_sql ($sql_tasks_count);
		$countWOs = process_sql ($sql_wo_count);
		
		if ($countRows === false)
			$countRows = 0;
		else
			$countRows = (int) $countRows[0][0];
		
		if ($countWOs === false)
			$countWOs = 0;
		else
			$countWOs = (int) $countWOs[0][0];
		
		if ($countRows == 0 && $countWOs == 0) {
			echo "<ul style='margin: 0; padding: 0;'>\n";
			echo "<li style='margin: 0; padding: 0;'>";
			
			foreach ($branches as $branch) {
				if ($branch) {
					print_image ("images/tree/branch.png", false, array ("style" => 'vertical-align: middle;'));
				} else {
					print_image ("images/tree/no_branch.png", false, array ("style" => 'vertical-align: middle;'));
				}
			}
			
			print_image ("images/tree/last_leaf.png", false, array ("style" => 'vertical-align: middle;'));
			echo "<i>" . __("Empty") . "</i>";
			echo "</li>";
			echo "</ul>";
			return;
		}
		
		$new = true;
		$count = 0;
		echo "<ul style='margin: 0; padding: 0;'>\n";
		
		while ($task = get_db_all_row_by_steps_sql($new, $result, $sql_tasks)) {
			
			$new = false;
			$count++;
			echo "<li style='margin: 0; padding: 0;'>";
			echo "<span style='display: inline-block;'>";
			
			$branches_aux = $branches;
			
			foreach ($branches as $branch) {
				if ($branch) {
					print_image ("images/tree/branch.png", false, array ("style" => 'vertical-align: middle;'));
				} else {
					print_image ("images/tree/no_branch.png", false, array ("style" => 'vertical-align: middle;'));
				}
			}
			
			if ($count < $countRows || $countWOs > 0) {
				$branches_aux[] = true;
				$img = print_image ("images/tree/closed.png", true, array ("style" => 'vertical-align: middle;', "id" => "tree_image" . $id_item. "_task_" . $task["id"], "pos_tree" => "2"));
			} else {
				$branches_aux[] = false;
				$img = print_image ("images/tree/last_closed.png", true, array ("style" => 'vertical-align: middle;', "id" => "tree_image" . $id_item. "_task_" . $task["id"], "pos_tree" => "3"));
			}
			
			// Priority
			$priority = print_priority_flag_image ($task['priority'], true);
			
			// Task name
			$name = safe_output($task['name']);
			
			if (strlen($name) > 25) {
				$name = substr ($name, 0, 25) . "...";
				$name = "<a title='".safe_output($task['name'])."' href='index.php?sec=projects&sec2=operation/projects/task_detail
					&id_project=".$task['id_project']."&id_task=".$task['id']."&operation=view'>".$name."</a>";
			} else {
				$name = "<a href='index.php?sec=projects&sec2=operation/projects/task_detail
					&id_project=".$task['id_project']."&id_task=".$task['id']."&operation=view'>".$name."</a>";
			}
			
			// Completion
			$progress = progress_bar($task['completion'], 70, 20, $graph_ttl);
			
			// Estimation
			$imghelp = "Estimated hours = ".$task['hours'];
			$taskhours = get_task_workunit_hours ($task['id']);
			$imghelp .= ", Worked hours = $taskhours";
			$a = round ($task["hours"]);
			$b = round ($taskhours);
			$mode = 2;
			
			if ($a > 0)
				$estimation = histogram_2values($a, $b, __("Planned"), __("Real"), $mode, 60, 18, $imghelp, $graph_ttl);
			else
				$estimation = "--";
			
			// Time used on all child tasks + this task
			$recursive_timeused = task_duration_recursive ($task["id"]);
			
			$time_used = _('Time used') . ": ";
			
			if ($taskhours == 0)
				$time_used .= "--";
			elseif ($taskhours == $recursive_timeused)
				$time_used .= $taskhours;
			else
				$time_used .= $taskhours . "<span title='Subtasks WU/HR'> (".$recursive_timeused. ")</span>";
				
			$wu_incidents = get_incident_task_workunit_hours ($task["id"]);
		
			if ($wu_incidents > 0)
			$time_used .= "<span title='".__("Time spent in related incidents")."'> ($wu_incidents)</span>";
			
			// People
			$people = combo_users_task ($task['id'], 1, true);
			$people .= ' ';
			$people .= get_db_value ('COUNT(DISTINCT(id_user))', 'trole_people_task', 'id_task', $task['id']);
			
			// Branches
			$branches_json = json_encode ($branches_aux);
			
			echo "<a onfocus='JavaScript: this.blur()' href='javascript: loadTasksSubTree(".$task['id_project'].",".$task['id'].",\"".$branches_json."\", ".$id_item.",\"".$sql_search."\")'>";
			echo $img;
			echo "</a>";
			echo "<span style='vertical-align:middle; display: inline-block;'>".$priority."</span>";
			echo "<span style='margin-left: 15px; min-width: 190px; vertical-align:middle; display: inline-block;'>".$name."</span>";
			echo "<span title='" . __('Progress') . "' style='margin-left: 15px; vertical-align:middle; display: inline-block;'>".$progress."</span>";
			echo "<span style='margin-left: 15px; vertical-align:middle; display: inline-block;'>".$estimation."</span>";
			echo "<span style='margin-left: 15px; vertical-align:middle; display: inline-block;'>".$people."</span>";
			echo "<span style='margin-left: 15px; display: inline-block;'>".$time_used."</span>";
			echo "</span>";
			echo "<div hiddenDiv='1' loadDiv='0' style='margin: 0px; padding: 0px;' class='tree_view tree_div_".$task['id']."' id='tree_div".$id_item."_task_".$task['id']."'></div>";
			echo "</li>";
		}
		
		$new = true;
		$count = 0;
		
		while ($wo = get_db_all_row_by_steps_sql($new, $result, $sql_wo)) {
			
			$new = false;
			$count++;
			echo "<li style='margin: 0; padding: 0;'>";
			echo "<span style='display: inline-block;'>";
			
			foreach ($branches as $branch) {
				if ($branch) {
					print_image ("images/tree/branch.png", false, array ("style" => 'vertical-align: middle;'));
				} else {
					print_image ("images/tree/no_branch.png", false, array ("style" => 'vertical-align: middle;'));
				}
			}
			
			if ($count < $countWOs) {
				$img = print_image ("images/tree/leaf.png", true, array ("style" => 'vertical-align: middle;', "id" => "tree_image" . $id_item. "_task_" . $task["id"], "pos_tree" => "2"));
			} else {
				$img = print_image ("images/tree/last_leaf.png", true, array ("style" => 'vertical-align: middle;', "id" => "tree_image" . $id_item. "_task_" . $task["id"], "pos_tree" => "3"));
			}
			
			// Background color
			if ($wo["end_date"] != "0000-00-00 00:00:00") {
				if ($wo["end_date"] < date('Y-m-d H:i:s')) {
					$background_color = "background: #fff0f0;";
				}
			} else {
				$background_color = "";
			}

			/*
			// Background color
			if ($wo["end_date"] != "0000-00-00 00:00:00") {
				if ($wo["end_date"] < date('Y-m-d H:i:s')) {
					$background_color = "background: #fff0f0;";
				}
			} elseif ($wo["progress"] == 1) {
				$background_color = "background: #f0fff0;";	
			} elseif ($wo["progress"] == 2) {
				$background_color = "background: #f0f0ff;";	
			} else {
				$background_color = "";
			}
			*/
			
			// WO icon
			$wo_icon = print_image ("images/paste_plain.png", true, array ("style" => 'vertical-align: middle;', "id" => "wo_icon", "title" => __('Work order')));
			
			// Priority
			$priority = print_priority_flag_image ($wo['priority'], true);
			
			// WO name
			$name = safe_output($wo['name']);
			
			if (strlen($name) > 50) {
				$name = substr ($name, 0, 50) . "...";
				$name = "<a title='".safe_output($wo['name'])."'
					href='index.php?sec=projects&sec2=operation/workorders/wo&operation=view&id=".$wo['id']."'>".$name."</a>";
			} else {
				$name = "<a href='index.php?sec=projects&sec2=operation/workorders/wo&operation=view&id=".$wo['id']."'>".$name."</a>";
			}
			
			// Owner
			$avatar = get_db_value ('avatar', 'tusuario', 'id_usuario', $wo['assigned_user']);
			if (!$avatar)
				$avatar = "avatar1";
			$owner_icon = "<img src='images/avatars/".$avatar."_small.png' title='".__('Assigned user')."'>";
			
			$owner = safe_output($wo['assigned_user']);
			if (strlen($owner) > 10) {
				$owner = "<a title='".safe_output($wo['assigned_user'])."'
					href='index.php?sec=projects&sec2=operation/workorders/wo&owner="
						.$owner."'>".substr ($owner, 0, 10)."...</a>";
			} else {
				$owner = "<a href='index.php?sec=projects&sec2=operation/workorders/wo&owner="
					.$owner."'>".$owner."</a>";
			}
			
			// Submitter
			$avatar = get_db_value ('avatar', 'tusuario', 'id_usuario', $wo['created_by_user']);
			if (!$avatar)
				$submitter_icon = "<img src='images/user_comment.png' title='".__('Creator')."'>";
			else
				$submitter_icon = "<img src='images/avatars/".$avatar."_small.png' title='".__('Creator')."'>";
			
			$submitter = safe_output($wo['created_by_user']);
			if (strlen($submitter) > 10) {
				$submitter = "<a title='".safe_output($wo['created_by_user'])."'
					href='index.php?sec=projects&sec2=operation/workorders/wo&creator="
						.$submitter."'>".substr ($submitter, 0, 10)."...</a>";
			} else {
				$submitter = "<a href='index.php?sec=projects&sec2=operation/workorders/wo&creator="
					.$submitter."'>".$submitter."</a>";
			}
			
			echo $img;
			echo "<span style='".$background_color." padding: 5px;'>";
			echo "<span style='vertical-align:middle; display: inline-block;'>".$wo_icon."</span>";
			echo "<span style='margin-left: 3px; vertical-align:middle; display: inline-block;'>".$priority."</span>";
			echo "<span style='margin-left: 15px; min-width: 300px; vertical-align:middle; display: inline-block;'>".$name."</span>";
			echo "<span style='margin-left: 15px; vertical-align:middle; display: inline-block;'>".$owner_icon."</span>";
			echo "<span style='margin-left: 3px; min-width: 80px; vertical-align:middle; display: inline-block;'>".$owner."</span>";
			echo "<span style='margin-left: 15px; vertical-align:middle; display: inline-block;'>".$submitter_icon."</span>";
			echo "<span style='margin-left: 3px; min-width: 80px; vertical-align:middle; display: inline-block;'>".$submitter."</span>";
			echo "</span>";
			echo "</span>";
			echo "</li>";
		}

		echo "</ul>";
		
	}
	
	return;
}


$id_project = (int) get_parameter ('id_project');

if (! $id_project) {// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to task manager without project");
	include ("general/noaccess.php");
	exit;
}

$project = get_db_row ('tproject', 'id', $id_project);

if (! user_belong_project ($config['id_user'], $id_project)) {
	audit_db($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation","Trying to access to task manager of unauthorized project");
	include ("general/noaccess.php");
	exit;
}

$id_task = (int) get_parameter ('id');
$operation = (string) get_parameter ('operation');

if ($operation == 'delete') {
	if (dame_admin ($config['id_user']) || project_manager_check ($id_project)) {
		delete_task ($id_task);
		echo '<h3 class="suc">'.__('Successfully deleted').'</h3>';
		$operation = '';
		project_tracking ($id_project, PROJECT_TASK_DELETED);
	} else {
		no_permission ();
	}
}

if ($operation == 'move') {
	$target_project = get_parameter ("target_project");
	$id_task = get_parameter ("id_task");
	if ((dame_admin($config['id_user'])==1) OR (project_manager_check ($id_project) == 1)){
		$sql = sprintf ('UPDATE ttask
			SET id_project = %d,
			id_parent_task = 0
			WHERE id = %d', $target_project, $id_task);
		process_sql ($sql);
		
		// Move subtasks of this task
		$sql = sprintf ('UPDATE ttask
			SET id_project = %d WHERE id_parent_task = %d', $target_project, $id_task);
		process_sql ($sql);
		
		
		task_tracking ($id_task, TASK_MOVED);
	} else {
		no_permission ();
	}
}

// MAIN LIST OF TASKS

$search_id_group = (int) get_parameter ('search_id_group');
$search_text = (string) get_parameter ('search_text');

echo '<h2>'.$project['name'].' &raquo; '.__('Task management');

if (!$clean_output) {
	echo "&nbsp;&nbsp;<a title='"._("Report")."'  href='index.php?sec=projects&sec2=operation/projects/task&id_project=$id_project&search_id_group=$search_id_group&search_text=$search_text&clean_output=1'><img src='images/html.png'></a>";
}

echo '</h2><br>';

$where_clause = ' 1=1 ';
if ($search_text != "")
	$where_clause .= sprintf (' AND name LIKE "%%%s%%" OR description LIKE "%%%s%%"',
		$search_text, $search_text);

if ($search_id_group != 0)
	$where_clause .= sprintf (' AND id_group = %d', $search_id_group);


$table->width = '800px';
$table->class = 'search-table';
$table->style = array ();
$table->style[0] = 'font-weight: bold;';
$table->style[2] = 'font-weight: bold;';
$table->data = array ();
$table->data[0][0] = __('Search');
$table->data[0][1] = print_input_text ("search_text", $search_text, "", 25, 100, true);
$table->data[0][2] = __('Group');
$table->data[0][3] = print_select (get_user_groups (),
	'search_id_group', $search_id_group, '', __('Any'), '0', true);
$table->data[0][4] = print_submit_button (__('Search'), "search_btn", false, 'class="sub search"', true);

echo '<form method="post">';
if ($clean_output == 0)
    print_table ($table);
echo '</form>';

if ($clean_output == 1) {
	
	unset ($table);

	$table->width = '90%x';
	$table->class = 'listing';
	$table->data = array ();
	$table->style = array ();
	$table->style[0] = 'font-size: 11px;';
	$table->head = array ();
	$table->head[0] = __('Name');
	$table->head[1] = __('Pri');
	$table->head[2] = __('Progress');
	$table->head[3] = __('Estimation');
	$table->head[4] = __('Time used');
	$table->head[5] = __('People');
	$table->head[6] = __('Start/End');
	$table->align = array ();
	$table->align[1] = 'left';
	$table->align[2] = 'center';
	$table->align[3] = 'center';
	$table->align[4] = 'center';
	$table->align[8] = 'center';

	$table->style[6] = "font-size: 9px";

	echo project_activity_graph ($id_project, $graph_ttl);

	$color = 1;

	show_task_tree ($table, $id_project, 0, 0, $where_clause);

	if(empty($table->data)) {
		echo '<h3 class="error">'.__('No tasks found').'</h3>';
	}
	else {
		print_table ($table);
	}
	
} else {
	tasks_print_tree ($id_project);
}

/*
if (give_acl ($config['id_user'], 0, 'PW')) {
	echo '<form method="post" action="index.php?sec=projects&sec2=operation/projects/task_detail">';
	echo '<div class="button" style="width: '.$table->width.'">';
	print_input_hidden ('id_project', $id_project);
	print_input_hidden ('operation', 'create');
	print_submit_button (__('New task'), 'crt_btn', false, 'class="sub next"');
	echo '</div>';
	echo '</form>';
}*/


function show_task_row ($table, $id_project, $task, $level) {
	global $config;
	global $graph_ttl;
	
	$data = array ();

	// Task  name
	$data[0] = '';
	
	for ($i = 0; $i < $level; $i++)
		$data[0] .= '<img src="images/small_arrow_right_green.gif" style="position: relative; top: 5px;"> ';
		
	
	$data[0] .= '<a href="index.php?sec=projects&sec2=operation/projects/task_detail&id_project='. $id_project.'&id_task='.$task['id'].'&operation=view">'. $task['name'].'</a>';

	// Priority
    $data[1] = print_priority_flag_image ($task['priority'], true);
	
	// Completion
	
	$data[2] = progress_bar($task["completion"], 70, 20, $graph_ttl);
	
	// Estimation
	$imghelp = "Estimated hours = ".$task["hours"];
	$taskhours = get_task_workunit_hours ($task["id"]);

	$imghelp .= ", Worked hours = $taskhours";
	$a = round ($task["hours"]);
	$b = round ($taskhours);
	$mode = 2;

	if ($a > 0)
		$data[3] = histogram_2values($a, $b, __("Planned"), __("Real"), $mode, 60, 18, $imghelp, $graph_ttl);
	else
		$data[3] = '--';

	// Time used on all child tasks + this task
	$recursive_timeused = task_duration_recursive ($task["id"]);
	
	if ($taskhours == 0)
		$data[4] = "--";
	elseif ($taskhours == $recursive_timeused)
		$data[4] = $taskhours;
	else
		$data[4] = $taskhours . "<span title='Subtasks WU/HR'> (".$recursive_timeused. ")</span>";
		
	$wu_incidents = get_incident_task_workunit_hours ($task["id"]);
	
	
	if ($wu_incidents > 0)
	$data[4] .= "<span title='".__("Time spent in related incidents")."'> ($wu_incidents) </span>";

	// People
	$data[5] = combo_users_task ($task['id'], 1, true);
	$data[5] .= ' ';
	$data[5] .= get_db_value ('COUNT(DISTINCT(id_user))', 'trole_people_task', 'id_task', $task['id']);

	if ($task["start"] == $task["end"]){
		$data[6] = date ('Y-m-d', strtotime ($task['start'])) . "<br>";
		$data[6] .= __('Recurrence').': '.get_periodicity ($task['periodicity']);
	} else {
		// Start
		$start = strtotime ($task['start']);
		$end = strtotime ($task['end']);
		$now = time ();
		
		$data[6] = date ('Y-m-d', $start) ."<br>";
		
		if ($task['completion'] == 100) {
			$data[6] .= '<span style="color: green">';
		} else {
			if ($now > $end)
				$data[6] .= '<span style="color: red">';
			else
				$data[6] .= '<span>';
		}
		$data[6] .= date ('Y-m-d', $end);
		$data[6] .= '</span>';
	}

	// Delete
	if (give_acl ($config['id_user'], 0, 'PM')) {
		$table->head[7] = __('Delete');
		$data[7] = '<a href="index.php?sec=projects&sec2=operation/projects/task&operation=delete&id_project='.$id_project.'&id='.$task["id"].'"
			onClick="if (!confirm(\''.__('Are you sure?').'\')) return false;">
			<img src="images/cross.png" /></a>';
	}
	array_push ($table->data, $data);
}

function show_task_tree (&$table, $id_project, $level, $id_parent_task, $where_clause) {
	global $config;
	
	// Simple query, needs to implement group control and ACL checking
	$sql = sprintf ('SELECT * FROM ttask
		WHERE %s
		AND id_project = %d
		AND id_parent_task = %d
		ORDER BY name',
		$where_clause, $id_project, $id_parent_task);
	$tasks = get_db_all_rows_sql ($sql);
	if ($tasks === false)
		return;
	foreach ($tasks as $task) {
		if (user_belong_task ($config['id_user'], $task['id']))
			show_task_row ($table, $id_project, $task, $level);
		show_task_tree ($table, $id_project, $level + 1, $task['id'], $where_clause);
	}
}

?>

<script type="text/javascript" src="include/js/integria_projects.js"></script>
<script type="text/javascript" src="include/js/jquery.validate.js"></script>
<script type="text/javascript" src="include/js/jquery.validation.functions.js"></script>
<script type="text/javascript">

// Form validation
trim_element_on_submit("#text-search_text");

</script>
