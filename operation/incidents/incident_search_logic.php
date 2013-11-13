<?php

// Integria 2.0 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2011 Artica Soluciones Tecnologicas
// Copyright (c) 2008 Esteban Sanchez

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// Load global vars

check_login ();

require_once ('include/functions_incidents.php');

if (defined ('AJAX')) {
	ob_clean();
	$get_type_fields_table = (boolean) get_parameter("get_type_fields_table");

	if ($get_type_fields_table) {

		$id_incident_type = (int) get_parameter("id_incident_type");

		$table_type_fields = new stdclass;
		$table_type_fields->width = "100%";
		$table_type_fields->class = "search-table";
		$table_type_fields->data = array();

		if ($id_incident_type) {

			$sql = sprintf("SELECT *
							FROM tincident_type_field
							WHERE id_incident_type = %d", $id_incident_type);
			$config['mysql_result_type'] = MYSQL_ASSOC;
			$type_fields = process_sql($sql);

			$column = 0;
			$row = 0;
			if ($type_fields) {
				foreach ($type_fields as $key => $type_field) {

					if ($type_field['type'] == "text" || $type_field['type'] == "textarea") {
						$input = print_input_text('search_type_field_'.$type_field['id'], '', '', 30, 30, true, $type_field['label']);
					} else if ($type_field['type'] == "combo") {
						$combo_values = explode(",", $type_field['combo_value']);
						$values = array();
						foreach ($combo_values as $value) {
							$values[$value] = $value;
						}
						$input = print_select ($values, 'search_type_field_'.$type_field['id'], '', '', __('Any'), '', true, false, false, $type_field['label']);
					}

					$table_type_fields->data[$row][$column] = $input;
					if ($column >= 3) {
						$column = 0;
						$row++;
					} else {
						$column++;
					}
				}
				if ($table_type_fields->data) {
					print_table($table_type_fields);
				}
			}
		}
	}
	return;
}

echo "<div id='incident-search-content'>";
echo "<h1>" .__('Incident search');
echo "<div id='button-bar-title'>";
echo "<ul>";
echo "<li>";
echo "<a id='stats_form_submit' href='#'>" .
	print_image ("images/chart_bar_dark.png", true, array("title" => __("Search statistics"))) .
	"</a>";
echo "</li>";
echo "<li>";
echo "<a id='graph_incidents' href='#'>" .
	print_image ("images/chart_pie.png", true, array("title" => __("Graph incidents"))) .
	"</a>";
echo "</li>";
echo "</ul>";
echo "</div>";
echo "</h1>";

echo "<div class='under_tabs_info'>";
echo "</div>";


print_autorefresh_button();


echo "<div id='button-bar-title' style='margin-right: 12px; padding-bottom: 3px; margin-top: 6px;'>";
echo "<ul>";	
echo "<li style='padding: 3px;'>";
echo "<a href='javascript:' onclick='toggleDiv (\"custom_search\")'>".__('Custom search')."</a>";
echo "</li>";
echo "</ul>";
echo "</div>";

$search_form = (bool) get_parameter ('search_form');
$create_custom_search = (bool) get_parameter ('save-search');
$delete_custom_search = (bool) get_parameter ('delete_custom_search');
$id_search = get_parameter ('saved_searches');
$serialized_filter = get_parameter("serialized_filter");

//If serialize filter use the filter stored in a file in tmp dir
if ($serialized_filter) {
	$filter = unserialize_in_temp($config["id_user"]);
}

//Filter auxiliar array 
$filter_form = $filter;

/* Create a custom saved search*/
if ($create_custom_search && !$id_search) {
	$form_values = get_parameter ('form_values');
	$search_name = (string) get_parameter ('search_name');
	
	if ($filter['order_by'] && !is_array($filter['order_by'])) {
		$filter['order_by'] = json_decode(clean_output($filter['order_by']), true);
	}
	
	$result = create_custom_search ($search_name, 'incidents', $filter);
	
	if ($result === false) {
		echo '<h3 class="error">'.__('Could not create custom search').'</h3>';
	}
	else {
		echo '<h3 class="suc">'.__('Custom search saved').'</h3>';
	}
}

/* Get a custom search*/
if ($id_search && !$delete_custom_search) {
	
	$search = get_custom_search ($id_search, 'incidents');
	
	if ($search) { 
		
		if ($search["form_values"]) {
			
			$filter = unserialize($search["form_values"]);
			$filter_form = $filter;
			
			echo '<h3 class="suc">'.sprintf(__('Custom search "%s" loaded'), $search["name"]).'</h3>';
		}
		else {
			echo '<h3 class="error">'.sprintf(__('Could not load "%s" custom search'), $search["name"]).'</h3>';	
		}
	}
	else {
		echo '<h3 class="error">'.__('Could not load custom search').'</h3>';
	}
}

/* Delete a custom saved search via AJAX */
if ($delete_custom_search) {
	
	$sql = sprintf ('DELETE FROM tcustom_search
		WHERE id_user = "%s"
		AND id = %d',
		$config['id_user'], $id_search);
	$result = process_sql ($sql);
	if ($result === false) {
		echo '<h3 class="error">'.__('Could not delete custom search').'</h3>';
	}
	else {
		echo '<h3 class="suc">'.__('Custom search deleted').'</h3>';
	}
}

//FORM AND TABLE TO MANAGE CUSTOM SEARCHES
$table = new stdClass;
$table->id = 'saved_searches_table';
$table->width = '99%';
$table->class = 'search-table';
$table->size = array ();
$table->style = array ();
$table->style[0] = 'font-weight: bold';
$table->style[1] = 'font-weight: bold';
$table->data = array ();
$sql = sprintf ('SELECT id, name FROM tcustom_search
	WHERE id_user = "%s"
	AND section = "incidents"
	ORDER BY name',
	$config['id_user']);
$table->data[0][0] = print_select_from_sql ($sql, 'saved_searches', $id_search, '', __('Select'), 0, true, false, true, __('Custom searches'));

//If a custom search was selected display cross
if ($id_search) {
	$table->data[0][0] .= '<a href="index.php?sec=incidents&sec2=operation/incidents/incident_search&delete_custom_search=1&saved_searches='.$id_search.'">';
	$table->data[0][0] .= '<img src="images/cross.png" title="' . __('Delete') . '"/></a>';
}
$table->data[0][1] = print_input_text ('search_name', '', '', 40, 60, true, __('Save current search'));
$table->data[0][2] = print_submit_button (__('Save'), 'save-search', false, 'class="sub save" style="margin-top: 13px;"', true);

echo '<div id="custom_search" style="display: none;">';
echo '<form id="saved-searches-form" method="post" action="index.php?sec=incidents&sec2=operation/incidents/incident_search">';
foreach ($filter as $key => $value) {
	print_input_hidden ("search_".$key, $value);
}
print_input_hidden ("offset", get_parameter("offset"));
print_table ($table);
echo '</form>';
echo '</div>';

/* Show search form via AJAX */

form_search_incident (false, $filter_form);

echo '<div id="pager" class="hide pager">';
echo '<form>';
echo '<img src="images/control_start_blue.png" class="first" />';
echo '<img src="images/control_rewind_blue.png" class="prev" />';
echo '<input type="text" class="pager pagedisplay" size=5 />';
echo '<img src="images/control_fastforward_blue.png" class="next" />';
echo '<img src="images/control_end_blue.png" class="last" />';
echo '<select class="pager pagesize" style="display:none">';
echo '<option selected="selected" value="5">5</option>';
echo '</select>';
echo '</form>';
echo '</div>';

incidents_search_result($filter);

/* Add a form to carry filter between statistics and search views */
echo '<form id="stats_form" method="post" action="index.php?sec=incidents&sec2=operation/incidents/incident_search&option=stats" style="clear: both">';
foreach ($filter as $key => $value) {
	print_input_hidden ("search_".$key, $value);
}
print_input_hidden ("offset", get_parameter("offset"));
echo "</form>";

/* Add a form to carry filter between graphs and search views */
echo '<form id="graph_incidents_form" method="post" action="index.php?sec=incidents&sec2=operation/incidents/incident_search&option=graph" style="clear: both">';
foreach ($filter as $key => $value) {
	print_input_hidden ("search_".$key, $value);
}
print_input_hidden ("offset", get_parameter("offset"));
echo "</form>";

//Store serialize filter
serialize_in_temp($filter, $config["id_user"]);

$table->class = 'search-table-button';
$table->width = '99%';
$table->id = 'incident_massive';
$table->data = array();
$table->style = array ();

$table->data[0][0] = combo_incident_status (-1, 0, 0, true, true);
$table->data[0][1] = print_select (get_priorities (),'mass_priority', -1, '', __('Select'), -1, true, 0, true, __('Priority'), false, 'min-width: 70px;');
$table->data[0][2] = combo_incident_resolution ($resolution, false, true, true);
$table->data[0][3] = print_select_from_sql('SELECT id_usuario, nombre_real FROM tusuario;', 'mass_assigned_user', '0', '', __('Select'), -1, true, false, true, __('Assigned user'));

$table->data[1][0] = print_submit_button (__('Update'), 'massive_update', false, 'class="sub next"', true);
$table->colspan[1][0] = 4;

$massive_oper_incidents = print_table ($table, true);

echo print_container('massive_oper_incidents', __('Massive operations over selected items'), $massive_oper_incidents, 'closed', true, '20px');

?>

<script type="text/javascript" src="include/js/jquery.metadata.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
<script type="text/javascript" src="include/js/integria_incident_search.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.autocomplete.js"></script>
<script type="text/javascript" src="include/js/integria_date.js"></script>

<script type="text/javascript">

// Datepicker
add_ranged_datepicker ("#text-search_first_date", "#text-search_last_date", null);

//Javascript search form configuration
$(document).ready(function () {
	$("#stats_form_submit").click(function (event) {
		event.preventDefault();
		$("#stats_form").submit();
	});
	
	$("#graph_incidents").click(function (event) {
		event.preventDefault();
		$("#graph_incidents_form").submit();
	});
	
	$("a.show_advanced_search").click (function () {
		table = $("#search_incident_form").children ("table");
		$("tr", table).show ();
		$(this).remove ();
		return false;
	});
	
	$("#saved_searches").change(function() {
		$("#saved-searches-form").submit();
	});
	
	//JS for massive operations
	$("#checkbox-incidentcb-all").change(function() {
		$(".cb_incident").prop('checked', $("#checkbox-incidentcb-all").prop('checked'));
	});

	$(".cb_incident").click(function(event) {
		event.stopPropagation();
	});
	
	$("#submit-massive_update").click(function(event) {
		process_massive_updates();
	});
	
	// Form validation
	trim_element_on_submit('#text-search_string');
	trim_element_on_submit('#text-search_name');
	trim_element_on_submit('#text-inventory_name');
	
	//Autocomplete for owner search field
	var idUser = "<?php echo $config['id_user'] ?>";
	
	bindAutocomplete ("#text-search_id_user", idUser);
	bindAutocomplete ("#text-search_creator", idUser);
	bindAutocomplete ("#text-search_editor", idUser);
	bindAutocomplete ("#text-search_closed_by", idUser);
	
	if ($("#search_incident_form").length > 0) {
		validate_user ("#search_incident_form", "#text-search_id_user", "<?php echo __('Invalid user')?>");
		validate_user ("#search_incident_form", "#text-search_creator", "<?php echo __('Invalid user')?>");
		validate_user ("#search_incident_form", "#text-search_editor", "<?php echo __('Invalid user')?>");
		validate_user ("#search_incident_form", "#text-search_closed_by", "<?php echo __('Invalid user')?>");
	}
	
});

function changeIncidentOrder(element, order) {
	$('#hidden-search_order_by').val('{ "'+element+'" : "'+order+'" }');
	$('#saved-searches-form').submit();
}

function loadInventory(id_inventory) {
	
	$('#hidden-id_inventory').val(id_inventory);
	$('#text-inventory_name').val(id_inventory);
	
	$("#search_inventory_window").dialog('close');
}

// Show the modal window of inventory search
function show_search_inventory(search_free, id_object_type_search, owner_search, id_manufacturer_search, id_contract_search, search, object_fields_search) {
	
	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=include/ajax/inventories&get_inventory_search=1&search_free="+search_free+"&id_object_type_search="+id_object_type_search+"&owner_search="+owner_search+"&id_manufacturer_search="+id_manufacturer_search+"&id_contract_search="+id_contract_search+"&object_fields_search="+object_fields_search+"&search=1",
		dataType: "html",
		success: function(data) {
			$("#search_inventory_window").html (data);
			$("#search_inventory_window").show ();
			
			$("#search_inventory_window").dialog ({
					resizable: true,
					draggable: true,
					modal: true,
					overlay: {
						opacity: 0.5,
						background: "black"
					},
					width: 920,
					height: 700
				});
			$("#search_inventory_window").dialog('open');
		}
	});
}

// Change the type fields table
function change_type_fields_table() {
	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=operation/incidents/incident_search_logic&get_type_fields_table=1&id_incident_type="+$("#search_id_incident_type").val(),
		dataType: "html",
		success: function(data) {
			$("#table_type_fields").html (data);
		}
	});
}
</script>
