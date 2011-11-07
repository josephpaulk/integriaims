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

// Load global vars

global $config;


check_login ();

if (defined ('AJAX')) {

	global $config;

	$search_users = (bool) get_parameter ('search_users');

	if ($search_users) {
		require_once ('include/functions_db.php');
		
		$id_user = $config['id_user'];
		$string = (string) get_parameter ('q'); /* q is what autocomplete plugin gives */
		
		$users = get_user_visible_users ($config['id_user'],"IR", false);
		if ($users === false)
			return;
		
		foreach ($users as $user) {
			if(preg_match('/'.$string.'/', $user['id_usuario']) || preg_match('/'.$string.'/', $user['nombre_real'])) {
				echo $user['id_usuario'] . "|" . $user['nombre_real']  . "\n";
			}
		}
		
		return;
 	}
}

include_once ("include/functions_graph.php");

$create_mode = 0;
$name = "";
$description = "";
$end_date = "";
$start_date = "";
$id_project = -1; // Create mode by default
$result_output = "";
$id_project_group = 0;

$action = (string) get_parameter ('action');
$id_project = (int) get_parameter ('id_project');

$create_project = (bool) get_parameter ('create_project');

if (!$create_project && ! user_belong_project ($config["id_user"], $id_project)) {
	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access project ".$id_project);
	no_permission();
}

// Update project
if ($action == 'update') {
	$id_owner = get_db_value ('id_owner', 'tproject', 'id', $id_project);
	if (! give_acl ($config["id_user"], 0, "PW") && $config["id_user"] != $id_owner) {
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to update an unauthorized Project");
		include ("general/noaccess.php");
		exit;
	}
	$user = get_parameter('id_owner');
	$name = get_parameter ("name");
	$description = get_parameter ('description');
	$start_date = get_parameter ('start_date');
	$end_date = get_parameter ('end_date');
	$id_project_group = get_parameter ("id_project_group");
	$sql = sprintf ('UPDATE tproject SET 
			name = "%s", description = "%s", id_project_group = %d,
			start = "%s", end = "%s", id_owner = "%s"
			WHERE id = %d',
			$name, $description, $id_project_group,
			$start_date, $end_date, $user, $id_project);
	$result = process_sql ($sql);
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "Project updated", "Project $name");
	if ($result !== false) {
		project_tracking ($id_project, PROJECT_UPDATED);
		$result_output = '<h3 class="suc">'.__('Successfully updated').'</h3>';
	} else {
		$result_output = '<h3 class="error">'.__('Could not update project').'</h3>';
	}
}

// Edition / View mode
if ($id_project) {
	$project = get_db_row ('tproject', 'id', $id_project);
	
	$name = $project["name"];
	$description = $project["description"];
	$start_date = $project["start"];
	$end_date = $project["end"];
	$owner = $project["id_owner"];
	$id_project_group = $project["id_project_group"];
} 


// Show result of previous operations
echo $result_output;

// Create project form
if ($create_project) {
	$email_notify = 0;
	$iduser_temp = $_SESSION['id_usuario'];
	$titulo = "";
	$prioridad = 0;
	$id_grupo = 0;
	$grupo = dame_nombre_grupo (1);
	$owner = $config["id_user"];
	$estado = 0;
	$actualizacion = date ("Y/m/d H:i:s");
	$inicio = $actualizacion;
	$id_creator = $iduser_temp;
	$create_mode = 1;
	$id_project_group = 0;
} 

if ($id_project)
	echo '<form method="post">';
else
	echo '<form method="post" action="index.php?sec=projects&sec2=operation/projects/project&action=insert">';
// Main project table

echo "<h2>".__('Project management')." &raquo; ";
if ($create_mode == 0){
	echo get_db_value ("name", "tproject", "id", $id_project);
}


echo "&nbsp;&nbsp;<a href='index.php?sec=projects&sec2=operation/projects/project_detail&id_project=$id_project&clean_output=1&pdf_output=1'><img src='images/page_white_acrobat.png'></A>";

echo "</h2>";

echo '<table width="90%" class="databox" >';

// Name

echo '<tr><td class="datos"><b>'.__('Name').'</b>';
echo '<td colspan=3><input type="text" name="name" size=70 value="'.$name.'">';

// start and end date
echo '<tr><td class="datos2"><b>'.__('Start').'</b>';
echo "<td class='datos2'>";

print_input_text ('start_date', $start_date, '', 10, 20);
echo '<td class="datos2"><b>'.__('End').'</b>';
echo "<td class='datos2'>";
print_input_text ('end_date', $end_date, '', 10, 20);

// Owner

echo '<tr>';
$id_owner = get_db_value ( 'id_owner', 'tproject', 'id', $id_project);
$src_code = print_image('images/group.png', true, false, true);
echo "<td class='datos'>";
echo "<b>".__('Project manager  ')."</b>";
echo "<td class='datos'>";
		
