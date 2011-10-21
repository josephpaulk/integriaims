<?php

// INTEGRIA IMS - the ITIL Management System
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
	return;
}


if (give_acl($config["id_user"], 0, "PR") != 1){
	// Doesn't have access to this page
	audit_db ($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access to project detail page");
	include ("general/noaccess.php");
	exit;
}

include_once ("include/functions_graph.php");

$id_user = get_parameter ("id_user", $config["id_user"]);

if (($id_user != $config["id_user"]) AND (give_acl($config["id_user"], 0, "PM") != 1)){
	$id_user = $config["id_user"];
}

$sql = "SELECT ttask.id, ttask.name, tproject.name, ttask.completion, tproject.id, ttask.id, ttask.priority FROM trole_people_task, ttask, tproject WHERE trole_people_task.id_user = '$id_user' AND trole_people_task.id_task = ttask.id AND ttask.id_project = tproject.id AND tproject.disabled = 0 AND ttask.completion < 100 ORDER BY ttask.priority DESC";

echo "<h2>".__('Global task assignment')." ".__('For user'). " '".$id_user. "' ".print_user_avatar($id_user, true,true)."</h2>";

if (give_acl ($config["id_user"], 0, "PM")) {
	echo "<form name='xx' method=post action='index.php?sec=users&sec2=operation/users/user_task_assigment'>";
	
	echo "<table style='margin-left: 15px;' class=blank>";
	echo "<tr><td>";
	// Show user
	//combo_user_visible_for_me ($config["id_user"], "id_user", 0, "PR");
	$src_code = print_image('images/group.png', true, false, true);
	echo print_input_text_extended ('id_user', '', 'text-id_user', '', 15, 30, false, '',
			array('style' => 'background: url(' . $src_code . ') no-repeat right;'), true, '', '')
		. print_help_tip (__("Type at least two characters to search"), true);
	echo "<td>";
	print_submit_button (__('Go'), 'sub_btn', false, 'class="upd sub"');
	echo "</form></table>";
}

echo "<table  class='listing' width=90%>";
echo "<th>".__('Pri');
echo "<th>".__('Project');
echo "<th>".__('Task');
echo "<th>".__('Progress');
echo "<th>".__('Worked hours');
echo "<th>".__('Last update');
$result=mysql_query($sql);

while ($row=mysql_fetch_array($result)){
	echo "<tr>";
	echo "<td>".print_priority_flag_image ($row['priority'], true);
	echo "<td>".$row[2];
	$id_proj = $row[4];
	$id_task = $row[5];
	echo "<td><a href='index.php?sec=projects&sec2=operation/projects/task_detail&id_project=$id_proj&id_task=$id_task&operation=view'>".$row[1]."</a>";

	echo "<td >";
	echo progress_bar($row[3], 70, 20);

	echo "<td align=center>".get_task_workunit_hours_user ($row[0], $id_user);
	
	echo "<td class='f9'>";
	$time1 = get_db_sql ("SELECT timestamp
		FROM tworkunit_task, tworkunit
		WHERE tworkunit.id_user = '$id_user'
		AND tworkunit_task.id_task = ".$row[0].
		' AND tworkunit.id = tworkunit_task.id_workunit
		ORDER BY timestamp DESC LIMIT 1');
	echo substr($time1, 0, 10);

}
echo "</table>";


?>
<script type="text/javascript" src="include/js/jquery.autocomplete.js"></script>

<script  type="text/javascript">
$(document).ready (function () {
	$("#text-id_user").autocomplete ("ajax.php",
		{
			scroll: true,
			minChars: 2,
			extraParams: {
				page: "operation/incidents/incident_detail",
				search_users: 1,
				id_user: "<?php echo $config['id_user'] ?>"
			},
			formatItem: function (data, i, total) {
				if (total == 0)
					$("#text-id_user").css ('background-color', '#cc0000');
				else
					$("#text-id_user").css ('background-color', '');
				if (data == "")
					return false;
				return data[0]+'<br><span class="ac_extra_field"><?php echo __(" ") ?>: '+data[1]+'</span>';
			},
			delay: 200

		});
});
</script>

