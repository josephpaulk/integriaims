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

if (defined ('AJAX')) {
	
	global $config;
	
	$print_subtree = get_parameter('print_subtree', 0);
	
	$id_item = get_parameter ('id_item');
	$lessBranchs = get_parameter('less_branchs');
	$type = get_parameter('type');
	$id_father = get_parameter('id_father');
	$sql_search = get_parameter('sql_search', '');
	$id_object_type = get_parameter("id_object_type_search");
	$end = get_parameter("end");
	$last_update = get_parameter("last_update");

	$ref_tree = (string)get_parameter("ref_tree");

	if ($type == 'object_types') {
		if (empty($id_item))
			$id_item = 0;

		$sql = base64_decode($sql_search);
		
		// The id_object_type can be NULL !!
		if (empty($id_item))
			$sql .= " AND (tinventory.id_object_type IS NULL OR tinventory.id_object_type = $id_item)";
		else
			$sql .= " AND tinventory.id_object_type = $id_item";
		
		if ($last_update == 1) {
			$sql .= " ORDER BY tinventory.last_update DESC";
		} else {
			$sql .= " ORDER BY tinventory.name ASC";
		}

		//If there is a father the just print the object (we only filter in first level)
		if ($id_father) {
			$sql = "SELECT * FROM tinventory WHERE id_parent = $id_father AND id_object_type = $id_item";
			if ($last_update == 1) {
				$sql .= " ORDER BY last_update DESC";
			} else {
				$sql .= " ORDER BY name ASC";
			}			
		}
		
		$cont_aux = get_db_all_rows_sql($sql);

		$count_blanks = strlen($ref_tree);		

		$cont = enterprise_hook ('inventory_get_user_inventories', array ($config['id_user'], $cont_aux));
		if ($cont === ENTERPRISE_NOT_HOOK) {
			$cont = $cont_aux;
		}

		if (!$cont) {
			$cont = array();
		}
		
		$countRows = count($cont);

		//Empty Branch
		if ($countRows == 0) {

			echo "<ul style='margin: 0; padding: 0;'>\n";
			echo "<li style='margin: 0; padding: 0;'>";
			echo "<i>" . __("Empty") . "</i>";
			echo "</li>";
			echo "</ul>";
			
			return;
		}
		
		//Branch with items
		$new = true;
		$count = 0;
		echo "<ul style='margin: 0; padding: 0;'>\n";
		
		$cont_size = count($cont);

		$end = 1;

		foreach ($cont as $row) {

			if ($row != end($cont)) {
				$end = 0;
			}

			$aux_ref_tree = $ref_tree."".$count;
			
			$new = false;
			$count++;

			$less = $lessBranchs;
			if ($count != $countRows)
				$img = print_image ("images/tree/closed.png", true, array ("style" => 'vertical-align: middle;', "id" => "tree_image" . $aux_ref_tree. "_inventory_" . $row["id"], "pos_tree" => "2"));
			else {
				$less = $less + 2; // $less = $less or 0b10
				$img = print_image ("images/tree/last_closed.png", true, array ("style" => 'vertical-align: middle;', "id" => "tree_image" . $aux_ref_tree. "_inventory_" . $row["id"], "pos_tree" => "3"));
			}
			echo "<li style='margin: 0; padding: 0;'>";

	
			
			echo "<a onfocus='JavaScript: this.blur()'
						href='javascript: loadTable(\"inventory\",\"" . $row["id"] . "\", " . $less . ", \"" . $id_item . "\",  \"" . $sql_search . "\", \"". $aux_ref_tree ."\", \"". $end ."\")'>";
			
			echo $img;
			
			echo $row["name"]. '&nbsp;&nbsp;<a href="index.php?sec=inventory&sec2=operation/inventories/inventory_detail&id='.$row['id'].'">'.print_image ("images/application_edit.png", true, array ("style" => 'vertical-align: middle;')).'</a>';
			
			echo "</a>";

			if ($end) {
				echo "<div hiddenDiv='1' loadDiv='0' class='tree_view' id='tree_div" . $aux_ref_tree . "_inventory_" . $row["id"] . "'></div>";
			} else {
				echo "<div hiddenDiv='1' loadDiv='0' class='tree_view tree_view_branch' id='tree_div" . $aux_ref_tree . "_inventory_" . $row["id"] . "'></div>";
			}
			echo "</li>";
			
		} 
	}
	echo "</ul>\n";
			
	//TERCER NIVEL DEL ARBOL.
	if ($type == 'inventory') {
	
		$sql = "SELECT id FROM tinventory WHERE `id_parent`=$id_item";

		$cont_invent = get_db_all_rows_sql($sql);
		
		$cont = enterprise_hook ('inventory_get_user_inventories', array ($config['id_user'], $cont_invent));
		
		if ($cont === ENTERPRISE_NOT_HOOK) {
			$cont = $cont_invent;
		}

		if (!$cont) {
			$cont = array();
		}

		$countRows = count($cont);

		$count_blanks = strlen($ref_tree);
	
		if ($countRows == false)
			$countRows = 0;
	
		if ($countRows == 0) {
			echo "<ul style='margin: 0; padding: 0;'>\n";
			echo "<li style='margin: 0; padding: 0;'>";
			echo "<i>" . __("Empty") . "</i>";
			echo "</li>";
			echo "</ul>";
			return;
		}
	
		//Branch with items
		$new = true;
		$count = 0;


		$clause = "";
		if ($cont) {
			
			foreach ($cont as $c) {
				$clause .= $c["id"].",";
			}

			$clause = substr($clause,0,-1);

			$clause = " AND tinventory.id IN ($clause)";
		}

		$sql = "SELECT DISTINCT(tinventory.id_object_type), tobject_type.* FROM tinventory, tobject_type 
				WHERE tinventory.id_object_type = tobject_type.id".$clause. " ORDER BY tobject_type.name ASC";
		
		$cont = get_db_all_rows_sql($sql);

		// Add no object type
		$last_key = count($cont);
		$cont[$last_key]['name'] = __('No object type');
		$cont[$last_key]['icon'] ="box.png";
		$cont[$last_key]['id'] = 0;

		echo "<ul style='margin: 0; padding: 0;'>\n";
		
		$cont_size = count($cont);

		$end = 1;
		foreach ($cont as $row) {

			if ($row != end($cont)) {
				$end = 0;
			}

			$aux_ref_tree = $ref_tree."".$count;
			
			$new = false;
			$count++;

			echo "<li style='margin: 0; padding: 0;'>";

			$less = $lessBranchs;
			if ($count != $countRows) {
				$img = print_image ("images/tree/closed.png", true, array ("style" => 'vertical-align: middle;', "id" => "tree_image" . $aux_ref_tree. "_object_types_" . $row["id"], "pos_tree" => "2"));
			} else {
				$less = $less + 2; // $less = $less or 0b10
				$img = print_image ("images/tree/last_closed.png", true, array ("style" => 'vertical-align: middle;', "id" => "tree_image" . $aux_ref_tree. "_object_types_" . $row["id"], "pos_tree" => "3"));
			}
			
			echo "<a onfocus='JavaScript: this.blur()'
						href='javascript: loadTable(\"object_types\",\"" . $row["id"] . "\", " . $less . ", \"" . $id_item . "\",  \"" . $sql_search . "\", \"". $aux_ref_tree ."\", \"". $end ."\")'>";

			echo $img;
			echo "<img src='images/objects/".$row["icon"]."' style='vertical-align: middle'>";
			echo '&nbsp;'.$row["name"];
			echo "</a>";
			
			if ($end) {
				echo "<div hiddenDiv='1' loadDiv='0' class='tree_view' id='tree_div" . $aux_ref_tree . "_object_types_" . $row["id"] . "'></div>";
			} else {
				echo "<div hiddenDiv='1' loadDiv='0' class='tree_view tree_view_branch' id='tree_div" . $aux_ref_tree . "_object_types_" . $row["id"] . "'></div>";
			}
			echo "</li>";
		}
		echo "</ul>\n";

	}
	
return;
}

