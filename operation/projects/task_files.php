<?php
// Integria IMS - The ITIL Management System
// =========================================
// Copyright (c) 2007 Sancho Lerena, slerena@openideas.info
// Copyright (c) 2008 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
// Load global vars

global $config;


if (check_login() != 0) {
    audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access", "Trying to access event viewer");
    require ("general/noaccess.php");
    exit;
}

$id_user = $_SESSION['id_usuario'];
$id_project = get_parameter ("id_project", -1);
$id_task = get_parameter ("id_task", -1);
$operation = get_parameter ("operation", "");
// Get names
if ($id_project != 1)
	$project_name = get_db_value ("name", "tproject", "id", $id_project);
else
	$project_name = "";

if ($id_task != 1)
	$task_name = get_db_value ("name", "ttask", "id", $id_task);
else
	$task_name = "";

if ( $id_project == -1 ){
    // Doesn't have access to this page
    audit_db($id_user, $config["REMOTE_ADDR"], "ACL Violation","Trying to access to task manager withour project");
    include ("general/noaccess.php");
    exit;
}

// -----------
// Upload file
// -----------
if ($operation == "attachfile"){
	if ( $_FILES['userfile']['name'] != "" ){ //if file
		$tipo = $_FILES['userfile']['type'];
		if (isset($_POST["file_description"]))
			$description = $_POST["file_description"];
		else
			$description = "No description available";
		// Insert into database
		$filename= $_FILES['userfile']['name'];
		$filesize = $_FILES['userfile']['size'];

		$sql = " INSERT INTO tattachment (id_task, id_usuario, filename, description, size ) VALUES (".$id_task.", '".$id_user." ','".$filename."','".$description."',".$filesize.") ";
		mysql_query($sql);
echo $sql;
		$id_attachment=mysql_insert_id();
		//project_tracking ( $id_inc, $id_usuario, 3);
		$result_output = "<h3 class='suc'>".__('File added')."</h3>";
		// Copy file to directory and change name
		$nombre_archivo = $config["homedir"]."/attachment/".$id_attachment."_".$filename;

	echo "Source file ".$_FILES['userfile']['tmp_name'];
	echo "<br>";
	echo "Destination file $nombre_archivo<br>";

	
		if (!(copy($_FILES['userfile']['tmp_name'], $nombre_archivo ))){
				$result_output = "<h3 class=error>".__('File cannot be saved. Please contact Integria administrator about this error')."</h3>";
			$sql = " DELETE FROM tattachment WHERE id_attachment =".$id_attachment;
			mysql_query($sql);
		} else {
			// Delete temporal file
			unlink ($_FILES['userfile']['tmp_name']);
		}
	}
}

// -----------
// Delete file
// -----------
if ($operation == "delete"){
	$file_id = get_parameter ("file", "");
	$file_row = get_db_row ("tattachment", "id_attachment", $file_id);
	$nombre_archivo = $config["homedir"]."/attachment/".$file_id."_".$file_row["filename"];
	unlink ($nombre_archivo);
	get_db_sql ("DELETE FROM tattachment WHERE id_attachment = $file_id");
	$result_output = "<h3 class='suc'>".__('File deleted')."</h3>";
}

// Specific task
if ($id_task != -1){ 
	$sql= "SELECT * FROM tattachment WHERE id_task = $id_task";
	echo "<h3>".__('Attached files');
	echo " - ".__('Task')." - ".$task_name."</h3>";
	echo "<table cellpadding=4 cellspacing=4 border='0' width=700 class='listing'>";
	echo "<tr><th>"; 
	echo __('Filename');
	echo "<th>"; 
	echo __('User');
	echo "<th>"; 
	echo __('Size');
	echo "<th>"; 
	echo __('Description');
	echo "<th>"; 
	echo __('Delete');
}

// Whole project
if ($id_task == -1){
	$sql= "SELECT tattachment.id_attachment, tattachment.size, tattachment.description, tattachment.filename, tattachment.id_usuario, ttask.name, ttask.id as task_id FROM tattachment, ttask
			WHERE ttask.id_project = $id_project AND ttask.id = tattachment.id_task";

	echo "<h3>".__('Attached files');
	echo " - ".__('Project')." - ".$project_name."</h3>";
	echo "<table  width=95% class='listing'>";
	echo "<tr><th>"; 
	echo __('Task');
	echo "<th>"; 
	echo __('Filename');
	echo "<th>"; 
	echo __('User');
	echo "<th>"; 
	echo __('Size');
	echo "<th>"; 
	echo __('Description');
	echo "<th>"; 
	echo __('Delete');
}

$color = 0;
if ($res = mysql_query($sql)) {
	while ($row=mysql_fetch_array($res)){
		if ($color == 1){
			$tdcolor = "datos";
			$color = 0;
		} else {
			$tdcolor = "datos2";
			$color = 1;
		}

		if (strlen($row["filename"]) > 15)
			$filename = substr($row["filename"],0,15)."...";
		else
			$filename = $row["filename"];

		$link = $config["base_url"]."/attachment/".$row["id_attachment"]."_".rawurlencode ($row["filename"]);
		// Show data
		if ($id_task == -1) {
			echo "<tr><td class='$tdcolor' valign='top'>";
			$task_id = $row["task_id"];
			echo "<a href='index.php?sec=projects&sec2=operation/projects/task_detail&id_project=$id_project&id_task=$task_id&operation=view'>";
			echo $row["name"];
			echo "</a>";
			echo "<td class='$tdcolor' valign='top'>";
			echo '<b><a href="'.$link.'">'.$filename."</a></b>";
		} else {
			echo "<tr><td class='$tdcolor' valign='top'>";
			echo '<b><a href="'.$link.'">'.$filename."</a></b>";
		}
		echo "<td class='$tdcolor f9' valign='top'>";
		echo $row["id_usuario"];

		echo "<td class='$tdcolor f9' valign='top'>";
		echo $row["size"];

		echo "<td class='$tdcolor' valign='top'>";
		echo $row["description"];

		echo "<td class='$tdcolor' valign='top'>";
		echo "<a href='index.php?sec=projects&sec2=operation/projects/task_files&id_project=$id_project&operation=delete&file=".$row["id_attachment"]."'><img src='images/cross.png' border=0></A>";
	}
}
echo "</table>";


?>
