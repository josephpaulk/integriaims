<?php

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

include ("config.php");
error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors", 1);
require_once ($config["homedir"].'/include/functions_calendar.php');
require_once ($config["homedir"].'/include/functions_groups.php');
require_once ($config["homedir"].'/include/functions_workunits.php');
require_once ($config["homedir"].'/include/functions_inventories.php');
require_once ($config["homedir"].'/include/functions_html.php');

// Activate errors. Should not be anyone, but if something happen, should be
// shown on console.

error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors", 1);

$config["id_user"] = 'System';
$now = time ();
$compare_timestamp = date ("Y-m-d H:i:s", $now - $config["notification_period"]*3600);
$human_notification_period = give_human_time ($config["notification_period"]*3600);

/**
 * This function delete temp files (mostly image temporaly serialize data)
 */

function delete_tmp_files(){

	if (function_exists(sys_get_temp_dir))
        	$dir =  sys_get_temp_dir ();
	else
		$dir = "/tmp";

        if ($dh = opendir($dir)){
                while(($file = readdir($dh))!== false){
                        if (strpos("___".$file, "integria_serialize")){
                            if (file_exists($dir."/".$file)) {
                                $fdate = filemtime($dir."/".$file);
                                $now = time();
                                if ($now - $fdate > 3600){
                                        @unlink($dir."/".$file);
                                } 
                            }
                        }

                }
                closedir($dh);
        }
}

/**
 * This function delete tsesion and tevent data with more than XXX days
 * TODO: Define DELETE_DAYS on config
 */

function delete_session_data () {
    global $config;
   
    $DELETE_DAYS = 90;
    $limit = strtotime ("now") - ($DELETE_DAYS * 86400);

    $query_del2 = "DELETE FROM tsesion WHERE utimestamp < $limit ";
	$resq2 =  process_sql ($query_del2);    

    $limit2 = date ("Y/m/d H:i:s", strtotime ("now") - ($DELETE_DAYS * 86400));
    $sql = "DELETE FROM tevent where timestamp < '$limit2'";
    $res = process_sql ($sql);
}
 
/** 
 * Interface to Integria API functionality.
 * 
 * @param string $url Url to Integria API with user, password and option (function to use).
 * @param string $postparameters Additional parameters to pass.
 *
 * @return variant The function result called in the API.
 */
function call_api($url, $postparameters = false) {

	$curlObj = curl_init();
	curl_setopt($curlObj, CURLOPT_URL, $url);
	curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
	if($postparameters !== false) {
		curl_setopt($curlObj, CURLOPT_POSTFIELDS, $postparameters);
	}
	$result = curl_exec($curlObj);
	curl_close($curlObj);

	return $result;
}

/**
 * This function creates an inventory object for each agent of pandora with name, address, description 
 * and extra fields if are defined as operating system and url address
 */
