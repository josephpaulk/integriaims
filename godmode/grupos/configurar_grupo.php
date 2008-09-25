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
    check_login();
    
    if (give_acl($config["id_user"], 0, "UM")==0) {
        audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access User Management");
        require ("general/noaccess.php");
        exit;
    }
    
    $id_user = $config["id_user"];

	// Inic vars
	$id_grupo = "";
	$nombre = "";
	$icon = "";
	$url = "";
	$id_user_default = "";
	$banner = "";
	$parent = "";
	$lang = "";
	$forced_email = "";
	$creacion_grupo = get_parameter ("creacion_grupo",0);
		
	if (isset($_GET["id_grupo"])){
		// Conecto con la BBDD
		$id_grupo = get_parameter ("id_grupo", "1");
		$group_row = get_db_row ("tgrupo", "id_grupo", $id_grupo);
		if ($group_row){
			$nombre = $group_row["nombre"];
			$icon = $group_row["icon"];
			$url = $group_row["url"];
			$id_user_default = $group_row["id_user_default"];
			$banner = $group_row["banner"];
			$parent = $group_row["parent"];
			$lang = $group_row["lang"];
			$forced_email = $group_row["forced_email"];

		} else
			{
			echo "<h3 class='error'>".$lang_label["group_error"]."</h3>";
			echo "</table>";
			include ("general/footer.php");
			exit;
			}
	}

	echo "<h2>".$lang_label["group_management"]."</h2>";
	if (isset($_GET["creacion_grupo"])) {
        echo "<h3>".$lang_label["create_group"]."</h3>";
    }
	if (isset($_GET["id_grupo"])) {
        echo "<h3>".$lang_label["update_group"]."</h3>";
    }
	
    echo '<table width="600" class="databox">';
	echo '<form name="grupo" method="post" action="index.php?sec=users&sec2=godmode/grupos/lista_grupos">';

	if ($creacion_grupo == 1)
		echo print_input_hidden ('crear_grupo', 1, true);
	else {
		echo print_input_hidden ('update_grupo', 1, true);
		echo print_input_hidden ('id_grupo', $id_grupo, true);
	}

    echo '<tr>';
    echo '<td>';
	echo print_input_text ("name", $nombre, '', 20, 0, true , __("group_name"));
	echo "<td>";
	echo print_checkbox ("forced_email", 1, $forced_email, true, __("Forced email"));
    echo '</td></tr>';

	echo '<tr>';
    echo '<td colspan=2>';
	echo print_input_text ("url", $url, '', 50, 0, true , __("URL"));

	echo "<tr>";
	echo '<td>';	
	$ficheros = list_files ('images/groups_small/', "png", 1, true);
	echo print_select ($ficheros, "icon", $icon, $script = '', '', 0, true, 0, false, __("Icon"));
	
	echo '<td>';	
	$banners = list_files ('images/', "png", 1, true);
	echo print_select ($banners, "banner", $banner, $script = '', '', 0, true, 0, false, __("Banner"));

	echo "<tr><td>";
	echo "<b>".__("Default user")."</b><br>";
	echo combo_user_visible_for_me ($config["id_user"], "id_user_default", 0, "IR");

	echo "<td>";
	echo print_select_from_sql ("SELECT id_language, name FROM tlanguage", "lang", $lang, '', '', '0', true, false, true, __("Language") );

	echo "</table>";

	echo "<div class='button' style='width: 600px;'>";
    
    if (isset($_GET["creacion_grupo"])){
		echo print_submit_button (__('Create'), '', false, "class='sub wizard'", '', false, false);
	} else {
		echo print_submit_button (__('Update'), '', false, "class='sub upd'", '', false, false);
	} 
    echo "</form></div>";

?>