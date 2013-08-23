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

$id_incident_type = (int) get_parameter ('id');
$add_field = (int) get_parameter('add_field', 0);
$update_field = (int) get_parameter('update_field', 0);

$label = '';
$type = '';
$combo_value = '';

$id_field = get_parameter ('id_field');

if ($id_field) {
	$id_field = get_parameter ('id_field');	
	$field_data = get_db_row_filter('tincident_type_field', array('id' => $id_field));

	$label = $field_data['label'];
	$type = $field_data['type'];
	$combo_value = $field_data['combo_value'];

}

echo '<h1>'.__('Incident fields management').'</h1>';

$table->width = "99%";
$table->class = "search-table-button";
$table->data = array ();

$table->data[0][0] = print_input_text ('label', $label, '', 45, 100, true, __('Field name'));

$types = array('text' =>__('Text'), 'textarea' => __('Textarea'), 'combo' => __('Combo'));
$table->data[0][1] = print_label (__("Type"), "label-id", 'text', true);
$table->data[0][1] .= print_select ($types, 'type', $type, '', __('Select type'), '0', true);

$table->data['id_combo_value'][0] = print_input_text ('combo_value', $combo_value, '', 45, 100, true, __('Combo value')).print_help_tip (__("Set values separated by comma"), true);

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
$table->colspan['button'][0] = 2;

echo '<form method="post" action="index.php?sec=incidents&sec2=operation/incidents/type_detail&id='.$id_incident_type.'&add_field=1">';
print_table ($table);
echo '</form>';

?>

<script type="text/javascript" src="include/js/jquery.validate.js"></script>
<script type="text/javascript" src="include/js/jquery.validation.functions.js"></script>

<script  type="text/javascript">
$(document).ready (function () {
	if ($("#type").val() == "combo") {
		$("#table1-id_combo_value-0").css ('display', '');
	} else {
		$("#table1-id_combo_value-0").css ('display', 'none');
	}

});

$("#type").change (function () {
	if ($("#type").val() == "combo") {
		$("#table1-id_combo_value-0").css ('display', '');
	} else {
		$("#table1-id_combo_value-0").css ('display', 'none');
	}
});


// Form validation
trim_element_on_submit('#text-label');
trim_element_on_submit('#text-combo_value');

</script>
