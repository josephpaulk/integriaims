<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2011 Ártica Soluciones Tecnológicas
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

require_once ('include/functions_tasks.php');
require_once ('include/functions_workunits.php');
require_once ('include/functions_user.php');

if (defined ('AJAX')) {

	global $config;

	$get_task_roles = (bool) get_parameter ('get_task_roles');
	

 	// Get the roles assigned to user in the project of a given task
	if ($get_task_roles) {
		$id_user = get_parameter ('id_user');
		$id_task = get_parameter ('id_task');

		$id_project = get_db_value('id_project','ttask','id',$id_task);
		
		// If the user is Project Manager, all the roles are retrieved. 
		// If not, only the assigned roles
		
		if(give_acl($id_user, 0, "PM")) {
			$roles = get_db_all_rows_filter('trole',array(),'id, name');
		}
		else {
			$roles = get_db_all_rows_sql('SELECT trole.id, trole.name FROM trole, trole_people_project WHERE id_role = trole.id AND id_user = "'.$id_user.'" AND id_project = '.$id_project);
		}	

		echo json_encode($roles);

		return;
	}
	
	if (get_parameter("get_new_mult_wu")) {
		$number = get_parameter ("next");
		create_new_table_multiworkunit($number);	
		
		return;
	}
}

$operation = (string) get_parameter ("operation");
$now = (string) get_parameter ("givendate", date ("Y-m-d H:i:s"));
$public = (bool) get_parameter ("public", 1);
$id_project = (int) get_parameter ("id_project");
$id_workunit = (int) get_parameter ('id_workunit');
$id_task = (int) get_parameter ("id_task",0);
$id_incident = (int) get_parameter ("id_incident", 0);

if ($id_task == 0){
    // Try to get id_task from tworkunit_task
    $id_task = get_db_sql ("SELECT id_task FROM tworkunit_task WHERE id_workunit = $id_workunit");
}

// If id_task is set, ignore id_project and get it from the task
if ($id_task) {
	$id_project = get_db_value ('id_project', 'ttask', 'id', $id_task);
}

if ($id_incident == 0){
	$id_incident = get_db_value ('id_incident', 'tworkunit_incident', 'id_workunit', $id_workunit);
}

if ($id_task >0){ // Skip vacations, holidays etc

	if (! user_belong_task ($config["id_user"], $id_task) && !give_acl($config["id_user"], 0, "UM") ){


		// Doesn't have access to this page
		audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to task workunit form without permission");
		no_permission();
	}
}

// Lock Workunit
if ($operation == "lock") {
	$success = lock_task_workunit ($id_workunit);
	if (! $success) {
		audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation",
			"Trying to lock WU $id_workunit without rigths");
		if (!defined ('AJAX'))
			include ("general/noaccess.php");
		return;
	}
	
	$result_output = '<h3 class="suc">'.__('Locked successfully').'</h3>';
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "Work unit locked",
		"Workunit for ".$config['id_user']);
	
	if (defined ('AJAX')) {
		echo '<img src="images/rosette.png" title="'.__('Locked by').' '.$config['id_user'].'" />';
		print_user_avatar ($config['id_user'], true);
		return;
	}
}

if ($id_workunit) {
	$sql = sprintf ('SELECT *
		FROM tworkunit
		WHERE tworkunit.id = %d', $id_workunit);
	$workunit = get_db_row_sql ($sql);

	if ($workunit === false) {
		require ("general/noaccess.php");
		return;
	}
	
//	$id_task = $workunit['id_task'];
//	$id_project = get_db_value ('id_project', 'ttask', 'id', $id_task);

	$id_user = $workunit['id_user'];
	$wu_user = $id_user;
	$duration = $workunit['duration']; 
	$description = $workunit['description'];
	$have_cost = $workunit['have_cost'];
	$id_profile = $workunit['id_profile'];
	$now = $workunit['timestamp'];
	$public = (bool) $workunit['public'];
	$now_date = substr ($now, 0, 10);
	$now_time = substr ($now, 10, 8);
	
	if ($id_user != $config["id_user"] && ! project_manager_check ($id_project) ) {
		if (!give_acl($config["id_user"], 0, "UM")){
			audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation",
			"Trying to access non owned workunit");
			require ("general/noaccess.php");
			return;
		}
	}
} else {
	$id_user = $config["id_user"];
	$wu_user = $id_user;
	$duration = $config["pwu_defaultime"]; 
	$description = "";
	$id_inventory = array();
	$have_cost = false;
	$public = true;
	$id_profile = "";
	// $now is passed as parameter or get current time by default
}

