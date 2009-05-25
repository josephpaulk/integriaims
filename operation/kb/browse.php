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

if (give_acl($config["id_user"], 0, "KR")==0) {
    audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access KB Browser");
    require ("general/noaccess.php");
    exit;
}

// Database Creation
// ==================
if (isset($_GET["create2"])){ // Create group
	$timestamp = date('Y-m-d H:i:s');
	$title = get_parameter ("title","");
	$data = get_parameter ("data",0);
	$id_product = get_parameter ("product","");
	$id_category = get_parameter ("category","");
	$id_language = get_parameter ("id_language", "");

	$sql_insert="INSERT INTO tkb_data (title, data, id_product, id_category, id_user, timestamp, id_language) 
		  		 VALUE ('$title','$data', '$id_product', '$id_category', '".$config["id_user"]."', '$timestamp', '$id_language') ";
	$result=mysql_query($sql_insert);	
	if (! $result)
		echo "<h3 class='error'>".__('Could not be created')."</h3>"; 
	else {
		echo "<h3 class='suc'>".__('Successfully created')."</h3>";
		$id_data = mysql_insert_id();
		insert_event ("KB ITEM CREATED", $id_data, 0, $title);
	}
	
}

// Attach DELETE
// ==============
if (isset($_GET["delete_attach"])){
	$id_attachment = get_parameter ("delete_attach", 0);
	$id_kb = get_parameter ("update", 0);
	$attach_row = get_db_row ("tattachment", "id_attachment", $id_attachment);
	$nombre_archivo = $config["homedir"]."attachment/".$id_attachment."_".$attach_row["filename"];	
	$sql = " DELETE FROM tattachment WHERE id_attachment =".$id_attachment;
	mysql_query($sql);
	unlink ($nombre_archivo);
	insert_event ("KB ITEM UPDATED", $id_kb, 0, "File ".$attach_row["filename"]." deleted");
	echo "<h3 class='suc'>".__('Attach deleted ok')."</h3>";
	unset ($id_kb);
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
	$id_language = get_parameter ("id_language", "");

	$sql_update ="UPDATE tkb_data
	SET title = '$title', data = '$data', id_language = '$id_language', timestamp = '$timestamp', id_user = '$id_user',
	id_category = $id_category, id_product = $id_product 
	WHERE id = $id";
	$result=mysql_query($sql_update);
	if (! $result)
		echo "<h3 class='error'>".__('Could not be updated')."</h3>"; 
	else {
		echo "<h3 class='suc'>".__('Successfully updated')."</h3>";
		insert_event ("KB ITEM UPDATED", $id, 0, $title);
	}

	if ( $_FILES['userfile']['name'] != "" ){ //if file
		$tipo = $_FILES['userfile']['type'];
		// Insert into database
		$filename = $_FILES['userfile']['name'];
		$filesize = $_FILES['userfile']['size'];

		$attach_description = get_parameter ("attach_description");

		$sql = "INSERT INTO tattachment (id_kb, id_usuario, filename, description, size ) VALUES (".$id.", '".$config["id_user"]. "','".$filename."','$attach_description', $filesize )";

		mysql_query($sql);
		$id_attachment=mysql_insert_id();
		$result_msg = "<h3 class='suc'>".__('File added')."</h3>";
		// Copy file to directory and change name
		$nombre_archivo = $config["homedir"]."attachment/".$id_attachment."_".$filename;

		if (!(copy($_FILES['userfile']['tmp_name'], $nombre_archivo ))){
			$result_msg = "<h3 class=error>".__('File cannot be saved. Please contact Integria administrator about this error')."</h3>";
			$sql = " DELETE FROM tattachment WHERE id_attachment =".$id_attachment;
			mysql_query($sql);
			unlink ($_FILES['userfile']['tmp_name']);
		} else {
			// Delete temporal file
			insert_event ("KB ITEM UPDATED", $id, 0, "File $filename added");
		}
		echo $result_msg;

	}	
}

// Database DELETE
// ==================
if (isset($_GET["delete_data"])){ // if delete
	$id = get_parameter ("delete_data",0);
	$kb_title = get_db_sql ("SELECT title FROM tkb_data WHERE id = $id ");

	$sql_delete= "DELETE FROM tkb_data WHERE id = $id";		
	$result=mysql_query($sql_delete);
	
	if ($result=mysql_query("SELECT * FROM tattachment WHERE id_kb = $id")) {
		while ($row=mysql_fetch_array($result)){
				$nombre_archivo = $config["homedir"]."attachment/".$row["id_attachment"]."_".$row["filename"];	
				unlink ($nombre_archivo);
		}
		$sql = " DELETE FROM tattachment WHERE id_kb = ".$id;
		mysql_query($sql);
	}
	insert_event ("KB ITEM DELETED", $id, 0, "Deleted KB $kb_title");
	echo "<h3 class='error'>".__('Successfully deleted')."</h3>"; 
}

