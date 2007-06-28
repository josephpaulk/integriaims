<?php

// FRITS - the FRee Incident Tracking System
// =========================================
// Copyright (c) 2007 Sancho Lerena, slerena@openideas.info
// Copyright (c) 2007 Artica Soluciones Tecnologicas

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

?>

<script language="javascript">

	/* Function to hide/unhide a specific Div id */
	function toggleDiv (divid){
		if (document.getElementById(divid).style.display == 'none'){
			document.getElementById(divid).style.display = 'block';
		} else {
			document.getElementById(divid).style.display = 'none';
		}
	}
</script>

<?PHP

global $config;

if (check_login() != 0) {
    audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access", "Trying to access event viewer");
    require ("general/noaccess.php");
    exit;
}

// Get our main stuff
$id_user = $_SESSION['id_usuario'];
$id_project = give_parameter_get ("id_project", -1);
$id_task = give_parameter_get ("id_task", -1);
$operation =  give_parameter_get ("operation", "");

// Get names
if ($id_project != 1)
	$project_name = give_db_value ("name", "tproject", "id", $id_project);
else
	$project_name = "";

if ($id_task != 1)
	$task_name = give_db_value ("name", "ttask", "id", $id_task);
else
	$task_name = "";

// Init variables
$name = "";
$description = "";
$end = date("Y-m-d");
$start = date("Y-m-d");
$completion = 0;
$priority = 1;
$id_group = 1;
$result_output = "";
$parent=-1;

if ($operation == ""){
	// Doesn't have access to this page
	audit_db($id_user, $config["REMOTE_ADDR"], "ACL Violation","Trying to access to task manager withour project");
	include ("general/noaccess.php");
	exit;
}

// -----------
// Upload file
// -----------
if ($operation == "upload_file"){
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
		$id_attachment=mysql_insert_id();
		//project_tracking ( $id_inc, $id_usuario, 3);
		$result_output = "<h3 class='suc'>".$lang_label["file_added"]."</h3>";
		// Copy file to directory and change name
		$nombre_archivo = $config["homedir"]."/attachment/frits".$id_attachment."_".$filename;
	echo "Source file ".$_FILES['userfile']['tmp_name'];
	echo "<br>";
	echo "Destination file $nombre_archivo<br>";
		if (!(copy($_FILES['userfile']['tmp_name'], $nombre_archivo ))){
				$result_output = "<h3 class=error>".$lang_label["attach_error"]."</h3>";
			$sql = " DELETE FROM tattachment WHERE id_attachment =".$id_attachment;
			mysql_query($sql);
		} else {
			// Delete temporal file
			unlink ($_FILES['userfile']['tmp_name']);
		}
	}
	
	$operation = "view";
}

// -----------
// Workunit
// -----------
if ($operation == "workunit"){
	
	$duration = give_parameter_post ("duration");
	if (!is_numeric( $duration))
		$duration = 0;
	$timestamp = give_parameter_post ("timestamp");
	$description = give_parameter_post ("description");
	$sql = "INSERT INTO tworkunit (timestamp, duration, id_user, description) VALUES 
			('$timestamp', $duration, '$id_user', '$description')";
	if (mysql_query($sql)){
		$id_workunit = mysql_insert_id();
		$sql2 = "INSERT INTO tworkunit_task (id_task, id_workunit) VALUES ($id_task, $id_workunit)";
		if (mysql_query($sql2)){
			$result_output = "<h3 class='suc'>".$lang_label["workunit_ok"]."</h3>";
			audit_db ($id_user, $config["REMOTE_ADDR"], "Work unit added", "Workunit for $id_user added to Task '$task_name'");
		}
	} else 
		$result_output = "<h3 class='error'>".$lang_label["workunit_no"]."</h3>";

	$operation = "view";
}


