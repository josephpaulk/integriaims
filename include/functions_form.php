<?PHP

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
function combo_task_user ($actual = 0, $id_user, $disabled = 0) {
	global $config;
	global $lang_label;

if ($disabled == 0)
		echo "<select name='task_user'>";
	else 
		echo "<select name='task_user' disabled>";

	if ($actual != 0){
		$sql = "SELECT * FROM ttask WHERE id = $actual";
		$result = mysql_query($sql);
		if ($row=mysql_fetch_array($result)){
			echo "<option value='".$row["id"]."'>".substr($row["name"],0,35);
		}
	} 

	echo "<option value=0>".$lang_label["N/A"];
	$sql = "SELECT ttask.id, ttask.name, tproject.id_group FROM ttask, tproject WHERE ttask.id != $actual AND ttask.id_project = tproject.id";
	$result = mysql_query($sql);
	while ($row=mysql_fetch_array($result)){
		$id_group = $row[2];
		//if (give_acl($config["id_user"], $id_group, "TR")==1){
			echo "<option value='".$row[0]."'>".substr($row[1],0,35);
		//}
	}
	echo "</select>";

}


// Returns a combo with the available roles
// ----------------------------------------------------------------------
function combo_roles () {
	echo "<select name='role'>";
	$sql = "SELECT * FROM trole";
	$result=mysql_query($sql);
	while ($row=mysql_fetch_array($result)){
		echo "<option value='".$row["id"]."'>".$row["name"];
	}
	echo "</select>";
}


?>