if (isset($_GET["update2"])){
	$_GET["update"]= $id;
}

// CREATE form
if ((isset($_GET["create"]) OR (isset($_GET["update"])))) {
	if (isset($_GET["create"])){
		
		$data = "";
		$title = "";
		$id = -1;
		$id_product = 1;
		$id_category = 1;	
		$id_language = '';
		$id_incident = (int) get_parameter ("id_incident", 0);
		if ($id_incident) {
			// Get the product id of the first inventory object associated to the incident
			$id_product_db = get_db_sql ('SELECT id_product FROM tinventory WHERE id = (SELECT id_inventory FROM tincident_inventory WHERE id_incident = ' . $id_incident . ' LIMIT 1)');
			if ($id_product_db !== false) {
				$id_product = $id_product_db;
			}
			// Get incident data
			$incident = get_db_row_sql ('SELECT titulo, descripcion, epilog FROM tincidencia WHERE id_incidencia = ' . $id_incident);
			if ($incident !== false) {
				$title = $incident['titulo'];
				$data = $incident['descripcion'] . "\n\n" .$incident['epilog'];
			}
		}
	} else {
		$id = get_parameter ("update",-1);
		$row = get_db_row ("tkb_data", "id", $id);
		$data = $row["data"];
		$title = $row["title"];
		$id_product = $row["id_product"];
		$id_language = $row["id_language"];
		$id_category = $row["id_category"];
	}

	echo "<h2>".__('KB Data management')."</h2>";	
	if ($id == -1){
		echo "<h3>".__('Create a new KB item')."</a></h3>";
		echo "<form name=prodman method='post' action='index.php?sec=kb&sec2=operation/kb/browse&create2'>";
	}
	else {
		echo "<h3>".__('Update existing KB item')."</a></h3>";
		echo "<form enctype='multipart/form-data' name=prodman2 method='post' action='index.php?sec=kb&sec2=operation/kb/browse&update2'>";
		echo "<input type=hidden name=id value='$id'>";
	}
	
	echo '<table width="90%" class="databox">';
	echo "<tr>";
	echo "<td class=datos>";
	echo __('Title');
	echo "<td class=datos>";
	echo "<input type=text size=60 name='title' value='$title'>";

	echo "<tr>";
	echo "<td>";
	echo __('Language');
	echo "<td>";
	echo print_select_from_sql ('SELECT id_language, name FROM tlanguage', 'id_language',
					$id_language, '', __("Any"), '', true, false, false, '');

	echo "<tr>";
	echo "<td class=datos2 valign=top>";
	echo __('Data');
	echo "<td class=datos2>";
	print_textarea ("data", 15, 40, $data, '', false,false);

	echo "<tr>";
	echo "<td class=datos2>";
	echo __('Product');
	echo "<td class=datos2>";
	combo_kb_products ($id_product);

	echo "<tr>";
	echo "<td class=datos>";
	echo __('Category');
	echo "<td class=datos>";
	combo_kb_categories ($id_category);

	if ($id != -1){
		echo "<tr>";
		echo "<td class=datos>";
		echo __('Attach');
		echo "<td class=datos>";
		if ($id == -1)
			echo "<i>".__('Need to create first')."</i>";
		else {
			echo "<input type=file size=60 value='userfile' name='userfile'>";
			echo "<tr>";
			echo "<td class=datos>";
			echo __('Attach description');
			echo "<td class=datos>";
			echo "<input type=text size=60 name='attach_description' value=''>";
		}
	}

	echo "</table>";
	
	echo '<div class="button" style="width:90%">';
	if ($id == -1)
		print_submit_button (__('Create'), 'crt_btn', false, 'class="sub next"');
	else
		print_submit_button (__('Update'), 'upd_btn', false, 'class="sub upd"');
	echo "</div>";
	echo "</form>";

	// Show list of attachments
	$sql1 = "SELECT * FROM tattachment WHERE id_kb = $id ORDER BY description";
	$result = mysql_query($sql1);
	if (mysql_num_rows($result) > 0){
		echo "<h3>".__('Attachment list')."</h3>";
		echo '<table class="databox" width="90%">';
	 	while ($row=mysql_fetch_array($result)){
			echo "<tr>";
			echo "<td>";
			echo "<img src='images/disk.png'>&nbsp;";
			$attach_id = $row["id_attachment"];
			echo '<a href="attachment/'.$row["id_attachment"].'_'.rawurlencode ($row["filename"]).'">';
			echo $row["filename"];
			echo "</a>";
			echo "<td>";
			echo $row["description"];
			echo "<td>";
			echo "<a href='index.php?sec=kb&sec2=operation/kb/browse&update=$id&delete_attach=$attach_id'><img border=0 src='images/cross.png'></A>";
		}
		echo "</table>";
	}
}


