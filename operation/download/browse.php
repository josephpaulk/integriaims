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

if (give_acl($config["id_user"], 0, "FRR")==0) {
    audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access Downloads browser");
    require ("general/noaccess.php");
    exit;
}

function get_download_files () {
	$base_dir = 'attachment/downloads';
	$files = list_files ($base_dir, "", 0, false);
	
	$retval = array ();
	foreach ($files as $file) {
		$retval[$file] = $file;
	}
	
	return $retval;
}

$delete_btn = get_parameter ("delete_btn", 0);

// File deletion
// ==================

if ($delete_btn){
	$location = clean_output (get_parameter ("location",""));
	
	$file_path = $config["homedir"]. "/". "attachment/downloads/" . $location;

	unlink ($file_path);
	$_GET["create"]=1;

}


// Database Creation
// ==================
if (isset($_GET["create2"])){ // Create group

	if (give_acl($config["id_user"], 0, "FRW") != 1){
		audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to create a new Download file without privileges");
		require ("general/noaccess.php");
		exit;
	}
	
	$timestamp = date('Y-m-d H:i:s');
	$name = get_parameter ("name","");

	if ($name != "") {

		$location = clean_output (get_parameter ("location",""));
		$description = get_parameter ("description","");
		$id_category = get_parameter ("id_category","");
		$public = (int) get_parameter ("public",0);
		$external_id = (string) get_parameter ("external_id");

		$sql_insert = "INSERT INTO tdownload (name, location, description, id_category, id_user, date, public, external_id) 
		  		 VALUE ('$name','attachment/downloads/$location', '$description', '$id_category', '".$config["id_user"]."', '$timestamp', $public, '$external_id') ";
		$result=mysql_query($sql_insert);	
		if (! $result)
			echo "<h3 class='error'>".__('Could not be created')."</h3>"; 
		else {
			echo "<h3 class='suc'>".__('Successfully created')."</h3>";
			$id_data = mysql_insert_id();
			insert_event ("DOWNLOAD ITEM CREATED", $id_data, 0, $name);
		}
	}
}