// Insert workunit
if ($operation == 'insert') {
	$duration = (float) get_parameter ("duration");
	$timestamp = (string) get_parameter ("start_date");
	$description = (string) get_parameter ("description");
	$have_cost = (bool) get_parameter ("have_cost");
	$id_task = (int) get_parameter ("id_task", -1);
	$id_profile = (int) get_parameter ("id_profile");
	$public = (bool) get_parameter ("public");
	$split = (bool) get_parameter ("split");
	$id_user = (string) get_parameter ("id_username", $config['id_user']);
	$wu_user = $id_user;
	
	// Multi-day assigment
	if ($split && $duration > $config["hours_perday"]) {
		$forward = (bool) get_parameter ("forward");
		$total_days = ceil ($duration / $config["hours_perday"]);
		$total_days_sum = 0;
		$hours_day = 0;
		for ($i = 0; $i < $total_days; $i++) {
			if (! $forward)
				$current_timestamp = calcdate_business_prev ($timestamp, $i);
			else
				$current_timestamp = calcdate_business ($timestamp, $i);
			
			if (($total_days_sum + 8) > $duration)
				$hours_day = $duration - $total_days_sum;
			else 
				$hours_day = $config["hours_perday"];
			$total_days_sum += $hours_day;
			
			$sql = sprintf ('INSERT INTO tworkunit 
				(timestamp, duration, id_user, description, have_cost, id_profile, public) 
				VALUES ("%s", %f, "%s", "%s", %d, %d, %d)',
				$current_timestamp, $hours_day, $id_user, $description,
				$have_cost, $id_profile, $public);
			$id_workunit = process_sql ($sql, 'insert_id');
			if ($id_workunit !== false) {
				$sql = sprintf ('INSERT INTO tworkunit_task 
					(id_task, id_workunit) VALUES (%d, %d)',
					$id_task, $id_workunit);
				$result = process_sql ($sql, 'insert_id');
				if ($result !== false) {
					$result_output = '<h3 class="suc">'.__('Workunit added').'</h3>';
				} else {
					$result_output = '<h3 class="error">'.__('Problem adding workunit.').'</h3>';
				}
			}
			else {
				$result_output = '<h3 class="error">'.__('Problem adding workunit.').'</h3>';
			}
		}
		mail_project (0, $config['id_user'], $id_workunit, $id_task,
			"This is part of a multi-workunit assigment of $duration hours");
	} else {
		// Single day workunit
		$sql = sprintf ('INSERT INTO tworkunit 
				(timestamp, duration, id_user, description, have_cost, id_profile, public) 
				VALUES ("%s", %.2f, "%s", "%s", %d, %d, %d)',
				$timestamp, $duration, $id_user, $description,
				$have_cost, $id_profile, $public);
		$id_workunit = process_sql ($sql, 'insert_id');
		if ($id_workunit !== false) {
			$sql = sprintf ('INSERT INTO tworkunit_task 
					(id_task, id_workunit) VALUES (%d, %d)',
					$id_task, $id_workunit);
			$result = process_sql ($sql, 'insert_id');
			if ($result !== false) {
				$result_output = '<h3 class="suc">'.__('Workunit added').'</h3>';
				audit_db ($config['id_user'], $config["REMOTE_ADDR"], "Spare work unit added", 
						'Workunit for '.$config['id_user'].' added to Task ID #'.$id_task);
				mail_project (0, $config['id_user'], $id_workunit, $id_task);
			}
			else {
				$result_output = '<h3 class="error">'.__('Problemd adding workunit.').'</h3>';
			}
		} else {
			$result_output = '<h3 class="error">'.__('Problemd adding workunit.').'</h3>';
		}
	}
	
	if ($id_workunit !== false) {
		set_task_completion ($id_task);
	}
	
	insert_event ("PWU INSERT", $id_task, 0, $description);
}

if ($operation == "delete") {
	$success = delete_task_workunit ($id_workunit);
	if (! $success) {
		audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation",
			"Trying to delete WU $id_workunit without rigths");
		include ("general/noaccess.php");
		return;
	}
	
	$result_output = '<h3 class="suc">'.__('Successfully deleted').'</h3>';
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "Work unit deleted", "Workunit for ".$config['id_user']);
	
	if (defined ('AJAX'))
		return;
}

