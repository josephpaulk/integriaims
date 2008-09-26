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
	audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation","Trying to access Manufacturer section");
	require ("general/noaccess.php");
	exit;
}

$id_user = $config["id_user"];

// CREATE
// ==================
if (isset($_GET["create2"])){ //

	$name = get_parameter ("name","");
	$comments = get_parameter ("comments", "");
	$address = get_parameter ("address", "");
	$id_company_role = get_parameter ("id_company_role", "");
	$id_sla = get_parameter ("id_sla", "");

	$sql_insert="INSERT INTO tmanufacturer (`name`, `comments`, `address`, `id_sla`, `id_company_role`) VALUE ('$name','$comments', '$address', '$id_company_role', '$id_sla') ";

	$result=mysql_query($sql_insert);
	if (! $result)
		echo "<h3 class='error'>".lang_string ("Manufacturer cannot be created")."</h3>";
	else {
		echo "<h3 class='suc'>".lang_string ("Manufacturer has been created successfully")."</h3>";
		$id_data = mysql_insert_id();
		insert_event ("MANUFACTURER CREATED", $id_data, 0, $name);
	}
}

// UPDATE
// ==================
if (isset($_GET["update2"])){ // if modified any parameter
	$id = get_parameter ("id","");
	$name = get_parameter ("name","");
	$comments = get_parameter ("comments", "");
	$address = get_parameter ("address", "");
	$id_company_role = get_parameter ("id_company_role", "");
	$id_sla = get_parameter ("id_sla", "");

	$sql_update ="UPDATE tmanufacturer
	SET address = '$address', id_sla = '$id_sla', id_company_role = '$id_company_role', comments = '$comments', name = '$name' WHERE id = $id";

	$result=mysql_query($sql_update);
	if (! $result)
		echo "<h3 class='error'>".lang_string ("Manufacturer cannot be updated")."</h3>";
	else {
		echo "<h3 class='suc'>".lang_string ("Manufacturer updated ok")."</h3>";
		insert_event ("MANUFACTURER", $id, 0, $name);
	}
}

