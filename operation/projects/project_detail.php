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
include_once ("include/functions_tasks.php");
include_once ("include/functions_graph.php");

check_login ();

/* include/ajax/users.php
if (defined ('AJAX')) {

	global $config;

	$search_users = (bool) get_parameter ('search_users');

	if ($search_users) {
		require_once ('include/functions_db.php');
		
		$id_user = $config['id_user'];
		$string = (string) get_parameter ('q'); // q is what autocomplete plugin gives
		
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
*/

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


$graph_ttl = 1;

if ($pdf_output) {
	$graph_ttl = 2;
}

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
	echo '<form method="post" id="form-new_project">';
else
	echo '<form method="post" id="form-new_project" action="index.php?sec=projects&sec2=operation/projects/project&action=insert">';
// Main project table

echo "<h2>".__('Project management')." &raquo; ";
if ($create_mode == 0){
	echo get_db_value ("name", "tproject", "id", $id_project);
}

if (!$clean_output) {
	echo "&nbsp;&nbsp;<a href='index.php?sec=projects&sec2=operation/projects/project_detail&id_project=$id_project&clean_output=1&pdf_output=1'><img src='images/page_white_acrobat.png'></A>";
}
echo "</h2>";

echo '<table class="project_overview" border=0>';

// Name

echo "<tr>";
echo "<th colspan=4>".__("Project info")."</th>";
echo "</tr>";

echo '<tr><td class="datos" colspan=3><b>'.__('Name').' </b><br>';
echo '<input type="text" name="name" size=70 value="'.$name.'">';

echo '<td colspan=1 align="center">';
//Only show project progress if there is a project created
if ($id_project) {
	echo '<b>'.__('Current progress').' </b><br>';
	echo '<span style="vertical-align:bottom">';
	$completion =  format_numeric(calculate_project_progress ($id_project));
	echo progress_bar($completion, 90, 20, $graph_ttl);
	echo "</span>";
}
echo "</td>";
echo "</tr>";

// start and end date
echo '<tr><td width="25%"><b>'.__('Start').' </b><br>';
print_input_text ('start_date', $start_date, '', 10, 20);

echo '<td width="25%"><b>'.__('End').' </b><br>';
print_input_text ('end_date', $end_date, '', 10, 20);

$id_owner = get_db_value ( 'id_owner', 'tproject', 'id', $id_project);
$src_code = print_image('images/group.png', true, false, true);
echo '<td width="25%">';
echo "<b>".__('Project manager')." </b><br>";
echo print_input_text_extended ('id_owner', $owner, 'text-id_owner', '', 10, 20, false, '',
			array('style' => 'background: url(' . $src_code . ') no-repeat right;'), true, '','');

echo '<td width="25%"><b>';
echo __('Project group') . "</b><br>";

if (!$clean_output) {
	echo print_select_from_sql ("SELECT * from tproject_group ORDER BY name",
		"id_project_group", $id_project_group, "", __('None'), '0',
		false, false, true, false);
} else {
	echo get_db_value ("name", "tproject_group", "id", $id_project_group);
}

// Description
echo "<tr><td colspan=4><b>".__("Description")."</b><br>";
echo '<textarea name="description" style="height: 40px;">';
	echo $description;
echo "</textarea></td></tr>";


echo "</table>";

if (!$clean_output)  {

	echo '<div style="width:800px;" class="button">';

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

}

