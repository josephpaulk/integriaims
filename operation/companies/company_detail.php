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

if (! give_acl($config["id_user"], 0, "IM")) {
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access company section");
	require ("general/noaccess.php");
	exit;
}

$id_user = $config["id_user"];

// CREATE
if (isset($_GET["create2"])) {
	$name = (string) get_parameter ("name");
	$address = (string) get_parameter ("address");
	$fiscal_id = (string) get_parameter ("fiscal_id");
	$comments = (string) get_parameter ("comments");
	$id_company_role = (int) get_parameter ("id_company_role");

	$sql = sprintf ('INSERT INTO tcompany (name, address, comments, fiscal_id, id_company_role)
			 VALUES ("%s", "%s", "%s", "%s", %d)',
			 $name, $address, $comments, $fiscal_id, $id_company_role);

	$id_data = process_sql ($sql, 'insert_id');
	if ($id_data === false)
		echo "<h3 class='error'>".__("Company cannot be created")."</h3>";
	else {
		echo "<h3 class='suc'>".__("Company has been created successfully")."</h3>";
		insert_event ("COMPANY CREATED", $id_data, 0, $name);
	}
}

// UPDATE
if (isset($_GET["update2"])){ // if modified any parameter
	$id = (int) get_parameter ("id");
	$name = (string) get_parameter ("name");
	$address = (string) get_parameter ("address");
	$fiscal_id = (string) get_parameter ("fiscal_id");
	$comments = (string) get_parameter ("comments");
	$id_company_role = (int) get_parameter ("id_company_role");

	$sql = sprintf ('UPDATE tcompany SET comments = "%s", name = "%s",
		address = "%s", fiscal_id = "%s", id_company_role = %d WHERE id = %d',
		$comments, $name, $address,
		$fiscal_id, $id_company_role, $id);

	$result = mysql_query ($sql);
	if ($result === false)
		echo "<h3 class='error'>".__("Company cannot be updated")."</h3>";
	else {
		echo "<h3 class='suc'>".__("Company updated ok")."</h3>";
		insert_event ("COMPANY", $id, 0, $name);
	}
}

// DELETE
// ==================
if (isset($_GET["delete"])) { // if delete
	$id = (int) get_parameter ("delete");
	$name = get_db_value ('name', 'tcompany', 'id', $id);
	$sql= sprintf ('DELETE FROM tcompany WHERE id = %d', $id);
	process_sql ($sql);
	insert_event ("COMPANY DELETED", $id, 0, "$name");
	echo "<h3 class='suc'>".__("Deleted successfully")."</h3>";
}

if (isset($_GET["update2"])) {
	// After apply update, let's redirecto to UPDATE form again.
	$_GET["update"]= $id;
}

