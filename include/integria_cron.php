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

include ("config.php");
require_once ($config["homedir"].'/include/functions_calendar.php');
require_once ($config["homedir"].'/include/functions_groups.php');
require_once ($config["homedir"].'/include/functions_workunits.php');

// Activate errors. Should not be anyone, but if something happen, should be
// shown on console.

error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors", 1);

$config["id_user"] = 'System';
$now = time ();
$compare_timestamp = date ("Y-m-d H:i:s", $now - $config["notification_period"]);
$human_notification_period = give_human_time ($config["notification_period"]);

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
}


/**
 * Auto close incidents mark as "pending to be deleted" and no activity in X hrs
 * Takes no parameters. Checked in the main loop.with the other tasks.
 */

function run_auto_incident_close () {
	global $config;
    require_once ($config["homedir"]."/include/functions_incidents.php");

    $utimestamp = date("U");
	$limit = date ("Y/m/d H:i:s", $utimestamp - $config["auto_incident_close"] * 3600);

    // For each incident
	$incidents = get_db_all_rows_sql ("SELECT * FROM tincidencia WHERE estado IN (1,2,3,4,5) AND actualizacion < '$limit'");
    $mailtext = __("This incident has been closed automatically by Integria after waiting confirmation to close this incident for ").$config["auto_incident_close"]."  ".__("hours");

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
	$current_date .= " 23:59:59";
	$result = get_db_sql ("SELECT COUNT(id) FROM tevent
		WHERE type = 'DAILY_CHECK'
		AND timestamp < '$current_date'");
	if ($result > 0)
		// Daily check has been executed in the past 24 hours.
		return false;
	return true;
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

// This will send pending mail from database queue, using its defined MTA, and swiftmail functions

function run_mail_queue () {

	global $config;
	include_once ($config["homedir"]."/include/functions.php");
	require_once($config["homedir"] . "/include/swiftmailer/swift_required.php");

   	$utimestamp = date("U");
	// $current_date = date ("Y/m/d H:i:s");

	// get pending mails 
	$mails = get_db_all_rows_sql ("SELECT * FROM tpending_mail WHERE status = 0");

	if ($mails)
	foreach ($mails as $email){

		try {
			$transport = Swift_SmtpTransport::newInstance($config["smtp_host"], $config["smtp_port"]);
			$transport->setUsername($config["smtp_user"]);
			$transport->setPassword($config["smtp_pass"]);
			$mailer = Swift_Mailer::newInstance($transport);
			$message = Swift_Message::newInstance($email["subject"]);
			$message->setFrom($config["mail_from"]);
			$message->setTo(ascii_output($email['recipient']));
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

		}
	}
}

// This will check POP3 pending mail

function run_mail_check () {

	global $config;
	include_once ($config["homedir"]."/include/functions_pop3.php");

	// Inicialization for internal variables

	$error            = "";   //    Error string.
	$timeout          = 90;   //    Default timeout before giving up on a network operation.
	$Count            = -1;   //    Mailbox msg count
	
	$RFC1939          = true;  //    Set by noop(). See rfc1939.txt
	$msg_list_array = array();  //    List of messages from server
	$login            = $config["pop_user"];
	$pass             = $config["pop_pass"];
	$server           = $config["pop_host"];

    # Check if POP3 server is defined, if not, abort
    if ($server == ""){
        return;
    }

	set_time_limit($timeout);
	$fp = connect ($server, $port = 110);
	$Count = login($login,$pass, $fp);
	if( (!$Count) or ($Count < 1) ){
		return 1; 
	}

	// DEBUG
	// echo "Login OK: Inbox contains [$Count] messages<BR>\n";
	$msg_list_array = uidl("", $fp);
	set_time_limit($timeout);

	// Loop thru the array to get each message
	for ($i=1; $i <= $Count; $i++){
		set_time_limit($timeout);
		$MsgOne = get($i, $fp);

		if( (!$MsgOne) or (gettype($MsgOne) != "array") ) {
			echo "oops, Message not returned by the server.<BR>\n";
			return 2;
		}
		message_parse($MsgOne, $i, $fp);
	}

	// Close the email box and delete all messages marked for deletion
	quit($fp);
	return 0;

}

// ---------------------------------------------------------------------------
/* Main code goes here */
// ---------------------------------------------------------------------------


// Daily check only

if (check_daily_task ())
	run_daily_check ();

// Execute always (POP3 processing)

run_mail_check();

// Execute always (Send pending mails, SMTP)

run_mail_queue();

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
    }

// Check SLA for number of opened items.

$slas = get_slas (false);
foreach ($slas as $sla) {
	$sql = sprintf ('SELECT id FROM tinventory WHERE id_sla = %d', $sla['id']);
	
	$inventories = get_db_all_rows_sql ($sql);
	if ($inventories === false)
		$inventories = array ();
	
	$noticed_groups = array ();
	foreach ($inventories as $inventory) {
		$sql = sprintf ('SELECT tincidencia.id_incidencia, tincidencia.id_grupo 
			FROM tincidencia, tincident_inventory
			WHERE tincidencia.id_incidencia = tincident_inventory.id_incident
			AND tincident_inventory.id_inventory = %d
			AND affected_sla_id = 0
			AND sla_disabled = 0
			AND estado NOT IN (6,7)', $inventory['id']);
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


?>
