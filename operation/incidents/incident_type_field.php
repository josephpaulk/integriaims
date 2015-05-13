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

if (! give_acl ($config["id_user"], 0, "IM")) {
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access company section");
	require ("general/noaccess.php");
	exit;
}

$id_incident_type = (int) get_parameter ('id');
$add_field = (int) get_parameter('add_field', 0);
$update_field = (int) get_parameter('update_field', 0);

$label = '';
$type = '';
$combo_value = '';
$linked_value = '';
$parent = '';
$show_in_list = false;

$id_field = get_parameter ('id_field');

if ($id_field) {
	$id_field = get_parameter ('id_field');	
	$field_data = get_db_row_filter('tincident_type_field', array('id' => $id_field));

	$label = $field_data['label'];
	$type = $field_data['type'];
	$combo_value = $field_data['combo_value'];
	$show_in_list = (boolean) $field_data['show_in_list'];
	$parent = $field_data['parent'];
	$linked_value = $field_data['linked_value'];

}

echo '<h1>'.__('Ticket fields management').'</h1>';

$table->width = "99%";
$table->class = "search-table-button";
$table->data = array ();

$table->data[0][0] = print_input_text ('label', $label, '', 45, 100, true, __('Field name'));

$types = array('text' =>__('Text'), 'textarea' => __('Textarea'), 'combo' => __('Combo'), 'linked' =>__('Linked'), 'numeric' =>__('Numeric'));
$table->data[0][1] = print_label (__("Type"), "label-id", 'text', true);
$table->data[0][1] .= print_select ($types, 'type', $type, '', __('Select type'), '0', true);
$table->data[0][2] = print_checkbox ('show_in_list', 1, $show_in_list, true, __('Show in the tickets list'));
$table->data[0][3] = print_checkbox ('global', 1, '', true, __('Global field'));

$table->data['id_combo_value'][0] = print_input_text ('combo_value', $combo_value, '', 45, 0, true, __('Combo value')).print_help_tip (__("Set values separated by comma"), true);

$sql = "SELECT id,label FROM tincident_type_field	
	WHERE id_incident_type = ".$id_incident_type.
	" AND type='linked'";

$parents_result = get_db_all_rows_sql($sql);

if ($parents_result == false) {
	$parents_result = array();
}
$parents = array();
foreach ($parents_result as $result) {
	$parents[$result['id']] = $result['label']; 
}

$table->data['id_parent_value'][0] .= print_label (__("Parent"), "label-id", 'text', true);
$table->data['id_parent_value'][0] = print_select ($parents, 'parent', $parent, '', __('Select parent'), '0', true);

$table->data['id_linked_value'][0] = print_textarea ('linked_value', 15, 1, $linked_value, '', true, __('Linked value').integria_help ("linked_values", true));


if ($add_field) {
	$button = print_input_hidden('add_field', 1, true);
	$button .= print_submit_button (__('Create'), 'create_btn', false, 'class="sub next"', true);
} else if ($update_field) {
	$button = print_input_hidden('update_field', 1, true);
	$button .= print_input_hidden('add_field', 0, true);
	$button .= print_input_hidden('id_field', $id_field, true);
	$button .= print_submit_button (__('Update'), 'update_btn', false, 'class="sub upd"', true);
}

$table->data['button'][0] = $button;
$table->colspan['button'][0] = 3;

echo '<form method="post" action="index.php?sec=incidents&sec2=operation/incidents/type_detail&id='.$id_incident_type.'&add_field=1">';
print_table ($table);
echo '</form>';

?>

<script type="text/javascript" src="include/js/jquery.validate.js"></script>
<script type="text/javascript" src="include/js/jquery.validation.functions.js"></script>

<script  type="text/javascript">
$(document).ready (function () {
	
	var type_val = $("#type").val();
	switch (type_val) {
		case "combo":
			$("#table1-id_combo_value-0").css ('display', '');
			$("#table1-id_linked_value-0").css ('display', 'none');
			$("#table1-id_parent_value-0").css ('display', 'none');
		break;
		case "linked":
			$("#table1-id_linked_value-0").css ('display', '');
			$("#table1-id_parent_value-0").css ('display', '');
			$("#table1-id_combo_value-0").css ('display', 'none');
		break;
		default:
			$("#table1-id_combo_value-0").css ('display', 'none');
			$("#table1-id_linked_value-0").css ('display', 'none');
			$("#table1-id_parent_value-0").css ('display', 'none');
		break;
	}
});

$("#type").change (function () {
	
	var type_val = $("#type").val();
	switch (type_val) {
		case "combo":
			$("#table1-id_combo_value-0").css ('display', '');
			$("#table1-id_linked_value-0").css ('display', 'none');
			$("#table1-id_parent_value-0").css ('display', 'none');
		break;
		case "linked":
			$("#table1-id_linked_value-0").css ('display', '');
			$("#table1-id_parent_value-0").css ('display', '');
			$("#table1-id_combo_value-0").css ('display', 'none');
		break;
		default:
			$("#table1-id_combo_value-0").css ('display', 'none');
			$("#table1-id_linked_value-0").css ('display', 'none');
			$("#table1-id_parent_value-0").css ('display', 'none');
		break;
	}
});


// Form validation
trim_element_on_submit('#text-label');
trim_element_on_submit('#text-combo_value');

</script>
