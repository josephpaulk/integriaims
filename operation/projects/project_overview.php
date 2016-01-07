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
global $REMOTE_ADDR;

check_login ();

// ACL
$section_access = get_project_access ($config["id_user"]);
if (! $section_access["read"]) {
	// Doesn't have access to this page
	audit_db($id_user, $config["REMOTE_ADDR"], "ACL Violation","Trying to access to project overview without permission");
	no_permission();
}

include_once ("include/functions_projects.php");
include_once ("include/functions_tasks.php");

$section_permission = get_project_access ($config['id_user']);

if (!$section_permission['read']) {
	audit_db ($config['id_user'], $REMOTE_ADDR, "ACL Violation", "Trying to access project detail");
	require ("general/noaccess.php");
	exit;
}

include_once ("include/functions_graph.php");

$id_project = (int) get_parameter ('id');
$delete_project = (bool) get_parameter ('delete_project');
$view_disabled = (int) get_parameter ('view_disabled');
$disable_project = (bool) get_parameter ('disable_project');
$delete_project = (bool) get_parameter ('delete_project');
$activate_project = (bool) get_parameter ('activate_project');
$action = (string) get_parameter ('action');
$search_id_project_group = (int) get_parameter ('search_id_project_group');
$search_text = (string) get_parameter ('search_text');


$project_permission = get_project_access ($config['id_user'], $id_project);

// Disable project
// ======================
if ($disable_project) {
	
	if (!$project_permission['manage']) {
		audit_db ($config['id_user'], $REMOTE_ADDR, "ACL Forbidden", "User ".$config['id_user']." try to disable project #$id_project");
		require ("general/noaccess.php");
		exit;
	}
	
	$id_owner = get_db_value ('id_owner', 'tproject', 'id', $id_project);
	$sql = sprintf ('UPDATE tproject SET disabled = 1 WHERE id = %d', $id_project);
	process_sql ($sql);
	echo '<h3 class="suc">'.__('Project successfully disabled').'</h3>';
	audit_db ($config['id_user'], $REMOTE_ADDR, "Project disabled", "User ".$config['id_user']." disabled project #".$id_project);
	project_tracking ($id_project, PROJECT_DISABLED);
}

// INSERT PROJECT
if ($action == 'insert') {
	
	if (!$project_permission['write']) {
		audit_db ($config['id_user'], $REMOTE_ADDR, "ACL Forbidden", "User ".$id_user. " try to create project");
		return;
	}
	
	// Read input variables
	$id_owner = get_parameter ("id_owner", "");
	$name = (string) get_parameter ("name");
	$description = (string) get_parameter ('description');
	$start_date = (string) get_parameter ('start_date');
	$end_date = (string) get_parameter ('end_date');
	$id_project_group = (int) get_parameter ('id_project_group');
	$cc = get_parameter("cc", "");
	
	$error_msg = "";
	
	if($id_owner == "") {
		$id_owner = $config['id_user'];
		$owner_exists = true;
	}
	else {
		$owner_exists = get_user($id_owner);
	}
	if($owner_exists === false) {
		$error_msg  = '<h3 class="error">'.__('Project manager user does not exist').'</h3>';
		$id_project = false;
	}
	else {
		$sql = sprintf ('INSERT INTO tproject
			(name, description, start, end, id_owner, id_project_group, cc)
			VALUES ("%s", "%s", "%s", "%s", "%s", %d, "%s")',
			$name, $description, $start_date, $end_date, $id_owner,
			$id_project_group, $cc);
		$id_project = process_sql ($sql, 'insert_id');
	}
	
	if ($id_project === false) {
		echo '<h3 class="error">'.__('Project cannot be created, problem found.').'</h3>'.$error_msg;
	} else {
		echo '<h3 class="suc">'.__('Successfully created').' #'.$id_project.'</h3>';
		audit_db ($id_owner, $REMOTE_ADDR, "Project created", "User ".$config['id_user']." created project '$name'");
		
		project_tracking ($id_project, PROJECT_CREATED);
		
		// Add this user as profile 1 (project manager) automatically
		$sql = sprintf ('INSERT INTO trole_people_project
			(id_project, id_user, id_role)
			VALUES ("%s", "%s", 1)',
			$id_project, $id_owner, 1);
		process_sql ($sql);		
		// If current user is different than owner, add also current user
		if ($config['id_user'] != $id_owner) {
			$sql = sprintf ('INSERT INTO trole_people_project
				(id_project, id_user, id_role)
				VALUES (%d, "%s", 1)',
				$id_project, $config['id_user']);
			process_sql ($sql);
		}
	}
}
echo '<h2>'.__('Projects').'</h2>';
echo '<h4>'.__('Projects overview').'</h4>';

