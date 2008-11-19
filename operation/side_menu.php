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

if (!isset($config["id_user"]))
	return;

// PROJECTS
if ($sec == "projects" && give_acl ($config["id_user"], 0, "PR")) {	
	$id_project = get_parameter ('id_project');
	echo "<div class='portlet'>";
	echo "<h3>".__('Projects')."</h3>";
	echo "<ul class='sidemenu'>";

	// Project overview
	if ($sec2 == "operation/projects/project_overview")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/project_overview'>".__('Project overview')."</a></li>";


	// Project detail
	if ($sec2 == "operation/projects/project")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/project'>".__('Project detail')."</a></li>";

	// Project tree
	if ($sec2 == "operation/projects/project_tree")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/project_tree'>".__('Project tree')."</a></li>";

	// Project create
	if (give_acl ($config['id_user'], 0, "PM")) {
		if ($sec2 == "operation/projects/project_detail" && !$id_project)
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=projects&sec2=operation/projects/project_detail'>".__('Create project')."</a></li>";
	}

	// View disabled projects
	if (($sec2 == "operation/projects/project") AND (isset($_REQUEST["view_disabled"])) )
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/project&view_disabled=1'>".__('Disabled projects')."</a></li>";


	// end of main Project options block
	echo "</ul>";
	echo "</div>";
	
	// Dynamic project sub options menu (PROJECT)
	$id_task = get_parameter ('id_task');
	if ($id_project) {
		echo "<br>";
		$project_manager = get_db_value ("id_owner", "tproject", "id", $id_project);

		echo "<div class='portlet'>";
		$project_title = substr(get_db_value ("name", "tproject", "id", $id_project), 0, 18);
		echo "<h3>".__('Project')." - $project_title ..</h3>";
		echo "<ul class='sidemenu'>";

		// Project detail
		if ($sec2 == "operation/projects/project_detail")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=projects&sec2=operation/projects/project_detail&id_project=$id_project'>".__('Project overview')."</a></li>";
		
		
		// Project detail
		if ($sec2 == "operation/projects/project_tracking")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=projects&sec2=operation/projects/project_tracking&id_project=$id_project'>".__('Project tracking')."</a></li>";
		
		// Tasks
		$task_number = get_tasks_count_in_project ($id_project);
		if ($task_number > 0) {
			if ($sec2 == "operation/projects/task")
				echo "<li id='sidesel'>";
			else
				echo "<li>";
			echo "<a href='index.php?sec=projects&sec2=operation/projects/task&id_project=$id_project'>".__('Task list')." ($task_number)</a></li>";
		}

		if (give_acl ($config["id_user"], 0, "PM") || $config["id_user"] == $project_manager) {
			// Create task
			if ($sec2 == "operation/projects/task_detail" && !$id_task)
				echo "<li id='sidesel'>";
			else
				echo "<li>";
			echo "<a href='index.php?sec=projects&sec2=operation/projects/task_detail&id_project=$id_project&operation=create'>".__('New task')."</a></li>";
		}

		// Gantt graph
		if ($sec2 == "operation/projects/gantt")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=projects&sec2=operation/projects/gantt&id_project=$id_project'>".__('Gantt graph')."</a></li>";

		// Milestones
		if ($sec2 == "operation/projects/milestones")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=projects&sec2=operation/projects/milestones&id_project=$id_project'>".__('Milestones')."</a></li>";

		// PROJECT - People management
		if (give_acl ($config["id_user"], 0, "PM") || $project_manager == $config["id_user"]) {
			if ($sec2 == "operation/projects/people_manager")
				echo "<li id='sidesel'>";
			else
				echo "<li>";
			echo "<a href='index.php?sec=projects&sec2=operation/projects/people_manager&id_task=-1&id_project=$id_project'>".__('People')."</a></li>";
		}

		// Workunits
		$totalhours = get_project_workunit_hours ($id_project);
		$totalwu = get_project_count_workunits ($id_project);
		if ($totalwu > 0){
			if ($sec2 == "operation/projects/task_workunit")
				echo "<li id='sidesel'>";
			else
				echo "<li>";
			echo "<a href='index.php?sec=projects&sec2=operation/projects/task_workunit&id_project=$id_project'>".__('Workunits');
			echo " ($totalhours ".__('Hours').")";
			echo "</a></li>";
		}

		// Files
		$numberfiles = give_number_files_project ($id_project);
		if ($numberfiles > 0){
			if ($sec2 == "operation/projects/task_files")
				echo "<li id='sidesel'>";
			else
				echo "<li>";
			echo "<a href='index.php?sec=projects&sec2=operation/projects/task_files&id_project=$id_project'>".__('Files')." ($numberfiles)";
			echo "</a></li>";
		}

		echo "</ul>";
		echo "</div>";
	}

	// Dynamic sub options menu (TASKS)
	if ($id_task) {
		echo "<br>";

		echo "<div class='portlet'>";
		$task_title = substr(get_db_value ("name", "ttask", "id", $id_task), 0, 19);
		echo "<h3>".__('Task')." - $task_title ..</h3>";
		echo "<ul class='sidemenu'>";

		// Task detail
		if ($sec2 == "operation/projects/task_detail")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=projects&sec2=operation/projects/task_detail&id_project=$id_project&id_task=$id_task&operation=view'>".__('Task detail')."</a></li>";

		// Task tracking
		if ($sec2 == "operation/projects/task_tracking")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=projects&sec2=operation/projects/task_tracking&id_project=$id_project&id_task=$id_task&operation=view'>".__('Task tracking')."</a></li>";

		// Add task workunit
		if ($sec2 == "operation/users/user_spare_workunit")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=projects&sec2=operation/users/user_spare_workunit&id_project=$id_project&id_task=$id_task'>".__('Add workunit')."</a></li>";

		// Add task file
		if ($sec2 == "operation/projects/task_attach_file")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=projects&sec2=operation/projects/task_attach_file&id_task=$id_task&id_project=$id_project'>".__('Add file')."</a></li>";

		// Task people_manager
		$project_manager = get_db_value ("id_owner", "tproject", "id", $id_project);
		if ((give_acl($config["id_user"], 0, "PM")==1) OR ($project_manager == $config["id_user"])) {
			if ($sec2 == "operation/projects/operation/projects/people_manager")
				echo "<li id='sidesel'>";
			else
				echo "<li>";
			echo "<a href='index.php?sec=projects&sec2=operation/projects/people_manager&id_project=$id_project&id_task=$id_task'>".__('People')."</a></li>";

			// Move this task
			if ($sec2 == "operation/projects/task_move")
				echo "<li id='sidesel'>";
			else
				echo "<li>";
			echo "<a href='index.php?sec=projects&sec2=operation/projects/task_move&id_task=$id_task&id_project=$id_project'>".__('Move task')."</a></li>";
		}

		// Workunits
		$totalhours = get_task_workunit_hours ($id_task);
		$totalwu = get_task_workunit_hours ($id_task);
		if ($totalwu > 0){
			if ($sec2 == "operation/projects/task_workunit")
				echo "<li id='sidesel'>";
			else
				echo "<li>";
			echo "<a href='index.php?sec=projects&sec2=operation/projects/task_workunit&id_project=$id_project&id_task=$id_task'>".__('Workunits');
			echo " ($totalhours ".__('Hours').")";
			echo "</a></li>";
		}

		// Incidents for this task
		$task_incidents = get_incident_task($id_task);
		if ( $task_incidents > 0){
			$task_incidents_wu = get_incident_task_workunit_hours ($id_task);
			if ($sec2 == "operation/projects/task_incidents")
				echo "<li id='sidesel'>";
			else
				echo "<li>";
			echo "<a href='index.php?sec=projects&sec2=operation/projects/task_incidents&id_project=$id_project&id_task=$id_task'>".__('Incidents');
			echo " ($task_incidents / $task_incidents_wu ".__('Hours').")";
			echo "</a></li>";
		}

		// Files
		$numberfiles = get_number_files_task ($id_task);
		if ($numberfiles > 0){
			if ($sec2 == "operation/projects/task_files")
				echo "<li id='sidesel'>";
			else
				echo "<li>";
			echo "<a href='index.php?sec=projects&sec2=operation/projects/task_files&id_project=$id_project&id_task=$id_task'>".__('Files')." ($numberfiles)";
			echo "</a></li>";
		}
		echo "</ul>";
		echo "</div>";
	}


}

