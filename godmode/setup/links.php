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

// Load global vars

global $config;

if (check_login() != 0) {
    audit_db("Noauth",$config["REMOTE_ADDR"], "No authenticated access","Trying to access setup ");
    require ("general/noaccess.php");
    exit;
}
    
if (dame_admin($config["id_user"]) == 0){
    audit_db("ACL Violation",$config["REMOTE_ADDR"], "No administrator access","Trying to access setup");
    require ("general/noaccess.php");
    exit;
}

	if (isset($_POST["create"])){ // If create
		$name = clean_input ($_POST["name"]);
		$link = clean_input ($_POST["link"]);
		$sql_insert="INSERT INTO tlink (name,link) VALUES ('$name','$link') ";
		$result=mysql_query($sql_insert);	
		if (! $result)
			echo "<h3 class='error'>".$lang_label["create_link_no"]."</h3>";
		else {
			echo "<h3 class='suc'>".$lang_label["create_link_ok"]."</h3>"; 
			$id_link = mysql_insert_id();
		}
	}

	if (isset($_POST["update"])){ // if update
		$id_link = entrada_limpia($_POST["id_link"]);
		$name = entrada_limpia($_POST["name"]);
		$link = entrada_limpia($_POST["link"]);
    	$sql_update ="UPDATE tlink SET name = '".$name."', link ='".$link."'  WHERE id_link = '".$id_link."'";
		$result=mysql_query($sql_update);
		if (! $result)
			echo "<h3 class='error'>".$lang_label["modify_link_no"]."</h3>";
		else
			echo "<h3 class='suc'>".$lang_label["modify_link_ok"]."</h3>";
	}
	
	if (isset($_GET["borrar"])){ // if delete
		$id_link = clean_input($_GET["borrar"]);
		$sql_delete= "DELETE FROM tlink WHERE id_link = ".$id_link;
		$result=mysql_query($sql_delete);
		if (! $result)
			echo "<h3 class='error'>".$lang_label["delete_link_no"]."</h3>";
		else
			echo "<h3 class='suc'>".$lang_label["delete_link_ok"]."</h3>"; 

	}

	// Main form view for Links edit
	if ((isset($_GET["form_add"])) or (isset($_GET["form_edit"]))){
		if (isset($_GET["form_edit"])){
			$creation_mode = 0;
				$id_link = entrada_limpia($_GET["id_link"]);
				$sql1='SELECT * FROM tlink WHERE id_link = '.$id_link;
				$result=mysql_query($sql1);
				if ($row=mysql_fetch_array($result)){
						$nombre = $row["name"];
				$link = $row["link"];
                	}
				else echo "<h3 class='error'>".$lang_label["name_error"]."</h3>";
		} else { // form_add
			$creation_mode =1;
			$nombre = "";
			$link = "";
		}

		// Create link
        echo "<h2>".$lang_label["setup_screen"]."</h2>";
		echo "<h3>".$lang_label["link_management"]."</h3>";
		echo '<table class="fon" cellpadding="4" cellspacing="4" width="500" class="databox_color">';   
		echo '<form name="ilink" method="post" action="index.php?sec=godmode&sec2=godmode/setup/links">';
        	if ($creation_mode == 1)
				echo "<input type='hidden' name='create' value='1'>";
			else
				echo "<input type='hidden' name='update' value='1'>";
		echo "<input type='hidden' name='id_link' value='"; 
		if (isset($id_link)) {
            echo $id_link;
        }
		
		echo "'>";
		echo '<tr><td class="datos">'.$lang_label["link_name"].'<td class="datos"><input type="text" name="name"  value="'.$nombre.'">';
		echo '<tr><td class="datos2">'.$lang_label["link"].'<td class="datos2"><input type="text" name="link"  value="'.$link.'">';
		echo "<tr><td colspan='3' align='right'><input name='crtbutton' type='submit' class='sub' value='".$lang_label["update"]."'>";
		echo '</form></table>';
	}

	else {  // Main list view for Links editor
		echo "<h2>".$lang_label["setup_screen"]."</h2>";
		echo "<h3>".$lang_label["link_management"]."</h3>";
		echo "<table cellpadding=4 cellspacing=4 class=databox_color width=500>";
		echo "<th>".$lang_label["link_name"];
        echo "<th>URL";
		echo "<th>".$lang_label["delete"];
		$sql1='SELECT * FROM tlink ORDER BY name';
		$result=mysql_query($sql1);
		$color=1;
		while ($row=mysql_fetch_array($result)){
			if ($color == 1){
				$tdcolor = "datos";
				$color = 0;
			}
			else {
				$tdcolor = "datos2";
				$color = 1;
			}
			echo "<tr><td class='$tdcolor'><b><a href='index.php?sec=godmode&sec2=godmode/setup/links&form_edit=1&id_link=".$row["id_link"]."'>".$row["name"]."</a></b>";
            echo '<td class="'.$tdcolor.'" align="center">'.$row["link"];
			echo '<td class="'.$tdcolor.'" align="center"><a href="index.php?sec=godmode&sec2=godmode/setup/links&id_link='.$row["id_link"].'&borrar='.$row["id_link"].'" onClick="if (!confirm(\' '.$lang_label["are_you_sure"].'\')) return false;"><img border=0 src="images/cross.png"></a>';
		}
		
			echo "<tr><td colspan='3' align='right'>";
			echo "<form method='post' action='index.php?sec=godmode&sec2=godmode/setup/links&form_add=1'>";
			echo "<input type='submit' class='sub next' name='form_add' value='".$lang_label["add"]."'>";
			echo "</form></table>";
    }