// Database UPDATE
// ==================
if (isset($_GET["update2"])){ // if modified any parameter

	if (give_acl($config["id_user"], 0, "FRW") != 1){
		audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to update a download without privileges");
	    require ("general/noaccess.php");
    	exit;
    }

	$id = get_parameter ("id","");
	
		
	if ($id != "" && ! check_fr_item_accessibility($config["id_user"], $id)) {
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to update a File Releases forbidden item");
		require ("general/noaccess.php");
		exit;
	}
	

	$timestamp = date('Y-m-d H:i:s');

	$name = get_parameter ("name","");
	// Location should not be changed never.
	$description = get_parameter ("description","");
	$id_category = get_parameter ("id_category","");
	$public = (int) get_parameter ("public",0);
	$external_id = (string) get_parameter ("external_id");

	$sql_update ="UPDATE tdownload
	SET public = $public, external_id = '$external_id', name = '$name', description = '$description', id_category = $id_category WHERE id = $id";
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

	if (give_acl($config["id_user"], 0, "FRW") != 1){
		audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to delete a Download without privileges");
		require ("general/noaccess.php");
		exit;
	}

	$id = get_parameter ("delete_data",0);
	
	if ($id && ! check_fr_item_accessibility($config["id_user"], $id)) {
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to delete a File Releases forbidden item");
		require ("general/noaccess.php");
		exit;
	}
	
	$download_title = get_db_sql ("SELECT name FROM tdownload WHERE id = $id ");
	$file_path = get_db_sql ("SELECT location FROM tdownload WHERE id = $id ");

	$file_path = $config["homedir"]."/".$file_path;

	unlink ($file_path);
	
	$sql_delete= "DELETE FROM tdownload WHERE id = $id";		
	$result=mysql_query($sql_delete);

	$sql_delete= "DELETE FROM tdownload_tracking WHERE id_download = $id";		
	$result=mysql_query($sql_delete);

	insert_event ("DOWNLOAD ITEM DELETED", $id, 0, "Deleted Download $download_title");
	echo "<h3 class='suc'>".__('Successfully deleted')."</h3>"; 
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
		$external_id = sha1(random_string(12).date());
		$public = 0;

	} else {
		$id = get_parameter ("update",-1);
		
		if ($id != -1 && ! check_fr_item_accessibility($config["id_user"], $id)) {
			audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to update a File Releases forbidden item");
			require ("general/noaccess.php");
			exit;
		}
		
		$row = get_db_row ("tdownload", "id", $id);
		$name = $row["name"];
		$description =$row["description"];
		$location = $row["location"];
		$id_category = $row["id_category"];
		$timestamp = $row["date"];
		$down_id_user = $row["id_user"];
		$public = $row["public"];
		$external_id = $row["external_id"];
	}
	
	echo "<h1>".__('File releases management')."</h1>";	
	
	$current_directory = $config["homedir"]. "/attachment/downloads";

	// Upload file
	if (isset($_GET["upload_file"])) {
		
		if (isset($_POST['upfile']) && ( $_POST['upfile'] != "" )){ //if file
			$filename= $_POST['upfile'];
			$file_tmp = sys_get_temp_dir().'/'.$filename;
			$directory = get_parameter ("directory","");

			// Copy file to directory and change name
			$file_target = $config["homedir"]."/".$directory."/".$filename;
			if (!(copy($file_tmp, $file_target))){
				echo "<h3 class=error>".__("Could not be attached")."</h3>";
			} else {
				// Delete temporal file
				echo "<h3 class=suc>".__("Successfully attached")."</h3>";
				$location = $file_target;
				unlink ($file_tmp);
			}
			
		}
	}

	echo '<a href="javascript:;" onclick="$(\'#upload_div\').slideToggle (); return false">';
	echo '<h3>'.__('Upload a new file').'</h3>';
	echo '</a>';
	echo '<div id="upload_div" style="padding: 20px; margin: 0px; display: none;">';

	if (is_writable($current_directory)) {
		$target_directory = 'attachment/downloads';
		$action = 'index.php?sec=download&sec2=operation/download/browse&create=1&upload_file';
				
		$into_form = "<input type='hidden' name='directory' value='$target_directory'>";

		print_input_file_progress($action,$into_form,'','sub next');	
	} else {
		echo "<h3 class='error'>".__('Current directory is not writtable by HTTP Server')."</h3>";
		echo "<p>";
		echo __('Please check that current directory has write rights for HTTP server');
		echo "</p>";
	}
	
	echo "</div>";

	// echo "<form method='post' action='index.php?sec=download&sec2=operation/download/browse&create=1&upload_file' enctype='multipart/form-data'>";
	//echo "<table>";
	
	if ($id == -1){
		echo "<h1>".__('Create a new file release')."</h1>";
		echo "<form id='form-file_release' name=prodman method='post' action='index.php?sec=download&sec2=operation/download/browse&create2=1'>";
	}
	else {
		echo "<h1>".__('Update existing file release')."</h1>";
		echo "<form id='form-file_release' enctype='multipart/form-data' name=prodman2 method='post' action='index.php?sec=download&sec2=operation/download/browse&update2=1'>";
		echo "<input id='id_download' type=hidden name=id value='$id'>";
	}
	
	echo '<table width="99%" class="search-table-button">';
	echo "<tr>";
	echo "<td class=datos>";
	echo __('Name');
	echo "<td class=datos>";
	echo "<input id='text-name' type=text size=40 name='name' value='$name'>";

	echo "<tr>";
	echo "<td class=datos>";
	echo __('External ID');
	echo "<td class=datos>";
	echo "<input type=text size=60 name='external_id' value='$external_id'>";

	echo "<tr>";
	echo "<td class=datos>";
	echo __('Public');
	echo "<td class=datos>";
	echo print_checkbox ("public", 1, $public, true, '');

	if ($id == -1){

		echo "<tr>";
		echo "<td>";
		echo __('Choose file from repository');
		echo integria_help ("choose_download", true);

		echo "<td valign=top>";

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

		echo "&nbsp;&nbsp;"; 

		print_submit_button (__('Delete file'), 'delete_btn', false, 'class="sub upd"');

	}

	
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
	if ($id == -1)
		echo "<tr><td colspan=2>" . print_submit_button (__('Create'), 'crt_btn', false, 'class="sub create"', true) . "</td></tr>";
	else
		echo "<tr><td colspan=2>" . print_submit_button (__('Update'), 'upd_btn', false, 'class="sub upd"', true) . "</td></tr>";
	echo "</table>";
	echo "</form>";
}