$sql_search = '';
$offset = (int)get_parameter('offset', 0);

//for params
$params = (string)get_parameter('params', '');

if ($params != ''){	
	$params = base64_decode($params);
	$params = json_decode($params, true);
	
	if($params['search'] != ''){
		$search = $params['search'];
	}
	if($params['search_free'] != ''){
		$search_free = $params['search_free'];
	}
	if($params['owner'] != ''){
		$owner = $params['owner'];
	}
	if($params['id_manufacturer'] != ''){
		$id_manufacturer = $params['id_manufacturer'];
	}
	if($params['id_contract'] != ''){
		$id_contract = $params['id_contract'];
	}
	if($params['last_update'] != ''){
		$last_update = $params['last_update'];
	}
	if($params['offset'] != ''){
		$offset = $params['offset'];
	}
	if($params['inventory_status'] != ''){
		$inventory_status = $params['inventory_status'];
	}
	if($params['id_company'] != ''){
		$id_company = $params['id_company'];
	}
	if($params['associated_user'] != ''){
		$associated_user = $params['associated_user'];
	}
	if($params['mode'] != ''){
		$mode = $params['mode'];
	}
	if($params['object_fields'] != ''){
		$object_fields = $params['object_fields'];
	}
	if($params['sort_mode'] != ''){
		$sort_mode = $params['sort_mode'];
	}
	if($params['sort_field_num'] != ''){
		$sort_field_num = $params['sort_field_num'];
	}
	if($params['id_object_type_search'] != ''){
		$id_object_type = $params['id_object_type_search'];
	}
	if($params['parent_name'] != ''){
		$parent_name = $params['parent_name'];
	}		
} else {
	$inventory_status = (string)get_parameter('inventory_status', '0');
	$id_company = (int)get_parameter('id_company', 0);
	$associated_user = (string)get_parameter('associated_user', "");
	$mode = (string)get_parameter('mode', "list");
	$object_fields = (string)get_parameter('object_fields', '');
	$id_object_type = (int)get_parameter('id_object_type_search', 1);
	$search = (int)get_parameter('search', 0);
	$search_free = (string)get_parameter ('search_free', '');
	$owner = (string)get_parameter('owner', '');
	$id_manufacturer = get_parameter ('id_manufacturer', 0);
	$id_contract = (int)get_parameter ('id_contract', 0);
	$last_update = get_parameter ('last_update');
	$parent_name = get_parameter ('parent_name', 'None');

	//sort table
	$sort_mode = (string)get_parameter('sort_mode', 'asc');
	$sort_field_num = (int)get_parameter('sort_field', 1);
}

