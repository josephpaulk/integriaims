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
		audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation","Trying to create a contract");
		require ("general/noaccess.php");
		exit;
}

$id_user = $config["id_user"];

// =======================
// CREATE
// =======================

if (isset($_GET["create2"])){
	$name = get_parameter ("name","");
	$id_company = get_parameter ("id_company", 0);
	$description = get_parameter ("description", "");
	$date_begin = get_parameter ("date_begin", "");
	$date_end = get_parameter ("date_end", "");
	$id_sla = get_parameter ("id_sla", "");

	$sql_insert="INSERT INTO tcontract (name, description, date_begin, date_end, id_company, id_sla) VALUE ('$name','$description', '$date_begin', '$date_end', '$id_company', '$id_sla' ) ";

	$result=mysql_query($sql_insert);
	if (! $result)
		echo "<h3 class='error'>".lang_string ("Contract cannot be created")."</h3>";
	else {
		echo "<h3 class='suc'>".lang_string ("Contract has been created successfully")."</h3>";
		$id_data = mysql_insert_id();
		insert_event ("CONTRACT CREATED", $id_data, 0, $name);
	}
}

// =======================
// UPDATE
// =======================

if (isset($_GET["update2"])){ // if modified any parameter
	$id = get_parameter ("id","");
	$name = get_parameter ("name","");
	$id_company = get_parameter ("id_company", 0);
	$description = get_parameter ("description", "");
	$date_begin = get_parameter ("date_begin", "");
	$date_end = get_parameter ("date_end", "");
	$id_sla = get_parameter ("id_sla", "");

	$sql_update ="UPDATE tcontract
	SET id_sla = $id_sla, description = '$description', name = '$name', date_begin= '$date_begin', date_end = '$date_end', id_company = '$id_company' WHERE id = $id";

	$result=mysql_query($sql_update);
	if (! $result)
		echo "<h3 class='error'>".lang_string ("Contract cannot be updated")."</h3>";
	else {
		echo "<h3 class='suc'>".lang_string ("Contract updated ok")."</h3>";
		insert_event ("CONTRACT", $id, 0, $name);
	}
}

// =======================
// DELETE
// =======================

if (isset($_GET["delete"])){ // if delete
	$id = get_parameter ("delete",0);
	$name = give_db_sqlfree_field  ("SELECT name FROM tcontract WHERE id = $id ");
	$sql_delete= "DELETE FROM tcontract WHERE id = $id";
	$result=mysql_query($sql_delete);
	insert_event ("CONTRACT DELETED", $id, 0, "$name");
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
		$date_begin = "";
		$date_end = "";
		$id_company = "";
		$id_sla = "";
		$description = "";
	} else {
		$id = get_parameter ("update", -1);
		$row = get_db_row ("tcontract", "id", $id);
		$name = $row["name"];
		$id_company = $row["id_company"];
		$date_begin = $row["date_begin"];
		$date_end   = $row["date_end"];
		$description = $row["description"];
		$id_sla = $row["id_sla"];
	}

	echo "<h2>".lang_string ("Contract management")."</h2>";
	if ($id == -1){
		echo "<h3>".lang_string ("Create a new contract")."</a></h3>";
		echo "<form method='post' action='index.php?sec=inventory&sec2=operation/contracts/contract_detail&create2=1'>";
	}
	else {
		echo "<h3>".lang_string ("Update existing contract")."</a></h3>";
		echo "<form method='post' action='index.php?sec=inventory&sec2=operation/contracts/contract_detail&update2=1'>";
		print_input_hidden ("id", "$id", false, '');
	}

	echo "<table width=620 class='databox'>";
	echo "<tr>";
	echo "<td class=datos>";
	echo lang_string ("Contract name");
	echo "<tr>";
	echo "<td class=datos colspan=4>";
	print_input_text ("name", $name, "", 60, 100, false);

	echo "<tr>";
	echo "<td class=datos2>";
	echo lang_string ("Begin date");

	echo "<td class=datos2>";
	echo lang_string ("End date");

	echo "<tr>";
	echo "<td class=datos2>";
	print_input_text ("date_begin", $date_begin, "", 15, 40, false);

	echo "<td class=datos2>";
	print_input_text ("date_end", $date_end, "", 15, 40, false);

	echo "<tr>";

	echo "<td class=datos>";
	echo lang_string ("Company");

	echo "<td class=datos>";
	echo lang_string ("SLA");

	echo "<tr>";
	echo "<td class=datos>";
	print_select_from_sql ("SELECT id, name FROM tcompany", "id_company", $id_company, '', 'Select', 0, false, false, true);

	echo "<td class=datos>";
	print_select_from_sql ("SELECT id, name FROM tsla_specific", "id_sla", $id_sla, '', 'Select', 0, false, false, true);

	echo "<tr>";
	echo "<td class=datos>";
	echo lang_string ("Description");
	echo "<tr>";
	echo "<td class=datos colspan=4>";
	print_textarea ("description", 1, 1, $description, "style='width: 600px; height: 120px;'", false);
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
// Show LISTING of items
// =======================

