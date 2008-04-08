<?php

// Integria 1.0 - http://integria.sourceforge.net
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

global $REMOTE_ADDR;
global $config;


if (check_login() != 0) {
	audit_db("Noauth",$REMOTE_ADDR, "No authenticated acces","Trying to access incident viewer");
	require ("general/noaccess.php");
	exit;
}

$id_user = $config["id_user"];

if (give_acl($id_user, 0, "IR")!=1) {
	audit_db($id_user,$REMOTE_ADDR, "ACL Violation","Trying to access incident viewer");
	require ("general/noaccess.php");
	exit;
}


$accion = "";
// Delete project
if (isset($_GET["quick_delete"])){
	$id_project = $_GET["quick_delete"];
	$id_owner = give_db_value ("id_owner", "tproject", "id", $id_project);
	if (($id_owner == $id_user) OR (dame_admin ($id_user))) {
		// delete_project ($id_project);
		$sql =" UPDATE tproject SET disabled=1 WHERE id = $id_project";
		mysql_query($sql);
		echo "<h3 class='suc'>".$lang_label["del_incid_ok"]."</h3>";
		audit_db($id_user,$REMOTE_ADDR,"Project deleted","User ".$id_user." deleted project #".$id_project);
	} else {
		audit_db ($id_user,$REMOTE_ADDR,"ACL Forbidden","User ".$id_user." try to delete project #$id_project");
		echo "<h3 class='error'>".$lang_label["del_incid_no"]."</h3>";
		no_permission();
	}
}

// INSERT PROJECT
if ((isset($_GET["action"])) AND ($_GET["action"]=="insert")){
	if (give_acl($config["id_user"], 0, "PW") == 1){
		// Read input variables
		$usuario = give_parameter_post ("user");
		$name = give_parameter_post ("name");
		$description = give_parameter_post ('description');
		$start_date = give_parameter_post ('start_date');
		$end_date = give_parameter_post ('end_date');
		$id_owner = $usuario;
		$sql = " INSERT INTO tproject
			(name, description, start, end, id_owner) VALUES
			('$name', '$description', '$start_date', '$end_date', '$id_owner') ";
		if (mysql_query($sql)){
			$id_inc = mysql_insert_id();
			echo "<h3 class='suc'>".$lang_label["create_project_ok"]." ( id #$id_inc )</h3>";
			audit_db ($usuario, $REMOTE_ADDR, "Project created", "User ".$id_user." created project '$name'");
            
            // Add this user as profile 1 (project manager) automatically
            $sql = "INSERT INTO trole_people_project
            (id_project, id_user, id_role) VALUES
            ($id_inc, '$id_owner', 1)";
            mysql_query($sql);

            // If current user is different than owner, add also current user
            if ($config["id_user"] != $id_owner){
                $sql = "INSERT INTO trole_people_project
                (id_project, id_user, id_role) VALUES
                ($id_inc, '".$config["id_user"]."', 1)";
                mysql_query($sql);
            }

		} else {
			echo "<h3 class='err'>".$lang_label["create_project_bad"]." ( id #$id_inc )</h3>";
		}
	} else {
		audit_db($id_user, $REMOTE_ADDR, "ACL Forbidden", "User ".$id_user. " try to create project");
		no_permission();
	}
}


// MAIN LIST OF PROJECTS

echo "<h2>".$lang_label["project_management"]."</h2>";


// -------------
// Show headers
// -------------
echo "<table width='680' class='databox'>";
echo "<tr>";
echo "<th>".$lang_label["name"];
echo "<th>".$lang_label["completion"];
echo "<th>".lang_string ("Task / People");
echo "<th>".$lang_label["time_used"];
echo "<th width=82>".$lang_label["updated_at"];
echo "<th>".$lang_label["delete"];
$color = 1;

// -------------
// Show DATA TABLE
// -------------

// Simple query, needs to implement group control and ACL checking
$sql2="SELECT * FROM tproject WHERE disabled = 0"; 
if ($result2=mysql_query($sql2))	
while ($row2=mysql_fetch_array($result2)){
	if (give_acl($config["id_user"], 0, "PR") ==1){
		if ($color == 1){
			$tdcolor = "datos";
			$color = 0;
		}
		else {
			$tdcolor = "datos2";
			$color = 1;
		}
			
		if (user_belong_project ($id_user, $row2["id"]) != 0){	
			echo "<tr>";

			// Project name
			echo "<td class='$tdcolor' align='left' >";
			echo "<b><a href='index.php?sec=projects&sec2=operation/projects/task&id_project=".$row2["id"]."'>".$row2["name"]."</a></b></td>";
			// Completion
			echo "<td class='$tdcolor' align='center'>";
			$completion =  format_numeric(calculate_project_progress ($row2["id"]));
			echo "<img src='include/functions_graph.php?type=progress&width=90&height=20&percent=$completion'>";
				
            // Total task / People
            echo "<td class='$tdcolor' align='center'>";
            echo give_db_sqlfree_field ("SELECT COUNT(*) FROM ttask WHERE id_project = ".$row2["id"]);
            echo " / ";
            echo give_db_sqlfree_field ("SELECT COUNT(*) FROM trole_people_project WHERE id_project = ".$row2["id"]);

			// Time wasted
			echo "<td class='$tdcolor' align='center'>";
			echo format_numeric(give_hours_project ($row2["id"])). " hr";

			// Last update time
			echo "<td class='$tdcolor'_f9 align='center'>";
            $timestamp = give_db_sqlfree_field ( "SELECT tworkunit.timestamp FROM ttask, tworkunit_task, tworkunit WHERE ttask.id_project =  ".$row2["id"]." AND ttask.id = tworkunit_task.id_task AND tworkunit_task.id_workunit = tworkunit.id ORDER BY tworkunit.timestamp DESC LIMIT 1");
            if ($timestamp != "")
                echo human_time_comparation ( $timestamp );
            else
                echo lang_string("N/A");
               
            


		
		// Delete	
		if ((give_acl($config["id_user"], 0, "PW") ==1) OR ($config["id_user"] == $row2["id_owner"] )) {
			echo "<td class='$tdcolor' align='center'><a href='index.php?sec=projects&sec2=operation/projects/project&quick_delete=".$row2["id"]."' onClick='if (!confirm(\' ".$lang_label["are_you_sure"]."\')) return false;'><img src='images/cross.png' border='0'></a></td>";
		} else
			echo "<td class='$tdcolor' align='center'>";
		
		}
	}
}
echo "</table>";


if (give_acl($config["id_user"], 0, "PM")==1) {
	echo "<form name='boton' method='POST'  action='index.php?sec=projects&sec2=operation/projects/project_detail&insert_form'>";
	echo "<input type='submit' class='sub next' name='crt' value='".$lang_label["create_project"]."'>";
	echo "</form>";
}


?>
