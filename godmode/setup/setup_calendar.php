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
	
if (! dame_admin ($config["id_user"])) {
	audit_db ("ACL Violation", $config["REMOTE_ADDR"], "No administrator access", "Trying to access visual setup");
	require ("general/noaccess.php");
	exit;
}

$is_enterprise = false;
if (file_exists ("enterprise/load_enterprise.php")) {
	$is_enterprise = true;
}

/* Tabs code */
echo '<div id="tabs">';

/* Tabs list */
echo '<ul class="ui-tabs-nav">';
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/setup"><span><img src="images/cog.png" title="'.__('Setup').'"></span></a></li>';
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/setup_visual"><span><img src="images/chart_bar.png" title="'.__('Visual setup').'"></span></a></li>';
if ($is_enterprise) {
	echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=enterprise/godmode/setup/setup_password"><span valign=bottom><img src="images/lock.png" title="'.__('Password policy').'"></span></a></li>';
}
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/incidents_setup"><span><img src="images/bug.png" title="'.__('Incident setup').'"></span></a></li>';
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/setup_mail"><span><img src="images/email.png"  title="'.__('Mail setup').'"></span></a></li>';
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/setup_mailtemplates"><span><img src="images/email_edit.png"  title="'.__('Mail templates setup').'"></span></a></li>';
if ($is_enterprise) {
	echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=enterprise/godmode/usuarios/menu_visibility_manager"><span valign=bottom><img src="images/eye.png" title="'.__('Visibility management').'"></span></a></li>';
}
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/setup_pandora"><span><img src="images/pandora.ico"  title="'.__('Pandora FMS inventory').'"></span></a></li>';
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/setup_auth"><span><img src="images/book_edit.png"  title="'.__('Authentication').'"></span></a></li>';
echo '<li class="ui-tabs-selected"><a href="index.php?sec=godmode&sec2=godmode/setup/setup_calendar"><span><img src="images/calendar_edit.png"  title="'.__('Calendar').'"></span></a></li>';
echo '</ul>';

echo '</div>';

$action = get_parameter ("action");
$description = get_parameter ("description", "");
$day = get_parameter ("day", "");
$yearly = get_parameter("yearly", 0);
$easter_related = get_parameter("easter-related", 0);
$id = get_parameter("id", 0);


echo '<h2>'.__('Calendar setup').'</h2>';

if ($action === 'update_general') {
	$config["working_weekends"] = (int) get_parameter("working_weekends", 0);
	
	update_config_token ("working_weekends", $config["working_weekends"]);
		
	echo '<h3 class="suc">'.__('Updated successfuly').'</h3>';
}

if ($action === 'insert') {
	
		$sql = sprintf ('INSERT INTO tholidays (day, yearly, description, aster_related, 
						easter_offset) VALUES ("%s", %d, "%s", %d, %d)',
			$day, $yearly, $description, $easter_related, $easter_offset);

		$id = process_sql ($sql, 'insert_id');
		
	if (!$id) {
		echo '<h3 class="error">'.__('Could not be created').'</h3>';
	} else {
		echo '<h3 class="suc">'.__('Successfully created').'</h3>';
		insert_event ("HOLIDAY CREATED", $id, 0, $description);
	}
	
	$id = 0;   
}

if ($action === 'update') {

		$sql = sprintf ('UPDATE tholidays SET day="%s", yearly=%d, 
						description="%s", easter_related=%d, easter_offset=%d 
						WHERE id = %d',
			$day, $yearly, $description, $easter_related, $easter_offset, $id);
		/* No funciona bien con process_sql
		 * $result = process_sql ($sql);*/
		$result =  mysql_query($sql);
		echo '<h3 class="error">'. $result .'</h3>';
	if (! $result) {
		echo '<h3 class="error">'.__('Could not be updated').'</h3>';
	} else {
		echo '<h3 class="suc">'.__('Successfully updated').'</h3>';
		insert_event ("HOLIDAY UPDATED", $id, 0, $description);
	}
	
	$id = 0;	 
}

if ($action === 'delete') {
	$sql = sprintf ('DELETE FROM tholidays WHERE id = %d', $id);
	$result = process_sql ($sql);

	if (!$result) {
			echo '<h3 class="error">'.__("Could not be deleted").'</h3>';
		}
	else {
			echo '<h3 class="suc">'.__("Successfully deleted").'</h3>';
			insert_event ("HOLIDAY DELETED", $id, 0, "");		
		}
	 
		$id = 0;
}

