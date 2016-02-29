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
	$id_object_type = get_parameter("id_object_type");
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
$clean_output = get_parameter("clean_output");
$inventory_status = get_parameter('inventory_status', 0);
$id_company = get_parameter('id_company');
$associated_user = get_parameter('associated_user');

if (isset($_POST['listview']))
	$mode = 'list';
if (isset($_POST['treeview']))
	$mode = 'tree';

$object_fields = array();
		
if ($fields_selected[0] != '') {
	foreach ($fields_selected as $selected) {
		$label_field = get_db_value('label', 'tobject_type_field', 'id', $selected);
		$object_fields[$selected] = $label_field;
	}
}

$sql_search = 'SELECT tinventory.* FROM tinventory WHERE 1=1';
$sql_search_count = 'SELECT COUNT(tinventory.id) FROM tinventory WHERE 1=1';
$sql_search_obj_type = 'SELECT DISTINCT(tobject_type.id), tobject_type.* FROM `tinventory`, `tobject_type` WHERE tinventory.id_object_type = tobject_type.id order by name';

if ($search) {
	
	$params = '&search=1';
	
	//If object type and fields were selected an there is a free search string.
	//Then we search for this text in the object field data.
	if ($id_object_type != 0 && !empty($object_fields) && $search_free != '') {

		$j = 0;
		
		foreach ($object_fields as $k=>$f) {
			
			if ($j == 0) 
				$string_fields = "$k";
			else
				$string_fields .= ",$k";
			$j++;
		}		

		$params .= "&object_fields_search=$string_fields";
		$params .= "&search_free=$search_free";

		//Compound sub select
		$sql_search = "SELECT tinventory.*
							FROM tinventory, tobject_type, tobject_field_data WHERE 
							tinventory.id_object_type = tobject_type.id AND
							 `tobject_field_data`.`id_inventory`=`tinventory`.`id`";

		$sql_search .= " AND `tobject_field_data`.`id_object_type_field` IN ($string_fields) ";					
		$sql_search .= "AND tobject_field_data.`data` LIKE '%$search_free%'";

		$sql_search_count = "SELECT COUNT(tinventory.id)
							FROM tinventory, tobject_type, tobject_field_data WHERE 
							tinventory.id_object_type = tobject_type.id AND
							 `tobject_field_data`.`id_inventory`=`tinventory`.`id`";

		$sql_search_count .= " AND `tobject_field_data`.`id_object_type_field` IN ($string_fields) ";
		$sql_search_count .= "AND tobject_field_data.`data` LIKE '%$search_free%'";


		$sql_search_obj_type = "SELECT DISTINCT(tobject_type.id), tobject_type.* FROM `tinventory`, `tobject_type`, `tobject_field_data` WHERE 
								tinventory.id_object_type = tobject_type.id AND `tobject_field_data`.`id_inventory`=`tinventory`.`id`";

		$sql_search_obj_type .= " AND `tobject_field_data`.`id_object_type_field` IN ($string_fields) ";
		$sql_search_obj_type .= "AND tobject_field_data.`data` LIKE '%$search_free%'";						
			
	} else { //búsqueda solo en nombre y descripción de inventario
		if ($search_free != '') {
			$sql_search .= " AND (tinventory.name LIKE '%$search_free%' OR tinventory.description LIKE '%$search_free%')";
			$sql_search_count .= " AND (tinventory.name LIKE '%$search_free%' OR tinventory.description LIKE '%$search_free%')";
			
			$params .= "&search_free=$search_free";
		}
	}

	if ($id_object_type) {
		$params .= "&id_object_type_search=$id_object_type";
		$sql_search .= " AND tinventory.id_object_type = $id_object_type";
		$sql_search_count .= " AND tinventory.id_object_type = $id_object_type";
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


if (!$clean_output) {
	
	echo '<form id="tree_search" method="post" action="index.php?sec=inventory&sec2=operation/inventories/inventory">';
		$table_search->class = 'search-table';
		$table_search->width = '98%';
		$table_search->data = array ();
		
		$table_search->data[0][0] = print_input_text ('search_free', $search_free, '', 40, 128, true, __('Search'));
		
		$objects_type = get_object_types ();
		$table_search->data[0][1] = print_label (__('Object type'), '','',true);
		$table_search->data[0][1] .= print_select($objects_type, 'id_object_type_search', $id_object_type, 'show_type_fields();', 'Select', '', true, 0, true, false, false, 'width: 200px;');
		
		$table_search->data[0][2] = print_label (__('Object fields'), '','',true);
		
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

		$buttons = '<div style=" text-align: right;">';
		$buttons .= print_input_hidden ('search', 1, true);
		$buttons .= print_input_hidden ('mode', $mode, true);
		$buttons .= print_submit_button (__('Search'), 'search', false, 'class="sub search"', true);
		
		$filter["query"] = $sql_search;
		serialize_in_temp($filter, $config["id_user"]);
		$buttons .= print_button(__('Export to CSV'), '', false, 'window.open(\'' . 'include/export_csv.php?export_csv_inventory=1'.'\')', 'class="sub csv"', true);

		$buttons .= print_report_button ("index.php?sec=inventory&sec2=operation/inventories/inventory&search=1&params=$params", __('Export to PDF')."&nbsp;");
		$buttons .= '</div>';

		$all_inventory_status = inventories_get_inventory_status ();
		array_unshift($all_inventory_status, __("All"));
		$table_search->data[2][0] = print_select ($all_inventory_status, 'inventory_status', $inventory_status[0], '', '', '', true, false, false, __('Status'));
		
		$params_associated['input_id'] = 'text-associated_user';
		$params_associated['input_name'] = 'associated_user';
		$params_associated['input_value'] = $associated_user;
		$params_associated['title'] = __('Associated user');
		$params_associated['return'] = true;
	
		$table_search->data[2][1] = user_print_autocomplete_input($params_associated);
		
		$companies = get_companies();
		$companies[0] = __("All");
		$table_search->data[2][2] = print_select ($companies, 'id_company', $id_company,'', '', 0, true, false, false, __('Associated company'), '', 'width: 200px;');
		
		$table_search->data[3][0] = "&nbsp;";
		$table_search->colspan[3][0] = 4;
		
		$table_search->data[4][0] = $buttons;
		$table_search->colspan[4][0] = 4;

		print_table($table_search);
	echo '</form>';

}

$write_permission = enterprise_hook ('inventory_check_acl', array ($config['id_user'], $id, true));	
$page = (int)get_parameter('page', 1);
switch ($mode) {
	case 'tree':
		inventories_print_tree($sql_search, $sql_search_obj_type, $last_update);
		break;
	case 'list':
		inventories_show_list($sql_search, $sql_search_count, $params, $last_update);
		if ($write_permission) {
			echo '<div style=" text-align: right;">';
			echo print_button(__('Delete All'), '', false, 'javascript: delete_massive_inventory()', 'class="sub delete"', true);
			echo '</div>';
		}
		break;
	default:
		inventories_show_list($sql_search, $sql_search_count, $params, $last_update);
		if ($write_permission) {	
			echo '<div style=" text-align: right;">';
			echo print_button(__('Delete All'), '', false, 'javascript: delete_massive_inventory()', 'class="sub delete"', true);
			echo '</div>';
		}
		break;
}


echo '<div id="sql_search_hidden" style="display:none;">';
	print_input_text('sql_search_hidden', $sql_search);
echo '</div>';

/* Add a form to carry filter between treeview and listview */
echo '<form id="tree_view_inventory" method="post" action="index.php?sec=inventory&sec2=operation/inventories/inventory" style="clear: both">';
	print_input_hidden ("search_free", $search_free);
	print_input_hidden ("id_object_type_search", $id_object_type);
	foreach ($object_fields as $k=>$v) {
		print_input_hidden ("object_fields_search[]", $k);
	}
	print_input_hidden ("owner", $owner);
	print_input_hidden ("id_contract", $id_contract);
	print_input_hidden ("id_manufacturer", $id_manufacturer);
	print_input_hidden ("last_update", $last_update);
	print_input_hidden ('search', 1);
	print_input_hidden ('mode', 'tree');
	print_input_hidden ("offset", get_parameter("offset"));
echo "</form>";

/* Add a form to carry filter between treeview and listview */
echo '<form id="list_view_inventory" method="post" action="index.php?sec=inventory&sec2=operation/inventories/inventory" style="clear: both">';
	print_input_hidden ("search_free", $search_free);
	print_input_hidden ("id_object_type_search", $id_object_type);
	foreach ($object_fields as $k=>$v) {
                print_input_hidden ("object_fields_search[]", $k);
        }
	print_input_hidden ("owner", $owner);
	print_input_hidden ("id_contract", $id_contract);
	print_input_hidden ("id_manufacturer", $id_manufacturer);
	print_input_hidden ("last_update", $last_update);
	print_input_hidden ('search', 1);
	print_input_hidden ('mode', 'list');
	print_input_hidden ("offset", get_parameter("offset"));
echo "</form>";

?>

<script type="text/javascript" src="include/js/jquery.ui.autocomplete.js"></script>
<script type="text/javascript" src="include/js/integria_inventory.js"></script>
<script type="text/javascript" src="include/js/fixed-bottom-box.js"></script>

<script type="text/javascript">

$(document).ready (function () {
	$("#listview_form_submit").click(function (event) {
		event.preventDefault();
		$("#list_view_inventory").submit();
	});

	$("#treeview_form_submit").click(function (event) {
		event.preventDefault();
		$("#tree_view_inventory").submit();
	});		
	
	var idUser = "<?php echo $config['id_user'] ?>";
	
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
});

</script>
