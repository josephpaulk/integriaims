<?PHP

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

function combo_user_visible_for_me ($id_user, $form_name ="user_form", $any = 0, $access = "IR"){
    global $config; 
    $userlist = array();

    echo "<select name='$form_name' style='width: 200px'>";
    if ($any == 1)
        echo "<option value=''>" . lang_string ("Any");
    if ($id_user != "")
        echo "<option value='".$id_user."'>" . dame_nombre_real ($id_user);

    // If this user has present in group "any" with any permission, show all users 
    if (give_acl($id_user, 1, "")==1) {
        $result_2=mysql_query("SELECT * FROM tusuario WHERE id_usuario != '$id_user'");
        while ($row_2=mysql_fetch_array($result_2)) {
            echo "<option value='".$row_2["id_usuario"]."'>". $row_2["nombre_real"];
        }
        echo "</select>";
        return;
    }

    // Show users from my groups
    $sql_1="SELECT * FROM tusuario_perfil WHERE id_usuario = '$id_user'";
    $result_1=mysql_query($sql_1);
    while ($row_1=mysql_fetch_array($result_1)){
        $sql_2="SELECT * FROM tusuario_perfil WHERE id_grupo = ".$row_1["id_grupo"];
        $result_2=mysql_query($sql_2);
        while ($row_2=mysql_fetch_array($result_2)){
            if (give_acl($row_2["id_usuario"], $row_2["id_grupo"], $access)==1) {
                if ($row_2["id_usuario"] != $id_user){
                    if (!in_array($row_2["id_usuario"], $userlist)) {
                        array_push($userlist, $row_2["id_usuario"]);
                    }
                }
            }
        }
    }

    // Show users for group 1 (ANY)
    $sql_2="SELECT * FROM tusuario_perfil WHERE id_grupo = 1";
    $result_2=mysql_query($sql_2);
    while ($row_2=mysql_fetch_array($result_2)){
        if (give_acl($row_2["id_usuario"], $row_2["id_grupo"], $access )==1) {
            if ($row_2["id_usuario"] != $id_user){
                if (!in_array($row_2["id_usuario"], $userlist)) {
                    array_push($userlist, $row_2["id_usuario"]);
                }
            }
        }
    }

    while (sizeof($userlist) >0){
            $tempuser_id = array_pop ($userlist);
            echo "<option value='".$tempuser_id."'>". give_db_sqlfree_field ("SELECT nombre_real FROM tusuario WHERE id_usuario= '".$tempuser_id."'");
        }
    echo "</select>";
}



function combo_groups_visible_for_me ($id_user, $form_name ="group_form", $any = 0, $perm = ''){
    global $config; 
    $grouplist = array();

    echo "<select name='$form_name' style='width: 200px'>";

    // Have group "ANY" attached to any of its profiles ?
    $sql_0 = "SELECT COUNT(*) FROM tusuario_perfil WHERE id_usuario = '$id_user' AND id_grupo = 1";
    $result_0 = mysql_query($sql_0);
    $row_0 = mysql_fetch_array($result_0);
    if ($row_0[0] > 0) {
        $result_1 = mysql_query("SELECT * FROM tgrupo WHERE id_grupo > 1 ORDER BY nombre");
        while ($row_1 = mysql_fetch_array($result_1)){
            echo "<option value='".$row_1["id_grupo"]."'>".$row_1["nombre"];
        }
    }
    // Not ANY...
    else {
        if ($any == 1)
            echo "<option value='1'>". lang_string ("Any");
    
        // Show my groups
        $sql_1="SELECT * FROM tusuario_perfil WHERE id_usuario = '$id_user'";
        $result_1=mysql_query($sql_1);
        while ($row_1=mysql_fetch_array($result_1)){
            if ($row_1["id_grupo"] != 1){
                if (!in_array($row_1["id_grupo"], $grouplist)) {
                    if ($perm != ""){
                        if (give_acl($id_user, $row_1["id_grupo"], $perm )==1){
                            array_push($grouplist, $row_1["id_grupo"]);
                        }
                    } else {
                        array_push($grouplist, $row_1["id_grupo"]);
                    }
                }
            }
        }
    
        while (sizeof($grouplist) >0){
            $tempgroup_id = array_pop ($grouplist);
            echo "<option value='".$tempgroup_id."'>". give_db_sqlfree_field ("SELECT nombre FROM tgrupo WHERE id_grupo = ".$tempgroup_id);
        }
    }
    echo "</select>";
}

