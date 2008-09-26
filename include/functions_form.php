<?PHP

// INTEGRIA IMS v1.2
// http://www.integriaims.com
// ===========================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2008 Artica, info@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License (LGPL)
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

function combo_user_visible_for_me ($id_user, $form_name ="user_form", $any = 0, $access = "IR", $return = false) {
	global $config;
	$userlist = array();
	$output = '';

	$values = get_user_visible_users ($id_user, $access, true);
	if ($any)
		$values[''] = __('Any');

	$output = print_select ($values, $form_name, $id_user, '', '', 0, true, false, false);

	if ($return)
		return $output;
	echo $output;
}

function combo_groups_visible_for_me ($id_user, $form_name ="group_form", $any = 0, $perm = '', $id_group = 0, $return = false) {
	$output = '';

	$values = array ();

	$sql = sprintf ("SELECT COUNT(*) FROM tusuario_perfil
			WHERE id_usuario = '%s' AND id_grupo = 1",
			$id_user);
	$in_any = get_db_sql ($sql);
	if ($in_any) {
		$groups = get_db_all_rows_sql ('SELECT id_grupo, nombre
						FROM tgrupo
						WHERE id_grupo != 1
						ORDER BY nombre');
	} else {
		$values[1] = __('Any');
		$sql = sprintf ('SELECT g.id_grupo, nombre
				FROM tusuario_perfil u, tgrupo g
				WHERE u.id_grupo = g.id_grupo
				AND id_usuario = "%s"
				ORDER BY nombre',
				$id_user);
		$groups = get_db_all_rows_sql ($sql);
	}

	if ($groups === false)
		$groups = array ();
	foreach ($groups as $group) {
		if ($perm != "" && ! give_acl ($id_user, $group['id_grupo'], $perm))
			continue;

		$values[$group['id_grupo']] = $group['nombre'];
	}
	$output .= print_select ($values, $form_name, $id_group, '', '', 0,
				true, false, false, __('Group'));

	if ($return)
		return $output;
	echo $output;
	return;
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


// Returns a combo with the users that belongs to a task
// ----------------------------------------------------------------------
function combo_users_task ($id_task, $iconic = 0){
	// Show only users assigned to this project
	$sql = "SELECT * FROM trole_people_task WHERE id_task = $id_task";
	$result = mysql_query($sql);

	if ($iconic == 0){
		echo "<select name='user' style='width: 100px;'>";
		while ($row=mysql_fetch_array($result)){
			echo "<option value='".$row["id"]."'>".$row["id_user"]." / ".give_db_value ("name","trole","id",$row["id_role"]);
		}
		echo "</select>";
	} else {
		echo "<a href='#' class='tip_people'><span><font size=1>";
		// Show also groupname
		$groupname = give_db_sqlfree_field ("SELECT nombre FROM tgrupo, ttask WHERE ttask.id = $id_task AND ttask.id_group = tgrupo.id_grupo");
		echo lang_string("Group")." <b>$groupname</b><br>";
		while ($row=mysql_fetch_array($result)){
			echo $row["id_user"]." / ".give_db_value ("name","trole","id",$row["id_role"]);
			echo "<br>";
		}
		echo "</font></span></a>";
	}
}

// Returns a combo with the users that belongs to a project
// ----------------------------------------------------------------------
function combo_users_project ($id_project){
	// Show only users assigned to this project
	$sql = "SELECT * FROM trole_people_project WHERE id_project = $id_project ORDER by id_user";
	$result = mysql_query($sql);
	echo "<select name='user' style='width: 100px;'>";
	while ($row=mysql_fetch_array($result)){
		echo "<option value='".$row["id"]."'>".$row["id_user"]." / ".give_db_value ("name","trole","id",$row["id_role"]);
	}
	echo "</select>";
}

// Returns a combo with categories
// ----------------------------------------------------------------------
function combo_kb_categories ($id_category){
	if ($id_category == 0)
		$id_category =1;
	$sql = "SELECT * FROM tkb_category WHERE id != $id_category ORDER by parent, name";
	$result = mysql_query($sql);
	echo "<select name='category' style='width: 180px;'>";
	
	$parent = give_db_value ("parent","tkb_category","id",$id_category);
	$parent_name = give_db_value ("name","tkb_category","id",$parent);
	$name = give_db_value ("name","tkb_category","id",$id_category);
	if ($parent != 0)
		echo "<option value='".$id_category."'>".$parent_name."/".$name;
	else
		echo "<option value='".$id_category."'>".$name;

	while ($row=mysql_fetch_array($result)){
		$parent = give_db_value ("name","tkb_category","id",$row["parent"]);
		if ($parent != "")
			echo "<option value='".$row["id"]."'>".$parent . "/".$row["name"];
		else
			echo "<option value='".$row["id"]."'>".$row["name"];
	}
	echo "</select>";
}


// Returns a combo with products
// ----------------------------------------------------------------------
function combo_kb_products ($id_product, $show_none = 0){

	if ($id_product == 0)
		$id_product = 1;
	$sql = "SELECT * FROM tkb_product WHERE id != $id_product ORDER BY parent, name ";
	$result = mysql_query($sql);
	echo "<select name='product' style='width: 180px;'>";
	if ($id_product > 0){
		$parent = give_db_value ("parent","tkb_product","id",$id_product);
		$parent_name = give_db_value ("name","tkb_product","id",$parent);
		$name = give_db_value ("name","tkb_product","id",$id_product);
		if ($parent != 0)
			echo "<option value='".$id_product."'>".$parent_name."/".$name;
		else
			echo "<option value='".$id_product."'>".$name;
	}
	if ($show_none == 1)
		echo "<option value=0>".lang_string ("none");
	while ($row=mysql_fetch_array($result)){
		$parent = give_db_value ("name","tkb_product","id",$row["parent"]);
		if ($parent != "")
			echo "<option value='".$row["id"]."'>".$parent . "/".$row["name"];
		else
			echo "<option value='".$row["id"]."'>".$row["name"];
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
function combo_incident_status ($actual = -1, $disabled = 0, $actual_only = 0, $return = false) {
	$output = '';

	if ($disabled) {
		$output .= get_db_value ('name', 'tincident_status', 'id', $actual);
		if ($return)
			return $output;
		echo $output;
	}
	if ($actual_only)
		$sql = sprintf ('SELECT id, name FROM tincident_status WHERE id = %d', $actual);
	else
		$sql = 'SELECT id, name FROM tincident_status';

	$output .= print_select_from_sql ($sql, 'incident_status', $actual, '', '', 0, true, false, false, __('Status'));

	if ($return)
		return $output;
	echo $output;
}

// Returns a combo with the incident origin
// ----------------------------------------------------------------------
function combo_incident_origin ($actual = -1, $disabled = 0, $return = false) {
	$output = '';

	if ($disabled) {
		$output .= get_db_value ('name', 'tincident_origin', 'id', $actual);
		if ($return)
			return $output;
		echo $output;
	}

	$output .= print_select_from_sql ('SELECT id,name FROM tincident_origin', 'incident_origin',
					$actual, '', '', 0, true, false, false, __('Source'));
	if ($return)
		return $output;
	echo $output;
}

// Returns a combo with the incident resolution
// ----------------------------------------------------------------------
function combo_incident_resolution ($actual = -1, $disabled = false, $return = false) {
	$output = print_select_from_sql ('SELECT id, name FROM tincident_resolution ORDER BY 2',
					'incident_resolution', $actual, '', '',
					0, true, false, false, __('resolution'));
	if ($return)
		return $output;
	echo $output;
}

// Returns a combo with the tasks that current user could see
// ----------------------------------------------------------------------
function combo_task_user ($actual = 0, $id_user, $disabled = 0, $show_vacations = 0, $return = false) {
	$output = '';

	if ($disabled) {
		$output .= '';
		if ($return)
			return $output;
		echo $output;
		return;
	}

	$values = array ();
	$values[0] = __('N/A');
	if ($show_vacations == 1)
		$values[-1] = __('vacations');

	$sql = sprintf ('SELECT ttask.id, ttask.name
			FROM ttask, trole_people_task
			WHERE ttask.id != %d
			AND ttask.id = trole_people_task.id_task
			AND trole_people_task.id_user = "%s"
			ORDER BY 2',
			$actual, $id_user);
	$tasks = get_db_all_rows_sql ($sql);
	if ($tasks === false)
		$tasks = array ();
	foreach ($tasks as $task) {
		$values[$task['id']] = $task['name'];
	}
	$output = print_select ($values,'task_user', $actual, '', '',
				0, true, false, false, __('Task'));
	if ($return)
		return $output;
	echo $output;
	return;
}

// Returns a combo with the tasks that current user is working on
// ----------------------------------------------------------------------
function combo_task_user_participant ($id_user, $show_vacations = false, $actual = 0, $return = false) {
	$output = '';
	$values = array ();
	
	if ($show_vacations) {
		$values[-1] = __("vacations");
		$values[-2] = __("not_working_by_disease");
		$values[-3] = __("not_justified");
	}
	
	$sql = sprintf ('SELECT DISTINCT (ttask.id), CONCAT(tproject.name," / ",ttask.name)
			FROM ttask, trole_people_task, tproject
			WHERE ttask.id_project = tproject.id
			AND tproject.disabled = 0
			AND ttask.id = trole_people_task.id_task
			AND trole_people_task.id_user = "%s"
			ORDER BY ttask.id_project', $id_user);
	
	$output .= print_select_from_sql ($sql, 'task', $actual, '', __('N/A'), '0', true,
				false, false, false);

	if ($return)
		return $output;
	echo $output;
}

// Returns a combo with the available roles
// ----------------------------------------------------------------------
function combo_roles ($include_na = 0, $name = 'role') {
	global $config;
	global $lang_label;

	echo "<select name='$name'>";
	if ($include_na == 1)
		echo "<option value=0>".__('N/A');
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
	$sql = "SELECT DISTINCT(id_project) FROM trole_people_project WHERE id_user = '$id_user'";
	$result=mysql_query($sql);
	while ($row=mysql_fetch_array($result)){
		$nombre = give_db_sqlfree_field("SELECT name FROM tproject WHERE disabled=0 AND id = ".$row[0]);
		if ($nombre != "")
		echo "<option value='".$row[0]."'>".$nombre;
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
	$public = $row3["public"];
	$locked = $row3["locked"];

	$id_group = get_db_sql ("SELECT i.id_grupo FROM tincidencia as i, tworkunit_incident as w WHERE w.id_workunit = $id_workunit AND i.id_incidencia = w.id_incident");


	// ACL Check for visibility
	if (($public == 0) AND ($id_user != $config["id_user"]) AND (give_acl_extra ($config["id_user"], $id_group, "IM")== 0))
		return;

	// Show data
	echo "<div class='notetitle'>"; // titulo
	echo "<span>";
	print_user_avatar ($id_user, true);
	echo " <a href='index.php?sec=users&sec2=operation/users/user_edit&ver=$id_user'>";
	echo $id_user;
	echo "</a>";
	echo "&nbsp;".__('said_on')."&nbsp;";
	echo $timestamp;
	echo "</span>";

	// Public WU ?
	echo "<span style='float:right; margin-top: -15px; margin-bottom:0px; padding-right:10px;'>";
	if ($public == 1)
		echo "<img src='images/group.png' title='".__("Public Workunit")."' border=0>";
	else
		echo "<img src='images/delete.png' title='".__("Non public Workunit")."' border=0>";
	echo "</span>";

	// WU Duration 
	echo "<span style='float:right; margin-top: -15px; margin-bottom:0px; padding-right:10px;'>";
	echo $duration;
	echo "&nbsp; ".__('hr');
	echo "</span>";

	echo "</div>";

	// Body
	echo "<div class='notebody'>";
	if (strlen($nota) > 1024){
		echo clean_output_breaks(substr($nota,0,1024));
		echo "<br><br>";
		echo "<a href='index.php?sec=incidents&sec2=operation/common/workunit_detail&id=".$id_workunit."&title=$title'>";
		echo __('read_more');
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
	if ($id_task == "")
		$id_incident = give_db_value ("id_incident", "tworkunit_incident", "id_workunit", $row["id"]);
	$id_group = give_db_value ("id_group", "ttask", "id", $id_task);
	$id_project = give_db_value ("id_project", "ttask", "id", $id_task);
	$task_title = substr(give_db_value ("name", "ttask", "id", $id_task), 0, 50);
	if ($id_task == "")
		$incident_title = substr(give_db_value ("titulo", "tincidencia", "id_incidencia", $id_incident), 0, 50);
	$project_title = substr(give_db_value ("name", "tproject", "id", $id_project), 0, 50);
	// Show data
	echo "<div class='notetitle' style='height: 75px;'>"; // titulo
	echo "<table class='blank' border=0 width='100%' cellspacing=0 cellpadding=0 style='margin-left: 0px;margin-top: 0px; background: transparent;'>";
	echo "<tr><td rowspan=3 width='7%'>";
	print_user_avatar ($id_user, true);

	echo "<td width='60%'><b>";
	if ($id_task != ""){
		echo __("task")." </b> : ";
		echo $task_title;
	} else  {
		echo __("incident")." </b> : ";
		echo $incident_title;
	}
	echo "<td width='13%'><b>";
	echo __("duration")."</b>";

	echo "<td width='20%'>";
	echo " : ".format_numeric($duration);


	echo "<tr>";
	echo "<td><b>";
	if ($id_task != ""){
		echo __("project")." </b> : ";
		echo $project_title;
	} else {
		echo __("group")."</b> : ";
		echo dame_nombre_grupo (give_db_sqlfree_field ("SELECT id_grupo FROM tincidencia WHERE id_incidencia = $id_incident"));
	}

	echo "<td><b>";

	if ($have_cost != 0){
		$profile_cost = give_db_value ("cost", "trole", "id", $profile);
		$cost = format_numeric ($duration * $profile_cost);
		$cost = $cost ." &euro;";
	} else
		$cost = __('N/A');
	echo __("cost");
	echo "</b>";
	echo "<td>";
	echo " : ".$cost;


	echo "<tr>";
	echo "<td>";
	echo "<a href='index.php?sec=users&sec2=operation/users/user_edit&ver=$id_user'>";
	echo "<b>".$id_user."</b>";
	echo "</a>";
	echo "&nbsp;".__('said_on')."&nbsp;";
	echo $timestamp;
	echo "<td><b>";
	echo __("profile");
	echo "</b></td><td>";
	echo " : ".give_db_value ("name", "trole", "id", $profile);
	echo "</table>";
	echo "</div>";

	// Body
	echo "<div class='notebody'>";
	echo "<table width='100%'  class='blank'>";
	echo "<tr><td valign='top'>";

	if ((strlen($nota) > 1024) AND ($full == 0)){
		echo topi_richtext ( clean_output_breaks(substr($nota,0,1024)) );
		echo "<br><br>";
		echo "<a href='index.php?sec=users&sec2=operation/users/user_workunit_report&id_workunit=".$id_workunit."&title=$task_title'>";
		echo __('read_more');
		echo "</a>";
	} else {
		echo topi_richtext(clean_output_breaks($nota));
	}
	echo "<td valign='top'>";
	echo "<table width='100%'  class='blank'>";


	if ($id_project > 0)
		$myurl = "index.php?sec=projects&sec2=operation/projects/task_workunit&id_project=$id_project&id_task=$id_task";
	else
		$myurl = "index.php?sec=users&sec2=operation/users/user_workunit_report&id=$id_user";

	if ((project_manager_check($id_project) == 1) OR ($id_user == $config["id_user"]) OR  (give_acl($config["id_user"], $id_group, "TM")) ) {
		echo "<tr><td align='right'>";
		echo "<br>";
		echo "<a href='$myurl&id_workunit=$id_workunit&operation=delete'><img src='images/cross.png' border='0'></a>";
	}

	// Edit workunit
	if ((project_manager_check($id_project) == 1) OR (give_acl($config["id_user"], $id_group, "TM")) OR (($id_user == $config["id_user"]) AND ($locked == 0)) ) {
		echo "<tr><td align='right'>";
		echo "<br>";
		echo "<a href='index.php?sec=projects&sec2=operation/projects/task_create_work&id_project=$id_project&id_task=$id_task&id_workunit=$id_workunit&operation=edit'><img border=0 src='images/page_white_text.png' title='".__("Lock workunit")."'></a>";
		echo "</td>";
	}

// Lock workunit
	if (((project_manager_check($id_project) == 1) OR (give_acl($config["id_user"], $id_group, "TM")) OR ($id_user == $config["id_user"])) AND ($locked == "") ) {
		echo "<tr><td align='right'>";
		echo "<br>";
		echo "<a href='$myurl&id_workunit=$id_workunit&operation=lock'><img border=0 src='images/lock.png' title='".__("Lock workunit")."'></a>";
		echo "</td>";
	} else {
		echo "<tr><td align='right'>";
		echo "<br><img src='images/rosette.png' title='".__("Locked by")." $locked'";
		echo print_user_avatar ($locked, true);
		echo "</td>";
	}

  	echo "</tr></table>";
	echo "</tr></table>";
	echo "</div>";
}


function form_search_incident ($return = false) {
	$output = '';

	$search_string = (string) get_parameter ('search_string');
	$status = (int) get_parameter ('search_status');
	$priority = (int) get_parameter ('search_priority', -1);
	$id_group = (int) get_parameter ('search_id_group');
	$id_inventory = (int) get_parameter ('search_id_inventory');
	$id_company = (int) get_parameter ('search_id_company');
	$id_product = (int) get_parameter ('search_id_product');
	$search_serial_number = (string) get_parameter ('search_serial_number');
	$search_id_building = (int) get_parameter ('search_id_building');
	$search_sla_fired = (bool) get_parameter ('search_sla_fired');
	$search_ticket = (int) get_parameter ('search_ticket',0);

	/* No action is set, so the form will be sent to the current page */
	$table->width = "100%";
	$table->class = "databox_color";
	$table->cellspacing = 2;
	$table->cellpadding = 2;
	$table->data = array ();
	$table->size = array ();
	$table->style = array ();
	$table->colspan = array ();

	$table->style[0] = 'font-weight: bold';
	$table->style[1] = 'font-weight: bold';
	$table->style[2] = 'font-weight: bold';
	//$table->colspan[3][0] = 2;

	$table->data[0][0] = print_select (get_indicent_status (),
			'search_status', $status,
			'', __('Any'), 0, true, false, false,
			__('Status'));
	
	$table->data[0][1] = print_select (get_indicent_priorities (),
			'search_priority', $priority,
			'', __('Any'), -1, true, false, false,
			__('Priority'));

	$table->data[0][2] = print_select (get_user_groups (),
			'search_id_group', $id_group,
			'', '', '', true, false, false, __('Group'));
	
	$table->data[1][0] = print_input_hidden ('search_id_inventory', $id_inventory, true);
	$name = __("Any");
	if ($id_inventory)
		$name = get_inventory_name ($id_inventory);
	$table->data[1][0] .= print_button ($name, 'inventory_name', false, '',
			'class="dialogbtn"', true, __('Inventory'));
	
	$table->data[1][1] = print_select (get_companies (),
			'search_id_company', $id_company,
			'', __('All'), 0, true, false, false,
			__('Company'));
	
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
	
	$table->data[3][1] = print_input_text ('search_string', $search_string,
			'', 30, 100, true, __('Search string'));
	
	$table->data[3][0] = print_input_text ('search_ticket', $search_ticket,
			'', 4, 10, true, __('Ticket ID#'));

	$table->data[3][2] = print_submit_button (__('Search'), 'search', false, 'class="sub search"', true);
	

	$output .= '<form id="search_incident_form" method="post">';
	$output .= print_table ($table, true);
	$output .= '</form>';

	if ($return)
		return $output;
	echo $output;
}

function incident_users_list ($id_incident, $return = false) {
	$output = '';
	
	$incident = get_db_row ('tincidencia', 'id_incidencia', (int) $id_incident);
	$creator = get_db_row ('tusuario', 'id_usuario', $incident['id_creator']);
	$assigned = get_db_row ('tusuario', 'id_usuario', $incident['id_usuario']);
	
	$output .= '<ul id="incident-users-list" class="sidemenu">';

	$output .= "&nbsp;&nbsp;" . print_user_avatar ($incident['id_usuario'], true, true);
	$output .= ' <strong>'.$incident['id_usuario'].'</strong> (<em>'.__('Responsible').'</em>)<br>';
		
	$output .= "&nbsp;&nbsp;" .print_user_avatar ($incident['id_creator'], true, true);
	$output .= ' <strong>'.$incident['id_creator'].'</strong> (<em>'.__('Creator').'</em>)<br>';
	
	$users = get_users_in_group ($incident['id_grupo'], false);
	foreach ($users as $user) {
		$output .= "&nbsp;&nbsp;" . print_user_avatar ($user['id_usuario'], true, true);
		$output .= ' <strong>'.$user['id_usuario'].'</strong> ('.__('Participant').')<br>';
	}
	
	$output .= '</ul>';
	
	if ($return)
		return $output;
	echo $output;
}

?>
