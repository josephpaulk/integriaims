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

$id_user = $_SESSION["id_usuario"];

if (isset($_GET["sec"]))
	$sec = $_GET["sec"];
else
	$sec = "";

if (isset($_GET["sec2"]))
	$sec2 = $_GET["sec2"];
else
	$sec2 = "";


// PROJECTS
if ($sec == "projects"){
	echo "<div class='portlet'>";
	echo "<h3>".lang_string ('projects')."</h3>";
	echo "<ul class='sidemenu'>";

	// Project overview
	if ($sec2 == "operation/projects/project")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/project'>".lang_string ('project_overview')."</a></li>";

	// Project tree
	if ($sec2 == "operation/projects/project_tree")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/project_tree'>".lang_string("Project tree")."</a></li>";

	// Project create
	if (give_acl($id_user, 0, "PM")){
		if (($sec2 == "operation/projects/project_detail&insert_form") AND (isset($_GET["insert_form"])) )
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=projects&sec2=operation/projects/project_detail&insert_form'>".lang_string ('create_project')."</a></li>";
	}

	// View disabled projects
	if (($sec2 == "operation/projects/project") AND (isset($_GET["view_disabled"])) )
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/project&view_disabled=1'>".lang_string("Disabled projects")."</a></li>";


	// end of main Project options block
	echo "</ul>";
	echo "</div>";

	// Dynamic project sub options menu (PROJECT)
	$id_project = give_parameter_get("id_project",-1);
	if (($id_project != -1) AND ($id_project != "")){
		echo "<br>";
		$project_manager = give_db_value ("id_owner", "tproject", "id", $id_project);

		echo "<div class='portlet'>";
		$project_title = substr(give_db_value ("name", "tproject", "id", $id_project), 0, 18);
		echo "<h3>".lang_string ('project')." - $project_title ..</h3>";
		echo "<ul class='sidemenu'>";

		// Project detail
		if ($sec2 == "operation/projects/project_detail")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=projects&sec2=operation/projects/project_detail&id_project=$id_project'>".lang_string ('project_overview')."</a></li>";


		if ((give_acl($config["id_user"], 0, "PM") ==1) OR ($config["id_user"] == $project_manager )) {
			// Create task
			if ($sec2 == "operation/projects/task_detail")
				echo "<li id='sidesel'>";
			else
				echo "<li>";
			echo "<a href='index.php?sec=projects&sec2=operation/projects/task_detail&id_project=$id_project&operation=create'>".lang_string ('create_task')."</a></li>";
		}
		// Tasks
		$task_number =  give_number_tasks ($id_project);
		if ($task_number > 0){
			if ($sec2 == "operation/projects/task")
				echo "<li id='sidesel'>";
			else
				echo "<li>";
			echo "<a href='index.php?sec=projects&sec2=operation/projects/task&id_project=$id_project'>".lang_string ('task_list')." ($task_number)</a></li>";
		}

		// Gantt graph
		if ($sec2 == "operation/projects/gantt")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=projects&sec2=operation/projects/gantt&id_project=$id_project'>".lang_string("Gantt graph")."</a></li>";

		// Milestones
		if ($sec2 == "operation/projects/milestones")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=projects&sec2=operation/projects/milestones&id_project=$id_project'>".lang_string("milestones")."</a></li>";

		// PROJECT - People management
		if ((give_acl($config["id_user"], 0, "PM")==1) OR ($project_manager == $config["id_user"])) {
			if ($sec2 == "operation/projects/people_manager")
				echo "<li id='sidesel'>";
			else
				echo "<li>";
			echo "<a href='index.php?sec=projects&sec2=operation/projects/people_manager&id_task=-1&id_project=$id_project'>".lang_string ('people')."</a></li>";
		}

		// Workunits
		$totalhours =  give_hours_project ($id_project);
		$totalwu =  give_wu_project ($id_project);
		if ($totalwu > 0){
			if ($sec2 == "operation/projects/task_workunit")
				echo "<li id='sidesel'>";
			else
				echo "<li>";
			echo "<a href='index.php?sec=projects&sec2=operation/projects/task_workunit&id_project=$id_project'>".lang_string ('workunits');
			echo " ( $totalhours ".lang_string ('hr')." )";
			echo "</a></li>";
		}

		echo "</ul>";
		echo "</div>";
	}

	// Dynamic sub options menu (TASKS)
	$id_task = give_parameter_get("id_task",-1);
	if (($id_task != -1) and ($id_task != "")){
		echo "<br>";

		echo "<div class='portlet'>";
		$task_title = substr(give_db_value ("name", "ttask", "id", $id_task), 0, 19);
		echo "<h3>".lang_string ('task')." - $task_title ..</h3>";
		echo "<ul class='sidemenu'>";

		// Task detail
		if ($sec2 == "operation/projects/task_detail")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=projects&sec2=operation/projects/task_detail&id_project=$id_project&id_task=$id_task&operation=view'>".lang_string ('task_detail')."</a></li>";

		// Task tracking
		if ($sec2 == "operation/projects/task_trackin g")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=projects&sec2=operation/projects/task_tracking&id_project=$id_project&id_task=$id_task&operation=view'>".lang_string("Task tracking")."</a></li>";

		// Add task workunit
		if ($sec2 == "operation/projects/task_create_work")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=projects&sec2=operation/projects/task_create_work&id_task=$id_task&id_project=$id_project'>".lang_string ('add_workunit')."</a></li>";

		// Add task file
		if ($sec2 == "operation/projects/task_attach_file")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=projects&sec2=operation/projects/task_attach_file&id_task=$id_task&id_project=$id_project'>".lang_string ('add_file')."</a></li>";

		// Task people_manager
		$project_manager = give_db_value ("id_owner", "tproject", "id", $id_project);
		if ((give_acl($config["id_user"], 0, "PM")==1) OR ($project_manager == $config["id_user"])) {
			if ($sec2 == "operation/projects/operation/projects/people_manager")
				echo "<li id='sidesel'>";
			else
				echo "<li>";
			echo "<a href='index.php?sec=projects&sec2=operation/projects/people_manager&id_project=$id_project&id_task=$id_task'>".lang_string ('people')."</a></li>";

			// Move this task
			if ($sec2 == "operation/projects/task_move")
				echo "<li id='sidesel'>";
			else
				echo "<li>";
			echo "<a href='index.php?sec=projects&sec2=operation/projects/task_move&id_task=$id_task&id_project=$id_project'>".lang_string("Move task")."</a></li>";
		}

		// Workunits
		$totalhours = give_hours_task ($id_task);
		$totalwu = give_wu_task ($id_task);
		if ($totalwu > 0){
			if ($sec2 == "operation/projects/task_workunit")
				echo "<li id='sidesel'>";
			else
				echo "<li>";
			echo "<a href='index.php?sec=projects&sec2=operation/projects/task_workunit&id_project=$id_project&id_task=$id_task'>".lang_string ('workunits');
			echo " ($totalhours ".lang_string ('hr').")";
			echo "</a></li>";
		}

		// Files
		$numberfiles = give_number_files_task ($id_task);
		if ($numberfiles > 0){
			if ($sec2 == "operation/projects/task_files")
				echo "<li id='sidesel'>";
			else
				echo "<li>";
			echo "<a href='index.php?sec=projects&sec2=operation/projects/task_files&id_project=$id_project&id_task=$id_task'>".lang_string ('files')." ($numberfiles)";
			echo "</a></li>";
		}
		echo "</ul>";
		echo "</div>";
	}


}

