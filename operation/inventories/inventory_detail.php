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

$id = (int) get_parameter ('id');

if (defined ('AJAX')) {
	
	global $config;
	
	$show_type_fields = (bool) get_parameter('show_type_fields', 0);
	$show_external_data = (bool) get_parameter('show_external_data', 0);
	$update_external_id = (bool) get_parameter('update_external_id', 0);
	$get_company_name = (bool) get_parameter('get_company_name', 0);
	$get_user_name = (bool) get_parameter('get_user_name', 0);
 	
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
	
	if ($get_company_name) {
		$id_company = get_parameter('id_company');
		$name = get_db_value('name', 'tcompany', 'id', $id_company);

		echo json_encode($name);
		return;
	}
	
	if ($get_user_name) {
		$id_user = get_parameter('id_user');
		$name = get_db_value('nombre_real', 'tusuario', 'id_usuario', $id_user);

		echo json_encode($name);
		return;
	}
}

enterprise_include('include/functions_inventory.php');

$read_permission = enterprise_hook ('inventory_check_acl', array ($config['id_user'], $id));
$write_permission = enterprise_hook ('inventory_check_acl', array ($config['id_user'], $id, true));

if ($read_permission === ENTERPRISE_NOT_HOOK) {
	$read_permission = true;
	$write_permission = true;
} else {
	if (!$read_permission) {
		include ("general/noaccess.php");
		exit;
	}
}

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
	echo "<h3>".__('Object')." #$id"."&nbsp;&nbsp;-&nbsp;".$inventory_name."</h3>";
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
			echo '<li class="ui-tabs"><a href="index.php?sec=inventory&sec2=operation/inventories/inventory_incidents&id=' . $id . '"><span>'.__('Incidents').'</span></a></li>';
			echo '<li class="ui-tabs"><a href="index.php?sec=inventory&sec2=operation/inventories/inventory_contacts&id=' . $id . '"><span>'.__('Contacts').'</span></a></li>';
			echo '<li class="ui-tabs"><a href="index.php?sec=inventory&sec2=operation/inventories/inventory_tracking&id=' . $id . '"><span>'.__('Tracking').'</span></a></li>';

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

if ((isset($_POST['parent_name'])) && ($_POST['parent_name'] == '')) {
	$id_parent = 0;
}