if ($id_project) {
	
	echo "<table class='project_overview'>";
	
	echo "<tr>";
	echo "<th width=40%>".__("Labour")."</th>";
	echo "<th width=35%>".__("Budget")."</th>";
	echo "<th width=25%>".__("People involved")."</th>";
	echo "</tr>";
	
	echo "<tr>";
	echo '<td><b>'.__('Total people involved').' </b>';
	$people_inv = get_db_sql ("SELECT COUNT(DISTINCT id_user) FROM trole_people_task, ttask WHERE ttask.id_project=$id_project AND ttask.id = trole_people_task.id_task;");
	echo $people_inv;
	
	echo '<td><b>'.__('Project profitability').' </b>';
	if ($real > 0) {
		echo format_numeric(($total/$real)*100);
		echo  " %" ;
	} else 
		echo __("N/A");
	
	$users_involved = array();
	
	//Get proyect owner
	$users_involved[] = $id_owner;
	
	//Get users with tasks
	$sql = sprintf("SELECT DISTINCT id_user FROM trole_people_task, ttask WHERE ttask.id_project= %d AND ttask.id = trole_people_task.id_task", $id_project);
	
	$users_aux = get_db_all_rows_sql($sql);
	
	foreach ($users_aux as $ua) {
		$users_involved[] = $ua['id_user'];
	}
	
	//Delete duplicated items
	$users_involved = array_unique($users_involved);
	
	echo "<td rowspan=5>";
	echo "<div style='height: 120px; overflow-y: auto; margin-top: 8px;'>";
	$inrow = 4;
	$count = 0;
	foreach ($users_involved as $u) {
		
		if ($count == $inrow) {
			echo "<br>";
			$count = 0;
		}
		
		$avatar = get_db_value ("avatar", "tusuario", "id_usuario", $u);
		if ($avatar != "") {
			echo "<a href='index.php?sec=users&sec2=enterprise/godmode/usuarios/role_user_global&id_user=".$u."'>";
			echo "<img src='images/avatars/".$avatar.".png' width=40 height=40 title='".$u."'/>";
			echo "</a>";
			$count++;
		}
	}
	echo "</div>";
	echo "</td>";
	
	echo "</tr>";	
	
	echo '<tr>';
	echo '<td><b>'.__('Total workunit (hr)').' </b>';
	$total_hr = get_project_workunit_hours ($id_project);
	echo $total_hr . " (".format_numeric ($total_hr/$config["hours_perday"]). " ".__("days"). ")";
	
	$deviation_percent = calculate_project_deviation ($id_project);

	echo '<td><b>'.__('Deviation').' </b>';
	echo $deviation_percent ."%";
	
	echo "</tr>";
	
	echo "<tr>";
	echo '<td><b>'.__('Planned workunit (hr)').' </b>';
	$total_planned = get_planned_project_workunit_hours($id_project);
	echo $total_planned . " (".format_numeric ($total_planned/$config["hours_perday"]). " ". __("days"). ")";
	
	echo '<td><b>'.__('Project costs').' </b>';

	// Costs (client / total)
	$real = project_workunit_cost ($id_project, 0);
	$external = project_cost_invoices ($id_project);
	$total_project_costs = $external + $real;

	echo format_numeric( $total_project_costs) ." ". $config["currency"];
	if ($external > 0)
		echo "<span title='External costs to the project'> ($external)</span>";	
	
	echo "</tr>";
	
	$expected_length = get_db_sql ("SELECT SUM(hours) FROM ttask WHERE id_project = $id_project");
	$pr_hour = get_project_workunit_hours ($id_project, 1);
    $deviation = format_numeric(($pr_hour-$expected_length)/$config["hours_perday"]);
	$total = project_workunit_cost ($id_project, 1);
    $real = project_workunit_cost ($id_project, 0);

	$real = $real + get_incident_project_workunit_cost ($id_project);
	
	echo '<tr>';
	echo '<td><b>'.__('Total payable workunit (hr)').' </b>';
	if ($pr_hour > 0)
			echo $pr_hour;
	else
			echo __("N/A");

	echo '<td><b>'.__('Charged to customer').' </b>';
	echo format_numeric($total) . " ". $config["currency"];
	echo "</tr>";
	
	echo "<tr>";
	
	echo '<td><b>'.__('Proyect length deviation (days)').' </b>';
	echo abs($deviation/8). " ".__('Days');


	echo '<td><b>'.__('Average Cost per Hour').' </b>';
	if ($total_hr > 0)
		$avg_cost_hour = format_numeric ($total_project_costs / $total_hr) . " ". $config["currency"];
	else
		$avg_cost_hour = __("N/A");
		
	echo $avg_cost_hour;
	
	echo "</tr>";
	
	echo "</table>";

	echo "<table class='project_overview'>";
	echo "<tr>";
	echo "<th width='50%'>".__("Workload distribution")."</th>";
	echo "<th width='50%'>".__("Task distribution")."</th>";
	echo "</tr>";
	echo "<tr>";
	echo "<td align='center'>";
	echo graph_workunit_project_user_single (350, 300, $id_project, $graph_ttl);
	echo "</td>";
	echo "<td align='center'>";
	echo graph_workunit_project (350, 300, $id_project, $graph_ttl);
	echo "</td>";
	echo "</tr>";
	echo "</table>";
}

echo "</form>";

?>
<script type="text/javascript" src="include/js/jquery.ui.slider.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
<script type="text/javascript" src="include/js/integria_date.js"></script>
<script type="text/javascript" src="include/js/jquery.autocomplete.js"></script>
<script type="text/javascript" src="include/js/jquery.validate.js"></script>
<script type="text/javascript" src="include/js/jquery.validation.functions.js"></script>
<script type="text/javascript">

$(document).ready (function () {
	configure_range_dates (null);
	$("textarea").TextAreaResizer ();
	
	$("#text-id_owner").autocomplete ("ajax.php",
		{
			scroll: true,
			minChars: 2,
			extraParams: {
				page: "include/ajax/users",
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

// Form validation
trim_element_on_submit('input[name="name"]');
validate_form("#form-new_project");
var rules, messages;
// Rules: input[name="name"]
rules = {
	required: true,
	remote: {
		url: "ajax.php",
        type: "POST",
        data: {
          page: "include/ajax/remote_validations",
          search_project_name: 1,
          project_name: function() { return $('input[name="name"]').val() },
          project_id: <?php echo $id_project?>
        }
	}
};
messages = {
	required: "<?php echo __('Name required')?>",
	remote: "<?php echo __('This project already exists')?>"
};
add_validate_form_element_rules('input[name="name"]', rules, messages);

</script>
