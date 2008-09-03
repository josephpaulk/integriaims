<?php

// Integria 2.0 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2008 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the  GNU Lesser General Public License
// as published by the Free Software Foundation; version 2

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// --------------------------------------------------------------- 
// give_acl ()
// Main Function to get access to resources
// Return 0 if no access, > 0  if access
// --------------------------------------------------------------- 

function give_acl($id_user, $id_group, $access){	
	$access = strtoupper ($access);
	// IF user is level = 1 then always return 1
	// Access codes could be
	/*	
		IR - Incident Read
		IW - Incident Write
		IM - Incident Management

		UM - User Management
		DM - DB Management
		FM - Integria management

		AR - Agenda read
		AW - Agenda write
		AM - Agenda management

		PR - Project read
		PW - Project write
		PM - Project management

		TR - Task read
		TM - Task management

		KR - Knowledge Base READ
		KW - Knowledge Base WRITE
		KM - Knowledge Base Manage
	*/

	global $config;
	$query1 = "SELECT * FROM tusuario WHERE id_usuario = '".$id_user."'";
	$res=mysql_query($query1);
	$row=mysql_fetch_array($res);
	if ($row["nivel"] == 1)
		return 1;
	
	if ($id_group == 0) // Group doesnt matter, any group, for check permission to do at least an action in a group
		$query1="SELECT * FROM tusuario_perfil WHERE id_usuario = '".$id_user."'";	// GroupID = 0, group doesnt matter (use with caution!)
	else
		$query1="SELECT * FROM tusuario_perfil WHERE id_usuario = '".$id_user."' and ( id_grupo =".$id_group." OR id_grupo = 1)";	// GroupID = 1 ALL groups	  
	$resq1=mysql_query($query1);  
	$result = 0; 
	while ($rowdup=mysql_fetch_array($resq1)){
		$id_perfil=$rowdup["id_perfil"];
		// For each profile for this pair of group and user do...
		$query2 = "SELECT * FROM tprofile WHERE id = ".$id_perfil;
		$resq2 = mysql_query ($query2);
		if ($rowq2 = mysql_fetch_array ($resq2)) {
			switch ($access) {
			case "IR":
				$result += $rowq2["ir"];
				break;
			case "IW":
				$result += $rowq2["iw"];
				break;
			case "IM":
				$result += $rowq2["im"];
				break;
			case "AR":
				$result += $rowq2["ar"];
				break;
			case "AW":
				$result += $rowq2["aw"];
				break;
			case "AM":
				$result += $rowq2["am"];
				break;
			case "FM":
				$result += $rowq2["fm"];
				break;
			case "DM":
				$result += $rowq2["dm"];
				break;
			case "UM":
				$result += $rowq2["um"];
				break;
			case "PR":
				$result += $rowq2["pr"];
				break;
			case "PM":
				$result += $rowq2["pm"];
				break;
			case "PW":
				$result += $rowq2["pw"];
				break;
			case "TW":
				$result += $rowq2["tw"];
				break;
			case "TM":
				$result += $rowq2["tm"];
				break;
			case "KR":
				$result += $rowq2["kr"];
				break;
			case "KW":
				$result += $rowq2["kw"];
				break;
			case "KM": 
				$result += $rowq2["km"];
				break;
			default:
				$result = $rowq2["tm"] + $rowq2["tw"] + $rowq2["pw"] + $rowq2["pm"] + $rowq2["pr"]+ $rowq2["um"]+ $rowq2["dm"] + $rowq2["fm"] + $rowq2["am"] + $rowq2["aw"] + $rowq2["ar"] + $rowq2["im"] +$rowq2["iw"] + $rowq2["ir"] + $rowq2["kr"] + $rowq2["kw"] + $rowq2["km"] ;
			}
		} 
	}
	
	if ($result > 1)
		$result = 1;
	return $result; 
} 

// --------------------------------------------------------------- 
// audit_db, update audit log
// --------------------------------------------------------------- 

function audit_db ($id, $ip, $accion, $descripcion){
	require("config.php");
	$today=date('Y-m-d H:i:s');
	$utimestamp = time();
	$sql1='INSERT INTO tsesion (ID_usuario, accion, fecha, IP_origen,descripcion, utimestamp) VALUES ("'.$id.'","'.$accion.'","'.$today.'","'.$ip.'","'.$descripcion.'", '.$utimestamp.')';
	$result=mysql_query($sql1);
}


// --------------------------------------------------------------- 
// logon_db, update entry in logon audit
// --------------------------------------------------------------- 

function logon_db($id,$ip){
	require("config.php");
	audit_db($id,$ip,"Logon","Logged in");
	// Update last registry of user to get last logon
	$sql2='UPDATE tusuario fecha_registro = $today WHERE id_usuario = "$id"';
	$result=mysql_query($sql2);
}

// --------------------------------------------------------------- 
// logoff_db, also adds audit log
// --------------------------------------------------------------- 

function logoff_db($id,$ip){
	require("config.php");
	audit_db($id,$ip,"Logoff","Logged out");
}

// --------------------------------------------------------------- 
// Returns profile given ID
// --------------------------------------------------------------- 

function dame_perfil($id){ 
	require("config.php");
	$query1="SELECT * FROM tprofile WHERE id =".$id;
	$resq1=mysql_query($query1);  
	if ($rowdup=mysql_fetch_array($resq1)){
		$cat=$rowdup["name"]; 
	}
		else $cat = "";
	return $cat; 
}


// --------------------------------------------------------------- 
// Returns group given ID
// --------------------------------------------------------------- 

function dame_grupo($id){ 
	require("config.php");
	$query1="SELECT * FROM tgrupo WHERE id_grupo =".$id;
	$resq1=mysql_query($query1);
	if ($rowdup=mysql_fetch_array($resq1)){
		$cat=$rowdup["nombre"];
	}
		else $cat = "";
	return $cat; 
}

// --------------------------------------------------------------- 
// Returns icon name given group ID
// --------------------------------------------------------------- 

function dame_grupo_icono($id){
	require("config.php");
	$query1="SELECT * FROM tgrupo WHERE id_grupo =".$id;
	$resq1=mysql_query($query1);
	if ($rowdup=mysql_fetch_array($resq1)){
		$cat=$rowdup["icon"];
	}
		else $cat = "";
	return $cat;
}

