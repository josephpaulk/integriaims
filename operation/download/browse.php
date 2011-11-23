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
	$id_category = get_parameter ("id_category","");

	$sql_insert="INSERT INTO tdownload (name, location, description, id_category, id_user, date) 
		  		 VALUE ('$name','attachment/downloads/$location', '$description', '$id_category', '".$config["id_user"]."', '$timestamp') ";
	$result=mysql_query($sql_insert);	
	if (! $result)
		echo "<h3 class='error'>".__('Could not be created')."</h3>"; 
	else {
		echo "<h3 class='suc'>".__('Successfully created')."</h3>";
		$id_data = mysql_insert_id();
		insert_event ("DOWNLOAD ITEM CREATED", $id_data, 0, $name);
	}
}

// Database UPDATE
// ==================
if (isset($_GET["update2"])){ // if modified any parameter

	if (give_acl($config["id_user"], 0, "KW") != 1){
		audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to update a download without privileges");
	    require ("general/noaccess.php");
    	exit;
    }

	$id = get_parameter ("id","");
	$timestamp = date('Y-m-d H:i:s');

	$name = get_parameter ("name","");
	$location = get_parameter ("location","");
	$description = get_parameter ("description","");
	$id_category = get_parameter ("id_category","");


	$sql_update ="UPDATE tdownload
	SET name = '$name', location = 'attachment/downloads/$location', description = '$description', id_category = $id_category WHERE id = $id";
	$result=mysql_query($sql_update);

	if (! $result)
		echo "<h3 class='error'>".__('Could not be updated')."</h3>"; 
	else {
		echo "<h3 class='suc'>".__('Successfully updated')."</h3>";
		insert_event ("DOWNLOAD ITEM UPDATED", $id, 0, $name);
	}
}

// ==================================================================
// Database DELETE
// ==================================================================

if (isset($_GET["delete_data"])){ // if delete

	if (give_acl($config["id_user"], 0, "KW") != 1){
		audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to delete a Download without privileges");
		require ("general/noaccess.php");
		exit;
	}

	$id = get_parameter ("delete_data",0);
	$download_title = get_db_sql ("SELECT name FROM tdownload WHERE id = $id ");
	$sql_delete= "DELETE FROM tdownload WHERE id = $id";		
	$result=mysql_query($sql_delete);

	$sql_delete= "DELETE FROM tdownload_tracking WHERE id_download = $id";		
	$result=mysql_query($sql_delete);

	insert_event ("DOWNLOAD ITEM DELETED", $id, 0, "Deleted Download $download_title");
	echo "<h3 class='error'>".__('Successfully deleted')."</h3>"; 
}

if (isset($_GET["update2"])){
	$_GET["update"]= $id;
}

// ==================================================================
// CREATE form
// ==================================================================

