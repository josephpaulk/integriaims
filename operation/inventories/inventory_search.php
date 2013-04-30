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
	
	$select_fields = get_parameter('select_fields', 0);
	$print_subtree = get_parameter('print_subtree', 0);
	$printTable = get_parameter('printTable', 0);
	
	if ($select_fields) {
		$id_object_type = get_parameter('id_object_type');
		
		$fields = get_db_all_rows_filter('tobject_type_field', array('id_object_type'=>$id_object_type), 'label, id');
		
		if ($fields === false) {
			$fields = array();
		}

		$object_fields = array();
		foreach ($fields as $key => $field) {
			$object_fields[$field['id']] = $field['label'];
		}
		
		echo json_encode($object_fields);
		return;
	}
	
	if ($printTable) {
		$id_item = get_parameter('id_item');
		$type = get_parameter('type');
		$id_father = get_parameter('id_father');
		$sql_search = base64_decode(get_parameter('sql_search', ''));
	
		inventories_printTable($id_item, $type, $id_father);
		return;
	}
	
	if ($print_subtree) {
	
		$id_item = get_parameter ('id_item');
		$lessBranchs = get_parameter('less_branchs');
		$type = get_parameter('type');
		$id_father = get_parameter('id_father');
		$sql_search = base64_decode(get_parameter('sql_search', ''));
	
		
			if ($type == 'object_types') {
		
				$sql = "SELECT tinventory.`id`, tinventory.`name` FROM tinventory, tobject_type
						WHERE `id_object_type`=$id_item
						AND tinventory.id_object_type = tobject_type.id $sql_search";
				
				$cont = get_db_all_rows_sql($sql);

				$countRows = count($cont);

				//Empty Branch
				if ($countRows == 0) {

					echo "<ul style='margin: 0; padding: 0;'>\n";
					echo "<li style='margin: 0; padding: 0;'>";
					if ($lessBranchs == 1)
						echo print_image ("images/tree/no_branch.png", true, array ("style" => 'vertical-align: middle;'));
					else
						echo print_image ("images/tree/branch.png", true, array ("style" => 'vertical-align: middle;'));
					
					echo "<i>" . __("Vacío") . "</i>";
					echo "</li>";
					echo "</ul>";
					
					return;
				}
				
				//Branch with items
				$new = true;
				$count = 0;
				echo "<ul style='margin: 0; padding: 0;'>\n";
				
				$sql_search = base64_encode($sql_search);
				
				while($row = get_db_all_row_by_steps_sql($new, $result, $sql)) {

					$new = false;
					$count++;
				
					$less = $lessBranchs;
					if ($count != $countRows)
						$img = print_image ("images/tree/leaf.png", true, array ("style" => 'vertical-align: middle;', "id" => "tree_image" . $id_item . "_$type_" . $row["id"], "pos_tree" => "2"));
					else {
						$less = $less + 2; // $less = $less or 0b10
						$img = print_image ("images/tree/last_leaf.png", true, array ("style" => 'vertical-align: middle;', "id" => "tree_image" . $id_item . "_$type_" . $row["id"], "pos_tree" => "3"));
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
					
					echo $row["name"];
					
					echo "</a>";
					echo "<div hiddenDiv='1' loadDiv='0' style='margin: 0px; padding: 0px;' class='tree_view' id='tree_div" . $id_item . "_$type_" . $row["INSTANCEID"] . "'></div>";
					echo "</li>";
					
				} 
			}
			echo "</ul>\n";
			return;
		}
}

if (! give_acl ($config['id_user'], 0, "VR")) {
	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access inventory search");
	include ("general/noaccess.php");
	exit;
}

$search = get_parameter('search', 0);
$sql_search = '';

$search_free = get_parameter ('search_free', '');
$id_object_type = get_parameter ('id_object_type', 0);
$owner = get_parameter('owner', '');
$id_manufacturer = get_parameter ('id_manufacturer', 0);
$id_contract = get_parameter ('id_contract', 0);

$fields_selected = (array)get_parameter('object_fields');

$mode = get_parameter('mode', 'tree');

