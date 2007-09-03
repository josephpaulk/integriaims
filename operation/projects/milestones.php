<?php

if (check_login() != 0) {
	audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access", "Trying to access event viewer");
	require ("general/noaccess.php");
	exit;
}

$id_user = $config["id_user"];
$operation = give_parameter_get ("operation");
$id_project = give_parameter_get ("id_project", -1);
if ($id_project != 1)
	$project_name = give_db_value ("name", "tproject", "id", $id_project);
else
	$project_name = "";

if ( $id_project == -1 ){
	// Doesn't have access to this page
	audit_db($id_user, $config["REMOTE_ADDR"], "ACL Violation","Trying to access to task manager withour project");
	include ("general/noaccess.php");
	exit;
}

// ---------------
// CREATE new milestone
// ---------------
if ($operation == "create2") {
	$name = give_parameter_post ("name");
	$description = give_parameter_post ("description");
	$timestamp = give_parameter_post ("timestamp");
	$id_project = give_parameter_post ("id_project");
	$sql_insert="INSERT INTO tmilestone (name, description, timestamp, id_project) VALUES ('$name','$description', '$timestamp', '$id_project') ";
	$result=mysql_query($sql_insert);	
	if (! $result)
		echo "<h3 class='error'>".$lang_label["create_no"]."</h3>";
	else {
		echo "<h3 class='suc'>".$lang_label["create_ok"]."</h3>"; 
		$id_ms = mysql_insert_id();
	}
	/*
	$msgtext = "A new Milestone has been created by user [$id_user] for user [$assigned_user]. Todo information is:\n\nTitle   : $name\nPriority: $priority\nDescription: $description\n\nFor more information please visit ".$config["base_url"]."/index.php?sec=todo&sec2=operation/todo/todo";
	if ($id_user != $assigned_user){
		topi_sendmail (return_user_email($id_user), "[TOPI] New ToDo item has been created", $msgtext);
		topi_sendmail (return_user_email($assigned_user), "[TOPI] New ToDo item has been created", $msgtext);
	} else
		topi_sendmail (return_user_email($assigned_user), "[TOPI] New ToDo item has been created", $msgtext);
	*/
	$operation = "";
}

// ---------------
// DELETE new todo
// ---------------
if ($operation == "delete") {
	$id_milestone = give_parameter_get ("id");
	$sql_delete= "DELETE FROM tmilestone WHERE id = $id_milestone";
	$result=mysql_query($sql_delete);
	if (! $result)
		echo "<h3 class='error'>".$lang_label["delete_no"]."</h3>";
	else
		echo "<h3 class='suc'>".$lang_label["delete_ok"]."</h3>";
	$operation = "";
}


// ---------------
// CREATE new todo (form)
// ---------------
if ($operation == "create") {
echo "<h2>".lang_string ("milestone_creation")."</h2>";
	echo '<table class="databox_color" cellpadding="4" cellspacing="4" width="80%">';
	echo '<form name="ilink" method="post" action="index.php?sec=projects&sec2=operation/projects/milestones&id_project='.$id_project.'&operation=create2">';

	echo "<tr><td class='datos'>".lang_string ("name");
	echo "<td class='datos'><input name='name' size=40>";
	
	echo "<tr><td class='datos2'>".lang_string ("timestamp");
	echo "<td class='datos2'>";
	echo "<input type='text' id='timestamp' name='timestamp' size=10 value='$ahora_date'> <img src='images/calendar_view_day.png' onclick='scwShow(scwID(\"timestamp\"),this);'> ";

	echo "<tr><td class='datos' valign='top'>".lang_string ("description");
	echo "<td class='datos'><textarea name='description' style='width:100%; height:100px'>";
	echo "</textarea>";
	echo "</table>";
	echo '<table cellpadding="4" cellspacing="4" width="80%">';
	echo "<tr><td align='right'>";
	echo "<input type=hidden name='id_project' value='$id_project'>";
	echo "<input name='crtbutton' type='submit' class='sub' value='".lang_string ("create")."'>";
	echo '</form></table>';
}

// -------------------------
// Milestone view
// -------------------------
if ($operation == ""){
	echo "<h1>".lang_string("milestones management");
	echo "<table cellpadding=4 cellspacing=4 width=100%>";
	echo "<th>".lang_string ("milestone");
	echo "<th>".lang_string ("description");
	echo "<th>".lang_string ("timestamp");
	echo "<th>".lang_string ("delete");
	$color=1;
	$sql1="SELECT * FROM tmilestone WHERE id_project = $id_project";
	if ($result=mysql_query($sql1))
		while ($row=mysql_fetch_array($result)){
			if ($color == 1){
				$tdcolor = "datos";
				$color = 0;
				$tip = "tip";
			}
			else {
				$tdcolor = "datos2";
				$color = 1;
				$tip = "tip2";
			}
			echo "<tr><td class='$tdcolor'>";
			echo $row["name"];
			
			echo "<td class='".$tdcolor."f9'>";
			echo $row["description"];
			
			echo "<td class='".$tdcolor."f9'>";
			echo $row["timestamp"];
			
			// DELETE
			echo '<td class="'.$tdcolor.'" align="center">';
			echo '<a href="index.php?sec=projects&sec2=operation/projects/milestones&id_project='.$id_project.'&operation=delete&id='.$row["id"].'" onClick="if (!confirm(\' '.$lang_label["are_you_sure"].'\')) return false;"><img border=0 src="images/cross.png"></a>';
			
		}
	echo "</table>";
	echo "<table cellpadding=4 cellspacing=4 width=100%>";
	echo "<tr><td align=right>";

	echo "<form name='ms' method='POST'  action='index.php?sec=projects&sec2=operation/projects/milestones&operation=create&id_project=$id_project'>";
	echo "<input type='submit' class='sub next' name='crt' value='".lang_string("create_milestone")."'>";
	echo "</form>";
	echo "</table>";
} // Fin bloque else


?>