// -----------
// Create task
// -----------
if ($operation == "insert"){
	$name = give_parameter_post ("name");
	$description = give_parameter_post ("description");
	$priority = give_parameter_post ("priority");
	$completion = give_parameter_post ("completion");
	$parent = give_parameter_post ("parent");
	$start = give_parameter_post ("start_date");
	$end = give_parameter_post ("end_date");
	$id_group = give_parameter_post ("group",1);
	$sql = "INSERT INTO ttask
			(id_project, name, description, priority, completion, start, end, id_parent_task, id_group) VALUES
			($id_project, '$name', '$description', '$priority', '$completion', '$start', '$end', '$parent', $id_group)";
	if (mysql_query($sql)){
		$id_task = mysql_insert_id();
		$result_output = "<h3 class='suc'>".$lang_label["create_ok"]."</h3>";
		audit_db ($id_user, $config["REMOTE_ADDR"], "Task added to project", "Task '$name' added to project '$id_project'");
		$operation = "view";
	} else {
		$update_mode = 0;
		$create_mode = 1;
		$result_output = "<h3 class='error'>".$lang_label["create_no"]."</h3>";
	}
}

// -----------
// Update task
// -----------
if ($operation == "update"){
	if ($id_task == -1){
		audit_db($id_user, $config["REMOTE_ADDR"], "ACL Violation","Trying to access to update invalid Task");
		include ("general/noaccess.php");
		exit;
	}
	$name = give_parameter_post ("name");
	$description = give_parameter_post ("description");
	$priority = give_parameter_post ("priority");
	$completion = give_parameter_post ("completion");
	$parent = give_parameter_post ("parent");
	$start = give_parameter_post ("start_date");
	$end = give_parameter_post ("end_date");
	$id_group = give_parameter_post ("group",1);
	$sql = "UPDATE ttask SET 
			name = '$name',
			description = '$description',
			priority = '$priority',
			completion = '$completion',
			start = '$start',
			end = '$end',
			id_parent_task = '$parent',
			id_group = '$id_group'
			WHERE id = $id_task";
	if (mysql_query($sql)){
		$result_output = "<h3 class='suc'>".$lang_label["update_ok"]."</h3>";
		audit_db ($id_user, $config["REMOTE_ADDR"], "Task updated", "Task '$name' updated to project '$id_project'");
		$operation = "view";
	} else {
		$result_output = "<h3 class='error'>".$lang_label["update_no"]."</h3>";
		echo "DEBUG $sql";
	}
}

// ---------------------
// Edition / View mode
// ---------------------
if ($operation == "view"){
	$sql1='SELECT * FROM ttask WHERE id = '.$id_task;
	if (!$result = mysql_query($sql1)){
        audit_db ($_SESSION['id_usuario'], $config["REMOTE_ADDR"], "ACL Violation","Trying to access to other task hacking with URL");
        include ("general/noaccess.php");
        exit;
    }
	$row=mysql_fetch_array($result);
	// Get values
	$name = clean_input ($row["name"]);
	$description = $row["description"];
	$completion = clean_input ($row["completion"]);
	$priority = clean_input ($row["priority"]);
	$dep_type = clean_input ($row["dep_type"]);
	$start = clean_input ($row["start"]);
	$end = clean_input ($row["end"]);
	$parent = clean_input ($row["id_parent_task"]);
	$id_group = clean_input ($row["id_group"]);
	// SHOW TABS
	echo "<div id='menu_tab'><ul class='mn'>";

	// Main
	echo "<li class='nomn'>";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/project_detail&id=$id_project'><img src='images/application_edit.png' class='top' border=0> ".$lang_label["project"]."</a>";
	echo "</li>";

	// Tasks
	echo "<li class='nomn'>";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/task&id_project=$id_project'><img src='images/page_white_text.png' class='top' border=0> ".$lang_label["tasklist"]."</a>";
	echo "</li>";

	// Workunits
	$timeused = give_hours_task ( $id_task);
	echo "<li class='nomn'>";
	if ($timeused > 0)
		echo "<a href='index.php?sec=projects&sec2=operation/projects/task_workunit&id_project=$id_project&id_task=$id_task'><img src='images/award_star_silver_1.png' class='top' border=0> ".$lang_label["workunits"]." ($timeused)</a>";
	else
		echo "<a href='index.php?sec=projects&sec2=operation/projects/task_workunit&id_project=$id_project&id_task=$id_task'><img src='images/award_star_silver_1.png' class='top' border=0> ".$lang_label["workunits"]."</a>";
	echo "</li>";

	// Tracking
	echo "<li class='nomn'>";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/tracking&id=$id_project'><img src='images/eye.png' class='top' border=0> ".$lang_label["tracking"]." </a>";
	echo "</li>";
	
	$numberfiles = give_number_files_task ($id_task);
		
	// Files
	echo "<li class='nomn'>";
	if ($numberfiles > 0)
		echo "<a href='index.php?sec=projects&sec2=operation/projects/task_files&id_project=$id_project&id_task=$id_task'><img src='images/disk.png' class='top' border=0> ".$lang_label["files"]." ($numberfiles) </a>";
	else
		echo "<a href='index.php?sec=projects&sec2=operation/projects/task_files&id_project=$id_project&id_task=$id_task'><img src='images/disk.png' class='top' border=0> ".$lang_label["files"]." </a>";
	echo "</li>";

	// People
	echo "<li class='nomn'>";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/people_manager&id_project=$id_project&id_task=$id_task'><img src='images/user_suit.png' class='top' border=0> ".$lang_label["people"]." </a>";
	echo "</li>";
	
	echo "</ul>";
	echo "</div>";
	echo "<div style='height: 25px'> </div>";
} 

