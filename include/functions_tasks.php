<?php 

// Integria IMS - http://integriaims.com
// ==================================================
// Copyright (c) 2007-2011 Artica Soluciones Tecnologicas
// Copyright (c) 2008 Esteban Sanchez, estebans@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License
// (LGPL) as published by the Free Software Foundation; version 2

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.


/**
 * Calculate task completion porcentage and set on task
 *
 * @param int Id of the task to calculate.
 */
function set_task_completion ($id_task) {
	$hours_worked = get_task_workunit_hours ($id_task);
	$hours_estimated = get_db_value ('hours', 'ttask', 'id', $id_task);
	if($hours_worked > $hours_estimated) {
		return -1;
	}
	
	$percentage_completed = ($hours_worked*100)/$hours_estimated;
	process_sql_update ('ttask', array('completion' => $percentage_completed), array('id' => $id_task));
}

/**
* Return total hours assigned to task and subtasks (recursive)
*
* $id_task	integer 	ID of task
**/

function task_duration_recursive ($id_task){
	
	// Get all childs for this task
	$tasks = get_db_all_rows_sql ("SELECT id FROM ttask WHERE id_parent_task = '$id_task'");
	if ($tasks === false) {
		// No parents ?, break recursion and give WU/hr for this task.
		$tasks = array();
	}
	
	$sum = 0;
	foreach ($tasks as $task) {
		$sum += task_duration_recursive ($task[id]);
	}
	return $sum + get_task_workunit_hours ($id_task);
}

/**
* Return total cost assigned to task on external costs attached
*
* $id_task	integer 	ID of task
**/

function task_cost_invoices ($id_task){
	$total = get_db_sql ("SELECT SUM(ammount) FROM ttask_cost WHERE id_task = $id_task");
	return $total;
}

/**
* Return total cost assigned to task on external costs attached
*
* $id_task	integer 	ID of task
**/

function project_cost_invoices ($id_project){
	
	$tasks = get_db_all_rows_sql ("SELECT * FROM ttask WHERE id_project = $id_project");
	if ($tasks === false)
		$tasks = array ();
	
	$total = 0;
	foreach ($tasks as $task) {
		$total += task_cost_invoices ($task["id"]);
	}	
	return $total;
}

/**
* Return total hours assigned to project (planned)
*
* $id_project	integer 	ID of project
**/

function get_planned_project_workunit_hours ($id_project){ 
	global $config;
	
	$total = 0;
	$total = (int) get_db_sql ("SELECT SUM(hours) FROM ttask WHERE id_project = $id_project");
	return $total;
}

?>