echo '<form id="tree_search" method="post" action="index.php?sec=inventory&sec2=operation/inventories/inventory_search">';
	$table_search->class = 'databox';
	$table_search->width = '98%';
	$table_search->data = array ();
	
	$table_search->data[0][0] = print_input_text ('search_free', $search_free, '', 60, 128, true, __('Search'));
	
	$objects_type = get_object_types ();
	$table_search->data[0][1] = print_label (__('Incident type'), '','',true);
	$table_search->data[0][1] .= print_select($objects_type, 'id_object_type', $id_object_type, 'show_fields();', 'Select', '', true, 0, true, false, false, 'width: 200px;');
	
	$table_search->data[0][2] = print_label (__('Incident fields'), '','',true);
	
	$object_fields = array();
	
	if ($fields_selected[0] != '') {
		foreach ($fields_selected as $selected) {
			$label_field = get_db_value('label', 'tobject_type_field', 'id', $selected);
			$object_fields[$selected] = $label_field;
		}
	}
	$table_search->data[0][2] .= print_select($object_fields, 'object_fields[]', '', '', 'Select', '', true, 4, true, false, false, 'width: 200px;');
	
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
	
	
	print_table($table_search);
	
	echo '<div style="width:'.$table_search->width.'" class="action-buttons button">';
		print_input_hidden ('search', 1);
		print_submit_button (__('Search'), 'search', false, 'class="sub next"');
		
		if ($mode == 'tree') {
			print_input_hidden ('mode', 'list');
			print_submit_button (__('List view'), 'listview', false, 'class="sub next"');
		} else {
			print_input_hidden ('mode', 'tree');
			print_submit_button (__('Tree view'), 'treeview', false, 'class="sub next"');
		}
		
	echo '</div>';
echo '</form>';

if ($search) {
	$sql_search = '';
	
	if ($id_object_type != 0) { //búsqueda de texto libre en nombre, descripción de inventario y en contenido de campo personalizado
		$sql_search .= " AND tinventory.id_object_type = $id_object_type";
		
		if (!empty($object_fields)) {
			$j = 0;
			foreach ($object_fields as $f) {
				if ($j == 0) 
					$string_fields = "$f";
				else
					$string_fields .= ",$f";
				$j++;
			}


			$sql_search .= " AND `tobject_field_data`.`id_inventory`=`tinventory`.`id`
							AND `tobject_field_data`.`id_object_type_field` IN ($string_fields) ";
						
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
	
	if ($owner != '') {
		$sql_search .= " AND tinventory.owner = '$owner'";
	}
	if ($id_manufacturer != 0) {
		$sql_search .= " AND tinventory.id_manufacturer = $id_manufacturer";
	}
	if ($id_contract != 0) {
		$sql_search .= " AND tinventory.id_contract = $id_contract";
	}
	
} 


$page = (int)get_parameter('page', 1);
switch ($mode) {
	case 'tree':
		inventories_print_tree($sql_search);
		break;
	case 'list':
		inventories_show_list($sql_search);
		break;
	default:
		inventories_print_tree($sql_search);
		break;
}
	

echo '<div id="sql_search_hidden" style="display:none;">';
	print_input_text('sql_search_hidden', $sql_search);
echo '</div>';

?>

<script type="text/javascript" src="include/js/jquery.autocomplete.js"></script>
<script type="text/javascript">
	

function show_fields () {

	id_object_type = $("#id_object_type").val();
	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=operation/inventories/inventory_search&select_fields=1&id_object_type=" + id_object_type,
		dataType: "json",
		success: function(data){
				$("#object_fields").empty();
				jQuery.each (data, function (id, value) {
					field = value;
					$("select[name='object_fields[]']").append($("<option>").val(id).html(field));
				});	
			}
	});
}

/**
 * loadSubTree asincronous load ajax the agents or modules (pass type, id to search and binary structure of branch),
 * change the [+] or [-] image (with same more or less div id) of tree and anime (for show or hide)
 * the div with id "div[id_father]_[type]_[div_id]"
 *
 * type string use in js and ajax php
 * div_id int use in js and ajax php
 * less_branchs int use in ajax php as binary structure 0b00, 0b01, 0b10 and 0b11
 * id_father int use in js and ajax php, its useful when you have a two subtrees with same agent for diferent each one
 */
