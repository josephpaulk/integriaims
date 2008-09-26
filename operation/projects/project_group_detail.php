<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

/*
CREATE TABLE `tproject_group` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(100) NOT NULL default '',
  `icon` varchar(50) default NULL,
  PRIMARY KEY  (`id`)
);*/

global $config;

check_login();

if (give_acl($config["id_user"], 0, "IM")==0) {
	audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation","Trying to access company role management");
	require ("general/noaccess.php");
	exit;
}

$id_user = $config["id_user"];

// =======================
// CREATE
// =======================

if (isset($_GET["create2"])){ //

	$name = get_parameter ("name","");
	$icon= get_parameter ("icon","");
	$sql_insert="INSERT INTO tproject_group (`name`, `icon` ) VALUE ('$name', '$icon') ";

	$result=mysql_query($sql_insert);
	if (! $result)
		echo "<h3 class='error'>".lang_string ("Project group cannot be created")."</h3>";
	else {
		echo "<h3 class='suc'>".lang_string ("Project group has been created successfully")."</h3>";
		$id_data = mysql_insert_id();
		insert_event ("PROJECT GROUP CREATED", $id_data, 0, $name);
	}
}

// =======================
// UPDATE
// =======================

if (isset($_GET["update2"])){ // if modified any parameter
	$id = get_parameter ("id","");
	$name = get_parameter ("name","");
	$icon = get_parameter ("icon","");

	$sql_update ="UPDATE tproject_group
	SET icon = '$icon', name = '$name' WHERE id = $id";

	$result=mysql_query($sql_update);
	if (! $result)
		echo "<h3 class='error'>".lang_string ("Project group cannot be updated")."</h3>";
	else {
		echo "<h3 class='suc'>".lang_string ("Project updated ok")."</h3>";
		insert_event ("PROJECT GROUP UPDATED", $id, 0, $name);
	}
}
// =======================
// DELETE
// =======================

if (isset($_GET["delete"])){ // if delete
	$id = get_parameter ("delete",0);
	$name = give_db_sqlfree_field  ("SELECT name FROM tproject_group WHERE id = $id ");
	$sql_delete= "DELETE FROM tproject_group WHERE id = $id";
	$result=mysql_query($sql_delete);
	insert_event ("PROJECT GROUP DELETED", $id, 0, "$name");
	echo "<h3 class='suc'>".lang_string("Deleted successfully")."</h3>";
}

if (isset($_GET["update2"])){
	// After apply update, let's redirecto to UPDATE form again.
	$_GET["update"]= $id;
}


// =======================
// FORM (Update / Create)
// =======================

if ((isset($_GET["create"]) OR (isset($_GET["update"])))) {
	if (isset($_GET["create"])){
		$id = -1;
		$name = "";
		$icon = "";
		$description = "";
	} else {
		$id = get_parameter ("update", -1);
		$row = get_db_row ("tproject_group", "id", $id);
		$name = $row["name"];
		$icon = $row["icon"];
	}

	echo "<h2>".lang_string ("Project group management")."</h2>";
	if ($id == -1){
		echo "<h3>".lang_string ("Create a project group")."</a></h3>";
		echo "<form method='post' action='index.php?sec=projects&sec2=operation/projects/project_group_detail&create2=1'>";
	}
	else {
		echo "<h3>".lang_string ("Update project group")."</a></h3>";
		echo "<form method='post' action='index.php?sec=projects&sec2=operation/projects/project_group_detail&update2=1'>";
		print_input_hidden ("id", "$id", false, '');
	}

	echo "<table width=620 class='databox'>";
	echo "<tr>";
	echo "<td class=datos>";
	echo lang_string ("Project group name");

	echo "<tr>";
	echo "<td class=datos colspan=4>";
	print_input_text ("name", $name, "", 60, 100, false);


	echo "<tr>";
	echo "<td class=datos>";
	echo lang_string ("Icon");

	echo "<tr>";
	echo "<td class=datos colspan=4>";
	$ficheros = list_files ('images/project_groups_small/', "png", 1, 0, 'svn');
	echo print_select ($ficheros, "icon", $icon, '', '', 0, true, 0, false, false);
	echo "</table>";

	echo "<table width=620 class='button'>";
	echo "<tr>";
	echo "<td class='datos3' align=right>";
	if ($id == -1)
		print_submit_button (lang_string("Create"), "enviar", false, "class='sub next'", false);
	else
		print_submit_button (lang_string("Update"), "enviar", false, "class='sub upd'", false);
	echo "</table>";
	echo "</form>";

	// Get some space here
	echo "<div style='min-height:50px'></div>";
}

// =======================
// Show LIST of items
// =======================
if ((!isset($_GET["update"])) AND (!isset($_GET["create"]))){
	echo "<h2>".lang_string ("Project groups")."</h2>";

	$sql1 = "SELECT * FROM tproject_group ORDER BY name";
	$color =0;
	if (($result=mysql_query($sql1)) AND (mysql_num_rows($result) >0)){

		$table->width = "720";
		$table->class = "listing";
		$table->cellspacing = 0;
		$table->cellpadding = 0;
		$table->data = array ();
		$table->size = array ();
		$table->style = array ();
		$table->colspan = array ();
		$table->head[0] = lang_string ("Name");
		$table->head[1] = lang_string ("Icon");
		$table->head[2] = lang_string ("Delete");
		$counter = 0;
		while ($row=mysql_fetch_array($result)){
			// Name
			$table->data[$counter][0] = "<b><a href='index.php?sec=projects&sec2=operation/projects/project_group_detail&update=".$row["id"]."'>".$row["name"]."</a></b>";

			// Contracts (link to new window)
			$table->data[$counter][1] = "<img src='images/project_groups_small/".$row["icon"]."' border=0>";

			// Delete
			$table->data[$counter][2] = "<a href='index.php?sec=projects&
						sec2=operation/projects/project_group_detail&
						delete=".$row["id"]."'
						onClick='if (!confirm(\' ".$lang_label["are_you_sure"]."\'))
						return false;'>
						<img border='0' src='images/cross.png'></a>";
			$counter++;
		}
		print_table ($table);
	}
	echo "<table width=720 class='button'>";
	echo "<tr><td align='right'>";
	echo "<form method=post action='index.php?sec=projects&
	sec2=operation/projects/project_group_detail&create=1'>";
	echo "<input type='submit' class='sub next' name='crt' value='".lang_string("Create group")."'>";
	echo "</form></td></tr></table>";
} // end of list
?>
