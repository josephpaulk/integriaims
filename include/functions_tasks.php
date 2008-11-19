<?php 

// Integria 2.0 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2008 Artica Soluciones Tecnologicas
// Copyright (c) 2008 Esteban Sanchez, estebans@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

/**
 * Add hours to a task and calculate task completion.
 *
 * It calculates task completion based on estimated hours and worked hours.
 *
 * @param int Id of the task to add hours.
 * @param float Worked hours to 
 */
function add_task_hours ($id_task, $hours) {
	
	/* No negative values allowed */
	if ($hours < 0)
		return;
	
	$task = get_db_row ('ttask', 'id', $id_task);
	if ($task === false)
		return;
	
	/* Do nothing if task is completed */
	if ($task['completion'] >= 100)
		return;
	/* Do nothing if ther are no estimated hours */
	if ($task['hours'] <= 0)
		return;
	
	/* Get expected task completion, based on worked hours */
	$expected_completion = round_number (floor ($current_hours * 100 / $hours));
	
	/* If completion was not set manually, update with current progress */
	if ($task['completion'] != $expected_completion)
		return;
	
	$current_hours += $duration;
	$expected_completion =  round_number (floor ($current_hours * 100 / $hours));
	$sql = sprintf ('UPDATE ttask
		SET completion = %d
		WHERE id = %d',
		$expected_completion, $id_task);
	process_sql ($sql);
}
?>