function loadSubTree(type, div_id, less_branchs, id_father, sql_search) {

	hiddenDiv = $('#tree_div'+'_'+div_id).attr('hiddenDiv');
	loadDiv = $('#tree_div'+'_'+div_id).attr('loadDiv');
	pos = parseInt($('#tree_image'+'_'+div_id).attr('pos_tree'));

	//If has yet ajax request running
	if (loadDiv == 2)
		return;
	
	if (loadDiv == 0) {
	
		//Put an spinner to simulate loading process

		$('#tree_div'+'_'+div_id).html("<img style='padding-top:10px;padding-bottom:10px;padding-left:20px;' src=images/spinner.gif>");
		$('#tree_div'+'_'+div_id).show('normal');
		$('#tree_div'+'_'+div_id).attr('loadDiv', 2);
	
		$.ajax({
			type: "POST",
			url: "ajax.php",
			data: "page=operation/inventories/inventory_search&print_subtree=1&type=" + 
				type + "&id_item=" + div_id + "&less_branchs=" + less_branchs+ "&sql_search=" + sql_search,
			success: function(msg){
				if (msg.length != 0) {
					
					$('#tree_div'+'_'+div_id).hide();
					$('#tree_div'+'_'+div_id).html(msg);
					$('#tree_div'+'_'+div_id).show('normal');
					//change image of tree [+] to [-]
					<?php if (! defined ('METACONSOLE')) {
						echo 'var icon_path = \'images/tree\';';
					}
					else {
						echo 'var icon_path = \'../../images/tree\';';
					}
					?>
					switch (pos) {
						case 0:
							$('#tree_image'+'_'+div_id).attr('src',icon_path+'/first_expanded.png');
							break;
						case 1:
							$('#tree_image'+'_'+div_id).attr('src',icon_path+'/one_expanded.png');
							break;
						case 2:
							$('#tree_image'+'_'+div_id).attr('src',icon_path+'/expanded.png');
							break;
						case 3:
							$('#tree_image'+'_'+div_id).attr('src',icon_path+'/last_expanded.png');
							break;
					}

					$('#tree_div'+'_'+div_id).attr('hiddendiv',0);
					$('#tree_div'+'_'+div_id).attr('loadDiv', 1);
				}
				
				// Refresh forced title callback to work with html code created dinamicly
				//forced_title_callback();
			}
		});
	}
	else {
		<?php
		if (! defined ('METACONSOLE')) {
			echo 'var icon_path = \'images/tree\';';
		}
		else {
			echo 'var icon_path = \'../../images/tree\';';
		}
		?>
		if (hiddenDiv == 0) {

			$('#tree_div'+'_'+div_id).hide('normal');
			$('#tree_div'+'_'+div_id).attr('hiddenDiv',1);
			
			//change image of tree [-] to [+]
			switch (pos) {
				case 0:
					$('#tree_image'+'_'+div_id).attr('src',icon_path+'/first_closed.png');
					break;
				case 1:
					$('#tree_image'+'_'+div_id).attr('src',icon_path+'/one_closed.png');
					break;
				case 2:
					$('#tree_image'+'_'+div_id).attr('src',icon_path+'/closed.png');
					break;
				case 3:
					$('#tree_image'+'_'+div_id).attr('src',icon_path+'/last_closed.png');
					break;
			}
		}
		else {
			//change image of tree [+] to [-]
			switch (pos) {
				case 0:
					$('#tree_image'+'_'+div_id).attr('src',icon_path+'/first_expanded.png');
					break;
				case 1:
					$('#tree_image'+'_'+div_id).attr('src',icon_path+'/one_expanded.png');
					break;
				case 2:
					$('#tree_image'+'_'+div_id).attr('src',icon_path+'/expanded.png');
					break;
				case 3:
					$('#tree_image'+'_'+div_id).attr('src',icon_path+'/last_expanded.png');
					break;
			}

			$('#tree_div'+'_'+div_id).show('normal');
			$('#tree_div'+'_'+div_id).attr('hiddenDiv',0);
		}
	}
}

function loadTable(type, div_id, less_branchs, id_father, sql_search) {
	id_item = div_id;

	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=operation/inventories/inventory_search&id_item=" + id_item + "&printTable=1&type="+ type+"&id_father=" + id_father +"&sql_search="+sql_search,
		success: function(data){
			$('#cont').html(data);
		}
	});
	loadSubTree(type, div_id, less_branchs, id_father, sql_search);		
}

$(document).ready (function () {
	
	$("#text-owner").autocomplete ("ajax.php",
		{
			scroll: true,
			minChars: 2,
			extraParams: {
				page: "include/ajax/users",
				search_users: 1,
				id_user: "<?php echo $config['id_user'] ?>"
			},
			formatItem: function (data, i, total) {
				if (total == 0)
					$("#text-owner").css ('background-color', '#cc0000');
				else
					$("#text-owner").css ('background-color', '');
				if (data == "")
					return false;
				return data[0]+'<br><span class="ac_extra_field"><?php echo __("Nombre Real") ?>: '+data[1]+'</span>';
			},
			delay: 200

		});
});

</script>
