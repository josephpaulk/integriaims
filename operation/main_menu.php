<?PHP
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2010 Ártica Soluciones Tecnológicas
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

$show_projects = enterprise_hook ('get_menu_section_access', array ('projects'));

// Projects
if (give_acl($config["id_user"], 0, "PR") && $show_projects != 0){
    // Project
    if ($sec == "projects" )
	    echo "<li id='current' class='project'>";
    else
	    echo "<li class='project'>";


    echo "<a href='index.php?sec=projects&sec2=operation/projects/project'>".__('Projects')."</a></li>";
}

$show_incidents = enterprise_hook ('get_menu_section_access', array ('incidents'));

// Incidents
if (give_acl($config["id_user"], 0, "IR") && $show_incidents != 0){
    // Incident
    if ($sec == "incidents" )
	    echo "<li id='current' class='incident'>";
    else
	    echo "<li class='incident'>";
    echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident'>".__('Incidents')."</a></li>";
}

$show_inventory = enterprise_hook ('get_menu_section_access', array ('inventory'));

// Inventory
if (give_acl($config["id_user"], 0, "VR") && (get_external_user($config["id_user"]) == false) && $show_inventory != 0) {
    // Incident
    if ($sec == "inventory" )
	    echo "<li id='current' class='inventory'>";
    else
	    echo "<li class='inventory'>";
    echo "<a href='index.php?sec=inventory&sec2=operation/inventories/inventory'>".__('Inventory')."</a></li>";
}

$show_kb = enterprise_hook ('get_menu_section_access', array ('kb'));

// KB
if (give_acl($config["id_user"], 0, "KR") && $show_kb != 0){
	if ($sec == "kb" )
		echo "<li id='current' class='kb'>";
	else
		echo "<li class='kb'>";
	echo "<a href='index.php?sec=kb&sec2=operation/kb/browse'>".__('KB')."</a></li>";
}

$show_file_releases = enterprise_hook ('get_menu_section_access', array ('file_releases'));

// FILE RELEASES
if (give_acl($config["id_user"], 0, "KR")){

	if($show_file_releases != 0) {
		// File Releases
		if ($sec == "download" )
				echo "<li id='current' class='files'>";
		else
				echo "<li class='files'>";
		echo "<a href='index.php?sec=download&sec2=operation/download/browse'>".__('File Releases')."</a></li>";
	}
}

$show_people = enterprise_hook ('get_menu_section_access', array ('people'));

if($show_people != 0) {
	// Users
	if ($sec == "users" )
		echo "<li id='current' class='people'>";
	else
		echo "<li class='people'>";
	echo "<a href='index.php?sec=users&sec2=operation/user_report/report_monthly'>".__('People')."</a></li>";
}

$show_todo = enterprise_hook ('get_menu_section_access', array ('todo'));

if($show_todo != 0) {
	// TODO
	if ($sec == "todo" )
		echo "<li id='current' class='todo'>";
	else
		echo "<li class='todo'>";
	echo "<a href='index.php?sec=todo&sec2=operation/todo/todo'>".__('Todo')."</a></li>";
}

$show_agenda = enterprise_hook ('get_menu_section_access', array ('agenda'));

// Agenda
if (give_acl($config["id_user"], 0, "AR") && $show_agenda != 0){
	// Agenda
	if ($sec == "agenda" )
		echo "<li id='current' class='agenda'>";
	else
		echo "<li class='agenda'>";
	echo "<a href='index.php?sec=agenda&sec2=operation/agenda/agenda'>".__('Agenda')."</a></li>";
}

$show_setup = enterprise_hook ('get_menu_section_access', array ('setup'));
// Setup
if (isset($config["id_user"]) && dame_admin($config["id_user"]) && $show_setup != 0){
	// Setup
	if ($sec == "godmode" )
		echo "<li id='current' class='setup'>";
	else
		echo "<li class='setup'>";
	echo "<a href='index.php?sec=godmode&sec2=godmode/setup/setup'>".__('Setup')."</a></li>";
}

echo "</ul>";
?>