$table = new stdClass;
$table->width = '20%';
$table->class = 'search-table';
$table->style = array ();
$table->data = array ();
$table->data[0][0] = __('Search');
$table->data[1][0] = print_input_text ("search_text", $search_text, "", 25, 100, true);
$table->data[2][0] = __('Group');
$table->data[3][0] = print_select_from_sql ("SELECT * FROM tproject_group", "search_id_project_group", $search_id_project_group, '', __("Any"), '0', true, false, true, false);
$table->data[4][0] = print_submit_button (__('Search'), "search_btn", false, '', true);

echo '<div class="divform">';
	echo '<form method="post">';
	print_table ($table);
	echo '</form>';
echo '</div>';

if ($search_id_project_group != 0) {
	$where_clause = sprintf (" where tproject_group.id=$search_id_project_group ");
}

$project_groups = process_sql("SELECT * FROM tproject_group".$where_clause." ORDER by name"); 

if($project_groups === false) {
	$project_groups = array();
}

if($where_clause == ""){
	$nogroup = array();
	$nogroup["id"] = 0;
	$nogroup["name"] = __('Without group');
	$nogroup["icon"] = '../group.png';
$project_groups[] = $nogroup;
}
echo "<div class='divresult'>";
foreach($project_groups as $group) {
	echo "<table width='99%' class='listing'>";
	// Get projects info
	$where_clause2 = "";
	if ($search_text != "") {
		$where_clause2 .= sprintf (" AND (tproject.name LIKE '%%%s%%' OR tproject.description LIKE '%%%s%%')", $search_text, $search_text);
	}
	$projects = get_db_all_rows_sql ("SELECT * FROM tproject WHERE disabled = 0 AND id_project_group = ".$group["id"].$where_clause2);
	if($projects === false) {
		$projects = array();
	}
	
	//Check project ACLs
	$aux_projects = array();
	foreach ($projects as $p) {
		$project_access = get_project_access ($config["id_user"], $p['id']);
		if ($project_access["read"]) {
			array_push($aux_projects, $p);
		}
	}
	
	//Set filtered projects
	$projects = $aux_projects;
	
	$nprojects = count($projects);
		
	echo "<tr>";
	// Project group name
	echo "<th colspan='7'><img src='images/icons/icono_camara.png' title='Prueba'>";
	echo "<span><b><a href='index.php?sec=projects&sec2=operation/projects/project_overview&search_id_project_group=".$group["id"]."'>".$group["name"]."</a>";
	echo "&nbsp;"."   |   "."&nbsp;".__('Nº Projects').": ".$nprojects."</b></span>";
	echo "<a href='javascript:'><img id='btn_".$group["id"]."' class='btn_tree' src='images/arrow_right.png' style='float:right;'></a>";
	echo "</th>";
	echo "<tr class='prj_".$group["id"]."' style='display:none'>";
		echo "<td class='no_border size_min'></td>";
		echo "<td class='no_border'><b>".__('Name')."</b></td>";
		//~ echo "<td><b>".__('PG')."</b></td>";
		echo "<td class='no_border'><b>".__('Manager')."</b></td>";
		echo "<td class='no_border'><b>".__('Completion')."</b></td>";
		echo "<td class='no_border'><b>".__('Updated')."</b></td>";
		if ($view_disabled == 0) {
			echo "<td class='no_border'><b>".__('Archive')."</b></td>";
		} elseif ($project['disabled'] && $project_permission['manage']) {
			echo "<td class='no_border'><b>".__('Delete/Unarchive')."</b></td>";
		}
		echo "<td class='no_border size_max'></td>";
	echo "</tr>";	

	// Projects inside
	foreach($projects as $project) {
	echo "<tr class='prj_".$group["id"]."' style='display:none;'>";
		echo "<td class='no_border size_min'></td>";
		// Project name
		echo "<td><a href='index.php?sec=projects&sec2=operation/projects/task&id_project=".$project["id"]."'>".$project["name"]."</a></td>";
		/*
		// Project PG
		if ($project['id_project_group']) {
			$icon = get_db_value ('icon', 'tproject_group', 'id', $project['id_project_group']);
			$name = get_db_value ('name', 'tproject_group', 'id', $project['id_project_group']);
			
			echo '<td><a href=index.php?sec=projects&sec2=operation/projects/project&filter_id_project_group='.$project["id_project_group"].'">';
			echo '<img src="images/project_groups_small/'.$icon.'" title="'.$name.'">';
			echo '</a></td>';
		} else {
			echo '<td> </td>';	
		}
		*/
		// Manager
		echo "<td>".$project['id_owner']."</a></td>";
		
		// Completion
		if ($project["start"] == $project["end"]) {
			echo '<td>'.__('Unlimited');
		} else {
			$completion = format_numeric (calculate_project_progress ($project['id']));
			echo "<td>";
			echo progress_bar($completion, 90, 20);
		}
		echo "</td>";
		
		// Last update time
		$sql = sprintf ('SELECT tworkunit.timestamp FROM ttask, tworkunit_task, tworkunit
						 WHERE ttask.id_project = %d AND ttask.id = tworkunit_task.id_task
						 AND tworkunit_task.id_workunit = tworkunit.id
						 ORDER BY tworkunit.timestamp DESC LIMIT 1', $project['id']);
		$timestamp = get_db_sql ($sql);
		if ($timestamp != "")
			echo "<td><span style='font-size: 10px'>".human_time_comparation ($timestamp)."</span></td>";
		else
			echo "<td>".__('Never')."</td>";
		
		// Disable or delete
		$project_permission = get_project_access ($config['id_user'], $project['id']);
	
		if ($project['id'] != -1 && $project_permission['manage']) {
			if ($view_disabled == 0) {
				echo '<td><a href="index.php?sec=projects&sec2=operation/projects/project_overview&disable_project=1&id='.$project['id'].'" 
					onClick="if (!confirm(\''.__('Are you sure?').'\')) return false;"><img src="images/icons/icono_archivar.png" /></a></td>';
			} elseif ($project['disabled'] && $project_permission['manage']) {
				echo '<td><a href="index.php?sec=projects&sec2=operation/projects/project&view_disabled=1&delete_project=1&id='.$project['id'].'"
					onClick="if (!confirm(\''.__('Are you sure?').'\')) return false;">
					<img src="images/cross.png" /></a></td>';
				echo '<a href="index.php?sec=projects&sec2=operation/projects/project&view_disabled=1&activate_project=1&id='.$project['id'].'">
					<img src="images/unarchive.png" /></a></td>';
			}
		}
		echo "<td class='no_border size_max'></td>";
	}
	if($nprojects == 0) {
		echo "<tr class='prj_".$group["id"]."' style='display:none'>";
		// Project name
		echo "<td colspan='7'>";
		echo "&nbsp;".__('empty')."</td>";
		echo "</td>";	
		echo "</tr>";
	}
	echo "</table>";
}
echo "</div>";
?>

<script type="text/javascript">

$('.btn_tree').click(function() {
	id = $(this).attr('id');
	id = id.split('_');
	id = id[1];
	
	if($('.prj_'+id).css('display') == 'none') {
		show_branches(id);
		if($('#nproj_'+id).html() == 0) {
			hidden_branches(id);
		}
	}
	else {
		hidden_branches(id);
	}
	
	function show_branches(id) {
		$('.prj_'+id).fadeIn('slow');
		$('#btn_'+id).attr('src', 'images/arrow_down.png');
	}
	
	function hidden_branches(id) {
		$('.prj_'+id).fadeOut('fast');
		$('#btn_'+id).attr('src', 'images/arrow_right.png');
	}
});

$('.btn_tree').mouseover(function() {
	id = $(this).attr('id');
	id = id.split('_');
	id = id[1];

	if($('.prj_'+id).css('display') == 'none') {
		show_branches_hover(id);
		if($('#nproj_'+id).html() == 0) {
			hidden_branches_hover(id);
		}
	}
	else {
		hidden_branches_hover(id);
	}
	
	function show_branches_hover(id) {
		$('#btn_'+id).hover(function(){
			$(this).attr('src', 'images/icons/mas_nar.png');
			}, function(){
			$(this).attr('src', 'images/arrow_right.png');
		});
	}
	
	function hidden_branches_hover(id) {
		$('#btn_'+id).hover(function(){
			$(this).attr('src', 'images/icons/menos_nar.png');
			}, function(){
			$(this).attr('src', 'images/arrow_down.png');
		});
	}
});
</script>
<script type="text/javascript" src="include/js/jquery.validation.functions.js"></script>
<script type="text/javascript">
	trim_element_on_submit("#text-search_text");
</script>
