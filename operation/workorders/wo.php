<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2010 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;
$operation = get_parameter ("operation");
$set_progress = (int) get_parameter ("set_progress", -1);
$progress = 0;

include_once ("include/functions_graph.php");
require_once ('include/functions_db.php');
require_once ('include/functions_ui.php');
require_once ('include/functions_user.php');

$id = (int) get_parameter ("id");
$offset = get_parameter ("offset", 0);

// ---------------
// CREATE new todo
// ---------------
if ($operation == "insert") {
	$name = (string) get_parameter ("name");
	
	$priority = (int) get_parameter ("priority");
	$progress = (int) get_parameter ("progress");
	$description = (string) get_parameter ("description");
	$id_task = (int) get_parameter ("id_task");
	$timestamp = date ('Y-m-d H:i:s');
	$last_updated = $timestamp;

	$creator = get_parameter ("creator", $config["id_user"]);
	$assigned_user = (string) get_parameter ("assigned_user");
	$start_date = (string) get_parameter ("start_date");
	$end_date = (string) get_parameter ("end_date");
	$validation_date = "";
	$need_external_validation = (int) get_parameter ("need_external_validation");
	$id_wo_category = (int) get_parameter ("id_wo_category"); 

	$sql = sprintf ('INSERT INTO ttodo (name, priority, assigned_user,
		created_by_user, progress, start_date, last_update, description, id_task, end_date, need_external_validation, id_wo_category)
		VALUES ("%s", %d, "%s", "%s", %d, "%s", "%s", "%s", %d, "%s", %d, %d)',
		$name, $priority, $assigned_user, $creator,
		$progress, $start_date, $last_updated, $description, $id_task, $end_date, $need_external_validation, $id_wo_category);
	$id = process_sql ($sql, 'insert_id');
	if (! $id)
		echo '<h3 class="error">'.__('Not created. Error inserting data').'</h3>';
	else {
		echo '<h3 class="suc">'.__('Successfully created').'</h3>'; 
		
		mail_todo (0, $id);

		// TODO: Create agenda item if end_date is defined.
	}

	clean_cache_db();
	$operation = "view"; // Keep in view/edit mode.

}

// ---------------
// UPDATE new todo
// ---------------
if ($operation == "update2") {
	$id = get_parameter ("id");
	$todo = get_db_row ("ttodo", "id", $id);
	
	if (!dame_admin($config["id_user"]))
		if (($todo["assigned_user"] != $config['id_user']) AND ($todo["created_by_user"] != $config['id_user'])){
			no_permission();
		}

	$name = (string) get_parameter ("name", "");
	$id_task = get_parameter ("id_task", 0);
	$priority = get_parameter ("priority");
	$progress = get_parameter ("progress");
	$description = get_parameter ("description");
	$last_update = date('Y-m-d H:i:s');
	$creator = get_parameter ("creator", $config["id_user"]);
	$assigned_user = (string) get_parameter ("assigned_user");
	$start_date = (string) get_parameter ("start_date");
	$end_date = (string) get_parameter ("end_date");
	$validation_date = "";
	$need_external_validation = (int) get_parameter ("need_external_validation");
	$id_wo_category = (int) get_parameter ("id_wo_category"); 


	$sql_update = "UPDATE ttodo SET created_by_user = '$creator', need_external_validation = $need_external_validation, id_wo_category = $id_wo_category, start_date = '$start_date', end_date = '$end_date', assigned_user = '$assigned_user', id_task = $id_task, priority = '$priority', progress = '$progress', description = '$description', last_update = '$last_update', name = '$name' WHERE id = $id";

	$result=mysql_query($sql_update);
	if (! $result)
		echo "<h3 class='error'>".__('Not updated. Error updating data')."</h3>";
	else
		echo "<h3 class='suc'>".__('Successfully updated')."</h3>";
	
	mail_todo (1, $id);
	// TODO. Review this.

	$operation = "view"; // Keep in view/edit mode.
	clean_cache_db();

}

// ---------------
// DELETE todo
// ---------------
if ($operation == "delete") {
	$id_todo = get_parameter ("id");
	$todo = get_db_row ("ttodo", "id", $id_todo);

	if (($todo["assigned_user"] != $config['id_user']) AND ($todo["created_by_user"] != $config['id_user'])){
		if (!dame_admin($config["id_user"]))
			no_permission();
	}
	
	$sql_delete= "DELETE FROM ttodo WHERE id = $id_todo";
	
	$result=mysql_query($sql_delete);

	// TODO: Delete attachment from disk and database

	if (! $result)
		echo "<h3 class='error'>".__('Not deleted. Error deleting data')."</h3>";
	else
		echo "<h3 class='suc'>".__('Successfully deleted')."</h3>";
	$operation = "";
}

