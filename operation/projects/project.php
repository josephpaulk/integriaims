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

// Disable project
// ======================

if (isset($_GET["quick_delete"])){
	$id_project = $_GET["quick_delete"];
	$id_owner = give_db_value ("id_owner", "tproject", "id", $id_project);
	if (($id_owner == $id_user) OR (dame_admin ($id_user))) {
		// delete_project ($id_project);
		$sql =" UPDATE tproject SET disabled=1 WHERE id = $id_project";
		mysql_query($sql);
		echo "<h3 class='suc'>".$lang_label["del_incid_ok"]."</h3>";
		audit_db($id_user,$REMOTE_ADDR,"Project disabled","User ".$id_user." disabled project #".$id_project);
	} else {
		audit_db ($id_user,$REMOTE_ADDR,"ACL Forbidden","User ".$id_user." try to disable project #$id_project");
		echo "<h3 class='error'>".$lang_label["del_incid_no"]."</h3>";
		no_permission();
	}
}

// Reactivate project
// ======================

if (isset($_GET["activate"])){
	$id_project = $_GET["activate"];
	$id_owner = give_db_value ("id_owner", "tproject", "id", $id_project);
	if (($id_owner == $id_user) OR (dame_admin ($id_user))) {
		$sql =" UPDATE tproject SET disabled=0 WHERE id = $id_project";
		mysql_query($sql);
		echo "<h3 class='suc'>".$lang_label["del_incid_ok"]."</h3>";
		audit_db($id_user,$REMOTE_ADDR,"Project activated","User ".$id_user." activated project #".$id_project);
	} else {
		audit_db ($id_user,$REMOTE_ADDR,"ACL Forbidden","User ".$id_user." try to activate project #$id_project");
		echo "<h3 class='error'>".$lang_label["del_incid_no"]."</h3>";
		no_permission();
	}
}


