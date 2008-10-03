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

global $config;

check_login();

if (give_acl($config["id_user"], 0, "IM")==0) {
	audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation","Trying to access company section");
	require ("general/noaccess.php");
	exit;
}

$id_user = $config["id_user"];

// CREATE
// ==================
if (isset($_GET["create2"])){ //

	$name = get_parameter ("name","");
	$description = get_parameter ("description", "");
	$sql_insert="INSERT INTO tbuilding (`name`, `description`) VALUE ('$name','$description') ";

	$result=mysql_query($sql_insert);
	if (! $result)
		echo "<h3 class='error'>".__('Building cannot be created')."</h3>";
	else {
		echo "<h3 class='suc'>".__('Building has been created successfully')."</h3>";
		$id_data = mysql_insert_id();
		insert_event ("BUILDING CREATED", $id_data, 0, $name);
	}
}

// UPDATE
// ==================
if (isset($_GET["update2"])){ // if modified any parameter
	$id = get_parameter ("id","");
	$name = get_parameter ("name","");
	$description = get_parameter ("description", "");

	$sql_update ="UPDATE tbuilding
	SET description = '$description', name = '$name' WHERE id = $id";

	$result=mysql_query($sql_update);
	if (! $result)
		echo "<h3 class='error'>".__('Building cannot be updated')."</h3>";
	else {
		echo "<h3 class='suc'>".__('Building updated ok')."</h3>";
		insert_event ("BUILDING", $id, 0, $name);
	}
}

// DELETE
// ==================
if (isset($_GET["delete"])){ // if delete
	$id = get_parameter ("delete",0);
	$name = give_db_sqlfree_field  ("SELECT name FROM tbuilding WHERE id = $id ");
	$sql_delete= "DELETE FROM tbuilding WHERE id = $id";
	$result=mysql_query($sql_delete);
	insert_event ("BUILDING DELETED", $id, 0, "$name");
	echo "<h3 class='suc'>".__('Deleted successfully')."</h3>";
}

if (isset($_GET["update2"])){
	// After apply update, let's redirecto to UPDATE form again.
	$_GET["update"]= $id;
}

// FORM (Update / Create)
if ((isset($_GET["create"]) OR (isset($_GET["update"])))) {
	if (isset($_GET["create"])){
		$id = -1;
		$name = "";
		$description = "";
	} else {
		$id = get_parameter ("update", -1);
		$row = get_db_row ("tbuilding", "id", $id);
		$name = $row["name"];
		$description = $row["description"];
	}

	echo "<h2>".__('Building management')."</h2>";
	if ($id == -1){
		echo "<h3>".__('Create a new building')."</a></h3>";
		echo "<form method='post' action='index.php?sec=inventory&sec2=operation/buildings/building_detail&create2=1'>";
	}
	else {
		echo "<h3>".__('Update existing building')."</a></h3>";
		echo "<form method='post' action='index.php?sec=inventory&sec2=operation/buildings/building_detail&update2=1'>";
		print_input_hidden ("id", "$id", false, '');
	}

	echo "<table width=620 class='databox'>";
	echo "<tr>";
	echo "<td class=datos>";
	echo __('Building name');

	echo "</td><tr>";
	echo "<td class=datos colspan=4>";
	print_input_text ("name", $name, "", 60, 100, false);

	echo "</td></tr>";

	echo "<tr>";
	echo "<td class=datos>";
	echo __('Description');
	echo "</td><tr>";
	echo "<td class=datos colspan=4>";
	print_textarea ("description", 1, 1, $description, "style='width: 600px; height: 160px;'", false);
	echo "</td></tr></table>";

	echo "<table width=620 class='button'>";
	echo "<tr>";
	echo "<td class='datos3' align=right>";
	if ($id == -1)
		print_submit_button (__('Create'), "enviar", false, "class='sub next'", false);
	else
		print_submit_button (__('Update'), "enviar", false, "class='sub upd'", false);
	echo "</td></tr></table>";
	echo "</form>";

	// Get some space here
	echo "<div style='min-height:50px'></div>";
}


// Show list of items
// =======================
if ((!isset($_GET["update"])) AND (!isset($_GET["create"]))){
	echo "<h2>".__('Building management')."</h2>";

	$text = get_parameter ("freetext", "");
	if ($text != ""){
		$sql_search = "WHERE name LIKE '%$text%' OR description LIKE '%$text%' ";
		echo "<h4>".__("Searching for")." ".$text."</h4>";
	}
	else
		$sql_search = "";

	echo "<table width=400>";
	echo "<form method=post action='index.php?sec=inventory&sec2=operation/buildings/building_detail'>";
	echo "<tr><td>";
	echo __('Free text search');
	echo "<td>";
	print_input_text ("freetext", $text, "", 15, 100, false);
	echo "<td>";
	print_submit_button (__('Search'), "enviar", false, "class='sub search'", false);
	echo "</form></td></tr></table>";

   	$sql1 = "SELECT * FROM tbuilding $sql_search ORDER BY name";
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
		$table->head[0] = __('Name');
		$table->head[1] = __('Description');
		$table->head[2] = __('Delete');
		$counter = 0;
		while ($row=mysql_fetch_array($result)){
			// Name
			$table->data[$counter][0] = "<b><a href='index.php?sec=inventory&sec2=operation/buildings/building_detail&update=".$row["id"]."'>".$row["name"]."</a></b>";
			
			// Desc
			$table->data[$counter][1] = substr($row["description"], 0, 50). "...";

			// Delete
			$table->data[$counter][2] = "<a href='index.php?sec=inventory&
						sec2=operation/buildings/building_detail&
						delete=".$row["id"]."'
						onClick='if (!confirm(\' ".$lang_label["are_you_sure"]."\'))
						return false;'>
						<img border='0' src='images/cross.png'></a>";
			$counter++;
		}
		print_table ($table);
		echo "<table width=720 class='button'>";
		echo "<tr><td align='right'>";
		echo "<form method=post action='index.php?sec=inventory&sec2=operation/buildings/building_detail&create=1'>";
		echo "<input type='submit' class='sub next' name='crt' value='".__('Create new building')."'>";
		echo "</form></td></tr></table>";
		
	}


} // end of list

?>
