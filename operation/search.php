<?PHP
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2011 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

check_login ();

// We need to strip HTML entities if we want to use in a sql search
$search_string = safe_output (get_parameter ("search_string",""));

echo "<h1>";

echo __("Searching for");
echo "...";
echo "<i>". safe_input($search_string) ."</i>";
echo "</h1>";

/* 

This code is a general search view, the first version, will be improved in the future. This will render in a single page, output for:

	* Incident data (title and/or #id)
    * Project / Task title

	* KB Articles problem
	* Inventory object
	* Companies
	* Contracts
	* Contacts

*/

// Incidents
if (give_acl($config["id_user"], 0, "IR") && $show_incidents != MENU_HIDDEN){

	$sql = "SELECT id_incidencia, inicio, titulo, estado FROM tincidencia WHERE titulo LIKE '%$search_string%' OR id_incidencia = '$search_string'";

	$incidents = get_db_all_rows_sql ($sql);
	
	if ($incidents !== false) {


		echo "<h3>";
		echo __("Incident management");
		echo "</h3>";

		$table->width = '80%';
		$table->class = 'listing';
		$table->data = array ();
		$table->size = array ();
		$table->style = array ();
		$table->head[0] = __('# ID');
		$table->head[1] = __('Title');
		$table->head[2] = __('Creation datetime');
		$table->head[3] = __('Status');
		$table->head[4] = __('WU time (hr)');

		foreach ($incidents as $incident) {
			$data = array ();
			if (user_belong_incident ($config["id_user"], $incident["id_incidencia"])) {

				$data[0] = $incident["id_incidencia"];
				$data[1] = "<a href='index.php?sec=incidents&sec2=operation/incidents/incident&id=".$incident["id_incidencia"]."'>".$incident["titulo"]."</a>";
				$data[2] = $incident["inicio"];
				$data[3] = $incident["estado"];
				$data[4] = get_incident_workunit_hours($incident["id_incidencia"]);
				array_push ($table->data, $data);
			}
		}

		print_table ($table);
	}
}


// Projects
if (give_acl($config["id_user"], 0, "PR") && $show_projects != MENU_HIDDEN){

	$sql = "SELECT tproject.id as project_id, ttask.id as task_id, tproject.name as pname, ttask.name as tname FROM tproject, ttask WHERE tproject.disabled = 0 AND ttask.id_project = tproject.id AND ttask.name LIKE '%$search_string%'";

	$tasks = get_db_all_rows_sql ($sql);
	
	if ($tasks !== false) {


		echo "<h3>";
		echo __("Project management");
		echo "</h3>";

		$table->width = '80%';
		$table->class = 'listing';
		$table->data = array ();
		$table->size = array ();
		$table->style = array ();
		$table->head[0] = __('Project');
		$table->head[1] = __('Task');

		foreach ($tasks as $task) {
			$data = array ();
		
		if (user_belong_project ($config["id_user"], $task["project_id"])){

				$data[0] = "<a href='index.php?sec=projects&sec2=operation/projects/task&id_project=".$task["project_id"]."'>".$task["pname"]."</a>";

				$data[1] = "<a href='index.php?sec=projects&sec2=operation/projects/task_detail&id_project=".$task["project_id"]."&id_task=".$task["task_id"]."&operation=view'>".$task["tname"]."</a>";

				array_push ($table->data, $data);
			}
		}
		print_table ($table);
	}
}

exit;

// Incidents
if (give_acl($config["id_user"], 0, "IR") && $show_incidents != MENU_HIDDEN){
    // Incident
    if ($sec == "incidents" )
	    echo "<li id='current' class='incident'>";
    else
	    echo "<li class='incident'>";
    echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident'>".__('Incidents')."</a></li>";
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

// KB
if (give_acl($config["id_user"], 0, "KR") && $show_kb != MENU_HIDDEN){
	if ($sec == "kb" )
		echo "<li id='current' class='kb'>";
	else
		echo "<li class='kb'>";
	echo "<a href='index.php?sec=kb&sec2=operation/kb/browse'>".__('KB')."</a></li>";
}

// FILE RELEASES
if (give_acl($config["id_user"], 0, "KR")){

	if($show_file_releases != MENU_HIDDEN) {
		// File Releases
		if ($sec == "download" )
				echo "<li id='current' class='files'>";
		else
				echo "<li class='files'>";
		echo "<a href='index.php?sec=download&sec2=operation/download/browse'>".__('File Releases')."</a></li>";
	}
}

if($show_people != MENU_HIDDEN) {
	// Users
	if ($sec == "users" )
		echo "<li id='current' class='people'>";
	else
		echo "<li class='people'>";
	echo "<a href='index.php?sec=users&sec2=operation/user_report/report_monthly'>".__('People')."</a></li>";
}

if($show_todo != MENU_HIDDEN) {
	// TODO
	if ($sec == "todo" )
		echo "<li id='current' class='todo'>";
	else
		echo "<li class='todo'>";
	echo "<a href='index.php?sec=todo&sec2=operation/todo/todo'>".__('Todo')."</a></li>";
}

// Agenda
if (give_acl($config["id_user"], 0, "AR") && $show_agenda != MENU_HIDDEN){
	// Agenda
	if ($sec == "agenda" )
		echo "<li id='current' class='agenda'>";
	else
		echo "<li class='agenda'>";
	echo "<a href='index.php?sec=agenda&sec2=operation/agenda/agenda'>".__('Agenda')."</a></li>";
}

// Setup
if (isset($config["id_user"]) && dame_admin($config["id_user"]) && $show_setup != MENU_HIDDEN){
	// Setup
	if ($sec == "godmode" )
		echo "<li id='current' class='setup'>";
	else
		echo "<li class='setup'>";
	echo "<a href='index.php?sec=godmode&sec2=godmode/setup/setup'>".__('Setup')."</a></li>";
}

echo "</ul>";
?>
