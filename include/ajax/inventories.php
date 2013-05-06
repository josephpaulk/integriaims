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

if ($get_external_data) {
	$table_name = get_parameter('table_name');
	$id_table = (string) get_parameter('id_table');
	$element_name = get_parameter('element_name');
	$id_object_type_field = get_parameter('id_object_type_field');
	
	$sql_ext = "SHOW COLUMNS FROM ".$table_name;
	$desc_ext = get_db_all_rows_sql($sql_ext);
	
	$fields = array();
	foreach ($desc_ext as $key=>$ext) {
		$fields[$ext['Field']] = $ext['Field'];
	}
	
	$external_data = get_db_all_rows_in_table($table_name);
		
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
			foreach ($ext_data as $k => $dat) {

				if ($k == $id_table) {
					$val_id = $dat;
				}
				if (array_key_exists($k, $fields)) {
					$data[$j] = "<a href='javascript: enviar(" . $val_id . ", " . $element_name . ", " . $id_object_type_field . ")'>".$dat."</a>";	
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

if ($get_inventory_search) {
	
	$sql_search = '';
	$search = get_parameter('search', 0);
	$search_free = get_parameter ('search_free', '');
	$id_object_type_search = get_parameter ('id_object_type_search', 0);
	$owner_search = get_parameter('owner_search', '');
	$id_manufacturer_search = get_parameter ('id_manufacturer_search', 0);
	$id_contract_search = get_parameter ('id_contract_search', 0);

	$fields_selected = get_parameter('object_fields_search');

	$table_search->class = 'databox';
	$table_search->width = '98%';
	$table_search->data = array ();
	
	$table_search->data[0][0] = print_input_text ('search_free', $search_free, '', 60, 128, true, __('Search'));
	
	$objects_type = get_object_types ();
	$table_search->data[0][1] = print_label (__('Incident type'), '','',true);
	$table_search->data[0][1] .= print_select($objects_type, 'id_object_type_search', $id_object_type_search, 'show_type_fields();', 'Select', '', true, 0, true, false, false, 'width: 200px;');

	
	$table_search->data[0][2] = print_label (__('Incident fields'), '','',true);
	
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
	
	$table_search->data[1][1] = print_select ($contracts, 'id_contract_search', $id_contract_search,
		'', __('None'), 0, true, false, false, __('Contract'), '', 'width: 200px;');

	$table_search->data[1][2] = print_select ($manufacturers, 'id_manufacturer_search',
		$id_manufacturer_search, '', __('None'), 0, true, false, false, __('Manufacturer'), '','width: 200px;');
	
	
	print_table($table_search);
	
	echo '<div style="width:'.$table_search->width.'" class="action-buttons button">';
		echo "<a href='javascript: loadParams(\".$search_free.\");'>".__('Search')."<img src='images/go.png' /></a>";
	echo '</div>';

	if ($search) {

		$sql_search = '';
		
		if ($id_object_type_search != 0) { //búsqueda de texto libre en nombre, descripción de inventario y en contenido de campo personalizado
			$sql_search .= " AND tinventory.id_object_type = $id_object_type_search";
	
			if ($fields_selected != '') {

				$sql_search .= " AND `tobject_field_data`.`id_inventory`=`tinventory`.`id`
								AND `tobject_field_data`.`id_object_type_field` IN ($fields_selected) ";
							
				if ($search_free != '') {
					$sql_search .= "AND (tobject_field_data.`data`LIKE '%$search_free%' OR tinventory.name LIKE '%$search_free%'
							OR tinventory.description LIKE '%$search_free%')";
				}			
			}
		} else { //búsqueda solo en nombre y descripción de inventario
			if ($search_free != '') {
				$sql_search .= " AND (tinventory.name LIKE '%$search_free%' OR tinventory.description LIKE '%$search_free%')";
			}
		}
		
		if ($owner_search != '') {
			$sql_search .= " AND tinventory.owner = '$owner_search'";
		}
		if ($id_manufacturer_search != 0) {
			$sql_search .= " AND tinventory.id_manufacturer = $id_manufacturer_search";
		}
		if ($id_contract_search != 0) {
			$sql_search .= " AND tinventory.id_contract = $id_contract_search";
		}
		
	}
	
	inventories_show_list($sql_search);
		
	return;
}

?>

