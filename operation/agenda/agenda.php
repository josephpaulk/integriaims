<?php
// Integria IMS - http://www.integriaims.com
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

global $config;
$result_msg = "";

check_login ();

$id_grupo = (int) get_parameter ('id_grupo');

if (! give_acl ($config['id_user'], $id_grupo, "AR")) {
 	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access agenda of group ".$id_grupo);
	include ("general/noaccess.php");
	exit;
}

$create_item = (bool) get_parameter ('create_item');
$delete_event = (bool) get_parameter ('delete_event');

// Add calendar item
if ($create_item) {
	$description = get_parameter ("description");
	$time = get_parameter ("time");
	$date = get_parameter ("start_date");
	$duration = get_parameter ("duration",0);
	$id_group_f = get_parameter ("id_group",0);
	$public = get_parameter ("public",0);
	$alarm = get_parameter ("alarm",0);
	$sql = sprintf ('INSERT INTO tagenda (public, alarm, timestamp, id_user,
		content, duration, id_group)
		VALUES (%d, "%s", "%s %s", "%s", "%s", %d, %d)',
		$public, $alarm, $date, $time, $config['id_user'], $description,
		$duration, $id_group_f);
	process_sql ($sql);

	$full_path = $config["homedir"]."/attachment/tmp/";
	$ical_text = create_ical ($date." ".$time, $duration, $config["id_user"], $description, "Integria imported event: $description");
	$full_filename_h = fopen ($full_path.$config['id_user'].".ics", "a");
	$full_filename = $full_path.$config['id_user'].".ics";
	fwrite( $full_filename_h, $ical_text);
	fclose ($full_filename_h);

	$nombre = get_db_sql ( " SELECT nombre_real 
		FROM tusuario WHERE id_usuario = '". $config["id_user"]."'");
	$email = get_db_sql ( " SELECT direccion 
		FROM tusuario WHERE id_usuario = '". $config["id_user"]."'");

	$mail_description = $config["HEADER_EMAIL"].
		"A new entry in calendar has been created by user ".$config['id_user']." ($nombre)\n\n
		Date and time: $date $time\n
		Description  : $description\n\n".$config["FOOTER_EMAIL"];

	if ($public) {

		
		if ($config["enteprise"] == 1){
			$sql = sprintf ('SELECT nombre_real, direccion FROM tusuario, tusuario_perfil
			WHERE tusuario_perfil.id_grupo = %d
			AND tusuario_perfil.id_usuario = tusuario.id_usuario',
			$id_group_f);
		} else {
				$sql = sprintf ('SELECT nombre_real, direccion FROM tusuario');
		}

		$users = get_db_all_rows_sql ($sql);
		foreach ($users as $user) {
			$nombre = $user['nombre_real'];
			$email = $user['direccion'];
			integria_sendmail_attach ( $nombre, $email, $config["mail_from"], 
					"[".$config["sitename"]."] New calendar event", 
					$full_filename, "text/Calendar", $mail_description );
		}
	} else {
		integria_sendmail_attach ( $nombre, $email, $config["mail_from"],
			"[".$config["sitename"]."] New calendar event", 
			$full_filename, "text/Calendar", $mail_description );
	}
	unlink ($full_filename);
	echo "<h3 class='suc'>".__('Added event to calendar')."</h3>";
	insert_event ("INSERT CALENDAR EVENT", 0, 0, $description);
}

// Delete event
if ($delete_event) {
	$id = get_parameter ("delete_event", 0);
	$event_user = get_db_value ("id_user", "tagenda", "id", $id);
	
	if ($event_user == $config['id_user']) { 
		// Only admins (manage incident) or owners can modify incidents, including their notes
		$sql = sprintf ('DELETE FROM tagenda WHERE id = %d', $id);
		process_sql ($sql);
	}
	insert_event ("DELETE CALENDAR EVENT", 0, 0, $event_user);
}

// Get parameters for actual Calendar show
$time = time();
$month = get_parameter ("month", date ('n'));
$year = get_parameter ("year", date ('y'));

$today = date ('j');
$days_f = array();
$first_of_month = gmmktime(0,0,0,$month,1,$year);
$days_in_month=gmdate('t',$first_of_month);
$locale = $config["language_code"];

// Calculate PREV button
if ($month == 1){
	$month_p = 12;
	$year_p = $year -1;
} else {
	$month_p = $month -1;
	$year_p = $year;
}

// Calculate NEXT button
if ($month == 12){
	$month_n = 1;
	$year_n = $year +1;
} else {
	$month_n = $month +1;
	$year_n = $year;
}

// Next month mini calendar
echo "<div align='right' style='float: right;'>";
echo generate_calendar ($year_n, $month_n, $days_f, 3, NULL, $locale);
echo "</div>";

echo "<div style='float: left;'>";
echo generate_calendar ($year_p, $month_p, $days_f, 3, NULL, $locale);
echo "</div>";


// Space to skip blocks
echo "<div style='height: 250px'> </div>";
//echo "<br>";

$start_date = $mydate_sql = date("Y-m-d", time());

$pn = array('&laquo;'=>"index.php?sec=agenda&sec2=operation/agenda/agenda&month=$month_p&year=$year_p", '&raquo;'=>"index.php?sec=agenda&sec2=operation/agenda/agenda&month=$month_n&year=$year_n");

echo '<div align="center">';
echo generate_calendar_agenda ($year, $month, $days_f, 3, NULL, $locale, $pn, $config['id_user']);
echo '</div>';

// Legend for icons
echo "<div style='float: right;'>";
echo "<h3>".__('Legend')."</h3>";
echo "<table width=150 cellspacing=10 border=0 class='databox'>";
echo "<tr><td valign='top'>";
echo "<img src='images/user_comment.png'>";
echo "<td valign='top'>";
echo "&nbsp;".__('Public');
echo "<tr><td valign='top'>";
echo "<img src='images/cancel.gif'>";
echo "<td valign='top'>";
echo "&nbsp;".__('Delete');
echo "<tr><td valign='top'>";
echo "<img src='images/bell.png'>";
echo "<td valign='top'>";
echo "&nbsp;".__('Alert');
echo "</table>";
echo "</div>";


// Add item control
?>
	<h3><img src='images/note.png'>&nbsp;&nbsp;
	<a href="javascript:;" onmousedown="toggleDiv('calendar_control');">
<?php
echo __('Add agenda entry')."</A></h3>";

echo "<div id='calendar_control' style='display:none'>";

$table->width = '400px';
$table->class = 'databox';
$table->colspan = array ();
$table->colspan[0][0] = 3;
$table->data = array ();
$table->data[0][0] = print_input_text ('description', '', '', 45, 100, true, __('Description'));

$table->data[1][0] = print_input_text ('duration', 0, '', 6, 6, true, __('Duration in hours'));
$table->data[1][1] = combo_groups_visible_for_me ($config['id_user'], "id_group", 0, 'AR', 0, true);

$table->data[2][0] = print_checkbox ('public', 1, false, true, __('Public'));

$alarms = array ();
$alarms[60] = __('One hour');
$alarms[120] = __('Two hours');
$alarms[240] = __('Four hours');
$alarms[1440] = __('One day');
$table->data[2][1] = print_select ($alarms, 'alarm', '', '', __('None'), '0',
	true, false, false, __('Alarm'));

$table->data[3][0] = print_input_text ('start_date', $start_date, '', 10, 20, true, __('Date'));
$table->data[3][1] = print_input_text ('time', date ('H:i'), '', 10, 20, true, __('Time'));

echo '<form method="post">';
print_table ($table);
echo '<div class="button" style="width: '.$table->width.'">';
print_submit_button (__('Create'), 'create_btn', false, 'class="sub next"');
print_input_hidden ('create_item', 1);
print_input_hidden ('month', $month);
print_input_hidden ('year', $year);
echo '</div>';
echo '</form>';
?>

<script type="text/javascript" src="include/js/jquery.ui.slider.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>

<script type="text/javascript">

$(document).ready (function () {
	$("#text-start_date").datepicker ({
		minDate: 0
	})
});
</script>
