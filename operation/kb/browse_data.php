<?php
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.


global $config;
check_login();

if (! give_acl ($config["id_user"], 0, "KR")) {
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access KB Browser");
	require ("general/noaccess.php");
	exit;
}

// Review form
if (! isset ($_GET["view"])) {
	return;
}

$id = (int) get_parameter ('view');
$kb_data = get_db_row ("tkb_data", "id", $id);
$data = $kb_data["data"];
$title = $kb_data["title"];
$timestamp = $kb_data["timestamp"];
$product = '';
if ($kb_data["id_product"])
	$product = get_db_value ('name', 'tkb_product', 'id', $kb_data['id_product']);

$category = '';
if ($kb_data["id_category"])
	$category = get_db_value ('name', 'tkb_category', 'id', $kb_data['id_category']);

echo '<h2>'.__('KB article review').'</h2>';
$avatar = get_db_value ('avatar', 'tusuario', 'id_usuario', $kb_data['id_user']);

echo "<p><b>$title</b>";

// Title header
echo "<div class='notetitle' style='height: 50px;'>"; 
echo "<table class='blank' border=0 width='100%' cellspacing=0 cellpadding=0 style='background: transparent; line-height: 12px; border: 0px; margin-left: 0px;margin-top: 0px;'>";
echo "<tr><td rowspan=3 width='7%'>";
echo "<img src='images/avatars/".$avatar."_small.png'>";

echo "<td width='50%'><b>";
echo __('Author')." </b> : ";
echo '<a href="index.php?sec=users&sec2=operation/users/user_edit&id='.$kb_data['id_user'].'">';
echo $kb_data['id_user'];
echo "</a>";
echo "<td> <b>";
echo __('Product')." </b> : ";
echo $product;

echo "<tr>";
echo "<td>";

echo " ".__("Wrote on ").$timestamp;
echo "<td>";
echo "<b>";
echo __('Category')." </b> : ";
echo $category;

echo "<td align=right>";
if (give_acl ($config["id_user"], 0, "KM")){
	echo "<a href='index.php?sec=kb&sec2=operation/kb/manage_data&update=".$kb_data['id']."'><img border=0 src='images/page_white_text.png'></a>";
}

echo "</table>";
echo "</div>";

// Body
echo "<div class='notebody'>";
echo "<table class='blank' width='100%' cellpadding=0 cellspacing=0>";
echo "<tr><td valign='top'>";
echo clean_output_breaks ($data);
echo "</table>";
echo "</div>";


// Show list of attachments
$attachments = get_db_all_rows_field_filter ('tattachment', 'id_kb', $id, 'description');
if ($attachments !== false) {
	echo '<h3>'.__('Attachment list').'</h3>';
	
	$table->width = '735';
	$table->class = 'listing';
	$table->data = array ();
	$table->head = array ();
	$table->head[0] = __('Filename');
	$table->head[1] = __('Description');
	
	foreach ($attachments as $attachment) {
		$data = array ();
		
		$attach_id = $attachment['id_attachment'];
		$link = 'attachment/'.$attachment['id_attachment'].'_'.$attachment['filename'];
		$data[0] = '<a href="'.$link.'" title="'.$attachment['description'].'">';
		$data[0] .= '<img src="images/disk.png"/> ';
		$data[0] .= $attachment['filename'];
		$data[0] .= '</a>';
		$data[1] = $attachment['description'];
		
		array_push ($table->data, $data);
	}
	print_table ($table);
	echo "</div>";
}
?>
