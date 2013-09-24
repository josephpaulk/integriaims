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

if (! give_acl ($config["id_user"], 0, "VM")) {
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access company section");
	require ("general/noaccess.php");
	exit;
}

include("include/functions_crm.php");

$manager = give_acl ($config["id_user"], 0, "VM");

$id = (int) get_parameter ('id');
$create = (bool) get_parameter ('create');
$update = (bool) get_parameter ('update');
$delete = (bool) get_parameter ('delete');

// CREATE
if ($create) {
	if (!$manager) {
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create a new newsletter");
		require ("general/noaccess.php");
		exit;
	}
	
	$email_subject = get_parameter ("email_subject");
	$status = get_parameter ("status");
	$html = get_parameter ("html");
	$plain = get_parameter ("plain");
	$datetime = get_parameter ("datetime"); 
	$id_newsletter = get_parameter ("id_newsletter"); 
	$campaign = get_parameter("campaign");
		
	if (! give_acl ($config["id_user"], $id_group, "VM")) {
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create a newsletter");
		require ("general/noaccess.php");
		exit;
	}

	$sql = sprintf ('INSERT INTO tnewsletter_content (id_newsletter, email_subject, status, datetime, html, plain, id_campaign) 
					VALUES (%d, "%s", "%s", "%s", "%s", "%s", %d)', $id_newsletter, $email_subject, $status, $datetime, 
					$html, $plain, $campaign);

	$id = process_sql ($sql, 'insert_id');
	if ($id === false)
		echo "<h3 class='error'>".__('Could not be created')."</h3>";
	else {
		echo "<h3 class='suc'>".__('Successfully created')."</h3>";
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "NEWSLETTER ISSUE CREATED", "Created newsletter $email_subject");
	}
	$id = 0;
}

// UPDATE
if ($update) {

	$id = get_parameter("id", 1);
	$email_subject = get_parameter ("email_subject");
	$status = get_parameter ("status");
	$html = get_parameter ("html");
	$plain = get_parameter ("plain");
	$datetime = get_parameter ("datetime"); 
	$id_newsletter = get_parameter ("id_newsletter"); 
	$campaign = get_parameter("campaign");

	$sql = sprintf ('UPDATE tnewsletter_content SET id_newsletter = %d, email_subject = "%s", html = "%s",
		plain = "%s", status = "%s",datetime = "%s", id_campaign = %d WHERE id = %d',
		$id_newsletter, $email_subject, $html, $plain, $status, $datetime, $campaign, $id);

	$result = mysql_query ($sql);
	if ($result === false)
		echo "<h3 class='error'>".__('Could not be updated')."</h3>";
	else {
		echo "<h3 class='suc'>".__('Successfully updated')."</h3>";
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "NEWSLETTER UPDATED", "Updated newsletter issue $email_subject");
	}
	$id = 0;
}

// DELETE
if ($delete) { // if delete

	$id = (int) get_parameter ('id');

	$id_newsletter = get_db_value("id_newsletter", "tnewsletter_content", "id", $id);
	$name = get_db_value ('name', 'tnewsletter', 'id', $id_newsletter);
	
	$sql= sprintf ('DELETE FROM tnewsletter_content WHERE id = %d', $id);
	process_sql ($sql);
	
	$sql= sprintf ('DELETE FROM tnewsletter_tracking WHERE id_newsletter_content = %d', $id);
	process_sql ($sql);

	$sql= sprintf ('DELETE FROM tnewsletter_queue_data WHERE id_newsletter_content = %d', $id);
	process_sql ($sql);
	
	insert_event ("NEWSLETTER ISSUE DELETED", $id, 0, $name);
	echo "<h3 class='suc'>".__('Successfully deleted')."</h3>";
	$id = 0;
}


// General issue listing

echo "<h2>".__('Newsletter issue management')."</h2>";
echo "<br>";
$search_text = (string) get_parameter ('search_text');	
$where_clause = "WHERE 1=1 ";

if ($search_text != "") {
	$where_clause .= sprintf ('AND email_subject LIKE "%%%s%%" OR html LIKE "%%%s%%" OR plain LIKE "%%%s%%"', $search_text, $search_text, $search_text);
}

$table->width = '90%';
$table->class = 'search-table';
$table->style = array ();
$table->style[0] = 'font-weight: bold;';
$table->style[2] = 'font-weight: bold;';
$table->data = array ();
$table->data[0][0] = __('Search');
$table->data[0][1] = print_input_text ("search_text", $search_text, "", 25, 100, true);
$table->data[0][4] = print_submit_button (__('Search'), "search_btn", false, 'class="sub search"', true);

echo '<form method="post" action="">';
print_table ($table);
echo '</form>';

$sql = "SELECT * FROM tnewsletter_content $where_clause ORDER BY datetime";
$issues = get_db_all_rows_sql ($sql);

$issues = print_array_pagination ($issues, "index.php?sec=customers&sec2=operation/newsletter/operation/newsletter/issues_definition&search_text='$search_text");

if ($issues !== false) {
	$table->width = "90%";
	$table->class = "listing";
	$table->data = array ();
	$table->style = array ();
	$table->style[0] = 'font-weight: bold';
	$table->colspan = array ();
	$table->head[0] = __('Newsletter');
	$table->head[1] = __('Issue #');
	$table->head[2] = __('Subject');
	$table->head[3] = __('Date');
	$table->head[4] = __('Status');
	$table->head[5] = __('Reads');
	if(give_acl ($config["id_user"], $id_group, "VM")) {
		$table->head[6] = __('Delete');
	}

	
	foreach ($issues as $issue) {
		$data = array ();
		
		$newsletter_name = get_db_value ('name', 'tnewsletter', 'id', $issue["id_newsletter"]);
		
		$data[0] = "<a href='index.php?sec=customers&sec2=operation/newsletter/newsletter_creation&id=".$issue["id_newsletter"]."'>$newsletter_name</a>";
		
		$data[1] = "<b>".$issue["id"]."</b>";
		
		$data[2] = "<a href='index.php?sec=customers&sec2=operation/newsletter/issue_creation&id=".
			$issue["id"]."'>".$issue["email_subject"]."</a>";

		$data[3] = $issue["datetime"];
		 
		if ($issue["status"] == 1)
			$data[4] = __("Ready");
		elseif ($issue["status"] == 0)
			$data[4] = __("Pending");	
		else
			$data[4] = __("Sent");	

		$data[5] = crm_get_issue_reads($issue["id"]);

		$data[6] = "<a target='_top' href='include/newsletter.php?operation=read&id=".$issue["id"]."'><img src='images/eye.png'></a> ";
	
		if(give_acl ($config["id_user"], $id_group, "VM")) {
			$data[6] .='<a href="index.php?sec=customers&sec2=operation/newsletter/issue_definition&
						delete=1&id='.$issue['id'].'"
						onClick="if (!confirm(\''.__('Are you sure?').'\'))
						return false;">
						<img src="images/cross.png"></a>';
		}
		array_push ($table->data, $data);
	}
	print_table ($table);
}

if($manager) {
	echo '<form method="post" action="index.php?sec=customers&sec2=operation/newsletter/issue_creation&create=1">';
	echo '<div class="button" style="width: '.$table->width.'">';
	print_submit_button (__('Create'), 'new_btn', false, 'class="sub next"');
	echo '</div>';
	echo '</form>';
}


?>
