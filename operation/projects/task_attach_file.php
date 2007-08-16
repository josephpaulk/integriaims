<?PHP

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// ADD FILE CONTROL
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

	global $config;
	if (check_login() != 0) {
		audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access","Trying to access task file attach tool");
		require ("general/noaccess.php");
		exit;
	}

	$id_task = give_parameter_get ("id_task", -1);
	if ($id_task != -1)
		$task_name = give_db_value ("name", "ttask", "id", $id_task);
	else
		$task_name = "";
	$id_project = give_parameter_get ("id_project", -1);
	
	echo "<h3><img src='images/disk.png'>&nbsp;&nbsp;";
	echo $lang_label["upload_file"]." - $task_name</A></h3>";

	echo "<div id='upload_control'>";
	echo "<table cellpadding=4 cellspacing=4 border=0 width='700' class='databox_color'>";
	echo "<tr>";
	echo '<td class="datos">'.$lang_label["filename"].'</td><td class="datos">';
	echo "<form method='post' action='index.php?sec=projects&sec2=operation/projects/task_files&id_task=$id_task&id_project=$id_project&operation=attachfile' enctype='multipart/form-data'>";
	echo '<input type="file" name="userfile" value="userfile" class="sub" size="40">';
	echo '<tr><td class="datos2">'.$lang_label["description"].'</td><td class="datos2" colspan=3><input type="text" name="file_description" size=47>';
	echo "</td></tr></table>";
	echo '<input type="submit" name="upload" value="'.$lang_label["upload"].'" class="sub next">';
	echo "</form>";
	echo '</div><br>';


?>