<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

check_login ();

if (! give_acl ($config["id_user"], 0, "PM")) {
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access company section");
	require ("general/noaccess.php");
	exit;
}

$id = (int) get_parameter ('id');
$new_type = (bool) get_parameter ('new_type');
$create_type = (bool) get_parameter ('create_type', 0);
$update_type = (bool) get_parameter ('update_type', 0);
$delete_type = (bool) get_parameter ('delete_type', 0);

$show_fields = false; //show fields of incident type
$add_field = (int) get_parameter ('add_field', 0);
$delete_field = (int) get_parameter ('delete_field', 0);
$update_field = (int) get_parameter ('update_field', 0);

if ($add_field) { //add field to incident type
	$value['id_incident_type'] = (int) get_parameter ('id', 0);
	$value['label'] = get_parameter('label', '');
	$value['type'] = get_parameter ('type');
	$value['combo_value'] = get_parameter ('combo_value', '');
	$error_combo = false;
	
	if ($value['type'] == 'combo') {
		if ($value['combo_value'] == '')
			$error_combo = true;
	}
	
	if ($value['label'] == '') {
		echo '<h3 class="error">'.__('Empty field name').'</h3>';
	} else if ($error_combo) {
		echo '<h3 class="error">'.__('Empty combo value').'</h3>';
	} else {

		$result_field = process_sql_insert('tincident_type_field', $value);
		
		if ($result_field === false) {
			echo '<h3 class="error">'.__('Field could not be created').'</h3>';
		} else {
			echo '<h3 class="suc">'.__('Field created successfully').'</h3>';
		}
	}
}

if ($delete_field) {
	$id_field = get_parameter ('id_field');
	
	$result_delete = process_sql_delete('tincident_type_field', array('id' => $id_field));
	
	if ($result_delete === false) {
		echo '<h3 class="error">'.__('Field could not be deleted').'</h3>';
	} else {
		echo '<h3 class="suc">'.__('Field deleted successfully').'</h3>';
	}
}

if ($update_field) { //update field to incident type
	$id_field = get_parameter ('id_field');
	
	$value_update['label'] = get_parameter('label');
	$value_update['type'] = get_parameter ('type');
	$value_update['combo_value'] = get_parameter ('combo_value', '');
	$error_update = false;

	if ($value_update['type'] == "combo") {
		if ($value_update['combo_value'] == '') 
			$error_update = true;
	} 
	if ($error_update) {
		echo '<h3 class="error">'.__('Field could not be updated. Empty combo value').'</h3>';
	} else {
		$result_update = process_sql_update('tincident_type_field', $value_update, array('id' => $id_field));
		
		if ($result_update === false) {
			echo '<h3 class="error">'.__('Field could not be updated').'</h3>';
		} else {
			echo '<h3 class="suc">'.__('Field updated successfully').'</h3>';
		}
	}
}

if ($id != 0) {
	$show_fields = true;
}

// CREATE
if ($create_type) {

	$values['name'] = (string) get_parameter ('name');
	$values['description'] = (string) get_parameter ('description');
	//$values['id_wizard'] = (int) get_parameter ('wizard');
	//$values['id_group'] = (int) get_parameter ('id_group');
	
	if ($values['name'] != "") {

		$id = process_sql_insert('tincident_type', $values);	
		if ($id === false) {
			echo '<h3 class="error">'.__('Could not be created').'</h3>';
		} else {
			$show_fields = true;
			echo '<h3 class="suc">'.__('Successfully created').'</h3>';
			insert_event ("INCIDENT TYPE CREATED", $id, 0, $values['name']);
		}
	} else {
		echo '<h3 class="error">'.__('Type name empty').'</h3>';
	}
	//$id = 0;
}

// UPDATE
if ($update_type) {

	$values['name'] = (string) get_parameter ('name');
	$values['description'] = (string) get_parameter ('description');
	//$values['id_wizard'] = (int) get_parameter ('wizard');
	//$values['id_group'] = (int) get_parameter ('id_group');

	if ($values['name'] != "") {
		$result = process_sql_update('tincident_type', $values, array('id' => $id));

		if ($result === false)
			echo '<h3 class="error">'.__('Could not be updated').'</h3>';
		else {
			echo '<h3 class="suc">'.__('Successfully updated').'</h3>';
			insert_event ("INCIDENT TYPE", $id, 0, $name);
			$show_fields = true;
		}
	} else {
		echo '<h3 class="error">'.__('Type name empty').'</h3>';
	}
	
	//$id = 0;
}

