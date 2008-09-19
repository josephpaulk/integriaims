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
	$description = get_parameter ("description","");
	$sql_insert="INSERT INTO tcompany_role (`name`, `description` ) VALUE ('$name', '$description') ";

	$result=mysql_query($sql_insert);
	if (! $result)
		echo "<h3 class='error'>".lang_string ("Company role cannot be created")."</h3>";
	else {
		echo "<h3 class='suc'>".lang_string ("Company role has been created successfully")."</h3>";
		$id_data = mysql_insert_id();
		insert_event ("COMPANY ROLE CREATED", $id_data, 0, $name);
	}
}

// =======================
// UPDATE
// =======================

if (isset($_GET["update2"])){ // if modified any parameter
	$id = get_parameter ("id","");
	$name = get_parameter ("name","");
	$description = get_parameter ("description","");

	$sql_update ="UPDATE tcompany_role
	SET description = '$description', name = '$name' WHERE id = $id";

	$result=mysql_query($sql_update);
	if (! $result)
		echo "<h3 class='error'>".lang_string ("Company role cannot be updated")."</h3>";
	else {
		echo "<h3 class='suc'>".lang_string ("Company role updated ok")."</h3>";
		insert_event ("COMPANY ROLE", $id, 0, $name);
	}
}
// =======================
// DELETE
// =======================

if (isset($_GET["delete"])){ // if delete
	$id = get_parameter ("delete",0);
	$name = give_db_sqlfree_field  ("SELECT name FROM tcompany_role WHERE id = $id ");
	$sql_delete= "DELETE FROM tcompany_role WHERE id = $id";
	$result=mysql_query($sql_delete);
	insert_event ("COMPANY ROLE DELETED", $id, 0, "$name");
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
		$description = "";
	} else {
		$id = get_parameter ("update", -1);
		$row = get_db_row ("tcompany_role", "id", $id);
		$name = $row["name"];
		$description = $row["description"];
	}

	echo "<h2>".lang_string ("Company role management")."</h2>";
	if ($id == -1){
		echo "<h3>".lang_string ("Create a new role")."</a></h3>";
		echo "<form method='post' action='index.php?sec=inventory&sec2=operation/companies/company_role&create2=1'>";
	}
	else {
		echo "<h3>".lang_string ("Update existing role")."</a></h3>";
		echo "<form method='post' action='index.php?sec=inventory&sec2=operation/companies/company_role&update2=1'>";
		print_input_hidden ("id", "$id", false, '');
	}

	echo "<table width=620 class='databox'>";
	echo "<tr>";
	echo "<td class=datos>";
	echo lang_string ("Role name");

	echo "<tr>";
	echo "<td class=datos colspan=4>";
	print_input_text ("name", $name, "", 60, 100, false);


	echo "<tr>";
	echo "<td class=datos>";
	echo lang_string ("Description");

	echo "<tr>";
	echo "<td class=datos colspan=4>";
	print_textarea ("description", 1, 1, $description, "style='width: 600px; height: 60px;'", false);
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
	echo "<h2>".lang_string ("Company roles")."</h2>";

	$text = get_parameter ("freetext", "");
	if ($text != ""){
		$sql_search = "WHERE name LIKE '%$text%' OR description LIKE '%$text%' ";
		echo "<h4>".__("Searching for")." ".$text."</h4>";
	}
	else
		$sql_search = "";

	echo "<table width=400>";
	echo "<form method=post action='index.php?sec=inventory&sec2=operation/companies/company_role'>";
	echo "<tr><td>";
	echo lang_string ("Free text search");
	echo "<td>";
	print_input_text ("freetext", $text, "", 15, 100, false);
	echo "<td>";
	print_submit_button (lang_string("Search"), "enviar", false, "class='sub search'", false);
	echo "</form></td></tr></table>";

	$sql1 = "SELECT * FROM tcompany_role $sql_search ORDER BY name";
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
		$table->head[1] = lang_string ("Description");
		$table->head[2] = lang_string ("Delete");
		$counter = 0;
		while ($row=mysql_fetch_array($result)){
			// Name
			$table->data[$counter][0] = "<b><a href='index.php?sec=inventory&sec2=operation/companies/company_role&update=".$row["id"]."'>".$row["name"]."</a></b>";

			// Contracts (link to new window)
			$table->data[$counter][1] = substr($row["description"],0,70). "...";

			// Delete
			$table->data[$counter][2] = "<a href='index.php?sec=inventory&
						sec2=operation/companies/company_role&
						delete=".$row["id"]."'
						onClick='if (!confirm(\' ".$lang_label["are_you_sure"]."\'))
						return false;'>
						<img border='0' src='images/cross.png'></a>";
			$counter++;
		}
		print_table ($table);
		echo "<table width=720 class='button'>";
		echo "<tr><td align='right'>";
		echo "<form method=post action='index.php?sec=inventory&
		sec2=operation/companies/company_role&create=1'>";
		echo "<input type='submit' class='sub next' name='crt' value='".lang_string("Create role")."'>";
		echo "</form></td></tr></table>";
	}
} // end of list
?>
