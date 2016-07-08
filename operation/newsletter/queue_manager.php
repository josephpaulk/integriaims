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

if (! give_acl ($config["id_user"], 0, "CN")) {
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access company section");
	require ("general/noaccess.php");
	exit;
}

if (defined ('AJAX')) {
		
	$calculate_total = get_parameter('calculate_total', 0);
	$create_queue = get_parameter('create_queue', 0);
	$add_address = get_parameter('add_address', 0);
	
	if ($calculate_total) {
		$id_newsletter_content = get_parameter('id_newsletter_content');
		$issue = get_db_row ("tnewsletter_content", "id", $id_newsletter_content);
		$newsletter = get_db_row ("tnewsletter", "id", $issue["id_newsletter"]);
	
		$sql = "SELECT COUNT(id) FROM tnewsletter_address WHERE status = 0 AND id_newsletter = ". $newsletter["id"];
		$total = get_db_value_sql($sql);
	
		echo json_encode($total);
		return;
	}
	
	if ($create_queue) {

		$id_newsletter_content = get_parameter('id_newsletter_content');
		
		$datetime = date ("Y-m-d H:i:s"); 
		$issue = get_db_row ("tnewsletter_content", "id", $id_newsletter_content);
		$newsletter = get_db_row ("tnewsletter", "id", $issue["id_newsletter"]);
		
		//Create the queue
		$sql = sprintf ('INSERT INTO tnewsletter_queue (id_newsletter_content, datetime, status) VALUES (%d, "%s", %d)', $issue["id"], $datetime, 0);
		$id_queue = process_sql ($sql, 'insert_id');
	
		echo json_encode($id_queue);
		return;
	}
	
	if ($add_address) {
		$id_queue = get_parameter('id_queue');
		$limit = get_parameter('limit');
		$offset = get_parameter('offset');
		$id_newsletter_content = get_parameter('id_newsletter_content');
		$datetime = date ("Y-m-d H:i:s"); 
		
		$issue = get_db_row ("tnewsletter_content", "id", $id_newsletter_content);
		$newsletter = get_db_row ("tnewsletter", "id", $issue["id_newsletter"]);
	
		$sql = "SELECT * FROM tnewsletter_address WHERE status = 0 AND id_newsletter = ". $newsletter["id"]." LIMIT $limit OFFSET $offset";

		$queue = get_db_all_rows_sql ($sql);

		if ($queue !== false) {
			foreach ($queue as $item) {
				$sql = sprintf ('INSERT INTO tnewsletter_queue_data (id_queue, id_newsletter, id_newsletter_content, email, name, datetime, status) VALUES (%d, %d, %d, "%s", "%s", "%s", %d)', $id_queue, $newsletter["id"], $issue["id"], $item["email"], $item["name"], $datetime, 0);
				process_sql ($sql);
			}
				
			audit_db ($config["id_user"], $config["REMOTE_ADDR"], "NEWSLETTER QUEUE CREATED", "Created newsletter queue for issue ".$issue["email_subject"]);
		}
	}
	
	$return = 1;
	echo json_encode($return);
	return;
}

$manager = give_acl ($config["id_user"], 0, "CN");

$id = (int) get_parameter ('id');
$create = (bool) get_parameter ('create');
$update = (bool) get_parameter ('update');
$delete = (bool) get_parameter ('delete');
$start = (bool) get_parameter ('start');
$stop = (bool) get_parameter ('stop');
$retry = (bool) get_parameter ('retry');
$disable_bad = (bool) get_parameter ('disabled_bad');

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

	if (! give_acl ($config["id_user"], $newsletter["id_group"], "CN")) {
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
			process_sql ($sql);
		}
			
		echo ui_print_success_message (__('Successfully created'), '', true, 'h3', true);
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "NEWSLETTER QUEUE CREATED", "Created newsletter queue for issue ".$issue["email_subject"]);
		
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
	echo ui_print_success_message (__("Successfully deleted"), '', true, 'h3', true);
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

// DISABLE BAD
if ($disable_bad) { // if delete
	$id = get_parameter("id", 0);
	
	$id_newsletter = get_db_sql ("SELECT id_newsletter FROM tnewsletter_content WHERE id = $id");
	// Get all bad addresses and disable the original address in newsletter
	$sql = "SELECT * FROM tnewsletter_queue_data WHERE status = 2 AND id_newsletter = ". $id;
	$data = get_db_all_rows_sql ($sql);
	if ($data) 
	foreach ($data as $item) {
		$sql = "UPDATE tnewsletter_address SET status = 1 WHERE id_newsletter = $id AND email = '".$item["email"]."'";
		$result = mysql_query ($sql);
	}
	echo ui_print_success_message (__('Disabled bad addresses'), '', true, 'h3', true);
	$id = 0;
}

echo '<br>';
echo '<div id="loading" style="display:none; width: 90%; margin-left: 0;">';
echo print_image("images/wait.gif", true, array("border" => '0')) . '<br />';
echo '<strong>' . __('Please wait...') . '</strong>';
echo '</div>';

// General issue listing

echo "<h2>".__('Newsletter queue management')."</h2>";

