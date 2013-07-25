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

global $config;

check_login ();

require_once ('include/functions_inventories.php');
require_once ('include/functions_user.php');

enterprise_include('include/functions_inventory.php');
	
if (defined ('AJAX')) {
	
	global $config;
	
	$print_subtree = get_parameter('print_subtree', 0);
	
	$id_item = get_parameter ('id_item');
	$lessBranchs = get_parameter('less_branchs');
	$type = get_parameter('type');
	$id_father = get_parameter('id_father');
	$sql_search = base64_decode(get_parameter('sql_search', ''));

	
	if ($type == 'object_types') {

		$sql = "SELECT tinventory.`id`, tinventory.`name` FROM tinventory, tobject_type, tobject_field_data
				WHERE `id_object_type`=$id_item
				AND tinventory.id_object_type = tobject_type.id $sql_search
				GROUP BY tinventory.`id`";
		
		$cont_aux = get_db_all_rows_sql($sql);

		$countRows = count($cont_aux);

		//Empty Branch
		if ($countRows == 0) {

			echo "<ul style='margin: 0; padding: 0;'>\n";
			echo "<li style='margin: 0; padding: 0;'>";
			if ($lessBranchs == 1)
				echo print_image ("images/tree/no_branch.png", true, array ("style" => 'vertical-align: middle;'));
			else
				echo print_image ("images/tree/branch.png", true, array ("style" => 'vertical-align: middle;'));
			
			echo "<i>" . __("Empty") . "</i>";
			echo "</li>";
			echo "</ul>";
			
			return;
		}
		
		//Branch with items
		$new = true;
		$count = 0;
		echo "<ul style='margin: 0; padding: 0;'>\n";
		
		$sql_search = base64_encode($sql_search);
		
		$cont = enterprise_hook ('inventory_get_user_inventories', array ($config['id_user'], $cont_aux));
		if ($cont === ENTERPRISE_NOT_HOOK) {
			$cont = $cont_aux;
		}
			
		foreach ($cont as $key => $row) {

			$new = false;
			$count++;
		
			$less = $lessBranchs;
			if ($count != $countRows)
				$img = print_image ("images/tree/closed.png", true, array ("style" => 'vertical-align: middle;', "id" => "tree_image" . $id_item. "_inventory_" . $row["id"], "pos_tree" => "2"));
			else {
				$less = $less + 2; // $less = $less or 0b10
				$img = print_image ("images/tree/last_closed.png", true, array ("style" => 'vertical-align: middle;', "id" => "tree_image" . $id_item. "_inventory_" . $row["id"], "pos_tree" => "3"));
			}
			echo "<li style='margin: 0; padding: 0;'>";
			
			echo "<a onfocus='JavaScript: this.blur()'
						href='javascript: loadTable(\"inventory\",\"" . $row["id"] . "\", " . $less . ", \"" . $id_item . "\", \"" . $id_father . "\",  \"" . $sql_search . "\")'>";
			
			if ($lessBranchs == 1) {
				print_image ("images/tree/no_branch.png", false, array ("style" => 'vertical-align: middle;'));
			} else {
				print_image ("images/tree/branch.png", false, array ("style" => 'vertical-align: middle;'));
			}
			echo $img;
			
			echo $row["name"]. '&nbsp;&nbsp;<a href="index.php?sec=inventory&sec2=operation/inventories/inventory_detail&id='.$row['id'].'">'.print_image ("images/edit.png", true, array ("style" => 'vertical-align: middle;')).'</a>';
			
			echo "</a>";
			echo "<div hiddenDiv='1' loadDiv='0' style='margin: 0px; padding: 0px;' class='tree_view' id='tree_div" . $id_item . "_inventory_" . $row["id"] . "'></div>";
			echo "</li>";
			
		} 
	}
	echo "</ul>\n";
			
	//TERCER NIVEL DEL ARBOL.
	if ($type == 'inventory') {

		$sql = "SELECT tinventory.`id`, tinventory.`name` FROM tinventory, tobject_type, tobject_field_data
				WHERE `id_parent`=$id_item
				AND tinventory.id_object_type = tobject_type.id $sql_search
				GROUP BY tinventory.`id`";
		
		$cont_invent = get_db_all_rows_sql($sql);

		$countRows = count($cont_invent);
	
		if ($countRows === false)
			$countRows = 0;
	
		if ($countRows == 0) {
			echo "<ul style='margin: 0; padding: 0;'>\n";
			echo "<li style='margin: 0; padding: 0;'>";
			
			switch ($lessBranchs) {
				case 0:
					print_image ("images/tree/branch.png", false, array ("style" => 'vertical-align: middle;'));
					print_image ("images/tree/branch.png", false, array ("style" => 'vertical-align: middle;'));
					break;
				case 1:
					print_image ("images/tree/no_branch.png", false, array ("style" => 'vertical-align: middle;'));
					print_image ("images/tree/branch.png", false, array ("style" => 'vertical-align: middle;'));
					break;
				case 2:
					print_image ("images/tree/branch.png", false, array ("style" => 'vertical-align: middle;'));
					print_image ("images/tree/no_branch.png", false, array ("style" => 'vertical-align: middle;'));
					break;
				case 3:
					print_image ("images/tree/no_branch.png", false, array ("style" => 'vertical-align: middle;'));
					print_image ("images/tree/no_branch.png", false, array ("style" => 'vertical-align: middle;'));
					break;
			}
			
			echo "<i>" . __("Empty") . "</i>";
			echo "</li>";
			echo "</ul>";
			return;
		}
	
		//Branch with items
		$new = true;
		$count = 0;
		echo "<ul style='margin: 0; padding: 0;'>\n";

		$cont = enterprise_hook ('inventory_get_user_inventories', array ($config['id_user'], $cont_invent));
		if ($cont === ENTERPRISE_NOT_HOOK) {
			$cont = $cont_invent;
		}
		
		foreach ($cont as $key => $row) {

			$new = false;
			$count++;

			$less = $lessBranchs;
			if ($count != $countRows) {
				$img = print_image ("images/tree/closed.png", true, array ("style" => 'vertical-align: middle;', "id" => "tree_image" . $id_item. "_child_" . $row["id"], "pos_tree" => "2"));
			} else {
				$less = $less + 2; // $less = $less or 0b10
				$img = print_image ("images/tree/last_closed.png", true, array ("style" => 'vertical-align: middle;', "id" => "tree_image" . $id_item. "_child_" . $row["id"], "pos_tree" => "3"));
			}


			echo "<li style='margin: 0; padding: 0;'>";

			echo "<a onfocus='JavaScript: this.blur()'
						href='javascript: loadTable(\"child\",\"" . $row["id"] . "\", " . $less . ", \"" . $id_item . "\", \"" . $id_father . "\",  \"" . $sql_search . "\")'>";

			switch ($lessBranchs) {
				case 0:

					print_image ("images/tree/branch.png", false, array ("style" => 'vertical-align: middle;'));
					print_image ("images/tree/branch.png", false, array ("style" => 'vertical-align: middle;'));

					break;
				case 1:
					print_image ("images/tree/no_branch.png", false, array ("style" => 'vertical-align: middle;'));
					print_image ("images/tree/branch.png", false, array ("style" => 'vertical-align: middle;'));
					break;
				case 2:
					print_image ("images/tree/branch.png", false, array ("style" => 'vertical-align: middle;'));
					print_image ("images/tree/no_branch.png", false, array ("style" => 'vertical-align: middle;'));
					break;
				case 3:
					print_image ("images/tree/no_branch.png", false, array ("style" => 'vertical-align: middle;'));
					print_image ("images/tree/no_branch.png", false, array ("style" => 'vertical-align: middle;'));
					break;
			}
			echo $img;

			echo $row["name"]. '&nbsp;&nbsp;'.'<a href="index.php?sec=inventory&sec2=operation/inventories/inventory_detail&id='.$row['id'].'">'.print_image ("images/edit.png", true, array ("style" => 'vertical-align: middle;')).'</a>';

			echo "</a>";
			echo "<div hiddenDiv='1' loadDiv='0' style='margin: 0px; padding: 0px;' class='tree_view tree_div_".$id_item."' id='tree_div" . $id_item . "_child_" . $row["id"] . "'></div>";
			echo "</li>";
		}
		echo "</ul>\n";

	}
	
	//CUARTO NIVEL DEL ARBOL.
	if ($type == 'child') {

		$sql = "SELECT tinventory.`id`, tinventory.`name` FROM tinventory, tobject_type, tobject_field_data
				WHERE `id_parent`=$id_item
				$sql_search
				GROUP BY tinventory.`id`";
		
		$cont_invent = get_db_all_rows_sql($sql);
		
		$countRows = count($cont_invent);
		
		if ($countRows === false)
			$countRows = 0;
	
		if ($countRows == 0) {
			echo "<ul style='margin: 0; padding: 0;'>\n";
			echo "<li style='margin: 0; padding: 0;'>";
			
			switch ($lessBranchs) {
				case 0:
					print_image ("images/tree/branch.png", false, array ("style" => 'vertical-align: middle;'));
					print_image ("images/tree/branch.png", false, array ("style" => 'vertical-align: middle;'));
					break;
				case 1:
					print_image ("images/tree/no_branch.png", false, array ("style" => 'vertical-align: middle;'));
					print_image ("images/tree/branch.png", false, array ("style" => 'vertical-align: middle;'));
					break;
				case 2:
					print_image ("images/tree/branch.png", false, array ("style" => 'vertical-align: middle;'));
					print_image ("images/tree/no_branch.png", false, array ("style" => 'vertical-align: middle;'));
					break;
				case 3:
					print_image ("images/tree/no_branch.png", false, array ("style" => 'vertical-align: middle;'));
					print_image ("images/tree/no_branch.png", false, array ("style" => 'vertical-align: middle;'));
					break;
			}
			
			print_image ("images/tree/last_leaf.png", false, array ("style" => 'vertical-align: middle;'));
			echo "<i>" . __("Empty") . "</i>";
			echo "</li>";
			echo "</ul>";
			return;
		}
		
		$new = true;
		$count = 0;
		echo "<ul style='margin: 0; padding: 0;'>\n";
		
		$cont = enterprise_hook ('inventory_get_user_inventories', array ($config['id_user'], $cont_invent));
		if ($cont === ENTERPRISE_NOT_HOOK) {
			$cont = $cont_invent;
		}
/*
		if ($is_enterprise) {
			$cont = inventory_get_user_inventories($config['id_user'], $cont_invent);
		} else {
			$cont = $cont_invent;
		}
*/
	
		foreach ($cont as $key => $row) {

			$new = false;
			$count++;
			echo "<li style='margin: 0; padding: 0;'><span style='min-width: 300px; display: inline-block;'>";
			
			switch ($lessBranchs) {
				case 0:
					print_image ("images/tree/branch.png", false, array ("style" => 'vertical-align: middle;'));
					print_image ("images/tree/branch.png", false, array ("style" => 'vertical-align: middle;'));
					break;
				case 1:
					print_image ("images/tree/no_branch.png", false, array ("style" => 'vertical-align: middle;'));
					print_image ("images/tree/branch.png", false, array ("style" => 'vertical-align: middle;'));
					break;
				case 2:
					print_image ("images/tree/branch.png", false, array ("style" => 'vertical-align: middle;'));
					print_image ("images/tree/no_branch.png", false, array ("style" => 'vertical-align: middle;'));
					break;
				case 3:
					print_image ("images/tree/no_branch.png", false, array ("style" => 'vertical-align: middle;'));
					print_image ("images/tree/no_branch.png", false, array ("style" => 'vertical-align: middle;'));
					break;
			}
			
			if ($countRows != $count)
				print_image ("images/tree/leaf.png", false, array ("style" => 'vertical-align: middle;', "id" => "tree_image" . $id_item. "_child2_" . $row["id"], "pos_tree" => "1"));
			else
				print_image ("images/tree/last_leaf.png", false, array ("style" => 'vertical-align: middle;', "id" => "tree_image" . $id_item. "_child2_" . $row["id"], "pos_tree" => "2"));
			
/*
			echo "<a onfocus='JavaScript: this.blur()'
						href='javascript: loadTable(\"child2\",\"" . $row["id"] . "\", " . $less . ", \"" . $id_item . "\", \"" . $id_father . "\",  \"" . $sql_search . "\")'>";
*/
						
			echo $row["name"]. '&nbsp;&nbsp;<a href="index.php?sec=inventory&sec2=operation/inventories/inventory_detail&id='.$row['id'].'">'.print_image ("images/edit.png", true, array ("style" => 'vertical-align: middle;')).'</a>';
			
			echo "</li>";
			
			echo "</a>";
		}

		echo "</ul>";
		
	}
	
return;
}


