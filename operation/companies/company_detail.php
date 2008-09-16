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

if (give_acl($config["id_user"], 0, "IR")==0) {
	audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation","Trying to access company section");
	require ("general/noaccess.php");
	exit;
}

$id_user = $config["id_user"];

// CREATE
// ==================
if (isset($_GET["create2"])){ //

	$name = get_parameter ("name","");
	$address = get_parameter ("address", "");
	$fiscal_id = get_parameter ("fiscal_id","");
	$comments = get_parameter ("comments","");
	$id_company_role = get_parameter ("id_company_role",0);

	$sql_insert="INSERT INTO tcompany (`name`, `address`, `comments`, fiscal_id, id_company_role ) VALUE ('$name','$address', '$comments', '$fiscal_id', '$id_company_role') ";

echo $sql_insert;
	$result=mysql_query($sql_insert);
	if (! $result)
		echo "<h3 class='error'>".lang_string ("Company cannot be created")."</h3>";
	else {
		echo "<h3 class='suc'>".lang_string ("Company has been created successfully")."</h3>";
		$id_data = mysql_insert_id();
		insert_event ("COMPANY CREATED", $id_data, 0, $name);
	}
}

// UPDATE
// ==================
if (isset($_GET["update2"])){ // if modified any parameter
	$id = get_parameter ("id","");
	$name = get_parameter ("name","");
	$address = get_parameter ("address", "");
	$fiscal_id = get_parameter ("fiscal_id","");
	$comments = get_parameter ("comments","");
	$id_company_role = get_parameter ("id_company_role",0);

	$sql_update ="UPDATE tcompany
	SET comments = '$comments', name = '$name', address = '$address', fiscal_id = '$fiscal_id',  id_company_role = '$id_company_role' WHERE id = $id";

	$result=mysql_query($sql_update);
	if (! $result)
		echo "<h3 class='error'>".lang_string ("Company cannot be updated")."</h3>";
	else {
		echo "<h3 class='suc'>".lang_string ("Company updated ok")."</h3>";
		insert_event ("COMPANY", $id, 0, $name);
	}
}

// DELETE
// ==================
if (isset($_GET["delete"])){ // if delete
	$id = get_parameter ("delete",0);
	$name = give_db_sqlfree_field  ("SELECT name FROM tcompany WHERE id = $id ");
	$sql_delete= "DELETE FROM tcompany WHERE id = $id";
	$result=mysql_query($sql_delete);
	insert_event ("COMPANY DELETED", $id, 0, "$name");
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
		$address = "";
		$comments = "";
		$id_company_role = "";
		$fiscal_id = "";
	} else {
		$id = get_parameter ("update", -1);
		$row = get_db_row ("tcompany", "id", $id);
		$name = $row["name"];
		$address = $row["address"];
		$comments = $row["comments"];
		$id_company_role = $row["id_company_role"];
		$fiscal_id = $row["fiscal_id"];
	}

	echo "<h2>".lang_string ("Company management")."</h2>";
	if ($id == -1){
		echo "<h3>".lang_string ("Create a new company")."</a></h3>";
		echo "<form method='post' action='index.php?sec=inventory&sec2=operation/companies/company_detail&create2=1'>";
	}
	else {
		echo "<h3>".lang_string ("Update existing company")."</a></h3>";
		echo "<form method='post' action='index.php?sec=inventory&sec2=operation/companies/company_detail&update2=1'>";
		print_input_hidden ("id", "$id", false, '');
	}

	echo "<table width=620 class='databox'>";
	echo "<tr>";
	echo "<td class=datos>";
	echo lang_string ("Company name");
	echo "<tr>";
	echo "<td class=datos colspan=4>";
	print_input_text ("name", $name, "", 60, 100, false);

	echo "<tr>";
	echo "<td class=datos>";
	echo lang_string ("Fiscal ID");

	echo "<td class=datos>";
	echo lang_string ("Company Role");


	echo "<tr>";
	echo "<td class=datos>";
	print_input_text ("fiscal_id", $fiscal_id, "", 10, 100, false);

	echo "<td class=datos>";
	print_select_from_sql ("SELECT id, name FROM tcompany_role", "id_company_role", $id_company_role, '', 'Select', 0, false, false, true);


	echo "<tr>";
	echo "<td class=datos>";
	echo lang_string ("Address");
	echo "<tr>";
	echo "<td class=datos colspan=4>";
	print_textarea ("address", 1, 1, $address, "style='width: 600px; height: 60px;'", false);

	echo "<tr>";
	echo "<td class=datos>";
	echo lang_string ("Comments");
	echo "<tr>";
	echo "<td class=datos colspan=4>";
	print_textarea ("comments", 1, 1, $comments, "style='width: 600px; height: 110px;'", false);
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


    // Show list of items
    // =======================
    if ((!isset($_GET["update"])) AND (!isset($_GET["create"]))){
        echo "<h2>".lang_string ("Company management")."</h2>";

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
    	echo lang_string ("Free text search");
    	echo "<td>";
    	print_input_text ("freetext", $text, "", 15, 100, false);
    	echo "<td>";
    	print_submit_button (lang_string("Search"), "enviar", false, "class='sub search'", false);
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
			$table->head[0] = lang_string ("Company");
			$table->head[1] = lang_string ("Contracts");
			$table->head[2] = lang_string ("Contacts");
			$table->head[3] = lang_string ("Delete");
			$counter = 0;
	        while ($row=mysql_fetch_array($result)){
		        if ($color == 1){
			        $tdcolor = "datos";
			        $color = 0;
			        }
		        else {
			        $tdcolor = "datos2";
			        $color = 1;
		        }
		        echo "</thead><tbody><tr>";

                // Name
                $table->data[$counter][0] = "<b><a href='index.php?sec=inventory&sec2=operation/companies/company_detail&update=".$row["id"]."'>".$row["name"]."</a></b>";

				// Contracts (link to new window)
               	$table->data[$counter][1] = "<img src='images/maintab.gif'>";

				// Contacts (link to new window)
               	$table->data[$counter][2] = "<img src='images/group.png'>";

                // Delete
                $table->data[$counter][3] = "<center><a href='index.php?sec=inventory&
				            sec2=operation/companies/company_detail&
				            delete=".$row["id"]."'
				            onClick='if (!confirm(\' ".$lang_label["are_you_sure"]."\'))
				            return false;'>
				            <img border='0' src='images/cross.png'></a></center>";
				$counter++;
            }
            print_table ($table);
        }


    } // end of list

?>