// Project group manager
if (give_acl ($config["id_user"], 0, "PM") && $sec == "projects") {
	echo "<div class='portlet'>";
	echo "<h3 class='admin'>".__('Project groups')."</h3>";
	echo "<ul class='sidemenu'>";

	// Building overview
	if ($sec2=="operation/projects/project_group_detail")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/project_group_detail'>".__('Project groups')."</a></li>";

	echo "</ul>";
	echo "</div>";
}

// INCIDENTS
if ($sec == "incidents" && give_acl ($config['id_user'], 0, "IR")) {
	echo "<div class='portlet'>";
	echo "<h3>".__('Incidents')."</h3>";
	echo "<ul class='sidemenu'>";

	$id_incident = get_parameter ('id');

	// Incident overview
	if ($sec2 == "operation/incidents/incident")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident'>".__('Incidents overview')."</a></li>";

	if (give_acl ($config['id_user'], 0, "IW")) {
		// Incident creation
		if ($sec2 == 'operation/incidents/incident_detail')
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_detail' id='link_create_incident'>".__('Create incident')."</a></li>";
	}

	if ($sec2 == 'operation/incidents/incident') {
		echo '<li>';
		echo '<a href="" onclick="return false">'.__('Incident #').'</a>';
		echo '<form id="goto-incident-form">';
		print_input_text ('id', $id_incident ? $id_incident : '', '', 3, 10);
		echo '</form>';
		echo '</li>';
	}
	echo "</ul></div>";

	// Dynamic incident sub options menu
	echo "<br>";

	echo '<div class="portlet incident-menu" id="incident-menu-actions" style="display: none">';
	echo '<h3>'.__('Incident').' #<span class="id-incident-menu">';
	if ($id_incident)
		echo $id_incident;
	echo '</span></h3>';

	echo "<ul class='sidemenu'>";

	// Add workunit to incident
	if ($sec2 == "operation/incidents/incident_create_work")
		echo "<li id='sidesel'>";
	else
		echo '<li>';

	echo "<a id='incident-create-work' href='index.php?sec=incidents&sec2=operation/incidents/incident_create_work&id=$id_incident'>".__('Add workunit')."</a>";
	
	echo "</li>";

	// Add file to incident
	if ($sec2 == "operation/incidents/incident_attach_file")
		echo '<li id="sidesel">';
	else
		echo '<li id="incident-attach-file">';
	echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_attach_file&id=$id_incident'>".__('Add file')."</a>";
	echo "</li>";

	// Blockend
	echo "</ul>";
	echo "</div>";

	/* Users affected by the incident */
	echo '<div class="portlet incident-menu" id="incident-menu-details" style="display: none">';
	echo '<h2 onclick="toggleDiv (\'incident-details\')">'.__('Details for incident').' #<span class="id-incident-menu">';
	if ($id_incident)
		echo $id_incident;
	echo "</h2>";

	echo '<div id="incident-details">';
	if ($id_incident) {
		incident_details_list ($id_incident);
	}
	echo '</div></div>';

	/* Users affected by the incident */
	echo '<div class="portlet incident-menu" id="incident-menu-users" style="display: none">';
	echo '<h2 onclick="toggleDiv (\'incident-users\')">'.__('Users in incident').' #<span class="id-incident-menu">';
	if ($id_incident)
		echo $id_incident;
	echo "</h2>";

	echo '<div id="incident-users">';

	if ($id_incident) {
		incident_users_list ($id_incident);
	}

	echo "</div></div>";
}