if ($update) {
	
	if (!$write_permission) {
		audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to update inventory #".$id);
		include ("general/noaccess.php");
		exit;
	}
	
	$old_parent = get_db_value('id_parent', 'tinventory', 'id', $id);
	$old_owner = get_db_value('owner', 'tinventory', 'id', $id);
	$old_public = get_db_value('public', 'tinventory', 'id', $id);
	
	$sql = sprintf ('UPDATE tinventory SET name = "%s", description = "%s",
			id_contract = %d,
			id_parent = %d, id_manufacturer = %d, owner = "%s", public = %d, id_object_type = %d
			WHERE id = %d',
			$name, $description, $id_contract,
			$id_parent,
			$id_manufacturer, $owner, $public, $id_object_type, $id);
	$result = process_sql ($sql);
	
	
	if ($result !== false) {
		inventory_tracking($id,INVENTORY_UPDATED);
		
		if ($owner != $old_owner) {
			inventory_tracking($id,INVENTORY_OWNER_CHANGED, $owner);
		}
		if ($public != $old_public) {
			if ($public)
				inventory_tracking($id,INVENTORY_PUBLIC);
			else 
				inventory_tracking($id,INVENTORY_PRIVATE);
		}
	}
	
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
		
		inventory_tracking($id,INVENTORY_OBJECT_TYPE, $id_object_type);
	}

	//parent
	if ($id_parent != 0) {	
		if ($old_parent != false) {
			//delete fields old parent
			$old_id_object_type_inherit = get_db_value('id_object_type', 'tinventory', 'id', $old_parent);
			//parent has object
			if ($old_id_object_type_inherit !== false) {
				$old_fields = get_db_all_rows_filter('tobject_type_field', array('id_object_type'=>$old_id_object_type_inherit, 'inherit' => 1));

				if ($old_fields === false) {
					$old_fields = array();
				}
				foreach ($old_fields as $key => $old) {
					process_sql_delete('tobject_field_data', array('id_object_type_field' => $old['id'], 'id_inventory' => $id));
				}
			}
			inventory_tracking($id,INVENTORY_PARENT_UPDATED, $id_parent);
		}
		
		inventory_tracking($id,INVENTORY_PARENT_CREATED, $id_parent);
		
		$id_object_type_inherit = get_db_value('id_object_type', 'tinventory', 'id', $id_parent);

		//parent has object
		if ($id_object_type_inherit !== false) {
			$inherit_fields = get_db_all_rows_filter('tobject_type_field', array('id_object_type'=>$id_object_type_inherit, 'inherit' => 1));
		
			if ($inherit_fields === false) {
				$inherit_fields = array();
			}

			foreach ($inherit_fields as $key=>$field) {
				$values = array();
				$values['id_object_type_field'] = $field['id'];
				$values['id_inventory'] = $id;
				$data = get_db_value_filter('data', 'tobject_field_data', array('id_inventory' => $id_parent, 'id_object_type_field' => $field['id']));
				$values['data'] = $data;

				process_sql_insert('tobject_field_data', $values);
			}
		}
	}
	
	$inventory_companies = get_parameter("companies");
	$result_hook = enterprise_hook ('inventory_get_user_inventories', array (get_parameter ('companies', $inventory_companies), true));
	
	if ($result_hook !== ENTERPRISE_NOT_HOOK) {
		$inventory_users = get_parameter("users");
			
		// Update users in inventory 
		enterprise_hook ('inventory_update_users', array ($id, get_parameter ('users', $inventory_users), true));
	}
	
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
	
	if (!$write_permission) {
		audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create inventory #".$id);
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
		
		inventory_tracking($id,INVENTORY_CREATED);
				
		if ($public)
				inventory_tracking($id,INVENTORY_PUBLIC);
			else 
				inventory_tracking($id,INVENTORY_PRIVATE);
		
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
			
			inventory_tracking($id,INVENTORY_OBJECT_TYPE, $id_object_type);
		}
		
		//parent
		if ($id_parent != 0) {
			$id_object_type_inherit = get_db_value('id_object_type', 'tinventory', 'id', $id_parent);

			//parent has object
			if ($id_object_type_inherit !== false) {
				$inherit_fields = get_db_all_rows_filter('tobject_type_field', array('id_object_type'=>$id_object_type_inherit, 'inherit' => 1));
			
				if ($inherit_fields === false) {
					$inherit_fields = array();
				}
				
				foreach ($inherit_fields as $key=>$field) {
					$values = array();
					$values['id_object_type_field'] = $field['id'];
					$values['id_inventory'] = $id;
					$data = get_db_value_filter('data', 'tobject_field_data', array('id_inventory' => $id_parent, 'id_object_type_field' => $field['id']));
					$values['data'] = $data;
	
					process_sql_insert('tobject_field_data', $values);
				}
			}
			
			inventory_tracking($id,INVENTORY_PARENT_CREATED, $id_parent);
		}
		
		$result_companies = enterprise_hook ('inventory_update_companies', array ($id, get_parameter ('companies')));
		$result_users = enterprise_hook ('inventory_update_users', array ($id, get_parameter ('users')));

		
		$result_msg = '<h3 class="suc">'.__('Successfully created').'</h3>';

		$result_msg .= "<h3><a href='index.php?sec=inventory&sec2=operation/inventories/inventory_detail&id=$id'>".__("Click here to continue working with Object #").$id."</a></h3>";

	} else {
		$result_msg = '<h3 class="error">'.$err_message.'</h3>';
	}
	
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