// --------------------------------------------------------------- 
// Return agent id given name of agent
// --------------------------------------------------------------- 

function dame_agente_id($nombre){
	require("config.php");
	$query1="SELECT * FROM tagente WHERE nombre = '".$nombre."'";
	$resq1=mysql_query($query1);
	if ($rowdup=mysql_fetch_array($resq1))
		$pro=$rowdup["id_agente"];
	else
		$pro = "";
	return $pro;
}


// --------------------------------------------------------------- 
// Returns userid given name an note id
// --------------------------------------------------------------- 

function give_note_author ($id_note){ 
	require("config.php");
	$query1="SELECT * FROM tnota WHERE id_nota = ".$id_note;
	$resq1=mysql_query($query1);
	if ($rowdup=mysql_fetch_array($resq1))
		$pro=$rowdup["id_usuario"];
	else
		$pro = "";
	return $pro;
}


// --------------------------------------------------------------- 
// Returns agent id given name of agent
// --------------------------------------------------------------- 

function dame_agente_modulo_id($id_agente, $id_tipomodulo, $nombre){
	require("config.php");
	$query1="SELECT * FROM tagente_modulo WHERE id_agente = ".$id_agente." and id_tipo_modulo = ".$id_tipomodulo." and nombre = '".$nombre."'";
	$resq1=mysql_query($query1);
	if ($rowdup=mysql_fetch_array($resq1))
		$pro=$rowdup["id_agente_modulo"];
	else
		$pro = "";
	return $pro;
}


// --------------------------------------------------------------- 
// Returns event description given it's id
// --------------------------------------------------------------- 

function return_event_description ($id_event){
	require("config.php");
	$query1="SELECT evento FROM tevento WHERE id_evento = $id_event";
	$resq1=mysql_query($query1);
	if ($rowdup=mysql_fetch_array($resq1))
		$pro=$rowdup[0];
	else
		$pro = "";
	return $pro;
}

// --------------------------------------------------------------- 
// Return ID_Group from an event given as id_event
// --------------------------------------------------------------- 

function gime_idgroup_from_idevent($id_event){
	require("config.php");
	$query1="SELECT * FROM tevento WHERE id_evento = ".$id_event;
	$pro = -1;
	if ($resq1=mysql_query($query1))
		if ($rowdup=mysql_fetch_array($resq1))
			$pro=$rowdup["id_grupo"]; 
	return $pro;
}


// --------------------------------------------------------------- 
// Returns password (HASH) given user_id
// --------------------------------------------------------------- 

function dame_password($id_usuario){
	require("config.php"); 
	$query1="SELECT * FROM tusuario WHERE id_usuario= '".$id_usuario."'"; 
	$resq1=mysql_query($query1);  
	if ($rowdup=mysql_fetch_array($resq1))
		$pro=$rowdup["password"]; 
	else
		$pro = "";
	return $pro; 
}

// --------------------------------------------------------------- 
// Returns name of the user when given ID
// --------------------------------------------------------------- 

function dame_nombre_real($id){
	require("config.php");
	$query1="SELECT * FROM tusuario WHERE id_usuario = '".$id."'";
	$resq1=mysql_query($query1);
	if ($rowdup=mysql_fetch_array($resq1))
		$pro=$rowdup["nombre_real"];
	else
		$pro = "";
	return $pro;
}


// --------------------------------------------------------------- 
// This function returns ID of user who has created incident
// --------------------------------------------------------------- 

function give_incident_author($id){
	require("include/config.php");
	$query1="SELECT * FROM tincidencia WHERE id_incidencia = '".$id."'";
	$resq1=mysql_query($query1);
	if ($rowdup=mysql_fetch_array($resq1))
		$pro=$rowdup["id_usuario"];
	else
		$pro = "";
	return $pro;
}


// --------------------------------------------------------------- 
// Return name of a group when given ID
// --------------------------------------------------------------- 

function dame_nombre_grupo ($id) {
	return get_db_value ('nombre', 'tgrupo', 'id_grupo', $id);
} 



// --------------------------------------------------------------- 
// Returns number of files from a given incident
// --------------------------------------------------------------- 

function give_number_files_incident ($id){
	require("config.php"); 
	$query1="select COUNT(*) from tattachment WHERE id_incidencia =".$id;
	$resq1=mysql_query($query1);
	if ($rowdup=mysql_fetch_array($resq1)){
		$pro=$rowdup[0]; 
	} else 
		$pro = 0;
	return $pro;
}


// --------------------------------------------------------------- 
// Returns number of files from a given incident
// --------------------------------------------------------------- 

function give_number_files_task ($id){
	require("config.php"); 
	$query1="select COUNT(*) from tattachment WHERE id_task =".$id;
	$resq1=mysql_query($query1);
	if ($rowdup=mysql_fetch_array($resq1)){
		$pro=$rowdup[0]; 
	} else 
		$pro = 0;
	return $pro;
}
/**
* Return number of tasks associated to an incident
*
* $item		integer 	ID of project
**/

function give_number_tasks ($id_project){
	global $config;
	$query1="SELECT COUNT(*) FROM ttask WHERE id_project =".$id_project;
	$resq1=mysql_query($query1);
	if ($rowdup=mysql_fetch_array($resq1)){
		$pro=$rowdup[0]; 
	} else 
		$pro = 0;
	return $pro;
}

/**
* Return total hours assigned to incident
*
* $id_inc	integer 	ID of incident
**/

function give_hours_incident ($id_inc){
	global $config;
	$query1="SELECT SUM(tworkunit.duration) 
			FROM tworkunit, tworkunit_incident, tincidencia 
			WHERE 	tworkunit_incident.id_incident = tincidencia.id_incidencia AND 
					tworkunit_incident.id_workunit = tworkunit.id AND
					 tincidencia.id_incidencia = $id_inc";
	$resq1=mysql_query($query1);
	if ($rowdup=mysql_fetch_array($resq1))
		$pro=$rowdup[0]; 
	else 
		$pro = 0;
	return $pro;
}


/**
* Return total wu assigned to incident
*
* $id_inc   integer	 ID of incident
**/