// Returns a combo with valid profiles for CURRENT user in this task
// ----------------------------------------------------------------------
function combo_user_task_profile ($id_task, $form_name="work_profile", $id="", $id_user = ""){
	if ($id_user == "")
		$current_user = $_SESSION["id_usuario"];
	else
		$current_user = $id_user;
	// Show only users assigned to this project
	$sql = "SELECT * FROM trole_people_task  WHERE id_task = $id_task AND id_user = '$current_user'";
	echo "<select name='$form_name'>";
	if ($result = mysql_query($sql)){
		if ($id != "")
			echo "<option value='".$id."'>".give_db_value ("name","trole","id",$id);
		while ($row=mysql_fetch_array($result)){
			echo "<option value='".$row["id_role"]."'>".give_db_value ("name","trole","id",$row["id_role"]);
		}
	} else
		echo "N/A";
	echo "</select>";
}


// Returns a combo with the users that belongs to a project
// ----------------------------------------------------------------------
function combo_users_task ($id_task){
	// Show only users assigned to this project
	$sql = "SELECT * FROM trole_people_task WHERE id_task = $id_task";
	$result = mysql_query($sql);
	echo "<select name='user' style='width: 100px;'>";
	while ($row=mysql_fetch_array($result)){
		echo "<option value='".$row["id"]."'>".$row["id_user"]." / ".give_db_value ("name","trole","id",$row["id_role"]);
	}
	echo "</select>";
}

// Returns a combo with the users that belongs to a project
// ----------------------------------------------------------------------
function combo_users_project ($id_project){
	// Show only users assigned to this project
	$sql = "SELECT * FROM trole_people_project WHERE id_project = $id_project";
	$result = mysql_query($sql);
	echo "<select name='user' style='width: 100px;'>";
	while ($row=mysql_fetch_array($result)){
		echo "<option value='".$row["id"]."'>".$row["id_user"]." / ".give_db_value ("name","trole","id",$row["id_role"]);
	}
	echo "</select>";
}


// Returns a combo with ALL the users available
// ----------------------------------------------------------------------
function combo_users ($actual = "") {
	echo "<select name='user'>";
	if ($actual != ""){ // Show current option
		echo "<option>".$actual;
	}
	$sql = "SELECT * FROM tusuario WHERE id_usuario != '$actual'";
	$result=mysql_query($sql);
	while ($row=mysql_fetch_array($result)){
		echo "<option>".$row["id_usuario"];
	}
	echo "</select>";
}


// Returns a combo with the groups available
// $mode is one ACL for access, like "IR", "AR", or "TW"
// ----------------------------------------------------------------------
function combo_groups ($actual = -1, $mode = "IR") {
	global $config;
	echo "<select name='group'>";
	if ($actual != -1){
		$sql = "SELECT * FROM tgrupo WHERE id_grupo = $actual";
		$result = mysql_query($sql);
		if ($row=mysql_fetch_array($result)){
			echo "<option value='".$row["id_grupo"]."'>".$row["nombre"];
		}
	}
	$sql="SELECT * FROM tgrupo WHERE id_grupo != $actual";
	$result=mysql_query($sql);
	while ($row=mysql_fetch_array($result)){
		if (give_acl ($config["id_user"], $row["id_grupo"], $mode) == 1)
			echo "<option value='".$row["id_grupo"]."'>".$row["nombre"];
	}
	echo "</select>";
}

// Returns a combo with the incident status available 
// ----------------------------------------------------------------------
function combo_incident_status ($actual = -1, $disabled = 0, $only_actual = 0) {
	if ($disabled != 0)
		echo "<select name='incident_status' disabled>";
	else			
		echo "<select name='incident_status'>";

	if ($only_actual != 0){
		$sql = "SELECT * FROM tincident_status WHERE id = $actual"; 
		$result = mysql_query($sql);
		if ($row=mysql_fetch_array($result)){
			echo "<option value='".$row["id"]."'>".$row["name"];
		}
	} else {
		if ($actual != -1){
			$sql = "SELECT * FROM tincident_status WHERE id = $actual";
			$result = mysql_query($sql);
			while ($row=mysql_fetch_array($result)){
				echo "<option value='".$row["id"]."'>".$row["name"];
			}
		}
		$sql = "SELECT * FROM tincident_status WHERE id != $actual";
		$result = mysql_query($sql);
		while ($row=mysql_fetch_array($result)){
			echo "<option value='".$row["id"]."'>".$row["name"];
		}
	}
	echo "</select>";
}

