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

require_once ('include/functions_incidents.php');

if (! give_acl ($config["id_user"], 0, "IM")) {
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access ticket type section");
	require ("general/noaccess.php");
	exit;
}

$id = (int) get_parameter ('id');
$new_type = (bool) get_parameter ('new_type');
$create_type = (bool) get_parameter ('create_type', 0);
$update_type = (bool) get_parameter ('update_type', 0);
$delete_type = (bool) get_parameter ('delete_type', 0);
$sort_items = (bool) get_parameter('sort_items', 0);

$show_fields = false; //show fields of incident type
$add_field = (int) get_parameter ('add_field', 0);
$delete_field = (int) get_parameter ('delete_field', 0);
$update_field = (int) get_parameter ('update_field', 0);

if ($add_field) { //add field to incident type
	
	$global = get_parameter("global");

	$value['id_incident_type'] = (int) get_parameter ('id', 0);
	$value['label'] = get_parameter('label', '');
	$value['type'] = get_parameter ('type');
	$value['combo_value'] = get_parameter ('combo_value', '');
	$value['show_in_list'] = (int) get_parameter ('show_in_list');
	$value['linked_value'] = get_parameter ('linked_value', '');
	$value['parent'] = get_parameter ('parent', '');
	$last_order = get_db_value_sql("SELECT MAX(`order`) FROM tincident_type_field WHERE id_incident_type = ".$value['id_incident_type']);
	$value['order'] = $last_order + 1;
	
	$error_combo = false;
	$error_linked = false;
	
	if ($value['type'] == 'combo') {
		if ($value['combo_value'] == '')
			$error_combo = true;
	}
	
	if ($value['type'] == 'linked') {
		if ($value['linked_value'] == '')
			$error_linked = true;
	}
	
	if ($value['label'] == '') {
		echo '<h3 class="error">'.__('Empty field name').'</h3>';
	} else if ($error_combo) {
		echo '<h3 class="error">'.__('Empty combo value').'</h3>';
	} else if ($error_linked) {
		echo '<h3 class="error">'.__('Empty linked value').'</h3>';
	} else if (!$value["type"]) {
		echo '<h3 class="error">'.__('No type selected').'</h3>';
	} else {

		$result_field = process_sql_insert('tincident_type_field', $value);
		
		if ($result_field === false) {
			echo '<h3 class="error">'.__('Field could not be created').'</h3>';
		} else {

			//Global fields are inserted in all types
			if ($global) {
			
				//Update field $result_field to get global_id = $result_field
				$sql = sprintf("UPDATE tincident_type_field SET global_id = %d WHERE id = %d", 
							$result_field, $result_field);

				$res = process_sql($sql);

				//Insert global field in the rest of types
				$sql_types = sprintf("SELECT id, name FROM tincident_type WHERE id != %d", $id);

				$types = get_db_all_rows_sql($sql_types);

				if (!$types) {
					$types = array();
				}

				//Add global field id
				$value['global_id'] = $result_field;

				foreach ($types as $t) {

					$value['id_incident_type'] = $t["id"];
				
					$res = process_sql_insert('tincident_type_field', $value);

					if (!$res) {
						echo '<h3 class="error">'.__('There was a problem creating global field for type could not be created for type: ')." ".$t["name"].'</h3>';
					}
				}
			}

			echo '<h3 class="suc">'.__('Field created successfully').'</h3>';
		}
	}
}

if ($delete_field) {
	$id_field = get_parameter ('id_field');

	$global_id = get_db_value("global_id", "tincident_type_field", "id", $id_field);

	if ($global_id) {
		//Delete all fields related to global field
		$fields_sql = sprintf("SELECT id FROM tincident_type_field WHERE global_id = %d", $global_id);
		$fields = get_db_all_rows_sql($fields_sql);

		$aux = array();
		foreach($fields as $f) {
			$aux[] = $f["id"];
		}

		$clause = "(".implode(",", $aux).")";

		$sql = sprintf("DELETE FROM tincident_type_field WHERE id IN %s", $clause);
		$result_delete = process_sql($sql);
	} else {
		//Delete only this field
		$result_delete = process_sql_delete('tincident_type_field', array('id' => $id_field));
	}
	
	
	
	if ($result_delete === false) {
		echo '<h3 class="error">'.__('Field could not be deleted').'</h3>';
	} else {
		echo '<h3 class="suc">'.__('Field deleted successfully').'</h3>';
	}
}