function give_wu_incident ($id_inc){
	global $config;
	$query1="SELECT COUNT(tworkunit.duration) 
			FROM tworkunit, tworkunit_incident, tincidencia 
			WHERE   tworkunit_incident.id_incident = tincidencia.id_incidencia AND 
					tworkunit_incident.id_workunit = tworkunit.id AND
					 tincidencia.id_incidencia = $id_inc";
	$resq1=mysql_query($query1);
	if ($rowdup=mysql_fetch_array($resq1))
		$pro=$rowdup[0]; 
	else 
		$pro = 0;
	return $pro;
}


/**
* Return total hours assigned to project
*
* $id_project	integer 	ID of project
**/

function give_hours_project ($id_project, $with_cost =0){
	global $config;
	if ($with_cost != 0)
		$query1="SELECT SUM(tworkunit.duration) 
			FROM tworkunit, tworkunit_task, ttask 
			WHERE   tworkunit_task.id_task = ttask.id AND 
					ttask.id_project = $id_project AND 
					tworkunit_task.id_workunit = tworkunit.id AND
					tworkunit.have_cost = 1";
	else
	   $query1="SELECT SUM(tworkunit.duration) 
			FROM tworkunit, tworkunit_task, ttask 
			WHERE 	tworkunit_task.id_task = ttask.id AND 
					ttask.id_project = $id_project AND 
					tworkunit_task.id_workunit = tworkunit.id";
	$resq1=mysql_query($query1);
	if ($rowdup=mysql_fetch_array($resq1))
		$pro=$rowdup[0]; 
	else 
		$pro = 0;
	return $pro;
}

/**
* Return total wu assigned to project
*
* $id_project   integer	 ID of project
**/

function give_wu_project ($id_project){
	global $config;
	$query1="SELECT COUNT(tworkunit.duration) 
			FROM tworkunit, tworkunit_task, ttask 
			WHERE   tworkunit_task.id_task = ttask.id AND 
					ttask.id_project = $id_project AND 
					tworkunit_task.id_workunit = tworkunit.id";
	$resq1=mysql_query($query1);
	if ($rowdup=mysql_fetch_array($resq1))
		$pro=$rowdup[0]; 
	else 
		$pro = 0;
	return $pro;
}


/**
* Return total hours assigned to task
*
* $id_task	integer 	ID of task
**/

function give_hours_task ($id_task){
	global $config;
	$query1="SELECT SUM(tworkunit.duration) 
			FROM tworkunit, tworkunit_task
			WHERE 	tworkunit_task.id_task = $id_task AND 
					tworkunit_task.id_workunit = tworkunit.id";
	$resq1=mysql_query($query1);
	if ($rowdup=mysql_fetch_array($resq1))
		$pro=$rowdup[0]; 
	else 
		$pro = 0;
	return $pro;
}


/**
* Return total workunits assigned to task
*
* $id_task  integer	 ID of task
**/

function give_wu_task ($id_task){
	global $config;

	$pro = 0;
	$query1="SELECT COUNT(tworkunit.duration) 
			FROM tworkunit, tworkunit_task
			WHERE   tworkunit_task.id_task = $id_task AND 
					tworkunit_task.id_workunit = tworkunit.id";
	$resq1=mysql_query($query1);
	if ($rowdup=mysql_fetch_array($resq1))
		$pro=$rowdup[0]; 
	return $pro;
}


/**
* Return total workunits assigned to task for a specific user
*
* $id_task  integer	 ID of task
* $id_user  string	  ID of user
**/

function give_wu_task_user ($id_task, $id_user){
	global $config;

	$pro = 0;
	$query1="SELECT COUNT(tworkunit.duration) 
			FROM tworkunit, tworkunit_task 
			WHERE   tworkunit_task.id_task = $id_task AND 
					tworkunit.id_user = '$id_user' AND 
					tworkunit_task.id_workunit = tworkunit.id";
	$resq1=mysql_query($query1);
	if ($rowdup=mysql_fetch_array($resq1))
		$pro=$rowdup[0]; 
	return $pro;
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
	$query1="SELECT * FROM ttask 
			WHERE id_project = $id_project";
	$resq1=mysql_query($query1);
	$sum = 0;
	$tot = 0;
	while ($row=mysql_fetch_array($resq1)){
		if ($row["priority"] > 0)
			$sum = $sum + $row["completion"];
		$tot++;
	}
	if ($tot > 0)
		return $sum / $tot;
	else
		return 0;
}


// --------------------------------------------------------------- 
// Delete incident given its id and all its notes
// --------------------------------------------------------------- 


function borrar_incidencia($id_inc){
	require("config.php");
	$sql1="DELETE FROM tincidencia WHERE id_incidencia = ".$id_inc;
	$result=mysql_query($sql1);
	$sql3="SELECT * FROM tworkunit_incident WHERE id_incident = ".$id_inc;
	$res2=mysql_query($sql3);
	while ($row2=mysql_fetch_array($res2)){
		// Delete all note ID related in table
		$sql4 = "DELETE FROM tworkunit WHERE id = ".$row2["id_workunit"];
		$result4 = mysql_query($sql4);
	}
	$sql6="DELETE FROM tworkunit_incident WHERE id_incident = ".$id_inc;
	$result6=mysql_query($sql6);
	// Delete attachments
	$sql1="SELECT * FROM tattachment WHERE id_incidencia = ".$id_inc;
	$result=mysql_query($sql1);
	while ($row=mysql_fetch_array($result)){
		// Unlink all attached files for this incident
		$file_id = $row["id_attachment"];
		$filename = $row["filename"];
		unlink ($attachment_store."attachment/pand".$file_id."_".$filename);
	}
	$sql1="DELETE FROM tattachment WHERE id_incidencia = ".$id_inc;
	$result=mysql_query($sql1);
	$sql1="DELETE FROM tincident_track WHERE id_incident = ".$id_inc;
	$result=mysql_query($sql1);
}


// --------------------------------------------------------------- 
//  Update "contact" field in User table for username $nick
// --------------------------------------------------------------- 

