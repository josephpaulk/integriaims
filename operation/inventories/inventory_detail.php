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

check_login ();

require_once ('include/functions_inventories.php');
require_once ('include/functions_user.php');

if (defined ('AJAX')) {
	
	global $config;
	
	$show_type_fields = (bool) get_parameter('show_type_fields', 0);
	$show_external_data = (bool) get_parameter('show_external_data', 0);
	$update_external_id = (bool) get_parameter('update_external_id', 0);
 	
 	if ($show_type_fields) {
		$id_object_type = get_parameter('id_object_type');
		$id_inventory = get_parameter('id_inventory');		
		$fields = inventories_get_all_type_field ($id_object_type, $id_inventory);
	
		echo json_encode($fields);
		return;
	}
	
	if ($show_external_data) {

		$external_table_name = get_parameter('external_table_name');
		$external_reference_field = get_parameter('external_reference_field');
		$data_id_external_table = get_parameter('id_external_table');
		
		$fields_ext = inventories_get_all_external_field ($external_table_name, $external_reference_field, $data_id_external_table);

		echo json_encode($fields_ext);
		return;
	}
	
	if ($update_external_id) {
		$id_object_type_field = get_parameter('id_object_type_field');
		$id_inventory = get_parameter('id_inventory');
		$id_value = get_parameter('id_value'); //new value for id field
		
		$result = process_sql_update('tobject_field_data', array('data' => $id_value), array('id_object_type_field' => $id_object_type_field, 'id_inventory'=>$id_inventory), 'AND');
		
		return $result;
	}	
}

$id = (int) get_parameter ('id');
$inventory_name = get_db_value('name', 'tinventory', 'id', $id);

if ($id) {
	$inventory = get_inventory ($id);
}

$check_inventory = (bool) get_parameter ('check_inventory');

/*
if ($check_inventory) {
	// IR and incident creator can see the incident
	if ($inventory !== false && (give_acl ($config['id_user'], get_inventory_group ($id), "IR"))){
		echo 1;
		$var = 1;
	}
	else {
		echo 0;
		$var = 0;
	}
	if (defined ('AJAX')) {
		//return $var;
		return;
	}
}
*/

if ($id) {
	echo "<h1>".__('Object')." #$id"."&nbsp;&nbsp;-&nbsp;".$inventory_name."</h1>";
} else {
	if (! defined ('AJAX'))
		echo "<h2>".__('Create inventory object')."</h2>";
}

//**********************************************************************
// Tabs
//**********************************************************************
if (!defined ('AJAX')) {
	if ($id) {
		echo '<div id="tabs">';

		/* Tabs list */
		echo '<ul class="ui-tabs-nav">';
		echo '<li class="ui-tabs-selected"><a href="index.php?sec=inventory&sec2=operation/inventories/inventory_detail"><span>'.__('Details').'</span></a></li>';
		if (!empty($id)) {
			echo '<li class="ui-tabs"><a href="index.php?sec=inventory&sec2=operation/inventories/inventory_relationship&id=' . $id . '"><span>'.__('Relationships').'</span></a></li>';
		}
		echo '</ul>';
		echo '</div>';
	}
}

$result_msg = '';


$update = (bool) get_parameter ('update_inventory');
$create = (bool) get_parameter ('create_inventory');
$name = (string) get_parameter ('name');
$description = (string) get_parameter ('description');
$id_contract = (int) get_parameter ('id_contract');
$id_parent = (int) get_parameter ('id_parent');
$id_manufacturer = (int) get_parameter ('id_manufacturer');
$owner = (string) get_parameter ('owner');
$public = (bool) get_parameter ('public');
$id_object_type = (int) get_parameter('id_object_type');
$is_unique = true;
$msg_err = '';

