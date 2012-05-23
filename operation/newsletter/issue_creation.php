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

$tinymce_path = $config["base_url"] ."/include/js/tiny_mce/tiny_mce.js";

check_login ();

echo '
<!-- TinyMCE -->
<script type="text/javascript" src="'.$tinymce_path.'"></script>
<script type="text/javascript">
	tinyMCE.init({
		extended_valid_elements : "iframe[src|style|width|height|scrolling|marginwidth|marginheight|frameborder]",
	invalid_elements : "",
        mode : "textareas",
        theme : "advanced",
	height: "500",
        plugins : "preview, print, table, searchreplace, nonbreaking,xhtmlxtras,noneditable",
        // Theme options
        theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,formatselect,fontselect,fontsize, select,|,tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr", theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
        theme_advanced_buttons3 : "",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_resizing : true,
	force_p_newlines : false,
	relative_urls : false,
	convert_urls : false,
	forced_root_block : "",
        theme_advanced_statusbar_location : "bottom"
	});
</script>
<!-- /TinyMCE -->';

if (! give_acl ($config["id_user"], 0, "VM")) {
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access newsletter management");
	require ("general/noaccess.php");
	exit;
}

$create = get_parameter("create", 0);
$id = get_parameter ("id", 0);

if ($create == 1) {
	$email_subject = "";
	$status = "0";
	$html = "";
	$plain = "";
	$id_newsletter = 0;
	$datetime = date ("Y-m-d H:i:s"); 
	
	echo "<h2>".__("Issue creation")."</h2>";
} else {
	echo "<h2>".__("Issue update")."</h2>";
	$issue = get_db_row ("tnewsletter_content", "id", $id);
	$html = $issue["html"];
	$plain = $issue["plain"];
	$id_newsletter = $issue["id_newsletter"];
	$datetime = $issue["datetime"];
	$status = $issue["status"];
	$email_subject = $issue["email_subject"];
}
	
$table->width = '90%';
$table->class = 'databox';
$table->colspan = array ();
$table->colspan[3][0] = 2;
$table->colspan[4][0] = 2;
$table->data = array ();


$table->data[0][0] = print_input_text ('email_subject', $email_subject, '', 40, 100, true, __('Email subject'));


$table->data[0][1] = print_input_text ('datetime', $datetime, '', 12, 100, true, __('Date time'));

$table->data[1][0] = print_select_from_sql ('SELECT id, name FROM tnewsletter ORDER BY name',
	'id_newsletter', $id_newsletter, '', '', '', true, false, false,__('Newsletter'));

$status_values[0] = __('Ready');
$status_values[1] = __('Pending');
$status_values[2] = __('Sent');

$table->data[1][1] = print_select ($status_values, "status", $status, '','','',true,0,true, __('Issue status'));

$table->data[3][0] = print_textarea ("plain", 10, 1, $plain, '', true, "<br>".__('Plain ascii'));
$table->data[4][0] = print_textarea ("html", 10, 1, $html, '', true, "<br>".__('HTML'));

echo '<form method="post" action="index.php?sec=customers&sec2=operation/newsletter/issue_definition">';
print_table ($table);


echo '<div class="button" style="width: '.$table->width.'">';
if ($id) {
		print_submit_button (__('Update'), 'update_btn', false, 'class="sub upd"');
		print_input_hidden ('id', $id);
		print_input_hidden ('update', 1);
	} else {
		print_submit_button (__('Create'), 'create_btn', false, 'class="sub next"');
		print_input_hidden ('create', 1);
	}
	echo "</div>";

echo "</form>";