function update_user_contact($nick){	
	require("config.php");
	$today=date("Y-m-d H:i:s",time());
	$query1="UPDATE tusuario set fecha_registro ='".$today."' WHERE id_usuario = '".$nick."'";
	$resq1=mysql_query($query1);
}


// ---------------------------------------------------------------
// Returns Admin value (0 no admin, 1 admin)
// ---------------------------------------------------------------

function dame_admin($id){
	require("config.php");
	$query1="SELECT * FROM tusuario WHERE id_usuario ='".$id."'";   
	$rowdup=mysql_query($query1);
	$rowdup2=mysql_fetch_array($rowdup);
	$admin=$rowdup2["nivel"];
	return $admin;
}

// --------------------------------------------------------------- 
// Gives error message and stops execution if user 
//doesn't have an open session and this session is from an valid user
// --------------------------------------------------------------- 

function check_login () { 
	if (isset($_SESSION["id_usuario"])){
		$id = $_SESSION["id_usuario"];
		require ("config.php");
		$query1="SELECT * FROM tusuario WHERE id_usuario = '".$id."'";
		$resq1=mysql_query($query1);
		$rowdup=mysql_fetch_array($resq1);
		$nombre=$rowdup["id_usuario"];
		if ( $id == $nombre ){
			return 0 ;	
		}
	}
	require("general/noaccess.php");
	return 1;	
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
// Return all childs groups of a given id_group inside array $child
// ---------------------------------------------------------------

function give_groupchild($id_group, &$child){
		// Conexion con la base Datos 
		$query1="select * from tgrupo where parent = ".$id_group;
		$resq1=mysql_query($query1);  
		while ($resq1 != NULL && $rowdup=mysql_fetch_array($resq1)){
			$child[]=$rowdup["id_grupo"];
		}
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
	//$parent_row =  get_db_row ("ttask", "id", $taskrow["id_parent_task"]);
	//if (strtotime($parent_row["start"]) > strtotime($taskrow["start"]))
		//return $parent_row["start"];
	//else
		return $taskrow["start"];
}

// ---------------------------------------------------------------
// Return true (1) if userid belongs to given project as any role
// ---------------------------------------------------------------

function user_belong_project ($id_user, $id_project, $real = 0){ 
	global $config;
		global $lang_label;

	if ($real == 0){
		if (dame_admin ($id_user) != 0)
			return 1;
	}

	$query1="SELECT COUNT(*) from trole_people_project WHERE id_project = $id_project AND id_user = '$id_user'";
		$resq1=mysql_query($query1);
	if ($resq1){
			$rowdup=mysql_fetch_array($resq1);
		if ($rowdup[0] == 0)
			return 0;
		else
			return 1; // There is at least one role for this person in that project
	} else 
		return 0;
}

// ---------------------------------------------------------------
// Return true (1) if userid belongs to given task as any role
// ---------------------------------------------------------------

function user_belong_task ($id_user, $id_task, $real=0){ 
	global $config;
		global $lang_label;

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


// ---------------------------------------------------------------
// Return true (1) if given group (a) belongs to given groupset
// ---------------------------------------------------------------

function group_belong_group($id_group_a, $id_groupset){
		// Conexion con la base Datos 
	$childgroup[] = "";
	if ($id_group_a == $id_groupset)
		return 1;
	give_groupchild($id_groupset, $childgroup);
	foreach ($childgroup as $key => $value){
		if (($value != $id_groupset) AND
			(group_belong_group($id_group_a, $value) == 1))
			return 1;
  	}
	if (array_in ($childgroup, $id_group_a) == 1)
		return 1; 
	else 
		return 0;
}


// --------------------------------------------------------------- 
// Return incident priority
// --------------------------------------------------------------- 

function give_inc_priority ($id_inc){
	require("config.php");
	$query1="SELECT * FROM tincidencia WHERE id_incidencia= ".$id_inc;
	$resq1=mysql_query($query1);
	if ($rowdup=mysql_fetch_array($resq1))
		$pro=$rowdup["prioridad"];
	else
		$pro = "";
	return $pro;
}

// --------------------------------------------------------------- 
// Return incident title
// --------------------------------------------------------------- 

function give_inc_title ($id_inc){
	require("config.php");
	$query1="SELECT * FROM tincidencia WHERE id_incidencia= ".$id_inc;
	$resq1=mysql_query($query1);
	if ($rowdup=mysql_fetch_array($resq1))
		$pro=$rowdup["titulo"];
	else
		$pro = "";
	return $pro;
}

// --------------------------------------------------------------- 
// Return incident notify by email feature
// --------------------------------------------------------------- 

function give_inc_email ($id_inc){
	require("config.php");
	$query1="SELECT * FROM tincidencia WHERE id_incidencia= ".$id_inc;
	$resq1=mysql_query($query1);
	if ($rowdup=mysql_fetch_array($resq1))
		$pro=$rowdup["notify_email"];
	else
		$pro = "";
	return $pro;
}

// --------------------------------------------------------------- 
// Return incident original author
// --------------------------------------------------------------- 

function give_inc_creator ($id_inc){
	require("config.php");
	$query1="SELECT * FROM tincidencia WHERE id_incidencia= ".$id_inc;
	$resq1=mysql_query($query1);
	if ($rowdup=mysql_fetch_array($resq1))
		$pro=$rowdup["id_creator"];
	else
		$pro = "";
	return $pro;
}

// --------------------------------------------------------------- 
// Returns agent id given name of agent
// --------------------------------------------------------------- 

function give_agent_id_from_module_id ($id_module){
	require("config.php");
	$query1="SELECT * FROM tagente_modulo WHERE id_agente_modulo = $id_module";
	$resq1=mysql_query($query1);
	if ($rowdup=mysql_fetch_array($resq1))
		$pro=$rowdup["id_agente"];
	else
		$pro = "";
	return $pro;
}

// --------------------------------------------------------------- 
// Returns user email fiven its id
// --------------------------------------------------------------- 

function return_user_email ($id_user){
	require("config.php");
	$query1="SELECT * FROM tusuario WHERE id_usuario = '$id_user'";
	$resq1=mysql_query($query1);
	if ($rowdup=mysql_fetch_array($resq1))
		$pro=$rowdup["direccion"];
	else
		$pro = "";
	return $pro;
}

function project_manager_check ($id_project) {
	global $config;
	global $lang_label;

	$manager = get_db_value ("id_owner", "tproject", "id", $id_project);
	if (isset($_SESSION["id_usuario"])){
	   $id = $_SESSION["id_usuario"];
	   if ($manager == $id)
			return 1;
		else
		  return 0;
	}
	else
		return 0;
		
}

function incident_tracking ( $id_incident, $id_user, $state, $aditional_data = 0) {
	global $config;
	require ("include/languages/language_".$config["language_code"].".php");
	switch($state){
		case 0: $descripcion = $lang_label["incident_creation"];
			break;
		case 1: $descripcion = $lang_label["incident_updated"];
			break;
		case 2: $descripcion = $lang_label["incident_note_added"];
			break;
		case 3: $descripcion = $lang_label["incident_file_added"];
			break;
		case 4: $descripcion = $lang_label["incident_note_deleted"];
			break;
		case 5: $descripcion = $lang_label["incident_file_deleted"];
			break;
		case 6: $descripcion = $lang_label["incident_change_priority"];
			break;
		case 7: $descripcion = $lang_label["incident_change_status"];
			break;
		case 8: $descripcion = $lang_label["incident_change_resolution"];
			break;
		case 9: $descripcion = $lang_label["incident_workunit_added"];
			break;
	}

	if ($state == 6)
		$descripcion .= " -> ".$aditional_data;

	if ($state == 7)
		$descripcion .= " -> ".get_db_value ("name", "tincident_status", "id", $aditional_data);

	if ($state == 8)
		$descripcion .= " -> ".get_db_value ("name", "tincident_resolution", "id", $aditional_data);
	

	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "Incident updated", $descripcion);
	$sql = "INSERT INTO tincident_track (id_user, id_incident, timestamp, state, id_aditional) values ('$id_user', $id_incident, NOW(), $state, $aditional_data)";
	$resq1=mysql_query($sql);
	
}

function task_tracking ( $id_user, $id_task, $state, $id_note = 0, $id_file = 0) {
	global $config;
	global $lang_label;
	global $REMOTE_ADDR;

	/* 
		11 - Task added
		12 - Task updated
		13 - Task. Note added
		14 - Task. Workunit added.
		15 - Task. File added
		16 - Task completion changed
		17 - Task finished.
		18 - Task member added
		19 - Task moved 
		20 - Task deleted
	*/		
	
	audit_db ($id_user, $REMOTE_ADDR, "Task #$id_task tracking updated", "State #$state");
	$id_external = $id_note + $id_file; // one or two of them must be 0, so sum is a good option to calculate who is usable
	$sql = "INSERT INTO ttask_track (id_user, id_task, timestamp, state, id_external) values ('$id_user', $id_task, NOW(), $state, $id_external)";
	$resq1=mysql_query($sql);
}

$sql_cache = array ('saved' => 0);

/** 
 * Get the first value of the first row of a table in the database.
 * 
 * @param field Field name to get
 * @param table Table to retrieve the data
 * @param field_search Field to filter elements
 * @param condition Condition the field must have
 *
 * @return Value of first column of the first row. False if there were no row.
 */  
function get_db_value ($field, $table, $field_search = 1, $condition = 1) {
	if (is_int ($condition)) {
		$sql = sprintf ("SELECT %s FROM `%s` WHERE `%s` = %d LIMIT 1",
				$field, $table, $field_search, $condition);
	} else if (is_float ($condition) || is_double ($condition)) {
		$sql = sprintf ("SELECT %s FROM `%s` WHERE `%s` = %f LIMIT 1",
				$field, $table, $field_search, $condition);
	} else {
		$sql = sprintf ("SELECT %s FROM `%s` WHERE `%s` = '%s' LIMIT 1",
				$field, $table, $field_search, $condition);
	}
	$result = get_db_all_rows_sql ($sql);
	
	if ($result === false)
		return false;
	
	return $result[0][$field];
}

/** Deprecated function */
function give_db_value ($field, $table, $field_search = 1, $condition = 1) {
	return get_db_value ($field, $table, $field_search, $condition);
}

/** 
 * Get the first row of an SQL database query.
 * 
 * @param sql SQL select statement to execute.
 * 
 * @return The first row of the result or something empty.
 */
function get_db_row_sql ($sql) {
	$sql .= " LIMIT 1";
	$result = get_db_all_rows_sql ($sql);
	
	if($result === false) 
		return false;
	
	return $result[0];
}

/** 
 * Get the first row of a database query into a table.
 *
 * The SQL statement executed would be something like:
 * "SELECT * FROM $table WHERE $field_search = $condition"
 *
 * @param table Table to get the row
 * @param field_search Field to filter elementes
 * @param condition Condition the field must have.
 * 
 * @return The first row of a database query.
 */
function get_db_row ($table, $field_search, $condition) {
	
	if (is_int ($condition)) {
		$sql = sprintf ("SELECT * FROM `%s` WHERE `%s` = %d LIMIT 1", $table, $field_search, $condition);
	} else if (is_float ($condition) || is_double ($condition)) {
		$sql = sprintf ("SELECT * FROM `%s` WHERE `%s` = %f LIMIT 1", $table, $field_search, $condition);
	} else {
		$sql = sprintf ("SELECT * FROM `%s` WHERE `%s` = '%s' LIMIT 1", $table, $field_search, $condition);
	}
	$result = get_db_all_rows_sql ($sql);
		
	if($result === false) 
		return false;
	
	return $result[0];
}

/** 
 * Get a single field in the databse from a SQL query.
 *
 * @param sql SQL statement to execute
 * @param field Field number to get, beggining by 0. Default: 0
 * @param cache Cache the query while generating this page. Default: 1
 * @return The selected field of the first row in a select statement.
 */
function get_db_sql ($sql, $field = 0) {
	$result = get_db_all_rows_sql ($sql);
	if($result === false)
		return false;

	return $result[0][$field];
}

/** Deprecated function */
function give_db_sqlfree_field ($sql, $field = 0) {
	return get_db_sql ($sql, $field = 0);
}

/**
 * Get all the result rows using an SQL statement.
 * 
 * @param $sql SQL statement to execute.
 *
 * @return A matrix with all the values returned from the SQL statement or
 * false in case of empty result
 */
function get_db_all_rows_sql ($sql) {
	$return = process_sql ($sql);
	
	if (! empty ($return))
		return $return;
	//Return false, check with === or !==
	return false;
}

/**
 * This function comes back with an array in case of SELECT
 * in case of UPDATE, DELETE etc. with affected rows
 * an empty array in case of SELECT without results
 * Queries that return data will be cached so queries don't get repeated
 *
 * @param $sql SQL statement to execute
 *
 * @param $rettype (optional) What type of info to return in case of INSERT/UPDATE.
 *		insert_id will return the ID of an autoincrement value
 *		info will return the full (debug) information of a query
 *		default will return mysql_affected_rows
 *
 * @return An array with the rows, columns and values in a multidimensional array 
 */
function process_sql ($sql, $rettype = "affected_rows") {
	global $config;
	global $sql_cache;
	$retval = array();

	if (! empty ($sql_cache[$sql])) {
		$retval = $sql_cache[$sql];
		$sql_cache['saved']++;
	} else {
		$result = mysql_query ($sql);
		if ($result === false) {
			echo '<strong>Error:</strong>process_sql ("'.$sql.'") :'. mysql_error ().'<br />';
			return false;
		} elseif ($result === true) {
			if ($rettype == "insert_id") {
				return mysql_insert_id ();
			} elseif ($rettype == "info") {
				return mysql_info ();
			}
			return mysql_affected_rows (); //This happens in case the statement was executed but didn't need a resource
		} else {
			while ($row = mysql_fetch_array ($result)) {
				array_push ($retval, $row);
			}
			$sql_cache[$sql] = $retval;
			mysql_free_result ($result);
		}
	}
	if (! empty ($retval))
		return $retval;
	//Return false, check with === or !==
	return false;
}

/**
 * Get all the rows in a table of the database.
 * 
 * @param $table Database table name.
 *
 * @return A matrix with all the values in the table
 */
function get_db_all_rows_in_table ($table, $order_field = "") {
	if ($order_field != "") {
		return get_db_all_rows_sql ("SELECT * FROM `".$table."` ORDER BY `".$order_field."` ");
	} else {	
		return get_db_all_rows_sql ("SELECT * FROM `".$table."`");
	}
}

/**
 * Get all the rows in a table of the databes filtering from a field.
 * 
 * @param $table Database table name.
 * @param $field Field of the table.
 * @param $condition Condition the field must have to be selected.
 *
 * @return A matrix with all the values in the table that matches the condition in the field
 */
function get_db_all_rows_field_filter ($table, $field, $condition, $order_field = "") {
	if (is_int ($condition)) {
		$sql = sprintf ("SELECT * FROM `%s` WHERE `%s` = '%d'", $table, $field, $condition);
	} else if (is_float ($condition) || is_double ($condition)) {
		$sql = sprintf ("SELECT * FROM `%s` WHERE `%s` = '%f'", $table, $field, $condition);
	} else {
		$sql = sprintf ("SELECT * FROM `%s` WHERE `%s` = '%s'", $table, $field, $condition);
	}

	if ($order_field != "")
		$sql .= sprintf(" ORDER BY `%s`",$order_field);	
	return get_db_all_rows_sql ($sql);
}

/**
 * Get all the rows in a table of the databes filtering from a field.
 * 
 * @param $table Database table name.
 * @param $field Field of the table.
 * @param $condition Condition the field must have to be selected.
 *
 * @return A matrix with all the values in the table that matches the condition in the field
 */
function get_db_all_fields_in_table ($table, $field, $condition='') {
	$sql = sprintf ("SELECT * FROM `%s`", $table);
	if($condition != '') {
		$sql .= sprintf (" WHERE `%s` = '%s'", $field, $condition);
	}
	return get_db_all_rows_sql ($sql);
}



function delete_project ($id_project){
	$query = "DELETE FROM trole_people_project WHERE id_project = $id_project";
	mysql_query($query);
	$query = "DELETE FROM trole_people_task, ttask WHERE ttask.id_project = $id_project AND trole_people_task.id_task = ttask.id";
	mysql_query($query);
	$query = "DELETE FROM ttask WHERE id_project = $id_project";
	mysql_query($query);
	$query = "DELETE FROM tproject WHERE id = $id_project";
	mysql_query($query);
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
}

function mail_project ($mode, $id_user, $id_workunit, $id_task, $additional_msg = "") {
	global $config;

	$workunit = get_db_row ("tworkunit", "id", $id_workunit);
	$task	 = get_db_row ("ttask", "id", $id_task);
	$project  = get_db_row ("tproject", "id", $task["id_project"]);
	$id_project = $task["id_project"];
	$id_manager = $project["id_owner"];
	$task_name = $task["name"];

	// WU data
	$current_timestamp = $workunit["timestamp"];
	$duration = $workunit["duration"];
	$have_cost = $workunit["have_cost"];
	if ($have_cost == 1)
		$have_cost = lang_string("YES");
	else
		$have_cost = lang_string("NO");
	$description = $workunit["description"];
	$url = $config["base_url"]."/index.php?sec=projects&sec2=operation/projects/task_workunit&id_project=$id_project&id_task=$id_task";

	switch ($mode){
	case 0: // Workunit add
		$text = "
Task ".$task["name"]." of project ".$project["name"]." has been updated by user $id_user and a new workunit has been added to history. You could track this workunit in the following URL (need to use your credentials): $url\n\n";
		$subject = "[".$config["sitename"]."] New workunit added to task '$task_name'";
		break;
	case 1: // Workunit updated
		$text = "
Task ".$task["name"]." of project ".$project["name"]." has been updated by user $id_user, a workunit has been updated. You could track this workunit in the following URL (need to use your credentials): $url\n\n";
		$subject = "[".$config["sitename"]."] A workunit has been updated in task '$task_name'";
		break;
	}
	
if ($additional_msg != "")
	$text .= "\n\n$additional_msg\n\n";
	
$text .= "
---------------------------------------------------[INFORMATION]-----
DATE / TIME : $current_timestamp
ASSIGNED BY : $id_user
HAVE COST   : $have_cost
TIME USED   : $duration
---------------------------------------------------[DESCRIPTION]-----
$description\n\n";

		$text = ascii_output ($text);
		$subject = ascii_output ($subject);
		// Send an email to project manager
		topi_sendmail (return_user_email($id_manager), $subject, $text);
}

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
PROGRESS	: $tprogress%
PRIORITY	: $tpriority
DESCRIPTION
---------------------------------------------------------------------
$tdescription\n\n";

		$text = ascii_output ($text);
		$subject = ascii_output ($subject);
		// Send an email to both
		topi_sendmail (return_user_email($tcreated), $subject, $text);
		topi_sendmail (return_user_email($tassigned), $subject, $text);
}

