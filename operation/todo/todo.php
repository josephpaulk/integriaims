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
		$sql_insert="INSERT INTO ttodo (name, priority, assigned_user, created_by_user, progress, timestamp, last_update, description, id_task ) VALUES ('$name',$priority, '$assigned_user', '$id_user', '$progress', '$timestamp', '$last_updated', '$description', $id_task)";

		$result=mysql_query($sql_insert);	
		if (! $result)
			echo "<h3 class='error'>".$lang_label["create_no"]."</h3>";
		else {
			echo "<h3 class='suc'>".$lang_label["create_ok"]."</h3>"; 
			$id_todo = mysql_insert_id();
		}
		$myurl = topi_quicksession ("/index.php?sec=todo&sec2=operation/todo/todo");
		$msgtext = "A new To-Do has been created by user [$id_user] for user [$assigned_user]. Todo information is:\n\nTitle   : $name\nPriority: $priority\nDescription: $description\n\nFor more information please visit ".$myurl;
		if ($id_user != $assigned_user){
			topi_sendmail (return_user_email($id_user), "[TOPI] New ToDo item has been created", $msgtext);
			topi_sendmail (return_user_email($assigned_user), "[TOPI] New ToDo item has been created", $msgtext);
		} else
			topi_sendmail (return_user_email($assigned_user), "[TOPI] New ToDo item has been created", $msgtext);

		$operation = "";
	}

	// ---------------
	// UPDATE new todo
	// ---------------
	if ($operation == "update2") {
		$id_todo = give_parameter_get ("id");
		$row = give_db_row ("ttodo", "id", $id_todo);
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
			echo "<h3 class='error'>".$lang_label["modify_no"]."</h3>";
		else
			echo "<h3 class='suc'>".$lang_label["modify_ok"]."</h3>";
		$myurl = topi_quicksession ("/index.php?sec=todo&sec2=operation/todo/todo");
		$msgtext = "A To-Do has been modified by user [$id_user]. Todo information is:\n\nTitle   : $name\nPriority: $priority\nProgress: $progress\nDescription: $description\n\nFor more information please visit ".$myurl;
		if ($id_user != $created_by_user){
			topi_sendmail (return_user_email($id_user), "[TOPI] ToDo '$name' has been updated", $msgtext);
			topi_sendmail (return_user_email($created_by_user), "[TOPI] ToDo '$name' has been updated", $msgtext);
		} else 
			topi_sendmail (return_user_email($created_by_user), "[TOPI] ToDo '$name' has been updated", $msgtext);
		$operation = "";
	}

	// ---------------
	// DELETE new todo
	// ---------------
	if ($operation == "delete") {
		$id_todo = give_parameter_get ("id");
		$row = give_db_row ("ttodo", "id", $id_todo);
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
		$myurl = topi_quicksession ("/index.php?sec=todo&sec2=operation/todo/todo");
		$msgtext = "A To-Do has been deleted by user [$id_user]. Todo information was:\n\nTitle   : $name\nPriority: $priority\nProgress: $progress\nDescription: $description\n\nFor more information please visit ".$myurl;
		if ($id_user != $created_by_user){
                        topi_sendmail (return_user_email($id_user), "[TOPI] ToDo '$name' has been deleted", $msgtext);
                        topi_sendmail (return_user_email($created_by_user), "[TOPI] ToDo '$name' has been deleted", $msgtext);
                } else
                        topi_sendmail (return_user_email($created_by_user), "[TOPI] ToDo '$name' has been deleted", $msgtext);

		$result=mysql_query($sql_delete);
		if (! $result)
			echo "<h3 class='error'>".$lang_label["delete_no"]."</h3>";
		else
			echo "<h3 class='suc'>".$lang_label["delete_ok"]."</h3>";
		$operation = "";
	}

	// ---------------
	// UPDATE todo (form)
	// ---------------
	if ($operation == "update") {
		$id_todo = give_parameter_get ("id");
		$row = give_db_row ("ttodo", "id", $id_todo);
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

        	echo "<h2>".lang_string ("todo_update")." - $name </h2>";
		echo '<table class="databox_color" cellpadding="4" cellspacing="4" width="90%">';
		echo "<form name='todou' method='post' action='index.php?sec=todo&sec2=operation/todo/todo&operation=update2&id=$id_todo'>";
		
		// Priority
		echo "<tr><td class='datos2'>".lang_string ("priority");
		echo "<td class='datos2'><select name='priority'>";
		echo "<option value='$priority'>".render_priority($priority);
		for ($ax=0; $ax < 5; $ax++)
			echo "<option value='$ax'>".render_priority($ax);
		echo "</select>";

		// Progress
		echo "<tr><td class='datos'>".lang_string ("progress");
		echo "<td class='datos'><select name='progress'>";
		echo "<option value='$progress'>".$progress." %";
		for ($ax=0; $ax < 11; $ax++)
			echo "<option value='". ($ax * 10) ."'>".($ax*10)." %";
		echo "</select>";

		// Description
		echo "<tr><td class='datos2' valign='top'>".lang_string ("description");
		echo "<td class='datos2'><textarea name='description' style='width:100%; height:250px' >";
		echo $description;
		echo "</textarea>";

		// Task
		echo "<tr><td class='datos'>".lang_string ("task");
		echo "<td class='datos' valign='top'>";
		echo combo_task_user_participant ($config["id_user"],0 ,$id_task);
		echo "</table>";

		// Submit
		echo '<table cellpadding="0" cellspacing="0" width="80%">';
		echo "<tr><td align='right'>";
		echo "<input name='crtbutton' type='submit' class='sub' value='".lang_string ("update")."'>";
		echo '</form></table>';
	}

	// ---------------
	// CREATE new todo (form)
	// ---------------
	if ($operation == "create") {
        echo "<h2>".lang_string ("todo_creation")."</h2>";
		echo '<table class="databox_color" cellpadding="4" cellspacing="4" width="80%">';
		echo '<form name="ilink" method="post" action="index.php?sec=todo&sec2=operation/todo/todo&operation=create2">';

		echo "<tr><td class='datos'>".lang_string ("todo");
		echo "<td class='datos'><input name='name' size=40>";

		echo "<tr><td class='datos2'>".lang_string ("priority");
		echo "<td class='datos2'><select name='priority'>";
		for ($ax=0; $ax < 5; $ax++)
			echo "<option value='$ax'>".render_priority($ax);
		echo "</select>";

		echo "<tr><td class='datos'>".lang_string ("progress");
		echo "<td class='datos'><select name='progress'>";
		for ($ax=0; $ax < 11; $ax++)
			echo "<option value='". ($ax * 10) ."'>".($ax*10)." %";
		echo "</select>";

		echo "<tr><td class='datos2'>".lang_string ("assigned_to_user");
		echo "<td class='datos2'>";
		echo combo_users($id_user);

		// Task
		echo "<tr><td class='datos'>".lang_string ("task");
		echo "<td class='datos' valign='top'>";
		echo combo_task_user_participant ($config["id_user"],0 ,0);
		
		echo "<tr><td class='datos2' valign='top'>".lang_string ("description");
		echo "<td class='datos2'><textarea name='description' style='width:100%; height:230px'>";
		echo "</textarea>";
		echo "</table>";
		echo '<table cellpadding="0" cellspacing="0" width="80%">';
		echo "<tr><td align='right'>";
		echo "<input name='crtbutton' type='submit' class='sub next' value='".lang_string ("create")."'>";
		echo '</form></table>';
	}

	// -------------------------
	// TODO VIEW of my OWN items
	// -------------------------
	if (($operation == "") OR ($operation == "notme")){
		if ($operation == "notme")
			echo "<h1>".$lang_label["todo_management"]. " - ". lang_string("assigned_to_other_users")."</h1>";
		else
			echo "<h1>".$lang_label["todo_management"]."</h1>";
		echo "<table cellpadding=4 cellspacing=4 class='databox_color' width=100%>";
		echo "<th>".lang_string ("todo");
		echo "<th>".$lang_label["priority"];
		echo "<th>".$lang_label["progress"];
		if ($operation == "notme")
			echo "<th>".lang_string ("assigned_to");
		else
			echo "<th>".$lang_label["assigned_by"];
		//echo "<th>".lang_string ("created");
		echo "<th>".lang_string ("updated");
		echo "<th>".lang_string ("task");
		echo "<th>".lang_string ("delete");
		if ($operation == "notme")
			$sql1="SELECT * FROM ttodo WHERE created_by_user = '$id_user' AND assigned_user != '$id_user'ORDER BY priority DESC";
		else
			$sql1="SELECT * FROM ttodo WHERE assigned_user = '$id_user' ORDER BY priority DESC";
		$result=mysql_query($sql1);
		$color=1;
		while ($row=mysql_fetch_array($result)){
			if ($color == 1){
				$tdcolor = "datos";
				$color = 0;
				$tip = "tip";
			}
			else {
				$tdcolor = "datos2";
				$color = 1;
				$tip = "tip2";
			}
			echo "<tr><td class='$tdcolor'>";
			echo "<a href='index.php?sec=todo&sec2=operation/todo/todo&operation=update&id=".$row["id"]."'>";
			echo $row["name"];
			echo "</A>";
			
			if (strlen($row["description"]) > 0){
				echo "<a href='#' class='$tip'>&nbsp;<span>";
				echo clean_output_breaks($row["description"]);
				echo "</span></a>";
			}
			
			echo '<td class="'.$tdcolor.'" align="center">';
			echo render_priority ($row["priority"]);
			echo '<td class="'.$tdcolor.'" align="center">';
			$completion = $row["progress"];
			echo "<img src='include/functions_graph.php?type=progress&width=80&height=20&percent=$completion'>";
			echo '<td class="'.$tdcolor.'" valign="middle">';
			if ($operation == "notme") 
				$avatar = give_db_value ("avatar", "tusuario", "id_usuario", $row["assigned_user"]);
			else
				$avatar = give_db_value ("avatar", "tusuario", "id_usuario", $row["created_by_user"]);
			echo "<img align='middle' src='images/avatars/".$avatar."_small.png'> ";
			echo "<a href='#' class='tip'><span>";
			if ($operation == "notme")
				echo $row["assigned_user"];
			else
				echo $row["created_by_user"];
			echo "</span></a>";
			//echo '<td class="'.$tdcolor.'f9">';
			//echo  human_time_comparation ($row["timestamp"]);
			echo '<td class="'.$tdcolor.'f9">';
			echo human_time_comparation ($row["last_update"]);
			// Close and assign WU to associate task
			echo '<td class="'.$tdcolor.'" align="center">';
			if ($row["id_task"] > 0){
				$id_project = give_db_value ("id_project", "ttask", "id", $row["id_task"]);
				$myurl = "index.php?sec=projects&sec2=operation/projects/task_create_work&id_project=$id_project&id_task=".$row["id_task"];
				echo '<a href="'.$myurl.'"><img border=0 src="images/award_star_silver_1.png"></a>';			
			}
			// DELETE
			echo '<td class="'.$tdcolor.'" align="center">';
			echo '<a href="index.php?sec=todo&sec2=operation/todo/todo&operation=delete&id='.$row["id"].'" onClick="if (!confirm(\' '.$lang_label["are_you_sure"].'\')) return false;"><img border=0 src="images/cross.png"></a>';
			
		}
		echo "</table>";
	} // Fin bloque else
}

?>