if ($update) {
	if (! give_acl ($config['id_user'], get_inventory_group ($id), "VW")) {
		// Doesn't have access to this page
		audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to update inventory #".$id);
		include ("general/noaccess.php");
		exit;
	}
	
	$old_parent = get_db_value('id_parent', 'tinventory', 'id', $id);
	
	$sql = sprintf ('UPDATE tinventory SET name = "%s", description = "%s",
			id_contract = %d,
			id_parent = %d, id_manufacturer = %d, owner = "%s", public = %d, id_object_type = %d
			WHERE id = %d',
			$name, $description, $id_contract,
			$id_parent,
			$id_manufacturer, $owner, $public, $id_object_type, $id);
	$result = process_sql ($sql);
	
	//update object type fields
	if ($id_object_type != 0) {
		$sql_label = "SELECT `label`, `type`, `unique` FROM `tobject_type_field` WHERE id_object_type = $id_object_type";
		$labels = get_db_all_rows_sql($sql_label);
		
		if ($labels === false) {
			$labels = array();
		}
		
		foreach ($labels as $label) {
				
				$values['data'] = get_parameter (base64_encode($label['label']));
				
				if ($label['unique']) {
					$is_unique = inventories_check_unique_field($values['data'], $label['type']);
					
					if (!$is_unique) {
						$msg_err .= '<h3 class="err">'.__(" Field '").$label['label'].__("' not updated. Value must be unique").'</h3>'; 
					}
				}
				$id_object_type_field = get_db_value_filter('id', 'tobject_type_field', array('id_object_type' => $id_object_type, 'label'=> $label['label']), 'AND');
				
				
				
				$values['id_object_type_field'] = $id_object_type_field;
				$values['id_inventory'] = $id;
				
				$exists_id = get_db_value_filter('id', 'tobject_field_data', array('id_inventory' => $id, 'id_object_type_field'=> $id_object_type_field), 'AND');
				if ($exists_id && $is_unique) 
					process_sql_update('tobject_field_data', $values, array('id_object_type_field' => $id_object_type_field, 'id_inventory' => $id), 'AND');
				else
					process_sql_insert('tobject_field_data', $values);
		}
	}
	
	//parent
	if ($id_parent != 0) {
		
		//delete fields old parent
		$old_fields = get_db_all_rows_filter('tobject_type_field', array('id_object_type'=>$old_parent, 'inherit' => 1));
		
		if ($old_fields === false) {
			$old_fields = aray();
		}
		
		foreach ($old_fields as $key => $old) {
			process_sql_delete('tobject_field_data', array('id_object_type' => $id, 'id' => $old['id']));
		}
		
		//add new fields
		$inherit_fields = get_db_all_rows_filter('tobject_type_field', array('ic_object_type'=>$id_parent, 'inherit' => 1));
		
		if ($inherit_fields === false) {
			$inherit_fields = aray();
		}
		
		foreach ($inherit_fields as $key => $in_field) {
			$in_field['id_object_type'] = $id;
			process_sql_insert('tobject_field_data', $in_field);
		}
	}
	
	/* Update contacts in inventory */
	
	if ($result !== false) {
		$result_msg = '<h3 class="suc">'.__('Successfully updated').'</h3>';
	} else {
		$result_msg = '<h3 class="error">'.__('There was an error updating inventory object').'</h3>';
	}
	
	if (defined ('AJAX')) {
		echo $result_msg;
		echo $msg_err;
		return;
	}
}

