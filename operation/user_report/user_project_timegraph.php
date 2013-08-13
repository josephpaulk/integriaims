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

include_once ("include/functions_projects.php");
include_once ("include/functions_graph.php");
include_once ("include/functions_user.php");

check_login ();

$id_user = $config["id_user"];

$id_user_filter = get_parameter('user', '');
$start_date = get_parameter('start_date');
$end_date = get_parameter('end_date');

// ACL
$id_grupo = get_parameter ("id_grupo", 0);
$id_user=$config['id_user'];

if ((give_acl($id_user, $id_grupo, "PR") != 1) AND (give_acl($id_user, $id_grupo, "IR") != 1)) {
 	// Doesn't have access to this page
	audit_db($id_user,$config["REMOTE_ADDR"], "ACL Violation","Trying to access to user report without projects access or Incident access permissions");
	include ("general/noaccess.php");
	exit;
}

echo "<h3>" . __('Time per project graph') . "</h3>";

echo "<form action='index.php?sec=users&sec2=operation/user_report/user_project_timegraph' method='post'>";

echo '<table class="project_overview" border=0>';
echo '<tr>';

echo '<td width="25%"><b>'.__('User ').' </b>';
echo '<br>';

$params['input_value'] = $id_user_filter;
$params['input_id'] = 'text-user';
$params['input_name'] = 'user';
$params['return'] = false;
$params['return_help'] = false;

user_print_autocomplete_input($params);
echo '</td>';


echo '<td width="25%"><b>'.__('Start').' </b>';
print_help_tip(__('Empty date is all range time.'));
echo '<br>';
print_input_text ('start_date', $start_date, '', 10, 20);
echo '</td>';


echo '<td width="25%"><b>'.__('End').' </b>';
print_help_tip(__('Empty date is all range time.'));
echo '<br>';
print_input_text ('end_date', $end_date, '', 10, 20);
echo '</td>';


echo '</tr>';
echo "</table>";

echo '<div style="width:800px;" class="button">';
print_input_hidden ('action', 'update');
print_submit_button (__('Update'), 'upd_btn', false, 'class="sub upd"');
echo '</div>';

echo "</form>";
?>
<script type="text/javascript" src="include/js/jquery.ui.autocomplete.js"></script>
<script type="text/javascript">
	add_ranged_datepicker ("#text-start_date", "#text-end_date", null);
	
	
	$(document).ready (function () {
		var idUser = "<?php echo $config['id_user'] ?>";
		
		bindAutocomplete ("#text-user", idUser);
	});
</script>
<?php

echo "<div id='time_graph'></div>";

if (empty($start_date)) {
	$start_date = false;
}

if (empty($end_date)) {
	$end_date = false;
}

print_project_user_timegraph($id_user_filter, $start_date, $end_date);
