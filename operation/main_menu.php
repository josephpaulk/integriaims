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

if (!isset($config["id_user"]))
	return;

echo "<ul>";

// Projects
if (give_acl($config["id_user"], 0, "PR") == 1){
    // Project
    if ($sec == "projects" )
	    echo "<li id='current'>";
    else
	    echo "<li>";
    echo "<a href='index.php?sec=projects&sec2=operation/projects/project'>".__('Projects')."</a></li>";
}

// Incidents
if (give_acl($config["id_user"], 0, "IR") == 1){
    // Incident
    if ($sec == "incidents" )
	    echo "<li id='current'>";
    else
	    echo "<li>";
    echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident'>".__('Incidents')."</a></li>";
}


// Inventory
if ((give_acl($config["id_user"], 0, "VR") == 1) AND (get_external_user($config["id_user"]) == false)) {
    // Incident
    if ($sec == "inventory" )
	    echo "<li id='current'>";
    else
	    echo "<li>";
    echo "<a href='index.php?sec=inventory&sec2=operation/inventories/inventory'>".__('Inventory')."</a></li>";
}

// KB
if (give_acl($config["id_user"], 0, "KR") == 1){
    if ($sec == "kb" )
	    echo "<li id='current'>";
    else
	    echo "<li>";
    echo "<a href='index.php?sec=kb&sec2=operation/kb/browse'>".__('KB')."</a></li>";

    if ($sec == "download" )
            echo "<li id='current'>";
    else
            echo "<li>";
    echo "<a href='index.php?sec=download&sec2=operation/download/browse'>".__('Downloads')."</a></li>";

}

// Users
if ($sec == "users" )
	echo "<li id='current'>";
else
	echo "<li>";
echo "<a href='index.php?sec=users&sec2=operation/users/user'>".__('Users')."</a></li>";

// TODO
if ($sec == "todo" )
	echo "<li id='current'>";
else
	echo "<li>";
echo "<a href='index.php?sec=todo&sec2=operation/todo/todo'>".__('Todo')."</a></li>";

// Agenda
if (give_acl($config["id_user"], 0, "AR") == 1){
    // Agenda
    if ($sec == "agenda" )
	    echo "<li id='current'>";
    else
	    echo "<li>";
    echo "<a href='index.php?sec=agenda&sec2=operation/agenda/agenda'>".__('Agenda')."</a></li>";
}

// Setup
if (isset($config["id_user"]))
	if (dame_admin($config["id_user"]) == 1){
	    // Setup
	    if ($sec == "godmode" )
	        echo "<li id='current'>";
	    else
	        echo "<li>";
	    echo "<a href='index.php?sec=godmode&sec2=godmode/setup/setup'>".__('Setup')."</a></li>";
	}

    echo "</ul>";
?>
