<?php

// Integria IMS - http://integriaims.com
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

include_once('include/functions_user.php');

$get_external_data = get_parameter('get_external_data', 0);
$get_inventory_search = get_parameter('get_inventory_search', 0);
$get_company_associated = get_parameter('get_company_associated', 0);
$get_user_associated = get_parameter('get_user_associated', 0);
$get_inventory_name = (bool) get_parameter('get_inventory_name', 0);
$select_fields = get_parameter('select_fields', 0);
$change_table = get_parameter('change_table', 0);
$printTable = get_parameter('printTable', 0);
$printTableMoreInfo = get_parameter('printTableMoreInfo', 0);
$get_item_info = (bool) get_parameter('get_item_info', 0);
	
if ($select_fields) {
	$id_object_type = get_parameter('id_object_type');
	
	$fields = get_db_all_rows_filter('tobject_type_field', array('id_object_type'=>$id_object_type, 'show_list'=>1), 'label, id');
	if ($fields === false) {
		$fields = array();
	}

	$object_fields = array();

		$object_fields[__('Id')] = 'id';
		$object_fields[__('Name')] = 'name';
		$object_fields[__('Owner')] = 'owner';
		$object_fields[__("Parent object")] = 'id_parent';
		$object_fields[__('Object type')] = 'id_object_type';
		$object_fields[__('Manufacturer')] = 'id_manufacturer';
		$object_fields[__('Contract')] = 'id_contract';
		$object_fields[__('Status')] = 'status';
		$object_fields[__('Receipt date')] = 'receipt_date';
	
	foreach ($fields as $key => $field) {
		$object_fields[$field['label']] = $field['id'];
	}
	
	echo json_encode($object_fields);
	return;
}

