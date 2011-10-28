<?PHP

// INTEGRIA IMS v2.0
// http://www.integriaims.com
// ===========================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2008 Esteban Sanchez, estebans@artica.es
// Copyright (c) 2007-2008 Artica, info@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License
// (LGPL) as published by the Free Software Foundation; version 2

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.

global $config;
include_once ("functions_incidents.php");

function combo_user_visible_for_me ($id_user, $form_name ="user_form", $any = false, $access = "IR", $return = false, $label = false, $both = true, $anygroup = false) {
	global $config;
	
	$userlist = array ();
	$output = '';

	$values = get_user_visible_users ($config['id_user'], $access, true, $both, $anygroup);
	if ($any)
		$values[''] = __('Any');

	$output .= print_select ($values, $form_name, $id_user, '', '', 0, true, false, false, $label);

	if ($return)
		return $output;
	echo $output;
}




function combo_groups_visible_for_me ($id_user, $form_name ="group_form", $any = 0, $perm = '', $id_group = 0, $return = false, $label = 1) {
	$output = '';

	$values = array ();

	$groups = get_user_groups ($id_user, $perm);
	
	if ($any) {
		$groups[1] = __('Any');
	} else {
         unset($groups[1]);
    }
	
	if ($label == 1)
		$output .= print_select ($groups, $form_name, $id_group, '', '', 0, true, false, false, __('Group'));
	else
		$output .= print_select ($groups, $form_name, $id_group, '', '', 0, true, false, false, '');

	if ($return)
		return $output;
	echo $output;
	return;
}

// Returns a combo with valid profiles for CURRENT user in this task
// ----------------------------------------------------------------------
function combo_user_task_profile ($id_task, $form_name = "work_profile", $selected = "", $id_user = false, $return = false) {
	global $config;
	
	$output = '';
	
	if (! $id_user)
		$id_user = $config['id_user'];
	$where_clause = '';
	if ($id_task)
		$where_clause = sprintf ('AND id_task = %d', $id_task);
	
	// Show only users assigned to this project
	$sql = sprintf ('SELECT trole.id, trole.name
		FROM trole_people_task, trole
		WHERE trole.id = trole_people_task.id_role
		%s
		AND id_user = "%s"
		ORDER BY name',
		$where_clause, $id_user);
	$output .= print_select_from_sql ($sql, $form_name, $selected, '', '', '',
		true, false, false, __('Role'));
	
	if ($return)
		return $output;
	echo $output;
}


