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
		FM - Frits management

		AR - Agenda read
		AW - Agenda write
		AM - Agenda management

		PR - Project read
		PW - Project write
        PM - Project management

		TR - Task read
		TM - Task management
	*/

	global $config;
	$query1 = "SELECT * FROM tusuario WHERE id_usuario = '".$id_user."'";
	$res=mysql_query($query1);
	$row=mysql_fetch_array($res);
	if ($row["nivel"] == 1)
		$result = 1;
	else {
		if ($id_group == 0) // Group doesnt matter, any group, for check permission to do at least an action in a group
			$query1="SELECT * FROM tusuario_perfil WHERE id_usuario = '".$id_user."'";	// GroupID = 0, group doesnt matter (use with caution!)
		else
			$query1="SELECT * FROM tusuario_perfil WHERE id_usuario = '".$id_user."' and ( id_grupo =".$id_group." OR id_grupo = 1)";	// GroupID = 1 ALL groups      
		$resq1=mysql_query($query1);  
		$result = 0; 
		while ($rowdup=mysql_fetch_array($resq1)){
			$id_perfil=$rowdup["id_perfil"];
			// For each profile for this pair of group and user do...
			$query2="SELECT * FROM tprofile WHERE id = ".$id_perfil;    
			$resq2=mysql_query($query2);  
			if ($rowq2=mysql_fetch_array($resq2)){
				switch ($access) {
					case "IR": $result = $result + $rowq2["ir"]; break;
					case "IW": $result = $result + $rowq2["iw"]; break;
					case "IM": $result = $result + $rowq2["im"]; break;
					case "AR": $result = $result + $rowq2["ar"]; break;
					case "AW": $result = $result + $rowq2["aw"]; break;
					case "AM": $result = $result + $rowq2["am"]; break;
					case "FM": $result = $result + $rowq2["fm"]; break;
					case "DM": $result = $result + $rowq2["dm"]; break;
					case "UM": $result = $result + $rowq2["um"]; break;
					case "PR": $result = $result + $rowq2["pr"]; break;
                    			case "PM": $result = $result + $rowq2["pm"]; break;
					case "PW": $result = $result + $rowq2["pw"]; break;
					case "TW": $result = $result + $rowq2["tw"]; break;
					case "TM": $result = $result + $rowq2["tm"]; break;
                    default : $result = $rowq2["tm"] + $rowq2["tw"] + $rowq2["pw"] + $rowq2["pm"] + $rowq2["pr"]+ $rowq2["um"]+ $rowq2["dm"] + $rowq2["fm"] + $rowq2["am"] + $rowq2["aw"] + $rowq2["ar"] + $rowq2["im"] +$rowq2["iw"] + $rowq2["ir"];
				}
			} 
		}
	} // else
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

function dame_nombre_grupo ($id){
	require ("config.php");
	$query1 = "SELECT * FROM tgrupo WHERE id_grupo = ".$id;
	$resq1 = mysql_query($query1);
	if ($rowdup = mysql_fetch_array ($resq1))
		$pro = $rowdup["nombre"];
	else
		$pro = "";
	return $pro;
} 

// --------------------------------------------------------------- 
// This function return group_id given an agent_id
// --------------------------------------------------------------- 