echo print_input_text_extended ('id_owner', $owner, 'text-id_owner', '', 10, 20, false, '',
			array('style' => 'background: url(' . $src_code . ') no-repeat right;'), true, '','')
		. print_help_tip (__("Type at least two characters to search"), true);

echo "<td><b>";
echo __('Project group') . "</b>";
echo "<td>";
echo print_select_from_sql ("SELECT * from tproject_group ORDER BY name",
	"id_project_group", $id_project_group, "", __('None'), '0',
	false, false, true, false);

if ($id_project) {
	echo '<tr><td class="datos"><b>'.__('Current progress').'</b>';
	echo "<td class='datos'>";
	$completion =  format_numeric(calculate_project_progress ($id_project));
	echo progress_bar($completion, 90, 20);


	echo '<tr>';
	echo '<td class="datos2"><b>'.__('Total workunit (hr)').'</b>';
	echo "<td class='datos2'>";
	$total_hr = get_project_workunit_hours ($id_project);
	echo $total_hr;
	echo '<td class="datos2"><b>'.__('Total people involved').'</b>';
	echo "<td class='datos2'>";
	$people_inv = get_db_sql ("SELECT COUNT(DISTINCT id_user) FROM trole_people_task, ttask WHERE ttask.id_project=$id_project AND ttask.id = trole_people_task.id_task;");
	echo $people_inv;

	$pr_hour = get_project_workunit_hours ($id_project, 1);
	$total = project_workunit_cost ($id_project, 1);
        $real = project_workunit_cost ($id_project, 0);

	if ($pr_hour > 0){
		echo '<tr>';
		echo '<td class="datos"><b>'.__('Total payable workunit (hr)').'</b>';
		echo '<td class="datos">';
		echo $pr_hour;

		echo '<td class="datos"><b>'.__('Project profitability').'</b>';
		echo '<td class="datos">';
		if ($real > 0) {
			echo format_numeric(($total/$real)*100);
			echo  " %" ;
		}
	}

	echo '<tr>';
	echo '<td class="datos2"><b>'.__('Project costs').'</b>';
	echo "<td class='datos2'>";
	echo format_numeric($real). " ". $config["currency"];

	echo '<td class="datos"><b>'.__('Proyect length deviation (days)').'</b>';
        echo '<td class="datos">';
        $expected_length = get_db_sql ("SELECT SUM(hours) FROM ttask WHERE id_project = $id_project");
        $deviation = format_numeric(($pr_hour-$expected_length)/$config["hours_perday"]);
        echo $deviation. " ".__('Days');


	echo '<tr>';
	echo '<td class="datos"><b>'.__('Charged cost per hour').'</b>';
	echo '<td class="datos">';
	if (($people_inv > 0) AND ($total_hr >0))
		echo format_numeric ($total/($total_hr/$people_inv)). " ". $config["currency"];
	else
		echo __('N/A');

	echo '<td class="datos2"><b>'.__('Charged to customer').'</b>';
        echo "<td class='datos2'>";
        echo $total." ". $config["currency"];
}

// Description

echo '<tr><td class="datos2" colspan="4"><textarea name="description" style="height: 40px;">';
	echo $description;
echo "</textarea>";

echo "<tr><td colspan=2>";
echo "<b>".__("Task effort ")."</b>";;
echo graph_workunit_project (300, 270, $id_project);

echo "<td colspan=2>";
echo "<b>".__("People effort ") ."</b>";
echo graph_workunit_project_user_single (300, 270, $id_project);

echo "</table>";
echo '<div style="width:100%;" class="button">';

if (give_acl ($config["id_user"], 0, "PM") || give_acl ($config["id_user"], 0, "PW") || $config["id_user"] == $id_owner) {
	if ($id_project) {
		print_input_hidden ('id_project', $id_project);
		print_input_hidden ('action', 'update');
		print_submit_button (__('Update'), 'upd_btn', false, 'class="sub upd"');
	} else {
		print_input_hidden ('action', 'insert');
		print_submit_button (__('Create'), 'create_btn', false, 'class="sub create"');
	}
}
echo '</div>';
echo "</form>";

?>
<script type="text/javascript" src="include/js/jquery.ui.slider.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
<script type="text/javascript" src="include/js/integria_date.js"></script>
<script type="text/javascript" src="include/js/jquery.autocomplete.js"></script>

<script type="text/javascript">

$(document).ready (function () {
	configure_range_dates (null);
	$("textarea").TextAreaResizer ();
	
	$("#text-id_owner").autocomplete ("ajax.php",
		{
			scroll: true,
			minChars: 2,
			extraParams: {
				page: "operation/projects/project_detail",
				search_users: 1,
				id_user: "<?php echo $config['id_user'] ?>"
			},
			formatItem: function (data, i, total) {
				if (total == 0)
					$("#text-id_owner").css ('background-color', '#cc0000');
				else
					$("#text-id_owner").css ('background-color', '');
				if (data == "")
					return false;
				return data[0]+'<br><span class="ac_extra_field"><?php echo __("Real name") ?>: '+data[1]+'</span>';
			},
			delay: 200

		});
});
</script>
