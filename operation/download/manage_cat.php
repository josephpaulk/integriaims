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

if (! give_acl ($config["id_user"], 0, "KM")) {
	audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access Download Management");
	require ("general/noaccess.php");
	exit;
}

$id_user = $config["id_user"];

// Database Creation
// ==================
if (isset($_GET["create2"])){ // Create group
	$name = get_parameter ("name","");
	$id_group = get_parameter ("id_group", 0);

	$sql_insert="INSERT INTO tdownload_category (name, id_group, icon) 
		  		 VALUE ('$name',$id_group, '$icon')";
	$result=mysql_query($sql_insert);	
	if (! $result)
		echo "<h3 class='error'>".__('Could not be created')."</h3>"; 
	else {
		echo "<h3 class='suc'>".__('Successfully created')."</h3>";
		$id_cat = mysql_insert_id();
		insert_event ("DOWNLOAD CATEGORY CREATED", $id_cat, 0, $name);
	}
	
}


// Database UPDATE
// ==================
if (isset($_GET["update2"])){ // if modified any parameter
	$id = get_parameter ("id","");
		$name = get_parameter ("name","");
	$id_group = get_parameter ("id_group", 0);
	$icon = get_parameter ("icon", "");

	$sql_update ="UPDATE tdownload_category
	SET name = '$name', icon = '$icon', id_group = $id_group 
	WHERE id = $id";
	$result=mysql_query($sql_update);
	if (! $result)
		echo "<h3 class='error'>".__('Could not be updated')."</h3>"; 
	else {
		echo "<h3 class='suc'>".__('Successfully updated')."</h3>";
		insert_event ("DOWNLOAD CATEGORY UPDATED", $id, 0, $name);
	}
}


// Database DELETE
// ==================
if (isset($_GET["delete_cat"])){ // if delete
	$id = get_parameter ("delete_cat",0);
	// First delete from tagente_modulo
	$sql_delete= "DELETE FROM tdownload_category WHERE id = $id";
	$result=mysql_query($sql_delete);
	if (! $result)
		echo "<h3 class='error'>".__('Successfully deleted')."</h3>"; 
	else
		echo "<h3 class='suc'>".__('Cannot be deteled')."</h3>";
}



// CREATE form
if ((isset($_GET["create"]) OR (isset($_GET["update"])))) {
	if (isset($_GET["create"])){
		$id_group = 0;
		$name = "";
		$id = -1;
	} else {
		$id = get_parameter ("update",-1);
		$row = get_db_row ("tdownload_category", "id", $id);
		$name = $row["name"];
		$icon = $row["icon"];
		$id_group = $row["id_group"];

	}

	echo "<h2>".__('File release category management')."</h2>";	
	if ($id == -1){
		echo "<h3>".__('Create a new category')."</a></h3>";
		echo "<form name=catman method='post' action='index.php?sec=download&
						sec2=operation/download/manage_cat&create2'>";
	}
	else {
		echo "<h3>".__('Update existing category')."</a></h3>";
		echo "<form name=catman method='post' action='index.php?sec=download&
						sec2=operation/download/manage_cat&update2'>";
		echo "<input type=hidden name=id value='$id'>";
	}
	
	echo '<table width="90%" class="databox">';
	echo "<tr>";
	echo "<td class=datos>";
	echo __('Name');
	echo "<td class=datos>";
	echo "<input type=text size=20 name=name value='$name'>";


	echo "<tr>";
	echo "<td class=datos2>";
	echo __('Group');
	echo "<td class=datos2>";
	combo_groups_visible_for_me ($config["id_user"], 'id_group', 1, 'KR', $id_group, false, 0 );

	echo "<tr>";
        echo "<td class=datos>";
	echo __('Icon');
	echo "<td class=datos>";
	$files = list_files ('images/download_category/', "png", 1, 0);
	print_select ($files, 'icon', $icon, '', __('None'), "");

	echo "</table>";
	echo '<div class="button" style="width:90%">';
	if ($id == -1)
		print_submit_button (__('Create'), 'crt_btn', false, 'class="sub next"');
	else
		print_submit_button (__('Update'), 'upd_btn', false, 'class="sub upd"');
	echo "</div></form>";

}

// Show list of categories
// =======================
if ((!isset($_GET["update"])) AND (!isset($_GET["create"]))){
	echo "<h2>".__('File release category management')." &raquo; ".__('Defined categories')."</h2>";
	$sql1='SELECT * FROM tdownload_category ORDER BY name';
	$color =0;
	if ($result=mysql_query($sql1)){
		echo '<table width="90%" class="listing">';
		echo "<th>".__('Name')."</th>";
		echo "<th>".__('Group')."</th>";
		echo "<th>".__('Items')."</th>";
		echo "<th>".__('Delete')."</th>";
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
			echo "<td class='$tdcolor' valign='top'><b><a href='index.php?sec=download&
					sec2=operation/download/manage_cat&update=".$row["id"]."'>".$row["name"]."</a></b></td>";

			// Group
			echo "<td class='$tdcolor' valign='top'>";
			echo dame_nombre_grupo($row["id_group"]);

			// Items
			echo "<td class='".$tdcolor."f9' align='center'>";
			echo get_db_sql ("SELECT COUNT(id) FROM tdownload WHERE id_category = ".$row["id"]);

			// Delete
			echo "<td class='".$tdcolor."f9' align='center' valign='top'>";
			echo "<a href='index.php?sec=download&
						sec2=operation/download/manage_cat&
						delete_cat=".$row["id"]."' 
						onClick='if (!confirm(\' ".__('Are you sure?')."\')) 
						return false;'>
						<img border='0' src='images/cross.png'></a>";
		}
		echo "</table>";
	}			
	echo '<div style="width:90%" class="button">';
	echo "<form method=post action='index.php?sec=download&sec2=operation/download/manage_cat&create=1'>";
	print_submit_button (__('Create'), 'crt_btn', false, 'class="sub next"');
	echo "</form></div>";
} // end of list

?>
