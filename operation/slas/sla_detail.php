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

check_login();

if (! give_acl ($config["id_user"], 0, "IM")) {
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation",
		"Trying to access SLA Management");
	require ("general/noaccess.php");
	exit;
}

$id = (int) get_parameter ('id');
$new_sla = (bool) get_parameter ('new_sla');
$create_sla = (bool) get_parameter ('create_sla');
$update_sla = (bool) get_parameter ('update_sla');
$delete_sla = (bool) get_parameter ('delete_sla');

// CREATE
if ($create_sla) {
	$name = (string) get_parameter ('name');
	$description = (string) get_parameter ('description');
	$min_response = (int) get_parameter ('min_response');
	$max_response = (int) get_parameter ('max_response');
	$max_incidents = (int) get_parameter ('max_incidents');
	$max_inactivity = (int) get_parameter ('max_inactivity');
	$id_sla_base = (int) get_parameter ('id_sla_base');
	$enforced = (int) get_parameter ('enforced');

    $five_daysonly = (int) get_parameter ("five_daysonly", 0);
    $time_from = (int) get_parameter ("time_from", 0);
    $time_to = (int) get_parameter ("time_to", 0);

	$sql = sprintf ('INSERT INTO tsla (`name`, `description`, id_sla_base,
		min_response, max_response, max_incidents, `enforced`, five_daysonly, time_from, time_to, max_inactivity)
		VALUE ("%s", "%s", %d, %d, %d, %d, %d, %d, %d, %d, %d)',
		$name, $description, $id_sla_base, $min_response,
		$max_response, $max_incidents, $enforced, $five_daysonly, $time_from, $time_to, $max_inactivity);

	$id = process_sql ($sql);
	if ($id === false)
		echo '<h3 class="error">'.__('Could not be created')."</h3>";
	else {
		echo "<h3 class='suc'>".__('Successfully created')."</h3>";

        audit_db ($config["id_user"], $config["REMOTE_ADDR"], "SLA Created",
		"Created a new SLA ($name)", $sql);
	}
	$id = 0;
}

// UPDATE
// ==================
if ($update_sla) {
	$name = (string) get_parameter ('name');
	$description = (string) get_parameter ('description');
	$min_response = (int) get_parameter ('min_response');
	$max_response = (int) get_parameter ('max_response');
	$max_incidents = (int) get_parameter ('max_incidents');
	$id_sla_base = (int) get_parameter ('id_sla_base');
	$enforced = (int) get_parameter ('enforced');
    $five_daysonly = (int) get_parameter ("five_daysonly", 0);
    $time_from = (int) get_parameter ("time_from", 0);
    $time_to = (int) get_parameter ("time_to", 0);
    $max_inactivity = (int) get_parameter ('max_inactivity');

	$sql = sprintf ('UPDATE tsla SET max_inactivity = %d, enforced = %d, description = "%s",
		name = "%s", max_incidents = %d, min_response = %d, max_response = %d,
		id_sla_base = %d, five_daysonly = %d, time_from = %d, time_to = %d WHERE id = %d', $max_inactivity, 
		$enforced, $description, $name, $max_incidents, $min_response,
		$max_response, $id_sla_base, $five_daysonly, $time_from, $time_to, $id);

	$result = process_sql ($sql);
	if (! $result)
		echo '<h3 class="error">'.__('Could not be updated').'</h3>';
	else {
		echo '<h3 class="suc">'.__('Successfully updated').'</h3>';
        audit_db ($config["id_user"], $config["REMOTE_ADDR"], "SLA Modified",
		"Updated SLA ($name)", $sql);
	}
	$id = 0;
}

// DELETE
// ==================
if ($delete_sla) {
	$name = get_db_value ('name', 'tsla', 'id', $id);
	$sql = sprintf ('DELETE FROM tsla WHERE id = %d', $id);
	$result = process_sql ($sql);
    audit_db ($config["id_user"], $config["REMOTE_ADDR"], "SLA Deleted",
		"Delete SLA ($name)", $sql);
	echo '<h3 class="suc">'.__('Successfully deleted').'</h3>';
	$id = 0;
}

echo "<h2>".__('SLA Management');
echo integria_help ("sla", true);
echo "</h2>";