function synchronize_pandora_inventory () {
	global $config;

	if (!isset($config["pandora_url"])){
		return;
	}

	if ($config["pandora_url"] == ""){
		return;
	}

	$separator = ':;:';
	
	$url = $config['pandora_url'].'/include/api.php?op=get&apipass='.$config['pandora_api_password'].'&op2=all_agents&return_type=csv&user='.$config['pandora_user'].'&pass='.$config['pandora_pass'];
	
	$return = call_api($url);

	$agents_csv = explode("\n",$return);

	foreach($agents_csv as $agent_csv) {
		// Avoiding empty csv lines like latest one
		if($agent_csv == '') {
			continue;
		}

		$values = array();

		$agent = explode(";",$agent_csv);
		$agent_id = $agent[0];
		$agent_name = $agent[1];
		$agent_name_safe = safe_input($agent_name);
		$address = $agent[2];
		$description = $agent[3];
		$os_name = $agent[4];
		$url_address = $agent[5];
		
		// Check if exist to avoid the creation
		$inventory_id = get_db_value ('id', 'tinventory', 'name', $agent_name_safe);

		if($inventory_id !== false) {
			continue;
		}
		
		$id_object_type = get_db_value('id', 'tobject_type', 'name', safe_input('Pandora agents'));
	
		$values['name'] = $agent_name_safe;
		$values['description'] = $description;
		$values['id_object_type'] = $id_object_type;
		$values['id_contract'] = $config['default_contract'];
		
		
		$id_inventory = process_sql_insert('tinventory', $values);
		
		if ($id_inventory) {
			
			$id_type_field_os = get_db_value_filter('id', 'tobject_type_field', array('id_object_type'=>$id_object_type, 'label'=>safe_input('OS')));
			$id_type_field_ip = get_db_value_filter('id', 'tobject_type_field', array('id_object_type'=>$id_object_type, 'label'=>'IP Address'));
			$id_type_field_url = get_db_value_filter('id', 'tobject_type_field', array('id_object_type'=>$id_object_type, 'label'=>'URL Address'));
			$id_type_field_id = get_db_value_filter('id', 'tobject_type_field', array('id_object_type'=>$id_object_type, 'label'=>'ID Agent'));

			$value_os = array();
			$value_os['id_inventory'] = $id_inventory;
			$value_os['id_object_type_field'] = $id_type_field_os;
			$value_os['data'] = $os_name;
			
			process_sql_insert('tobject_field_data', $value_os);
			
			$value_ip = array();
			$value_ip['id_inventory'] = $id_inventory;
			$value_ip['id_object_type_field'] = $id_type_field_ip;
			$value_ip['data'] = $address;
			
			process_sql_insert('tobject_field_data', $value_ip);
			
			$value_url = array();
			$value_url['id_inventory'] = $id_inventory;
			$value_url['id_object_type_field'] = $id_type_field_url;
			$value_url['data'] = $url_address;
			
			process_sql_insert('tobject_field_data', $value_url);
			
			$value_id = array();
			$value_id['id_inventory'] = $id_inventory;
			$value_id['id_object_type_field'] = $id_type_field_id;
			$value_id['data'] = $agent_id;
			
			process_sql_insert('tobject_field_data', $value_id);
		}
	}		
}

/**
 * This function is executed once per day and do several different subtasks
 * like email notify ending tasks or projects, events for this day, etc.
 */

function run_daily_check () {
	$current_date = date ("Y-m-d h:i:s");

	// Create a mark in event log
	process_sql ("INSERT INTO tevent (type, timestamp) VALUES ('DAILY_CHECK', '$current_date') ");

	// Do checks
	run_calendar_check ();
	run_project_check ();
	run_task_check ();
	run_autowu();
    run_auto_incident_close();
    delete_session_data();
	synchronize_pandora_inventory();
	delete_tmp_files();
}


/**
 * Auto close incidents mark as "pending to be deleted" and no activity in X hrs
 * Takes no parameters. Checked in the main loop.with the other tasks.
 */

