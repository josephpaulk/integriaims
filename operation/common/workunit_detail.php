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


global $config;

if (check_login() != 0) {
	audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access","Trying to access incident viewer");
	require ("general/noaccess.php");
	exit;
}

$id = give_parameter_get ("id",0);
$title = give_parameter_get ("title",0);

// ********************************************************************
// Note detail of $id_note
// ********************************************************************
echo "<h1>$title</h1>";
$sql4='SELECT * FROM tworkunit WHERE id = '.$id;
$res4=mysql_query($sql4);
if ($row3=mysql_fetch_array($res4)){

	echo "<div class='notetitle'>"; // titulo

	$timestamp = $row3["timestamp"];
	$duration = $row3["duration"];
	$id_user = $row3["id_user"];
	$avatar = give_db_value ("avatar", "tusuario", "id_usuario", $id_user);
	$description = $row3["description"];

	// Show data
	echo "<img src='images/avatars/".$avatar."_small.png'>&nbsp;";
	echo " <a href='index.php?sec=users&sec2=operation/users/user_edit&ver=$id_user'>";
	echo $id_usuario_nota;
	echo "</a>";
	echo ' '.__('said on').' '.$timestamp;
	echo "</div>";

	// Body
	echo "<div class='notebody'>";
	echo clean_output_breaks($description);
	echo "</div>";
} else 
	echo __('No data available');

?>