// Indicent type editor
if (give_acl ($config["id_user"], 0, "IM") && $sec == "incidents") {
	echo "<div class='portlet'>";
	echo "<h3 class='admin'>".__('Incident types')."</h3>";
	echo "<ul class='sidemenu'>";

	// Building overview
	if ($sec2=="operation/incidents/types_detail")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=incidents&sec2=operation/incidents/type_detail'>".__('Incident types')."</a></li>";

	echo "</ul>";
	echo "</div>";
}

// INVENTORY
if ($sec == "inventory" && give_acl ($config['id_user'], 0, "VR")) {
	$id_inventory = (int) get_parameter ('id');
	echo "<div class='portlet'>";
	echo "<h3>".__('Inventory')."</h3>";
	echo "<ul class='sidemenu'>";
	// Incident overview
	if ($sec2 == "operation/inventories/inventory")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=inventory&sec2=operation/inventories/inventory'>".__('Inventory overview')."</a></li>";

	if (give_acl ($config["id_user"], 0, "VW")) {
		// Incident creation
		if ($sec2 == "operation/inventories/inventory_detail")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=inventory&sec2=operation/inventories/inventory_detail'>".__('Create inventory object')."</a></li>";
	}

	if ($sec2 == 'operation/inventories/inventory') {
		echo '<li>';
		echo '<a href="" onclick="return false">'.__('Inventory #').'</a>';
		echo '<form id="goto-inventory-form">';
		print_input_text ('id', $id_inventory ? $id_inventory : '', '', 3, 10);
		echo '</form>';
		echo '</li>';
	}
	echo "</ul>";

	echo "</div>";

	// Dynamic inventory sub options menu
	echo '<div class="portlet inventory-menu" id="inventory-menu-actions" style="display: none">';
	echo '<h3>'.__('Inventory').' #<span class="id-inventory-menu">';
	if ($id_inventory)
		echo $id_inventory;
	echo '</span></h3>';

	echo "<ul class='sidemenu'>";
	echo '<li>';
	echo '<a id="inventory-create-incident" href="index.php?sec=incidents&sec2=operation/incidents/incident_detail&id_inventory='.$id_inventory.'">'.__('Create incident').'</a>';

	echo '</li>';

	echo "</ul>";
	echo "</div>";
}

