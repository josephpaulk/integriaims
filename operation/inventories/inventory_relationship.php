<?php

// Integria IMS - http://integriaims.com
// ==================================================
// Copyright (c) 2008 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

check_login ();

require_once ('include/functions_inventories.php');
require_once ('include/functions_user.php');

$id = (int) get_parameter ('id');
$inventory_name = get_db_value('name', 'tinventory', 'id', $id);

echo "<h1>".__('Object')." #$id"."&nbsp;&nbsp;-&nbsp;".$inventory_name."</h1>";

//**********************************************************************
// Tabs
//**********************************************************************

echo '<div id="tabs">';

/* Tabs list */
echo '<ul class="ui-tabs-nav">';
echo '<li class="ui-tabs"><a href="index.php?sec=inventory&sec2=operation/inventories/inventory_detail&id=' . $id . '"><span>'.__('Details').'</span></a></li>';
echo '<li class="ui-tabs-selected"><a href="index.php?sec=inventory&sec2=operation/inventories/inventory_relationship&id=' . $id . '"><span>'.__('Relationships').'</span></a></li>';

echo '</ul>';
echo '</div>';

$delete_link = get_parameter ('delete_link', 0);
$add_link = get_parameter ('add_link', 0);
$ids_str = '';

if ($delete_link) {
	$id_src = get_parameter('id_src');
	$id_dst = get_parameter('id_dst');

	$result = process_sql_delete ('tinventory_relationship', array ('id_object_src' => $id_src, 'id_object_dst' => $id_dst));
	
	if ($result) {
		echo '<h3 class="suc">'.__("Inventory relationship deleted").'</h3>'; 
	} else {
		echo '<h3 class="error">'.__("Error deleting inventory relationship").'</h3>';
	}
}

if ($add_link) {
	$id_dst = get_parameter('link', 0);
	$id_src = get_parameter('id_src');
		
	$sql = "INSERT INTO tinventory_relationship (id_object_src, id_object_dst) VALUES ($id_src, $id_dst)";
	$result = process_sql($sql);
	
	if ($result) {
		echo '<h3 class="suc">'.__("Inventory relationship added").'</h3>'; 
	} else {
		echo '<h3 class="error">'.__("Error adding inventory relationship").'</h3>';
	}
}

$sql_links = "SELECT * FROM tinventory_relationship 
			WHERE `id_object_src`=$id OR `id_object_dst`=$id";
			
$all_links = get_db_all_rows_sql($sql_links);

if ($all_links == false) {
	$all_links = array();
}

$table->width = '100%';
$table->data = array ();
$table->head = array();
$table->style = array();
$table->size = array ();
$table->size[0] = '40%';
$table->size[1] = '40%';
$table->size[2] = '20%';
	
if (empty($all_links)) {
	echo "<h4>".__('No links')."</h4>";
} else {

	$table->head[0] = __("Source");
	$table->head[1] = __("Destination");
	$table->head[2] = __("Operation");

	$data = array();

	$ids_str .= $id;
	foreach ($all_links as $key => $link) {
		$id_src = $link['id_object_src'];
		$id_dst = $link['id_object_dst'];
		
		$ids_str .= ','.$id_src.','.$id_dst;
		
		$url = "index.php?sec=inventory&sec2=operation/inventories/inventory_relationship&delete_link=1&id_src=$id_src&id_dst=$id_dst&id=$id";
		
		$data[0] = inventories_link_get_name($id_src);
		$data[1] = inventories_link_get_name($id_dst);
		$data[2] = "<a
		onclick=\"if (!confirm('" . __('Are you sure?') . "')) return false;\" href='" . $url . "'>
		<img src='images/cross.png' border=0 /></a>";

		array_push ($table->data, $data);
	}
}

if ($ids_str == '') {
	$available_links = get_db_all_rows_sql("SELECT `id`,`name` FROM tinventory WHERE id NOT IN ($id)");
} else {
	$available_links = get_db_all_rows_sql("SELECT `id`,`name` FROM tinventory WHERE id NOT IN ($ids_str)");
}

if ($available_links == false) {
	$available_links = array();
}

$available_inventory = array();
foreach ($available_links as $key => $inventory) {
	$available_inventory[$inventory['id']] = $inventory['name'];
}

$url = "index.php?sec=inventory&sec2=operation/inventories/inventory_relationship&add_link=1&id_src=$id&id=$id";

$data[0] = "<form name=dataedit method=post action='" . $url . "'>";
$data[0] .= print_select($available_inventory, "link", '', '', __("Select inventory"), 0, true);

$data[1] = "";

$data[2] = print_input_image("add_link", "images/add.png", 1, '', true);
$data[2] .= "</form>";

array_push ($table->data, $data);

print_table($table);
?>