echo $result_output;

// ********************************************************************************************************
// Show forms
// ********************************************************************************************************

if ($operation == "create")
	echo "<form name='projectf' method='POST' action='index.php?sec=projects&sec2=operation/projects/task_detail&operation=insert&id_project=$id_project'>";
else
	echo "<form name='projectf' method='POST' action='index.php?sec=projects&sec2=operation/projects/task_detail&operation=update&id_project=$id_project&id_task=$id_task'>";
 
// --------------------
// Main task form table
// --------------------

echo "<h2>".$lang_label["task_management"]." -&gt;";
if ($operation != "create"){
	echo $lang_label["rev_task"]."</h2>";
} else {
	
	echo $lang_label["create_task"]." ( $project_name )</h2>";
}

echo '<table width=700 class="databox_color" cellpadding=3 cellspacing=3>';

// Name
echo '<tr><td class="datos"><b>'.$lang_label["name"].'</b>';
echo '<td class="datos"><input type="text" name="name" size=40 value="'.$name.'">';

// Parent task
echo '<td class="datos">';
echo "<b>".$lang_label["parent"]."</b> ";
echo '<td class="datos">';
echo '<select name="parent">';

if ($parent > 0)
	echo "<option value='$parent'>".give_db_value ("name", "ttask", "id", $parent);

echo "<option value=0>".$lang_label["none"];
$query1="SELECT * FROM ttask WHERE id_project = $id_project and id != $id_task and id != $parent";
$resq1=mysql_query($query1);
while ($row=mysql_fetch_array($resq1)){
	echo "<option value='".$row["id"]."'>".substr($row["name"],0,20);
}echo "</select>";

// start and end date
echo '<tr><td class="datos2"><b>'.$lang_label["start"].'</b>';
echo "<td class='datos2'>";
//echo "<input type='text' id='start_date' onclick='scwShow(this,this);' name='start_date' size=10 value='$start_date'> 
echo "<input type='text' id='start_date' name='start_date' size=10 value='$start'> <img src='images/calendar_view_day.png' onclick='scwShow(scwID(\"start_date\"),this);'> ";
echo '<td class="datos2"><b>'.$lang_label["end"].'</b>';
echo "<td class='datos2'>";
echo "<input type='text' id='end_date' name='end_date' size=10 value='$end'> <img src='images/calendar_view_day.png' title='Click Here' alt='Click Here' onclick='scwShow(scwID(\"end_date\"),this);'>";

// group
echo '<tr><td class="datos"><b>'.$lang_label["group"].'</b>';
echo '<td class="datos">';
echo combo_groups($id_group, "TW");

// Priority
echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
echo '<b>'.$lang_label["priority"].'</b>';
echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
echo "<select name='priority'>";
if ($priority != "")
	echo '<option>'.$priority;
echo "<option>1";
echo "<option>2";
echo "<option>3";
echo "<option>4";
echo "<option>5";
echo "<option>6";
echo "<option>7";
echo "<option>8";
echo "<option>9";
echo "<option>10";
echo "</select>";

// Completion
echo '<td class="datos"><b>'.$lang_label["completion"].'</b>';
echo '<td class="datos">';
echo "<select name='completion'>";
if ($completion != "")
	echo '<option value='.$completion.'>'.$completion."%";