// Edit workunit
if ($operation == 'update') {
	$duration = (float) get_parameter ("duration");
	$timestamp = (string) get_parameter ("start_date");
	$start_date = $timestamp;
	
	$description = (string) get_parameter ("description");
	$have_cost = (bool) get_parameter ("have_cost");
	$id_profile = (int) get_parameter ("id_profile");
	$public = (bool) get_parameter ("public");
	$wu_user = (string) get_parameter ("id_username", $config['id_user']);
	$id_task = (int) get_parameter ("id_task",0);
	
	// UPDATE WORKUNIT
	$sql = sprintf ('UPDATE tworkunit
		SET timestamp = "%s", duration = %.2f, description = "%s",
		have_cost = %d, id_profile = %d, public = %d, id_user = "%s" 
		WHERE id = %d',
		$timestamp, $duration, $description, $have_cost,
		$id_profile, $public, $wu_user, $id_workunit);
	$result = process_sql ($sql);

	if ($id_task !=0) {
	    // Old old association
	    process_sql ("DELETE FROM tworkunit_task WHERE id_workunit = $id_workunit");
	    // Create new one
            $sql = sprintf ('INSERT INTO tworkunit_task
                            (id_task, id_workunit) VALUES (%d, %d)',
                                        $id_task, $id_workunit);
            $result = process_sql ($sql, 'insert_id');
	}
	$result_output = '<h3 class="suc">'.__('Workunit updated').'</h3>';
	insert_event ("PWU UPDATED", 0, 0, $description);
	
	if ($result !== false) {
		set_task_completion ($id_task);
	}
}

$multiple_wu_report = array();

if ($operation == 'multiple_wu_insert') {
	
	//Walk post array looking for 
	$i = 1;
	while(true) {
		
		if (!get_parameter("start_date_".$i)) {
			break;
		} 
		
		//Add single workunit
		$res = create_single_workunit($i);
		
		$res['id'] = $i;
		
		array_push($multiple_wu_report, $res);
		
		//Look next item
		$i++;
	}
}



echo "<div id='tabs'>";
echo "<ul class='ui-tabs-nav'>";

//If the multiple_wu_insert option was sent single wu is disabled
if ($operation == 'multiple_wu_insert') {
	echo "<li id='tabmenu1' class='ui-tabs-disabled'>";
} else {
	echo "<li id='tabmenu1' class='ui-tabs-selected'>";
}
echo "<a href='#tab1'><span>".__("Single WU")."</span></a>";
echo "</span></li>";

//If single workunit update multiple addition is disabled
if ($id_workunit) {
	echo "<li id='tabmenu2' class='ui-tabs-disabled'><span>";
} else {
	
	//If the multiple_wu_insert option was sent this tab is selected
	if ($operation == 'multiple_wu_insert') {
		echo "<li id='tabmenu2' class='ui-tabs-selected'><span>";
	} else {
		echo "<li id='tabmenu2' class='ui-tabs'><span>";
	}
}
	echo "<a href='#tab2'><span>".__("Multiple WU")."</span></a>";
	echo "</span></li>";
echo "</ul>";


//If we inserted multiple workunits then 
if ($operation == 'multiple_wu_insert') {

	echo "<div id='tab1' class='ui-tabs-panel ui-tabs-hide'>"; //Single WU

} else {
	echo "<div id='tab1' class='ui-tabs-panel'>"; //Single WU
}

echo "<h3><img src='images/award_star_silver_1.png'> ";

if ($id_workunit) {
	echo __('Update workunit');
} else {
	echo __('Add workunit');
}

if ($id_task) {
	echo ' - ';
	echo get_db_value ('name', 'ttask', 'id', $id_task);
}

if ($id_workunit) {
	$wu_user = get_db_value ('id_user', 'tworkunit', 'id', $id_workunit);
} else {
	$wu_user = $config["id_user"];
}

echo '</h3>';

//Print result output if any
if ($result_output) {
	echo $result_output;
}

$table->class = 'databox';
$table->width = '90%';
$table->data = array ();
$table->colspan = array ();
$table->colspan[5][0] = 3;

if (!isset($start_date))
	$start_date = substr ($now, 0, 10);

$table->data[0][0] = print_input_text ('start_date', $start_date, '', 10, 20,
	true, __('Date'));

// Profile or role
if (dame_admin ($config['id_user'])) {
	$table->data[0][1] = combo_roles (true, 'id_profile', __('Role'), true);
	//$table->data[0][1] = combo_roles_people_task ($id_task, $config['id_user'], __('Role'));
} else {
	$table->data[0][1] = combo_user_task_profile ($id_task, 'id_profile',
		$id_profile, false, true);
}

// Show task combo if none was given.
if (! $id_task) {
	$table->colspan[1][0] = 3;
	$table->data[1][0] = combo_task_user_participant ($wu_user,
		true, 0, true, __('Task'));
} else {
	$table->colspan[1][0] = 3;
	$table->data[1][0] = combo_task_user_participant ($wu_user,
	true, $id_task, true, __('Task'));
}

