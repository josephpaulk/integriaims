<?php

// Integria 1.0 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2008 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// Load globar vars
global $config;

check_login ();

if (! give_acl($config["id_user"], 0, "UM")) {
	audit_db ($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation", "Trying to access User Management");
	require ("general/noaccess.php");
	exit;
}

//  INSERTION
if (isset($_POST["create"])){ // If create
	$name = get_parameter ('name');
	$description = get_parameter ('description');
	$cost = get_parameter ('cost');
	$sql_insert="INSERT INTO trole (name,description,cost) VALUES ('$name','$description','$cost') ";
	$result=mysql_query($sql_insert);	
	if (! $result)
		echo "<h3 class='error'>".__('create_no')."</h3>";
	else {
		echo "<h3 class='suc'>".__('create_ok')."</h3>";
		$id = mysql_insert_id();
	}
}

// UPDATE
if (isset($_POST["update"])){ // if update
	$id = (int) get_parameter ('id');
	$name = get_parameter ('name');
	$description = get_parameter ('description');
	$cost = get_parameter ('cost');
	$sql_update = "UPDATE trole SET
					cost = '$cost', name = '".$name."',
					description = '$description'
				   WHERE id = '$id'";
	$result=mysql_query($sql_update);
	if (! $result)
		echo "<h3 class='error'>".__('modify_no')."</h3>";
	else
		echo "<h3 class='suc'>".__('modify_ok')."</h3>";
}

// DELETE
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
if (isset($_GET["borrar"])){ // if delete
	$id = get_parameter ('borrar');
	// Role 1 cannot be deleted (project manager)
	if ($id) { 
		$sql_delete= "DELETE FROM tprofile WHERE id = ".$id;
		$result=mysql_query($sql_delete);
		if (! $result)
			echo "<h3 class='error'>".__('delete_no')."</h3>";
		else
			echo "<h3 class='suc'>".__('delete_ok')."</h3>";
	} else 
		echo "<h3 class='error'>".__('delete_no')."</h3>";

}

// EDIT ROLE
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
if ((isset($_GET["form_add"])) or (isset($_GET["form_edit"]))){
	if (isset($_GET["form_edit"])){
		$creation_mode = 0;
			$id = get_parameter ('id');
			$sql1='SELECT * FROM trole WHERE id = '.$id;
			$result=mysql_query($sql1);
			if ($row=mysql_fetch_array($result)){
					$name = $row["name"];
					$description = $row["description"];
					$cost = $row["cost"];
				}
			else echo "<h3 class='error'>".__('name_error')."</h3>";
	} else { // form_add
		$creation_mode =1;
		$name = "";
		$description = "No description";
		$cost = 0;
	}

	// Create link
	echo "<h2>".__('setup_screen')."</h2>";
	echo "<h3>".__('link_management')."</h3>";
	echo '<table class="fon" cellpadding="3" cellspacing="3" width="500" class="databox_color">';
	echo '<form name="ilink" method="post" action="index.php?sec=users&sec2=godmode/usuarios/role_manager">';
	if ($creation_mode == 1){
		echo "<input type='hidden' name='create' value='1'>";
	} else {
		echo "<input type='hidden' name='update' value='1'>";
		echo "<input type='hidden' name='id' value='$id'>";
	}
	
	echo '<tr><td class="datos">'.__('role').'<td class="datos"><input type="text" name="name" size="25" value="'.$name.'">';
	
	echo '<tr><td class="datos2">'.__('description').'<td class="datos2"><input type="text" name="description" size="55" value="'.$description.'">';

	echo '<tr><td class="datos">'.__('cost').'<td class="datos"><input type="text" name="cost" size="6" value="'.$cost.'">';
	echo "</table>";
	echo '<table class="fon" cellpadding="3" cellspacing="3" width="500">';
	echo "<tr><td align='right'>";
	echo "<input name='crtbutton' type='submit' class='sub next' value='".__('update')."'>";
	echo '</form></table>';
}

// Role viewer
// ~~~~~~~~~~~~~~~~~~~~~~~4
else {  // Main list view for Links editor
	echo "<h2>".__('role_management')."</h2>";
	echo "<table cellpadding=4 cellspacing=4 width=700 class='listing'>";
	echo "<th>".__('name');
	echo "<th>".__('description');
	echo "<th>".__('cost');
	echo "<th>".__('delete');
	$sql1='SELECT * FROM trole ORDER BY id';
	$result=mysql_query($sql1);
	$color=1;
	while ($row=mysql_fetch_array($result)){
		if ($color == 1){
			$tdcolor = "datos";
			$color = 0;
		}
		else {
			$tdcolor = "datos2";
			$color = 1;
		}
		echo "<tr><td valign='top' class='$tdcolor'><b><a href='index.php?sec=users&sec2=godmode/usuarios/role_manager&form_edit=1&id=".$row["id"]."'>".$row["name"]."</a></b>";
		echo '<td valign="top" class="'.$tdcolor.'">'.$row["description"];
		echo '<td valign="top" class="'.$tdcolor.'" align="center">'.$row["cost"];
		echo '<td valign="top" class="'.$tdcolor.'" align="center">';
		if ($row["id"] >1){
			echo '<a href="index.php?sec=users&sec2=godmode/usuarios/role_manager&id='.$row["id"].'&delete='.$row["id"].'" onClick="if (!confirm(\' '.__('are_you_sure').'\')) return false;"><img border=0 src="images/cross.png"></a>';
		}
	}
	echo "</table>";
	echo "<table cellpadding=4 cellspacing=4 width=700>";
	echo "<tr><td align='right'>";
	echo "<form method='post' action='index.php?sec=users&sec2=godmode/usuarios/role_manager&form_add=1'>";
	echo "<input type='submit' class='sub create' name='form_add' value='".__('add')."'>";
	echo "</form></table>";
} // Fin bloque else

?>
