<?PHP
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2012 Ártica Soluciones Tecnológicas
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
$search_string = get_parameter ("search_string","");

// Delete spaces from start and end of the search string
$search_string = safe_input(trim(safe_output($search_string)));

echo "<h1>";

echo __("Searching for");
echo "...";
echo "<i> '". $search_string ."'</i>";
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
		$table->head = array();
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

// Users - Only for UM
if (give_acl($config["id_user"], 0, "UM")){

	$sql = "SELECT * FROM tusuario WHERE id_usuario LIKE '%".$search_string."%' OR direccion LIKE '%".$search_string."%' OR comentarios LIKE '%".$search_string."%' ";
	$users = get_db_all_rows_sql ($sql);
	
	if ($users !== false) {

		echo "<h3>";
		echo __("People");
		echo "</h3>";
		
		$table->width = '80%';
		$table->class = 'listing';
		$table->data = array ();
		$table->size = array ();
		$table->style = array ();
		$table->head[0] = __('Full name');
		$table->head[1] = __('Full report');
		$table->head[2] = __('Monthly report');
		
		foreach ($users as $user) {

			$data = array ();

			// CHECK ACK !!
			if (user_visible_for_me ($config["id_user"], $user["id_usuario"], "")){
				$data[0] = "<a href='index.php?sec=users&sec2=godmode/usuarios/configurar_usuarios&id_usuario_mio=".$user["id_usuario"]."'>".$user["nombre_real"]." ( ". $user["id_usuario"]." ) "."</a>";

				$data[1] = "<a href='index.php?sec=users&sec2=operation/user_report/report_full&user_id=".$user["id_usuario"]."'>".__("Full report")."</a>";

				$data[2] = "<a href='index.php?sec=users&sec2=operation/user_report/monthly&id=".$user["id_usuario"]."'><img src='images/clock.png'></a>";

				array_push ($table->data, $data);
			}
		}
		print_table ($table);
	}
}

// KB
if (give_acl($config["id_user"], 0, "KR") && $show_kb != MENU_HIDDEN){

	$sql = "SELECT tkb_category.name as category, tkb_product.name as product, tkb_data.title as kb_name, tkb_data.id as kb_id FROM tkb_data, tkb_product, tkb_category WHERE title LIKE '%".$search_string."%' AND tkb_data.id_category = tkb_category.id AND tkb_data.id_product = tkb_product.id";
	$kbs = get_db_all_rows_sql ($sql);
	
	if ($kbs !== false) {
		
		echo "<h3>";
		echo __("Knowlegue Base");
		echo "</h3>";
		
		$table->width = '80%';
		$table->class = 'listing';
		$table->data = array ();
		$table->size = array ();
		$table->style = array ();
		$table->head[0] = __('KB');
		$table->head[1] = __('Product');
		$table->head[2] = __('Category');

		foreach ($kbs as $kb) {
			$data = array ();
		
			$data[0] = "<a href='index.php?sec=kb&sec2=operation/kb/browse_data&view=".$kb["kb_id"]."'>".$kb["kb_name"]."</a>";
			$data[1] = $kb["product"];
			$data[2] = $kb["category"];
			array_push ($table->data, $data);
		}
		print_table ($table);
	}
}

// Contact
if (give_acl($config["id_user"], 0, "VR") && $show_inventory != MENU_HIDDEN){

	$sql = "SELECT * FROM tcompany_contact  WHERE fullname LIKE '%".$search_string."%' OR email LIKE '%".$search_string."%'";
	$contacts = get_db_all_rows_sql ($sql);
	
	if ($contacts !== false) {
		
		echo "<h3>";
		echo __("Contacts");
		echo "</h3>";
		
		$table->width = '80%';
		$table->class = 'listing';
		$table->data = array ();
		$table->size = array ();
		$table->style = array ();
		$table->head[0] = __('Name');
		$table->head[1] = __('Company');
		$table->head[2] = __('Email');
		$table->head[3] = __('Position');

		foreach ($contacts as $contact) {
			$data = array ();
		
			$data[0] = "<a href='index.php?sec=customers&sec2=operation/contacts/contact_detail&id=".$contact["id"]."'>".$contact["fullname"].'</a>';
			$data[1] = "<a href='index.php?sec=customers&sec2=operation/companies/company_detail&id=".$contact["id_company"]."'>" . 	
			get_db_sql ("SELECT name FROM tcompany WHERE id = " . $contact["id_company"]). "</a>";
			$data[2] = $contact["email"];
			$data[3] = $contact["position"];
			array_push ($table->data, $data);
		}
		print_table ($table);
	}
}


// Companies
if (give_acl($config["id_user"], 0, "VR") && $show_inventory != MENU_HIDDEN){

	$sql = "SELECT * FROM tcompany WHERE 
		name LIKE '%".$search_string."%'
		OR id IN (SELECT id_company FROM tcompany_activity WHERE description LIKE '%$search_string%')";
	$companies = get_db_all_rows_sql ($sql);
	
	if ($companies !== false) {
		
		echo "<h3>";
		echo __("Companies");
		echo "</h3>";
		
		$table->width = '80%';
		$table->class = 'listing';
		$table->data = array ();
		$table->size = array ();
		$table->style = array ();
		$table->head = array();
		$table->head[0] = __('Company');
		$table->head[1] = __('Role');
		
		foreach ($companies as $company) {
			$data = array ();
	
			$data[0] = "<a href='index.php?sec=customers&sec2=operation/companies/company_detail&id=".$company["id"]."'>" . 	
			$company["name"]. "</a>";
			$data[1] = get_db_sql ("SELECT name FROM tcompany_role WHERE id = ".$company["id_company_role"]);
			
			array_push ($table->data, $data);
		}
		print_table ($table);
	}
}

echo "<br><br>";
echo "<strong>";
echo "-- ";
echo __("End of search");
echo " --";
echo "</strong>";


?>
