<?php
// Integria IMS - http://integriaims.com
// ==================================================
// Copyright (c) 2008-2011 Artica Soluciones Tecnologicas

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

$create = (bool) get_parameter ('create_report');
$update = (bool) get_parameter ('update_report');
$name = (string) get_parameter ('name');
$sql = (string) get_parameter ('sql');
$id_group = get_parameter('id_group', 0);
$id = (int) get_parameter ('id');

if ($id) {
	$report = get_db_row ('tinventory_reports', 'id', $id);
	if ($report === false)
		return;
	$name = $report['name'];
	$sql = $report['sql'];
	$id_group = $report['id_group'];
	
	$user_in_group = get_db_value_filter('id_grupo', 'tusuario_perfil', array('id_usuario'=>$config['id_user'],'id_grupo'=>$id_group));	
	if ($id_group == 1) {
		$user_in_group = 1;
	}
}


if ((!dame_admin ($config['id_user'])) && ($user_in_group == false)) {
	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access inventory reports");
	include ("general/noaccess.php");
	return;
}

$result_msg = '';
if ($create) {
	$values['name'] = (string) get_parameter ('name');
	$values['sql'] = (string) get_parameter ('sql');
	$values['id_group'] = get_parameter('id_group', 0);
	
	$result = false;
	if (! empty ($values['name']))
		$result = process_sql_insert ('tinventory_reports', $values);
	
	if ($result) {
		$result_msg = '<h3 class="suc">'.__('Successfully created').'</h3>';
		$id = $result;
	} else {
		$result_msg = '<h3 class="error">'.__('Could not be created').'</h3>';
		$id = false;
	}
}

if ($update) {
	$values['name'] = (string) get_parameter ('name');
	$values['sql'] = (string) get_parameter ('sql');
	$values['id_group'] = get_parameter('id_group');
	
	$result = false;
	if (! empty ($values['name']))
		$result = process_sql_update ('tinventory_reports', $values, array ('id' => $id));
	if ($result) {
		$result_msg = '<h3 class="suc">'.__('Successfully updated').'</h3>';
	} else {
		$result_msg = '<h3 class="error">'.__('Could not be updated').'</h3>';
	}
}

if ($id) {
	clean_cache_db();

	$report = get_db_row ('tinventory_reports', 'id', $id);
	if ($report === false)
		return;
	$name = $report['name'];
	$sql = $report['sql'];
	$id_group = $report['id_group'];
}

$render = get_parameter ("render",0);
$render_html = get_parameter ("render_html",0);

if ($render == 1){
	$report = get_db_row ('tinventory_reports', 'id', $id);
	if ($report === false)
		return;
	
	$filename = clean_output ($report['name']).'-'.date ("YmdHi");

	ob_end_clean();

	// We'll be outputting a CSV
	header ('Content-Disposition: attachment; filename="'.$filename.'.csv"');
	$config['mysql_result_type'] = MYSQL_ASSOC;
	$rows = get_db_all_rows_sql (clean_output ($report['sql']));
	if ($rows === false)
		return;
	// Header
	echo safe_output (implode (',', array_keys ($rows[0])))."\n";
	
	// Item / data
	foreach ($rows as $row) {
		echo safe_output (implode (',', $row))."\n";
	}
    exit;
}

if ($render_html == 1){
	$report = get_db_row ('tinventory_reports', 'id', $id);
	if ($report === false)
		return;
	
	echo "<h1>".__('Custom report')." &raquo; ".$report['name']."</h1>";

	$config['mysql_result_type'] = MYSQL_ASSOC;
	$rows = get_db_all_rows_sql (clean_output ($report['sql']));
	if ($rows === false)
		return;
	
	// Get the header
	echo "<table width=99% cellpadding=0 cellspacing=0 class=listing>";
	echo "<tr>";
	foreach (array_keys ($rows[0]) as $header_item){
		echo "<th>".$header_item."</th>";
	}
	echo "</tr>";
	
	// Get the data row
	foreach ($rows as $row) {
		echo "<tr>";
		foreach ($row as $item) {
			echo "<td>$item</td>";
		}
		echo "</tr>";
	}
	echo "</table>";
    return;
}

echo "<h1>".__('Inventory reports')."</h1>";

echo $result_msg;

$table->width = '99%';
$table->class = 'search-table-button';
$table->data = array ();
$table->colspan = array ();
$table->colspan[1][0] = 2;
$table->colspan[2][0] = 2;
	
$table->data[0][0] = print_input_text ('name', $name, '', 40, 255, true, __('Name'));

$groups = get_user_groups ($config['id_user'], "VR");
$groups[0] = __('None');
$table->data[0][1] = print_select ($groups, "id_group", $id_group, '', '', 0, true, false, false, __('Group'));

$table->data[1][0] = print_textarea ('sql', 10, 100, $sql, '', true, __('Report SQL sentence'));

if (dame_admin ($config['id_user'])) {
	if ($id) {
			$button = print_input_hidden ('update_report', 1, true);
			$button .= print_input_hidden ('id', $id, true);
			$button .= print_submit_button (__('Update'), 'update', false, 'class="sub upd"', true);
	} else {
		$button = print_input_hidden ('create_report', 1, true);
		$button .= print_submit_button (__('Create'), 'create', false, 'class="sub create"', true);
	}
}

$table->data[2][0] = $button;

echo '<form id="form-inventory_report" method="post">';
print_table ($table);
echo '</form>';
?>

<script type="text/javascript" src="include/js/jquery.validate.js"></script>
<script type="text/javascript" src="include/js/jquery.validation.functions.js"></script>

<script type="text/javascript">
	
// Form validation
trim_element_on_submit('#text-name');
validate_form("#form-inventory_report");
var rules, messages;
// Rules: #text-name
rules = {
	required: true/*,
	remote: {
		url: "ajax.php",
        type: "POST",
        data: {
			page: "include/ajax/remote_validations",
			search_existing_inventory_report: 1,
			inventory_report_name: function() { return $('#text-name').val() },
			inventory_report_id: "<?php echo $id?>"
        }
	}*/
};
messages = {
	required: "<?php echo __('Name required')?>"/*,
	remote: "<?php echo __('This inventory report already exists')?>"*/
};
add_validate_form_element_rules('#text-name', rules, messages);
// Rules: #textarea-sql
rules = {
	required: true
};
messages = {
	required: "<?php echo __('SQL sentence required')?>"
};
add_validate_form_element_rules('#textarea-sql', rules, messages);

</script>