// FORM (Update / Create)
if ((isset($_GET["create"]) OR (isset($_GET["update"])))) {
	if (isset($_GET["create"])){
		$id = -1;
		$name = "";
		$address = "";
		$comments = "";
		$id_company_role = "";
		$fiscal_id = "";
	} else {
		$id = (int) get_parameter ("update", -1);
		$company = get_db_row ("tcompany", "id", $id);
		$name = $company["name"];
		$address = $company["address"];
		$comments = $company["comments"];
		$id_company_role = $company["id_company_role"];
		$fiscal_id = $company["fiscal_id"];
	}

	echo "<h2>".__("Company management")."</h2>";
	if ($id == -1){
		echo "<h3>".__("Create a new company")."</a></h3>";
		echo "<form method='post' action='index.php?sec=inventory&sec2=operation/companies/company_detail&create2=1'>";
	}
	else {
		echo "<h3>".__("Update existing company")."</a></h3>";
		echo "<form method='post' action='index.php?sec=inventory&sec2=operation/companies/company_detail&update2=1'>";
		print_input_hidden ("id", "$id", false, '');
	}

	echo "<table width=620 class='databox'>";
	echo "<tr>";
	echo "<td class=datos>";
	echo __("Company name");
	echo "<tr>";
	echo "<td class=datos colspan=4>";
	print_input_text ("name", $name, "", 60, 100, false);

	echo "<tr>";
	echo "<td class=datos>";
	echo __("Fiscal ID");

	echo "<td class=datos>";
	echo __("Company Role");


	echo "<tr>";
	echo "<td class=datos>";
	print_input_text ("fiscal_id", $fiscal_id, "", 10, 100, false);

	echo "<td class=datos>";
	print_select_from_sql ("SELECT id, name FROM tcompany_role", "id_company_role", $id_company_role, '', 'Select', 0, false, false, true);


	echo "<tr>";
	echo "<td class=datos>";
	echo __("Address");
	echo "<tr>";
	echo "<td class=datos colspan=4>";
	print_textarea ("address", 1, 1, $address, "style='width: 600px; height: 60px;'", false);

	echo "<tr>";
	echo "<td class=datos>";
	echo __("Comments");
	echo "<tr>";
	echo "<td class=datos colspan=4>";
	print_textarea ("comments", 1, 1, $comments, "style='width: 600px; height: 110px;'", false);
	echo "</table>";

	echo "<table width=620 class='button'>";
	echo "<tr>";
	echo "<td class='datos3' align=right>";
	if ($id == -1)
		print_submit_button (__("Create"), "enviar", false, "class='sub next'", false);
	else
		print_submit_button (__("Update"), "enviar", false, "class='sub upd'", false);
	echo "</table>";
	echo "</form>";

	// Get some space here
	echo "<div style='min-height:50px'></div>";
}


// Show list of items
if ((!isset($_GET["update"])) AND (!isset($_GET["create"]))){
	echo "<h2>".__("Company management")."</h2>";

	$text = get_parameter ("freetext", "");
	if ($text != ""){
		$sql_search = "WHERE name LIKE '%$text%' OR address LIKE '%$text%' OR comments LIKE '%$text%' ";
		echo "<h4>".__("Searching for")." ".$text."</h4>";
	}
	else
		$sql_search = "";

	echo "<table width=400>";
	echo "<form method=post action='index.php?sec=inventory&sec2=operation/companies/company_detail'>";
	echo "<tr><td>";
	echo __("Free text search");
	echo "<td>";
	print_input_text ("freetext", $text, "", 15, 100, false);
	echo "<td>";
	print_submit_button (__("Search"), "enviar", false, "class='sub search'", false);
	echo "</form></td></tr></table>";

	$sql1 = "SELECT * FROM tcompany $sql_search ORDER BY name";
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
		$table->head[0] = __("Company");
		$table->head[1] = __("Role");
		$table->head[2] = __("Contracts");
		$table->head[3] = __("Contacts");
		$table->head[4] = __("Delete");
		$counter = 0;
		while ($row=mysql_fetch_array($result)){
			// Name
			$table->data[$counter][0] = "<b><a href='index.php?sec=inventory&sec2=operation/companies/company_detail&update=".$row["id"]."'>".$row["name"]."</a></b>";
			
			// Role
			$table->data[$counter][1] = get_db_sql("SELECT name FROM tcompany_role WHERE id = ".$row["id_company_role"]);

			// Contracts (link to new window)
			$table->data[$counter][2] = "<img src='images/maintab.gif'>";

			// Contacts (link to new window)
			$table->data[$counter][3] = "<img src='images/group.png'>";

			// Delete
			$table->data[$counter][4] = "<a href='index.php?sec=inventory&
						sec2=operation/companies/company_detail&
						delete=".$row["id"]."'
						onClick='if (!confirm(\' ".__('are_you_sure')."\'))
						return false;'>
						<img border='0' src='images/cross.png'></a>";
			$counter++;
		}
		print_table ($table);
	}
	echo "<table width=720 class='button'>";
	echo "<tr><td align='right'>";
	echo "<form method=post action='index.php?sec=inventory&
	sec2=operation/companies/company_detail&create=1'>";
	echo "<input type='submit' class='sub next' name='crt' value='".__("Create company")."'>";
	echo "</form></td></tr></table>";
} // end of list

?>
