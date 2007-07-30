<?PHP

// Returns a combo with valid profiles for CURRENT user in this task
// ----------------------------------------------------------------------
function combo_user_task_profile ($id_task){
	$current_user = $_SESSION["id_usuario"];
	// Show only users assigned to this project
	$sql = "SELECT * FROM trole_people_task  WHERE id_task = $id_task AND id_user = '$current_user'";
	$result = mysql_query($sql);
	echo "<select name='work_profile'>";
	while ($row=mysql_fetch_array($result)){
		echo "<option value='".$row["id_role"]."'>".give_db_value ("name","trole","id",$row["id_role"]);
	}
	echo "</select>";
}


// Returns a combo with the users that belongs to a project
// ----------------------------------------------------------------------
function combo_users_task ($id_task){
	// Show only users assigned to this project
	$sql = "SELECT * FROM trole_people_task WHERE id_task = $id_task";
	$result = mysql_query($sql);
	echo "<select name='user' style='width: 100px;'>";
	while ($row=mysql_fetch_array($result)){
		echo "<option value='".$row["id"]."'>".$row["id_user"]." / ".give_db_value ("name","trole","id",$row["id_role"]);
	}
	echo "</select>";
}

// Returns a combo with the users that belongs to a project
// ----------------------------------------------------------------------
function combo_users_project ($id_project){
	// Show only users assigned to this project
	$sql = "SELECT * FROM trole_people_project WHERE id_project = $id_project";
	$result = mysql_query($sql);
	echo "<select name='user' style='width: 100px;'>";
	while ($row=mysql_fetch_array($result)){
		echo "<option value='".$row["id"]."'>".$row["id_user"]." / ".give_db_value ("name","trole","id",$row["id_role"]);
	}
	echo "</select>";
}


// Returns a combo with the users available
// ----------------------------------------------------------------------
function combo_users ($actual = "") {
	echo "<select name='user'>";
	if ($actual != ""){ // Show current option
		echo "<option>".$actual;
	}
	$sql = "SELECT * FROM tusuario WHERE id_usuario != '$actual'";
	$result=mysql_query($sql);
	while ($row=mysql_fetch_array($result)){
		echo "<option>".$row["id_usuario"];
	}
	echo "</select>";
}


// Returns a combo with the groups available
// $mode is one ACL for access, like "IR", "AR", or "TW"
// ----------------------------------------------------------------------
function combo_groups ($actual = -1, $mode = "IR") {
	global $config;
	echo "<select name='group'>";
	if ($actual != -1){
		$sql = "SELECT * FROM tgrupo WHERE id_grupo = $actual";
		$result = mysql_query($sql);
		if ($row=mysql_fetch_array($result)){
			echo "<option value='".$row["id_grupo"]."'>".$row["nombre"];
		}
	}
	$sql="SELECT * FROM tgrupo WHERE id_grupo != $actual";
	$result=mysql_query($sql);
	while ($row=mysql_fetch_array($result)){
		if (give_acl ($config["id_user"], $row["id_grupo"], $mode) == 1)
			echo "<option value='".$row["id_grupo"]."'>".$row["nombre"];
	}
	echo "</select>";
}

// Returns a combo with the incident status available 
// ----------------------------------------------------------------------
function combo_incident_status ($actual = -1, $disabled = 0, $only_actual = 0) {
	if ($disabled != 0)
		echo "<select name='incident_status' disabled>";
	else			
		echo "<select name='incident_status'>";

	if ($only_actual != 0){
		$sql = "SELECT * FROM tincident_status WHERE id = $actual"; 
		$result = mysql_query($sql);
		if ($row=mysql_fetch_array($result)){
			echo "<option value='".$row["id"]."'>".$row["name"];
		}
	} else {
		if ($actual != -1){
			$sql = "SELECT * FROM tincident_status WHERE id = $actual";
			$result = mysql_query($sql);
			while ($row=mysql_fetch_array($result)){
				echo "<option value='".$row["id"]."'>".$row["name"];
			}
		}
		$sql = "SELECT * FROM tincident_status WHERE id != $actual";
		$result = mysql_query($sql);
		while ($row=mysql_fetch_array($result)){
			echo "<option value='".$row["id"]."'>".$row["name"];
		}
	}
	echo "</select>";
}

// Returns a combo with the incident origin
// ----------------------------------------------------------------------
function combo_incident_origin ($actual = -1, $disabled = 0) {
	if ($disabled != 0)
		echo "<select name='incident_origin' disabled>";
	else 
		echo "<select name='incident_origin'>";
	if ($actual != -1){
		$sql = "SELECT * FROM tincident_origin WHERE id = $actual";
		$result = mysql_query($sql);
		if ($row=mysql_fetch_array($result)){
			echo "<option value='".$row["id"]."'>".$row["name"];
		}
	}
	$sql = "SELECT * FROM tincident_origin WHERE id != $actual";
	$result = mysql_query($sql);
	while ($row=mysql_fetch_array($result)){
		echo "<option value='".$row["id"]."'>".$row["name"];
	}
	echo "</select>";
}

