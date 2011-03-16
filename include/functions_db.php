<?php

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

require_once ('functions_ui.php');

require_once ("functions_db.mysql.php");

// Load enterprise version functions

global $config;

enterprise_include ('include/functions_db.php');

define ('INCIDENT_CREATED', 0);
define ('INCIDENT_UPDATED', 1);
define ('INCIDENT_WORKUNIT_ADDED', 2);
define ('INCIDENT_FILE_ADDED', 3);
define ('INCIDENT_NOTE_ADDED', 4);
define ('INCIDENT_FILE_REMOVED', 5);
define ('INCIDENT_PRIORITY_CHANGED', 6);
define ('INCIDENT_STATUS_CHANGED', 7);
define ('INCIDENT_RESOLUTION_CHANGED', 8);
define ('INCIDENT_NOTE_DELETED', 9);
define ('INCIDENT_INVENTORY_ADDED', 10);
define ('INCIDENT_USER_CHANGED', 17);
define ('INCIDENT_DELETED', 18);
define ('INCIDENT_CONTACT_ADDED', 19);

define ('TASK_CREATED', 11);
define ('TASK_UPDATED', 12);
define ('TASK_NOTE_ADDED', 13);
define ('TASK_WORKUNIT_ADDED', 14);
define ('TASK_FILE_ADDED', 15);
define ('TASK_COMPLETION_CHANGED', 16);
define ('TASK_FINISHED', 17);
define ('TASK_MEMBER_ADDED', 18);
define ('TASK_MOVED', 19);
define ('TASK_DELETED', 20);
define ('TASK_MEMBER_DELETED', 20);

define ('PROJECT_CREATED', 21);
define ('PROJECT_UPDATED', 22);
define ('PROJECT_DISABLED', 23);
define ('PROJECT_ACTIVATED', 24);
define ('PROJECT_DELETED', 25);
define ('PROJECT_TASK_ADDED', 26);

// Incident status constants

define ('STATUS_NEW', 1);
define ('STATUS_UNCONFIRMED', 2);
define ('STATUS_ASSIGNED', 3);
define ('STATUS_REOPENED', 4);
define ('STATUS_VERIFIED', 5);
define ('STATUS_RESOLVED', 6);
define ('STATUS_CLOSED', 7);

// Incident resolution constants

define ('RES_FIXED', 1);
define ('RES_INVALID', 2);
define ('RES_WONTFIX', 3);
define ('RES_DUPLICATE', 4);
define ('RES_WORKSFORME', 5);
define ('RES_INCOMPLETE', 6);
define ('RES_EXPIRED', 7);
define ('RES_MOVED', 8);
define ('RES_INPROCESS', 9);

/**
 * Function to check user permissions in a group.
 * NOT FILLY IMPLEMENTED IN OPENSOURCE version
 * Please visit http://integriaims.com for more information
*/

function give_acl ($id_user, $id_group, $access) {
	global $config;


	$return = enterprise_hook ('give_acl_extra', array ($id_user, $id_group, $access));
	if ($return !== ENTERPRISE_NOT_HOOK)
		return $return;

    // Opensource ACL implementation (not hooked to profiles / groups)
    $admin = get_admin_user($id_user);

    if (($access == "UM") AND ($admin == 0))
        return false;

    if (($access == "PM") AND ($admin == 0))
        return false;

    if (($access == "IM") AND ($admin == 0))
        return false;

    if (($access == "VM") AND ($admin == 0))
        return false;

    if (($access == "IM") AND ($admin == 0))
        return false;

    if (($access == "AM") AND ($admin == 0))
        return false;

    if (($access == "FM") AND ($admin == 0))
        return false;

    if (($access == "DM") AND ($admin == 0))
        return false;

    if (($access == "AM") AND ($admin == 0))
        return false;

    if (($access == "KM") AND ($admin == 0))
        return false;

    if (($access == "TM") AND ($admin == 0))
        return false;

	return true;
} 

/**
 This function return 1 if target_user is visible for a user (id_user)
 with a specific permission bit on any of its profiles 
 * NOT ENABLED IN OPENSOURCE version
 * Please visit http://integriaims.com for more information
**/

function user_visible_for_me ($id_user, $target_user, $access = "") {
	global $config;

	$return = enterprise_hook ('user_visible_for_me_extra', array ($id_user, $target_user, $access));
	if ($return !== ENTERPRISE_NOT_HOOK)
		return $return;
	return true;
} 


// --------------------------------------------------------------- 
// audit_db, update audit log
// --------------------------------------------------------------- 

function audit_db ($id, $ip, $accion, $description, $extra = "") {
	require ("config.php");
	$today = date('Y-m-d H:i:s');

    $extra = mysql_real_escape_string ($extra);

	$utimestamp = time();
	$sql = 'INSERT INTO tsesion (ID_usuario, accion, fecha, IP_origen,descripcion, utimestamp, extra_info) VALUES ("'.$id.'","'.$accion.'","'.$today.'","'.$ip.'","'.$description.'", '.$utimestamp.', "'.$extra.'")';
	process_sql ($sql);
}


// --------------------------------------------------------------- 
// logon_db, update entry in logon audit
// --------------------------------------------------------------- 

function logon_db ($id, $ip) {
	global $config;
	audit_db ($id, $ip, "Logon", "Logged in");
	$today = date ('Y-m-d H:i:s');
	// Update last registry of user to get last logon
	$sql = sprintf ('UPDATE tusuario SET fecha_registro = "%s" WHERE id_usuario = "%s"', $today, $id);
	process_sql ($sql);
}

// --------------------------------------------------------------- 
// logoff_db, also adds audit log
// --------------------------------------------------------------- 

function logoff_db ($id, $ip) {
	audit_db ($id, $ip, "Logoff", "Logged out");
}

// --------------------------------------------------------------- 
// Returns profile given ID
// --------------------------------------------------------------- 

function dame_perfil ($id) {
	return get_db_value ('name', 'tprofile', 'id', $id);
}


// --------------------------------------------------------------- 
// Returns group given ID
// --------------------------------------------------------------- 

function dame_grupo ($id_group) {
	return get_db_value ('nombre', 'tgrupo', 'id_grupo', $id_group);
}

// --------------------------------------------------------------- 
// Returns icon name given group ID
// --------------------------------------------------------------- 

function dame_grupo_icono ($id_group) {
	return get_db_value ('icon', 'tgrupo', 'id_grupo', $id_group);
}

// --------------------------------------------------------------- 
// Returns password (HASH) given user_id
// --------------------------------------------------------------- 

function dame_password ($id_user) {
	return get_db_value ('password', 'tusuario', 'id_usuario', $id_user);
}

// --------------------------------------------------------------- 
// Returns name of the user when given ID
// --------------------------------------------------------------- 

function dame_nombre_real ($id_user) {
	return get_db_value ('nombre_real', 'tusuario', 'id_usuario', $id_user);
}


// --------------------------------------------------------------- 
// This function returns ID of user who has created incident
// --------------------------------------------------------------- 

function get_incident_author ($id_incident) {
	return get_db_value ('id_usuario', 'tincidencia', 'id_incidencia', $id_incident);
}


// --------------------------------------------------------------- 
// Return name of a group when given ID
// --------------------------------------------------------------- 

function dame_nombre_grupo ($id_group) {
	return get_db_value ('nombre', 'tgrupo', 'id_grupo', $id_group);
} 

// --------------------------------------------------------------- 
// Returns number of files from a given incident
// --------------------------------------------------------------- 

function get_number_files_incident ($id_incident) {
	return (int) get_db_value ('COUNT(*)', 'tattachment', 'id_incidencia', $id_incident);
}


// --------------------------------------------------------------- 
// Returns number of files from a given incident
// --------------------------------------------------------------- 

function get_number_files_task ($id_task) {
	return (int) get_db_value ('COUNT(*)', 'tattachment', 'id_task', $id_task);
}


