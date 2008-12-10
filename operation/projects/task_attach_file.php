<?PHP
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


// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// ADD FILE CONTROL
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

global $config;
check_login ();

$id_task = get_parameter ("id_task", -1);
if ($id_task != -1)
	$task_name = get_db_value ("name", "ttask", "id", $id_task);
else
	$task_name = "";
$id_project = get_parameter ("id_project", -1);
	
if ($id_task > 0 && ! user_belong_task ($config["id_user"], $id_task)){
	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to task tracking without permission");
	no_permission();
}

echo "<h3><img src='images/disk.png'>&nbsp;&nbsp;";
echo __('Upload file')." - $task_name</A></h3>";

echo "<div id='upload_control'>";
echo "<table cellpadding=4 cellspacing=4 border=0 width='700' class='databox_color'>";
echo "<tr>";
echo '<td class="datos">'.__('Filename').'</td><td class="datos">';
echo "<form method='post' action='index.php?sec=projects&sec2=operation/projects/task_files&id_task=$id_task&id_project=$id_project&operation=attachfile' enctype='multipart/form-data'>";
echo '<input type="file" name="userfile" value="userfile" class="sub" size="40">';
echo '<tr><td class="datos2">'.__('Description').'</td><td class="datos2" colspan=3><input type="text" name="file_description" size=47>';
echo "</td></tr></table>";
echo "<div class=button style='width:700px'>";
echo '<input type="submit" name="upload" value="'.__('Upload').'" class="sub next">';
echo "</form>";
echo "</div>";
echo '</div>';


?>