// ---------------
// Set progress
// ---------------

if ($set_progress > -1 ) {
	$id_todo = get_parameter ("id");
	$todo = get_db_row ("ttodo", "id", $id_todo);

	if (($todo["assigned_user"] != $config['id_user']) AND ($todo["created_by_user"] != $config['id_user'])){
		no_permission();
	}
	$datetime =  date ("Y-m-d H:i:s");
	$sql_delete= "UPDATE ttodo SET progress = $set_progress, last_update = '$datetime' WHERE id = $id_todo";
	$result=mysql_query($sql_delete);
}

// ---------------
// CREATE new todo (form)
// ---------------

if ($operation == "create" || $operation == "update" || $operation == "view")  {
	if ($operation == "create") {
		$progress = 0;
		$priority = 2;
		$name = '';
		$description = '';
		$id_task = 0;
		$creator = $config["id_user"];
		$assigned_user = $config["id_user"];
		$start_date = date('Y-m-d');
		$end_date = "";
		$validation_date = "";
		$need_external_validation = 0;
		$id_wo_category = 0;  
		$owner = "";
	} else {

		if (!isset($id))
			$id = get_parameter ("id");

		$todo = get_db_row ("ttodo", "id", $id);

		// Basic ACL check
		if (!dame_admin($config["id_user"]))
			if ($todo["assigned_user"] != $config['id_user'] && $todo["created_by_user"] != $config['id_user']) {
				no_permission ();
			}

		$creator = $todo["created_by_user"];
		$assigned_user = $todo["assigned_user"];
		$progress = $todo["progress"];
		$name = $todo["name"];
		$description = $todo["description"];
		$priority = $todo["priority"];
		$id_task = $todo["id_task"];
		$end_date = $todo["end_date"];
		$start_date = $todo["start_date"];
		$validation_date = $todo["validation_date"];
		$need_external_validation = $todo["need_external_validation"];
		$id_wo_category = $todo["id_wo_category"];
	}

	$tab = get_parameter ("tab", "");	

	if ($operation == "view" || $operation == "update") {


		$search_params="&owner=$assigned_user&creator=$creator";

		echo '<ul style="height: 30px;" class="ui-tabs-nav">';
		echo '<li class="ui-tabs">';
		echo '<a href="index.php?sec=projects&sec2=operation/workorders/wo'.$search_params.'"><span>'.__("Search").'</span></a></li>';

		if ($tab == "")
			echo '<li class="ui-tabs-selected">';
		else
			echo '<li class="ui-tabs">';
		echo '<a href="index.php?sec=projects&sec2=operation/workorders/wo&operation=view&id='.$id.'"><span>'.__("Workorder").'</span></a></li>';

		if ($tab == "wu")
			echo '<li class="ui-tabs-selected">';
		else
			echo '<li class="ui-tabs">';
		echo '<a href="index.php?sec=projects&sec2=operation/workorders/wo&operation=view&tab=wu&id='.$id.'"><span>'.__("Add Workunit").'</span></a></li>';
	

		if ($tab == "files")
			echo '<li class="ui-tabs-selected">';
		else
			echo '<li class="ui-tabs">';
		echo '<a href="index.php?sec=projects&sec2=operation/workorders/wo&operation=view&tab=files&id='.$id.'"><span>'.__("Files").'</span></a></li>';
		echo "</ul>";
	}

	// Create WU
	if ($tab == "wu"){
		$_POST["id_task"]=$id_task;
		include "operation/users/user_spare_workunit.php";
	}


	// Files
	if ($tab == "files"){
		$_POST["id_task"]=$id_task;
		include "operation/workorders/wo_files.php";
	}

	// Display main form / view 

	if ($tab == ""){ 
		$table->width = '90%';
		$table->class = 'databox';
		$table->colspan = array ();
		
		$table->colspan[5][0] = 2;
		$table->data = array ();
		
		$table->data[0][0] = print_input_text ('name', $name, '', 80, 120, true,
			__('Title'));
		
		$table->data[0][1] = print_select (get_priorities (), 'priority', $priority,
			'', '', '', true, false, false, __('Priority'));
		
		$table->data[1][0] = print_select_from_sql ('SELECT id, name FROM two_category ORDER BY name',
		'id_wo_category', $id_wo_category, '', __("Any"), 0, true, false, false,
		__('Category'));


		if ($creator != $config["id_user"]){
			$table->data[1][1] = print_label (__("Requires validation"), '', 'input', true);
			if ($need_external_validation == 1)
				$table->data[1][1] .= __("Yes");
			else
				$table->data[1][1] .= __("No");
			$table->data[1][1] .= print_input_hidden ("need_external_validation", $need_external_validatio, true);
		} else
			$table->data[1][1] = print_checkbox ("need_external_validation", 1, $need_external_validation, true, __("Require external validation"));


		if ($creator != $config["id_user"]){
			$table->data[2][0] = print_label (__("Submitter"), '', 'input', true);
			$table->data[2][0] .= dame_nombre_real($creator);		
			$table->data[2][0] .= print_input_hidden ("creator", $creator, true);
		} else {
			$table->data[2][0] = print_input_text_extended ('creator', $creator, 'text-user2', '', 15, 30, false, '',
				array('style' => 'background: url(' . $src_code . ') no-repeat right;'), true, '', __('Submitter'))

			. print_help_tip (__("Type at least two characters to search"), true);
		}

		$params['input_id'] = 'text-user';
		$params['input_name'] = 'assigned_user';
		$params['input_size'] = 30;
		$params['input_maxlength'] = 100;
		$params['input_value'] = $assigned_user;
		$params['title'] = 'Assigned user';
		$params['return'] = true;
		$params['return_help'] = true;
			
		$table->data[2][1] = user_print_autocomplete_input($params);

		$table->data[3][0] = combo_task_user_participant ($config["id_user"],
			false, $id_task, true, __('Task'));
		

		// Remove validated user if current user is not the creator OR this doesnt need to be validated
		if (($creator != $config["id_user"]) OR ($need_external_validation == 0))
			$wo_status_values = wo_status_array (1);	
		else
			$wo_status_values = wo_status_array (0);	


		$table->data[3][1] = print_select ($wo_status_values, 'progress', $progress, 0, '', -1, true, 0, false, __('Status') );

		if ($creator != $config["id_user"]){
			$table->data[4][0] = print_label (__("Start date"), '', 'input', true);
			$table->data[4][0] .= $start_date;
			$table->data[4][0] .= print_input_hidden ("start_date", $start_date, true);
		} else
			$table->data[4][0] = print_input_text ('start__date', $start_date , '', 25, 25, true, __('Start date'));
		
		if ($end_date == "0000-00-00 00:00:00"){
				$end_date = '';
		}

		if ($creator != $config["id_user"]){
			$table->data[4][1] = print_label (__("Deadline"), '', 'input', true);
			if ($end_date == "")
				$table->data[4][1] .= __("None");
			else
				$table->data[4][1] .= $end_date;
			$table->data[4][1] .= print_input_hidden ("end_date", $end_date, true);
		} else 
			$table->data[4][1] = print_input_text ('end_date', $end_date , '', 25, 25, true, __('Deadline'));

		$table->data[5][0] = print_textarea ('description', 12, 50, $description, '', true, __('Description'));

		echo '<form id="form-wo" method="post">';
		print_table ($table);

		echo '<div class="button" style="width: '.$table->width.'">';
		if ($operation == 'create') {
			print_submit_button (__('Create'), 'crt', false, 'class="sub next"');
			print_input_hidden ('operation', 'insert');
		} else {
			print_submit_button (__('Update'), 'upd', false, 'class="sub upd"');
			print_input_hidden ('operation', 'update2');
			print_input_hidden ('id', $id);
		}
		echo '</form></div>';
	}
}