if ($update_field) { //update field to incident type
	$id_field = (int)get_parameter ('id_field');
	// The item should be updated to global
	$new_global = (bool) get_parameter("global");
	// The item is global yet
	$is_global = get_db_value('global_id', 'tincident_type_field','id', $id_field);
	
	$value_update['label'] = get_parameter('label');
	$value_update['type'] = get_parameter ('type');
	$value_update['combo_value'] = get_parameter ('combo_value', '');
	$value_update['show_in_list'] = (int) get_parameter ('show_in_list');
	$value_update['linked_value'] = get_parameter ('linked_value', '');
	$value_update['parent'] = get_parameter ('parent', '');
	$add_linked_value = get_parameter('add_linked_value', '');
	$add_combo_value = get_parameter('add_combo_value', '');
	$error_combo_update = false;
	$error_linked_update = false;

	if ($value_update['type'] == "combo") {
		if ($value_update['combo_value'] == '') 
			$error_combo_update = true;
	}
	
	if ($value_update['type'] == "linked") {
		if ($value_update['linked_value'] == '') 
			$error_linked_update = true;
	} 
	
	if ($error_combo_update) {
		echo '<h3 class="error">'.__('Field could not be updated. Empty combo value').'</h3>';
	} else if ($error_linked_update) {
		echo '<h3 class="error">'.__('Field could not be updated. Empty linked value').'</h3>';
	
	} else {
		if ($is_global) {
			if ($add_linked_value != "") {
				$old_linked_value = get_db_value('linked_value', 'tincident_type_field', 'id', $id_field);
				$value_update = array();
				$value_update['linked_value'] = $old_linked_value.','.$add_linked_value;
				$result_update = process_sql_update('tincident_type_field', $value_update, array('id' => $id_field));
			}
			if ($add_combo_value != "") {
				$old_combo_value = get_db_value('combo_value', 'tincident_type_field', 'id', $id_field);
				$value_update = array();
				$value_update['combo_value'] = $old_combo_value.','.$add_combo_value;
				$result_update = process_sql_update('tincident_type_field', $value_update, array('id' => $id_field));
			}

			if ($result_update) {
				//Global fields are inserted in all types
				if ($is_global) {
					//Insert global field in the rest of types
					$sql_types = sprintf("SELECT id, name FROM tincident_type WHERE id != %d", $id);

					$types = get_db_all_rows_sql($sql_types);

					if (!$types) {
						$types = array();
					}

					foreach ($types as $t) {
						$res = process_sql_update('tincident_type_field', $value_update, array('id_incident_type' => $t['id'],'global_id'=>$is_global));
						if (!$res) {
							echo '<h3 class="error">'.__('There was a problem updating global field for type: ')." ".$t["name"].'</h3>';
						}
					}
				}
			}

		} else {
			//Add global field id
			if ($new_global)
				$value_update['global_id'] = $id_field;
				
			$result_update = process_sql_update('tincident_type_field', $value_update, array('id' => $id_field));
			
			// Global fields are inserted in all types
			if ($result_update !== false && $new_global) {
				$sql = sprintf('SELECT id, name
								FROM tincident_type
								WHERE id <> %d', $id);
				$types = get_db_all_rows_sql($sql);
				if (empty($types)) $types = array();
				
				foreach ($types as $type) {

					$value_update['id_incident_type'] = $type["id"];
				
					$res = process_sql_insert('tincident_type_field', $value_update);

					if (empty($res))
						echo '<h3 class="error">'.__('There was a problem creating global field for type could not be created for type: ')." ".$type["name"].'</h3>';
				}
			}
		}		
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
	$values['id_group'] = (string) get_parameter ('id_group');
	//$values['id_wizard'] = (int) get_parameter ('wizard');
	
	if ($values['name'] != "") {

		$id = process_sql_insert('tincident_type', $values);	
		if ($id === false) {
			echo '<h3 class="error">'.__('Could not be created').'</h3>';
		} else {
			$show_fields = true;
			echo '<h3 class="suc">'.__('Successfully created').'</h3>';
			audit_db ($config["id_user"], $config["REMOTE_ADDR"], "Ticket Management", "Created ticket type $id - ".$values['name']);
		}
	} else {
		echo '<h3 class="error">'.__('Type name empty').'</h3>';
	}
	//$id = 0;
	
	$sql_global_ids = "SELECT DISTINCT (global_id)
				FROM tincident_type_field
				WHERE global_id != 0";
				
	$global_ids = get_db_all_rows_sql($sql_global_ids);

	if ($global_ids) {
		foreach ($global_ids as $global_id) {
			$sql = "SELECT * FROM tincident_type_field WHERE id=".$global_id['global_id'];
			$type_field = get_db_row_sql($sql);
			
			$value['id_incident_type'] = $id;
			$value['label'] = $type_field["label"];
			$value['type'] = $type_field["type"];
			$value['combo_value'] = $type_field["combo_value"];
			$value['linked_value'] = $type_field["linked_value"];
			$value['show_in_list'] = $type_field["show_in_list"];
			$value['global_id'] = $type_field["global_id"];
			
			$result = process_sql_insert('tincident_type_field', $value);

			if (!$result) {
				echo '<h3 class="error">'.__('There was a problem creating global field for type could not be created for type: ')." ".$global_id["global_id"].'</h3>';
			}
		}
	}
}

// UPDATE
if ($update_type) {

	$values['name'] = (string) get_parameter ('name');
	$values['description'] = (string) get_parameter ('description');
	$values['id_group'] = (string) get_parameter ('id_group');
	//$values['id_wizard'] = (int) get_parameter ('wizard');

	if ($values['name'] != "") {
		$result = process_sql_update('tincident_type', $values, array('id' => $id));

		if ($result === false)
			echo '<h3 class="error">'.__('Could not be updated').'</h3>';
		else {
			echo '<h3 class="suc">'.__('Successfully updated').'</h3>';
			audit_db ($config["id_user"], $config["REMOTE_ADDR"], "Ticket Management", "Updated ticket type $id - $name");
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
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "Ticket Management", "Deleted ticket type $id - $name");
	echo '<h3 class="suc">'.__('Successfully deleted').'</h3>';
	$id = 0;
}

if ($sort_items) {
	
	$position_to_sort = (int)get_parameter('position_to_sort', 1);
	$ids_serialize = (string)get_parameter('ids_items_to_sort', '');
	$move_to = (string)get_parameter('move_to', 'after');
		
	$count_items = get_db_sql('SELECT COUNT(id) FROM tincident_type_field WHERE id_incident_type = ' . $id);
	
	if (($count_items < $position_to_sort) || ($position_to_sort < 1)) {
		$result_operation = false;
	}
	else if (!empty($ids_serialize)) {
		$ids = explode(',', $ids_serialize);

		$items = get_db_all_rows_sql('SELECT id, `order`
			FROM tincident_type_field WHERE id_incident_type = ' . $id . '
			ORDER BY `order`');
		
		if ($items === false) $items = array();

		$temp = array();
		foreach ($items as $item) {
			//Remove the contents from the block to sort
			if (array_search($item['id'], $ids) === false) {
				$temp[$item['order']] = $item['id'];
			}
		}
		
		$items = $temp;

		$sorted_items = array();
		foreach ($items as $pos => $id_unsort) {
			if ($pos == $position_to_sort) {
				if ($move_to == 'after') {
					$sorted_items[] = $id_unsort;
				}
				
				foreach ($ids as $id) {
					$sorted_items[] = $id;
				}
				
				if ($move_to != 'after') {
					$sorted_items[] = $id_unsort;
				}
			}
			else {
				$sorted_items[] = $id_unsort;
			}
		}
		
		$items = $sorted_items;	

		foreach ($items as $order => $id) {
			process_sql_update('tincident_type_field', array('order' => ($order + 1)), array('id' => $id));
		}
		$result_operation = true;
	}
	else {
		$resul_operation = false;
	}
}

echo '<h2>'.__('Support').'</h2>';
echo '<h4>'.__('Ticket types').'</h4>';

// FORM (Update / Create)
if ($id || $new_type) {
	if ($new_type) {
		$id = 0;
		$name = "";
		$description = "";
		$id_group = "";
		//$id_wizard = "";
	} else {
		$type = get_db_row ('tincident_type', 'id', $id);
		$name = $type['name'];
		$description = $type['description'];
		$id_group = $type['id_group'];
		//$id_wizard = $type['id_wizard'];
	}
	
	$table = new StdClass();
	$table->width = "100%";
	$table->class = "search-table-button";
	$table->data = array ();
	$table->colspan = array ();
	$table->colspan[1][0] = 2;
	$table->colspan[2][0] = 2;
	
	$table->data[0][0] = print_input_text ('name', $name, '', 40, 100, true, __('Type name'));
	$table->data[0][1] = print_input_text ('id_group', $id_group,"", 40, 100, true, __("Groups"), false, false, true);
	$table->data[0][1] .= "&nbsp;&nbsp;<a href='javascript: incident_show_groups_search();'>" . print_image('images/add.png', true, array('title' => __('Add'))) . "</a>";
	$table->data[0][1] .= "&nbsp;&nbsp;<a href='javascript: clean_groups_field();'>" . print_image('images/cross.png', true, array('title' => __('Remove'))) . "</a>";
	
	/*
		$table->data[0][1] = print_select_from_sql ('SELECT id, name FROM twizard ORDER BY name',
			'id_wizard', $id_wizard, '', 'Select', 0, true, false, false, __('Wizard'));
		$table->data[1][0] = print_select_from_sql ('SELECT id_grupo, nombre FROM tgrupo ORDER BY nombre',
			'id_group', $id_group, '', 'Select', 0, true, false, false, __('Group'));
	*/
	
	$table->data[1][0] = print_textarea ('description', 3, 1, $description, '', true, __('Description'));
	
	echo '<form id="form-type_detail" method="post" action="index.php?sec=incidents&sec2=operation/incidents/type_detail">';
	print_table ($table);
	
		echo '<div class="button-form" style="width:100%;">';
			unset($table->data);
			$table->width = '100%';
			$table->class = "button-form";
			if ($id) {
				$button = print_submit_button (__('Update'), 'update_btn', false, 'class="sub upd"', true);
				$button .= print_input_hidden ('update_type', 1, true);
				$button .= print_input_hidden ('id', $id, true);
			} else {
				$button = print_submit_button (__('Create'), 'create_btn', false, 'class="sub next"', true);
				$button .= print_input_hidden ('create_type', 1, true);
			}
			$table->data[2][0] = $button;
			print_table ($table);
		echo '</div>';
	echo '</form>';
	unset($table);

	if ($show_fields) {
		//FIELD MANAGEMENT
		echo "<div class='divresult'>";
		echo "<h4>".__("Ticket fields")."</h4>";
		if ($id == '') {
			$id = get_parameter('id');
		}
		//INCIDENT FIELDS
		$sql = "SELECT * FROM tincident_type_field WHERE id_incident_type=$id ORDER BY `order`";
		$incident_fields = process_sql ($sql);
		if ($incident_fields === false) {
			$incident_fields = array ();
		}

		//ALL FIELDS
		$all_fields = array();
		foreach ($incident_fields as $field) {
			$all_fields[$field['id']] = $field['label'];
		}
		
		$table = new StdClass();
		$table->width = '100%';
		$table->class = 'listing';
		$table->data = array ();
		$table->head = array();
		$table->style = array();
		$table->size = array ();
		$table->size[0] = '30%';
		$table->size[1] = '20%';
		$table->size[2] = '30%';

		$table->head[0] = __("Name field");
		$table->head[1] = __("Type");
		$table->head[2] = __("Value");
		$table->head[3] = __("List");
		$table->head[4] = __("Action");
		$table->head[5] = __("Sort");

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
					$data[2] = ui_print_truncate_text($field["combo_value"], 40);
				} else if ($field["type"] == "linked") {
					$data[2] =  ui_print_truncate_text($field["linked_value"], 40);
				}
				else {
					$data[2] = "";
				}
				
				if ($field["show_in_list"]) {
					$data[3] = __('Yes');
				} else {
					$data[3] = __('No');
				}
				
				$data[4] = "";
				
				if (!$field["global_id"]) {
					if (get_admin_user ($config['id_user'])) {
						$data[4] = "<a
						href='" . $url_update . "'>
						<img src='images/wrench.png' border=0 /></a>";
					}
				} else {
					$data[4] = "<a
						href='" . $url_update . "'>
						<img src='images/eye.png' border=0 /></a>";
				}
			
				if (get_admin_user ($config['id_user'])) {
					$data[4] .= "<a
					onclick=\"if (!confirm('" . __('Are you sure?') . "')) return false;\" href='" . $url_delete . "'>
					<img src='images/cross.png' border=0 /></a>";
				}	
			
				$data[5] = print_checkbox_extended ('sorted_items[]', $field['id'], false, false, '', '', true);
				
				array_push ($table->data, $data);
			}
			print_table($table);
		} 
		else {
			echo "<h4>".__("No fields")."</h4>";
		}
		
		echo '</div>';
		echo "<div class='divform'>";
			
			
			$table_sort = new StdClass();
			$table_sort->class = 'search-table';
			$table_sort->width = '100%';
			$table_sort->colspan[0][0] = 3;
			$table_sort->size = array();
			$table_sort->size[0] = '25%';
			$table_sort->size[1] = '25%';
			$table_sort->size[2] = '25%';
			$table_sort->size[3] = '25%';

			$table_sort->data[0][0] = "<b>". __("Sort items") . "</b>";

			$table_sort->data[1][0] = __('Sort selected items from position: ');
			$table_sort->data[2][0] =  print_select (array('before' => __('Move before to'), 'after' => __('Move after to')), 'move_to', '', '', '', '0', true);
			$table_sort->data[3][0] = print_input_text_extended('position_to_sort', 1,'text-position_to_sort', '', 3, 10, false, "only_numbers('position_to_sort');", '', true);
			$table_sort->data[4][0] .= print_input_hidden('ids_items_to_sort', '', true);
			$table_sort->data[5][0] = print_submit_button(__('Sort'), 'sort_submit', false, 'class="sub upd"', true);

			echo "<form action='index.php?sec=incidents&sec2=operation/incidents/type_detail&sort_items=1&id=" . $id . "'
				method='post' onsubmit='return added_ids_sorted_items_to_hidden_input();'>";
			print_table($table_sort);
			echo "</form>";
			echo "<form id='form-add_field' name='dataedit' method='post' action='index.php?sec=incidents&sec2=operation/incidents/incident_type_field&add_field=1&id=".$id."'>";
				unset($table_sort->data);
				$table_sort->data[0][0] = print_submit_button (__('Add field'), 'create_btn', false, 'class="sub create"', true);
				print_table($table_sort);
			echo "</form>";
		echo '</div>';
	}
//LISTADO GENERAL	
}
else {
	$search_text = (string) get_parameter ('search_text');
	
	$where_clause = '';
	if ($search_text != "") {
		$where_clause .= sprintf ('WHERE name LIKE "%%%s%%"', $search_text);
	}

	$table->width = '100%';
	$table->class = 'search-table';
	$table->style = array ();
	$table->style[0] = 'font-weight: bold;';
	$table->data = array ();
	$table->data[0][0] = __('Search');
	$table->data[0][0] .= print_input_text ("search_text", $search_text, "", 20, 100, true);
	$table->data[1][0] = print_submit_button (__('Search'), "search_btn", false, 'class="sub search"', true);;
	
	echo '<div class="divform">';
		echo '<form method="post">';
			print_table ($table);
		echo '</form>';
		echo '<form method="post" action="index.php?sec=incidents&sec2=operation/incidents/type_detail">';
			unset($table->data);
			$table->data[0][0] = print_submit_button (__('Create type'), 'new_btn', false, 'class="sub next"',true);
			$table->data[0][0] .= print_input_hidden ('new_type', 1);
			print_table ($table);
		echo '</form>';
	echo '</div>';

	$sql = "SELECT * FROM tincident_type $where_clause ORDER BY name";
	$types = get_db_all_rows_sql ($sql);
	
	if ($types !== false) {
		
		$table = new StdClass();
		$table->width = '100%';
		$table->class = 'listing';
		$table->data = array ();
		$table->size = array ();
		$table->size[2] = '40px';
		$table->align = array ();
		$table->align[2] = 'center';
		$table->style = array ();
		$table->style[0] = 'font-weight: bold';
		$table->head[0] = __('Name');
		$table->head[1] = __('Description');
		if (get_admin_user ($config['id_user'])) {
			$table->head[2] = __('Delete');
		}
		
		foreach ($types as $type) {
			$data = array ();
			
			$data[0] = '<a href="index.php?sec=incidents&sec2=operation/incidents/type_detail&id='.
				$type['id'].'">'.$type['name'].'</a>';
			$data[1] = $type['description'];
			
			if (get_admin_user ($config['id_user'])) {
				$data[2] = '<a href="index.php?sec=incidents&
							sec2=operation/incidents/type_detail&
							delete_type=1&id='.$type['id'].'"
							onClick="if (!confirm(\''.__('Are you sure?').'\'))
							return false;">
							<img src="images/cross.png"></a>';
			}
			array_push ($table->data, $data);
		}
		echo '<div class="divresult">';
			print_table ($table);
		echo '</div>';
	}
}
	echo "<div class= 'dialog ui-dialog-content' title='".__("Groups")."' id='group_search_window'></div>";
?>

<script type="text/javascript" src="include/js/integria_incident_search.js"></script>
<script type="text/javascript" src="include/js/jquery.validate.js"></script>
<script type="text/javascript" src="include/js/jquery.validation.functions.js"></script>
<script type="text/javascript" >
// Form validation
trim_element_on_submit('#text-search_text');
// Form: #form-type_detail
trim_element_on_submit('#text-name');
validate_form("#form-type_detail");
var rules, messages;
// Rules: #text-name
rules = {
	required: true,
	remote: {
		url: "ajax.php",
        type: "POST",
        data: {
			page: "include/ajax/remote_validations",
			search_existing_incident_type: 1,
			type_name: function() { return $('#text-name').val() },
			type_id: "<?php echo $id?>"
        }
	}
};
messages = {
	required: "<?php echo __('Name required')?>",
	remote: "<?php echo __('This name already exists')?>"
};
add_validate_form_element_rules('#text-name', rules, messages);

function added_ids_sorted_items_to_hidden_input() {
	var ids = '';
	var first = true;
	var ids = "";
	$('input[name="sorted_items[]"]:checked').each(function() {
		if (ids == "")
			ids += this.value;
		else 
			ids += ","+this.value;
	});	

	$("input[name='ids_items_to_sort']").val(ids);
	
	if (ids == '') {
		alert("<?php echo __("Please select any item to order");?>");
		
		return false;
	}
	else {
		return true;
	}
}

function only_numbers(name) {
	var value = $("input[name='" + name + "']").val();
	
	if (value == "") {
		// Do none it is a empty field.
		return;
	}
	
	value = parseInt(value);
	
	if (isNaN(value)) {
		value = 1;
	}
	
	$("input[name='" + name + "']").val(value);
}

</script>