if ($id) {

	if (!$read_permission) {
		audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access inventory #".$id);
		include ("general/noaccess.php");
		exit;
	}
	
	clean_cache_db();
	
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

if ($write_permission) {
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

if ($write_permission) {
	$table->data[0][1] = user_print_autocomplete_input($params_assigned);
} else {
	$table->data[0][1] = print_label (__('Owner'), '', '', true, $owner);
}
	
$table->data[0][2] = print_checkbox_extended ('public', 1, $public,
	! $write_permission, '', '', true, __('Public'));


if ($write_permission) {
	
	$parent_name = $id_parent ? get_inventory_name ($id_parent) : __("None");
	
	$table->data[1][0] = print_input_text_extended ("parent_name", $parent_name, "text-parent_name", '', 20, 0, false, "show_inventory_search('','','','','','')", "class='inventory_obj_search'", true, false,  __('Parent object'));
	$table->data[1][0] .= print_image("images/cross.png", true, array("onclick" => "cleanParentInventory()", "style" => "cursor: pointer"));	
	$table->data[1][0] .= print_input_hidden ('id_parent', $id_parent, true);

} else {
	$parent_name = $id_parent ? get_inventory_name ($id_parent) : __('Not set');
	
	$table->data[1][0] = print_label (__('Parent object'), '', '', true, $parent_name);
	if ($id_parent)
		$table->data[1][0] .= '<a href="index.php?sec=inventory&sec2=operation/inventories/inventory_detail&id='.$id_parent.'"><img src="images/go.png" /></a>';
}

$contracts = get_contracts ();
$manufacturers = get_manufacturers ();

if ($write_permission) {
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

if ($id_object_type == 0) {
	$disabled = false;
} else {
	$disabled = true;
}

if ($write_permission) {
	$table->data[2][0] = print_label (__('Object type'), '','',true);
	$table->data[2][0] .= print_select($objects_type, 'id_object_type', $id_object_type, 'show_fields();', 'Select', '', true, 0, true, false, $disabled);
} else {
	$object_name = get_db_value('name', 'tobject_type', 'id', $id_object_type);
	$table->data[2][0] = print_label (__('Object type'), '', '', true, $object_name);
	
	//show object hidden
	echo '<div id="show_object_fields_hidden" style="display:none;">';
		print_input_text('show_object_hidden', 1);
	echo '</div>';
	
	//id_object_type hidden
	echo '<div id="id_object_type_hidden" style="display:none;">';
		print_input_text('id_object_type_hidden', $id_object_type);
	echo '</div>';
}

$companies = array();
$users = array();

if ($id) {
	$companies = enterprise_hook ('inventory_get_companies', array ($id));
	
	if ($companies !== ENTERPRISE_NOT_HOOK) {
		$users = enterprise_hook ('inventory_get_users', array ($id, get_parameter ('users')));
	}
}

if ($write_permission) {
		$table->data[2][1] = print_select ($companies, 'inventory_companies', NULL,
								'', '', '', true, false, false, __('Associated company'));
		$table->data[2][1] .= "&nbsp;&nbsp;<a href='javascript: show_company_associated();'>".__('Add')."</a>";
		$table->data[2][1] .= "&nbsp;&nbsp;<a href='javascript: removeCompany();'>".__('Remove')."</a>";

		$table->data[2][2] = print_select ($users, 'inventory_users', NULL,
								'', '', '', true, false, false, __('Associated user'));
		$table->data[2][2] .= "&nbsp;&nbsp;<a href='javascript: show_user_associated(\"\",\"\",\"\",\"\",\"\",\"\");'>".__('Add')."</a>";
		$table->data[2][2] .= "&nbsp;&nbsp;<a href='javascript: removeUser();'>".__('Remove')."</a>";

		foreach ($companies as $company_id => $company_name) {
			$table->data[2][1] .= print_input_hidden ("companies[]",
								$company_id, true, 'selected-companies');
		}

		foreach ($users as $user_id => $user_name) {
			$table->data[2][2] .= print_input_hidden ("users[]",
								$user_id, true, 'selected-users');
		}
	} else {
		$table->data[2][1] = print_select ($companies, 'inventory_companies', NULL,
								'', '', '', true, false, false, __('Associated company'));
								
		$table->data[2][2] = print_select ($users, 'inventory_users', NULL,
								'', '', '', true, false, false, __('Associated user'));
	}
	
/* Fourth row */
$table->colspan[3][0] = 3;		
$table->data[3][0] = "";


/* Fifth row */
$table->data[4][1] = "</div>&nbsp;";

/* Sixth row */
$disabled_str = ! $write_permission ? 'readonly="1"' : '';
$table->data[5][0] = print_textarea ('description', 15, 100, $description,
	$disabled_str, true, __('Description'));

echo '<div class="result">'.$result_msg.$msg_err.'</div>';

if ($write_permission) {
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

echo "<div class= 'dialog ui-dialog-content' id='inventory_search_window'></div>";

echo "<div class= 'dialog ui-dialog-content' id='company_search_modal'></div>";

echo "<div class= 'dialog ui-dialog-content' id='user_search_modal'></div>";


//if (! defined ('AJAX')):
?>

<script type="text/javascript" src="include/js/jquery.metadata.js"></script>
<script type="text/javascript" src="include/js/integria_incident_search.js"></script>
<script type="text/javascript" src="include/js/integria_inventory.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.autocomplete.js"></script>

<script type="text/javascript">

$(document).ready (function () {
	
	configure_inventory_form (false);

	if ($("#text-show_object_hidden").val() == 1) { //user with only read permissions
		show_fields();
	} else {
		if ($("#id_object_type").val() != 0) {
			show_fields();
		}
	}

	$("form.delete").submit (function () {
		if (! confirm ("<?php echo __('Are you sure?'); ?>"))
			return false;
	});
	
	var idUser = "<?php echo $config['id_user'] ?>";
	
	bindAutocomplete ("#text-owner", idUser);	
	//bindAutocomplete ("#text-owner_search", idUser);
	
});

// Form validation
trim_element_on_submit('#text-name');
validate_form("#inventory_status_form");
var rules, messages;
// Rules: #text-name
rules = {
	required: true,
	remote: {
		url: "ajax.php",
        type: "POST",
        data: {
			page: "include/ajax/remote_validations",
			search_existing_object: 1,
			object_name: function() { return $('#text-name').val() },
			object_id: "<?php echo $id?>"
        }
	}
};
messages = {
	required: "<?php echo __('Name required')?>",
	remote: "<?php echo __('This object already exists')?>"
};
add_validate_form_element_rules('#text-name', rules, messages);

</script>

<?php //endif; ?>