function run_auto_incident_close () {
	global $config;
    	require_once ($config["homedir"]."/include/functions_incidents.php");

    	$utimestamp = date("U");
	$limit = date ("Y-m-d H:i:s", $utimestamp - $config["auto_incident_close"] * 86400);

    	// For each incident
	$incidents = get_db_all_rows_sql ("SELECT * FROM tincidencia WHERE estado IN (1,2,3,4,5) AND actualizacion < '$limit'");
    	$mailtext = __("This incident has been closed automatically by Integria after waiting confirmation to close this incident for 
").$config["auto_incident_close"]."  ".__("days");

    	if ($incidents)
	    foreach ($incidents as $incident){
			
            // Set status to "Closed" (# 7) and solution to 7 (Expired)
            process_sql ("UPDATE tincidencia SET resolution = 7, estado = 7 WHERE id_incidencia = ".$incident["id_incidencia"]);

			// Add workunit
			create_workunit ($incident["id_incidencia"], $mailtext, $incident["id_usuario"], 0,  0, "", 1);
	
            // Send mail warning about this autoclose
            mail_incident ($incident["id_incidencia"], $incident["id_usuario"], $mailtext, 0, 10, 1);
        }

}

/**
 * Autofill WU ("Not justified") for users without reporting anything.
 * 
 * This function is used to verify that a user has $config["hours_perday"]
 * (as minimum) in each labor day in a period of time going from
 * now - $config["autowu_completion"] until now - ($config["autowu_completion"]*2)
 * If user dont have WU in that days, will create a WU associated 
 * to task "Not Justified" inside special project -1.
 */

function run_autowu () {
	global $config;

	$now = date ("Y-m-d");
 	// getWorkingDays($startDate,$endDate,$holidays){

	$autowu = $config["autowu_completion"];

	if ($autowu == 0)
		return;

	$autowu2 = $autowu + 7; // Always work with one week of margin

	// Calc interval dates
	$start_date = date('Y-m-d', strtotime("$now - $autowu2 days"));
	$end_date = date('Y-m-d', strtotime("$now - $autowu days"));
	$current_date = $start_date;

	
	// For each user
	$users = get_db_all_rows_sql ("SELECT * FROM tusuario");

	$end_loop = 0;
	
	while ($end_loop == 0){
		foreach ($users as $user){
			if (!is_working_day($current_date))
				continue;

			
			// If this user is in no_wu_completion list, skip it.
			if (strpos("_____".$config["no_wu_completion"], $user["id_usuario"]) > 0 ){
				continue;
			}
			
			$user_wu = get_wu_hours_user ($user["id_usuario"], $current_date);
			if ($user_wu < $config["hours_perday"]) {
				$nombre = $user['nombre_real'];
				$email = $user['direccion'];

				$mail_description = "Integria IMS has entered an automated Workunit to 'Not justified' task because you've more than $autowu days without filling by a valid Workunit.";
	
				integria_sendmail ($email, "[".$config["sitename"]."] Automatic WU (Non justified) has been entered",  $mail_description );
	
				create_wu_task (-3, $user["id_usuario"], $mail_description, 0, 0, 0, $config["hours_perday"]-$user_wu, $current_date);
	
			}
			
		}
	$current_date = date('Y-m-d', strtotime("$current_date +1 day"));	
	if ($current_date == $end_date)
		$end_loop = 1;
	}
}

/**
 * Checks and notify user by mail if in current day there agenda items 
 */
function run_calendar_check () {
	global $config;

	$now = date ("Y-m-d");
	$events = get_event_date ($now, 1, "_ANY_");

	foreach ($events as $event){
		list ($timestamp, $event_data, $user_event) = split ("\|", $event);
		$user = get_db_row ("tusuario", "id_usuario", $user_event);
		$nombre = $user['nombre_real'];
		$email = $user['direccion'];
		$mail_description = "There is an Integria agenda event planned for today at ($timestamp): \n\n$event_data\n\n";
		integria_sendmail ($email, "[".$config["sitename"]."] Calendar event for today ",  $mail_description );
	}
}

/**
 * Checks and notify user by mail if in current day there are ending projects
 *
 */
function run_project_check () {
	global $config;

	$now = date ("Y-m-d");
	$projects = get_project_end_date ($now, 0, "_ANY_");

	foreach ($projects as $project){
		list ($pname, $idp, $pend, $owner) = split ("\|", $project);
		$user = get_db_row ("tusuario", "id_usuario", $owner);
		$nombre = $user['nombre_real'];
		$email = $user['direccion'];
		$mail_description = "There is an Integria project ending today ($pend): $pname. \n\nUse this link to review the project information:\n";
		$mail_description .= $config["base_url"]. "/index.php?sec=projects&sec2=operation/projects/task&id_project=$idp\n";

		integria_sendmail ($email, "[".$config["sitename"]."] Project ends today ($pname)",  $mail_description );
	}
}


/**
 * Checks and notify user by mail if in current day there are ending tasks
 */
function run_task_check () {
	global $config;

	$now = date ("Y-m-d");
	$baseurl = 
	$tasks = get_task_end_date_by_user ($now);
	
	foreach ($tasks as $task){
		list ($tname, $idt, $tend, $pname, $user) = split ("\|", $task);
		$user_row = get_db_row ("tusuario", "id_usuario", $user);
		$nombre = $user_row['nombre_real'];
		$email = $user_row['direccion'];
		
		$mail_description = "There is a task ending today ($tend) : $pname / $tname \n\nUse this link to review the project information:\n";
		$mail_description .= $config["base_url"]. "/index.php?sec=projects&sec2=operation/projects/task_detail&id_task=$idt&operation=view\n";

		integria_sendmail ($email, "[".$config["sitename"]."] Task ends today ($tname)",  $mail_description );
	}
}



/**
 * Check if daily task has been executed in the last 24 hours.
 */
function check_daily_task () {
	$current_date = date ("Y-m-d");
	$current_date .= " 00:00:00";
	$result = get_db_sql ("SELECT COUNT(id) FROM tevent WHERE type = 'DAILY_CHECK' AND timestamp > '$current_date'");
	// Daily check has been executed in the past 24 hours.
	if ($result > 0)
		return false;

	return true;
}

//Inserts data into SLA graph table
function graph_sla($incident) {
	$id_incident = $incident['id_incidencia'];
	$utimestamp = time();
	
	//Get sla values for this incident
	$sla_affected = get_db_value("affected_sla_id", "tincidencia", 
						"id_incidencia", $id_incident);
	
	$values['id_incident'] = $id_incident;
	$values['utimestamp'] = $utimestamp;	
	
	//If incident is affected by SLA then the graph value is 0
	if ($sla_affected) {
		$values['value'] = 0;
	} else {
		$values['value'] = 1;
	}
	
	//Insert SLA value in table
	process_sql_insert('tincident_sla_graph', $values);
}

/**
 * Check an SLA min response value on an incident and send emails if needed.
 *
 * @param array Incident to check
 */
function check_sla_min ($incident) {
	global $compare_timestamp;
	global $config;

	$id_sla = check_incident_sla_min_response ($incident['id_incidencia']);

	if (! $id_sla)
		return false;
	
    $sla = get_db_row("tsla", "id", $id_sla);

	/* Check if it was already notified in a specified time interval */
	$sql = sprintf ('SELECT COUNT(id) FROM tevent
		WHERE type = "SLA_MIN_RESPONSE_NOTIFY"
		AND id_item = %d
		AND timestamp > "%s"',
		$incident['id_incidencia'],
		$compare_timestamp);
	$notified = get_db_sql ($sql);

	if ($notified > 0){
		return true;
   }

	/* We need to notify via email to the owner user */
	$user = get_user ($incident['id_usuario']);

    	$MACROS["_sitename_"] = $config["sitename"];
	$MACROS["_username_"] = $incident['id_usuario'];
	$MACROS["_fullname_"] = dame_nombre_real ($incident['id_usuario']);
	$MACROS["_group_"] = dame_nombre_grupo ($incident['id_grupo']);
	$MACROS["_incident_id_"] = $incident["id_incidencia"];
	$MACROS["_incident_title_"] = $incident['titulo'];
	$MACROS["_data1_"] = give_human_time ($sla['min_response']*60*60);

	$MACROS["_access_url_"] = $config["base_url"]."/index.php?sec=incidents&sec2=operation/incidents/incident&id=".$incident['id_incidencia'];

	$text = template_process ($config["homedir"]."/include/mailtemplates/incident_sla_min_response_time.tpl", $MACROS);
	$subject = template_process ($config["homedir"]."/include/mailtemplates/incident_sla_min_response_time_subject.tpl", $MACROS);
	
	integria_sendmail ($user['direccion'], $subject, $text);
	insert_event ('SLA_MIN_RESPONSE_NOTIFY', $incident['id_incidencia']);

	return true;
}

/**
 * Check an SLA max response value on an incident and send emails if needed.
 *
 * @param array Incident to check
 */
function check_sla_max ($incident) {
	global $compare_timestamp;
	global $config;
	
	$id_sla = check_incident_sla_max_response ($incident['id_incidencia']);
	if (! $id_sla)
		return false;
	
        $sla = get_db_row("tsla", "id", $id_sla);

	/* Check if it was already notified in a specified time interval */
	$sql = sprintf ('SELECT COUNT(id) FROM tevent
		WHERE type = "SLA_MAX_RESPONSE_NOTIFY"
		AND id_item = %d
		AND timestamp > "%s"',
		$incident['id_incidencia'],
		$compare_timestamp);
	$notified = get_db_sql ($sql);

	if ($notified > 0)
		return true;
	
	/* We need to notify via email to the owner user */
	$user = get_user ($incident['id_usuario']);

    	$MACROS["_sitename_"] = $config["sitename"];
	$MACROS["_username_"] = $incident['id_usuario'];
	$MACROS["_fullname_"] = dame_nombre_real ($incident['id_usuario']);
	$MACROS["_group_"] = dame_nombre_grupo ($incident['id_grupo']);
	$MACROS["_incident_id_"] = $incident["id_incidencia"];
	$MACROS["_incident_title_"] = $incident['titulo'];
	$MACROS["_data1_"] = give_human_time ($sla['max_response']*3600);
	$MACROS["_access_url_"] = $config["base_url"]."/index.php?sec=incidents&sec2=operation/incidents/incident&id=".$incident['id_incidencia'];

	$text = template_process ($config["homedir"]."/include/mailtemplates/incident_sla_max_response_time.tpl", $MACROS);
	$subject = template_process ($config["homedir"]."/include/mailtemplates/incident_sla_max_response_time_subject.tpl", $MACROS);
	integria_sendmail ($user['direccion'], $subject, $text);
	insert_event ('SLA_MAX_RESPONSE_NOTIFY', $incident['id_incidencia']);
}

/**
 * Check an SLA inactivity value on an incident and send email (to incident owner) if needed.
 *
 * @param array Incident to check
 */
function check_sla_inactivity ($incident) {
	global $compare_timestamp;
	global $config;
	
	$id_sla = check_incident_sla_max_inactivity ($incident['id_incidencia']);
	if (! $id_sla)
		return false;
	
        $sla = get_db_row("tsla", "id", $id_sla);

	/* Check if it was already notified in a specified time interval */
	$sql = sprintf ('SELECT COUNT(id) FROM tevent
		WHERE type = "SLA_MAX_INACTIVITY_NOTIFY"
		AND id_item = %d
		AND timestamp > "%s"',
		$incident['id_incidencia'],
		$compare_timestamp);
	$notified = get_db_sql ($sql);

	if ($notified > 0)
		return true;
	
	/* We need to notify via email to the owner user */
	$user = get_user ($incident['id_usuario']);

    $MACROS["_sitename_"] = $config["sitename"];
	$MACROS["_username_"] = $incident['id_usuario'];
	$MACROS["_fullname_"] = dame_nombre_real ($incident['id_usuario']);
	$MACROS["_group_"] = dame_nombre_grupo ($incident['id_grupo']);
	$MACROS["_incident_id_"] = $incident["id_incidencia"];
	$MACROS["_incident_title_"] = $incident['titulo'];
	$MACROS["_data1_"] = give_human_time ($sla['max_inactivity']*3600);
	$MACROS["_access_url_"] = $config["base_url"]."/index.php?sec=incidents&sec2=operation/incidents/incident&id=".$incident['id_incidencia'];

	$text = template_process ($config["homedir"]."/include/mailtemplates/incident_sla_max_inactivity_time.tpl", $MACROS);
	$subject = template_process ($config["homedir"]."/include/mailtemplates/incident_sla_max_inactivity_time_subject.tpl", $MACROS);
	integria_sendmail ($user['direccion'], $subject, $text);
	insert_event ('SLA_MAX_INACTIVITY_NOTIFY', $incident['id_incidencia']);
}

// This will send pending mail from database queue, using its defined MTA, and swiftmail functions

function run_mail_queue () {

	global $config;
	
	include_once ($config["homedir"]."/include/functions.php");
	
	// If SMTP port is not configured, abort mails directly!
	if ($config["smtp_port"] == 0){
		integria_logwrite ("SMTP has no port configured. Aborting mail queue processing");
		return;		
	}
	
	require_once($config["homedir"] . "/include/swiftmailer/swift_required.php");

   	$utimestamp = date("U");
	// $current_date = date ("Y/m/d H:i:s");

	// get pending mails 
	$mails = get_db_all_rows_sql ("SELECT * FROM tpending_mail WHERE status = 0");

	if ($mails)
	foreach ($mails as $email){

		// Use local mailer if host not provided - Attach not supported !!

		if ($config["smtp_host"] == ""){

			// Use internal mail() function
                        $headers   = array();
                        $headers[] = "MIME-Version: 1.0";
                        $headers[] = "Content-type: text/plain; charset=utf-8";

			if ($email["from"] == "")
                                $from = $config["mail_from"];
                        else
                                $from = $email["from"]; 

                        $headers[] = "From: ". $from;
                        $headers[] = "Subject: ". safe_output($email["subject"]);

                        $dest_email = trim(ascii_output($email['recipient']));
                        $body = safe_output($email['body']);
                        $error = mail($dest_email, safe_output($email["subject"]), $body, implode("\r\n", $headers));
                        if (!$error) {
				process_sql ("UPDATE tpending_mail SET status = $status, attempts = $retries WHERE id = ".$email["id"]);
                        } else {
				// no errors found
				process_sql ("DELETE FROM tpending_mail WHERE id = ".$email["id"]);
                        }

		} else {
		
		// Use swift mailer library to connect to external SMTP

			try {	
			$transport = Swift_SmtpTransport::newInstance($config["smtp_host"], $config["smtp_port"]);
			$transport->setUsername($config["smtp_user"]);
			$transport->setPassword($config["smtp_pass"]);
			$mailer = Swift_Mailer::newInstance($transport);
			$message = Swift_Message::newInstance($email["subject"]);
			
			if ($email["from"] == "")
				$message->setFrom($config["mail_from"]);
			else
				$message->setFrom($email["from"]);

			$to = trim(ascii_output($email['recipient']));

			$message->setTo($to);
			$message->setBody($email['body'], 'text/plain', 'utf-8');

			if ($email["attachment_list"] != ""){
				$attachments = split ( ",",$email["attachment_list"]);
				foreach ($attachments as $attachment)
				        if (is_file($attachment))
				                $message->attach(Swift_Attachment::fromPath($attachment));
			}

			// If SMTP port is not configured, abort mails directly!
			if ($config["smtp_port"] == 0)
				return;

			$message->setContentType("text/plain");

			if ($mailer->send($message) == 1)
				process_sql ("DELETE FROM tpending_mail WHERE id = ".$email["id"]);

			// SMTP error management!
			} catch (Exception $e) {
				$retries = $email["attempts"] + 1;
 				if ($retries > 5) {
					$status = 1;
					insert_event ('MAIL_FAILURE', 0, 0, $email["recipient"]. " - ". $e);
				}
				else  {
					$status = 0;
				}
				process_sql ("UPDATE tpending_mail SET status = $status, attempts = $retries WHERE id = ".$email["id"]);
				integria_logwrite ("SMTP error sending to $to ($e)");
			}
		}
	}
}

// This will send mails on the newsletters queue. Will have a global parameter to control how 
// many emails can sent in a single execution, to avoid SMTP servers overload or SPAM protection

function run_newsletter_queue () {

	global $config;

	include_once ($config["homedir"] . "/include/functions.php");	
	require_once($config["homedir"] . "/include/swiftmailer/swift_required.php");
	
	// If SMTP port is not configured, abort mails directly!
	if ($config["smtp_port"] == 0){
		integria_logwrite ("SMTP has no port configured. Aborting newsletter processing");
		return;		
	}

	$total = $config["batch_newsletter"];
	
   	$utimestamp = date("U");
	$current_date = date ("Y/m/d H:i:s");

	// Select valid QUEUES for processing
	
	$queues = get_db_all_rows_sql ("SELECT * FROM tnewsletter_queue WHERE status = 1");	
	if ($queues) foreach ($queues as $queue){
	
		$id_newsletter = get_db_sql ("SELECT id_newsletter FROM tnewsletter_content WHERE id = ".$queue["id_newsletter_content"]);
		$id_queue = $queue["id"];
		
		// Get issue and newsletter records
		
		$issue = get_db_row ("tnewsletter_content", "id", $queue["id_newsletter_content"]);
		$newsletter = get_db_row ("tnewsletter", "id", $id_newsletter);
		
		// TODO
		// max block size here is 500. NO MORE, fix this in the future with a real buffered system!
		
		$addresses = get_db_all_rows_sql ("SELECT * FROM tnewsletter_queue_data WHERE status = 0 AND id_queue = $id_queue LIMIT 500");

		if (!$addresses) 
			process_sql ("UPDATE tnewsletter_queue SET status=3 WHERE id = $id_queue");
		else 
		foreach ($addresses as $address){


			// Use external SMTP if smtp_host is defined
			
			if ($config["smtp_host"]!= ""){
		
				// Compose the mail for each valid address

				$transport = Swift_SmtpTransport::newInstance($config["smtp_host"], $config["smtp_port"]);
				$transport->setUsername($config["smtp_user"]);
				$transport->setPassword($config["smtp_pass"]);
				$mailer = Swift_Mailer::newInstance($transport);
			
				$message = Swift_Message::newInstance(safe_output($issue["email_subject"]));
				$message->setFrom($newsletter["from_address"]);
				$dest_email = trim(safe_output($address['email']));
			
				$message->setTo($dest_email);

				// TODO: replace names on macros in the body / HTML parts.

				$message->setBody(safe_output($issue['html']), 'text/html', 'utf-8');

				$message->addPart(replace_breaks(safe_output(safe_output($issue['plain']))), 'text/plain', 'utf-8');

				if (!$mailer->send($message, $failures)) {
					integria_logwrite ("Trouble with address ".$failures[0]);

					// TODO: 
					// Do something like error count to disable invalid addresses after a 
					// number of invalid counts
					// process_sql ("UPDATE tnewsletter_address SET status=1 WHERE id_newsletter = $id_newsletter AND email = '$dest_email'");
				
					process_sql ("UPDATE tnewsletter_queue_data SET status=2 WHERE id_queue = $id_queue AND email = '$dest_email'");
				
				} else {
					process_sql ("UPDATE tnewsletter_queue_data SET status=1 WHERE id_queue = $id_queue AND email = '$dest_email'");
				}
			} else {

				// Use internal mail() function
				$headers   = array();
				$headers[] = "MIME-Version: 1.0";
				$headers[] = "Content-type: text/html; charset=utf-8";
				$headers[] = "From: ".safe_output($newsletter["from_desc"]). " <".safe_output($newsletter["from_address"]).">";
				//$headers[] = "Bcc: JJ Chong <bcc@domain2.com>";
				$headers[] = "Subject: ". safe_output($issue["email_subject"]);
				$headers[] = "X-Mailer: PHP/".phpversion();

				$dest_email = trim(safe_output($address['email']));
				$email = safe_output($issue['html']);
				$error = mail($dest_email, safe_output($issue["email_subject"]), $email, implode("\r\n", $headers));
				if (!$error) {
					process_sql ("UPDATE tnewsletter_queue_data SET status=2 WHERE id_queue = $id_queue AND email = '$dest_email'");
				} else {
					process_sql ("UPDATE tnewsletter_queue_data SET status=1 WHERE id_queue = $id_queue AND email = '$dest_email'");
				}
			}	

			$total = $total - 1;
		
			if ($total <= 0)
					return;
		
			} // end of loop for each address in the queue		

	} // end of main loop
	
}


// This will check POP3 pending mail

function run_mail_check () {

	global $config;
	
	//If imap module is not loaded don't executhe anything.
	if (!function_exists(imap_open)) {
		integria_logwrite ("IMAP Extension not loaded, I can't continue processing mail queue");
		return;
	}

	include_once ($config["homedir"]."/include/functions_pop3.php");

	// If SMTP port is not configured, abort mails directly!
	if ($config["smtp_port"] == 0){
		integria_logwrite ("SMTP has no port configured. Aborting mail queue check processing");
		return;		
	}

	// Inicialization for internal variables

	$error            = "";   //    Error string.
	$timeout          = 90;   //    Default timeout before giving up on a network operation.
	$login            = $config["pop_user"];
	$pass             = $config["pop_pass"];
	$server           = $config["pop_host"];
	$port      	  = $config["pop_port"];

    # Check if POP3 server is defined, if not, abort
    if ($server == ""){
        return;
    }
    
    //Check if SSL connection was set
    $ssl_str = substr($server, 0, 6);
    $subfix = "pop3";
    
    //Convert connection string to use SSL encryption
    if ( $ssl_str == "ssl://") {
		$server = substr($server, 6);
		$subfix = "ssl/novalidate-cert";
	}
	
    if ($port) {
		$port = ":$port";
	} else {
		$port = "";
	}
	
	set_time_limit($timeout);

	//Set open timeout to 30 seconds
	imap_timeout(IMAP_OPENTIMEOUT, 30);

	//Open mail connection
	$mail = imap_open("{".$server.$port."/".$subfix."}", $login, $pass, NIL, 3);	
	
	//Walk the mailbox from last mail to the first
	$last = imap_num_msg($mail);
	
	$i = $last;
	for ($i; $i>0; $i--) {
	
echo "DEBUG: Navegando por los mails...\n";
	
		$struct = imap_fetchstructure($mail, $i);
		
		$encoding = $struct->{'encoding'};
	 
		$header = imap_header($mail, $i); 	
	
		$subject = imap_utf8($header->{'subject'});
				
		$from = $header->{'from'}[0]->{'mailbox'}."@".$header->{'from'}[0]->{'host'};
		$body = (imap_fetchbody ($mail, $i, 1));
				
		//Check encoding and decode body
		switch ($encoding) {
			case 0: //7 bits		
				$body = quoted_printable_decode($body);
				break;
				
			case 1: //8bit
				//From 8bit to Quoted
				$body = imap_8bit($body);
	
				//From quoted to text
				$body = quoted_printable_decode($body);

				break;
				
			case 2: //Binary
				//From 8 bit binary to BASE64
				$body = imap_8bit($body);

				//From BASE64 a text
				$body = imap_base64($body);
				break;
				
			case 3: //Base 64
				$body = imap_base64($body);
				break;
				
			case 4: //Quoted-printable
				$body = quoted_printable_decode($body);
				break;
				
			case 5: //Other
				break;
							
		}
		
		$body = utf8_encode($body);

		//Parse message	
		message_parse($subject, $body, $from);
	}	
	
	//Close mail connection
	if ($mail) {
		imap_close ($mail);
	}
}

function delete_incidents () {
	
	global $config;
	
	if ($config['months_to_delete_incidents'] != 0) {

		$limit = date ("Y/m/d H:i:s", (strtotime ("now")-($config['months_to_delete_incidents'] * 86400 * 30)));
		
		$sql_delete = "DELETE FROM tincidencia where inicio < '$limit'";
		$result = process_sql ($sql_delete);
		
	}
}

// ---------------------------------------------------------------------------
/* Main code goes here */
// ---------------------------------------------------------------------------

// Crontab control on tconfig, install first run to let user know where is

$installed = get_db_sql ("SELECT COUNT(*) FROM tconfig WHERE `token` = 'crontask'");
$current_date = date ("Y/m/d H:i:s");

if ($installed == 0){
	process_sql ("INSERT INTO tconfig (`token`,`value`) VALUES ('crontask', '$current_date')");
} else {
	process_sql ("UPDATE tconfig SET `value` = '$current_date' WHERE `token` = 'crontask'");
}


// Daily check only

if (check_daily_task()){
	run_daily_check ();
}

// Call enterprise crontab

enterprise_include ("include/integria_cron_enterprise.php");

// Execute always (POP3 processing)

run_mail_check();

// Execute always (Send pending mails, SMTP)

run_mail_queue();

// if enabled, run newsletter queue

if ($config["enable_newsletter"] == 1)
	run_newsletter_queue();

// Check SLA on active incidents (max. opened time without fixing and min. response)

$incidents = get_db_all_rows_sql ('SELECT * FROM tincidencia
	WHERE sla_disabled = 0
	AND estado NOT IN (6,7)');

if ($incidents === false)
	$incidents = array ();

if ($incidents)
    foreach ($incidents as $incident) {
    	check_sla_min ($incident);
    	check_sla_max ($incident);
    	check_sla_inactivity ($incident);
    	graph_sla($incident);
    }

// Check SLA for number of opened items.

$slas = get_slas (false);
foreach ($slas as $sla) {
	
	$sql = sprintf ('SELECT id_grupo FROM tgrupo WHERE id_grupo != 1 AND id_sla = %d', $sla['id']);
	
	$groups = get_db_all_rows_sql ($sql);
	if ($groups === false)
		$groups = array ();
	
	$noticed_groups = array ();
	
	foreach ($groups as $group) {
		$sql = sprintf ('SELECT id_incidencia, id_grupo 
			FROM tincidencia WHERE id_grupo = %d AND affected_sla_id = 0
			AND sla_disabled = 0 AND estado NOT IN (6,7)', $group['id_grupo']);
			
		$opened_incidents = get_db_all_rows_sql ($sql);
		
		if (sizeof ($opened_incidents) <= $sla['max_incidents']) 
			continue;
		
		/* There are too many open incidents */
		foreach ($opened_incidents as $incident) {
			/* Check if it was already notified in a specified time interval */
			$sql = sprintf ('SELECT COUNT(id) FROM tevent
				WHERE type = "SLA_MAX_OPENED_INCIDENTS_NOTIFY"
				AND id_item = %d
				AND timestamp > "%s"',
				$incident['id_grupo'],
				$compare_timestamp);
			$notified = get_db_sql ($sql);
			if ($notified > 0)
				continue;
			
			// Notify by mail for max. incidents opened (ONCE) to this 
			// the group email, if defined, if not, to default user.

			if (! isset ($noticed_groups[$incident['id_grupo']])) {
				$noticed_groups[$incident['id_grupo']] = 1;
				$group_name = dame_nombre_grupo ($incident['id_grupo']);
				$subject = "[".$config['sitename']."] Openened incident limit reached ($group_name)";
				$body = "Opened incidents limit for this group has been exceeded. Please check opened incidentes.\n";
				send_group_email ($incident['id_grupo'], $subject, $body);
				insert_event ('SLA_MAX_OPENED_INCIDENTS_NOTIFY',
					$incident['id_grupo']);
			}
		}
	}
}

// Clean temporal directory

$temp_dir = $config["homedir"]."/attachment/tmp";
delete_all_files_in_dir ($temp_dir);

delete_incidents();

?>