function mail_incident ($id_inc, $id_usuario, $nota, $timeused, $mode){
	global $config;

	$row = get_db_row ("tincidencia", "id_incidencia", $id_inc);
	$titulo =$row["titulo"];
	$descripcion = wordwrap(ascii_output($row["descripcion"]), 70, "\n");
	$prioridad = $row["prioridad"];
	$nota = wordwrap($nota, 70, "\n");

	$estado = get_db_sql ("SELECT name FROM tincident_status WHERE id = ".$row["estado"]);
	$resolution = get_db_sql ("SELECT name FROM tincident_resolution WHERE id = ".$row["resolution"]);
	$create_timestamp = $row["inicio"];
	$update_timestamp = $row["actualizacion"];
	$usuario = $row["id_usuario"];
	$creator = $row["id_creator"];

	// Resolve code for its name
	switch ($mode){
	case 10: // Add Workunit
		$subject = "[".$config["sitename"]."] Incident #$id_inc ($titulo) has a new workunit from $id_usuario ";
		$url = $config["base_url"]."/index.php?sec=incidents&sec2=operation/incidents/incident_workunits&id=$id_inc";
		break;
	case 0: // Incident update
		$subject = "[".$config["sitename"]."] Incident #$id_inc ($titulo) has been updated.";
		$url = $config["base_url"]."/index.php?sec=incidents&sec2=operation/incidents/incident_detail&id=$id_inc";
		break;
	case 1: // Incident creation
		$subject = "[".$config["sitename"]."] Incident #$id_inc ($titulo) has been created.";
		$url = $config["base_url"]."/index.php?sec=incidents&sec2=operation/incidents/incident_detail&id=$id_inc";
		break;
	case 2: // New attach
		$subject = "[".$config["sitename"]."] Incident #$id_inc ($titulo) has a new file attached.";
		$url = $config["base_url"]."/index.php?sec=incidents&sec2=operation/incidents/incident_detail&id=$id_inc";
		break;
	case 3: // Incident deleted 
		$subject = "[".$config["sitename"]."] Incident #$id_inc ($titulo) has been deleted.";
		$url = $config["base_url"]."/index.php?sec=incidents&sec2=operation/incidents/incident_detail&id=$id_inc";
		break;
	}
		
	// Send email for owner and creator of this incident
	$email_creator = get_db_value ("direccion", "tusuario", "id_usuario", $creator);
	$email_owner = get_db_value ("direccion", "tusuario", "id_usuario", $usuario);
  
	// Incident owner
	$text = "
Incident #$id_inc ($titulo) has been updated. You could track this incident in the following URL (need to use your credentials): $url\n
-----------------------------------------------[INFORMATION]--------
ID		  : # $id_inc - $titulo
CREATED ON  : $create_timestamp
LAST UPDATE : $update_timestamp
PRIORITY	: $prioridad
STATUS	  : $estado
RESOLUTION  : $resolution
ASSIGNED TO : $usuario
TIME USED   : $timeused
----------------------------------------------[DESCRIPTION]---------
$descripcion\n\n";

if ($mode == 10){
$text .= "
----------------------------------------------[WORK UNIT ADDED]-----
WORKUNIT ADDED BY $id_usuario
---------------------------------------------------------------------
$nota \n\n";
}

	$text = ascii_output ($text);
	$subject = ascii_output ( $subject ) ;
	topi_sendmail ($email_owner, $subject, $text);
	// Incident owner
	if ($email_owner != $email_creator)
		topi_sendmail ($email_creator, $subject, $text);
	
	// Send email for all users with workunits for this incident
	$sql1 = "SELECT DISTINCT(tusuario.direccion), tusuario.id_usuario FROM tusuario, tworkunit, tworkunit_incident WHERE tworkunit_incident.id_incident = $id_inc AND tworkunit_incident.id_workunit = tworkunit.id AND tworkunit.id_user = tusuario.id_usuario";
	if ($result=mysql_query($sql1)) {
		while ($row=mysql_fetch_array($result)){
			if (($row[0] != $email_owner) AND ($row[0] != $email_creator))
				topi_sendmail ( $row[0], $subject, $text);
		}
	}
}
			