// -------------------------
// Workorder listing
// -------------------------
if ($operation == "") {

	echo "<h1>".__('Work order management')."</h1>";

	// TODO: Show only leads of my company or my company's children.
	// TODO: Implement ACL check !

	$search_text = (string) get_parameter ('search_text');
	$id_wo_category = (int) get_parameter ('id_wo_category');
	$search_status = (int) get_parameter ("search_status",0);
	$owner = (string) get_parameter ("owner", "");

	$creator = (string) get_parameter ("creator", "");
	$id_category = get_parameter ("id_category");
	$search_priority = get_parameter ("search_priority", -1);
	$need_validation =get_parameter("need_validation",0);

	$params = "&search_priority=$search_priority&search_tatus=$search_status&search_text=$search_text&id_category=$id_category&owner=$owner&creator=$creator&need_validation=$need_validation";

	$where_clause = "WHERE 1=1 ";

	if ($need_validation){
		$where_clause = "WHERE need_external_validation = 1 ";
	}

	if ($creator != ""){
		$where_clause .= " AND created_by_user = '$creator' ";
	}

	if ($search_priority > -1){
		$where_clause .= " AND priority = $search_priority ";
	}

	if ($owner != "") {
		$where_clause .= sprintf (' AND assigned_user =  "%s"', $owner);
	}

	if ($search_text != "") {
		$where_clause .= sprintf (' AND (name LIKE "%%%s%%" OR description LIKE "%%%s%%")', $search_text, $search_text);
	}
	
	if ($search_status > -1) {
		$where_clause .= sprintf (' AND progress = %d ', $search_status);
	}

	if ($id_category) {
		$where_clause .= sprintf(' AND id_wo_category = %d ', $id_category);
	}

	echo '<form action="index.php?sec=projects&sec2=operation/workorders/wo" method="post">';		

	$table->class = 'databox';
	$table->style = array ();
	$table->style[0] = 'font-weight: bold;';
	$table->data = array ();
	$table->width = "94%";

	$table->data[0][0] = print_input_text ("search_text", $search_text, "", 15, 100, true, __('Search'));

	$table->data[0][1] = print_input_text_extended ('owner', $owner, 'text-user', '', 15, 30, false, '',
			array('style' => 'background: url(' . $src_code . ') no-repeat right;'), true, '', __('Owner'))

		. print_help_tip (__("Type at least two characters to search"). ". " . __("Use '*' for get all values"), true);

	$table->data[0][2] = print_input_text_extended ('creator', $creator, 'text-user2', '', 15, 30, false, '',
			array('style' => 'background: url(' . $src_code . ') no-repeat right;'), true, '', __('Submitter'))

		. print_help_tip (__("Type at least two characters to search"), true);


	$wo_status_values = wo_status_array ();		

	$table->data[1][0] = print_select ($wo_status_values, 'search_status', $search_status, '', __("Any"), -1, true, 0, false, __('WO Status') );

	$priorities = get_priorities();
	$table->data[1][1] = print_select ($priorities, 'search_priority', $search_priority, '', __("Any"), -1, true, 0, false, __('Priority') );
	
	$avatar = get_db_value ('avatar', 'tusuario', 'id_usuario', $config["id_user"]);
	if (!$avatar)
		$avatar = "avatar1";
	
	$table->data[1][2] = print_submit_button (__('Search'), "search_btn", false, 'class="sub search"', true);
	$table->data[1][2] .= ' <a href="index.php?sec=projects&sec2=operation/workorders/wo&owner='
		.$config["id_user"].'"><img src="images/avatars/'.$avatar.'_small.png" title="'.__('My WO\'s').'"></a>';
	$table->data[1][2] .= ' <a href="index.php?sec=projects&sec2=operation/workorders/wo&creator='
		.$config["id_user"].'"><img src="images/user_comment.png" title="'.__('My delegated WO\'s').'"></a>';
	
	$table->rowspan[0][3] = 3;
	
	if ($owner != "") {
		$table->data[0][3] = '<b>'.__('Submitters') .'</b>';
		$table->data[0][3] .= '<br>'. graph_workorder_num ('200', '100', 'submitter',$where_clause);
	} else {
		$table->data[0][3] = '<b>'.__('Owners') .'</b>';
		$table->data[0][3] .= '<br>'. graph_workorder_num ('200', '100', 'owner',$where_clause);

	}
	
	print_table ($table);
	$table->data = array ();

	echo '<a href="javascript:;" onclick="$(\'#advanced_div\').slideToggle (); return false">';
	echo __('Advanced search &gt;&gt;');
	echo '</a>';
	echo '<div id="advanced_div" style="padding: 0px; margin: 0px; display: none;">';

	$table->data[0][0] = print_select_from_sql ('SELECT id, name FROM two_category ORDER BY name',
	'id_category', $id_category, '', __("Any"), 0, true, false, false,
	__('Category'));

	$table->data[0][1] =  print_checkbox ("need_validation", 1, $need_validation, true, __("Require validation"));
	print_table ($table);
	
	echo "</div>";
	echo '</form>';
	
	if ($owner == $config['id_user'] && $creator == "") {
		$order_by = "ORDER BY created_by_user, priority, last_update DESC";
	} elseif ($creator == $config['id_user'] && $owner == "") {
		$order_by = "ORDER BY assigned_user, priority, last_update DESC";
	} else {
		$order_by = "ORDER BY priority, last_update DESC";
	}
	
	$sql = "SELECT * FROM ttodo ".$where_clause." ".$order_by;
	
	$wos = get_db_all_rows_sql ($sql);

	$wos = print_array_pagination ($wos, "index.php?sec=projects&sec2=operation/workorders/wo$params");

	if ($wos !== false) {
		unset ($table);
		$table->width = "94%";
		$table->class = "listing";
		$table->data = array ();
		$table->size = array ();
		$table->style = array ();
		$table->rowstyle = array ();

		
		$table->head = array ();
		$table->head[0] = __('WO #');
		$table->head[2] = __('Name');
		$table->head[3] = __('Criticity');
		$table->head[4] = __('Status');
		$table->head[5] = __('Owner');
		$table->head[6] = __('Submmiter');
		$table->head[7] = __('Cat.');
		$table->head[8] = __('Deadline');
		$table->head[9] = __('Created/Updated');
		$table->head[10] = __('Options');
		/*$table->size[6] = '80px;';
		$table->size[5] = '130px;';*/
		

		foreach ($wos as $wo) {
			$data = array ();
			
			// Detect is the WO is pretty old 
			// Stored in $config["lead_warning_time"] in days, need to calc in secs for this

			$config["lead_warning_time"]= 7; // days
			$config["lead_warning_time"] = $config["lead_warning_time"] * 86400;

			if (calendar_time_diff ($wo["last_update"]) > $config["lead_warning_time"] ){
				$style = "background: #fff0f0";
			} else {
				$style = "";
			}

			if ($wo["end_date"] != "0000-00-00 00:00:00")
				if ($wo["end_date"] < date('Y-m-d H:i:s')){
					$style = "background: #fff0f0";
				}

			if ($wo["progress"] == 1)
					$style = "background: #f0fff0";	

			if ($wo["progress"] == 2)
					$style = "background: #f0f0ff";	


			$data[0] = "<a href='index.php?sec=projects&sec2=operation/workorders/wo&operation=view&id=".
				$wo['id']."'>#<b>".$wo["id"]."</b></a>";

			$data[2] = "<a href='index.php?sec=projects&sec2=operation/workorders/wo&operation=view&id=".
				$wo['id']."'>".short_string ($wo['name'],45)."</a>";

			if ($wo["id_task"] != 0){

				$id_project = get_db_value ("id_project", "ttask", "id", $wo["id_task"]);
				$project_title = short_string (get_db_value ("name", "tproject", "id", $id_project), 35);
				$task_title = short_string (get_db_value ("name", "ttask", "id", $wo["id_task"]), 35);
				$buffer = "<br><span style='font-size: 9px'>" . $project_title . " / " . $task_title . "</span>";
				$data[2] .= $buffer;
			}

			if ($wo["priority"] == 0)
				$data[3] = "<img src='images/pixel_blue.png' width=12 height=12 title='Informative'>";

			if ($wo["priority"] == 1)
				$data[3] = "<img src='images/pixel_yellow.png' width=12 height=12 title='Low'>";

			if ($wo["priority"] == 2)
				$data[3] = "<img src='images/pixel_orange.png' width=12 height=12 title='Medium'>";

			if ($wo["priority"] == 3)
				$data[3] = "<img src='images/pixel_red.png' width=12 height=12 title='High'>";

			if ($wo["priority"] == 4)
				$data[3] = "<img src='images/pixel_fucsia.png' width=12 height=12 title='Very High'>";

			if ($wo["priority"] == 10)
				$data[3] = "<img src='images/pixel_gray.png' width=12 height=12 title='--'>";

			if ($wo["progress"] == 0)
				$data[4] = __("Pending");
			
			if ($wo["progress"] == 1)
				$data[4] = __("Finished");
			
			if ($wo["progress"] == 2)
				$data[4] = __("Validated");
			
			if ($wo["assigned_user"] == $config["id_user"]) {
				$data[5] = '<a href="index.php?sec=projects&sec2=operation/workorders/wo&owner='.$wo["assigned_user"].'">'.__("Me").'</a>';
			}
			else {
				$data[5] = '<a href="index.php?sec=projects&sec2=operation/workorders/wo&owner='.$wo["assigned_user"].'">'.$wo["assigned_user"].'</a>';
			}
			
			if ($wo["assigned_user"] != $wo["created_by_user"]) {
				if ($wo["created_by_user"] == $config["id_user"]) {
					$data[6] = '<a href="index.php?sec=projects&sec2=operation/workorders/wo&creator='.$wo["created_by_user"].'">'.__("Me").'</a>';
				} else {
					$data[6] = '<a href="index.php?sec=projects&sec2=operation/workorders/wo&creator='.$wo["created_by_user"].'">'.$wo["created_by_user"].'</a>';
				}
				if ($wo["need_external_validation"] == 1)
					$data[6] .= "<img src='images/bullet_delete.png' title='".__("Requires validation") . "'>";
			} else {
				if ($wo["created_by_user"] == $config["id_user"]) {
					$data[6] = '<a href="index.php?sec=projects&sec2=operation/workorders/wo&creator='.$wo["created_by_user"].'">'.__("Me").'</a>';
				} else {
					$data[6] = '<a href="index.php?sec=projects&sec2=operation/workorders/wo&creator='.$wo["created_by_user"].'">'.$wo["created_by_user"].'</a>';
				}
			}

			
			if ($wo["id_wo_category"]){
				$category = get_db_row ("two_category", "id", $wo["id_wo_category"]);
				$data[7] = "<img src='images/wo_category/".$category["icon"]."' title='".$category["name"]."'>";
			} else {
				$data[7] = "";
			}

			if ($wo["end_date"] != "0000-00-00 00:00:00")
				$data[8] = "<span style='font-size: 9px'>".substr($wo["end_date"],0,10). "</span>";
			else
				$data[8] = "--";

			$data[9] = "<span style='font-size: 9px'>". human_time_comparation($wo["start_date"]) . "<br>". human_time_comparation($wo["last_update"]). "</span>";
			
			$data[10] = "";
			if ($wo['assigned_user'] == $config["id_user"]){
				if ($wo["progress"] == 0){
					$data[10] .= "<a href='index.php?sec=projects&sec2=operation/workorders/wo$params&id=". $wo['id']."&set_progress=1'><img src='images/ack.png' title='".__("Set as finished")."'></a>";
				} 
			}

			if (($wo["progress"] < 2) AND ($wo["created_by_user"] == $config["id_user"]) AND ($wo["need_external_validation"] == 1) ){	
				$data[10] = "<a href='index.php?sec=projects&sec2=operation/workorders/wo$params&id="
					. $wo['id']."&set_progress=2&offset=$offset'><img src='images/rosette.png' title='".__("Validate")."'></a>";
			}

			// Evaluate different conditions to allow WO deletion
			$can_delete = dame_admin($config["id_user"]);

			if (($wo["need_external_validation"] == 0) AND ($wo["assigned_user"] == $config["id_user"]))
				$can_delete = 1;

			if (($wo["need_external_validation"] == 1) AND ($wo["created_by_user"] == $config["id_user"]))
				$can_delete = 1;				

			if ($can_delete){
				$data[10] .= '&nbsp;&nbsp;<a href="index.php?sec=projects&sec2=operation/workorders/wo'
					.$params.'&operation=delete&id='.$wo['id'].'&offset='.$offset.'""onClick="if (!confirm(\''
					.__('Are you sure?').'\')) return false;"><img src="images/cross.png"></a>';
			}

			array_push ($table->data, $data);
			array_push ($table->rowstyle, $style);
		}
		print_table ($table);
	}


} // Fin bloque else
?>

<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
<script type="text/javascript" src="include/js/integria_date.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.autocomplete.js"></script>
<script type="text/javascript" src="include/js/jquery.validate.js"></script>
<script type="text/javascript" src="include/js/jquery.validation.functions.js"></script>

<script type="text/javascript" >

// Datepicker
add_ranged_datepicker ("#text-start__date", "#text-end_date", null);

// Form validation
trim_element_on_submit('#text-search_text');
trim_element_on_submit('#text-name');
validate_form("#form-wo");
var rules, messages;
// Rules: #text-name
rules = { required: true };
messages = { required: "<?php echo __('Name required')?>" };
add_validate_form_element_rules('#text-name', rules, messages);

$(document).ready (function () {
	$("#textarea-description").TextAreaResizer ();
	
	var idUser = "<?php echo $config['id_user'] ?>";
	bindAutocomplete ("#text-user", idUser);
	bindAutocomplete ("#text-user2", idUser);
	
});

</script>
