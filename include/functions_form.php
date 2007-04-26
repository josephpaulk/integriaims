<?PHP

// Returns a combo with the users that belongs to a project
// ----------------------------------------------------------------------
function combo_users_project ($id_project){
	echo "<select name='user_project'>";
	$sql='SELECT tproject_user.id_user FROM tproject_user, tusuario WHERE tusuario.id_usuario =  tproject_user.id_user AND tproject_user.id_project = $id_project';
	$result=mysql_query($sql);
	while ($row=mysql_fetch_array($result)){
		echo "<option>".$row[0];
	}
	echo "</select>";
}

// Returns a combo with the users available 
// ----------------------------------------------------------------------
function combo_users ($actual = -1) {
	echo "<select name='user'>";
	$sql='SELECT * FROM tusuario';
	$result=mysql_query($sql);
	while ($row=mysql_fetch_array($result)){
		echo "<option>".$row["id_usuario"];
	}
	echo "</select>";
}


// Returns a combo with the groups available 
// ----------------------------------------------------------------------
function combo_groups ($actual = -1) {
	echo "<select name='group'>";
	$sql='SELECT * FROM tgrupo';
	$result=mysql_query($sql);
	while ($row=mysql_fetch_array($result)){
		echo "<option value='".$row["id_grupo"]."'>".$row["nombre"];
	}
	echo "</select>";
}


?>