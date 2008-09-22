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
$get_sla = (bool) get_parameter ('get_sla');

if ($get_sla) {
	$id = (int) get_parameter ('id');
	$sla = get_contract_sla ($id, false);
	echo json_encode ($sla);
	
	if (defined ('AJAX'))
		return;
}

// CREATE
if (isset($_GET["create2"])){
	$name = (string) get_parameter ("name");
	$id_company = (int) get_parameter ("id_company");
	$description = (string) get_parameter ("description");
	$date_begin = (string) get_parameter ("date_begin");
	$date_end = (string) get_parameter ("date_end");
	$id_sla = (int) get_parameter ("id_sla");
	$id_group = (int) get_parameter ("id_group");

	if ($id_group < 2){
		echo "<h3 class='error'>".__("You must specify a valid group")."</h3>";
	} else {
		$sql_insert="INSERT INTO tcontract (name, description, date_begin, date_end, id_company, id_sla, id_group) VALUE ('$name','$description', '$date_begin', '$date_end', '$id_company', '$id_sla' , '$id_group') ";
	
		$result=mysql_query($sql_insert);
		if (! $result)
			echo "<h3 class='error'>".__("Contract cannot be created")."</h3>";
		else {
			echo "<h3 class='suc'>".__("Contract has been created successfully")."</h3>";
			$id_data = mysql_insert_id();
			insert_event ("CONTRACT CREATED", $id_data, 0, $name);
		}
	}
}

// UPDATE
if (isset($_GET["update2"])){ // if modified any parameter
	$id = (int) get_parameter ("id");
	$name = (string) get_parameter ("name");
	$id_company = (int) get_parameter ("id_company");
	$description = (string) get_parameter ("description");
	$date_begin = (string) get_parameter ("date_begin");
	$date_end = (string) get_parameter ("date_end");
	$id_sla = (int) get_parameter ("id_sla");
	$id_group = (int) get_parameter ("id_group");

	if ($id_group < 2) {
		echo "<h3 class='error'>".__("You must specify a valid group")."</h3>";
	} else {
		$sql_update ="UPDATE tcontract
		SET id_sla = $id_sla, id_group = $id_group, description = '$description', name = '$name', date_begin= '$date_begin', date_end = '$date_end', id_company = '$id_company' WHERE id = $id";
	
		$result=mysql_query($sql_update);
		if (! $result)
			echo "<h3 class='error'>".__("Contract cannot be updated")."</h3>";
		else {
			echo "<h3 class='suc'>".__("Contract updated ok")."</h3>";
			insert_event ("CONTRACT UPDATED", $id, 0, $name);
		}
	}
}

// DELETE
if (isset($_GET["delete"])){ // if delete
	$id = get_parameter ("delete",0);
	$name = get_db_sql  ("SELECT name FROM tcontract WHERE id = $id ");
	$sql_delete= "DELETE FROM tcontract WHERE id = $id";
	$result=mysql_query($sql_delete);
	insert_event ("CONTRACT DELETED", $id, 0, "$name");
	echo "<h3 class='suc'>".__("Deleted successfully")."</h3>";
}

if (isset($_GET["update2"])){
	// After apply update, let's redirecto to UPDATE form again.
	$_GET["update"]= $id;
}

