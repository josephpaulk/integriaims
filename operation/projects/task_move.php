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

global $config;

if (check_login() != 0) {
    audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access", "Trying to access event viewer");
    require ("general/noaccess.php");
    exit;
}

// Get our main stuff
$id_project = get_parameter ("id_project", -1);
$id_task = get_parameter ("id_task", -1);

//TASK MOVE Operation
// PROJECT - People management
$project_manager = get_db_value ("id_owner", "tproject", "id", $id_project);
if ((give_acl($config["id_user"], 0, "PM")==1) OR ($project_manager == $config["id_user"])) {

    echo "<form name='project_move' method='POST' action='index.php?sec=projects&sec2=operation/projects/task&operation=move&id_project=$id_project&id_task=$id_task'>";
    echo "<h3>".__('Move this task to other project')."</h3>";
    echo '<table width="600" class="databox_color" cellpadding=4 cellspacing=4>';
    
    // Project combo
    echo '<tr><td class="datos"><b>'.__('Destination project').'</b>';
    echo '</td><td class="datos">';
    combo_projects_user ($config["id_user"], 'target_project');
    echo '</td><td class="datos">';
    echo '<input type="submit" class="sub create" name="accion" value="'.__('Move').'" border="0">';
    echo "</form></td></tr></table>";

}