// CONTRACTS
if ($sec == "inventory" && give_acl ($config["id_user"], 0, "IM")) {
	echo "<div class='portlet'>";

	// Contract
	echo "<h3>".__('Contracts')."</h3>";
	echo "<ul class='sidemenu'>";

	// Contact overview
	if (($sec2=="operation/contracts/contract_detail") AND (!isset($_GET["create"])))
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=inventory&sec2=operation/contracts/contract_detail'>".__('Contract overview')."</a></li>";

	echo "</ul>";
	echo "</div>";
}

// CONTACTS
if ($sec == "inventory" && give_acl ($config["id_user"], 0, "IM")) {
	echo "<div class='portlet'>";


	echo "<h3 class='admin'>".__('Contacts')."</h3>";
	echo "<ul class='sidemenu'>";

	// Contact overview
	if (($sec2=="operation/contacts/contact_detail") AND (!isset($_GET["create"])))
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=inventory&sec2=operation/contacts/contact_detail'>".__('Contact overview')."</a></li>";

	echo "</ul>";
	echo "</div>";
}

// COMPANIES
if ($sec == "inventory" && give_acl ($config["id_user"], 0, "IM")) {
	echo "<div class='portlet'>";

	// Contract
	echo "<h3 class='admin'>".__('Companies')."</h3>";
	echo "<ul class='sidemenu'>";

	// Company
	if (($sec2=="operation/companies/company_detail") AND (!isset($_GET["create"])))
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=inventory&sec2=operation/companies/company_detail'>".__('Company overview')."</a></li>";

	// Company roles
	if (($sec2=="operation/companies/company_role") AND (!isset($_GET["create"])))
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=inventory&sec2=operation/companies/company_role'>".__('Company roles')."</a></li>";

	echo "</ul>";
	echo "</div>";

}