if ($create) {
	if (! give_acl ($config['id_user'], 0, "VW")) {
		// Doesn't have access to this page
		audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create inventory object");
		include ("general/noaccess.php");
		exit;
	}
	
	$err_message = __('Could not be created');
	
	$inventory_id = get_db_value ('id', 'tinventory', 'name', $name);

	if($name == '') {
		$err_message .= ". ".__('Name cannot be empty').".";
		$id = false;
	}
	else if ($inventory_id !== false) {
		$err_message .= ". ".__('Duplicate name').".";
		$id = false;
	}
	else {

		$sql = sprintf ('INSERT INTO tinventory (name, description,
				id_contract, id_parent, id_manufacturer, owner, public, id_object_type)
				VALUES ("%s", "%s", %d, %d, %d, "%s", %d, %d)',
				$name, $description, $id_contract,
				$id_parent, $id_manufacturer, $owner, $public, $id_object_type);
		$id = process_sql ($sql, 'insert_id');
	}
	if ($id !== false) {
		
		//insert data to incident type fields
		if ($id_object_type != 0) {
			$sql_label = "SELECT `label`, `unique`, `type` FROM `tobject_type_field` WHERE id_object_type = $id_object_type";
			$labels = get_db_all_rows_sql($sql_label);
		
			if ($labels === false) {
				$labels = array();
			}
			
			foreach ($labels as $label) {

				$id_object_field = get_db_value_filter('id', 'tobject_type_field', array('id_object_type' => $id_object_type, 'label'=> $label['label']), 'AND');
				
				$values_insert['id_inventory'] = $id;
				$values_insert['data'] = get_parameter (base64_encode($label['label']));
				
				if ($label['unique']) {
					$is_unique = inventories_check_unique_field($values_insert['data'], $label['type']);
					
					if (!$is_unique) {
						$msg_err .= '<h3 class="err">'.__(" Field '").$label['label'].__("' not created. Value must be unique").'</h3>'; 
					}
				}
				$values_insert['id_object_type_field'] = $id_object_field;
				$id_object_type_field = get_db_value('id', 'tobject_type_field', 'id_object_type', $id_object_type);
				
				if ($is_unique)
					process_sql_insert('tobject_field_data', $values_insert);
			
			}
		}
		
		//parent
		if ($id_parent != 0) {
			$inherit_fields = get_db_all_rows_filter('tobject_type_field', array('ic_object_type'=>$id_parent, 'inherit' => 1));
			
			if ($inherit_fields === false) {
				$inherit_fields = aray();
			}
			
			foreach ($inherit_fields as $key => $in_field) {
				$in_field['id_object_type'] = $id;
				process_sql_insert('tobject_field_data', $in_field);
			}
		}
			
		$result_msg = '<h3 class="suc">'.__('Successfully created').'</h3>';

		$result_msg .= "<h3><a href='index.php?sec=inventory&sec2=operation/inventories/inventory_detail&id=$id'>".__("Click here to continue working with Object #").$id."</a></h3>";

	} else {
		$result_msg = '<h3 class="error">'.$err_message.'</h3>';
	}
	
/*
	if (defined ('AJAX')) {
		echo $result_msg;
		return;
	}
*/
	$id = 0;
	$name = "";
	$description = "";
	$id_contract = "";
	$id_parent = "";
	$id_manufacturer = 0;
	$public = false;
	$owner = $config['id_user'];
	$id_object_type = 0;
}

/* This is the default permission checking to create an inventory */
$has_permission = give_acl ($config['id_user'], 0, "VW");

if ($id) {
	$group = get_inventory_group ($id);
	if (! give_acl ($config['id_user'], $group, "VR")) {
		// Doesn't have access to this page
		audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access inventory #".$id);
		include ("general/noaccess.php");
		exit;
	}
	
	/* If editing, the permission checks is now specific for this object */
	$has_permission = give_acl ($config['id_user'], $group, "VW");
	
	$inventory = get_db_row ('tinventory', 'id', $id);
	$name = $inventory['name'];
	$description = $inventory['description'];
	$id_contract = $inventory['id_contract'];
	$id_parent = $inventory['id_parent'];
	$id_manufacturer = $inventory['id_manufacturer'];
	$owner = $inventory['owner'];
	$public = $inventory['public'];
	$id_object_type = $inventory['id_object_type'];
}


$table->class = 'databox';
$table->width = '90%';
$table->data = array ();
$table->colspan = array ();
$table->colspan[4][1] = 2;
$table->colspan[5][0] = 3;

/* First row */
if ($has_permission) {
	$table->data[0][0] = print_input_text ('name', $name, '', 40, 128, true,
		__('Name'));
} else {
	$table->data[0][0] = print_label (__('Name'), '', '', true, $name);
}

$params_assigned['input_id'] = 'text-owner';
$params_assigned['input_name'] = 'owner';
$params_assigned['input_value'] = $owner;
$params_assigned['title'] = 'Owner';
$params_assigned['return'] = true;

$table->data[0][1] = user_print_autocomplete_input($params_assigned);
	