$search = get_parameter('search', 0);
$sql_search = '';

$search_free = get_parameter ('search_free', '');
$id_object_type = get_parameter ('id_object_type_search', 0);
$owner = get_parameter('owner', '');
$id_manufacturer = get_parameter ('id_manufacturer', 0);
$id_contract = get_parameter ('id_contract', 0);
$fields_selected = (array)get_parameter('object_fields_search');
$mode = get_parameter('mode', 'list');
$last_update = (bool) get_parameter ('last_update');

if (isset($_POST['listview']))
	$mode = 'list';
if (isset($_POST['treeview']))
	$mode = 'tree';


echo '<form id="tree_search" method="post" action="index.php?sec=inventory&sec2=operation/inventories/inventory_search">';
	$table_search->class = 'databox';
	$table_search->width = '98%';
	$table_search->data = array ();
	
	$table_search->data[0][0] = print_input_text ('search_free', $search_free, '', 40, 128, true, __('Search'));
	
	$objects_type = get_object_types ();
	$table_search->data[0][1] = print_label (__('Object type'), '','',true);
	$table_search->data[0][1] .= print_select($objects_type, 'id_object_type_search', $id_object_type, 'show_type_fields();', 'Select', '', true, 0, true, false, false, 'width: 200px;');
	
	$table_search->data[0][2] = print_label (__('Object fields'), '','',true);
	
	$object_fields = array();
	
	if ($fields_selected[0] != '') {
		foreach ($fields_selected as $selected) {
			$label_field = get_db_value('label', 'tobject_type_field', 'id', $selected);
			$object_fields[$selected] = $label_field;
		}
	}

	$table_search->data[0][2] .= print_select($object_fields, 'object_fields_search[]', '', '', 'Select', '', true, 4, true, false, false, 'width: 200px;');
	
	$params_assigned['input_id'] = 'text-owner';
	$params_assigned['input_name'] = 'owner';
	$params_assigned['input_value'] = $owner;
	$params_assigned['title'] = 'Owner';
	$params_assigned['return'] = true;

	$table_search->data[1][0] = user_print_autocomplete_input($params_assigned);
	
	$contracts = get_contracts ();
	$manufacturers = get_manufacturers ();
	
	$table_search->data[1][1] = print_select ($contracts, 'id_contract', $id_contract,
		'', __('None'), 0, true, false, false, __('Contract'), '', 'width: 200px;');

	$table_search->data[1][2] = print_select ($manufacturers, 'id_manufacturer',
		$id_manufacturer, '', __('None'), 0, true, false, false, __('Manufacturer'), '','width: 200px;');
	
	$table_search->data[1][3] = print_checkbox_extended ('last_update', 1, $last_update,
	false, '', '', true, __('Last updated'));
	
	print_table($table_search);
	
	echo '<div style="width:'.$table_search->width.'" class="action-buttons button">';
		print_input_hidden ('search', 1);
		print_input_hidden ('mode', $mode);
		print_submit_button (__('Search'), 'search', false, 'class="sub next"');
		
		if ($mode == 'tree') {
			print_submit_button (__('List view'), 'listview', false, 'class="sub next"');
		} else {
			print_submit_button (__('Tree view'), 'treeview', false, 'class="sub next"');
		}
		
	echo '</div>';