function dame_id_grupo($id_agente){
	require("config.php");
	$query1="SELECT * FROM tagente WHERE id_agente =".$id_agente;
	$resq1=mysql_query($query1);
	if ($rowdup=mysql_fetch_array($resq1)){
		$pro=$rowdup["id_grupo"];
	}
	else $pro = "";
	return $pro;
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
* $id_inc   integer     ID of incident
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

function give_hours_project ($id_project){
	global $config;
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
* $id_project   integer     ID of project
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
* $id_task  integer     ID of task
**/

function give_wu_task ($id_task){
    global $config;
    $query1="SELECT COUNT(tworkunit.duration) 
            FROM tworkunit, tworkunit_task
            WHERE   tworkunit_task.id_task = $id_task AND 
                    tworkunit_task.id_workunit = tworkunit.id";
    $resq1=mysql_query($query1);
    if ($rowdup=mysql_fetch_array($resq1))
        $pro=$rowdup[0]; 
    else 
        $pro = 0;
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
	$sql3="SELECT * FROM tnota_inc WHERE id_incidencia = ".$id_inc;
	$res2=mysql_query($sql3);
	while ($row2=mysql_fetch_array($res2)){
		// Delete all note ID related in table
		$sql4 = "DELETE FROM tnota WHERE id_nota = ".$row2["id_nota"];
		$result4 = mysql_query($sql4);
	}
	$sql6="DELETE FROM tnota_inc WHERE id_incidencia = ".$id_inc;
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
// Return true (1) if userid belongs to given project as any role
// ---------------------------------------------------------------

function user_belong_project ($id_user, $id_project){ 
	global $config;
        global $lang_label;

	if (dame_admin ($id_user) != 0)
		return 1;

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

function user_belong_task ($id_user, $id_task){ 
	global $config;
        global $lang_label;

	if (dame_admin ($id_user) != 0)
		return 1;


    $id_project = give_db_sqlfree_field ("SELECT id_project FROM ttask WHERE id = $id_task");
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

	$manager = give_db_value ("id_owner", "tproject", "id", $id_project);
	$id = $_SESSION["id_usuario"];
	if ($manager == $id)
		return 1;
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
		$descripcion .= " -> ".give_db_value ("name", "tincident_status", "id", $aditional_data);

	if ($state == 8)
		$descripcion .= " -> ".give_db_value ("name", "tincident_resolution", "id", $aditional_data);
	

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

function give_db_value ($field, $table, $field_search, $condition_value){
	global $config;
	$query = "SELECT $field FROM $table WHERE $field_search = '$condition_value' ";
	$resq1 = mysql_query($query);
	if ($rowdup = mysql_fetch_array($resq1))
		$pro = $rowdup[0];
	else
		$pro = "";
	return $pro;
}

function give_db_sqlfree_field ($sql){
	global $config;
	$query = $sql;
	$resq1 = mysql_query($query);
	if ($rowdup = mysql_fetch_array($resq1))
		$pro = $rowdup[0];
	else
		$pro = "";
	return $pro;
}

function give_db_row ($table, $field_search, $condition_value){
	global $config;
	$query = "SELECT * FROM $table WHERE $field_search = '$condition_value' ";
	$resq1 = mysql_query($query);
	if ($rowdup = mysql_fetch_array($resq1))
		return $rowdup;
	else
		return -1;
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
	$query = "DELETE FROM trole_people_task WHERE ttask.id_task = $id_task";
	mysql_query($query);
	$query = "DELETE FROM ttask_track WHERE id_task = $id_task";
	mysql_query($query);
	$query = "DELETE FROM tworkunit_task, tworkunit WHERE tworkunit_task.id_task = $id_task AND tworkunit_task.id_workunit = tworkunit.id";
	mysql_query($query);
	$query = "DELETE FROM ttask WHERE id = $id_task";
	mysql_query($query);
}

function mail_incident_workunit ($id_inc, $id_usuario, $nota, $timeused){
	global $config;

	$row = give_db_row ("tincidencia", "id_incidencia", $id_inc);
	$titulo =$row["titulo"];
	$descripcion = $row["descripcion"];
	$prioridad = $row["prioridad"];
	$estado = $row["estado"];
	$usuario = $row["id_usuario"];
	$creator = $row["id_creator"];

    // Resolve code for its name
    $estado = give_db_sqlfree_field ("SELECT name FROM tincident_status WHERE id = $estado");

	$subject = "[".$config["sitename"]."] Incident #$id_inc ($titulo) has a new workunit from $id_usuario ";
		
	// Send email for owner and creator of this incident
	$email_creator = give_db_value ("direccion", "tusuario", "id_usuario", $creator);
	$email_owner = give_db_value ("direccion", "tusuario", "id_usuario", $usuario);

    // Incident owner
    $myurl = topi_quicksession ("/index.php?sec=incidents&sec2=operation/incidents/incident_workunits&id=$id_inc", $usuario);
    $text = "Incident #$id_inc ($titulo) has been updated and a new workunit has been added to history.\n"; 
    $text .= "\nPriority: $prioridad\nStatus: $estado\nAssigned to:$usuario";
    $text .= "\nTimeused on new workunit: $timeused";
    $text .= "\nDirect URL access: $myurl";
    $text .= "\n\nDescription: \n\n$descripcion\n";
    $text .= "\nNew workunit added to incident by $id_usuario: \n\n $nota \n\n";
    topi_sendmail ($email_owner, $subject, $text);

    // Incident owner
    if ($email_owner != $email_creator){    
        $myurl = topi_quicksession ("/index.php?sec=incidents&sec2=operation/incidents/incident_workunits&id=$id_inc", $creator);
        $text = "Incident #$id_inc ($titulo) has been updated and a new workunit has been added to history.\n"; 
        $text .= "\nPriority: $prioridad\nStatus: $estado\nAssigned to:$usuario";
        $text .= "\nTimeused on new workunit: $timeused";
        $text .= "\nDirect URL access: $myurl";
        $text .= "\n\nDescription: \n\n$descripcion\n";
        $text .= "\nNew workunit added to incident by $id_usuario: \n\n $nota \n\n";
		topi_sendmail ($email_creator, $subject, $text);
	} 
	// Send email for all users with workunits for this incident
	$sql1 = "SELECT DISTINCT(tusuario.direccion), tusuario.id_usuario FROM tusuario, tworkunit, tworkunit_incident WHERE tworkunit_incident.id_incident = $id_inc AND tworkunit_incident.id_workunit = tworkunit.id AND tworkunit.id_user = tusuario.id_usuario";
	if ($result=mysql_query($sql1)) {
		while ($row=mysql_fetch_array($result)){
			if (($row[0] != $email_owner) AND ($row[0] != $email_creator))
                $myurl = topi_quicksession ("/index.php?sec=incidents&sec2=operation/incidents/incident_workunits&id=$id_inc", $row[1]);
                $text = "Incident #$id_inc ($titulo) has been updated and a new workunit has been added to history.\n"; 
                $text .= "\nPriority: $prioridad\nStatus: $estado\nAssigned to:$usuario";
                $text .= "\nTimeused on new workunit: $timeused";
                $text .= "\nDirect URL access: $myurl";
                $text .= "\n\nDescription: \n\n$descripcion\n";
                $text .= "\nNew workunit added to incident by $id_usuario: \n\n $nota \n\n";
                topi_sendmail ( $row[0], $subject, $text);
		}
	}
}
			

function mail_incident ($id_inc, $modo = 0){
	global $config;

	$row = give_db_row ("tincidencia", "id_incidencia", $id_inc);
	$titulo =$row["titulo"];
	$descripcion = $row["descripcion"];
	$prioridad = $row["prioridad"];
	$estado = $row["estado"];
	$usuario = $row["id_usuario"];
	$creator = $row["id_creator"];
    
    // Resolve code for its name
    $estado = give_db_sqlfree_field ("SELECT name FROM tincident_status WHERE id = $estado");

	if ($modo == 0){
		$subject = "[".$config["sitename"]."] Incident #$id_inc ($titulo) has been updated.";
	} else if ($modo == 1){
		$subject = "[".$config["sitename"]."] Incident #$id_inc ($titulo) has been created.";
	} else if ($modo == 2){
		$subject = "[".$config["sitename"]."] Incident #$id_inc ($titulo) has a new file attached.";
	} else if ($modo == 3){
		$subject = "[".$config["sitename"]."] Incident #$id_inc ($titulo) has been deleted.";
	}
	
    // Send email for owner of this incident
	$text = "Incident #$id_inc ($titulo) has been updated. \n\nPriority: $prioridad\nStatus: $estado\nAssigned to:$usuario\n";
	$myurl = topi_quicksession ("/index.php?sec=incidents&sec2=operation/incidents/incident_detail&id=$id_inc", $usuario);
	$text .= "Direct URL Access: $myurl\n";
	$text .= "\nDescription: \n\n$descripcion";
    $email_owner = give_db_value ("direccion", "tusuario", "id_usuario", $usuario);
    topi_sendmail ( $email_owner, $subject, $text);

    // Send email for creator of this incident
    $text = "Incident #$id_inc ($titulo) has been updated. \n\nPriority: $prioridad\nStatus: $estado\nAssigned to:$usuario\n";
    $myurl = topi_quicksession ("/index.php?sec=incidents&sec2=operation/incidents/incident_detail&id=$id_inc", $creator);
    $text .= "Direct URL Access: $myurl\n";
    $text .= "\nDescription: \n\n$descripcion";
    $email_creator = give_db_value ("direccion", "tusuario", "id_usuario", $creator);
	if ($email_owner != $email_creator){	
		topi_sendmail (  $email_creator, $subject, $text);
	} 

	// Send email for all users with workunits for this incident
	$sql1 = "SELECT DISTINCT(tusuario.direccion), tusuario.id_usuario FROM tusuario, tworkunit, tworkunit_incident WHERE tworkunit_incident.id_incident = $id_inc AND tworkunit_incident.id_workunit = tworkunit.id AND tworkunit.id_user = tusuario.id_usuario";
	if ($result=mysql_query($sql1)) {
		while ($row=mysql_fetch_array($result)){
			if (($row[0] != $email_owner) AND ($row[0] != $email_creator))
				// echo "ENVIANDO EMAIL a ".$row[0];
                $text = "Incident #$id_inc ($titulo) has been updated. \n\nPriority: $prioridad\nStatus: $estado\nAssigned to:$usuario\n";
                $myurl = topi_quicksession ("/index.php?sec=incidents&sec2=operation/incidents/incident_detail&id=$", $row[1]);
                $text .= "Direct URL Access: $myurl\n";
                $text .= "\nDescription: \n\n$descripcion";
				topi_sendmail ( $row[0], $subject, $text);
		}
	}		
}

function people_involved_incident ($id_inc){
    global $config;
    $row0 = give_db_row ("tincidencia", "id_incidencia", $id_inc);
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
        $cost_per_hour = give_db_sqlfree_field ("SELECT cost FROM trole WHERE id = ".$row[0]);
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
    return give_db_sqlfree_field ($sql);
}

function incidents_active_user ($id_user) {
    $sql = "SELECT COUNT(*) FROM tincidencia WHERE id_usuario = '$id_user' AND estado IN (1,2,3,4,5)";
    return give_db_sqlfree_field ($sql);
}

function todos_active_user ($id_user) {
    $sql = "SELECT COUNT(*) FROM ttodo WHERE assigned_user = '$id_user'";
    return give_db_sqlfree_field ($sql);
}

?>