//form
if (($action === 'create') ||(!empty($id))){
	$table->width = '90%';
	$table->class = 'databox';
	$table->colspan = array ();
	$table->data = array ();
	
	if ($id){
		$holiday = get_db_row('tholidays', 'id', $id);
		$description = $holiday['description'];
		$day = date("Y-m-d", strtotime($holiday['day']));
		$yearly = $holiday['yearly'];
		$easter_related = $holiday['easter_related'];
	}

	$table->data[0][0] = print_input_text ("description", $description, '',
			50, 60, true, __('Description'));
	$table->data[0][1] = print_input_text ("day", $day, '',
			30, 30, true, __('Date'));
	
	$table->data[1][0] = print_checkbox ("yearly",  1,  $yearly, true, 
			__('Repeats Annually on same month and day'));
	$table->data[1][1] = print_checkbox ("easter-related", 1, $easter_related, true, 
			__('Repeats Annually variable and related to easter '));

	echo "<form name='setup_calendar_holidays' method='post' action='index.php?sec=godmode&sec2=godmode/setup/setup_calendar'>";

	print_table ($table);

	echo '<div style="width: '.$table->width.'" class="button">';
	if (empty($id)) {
			print_submit_button (__('Create'), 'crt_btn', false, 'class="sub next"');
			print_input_hidden ('action', 'insert');
	} else {
			print_submit_button (__('Update'), 'upd_btn', false, 'class="sub upd"');
			print_input_hidden ('id', $id);
			print_input_hidden ('action', 'update');
	}
	echo '</div>';
	echo '</form>';
}

if (($action != 'create') && (empty($id))){

	echo '<br>';
	echo '<h3>'.__('General').'</h3>'; 
	unset($table);
	
	$table->width = '90%';
	$table->class = 'databox';
	$table->colspan = array ();
	$table->data = array ();
	
	$table->data[0][0] =print_checkbox ("working_weekends", 1, $config["working_weekends"], 
					true, __("Weekends are working days"));
	
	echo "<form name='setup_calendar_general' method='post' action='index.php?sec=godmode&sec2=godmode/setup/setup_calendar'>";

	print_table ($table);

	echo '<div style="width: '.$table->width.'" class="button">';
	print_submit_button (__('Update'), 'upd_btn_gral', false, 'class="sub upd"');
	print_input_hidden ('action', 'update_general');
	echo '</div>';
	echo '</form>';
   
	echo '<br>';
	echo '<h3>'.__('Non-working days Management').'</h3>';
	
	unset($table);
	
	$table->width = '90%';
	$table->class = 'listing';
	$table->data = array ();
	$table->head = array ();
	$table->colspan = array ();
	$table->head[0] = __('Description');
	$table->head[1] = __('Date');
	$table->head[2] = __('Annually Fixed');
	$table->head[3] = __('Annually Easter');
	$table->head[4] = __('Easter Offset (days)');
	$table->head[5] = __('Delete');
	$table->style = array ();
	$table->style[0] = 'font-weight: bold';
	$table->align = array ();
	$table->align[5] = 'center';

	$holidays = calendar_get_holidays();
		
	if ($holidays !== false) {
			foreach ($holidays as $holiday) {
					$data = array ();

					$data[0] = ' <a href="index.php?sec=godmode&sec2=godmode/setup/setup_calendar&id='.
							$holiday['id'].'">'.$holiday['description'].'</a>';
					if ($holiday['easter_related']) 
						$data[1] = __('Variable');
					else
						$data[1] = strftime("%B %d", strtotime($holiday['day']));
					$data[2] = $holiday['yearly'] ? __('Yes'):__('No');
					$data[3] = $holiday['easter_related'] ? __('Yes'):__('No');
					$data[4] = $holiday['easter_offset'];
					$data[5] = '<a href=index.php?sec=godmode&sec2=godmode/setup/setup_calendar&action=delete&id='.
							$holiday['id'].' onClick="if (!confirm(\''.__('Are you sure?').'\'))
							return false;"><img src="images/cross.png"></a>';

					array_push ($table->data, $data);
			}
	}
	else{
			$table->colspan[0][0] = 6;
			$table->data[0][0] = __("No holidays defined");
	}
	
	print_table ($table);

	echo '<div class="button" style="width: '.$table->width.'">';
	echo '<form method="post">';
	print_input_hidden ('action', 'create');
	print_submit_button (__("Add"), 'add_btn', false, 'class="sub next"');
	echo "</form></div>";
}

?>

<script type="text/javascript" src="include/js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
<script type="text/javascript">
$(document).ready (function () {
	$("#text-day").datepicker();
		
		 $("#checkbox-yearly").change(function(){
			 if ($(this).is(':checked')){
				$("#checkbox-easter-related").attr('checked', false);  
			 }
		 });
		 
		 $("#checkbox-easter-related").change(function(){
			 if ($(this).is(':checked')){
				$("#checkbox-yearly").attr('checked', false);  
			 }
		 });
});
</script>