// Returns a combo with the incident origin
// ----------------------------------------------------------------------
function combo_incident_origin ($actual = -1, $disabled = 0) {
	if ($disabled != 0)
		echo "<select name='incident_origin' disabled>";
	else 
		echo "<select name='incident_origin'>";
	if ($actual != -1){
		$sql = "SELECT * FROM tincident_origin WHERE id = $actual";
		$result = mysql_query($sql);
		if ($row=mysql_fetch_array($result)){
			echo "<option value='".$row["id"]."'>".$row["name"];
		}
	}
	$sql = "SELECT * FROM tincident_origin WHERE id != $actual";
	$result = mysql_query($sql);
	while ($row=mysql_fetch_array($result)){
		echo "<option value='".$row["id"]."'>".$row["name"];
	}
	echo "</select>";
}

// Returns a combo with the incident resolution
// ----------------------------------------------------------------------
function combo_incident_resolution ($actual = -1) {
	echo "<select name='incident_resolution' style='width=120px;'>";
	if ($actual != -1){
		$sql = "SELECT * FROM tincident_resolution WHERE id = $actual";
		$result = mysql_query($sql);
		if ($row=mysql_fetch_array($result)){
			echo "<option value='".$row["id"]."'>".$row["name"];
		}
	}
	$sql = "SELECT * FROM tincident_resolution WHERE id != $actual";
	$result = mysql_query($sql);
	while ($row=mysql_fetch_array($result)){
		echo "<option value='".$row["id"]."'>".$row["name"];
	}
	echo "</select>";
}

// Returns a combo with the tasks that current user could see
// ----------------------------------------------------------------------
function combo_task_user ($actual = 0, $id_user, $disabled = 0, $show_vacations = 0) {
	global $config;
	global $lang_label;

	if ($disabled == 0)
		echo "<select name='task_user' style='width: 120px'>";
	else 
		echo "<select name='task_user' disabled style='width: 120px'>";

	if ($show_vacations == 1)
		echo "<option value=-1>".$lang_label["vacations"];
	
	if ($actual != 0){
		$sql = "SELECT * FROM ttask WHERE id = $actual";
		$result = mysql_query($sql);
		if ($row=mysql_fetch_array($result)){
			echo "<option value='".$row["id"]."'>".substr($row["name"],0,35);
		}
	} 

	echo "<option value=0>".$lang_label["N/A"];
	$sql = "SELECT ttask.id, ttask.name FROM ttask, trole_people_task WHERE ttask.id != $actual AND ttask.id = trole_people_task.id_task AND trole_people_task.id_user = '$id_user'";
	$result = mysql_query($sql);
	while ($row=mysql_fetch_array($result)){
		echo "<option value='".$row[0]."'>".substr($row[1],0,35);
	}
    
	echo "</select>";
}

// Returns a combo with the tasks that current user is working on
// ----------------------------------------------------------------------
function combo_task_user_participant ($id_user, $show_vacations = 0, $actual = 0) {
	global $config;
	global $lang_label;
	
	echo "<select name='task'>";
	if ($show_vacations == 1){
		echo "<option value=-1>(*) ".lang_string ("vacations");
		echo "<option value=-2>(*) ".lang_string ("not_working_by_disease");
		echo "<option value=-3>(*) ".lang_string ("not_justified");
	}
	
	if ($actual != 0){
		$sql = "SELECT id, id_project,name FROM ttask WHERE id = $actual";
		$result = mysql_query($sql);
		if ($row=mysql_fetch_array($result)){
			$id = $row[0];
			$id_project = $row[1];
			$name = $row[2];
			$project_name = give_db_value ("name", "tproject", "id", $id_project);
			echo "<option value='$id'>$project_name / $name";
		}
	} 
	echo "<option value=''>".$lang_label["N/A"];
	$sql = "SELECT DISTINCT (ttask.id) FROM ttask, trole_people_task WHERE ttask.id = trole_people_task.id_task AND trole_people_task.id_user = '$id_user' ORDER BY ttask.id_project";
	$result = mysql_query($sql);
	while ($row=mysql_fetch_array($result)){
		$id = $row[0];
		$task_name = give_db_value ("name", "ttask", "id", $id);
		$id_project = give_db_value ("id_project", "ttask", "id", $id);
		$project_name = give_db_value ("name", "tproject", "id", $id_project);
		echo "<option value='$id'>$project_name / $task_name";
	}
	echo "</select>";
}