// Project group manager
if ((give_acl($config["id_user"], 0, "PM")==1) AND ($sec == "projects")) {
	echo "<div class='portlet'>";
	echo "<h3 class='admin'>".lang_string ('Project groups')."</h3>";
	echo "<ul class='sidemenu'>";

	// Building overview
	if ($sec2=="operation/projects/project_group_detail")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=projects&sec2=operation/projects/project_group_detail'>".lang_string ('Project groups')."</a></li>";

	echo "</ul>";
	echo "</div>";
}


// INCIDENTS
if ($sec == "incidents") {
	echo "<div class='portlet'>";
	echo "<h3>".lang_string ('incidents')."</h3>";
	echo "<ul class='sidemenu'>";

	// Incident overview
	if ($sec2 == "operation/incidents/incident")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident'>".lang_string ('incidents_overview')."</a></li>";

	if (give_acl($_SESSION["id_usuario"], 0, "IW")==1) {
		// Incident creation
		if (isset($_GET["insert_form"]))
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_detail' id='link_create_incident'>".lang_string ('create_incident')."</a></li>";
	}

	echo "</ul></div>";

	// Dynamic incident sub options menu
	$id_incident = get_parameter ('id');
	echo "<br>";
	if ($id_incident)
		echo '<div class="portlet" id="incident-menu"><h3>'.lang_string ('incident').' #'.$id_incident.'</h3>';
	else
		echo '<div class="portlet" id="incident-menu" style="display: none"><h3></h3>';
	echo "<ul class='sidemenu'>";

	// Incident detail
	if ($id_incident) {
		if ($sec2 == "operation/incidents/incident_detail")
			echo '<li id="sidesel">';
		else
			echo '<li id="incident-detail">';
		echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_detail&id=$id_incident'>".lang_string ('incident_detail')."</a></li>";
	}

	// Add workunit to incident
	if ($sec2 == "operation/incidents/incident_create_work")
		echo "<li id='sidesel'>";
	else
		echo '<li id="incident-create-work">';
	if ($id_incident)
		echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_create_work&id=$id_incident'>".lang_string ('add_workunit')."</a></li>";

	// Add file to incident
	if ($sec2 == "operation/incidents/incident_attach_file")
		echo '<li id="sidesel">';
	else
		echo '<li id="incident-attach-file">';
	if ($id_incident)
		echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_attach_file&id=$id_incident'>".lang_string ('add_file')."</a></li>";

	// Incident workunits
	if ($id_incident) {
		$timeused = give_hours_incident ($id_incident);
		$in_wu = give_wu_incident ($id_incident);
		if ($in_wu > 0) {
			if ($sec2 == "operation/incidents/incident_workunits")
				echo "<li id='sidesel'>";
			else
				echo "<li>";

			echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_workunits&id=$id_incident'>".lang_string ('workunits_view');
			echo " ($timeused ".lang_string ('hr').")";
			echo "</a></li>";
		}

		// Incident files
		$file_number = give_number_files_incident ($id_incident);
		if ($file_number > 0) {
			if ($sec2 == "operation/incidents/incident_files")
				echo "<li id='sidesel'>";
			else
				echo "<li>";

			echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_files&id=$id_incident'>".lang_string ('incident_files');
			echo " ($file_number)";
			echo "</a></li>";
		}
	}
	// Blockend
	echo "</ul>";
	echo "</div>";

}