// DELETE
// ==================
if (isset($_GET["delete"])){ // if delete
	$id = get_parameter ("delete",0);
	$name = give_db_sqlfree_field  ("SELECT name FROM tmanufacturer WHERE id = $id ");
	$sql_delete= "DELETE FROM tmanufacturer WHERE id = $id";
	$result=mysql_query($sql_delete);
	insert_event ("MANUFACTURER DELETED", $id, 0, "$name");
	echo "<h3 class='suc'>".lang_string("Deleted successfully")."</h3>";
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
		$comments = "";

		$address = "";
		$id_sla = "";
		$id_company_role = "";
	} else {
		$id = get_parameter ("update", -1);
		$row = get_db_row ("tmanufacturer", "id", $id);
		$name = $row["name"];
		$comments = $row["comments"];
		$address = $row["address"];
		$id_sla = $row["id_sla"];
		$id_company_role = $row["id_company_role"];
	}

	echo "<h2>".lang_string ("Manufacturer management")."</h2>";
	if ($id == -1){
		echo "<h3>".lang_string ("Create a new manufacturer")."</a></h3>";
		echo "<form method='post' action='index.php?sec=inventory&sec2=operation/inventory/manufacturer_detail&create2=1'>";
	}
	else {
		echo "<h3>".lang_string ("Update existing manufacturer")."</a></h3>";
		echo "<form method='post' action='index.php?sec=inventory&sec2=operation/inventory/manufacturer_detail&update2=1'>";
		print_input_hidden ("id", "$id", false, '');
	}

	echo "<table width=620 class='databox'>";

	echo "<tr>";
	echo "<td class=datos>";
	echo lang_string ("Manufacturer name");
	echo "</td><tr>";
	echo "<td class=datos colspan=4>";
	print_input_text ("name", $name, "", 60, 100, false);
	echo "</td></tr>";
	
	echo "<tr>";
	echo "<td>".lang_string ("Company role");
	echo "<td>".lang_string ("Base SLA");
	
	echo "<tr>";
	echo "<td>";
	print_select_from_sql ("SELECT id, name FROM tcompany_role", "id_company_role", $id_company_role, '', 'select', '0', false, false, true, false);
	echo "<td>";
	print_select_from_sql ("SELECT id, name FROM tsla", "id_sla", $id_sla, '', 'select', '0', false, false, true, false);

	echo "<tr>";
	echo "<td class=datos>";
	echo lang_string ("Address");
	echo "</td><tr>";
	echo "<td class=datos colspan=4>";
	print_textarea ("address", 1, 1, $address, "style='width: 600px; height: 100px;'", false);
	echo "</td></tr>";

	echo "<tr>";
	echo "<td class=datos>";
	echo lang_string ("Comments");
	echo "</td><tr>";
	echo "<td class=datos colspan=4>";
	print_textarea ("comments", 1, 1, $comments, "style='width: 600px; height: 100px;'", false);
	echo "</td></tr>";


	echo "</table>";

	echo "<table width=620 class='button'>";
	echo "<tr>";
	echo "<td class='datos3' align=right>";
	if ($id == -1)
		print_submit_button (lang_string("Create"), "enviar", false, "class='sub next'", false);
	else
		print_submit_button (lang_string("Update"), "enviar", false, "class='sub upd'", false);
	echo "</td></tr></table>";
	echo "</form>";

	// Get some space here
	echo "<div style='min-height:50px'></div>";
}


    // Show list of items
    // =======================
    if ((!isset($_GET["update"])) AND (!isset($_GET["create"]))){
        echo "<h2>".lang_string ("Manufacturers management")."</h2>";

    	$text = get_parameter ("freetext", "");
    	if ($text != ""){
    		$sql_search = "WHERE address LIKE '%$text%' OR name LIKE '%$text%' OR comments LIKE '%$text%' ";
    		echo "<h4>".__("Searching for")." ".$text."</h4>";
    	}
    	else
    		$sql_search = "";

		echo "<table width=400>";
    	echo "<form method=post action='index.php?sec=inventory&sec2=operation/inventory/building_detail'>";
    	echo "<tr><td>";
    	echo lang_string ("Free text search");
    	echo "<td>";
    	print_input_text ("freetext", $text, "", 15, 100, false);
    	echo "<td>";
    	print_submit_button (lang_string("Search"), "enviar", false, "class='sub search'", false);
    	echo "</form></td></tr></table>";

	   	$sql1 = "SELECT * FROM tmanufacturer $sql_search ORDER BY name";
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
			$table->head[1] = lang_string ("Address");
			$table->head[2] = lang_string ("Company role");
			$table->head[3] = lang_string ("SLA");
			$table->head[4] = lang_string ("Delete");
			$counter = 0;
	        while ($row=mysql_fetch_array($result)){
                // Name
                $table->data[$counter][0] = "<b><a href='index.php?sec=inventory&sec2=operation/inventory/manufacturer_detail&update=".$row["id"]."'>".$row["name"]."</a></b>";
				
				// Address
				$table->data[$counter][1] = substr($row["address"], 0, 50). "...";

				// Company role
				$table->data[$counter][2] = get_db_sql ("SELECT name FROM tcompany_role WHERE id = ".$row["id_company_role"]);

				// SLA 
				$table->data[$counter][3] = get_db_sql ("SELECT name FROM tsla WHERE id = ".$row["id_sla"]);

                // Delete
                $table->data[$counter][4] = "<a href='index.php?sec=inventory&
				            sec2=operation/inventory/building_detail&
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
		echo "<form method=post action='index.php?sec=inventory&sec2=operation/inventory/manufacturer_detail&create=1'>";
		echo "<input type='submit' class='sub next' name='crt' value='".lang_string("Create new manufacturer")."'>";
		echo "</form></td></tr></table>";
    } // end of list

?>
