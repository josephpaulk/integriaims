<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2012 Ártica Soluciones Tecnológicas
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

if (! give_acl ($config["id_user"], 0, "VR")) {
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access company section");
	require ("general/noaccess.php");
	exit;
}

$manager = give_acl ($config["id_user"], 0, "VM");

$id = (int) get_parameter ('id');
$create = (bool) get_parameter ('create');
$update = (bool) get_parameter ('update');
$delete = (bool) get_parameter ('delete');
$start = (bool) get_parameter ('start');
$stop = (bool) get_parameter ('stop');
$retry = (bool) get_parameter ('retry');

// CREATE
if ($create) {
	if (!$manager) {
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create a new newsletter");
		require ("general/noaccess.php");
		exit;
	}
	

	$datetime = date ("Y-m-d H:i:s"); 
	$id_newsletter_content = get_parameter ("id_newsletter_content"); 
	$issue = get_db_row ("tnewsletter_content", "id", $id_newsletter_content);
	$newsletter = get_db_row ("tnewsletter", "id", $issue["id_newsletter"]);

	if (! give_acl ($config["id_user"], $newsletter["id_group"], "VM")) {
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create a newsletter");
		require ("general/noaccess.php");
		exit;
	}


	// First create the queue
	
	$sql = sprintf ('INSERT INTO tnewsletter_queue (id_newsletter_content, datetime, status) VALUES (%d, "%s", %d)', $issue["id"], $datetime, 0);	
	$id_queue = process_sql ($sql, 'insert_id');

	// Add addresses to this queue I've created !
	$sql = "SELECT * FROM tnewsletter_address WHERE status = 0 AND id_newsletter = ". $newsletter["id"];
	$queue = get_db_all_rows_sql ($sql);

	if ($queue !== false) {	
		foreach ($queue as $item) {
			$sql = sprintf ('INSERT INTO tnewsletter_queue_data (id_queue, id_newsletter, id_newsletter_content, email, name, datetime, status) VALUES (%d, %d, %d, "%s", "%s", "%s", %d)', $id_queue, $newsletter["id"], $issue["id"], $item["email"], $item["name"], $datetime, 0);
			$id = process_sql ($sql, 'insert_id');
		}
			
		if ($id === false)
			echo "<h3 class='error'>".__('Could not be created')."</h3>";
		else {
			echo "<h3 class='suc'>".__('Successfully created')."</h3>";
			audit_db ($config["id_user"], $config["REMOTE_ADDR"], "NEWSLETTER QUEUE CREATED", "Created newsletter queue for issue ".$issue["email_subject"]);
		}
	}
	$id = 0;
}

// START
if ($start) {
	$id = get_parameter("id", 0);
	$sql = "UPDATE tnewsletter_queue SET status = 1 WHERE id = $id";
	$result = mysql_query ($sql);
}

// STOP
if ($stop) {
	$id = get_parameter("id", 0);
	$sql = "UPDATE tnewsletter_queue SET status = 0 WHERE id = $id";
	$result = mysql_query ($sql);
}

// DELETE
if ($delete) { // if delete

	$id = (int) get_parameter ('id');
	$sql= sprintf ('DELETE FROM tnewsletter_queue WHERE id = %d', $id);
	process_sql ($sql);
	$sql= sprintf ('DELETE FROM tnewsletter_queue_data WHERE id_queue = %d', $id);
	process_sql ($sql);
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "NEWSLETTER QUEUE DELETED", "Deleted newsletter queue $id");
	echo "<h3 class='suc'>".__('Successfully deleted')."</h3>";
	$id = 0;
}

// RETRY BAD
if ($retry) { // if delete
	$id = get_parameter("id", 0);
	$sql = "UPDATE tnewsletter_queue SET status = 1 WHERE id = $id";
	$result = mysql_query ($sql);
	
	$sql = "UPDATE tnewsletter_queue_data SET status = 0 WHERE id_queue = $id AND status = 2";
	$result = mysql_query ($sql);
	$id = 0;
}



// General issue listing

echo "<h2>".__('Newsletter queue management')."</h2>";

if($manager) {
	echo '<form method="post" action="index.php?sec=customers&sec2=operation/newsletter/queue_manager&create=1">';
	echo "<table width=600 class=databox>";
	echo "<tr><td>";

	echo print_select_from_sql ('SELECT id, email_subject FROM tnewsletter_content ORDER BY email_subject', 'id_newsletter_content', 0, '', '', '', true, false, false,"");
	echo "<td>";
	print_submit_button (__('Create'), 'new_btn', false, 'class="sub next"');
	echo "</tr></table>";
	echo '</form>';
}