// Indicent type editor
if ((give_acl($config["id_user"], 0, "IM")==1) AND ($sec == "incidents")) {
	echo "<div class='portlet'>";
	echo "<h3 class='admin'>".lang_string ('Incident types')."</h3>";
	echo "<ul class='sidemenu'>";

	// Building overview
	if ($sec2=="operation/incidents/types_detail")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=incidents&sec2=operation/incidents/type_detail'>".lang_string ('Incident types')."</a></li>";

	echo "</ul>";
	echo "</div>";
}

// INVENTORY
if ($sec == "inventory") {
	echo "<div class='portlet'>";
	echo "<h3>".lang_string ('Inventory')."</h3>";
	echo "<ul class='sidemenu'>";
	// Incident overview
	if ($sec2 == "operation/inventories/inventory")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=inventory&sec2=operation/inventories/inventory'>".lang_string ('Inventory overview')."</a></li>";

	if (give_acl ($config["id_user"], 0, "IW")) {
		// Incident creation
		if ($sec2 == "operation/inventories/inventory_detail")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=inventory&sec2=operation/inventories/inventory_detail'>".lang_string ('Create inventory object')."</a></li>";
	}

	echo "</ul>";
	echo "</div>";
}

// CONTRACTS
if ((give_acl($config["id_user"], 0, "IM")==1) AND  ($sec == "inventory")) {
	echo "<div class='portlet'>";

	// Contract
	echo "<h3>".lang_string ('Contracts')."</h3>";
	echo "<ul class='sidemenu'>";

	// Contact overview
	if (($sec2=="operation/contracts/contract_detail") AND (!isset($_GET["create"])))
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=inventory&sec2=operation/contracts/contract_detail'>".lang_string ('Contract overview')."</a></li>";

	// Create new contract
/*
	if (give_acl($config["id_user"], 0, "IW")==1) {
		if (($sec2=="operation/contracts/contract_detail") AND (isset($_GET["create"])))
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=inventory&sec2=operation/contracts/contract_detail&create=1'>".lang_string ('Create contract')."</a></li>";
		
	}
*/

	echo "</ul>";
	echo "</div>";
}