if($object_fields != ""){
	$object_fields_string = str_replace ('&quot;' ,  '"' , $object_fields);
}

/*
 * Sort field is: (DYNAMIC!!!!!!!) in select all view:
 * Id	Name	Owner	Parent object	Object type	Manufacturer	Contract	Status	Receipt date	
 *  0	1		2		3				4			5				6			7		8				
 */

switch ($sort_field_num) {
	case 0: $sort_field = "id";break;
	case 1: $sort_field = "name";break;
	case 2: $sort_field = "owner";break;
	case 7: $sort_field = "status";break;
	case 8: $sort_field = "receipt_date";break;
	default:
		$sort_field = "name";
		break;
}

$sql_search = 'SELECT tinventory.* FROM tinventory WHERE 1=1';
$sql_search_count = 'SELECT COUNT(tinventory.id) FROM tinventory WHERE 1=1';
$sql_search_obj_type = 'SELECT DISTINCT(tobject_type.id), tobject_type.* FROM `tinventory`, `tobject_type` WHERE tinventory.id_object_type = tobject_type.id order by name';

if ($search) {
	
	$params = array();
	$params['search'] = 1;
	$params['object_fields'] = $object_fields;
	//$params['search_free'] = $search_free;
	//If object type and fields were selected an there is a free search string.
	//Then we search for this text in the object field data.
	if ($id_object_type != 0 && !empty($object_fields) && $search_free != '') {
		$string_fields_object_types == '';
		$string_fields_types == '';
		$object_fields_array = json_decode($object_fields_string, true);
		
		foreach ($object_fields_array as $k=>$f) {
			if (is_numeric($k)){
				if($string_fields_object_types == ''){
					$string_fields_object_types = "$k";
				} else {
					$string_fields_object_types .= ",$k ";
				}
			}
		}		
		$params['search_free']= $search_free;
		//Compound sub select

		if ($string_fields_object_types){
			$sql_search = "SELECT tinventory.* FROM tinventory, tobject_type, tobject_field_data WHERE 
							`tinventory`.`id_object_type` = `tobject_type`.`id` AND `tobject_field_data`.`id_inventory`=`tinventory`.`id`";
			$sql_search .= " AND ((`tobject_field_data`.`id_object_type_field` IN ($string_fields_object_types) ";		
			$sql_search .= "AND tobject_field_data.`data` LIKE '%$search_free%')";
			$sql_search .= "OR (tinventory.name LIKE '%$search_free%' OR tinventory.description LIKE '%$search_free%' OR tinventory.id LIKE '%$search_free%' OR tinventory.status LIKE '%$search_free%'))";

			$sql_search_count = "SELECT COUNT(`tinventory`.`id`) FROM tinventory, tobject_type, tobject_field_data WHERE 
								`tinventory`.`id_object_type` = `tobject_type`.`id` AND
								 `tobject_field_data`.`id_inventory`=`tinventory`.`id`";
			$sql_search_count .= " AND ((`tobject_field_data`.`id_object_type_field` IN ($string_fields_object_types) ";		
			$sql_search_count .= "AND tobject_field_data.`data` LIKE '%$search_free%')";
			$sql_search_count .= "OR (tinventory.name LIKE '%$search_free%' OR tinventory.description LIKE '%$search_free%' OR tinventory.id LIKE '%$search_free%' OR tinventory.status LIKE '%$search_free%'))";

			$sql_search_obj_type = "SELECT DISTINCT(`tobject_type`.`id`), tobject_type.* FROM `tinventory`, `tobject_type`, `tobject_field_data` WHERE
									`tinventory.id_object_type` = `tobject_type`.`id` AND `tobject_field_data`.`id_inventory`=`tinventory`.`id`";
			$sql_search_obj_type .= " AND ((`tobject_field_data`.`id_object_type_field` IN ($string_fields_object_types) ";
			$sql_search_obj_type .= "AND tobject_field_data.`data` LIKE '%$search_free%')";
			$sql_search_obj_type .= "OR (tinventory.name LIKE '%$search_free%' OR tinventory.description LIKE '%$search_free%' OR tinventory.id LIKE '%$search_free%' OR tinventory.status LIKE '%$search_free%'))";	
		} else {
			$sql_search .= " AND (tinventory.name LIKE '%$search_free%' OR tinventory.description LIKE '%$search_free%' OR tinventory.id LIKE '%$search_free%' OR tinventory.status LIKE '%$search_free%')";
			$sql_search_count .= " AND (tinventory.name LIKE '%$search_free%' OR tinventory.description LIKE '%$search_free%' OR tinventory.id LIKE '%$search_free%' OR tinventory.status LIKE '%$search_free%')";
		}

	} else { //búsqueda solo en nombre y descripción de inventario
		if ($search_free != '') {
			$sql_search .= " AND (tinventory.name LIKE '%$search_free%' OR tinventory.description LIKE '%$search_free%' OR tinventory.id LIKE '%$search_free%' OR tinventory.status LIKE '%$search_free%')";
			$sql_search_count .= " AND (tinventory.name LIKE '%$search_free%' OR tinventory.description LIKE '%$search_free%' OR tinventory.id LIKE '%$search_free%' OR tinventory.status LIKE '%$search_free%')";

			$params['search_free'] = $search_free;
		}
	}

	if ($id_object_type) {
		$sql_search .= " AND tinventory.id_object_type = $id_object_type";
		$sql_search_count .= " AND tinventory.id_object_type = $id_object_type";
		$params['id_object_type_search'] = $id_object_type;
	}

	if ($owner != '') {
		$sql_search .= " AND tinventory.owner = '$owner'";
		$sql_search_count .= " AND tinventory.owner = '$owner'";
		$params['owner'] = $owner;
	}
	if ($id_manufacturer != 0) {
		$sql_search .= " AND tinventory.id_manufacturer = $id_manufacturer";
		$sql_search_count .= " AND tinventory.id_manufacturer = $id_manufacturer";
		$params['id_manufacturer'] = $id_manufacturer;
	}
	
	if ($id_contract != 0) {
		$sql_search .= " AND tinventory.id_contract = $id_contract";
		$sql_search_count .= " AND tinventory.id_contract = $id_contract";
		$params['id_contract'] = $id_contract;
	}
	if ($inventory_status != '0') {
		$sql_search .= " AND tinventory.status = '$inventory_status'";
		$sql_search_count .= " AND tinventory.status = '$inventory_status'";
		$params['inventory_status'] = $inventory_status;
	}
	if ($id_company != 0) {
		$sql_search .= " AND tinventory.id IN (SELECT id_inventory FROM tinventory_acl WHERE `type`='company' AND id_reference='$id_company')";
		$sql_search_count .= " AND tinventory.id IN (SELECT id_inventory FROM tinventory_acl WHERE `type`='company' AND id_reference='$id_company')";
		$params['id_company'] = $id_company;
	}
	if ($associated_user != '') {
		$sql_search .= " AND tinventory.id IN (SELECT id_inventory FROM tinventory_acl WHERE `type`='user' AND id_reference='$associated_user')";
		$sql_search_count .= " AND tinventory.id IN (SELECT id_inventory FROM tinventory_acl WHERE `type`='user' AND id_reference='$associated_user')";
		$params['associated_user'] = $associated_user;
	}
	if ($parent_name != 'None') {
		$sql_parent_name = "select id from tinventory where name ='". $parent_name."';";
		$id_parent_name = get_db_sql($sql_parent_name);

		$sql_search .= " AND tinventory.id_parent =" . $id_parent_name;
		$sql_search_count .=  " AND tinventory.id_parent =" . $id_parent_name;
		$params['parent_name'] = $parent_name;

	}
	if ($mode == 'list'){
		$sql_search .= " group by tinventory.id order by $sort_field $sort_mode ";
		$params['sort_field_num'] = $sort_field_num;
		$params['sort_mode'] = $sort_mode;
	}
}


