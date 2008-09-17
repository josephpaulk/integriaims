<?php

// Integria 2.0 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008 Artica Soluciones Tecnologicas
// Copyright (c) 2008 Esteban Sanchez, estebans@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

if (check_login () != 0) {
	audit_db ("Noauth", $config["REMOTE_ADDR"], "No authenticated access","Trying to access incident viewer");
	require ("general/noaccess.php");
	exit;
}

$id_profile = (int) get_parameter ('user_profile_search');
$id_group = (int) get_parameter ('user_group_search');
$search_string = (string) get_parameter ('text-search_string');
$search = (bool) get_parameter ('search');

if ($search) {
	$sql = sprintf ('SELECT id, name, description, comments
			FROM tinventory');
	$inventories = get_db_all_rows_sql ($sql);
	if ($inventories === false) {
		$inventories = array ();
	}
	
	$total_inventories = 0;
	foreach ($inventories as $inventory) {
				
		echo '<tr id="result-'.$inventory['id'].'">';
		echo '<td>'.$inventory['name'].'</td>';
		echo '<td>'.$inventory['description'].'</td>';
		echo '<td>'.$inventory['comments'].'</td>';
		echo '</tr>';
		$total_inventories++;
	}
	
	if ($total_inventories == 0) {
		echo '<tr colspan="4">'.lang_string ('No inventory found').'</tr>';
	}
	
	if (defined ('AJAX'))
		return;
}

$table->data = array ();
$table->width = '97%';
$table->style = array ();
$table->style[0] = 'font-weight: bold';

$table->data[0][0] = print_label (lang_string ('Name'), 'search_string', 'text', true);
$table->data[0][0] .= print_input_text ('search_string', '', '', 20, 255, true);

echo '<div id="inventory_search_result"></div>';

echo '<form id="inventory_search_form" method="post">';
print_table ($table);
echo '<div class="action-buttons" style="width: '.$table->width.'">';
print_input_hidden ('search', 1);
print_submit_button (lang_string ('Search'), 'search_button');
echo '</div>';
echo '</form>';

unset ($table);
$table->class = 'hide result_table listing';
$table->width = '90%';
$table->id = 'inventory_search_result_table';
$table->head = array ();
$table->head[0] = lang_string ("Name");
$table->head[1] = lang_string ("Description");
$table->head[2] = lang_string ("Comments");

print_table ($table);

echo '<div id="inventory-pager" class="hide pager">';
echo '<form>';
echo '<img src="images/go-first.png" class="first" />';
echo '<img src="images/go-previous.png" class="prev" />';
echo '<input type="text" class="pagedisplay" />';
echo '<img src="images/go-next.png" class="next" />';
echo '<img src="images/go-last.png" class="last" />';
if (defined ('AJAX')) {
	echo '<select class="pagesize" style="display: none">';
	echo '<option selected="selected" value="5">5</option>';
} else {
	echo '<select class="pagesize">';
	echo '<option selected="selected" value="10">10</option>';
	echo '<option value="20">20</option>';
	echo '<option value="30">30</option>';
	echo '<option  value="40">40</option>';
	echo '</select>';
}
echo '</select>';
echo '</form>';
echo '</div>';

?>
