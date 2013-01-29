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

// Get if the user has simple mode
$simple_mode = get_db_value('simple_mode','tusuario','id_usuario',$config['id_user']);

echo "<ul>";

// Projects
if (give_acl($config["id_user"], 0, "PR") && $show_projects != MENU_HIDDEN){
    // Project
    if ($sec == "projects" )
	    echo "<li id='current' class='project'>";
    else
	    echo "<li class='project'>";


    echo "<a href='index.php?sec=projects&sec2=operation/projects/project'>".__('Projects')."</a></li>";
}

// Incidents
if (give_acl($config["id_user"], 0, "IR") && $show_incidents != MENU_HIDDEN){
    // Incident
    if ($sec == "incidents" )
	    echo "<li id='current' class='incident'>";
    else
	    echo "<li class='incident'>";
	if($simple_mode) {
		echo "<a href='index.php?sec=incidents&sec2=operation/incidents_simple/incidents'>".__('Incidents')."</a></li>";
	}
	else {
		echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident'>".__('Incidents')."</a></li>";
	}
}

// Inventory
if (give_acl($config["id_user"], 0, "VR") && (get_external_user($config["id_user"]) == false) && $show_inventory != MENU_HIDDEN) {
    // Incident
    if ($sec == "inventory" )
	    echo "<li id='current' class='inventory'>";
    else
	    echo "<li class='inventory'>";
    echo "<a href='index.php?sec=inventory&sec2=operation/inventories/inventory'>".__('Inventory')."</a></li>";
}

// Customers
if (give_acl($config["id_user"], 0, "VR") && (get_external_user($config["id_user"]) == false) && $show_customers != MENU_HIDDEN) {
    if ($sec == "customers" )
	    echo "<li id='current' class='customer'>";
    else
	    echo "<li class='customer'>";
    echo "<a href='index.php?sec=customers&sec2=operation/companies/company_detail'>".__('Customers')."</a></li>";
}

// KB
if (give_acl($config["id_user"], 0, "KR") && $show_kb != MENU_HIDDEN) {
	if ($sec == "kb" )
		echo "<li id='current' class='kb'>";
	else
		echo "<li class='kb'>";
	echo "<a href='index.php?sec=kb&sec2=operation/kb/browse'>".__('KB')."</a></li>";
}

// FILE RELEASES
if (give_acl($config["id_user"], 0, "KR")) {

	if ($show_file_releases != MENU_HIDDEN) {
		// File Releases
		if ($sec == "download" )
				echo "<li id='current' class='files'>";
		else
				echo "<li class='files'>";
		echo "<a href='index.php?sec=download&sec2=operation/download/browse'>".__('File Releases')."</a></li>";
	}
}

if ($show_people != MENU_HIDDEN) {
	// Users
	if ($sec == "users" )
		echo "<li id='current' class='people'>";
	else
		echo "<li class='people'>";
	echo "<a href='index.php?sec=users&sec2=operation/user_report/report_monthly'>".__('People')."</a></li>";
}

// Agenda
if (give_acl($config["id_user"], 0, "AR") && $show_agenda != MENU_HIDDEN) {
	// Agenda
	if (($sec == "agenda" ) OR ($sec == "todo"))
		echo "<li id='current' class='agenda'>";
	else
		echo "<li class='agenda'>";
	echo "<a href='index.php?sec=agenda&sec2=operation/agenda/agenda'>".__('Agenda')."</a></li>";
}

// Wiki
if (give_acl($config["id_user"], 0, "WR") && $show_wiki != MENU_HIDDEN) {
	// Wiki
	if ($sec == "wiki" )
		echo "<li id='current' class='wiki'>";
	else
		echo "<li class='wiki'>";
	echo "<a href='index.php?sec=wiki&sec2=operation/wiki/wiki'>" . __('Wiki') . "</a></li>";
}

// Setup
if (isset($config["id_user"]) && dame_admin($config["id_user"]) && $show_setup != MENU_HIDDEN) {
	// Setup
	if ($sec == "godmode" )
		echo "<li id='current' class='setup'>";
	else
		echo "<li class='setup'>";
	echo "<a href='index.php?sec=godmode&sec2=godmode/setup/setup'>".__('Setup')."</a></li>";
}

echo "</ul>";
?>
