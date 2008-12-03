<?php

// Load global vars
check_login ();

$operation = get_parameter ("operation");
$progress = 0;

// ---------------
// CREATE new todo
// ---------------
if ($operation == "insert") {
	$name = (string) get_parameter ("name");
	$assigned_user = (string) get_parameter ("user");
	$priority = (int) get_parameter ("priority");
	$progress = (int) get_parameter ("progress");
	$description = (string) get_parameter ("description");
	$id_task = (int) get_parameter ("id_task");
	$timestamp = date ('Y-m-d H:i:s');
	$last_updated = $timestamp;
	$sql = sprintf ('INSERT INTO ttodo (name, priority, assigned_user,
		created_by_user, progress, timestamp, last_update, description, id_task)
		VALUES ("%s", %d, "%s", "%s", %d, "%s", "%s", "%s", %d)',
		$name, $priority, $assigned_user, $config['id_user'],
		$progress, $timestamp, $last_updated, $description, $id_task);
	$id = process_sql ($sql, 'insert_id');
	if (! $id)
		echo '<h3 class="error">'.__('Not created. Error inserting data').'</h3>';
	else {
		echo '<h3 class="suc">'.__('Successfully created').'</h3>'; 
		mail_todo (0, $id);
	}
	$operation = "";
	$id = 0;
}

// ---------------
// UPDATE new todo
// ---------------
if ($operation == "update2") {
	$id_todo = get_parameter ("id");
	$todo = get_db_row ("ttodo", "id", $id_todo);
	if (($todo["assigned_user"] != $config['id_user']) AND ($todo["created_by_user"] != $config['id_user'])){
		no_permission();
	}
	$name = $todo["name"];
	$created_by_user = $todo["created_by_user"];
	$id_task = get_parameter ("id_task", 0);
	$priority = get_parameter ("priority");
	$progress = get_parameter ("progress");
	$description = get_parameter ("description");
	$last_update = date('Y-m-d H:i:s');
	$sql_update = "UPDATE ttodo SET id_task = $id_task, priority = '$priority', progress = '$progress', description = '$description', last_update = '$last_update' WHERE id = $id_todo";
	$result=mysql_query($sql_update);
	if (! $result)
		echo "<h3 class='error'>".__('Not updated. Error updating data')."</h3>";
	else
		echo "<h3 class='suc'>".__('Successfully updated')."</h3>";
	mail_todo (1, $id_todo);
	$operation = "";
}

// ---------------
// DELETE new todo
// ---------------
if ($operation == "delete") {
	$id_todo = get_parameter ("id");
	$todo = get_db_row ("ttodo", "id", $id_todo);
	if (($todo["assigned_user"] != $config['id_user']) AND ($todo["created_by_user"] != $config['id_user'])){
		no_permission();
	}
	$assigned_user = $todo["assigned_user"];
	$created_by_user = $todo["created_by_user"];
	$progress = $todo["progress"];
	$name = $todo["name"];
	$description = $todo["description"];
	$priority = $todo["priority"];
	$sql_delete= "DELETE FROM ttodo WHERE id = $id_todo";
	mail_todo (2, $id_todo);
	$result=mysql_query($sql_delete);
	if (! $result)
		echo "<h3 class='error'>".__('Not deleted. Error deleting data')."</h3>";
	else
		echo "<h3 class='suc'>".__('Successfully deleted')."</h3>";
	$operation = "";
}


