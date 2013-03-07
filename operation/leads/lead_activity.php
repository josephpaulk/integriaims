<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2013 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

check_login ();

// Activities
$op2 = get_parameter ("op2", "");

if ($op2 == "add"){
	$datetime =  date ("Y-m-d H:i:s");
	$comments = get_parameter ("comments", "");
	$sql = sprintf ('INSERT INTO tlead_activity (id_lead, written_by, creation, description) VALUES (%d, "%s", "%s", "%s")', $id, $config["id_user"], $datetime, $comments);
	process_sql ($sql, 'insert_id');

	$datetime =  date ("Y-m-d H:i:s");
	$sql = sprintf ('INSERT INTO tlead_history (id_lead, id_user, timestamp, description) VALUES (%d, "%s", "%s", "%s")', $id, $config["id_user"], $datetime, "Added comments");
	process_sql ($sql, 'insert_id');
}

// Add item form
if($manager) {
	echo '<form method="post" action="index.php?sec=customers&sec2=operation/leads/lead_detail&id='.$id.'&op=activity&op2=add">';
	echo "<h3>".__("Add activity")."</h3><p>";
	echo "<textarea name='comments' style='margin-left: 10px; width:94%; height: 50px'>";
	echo "</textarea>";

	echo '<div class="button" style="margin-left: 10px; width: 92%;">';
	print_submit_button (__('Add activity'), "create_btn", false, 'class="sub next"', false);
	echo "</div>";
	echo '</form>';
}

$sql = "SELECT * FROM tlead_activity WHERE id_lead = $id ORDER BY creation DESC";

$activities = get_db_all_rows_sql ($sql);
$activities = print_array_pagination ($activities, "index.php?sec=customers&sec2=operation/leads/lead_detail&id=$id&op=activities");

if ($activities !== false) {	
	if (sizeof($activities) == 0){
		echo "<h3>".__("There is no activity")."</h3>";
	} else {
		foreach ($activities as $activity) {
			echo "<div class='notetitle'>"; // titulo

			$timestamp = $activity["creation"];
			$nota = $activity["description"];
			$id_usuario_nota = $activity["written_by"];

			$avatar = get_db_value ("avatar", "tusuario", "id_usuario", $id_usuario_nota);

			// Show data
			echo "<img src='images/avatars/".$avatar."_small.png'>&nbsp;";
			echo " <a href='index.php?sec=users&sec2=operation/users/user_edit&id=$id_usuario_nota'>";
			echo $id_usuario_nota;
			echo "</a>";
			echo " ".__("said on $timestamp");
			echo "</div>";

			// Body
			echo "<div class='notebody'>";
			echo clean_output_breaks($nota);
			echo "</div>";
		}
	}
} 
?>