/**
* Return number of files associated to a project
*
* $id		integer 	ID of project
**/
function give_number_files_project ($id) {
	return (int) get_db_sql ('SELECT COUNT(*) FROM tattachment, ttask WHERE ttask.id_project = '.$id.' AND ttask.id = tattachment.id_task');
}


/**
* Return number of tasks associated to an incident
*
* $id		integer 	ID of project
**/
function get_tasks_count_in_project ($id_project) {
	return (int) get_db_value ('COUNT(*)', 'ttask', 'id_project', $id_project);
}


/**
* Return total hours assigned to incidents assigned to a task
*
* $id_task	integer 	ID of task
**/

function get_incident_task_workunit_hours ($id_task) {
	global $config;
	$sql = sprintf ('SELECT SUM(tworkunit.duration) 
			FROM tworkunit, tworkunit_incident, tincidencia 
			WHERE tworkunit_incident.id_incident = tincidencia.id_incidencia
			AND tworkunit_incident.id_workunit = tworkunit.id
			AND tincidencia.id_task = %d', $id_task);
	return (int) get_db_sql ($sql);
}


/**
* Return total hours assigned in specific day by a user
*
* $id_user	string ID Of user
* $timestamp string date in format YYYY-MM-DD
**/

function get_wu_hours_user ($id_user, $timestamp) {
	global $config;
	$sql = sprintf ('SELECT SUM(tworkunit.duration) 
			FROM tworkunit
			WHERE tworkunit.id_user = "%s"  
			AND tworkunit.timestamp LIKE "%s%%"', $id_user, $timestamp);
	return (int) get_db_sql ($sql);
}


/**
* Return total hours assigned to incidents assigned to tasks in a project
*
* $id_project	integer 	ID of project
**/

function get_incident_project_workunit_hours ($id_project) {
	global $config;
	$sql = sprintf ('SELECT SUM(tworkunit.duration) 
			FROM tworkunit, tworkunit_incident, tincidencia, ttask 
			WHERE tworkunit_incident.id_incident = tincidencia.id_incidencia
			AND tworkunit_incident.id_workunit = tworkunit.id
			AND ttask.id_project = %d
			AND ttask.id = tincidencia.id_task', $id_project);
	return (int) get_db_sql ($sql);
}



/**
* Return total number of incidents related to a task
*
* $id_task	integer 	ID of task
**/

function get_incident_task ($id_task) {
	global $config;
	$sql = sprintf ('SELECT COUNT(id_incidencia) 
			FROM tincidencia 
			WHERE id_task = %d', $id_task);
	return (int) get_db_sql ($sql);
}



/**
* Return total wu assigned to incident
*
* $id_incident   integer	 ID of incident
**/
function get_incident_count_workunits ($id_incident) {
	global $config;
	$sql = sprintf ('SELECT COUNT(tworkunit.duration) 
			FROM tworkunit, tworkunit_incident, tincidencia 
			WHERE   tworkunit_incident.id_incident = tincidencia.id_incidencia AND 
					tworkunit_incident.id_workunit = tworkunit.id AND
					 tincidencia.id_incidencia = %d', $id_incident);
	return (int) get_db_sql ($sql);
}


/**
* Return total hours assigned to project
*
* $id_project	integer 	ID of project
**/

function get_project_workunit_hours ($id_project, $with_cost = 0, $start_date = "", $end_date =""){ 
	global $config;
	
	$timesearch = "";
	if ($start_date != "")
		$timesearch = " AND tworkunit.timestamp >= '$start_date' AND tworkunit.timestamp <= '$end_date'";

	if ($with_cost != 0) {
		$sql = sprintf ('SELECT SUM(tworkunit.duration) 
			FROM tworkunit, tworkunit_task, ttask 
			WHERE tworkunit_task.id_task = ttask.id
			AND ttask.id_project = %d
			AND tworkunit_task.id_workunit = tworkunit.id
			AND tworkunit.have_cost = 1 %s', $id_project, $timesearch);
	} else {
		$sql = sprintf ('SELECT SUM(tworkunit.duration) 
			FROM tworkunit, tworkunit_task, ttask 
			WHERE tworkunit_task.id_task = ttask.id
			AND ttask.id_project = %d
			AND tworkunit_task.id_workunit = tworkunit.id %s',
			$id_project, $timesearch);
	}
	return (int) get_db_sql ($sql);
}

/**
* Return total wu assigned to project
*
* $id_project   integer	 ID of project
**/

function get_project_count_workunits ($id_project) {
	$sql = sprintf ('SELECT COUNT(tworkunit.duration) 
			FROM tworkunit, tworkunit_task, ttask 
			WHERE tworkunit_task.id_task = ttask.id
			AND ttask.id_project = %d
			AND tworkunit_task.id_workunit = tworkunit.id',
			$id_project);
	return (int) get_db_sql ($sql);
}


/**
* Return total hours assigned to task
*
* $id_task	integer 	ID of task
**/
function get_task_workunit_hours ($id_task) {
	$sql = sprintf ('SELECT SUM(tworkunit.duration) 
			FROM tworkunit, tworkunit_task
			WHERE tworkunit_task.id_task = %d
			AND tworkunit_task.id_workunit = tworkunit.id',
			$id_task);
	return (int) get_db_sql ($sql);
}

/**
* Return total workunits assigned to task
*
* $id_task  integer	 ID of task
**/
function get_task_count_workunits ($id_task) {
	$sql = sprintf ('SELECT COUNT(tworkunit.duration) 
			FROM tworkunit, tworkunit_task
			WHERE tworkunit_task.id_task = %d
			AND tworkunit_task.id_workunit = tworkunit.id',
			$id_task);
	return (int) get_db_sql ($sql);
}


/**
* Return total workunits assigned to task for a specific user
*
* $id_task  integer	 ID of task
* $id_user  string	  ID of user
**/

function get_task_workunit_hours_user ($id_task, $id_user) {
	$sql = sprintf ('SELECT COUNT(tworkunit.duration) 
			FROM tworkunit, tworkunit_task 
			WHERE tworkunit_task.id_task = %d
			AND tworkunit.id_user = "%s"
			AND tworkunit_task.id_workunit = tworkunit.id',
			$id_task, $id_user);
	return (int) get_db_sql ($sql);
}


/**
* Calculate project completion
*
* Uses each task completion and priority and uses second
* to ponderate progress of this task. A average value of 
* ponderated values is made to give final result.
* $id_project 	integer 	ID of project
**/

function calculate_project_progress ($id_project){
	global $config;
	$sql = sprintf ('SELECT AVG(completion)
			FROM ttask
			WHERE id_project = %d',
			$id_project);
	return get_db_sql ($sql);
}

/**
* Delete an incident
*
* Delete incident given its id and all its workunits
* $id_incident integer 	ID of incident
**/
 
function borrar_incidencia ($id_incident) {
	global $config;

	$incident_title = get_db_value ("titulo", "tincidencia", "id_incidencia", $id_incident);
	$sql = sprintf ('DELETE FROM tincidencia
			WHERE id_incidencia = %d', $id_incident);
	process_sql ($sql);
	$sql = sprintf ('SELECT id_workunit FROM tworkunit_incident
			WHERE id_incident = %d',$id_incident);
	$workunits = get_db_all_rows_sql ($sql);
	if ($workunits === false) {
		$workunits = array ();
	}
	foreach ($workunits as $workunit) {
		// Delete all note ID related in table
		$sql = sprintf ('DELETE FROM tworkunit WHERE id = %d',
				$workunit['id_workunit']);
		process_sql ($sql);
	}
	$sql = sprintf ('DELETE FROM tworkunit_incident
			WHERE id_incident = %d', $id_incident);
	process_sql ($sql);
	
	// Delete attachments
	$sql = sprintf ('SELECT id_attachment, filename
			FROM tattachment
			WHERE id_incidencia = %d', $id_incident);
	$attachments = get_db_all_rows_sql ($sql);
	if ($attachments === false) {
		$attachments = array ();
	}
	foreach ($attachments as $attachment) {
		// Unlink all attached files for this incident
		$id = $attachment["id_attachment"];
		$name = $attachment["filename"];
		unlink ($config["homedir"]."/attachment/".$id."_".$name);
	}
	
	$sql = sprintf ('DELETE FROM tattachment
			WHERE id_incidencia = %d', $id_incident);
	process_sql ($sql);
	$sql = sprintf ('DELETE FROM tincident_track
			WHERE id_incident = %d', $id_incident);
	process_sql ($sql);
	incident_tracking ($id_incident, INCIDENT_DELETED, 0);
	insert_event ('INCIDENT_DELETED', 0,0, $incident_title);
}

/**
* Delete an inventory object. All depending data will 
* deleted using SQL referencial integrity
*
* $id_inventory integer 	ID of inventory object
**/

// --------------------------------------------------------------- 
// Delete an inventory object given its id
// --------------------------------------------------------------- 
function borrar_objeto ($id) {
	$sql = sprintf ('DELETE FROM tinventory WHERE id = %d', $id);
	process_sql ($sql);
}

// --------------------------------------------------------------- 
//  Update "contact" field in User table for username $nick
// --------------------------------------------------------------- 

function update_user_contact ($id_user) {
	$today = date ("Y-m-d H:i:s", time ());
	$sql = sprintf ('UPDATE tusuario set fecha_registro ="%s"
			WHERE id_usuario = "%s"',
			$today, $id_user);
	process_sql ($sql);
}

// ---------------------------------------------------------------
// Returns Admin value (0 no admin, 1 admin)
// ---------------------------------------------------------------

function get_admin_user ($id) {
	$nivel = get_db_value ('nivel', 'tusuario', 'id_usuario', $id);
	if ($nivel == 1)
		return true;
	// Be careful, other possible values on level could be implemented
	// In the future, so only "admin" value possible is 1
	return false;
}

// Wrapper for compatibility
function dame_admin ($id) {
    return get_admin_user ($id);
}

// ---------------------------------------------------------------
// Returns true is provided user is external
// ---------------------------------------------------------------

function get_external_user ($id) {
	$nivel = get_db_value ('nivel', 'tusuario', 'id_usuario', $id);
	if ($nivel == -1)
		return true;
	return false;
}


// --------------------------------------------------------------- 
// Gives error message and stops execution if user 
//doesn't have an open session and this session is from an valid user
// --------------------------------------------------------------- 

function check_login () { 
	if (isset ($_SESSION["id_usuario"])) {
		$id = $_SESSION["id_usuario"];
		$id_user = get_db_value ('id_usuario', 'tusuario', 'id_usuario', $id);
		if ($id == $id_user) {
			return false;
		}
	}
	global $config;
	require ($config["homedir"]."/general/noaccess.php");
	exit;
}


// ---------------------------------------------------------------
// 0 if it doesn't exist, 1 if it does, when given email
// ---------------------------------------------------------------

function existe($id){
	require("config.php");
	$query1="SELECT * FROM tusuario WHERE id_usuario = '".$id."'";   
	$resq1=mysql_query($query1);
	if ($resq1 != 0) {
		if ($rowdup=mysql_fetch_array($resq1)){ 
			return 1; 
		}
		else {
			return 0; 
		}
	} else { return 0 ; }
}

// ---------------------------------------------------------------
// Return if a task have childs
// Return date of end of this task of last of it's childs
// ---------------------------------------------------------------

function task_child_enddate ($id_task){
	global $config;
   
	$start_date =  task_start_date($id_task);
	$tasktime = get_db_sql ("SELECT hours FROM ttask WHERE id= $id_task");
	$tasktime = $tasktime / $config["hours_perday"];
	$end_date = calcdate_business ($start_date, $tasktime);
	
	$max = '1980-01-01';
	$query1="SELECT * FROM ttask WHERE id_parent_task = $id_task";
	$resq1=mysql_query($query1);  
	while ($row=mysql_fetch_array($resq1)){
		$thisvalue = $row["hours"];
		$thisstart = $row["start"];
		$childtime = $thisvalue / $config["hours_perday"];
		$childdate = calcdate_business ($thisstart, $childtime);

		$grandchilddate = task_child_enddate ($row["id"]);
		if ($grandchilddate != $childdate)
			$childdate = $grandchilddate;

		if (strtotime($childdate) > strtotime($max)){
			$max = $childdate;
		}
	}

	if (strtotime($max) > strtotime($end_date))
		return $max;
	else
		return $end_date;
}

// ---------------------------------------------------------------
// Return start date of a task
// If is a nested task, return parent task + assigned time for parent
// ---------------------------------------------------------------

function task_start_date ($id_task){
	global $config;
	
	$taskrow =  get_db_row ("ttask", "id", $id_task);
	return $taskrow["start"];
}

// ---------------------------------------------------------------
// Return true (1) if userid belongs to given project as any role
// ---------------------------------------------------------------

function user_belong_project ($id_user, $id_project, $real = 0) { 
	global $config;
	
	if ($real == 0 && dame_admin ($id_user) != 0)
		return 1;
	
	$sql = sprintf ('SELECT COUNT(*) FROM trole_people_project
		WHERE id_project = %d
		AND id_user = "%s"', $id_project, $id_user);
	return (bool) get_db_sql ($sql);
}

// ---------------------------------------------------------------
// Return true (1) if userid belongs to given task as any role
// ---------------------------------------------------------------

function user_belong_task ($id_user, $id_task, $real=0){ 
	global $config;

	if ($real == 0){
	   if (dame_admin ($id_user) != 0)
			return 1;
	}

	$id_project = get_db_sql ("SELECT id_project FROM ttask WHERE id = $id_task");
	// Project manager always has access to all tasks of his project
	if (project_manager_check ($id_project) == 1 )
		return 1;

	$query1="SELECT COUNT(*) from trole_people_task WHERE id_task = $id_task AND id_user = '$id_user'";
		$resq1=mysql_query($query1);
		$rowdup=mysql_fetch_array($resq1);
	if ($rowdup[0] == 0)
		return 0;
	else
		return 1; // There is at least one role for this person in that project
}

function get_incident_resolution ($id_incident) {
	return get_db_value ('resolution', 'tincidencia', 'id_incidencia', $id_incident);
}

function get_incident_creator ($id_incident) {
	return (int) get_db_value ('id_creator', 'tincidencia', 'id_incidencia', $id_incident);
}

function get_incident_group ($id_incident) {
	return (int) get_db_value ('id_grupo', 'tincidencia', 'id_incidencia', $id_incident);
}

// --------------------------------------------------------------- 
// Return incident priority
// --------------------------------------------------------------- 

function get_incident_priority ($id_incident) {
	return get_db_value ('prioridad', 'tincidencia', 'id_incidencia', $id_incident);
}

// --------------------------------------------------------------- 
// Return incident title
// --------------------------------------------------------------- 

function get_incident_title ($id_incident) {
	return (string) get_db_value ('titulo', 'tincidencia', 'id_incidencia', $id_incident);
}

function get_user ($id_user) {
	return get_db_row ('tusuario', 'id_usuario', $id_user);
}

function get_user_email ($id_user) {
	return (string) get_db_value ('direccion', 'tusuario', 'id_usuario', $id_user);
}

function project_manager_check ($id_project) {
	global $config;

	$manager = get_db_value ('id_owner', 'tproject', 'id', $id_project);
	if ($manager == $config['id_user'])
		return true;
	return false;
}

function incident_tracking ($id_incident, $state, $aditional_data = 0) {
	global $config;
	
	switch ($state) {
	case INCIDENT_CREATED:
		$description = __('Created');
		break;
	case INCIDENT_UPDATED:
		$description = __('Updated');
		break;
	case INCIDENT_WORKUNIT_ADDED:
		$description = __('Workunit added');
		break;
	case INCIDENT_FILE_ADDED:
		$description = __('File added');
		break;
	case INCIDENT_NOTE_ADDED:
		$description = __('Note added');
		break;
	case INCIDENT_FILE_REMOVED:
		$description = __('File removed');
		break;
	case INCIDENT_PRIORITY_CHANGED:
		$description = __('Priority changed');
		$priorities = get_priorities ();
		$description .= " -> ".$priorities[$aditional_data];
		break;
	case INCIDENT_STATUS_CHANGED:
		$description = __('Status changed');
		$description .= " -> ".get_db_value ("name", "tincident_status", "id", $aditional_data);
		break;
	case INCIDENT_RESOLUTION_CHANGED:
		$description = __('Resolution changed');
		$description .= " -> ".get_db_value ("name", "tincident_resolution", "id", $aditional_data);
		break;
	case INCIDENT_NOTE_DELETED:
		$description = __('Note deleted');
		break;
	case INCIDENT_USER_CHANGED:
		$description = __('Assigned user changed');
		$description .= ' -> '.get_db_value ('nombre_real', 'tusuario', 'id_usuario', $aditional_data);
		break;
	case INCIDENT_DELETED:
		$description = __('Incident deleted');
		break;
	case INCIDENT_CONTACT_ADDED:
		$description = __('Contact added');
		$description .= ' -> '.get_db_value ('fullname', 'tcompany_contact', 'id', $aditional_data);
		break;
	default:
		$description = __('Unknown update');
		break;
	}
	$fecha = print_mysql_timestamp();	
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "Incident updated", $description);
	$sql = sprintf ('INSERT INTO tincident_track (id_user, id_incident,
		timestamp, state, id_aditional, description)
		VALUES ("%s", %d, "%s", %d, %d, "%s")',
		$config['id_user'], $id_incident, $fecha, $state, $aditional_data, $description);
	return process_sql ($sql, 'insert_id');
}

function task_tracking ($id_task, $state, $id_external = 0) {
	global $config;
	global $REMOTE_ADDR;

	$fecha = print_mysql_timestamp();
	audit_db ($config['id_user'], $REMOTE_ADDR, "Task #$id_task tracking updated", "State #$state");
	$sql = sprintf ('INSERT INTO ttask_track (id_user, id_task, timestamp,
		state, id_external)
		VALUES ("%s", %d, "%s", %d, %d)',
		$config['id_user'], $id_task, $fecha, $state, $id_external);
	return process_sql ($sql);
}

function project_tracking ($id_project, $state, $id_aditional = 0) {
	global $config;
	global $REMOTE_ADDR;

	$fecha = print_mysql_timestamp();
	audit_db ($config['id_user'], $REMOTE_ADDR, "Project #$id_project tracking updated", "State #$state");
	$sql = sprintf ('INSERT INTO tproject_track (id_user, id_project, timestamp,
		state, id_aditional)
		VALUES ("%s", %d, "%s", %d, %d)',
		$config['id_user'], $id_project, $fecha, $state, $id_aditional);
	return process_sql ($sql);
}


function delete_project ($id_project){
	$project_name = get_db_value ("name", "tproject", "id", $id_project);
	$query = "DELETE FROM trole_people_project WHERE id_project = $id_project";
	mysql_query($query);
	$query = "DELETE FROM trole_people_task, ttask WHERE ttask.id_project = $id_project AND trole_people_task.id_task = ttask.id";
	mysql_query($query);
	$query = "DELETE FROM ttask WHERE id_project = $id_project";
	mysql_query($query);
	$query = "DELETE FROM tproject WHERE id = $id_project";
	mysql_query($query);
	insert_event ('PROJECT_DELETED', 0,0, $project_name);
}

function delete_task ($id_task){
	// Have a parent ?
	$task = get_db_row ("ttask", "id", $id_task);
	if ($task["id_parent_task"] > 0){
		$query = "UPDATE tworkunit_task SET id_task = ".$task["id_parent_task"]." WHERE id_task = $id_task";
		mysql_query($query);
		$query = "DELETE FROM trole_people_task WHERE ttask.id_task = $id_task";
				mysql_query($query);
		$query = "DELETE FROM ttask WHERE id = $id_task";
				mysql_query($query);
	} else {
		$query = "DELETE FROM trole_people_task WHERE ttask.id_task = $id_task";
		mysql_query($query);
		$query = "DELETE FROM ttask_track WHERE id_task = $id_task";
		mysql_query($query);
		$query = "DELETE FROM tworkunit_task, tworkunit WHERE tworkunit_task.id_task = $id_task AND tworkunit_task.id_workunit = tworkunit.id";
		mysql_query($query);
		$query = "DELETE FROM ttask WHERE id = $id_task";
		mysql_query($query);
	}
	insert_event ('TASK_DELETED', 0,0, $task["name"]);
}

function mail_project ($mode, $id_user, $id_workunit, $id_task, $additional_msg = "") {
	global $config;

	$workunit = get_db_row ("tworkunit", "id", $id_workunit);
	$task	 = get_db_row ("ttask", "id", $id_task);
	$project  = get_db_row ("tproject", "id", $task["id_project"]);
	$id_project = $task["id_project"];
	$id_manager = $project["id_owner"];

	$MACROS["_time_used_"] = $workunit["duration"];
	$MACROS["_access_url_"] = $config["base_url"]."/index.php?sec=projects&sec2=operation/projects/task_workunit&id_project=$id_project&id_task=$id_task";

	if ($workunit["have_cost"] == 1)
		$MACROS["_havecost_"] = __('Yes');
	else
		$MACROS["_havecost_"] = __('No');

	if ($workunit["public"] == 1)
		$MACROS["_public_"] = __('Yes');
	else
		$MACROS["_public_"] = __('No');

	$MACROS["_sitename_"] = $config["sitename"];
	$MACROS["_taskname_"] = $task["name"];
	$MACROS["_projectname_"] =  $project["name"];
	$MACROS["_fullname_"] = dame_nombre_real ($project["id_owner"]);
	$MACROS["_update_timestamp_"] = $workunit["timestamp"];
	$MACROS["_owner_"] = $project["id_owner"];
	$MACROS["_wu_text_"] = $workunit["description"];
	$MACROS["_wu_user_"] = dame_nombre_real($id_user);
	$MACROS["_additional_message_"] = $additional_msg;
	$description = $workunit["description"];

	switch ($mode){
	case 0: // Workunit add
		$text = template_process ($config["homedir"]."/include/mailtemplates/project_wu_create.tpl", $MACROS);
		$subject = template_process ($config["homedir"]."/include/mailtemplates/project_subject_wucreate.tpl", $MACROS);
		break;
	case 1: // Workunit updated
		$text = template_process ($config["homedir"]."/include/mailtemplates/project_wu_update.tpl", $MACROS);
		$subject = template_process ($config["homedir"]."/include/mailtemplates/project_subject_wuupdate.tpl", $MACROS);
		break;
	}
	
	// Send an email to project manager
	integria_sendmail (get_user_email($id_manager), $subject, $text);
}

// TODO: Make todo mail using a template, like the other mails !


function mail_todo ($mode, $id_todo) {
	global $config;

	$todo = get_db_row ("ttodo", "id", $id_todo);
	$tcreated = $todo["created_by_user"];
	$tassigned = $todo["assigned_user"];

	// Only send mails when creator is different than owner
	if ($tassigned == $tcreated)
		return;

	$tlastupdate = $todo["last_update"];
	$tdescription = wordwrap($todo["description"], 70, "\n");
	$tprogress = $todo["progress"];
	$tpriority = $todo["priority"];
	$tname = $todo["name"];
	$url = $config["base_url"]."/index.php?sec=todo&sec2=operation/todo/todo&operation=update&id=$id_todo";

	switch ($mode){
	case 0: // Add
		$text = "TO-DO '$tname' has been CREATED by user $tcreated. You could track this todo in the following URL (need to use your credentials): $url\n\n";
		$subject = "[".$config["sitename"]."] New TO-DO from '$tcreated' : $tname";
		break;
	case 1: // Update
$text = "TO-DO '$tname' has been UPDATED by user $tassigned. This TO-DO was created by user $tcreated. You could track this todo in the following URL (need to use your credentials): $url\n\n";
		$subject = "[".$config["sitename"]."] Updated TO-DO from '$tcreated' : $tname";
		break;
	case 2: // Delete
		$text = "TO-DO '$tname' has been DELETED by user $tassigned. This TO-DO was created by user $tcreated. You could track this todo in the following URL (need to use your credentials): $url\n\n";
		$subject = "[".$config["sitename"]."] Deleted TO-DO from '$tcreated' : $tname";
	}	
$text .= "
---------------------------------------------------------------------
TO-DO NAME  : $tname
DATE / TIME : $tlastupdate
CREATED BY  : $tcreated
ASSIGNED TO : $tassigned
PROGRESS    : $tprogress%
PRIORITY    : $tpriority
DESCRIPTION
---------------------------------------------------------------------
$tdescription\n\n";

		$text = ascii_output ($text);
		$subject = ascii_output ($subject);
		// Send an email to both
		integria_sendmail (get_user_email ($tcreated), $subject, $text);
		integria_sendmail (get_user_email ($tassigned), $subject, $text);
}


/* Returns cost for a given task */

function task_workunit_cost ($id_task, $only_marked = true) {
	global $config;
	$total = 0;
	if ($only_marked)
		$res = mysql_query("SELECT id_profile, SUM(duration) FROM tworkunit, tworkunit_task
				WHERE tworkunit_task.id_task = $id_task AND 
				tworkunit_task.id_workunit = tworkunit.id AND 
				have_cost = 1 GROUP BY id_profile");
	else 
		$res = mysql_query("SELECT id_profile, SUM(duration) FROM tworkunit, tworkunit_task
				WHERE tworkunit_task.id_task = $id_task AND 
				tworkunit_task.id_workunit = tworkunit.id 
				GROUP BY id_profile");
	while ($row=mysql_fetch_array($res)){
		$cost_per_hour = get_db_sql ("SELECT cost FROM trole WHERE id = ".$row[0]);
		$total = $total + $cost_per_hour * $row[1];
	}
	return $total;
}

/* Returns cost for a given project */

function project_workunit_cost ($id_project, $only_marked = 1){
	global $config;
	$total = 0;
	$res = mysql_query("SELECT * FROM ttask WHERE id_project = $id_project");
	while ($row=mysql_fetch_array($res)){
		$total += task_workunit_cost ($row[0], $only_marked);
	}
	return $total;
}


function projects_active_user ($id_user) {
	$sql = "SELECT COUNT(DISTINCT(id_project)) FROM tproject, trole_people_project WHERE trole_people_project.id_user ='$id_user' AND trole_people_project.id_project = tproject.id AND tproject.disabled = 0";
	return get_db_sql ($sql);
}

function incidents_active_user ($id_user) {
	$sql = "SELECT COUNT(*) FROM tincidencia WHERE id_creator = '$id_user' OR id_usuario = '$id_user' AND estado IN (1,2,3,4,5)";
	return get_db_sql ($sql);
}

function todos_active_user ($id_user) {
	$sql = "SELECT COUNT(*) FROM ttodo WHERE assigned_user = '$id_user'";
	return get_db_sql ($sql);
}

function get_user_vacations ($id_user, $year){
	global $config;
	$hours = get_db_sql ("SELECT SUM(tworkunit.duration) FROM tworkunit, tworkunit_task WHERE tworkunit_task.id_workunit = tworkunit.id AND tworkunit_task.id_task =-1 AND id_user = '$id_user' AND timestamp >= '$year-01-00 00:00:00' AND timestamp <= '$year-12-31 23:59:59'");
	return format_numeric ($hours/$config["hours_perday"]);
}

function get_user_worked_days ($id_user, $year) {
	global $config;
	$hours = get_db_sql ("SELECT SUM(tworkunit.duration) FROM tworkunit, tworkunit_task WHERE tworkunit_task.id_workunit = tworkunit.id AND tworkunit_task.id_task > 0 AND id_user = '$id_user' AND timestamp >= '$year-01-00 00:00:00' AND timestamp <= '$year-12-31 23:59:59'");
	return format_numeric ($hours/$config["hours_perday"]);
}

function get_user_incident_worked_days ($id_user, $year) {
	global $config;
	$hours = get_db_sql ("SELECT SUM(tworkunit.duration) FROM tworkunit, tworkunit_incident WHERE tworkunit_incident.id_workunit = tworkunit.id AND tworkunit_incident.id_incident > 0 AND id_user = '$id_user' AND timestamp >= '$year-01-00 00:00:00' AND timestamp <= '$year-12-31 23:59:59'");
	return format_numeric ($hours/$config["hours_perday"]);
}

function get_user_other ($id_user, $year){
	global $config;
	$hours = get_db_sql ("SELECT SUM(tworkunit.duration) FROM tworkunit, tworkunit_task WHERE tworkunit_task.id_workunit = tworkunit.id AND tworkunit_task.id_task < -1 AND id_user = '$id_user' AND timestamp >= '$year-01-00 00:00:00' AND timestamp <= '$year-12-31 23:59:59'");
	return format_numeric ($hours/$config["hours_perday"]);
}

function create_ical ( $date_from, $duration, $id_user, $title, $description ){
	require("config.php");

	$date_from_date = date('Ymd', strtotime("$date_from"));
	$date_from_time = date('His', strtotime("$date_from"));
	$date_to_date = date('Ymd', strtotime("$date_from + $duration hours"));
	$date_to_time = date('His', strtotime("$date_from + $duration hours"));
	
	// Define the file as an iCalendar file
	$output = "Content-Type: text/Calendar\n";
	// Give the file a name and force download
	$output .= "Content-Disposition: inline; filename=$id_user.ics\n";

	// Header of ics file
	$output .= "BEGIN:VCALENDAR\n";
	$output .= "VERSION:2.0\n";
	$output .= "PRODID:Integria\n";
	$output .= "METHOD:REQUEST\n";
	$output .= "BEGIN:VEVENT\n";
	$output .= "DTSTART:".$date_from_date."T".$date_from_time."\n";
	$output .= "DTEND:".$date_to_date."T".$date_to_time."\n";
	$output .= "DESCRIPTION:";
	$description = str_replace(chr(13).chr(10),"  ", $description);
	$output .= $description."\n";
	$output .=  "SUMMARY:$title\n";
	$output .=  "UID:$id_user\n";
	$output .=  "SEQUENCE:0\n";
	$output .=  "DTSTAMP:".date('Ymd').'T'.date('His')."\n";
	$output .=  "END:VEVENT\n";
	$output .=  "END:VCALENDAR\n";

	return $output;
}



function insert_event ($type, $id1 = 0, $id2 = 0, $id3 = ''){
   	require("config.php");
	$timestamp = date('Y-m-d H:i:s');

	$sql = "INSERT INTO tevent (type, id_user, timestamp, id_item, id_item2, id_item3) VALUES 
			('$type', '".$config["id_user"]."', '$timestamp', $id1, $id2, '$id3')";
	mysql_query($sql);
}

function get_groups ($order = 'nombre') {
	return get_db_all_rows_in_table ('tgrupo', $order);
}

/** 
 * Get all the groups a user has reading privileges.
 * 
 * @param id_user User id
 * @param permission Permission to have in the group (IR by default)
 * 
 * @return A list of the groups the user has reading privileges.
 */
function get_user_groups ($id_user = 0, $permission = 'IR') {
	if ($id_user == 0) {
		global $config;
		$id_user = $config['id_user'];
	}
	$user_groups = array ();
	$groups = get_groups ();

	if (!$groups)
		return $user_groups;

	foreach ($groups as $group) {
		if (! give_acl ($id_user, $group["id_grupo"], $permission))
			continue;
		$user_groups[$group['id_grupo']] = $group['nombre'];
	}
	
	return $user_groups;
}

function get_user_visible_users ($id_user = 0, $access = "IR", $only_name = true) {
	global $config;

	$values = array ();
	
	if ($id_user == 0) {
		$id_user = $config['id_user'];
	}

	$level = get_db_sql("SELECT nivel FROM tusuario WHERE id_usuario = '$id_user'");

    // External user only can see himself
	if ($level == -1){
		$user= array();
		$sql = sprintf ('SELECT *
						FROM tusuario 
						WHERE id_usuario = "%s"', $id_user);
		$user = get_db_all_rows_sql ($sql);
		if ($only_name){
			$user_onlyname ['id_usuario'] = $id_user;
			return $user_onlyname;
		}
		return $user;
	}
		

	if (give_acl ($id_user, 1, $access)) {
		$users = get_db_all_rows_sql ("SELECT * FROM tusuario ORDER BY id_usuario");
		if ($users === false)
			$users = array ();
		foreach ($users as $user) {
			if ($only_name)
				$values[$user['id_usuario']] = $user['nombre_real'];
			else
				$values[$user['id_usuario']] = $user;
		}
	} else {
		$sql = sprintf ('SELECT id_grupo FROM tusuario_perfil
				WHERE id_usuario = "%s"', $id_user);
		$groups = get_db_all_rows_sql ($sql);
		if ($groups === false)
			$groups = array ();
		foreach ($groups as $group) {
			$sql = sprintf ('SELECT *
					FROM tusuario_perfil p, tusuario u
					WHERE p.id_usuario = u.id_usuario
					AND id_grupo = %d', $group['id_grupo']);
			$users = get_db_all_rows_sql ($sql);
			if ($users === false)
				continue;
			foreach ($users as $user) {
				if (! give_acl ($user["id_usuario"], $group['id_grupo'], $access))
					continue;
				if ($only_name)
					$values[$user['id_usuario']] = $user['nombre_real'];
				else
					$values[$user['id_usuario']] = $user;
			}
		}
	}

	
	return $values;
}

function get_inventories ($only_names = true, $exclude_id = false) {
	if ($exclude_id) {
		$sql = sprintf ('SELECT * FROM tinventory WHERE id != %d', $exclude_id);
		$inventories = get_db_all_rows_sql ($sql);
	} else {
		$inventories = get_db_all_rows_in_table ('tinventory');
	}
	if ($inventories == false)
		return array ();
	
	if ($only_names) {
		$retval = array ();
		foreach ($inventories as $inventory) {
			$retval[$inventory['id']] = $inventory['name'];
		}
		return $retval;
	}
	
	return $inventories;
}

function get_inventory ($id_inventory) {
	return get_db_row ('tinventory', 'id', $id_inventory);
}

function get_inventory_name ($id) {
	return (string) get_db_value ('name', 'tinventory', 'id', $id);
}

function get_inventories_in_incident ($id_incident, $only_names = true) {
	$sql = sprintf ('SELECT tinventory.* FROM tincidencia, tincident_inventory, tinventory
			WHERE tincidencia.id_incidencia = tincident_inventory.id_incident
			AND tinventory.id = tincident_inventory.id_inventory
			AND tincidencia.id_incidencia = %d', $id_incident);
	$all_inventories = get_db_all_rows_sql ($sql);
	if ($all_inventories == false)
		return array ();
	
	global $config;
	$inventories = array ();
	foreach ($all_inventories as $inventory) {
		if (! give_acl ($config['id_user'], get_inventory_group ($inventory['id']), 'VR')) {
			$inventory['name'] = ellipsize_string ($inventory['name']);
		}
		array_push ($inventories, $inventory);
	}
	
	if ($only_names) {
		$result = array ();
		foreach ($inventories as $inventory) {
			$result[$inventory['id']] = $inventory['name'];
		}
		return $result;
	}
	return $inventories;
}

function get_inventory_contracts ($id_inventory, $only_names = true) {
	$sql = sprintf ('SELECT tcontract.* FROM tinventory, tcontract
			WHERE tinventory.id_contract = tcontract.id
			AND tinventory.id = %d', $id_inventory);
	$contracts = get_db_all_rows_sql ($sql);
	if ($contracts == false)
		return array ();
	
	if ($only_names) {
		$result = array ();
		foreach ($contracts as $contract) {
			$result[$contract['id']] = $contract['name'];
		}
		return $result;
	}
	return $contracts;
}

function get_inventory_group ($id_inventory, $only_id = true) {
	$sql = sprintf ('SELECT tgrupo.%s FROM tinventory, tcontract, tgrupo
			WHERE tinventory.id_contract = tcontract.id
			AND tcontract.id_group = tgrupo.id_grupo
			AND tinventory.id = %d',
			($only_id ? "id_grupo" : "*"),
			$id_inventory);
	if ($only_id)
		return (int) get_db_sql ($sql);
	return get_db_row_sql ($sql);
}

function get_inventory_affected_companies ($id_inventory, $only_names = true) {
	$sql = sprintf ('SELECT tcompany.* FROM tinventory, tcontract, tcompany
			WHERE tinventory.id_contract = tcontract.id
			AND tcontract.id_company = tcompany.id
			AND tinventory.id = %d', $id_inventory);
	$companies = get_db_all_rows_sql ($sql);
	if ($companies == false)
		return array ();
	
	if ($only_names) {
		$result = array ();
		foreach ($companies as $company) {
			$result[$company['id']] = $company['name'];
		}
		return $result;
	}
	return $companies;
}

function get_incident ($id_incident) {
	return get_db_row ('tincidencia', 'id_incidencia', $id_incident);
}

function get_incident_slas ($id_incident, $only_names = true) {
	$sql = sprintf ('SELECT tsla.*
		FROM tinventory, tsla, tincident_inventory
		WHERE tinventory.id_sla = tsla.id
		AND tincident_inventory.id_inventory = tinventory.id
		AND tincident_inventory.id_incident = %d', $id_incident);
	$slas = get_db_all_rows_sql ($sql);
	if ($slas == false)
		return array ();
	
	if ($only_names) {
		$result = array ();
		foreach ($slas as $sla) {
			$result[$sla['id']] = $sla['name'];
		}
		return $result;
	}
	return $slas;
}


function get_company ($id_company) {
	return get_db_row ('tcompany', 'id', $id_company);
}

function get_companies ($only_names = true) {
	$companies = get_db_all_rows_in_table ('tcompany');
	if ($companies === false)
		return array ();
	
	if ($only_names) {
		$retval = array ();
		foreach ($companies as $company) {
			$retval[$company['id']] = $company['name'];
		}
		return $retval;
	}
	
	return $companies;
}

function get_contract ($id_contract) {
	return get_db_row ('tcontract', 'id', $id_contract);
}

function get_contracts ($only_names = true) {
	$contracts = get_db_all_rows_in_table ('tcontract');
	if ($contracts === false)
		return array ();
	
	if ($only_names) {
		$retval = array ();
		foreach ($contracts as $contract) {
			$retval[$contract['id']] = $contract['name'];
		}
		return $retval;
	}
	
	return $contracts;
}

function get_products ($only_names = true) {
	$products = get_db_all_rows_in_table ('tkb_product');
	if ($products === false)
		return array ();
	
	if ($only_names) {
		$retval = array ();
		foreach ($products as $product) {
			$retval[$product['id']] = $product['name'];
		}
		return $retval;
	}
	
	return $products;
}

function get_company_contacts ($id_company, $only_names = true) {
	$sql = sprintf ('SELECT * FROM tcompany_contact
			WHERE id_company = %d', $id_company);
	$contacts = get_db_all_rows_sql ($sql);
	if ($contacts == false)
		return array ();
	
	if ($only_names) {
		$result = array ();
		foreach ($contacts as $contact) {
			$result[$contact['id']] = $contact['name'];
		}
		return $result;
	}
	return $contacts;
}

function get_incident_workunits ($id_incident) {
	$workunits = get_db_all_rows_field_filter ('tworkunit_incident', 'id_incident',
					$id_incident, 'id_workunit DESC');
	if ($workunits === false)
		return array ();
	return $workunits;
}

function get_inventory_workunits ($id_inventory) {
	$sql = sprintf ("SELECT tworkunit.*, tincidencia.id_incidencia as id_incident
		FROM tworkunit, tworkunit_incident, tincidencia, tincident_inventory
		WHERE tworkunit.id = tworkunit_incident.id_workunit
		AND tworkunit_incident.id_incident = tincidencia.id_incidencia
		AND tincidencia.id_incidencia = tincident_inventory.id_incident
		AND tincident_inventory.id_inventory = %d ORDER BY timestamp DESC",
		$id_inventory);
	$workunits = get_db_all_rows_sql ($sql);
	if ($workunits === false)
		return array ();
	return $workunits;
}

function get_workunit_data ($id_workunit) {
	return get_db_row ('tworkunit', 'id', $id_workunit);
}

function get_building ($id_building) {
	return get_db_row ('tbuilding', 'id', $id_building);
}

function get_buildings ($only_names = true) {
	$buildings = get_db_all_rows_in_table ('tbuilding');
	if ($buildings === false)
		return array ();
	
	if ($only_names) {
		$retval = array ();
		foreach ($buildings as $building) {
			$retval[$building['id']] = $building['name'];
		}
		return $retval;
	}
	
	return $buildings;
}

function print_product_icon ($id_product, $return = false) {
	$output = '';
	
	$icon = (string) get_db_value ('icon', 'tkb_product', 'id', $id_product);
	
	$output .= '<img id="product-icon" width="16" height="16" ';
	if ($icon != '') {
		$output .= 'src="images/products/'.$icon.'"';
	} else {
		$output .= 'src="images/pixel_gray.png" style="display:none"';
	}
	$output .= ' />';
	
	if ($return)
		return $output;
	echo $output;
}

function get_manufacturers ($only_names = true) {
	$manufacturers = get_db_all_rows_in_table ('tmanufacturer');
	if ($manufacturers === false)
		return array ();
	
	if ($only_names) {
		$retval = array ();
		foreach ($manufacturers as $manufacturer) {
			$retval[$manufacturer['id']] = $manufacturer['name'];
		}
		return $retval;
	}
	
	return $manufacturers;
}

function get_sla ($id_sla) {
	return get_db_row ('tsla', 'id', $id_sla);
}

function get_slas ($only_names = true) {
	$slas = get_db_all_rows_in_table ('tsla');
	if ($slas == false)
		return array ();
	
	if ($only_names) {
		$result = array ();
		foreach ($slas as $sla) {
			$result[$sla['id']] = $sla['name'];
		}
		return $result;
	}
	return $slas;
}

function get_contract_sla ($id_contract, $only_name = true) {
	$sql = sprintf ('SELECT tsla.* FROM tcontract, tsla
			WHERE tcontract.id_sla = tsla.id
			AND tcontract.id = %d', $id_contract);
	$sla = get_db_row_sql ($sql);
	if ($sla == false)
		return array ();
	
	if ($only_name) {
		$result = array ();
		$result[$sla['id']] = $sla['name'];
		return $result;
	}
	return $sla;
}

function get_incidents_on_inventory ($id_inventory, $only_names = true) {
	$sql = sprintf ('SELECT tincidencia.*
			FROM tincidencia, tincident_inventory
			WHERE tincidencia.id_incidencia = tincident_inventory.id_incident
			AND tincident_inventory.id_inventory = %d
			ORDER BY tincidencia.inicio DESC',
			$id_inventory);
	$all_incidents = get_db_all_rows_sql ($sql);
	if ($all_incidents == false)
		return array ();
	
	global $config;
	$incidents = array ();
	foreach ($all_incidents as $incident) {
		if (give_acl ($config['id_user'], $incident['id_grupo'], 'IR')) {
			if ($only_names) {
				$incidents[$incident['id']] = $incident['name'];
			} else {
				array_push ($incidents, $incident);
			}
		}
	}
	return $incidents;
}

function get_incident_types ($only_names = true) {
	$types = get_db_all_rows_in_table ('tincident_type');
	if ($types == false)
		return array ();
	
	if ($only_names) {
		$result = array ();
		foreach ($types as $type) {
			$result[$type['id']] = $type['name'];
		}
		return $result;
	}
	return $types;
}

function print_user_avatar ($id_user = "", $small = false, $return = false) {
	if ($id_user == "") {
		global $config;
		$id_user = $config['id_user'];
	}
	$avatar =  get_db_value ('avatar', 'tusuario', 'id_usuario', $id_user);
	$output = '';
	if ($avatar != '') 
		$output .= '<img src="images/avatars/'.$avatar.($small ? '_small' : '').'.png" />';
	
	if ($return)
		return $output;
	echo $output;
}

function create_custom_search ($name, $section, $search_values) {
	global $config;
	
	$sql = sprintf ('INSERT INTO tcustom_search (section, name, id_user,
		form_values) VALUES ("%s", "%s", "%s", \'%s\')', 
		$section, $name, $config['id_user'],
		clean_output (serialize ($search_values)));
	return process_sql ($sql, 'insert-id');
}

function get_custom_search ($id_search, $section) {
	global $config;
	
	$sql = sprintf ('SELECT * FROM tcustom_search
		WHERE id = %d
		AND id_user = "%s"
		AND section = "%s"',
		$id_search, $config['id_user'], $section);
	return get_db_row_sql ($sql);
}

function get_incident_files ($id_incident) {
	return get_db_all_rows_field_filter ('tattachment', 'id_incidencia', $id_incident);
}

function get_incident_tracking ($id_incident) {
	return get_db_all_rows_field_filter ('tincident_track', 'id_incident', $id_incident);
}

function get_incident_users ($id_incident) {
	$incident = get_incident ($id_incident);
	$users = array ();
	$userswu = array();
	
	$users['owner'] = get_db_row ('tusuario', 'id_usuario', $incident['id_usuario']);
	$users['creator'] = get_db_row ('tusuario', 'id_usuario', $incident['id_creator']);
	$userswu  = people_involved_incident ($id_incident);
	$users['affected'] = array();

	foreach ($userswu as $user) {
		if ($users['owner']['id_usuario'] == $user)
			continue;
		if ($users['creator']['id_usuario'] == $user)
			continue;
	}
	return $users;
}

function check_incident_sla_min_response ($id_incident) {
	$incident = get_incident ($id_incident);
	
	/* If closed, disable any affected SLA */
	if ($incident['estado'] == 6 || $incident['estado'] == 7) {
		if ($incident['affected_sla_id']) {
			$sql = sprintf ('UPDATE tincidencia
				SET affected_sla_id = 0
				WHERE id_incidencia = %d',
				$id_incident);
			process_sql ($sql);
		}
		return false;
	}

	$slas = get_incident_slas ($id_incident, false);

	$start = strtotime ($incident['inicio']);
	$now = time ();

	/* Check wheter it was updated before, so there's no need to check SLA */
	$update = strtotime ($incident['actualizacion']);


    /* Clean status of fired SLA */
	if ($update > $start) {
		if ($incident['affected_sla_id']) {
			$sql = sprintf ('UPDATE tincidencia
				SET affected_sla_id = 0
				WHERE id_incidencia = %d',
				$id_incident);
			process_sql ($sql);
		}
	}

    // Check SLA here.
	foreach ($slas as $sla) {

		if ($now < ($start + $sla['min_response'] * 3600))
			 continue;

        // Incident owner is the last workunit author ?, then SKIP
    	$last_wu = get_incident_lastworkunit ($id_incident);

    	if ($last_wu["id_user"] == $incident["id_usuario"]){
            continue;
        }

        if ($last_wu["timestamp"] != ""){
            $last_wu_time = strtotime ($last_wu['timestamp']);
        
    		if ($now < ($last_wu_time + $sla['min_response'] * 3600))
    			 continue;
        }

        // Datetime/Time check when exists (version compatibility code), this
        // was added as a 3.0 post-feature :-)

        if (isset($sla["five_daysonly"])){

            $dow = date("w", time());
            $hod = date("G", time());

            // Skip if we're on weekend
            if (($sla["five_daysonly"] == 1) AND (($dow == 0) OR ($dow == 6))){
                continue;
            }

            // Skip if we're out of job time
            if ($sla["time_from"] != $sla["time_to"]){
                if (($sla["time_from"] > $hod) OR ($sla["time_to"] < $hod)){
                    continue;
                }
            }
        }


		$sql = sprintf ('UPDATE tincidencia
			SET affected_sla_id = %d
			WHERE id_incidencia = %d',
			$sla['id'], $id_incident);
		process_sql ($sql);
		
		/* SLA has been fired */
		return $sla['id'];
	}

    // No SLA fired.
	return false;
}

function check_incident_sla_max_response ($id_incident) {
	$incident = get_incident ($id_incident);
	
	/* If closed, disable any affected SLA */
	if ($incident['estado'] == 6 || $incident['estado'] == 7) {
		if ($incident['affected_sla_id']) {
			$sql = sprintf ('UPDATE tincidencia
				SET affected_sla_id = 0
				WHERE id_incidencia = %d',
				$id_incident);
			process_sql ($sql);
		}
		return false;
	}

	// Get the writer of the last WU incident
	$last_wu = get_incident_lastworkunit ($id_incident);
	if ($last_wu["id_user"] == $incident["id_creator"]){
		if ($incident['affected_sla_id']) {
			$sql = sprintf ('UPDATE tincidencia
                               SET affected_sla_id = 0
                               WHERE id_incidencia = %d',
                               $id_incident);
	                        process_sql ($sql);
		}
		return false;
	}
	

	$slas = get_incident_slas ($id_incident, false);
	$start = strtotime ($incident['inicio']);
	$now = time ();
	foreach ($slas as $sla) {


        // Datetime/Time check when exists (version compatibility code), this
        // was added as a 3.0 post-feature :-)

        if (isset($sla["five_daysonly"])){

            $dow = date("w", time());
            $hod = date("G", time());

            // Skip if we're on weekend
            if (($sla["five_daysonly"] == 1) AND (($dow == 0) OR ($dow == 6))){
                continue;
            }

            // Skip if we're out of job time
            if ($sla["time_from"] != $sla["time_to"]){
                if (($sla["time_from"] > $hod) OR ($sla["time_to"] < $hod)){
                    continue;
                }
            }
        }

		if ($now < ($start + $sla['max_response'] * 3600))
			 continue;
		$sql = sprintf ('UPDATE tincidencia
			SET affected_sla_id = %d
			WHERE id_incidencia = %d',
			$sla['id'], $id_incident);
		process_sql ($sql);
		
		/* SLA has expired */
		return $sla['id'];
	}
	
	return false;
}

function get_group_default_user ($id_group) {
	$id_user = get_db_value ('id_user_default', 'tgrupo', 'id_grupo', $id_group);
	return get_db_row ('tusuario', 'id_usuario', $id_user);
}

/** 
 * Returns the n most active users (users with more hours worked on incidents).
 *
 * @param lim n, number of users to return.
 */
function get_most_active_users ($lim) {
	$most_active_users = get_db_all_rows_sql ('SELECT id_user, SUM(duration) as worked_hours
	                                          FROM tworkunit, tworkunit_incident
	                                          WHERE tworkunit.id = tworkunit_incident.id_workunit
	                                          GROUP BY id_user
	                                          ORDER BY worked_hours DESC LIMIT ' . $lim);
	if ($most_active_users === false) {
		return array ();
	}

	return $most_active_users;
}

/** 
 * Returns the n most active incidents (incidents with more worked hours).
 *
 * @param lim n, number of incidents to return.
 */
function get_most_active_incidents ($lim) {
	$most_active_incidents = get_db_all_rows_sql ('SELECT tincidencia.id_incidencia, titulo, SUM(duration) AS worked_hours
	                                               FROM tworkunit, tworkunit_incident, tincidencia
	                                               WHERE tworkunit.id = tworkunit_incident.id_workunit
	                                                 AND tworkunit_incident.id_incident = tincidencia.id_incidencia
	                                               GROUP BY tworkunit_incident.id_incident
	                                               ORDER BY worked_hours DESC LIMIT ' . $lim);
	if ($most_active_incidents === false) {
		return array ();
	}

	return $most_active_incidents;
}

/** 
 * Returns the incident SLA compliance percentage, from a list of incidents, passed as arguments
 *
 * @param, incidents, array with a list of incidents
 */
function get_sla_compliance ($incidents) {

    $total = 0;
    $sum = 0;

    foreach ($incidents as $incident) {
        $total++;
		if ($incident['affected_sla_id'] > 0)
            $sum = $sum + 1;
    }

    $OK = $total - $sum;

    // Division by zero check
    if ($total == 0)
        return 100;

    $sla_compliance = ( $OK / $total) * 100; 

	return $sla_compliance;

}

function get_task_end_date_by_user ($now){
	global $config;
	
	$result = array();

	// Search for Project end in this date
	$sql = "SELECT tproject.name as pname, ttask.name as tname, ttask.end as tend, ttask.id as idt, trole_people_task.id_user as user FROM trole_people_task, tproject, ttask WHERE tproject.id = ttask.id_project AND trole_people_task.id_task = ttask.id AND ttask.end = '$now' GROUP BY idt, user";
	$res = mysql_query ($sql);
	while ($row=mysql_fetch_array ($res)){
		$result[] = $row["tname"] ."|".$row["idt"]."|".$row["tend"]."|".$row["pname"]."|".$row["user"];
	}
	return $result;
}

function create_wu_task ($id_task, $id_user, $description, $have_cost, $id_profile, $public, $duration, $timestamp){
	global $config;
	$sql = sprintf ('INSERT INTO tworkunit 
					(timestamp, duration, id_user, description, have_cost, id_profile, public) 
					VALUES ("%s", %f, "%s", "%s", %d, %d, %d)',
					$timestamp, $duration, $id_user, $description, $have_cost, $id_profile, $public);
	$id_workunit = process_sql ($sql, 'insert_id');
	if ($id_workunit !== false) {
		$sql = sprintf ('INSERT INTO tworkunit_task (id_task, id_workunit) VALUES (%d, %d)', -3, $id_workunit);
		$result = process_sql ($sql, 'insert_id');
		return $id_workunit;
	}
	return false;
}


function get_indicent_status () {
	$retval = array ();
	$status = get_db_all_rows_in_table ('tincident_status');
	
	__('New');
	__('Unconfirmed');
	__('Assigned');
	__('Re-opened');
	__('Verified'); 
	__('Resolved');
	__('Closed');
	
	foreach ($status as $stat) {
		/* FIXME: This is a workaround since you don't change or add any status
		 on Integria setup */
		$retval[$stat['id']] = __($stat['name']);
	}
	
	return $retval;
}

function get_incident_resolutions () {
	$retval = array ();
	$resolutions = get_db_all_rows_in_table ('tincident_resolution');
	
	/* Translators: stands for "Incident is fixed" */
	__('Fixed');
	__('Invalid');
	__('Wont fix');
	__('Duplicate');
	__('Works for me');
	__('Incomplete');
	__('Expired');
	__('Moved');
	__('In process');
	
	foreach ($resolutions as $resolution) {
		$retval[$resolution['id']] = __($resolution['name']);
	}
	
	return $retval;
}

function get_incident_origins () {
	$retval = array ();
	$origins = get_db_all_rows_in_table ('tincident_origin');
	
	/* Translators: Incident is fixed */
	__('User report');
	__('Customer');
	__('Internal department');
	__('External department');
	__('Application data');
	__('Bug report');
	__('Problem detected');
	__('Other source');
	
	foreach ($origins as $origin) {
		$retval[$origin['id']] = __($origin['name']);
	}
	
	return $retval;
}

function render_resolution ($res){
	$res2 =  get_db_sql ("SELECT name FROM tincident_resolution WHERE id = ".$res);
	if ($res2 == "")
		return __("None");
	return __($res2);
}

function render_status ($sta){
	$estado = get_db_sql ("SELECT name FROM tincident_status WHERE id = ".$sta);
	return __($estado);
}



?>
