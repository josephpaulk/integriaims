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

if (give_acl($config["id_user"], 0, "KM")==0) {
    audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access KB Management");
    require ("general/noaccess.php");
    exit;
}

$id_user = $config["id_user"];

    // Database Creation
    // ==================
	if (isset($_GET["create2"])){ // Create group
		$name = get_parameter ("name","");
        $parent = get_parameter ("category",0);
        $icon = get_parameter ("icon","");
        $description = get_parameter ("description","");
		$sql_insert="INSERT INTO tkb_product (name, description, parent, icon) 
              		 VALUE ('$name','$description', '$parent', '$icon') ";
		$result=mysql_query($sql_insert);	
		if (! $result)
			echo "<h3 class='error'>".lang_string ("KB Product cannot be created")."</h3>"; 
		else {
			echo "<h3 class='suc'>".lang_string ("KB Product created ok")."</h3>";
            $id_cat = mysql_insert_id();
            insert_event ("PRODUCT CREATED", $id_cat, 0, $name);
		}
        
	}


    // Database UPDATE
    // ==================
	if (isset($_GET["update2"])){ // if modified any parameter
        $id = get_parameter ("id","");
		$name = get_parameter ("name","");
        $parent = get_parameter ("product",0);
        $icon = get_parameter ("icon","");
        $description = get_parameter ("description","");
	    $sql_update ="UPDATE tkb_product
		SET name = '$name', icon = '$icon', description = '$description', parent = '$parent' 
		WHERE id = $id";
		$result=mysql_query($sql_update);
		if (! $result)
			echo "<h3 class='error'>".lang_string ("KB Product cannot be updated")."</h3>"; 
		else {
			echo "<h3 class='suc'>".lang_string ("KB Product updated ok")."</h3>";
            insert_event ("PRODUCT UPDATED", $id, 0, $name);
        }
	}


    // Database DELETE
    // ==================
	if (isset($_GET["delete_prod"])){ // if delete
        $id = get_parameter ("delete_prod",0);
		
		// First delete from tagente_modulo
		$sql_delete= "DELETE FROM tkb_product WHERE id = $id";
		$result=mysql_query($sql_delete);

        // Move parent who has this product to 0
        mysql_query("UPDATE tkb_product SET parent = 0 WHERE parent = $id");        
		if (! $result)
			echo "<h3 class='error'>".lang_string("Deleted successfully")."</h3>"; 
		else
			echo "<h3 class='suc'>".lang_string("Cannot be deteled")."</h3>";
	}
	


    // CREATE form
    if ((isset($_GET["create"]) OR (isset($_GET["update"])))) {
        if (isset($_GET["create"])){
            $icon = "";
            $description = "";
            $name = "";
            $id = -1;
            $parent = -1;
        } else {
            $id = get_parameter ("update", -1);
            $row = get_db_row ("tkb_product", "id", $id);
            $description = $row["description"];
            $name = $row["name"];
            $icon = $row["icon"];
            $parent = $row["parent"];
        }

        echo "<h2>".lang_string ("KB Product management")."</h2>";	
        if ($id == -1){
        	echo "<h3>".lang_string ("Create a new product")."</a></h3>";
            echo "<form name=prodman method='post' action='index.php?sec=kb&sec2=operation/kb/manage_prod&create2'>";
        }
        else {
            echo "<h3>".lang_string ("Update existing product")."</a></h3>";
            echo "<form name=prodman2 method='post' action='index.php?sec=kb&sec2=operation/kb/manage_prod&update2'>";
            echo "<input type=hidden name=id value='$id'>";
        }
        
        echo "<table width=500 class='databox'>";
        echo "<tr>";
        echo "<td class=datos>";
        echo lang_string ("Name");
        echo "<td class=datos>";
        echo "<input type=text size=45 name=name value='$name'>";

        echo "<tr>";
        echo "<td class=datos2>";
        echo lang_string ("Description");
        echo "<td class=datos2>";
        echo "<input type=text size=50 name=description value='$description'>";

        echo "<tr>";
        echo "<td class=datos>";
        echo lang_string ("Icon");
        echo "<td class=datos>";
        echo '<select name="icon">';
    	if ($icon != ""){
		    echo '<option>' . $icon;
    	}
        $ficheros = list_files ('images/groups_small/', "png", 1, 0);
    	$size = count ($ficheros);
	    for ($i = 0; $i < $size; $i++) {
    		echo "<option>".substr($ficheros[$i],0,strlen($ficheros[$i])-4);
    	}
    	echo '</select>';

        echo "<tr>";
        echo "<td class=datos2>";
        echo lang_string ("Parent");
        echo "<td class=datos2>";
        combo_kb_products ($parent);

        echo "</table>";
        echo "<table class='button' width=500>";
        echo "<tr>";
        echo "<td align=right>";
        if ($id == -1)
            echo "<input type=submit class='sub next' value='Create'>";
        else
            echo "<input type=submit class='sub upd' value='Update'>";
        echo "</table></form>";

    }


    // Show list of product
    // =======================
    if ((!isset($_GET["update"])) AND (!isset($_GET["create"]))){
        echo "<h2>".lang_string ("KB Product management")."</h2>";	
    	echo "<h3>".lang_string ("Defined products")."</a></h3>";
	    $sql1='SELECT * FROM tkb_product ORDER BY parent, name';
	    if ($result=mysql_query($sql1)){
            echo "<table width=700 class='listing'>";
	        echo "<th>".lang_string ("icon")."</th>";
	        echo "<th>".lang_string ("Name")."</th>";
	        echo "<th>".lang_string ("parent")."</th>";
	        echo "<th>".lang_string ("Description")."</th>";
	        echo "<th>".lang_string ("Items")."</th>";
	        echo "<th>".lang_string ("delete")."</th>";
	        while ($row=mysql_fetch_array($result)){
		        echo "<tr>";
                // Icon
                echo "<td valign='top' align='center'>";
		        echo "<img src='images/groups_small/".$row["icon"].".png'border='0'>";
                echo "</td>";
                // Name
                echo "<td valign='top'><b><a href='index.php?sec=kb&
				        sec2=operation/kb/manage_prod&update=".$row["id"]."'>".$row["name"]."</a></b></td>";
                // Parent
                echo "<td valign='top'>".give_db_sqlfree_field ("SELECT name FROM tkb_product WHERE id = ".$row["parent"]);

                // Descripcion
                echo "<td class='f9' valign='top'>";
                echo $row["description"];

                // Items
                echo "<td class='f9'>";
                echo give_db_sqlfree_field ("SELECT COUNT(id) FROM tkb_data WHERE id_product = ".$row["id"]);

                // Delete
                echo "<td class='f9' align='center' valign='top'>";
                echo "<a href='index.php?sec=kb&
				            sec2=operation/kb/manage_prod&
				            delete_prod=".$row["id"]."' 
				            onClick='if (!confirm(\' ".$lang_label["are_you_sure"]."\')) 
				            return false;'>
				            <img border='0' src='images/cross.png'></a>";
            }
            echo "</table>";
        }			
	echo "<table width=700 class='button'>";
    	echo "<tr><td align='right'>";
	echo "<form method=post action='index.php?sec=kb&
	    sec2=operation/kb/manage_prod&create=1'>";
	echo "<input type='submit' class='sub next' name='crt' value='".lang_string("Create product")."'>";
	    echo "</form></td></tr></table>";
    } // end of list

?>