if (!$pure) {
	echo '<div>';
	$form2 = '<div class = "divform">';
		$form2 .= '<form>';
			$form2 .= '<table class="search-table"><tr><td>';
				$objects_type = get_object_types ();
				$objects_type[0] = __('All');
				$form2 .= print_label (__('Object type'), '','',true);
				$form2 .= print_select($objects_type, 'id_object_type_search_check', $id_object_type, 'change_object_type();', '', '', true, 4, false, false, false, '');
			$form2 .= '</td></tr></table>';
		$form2 .= '</form>';
	$form2 .= '</div>';
	
	print_container_div("inventory_type_object",__("Select type object"),$form2, 'open', false, false);

	$form3 = '<div class = "divform">';
		$form3 .= '<form id="form_object_field">';
			$form3 .= '<table class="search-table"><tr><td>';
				$form3 .= print_label (__('Object fields'), '','',true);
				$form3 .= '<div id = "object_fields_search_check" class="div_multiselect" ></div>';
			$form3 .= '</td></tr></table>';
		$form3 .= '</form>';
	$form3 .= '</div>';

	print_container_div("inventory_column",__("Column editor"),$form3, 'open', false, false);

	$form = '<form id="tree_search" method="post" onsubmit="tree_search_submit();return false">'; //index.php?sec=inventory&sec2=operation/inventories/inventory">';
		$form .= "<div class='divresult_inventory'>";
		$table_search = new StdClass();
		$table_search->class = 'search-table-button';
		$table_search->width = '100%';
		$table_search->data = array ();
		$table_search->size[0] = "40%";
		$table_search->size[1] = "35%";
		
		//find
		$table_search->data[0][0] = print_input_text ('search_free', $search_free, '', 25, 128, true, __('Search'));
		
		//associate company
		$companies = get_companies();
		$companies[0] = __("All");
		$table_search->data[0][1] = print_select ($companies, 'id_company', $id_company,'', '', 0, true, false, false, __('Associated company'), '', 'width: 218px;');

		//owner
		$params_assigned['input_id'] = 'text-owner';
		$params_assigned['input_name'] = 'owner';
		$params_assigned['input_value'] = $owner;
		$params_assigned['title'] = 'Owner';
		$params_assigned['return'] = true;

		$table_search->data[0][2] = user_print_autocomplete_input($params_assigned);
		
		//Contract
		$contracts = get_contracts ();
		$table_search->data[1][0] = print_select ($contracts, 'id_contract', $id_contract,
			'', __('None'), 0, true, false, false, __('Contract'), '', '');

		//Manufacturer
		$manufacturers = get_manufacturers ();
		$table_search->data[1][1] = print_select ($manufacturers, 'id_manufacturer',
		$id_manufacturer, '', __('None'), 0, true, false, false, __('Manufacturer'), '','');

		//User Assoc
		$params_associated['input_id'] = 'text-associated_user';
		$params_associated['input_name'] = 'associated_user';
		$params_associated['input_value'] = $associated_user;
		$params_associated['title'] = __('Associated user');
		$params_associated['return'] = true;
	
		$table_search->data[1][2] = user_print_autocomplete_input($params_associated);
		
		//status
		$all_inventory_status = inventories_get_inventory_status ();
		array_unshift($all_inventory_status, __("All"));
		
		$table_search->data[2][0] = print_select ($all_inventory_status, 'inventory_status', $inventory_status, '', '', '', true, false, false, __('Status'));

		//Parent name
		$table_search->data[2][1] =  print_input_text_extended ("parent_name", $parent_name, "text-parent_name", '', 20, 0, false, "show_inventory_search('','','','','','','','','','', '', '')", "class='inventory_obj_search' style='width:210px;'", true, false,  __('Parent object'));
		$table_search->data[2][1] .= print_image("images/cross.png", true, array("onclick" => "cleanParentInventory()", "style" => "cursor: pointer"));	
		$table_search->data[2][1] .= print_input_hidden ('id_parent', $id_parent, true);

		//check
		$table_search->data[2][2] = print_checkbox_extended ('last_update', 1, $last_update,
		false, '', '', true, __('Last updated'));

		//hidden select to perform the search
		$objects_type = get_object_types ();
		$table_search->data[2][2] .= print_select($objects_type, 'id_object_type_search', $id_object_type, '', 'Select', '', true, 0, true, false, false, '');

		$table_search->data[3][0] = print_input_hidden ('sort_field', $sort_field_num, true, false, 'sort_field');
		$table_search->data[3][1] = print_input_hidden	('sort_mode', $sort_mode, true, false, 'sort_mode');
		$table_search->data[3][1] .= print_input_hidden	('object_fields', $object_fields, true, false, 'object_fields');
		$table_search->data[3][1] .= print_input_hidden	('offset', $offset, true, false, 'offset');
		
		//button
		//$table_search->colspan[3][3] = 3;
		$table_search->data[3][2] = print_input_hidden ('search', 1, true);
		$table_search->data[3][2] .= print_input_hidden ('mode', $mode, true, false, 'mode');
		$table_search->data[3][2] .= print_submit_button (__('Search'), 'search', false, 'class="sub search"', true);

		$form .= print_table($table_search, true);
		$form .= '</div>';
	$form .= '</form>';

	print_container_div("inventory_form",__("Inventory form search"),$form, 'open', false, false);
	echo '</div>';
}