if ($change_table) {
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
	if($params['sort_field'] != ''){
		$sort_field = $params['sort_field'];
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
	$id_object_type = (int)get_parameter('id_object_type_search');
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
		//$string_fields_types == '';
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
	if($mode == 'list'){
		inventories_show_list($sql_search, $sql_search_count, $params, $last_update);
	} else {
		inventories_print_tree ($sql_search, $sql_search_obj_type, $last_update);
	}
	return;
}

if ($printTable) {
	$id_item = get_parameter('id_item');
	$type = get_parameter('type');
	$id_father = get_parameter('id_father');

	inventories_printTable($id_item, $type, $id_father);
	return;
}

if ($get_item_info) {
	$id_item = get_parameter('id_item');
	$id_father = get_parameter('id_father');

	echo json_encode(inventories_get_info($id_item, $id_father));
	return;
}

if ($get_inventory_name) {
	$id_inventory = get_parameter('id_inventory');
	$name = get_db_value('name', 'tinventory', 'id', $id_inventory);

	echo safe_output($name);
	return;
}

if ($get_external_data) {
	$table_name = get_parameter('table_name');
	$id_table = (string) get_parameter('id_table');
	$element_name = get_parameter('element_name');
	$id_object_type_field = get_parameter('id_object_type_field');
	$id_parent_value = get_parameter('id_parent_value', 0);
	$id_parent_table = get_parameter('id_parent_table', "");
	$external_label = get_parameter('external_label', "");

	//We use MYSQL_QUERY becase we need this to fail silently to not show
	//SQL errors on screen
	$exists = mysql_query("SELECT * FROM ".$table_name." LIMIT 1");

	if (!$exists) {
		echo "<h3 class='error'>".__("External table is not present")."</h3>";
		return;
	}
	
	$sql_ext = "SHOW COLUMNS FROM ".$table_name;
	$desc_ext = get_db_all_rows_sql($sql_ext);

	$parent_reference_field = get_db_value_sql('SELECT parent_reference_field FROM tobject_type_field WHERE id='.$id_object_type_field);
	
	$fields = array();
	foreach ($desc_ext as $key=>$ext) {
		if ($parent_reference_field == $ext['Field']) {
			continue;
		}
		$fields[$ext['Field']] = $ext['Field'];
	}

	if ($id_parent_value) {
		$table_name_parent = get_db_value_sql("SELECT parent_table_name FROM tobject_type_field WHERE id=".$id_object_type_field);
		$id_reference_parent = get_db_value_sql("SELECT parent_reference_field FROM tobject_type_field WHERE id=".$id_object_type_field);
		$external_data = get_db_all_rows_sql("SELECT * FROM $table_name WHERE $id_reference_parent=".$id_parent_value);
	} else {
		$external_data = get_db_all_rows_in_table($table_name);
	}

	if ($external_data !== false) {
	
		$table->class = 'listing';
		$table->width = '98%';
		$table->data = array ();
		$table->head = array ();
		
		$keys = array_keys($fields);
	
		$i = 0;
		foreach ($keys as $k=>$head) {
			$table->head[$i] =$head;
			if ($head == $id_table)
				$pos_id = $i+1;
			$i++;
		}
	
		foreach ($external_data as $key => $ext_data) {
			$j = 0;
			$data_name = $ext_data[$external_label];

			foreach ($ext_data as $k => $dat) {

				if ($k == $id_table) {
					$val_id = $dat;
				}
				if (array_key_exists($k, $fields)) {
					//~ $data[$j] = "<a href='javascript: enviar(" . $val_id . ", " . $element_name . ", " . $id_object_type_field . ")'>".$dat."</a>";	
					$data[$j] = "<a href='javascript: enviar(" . $val_id . ", " . $element_name . ", " . $id_object_type_field . ", \"" . $data_name . "\")'>".$dat."</a>";	
				}
				$j++;
			}
			array_push ($table->data, $data);
		}

		print_table ($table);
	} else {
		echo "<h4>".__("No data to show")."</h4>";
	}
	return;
}

//Search formulary modal
if ($get_inventory_search) {
	$search = get_parameter('search', 0);
	$search_free = get_parameter ('search_free', '');
	$id_object_type_search = get_parameter ('id_object_type_search', 0);
	$owner = get_parameter('owner_search', '');
	$id_manufacturer = get_parameter ('id_manufacturer_search', 0);
	$id_contract = get_parameter ('id_contract_search', 0);
	$last_update = get_parameter ('last_update_search');
	$offset = get_parameter('offset', 0);
	$inventory_status = (string)get_parameter('inventory_status_search', "0");
	$id_company = (int)get_parameter('id_company', 0);
	$associated_user = (string)get_parameter('associated_user_search', "");
	$fields_selected = get_parameter('object_fields_search');
	$table_search->class = 'search-table';
	$table_search->width = '98%';
	$table_search->data = array ();
	
	$table_search->data[0][0] = print_input_text ('search_free', $search_free, '', 40, 128, true, __('Search'));
	
	$objects_type = get_object_types ();
	$table_search->data[0][1] = print_label (__('Object type'), '','',true);
	$table_search->data[0][1] .= print_select($objects_type, 'id_object_type_search', $id_object_type_search, 'show_type_fields();', 'Select', '', true, 0, true, false, false, 'width: 200px;');
	
	$table_search->data[0][2] = print_label (__('Object fields'), '','',true);
	
	$object_fields_search = array();
	
	if ($fields_selected != '') {
		$fields = explode(',',$fields_selected);
		foreach ($fields as $selected) {
			$label_field = get_db_value('label', 'tobject_type_field', 'id', $selected);
			$object_fields_search[$selected] = $label_field;
		}
	}
	$table_search->data[0][2] .= print_select($object_fields_search, 'object_fields_search[]', '', '', 'Select', '', true, 4, true, false, false, 'width: 200px;');
	
	$params_assigned['input_id'] = 'text-owner_search';
	$params_assigned['input_name'] = 'owner_search';
	$params_assigned['input_value'] = $owner;
	$params_assigned['title'] = 'Owner';
	$params_assigned['return'] = true;
	$table_search->data[1][0] = user_print_autocomplete_input($params_assigned);
	
	$contracts = get_contracts ();
	$manufacturers = get_manufacturers ();
	
	$table_search->data[1][1] = print_select ($contracts, 'id_contract_search', $id_contract,
		'', __('None'), 0, true, false, false, __('Contract'), '', 'width: 200px;');
	$table_search->data[1][2] = print_select ($manufacturers, 'id_manufacturer_search',
		$id_manufacturer, '', __('None'), 0, true, false, false, __('Manufacturer'), '','width: 200px;');
	
	$table_search->data[1][3] = print_checkbox_extended ('last_update_search', 1, $last_update,
		false, '', '', true, __('Last updated'));
	
	$all_inventory_status = inventories_get_inventory_status ();
	array_unshift($all_inventory_status, __("All"));
	$table_search->data[2][0] = print_select ($all_inventory_status, 'inventory_status_search', $inventory_status, '', '', '', true, false, false, __('Status'));
	
	$params_associated['input_id'] = 'text-associated_user_search';
	$params_associated['input_name'] = 'associated_user_search';
	$params_associated['input_value'] = $associated_user;
	$params_associated['title'] = __('Associated user');
	$params_associated['return'] = true;
	$table_search->data[2][1] = user_print_autocomplete_input($params_associated);
	
	$companies = get_companies();
	$companies[0] = __("All");
	$table_search->data[2][2] = print_select ($companies, 'id_company', $id_company,'', '', 0, true, false, false, __('Associated company'), '', 'width: 200px;');
	
	$table_search->data[3][0] = "&nbsp;";
	$table_search->colspan[3][0] = 4;
	$buttons = '<div style="width:'.$table_search->width.'" class="action-buttons button">';
	$buttons .= "<input type='button' class='sub next' onClick='javascript: loadParams(\"$search_free\");' value='".__("Search")."''>";
	$buttons .= '</div>';
	$table_search->data[4][0] = $buttons;
	$table_search->colspan[4][0] = 4;
	print_table($table_search);	
	$sql_search = 'SELECT tinventory.* FROM tinventory WHERE 1=1';
	$sql_search_count = 'SELECT COUNT(tinventory.id) FROM tinventory WHERE 1=1';	
	

	if ($search) {
	
		$params = '&search=1';
				
		//search only in name and description of inventory
		if ($search_free != '') {
			$sql_search .= " AND (tinventory.name LIKE '%$search_free%' OR tinventory.description LIKE '%$search_free%')";
			$sql_search_count .= " AND (tinventory.name LIKE '%$search_free%' OR tinventory.description LIKE '%$search_free%')";
			
			$params .= "&search_free=$search_free";
		}
		if ($id_object_type_search) {
			$params .= "&id_object_type_search=$id_object_type";
			$sql_search .= " AND tinventory.id_object_type = $id_object_type_search";
			$sql_search_count .= " AND tinventory.id_object_type = $id_object_type_search";
		}
		if ($owner != '') {
			$sql_search .= " AND tinventory.owner = '$owner'";
			$sql_search_count .= " AND tinventory.owner = '$owner'";
			$params .= "&owner=$owner";
		}
		if ($id_manufacturer != 0) {
			$sql_search .= " AND tinventory.id_manufacturer = $id_manufacturer";
			$sql_search_count .= " AND tinventory.id_manufacturer = $id_manufacturer";
			$params .= "&id_manufacturer=$id_manufacturer";
		}
		if ($id_contract != 0) {
			$sql_search .= " AND tinventory.id_contract = $id_contract";
			$sql_search_count .= " AND tinventory.id_contract = $id_contract";
			$params .= "&id_contract=$id_contract";
		}
		if ($inventory_status != "0") {
			$sql_search .= " AND tinventory.status = '$inventory_status'";
			$sql_search_count .= " AND tinventory.status = '$inventory_status'";
			$params .= "&inventory_status=$inventory_status";
		}
		if ($id_company != 0) {
			$sql_search .= " AND tinventory.id IN (SELECT id_inventory FROM tinventory_acl WHERE `type`='company' AND id_reference='$id_company')";
			$sql_search_count .= " AND tinventory.id IN (SELECT id_inventory FROM tinventory_acl WHERE `type`='company' AND id_reference='$id_company')";
			$params .= "&id_company=$id_company";
		}
		if ($associated_user != '') {
			$sql_search .= " AND tinventory.id IN (SELECT id_inventory FROM tinventory_acl WHERE `type`='user' AND id_reference='$associated_user')";
			$sql_search_count .= " AND tinventory.id IN (SELECT id_inventory FROM tinventory_acl WHERE `type`='user' AND id_reference='$associated_user')";
			$params .= "&associated_user=$associated_user";
		}
	} 
	inventories_show_list($sql_search, $sql_search_count, $params, $last_update, 1);
		
	return;
}

if ($get_company_associated) {
	
	$table_company->class = 'databox';
	$table_company->width = '98%';
	$table_company->data = array ();
	
	$companies = get_companies();
	$table_company->data[0][0] = print_label (__('Company'), '','',true);
	$table_company->data[0][1] = print_select ($companies, 'id_company', '',
		'', '', 0, true, false, false, '', '', 'width: 200px;');
	
	print_table($table_company);
	
	echo '<div style="width:'.$table_company->width.'" class="action-buttons button">';
		echo "<a href='javascript: loadCompany();'>".__('Add')."<img src='images/go.png' /></a>";
	echo '</div>';
	
	return;
}

if ($get_user_associated) {
	
	$inventory_user = get_parameter('inventory_user', '');
	$table_user->class = 'search-table';
	$table_user->width = '100%';
	$table_user->data = array ();
	
	$params_user['input_id'] = 'text-inventory_user';
	$params_user['input_name'] = 'inventory_user';
	$params_user['input_value'] = $inventory_user;
	$params_user['return'] = true;
	$params_user['title'] = __('User');

	$table_user->data[0][0] = user_print_autocomplete_input($params_user);
	
	print_table($table_user);
	
	echo '<div style="width:'.$table_user->width.'" class="action-buttons button">';
	echo "<a href='javascript: loadUser();'>".__('Add')."<img src='images/go.png' /></a>";
	echo '</div>';
	
	return;
}

if ($printTableMoreInfo) {

	$id_inventory = get_parameter('id_inventory');
	
	$id_object_type = get_db_value_sql('SELECT id_object_type FROM tinventory WHERE id='.$id_inventory);

	if ($id_object_type) {
		$object_fields = get_db_all_rows_sql("SELECT * FROM tobject_type_field WHERE id_object_type=".$id_object_type);

		if ($object_fields == false) {
			$object_fields = array();
		}
		$table_info->class = 'list';
		$table_info->width = '98%';
		$table_info->data = array ();
		
		$i = 0;
		foreach ($object_fields as $field) {
			$value = get_db_value_sql("SELECT data FROM tobject_field_data WHERE id_inventory=".$id_inventory." AND id_object_type_field=".$field['id']);

			if ($value == "") {
				$value = "--";
			}
			$table_info->data[$i][0] = print_label ($field['label'], '','',true);
			$table_info->data[$i][1] = $value;
			$i++;
		}
		
		print_table($table_info);
		return;
	} else {
		echo "<b>".__('No data to show')."</b>";
		return;
	}
	
}
?>