$sql = "SELECT * FROM tnewsletter_queue ORDER BY datetime DESC";
$queue = get_db_all_rows_sql ($sql);


if ($queue !== false) {
	$table->width = "90%";
	$table->class = "listing";
	$table->data = array ();
	$table->style = array ();
	$table->style[0] = 'font-weight: bold';
	$table->colspan = array ();
	$table->head[0] = __('Newsletter');
	$table->head[1] = __('Issue');
	$table->head[2] = __('Date');
	$table->head[3] = __('Status');
	$table->head[4] = __('Addresses');

	$table->head[4] .= print_help_tip (__("Total / Ready / Sent / Error"), true);		

	if(give_acl ($config["id_user"], $id_group, "VM")) {
		$table->head[5] = __('Delete');
		$table->head[5] .= print_help_tip (__("Similar to the stop operation, but will delete the queue contents. If crontask is being processed, addresses in memory will be processed"), true);			
		
		$table->head[6] = __('Start');
		$table->head[6] .= print_help_tip (__("Queue will be activated and mails will be sent in next crontab execution. You will see the detailed process in the errorlog"), true);			

		$table->head[7] = __('Stop');
		$table->head[7] .= print_help_tip (__("This will mark as pending the queue, if that queue is being processed in that moment, the whole batch will be processed"), true);
		
		$table->head[8] = __('Retry');
		$table->head[8] .= print_help_tip (__("This will mark as ready all email address marked as error in a previous attempt and rerun the qeue"), true);						

	}
	
	foreach ($queue as $items) {
		$data = array ();
		
		$id_issue = $items["id_newsletter_content"];
		$id_newsletter = get_db_sql ("SELECT id_newsletter FROM tnewsletter_content WHERE id = $id_issue");
		$newsletter = get_db_row ("tnewsletter", "id", $id_newsletter);
		$issue_name = get_db_sql ("SELECT email_subject FROM tnewsletter_content WHERE id = $id_issue");
		
		$data[0] = "<a href='index.php?sec=customers&sec2=operation/newsletter/newsletter_creation&id=".$items["id_newsletter"]."'>".$newsletter["name"]."</a>";
		
		$data[1] = "<a href='index.php?sec=customers&sec2=operation/newsletter/issue_creation&id=".
			$id_issue."'>".$issue_name."</a>";

		$data[2] = $items["datetime"];
		 
		if ($items["status"] == 1)
			$data[3] = __("Ready");
		elseif ($items["status"] == 0)
			$data[3] = __("Pending");
		elseif ($items["status"] == 2)
			$data[3] = __("In process");		
		else
			$data[3] = __("Done");	


		$total = get_db_sql ("SELECT COUNT(id) FROM tnewsletter_queue_data WHERE id_newsletter_content = $id_issue");
		$ready = get_db_sql ("SELECT COUNT(id) FROM tnewsletter_queue_data WHERE status = 0 AND id_newsletter_content = $id_issue");
		$done = get_db_sql ("SELECT COUNT(id) FROM tnewsletter_queue_data WHERE status = 1 AND id_newsletter_content = $id_issue");
		$error = get_db_sql ("SELECT COUNT(id) FROM tnewsletter_queue_data WHERE status = 2 AND id_newsletter_content = $id_issue");
		
		$data[4] = "$total / $ready / $done / $error";
	
		if(give_acl ($config["id_user"], $id_group, "VM")) {
			$data[5] ='<a href="index.php?sec=customers&sec2=operation/newsletter/queue_manager&
						delete=1&id='.$items['id'].'"
						onClick="if (!confirm(\''.__('Are you sure?').'\'))
						return false;">
						<img src="images/cross.png" title="delete"></a>';

			$data[6] ='<a href="index.php?sec=customers&sec2=operation/newsletter/queue_manager&
						start=1&id='.$items['id'].'"
						onClick="if (!confirm(\''.__('Are you sure?').'\'))
						return false;">
						<img src="images/go-next.png" title="start"></a>';


			$data[7] ='<a href="index.php?sec=customers&sec2=operation/newsletter/queue_manager&
						stop=1&id='.$items['id'].'"
						onClick="if (!confirm(\''.__('Are you sure?').'\'))
						return false;">
						<img src="images/exclamation.png" title="stop"></a>';
						
			$data[8] ='<a href="index.php?sec=customers&sec2=operation/newsletter/queue_manager&
						retry=1&id='.$items['id'].'"
						onClick="if (!confirm(\''.__('Are you sure?').'\'))
						return false;">
						<img src="images/arrow_refresh.png" title="Retry"></a>';
						

									
		}
		array_push ($table->data, $data);
	}
	print_table ($table);
}

?>