echo "<option value=0> 0%";
echo "<option value=10> 10%";
echo "<option value=20> 20%";
echo "<option value=30> 30%";
echo "<option value=40> 40%";
echo "<option value=50> 50%";
echo "<option value=60> 60%";
echo "<option value=70> 70%";
echo "<option value=80> 80%";
echo "<option value=90> 90%";
echo "<option value=100> 100%";
echo "</select>";

// Description

echo '<tr><td class="datos2" colspan="4"><textarea name="description" rows="15" cols="85" style="height: 250px">';
	echo $description;
echo "</textarea>";

echo "</table>";

if ($operation != "create")
	echo '<input type="submit" class="sub next" name="accion" value="'.$lang_label["update"].'" border="0">';
else 
	echo '<input type="submit" class="sub create" name="accion" value="'.$lang_label["create"].'" border="0">';

echo "</form>";
echo "</table>";


// --------------------
// Workunit / Note  form
// --------------------
if ($operation != "create"){

	$ahora = date("Y-m-d H:i:s");

	?>
		<h3><img src='images/award_star_silver_1.png'>&nbsp;&nbsp;
		<a href="javascript:;" onmousedown="toggleDiv('workunit_control');">
	<?PHP

	echo $lang_label["add_workunit"]."</a></h3>";
	echo "<div id='workunit_control' style='display:none'>";
	echo "<table cellpadding=3 cellspacing=3 border=0 width='700' class='databox_color'>";
	echo "<form name='nota' method='post' action='index.php?sec=projects&sec2=operation/projects/task_detail&id_project=$id_project&id_task=$id_task&operation=workunit'>";
	echo "<td class='datos' width=140><b>".$lang_label["date"]."</b>";
	echo "<td class='datos'>";

	echo "<input type='text' id='workunit_date' name='workunit_date' size=10 value='".substr($ahora,0,10)."'> <img src='images/calendar_view_day.png' onclick='scwShow(scwID(\"workunit_date\"),this);'> ";



	echo "<tr><td class='datos2'>";
	echo "<b>".$lang_label["profile"]."</b>";
	echo "<td class='datos2'>";
	echo "<select name='work_profile'>";
	echo "<option>N/A";
	echo "</select>";
	
	echo "&nbsp;&nbsp;";
	echo "<input type='checkbox' name='have_cost' value=1>";
	echo "&nbsp;&nbsp;";
	echo "<b>".$lang_label["have_cost"]."</b>";

	echo "<tr><td class='datos'>";
	echo "<b>".$lang_label["time_used"]."</b>";
	echo "<td class='datos'>";
	echo "<input type='text' name='duration' value='0' size='7'>";

	echo "<input type='hidden' name='timestamp' value='".$ahora."'>";
	echo '<tr><td colspan="4" class="datos2"><textarea name="description" rows="5" cols="85">';
	echo '</textarea>';
	echo "</table>";
	echo '<input name="addnote" type="submit" class="sub next" value="'.$lang_label["add"].'">';
	echo "</form>";
	echo "</div>";
}

// --------------------
// File attach form
// --------------------
if ($operation != "create"){

	?>
		<h3><img src='images/disk.png'>&nbsp;&nbsp;
		<a href="javascript:;" onmousedown="toggleDiv('upload_control');">
	<?PHP

	echo $lang_label["add_file"]."</a></h3>";
	echo "<div id='upload_control' style='display:none'>";
	echo "<form method='post' action='index.php?sec=projects&sec2=operation/projects/task_detail&id_project=$id_project&id_task=$id_task&operation=upload_file' enctype='multipart/form-data'>";
	echo "<table cellpadding=3 cellspacing=3 border=0 width='700' class='databox_color'>";
	echo '<tr><td class="datos">'.$lang_label["filename"];
	echo '<td colspan=4 class="datos">';
	echo '<input type="file" name="userfile" value="userfile" class="sub" size="35">';
	echo '<tr><td class="datos2">'.$lang_label["description"].'</td><td class="datos2" colspan=3><input type="text" name="file_description" size=45>';
	echo "<td class='datos2'>";
	echo '<input type="submit" name="upload" value="'.$lang_label["upload"].'" class="sub next">';
	echo '</td></tr></table>';
	echo "</div>";
	
	echo "</form>";
	echo "<br>";
}

?>