// REAL PROJECT DELETE
// ======================
if (isset($_GET["real_delete"])){
    $id_project = $_GET["real_delete"];
    $id_owner = give_db_value ("id_owner", "tproject", "id", $id_project);
    if (($id_owner == $id_user) OR (dame_admin ($id_user))) {
        // delete_project ($id_project);
        $sql ="DELETE FROM tproject WHERE disabled=1 AND id = $id_project";
        mysql_query($sql);
        $sql ="DELETE FROM ttask WHERE id_project = $id_project";
        mysql_query($sql);

        echo "<h3 class='suc'>".$lang_label["del_incid_ok"]."</h3>";
        audit_db($id_user,$REMOTE_ADDR,"Project deleted","User ".$id_user." deleted project #".$id_project);


        // Workunits ARE NOT DELETED -NEVER-
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
		$id_project_group = get_parameter ("id_project_group",0);

		$id_owner = $usuario;
		$sql = " INSERT INTO tproject
			(name, description, start, end, id_owner, id_project_group) VALUES
			('$name', '$description', '$start_date', '$end_date', '$id_owner', '$id_project_group') ";
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

$filter_id_project_group = get_parameter ("filter_id_project_group", 0);
$filter_id_group = get_parameter ("filter_id_group", 0);
$filter_freetext = get_parameter ("filter_freetext", "");

$FILTER = " 1=1 ";

if ( $filter_freetext != "")
	$FILTER .= " AND name LIKE '%$filter_freetext%' OR description LIKE '%$filter_freetext%' ";

if ($filter_id_project_group != 0)
	$FILTER .= " AND id_project_group = $filter_id_project_group";

if ($filter_id_group != 0)
	$FILTER .= " AND id_group = $filter_id_group";



echo "<table width=710 class=box>";
	echo "<form method=post action='index.php?sec=projects&sec2=operation/projects/project'>";
	echo "<tr><td>";
	echo lang_string ("Free text search");
	echo "<td>";
	echo print_input_text ("filter_freetext", $filter_freetext, "", 15, 100, false);
	echo "<td>";
	echo lang_string ("Project group");
	echo "<td>";
	echo print_select_from_sql ("SELECT * from tproject_group ORDER BY name", "filter_id_project_group", $filter_id_project_group, "", lang_string("None"), '0', false, false, true, false);

	echo "<td>";
	print_submit_button (lang_string("Search"), "enviar", false, "class='sub search'", false);
	echo "</form></td></tr></table>";


// -------------
// Show headers
// -------------
echo "<table width='740' class='listing'>";
echo "<tr>";
echo "<th>".$lang_label["name"];
echo "<th>".__ ("PG");
echo "<th>".$lang_label["completion"];
echo "<th>".lang_string ("Task / People");
echo "<th>".$lang_label["time_used"];
echo "<th>".lang_string ("Cost");
echo "<th>".$lang_label["updated_at"];
echo "<th>".$lang_label["delete"];

// -------------
// Show DATA TABLE
// -------------

$view_disabled = get_parameter ("view_disabled", 0);
// Simple query, needs to implement group control and ACL checking
$sql2="SELECT * FROM tproject WHERE $FILTER AND disabled = $view_disabled ORDER by name"; 
if ($result2=mysql_query($sql2))	
while ($row2=mysql_fetch_array($result2)){
	if (give_acl($config["id_user"], 0, "PR") ==1){
		if (user_belong_project ($id_user, $row2["id"]) != 0){	
			echo "<tr>";
		
			// Project name
			echo "<td align='left' >";
			echo "<b><a href='index.php?sec=projects&sec2=operation/projects/task&id_project=".$row2["id"]."'>".$row2["name"]."</a></b></td>";

			// Project group
			echo "<td>";
			if ($row2["id_project_group"] > 0){
				$icon = get_db_sql ("SELECT icon FROM tproject_group WHERE id = ". $row2["id_project_group"]);
				$name = get_db_sql ("SELECT name FROM tproject_group WHERE id = ". $row2["id_project_group"]);

				echo "<a href='index.php?sec=projects&sec2=operation/projects/project&filter_id_project_group=".$row2["id_project_group"]."'><img src='images/project_groups_small/".$icon."' border=0 title='$name'></a>";
			}

			// Progress
			echo "<td >";
			if ($row2["start"] == $row2["end"]){
				echo "<img src='images/comments.png'> ";
				echo lang_string ("Unlimited");
			} else {
				$completion =  format_numeric(calculate_project_progress ($row2["id"]));
				echo "<img src='include/functions_graph.php?type=progress&width=90&height=20&percent=$completion'>";
			}
				
            // Total task / People
            echo "<td >";
            echo give_db_sqlfree_field ("SELECT COUNT(*) FROM ttask WHERE id_project = ".$row2["id"]);
            echo " / ";
            echo give_db_sqlfree_field ("SELECT COUNT(*) FROM trole_people_project WHERE id_project = ".$row2["id"]);

			// Time wasted
			echo "<td class='f9' >";
			echo format_numeric(give_hours_project ($row2["id"])). " hr";
            
            // Costs (client / total)
            echo "<td class='f9' >";
            echo format_numeric (project_workunit_cost ($row2["id"], 1));
            echo $config["currency"];
            //echo " / ";
            //echo format_numeric (project_workunit_cost ($row2["id"], 0));

	    	// Last update time
	    	echo "<td class='f9' >";
            $timestamp = give_db_sqlfree_field ( "SELECT tworkunit.timestamp FROM ttask, tworkunit_task, tworkunit WHERE ttask.id_project =  ".$row2["id"]." AND ttask.id = tworkunit_task.id_task AND tworkunit_task.id_workunit = tworkunit.id ORDER BY tworkunit.timestamp DESC LIMIT 1");
            if ($timestamp != "")
                echo human_time_comparation ( $timestamp );
            else
                echo lang_string("N/A");
		
		// Delete	
		if ((give_acl($config["id_user"], 0, "PW") ==1) OR ($config["id_user"] == $row2["id_owner"] )) {
            if ($view_disabled == 0)
			     echo "<td><a href='index.php?sec=projects&sec2=operation/projects/project&quick_delete=".$row2["id"]."' onClick='if (!confirm(\' ".$lang_label["are_you_sure"]."\')) return false;'><img src='images/cross.png' border='0'></a></td>";
            else {
                echo "<td '><a href='index.php?sec=projects&sec2=operation/projects/project&view_disabled=1&real_delete=".$row2["id"]."' onClick='if (!confirm(\' ".$lang_label["are_you_sure"]."\')) return false;'><img src='images/cross.png' border='0'></a> &nbsp;";
                echo "<a href='index.php?sec=projects&sec2=operation/projects/project&view_disabled=1&activate=".$row2["id"]."' ><img src='images/play.gif' border='0'></a></td>";
           } 
		} else
			echo "<td></td>";
		}
	}
}
echo "</table>";

/*
if (give_acl($config["id_user"], 0, "PM")==1) {
	echo "<table width=100% class='button'>";
	echo "<tr><td align=right>";
	echo "<form name='boton' method='POST'  action='index.php?sec=projects&sec2=operation/projects/project_detail&insert_form'>";
	echo "<input type='submit' class='sub next' name='crt' value='".$lang_label["create_project"]."'>";
	echo "</form>";
	echo "</td></tr></table>";
}
*/

?>
