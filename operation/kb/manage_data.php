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

if (give_acl($config["id_user"], 0, "KM")==0) {
    audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access KB Management");
    require ("general/noaccess.php");
    exit;
}

$id_user = $config["id_user"];

/*

CREATE TABLE `tkb_data` (
  `id` int(6) unsigned NOT NULL auto_increment,
  `title` varchar(250) default NULL,
  `data` mediumtext NOT NULL,
  `timestamp` datetime NOT NULL default '0000-00-00 00:00:00',
  `id_user` varchar(150) NOT NULL default '',
  `id_attachment` bigint(20) unsigned default 0,  
  `id_product` mediumint(8) unsigned default 0,
  `id_category` mediumint(8) unsigned default 0,

*/

    // Database Creation
    // ==================
	if (isset($_GET["create2"])){ // Create group
        $timestamp = date('Y-m-d H:i:s');
		$title = get_parameter ("title","");
        $data = get_parameter ("data",0);
        $id_product = get_parameter ("product","");
        $id_category = get_parameter ("category","");
		$sql_insert="INSERT INTO tkb_data (title, data, id_product, id_category, id_user, timestamp) 
              		 VALUE ('$title','$data', '$id_product', '$id_category', '".$config["id_user"]."', '$timestamp') ";
		$result=mysql_query($sql_insert);	
		if (! $result)
			echo "<h3 class='error'>".lang_string ("KB data item cannot be created")."</h3>"; 
		else {
			echo "<h3 class='suc'>".lang_string ("KB data item created ok")."</h3>";
            $id_data = mysql_insert_id();
            insert_event ("KB ITEM CREATED", $id_data, 0, $title);
		}
        
	}


    // Database UPDATE
    // ==================
	if (isset($_GET["update2"])){ // if modified any parameter
        $id = get_parameter ("id","");
		        $timestamp = date('Y-m-d H:i:s');
		$title = get_parameter ("title","");
        $data = get_parameter ("data",0);
        $id_product = get_parameter ("product","");
        $id_category = get_parameter ("category","");
        $id_user = $config["id_user"];

	    $sql_update ="UPDATE tkb_data
		SET title = '$title', data = '$data', timestamp = '$timestamp', id_user = '$id_user',
        id_category = $id_category, id_product = $id_product 
		WHERE id = $id";
		$result=mysql_query($sql_update);
		if (! $result)
			echo "<h3 class='error'>".lang_string ("KB data item cannot be updated")."</h3>"; 
		else {
			echo "<h3 class='suc'>".lang_string ("KB data item updated ok")."</h3>";
            insert_event ("KB ITEM UPDATED", $id, 0, $title);
        }
	}


    // Database DELETE
    // ==================
	if (isset($_GET["delete_data"])){ // if delete
        $id = get_parameter ("delete_data",0);
		
		// First delete from tagente_modulo
		$sql_delete= "DELETE FROM tkb_data WHERE id = $id";
		$result=mysql_query($sql_delete);
		if (! $result)
			echo "<h3 class='error'>".lang_string("Deleted successfully")."</h3>"; 
		else
			echo "<h3 class='suc'>".lang_string("Cannot be deteled")."</h3>";
	}
	


    // CREATE form
    if ((isset($_GET["create"]) OR (isset($_GET["update"])))) {
        if (isset($_GET["create"])){
            
            $data = "";
            $title = "";
            $id = -1;
            $id_product = 0;
            $id_category = 0;    
        } else {
            $id = get_parameter ("update",-1);
            $row = give_db_row ("tkb_data", "id", $id);
            $data = $row["data"];
            $title = $row["title"];
            $id_product = $row["id_product"];
            $id_category = $row["id_category"];
        }

        echo "<h2>".lang_string ("KB Data management")."</h2>";	
        if ($id == -1){
        	echo "<h3>".lang_string ("Create a new KB item")."</a></h3>";
            echo "<form name=prodman method='post' action='index.php?sec=kb&sec2=operation/kb/manage_data&create2'>";
        }
        else {
            echo "<h3>".lang_string ("Update existing KB item")."</a></h3>";
            echo "<form name=prodman2 method='post' action='index.php?sec=kb&sec2=operation/kb/manage_data&update2'>";
            echo "<input type=hidden name=id value='$id'>";
        }
        
        echo "<table cellpadding=4 cellspacing=4 width=500 class='databox'>";
        echo "<tr>";
        echo "<td class=datos>";
        echo lang_string ("Title");
        echo "<td class=datos>";
        echo "<input type=text size=20 name='title' value='$title'>";

        echo "<tr>";
        echo "<td class=datos2>";
        echo lang_string ("Data");
        echo "<td class=datos2>";
        echo "<textarea cols=60 rows=10 name=data>$data</textarea>";

        echo "<tr>";
        echo "<td class=datos>";
        echo lang_string ("Attach");
        echo "<td class=datos>";
        if ($id = -1)
            echo "<i>".lang_string ("Need to create first")."</i>";


        echo "<tr>";
        echo "<td class=datos2>";
        echo lang_string ("Product");
        echo "<td class=datos2>";
        combo_kb_products ($id_product);

        echo "<tr>";
        echo "<td class=datos>";
        echo lang_string ("Category");
        echo "<td class=datos>";
        combo_kb_categories ($id_category);

        echo "</table>";
        echo "<table cellpadding=4 cellspacing=4 width=500>";
        echo "<tr>";
        echo "<td align=right>";
        if ($id == -1)
            echo "<input type=submit class='sub next' value='Create'>";
        else
            echo "<input type=submit class='sub upd' value='Update'>";
        echo "</table></form>";

    }


    // Show list of items
    // =======================
    if ((!isset($_GET["update"])) AND (!isset($_GET["create"]))){
        echo "<h2>".lang_string ("KB Data management")."</h2>";	
    	echo "<h3>".lang_string ("Defined data")."</a></h3>";
	    $sql1='SELECT * FROM tkb_data ORDER BY title, id_category, id_product';
        $color =0;
	    if ($result=mysql_query($sql1)){
            echo "<table cellpadding=4 cellspacing=4 width=750 class='databox'>";

	        echo "<th>".lang_string ("Title")."</th>";
	        echo "<th>".lang_string ("Timestamp")."</th>";
	        echo "<th>".lang_string ("Category")."</th>";
	        echo "<th>".lang_string ("Product")."</th>";
	        echo "<th>".lang_string ("File")."</th>";
	        echo "<th>".lang_string ("User")."</th>";
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
                echo "<td class='$tdcolor' valign='top'><b><a href='index.php?sec=kb&sec2=operation/kb/manage_data&update=".$row["id"]."'>".$row["title"]."</a></b></td>";

                // Timestamp
                echo "<td class='".$tdcolor."f9' align='center' valign='top'>";
                echo $row["timestamp"];

                // Category
                echo "<td class='".$tdcolor."' align='center'>";
                echo give_db_sqlfree_field ("SELECT name FROM tkb_category WHERE id = ".$row["id_category"]);
    
                // Product
                echo "<td class='".$tdcolor."' align='center'>";
                echo give_db_sqlfree_field ("SELECT name FROM tkb_product WHERE id = ".$row["id_product"]);
    
                // Attach ?
                echo "<td class='".$tdcolor."' align='center'>";
                if (give_db_sqlfree_field ("SELECT count(*) FROM tattachment WHERE id_kb = ".$row["id_product"]) != 0)
                    echo "<img src='images/disk.png'>";

                // User
                echo "<td class='".$tdcolor."f9' align='center'>";
                echo $row["id_user"];

                // Delete
                echo "<td class='".$tdcolor."f9' align='center' valign='top'>";
                echo "<a href='index.php?sec=kb&
				            sec2=operation/kb/manage_data&
				            delete_data=".$row["id"]."' 
				            onClick='if (!confirm(\' ".$lang_label["are_you_sure"]."\')) 
				            return false;'>
				            <img border='0' src='images/cross.png'></a>";
            }
            echo "</table>";
        }			
        echo "<table cellpadding=4 cellspacing=4 width=750>";
	    echo "<tr><td align='right'>";
	    echo "<form method=post action='index.php?sec=kb&
	    sec2=operation/kb/manage_data&create=1'>";
	    echo "<input type='submit' class='sub next' name='crt' value='".lang_string("Create data")."'>";
	    echo "</form></td></tr></table>";
    } // end of list

?>
