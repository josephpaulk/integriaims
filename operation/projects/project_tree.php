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

if (! give_acl ($config["id_user"], 0, "IR")) {
	// Doesn't have access to this page
	audit_db ($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access to project detail page");
	include ("general/noaccess.php");
	exit;
}

$id_user = get_parameter ("user_form", $config["id_user"]);
$completion = get_parameter ("completion", 100);
$project_kind = get_parameter ("project_kind", "defined_end");

// Show user
$table->width = '90%';
$table->class = 'blank';
$table->data = array ();
$table->data[0][0] = combo_user_visible_for_me ($id_user, "user_form", 0, "PR", true, __('User'));

$completions = array ();
$completions[-1] = __('All');
$completions[100] = __('Not finished');
$completions[666] = __('Done');
$table->data[0][1] = print_select ($completions, 'completion', '', $completion,
	'', '', true, false, false, __('Completion'));

$table->data[0][2] = print_select ($completions, 'completion', '', $completion,
	'', '', true, false, false, __('Progress'));

$types = array ();
$types['all'] = __('All');
$types['defined_end'] = __('Defined end');
$table->data[0][3] = print_select ($types, 'project_kind', '', $project_kind,
	'', '', true, false, false, __('Type'));

echo '<form method="post">';
print_table ($table);
echo '<div class="button" style="width: '.$table->width.'">';
print_submit_button (__('Update'), '', false, 'class="sub upd"');
echo '</div>';
echo '</form>';

if ($id_user != ""){
	$mapfilename = $config["base_url"]. "/attachment/tmp/$id_user.projectall.map";

	echo '<a href="'.$mapfilename.'">';
	echo "<img src='include/functions_graph.php?type=all_project_tree&project_kind=$project_kind&id_user=$id_user&completion=$completion'>";
	echo '</a>';
}

?>
