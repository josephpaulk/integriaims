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

?>
