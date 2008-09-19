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
	$id_wizard = get_parameter ("id_wizard", 0);

	$sql_insert="INSERT INTO tincident_type (`name`, `description`, id_wizard) VALUE ('$name','$description', '$id_wizard') ";

	$result=mysql_query($sql_insert);
	if (! $result)
		echo "<h3 class='error'>".lang_string ("Incident type cannot be created")."</h3>";
	else {
		echo "<h3 class='suc'>".lang_string ("Incident type has been created successfully")."</h3>";
		$id_data = mysql_insert_id();
		insert_event ("INCIDENT TYPE CREATED", $id_data, 0, $name);
	}
}

// UPDATE
// ==================
if (isset($_GET["update2"])){ // if modified any parameter
	$id = get_parameter ("id","");
	$name = get_parameter ("name","");
	$description = get_parameter ("description", "");
	$id_wizard = get_parameter ("id_wizard", 0);

	$sql_update ="UPDATE tincident_type
	SET description = '$description', name = '$name', id_wizard= '$id_wizard' WHERE id = $id";

	$result=mysql_query($sql_update);
	if (! $result)
		echo "<h3 class='error'>".lang_string ("Incident type cannot be updated")."</h3>";
	else {
		echo "<h3 class='suc'>".lang_string ("Incident type updated ok")."</h3>";
		insert_event ("INCIDENT TYPE", $id, 0, $name);
	}
}

// DELETE
// ==================
if (isset($_GET["delete"])){ // if delete
	$id = get_parameter ("delete",0);
	$name = give_db_sqlfree_field  ("SELECT name FROM tincident_type WHERE id = $id ");
	$sql_delete= "DELETE FROM tincident_type WHERE id = $id";
	$result=mysql_query($sql_delete);
	insert_event ("INCIDENT TYPE DELETED", $id, 0, "$name");
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
		$description = "";
		$id_wizard = "";
	} else {
		$id = get_parameter ("update", -1);
		$row = get_db_row ("tincident_type", "id", $id);
		$name = $row["name"];
		$description = $row["description"];
		$id_wizard = $row["id_wizard"];
	}

	echo "<h2>".lang_string ("Incident types")."</h2>";
	if ($id == -1){
		echo "<h3>".lang_string ("Create a new type")."</a></h3>";
		echo "<form method='post' action='index.php?sec=incidents&sec2=operation/incidents/type_detail&create2=1'>";
	}
	else {
		echo "<h3>".lang_string ("Update existing company")."</a></h3>";
		echo "<form method='post' action='index.php?sec=incidents&sec2=operation/incidents/type_detail&update2=1'>";
		print_input_hidden ("id", "$id", false, '');
	}

	echo "<table width=620 class='databox'>";
	echo "<tr>";
	echo "<td>";
	echo lang_string ("Type name");
	echo "<td>";
	echo lang_string ("Wizard");


	echo "<tr>";
	echo "<td>";
	print_input_text ("name", $name, "", 30, 100, false);

	echo "<td>";
	print_select_from_sql ("SELECT id, name FROM twizard", "id_wizard", $id_wizard, '', 'Select', 0, false, false, true);


	echo "<tr>";
	echo "<td>";
	echo lang_string ("Description");
	echo "<tr>";
	echo "<td colspan=4>";
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


    // Show list of items
    // =======================
    if ((!isset($_GET["update"])) AND (!isset($_GET["create"]))){
        echo "<h2>".lang_string ("Type management")."</h2>";

    	$text = get_parameter ("freetext", "");
    	if ($text != ""){
    		$sql_search = "WHERE name LIKE '%$text%' OR descriptionLIKE '%$text%'";
    		echo "<h4>".__("Searching for")." ".$text."</h4>";
    	}
    	else
    		$sql_search = "";

		echo "<table width=400>";
    	echo "<form method=post action='index.php?sec=incidents&sec2=operation/incidents/type_detail'>";
    	echo "<tr><td>";
    	echo lang_string ("Free text search");
    	echo "<td>";
    	print_input_text ("freetext", $text, "", 15, 100, false);
    	echo "<td>";
    	print_submit_button (lang_string("Search"), "enviar", false, "class='sub search'", false);
    	echo "</form></td></tr></table>";

	   	$sql1 = "SELECT * FROM tincident_type $sql_search ORDER BY name";
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
			$table->head[1] = lang_string ("Wizard");
			$table->head[2] = lang_string ("Delete");
			$counter = 0;
	        while ($row=mysql_fetch_array($result)){

                // Name
                $table->data[$counter][0] = "<b><a href='index.php?sec=incidents&sec2=operation/incidents/type_detail&update=".$row["id"]."'>".$row["name"]."</a></b>";
				
				// Wizard
				$table->data[$counter][1] = get_db_sql("SELECT name FROM tincident_type WHERE id = ".$row["id_wizard"]);

                // Delete
                $table->data[$counter][4] = "<a href='index.php?sec=incidents&
				            sec2=operation/incidents/type_detail&
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
		echo "<form method=post action='index.php?sec=incidents&
		sec2=operation/incidents/type_detail&create=1'>";
		echo "<input type='submit' class='sub next' name='crt' value='".lang_string("Create type")."'>";
		echo "</form></td></tr></table>";


    } // end of list

?>