$table->data[0][2] = print_checkbox_extended ('public', 1, $public,
	! $has_permission, '', '', true, __('Public'));

/* Second row */
if ($has_permission) {
	$parent_name = $id_parent ? get_inventory_name ($id_parent) : __('Search parent');
	$table->data[1][0] = print_button ($parent_name,
				'parent_search', false, '', 'class="dialogbtn"',
				true, __('Parent object'));
	if ($id_parent)
		$table->data[1][0] .= '<a href="index.php?sec=inventory&sec2=operation/inventories/inventory&id='.$id_parent.'"><img src="images/go.png" /></a>';
	
	$table->data[1][0] .= print_input_hidden ('id_parent', $id_parent, true);

} else {
	$parent_name = $id_parent ? get_inventory_name ($id_parent) : __('Not set');
	
	$table->data[1][0] = print_label (__('Parent object'), '', '', true, $parent_name);
	if ($id_parent)
		$table->data[1][0] .= '<a href="index.php?sec=inventory&sec2=operation/inventories/inventory&id='.$id_parent.'"><img src="images/go.png" /></a>';
}

$contracts = get_contracts ();
$manufacturers = get_manufacturers ();

if ($has_permission) {
	$table->data[1][1] = print_select ($contracts, 'id_contract', $id_contract,
		'', __('None'), 0, true, false, false, __('Contract'));

	$table->data[1][2] = print_select ($manufacturers, 'id_manufacturer',
		$id_manufacturer, '', __('None'), 0, true, false, false, __('Manufacturer'));
} else {
	$contract = isset ($contracts[$id_contract]) ? $contracts[$id_contract] : __('Not set');
	$manufacturer = isset ($manufacturers[$id_manufacturer]) ? $manufacturers[$id_manufacturer] : __('Not set');
	
	$table->data[1][1] = print_label (__('Contract'), '', '', true, $contract);
	$table->data[1][2] = print_label (__('Manufacturer'), '', '', true, $manufacturer);
}

/* Third row */
$objects_type = get_object_types ();
$table->data[2][0] = print_label (__('Incident type'), '','',true);
if ($id_object_type == 0) {
	$disabled = false;
} else {
	$disabled = true;
}
$table->data[2][0] .= print_select($objects_type, 'id_object_type', $id_object_type, 'show_fields();', 'Select', '', true, 0, true, false, $disabled);

/* Fourth row */
$table->colspan[3][0] = 3;		
$table->data[3][0] = "";


/* Fifth row */
$table->data[4][1] = "</div>&nbsp;";

/* Sixth row */
$disabled_str = ! $has_permission ? 'readonly="1"' : '';
$table->data[5][0] = print_textarea ('description', 15, 100, $description,
	$disabled_str, true, __('Description'));

echo '<div class="result">'.$result_msg.$msg_err.'</div>';


if ($has_permission) {	
	echo '<form method="post" id="inventory_status_form">';
	print_table ($table);

	echo '<div style="width:'.$table->width.'" class="action-buttons button">';
	if ($id) {
		print_input_hidden ('update_inventory', 1);
		print_input_hidden ('id', $id);
		print_input_hidden ('id_object_type', $id_object_type);
		print_submit_button (__('Update'), 'update', false, 'class="sub upd"');
	} else {
		print_input_hidden ('create_inventory', 1);
		print_submit_button (__('Create'), 'create', false, 'class="sub next"');
	}
	echo '</div>';
	echo '</form>';
} else {
	print_table ($table);
}

//id_inventory hidden
echo '<div id="id_inventory_hidden" style="display:none;">';
	print_input_text('id_object_hidden', $id);
echo '</div>';

echo "<div class= 'dialog ui-dialog-content' id='external_table_window'></div>";

//if (! defined ('AJAX')):
?>

<script type="text/javascript" src="include/js/jquery.metadata.js"></script>
<script type="text/javascript" src="include/js/jquery.tablesorter.js"></script>
<script type="text/javascript" src="include/js/jquery.tablesorter.pager.js"></script>
<script type="text/javascript" src="include/js/integria_incident_search.js"></script>
<script type="text/javascript" src="include/js/jquery.autocomplete.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.dialog"></script>
<script type="text/javascript">