// FORM (Update / Create)
if ($id || $new_sla) {
	if ($new_sla) {
		$name = "";
		$description = "";
		$min_response = 48;
		$max_response = 480;
		$max_incidents = 10;
		$max_inactivity = 96;
		$id_sla_base = 0;
		$enforced = 1;
        $five_daysonly = 1;
        $time_from = 8;
        $time_to = 18;

	} else {
		$sla = get_db_row ('tsla', 'id', $id);
		$name = $sla['name'];
		$description = $sla['description'];
		$min_response = $sla['min_response'];
		$max_response = $sla['max_response'];
		$max_incidents = $sla['max_incidents'];
		$max_inactivity = $sla['max_inactivity'];
		$id_sla_base = $sla['id_sla_base'];
		$enforced = $sla['enforced'];
        $five_daysonly = $sla["five_daysonly"];
        $time_from = $sla["time_from"];
        $time_to = $sla["time_to"];

	}

	$table->width = "90%";
	$table->class = "databox";
	$table->data = array ();
	$table->colspan = array ();
	$table->colspan[5][0] = 2;
	
	$table->data[0][0] = print_input_text ("name", $name, "", 30, 100, true, __('SLA name'));
	$table->data[0][1] = print_checkbox ('enforced', 1 ,$enforced, true, __('Enforced'));
	$table->data[1][0] = print_input_text ('min_response', $min_response, '',
		5, 100, true, __('Min. response time (in hours)'));

	$table->data[1][1] = print_input_text ('max_response', $max_response, '',
		5, 100, true, __('Max. resolution time (in hours)'));

	$table->data[2][0] = print_input_text ("max_incidents", $max_incidents, '',
		5, 100, true, __('Max. incidents at the same time'));
	
	$table->data[2][1] = print_input_text ("max_inactivity", $max_inactivity, '',
		5, 100, true, __('Max. incident inactivity'));
		
	$table->data[3][0] = print_checkbox ('five_daysonly', 1 ,$five_daysonly, true, __('Fire only between week, not weekends'));

	$table->data[3][1] = print_select_from_sql ('SELECT id, name FROM tsla ORDER BY name',
		'id_sla_base', $id_sla_base, '', __('None'), 0, true, false, false, __('SLA Base'));

	$table->data[4][0] = print_input_text ('time_from', $time_from, '',
		5, 10, true, __('Start hour to compute SLA'));
	$table->data[4][1] = print_input_text ('time_to', $time_to, '',
		5, 10, true, __('Last hour to compute SLA'));


	$table->data[5][0] = print_textarea ("description", 8, 1, $description, '', true, __('Description'));

	echo '<form method="post" action="index.php?sec=inventory&sec2=operation/slas/sla_detail">';
	print_table ($table);
	
	echo '<div class="button" style="width: '.$table->width.'">';
	if ($id) {
		print_submit_button (__('Update'), "update_btn", false, 'class="sub upd"', false);
		print_input_hidden ('update_sla', 1);
		print_input_hidden ("id", $id);
	} else {
		print_input_hidden ('create_sla', 1);
		print_submit_button (__('Create'), "create_btn", false, 'class="sub next"', false);
	}
	echo "</div>";

	echo "</form>";
} else {
	$search_text = (string) get_parameter ('search_text');
	
	$where_clause = "";
	if ($search_text != "") {
		$where_clause = sprintf ('WHERE name LIKE "%%%s%%"
			OR description LIKE "%%%s%%"',
			$search_text, $search_text);
	}

	$table->width = '400px';
	$table->class = 'search-table';
	$table->style = array ();
	$table->style[0] = 'font-weight: bold;';
	$table->data = array ();
	$table->data[0][0] = __('Search');
	$table->data[0][1] = print_input_text ("search_text", $search_text, "", 25, 100, true);
	$table->data[0][2] = print_submit_button (__('Search'), "search_btn", false, 'class="sub search"', true);;
	
	echo "<div style='padding: 5px; padding-bottom: 15px; padding-top: 15px;'>";
	echo '<form method="post" action="index.php?sec=incidents&sec2=operation/slas/sla_detail">';
	print_table ($table);
	echo '</form>';
	echo "</div>";
	
	
	$sql = "SELECT * FROM tsla $where_clause ORDER BY name";
	$slas = get_db_all_rows_sql ($sql);
	
	if ($slas !== false) {
		$table->width = "90%";
		$table->class = "listing";
		$table->data = array ();
		$table->style = array ();
		$table->style[0] = 'font-weight: bold';
		$table->head[0] = __('Name');
		$table->head[1] = __('Min.Response');
		$table->head[2] = __('Max.Resolution');
		$table->head[3] = __('Max.Incidents');
		$table->head[4] = __('Max.Inactivity');
		$table->head[5] = __('Enforced');
		$table->head[6] = __('Parent');
		$table->head[7] = __('Delete');
		
		foreach ($slas as $sla) {
			$data = array ();
			
			$data[0] = "<a href='index.php?sec=incidents&sec2=operation/slas/sla_detail&id=".$sla['id']."'>".$sla['name']."</a>";
			$data[1] = $sla['min_response'].' '.__('Hours');
			$data[2] = $sla['max_response'].' '.__('Hours');
			$data[3] = $sla['max_incidents'];
			$data[4] = $sla['max_inactivity'];
			
			if ($sla['enforced'] == 1)
				$data[5] = __("Yes");
			else
				$data[5] = __("No");
			$data[6] = get_db_value ('name', 'tsla', 'id', $sla['id_sla_base']);
			$data[7] = '<a href="index.php?sec=inventory&
						sec2=operation/slas/sla_detail&
						delete_sla=1&id='.$sla['id'].'"
						onClick="if (!confirm(\''.__('Are you sure?').'\'))
						return false;">
						<img src="images/cross.png"></a>';
			array_push ($table->data, $data);
		}
		print_table ($table);
	}
	
	echo '<form method="post" action="index.php?sec=incidents&sec2=operation/slas/sla_detail">';
	echo '<div class="button" style="width: '.$table->width.'">';
	print_submit_button (__('Create'), 'new_btn', false, 'class="sub next"');
	print_input_hidden ('new_sla', 1);
	echo '</div>';
	echo '</form>';
}
?>