// Time used
$table->data[2][0] = print_input_text ('duration', $duration, '', 7, 7,
	true, __('Time used'));

if (dame_admin ($config['id_user'])) {
	$table->colspan[2][1] = 3;
	
	$params = array();
	$params['input_id'] = 'text-id_username';
	$params['input_name'] = 'id_username';
	$params['input_value'] = $wu_user;
	$params['title'] = 'Username';
	$params['return'] = true;
	$params['return_help'] = true;
	
	$table->data[2][1] = user_print_autocomplete_input($params);
}

// Various checkboxes
$table->data[3][0] = print_checkbox ('have_cost', 1, $have_cost, true,
	__('Have cost'));
$table->data[3][1] = print_checkbox ('public', 1, $public, true, __('Public'));

if (! $id_workunit) {
	$table->data[4][0] = print_checkbox ('forward', 1, false, true,
		__('Forward'));
	$table->data[4][0] .= print_help_tip (__('If this checkbox is activated, propagation will be forward instead backward'),
		true);

	$table->data[4][1] = print_checkbox ('split', 1, false, true,
		__('Split > 1day'));
	$table->data[4][1] .= print_help_tip (__('If workunit added is superior to 8 hours, it will be propagated to previous workday and deduced from the total, until deplete total hours assigned'),
		true);
}
$table->data[5][0] = print_textarea ('description', 10, 30, $description,
	'', true, __('Description'));

echo '<form id="single_task_form" method="post" onsubmit="return validate_single_form()">';
print_table ($table);

echo '<div style="width: '.$table->width.'" class="button">';
if ($id_workunit) {
	print_input_hidden ('operation', 'update');
	print_input_hidden ('id_workunit', $id_workunit);
	print_input_hidden ("wu_user", $wu_user);
	print_submit_button (__('Update'), 'btn_upd', false, 'class="sub upd"');
} else {
	print_input_hidden ('operation', 'insert');
	print_submit_button (__('Add'), 'btn_add', false, 'class="sub next"');
}
print_input_hidden ('timestamp', $now);
echo '</form>';	
echo "</div>";

echo "</div>";

//If not workunit update then enable the multiple workunit option
if (!$id_workunit) {
	
	
	if ($operation == 'multiple_wu_insert') {
		echo "<div id='tab2' class='ui-tabs-panel'>"; //Multiple WU
		echo "<table>";
		echo "<tr>";
		echo "<td>";
		echo "<h3><img src='images/award_star_silver_1.png'> ";
		echo __('Add multiple workunits summary ');
		echo '</h3>';
		echo "</td>";
		echo "<td>";
		echo print_button (__('Add new parse Workunit'), 'add_link', false, 'location.href=\'index.php?sec=users&sec2=operation/users/user_spare_workunit\'', 'class="sub upd"');
		echo "</td>";
		echo "</table>";
		foreach ($multiple_wu_report as $number => $mwur) {
			print_single_workunit_report($mwur);
		}

	} else {
		echo "<div id='tab2' class='ui-tabs-panel ui-tabs-hide'>"; //Multiple WU
		echo '<form id="multiple_task_form" method="post" onsubmit="return validate_multiple_form()">';
		print_input_hidden ('operation', 'multiple_wu_insert');
		echo "<table>";
		echo "<tr>";
		echo "<td>";
		echo "<h3 id='multi_task_title'><img src='images/award_star_silver_1.png'> ";
		echo __('Add multiple workunits');
		echo '</h3>';
		echo "</td>";
		echo "<td>";
		echo print_button (__('Add'), 'add_multi_wu', false, '', 'class="sub next"');
		echo "</td>";
		echo "<td>";
		echo print_submit_button (__('Save'), 'btn_upd', false, 'class="sub create"');
		echo "</td>";
		echo "</tr>";
		echo "</table>";
		
		//Massive work unit list
		create_new_table_multiworkunit(1);
		echo "</div>";
		echo '</form>';
	}
}

echo "</div>"; // End div tabs

echo '</div>';
?>

<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.autocomplete.js"></script>
<script type="text/javascript" src="include/js/integria_date.js"></script>

<script type="text/javascript">
	
function datepicker_hook () {
	add_datepicker ('input[name*="start_date"]', null);
}

function username_hook () {
	var idUser = "<?php echo $config['id_user'] ?>";
	
	bindAutocomplete ('input[name~="id_username"]', idUser);

}