echo '</form>';

if ($search) {
	$sql_search = '';
	$params = '&search=1';
	
	if ($id_object_type != 0) { //búsqueda de texto libre en nombre, descripción de inventario y en contenido de campo personalizado
		$sql_search .= " AND tinventory.id_object_type = $id_object_type";
		
		$params .= "&id_object_type_search=$id_object_type";
		
		if (!empty($object_fields)) {
			$j = 0;
			foreach ($object_fields as $k=>$f) {
				if ($j == 0) 
					$string_fields = "$k";
				else
					$string_fields .= ",$k";
				$j++;
			}


			$sql_search .= " AND `tobject_field_data`.`id_inventory`=`tinventory`.`id`
							AND `tobject_field_data`.`id_object_type_field` IN ($string_fields) ";
							
			$params .= "&object_fields_search=$string_fields";
						
			if ($search_free != '') {
				/*
				$sql_search .= "AND (tobject_field_data.`data`LIKE '%$search_free%' OR tinventory.name LIKE '%$search_free%'
					OR tinventory.description LIKE '%$search_free%')";
				 */
				$sql_search .= "AND tobject_field_data.`data` LIKE '%$search_free%'";
				
				$params .= "&search_free=$search_free";
			}			
		}
	} else { //búsqueda solo en nombre y descripción de inventario
		if ($search_free != '') {
			$sql_search .= " AND (tinventory.name LIKE '%$search_free%' OR tinventory.description LIKE '%$search_free%')";
			
			$params .= "&search_free=$search_free";
		}
	}
	
	if ($owner != '') {
		$sql_search .= " AND tinventory.owner = '$owner'";
		$params .= "&owner=$owner";
	}
	if ($id_manufacturer != 0) {
		$sql_search .= " AND tinventory.id_manufacturer = $id_manufacturer";
		$params .= "&id_manufacturer=$id_manufacturer";
	}
	if ($id_contract != 0) {
		$sql_search .= " AND tinventory.id_contract = $id_contract";
		$params .= "&id_contract=$id_contract";
	}
	
} 

$page = (int)get_parameter('page', 1);
switch ($mode) {
	case 'tree':
		inventories_print_tree($sql_search);
		break;
	case 'list':
		inventories_show_list($sql_search, $params, $last_update);
		break;
	default:
		inventories_show_list($sql_search, $params, $last_update);
		break;
}
	

echo '<div id="sql_search_hidden" style="display:none;">';
	print_input_text('sql_search_hidden', $sql_search);
echo '</div>';

?>

<script type="text/javascript" src="include/js/jquery.ui.autocomplete.js"></script>
<script type="text/javascript" src="include/js/integria_inventory.js"></script>

<script type="text/javascript">

$(document).ready (function () {
	
	var idUser = "<?php echo $config['id_user'] ?>";
	
	bindAutocomplete ("#text-owner", idUser);

	// Form validation
	trim_element_on_submit('#text-search_free');

});

</script>
