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
	audit_db ("ACL Violation", $config["REMOTE_ADDR"], "No administrator access", "Trying to access setup");
	require ("general/noaccess.php");
	exit;
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
echo '<li class="ui-tabs-selected"><a href="index.php?sec=godmode&sec2=godmode/setup/incidents_setup"><span><img src="images/bug.png" title="'.__('Incidents setup').'"></span></a></li>';
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/inventories_setup"><span><img src="images/page_white_text.png"  title="'.__('Inventories setup').'"></span></a></li>';
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/setup_mail"><span><img src="images/email.png"  title="'.__('Mail setup').'"></span></a></li>';
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/setup_mailtemplates"><span><img src="images/email_edit.png"  title="'.__('Mail templates setup').'"></span></a></li>';
if ($is_enterprise) {
	echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=enterprise/godmode/usuarios/menu_visibility_manager"><span valign=bottom><img src="images/eye.png" title="'.__('Visibility management').'"></span></a></li>';
}
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/setup_pandora"><span><img src="images/pandora.ico"  title="'.__('Pandora FMS inventory').'"></span></a></li>';
echo '<li class="ui-tabs"><a href="index.php?sec=godmode&sec2=godmode/setup/setup_auth"><span><img src="images/book_edit.png"  title="'.__('Authentication').'"></span></a></li>';
echo '</ul>';

echo '</div>';

$update = (bool) get_parameter ("update");

echo "<h2>".__('Incidents setup')."</h2>";

if ($update) {
	$status = (array) get_parameter ('status');
	$resolutions = (array) get_parameter ('resolutions');
	
	foreach ($status as $id => $name) {
		$sql = sprintf ('UPDATE tincident_status SET name = "%s"
			WHERE id = %d',
			$name, $id);
		process_sql ($sql);
	}
	
	foreach ($resolutions as $id => $name) {
		$sql = sprintf ('UPDATE tincident_resolution SET name = "%s"
			WHERE id = %d',
			$name, $id);
		process_sql ($sql);
	}
	echo '<h3 class="suc">'.__('Updated successfuly').'</h3>';
}


echo '<h3>'.__('Status').'</h3>';

echo '<form method="post">';

$table->width = '30%';
$table->class = 'databox';
$table->colspan = array ();
$table->data = array ();

$status = get_db_all_rows_in_table ('tincident_status');

foreach ($status as $stat) {
	$data = array ();
	
	$data[0] = print_input_text ('status['.$stat['id'].']', $stat['name'],
		'', 35, 255, true);
	
	array_push ($table->data, $data); 
}

print_table ($table);

echo '<h3>'.__('Resolutions').'</h3>';

$table->data = array ();

$resolutions = get_db_all_rows_in_table ('tincident_resolution');

foreach ($resolutions as $resolution) {
	$data = array ();
	
	$data[0] = print_input_text ('resolutions['.$resolution['id'].']', $resolution['name'],
		'', 35, 255, true);
	
	array_push ($table->data, $data); 
}

print_table ($table);

echo '<div style="width: '.$table->width.'" class="button">';
print_input_hidden ('update', 1);
print_submit_button (__('Update'), 'upd_button', false, 'class="sub upd"');
echo '</div>';
echo '</form>';
?>

<script type="text/javascript">
$(document).ready (function () {
	$("textarea").TextAreaResizer ();
});
</script>