function people_involved_incident ($id_inc){
	global $config;
	$row0 = get_db_row ("tincidencia", "id_incidencia", $id_inc);
	$people = array();

	array_push ($people, $row0["id_creator"]);
	 if (!in_array($row0["id_usuario"], $people)) {	
		array_push ($people, $row0["id_usuario"]);
	}
 
	// Take all users with workunits for this incident
	$sql1 = "SELECT DISTINCT(tusuario.id_usuario) FROM tusuario, tworkunit, tworkunit_incident WHERE tworkunit_incident.id_incident = $id_inc AND tworkunit_incident.id_workunit = tworkunit.id AND tworkunit.id_user = tusuario.id_usuario";
	if ($result=mysql_query($sql1)) {
		while ($row=mysql_fetch_array($result)){
			if (!in_array($row[0], $people))
				array_push ($people, $row[0]);
		}
	}
	return $people;
}

/* Returns cost for a given task */

function task_workunit_cost ($id_task, $only_marked = 1){
	global $config;
	$total = 0;
	if ($only_marked == 1)
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


/*
 This function return 1 if target_user is visible for a user (id_user)
 with a permission oc $access (PM, IM, IW...) on any of its profiles 
 For each comparation uses profile (access bit) and group that id_user
 have.
*/

function user_visible_for_me ($id_user, $target_user, $access = ""){
	global $config; 
	$access = strtolower($access);

	if (dame_admin ($id_user) == 1){
		return 1;
	}

	if ($id_user == $target_user){
	return 1;
	}

	// I have access to group ANY ?

	if ($access == "")
		$sql_0 = "SELECT COUNT(*) FROM tusuario_perfil WHERE id_usuario = '$id_user' AND id_grupo = 1 ";
	else
		$sql_0 = "SELECT COUNT(*) FROM tusuario_perfil, tprofile WHERE tusuario_perfil.id_usuario = '$id_user' AND id_grupo = 1 AND tprofile.$access = 1 AND tprofile.id = tusuario_perfil.id_perfil";
	$result_0 = mysql_query($sql_0);
	$row_0 = mysql_fetch_array($result_0);
	if ($row_0[0] > 0){
		return 1;
	}

	// Show users from my groups
	if ($access == "")
		$sql_1="SELECT id_grupo FROM tusuario_perfil WHERE id_usuario = '$id_user'";
	else
		$sql_1="SELECT tusuario_perfil.id_grupo FROM tusuario_perfil, tprofile WHERE tusuario_perfil.id_usuario = '$id_user' AND tprofile.$access = 1 AND tprofile.id = tusuario_perfil.id_perfil";
	$result_1=mysql_query($sql_1);
	while ($row_1=mysql_fetch_array($result_1)){
		$sql_2="SELECT * FROM tusuario_perfil WHERE id_grupo = ".$row_1["id_grupo"];
		$result_2=mysql_query($sql_2);
		while ($row_2=mysql_fetch_array($result_2)){
			if ($row_2["id_usuario"] == $target_user){
				return 1;
			}
		}
	}
   
	// Show users for group 1 (ANY)
	$sql_2 = "SELECT * FROM tusuario_perfil WHERE id_grupo = 1";
	$result_2 = mysql_query($sql_2);
	while ($row_2 = mysql_fetch_array($result_2)){
		if ($row_2["id_usuario"] == $target_user){
			if ($access == ""){
				return 1; 
		}
			else {
				if (give_acl ($config["id_user"], 1, $access) == 1)
					return 1;
			}
		}
	}
	
	return 0;
}

function projects_active_user ($id_user) {
	$sql = "SELECT COUNT(DISTINCT(id_project)) FROM tproject, trole_people_project WHERE trole_people_project.id_user ='$id_user' AND trole_people_project.id_project = tproject.id AND tproject.disabled = 0";
	return get_db_sql ($sql);
}

function incidents_active_user ($id_user) {
	$sql = "SELECT COUNT(*) FROM tincidencia WHERE id_usuario = '$id_user' AND estado IN (1,2,3,4,5)";
	return get_db_sql ($sql);
}

function todos_active_user ($id_user) {
	$sql = "SELECT COUNT(*) FROM ttodo WHERE assigned_user = '$id_user'";
	return get_db_sql ($sql);
}

function return_vacations_user ($id_user, $year){
	global $config;
	$hours = get_db_sql ("SELECT SUM(tworkunit.duration) FROM tworkunit, tworkunit_task WHERE tworkunit_task.id_workunit = tworkunit.id AND tworkunit_task.id_task =-1 AND id_user = '$id_user' AND timestamp >= '$year-01-00 00:00:00' AND timestamp <= '$year-12-31 23:59:59'");
	return format_numeric ($hours/$config["hours_perday"]);
}

function return_daysworked_user ($id_user, $year) {
	global $config;
	$hours = get_db_sql ("SELECT SUM(tworkunit.duration) FROM tworkunit, tworkunit_task WHERE tworkunit_task.id_workunit = tworkunit.id AND tworkunit_task.id_task > 0 AND id_user = '$id_user' AND timestamp >= '$year-01-00 00:00:00' AND timestamp <= '$year-12-31 23:59:59'");
	return format_numeric ($hours/$config["hours_perday"]);
}

function return_daysworked_incident_user ($id_user, $year) {
	global $config;
	$hours = get_db_sql ("SELECT SUM(tworkunit.duration) FROM tworkunit, tworkunit_incident WHERE tworkunit_incident.id_workunit = tworkunit.id AND tworkunit_incident.id_incident > 0 AND id_user = '$id_user' AND timestamp >= '$year-01-00 00:00:00' AND timestamp <= '$year-12-31 23:59:59'");
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

function email_attach ( $name, $email, $from, $subject, $fileatt, $fileatttype, $texto ){
	$to = "$name <$email>";
	$fileattname = "$fileatt";
	$headers = "From: $from";
	$file = fopen( $fileatt, 'rb' ); 
	$data = fread( $file, filesize( $fileatt ) ); 
	fclose( $file );
	$semi_rand = md5( time() ); 
	$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x"; 

	$headers .= "\nMIME-Version: 1.0\n" . 
				"Content-Type: multipart/mixed;\n" . 
				" boundary=\"{$mime_boundary}\"";

	$message = "This is a multi-part message in MIME format.\n\n" . 
			"--{$mime_boundary}\n" . 
			"Content-Type: text/plain; charset=\"iso-8859-1\"\n" . 
			"Content-Transfer-Encoding: 7bit\n\n" . 
			$texto . "\n\n";

	$data = chunk_split( base64_encode( $data ) );
	$message .= "--{$mime_boundary}\n" . 
			 "Content-Type: {$fileatttype};\n" . 
			 " name=\"{$fileattname}\"\n" . 
			 "Content-Disposition: attachment;\n" . 
			 " filename=\"{$fileattname}\"\n" . 
			 "Content-Transfer-Encoding: base64\n\n" . 
			 $data . "\n\n" . 
			 "--{$mime_boundary}--\n"; 
	$message .= "\n".$texto;
	mail( $to, $subject, $message, $headers );
}

function insert_event ($type, $id1 = 0, $id2 = 0, $id3 = 0){
   	require("config.php");
	$timestamp = date('Y-m-d H:i:s');

	$sql = "INSERT INTO tevent (type, id_user, timestamp, id_item, id_item2, id_item3) VALUES 
			('$type', '".$config["id_user"]."', '$timestamp', $id1, $id2, '$id3')";
	mysql_query($sql);
}

?>