if ((isset($_GET["update"])) OR (isset($_GET["create"]))){
	return;
}

// Show list of items
// =======================

echo "<h2>".__('KB Data management')." &raquo; ".__('Defined data')."</a></h2>";

// Search parameter 
$free_text = get_parameter ("free_text", "");
$product = get_parameter ("product", 0);
$category = get_parameter ("category", 0);
$id_language = get_parameter ("id_language", '');

// Search filters
echo '<form method="post">';
echo '<table width="90%" class="blank">';
echo "<tr>";
echo "<td>";
echo __('Product types');
echo "<td>";

echo print_select_from_sql ('SELECT id, name FROM tkb_product ORDER BY name', 'product', $product, '', __("Any"), '', true, false, false, '');

echo "<td>";
echo __('Categories');
echo "<td>";
combo_kb_categories ($category, 1);

echo "<tr>";
echo "<td>";
echo __('Search');
echo "<td>";
echo "<input type=text name='free_text' size=25 value='$free_text'>";

echo "<td>";
echo __('Language');
echo "<td>";
echo print_select_from_sql ('SELECT id_language, name FROM tlanguage', 'id_language',
					$id_language, '', __("Any"), '', true, false, false, '');

echo "<td >";
echo "<input type=submit class='sub search' value='".__('Search')."'>";


echo "</td></tr></table></form>";

// Search filter processing
// ========================

$sql_filter = "WHERE 1=1 ";

if ($free_text != "")
	$sql_filter .= " AND title LIKE '%$free_text%' OR data LIKE '%$free_text%'";

if ($product != 0)
	$sql_filter .= " AND id_product = $product ";

if ($category != 0)
	$sql_filter .= " AND id_category = $category ";

if ($id_language != '')
	$sql_filter .= " AND id_language = '$id_language' ";

$offset = get_parameter ("offset", 0);

$count = get_db_sql("SELECT COUNT(id) FROM tkb_data $sql_filter");
pagination ($count, "index.php?sec=kb&sec2=operation/kb/browse", $offset);

$sql1 = "SELECT * FROM tkb_data $sql_filter ORDER BY title, id_category, id_product LIMIT $offset, ". $config["block_size"];

$color =0;
if ($result=mysql_query($sql1)){
	echo '<table width="90%" class="listing">';

	echo "<th>".__('Title')."</th>";
	echo "<th>".__('Category')."</th>";
	echo "<th>".__('Product')."</th>";
	echo "<th>".__('Language')."</th>";
	echo "<th>".__('Timestamp')."</th>";
	echo "<th>".__('Delete')."</th>";
	
	while ($row=mysql_fetch_array($result)){
		echo "<tr>";
		// Name
		echo "<td valign='top'><b><a href='index.php?sec=kb&sec2=operation/kb/browse_data&view=".$row["id"]."'>".short_string($row["title"],54)."</a></b></td>";

		// Category
		echo "<td class=f9>";
		echo get_db_sql ("SELECT name FROM tkb_category WHERE id = ".$row["id_category"]);

		// Product
		echo "<td class=f9>";
		echo get_db_sql ("SELECT name FROM tkb_product WHERE id = ".$row["id_product"]);

		// Language
		echo "<td class=f9>";
		echo $row["id_language"];

		// Timestamp
		echo "<td class='f9' valign='top'>";
		echo human_time_comparation($row["timestamp"]);

		// Delete
		echo "<td class='f9' align='center' >";
		echo "<a href='index.php?sec=kb&sec2=operation/kb/browse&delete_data=".$row["id"]."' onClick='if (!confirm(\' ".__('Are you sure?')."\')) return false;'><img border='0' src='images/cross.png'></a>";

	}
	echo "</table>";
}			

?>