$write_permission = enterprise_hook ('inventory_check_acl', array ($config['id_user'], $id, true));	
$page = (int)get_parameter('page', 1);

switch ($mode) {
	case 'tree':
		echo '<div class = "inventory_tree_table" id = "inventory_tree_table">';
			inventories_print_tree($sql_search, $sql_search_obj_type, $last_update);
		echo '</div>';
		break;
	case 'list':
		echo '<div style="display: none;" id="tmp_data"></div>';
		echo '<div class = "inventory_list_table" id = "inventory_list_table">';
			echo '<div id= "inventory_only_table">';
				inventories_show_list($sql_search, $sql_search_count, $params, $last_update);
			echo '</div>';
			if ($write_permission) {	
				echo '<div class="button-form">';
				echo print_button(__('Delete All'), '', false, 'javascript: delete_massive_inventory()', 'class="sub"', true);
				echo '</div>';
			}
		echo '</div>';
		break;
	default:
		echo '<div style="display: none;" id="tmp_data"></div>';
		echo '<div class = "inventory_list_table" id = "invetory_list_table">';
			echo '<div id= "inventory_only_table">';
				inventories_show_list($sql_search, $sql_search_count, $params, $last_update);
			echo '</div>';
			if ($write_permission) {	
				echo '<div class="button-form">';
				echo print_button(__('Delete All'), '', false, 'javascript: delete_massive_inventory()', 'class="sub"', true);
				echo '</div>';
			}
		echo '</div>';
		
}

echo "<div class= 'dialog ui-dialog-content' id='inventory_search_window'></div>";

?>

<script type="text/javascript">

$(document).ready (function () {

	var idUser = "<?php echo $config['id_user']; ?>";
	var object_types = "<?php echo $object_fields; ?>";
	//object_types = jQuery.parseJSON(object_types.replace(/&quot;/g, '"'));
	var pure = "<?php echo $pure; ?>";
	
	bindAutocomplete ("#text-owner", idUser);
	bindAutocomplete ("#text-associated_user", idUser);

	// Form validation
	trim_element_on_submit('#text-search_free');
	
	if ($("#tree_search").length > 0) {
		validate_user ("#tree_search", "#text-owner", "<?php echo __('Invalid user')?>");
	}

	//JS for massive operations
	$("#checkbox-inventorycb-all").change(function() {
		$(".cb_inventory").prop('checked', $("#checkbox-inventorycb-all").prop('checked'));
	});

	$(".cb_inventory").click(function(event) {
		event.stopPropagation();
	});

	////cambia el offset a 0
	$('#id_object_type_search_check').change(function(){
		$("#hidden-offset").val(0);
	});
	
	tree_search_submit();
});

</script>