if ((isset($_GET["create"]) OR (isset($_GET["update"])))) {
	if (isset($_GET["create"])){	
		$name = "";
		$location = "";
		$id_category = 1;
		$id = -1;
		$description = "";	
	} else {
		$id = get_parameter ("update",-1);
		$row = get_db_row ("tdownload", "id", $id);
		$name = $row["name"];
		$description =$row["description"];
		$location = $row["location"];
		$id_category = $row["id_category"];
		$timestamp = $row["date"];
		$down_id_user = $row["id_user"];
	}

	echo "<h2>".__('File releases management')."</h2>";	
	
	$current_directory = get_parameter ("directory", "/");

	// Upload file
	if (isset($_GET["upload_file"])) {

		if (isset($_FILES["userfile"]) && ( $_FILES['userfile']['name'] != "" )){ //if file
			$tipo = $_FILES['userfile']['type'];

			$filename= $_FILES['userfile']['name'];
			$filesize = $_FILES['userfile']['size'];
			$directory = get_parameter ("directory","");

			// Copy file to directory and change name
			$nombre_archivo = $config["homedir"]."/".$directory."/".$filename;
			if (!(copy($_FILES['userfile']['tmp_name'], $nombre_archivo))){
				echo "<h3 class=error>".__("attach_error")."</h3>";
			} else {
				// Delete temporal file
				echo "<h3 class=suc>".__("attach_success")."</h3>";
				$location = $nombre_archivo;
				unlink ($_FILES['userfile']['tmp_name']);
			}
			
		}
	}

	// A miminal security check to avoid directory traversal
	if (preg_match("/\.\./", $current_directory))
		$current_directory = "images";
	if (preg_match("/^\//", $current_directory))
		$current_directory = "images";
	if (preg_match("/^manager/", $current_directory))
		$current_directory = "images";

	echo "<h3>".__('Update a new file')."</a></h3>";

	echo "<form method='post' action='index.php?sec=download&sec2=operation/download/browse&create=1&upload_file' enctype='multipart/form-data'>";
	echo "<table>";

	echo "<input type='hidden' name='directory' value='attachment/downloads'>";
	if (is_writable($current_directory)) {
		echo "<tr><td class='datos'>";
		echo __("Upload new file");
		echo "<td class='datos'>";
		echo "<input type='file' size=25 name='userfile' value='userfile'>";
		echo "&nbsp;&nbsp;";
		echo "<input type=submit value='".__("Upload")."'>";
	} else {
		echo "<h3 class='error'>".__('Current directory is not writtable by HTTP Server')."</h3>";
		echo "<p>";
		echo __('Please check that current directory has write rights for HTTP server');
		echo "</p>";
	}
	echo "</table>";
	echo "</form>";
	
	if ($id == -1){
		echo "<h3>".__('Create a new file release')."</a></h3>";
		echo "<form name=prodman method='post' action='index.php?sec=download&sec2=operation/download/browse&create2'>";
	}
	else {
		echo "<h3>".__('Update existing file release')."</a></h3>";
		echo "<form enctype='multipart/form-data' name=prodman2 method='post' action='index.php?sec=download&sec2=operation/download/browse&update2'>";
		echo "<input type=hidden name=id value='$id'>";
	}
	
	echo '<table width="90%" class="databox">';
	echo "<tr>";
	echo "<td class=datos>";
	echo __('Name');
	echo "<td class=datos>";
	echo "<input type=text size=40 name='name' value='$name'>";

	echo "<tr>";
	echo "<td>";
	echo __('Choose file from repository');
	echo integria_help ("choose_download", true);

	echo "<td>";

    // This chunk of code is to do not show in the combo with files, files already as file downloads
    // (slerena, Sep2011)

    $location = basename ($location);
    $files = get_download_files();
    $files_db  = get_db_all_rows_sql ("SELECT * FROM tdownload WHERE location LIKE 'attachment/downloads/%'");
	if($files_db == false) {
		$files_db = array();
	}

	$files_in = array();
    foreach ($files_db as $file_db){
        $files_in[basename($file_db['location'])] = 1;
    }

    $files_not_in = array();
    $match = 0;
    foreach ($files as $file) {
        if(!isset($files_in[$file])) {
                $files_not_in[$file] = $file;
        }
    }

	print_select ($files_not_in, 'location', $location, '', '', '', false);

	echo "<tr>";
	echo "<td class=datos2 valign=top>";
	echo __('Description');
	echo "<td class=datos2>";
	print_textarea ("description", 5, 40, $description, '', false,false);

	echo "<tr>";
	echo "<td class=datos>";
	echo __('Main category');
	echo "<td class=datos>";
	combo_download_categories ($id_category, 0);
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

// ==================================================================
// Show search controls
// ==================================================================

echo "<h2>".__('Downloads')." &raquo; ".__('Defined data')."</a></h2>";

// Search parameter 
$free_text = get_parameter ("free_text", "");
$category = get_parameter ("id_category", 0);

// Search filters
echo '<form method="post">';
echo '<table width="90%" class="blank">';
echo "<tr>";
echo "<td>";
echo __('Categories');
echo "<td>";

combo_download_categories ($category, 1);

echo "<tr>";
echo "<td>";
echo __('Search');
echo "<td>";
echo "<input type=text name='free_text' size=25 value='$free_text'>";

echo "<td >";
echo "<input type=submit class='sub search' value='".__('Search')."'>";


echo "</td></tr></table></form>";

// ==================================================================
// Download listings
// ==================================================================

$sql_filter = "";

if ($free_text != "")
	$sql_filter .= " AND d.name LIKE '%$free_text%' OR d.description LIKE 
'%$free_text%'";

if ($category > 0)
	$sql_filter .= " AND d.id_category = $category ";

if(get_admin_user ($config['id_user']) != 1) {
	$sql_filter .= " AND up.id_grupo IN (SELECT id_group FROM tdownload_category_group WHERE id_category = dc.id) ";
}

$offset = get_parameter ("offset", 0);

$condition = "tusuario u, tprofile p, tdownload d, tusuario_perfil up, tdownload_category dc
WHERE up.id_usuario = '".$config["id_user"]."' AND 
up.id_perfil = p.id  AND
dc.id = d.id_category $sql_filter";

$count = get_db_sql("SELECT COUNT(DISTINCT d.id) FROM $condition");

pagination ($count, "index.php?sec=download&sec2=operation/download/browse", $offset);

$sql = "SELECT d.* FROM $condition GROUP BY d.id ORDER BY date DESC, name, id_category LIMIT
$offset, ". $config["block_size"];

$color =0;

$downloads = process_sql($sql);

if($downloads == false) {
	$downloads = array();
	echo "<h3 class='error'>".__('No Downloads found')."</h3>"; 
}
else {
	echo '<table width="95%" class="listing" cellspacing=4 cellpading=4>';

	echo "<th>".__('Name')."</th>";
	echo "<th>".__('Size')."</th>";
	echo "<th>".__('Category')."</th>";
	echo "<th>".__('Downloads')."</th>";
	echo "<th>".__('Date')."</th>";
	if (give_acl($config["id_user"], 0, "KW")){
		echo "<th>".__('Admin')."</th>";
	}
}

foreach($downloads as $row){
	echo "<tr>";

	// Name
	echo "<td><b><a title='".$row["description"]."' href='operation/download/download.php?id=".$row["id"]."'>";
	echo $row["name"]."</a></b> ";
	if ($row["description"] != ""){
		echo "<img src='images/zoom.png'>";
	}
	echo "</td>";

	// Size
	echo "<td>";
	echo format_for_graph(filesize($config["homedir"].$row["location"]),1,".",",",1024);
	
	// Category
	echo "<td>";
			echo "<img src='images/download_category/".get_db_sql ("SELECT icon FROM tdownload_category WHERE id = ".$row["id_category"]). "'>";

	// Description
	//	echo "<td class=f9>";
	//	echo $row["description"];

	// Downloads
	echo "<td>";
	echo get_db_sql ("SELECT COUNT(*) FROM tdownload_tracking where id_download = ".$row["id"]);

	// Timestamp
	echo "<td class='f9'>";
	echo human_time_comparation($row["date"]);

	if (give_acl($config["id_user"], 0, "KW")){

		// Editr
		echo "<td class='f9' align='center' >";
		echo "<a href='index.php?sec=download&sec2=operation/download/browse&update=".$row["id"]."'><img border='0' src='images/wrench.png'></a>";
		echo "&nbsp;&nbsp;";

		// Delete
		echo "<a href='index.php?sec=download&sec2=operation/download/browse&delete_data=".$row["id"]."' onClick='if (!confirm(\' ".__('Are you sure?')."\')) return false;'><img border='0' src='images/cross.png'></a>";
	}

}
echo "</table>";	
?>
