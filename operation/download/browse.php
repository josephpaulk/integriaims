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
    audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access Downloads browser");
    require ("general/noaccess.php");
    exit;
}

function get_download_files () {
	$base_dir = 'attachment/downloads';
	$files = list_files ($base_dir, ".", 1, 0);
	
	$retval = array ();
	foreach ($files as $file) {
		$retval[$file] = $file;
	}
	
	return $retval;
}

// Database Creation
// ==================
if (isset($_GET["create2"])){ // Create group

	if (give_acl($config["id_user"], 0, "KW") != 1){
		audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to crete a new Download file without privileges");
	    require ("general/noaccess.php");
    	exit;
    }
	$timestamp = date('Y-m-d H:i:s');
	$name = get_parameter ("name","");
	$location = get_parameter ("location","");
	$description = get_parameter ("description","");
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

	if (give_acl($config["id_user"], 0, "KW") != 1){
		audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to delete an attach on a KB without privileges");
	    require ("general/noaccess.php");
    	exit;
    }

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

	if (give_acl($config["id_user"], 0, "KW") != 1){
		audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to update an article on KB without privileges");
	    require ("general/noaccess.php");
    	exit;
    }


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

	if (give_acl($config["id_user"], 0, "KW") != 1){
		audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to delete a KB without privileges");
	    require ("general/noaccess.php");
    	exit;
    }

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
		$name = "";
		$location = "";
		$id_category = 1;
		$description = "";	
	} else {
		$id = get_parameter ("update",-1);
		$row = get_db_row ("tdownload", "id", $id);
		$name = $row["name"];
		$description =$row["description"];
		$location = $row["location"];
		$id_category = $row["id_category"];
		$date = $row["date"];
	}

	echo "<h2>".__('File releases management')."</h2>";	
	if ($id == -1){
		echo "<h3>".__('Create a new file release')."</a></h3>";
		echo "<form name=prodman method='post' action='index.php?sec=kb&sec2=operation/download/browse&create2'>";
	}
	else {
		echo "<h3>".__('Update existing file release')."</a></h3>";
		echo "<form enctype='multipart/form-data' name=prodman2 method='post' action='index.php?sec=kb&sec2=operation/download/browse&update2'>";
		echo "<input type=hidden name=id value='$id'>";
	}
	
	echo '<table width="90%" class="databox">';
	echo "<tr>";
	echo "<td class=datos>";
	echo __('Name');
	echo "<td class=datos>";
	echo "<input type=text size=40 name='title' value='$name'>";

	echo "<tr>";
	echo "<td>";
	echo __("Choose file from repository");
	echo "<td>";
	print_select (get_download_files(), 'file', $location, '', '', '', false);

	echo "<tr>";
	echo "<td class=datos2 valign=top>";
	echo __('Description');
	echo "<td class=datos2>";
	print_textarea ("description", 5, 40, $description, '', false,false);

	echo "<tr>";
	echo "<td class=datos>";
	echo __('Main category');
	echo "<td class=datos>";
	combo_kb_categories ($id_category);
	echo "</table>";
	
	echo '<div class="button" style="width:90%">';
	if ($id == -1)
		print_submit_button (__('Create'), 'crt_btn', false, 'class="sub next"');
	else
		print_submit_button (__('Update'), 'upd_btn', false, 'class="sub upd"');
	echo "</div>";
	echo "</form>";
}


if ((isset($_GET["update"])) OR (isset($_GET["create"]))){
	return;
}

// Show list of items
// =======================

echo "<h2>".__('Downloads')." &raquo; ".__('Defined data')."</a></h2>";

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
echo __('Categories');
echo "<td>";
combo_kb_categories ($category, 1);

echo "<tr>";
echo "<td>";
echo __('Search');
echo "<td>";
echo "<input type=text name='free_text' size=25 value='$free_text'>";

echo "<td >";
echo "<input type=submit class='sub search' value='".__('Search')."'>";


echo "</td></tr></table></form>";

// Search filter processing
// ========================

$sql_filter = "WHERE 1=1 ";

if ($free_text != "")
	$sql_filter .= " AND title LIKE '%$free_text%' OR data LIKE '%$free_text%'";
if ($category != 0)
	$sql_filter .= " AND id_category = $category ";
$offset = get_parameter ("offset", 0);

$count = get_db_sql("SELECT COUNT(id) FROM tkb_data $sql_filter");
pagination ($count, "index.php?sec=kb&sec2=operation/kb/browse", $offset);

$sql1 = "SELECT * FROM tkb_data $sql_filter ORDER BY title, id_category, id_product LIMIT $offset, ". $config["block_size"];

$color =0;
if ($result=mysql_query($sql1)){
	echo '<table width="90%" class="listing">';

	echo "<th>".__('Name')."</th>";
	echo "<th>".__('Size')."</th>";
	echo "<th>".__('Description')."</th>";
	echo "<th>".__('Downloads')."</th>";
	echo "<th>".__('Date')."</th>";
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