// SLA's
if ($sec == "inventory" && give_acl ($config["id_user"], 0, "IM")) {
	echo "<div class='portlet'>";
	echo "<h3 class='admin'>".__('SLA')."</h3>";
	echo "<ul class='sidemenu'>";

	// Building overview
	if ($sec2=="operation/slas/sla_detail")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=inventory&sec2=operation/slas/sla_detail'>".__('SLA Management')."</a></li>";

	echo "</ul>";
	echo "</div>";
}

// MANUFACTURER
if ($sec == "inventory" && give_acl ($config["id_user"], 0, "IM")) {
	echo "<div class='portlet'>";
	echo "<h3 class='admin'>".__('Manufacturers')."</h3>";
	echo "<ul class='sidemenu'>";

	// Building overview
	if ($sec2=="operation/manufacturers/manufacturer_detail")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=inventory&sec2=operation/manufacturers/manufacturer_detail'>".__('Manufacturer overview')."</a></li>";

	echo "</ul>";
	echo "</div>";
}

// BUILDINGS
if ($sec == "inventory" && give_acl ($config["id_user"], 0, "IM")) {
	echo "<div class='portlet'>";
	echo "<h3 class='admin'>".__('Buildings')."</h3>";
	echo "<ul class='sidemenu'>";

	// Building overview
	if ($sec2=="operation/buildings/building_detail")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=inventory&sec2=operation/buildings/building_detail'>".__('Building overview')."</a></li>";

	echo "</ul>";
	echo "</div>";
}

// Product types
if ($sec == "inventory" && give_acl($config["id_user"], 0, "VM")) {
	echo "<div class='portlet'>";
	echo "<h3 class='admin'>".__('Products')."</h3>";
	echo "<ul class='sidemenu'>";

	// Building overview
	if ($sec2=="operation/inventories/manage_prod")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=inventory&sec2=operation/inventories/manage_prod'>".__('Manage products')."</a></li>";

	echo "</ul>";
	echo "</div>";
}

// KNOWLEDGE BASE (KB)
if ($sec == "kb" && give_acl ($config["id_user"], 0, "KR")) {
	echo "<div class='portlet'>";
	echo "<h3>".__('Knowledge Base')."</h3>";
	echo "<ul class='sidemenu'>";

	// KB Browser
	if ($sec2 == "operation/kb/browse")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=kb&sec2=operation/kb/browse'>".__('Browse')."</a></li>";

	if  (give_acl($config["id_user"], 0, "KW")) {
		// KB Add
		if (($sec2 == "operation/kb/manage_data") AND (isset($_GET["create"])))
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=kb&sec2=operation/kb/manage_data&create=1'>".__('Create KB item')."</a></li>";

		// Manage KB
		if (($sec2 == "operation/kb/manage_data") AND (!isset($_GET["create"])))
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=kb&sec2=operation/kb/manage_data'>".__('Manage KB item')."</a></li>";

		// KB Manage Cat.
		if ($sec2 == "operation/kb/manage_cat")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=kb&sec2=operation/kb/manage_cat'>".__('Manage Categories')."</a></li>";
	}


	echo "</ul>";
	echo "</div>";
}

// TODO
if ($sec == "todo")  {
	echo "<div class='portlet'>";
	echo "<h3>".__('To-Do')."</h3>";
	echo "<ul class='sidemenu'>";

	// Todo overview
	if (($sec2 == "operation/todo/todo") && (!isset($_GET["operation"])))
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=todo&sec2=operation/todo/todo'>".__('To-Do')."</a></li>";

	// Todo overview of another users
	if (($sec2 == "operation/todo/todo") && (isset($_GET["operation"])) && ($_GET["operation"] == "notme"))
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=todo&sec2=operation/todo/todo&operation=notme'>".__('To-Do another people')."</a></li>";

	// Todo create
	if (($sec2 == "operation/todo/todo") && (isset($_GET["operation"])) && ($_GET["operation"] == "create"))
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=todo&sec2=operation/todo/todo&operation=create'>".__('Add todo')."</a></li>";
	echo "</ul>";
	echo "</div>";
}