// CONTACTS
if (give_acl ($config["id_user"], 0, "IM") && $sec == "inventory") {
	echo "<div class='portlet'>";


	echo "<h3 class='admin'>".lang_string ('Contacts')."</h3>";
	echo "<ul class='sidemenu'>";

	// Contact overview
	if (($sec2=="operation/contacts/contact_detail") AND (!isset($_GET["create"])))
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=inventory&sec2=operation/contacts/contact_detail'>".lang_string ('Contact overview')."</a></li>";

	echo "</ul>";
	echo "</div>";
}

// COMPANIES
if ((give_acl($config["id_user"], 0, "IM")==1) AND  ($sec == "inventory")) {
	echo "<div class='portlet'>";

	// Contract
	echo "<h3 class='admin'>".lang_string ('Companies')."</h3>";
	echo "<ul class='sidemenu'>";

	// Company
	if (($sec2=="operation/companies/company_detail") AND (!isset($_GET["create"])))
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=inventory&sec2=operation/companies/company_detail'>".lang_string ('Company overview')."</a></li>";

	// Company roles
	if (($sec2=="operation/companies/company_role") AND (!isset($_GET["create"])))
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=inventory&sec2=operation/companies/company_role'>".lang_string ('Company roles')."</a></li>";

	echo "</ul>";
	echo "</div>";

}

// SLA's
if ((give_acl($config["id_user"], 0, "IM")==1) AND ($sec == "inventory")) {
	echo "<div class='portlet'>";
	echo "<h3 class='admin'>".lang_string ('SLA')."</h3>";
	echo "<ul class='sidemenu'>";

	// Building overview
	if ($sec2=="operation/inventory/sla_detail")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=inventory&sec2=operation/inventory/sla_detail'>".lang_string ('SLA Management')."</a></li>";

	echo "</ul>";
	echo "</div>";
}

// MANUFACTURER
if ((give_acl($config["id_user"], 0, "IM")==1) AND ($sec == "inventory")) {
	echo "<div class='portlet'>";
	echo "<h3 class='admin'>".lang_string ('Manufacturers')."</h3>";
	echo "<ul class='sidemenu'>";

	// Building overview
	if ($sec2=="operation/inventory/manufacturer_detail")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=inventory&sec2=operation/inventory/manufacturer_detail'>".lang_string ('Manufacturer overview')."</a></li>";

	echo "</ul>";
	echo "</div>";
}

// BUILDINGS
if ((give_acl($config["id_user"], 0, "IM")==1) AND ($sec == "inventory")) {
	echo "<div class='portlet'>";
	echo "<h3 class='admin'>".lang_string ('Buildings')."</h3>";
	echo "<ul class='sidemenu'>";

	// Building overview
	if ($sec2=="operation/inventory/building_detail")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=inventory&sec2=operation/inventory/building_detail'>".lang_string ('Building overview')."</a></li>";

	echo "</ul>";
	echo "</div>";
}

// KNOWLEDGE BASE (KB)
if (($sec == "kb") AND (give_acl($config["id_user"], 0, "KR"))) {
	echo "<div class='portlet'>";
	echo "<h3>".lang_string ("Knowledge Base")."</h3>";
	echo "<ul class='sidemenu'>";

	// KB Browser
	if ($sec2 == "operation/kb/browse")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=kb&sec2=operation/kb/browse'>".lang_string ("Browse")."</a></li>";

	if  (give_acl($config["id_user"], 0, "KW")) {
		// KB Add
		if ($sec2 == "operation/kb/manage_data")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=kb&sec2=operation/kb/manage_data'>".lang_string ("Manage KB item")."</a></li>";

		// KB Manage Cat.
		if ($sec2 == "operation/kb/manage_cat")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=kb&sec2=operation/kb/manage_cat'>".lang_string ("Manage Categories")."</a></li>";

		// KB Manage Prod.
		if ($sec2 == "operation/kb/manage_prod")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=kb&sec2=operation/kb/manage_prod'>".lang_string ("Manage Products")."</a></li>";

	}


	echo "</ul>";
	echo "</div>";
}