// CREATE new todo (form)
if ($operation == "create" || $operation == "update") {
	if ($operation == "create") {
		$progress = 0;
		$priority = 0;
		$name = '';
		$description = '';
		$id_task = 0;
	} else {
		$id = get_parameter ("id");
		$todo = get_db_row ("ttodo", "id", $id);
		if ($todo["assigned_user"] != $config['id_user'] && $todo["created_by_user"] != $config['id_user']) {
			no_permission ();
		}
		$assigned_user = $todo["assigned_user"];
		$progress = $todo["progress"];
		$name = $todo["name"];
		$description = $todo["description"];
		$priority = $todo["priority"];
		$id_task = $todo["id_task"];
	}

	$table->width = '90%';
	$table->class = 'databox';
	$table->colspan = array ();
	$table->colspan[0][0] = 2;
	$table->colspan[2][0] = 2;
	$table->colspan[3][0] = 2;
	$table->colspan[4][0] = 2;
	$table->data = array ();
	
	$table->data[0][0] = print_input_text ('name', $name, '', 40, 100, true,
		__('Title'));
	
	$table->data[1][0] = print_select (get_priorities (), 'priority', $priority,
		'', '', '', true, false, false, __('Priority'));
	
	if ($operation == "create") {
		$table->data[1][1] = combo_user_visible_for_me ('', "user", 0,
			"TW", true, __('Assigned to'));
	}
	
	$table->data[2][0] = combo_task_user_participant ($config["id_user"],
		false, 0, true, __('Task'));
	
	$table->data[3][0] = print_label (__('Completion'), '', '', true,
		'<div id="slider"><div class="ui-slider-handle"></div></div><span id="progress">'.$progress.'%</span>');
	$table->data[3][0] .= print_input_hidden ('progress', $progress, true);
	
	$table->data[4][0] = print_textarea ('description', 10, 50, $description, '', true,
		__('Description'));
	
	echo '<form method="post" action="index.php?sec=todo&sec2=operation/todo/todo">';
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

// -------------------------
// TODO VIEW of my OWN items
// -------------------------
if (($operation == "") OR ($operation == "notme")) {
	if ($operation == "notme")
		echo "<h1>".__('To-Do management'). " &raquo; ". __('Assigned to other users')."</h1>";
	else
		echo "<h1>".__('To-Do management')."</h1>";

	if ($operation == "notme")
		$sql = sprintf ('SELECT * FROM ttodo
			WHERE created_by_user = "%s"
			AND assigned_user != "%s"
			ORDER BY priority DESC',
			$config['id_user'], $config['id_user']);
	else
		$sql = sprintf ('SELECT * FROM ttodo
			WHERE assigned_user = "%s"
			ORDER BY priority DESC',
			$config['id_user']);
	$todos = get_db_all_rows_sql ($sql);
	if ($todos === false)
		$todos = array ();
	
	$todos = print_array_pagination ($todos, "index.php?sec=todo&sec2=operation/todo/todo");

	echo '<table class="listing" width="90%">';
	echo "<th>".__('To-Do');
	echo "<th>".__('Priority');
	echo "<th>".__('Progress');
	if ($operation == "notme")
		echo "<th>".__('Assigned to');
	else
		echo "<th>".__('Assigned by');
	//echo "<th>".__('Created');
	echo "<th>".__('Updated');
	echo "<th>".__('Task');
	echo "<th>".__('Delete');

	foreach ($todos as $todo) {
		
		echo "<tr><td>";
		echo "<a href='index.php?sec=todo&sec2=operation/todo/todo&operation=update&id=".$todo["id"]."'>";
		echo $todo["name"];
		echo "</a>";
		
		if (strlen($todo["description"]) > 0){
			echo "<a href='#' class='tip'>&nbsp;<span>";
			echo clean_output_breaks($todo["description"]);
			echo "</span></a>";
		}
		
		echo '<td align="center">';
		echo render_priority ($todo["priority"]);
		echo '<td align="center">';
		$progress = $todo["progress"];
		echo "<img src='include/functions_graph.php?type=progress&width=80&height=20&percent=$progress'>";
		echo '<td valign="middle">';
		if ($operation == "notme") 
			$avatar = get_db_value ("avatar", "tusuario", "id_usuario", $todo["assigned_user"]);
		else
			$avatar = get_db_value ("avatar", "tusuario", "id_usuario", $todo["created_by_user"]);
		echo "<img align='middle' src='images/avatars/".$avatar."_small.png'> ";
		echo "<a href='#' class='tip'><span>";
		if ($operation == "notme")
			echo $todo["assigned_user"];
		else
			echo $todo["created_by_user"];
		echo "</span></a>";
		//echo '<td class="'.$tdcolor.'f9">';
		//echo  human_time_comparation ($todo["timestamp"]);
		echo '<td class="f9">';
		echo human_time_comparation ($todo["last_update"]);
		// Close and assign WU to associate task
		echo '<td align="center">';
		if ($todo["id_task"] > 0){
			$id_project = get_db_value ("id_project", "ttask", "id", $todo["id_task"]);
			$url = "index.php?sec=projects&sec2=operation/users/user_spare_workunit&id_project=$id_project&id_task=".$todo["id_task"];
			echo '<a href="'.$url.'"><img border=0 src="images/award_star_silver_1.png"></a>';
		}
		// DELETE
		echo '<td align="center">';
		echo '<a href="index.php?sec=todo&sec2=operation/todo/todo&operation=delete&id='.$todo["id"].'" onClick="if (!confirm(\' '.__('Are you sure?').'\')) return false;"><img border=0 src="images/cross.png"></a>';
		
	}
	echo "</table>";
} // Fin bloque else

?>
<script type="text/javascript" src="include/js/jquery.ui.slider.js"></script>

<script type="text/javascript">
$(document).ready (function () {
	$("#textarea-description").TextAreaResizer ();
	$("#slider").slider ({
		min: 0,
		max: 100,
		stepping: 5,
		slide: function (event, ui) {
			$("#progress").empty ().append (ui.value+"%");
		},
		change: function (event, ui) {
			$("#hidden-progress").attr ("value", ui.value);
		}
	});
<?php if ($progress)
	echo '$("#slider").slider ("moveTo", '.$progress.');';
?>
});
</script>
