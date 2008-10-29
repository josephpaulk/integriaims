<?PHP

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


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// ADD WORK UNIT CONTROL ( TASK )
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

global $config;
check_login ();

$id_project = (int) get_parameter ('id_project');
$id_workunit = (int) get_parameter ('id_workunit');
$id_task = (int) get_parameter ('id_task');
$task_name = get_db_value ('name', 'ttask', 'id', $id_task);

if ($id_workunit){
	$workunit = get_db_row ("tworkunit", "id", $id_workunit);
	$id_user = $workunit['id_user'];
	$duration = $workunit['duration']; 
	$description = $workunit['description'];
	$have_cost = $workunit['have_cost'];
	$id_profile = $workunit['id_profile'];
	$now = $workunit['timestamp'];
	$public = (bool) $workunit['public'];
	$now_date = substr ($now, 0, 10);
	$now_time = substr ($now, 10, 8);
} else {
	$id_user = $config["id_user"];
	$duration = 1; 
	$description = "";
	$id_inventory = array();
	$have_cost = 0;
	$public = true;
	$id_profile = "";
	$now_date = date ("Y-m-d");
	$now_time = date ("H:i:s");
	$now = date ("Y-m-d H:i:s");
}

if (project_manager_check ($id_project) || ($id_user = $config["id_user"])) {
	if ($id_workunit)
		echo __('Update workunit')." - $task_name</h3>";
	else
		echo __('Add workunit')." - $task_name</h3>";
	
	$table->class = 'databox';
	$table->width = '750px';
	$table->colspan = array ();
	$table->colspan[3][0] = 3;
	$table->data = array ();
	
	$table->data[0][0] = print_input_text ('date', $now_date, '', 10, 10, true, __('Date'));
	$table->data[0][1] = print_input_text ('time', $now_time, '', 10, 10, true, __('Time'));
	
	$table->data[0][2] = print_select (array (), 'incident_inventories', false, '', '', '', true, false, false, __('Inventory affected'));
	$table->data[0][2] .= print_button (__('Add'), 'search_inventory', false, '', 'class="dialogbtn"', true);
	$table->data[0][2] .= print_button (__('Remove'), 'delete_inventory', false, '', 'class="dialogbtn"', true);
	
	$table->data[1][0] = combo_user_task_profile ($id_task, 'work_profile', $id_profile, false, true);
	$table->data[1][1] = print_checkbox ('have_cost', 1, $have_cost, true, __('Have cost'));
	
	$table->data[2][0] = print_input_text ('duration', $duration, '', 7, 7, true, __('Time used'));
	$table->data[2][0] .= ' '.__('Hours');
	$table->data[2][1] = print_checkbox ('public', 1, $public, true, __('Public'));
	
	$table->data[3][0] = print_textarea ('description', 5, 10, $description, '', true, __('Description'));
	
	echo '<form method="post" id="add_workunit_form" action="index.php?sec=projects&sec2=operation/projects/task_workunit">';
	print_table ($table);
	echo '<div class="button" style="width:'.$table->width.'">';
	
	print_input_hidden ('operation', 'workunit');
	print_input_hidden ('id_task', $id_task);
	print_input_hidden ('id_project', $id_project);
	
	if ($id_workunit) {
		// Update
		print_input_hidden ('id_workunit', $id_workunit);
		print_submit_button (__('Update'), 'upd_btn', false, 'class="sub upd"');
	} else {
		// Insert
		print_submit_button (__('Add'), 'crt_btn', false, 'class="sub next"');
	}
	echo '</div>';
	echo '</form>';
}
?>

<script type="text/javascript" src="include/js/jquery.metadata.js"></script>
<script type="text/javascript" src="include/js/jquery.tablesorter.js"></script>
<script type="text/javascript" src="include/js/jquery.tablesorter.pager.js"></script>
<script type="text/javascript" src="include/js/integria_incident_search.js"></script>
<script  type="text/javascript">
$(document).ready (function () {
	configure_inventory_buttons ("add_workunit_form", "");
});
</script>