// Returns a combo with the available roles
// ----------------------------------------------------------------------
function combo_roles ($include_na = 0, $name = 'role') {
	global $config;
	global $lang_label;
	
	echo "<select name='$name'>";
	if ($include_na == 1)
		echo "<option value=0>".$lang_label["N/A"];
	$sql = "SELECT * FROM trole";
	$result=mysql_query($sql);
	while ($row=mysql_fetch_array($result)){
		echo "<option value='".$row["id"]."'>".$row["name"];
	}
	echo "</select>";
}

// Returns a combo with projects with id_user inside participants
// ----------------------------------------------------------------------
function combo_projects_user ($id_user, $name = 'project') {
    global $config;
    
    echo "<select name='$name' style='width:200px'>";
    $sql = "SELECT * FROM trole_people_project WHERE id_user = '$id_user'";
    $result=mysql_query($sql);
    while ($row=mysql_fetch_array($result)){
        echo "<option value='".$row["id_project"]."'>".give_db_sqlfree_field("SELECT name FROM tproject WHERE id = ".$row["id_project"]);
    }
    echo "</select>";
}



function show_workunit_data ($row3, $title) {
	global $config;
	global $lang_label;

	$timestamp = $row3["timestamp"];
	$duration = $row3["duration"];
	$id_user = $row3["id_user"];
	$avatar = give_db_value ("avatar", "tusuario", "id_usuario", $id_user);
	$nota = $row3["description"];
	$id_workunit = $row3["id"];

	// Show data
	echo "<div class='notetitle'>"; // titulo
	echo "<span>";
	echo "<img src='images/avatars/".$avatar."_small.png'>&nbsp;";
	echo " <a href='index.php?sec=users&sec2=operation/users/user_edit&ver=$id_user'>";
	echo $id_user;
	echo "</a>";
	echo "&nbsp;".$lang_label["said_on"]."&nbsp;";
	echo $timestamp;
	echo "</span>";
	echo "<span style='float:right; margin-top: -15px; margin-bottom:0px; padding-right:10px;'>";
	echo $duration;
	echo "&nbsp; ".$lang_label["hr"];
	echo "</span>";
	echo "</div>";

	// Body
	echo "<div class='notebody'>";
	if (strlen($nota) > 1024){
		echo clean_output_breaks(substr($nota,0,1024));
		echo "<br><br>";
		echo "<a href='index.php?sec=incidents&sec2=operation/common/workunit_detail&id=".$id_workunit."&title=$title'>";
		echo $lang_label["read_more"];
		echo "</a>";
	} else {
		echo clean_output_breaks($nota);
	}
	echo "</div>";
}

function topi_richtext ( $string ){
	$imageBullet = "<img src='images/bg_bullet_full_1.gif'>";
	$string = str_replace ( "->", $imageBullet, $string);
	$string = str_replace ( "*", $imageBullet, $string);
	$string = str_replace ( "[b]", "<b>",  $string);
	$string = str_replace ( "[/b]", "</b>",  $string);
	$string = str_replace ( "[u]", "<u>",  $string);
	$string = str_replace ( "[/u]", "</u>",  $string);
	$string = str_replace ( "[i]", "<i>",  $string);
	$string = str_replace ( "[/i]", "</i>",  $string);
	return $string;
}
 