$(document).ready (function () {
	
	configure_inventory_form (false);

	if ($("#id_object_type").val() != 0) {
		show_fields();
	}

	$("form.delete").submit (function () {
		if (! confirm ("<?php echo __('Are you sure?'); ?>"))
			return false;
	});
	
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
		
		$("#img_show_external_table").click(function() {
			alert("SI");
		});
});

function show_fields() {

	id_object_type = $("#id_object_type").val();

	id_inventory = $("#text-id_object_hidden").val();

	$('#table_fields').remove();

	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=operation/inventories/inventory_detail&show_type_fields=1&id_object_type=" + id_object_type +"&id_inventory=" +id_inventory,
		dataType: "json",
		success: function(data){
			
			fi=document.getElementById('table1-3-0');
			var table = document.createElement("table"); //create table
			table.id='table_fields';
			table.className = 'databox_color_without_line';
			table.width='98%';
			fi.appendChild(table); //append table to row
			
			var i = 0;
			var resto = 0;
			jQuery.each (data, function (id, value) {
				
				resto = i % 2;

				if (value['type'] == "combo") {
					if (resto == 0) {
						var objTr = document.createElement("tr"); //create row
						objTr.id = 'new_row_'+i;
						objTr.width='98%';
						table.appendChild(objTr);
					} else {
						pos = i-1;
						objTr = document.getElementById('new_row_'+pos);
					}
					
					var objTd1 = document.createElement("td"); //create column for label
					objTd1.width='50%';
					lbl = document.createElement('label');
					lbl.innerHTML = value['label']+' ';
					
					objTr.appendChild(objTd1);
					objTd1.appendChild(lbl);
					
					txt = document.createElement('br');
					lbl.appendChild(txt);
					
					element=document.createElement('select');
					element.id=value['label']; 
					element.name=value['label_enco'];
					element.value=value['label'];
					element.style.width="170px";
					element.class="type";
					
					var new_text = value['combo_value'].split(',');
					jQuery.each (new_text, function (id, val) {
						element.options[id] = new Option(val);
						element.options[id].setAttribute("value",val);
						if (value['data'] == val) {
							element.options[id].setAttribute("selected",'');
						}
					});
			
					lbl.appendChild(element);
					i++;
				}
				
				if ((value['type'] == "text") || (value['type'] == "numeric") || (value['type'] == "external")) {
				
					if (resto == 0) {
						var objTr = document.createElement("tr"); //create row
						objTr.id = 'new_row_'+i;
						objTr.width='98%';
						table.appendChild(objTr);
					} else {
						pos = i-1;
						objTr = document.getElementById('new_row_'+pos);
					}
					
					var objTd1 = document.createElement("td"); //create column for label
					objTd1.width='50%';
					lbl = document.createElement('label');
					lbl.innerHTML = value['label']+' ';
					objTr.appendChild(objTd1);
					objTd1.appendChild(lbl);
					
					txt = document.createElement('br');
					lbl.appendChild(txt);

					
					element=document.createElement('input');
					element.id=i;
					//element.id=value['label_enco'];
					element.name=value['label_enco'];
					element.value=value['data'];
					if ((value['type'] == 'text') || (value['type'] == 'external')) {
						element.type='text';

					} else if (value['type'] == 'numeric') {
						element.type='number';
					} 
					
					element.size=40;
					lbl.appendChild(element);
				
					if (value['type'] == 'external') {
						
						id_object_type_field = value['id'];
						
						a = document.createElement('a');
						a.title = "Show table";
						table_name = value['external_table_name'];
						id_table = value['external_reference_field'];
						//element_name = value['label_enco'];
						//a.href = 'javascript: show_external_query("'+table_name+'","'+id_table+'","'+element_name+'")';
						a.href = 'javascript: show_external_query("'+table_name+'","'+id_table+'","'+i+'", "'+id_object_type_field+'")';
						
						img=document.createElement('img');
						img.id='img_show_external_table';
						img.height='16';
						img.width='16';
						img.src='images/lupa.gif';
						
						a.appendChild(img);
						lbl.appendChild(a);
						
						id_inventory = $('#text-id_object_hidden').val();
					}
					
					i++;
					
					if (value['type'] == 'external') {
						if (value['data'] != '') {
							
							external_table_name = value['external_table_name'];
							external_reference_field = value['external_reference_field'];
							id_external_table = value['data'];
							
							$.ajax({
								type: "POST",
								url: "ajax.php",
								data: "page=operation/inventories/inventory_detail&show_external_data=1&external_table_name=" + external_table_name +"&external_reference_field=" + external_reference_field +'&id_external_table='+id_external_table, 
								dataType: "json",
								success: function(data_external){
									resto_ext = 0;
									
									jQuery.each (data_external, function (id_ext, value_ext) {
										resto_ext = i % 2;
										
										if (resto_ext == 0) {
											var objTr = document.createElement("tr"); //create row
											objTr.id = 'new_row_'+i;
											objTr.width='98%';
											table.appendChild(objTr);
										} else {
											pos = i-1;
											objTr = document.getElementById('new_row_'+pos);
										}
										
										var objTd1 = document.createElement("td"); //create column for label
										objTd1.width='50%';
										lbl = document.createElement('label');
										lbl.innerHTML = value_ext['label']+' ';
										objTr.appendChild(objTd1);
										objTd1.appendChild(lbl);
										
										txt = document.createElement('br');
										lbl.appendChild(txt);

										
										element=document.createElement('input');
										element.id=value_ext['label'];
										element.name=value_ext['label_enco'];
										element.value=value_ext['data'];
										element.type='text';
										element.readOnly=true
										
										element.size=40;
										lbl.appendChild(element);
										i++;
									});
								}
							});
						}
					}
				}
				
				if ((value['type'] == "textarea")) {
					
					if (resto == 0) {
						var objTr = document.createElement("tr"); //create row
						objTr.id = 'new_row_'+i;
						table.appendChild(objTr);
					} else {
						pos = i-1;
						objTr = document.getElementById('new_row_'+pos);
					}
					
					var objTd1 = document.createElement("td"); //create column for label
					
					lbl = document.createElement('label');
					lbl.innerHTML = value['label']+' ';
					objTr.appendChild(objTd1);
					objTd1.appendChild(lbl);
					
					element=document.createElement("textarea");
					element.id=value['label'];
					element.name=value['label_enco'];
					element.value=value['data'];
					element.type='text';
					element.rows='3';
					
					lbl.appendChild(element);
					i++;
				}

			});
		}
	});
}