if ((!isset($_GET["update"])) AND (!isset($_GET["create"]))){
	
	// ==================================================================
	// Show search controls
	// ==================================================================
	
	echo "<h1>".__('Downloads')." &raquo; ".__('Defined data')."</h1>";
	
	// Search parameter 
	$free_text = get_parameter ("free_text", "");
	$category = get_parameter ("id_category", 0);
	
	// Search filters
	echo '<form method="post">';
	echo '<table width="99%" class="search-table">';
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
		$sql_filter .= " AND name LIKE '%$free_text%' OR description LIKE 
	'%$free_text%'";
	
	if ($category > 0)
		$sql_filter .= " AND id_category = $category ";
	
	$offset = get_parameter ("offset", 0);
	$condition = get_filter_by_fr_category_accessibility();
	$count = get_db_sql("SELECT COUNT(id) FROM tdownload $condition $sql_filter");
	pagination ($count, "index.php?sec=download&sec2=operation/download/browse&id_category=$category&free_text=$free_text", $offset);
	
	$sql = "SELECT * FROM tdownload $condition $sql_filter ORDER BY date DESC, name LIMIT $offset, ". $config["block_size"];
	
	$color =0;
	
	$downloads = process_sql($sql);

	if($downloads == false) {
		$downloads = array();
		echo "<h3 class='error'>".__('No Downloads found')."</h3>"; 
	}
	else {
		echo '<table width="99%" class="listing" cellspacing=4 cellpading=4>';

		echo "<th>".__('Name')."</th>";
		echo "<th>".__('Size')."</th>";
		echo "<th>".__('Category')."</th>";
		echo "<th>".__('Downloads')."</th>";
		echo "<th>".__('Public link')."</th>";
		echo "<th>".__('Date')."</th>";
		if (give_acl($config["id_user"], 0, "FRW")){
			echo "<th>".__('Admin')."</th>";
		}
	}

	foreach($downloads as $row){
		echo "<tr>";

		// Name
		echo "<td><a title='".$row["description"]."' href='operation/download/download.php?id=".$row["id"]."'>";
		echo $row["name"]."</a>";
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

		// Public URL
		echo "<td>";
		if ($row["public"]){
			$url = $config["base_url"] . "/index.php?external_download_id=".$row["external_id"];
			echo "<a href='$url'><img src='images/world.png'></a>";
		}

		// Timestamp
		echo "<td class='f9'>";
		echo human_time_comparation($row["date"]);

		if (give_acl($config["id_user"], 0, "FRW")){

			// Edit
			echo "<td class='f9' align='center' >";
			echo "<a href='index.php?sec=download&sec2=operation/download/browse&update=".$row["id"]."'><img border='0' src='images/wrench.png'></a>";
			echo "&nbsp;&nbsp;";

			// Delete
			echo "<a href='index.php?sec=download&sec2=operation/download/browse&delete_data=".$row["id"]."' onClick='if (!confirm(\' ".__('Are you sure?')."\')) return false;'><img border='0' src='images/cross.png'></a>";
		}

	}
	echo "</table>";	

}

?>

<script type="text/javascript" src="include/js/jquery.validate.js"></script>
<script type="text/javascript" src="include/js/jquery.validation.functions.js"></script>

<script type="text/javascript">

// Form validation
trim_element_on_submit('input[name="free_text"]');
trim_element_on_submit('#text-name');
validate_form("#form-file_release");
var rules, messages;
// Rules: #text-name
rules = {
	required: true,
	remote: {
		url: "ajax.php",
        type: "POST",
        data: {
			page: "include/ajax/remote_validations",
			search_existing_download: 1,
			download_name: function() { return $('#text-name').val() },
			download_id: function() { return $('#id_download').val() }
        }
	}
};
messages = {
	required: "<?php echo __('Name required')?>",
	remote: "<?php echo __('This download already exists')?>"
};
add_validate_form_element_rules('#text-name', rules, messages);

</script>