if ($sec == "godmode") {
	echo "<div class='portlet'>";
	echo "<h3>".__('Setup')."</h3>";
	echo "<ul class='sidemenu'>";

	// Main Seetup
	if ($sec2 == "godmode/setup/setup")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=godmode&sec2=godmode/setup/setup'>".__('General setup')."</a></li>";

	// Mail Seetup
	if ($sec2 == "godmode/setup/setup_mail")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=godmode&sec2=godmode/setup/setup_mail'>".__('Mail setup')."</a></li>";


	// Update Manager
	if ($sec2 == "godmode/updatemanager/main")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=godmode&sec2=godmode/updatemanager/main'>".__('Update')."</a></li>";

	// Setup Update Manager
	if ($sec2 == "godmode/updatemanager/settings")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=godmode&sec2=godmode/updatemanager/settings'>".__('Configure updates')."</a></li>";

	// Link management
	if ($sec2 == "godmode/setup/links")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=godmode&sec2=godmode/setup/links'>".__('Links')."</a></li>";

	// Event management
	if ($sec2 == "godmode/setup/event")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=godmode&sec2=godmode/setup/event'>".__('System events')."</a></li>";

	echo "</ul>";
	echo "</div>";
}

if ($sec == "users") {
	echo "<div class='portlet'>";
	echo "<h3>".__('Users defined in Integria')."</h3>";
	echo "<ul class='sidemenu'>";

		// View users
		if ($sec2 == "operation/users/user")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=users&amp;sec2=operation/users/user'>".__('View Users')."</a></li>";

		// Edit my user
		if ($sec2 == "operation/users/user_edit")
			if (isset ($_REQUEST['id']) && $_REQUEST['id'] == $config['id_user'])
				echo "<li id='sidesel'>";
			else
				echo "<li>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=users&sec2=operation/users/user_edit&id=".$config['id_user']."'>".__('Edit my user')."</a></li>";

	if (give_acl ($config["id_user"], 0, "TW")) {
		// Add spare workunit
		if ($sec2 == "operation/users/user_spare_workunit")
		echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=users&sec2=operation/users/user_spare_workunit'>".__('Spare workunit')."</a></li>";


		$now = date("Y-m-d H:i:s");
		$now_year = date("Y");
		$now_month = date("m");

		// My workunit report
		if ($sec2 == "operation/users/user_workunit_report")
		echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=users&sec2=operation/user_report/monthly&month=$now_month&year=$now_year&id=".$config['id_user']."'>".__('Workunit report')."</a></li>";

		// My tasks
		if ($sec2 == "operation/users/user_task_assigment")
		echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=users&sec2=operation/users/user_task_assigment'>".__( "My task assigments")."</a></li>";
		
		echo "</ul>";
	}
	
	echo "</div>";
	
	if  ((give_acl($config["id_user"], 0, "PR")) OR  (give_acl($config["id_user"], 0, "IR"))) {
		echo "<div class='portlet'>";
		echo "<h3>".__('User reporting')."</h3>";
		echo "<ul class='sidemenu'>";

		// Full report 
		if ($sec2 == "operation/user_report/report_full")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=users&sec2=operation/user_report/report_full'>".__('Full  report')."</a></li>";

		// Basic report (monthly)
		if ($sec2 == "operation/user_report/report_monthly")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=users&sec2=operation/user_report/report_monthly'>".__('Montly report')."</a></li>";

		// Basic report (weekly)
		if ($sec2 == "operation/user_report/report_weekly")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=users&sec2=operation/user_report/report_weekly'>".__('Weekly report')."</a></li>";

		// Basic report (annual)
		if ($sec2 == "operation/user_report/report_annual")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=users&sec2=operation/user_report/report_annual'>".__('Annual report')."</a></li>";

		// View vacations
		if ($sec2 == "operation/projects/task_workunit")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=users&sec2=operation/projects/task_workunit&id_project=-1&id_task=-1'>".__('View vacations')."</a></li>";

		echo "</ul></div>";
	}

	if (give_acl($config["id_user"], 0, "UM")){
		echo "<div class='portlet'>";
		echo "<h3>".__('User management')."</h3>";
		echo "<ul class='sidemenu'>";

		// Usermanager
		if ($sec2 == "godmode/usuarios/lista_usuarios")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=users&sec2=godmode/usuarios/lista_usuarios'>".__('Manage users')."</a></li>";

		// Rolemanager
		if ($sec2 == "godmode/usuarios/role_manager")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=users&sec2=godmode/usuarios/role_manager'>".__('Manage roles')."</a></li>";

		// Group manager
		if ($sec2 == "godmode/grupos/lista_grupos")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=users&sec2=godmode/grupos/lista_grupos'>".__('Manage Groups')."</a></li>";
		
		enterprise_include ("operation/sidemenu_user_mgmt.php");
		
		echo "</ul>";
		echo "</div>";
	}
}