// Show the modal window of external table
function show_external_query(table_name, id_table, element_name, id_object_type_field) {

	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/inventories&get_external_data=1&table_name="+table_name+"&id_table="+id_table+"&element_name="+element_name+"&id_object_type_field="+id_object_type_field,
		dataType: "html",
		success: function(data){	
			$("#external_table_window").html (data);
			$("#external_table_window").show ();

			$("#external_table_window").dialog ({
					resizable: true,
					draggable: true,
					modal: true,
					overlay: {
						opacity: 0.5,
						background: "black"
					},
					width: 620,
					height: 500
				});
			$("#external_table_window").dialog('open');
		}
	});
}

function refresh_external_id(id_object_type_field, id_inventory, id_value) {
	value_id = $('#'+id_value).val();

	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=operation/inventories/inventory_detail&update_external_id=1&id_object_type_field=" + id_object_type_field +"&id_inventory=" + id_inventory+ "&id_value="+value_id, 
		dataType: "html",
		success: function(data){
			show_fields();
		}
	});

}

function enviar(data, element_name, id_object_type_field) {

	//$('#'+element_name.id).val(data);
	$('#'+element_name).val(data);
	
	id_inventory = $('#text-id_object_hidden').val();
	
	if (id_inventory != 0) {
		refresh_external_id(id_object_type_field, id_inventory, element_name);
		$("#external_table_window").dialog('close');
	}

} 

</script>
<?php //endif; ?>
