<?php

// Integria 1.0 - http://integria.sourceforge.net
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

// Load global vars

global $config;

if (check_login() != 0) {
 	audit_db("Noauth",$config["REMOTE_ADDR"], "No authenticated access","Trying to access task tracking");
	require ("general/noaccess.php");
	exit;
}

$id_task = give_parameter_get ("id_task", -1);
$cabecera=0;
$sql4='SELECT * FROM ttask_track WHERE id_task= '.$id_task;

$color = 0;
echo "<h1>".lang_string ("task_tracking")."</h1>";
echo "<h3>";
echo give_db_sqlfree_field("SELECT name FROM ttask WHERE id = $id_task");
echo "</h3>";

echo "<table cellpadding='3' cellspacing='3' border='0' width=600>";

if ($res4=mysql_query($sql4)){
	echo "<tr><th>".$lang_label["state"]."<th>".$lang_label["user"]."<th  width='80'>".$lang_label["timestamp"];
	while ($row2=mysql_fetch_array($res4)){
		$timestamp = $row2["timestamp"];
		$state = $row2["state"];
		$user = $row2["id_user"];
		$external_data = $row2["id_external"];
		
		if ($color == 1){
			$tdcolor = "datos";
			$color = 0;
		} else {
			$tdcolor = "datos2";
			$color = 1;
		}
		
		echo '<tr><td class="' . $tdcolor . '">';

		switch($state){
			case 11: $descripcion = lang_string ("Task added");
				break;
            case 12: $descripcion = lang_string ("Task updated");
                break;
            case 14: $descripcion = lang_string ("Workunit added");
                break;
            case 15: $descripcion = lang_string ("File added");
                break;			
            case 16: $descripcion = lang_string ("Task completion progress updated");
                break;
            case 17: $descripcion = lang_string ("Task finished");
                break;
            case 18: $descripcion = lang_string ("Task member updated");
                break;
            case 19: $descripcion = lang_string ("Task moved");
                break;
            case 20: $descripcion = lang_string ("Task deleted");
                break;
 			default: $descripcion = $lang_label["unknown"];
		}
		
		echo $descripcion;
		echo '<td class="' . $tdcolor . '">';
		$nombre_real = dame_nombre_real($user);
		echo " $nombre_real";
		echo '<td class="' . $tdcolor . '">';
		echo $timestamp;
	}
echo "</table>"; 
} else
	echo $lang_label["no_data"];

?>
