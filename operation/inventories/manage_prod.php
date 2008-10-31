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

if (! give_acl($config["id_user"], 0, "KM")) {
	audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access KB Management");
	require ("general/noaccess.php");
	exit;
}

$id_user = $config["id_user"];

$id = (int) get_parameter ('id');
$create = (bool) get_parameter ('create');
$insert_product = (bool) get_parameter ('insert_product');
$update = (bool) get_parameter ('update');
$get_icon = (bool) get_parameter ('get_icon');

if ($get_icon) {
	$icon = (string) get_db_value ('icon', 'tkb_product', 'id', $id);
	
	if (defined ('AJAX')) {
		echo $icon;
		return;
	}
}


// Database Creation
// ==================
if ($insert_product) {
	$name = (string) get_parameter ("name");
	$parent = (int) get_parameter ("product",0);
	$icon = (string) get_parameter ("icon","");
	$description = (string) get_parameter ("description","");
	$sql = sprintf ('INSERT INTO tkb_product (name, description, parent, icon) 
		  		 VALUES ("%s", "%s", %d, "%s")',
		  		 $name, $description, $parent, $icon);
	$result = mysql_query ($sql);	
	if (! $result)
		echo "<h3 class='error'>".__("KB Product cannot be created")."</h3>"; 
	else {
		echo "<h3 class='suc'>".__("KB Product created ok")."</h3>";
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
		echo "<h3 class='error'>".__("Product cannot be updated")."</h3>"; 
	else {
		echo "<h3 class='suc'>".__("Product updated ok")."</h3>";
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

if ($create || $update) {
	if ($create) {
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

	echo "<h2>".__("Product management")."</h2>";	
	if ($id == -1){
		echo "<h3>".__("Create a new product")."</a></h3>";
		echo "<form name=prodman method='post' action='index.php?sec=inventory&sec2=operation/inventories/manage_prod'>";
		print_input_hidden ('insert_product', 1);
	} else {
		echo "<h3>".__("Update existing product")."</a></h3>";
		echo "<form name=prodman2 method='post' action='index.php?sec=inventory&sec2=operation/inventories/manage_prod&update2'>";
		echo "<input type=hidden name=id value='$id'>";
	}
	
	echo "<table cellpadding=4 cellspacing=4 width=500 class='databox'>";
	echo "<tr>";
	echo "<td class=datos>";
	echo __("Name");
	echo "<td class=datos>";
	echo "<input type=text size=45 name=name value='$name'>";

	echo "<tr>";
	echo "<td class=datos2>";
	echo __("Description");
	echo "<td class=datos2>";
	echo "<input type=text size=50 name=description value='$description'>";

	echo "<tr>";
	echo "<td class=datos>";
	echo __("Icon");
	echo "<td class=datos>";
	
	$files = list_files ('images/products/', "png", 1, 0);
	print_select ($files, 'icon', $icon, '', __('None'), "");
	print_product_icon ($id);
	
	echo "<tr>";
	echo "<td class=datos2>";
	echo __("Parent");
	echo "<td class=datos2>";
	combo_kb_products ($parent, 1);

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

// Show list of product
// =======================
if (! $update && ! $create) {
	echo "<h2>".__("Product management")."</h2>";	
	echo "<h3>".__("Defined products")."</a></h3>";
	$sql1='SELECT * FROM tkb_product ORDER BY parent, name';
	$color =0;
	if ($result=mysql_query($sql1)){
		echo "<table width=700 class='listing'>";
		echo "<th>".__("icon")."</th>";
		echo "<th>".__("Name")."</th>";
		echo "<th>".__("parent")."</th>";
		echo "<th>".__("Description")."</th>";
		echo "<th>".__("Items")."</th>";
		echo "<th>".__("delete")."</th>";
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
			// Icon
			echo "<td class='$tdcolor' valign='top' align='center'>";
			echo "<img src='images/products/".$row["icon"]."' border='0'>";
			echo "</td>";
			// Name
			echo "<td class='$tdcolor' valign='top'><b><a href='index.php?sec=inventory&
					sec2=operation/inventories/manage_prod&update=".$row["id"]."'>".$row["name"]."</a></b></td>";
			// Parent
			echo "<td class='$tdcolor' valign='top'>".give_db_sqlfree_field ("SELECT name FROM tkb_product WHERE id = ".$row["parent"]);
			
			// Descripcion
			echo "<td class='".$tdcolor."f9' align='center' valign='top'>";
			echo $row["description"];

			// Items
			echo "<td class='".$tdcolor."f9' align='center'>";
			echo give_db_sqlfree_field ("SELECT COUNT(id) FROM tkb_data WHERE id_product = ".$row["id"]);

			// Delete
			echo "<td class='".$tdcolor."f9' align='center' valign='top'>";
			echo "<a href='index.php?sec=inventory&
						sec2=operation/inventories/manage_prod&
						delete_prod=".$row["id"]."' 
						onClick='if (!confirm(\' ".__("are_you_sure")."\')) 
						return false;'>
						<img border='0' src='images/cross.png'></a>";
		}
		echo "</table>";
	}			
	echo "<table width=700 class='button'>";
	echo "<tr><td align='right'>";
	echo "<form method=post action='index.php?sec=inventory&sec2=operation/inventories/manage_prod'>";
	print_input_hidden ('create', 1);
	echo "<input type='submit' class='sub next' name='crt' value='".__("Create product")."'>";
	echo "</form></td></tr></table>";
} // end of list

?>

<script type="text/javascript">
$(document).ready (function () {
	$("#icon").change (function () {
		data = this.value;
		$("#product-icon").fadeOut ('normal', function () {
			$("#product-icon").attr ("src", "images/products/"+data).fadeIn ();
		});
	})
});

</script>