// TODO
if ($sec == "todo")  {
	echo "<div class='portlet'>";
	echo "<h3>".lang_string ('todo')."</h3>";
	echo "<ul class='sidemenu'>";

	// Todo overview
	if (($sec2 == "operation/todo/todo") && (!isset($_GET["operation"])))
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=todo&sec2=operation/todo/todo'>".lang_string ('todo')."</a></li>";

	// Todo overview of another users
	if (($sec2 == "operation/todo/todo") && (isset($_GET["operation"])) && ($_GET["operation"] == "notme"))
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=todo&sec2=operation/todo/todo&operation=notme'>".lang_string ("todo_notme")."</a></li>";

	// Todo create
	if (($sec2 == "operation/todo/todo") && (isset($_GET["operation"])) && ($_GET["operation"] == "create"))
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=todo&sec2=operation/todo/todo&operation=create'>".lang_string ('add_todo')."</a></li>";
	echo "</ul>";
	echo "</div>";
}

if ($sec == "godmode") {
	echo "<div class='portlet'>";
	echo "<h3>".lang_string ("Setup")."</h3>";
	echo "<ul class='sidemenu'>";

	// Main Seetup
	if ($sec2 == "godmode/setup/setup")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=godmode&sec2=godmode/setup/setup'>".lang_string ("Setup")."</a></li>";

	// Incident management per task
	if ($sec2 == "godmode/setup/incident")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=godmode&sec2=godmode/setup/incident'>".lang_string ("Incident SLA")."</a></li>";

	// Link management
	if ($sec2 == "godmode/setup/links")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=godmode&sec2=godmode/setup/links'>".lang_string ("Links")."</a></li>";

	// Event management
	if ($sec2 == "godmode/setup/event")
		echo "<li id='sidesel'>";
	else
		echo "<li>";
	echo "<a href='index.php?sec=godmode&sec2=godmode/setup/event'>".lang_string ("System events")."</a></li>";

	echo "</ul>";
	echo "</div>";
}



if ($sec == "users"){

echo "<div class='portlet'>";
	echo "<h3>".lang_string ('users')."</h3>";
	echo "<ul class='sidemenu'>";

		// View users
		if ($sec2 == "operation/users/user")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=users&amp;sec2=operation/users/user'>".lang_string ("view_users")."</a></li>";

		// Edit my user
		if ($sec2 == "operation/users/user_edit")
		echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=users&sec2=operation/users/user_edit&ver=".$_SESSION["id_usuario"]."'>".lang_string("Edit my user")."</a></li>";

		// Add spare workunit
		if ($sec2 == "operation/users/user_spare_workunit")
		echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=users&sec2=operation/users/user_spare_workunit'>".lang_string("Spare Workunit")."</a></li>";


		$now = date("Y-m-d H:i:s");
		$now_year = date("Y");
		$now_month = date("m");

		// My workunit report
		if ($sec2 == "operation/users/user_workunit_report")
		echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=users&sec2=operation/user_report/monthly&month=$now_month&year=$now_year&id=$id_user'>".lang_string ('work_unit_report')."</a></li>";

		// My tasks
		if ($sec2 == "operation/users/user_task_assigment")
		echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=users&sec2=operation/users/user_task_assigment'>".lang_string ( "My task assigments")."</a></li>";


		echo "</ul>";
		echo "</div>";

	if  ((give_acl($config["id_user"], 0, "PR")) OR  (give_acl($config["id_user"], 0, "IR"))) {
		echo "<div class='portlet'>";
		echo "<h3>".lang_string ("user_reporting")."</h3>";
		echo "<ul class='sidemenu'>";

		// Basic report (monthly)
		if ($sec2 == "operation/user_report/report_monthly")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=users&sec2=operation/user_report/report_monthly'>".lang_string ("montly_report")."</a></li>";

		// Basic report (weekly)
		if ($sec2 == "operation/user_report/report_weekly")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=users&sec2=operation/user_report/report_weekly'>".lang_string ("weekly_report")."</a></li>";

		// Basic report (annual)
		if ($sec2 == "operation/user_report/report_annual")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=users&sec2=operation/user_report/report_annual'>".lang_string ("Annual report")."</a></li>";

		// View vacations
		if ($sec2 == "operation/projects/task_workunit")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=users&sec2=operation/projects/task_workunit&id_project=-1&id_task=-1'>".lang_string ("View vacations")."</a></li>";

		echo "</ul></div>";


	}

	if (give_acl($config["id_user"], 0, "UM")){
		echo "<div class='portlet'>";
		echo "<h3>".lang_string ('user_management')."</h3>";
		echo "<ul class='sidemenu'>";

		// Usermanager
		if ($sec2 == "godmode/usuarios/lista_usuarios")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=users&sec2=godmode/usuarios/lista_usuarios'>".lang_string ('manage_user')."</a></li>";

		// Rolemanager
		if ($sec2 == "godmode/usuarios/role_manager")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=users&sec2=godmode/usuarios/role_manager'>".lang_string ('manage_roles')."</a></li>";

		// Group manager
		if ($sec2 == "godmode/grupos/lista_grupos")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=users&sec2=godmode/grupos/lista_grupos'>".lang_string ("manage_groups")."</a></li>";

		// Profile manager
		if ($sec2 == "godmode/perfiles/lista_perfiles")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=users&sec2=godmode/perfiles/lista_perfiles'>".lang_string ("manage_profiles")."</a></li>";

		// Global user/role/task assigment
		if ($sec2 == "godmode/usuarios/role_user_global")
			echo "<li id='sidesel'>";
		else
			echo "<li>";
		echo "<a href='index.php?sec=users&sec2=godmode/usuarios/role_user_global'>".lang_string ("Global task assigment")."</a></li>";


		echo "</ul>";
		echo "</div>";
	}
}

