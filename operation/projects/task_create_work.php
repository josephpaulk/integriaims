<?PHP

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// ADD WORK UNIT CONTROL ( TASK )
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

global $config;
if (check_login() != 0) {
	audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access","Trying to access task workunit assignment");
	require ("general/noaccess.php");
	exit;
}

	$id_task = give_parameter_get ("id_task", -1);
	if ($id_task != -1)
		$task_name = give_db_value ("name", "ttask", "id", $id_task);
	else
		$task_name = "";
	$id_project = give_parameter_get ("id_project", -1);
	echo "<h3><img src='images/award_star_silver_1.png'>&nbsp;&nbsp;";
	echo $lang_label["add_workunit"]." - $task_name</h3>";

	$ahora_date = date("Y-m-d");
	$ahora_time = date("H:i:s");
	$ahora = date("Y-m-d H:i:s");
	
	echo "<table cellpadding=3 cellspacing=3 border=0 width='100%' class='databox_color' >";
	echo "<form name='nota' method='post' action='index.php?sec=projects&sec2=operation/projects/task_workunit&operation=workunit&id_task=$id_task&id_project=$id_project'>";

	echo "<tr><td class='datos' width='140'><b>".$lang_label["date"]."</b></td>";
	echo "<td class='datos'>";

	echo "<input type='text' id='date' name='date' size=10 value='$ahora_date'> <img src='images/calendar_view_day.png' onclick='scwShow(scwID(\"date\"),this);'> ";

	echo "&nbsp;&nbsp;";
	echo "<input type='text' name='time' size=10 value='$ahora_time'>";

	echo "<tr><td class='datos2'  width='140'>";
	echo "<b>".$lang_label["profile"]."</b>";
	echo "<td class='datos2'>";
	echo combo_user_task_profile ($id_task);
	echo "&nbsp;&nbsp;";
	echo "<input type='checkbox' name='have_cost' value=1>";
	echo "&nbsp;&nbsp;";
	echo "<b>".$lang_label["have_cost"]."</b>";

	echo "<tr><td class='datos'>";
	echo "<b>".$lang_label["time_used"]."</b>";
	echo "<td class='datos'>";
	echo "<input type='text' name='duration' value='0' size='7'>";
	
	echo '<tr><td colspan="2" class="datos2"><textarea name="description" style="height: 250px; width: 100%;">';
	echo '</textarea>';
	echo "</tr></table>";
	echo "<table width=100%>";
	echo "<tr><td align=right>";
	echo '<input name="addnote" type="submit" class="sub next" value="'.$lang_label["add"].'">';
	echo "</td></tr></table>";
	echo "</form>";

?>