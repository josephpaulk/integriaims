<?php

// Integria 1.1 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2008 Artica Soluciones Tecnologicas

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

	$sql_insert="INSERT INTO tcontact (fullname, phone, mobile, email, position, id_company, disabled)
				 VALUE ('$fullname','$phone', '$mobile', '$email', '$position', '$id_company', '$disabled') ";
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

	$sql_update ="UPDATE tcontact
	SET fullname = '$fullname', phone = '$phone', mobile = '$mobile', email = '$email', position = '$position', id_company = '$id_company', disabled = '$disabled' WHERE id = $id";

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
	$fullname = give_db_sqlfree_field  ("SELECT fullname FROM tcontact WHERE id = $id ");
	$sql_delete= "DELETE FROM tcontact WHERE id = $id";
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
	} else {
		$id = get_parameter ("update", -1);
		$row = give_db_row ("tcontact", "id", $id);
		$fullname = $row["fullname"];
		$phone = $row["phone"];
		$mobile = $row["mobile"];
		$email = $row["email"];
		$position = $row["position"];
		$id_company = $row["id_company"];
		$disabled = $row["disabled"];
	}

	echo "<h2>".lang_string ("Contact management")."</h2>";
	if ($id == -1){
		echo "<h3>".lang_string ("Create a new contact")."</a></h3>";
		echo "<form method='post' action='index.php?sec=inventory&sec2=operation/contacts/contact_detail&create2=1'>";
	}
	else {
		echo "<h3>".lang_string ("Update existing KB item")."</a></h3>";
		echo "<form method='post' action='index.php?sec=inventory&sec2=operation/contacts/contact_detail&update2=1'>";
		echo "<input type='hidden name='id' value='$id'>";
	}

	echo "<table width=720 class='databox'>";
	echo "<tr>";
	echo "<td class=datos>";
	echo lang_string ("Full name");
	echo "<td class=datos colspan=3>";
	echo "<input type=text size=60 name='fullname' value='$fullname'>";

	echo "<tr>";
	echo "<td class=datos2>";
	echo lang_string ("Phone number");
	echo "<td class=datos2>";
	echo "<input type=text size=20 name='phone' value='$phone'>";

	echo "<td class=datos2>";
	echo lang_string ("Mobile number");
	echo "<td class=datos2>";
	echo "<input type=text size=20 name='mobile' value='$mobile'>";

	echo "<tr>";
	echo "<td class=datos>";
	echo lang_string ("Email");
	echo "<td class=datos colspan=3>";
	echo "<input type=text size=40 name='email' value='$email'>";


	echo "<tr>";
	echo "<td class=datos>";
	echo lang_string ("Company");
	echo "<td class=datos>";
	print_select_from_sql ("SELECT id, name FROM tcompany", "id_company", $id_company, '', 'Select', 0, false, false, true);

	echo "<td class=datos>";
	echo lang_string ("Position");
	echo "<td class=datos>";
	echo "<input type=text size=20 name='position' value='$position'>";

	echo "</table>";

	echo "<table width=720>";
	echo "<tr>";
	echo "<td align=right>";
	if ($id == -1)
		echo "<input type=submit class='sub next' value='Create'>";
	else
		echo "<input type=submit class='sub upd' value='Update'>";
	echo "</table>";
	echo "</form>";

	// Get some space here
	echo "<div style='min-height:50px'></div>";
}


    // Show list of items
    // =======================
    if ((!isset($_GET["update"])) AND (!isset($_GET["create"]))){
        echo "<h2>".lang_string ("Contact management")."</h2>";
    	echo "<h3>".lang_string ("Defined contacts")."</a></h3>";

    	$text = get_parameter ("freetext", "");
    	if ($text != ""){
    		$sql_search = "WHERE fullname LIKE '%$text%'";
    		echo "<h4>".__("Searching for")." ".$text."</h4>";
    	}
    	else
    		$sql_search = "";

		echo "<table width=400 class=databox>";
    	echo "<form method=post action='index.php?sec=inventory&sec2=operation/contacts/contact_detail'>";
    	echo "<tr><td>";
    	echo lang_string ("Free text search");
    	echo "<td>";
    	echo "<input type=text name='freetext' value='' size=15>";
    	echo "<td>";
    	echo "<input type=submit class='sub search' value='".lang_string("Search")."'>";
    	echo "</form></td></tr></table>";

	    $sql1 = "SELECT * FROM tcontact $sql_search ORDER BY fullname, id_company";
        $color =0;
	    if ($result=mysql_query($sql1)){
            echo "<table width=720 class='databox'>";

	        echo "<th>".lang_string ("Full name")."</th>";
	        echo "<th>".lang_string ("Company")."</th>";
	        echo "<th>".lang_string ("Email")."</th>";
	        echo "<th>".lang_string ("Delete")."</th>";
	        while ($row=mysql_fetch_array($result)){
		        if ($color == 1){
			        $tdcolor = "datos";
			        $color = 0;
			        }
		        else {
			        $tdcolor = "datos2";
			        $color = 1;
		        }
		        echo "<tr>";

                // Name
                echo "<td class='$tdcolor' valign='top'><b><a href='index.php?sec=inventory&sec2=operation/contacts/contact_detail&update=".$row["id"]."'>".$row["fullname"]."</a></b></td>";

				// Company
                echo "<td class='".$tdcolor."' align='center'>";
                echo give_db_sqlfree_field ("SELECT name FROM tcompany WHERE id = ".$row["id_company"]);

         		// Email
                echo "<td class='".$tdcolor."' align='center'>";
                echo $row["email"];

                // Delete
                echo "<td class='".$tdcolor."' align='center' valign='top'>";
                echo "<a href='index.php?sec=inventory&
				            sec2=operation/contacts/contact_detail&
				            delete=".$row["id"]."'
				            onClick='if (!confirm(\' ".$lang_label["are_you_sure"]."\'))
				            return false;'>
				            <img border='0' src='images/cross.png'></a>";
            }
            echo "</table>";
        }


    } // end of list

?>