// Returns a combo with the users that belongs to a task
// ----------------------------------------------------------------------
function combo_users_task ($id_task, $icon_list = false, $return = false) {
	global $config;

	// Show only users assigned to this project
	$task_users = get_db_all_rows_field_filter ('trole_people_task', 'id_task', $id_task);
	$visible_users = get_user_visible_users ($config["id_user"], 'PR', true);
	$users = array ();

	if ($task_users)	
	foreach ($task_users as $user) {
		if (isset ($visible_users[$user['id_user']]))
			if ($icon_list)
				array_push ($users, $user);
			else
				$users[$user['id_user']] = $user['id_user'];
	}
	
	$output = '';
	
	if (! $icon_list) {
		$output .= print_select ($users, 'user', '', '', '', '', true);
	} else {
		// Show also groupname
		$sql = sprintf ('SELECT nombre FROM tgrupo, ttask
			WHERE ttask.id_group = tgrupo.id_grupo
			AND ttask.id = %d', $id_task);
		$group_name = get_db_sql ($sql);
		$text = __('Group').' <strong>'.$group_name.'</strong><br />';
		foreach ($users as $user) {
			$text .= $user["id_user"];
			$text .= ", ";
		}
		$output .= print_help_tip ($text, true, 'tip_people');
	}
	
	if ($return)
		return $output;
	echo $output;
}

// Returns a combo with the users that belongs to a project
// ----------------------------------------------------------------------
function combo_users_project ($id_project){
	// Show only users assigned to this project
	$sql = "SELECT * FROM trole_people_project WHERE id_project = $id_project ORDER by id_user";
	$result = mysql_query($sql);
	echo "<select name='user' style='width: 100px;'>";
	while ($row=mysql_fetch_array($result)){
		echo "<option value='".$row["id"]."'>".$row["id_user"]." / ".get_db_value ("name","trole","id",$row["id_role"]);
	}
	echo "</select>";
}

// Returns a combo with categories
// ----------------------------------------------------------------------
function combo_kb_categories ($id_category, $show_any = 0){
	global $config;

	if ($id_category == 0)
		$id_category = 1;

	

	echo "<select name='category' style='width: 180px;'>";
	if ($show_any != 0){
		$id_category = -1;
		echo "<option value=''>".__("Any");
	}	
	$sql = "SELECT * FROM tkb_category WHERE id != $id_category ORDER by parent, name";
	$result = mysql_query($sql);
	
	$parent = get_db_value ("parent","tkb_category","id",$id_category);
	$parent_name = get_db_value ("name","tkb_category","id",$parent);
	$name = get_db_value ("name","tkb_category","id",$id_category);
	if ($parent != 0)
		echo "<option value='".$id_category."'>".$parent_name."/".$name;
	else
		echo "<option value='".$id_category."'>".$name;

	while ($row=mysql_fetch_array($result)){
		$parent = get_db_value ("name","tkb_category","id",$row["parent"]);
		if ($parent != "")
			echo "<option value='".$row["id"]."'>".$parent . "/".$row["name"];
		else
			echo "<option value='".$row["id"]."'>".$row["name"];
	}
	echo "</select>";
}


// Returns a combo with products
// ----------------------------------------------------------------------
function combo_kb_products ($id_product, $show_none = 0, $label = '', $return = false) {
	$output = '';
	
	$none = '';
	$none_value = '';
	if ($show_none) {
		$none = __('None');
		$none_value = 0;
	}
	
	$sql = "";
	$output = print_select_from_sql ('SELECT id, name FROM tkb_product ORDER BY name',
		'product', $id_product, '', $none, $none_value, true, false, false, $label);
	
	if ($return)
		return $output;
	echo $output;
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
	echo "<select id='group' name='group'>";
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
function combo_incident_status ($actual = -1, $disabled = 0, $actual_only = 0, $return = false, $for_massives = false) {
	$output = '';

	if ($disabled) {
		$value = __(get_db_value ('name', 'tincident_status', 'id', $actual));
		$output .= print_label (__('Status'), '', '', true, $value);
		if ($return)
			return $output;
		echo $output;
		return;
	}
	if ($actual_only)
		$sql = sprintf ('SELECT id, name FROM tincident_status WHERE id = %d', $actual);
	else
		$sql = 'SELECT id, name FROM tincident_status';
	
	$rows = get_db_all_rows_sql ($sql);
	$values = array ();
	foreach ($rows as $row)
		$values[$row['id']] = __($row['name']);

	if($for_massives) {
	$output .= print_select ($values, 'mass_status', $actual, '', __('Select'), -1,
		true);
	}
	else {
	$output .= print_select ($values, 'incident_status', $actual, '', '', 0,
		true, false, false, __('Status'));
	}

	if ($return)
		return $output;
	echo $output;
}

// Returns a combo with the incident origin
// ----------------------------------------------------------------------
function combo_incident_origin ($actual = -1, $disabled = 0, $return = false) {
	$output = '';

	if ($disabled) {
		$origins = get_incident_resolutions ();
		$origin = isset ($origins[$actual]) ? $origins[$actual] : __('None');
		$output .= print_label (__('Source'), '', '', true, $origin);
		if ($return)
			return $output;
		echo $output;
		return;
	}
		
	$output .= print_select (get_incident_origins (), 'incident_origin',
					$actual, '', '', 0, true, false, false, __('Source'));
	if ($return)
		return $output;
	echo $output;
}

// Returns a combo with the incident resolution
// ----------------------------------------------------------------------
function combo_incident_resolution ($actual = -1, $disabled = false, $return = false, $for_massives = false) {
	$output = '';
	
	if ($disabled) {
		$resolutions = get_incident_resolutions ();
		$resolution = isset ($resolutions[$actual]) ? $resolutions[$actual] : __('None');
		
		$output .= print_label (__('Resolution'), '', '', true, $resolution);
		if ($return)
			return $output;
		echo $output;
		return;
	}

	if($for_massives) {
		$output .= print_select (get_incident_resolutions (),
						'mass_resolution', $actual, '', __('Select'),
						-1, true);
	}
	else {
		$output .= print_select (get_incident_resolutions (),
						'incident_resolution', $actual, '', __('None'),
						0, true, false, false, __('Resolution'));
	}
	
	if ($return)
		return $output;
	echo $output;
}

function combo_incident_types ($selected, $disabled = false, $return = false) {
	$output = '';
	
	$types = get_incident_types ();
	
	if ($disabled) {
		$value = isset ($types[$selected]) ? $types[$selected] : __('None');
		$output .= print_label (__('Type'), '', '', true, $value);
		
		if ($return)
			return $output;
		echo $output;
		return;
	}
	
	$output .= print_select ($types, 'id_incident_type', $selected, '',
		__('None'), 0, true, false, true, __('Type'));
	if ($return)
		return $output;
	echo $output;
}

// Returns a combo with the tasks that current user could see
// ----------------------------------------------------------------------
function combo_task_user ($actual, $id_user, $disabled = 0, $show_vacations = 0, $return = false) {
	$output = '';

	if ($disabled) {
		$output .= print_label (__('Task'), '', '', true);
		$name = get_db_value ('name', 'ttask', 'id', $actual);
		if ($name === false)
			$name = __('N/A');
		$output .= $name;
		if ($return)
			return $output;
		echo $output;
		return;
	}

	$values = array ();
	$values[0] = __('N/A');
	if ($show_vacations == 1)
		$values[-1] = __('Vacations');

	$sql = sprintf ('SELECT ttask.id, ttask.name as tname, tproject.name as pname
			FROM tproject, ttask, trole_people_task
			WHERE ttask.id_project = tproject.id AND tproject.disabled = 0 AND ttask.id = trole_people_task.id_task
			AND trole_people_task.id_user = "%s"
			ORDER BY pname',
			$id_user);
	$tasks = get_db_all_rows_sql ($sql);
	if ($tasks === false)
		$tasks = array ();
	foreach ($tasks as $task) {
		$values[$task['id']] = substr($task['pname'],0,17). " / ".substr($task['tname'],0,22);
	}
	$output = print_select ($values, 'task_user', $actual, '', '',
				0, true, false, false, __('Task'));
	if ($return)
		return $output;
	echo $output;
	return;
}

// Returns a combo with the tasks that current user is working on
// ----------------------------------------------------------------------
function combo_task_user_participant ($id_user, $show_vacations = false, $actual = 0, $return = false, $label = false) {
	$output = '';
	$values = array ();
	
	if ($show_vacations) {
		$values[-1] = "(*) ".__('Vacations');
		$values[-2] = "(*) ".__('Not working for disease');
		$values[-3] = "(*) ".__('Not justified');
	}
	
	$sql = sprintf ('SELECT ttask.id, tproject.name,ttask.name 
			FROM ttask, trole_people_task, tproject
			WHERE ttask.id_project = tproject.id
			AND tproject.disabled = 0
			AND ttask.id = trole_people_task.id_task
			AND trole_people_task.id_user = "%s" 
			ORDER BY tproject.name', $id_user);
	
	$tasks = get_db_all_rows_sql ($sql);

	if ($tasks)
	foreach ($tasks as $task){
		$values[$task[0]] = $task[1]."/".$task[2];
	}

	$output .= print_select ($values, 'id_task', $actual, '', __('N/A'), '0', true,
		false, false, $label);

	if ($return)
		return $output;
	echo $output;
}

// Returns a combo with the available roles
// ----------------------------------------------------------------------
function combo_roles ($include_na = false, $name = 'role', $label = '', $return = false) {
	global $config;
	
	$output = '';
	
	$nothing = '';
	$nothing_value = '';
	if ($include_na) {
		$nothing = __('N/A');
		$nothing_value = 0;
	}
	$output .= print_select_from_sql ('SELECT id, name FROM trole',
		$name, '', '', $nothing, $nothing_value, true, false, false, $label);
	
	if ($return)
		return $output;
	echo $output;
}

// Returns a combo with projects with id_user inside participants
// ----------------------------------------------------------------------
function combo_projects_user ($id_user, $name = 'project') {
	global $config;

	echo "<select name='$name' style='width:200px'>";
	$sql = "SELECT DISTINCT(id_project) FROM trole_people_project WHERE id_user = '$id_user'";
	$result=mysql_query($sql);
	while ($row=mysql_fetch_array($result)){
		$nombre = get_db_sql("SELECT name FROM tproject WHERE disabled=0 AND id = ".$row[0]);
		if ($nombre != "")
		echo "<option value='".$row[0]."'>".$nombre;
	}
	echo "</select>";
}

function topi_richtext ($string) {
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


function show_workunit_data ($workunit, $title) {
	global $config;
	
	$timestamp = $workunit["timestamp"];
	$duration = $workunit["duration"];
	$id_user = $workunit["id_user"];
	$avatar = get_db_value ("avatar", "tusuario", "id_usuario", $id_user);
	$nota = $workunit["description"];
	$id_workunit = $workunit["id"];
	$public = $workunit["public"];
	$locked = $workunit["locked"];

	$sql = sprintf ('SELECT tincidencia.id_grupo
			FROM tincidencia, tworkunit_incident
			WHERE tworkunit_incident.id_workunit = %d
			AND tincidencia.id_incidencia = tworkunit_incident.id_incident',
			$id_workunit);
	$id_group = get_db_sql ($sql);

	$sql = sprintf ('SELECT tworkunit_incident.id_incident
                        FROM tincidencia, tworkunit_incident
                        WHERE tworkunit_incident.id_workunit = %d
                        AND tincidencia.id_incidencia = tworkunit_incident.id_incident',
                        $id_workunit);
        $id_incident = get_db_sql ($sql);

	// ACL Check for visibility
	if (!$public && $id_user != $config["id_user"] && ! give_acl ($config["id_user"], $id_group, "IM"))
		return;

	// Show data
	echo '<div class="notetitle">';
	echo "<span>";
	print_user_avatar ($id_user, true);
	echo " <a href='index.php?sec=users&sec2=operation/users/user_edit&id=$id_user'>";
	echo $id_user;
	echo "</a>";
	echo " ".__('said').' <span title="'.$timestamp.'">'.human_time_comparation ($timestamp).'</span>';
	echo "</span>";

	// Public WU ?
	echo "<span style='float:right; margin-top: -1px; margin-bottom:0px; padding-right:10px;'>";
	if ($public == 1)
		echo "<img src='images/group.png' title='".__('Public Workunit')."' border=0>";
	else
		echo "<img src='images/delete.png' title='".__('Non public Workunit')."' border=0>";
	echo "</span>";

	// WU Duration 
	echo "<span style='float:right; margin-top: -1px; margin-bottom:0px; padding-right:10px;'>";
	echo $duration;
	echo "&nbsp; ".__('Hours');
	echo "</span>";

	echo "</div>";

	// Body
	echo "<div class='notebody'>";
	if (strlen ($nota) > 3024) {
		echo clean_output_breaks (substr ($nota, 0, 1024));
		echo "<br /><br />";
		echo "<a href='index.php?sec=incidents&sec2=operation/common/workunit_detail&id=".$id_workunit."&id_incident=$id_incident'>";
		echo __('Read more...');
		echo "</a>";
	} else {
		echo clean_output_breaks ($nota);
	}
	echo "</div>";
}


function show_workunit_user ($id_workunit, $full = 0) {
	global $config;
	
	$sql = "SELECT * FROM tworkunit WHERE id = $id_workunit";
	if ($res = mysql_query($sql))
		$row=mysql_fetch_array($res);
	else
		return;

	$timestamp = $row["timestamp"];
	$duration = $row["duration"];
	$id_user = $row["id_user"];
	$avatar = get_db_value ("avatar", "tusuario", "id_usuario", $id_user);
	$nota = $row["description"];
	$have_cost = $row["have_cost"];
	$profile = $row["id_profile"];
	$public = $row["public"];
	$locked = $row["locked"];
	$id_task = get_db_value ("id_task", "tworkunit_task", "id_workunit", $row["id"]);
	if ($id_task == "")
		$id_incident = get_db_value ("id_incident", "tworkunit_incident", "id_workunit", $row["id"]);
	$id_group = get_db_value ("id_group", "ttask", "id", $id_task);
	$id_project = get_db_value ("id_project", "ttask", "id", $id_task);
	$task_title = substr(get_db_value ("name", "ttask", "id", $id_task), 0, 50);
	if ($id_task == "")
		$incident_title = substr(get_db_value ("titulo", "tincidencia", "id_incidencia", $id_incident), 0, 50);
	$project_title = substr(get_db_value ("name", "tproject", "id", $id_project), 0, 50);

	// ACL Check for visibility
	if (!$public && $id_user != $config["id_user"] && ! give_acl ($config["id_user"], $id_group, "TM"))
		return;


	// Show data
	echo "<div class='notetitle' style='height: 75px;'>"; // titulo
	echo "<table class='blank' border=0 width='100%' cellspacing=0 cellpadding=0 style='margin-left: 0px;margin-top: 0px; background: transparent;'>";
	echo "<tr><td rowspan=3 width='7%'>";
	print_user_avatar ($id_user, true);

	echo "<td width='60%'><b>";
	if ($id_task != ""){
		echo __('Task')." </b> : ";
		echo "<a href='index.php?sec=projects&sec2=operation/projects/task_detail&id_task=$id_task&operation=view'>$task_title</A>";
	} else  {
		echo __('Incident')." </b> : ";
		echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident&id=$id_incident'>$incident_title</A>";
	}
	echo "<td width='13%'><b>";
	echo __('Duration')."</b>";

	echo "<td width='20%'>";
	echo " : ".format_numeric($duration);
	// Public WU ?
	echo "<span style='float:right; margin-top: -15px; margin-bottom:0px; padding-right:10px;'>";
	if ($public == 1)
		echo "<img src='images/group.png' title='".__('Public Workunit')."' />";
	else
		echo "<img src='images/delete.png' title='".__('Non public Workunit')."' />";
	echo "</span>";

	echo "<tr>";
	echo "<td><b>";
	if ($id_task != "") {
		echo __('Project')." </b> : ";
		echo "<a href='index.php?sec=projects&sec2=operation/projects/task&id_project=$id_project'>$project_title</A>";
	} else {
		echo __('Group')."</b> : ";
		echo dame_nombre_grupo (get_db_sql ("SELECT id_grupo FROM tincidencia WHERE id_incidencia = $id_incident"));
	}

	echo "<td><b>";

	if ($have_cost != 0){
		$profile_cost = get_db_value ("cost", "trole", "id", $profile);
		$cost = format_numeric ($duration * $profile_cost);
		$cost = $cost ." &euro;";
	} else
		$cost = __('N/A');
	echo __('Cost');
	echo "</b>";
	echo "<td>";
	echo " : ".$cost;


	echo "<tr>";
	echo "<td>";
	echo "<a href='index.php?sec=users&sec2=operation/users/user_edit&id=$id_user'>";
	echo "<b>".$id_user."</b>";
	echo "</a>";
	echo " ".__('said on').' '.$timestamp;
	echo "<td><b>";
	echo __('Profile');
	echo "</b></td><td>";
	echo " : ".get_db_value ("name", "trole", "id", $profile);
	echo "</table>";
	echo "</div>";

	// Body
	echo "<div class='notebody'>";
	echo "<table width='100%'  class='blank'>";
	echo "<tr><td valign='top'>";

	if ((strlen($nota) > 1024) AND ($full == 0)) {
		echo topi_richtext (clean_output_breaks (substr ($nota, 0, 1024)));
		echo "<br><br>";
		echo "<a href='index.php?sec=users&sec2=operation/users/user_workunit_report&id_workunit=".$id_workunit."&title=$task_title'>";
		echo __('Read more...');
		echo "</a>";
	} else {
		echo topi_richtext(clean_output_breaks($nota));
	}
	echo "<td valign='top'>";
	echo "<table width='100%'  class='blank'>";

	if ($_GET["sec2"] == "operation/users/user_workunit_report")
		$myurl = "index.php?sec=users&sec2=operation/users/user_workunit_report&id=$id_user";
	else {
		if ($id_project > 0)
			$myurl = "index.php?sec=projects&sec2=operation/users/user_spare_workunit&id_project=$id_project&id_task=$id_task";
		else
			$myurl = "index.php?sec=users&sec2=operation/users/user_workunit_report&id=$id_user";
	}
	
	if ((project_manager_check($id_project) == 1) OR ($id_user == $config["id_user"]) OR  (give_acl($config["id_user"], $id_group, "TM")) ) {
		echo "<tr><td align='right'>";
		echo "<br>";
		echo "<a class='delete-workunit' id='delete-$id_workunit' href='$myurl&id_workunit=$id_workunit&operation=delete' onclick='if (!confirm(\"".__('Are you sure?')."\")) return false;'><img src='images/cross.png' /></a>";
	}

	// Edit workunit
	if (((project_manager_check($id_project) == 1) OR (give_acl($config["id_user"], $id_group, "TM")) OR ($id_user == $config["id_user"])) AND (($locked == "") OR (give_acl($config["id_user"], $id_group, "UM")) )) {
		echo "<tr><td align='right'>";
		echo "<br>";
		echo "<a class='edit-workunit' id='edit-$id_workunit' href='index.php?sec=projects&sec2=operation/users/user_spare_workunit&id_project=$id_project&id_task=$id_task&id_workunit=$id_workunit'><img border=0 src='images/page_white_text.png' title='".__('Edit workunit')."'></a>";
		echo "</td>";
	}

	// Lock workunit
	if (((project_manager_check($id_project) == 1) OR (give_acl($config["id_user"], $id_group, "TM")) OR ($id_user == $config["id_user"])) AND (($locked == "")  )) {
		echo "<tr><td align='right'>";
		echo "<br>";
		echo "<a class='lock_workunit' id='lock-$id_workunit' href='$myurl&id_workunit=$id_workunit&operation=lock'><img src='images/lock.png' title='".__('Lock workunit')."'></a>";
		echo "</td>";
	} else {
		echo "<tr><td align='right'>";
		echo "<br><img src='images/rosette.png' title='".__('Locked by')." $locked'";
		echo print_user_avatar ($locked, true);
		echo "</td>";
	}

  	echo "</tr></table>";
	echo "</tr></table>";
	echo "</div>";
}


function form_search_incident ($return = false) {
	global $config;
	$output = '';

	$search_string = (string) get_parameter ('search_string');
	$status = (int) get_parameter ('search_status', -10);
	$priority = (int) get_parameter ('search_priority', -1);
	$id_group = (int) get_parameter ('search_id_group');
	$id_inventory = (int) get_parameter ('search_id_inventory');
	$id_company = (int) get_parameter ('search_id_company');
	$id_product = (int) get_parameter ('search_id_product');
	$search_serial_number = (string) get_parameter ('search_serial_number');
	$search_id_building = (int) get_parameter ('search_id_building');
	$search_sla_fired = (bool) get_parameter ('search_sla_fired');
	$search_id_user = (string) get_parameter ('search_id_user');
	$search_id_incident_type = (int) get_parameter ('search_id_incident_type');
	
	/* No action is set, so the form will be sent to the current page */
	$table->width = "100%";
	$table->class = "databox_color";
	$table->cellspacing = 2;
	$table->cellpadding = 2;
	$table->data = array ();
	$table->size = array ();
	$table->style = array ();
	$table->style[0] = 'width: 30%';
	$table->style[1] = 'width: 20%';
	$table->style[2] = 'width: 30%';
	$table->rowstyle = array ();
	$table->rowstyle[1] = 'display: none';
	$table->rowstyle[2] = 'display: none';
	$table->rowstyle[3] = 'display: none';
	$table->rowstyle[4] = 'display: none';
	$table->rowstyle[5] = 'text-align: right';
	$table->colspan = array ();
	$table->colspan[5][0] = 3;
	
	$table->data[0][0] = print_input_text ('search_string', $search_string,
		'', 30, 100, true, __('Search string'));
	
	$available_status = get_indicent_status();
	$available_status[-10] = __("Not closed");
	
	$table->data[0][1] = print_select ($available_status,
			'search_status', $status,
			'', __('Any'), 0, true, false, true,
			__('Status'));
	
	$table->data[1][0] = print_select (get_priorities (),
			'search_priority', $priority,
			'', __('Any'), -1, true, false, false,
			__('Priority'));

	$table->data[0][2] = print_select (get_user_groups (),
			'search_id_group', $id_group,
			'', __('All'), 1, true, false, false, __('Group'));
	
	$table->data[1][1] = print_input_hidden ('search_id_inventory', $id_inventory, true);
	$name = __('Any');
	if ($id_inventory)
		$name = get_inventory_name ($id_inventory);
	$table->data[1][1] .= print_button ($name, 'inventory_name', false, '',
		'class="dialogbtn"', true, __('Inventory'));
	
	$table->data[1][2] = print_select (get_products (),
		'search_id_product', $id_product,
		'', __('All'), 0, true, false, false,
		__('Product type'));

	$table->data[2][0] = print_input_text ('search_serial_number', $search_serial_number,
				'', 30, 100, true, __('Serial number'));
	$table->data[2][1] = print_select (get_buildings (),
		'search_id_building', $search_id_building,
		'', __('All'), 0, true, false, false,
		__('Building'));
	$table->data[2][2] = print_checkbox ('search_sla_fired', 1, $search_sla_fired, true, __('SLA fired'));
	
	$table->data[3][0] = print_select (get_user_visible_users ($config['id_user'], 'IR', true),
		'search_id_user', $search_id_user,
		'', __('Any'), 0, true, false, false, __('User'));
	$table->data[3][1] = print_input_text ('search_first_date', '', '', 15, 15, true, __('Begin date'));
	$table->data[3][2] = print_input_text ('search_last_date', '', '', 15, 15, true, __('End date'));
	
	if (!get_external_user ($config["id_user"]))
		$table->data[4][0] = print_select (get_companies (), 'search_id_company',
			$id_company, '', __('All'), 0, true, false, false, __('Company'));
			
	$table->data[4][1] = print_select (get_incident_types (), 'search_id_incident_type',
		$id_company, '', __('All'), 0, true, false, false, __('Incident type'));
	
	$table->data[5][0] = print_submit_button (__('Search'), 'search', false, 'class="sub search"', true);
	
	$output .= '<form id="search_incident_form" method="post">';
	$output .= print_table ($table, true);
	$output .= '</form>';
	
	$output .= '<a class="show_advanced_search" href="index.php">'.__('Advanced search').' >></a>';
	
	if ($return)
		return $output;
	echo $output;
}

function incident_users_list ($id_incident, $return = false) {
	$output = '';
	
	$users = get_incident_users ($id_incident);
	
	$output .= '<ul id="incident-users-list" class="sidemenu">';

	$output .= "&nbsp;&nbsp;".print_user_avatar ($users['owner']['id_usuario'], true, true);
	$output .= ' <strong>'.$users['owner']['id_usuario'].'</strong> (<em>'.__('Responsible').'</em>)';
	
	if ($users['owner']['id_usuario'] != $users['creator']['id_usuario']) {
		$output .= "<br />&nbsp;&nbsp;".print_user_avatar ($users['creator']['id_usuario'], true, true);
		$output .= ' <strong>'.$users['creator']['id_usuario'].'</strong> (<em>'.__('Creator').'</em>)<br />';
	} else {
		$output .= ' (<em>'.__('Creator').'</em>)<br>';
	}
	
	if ($users['affected'])
	foreach ($users['affected'] as $user) {
		if (!get_external_user($user)){
			$output .= "&nbsp;&nbsp;" . print_user_avatar ($user, true, true);
			$output .= ' <strong>'.$user.'</strong> (<em>'.__('Participant').'</em>)';
			$output .= "<br>";
		} 
	}
	$output .= '</ul>';
	
	if ($return)
		return $output;
	echo $output;
}

function incident_details_list ($id_incident, $return = false) {
	$output = '';
	
	$incident = get_incident ($id_incident);
	
	$output .= '<ul id="incident-details-list" class="sidemenu">';
	$output .= '&nbsp;&nbsp;<strong>'.__('Opened').'</strong>: '.human_time_comparation($incident['inicio']);
	
	if ($incident['estado'] == 6 || $incident['estado'] == 7) {
		$output .= '<br />&nbsp;&nbsp;<strong>'.__('Closed at').'</strong>: '.human_time_comparation($incident['cierre']);
	}
	if ($incident['actualizacion'] != $incident['inicio']) {
		$output .= '<br />&nbsp;&nbsp;<strong>'.__('Last update').'</strong>: '.human_time_comparation($incident['actualizacion']);
	}
	
	/* Show workunits if there are some */
	$workunit_count = get_incident_count_workunits ($id_incident);
	if ($workunit_count) {
		$work_hours = get_incident_workunit_hours ($id_incident);
		$workunits = get_incident_workunits ($id_incident);	
		$workunit_data = get_workunit_data ($workunits[0]['id_workunit']);
		$output .= '<br />&nbsp;&nbsp;<strong>'.__('Last work at').'</strong>: '.human_time_comparation ($workunit_data['timestamp']);
		$output .= '<br />&nbsp;&nbsp;<strong>'.__('Workunits').'</strong>: '.$workunit_count;
		$output .= '<br />&nbsp;&nbsp;<strong>'.__('Time used').'</strong>: '.$work_hours;
		;
		$output .= '<br />&nbsp;&nbsp;<strong>'._('Done by').'</strong>: <em>'.$workunit_data['id_user'].'</em>';
	}
	
	$output .= '</ul>';
	
	if ($return)
		return $output;
	echo $output;
}


function print_table_pager ($id = 'pager', $hidden = true, $return = false) {
	global $config;
	
	$output = '';
	
	$output .= '<div id="'.$id.'" class="'.($hidden ? 'hide ' : '').'pager">';
	$output .= '<form>';
	$output .= '<img src="images/control_start_blue.png" class="first" />';
	$output .= '<img src="images/control_rewind_blue.png" class="prev" /> ';
	$output .= '<input type="text" size=3 class="pagedisplay" />';
	$output .= '<img src="images/control_fastforward_blue.png" class="next" />';
	$output .= '<img src="images/control_end_blue.png" class="last" />';
	$output .= '&nbsp;&nbsp;'. __("Items per page"). '&nbsp;';
	if (defined ('AJAX')) {
		$output .= '<select class="pagesize" style="display: none">';
		$output .= '<option selected="selected" value="10">10</option>';
	} else {
		$output .= '<select class="pagesize">';
		// The id of the following <option> is to recover from ajax the block size
		$output .= '<option id="block_size" selected="selected" value="'.$config['block_size'].'">'.$config['block_size'].'</option>';
		$output .= '<option value="'.($config['block_size'] * 2).'">'.($config['block_size'] * 2).'</option>';
		$output .= '<option value="'.($config['block_size'] * 3).'">'.($config['block_size'] * 3).'</option>';
		$output .= '<option value="'.($config['block_size'] * 5).'">'.($config['block_size'] * 5).'</option>';
		$output .= '<option value="'.($config['block_size'] * 10).'">'.($config['block_size'] * 10).'</option>';
		$output .= '</select>';
	}
	$output .= '</select>';
	$output .= '</form>';
	$output .= '</div>';
	
	if ($return)
		return $output;
	echo $output;
}


// Returns a combo with download categories
// ----------------------------------------------------------------------
function combo_download_categories ($id_category, $show_any = 0){
	global $config;

	echo "<select name='id_category' style='width: 180px;'>";
	if ($show_any == 1){
		if($id_category == 0) {
			$selected = 'selected';
		}
		else {
			$selected = '';
		}
		echo "<option value='0' selected='$selected'>".__("Any");
	}	

	// Fix for group All
	if ($id_category == 0)
		$id_category = 1;
		
	$sql = "SELECT * FROM tdownload_category ORDER by name";
	$result = process_sql($sql);
	if($result == false) {
		$result = array();
	}

	$debug = "";
	foreach ($result as $row){
		if (give_acl($config["id_user"], $row["id_group"], "KR")){
			if($row["id"] == $id_category) {
				$selected = 'selected';
			}
			else {
				$selected = '';
			}
			echo "<option value='".$row["id"]."' selected='$selected'>".$row["name"];
		}
	}
	echo "</select>";
}


?>