function show_workunit_user ($id_workunit, $full = 0) {
	global $config;
	global $lang_label;

	$sql = "SELECT * FROM tworkunit WHERE id = $id_workunit";
	if ($res = mysql_query($sql)) 
		$row=mysql_fetch_array($res);
	else
		return;
		
	$timestamp = $row["timestamp"];
	$duration = $row["duration"];	
	$id_user = $row["id_user"];
	$avatar = give_db_value ("avatar", "tusuario", "id_usuario", $id_user);
	$nota = $row["description"];
	$have_cost = $row["have_cost"];
	$profile = $row["id_profile"];
    $locked = $row["locked"];
	$id_task = give_db_value ("id_task", "tworkunit_task", "id_workunit", $row["id"]);
    $id_group = give_db_value ("id_group", "ttask", "id", $id_task);
	$id_project = give_db_value ("id_project", "ttask", "id", $id_task);
	$task_title = substr(give_db_value ("name", "ttask", "id", $id_task), 0, 50);
	$project_title = substr(give_db_value ("name", "tproject", "id", $id_project), 0, 50);
	// Show data
	echo "<div class='notetitle' style='height: 50px;'>"; // titulo
	echo "<table border=0 width='100%' cellspacing=0 cellpadding=0 style='margin-left: 0px;margin-top: 0px;'>";
	echo "<tr><td rowspan=3 width='7%'>";
	echo "<img src='images/avatars/".$avatar."_small.png'>";
	
	echo "<td width='60%'><b>";
	echo lang_string ("task")." </b> : ";
	echo $task_title;

	echo "<td width='13%'><b>";
	echo lang_string ("duration")."</b>";

	echo "<td width='20%'>";
	echo " : ".format_numeric($duration);


	echo "<tr>";
	echo "<td><b>";
	echo lang_string ("project")." </b> : ";
	echo $project_title;

	echo "<td><b>";
	
	if ($have_cost != 0){
		$profile_cost = give_db_value ("cost", "trole", "id", $profile);
		$cost = format_numeric ($duration * $profile_cost);
		$cost = $cost ." &euro;";
	} else
		$cost = $lang_label["N/A"];
	echo lang_string ("cost");
	echo "</b>";
	echo "<td>";
	echo " : ".$cost;

	
	echo "<tr>";
	echo "<td>";
	echo "<a href='index.php?sec=users&sec2=operation/users/user_edit&ver=$id_user'>";
	echo "<b>".$id_user."</b>";
	echo "</a>";
	echo "&nbsp;".$lang_label["said_on"]."&nbsp;";
	echo $timestamp;
	echo "<td><b>";
	echo lang_string ("profile");
	echo "</b></td><td>";
	echo " : ".give_db_value ("name", "trole", "id", $profile);
	echo "</table>";
	echo "</div>";

	// Body
	echo "<div class='notebody'>";
	echo "<table width='100%'  border=0 cellpadding=0 cellspacing=0>";
	echo "<tr><td valign='top'>";
	
	if ((strlen($nota) > 1024) AND ($full == 0)){
		echo topi_richtext ( clean_output_breaks(substr($nota,0,1024)) );
		echo "<br><br>";
		echo "<a href='index.php?sec=users&sec2=operation/users/user_workunit_report&id_workunit=".$id_workunit."&title=$task_title'>";
		echo $lang_label["read_more"];
		echo "</a>";
	} else {
		echo topi_richtext(clean_output_breaks($nota));
	}
	echo "<td valign='top'>";
	echo "<table width='100%'  border=0 cellpadding=0 cellspacing=0>";
	
	if ((project_manager_check($id_project) == 1) OR ($id_user == $config["id_user"]) OR  (give_acl($config["id_user"], $id_group, "TM")) ) {	
		echo "<tr><td align='right'>";
		echo "<br>";
		echo "<a href='index.php?sec=projects&sec2=operation/projects/task_workunit&id_project=$id_project&id_task=$id_task&id_workunit=$id_workunit&operation=delete'><img src='images/cross.png' border='0'></a>";
	}

    // Edit workunit
	if ((project_manager_check($id_project) == 1) OR (give_acl($config["id_user"], $id_group, "TM")) OR (($id_user == $config["id_user"]) AND ($locked == 0)) ) { 
		echo "<tr><td align='right'>";
		echo "<br>";
		echo "<a href='index.php?sec=projects&sec2=operation/projects/task_create_work&id_project=$id_project&id_task=$id_task&id_workunit=$id_workunit&operation=edit'><img border=0 src='images/page_white_text.png'></a>";
		echo "</td>";
	}
    
// Lock workunit
	if (((project_manager_check($id_project) == 1) OR (give_acl($config["id_user"], $id_group, "TM")) OR ($id_user == $config["id_user"])) AND ($locked == 0) ) { 
		echo "<tr><td align='right'>";
		echo "<br>";
		echo "<a href='index.php?sec=projects&sec2=operation/projects/task_workunit&id_project=$id_project&id_task=$id_task&id_workunit=$id_workunit&operation=lock'><img border=0 src='images/lock.png'></a>";
		echo "</td>";
	} 
  	echo "</tr></table>";
	echo "</tr></table>";
	echo "</div>";
}

?>