// Testing boxes for side menus
$id_user = $_SESSION['id_usuario'];
$avatar = give_db_value ("avatar", "tusuario", "id_usuario", $id_user);
$realname = give_db_value ("nombre_real", "tusuario", "id_usuario", $id_user);
$email = give_db_value ("direccion", "tusuario", "id_usuario", $id_user);
$description = give_db_value ("comentarios", "tusuario", "id_usuario", $id_user);


$now = date("Y-m-d H:i:s");
$now_year = date("Y");
$now_month = date("m");
$working_month = give_parameter_post ("working_month", $now_month);
$working_year = give_parameter_post ("working_year", $now_year);

echo '
 <div class="portlet">
  <a href="javascript:;" onmousedown="toggleDiv(\'userdiv\');"><h2>'.lang_string("user_info").'</h2></a>

  <div class="portletBody">';

echo "<img src='images/avatars/".$avatar."_small.png' align='left'>";
echo '<a href="index.php?sec=users&sec2=operation/users/user_edit&ver='.$id_user.'">'.$id_user.'</a><br>';
echo "<b>".$realname."</b><br>";
echo '<b>E-mail:</b>&nbsp;'.$email.'<br><b>Timezone:</b>Europe/Madrid<br>';

// Link to workunit calendar (month)
echo "<a href='index.php?sec=users&sec2=operation/user_report/monthly&month=$now_month&year=$now_year&id=$id_user'><img border=0 hspace=5 src='images/clock.png' title='".lang_string ('work_unit_report')."'></a>";

if (give_acl($config["id_user"], 0, "PR") == 1){

	// Link to project graph
	echo "&nbsp;&nbsp;";
	echo "<a href='index.php?sec=users&sec2=operation/user_report/monthly_graph&month=$working_month&year=$working_year&id=".$id_user."'>";
	echo "<img border=0 src='images/chart_bar.png' title='Project distribution'></a>";


	// Link to Work user spare inster
	echo "&nbsp;&nbsp;";
	echo "<a href='index.php?sec=users&sec2=operation/users/user_spare_workunit'>";
	echo "<img border=0 src='images/award_star_silver_1.png' title='Workunit'></a>";

	// Week Workunit meter :)
	echo "&nbsp;&nbsp;";
	$begin_week = week_start_day();
	$begin_week .= " 00:00:00";
	$end_week = date('Y-m-d H:i:s',strtotime("$begin_week + 1 week"));
	$total_hours = 5 * $config["hours_perday"];
	$week_hours = give_db_sqlfree_field ("SELECT SUM(duration) FROM tworkunit WHERE timestamp > '$begin_week' AND timestamp <   '$end_week' AND id_user = '".$id_user."'");
	$ratio = "$week_hours / $total_hours";
	if ($week_hours < $total_hours)
		echo "<img src='images/exclamation.png' title='".lang_string ("Week workunit time not fully justified")." - $ratio'>";
	else
		echo "<img src='images/heart.png' title='".lang_string ("Week workunit are fine")." - $ratio'>";
}

echo '
  </div>
</div>';
// End of user box


?>