if ((!isset($_GET["update"])) AND (!isset($_GET["create"]))){
	echo "<h2>".lang_string ("Contract management")."</h2>";

	$text = get_parameter ("freetext", "");
	if ($text != ""){
		$sql_search = "WHERE name LIKE '%$text%'";
		echo "<h4>".__("Searching for")." ".$text."</h4>";
	}
	else
		$sql_search = "";


	echo "<table width=400>";
	echo "<form method=post action='index.php?sec=inventory&sec2=operation/contracts/contract_detail'>";
	echo "<tr><td>";
	echo lang_string ("Free text search");
	echo "<td>";
	print_input_text ("freetext", $text, "", 15, 100, false);
	echo "<td>";
	print_submit_button (lang_string("Search"), "enviar", false, "class='sub search'", false);
	echo "</form></td></tr></table>";

	$sql1 = "SELECT * FROM tcontract $sql_search ORDER BY name, id_company";
	$color =0;
	if (($result=mysql_query($sql1)) AND (mysql_num_rows($result) >0)){

		$table->width = "720";
		$table->class = "listing";
		$table->cellspacing = 0;
		$table->cellpadding = 0;
		$table->tablealign="left";
		$table->data = array ();
		$table->size = array ();
		$table->style = array ();
		$table->colspan = array ();
		$table->head[0] = lang_string ("Name");
		$table->head[1] = lang_string ("Company");
		$table->head[2] = lang_string ("SLA");
		$table->head[3] = lang_string ("Begin");
		$table->head[4] = lang_string ("End");
		$table->head[5] = lang_string ("Delete");
		$counter = 0;
		while ($row=mysql_fetch_array($result)){
			// Name
			$table->data[$counter][0] = "<b><a href='index.php?sec=inventory&sec2=operation/contracts/contract_detail&update=".$row["id"]."'>".$row["name"]."</a></b>";

			// Company
			$table->data[$counter][1] = give_db_sqlfree_field ("SELECT name FROM tcompany WHERE id = ".$row["id_company"]);

			// SLA
			$table->data[$counter][2] = give_db_sqlfree_field ("SELECT name FROM tsla_specific WHERE id = ".$row["id_sla"]);

			// Begin
			$table->data[$counter][3] = $row["date_begin"];

			// End
			$table->data[$counter][4] = $row["date_end"];

			// Delete
			$table->data[$counter][5] = "<a href='index.php?sec=inventory&
						sec2=operation/contracts/contract_detail&
						delete=".$row["id"]."'
						onClick='if (!confirm(\' ".$lang_label["are_you_sure"]."\'))
						return false;'>
						<img border='0' src='images/cross.png'></a>";
			$counter++;
		}
		print_table ($table);
	}
} // end of list

?>