// Testing boxes for side menus
$user_row = get_db_row ("tusuario", "id_usuario", $config['id_user']);

$avatar = $user_row["avatar"];
$realname = $user_row["nombre_real"];
$email = $user_row["direccion"];
$description = $user_row["comentarios"];
$userlang = $user_row["lang"];
$telephone = $user_row["telefono"];

$now = date("Y-m-d H:i:s");
$now_year = date("Y");
$now_month = date("m");
$working_month = get_parameter ("working_month", $now_month);
$working_year = get_parameter ("working_year", $now_year);

echo '<div class="portlet">';
echo '<a href="javascript:;" onclick="$(\'#userdiv\').slideToggle (); return false">';
echo '<h2>'.__('User info').'</h2>';
echo '</a>';
echo '<div class="portletBody" id="userdiv">';

echo '<img src="images/avatars/'.$avatar.'_small.png" style="float: left" />';
echo '<a href="index.php?sec=users&sec2=operation/users/user_edit&id='.$config['id_user'].'">';
echo '<strong>'.$config['id_user'].'</strong>';
echo '</a><br/>';
echo "<em>".$realname."</em><br />";
echo __('Phone').": $telephone <br />";
echo __('E-mail').':</strong> '.$email.'<br />';

// Link to workunit calendar (month)
echo '<a href="index.php?sec=users&sec2=operation/user_report/monthly&month='.$now_month.'&year='.$now_year.'&id='.$config['id_user'].'" />';
echo '<img src="images/clock.png" title="'.__('Workunit report').'" /></a>';

if (give_acl ($config["id_user"], 0, "PR")) {
	// Link to project graph
	echo "&nbsp;&nbsp;";
	echo "<a href='index.php?sec=users&sec2=operation/user_report/monthly_graph&month=$working_month&year=$working_year&id=".$config['id_user']."'>";
	echo '<img src="images/chart_bar.png" title="'.__('Project distribution').'"></a>';

	// Link to Work user spare inster
	echo "&nbsp;&nbsp;";
	echo '<a href="index.php?sec=users&sec2=operation/users/user_spare_workunit">';
	echo '<img src="images/award_star_silver_1.png" title="'.__('Workunit').'"></a>';

	// Week Workunit meter
	echo "&nbsp;&nbsp;";
	$begin_week = week_start_day ();
	$begin_week .= " 00:00:00";
	$end_week = date ('Y-m-d H:i:s', strtotime ("$begin_week + 1 week"));
	$total_hours = 5 * $config["hours_perday"];
	$sql = sprintf ('SELECT SUM(duration)
		FROM tworkunit WHERE timestamp > "%s"
		AND timestamp < "%s"
		AND id_user = "%s"',
		$begin_week, $end_week, $config['id_user']);
	$week_hours = get_db_sql ($sql);
	$ratio = $week_hours." ".__('over')." ".$total_hours;
	if ($week_hours < $total_hours)
		echo '<img src="images/exclamation.png" title="'.__('Week workunit time not fully justified').' - '.$ratio.'" />';
	else
		echo '<img src="images/heart.png" title="'.__('Week workunit are fine').' - '.$ratio.'">';
}

echo '</div></div>';
// End of user box
?>