//single task validation form
function validate_single_form() {
	var val = $("#id_task").val();
	
	if (val == 0) {
		
		var error_textbox = document.getElementById("error_task");
	
		if (error_textbox == null) {
			$('#single_task_form').prepend("<h3 id='error_task' class='error'><?php echo __("Task was not selected")?></h3>");
		}
		
		pulsate("#id_task");
		pulsate("#error_task");
		return false;  
		
	} 
	
	return true;
}

//Multiple task validation form
function validate_multiple_form() {

	var val = $("select[id^='id_task_']").val();

	if (val == 0) {
		
		var id_element = $("select[id^='id_task_']").attr("id");
		
		var number_id =  id_element.slice(8, id_element.length);
		
		var error_textbox = document.getElementById("error_task");
	
		if (error_textbox == null) {
			$("#wu_"+number_id).before("<h3 id='error_task' class='error'><?php echo __("Task was not selected")?></h3>");
		}
		
		pulsate("#"+id_element);
		pulsate("#error_task");
		return false;  
		
	} 
	
	return true;
}

function del_wu_button () {
	
	var cross1 = "<a id='del_wu_1' href='#'><img src='images/cross.png'></a>";
	
	$("#del_wu_1").html("");
		
	$("#del_wu_1").html(cross1);
	
	$('a[id^="del_wu"]').click(function (e) {
		//Prevent default behavior
		e.preventDefault();
		var id_element = $(this).attr("id");
		var number_id =  id_element.slice(7, id_element.length);
		
		$("table[id='wu_"+number_id+"']").remove();

	});
}

$(document).ready (function () {
	//Configure calendar datepicker
	datepicker_hook();
	
	//Configure username selector
	username_hook();
	
	$("#textarea-description").TextAreaResizer ();

		
	$("#id_task").change(function() {
		id_task = $(this).val();
		
		values = Array ();
		values.push ({name: "page",
					value: "operation/users/user_spare_workunit"});
		values.push ({name: "get_task_roles",
			value: 1});
		values.push ({name: "id_task",
			value: id_task});
		values.push ({name: "id_user",
			value: "<?php echo $config['id_user']; ?>"});
		jQuery.get ("ajax.php",
			values,
			function (data, status) {
				$("#id_profile").hide ().empty ();
				$("#id_profile").append ($('<option value="0"><?php echo __('N/A'); ?></option>'));
				if(data != false) {
					$(data).each (function () {
						$("#id_profile").append ($('<option value="'+this.id+'">'+this.name+'</option>'));
					});
				}
				$("#id_profile").show ();
			},
			"json"
		);
	});
	
	////////Configure menu tab interaction///////
	$('#tabmenu1').not('.ui-tabs-disabled').click (function (e){
		e.preventDefault();//Deletes default behavior
	
		//Change CSS tabs
		//tab1 selected
		$('#tabmenu1').addClass("ui-tabs-selected");
		$('#tabmenu1').removeClass("ui-tabs");
		
		//tab2 not selecteed
		$('#tabmenu2').addClass("ui-tabs");
		$('#tabmenu2').removeClass("ui-tabs-selected");
		
		//Show/hide divs
		$('#tab2').addClass("ui-tabs-hide");
		$('#tab1').removeClass("ui-tabs-hide");
	});

	$('#tabmenu2').not('.ui-tabs-disabled').click (function (e){
		e.preventDefault();//Deletes default behavior
	
		//Change CSS tabs
		//tab2 selected
		$('#tabmenu2').addClass("ui-tabs-selected");
		$('#tabmenu2').removeClass("ui-tabs");
		
		//tab1 not selecteed
		$('#tabmenu1').addClass("ui-tabs");
		$('#tabmenu1').removeClass("ui-tabs-selected");

		//Show/hide divs				
		$('#tab1').addClass("ui-tabs-hide");
		$('#tab2').removeClass("ui-tabs-hide");
	});	
	
	/////Add new table to add a massive task/////
	$("#button-add_multi_wu").click (function () {

		var error_textbox = document.getElementById("error_task");

		if (error_textbox != null) {

			$("#error_task").remove();
		}
		
		var valid_form = validate_multiple_form();
		
		if (valid_form) {
			
			var number_wu = $('#wu_1').siblings().length;
			
			values = Array ();
			values.push ({name: "page",
						value: "operation/users/user_spare_workunit"});
			values.push ({name: "get_new_mult_wu",
				value: 1});
			values.push ({name: "next",
				value: number_wu});
			jQuery.get ("ajax.php",
				values,
				function (data, status) {
						
					$("#wu_"+(number_wu-1)).before(data);
					
					//Reset datepicker hook function
					datepicker_hook();
					
					//Reset username selector hook
					username_hook();
					
					//Assign del button action
					del_wu_button();
				},
				"html"
			);		
		}
	});
});

</script>