if($manager) {
	echo '<form method="post" action="index.php?sec=customers&sec2=operation/newsletter/queue_manager&create=1">';
	echo "<table width=90% class=databox>";
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
	$table->style[2] = 'font-size: 9px';
	$table->style[1] = 'font-size: 10px';
	$table->style[0] = 'font-size: 10px';
	$table->style[3] = 'font-size: 10px';
	$table->colspan = array ();
	$table->head[0] = __('Newsletter');
	$table->head[1] = __('Issue');
	$table->head[2] = __('Date');
	$table->head[3] = __('Status');
	$table->head[4] = __('Addresses');
	
	$table->head[4] .= print_help_tip (__("Total / Ready / Sent / Error"), true);		

	if(give_acl ($config["id_user"], $id_group, "CN")) {
		$table->head[5] = __('Delete');
		$table->head[5] .= print_help_tip (__("Similar to the stop operation, but will delete the queue contents. If crontask is being processed, addresses in memory will be processed"), true);			
		
		$table->head[6] = __('Start');
		$table->head[6] .= print_help_tip (__("Queue will be activated and mails will be sent in next crontab execution. You will see the detailed process in the errorlog"), true);			

		$table->head[7] = __('Stop');
		$table->head[7] .= print_help_tip (__("This will mark as pending the queue, if that queue is being processed in that moment, the whole batch will be processed"), true);
		
		$table->head[8] = __('Retry');
		$table->head[8] .= print_help_tip (__("This will mark as ready all email address marked as error in a previous attempt and rerun the qeue"), true);
		
		$table->head[9] = __('Disable Bad Address');
		$table->head[9] .= print_help_tip (__("This will mark as disable all email address which cannot be sent. Warning, this will be done in the address associated to the newsletter."), true);												

	}
	
	foreach ($queue as $items) {
		$data = array ();

		$id_issue = $items["id_newsletter_content"];

		//$id_newsletter = get_db_sql ("SELECT id_newsletter FROM tnewsletter_content WHERE id = $id_issue");
		$id_newsletter = get_db_value('id_newsletter', 'tnewsletter_queue_data', 'id_queue', $items['id']);
		$newsletter = get_db_row ("tnewsletter", "id", $id_newsletter);

		$issue_name = get_db_sql ("SELECT email_subject FROM tnewsletter_content WHERE id = $id_issue");
		
		$data[0] = "<a href='index.php?sec=customers&sec2=operation/newsletter/newsletter_creation&id=".$id_newsletter."'>".$newsletter["name"]."</a>";
		
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

		if ($id_newsletter) {
			$total = get_db_sql ("SELECT COUNT(id) FROM tnewsletter_queue_data WHERE id_newsletter_content = $id_issue and id_newsletter = $id_newsletter");
			$ready = get_db_sql ("SELECT COUNT(id) FROM tnewsletter_queue_data WHERE status = 0 AND id_newsletter_content = $id_issue and id_newsletter = $id_newsletter");
			$done = get_db_sql ("SELECT COUNT(id) FROM tnewsletter_queue_data WHERE status = 1 AND id_newsletter_content = $id_issue and id_newsletter = $id_newsletter ");
			$error = get_db_sql ("SELECT COUNT(id) FROM tnewsletter_queue_data WHERE status = 2 AND id_newsletter_content = $id_issue and id_newsletter = $id_newsletter");
			
			$data[4] = "$total / $ready / $done / $error";
		}
		
		$data[4] = __("Empty");

	
		if(give_acl ($config["id_user"], $id_group, "CN")) {
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

			$data[9] ='<a href="index.php?sec=customers&sec2=operation/newsletter/queue_manager&
						disabled_bad=1&id='.$items['id'].'"
						onClick="if (!confirm(\''.__('Are you sure?').'\'))
						return false;">
						<img src="images/bug.png" title="Disable bad address"></a>';
						

									
		}
		array_push ($table->data, $data);
	}
	print_table ($table);
}

?>

<script language="javascript" type="text/javascript">

$(document).ready (function () {
	$("#loading").css("display", "none");	
});
	
$('#submit-new_btn').click(function (event) {
		
	event.preventDefault();
	$("#loading").css("display", "");
	var id_newsletter_content = $('#id_newsletter_content').val();
	var limit = 200;
	var offset = 0;
	var offset_final = 0;
	
	$.ajax ({
		type: "POST",
		data: "page=<?php echo $_GET['sec2']; ?>&calculate_total=1&id_newsletter_content="+id_newsletter_content,
		url: "ajax.php",
		async: false,
		timeout: 10000,
		dataType: 'json',
		success: function (data_total, status) {
			var total = data_total;
			var i_max = Math.ceil((total/limit));
	
			$.ajax ({
				type: "POST",
				data: "page=<?php echo $_GET['sec2']; ?>&create_queue=1&id_newsletter_content="+id_newsletter_content,
				url: "ajax.php",
				async: false,
				timeout: 10000,
				dataType: 'json',
				success: function (data_queue, status) {

					var id_queue = data_queue;
					
					for(i=1;i<=i_max;i++) {
						if (i != 1) {
							offset_final += limit; 
						}
						
						$.ajax ({
							type: "POST",
							data: "page=<?php echo $_GET['sec2']; ?>&add_address=1&id_newsletter_content="+id_newsletter_content+"&id_queue="+id_queue+"&limit="+limit+"&offset="+offset_final,
							url: "ajax.php",
							async: false,
							timeout: 10000,
							dataType: 'json',
							success: function (data_result, status) {
		
							}
						}); //end add
					}
				}
			}); //end create queue
		}
	});
	
	location.reload();

});
</script>
