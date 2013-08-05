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


$id = get_parameter("id");

$add_note = get_parameter("addnote");

if ($add_note) {

	$note = get_parameter("note");

	$now = print_mysql_timestamp();

	$sql = sprintf('INSERT INTO ttodo_notes (`id_todo`,`written_by`,`description`, `creation`) 
					VALUES (%d, "%s", "%s", "%s")', $id, $config["id_user"], $note, $now);

	$res = process_sql ($sql, 'insert_id');

	if (! $res)
		echo '<h3 class="error">'.__('There was a problem creating the note').'</h3>';
	else
		echo '<h3 class="suc">'.__('Note was added successfully').'</h3>'; 

}


echo '<h3>'.__('Add a note').'</h3>';

$table->width = '100%';
$table->colspan = array ();
$table->data = array ();
$table->size = array();
$table->style = array();
$table->class = "none";

$table->data[0][0] = print_textarea ('note', 10, 70, '', "style='resize:none;'", true, __('Note'));

echo '<form method="post" action="index.php?sec=projects&sec2=operation/workorders/wo&operation=view&tab=notes&addnote=1&id='.$id.'">';

echo "<div style='width: 80%;margin: 0 auto;'>";
print_table ($table);

echo '<div style="width: 100%" class="button">';
print_submit_button (__('Add'), 'addnote', false, 'class="sub next"');
echo '</div>';
echo "</form>";

echo "</div>";

// List of WO attachments

$sql = "SELECT * FROM ttodo_notes WHERE id_todo = $id ORDER BY `creation` DESC";
$notes = get_db_all_rows_sql ($sql);	
if ($notes !== false) {
	echo "<h3>". __('Notes for this workorder')."</h3>";

	foreach ($notes as $note) {
			echo "<div class='notetitle'>"; // titulo

			$timestamp = $note["creation"];
			$nota = clean_output_breaks($note["description"]);
			$id_usuario_nota = $note["written_by"];

			$avatar = get_db_value ("avatar", "tusuario", "id_usuario", $id_usuario_nota);

			// Show data
			echo "<img src='images/avatars/".$avatar."_small.png'>&nbsp;";
			echo " <a href='index.php?sec=users&sec2=operation/users/user_edit&id=$id_usuario_nota'>";
			echo $id_usuario_nota;
			echo "</a>";
			echo " ".__("said on $timestamp");

			// show delete activity only on owners
			$owner = get_db_value ("owner", "tlead", "id", $id);
			if ($owner == $config["id_user"])
				echo "&nbsp;&nbsp;<a href='index.php?sec=customers&sec2=operation/leads/lead_detail&id=$id&op=activity&op2=purge&activity_id=".$note["id"]." '><img src='images/cross.png'></a>";
			echo "</div>";

			// Body
			echo "<div class='notebody'>";
			echo clean_output_breaks($nota);
			echo "</div>";
		}
} else {
	echo "<h3>". __('There aren\'t notes for this workorder')."</h3>";
}


?>