// DELETE
if ($delete_type) {
	$name = get_db_value ('name', 'tincident_type', 'id', $id);
	$sql = sprintf ('DELETE FROM tincident_type WHERE id = %d', $id);
	process_sql ($sql);
	insert_event ("INCIDENT TYPE DELETED", $id, 0, $name);
	echo '<h3 class="suc">'.__('Successfully deleted').'</h3>';
	$id = 0;
}

echo '<h2>'.__('Incident types').'</h2>';

// FORM (Update / Create)
if ($id || $new_type) {
	if ($new_type) {
		$id = 0;
		$name = "";
		$description = "";
		//$id_wizard = "";
		//$id_group = "";
	} else {
		$type = get_db_row ('tincident_type', 'id', $id);
		$name = $type['name'];
		$description = $type['description'];
		//$id_wizard = $type['id_wizard'];
		//$id_group = $type['id_group'];
	}
	
	$table->width = "90%";
	$table->class = "databox";
	$table->data = array ();
	$table->colspan = array ();
	//$table->colspan[2][0] = 2;
	
	$table->data[0][0] = print_input_text ('name', $name, '', 40, 100, true, __('Type name'));
/*
	$table->data[0][1] = print_select_from_sql ('SELECT id, name FROM twizard ORDER BY name',
		'id_wizard', $id_wizard, '', 'Select', 0, true, false, false, __('Wizard'));
	$table->data[1][0] = print_select_from_sql ('SELECT id_grupo, nombre FROM tgrupo ORDER BY nombre',
		'id_group', $id_group, '', 'Select', 0, true, false, false, __('Group'));
*/
	$table->data[2][0] = print_textarea ('description', 3, 1, $description, '', true, __('Description'));
	
	echo '<form id="form-type_detail" method="post" action="index.php?sec=incidents&sec2=operation/incidents/type_detail">';
	print_table ($table);
	
	echo '<div class="button" style="width: '.$table->width.'">';
	if ($id) {
		print_submit_button (__('Update'), 'update_btn', false, 'class="sub upd"', false);
		print_input_hidden ('update_type', 1);
		print_input_hidden ('id', $id);
	} else {
		print_submit_button (__('Create'), 'create_btn', false, 'class="sub next"', false);
		print_input_hidden ('create_type', 1);
	}
	echo '</div>';
	echo '</form>';
	
	echo '<br>';
	if ($show_fields) {
		//FIELD MANAGEMENT
		echo "<h2>".__("Incident fields")."</h2>";
		
		//INCIDENT FIELDS
		$incident_fields = get_db_all_rows_filter ("tincident_type_field", array("id_incident_type" => $id));
		if ($incident_fields === false) {
			$incident_fields = array ();
		}

		//ALL FIELDS
		$all_fields = array();
		foreach ($incident_fields as $field) {
			$all_fields[$field['id']] = $field['label'];
		}

		$table->width = '90%';
		$table->data = array ();
		$table->head = array();
		$table->style = array();
		$table->size = array ();
		$table->size[0] = '30%';
		$table->size[1] = '30%';
		$table->size[2] = '30%';

		$table->head[0] = __("Name field");
		$table->head[1] = __("Type");
		$table->head[2] = __("Value");
		$table->head[3] = __("Action");

		$data = array();

		if (!empty($incident_fields)) {
			foreach ($incident_fields as $field) {
				$url_delete = "index.php?sec=incidents&sec2=operation/incidents/type_detail&delete_field=1&id=$id&id_field=".$field['id'];
				$url_update = "index.php?sec=incidents&sec2=operation/incidents/incident_type_field&update_field=1&id=$id&id_field=".$field['id'];
				
				if ($field['label'] == '') {
					$data[0] = '';
				} else {
					$data[0] = $field["label"];
				}
				
				if ($field_type = '') {
					$data[1] = '';
				} else {
					$data[1] = $field["type"];
				}
				
				if ($field["type"] == "combo") {
					$data[2] = $field["combo_value"];
				} else {
					$data[2] = "";
				}
				
				$data[3] = "<a
				href='" . $url_update . "'>
				<img src='images/config.gif' border=0 /></a>";
				$data[3] .= "<a
				onclick=\"if (!confirm('" . __('Are you sure?') . "')) return false;\" href='" . $url_delete . "'>
				<img src='images/cross.png' border=0 /></a>";
				
				array_push ($table->data, $data);
			}
			print_table($table);
		} else {
			echo "<h4>".__("No fields")."</h4>";
		}

		echo "<form id='form-add_field' name=dataedit method=post action='index.php?sec=incidents&sec2=operation/incidents/incident_type_field&add_field=1&id=".$id.">'";
			echo '<div class="button" style="width: '.$table->width.'">';
				print_submit_button (__('Add field'), 'create_btn', false, 'class="sub next"', false);
			echo '</div>';
		echo "</form>";
		
	}
//LISTADO GENERAL	
} else {
	$search_text = (string) get_parameter ('search_text');
	
	$where_clause = '';
	if ($search_text != "") {
		$where_clause .= sprintf ('WHERE name LIKE "%%%s%%"', $search_text);
	}

	$table->width = '90%';
	$table->class = 'search-table';
	$table->style = array ();
	$table->style[0] = 'font-weight: bold;';
	$table->data = array ();
	$table->data[0][0] = __('Search');
	$table->data[0][1] = print_input_text ("search_text", $search_text, "", 25, 100, true);
	$table->data[0][2] = print_submit_button (__('Search'), "search_btn", false, 'class="sub search"', true);;
	
	echo '<form method="post">';
	print_table ($table);
	echo '</form>';

	$sql = "SELECT * FROM tincident_type $where_clause ORDER BY name";
	$types = get_db_all_rows_sql ($sql);
	
	if ($types !== false) {
		$table->width = '90%';
		$table->class = 'listing';
		$table->data = array ();
		$table->size = array ();
		$table->size[2] = '40px';
		$table->align = array ();
		$table->align[2] = 'center';
		$table->style = array ();
		$table->style[0] = 'font-weight: bold';
		$table->head[0] = __('Name');
		$table->head[1] = __('Wizard');
		$table->head[2] = __('Delete');
		
		foreach ($types as $type) {
			$data = array ();
			
			$data[0] = '<a href="index.php?sec=incidents&sec2=operation/incidents/type_detail&id='.
				$type['id'].'">'.$type['name'].'</a>';
			$data[1] = get_db_value ('name', 'tincident_type', 'id', $type['id_wizard']);
			$data[2] = '<a href="index.php?sec=incidents&
						sec2=operation/incidents/type_detail&
						delete_type=1&id='.$type['id'].'"
						onClick="if (!confirm(\''.__('Are you sure?').'\'))
						return false;">
						<img src="images/cross.png"></a>';
			array_push ($table->data, $data);
		}
		print_table ($table);
	}
	
	echo '<form method="post" action="index.php?sec=incidents&sec2=operation/incidents/type_detail">';
	echo '<div class="button" style="width: '.$table->width.'">';
	print_submit_button (__('Create type'), 'new_btn', false, 'class="sub next"');
	print_input_hidden ('new_type', 1);
	echo '</div>';
	echo '</form>';
}

?>

<script type="text/javascript" src="include/js/jquery.validate.js"></script>
<script type="text/javascript" src="include/js/jquery.validation.functions.js"></script>
<script type="text/javascript" >
// Form validation
trim_element_on_submit('#text-search_text');
// Form: #form-type_detail
trim_element_on_submit('#text-name');
validate_form("#form-type_detail");
// Rules: #text-name
var name_rules = {
	required: true,
	remote: {
		url: "ajax.php",
        type: "POST",
        data: {
			page: "include/ajax/remote_validations",
			search_existing_type: 1,
			type_name: function() { return $('#text-name').val() },
			type_id: "<?=$id?>"
        }
	}
};
var name_messages = {
	required: "<?=__('Name required')?>",
	remote: "<?=__('This name already exists')?>"
};
add_validate_form_element_rules('#text-name', name_rules, name_messages);

</script>
