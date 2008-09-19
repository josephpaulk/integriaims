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
	audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation","Trying to access Contact");
	require ("general/noaccess.php");
	exit;
}

$id_user = $config["id_user"];

// CREATE
// ==================
if (isset($_GET["create2"])){ //

	$fullname = get_parameter ("fullname","");
	$phone = get_parameter ("phone", "");
	$mobile = get_parameter ("mobile","");
	$email = get_parameter ("email","");
	$position = get_parameter ("position","");
	$id_company = get_parameter ("id_company", 0);
	$disabled = get_parameter ("disabled", 0);
	$description = get_parameter ("description", "");

	$sql_insert="INSERT INTO tcompany_contact (fullname, phone, mobile, email, position, id_company, disabled, description) VALUE ('$fullname','$phone', '$mobile', '$email', '$position', '$id_company', '$disabled', '$description') ";

	$result=mysql_query($sql_insert);
	if (! $result)
		echo "<h3 class='error'>".lang_string ("Contact cannot be created")."</h3>";
	else {
		echo "<h3 class='suc'>".lang_string ("Contact has been created successfully")."</h3>";
		$id_data = mysql_insert_id();
		insert_event ("CONTACT CREATED", $id_data, 0, $fullname);
	}
}

// UPDATE
// ==================
if (isset($_GET["update2"])){ // if modified any parameter
	$id = get_parameter ("id","");
	$fullname = get_parameter ("fullname","");
	$phone = get_parameter ("phone", "");
	$mobile = get_parameter ("mobile","");
	$email = get_parameter ("email","");
	$position = get_parameter ("position","");
	$id_company = get_parameter ("id_company", 0);
	$disabled = get_parameter ("disabled", 0);
	$description = get_parameter ("description", "");

	$sql_update ="UPDATE tcompany_contact
	SET description = '$description', fullname = '$fullname', phone = '$phone', mobile = '$mobile', email = '$email', position = '$position', id_company = '$id_company', disabled = '$disabled' WHERE id = $id";

	$result=mysql_query($sql_update);
	if (! $result)
		echo "<h3 class='error'>".lang_string ("Contact cannot be updated")."</h3>";
	else {
		echo "<h3 class='suc'>".lang_string ("Contact updated ok")."</h3>";
		insert_event ("CONTACT", $id, 0, $fullname);
	}
}

// DELETE
// ==================
if (isset($_GET["delete"])){ // if delete
	$id = get_parameter ("delete",0);
	$fullname = give_db_sqlfree_field  ("SELECT fullname FROM tcompany_contact WHERE id = $id ");
	$sql_delete= "DELETE FROM tcompany_contact WHERE id = $id";
	$result=mysql_query($sql_delete);
	insert_event ("CONTACT DELETED", $id, 0, "$fullname");
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
		$fullname = "";
		$phone = "";
		$mobile = "";
		$email = "";
		$position = "";
		$id_company = "";
		$disabled = "";
		$description = "";
	} else {
		$id = get_parameter ("update", -1);
		$row = get_db_row ("tcompany_contact", "id", $id);
		$fullname = $row["fullname"];
		$phone = $row["phone"];
		$mobile = $row["mobile"];
		$email = $row["email"];
		$position = $row["position"];
		$id_company = $row["id_company"];
		$disabled = $row["disabled"];
		$description = $row["description"];
	}

	echo "<h2>".lang_string ("Contact management")."</h2>";
	if ($id == -1){
		echo "<h3>".lang_string ("Create a new contact")."</a></h3>";
		echo "<form method='post' action='index.php?sec=inventory&sec2=operation/contacts/contact_detail&create2=1'>";
	}
	else {
		echo "<h3>".lang_string ("Update existing contact")."</a></h3>";
		echo "<form method='post' action='index.php?sec=inventory&sec2=operation/contacts/contact_detail&update2=1'>";
		print_input_hidden ("id", "$id", false, '');
	}

	echo "<table width=620 class='databox'>";
	echo "<tr>";
	echo "<td class=datos>";
	echo lang_string ("Full name");
	echo "<tr>";
	echo "<td class=datos colspan=4>";
	print_input_text ("fullname", $fullname, "", 60, 100, false);

	echo "<tr>";
	echo "<td class=datos>";
	echo lang_string ("Email");
	echo "<tr>";
	echo "<td class=datos colspan=4>";
	print_input_text ("email", $email, "", 35, 100, false);

	echo "<tr>";
	echo "<td class=datos2>";
	echo lang_string ("Phone number");

	echo "<td class=datos2>";
	echo lang_string ("Mobile number");

	echo "<tr>";
	echo "<td class=datos2>";
	print_input_text ("phone", $phone, "", 15, 40, false);

	echo "<td class=datos2>";
	print_input_text ("mobile", $mobile, "", 15, 40, false);

	echo "<tr>";

	echo "<td class=datos>";
	echo lang_string ("Position");

	echo "<td class=datos>";
	echo lang_string ("Company");

	echo "<tr>";
	echo "<td class=datos>";
	echo "<input type=text size=25 name='position' value='$position'>";
	echo "<td class=datos>";
	print_select_from_sql ("SELECT id, name FROM tcompany", "id_company", $id_company, '', 'Select', 0, false, false, true);


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


    // Show list of items
    // =======================
    if ((!isset($_GET["update"])) AND (!isset($_GET["create"]))){
        echo "<h2>".lang_string ("Contact management")."</h2>";

    	$text = get_parameter ("freetext", "");
    	if ($text != ""){
    		$sql_search = "WHERE fullname LIKE '%$text%'";
    		echo "<h4>".__("Searching for")." ".$text."</h4>";
    	}
    	else
    		$sql_search = "";


		echo "<table width=400>";
    	echo "<form method=post action='index.php?sec=inventory&sec2=operation/contacts/contact_detail'>";
    	echo "<tr><td>";
    	echo lang_string ("Free text search");
    	echo "<td>";
    	print_input_text ("freetext", $text, "", 15, 100, false);
    	echo "<td>";
    	print_submit_button (lang_string("Search"), "enviar", false, "class='sub search'", false);
    	echo "</form></td></tr></table>";

	    $sql1 = "SELECT * FROM tcompany_contact $sql_search ORDER BY fullname, id_company";
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
			$table->head[0] = lang_string ("Full name");
			$table->head[1] = lang_string ("Company");
			$table->head[2] = lang_string ("eMail");
			$table->head[3] = lang_string ("Delete");
			$counter = 0;
	        while ($row=mysql_fetch_array($result)){
                // Name
                $table->data[$counter][0] = "<b><a href='index.php?sec=inventory&sec2=operation/contacts/contact_detail&update=".$row["id"]."'>".$row["fullname"]."</a></b>";

				// Company
               	$table->data[$counter][1] = give_db_sqlfree_field ("SELECT name FROM tcompany WHERE id = ".$row["id_company"]);

         		// Email
                $table->data[$counter][2] = $row["email"];

                // Delete
                $table->data[$counter][3] = "<a href='index.php?sec=inventory&
				            sec2=operation/contacts/contact_detail&
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
			sec2=operation/contacts/contact_detail&create=1'>";
			echo "<input type='submit' class='sub next' name='crt' value='".lang_string("Create contact")."'>";
			echo "</form></td></tr></table>";
        }


    } // end of list

?>
