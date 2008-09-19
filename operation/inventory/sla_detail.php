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
	audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation","Trying to access SLA Management");
	require ("general/noaccess.php");
	exit;
}

$id_user = $config["id_user"];

// CREATE
// ==================
if (isset($_GET["create2"])){ //

	$name = get_parameter ("name","");
	$description = get_parameter ("description", "");
	$min_response = get_parameter ("min_response", "");
	$max_response = get_parameter ("max_response", "");
	$max_incidents = get_parameter ("max_incidents", "");
	$id_sla_base = get_parameter ("id_sla_base", 0);
	$enforced = get_parameter ("enforced", 0);

	$sql_insert="INSERT INTO tsla (`name`, `description`, id_sla_base, min_response, max_response, max_incidents, `enforced`) VALUE ('$name','$description', '$id_sla_base', '$min_response', '$max_response', '$max_incidents', '$enforced') ";

	$result=mysql_query($sql_insert);
	if (! $result)
		echo "<h3 class='error'>".lang_string ("SLA cannot be created")."</h3>";
	else {
		echo "<h3 class='suc'>".lang_string ("SLA has been created successfully")."</h3>";
		$id_data = mysql_insert_id();
		insert_event ("SLA CREATED", $id_data, 0, $name);
	}
}

// UPDATE
// ==================
if (isset($_GET["update2"])){ // if modified any parameter
	$id = get_parameter ("id","");
	$name = get_parameter ("name","");
	$description = get_parameter ("description", "");
	$min_response = get_parameter ("min_response", "");
	$max_response = get_parameter ("max_response", "");
	$max_incidents = get_parameter ("max_incidents", "");
	$id_sla_base = get_parameter ("id_sla_base", 0);
	$enforced = get_parameter ("enforced", 0);

	$sql_update ="UPDATE tsla 
	SET enforced = $enforced, description = '$description', name = '$name', max_incidents = '$max_incidents', min_response = '$min_response', max_response = '$max_response', id_sla_base = '$id_sla_base' WHERE id = $id";

	$result=mysql_query($sql_update);
	if (! $result)
		echo "<h3 class='error'>".lang_string ("SLA cannot be updated")."</h3>";
	else {
		echo "<h3 class='suc'>".lang_string ("SLA updated ok")."</h3>";
		insert_event ("SLA UPDATED", $id, 0, $name);
	}
}

// DELETE
// ==================
if (isset($_GET["delete"])){ // if delete
	$id = get_parameter ("delete",0);
	$name = give_db_sqlfree_field  ("SELECT name FROM tsla WHERE id = $id ");
	$sql_delete= "DELETE FROM tsla WHERE id = $id";
	$result=mysql_query($sql_delete);
	insert_event ("SLA DELETED", $id, 0, "$name");
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
		$min_response = 48;
		$max_response = 480;
		$max_incidents = 10;
		$id_sla_base = 0;
		$enforced = 1;

	} else {
		$id = get_parameter ("update", -1);
		$row = get_db_row ("tsla", "id", $id);
		$name = $row["name"];
		$description = $row["description"];
		$min_response = $row["min_response"];
		$max_response = $row["max_response"];
		$max_incidents = $row["max_incidents"];
		$id_sla_base = $row["id_sla_base"];
		$enforced = $row["enforced"];
	}

	echo "<h2>".lang_string ("SLA Management")."</h2>";
	if ($id == -1){
		echo "<h3>".lang_string ("Create a new SLA")."</a></h3>";
		echo "<form method='post' action='index.php?sec=inventory&sec2=operation/inventory/sla_detail&create2=1'>";
	}
	else {
		echo "<h3>".lang_string ("Update existing SLA")."</a></h3>";
		echo "<form method='post' action='index.php?sec=inventory&sec2=operation/inventory/sla_detail&update2=1'>";
		print_input_hidden ("id", "$id", false, '');
	}

	echo "<table width=620 class='databox'>";
	echo "<tr>";
	echo "<td>";
	echo lang_string ("SLA name");
	echo "<td>";
	echo lang_string ("Enforced");

	echo "<tr>";
	echo "<td>";
	print_input_text ("name", $name, "", 30, 100, false);
	echo "<td>";
	print_checkbox ("enforced", 1 ,$enforced, false);

	echo "<tr>";
	echo "<td>";
	echo lang_string ("Min. Response");
	echo "<td>";
	echo lang_string ("Max. Resolution");

	echo "<tr>";
	echo "<td>";
	print_input_text ("min_response", $min_response, "", 5, 100, false);
	echo "<td>";
	print_input_text ("max_response", $max_response, "", 5, 100, false);


	echo "<tr>";
	echo "<td>";
	echo lang_string ("Max. Incidents");
	echo "<td>";
	echo lang_string ("SLA Base");

	echo "<tr>";
	echo "<td>";
	print_input_text ("max_incidents", $max_incidents, "", 5, 100, false);
	echo "<td>";
	print_select_from_sql ("SELECT id, name FROM tsla", "id_sla_base", $id_sla_base, '', 'None', 0, false, false, true);

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
        echo "<h2>".lang_string ("SLA management")."</h2>";

    	$text = get_parameter ("freetext", "");
    	if ($text != ""){
    		$sql_search = "WHERE name LIKE '%$text%' OR descriptionLIKE '%$text%'";
    		echo "<h4>".__("Searching for")." ".$text."</h4>";
    	}
    	else
    		$sql_search = "";

		echo "<table width=400>";
    	echo "<form method=post action='index.php?sec=inventory&sec2=operation/inventory/sla_detail'>";
    	echo "<tr><td>";
    	echo lang_string ("Free text search");
    	echo "<td>";
    	print_input_text ("freetext", $text, "", 15, 100, false);
    	echo "<td>";
    	print_submit_button (lang_string("Search"), "enviar", false, "class='sub search'", false);
    	echo "</form></td></tr></table>";

	   	$sql1 = "SELECT * FROM tsla  $sql_search ORDER BY name";
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
			$table->head[1] = lang_string ("Min.Response");
			$table->head[2] = lang_string ("Max.Resolution");
			$table->head[3] = lang_string ("Max.Incidents");
			$table->head[4] = lang_string ("Enforced");
			$table->head[5] = lang_string ("Parent");
			$table->head[6] = lang_string ("Delete");
			$counter = 0;
	        while ($row=mysql_fetch_array($result)){

                // Name
                $table->data[$counter][0] = "<b><a href='index.php?sec=inventory&sec2=operation/inventory/sla_detail&update=".$row["id"]."'>".$row["name"]."</a></b>";
				
				// Minresp
                $table->data[$counter][1] = $row["min_response"];

				// Minresp
                $table->data[$counter][2] = $row["max_response"];

				// Minresp
                $table->data[$counter][3] = $row["max_incidents"];

				// Minresp
                $table->data[$counter][4] = $row["enforced"];

				// Minresp
                $table->data[$counter][5] = get_db_sql("SELECT name FROM tsla WHERE id = ".$row["id_sla_base"]);
				
                // Delete
                $table->data[$counter][6] = "<a href='index.php?sec=inventorys&
				            sec2=operation/inventory/sla_detail&
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
		echo "<form method=post action='index.php?sec=inventory&
		sec2=operation/inventory/sla_detail&create=1'>";
		echo "<input type='submit' class='sub next' name='crt' value='".lang_string("Create SLA")."'>";
		echo "</form></td></tr></table>";


    } // end of list

?>
