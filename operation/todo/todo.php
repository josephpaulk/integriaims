<?php

// Load global vars
if (check_login() == 0){
   	$id_user = $config["id_user"];
	$operation = give_parameter_get ("operation");

	// ---------------
	// CREATE new todo
	// ---------------
	if ($operation == "create2") {
		$name = give_parameter_post ("name");
		$assigned_user = give_parameter_post ("user");
		$priority = give_parameter_post ("priority");
		$progress = give_parameter_post ("progress");
		$description = give_parameter_post ("description");
		$id_task = give_parameter_post ("task",0);
		$timestamp = date('Y-m-d H:i:s');
		$last_updated = $timestamp;
		if ($id_task == "")
			$id_task = 0;
		$sql_insert="INSERT INTO ttodo (name, priority, assigned_user, created_by_user, progress, timestamp, last_update, description, id_task ) VALUES ('$name',$priority, '$assigned_user', '$id_user', '$progress', '$timestamp', '$last_updated', '$description', $id_task)";

		$result=mysql_query($sql_insert);	
		if (! $result)
			echo "<h3 class='error'>".__('Not created. Error inserting data')."</h3>";
		else {
			echo "<h3 class='suc'>".__('Created successfully')."</h3>"; 
			$id_todo = mysql_insert_id();
		}
        mail_todo (0, $id_todo);
		$operation = "";
	}

	// ---------------
	// UPDATE new todo
	// ---------------
	if ($operation == "update2") {
		$id_todo = give_parameter_get ("id");
		$row = get_db_row ("ttodo", "id", $id_todo);
		if (($row["assigned_user"] != $id_user) AND ($row["created_by_user"] != $id_user)){
			no_permission();
		}
		$name = $row["name"];
		$created_by_user = $row["created_by_user"];
		$id_task = give_parameter_post ("task", 0);
		$priority = give_parameter_post ("priority");
		$progress = give_parameter_post ("progress");
		$description = give_parameter_post ("description");
		$last_update = date('Y-m-d H:i:s');
		$sql_update = "UPDATE ttodo SET id_task = $id_task, priority = '$priority', progress = '$progress', description = '$description', last_update = '$last_update' WHERE id = $id_todo";
		$result=mysql_query($sql_update);
		if (! $result)
			echo "<h3 class='error'>".__('Not updated. Error updating data')."</h3>";
		else
			echo "<h3 class='suc'>".__('Updated successfully')."</h3>";
        mail_todo (1, $id_todo);
		$operation = "";
	}

	// ---------------
	// DELETE new todo
	// ---------------
	if ($operation == "delete") {
		$id_todo = give_parameter_get ("id");
		$row = get_db_row ("ttodo", "id", $id_todo);
		if (($row["assigned_user"] != $id_user) AND ($row["created_by_user"] != $id_user)){
			no_permission();
		}
		$assigned_user = $row["assigned_user"];
		$created_by_user = $row["created_by_user"];
		$progress = $row["progress"];
		$name = $row["name"];
		$description = $row["description"];
		$priority = $row["priority"];
		$sql_delete= "DELETE FROM ttodo WHERE id = $id_todo";
        mail_todo (2, $id_todo);
		$result=mysql_query($sql_delete);
		if (! $result)
			echo "<h3 class='error'>".__('Not deleted. Error deleting data')."</h3>";
		else
			echo "<h3 class='suc'>".__('Deleted successfully')."</h3>";
		$operation = "";
	}

	// ---------------
	// UPDATE todo (form)
	// ---------------
	if ($operation == "update") {
		$id_todo = give_parameter_get ("id");
		$row = get_db_row ("ttodo", "id", $id_todo);
		if (($row["assigned_user"] != $id_user) AND ($row["created_by_user"] != $id_user)){
			no_permission();
		}
		$assigned_user = $row["assigned_user"];
		$created_by_user = $row["created_by_user"];
		$progress = $row["progress"];
		$name = $row["name"];
		$description = $row["description"];
		$priority = $row["priority"];
		$id_task = $row["id_task"];
		
		echo '<table class="databox"  width="700">';
		echo "<form name='todou' method='post' action='index.php?sec=todo&sec2=operation/todo/todo&operation=update2&id=$id_todo'>";
		
		// Priority
		echo "<tr><td class='datos2'>".__('Priority');
		echo "<td class='datos2'><select name='priority'>";
		echo "<option value='$priority'>".render_priority($priority);
		for ($ax=0; $ax < 5; $ax++)
			echo "<option value='$ax'>".render_priority($ax);
		echo "</select>";

		// Progress
		echo "<tr><td class='datos'>".__('Progress');
		echo "<td class='datos'><select name='progress'>";
		echo "<option value='$progress'>".$progress." %";
		for ($ax=0; $ax < 11; $ax++)
			echo "<option value='". ($ax * 10) ."'>".($ax*10)." %";
		echo "</select>";

		// Description
		echo "<tr><td class='datos2' valign='top'>".__('Description');
		echo "<td class='datos2'><textarea name='description' style='width:100%; height:250px' >";
		echo $description;
		echo "</textarea>";

		// Task
		echo "<tr><td class='datos'>".__('Task');
		echo "<td class='datos' valign='top'>";
		echo combo_task_user_participant ($config["id_user"],0 ,$id_task);
		echo "</table>";

		// Submit
		echo '<div class="button" style="height: 700px">';
		echo "<input name='crtbutton' type='submit' class='sub' value='".__('Update')."'>";
		echo '</form></div>';
	}

	// ---------------
	// CREATE new todo (form)
	// ---------------
	if ($operation == "create") {
		echo '<table class="databox" width="700">';
		echo '<form name="ilink" method="post" action="index.php?sec=todo&sec2=operation/todo/todo&operation=create2">';

		echo "<tr><td class='datos'>".__('To-Do');
		echo "<td class='datos'><input name='name' size=40>";

		echo "<tr><td class='datos2'>".__('Priority');
		echo "<td class='datos2'><select name='priority'>";
		for ($ax=0; $ax < 5; $ax++)
			echo "<option value='$ax'>".render_priority($ax);
		echo "</select>";

		echo "<tr><td class='datos'>".__('Progress');
		echo "<td class='datos'><select name='progress'>";
		for ($ax=0; $ax < 11; $ax++)
			echo "<option value='". ($ax * 10) ."'>".($ax*10)." %";
		echo "</select>";

		echo "<tr><td class='datos2'>".__('Assigned to user');
		echo "<td class='datos2'>";	
        combo_user_visible_for_me ($config["id_user"],"user",0, "");

		// Task
		echo "<tr><td class='datos'>".__('Task');
		echo "<td class='datos' valign='top'>";
		echo combo_task_user_participant ($config["id_user"],0 ,0, true);
		echo "<tr><td class='datos2' valign='top'>".__('Description');
		echo "<td class='datos2'><textarea name='description' style='width:100%; height:230px'>";
		echo "</textarea>";
		echo "</table>";

		echo '<div class="button" style="width: 700px">';
		echo "<input name='crtbutton' type='submit' class='sub next' value='".__('Create')."'>";
		echo '</form></div>';
	}

	// -------------------------
	// TODO VIEW of my OWN items
	// -------------------------
	if (($operation == "") OR ($operation == "notme")){
		if ($operation == "notme")
			echo "<h1>".__('To-Do management'). " - ". __('Assigned to other users')."</h1>";
		else
			echo "<h1>".__('To-Do management')."</h1>";
		echo "<table class='listing' width=760>";
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
		if ($operation == "notme")
			$sql1="SELECT * FROM ttodo WHERE created_by_user = '$id_user' AND assigned_user != '$id_user'ORDER BY priority DESC";
		else
			$sql1="SELECT * FROM ttodo WHERE assigned_user = '$id_user' ORDER BY priority DESC";
		$result=mysql_query($sql1);
		while ($row=mysql_fetch_array($result)){
			
			echo "<tr><td>";
			echo "<a href='index.php?sec=todo&sec2=operation/todo/todo&operation=update&id=".$row["id"]."'>";
			echo $row["name"];
			echo "</A>";
			
			if (strlen($row["description"]) > 0){
				echo "<a href='#' class='tip'>&nbsp;<span>";
				echo clean_output_breaks($row["description"]);
				echo "</span></a>";
			}
			
			echo '<td align="center">';
			echo render_priority ($row["priority"]);
			echo '<td align="center">';
			$completion = $row["progress"];
			echo "<img src='include/functions_graph.php?type=progress&width=80&height=20&percent=$completion'>";
			echo '<td valign="middle">';
			if ($operation == "notme") 
				$avatar = get_db_value ("avatar", "tusuario", "id_usuario", $row["assigned_user"]);
			else
				$avatar = get_db_value ("avatar", "tusuario", "id_usuario", $row["created_by_user"]);
			echo "<img align='middle' src='images/avatars/".$avatar."_small.png'> ";
			echo "<a href='#' class='tip'><span>";
			if ($operation == "notme")
				echo $row["assigned_user"];
			else
				echo $row["created_by_user"];
			echo "</span></a>";
			//echo '<td class="'.$tdcolor.'f9">';
			//echo  human_time_comparation ($row["timestamp"]);
			echo '<td class="f9">';
			echo human_time_comparation ($row["last_update"]);
			// Close and assign WU to associate task
			echo '<td align="center">';
			if ($row["id_task"] > 0){
				$id_project = get_db_value ("id_project", "ttask", "id", $row["id_task"]);
				$myurl = "index.php?sec=projects&sec2=operation/projects/task_create_work&id_project=$id_project&id_task=".$row["id_task"];
				echo '<a href="'.$myurl.'"><img border=0 src="images/award_star_silver_1.png"></a>';			
			}
			// DELETE
			echo '<td align="center">';
			echo '<a href="index.php?sec=todo&sec2=operation/todo/todo&operation=delete&id='.$row["id"].'" onClick="if (!confirm(\' '.__('Are you sure?').'\')) return false;"><img border=0 src="images/cross.png"></a>';
			
		}
		echo "</table>";
	} // Fin bloque else
}

?>