// FORM (Update / Create)
if ((isset($_GET["create"]) OR (isset($_GET["update"])))) {
	if (isset($_GET["create"])) {
		$id = -1;
		$name = "";
		$date_begin = date('Y-m-d');
		$date_end = $date_begin;
		$id_company = "";
		$id_group = "2";
		$id_sla = "";
		$description = "";
	} else {
		$id = get_parameter ("update", -1);
		$row = get_db_row ("tcontract", "id", $id);
		$name = $row["name"];
		$id_company = $row["id_company"];
		$date_begin = $row["date_begin"];
		$id_group = $row["id_group"];
		$date_end   = $row["date_end"];
		$description = $row["description"];
		$id_sla = $row["id_sla"];
	}

	echo "<h2>".__("Contract management")."</h2>";
	if ($id == -1){
		echo "<h3>".__("Create a new contract")."</a></h3>";
		echo "<form method='post' action='index.php?sec=inventory&sec2=operation/contracts/contract_detail&create2=1'>";
	}
	else {
		echo "<h3>".__("Update existing contract")."</a></h3>";
		echo "<form method='post' action='index.php?sec=inventory&sec2=operation/contracts/contract_detail&update2=1'>";
		print_input_hidden ("id", "$id", false, '');
	}

	echo "<table width=620 class='databox'>";
	echo "<tr>";
	echo "<td>";
	echo __("Contract name");
	echo "<td>";
	echo __("Group");

	echo "<tr>";
	echo "<td>";
	print_input_text ("name", $name, "", 40, 100, false);
	echo "<td>";
	print_select_from_sql ("SELECT id_grupo, nombre FROM tgrupo WHERE id_grupo > 1", "id_group", $id_group, '', '', '', false, false, true);

	echo "<tr>";
	echo "<td>";
	echo __("Begin date");

	echo "<td>";
	echo __("End date");

	echo "<tr>";
	echo "<td>";
	print_input_text ("date_begin", $date_begin, "", 15, 40, false, __("Begin date"));

	echo "<td>";
	print_input_text ("date_end", $date_end, "", 15, 40, false, __("End date"));

	echo "<tr>";

	echo "<td>";
	echo __("Company");

	echo "<td>";
	echo __("SLA");

	echo "<tr>";
	echo "<td>";
	print_select_from_sql ("SELECT id, name FROM tcompany", "id_company", $id_company, '', '', '', false, false, true, __('Company'));

	echo "<td>";
	print_select_from_sql ("SELECT id, name FROM tsla", "id_sla", $id_sla, '', '', '', false, false, true. __('SLA'));

	echo "<tr>";
	echo "<td>";
	echo __("Description");
	echo "<tr>";
	echo "<td colspan=4>";
	print_textarea ("description", 1, 1, $description, "style='width: 600px; height: 120px;'", false);
	echo "</table>";

	echo "<table width=620 class='button'>";
	echo "<tr>";
	echo "<td align=right>";
	if ($id == -1)
		print_submit_button (__("Create"), "enviar", false, "class='sub next'", false);
	else
		print_submit_button (__("Update"), "enviar", false, "class='sub upd'", false);
	echo "</table>";
	echo "</form>";

	// Get some space here
	echo "<div style='min-height:50px'></div>";
}


// Show LISTING of items
if (!isset ($_GET["update"]) && ! isset ($_GET["create"])) {
	echo "<h2>".__("Contract management")."</h2>";

	$text = (string) get_parameter ("freetext");
	$sql_search = sprintf ('WHERE name LIKE "%%%s%%"', $text);
	if ($text != "") {
		echo "<h4>".__("Searching for")." ".$text."</h4>";
	}

	echo '<table width="400px">';
	echo "<form method=post action='index.php?sec=inventory&sec2=operation/contracts/contract_detail'>";
	echo "<tr><td>";
	echo __("Free text search");
	echo "<td>";
	print_input_text ("freetext", $text, "", 15, 100, false);
	echo "<td>";
	print_submit_button (__("Search"), "enviar", false, "class='sub search'", false);
	echo "</form></td></tr></table>";

	$sql1 = "SELECT * FROM tcontract $sql_search ORDER BY name, id_company";
	$color = 0;
	if (($result=mysql_query($sql1)) AND (mysql_num_rows($result) >0)){

		$table->width = "720px";
		$table->class = "listing";
		$table->cellspacing = 0;
		$table->cellpadding = 0;
		$table->tablealign="left";
		$table->data = array ();
		$table->size = array ();
		$table->style = array ();
		$table->colspan = array ();
		$table->head[0] = __("Name");
		$table->head[1] = __("Company");
		$table->head[2] = __("SLA");
		$table->head[3] = __("Group");
		$table->head[4] = __("Begin");
		$table->head[5] = __("End");
		$table->head[6] = __("Delete");
		$counter = 0;
		
		while ($row = mysql_fetch_array ($result)) {
			if (! give_acl ($config["id_user"], $row["id_group"], "IR"))
				continue;
			$data = array ();
			// Name
			$data[0] = "<b><a href='index.php?sec=inventory&sec2=operation/contracts/contract_detail&update=".$row["id"]."'>".$row["name"]."</a></b>";

			// Company
			$data[1] = get_db_value ('name', 'tcompany', 'id', $row["id_company"]);

			// SLA
			$data[2] = get_db_value ('name', 'tsla', 'id', $row["id_sla"]);

			// Group
			$data[3] = get_db_value ('nombre', 'tgrupo', 'id_grupo', $row["id_group"]);

			// Begin
			$data[4] = $row["date_begin"];

			// End
			$data[5] = $row["date_end"] != '0000-00-00' ? $row["date_end"] : "-";

			// Delete
			$data[6] = "<a href='index.php?sec=inventory&
						sec2=operation/contracts/contract_detail&
						delete=".$row["id"]."'
						onClick='if (!confirm(\' ".lang_string ('are_you_sure')."\'))
						return false;'>
						<img border='0' src='images/cross.png'></a>";
			array_push ($table->data, $data);
		}
		
		print_table ($table);
		echo "<table width=720 class='button'>";
		echo "<tr><td align='right'>";
		echo "<form method=post action='index.php?sec=inventory&
		sec2=operation/contracts/contract_detail&create=1'>";
		echo "<input type='submit' class='sub next' name='crt' value='".__("Create contract")."'>";
		echo "</form></td></tr></table>";
	}
} // end of list

?>