// Returns a combo with the incident resolution
// ----------------------------------------------------------------------
function combo_incident_resolution ($actual = -1) {
	echo "<select name='incident_resolution' style='width=120px;'>";
	if ($actual != -1){
		$sql = "SELECT * FROM tincident_resolution WHERE id = $actual";
		$result = mysql_query($sql);
		if ($row=mysql_fetch_array($result)){
			echo "<option value='".$row["id"]."'>".$row["name"];
		}
	}
	$sql = "SELECT * FROM tincident_resolution WHERE id != $actual";
	$result = mysql_query($sql);
	while ($row=mysql_fetch_array($result)){
		echo "<option value='".$row["id"]."'>".$row["name"];
	}
	echo "</select>";
}

// Returns a combo with the tasks that current user could see
// ----------------------------------------------------------------------
function combo_task_user ($actual = 0, $id_user, $disabled = 0, $show_vacations = 0) {
	global $config;
	global $lang_label;

	if ($disabled == 0)
		echo "<select name='task_user'>";
	else 
		echo "<select name='task_user' disabled>";

	if ($show_vacations == 1)
		echo "<option value=-1>".$lang_label["vacations"];
	
	if ($actual != 0){
		$sql = "SELECT * FROM ttask WHERE id = $actual";
		$result = mysql_query($sql);
		if ($row=mysql_fetch_array($result)){
			echo "<option value='".$row["id"]."'>".substr($row["name"],0,35);
		}
	} 

	echo "<option value=0>".$lang_label["N/A"];
	$sql = "SELECT ttask.id, ttask.name FROM ttask, tproject WHERE ttask.id != $actual AND ttask.id_project = tproject.id";

	$result = mysql_query($sql);
	while ($row=mysql_fetch_array($result)){
		echo "<option value='".$row[0]."'>".substr($row[1],0,35);
	}
	echo "</select>";
}

// Returns a combo with the tasks that current user is working on
// ----------------------------------------------------------------------
function combo_task_user_participant ($id_user, $show_vacations = 0) {
	global $config;
	global $lang_label;
	
	echo "<select name='task'>";
	if ($show_vacations == 1)
		echo "<option value=-1>".$lang_label["vacations"];
	$sql = "SELECT DISTINCT (ttask.id) FROM ttask, trole_people_task WHERE ttask.id = trole_people_task.id_task AND trole_people_task.id_user = '$id_user'";
	$result = mysql_query($sql);
	while ($row=mysql_fetch_array($result)){
		$id = $row[0];
		$task_name = give_db_value ("name", "ttask", "id", $id);
		echo "<option value='$id'>$task_name";
	}
	echo "</select>";
}

// Returns a combo with the available roles
// ----------------------------------------------------------------------
function combo_roles ($include_na = 0) {
	global $config;
	global $lang_label;
	
	echo "<select name='role'>";
	if ($include_na == 1)
		echo "<option value=0>".$lang_label["N/A"];
	$sql = "SELECT * FROM trole";
	$result=mysql_query($sql);
	while ($row=mysql_fetch_array($result)){
		echo "<option value='".$row["id"]."'>".$row["name"];
	}
	echo "</select>";
}

function show_workunit_data ($row3, $title) {
	global $config;
	global $lang_label;

	$timestamp = $row3["timestamp"];
	$duration = $row3["duration"];
	$id_user = $row3["id_user"];
	$avatar = give_db_value ("avatar", "tusuario", "id_usuario", $id_user);
	$nota = $row3["description"];
	$id_workunit = $row3["id"];

	// Show data
	echo "<div class='notetitle'>"; // titulo
	echo "<span>";
	echo "<img src='images/avatars/".$avatar."_small.png'>&nbsp;";
	echo " <a href='index.php?sec=users&sec2=operation/users/user_edit&ver=$id_user'>";
	echo $id_user;
	echo "</a>";
	echo "&nbsp;".$lang_label["said_on"]."&nbsp;";
	echo $timestamp;
	echo "</span>";
	echo "<span style='float:right; margin-top: -15px; margin-bottom:0px; padding-right:10px;'>";
	echo $duration;
	echo "&nbsp; ".$lang_label["hr"];
	echo "</span>";
	echo "</div>";

	// Body
	echo "<div class='notebody'>";
	if (strlen($nota) > 1024){
		echo clean_output_breaks(substr($nota,0,1024));
		echo "<br><br>";
		echo "<a href='index.php?sec=incidents&sec2=operation/common/workunit_detail&id=".$id_workunit."&title=$title'>";
		echo $lang_label["read_more"];
		echo "</a>";
	} else {
		echo clean_output_breaks($nota);
	}
	echo "</div>";
}

?>
