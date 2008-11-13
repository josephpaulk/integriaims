<?php

// Integria 1.1 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2008 Artica Soluciones Tecnologicas
// Copyright (c) 2007-2008 Esteban Sanchez, estebans@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

check_login ();

if (! give_acl ($config['id_user'], 0, "VR")) {
	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access inventory list");
	include ("general/noaccess.php");
	exit;
}

$id = (int) get_parameter ('id');

/* Tabs code */
echo '<div id="tabs">';

/* Tabs list */
echo '<ul style="height: 30px;" class="ui-tabs-nav">';
if ($id) {
	echo '<li class="ui-tabs"><a href="#ui-tabs-1"><span>'.__('Search').'</span></a></li>';
	echo '<li class="ui-tabs-selected"><a href="ajax.php?page=operation/inventories/inventory_detail&id='.$id.'"><span>'.__('Details').'</span></a></li>';
} else {
	echo '<li class="ui-tabs-selected"><a href="#ui-tabs-1"><span>'.__('Search').'</span></a></li>';
	echo '<li class="ui-tabs-disabled"><a href="index.php"><span>'.__('Details').'</span></a></li>';
}

echo '<li class="ui-tabs-disabled"><a href="index.php"><span>'.__('Incidents').'</span></a></li>';
echo '<li class="ui-tabs-disabled"><a href="index.php"><span>'.__('Contracts').'</span></a></li>';
echo '<li class="ui-tabs-disabled"><a href="index.php"><span>'.__('Contacts').'</span></a></li>';
echo '<li class="ui-tabs-disabled"><a href="index.php"><span>'.__('Workunits').'</span></a></li>';
echo '</ul>';

/* Tabs first container is manually set, so it loads immediately */
echo '<div id="ui-tabs-1" class="ui-tabs-panel" style="display: '.($id ? 'none' : 'block').';">';

echo '<div class="result"></div>';

$table->id = 'saved_searches_table';
$table->width = '90%';
$table->class = 'search-table';
$table->size = array ();
$table->size[0] = '120px';
$table->style = array ();
$table->style[0] = 'font-weight: bold';
$table->style[2] = 'display: none; font-weight: bold';
$table->style[3] = 'display: none';
$table->style[4] = 'display: none';
$table->data = array ();
$table->data[0][0] = __('Custom searches');
$sql = sprintf ('SELECT id, name FROM tcustom_search
	WHERE id_user = "%s"
	AND section = "inventories"
	ORDER BY name',
	$config['id_user']);
$table->data[0][1] = print_select_from_sql ($sql, 'saved_searches', 0, '', __('Select'), 0, true);
$table->data[0][1] .= '<a href="ajax.php" style="display:none" id="delete_custom_search">';
$table->data[0][1] .= '<img src="images/cross.png" /></a>';
$table->data[0][2] = __('Save current search');
$table->data[0][2] = __('Save current search');
$table->data[0][3] = print_input_text ('search_name', '', '', 10, 20, true);
$table->data[0][4] = print_submit_button (__('Save'), 'save-search', false, 'class="sub next"', true);

echo '<form id="saved-searches-form">';
print_table ($table);
echo '</form>';
unset ($table);

require_once ('inventory_search.php');

echo '</div>';
echo '</div>';

?>

<script type="text/javascript" src="include/js/jquery.metadata.js"></script>
<script type="text/javascript" src="include/js/jquery.tablesorter.js"></script>
<script type="text/javascript" src="include/js/jquery.tablesorter.pager.js"></script>
<script type="text/javascript" src="include/js/integria_incident_search.js"></script>

<script type="text/javascript">

var id_inventory;
var old_inventory = 0;

function tab_loaded (event, tab) {
	if (tab.index == 1) {
		configure_inventory_form (true);
		
		if (id_inventory == old_inventory) {
			return;
		}
		
		if ($(".inventory-menu").css ('display') != 'none') {
			$(".inventory-menu").slideUp ('normal', function () {
				configure_inventory_side_menu (id_inventory, false);
				$(this).slideDown ();
			});
		} else {
			configure_inventory_side_menu (id_inventory, false);
			$(".inventory-menu").slideDown ();
		}
		old_inventory = id_inventory;
	}
}

function show_inventory_details (id) {
	id_inventory = id;
	$("#tabs > ul").tabs ("url", 1, "ajax.php?page=operation/inventories/inventory_detail&id=" + id);
	$("#tabs > ul").tabs ("url", 2, "ajax.php?page=operation/inventories/inventory_incidents&id=" + id);
	$("#tabs > ul").tabs ("url", 3, "ajax.php?page=operation/inventories/inventory_contracts&id=" + id);
	$("#tabs > ul").tabs ("url", 4, "ajax.php?page=operation/inventories/inventory_contacts&id=" + id);
	$("#tabs > ul").tabs ("url", 5, "ajax.php?page=operation/inventories/inventory_workunits&id=" + id);
	$("#tabs > ul").tabs ("enable", 1).tabs ("enable", 2).tabs ("enable", 3)
		.tabs ("enable", 4).tabs ("enable", 5);
	$("#tabs > ul").tabs ("select", 1);
}

$(document).ready (function () {
	$("#tabs > ul").tabs ({"load" : tab_loaded});

<?php if ($id) : ?>
	id_inventory = <?php echo $id ?>;
	$(".inventory-menu").slideDown ();
	show_inventory_details (<?php echo $id; ?>);
<?php endif; ?>
	
	$("#saved-searches-form").submit (function () {
		search_values = get_form_input_values ('inventory_search_form');
		
		values = get_form_input_values (this);
		values.push ({name: "page", value: "operation/inventories/inventory_search"});
		$(search_values).each (function () {
			values.push ({name: "form_values["+this.name+"]", value: this.value});
		});
		values.push ({name: "create_custom_search", value: 1});
		jQuery.post ("ajax.php",
			values,
			function (data, status) {
				result_msg (data);
			},
			"html"
		);
		return false;
	});
	
	$("#saved_searches").change (function () {
		if (this.value == 0) {
			$("#delete_custom_search").fadeOut ();
			return;
		}
		$("#delete_custom_search").fadeIn ();
		
		values = Array ();
		values.push ({name: "page", value: "operation/inventories/inventory_search"});
		values.push ({name: "get_custom_search_values", value: 1});
		values.push ({name: "id_search", value: this.value});
		
		jQuery.get ("ajax.php",
			values,
			function (data, status) {
				load_form_values ("inventory_search_form", data);
				$("#inventory_search_form").submit ();
			},
			"json"
		);
	});
	
	$("#delete_custom_search").click (function () {
		id_search = $("#saved_searches").attr ("value");
		values = Array ();
		values.push ({name: "page", value: "operation/inventories/inventory_search"});
		values.push ({name: "delete_custom_search", value: 1});
		values.push ({name: "id_search", value: id_search});
		
		jQuery.get ("ajax.php",
			values,
			function (data, status) {
				result_msg (data);
				$("#delete_custom_search").fadeOut ();
				$("#saved_searches").attr ("value", 0);
				$("option[value="+id_search+"]", "#saved_searches").remove ();
			},
			"html"
		);
		return false;
	});
	
	$("#inventory_search_form").submit (function () {
		$("#saved_searches_table td:gt(1)").fadeIn ();
	});
	
	$("#goto-inventory-form").submit (function () {
		id = $("#text-id", this).attr ("value");
		show_inventory_details (id);
		if (old_inventory)
			$("#tabs > ul").tabs ("load", 1);
		return false;
	});
	
	configure_inventory_search_form (<?php echo $config['block_size']?>,
		function (id, name) {
			show_inventory_details (id);
		}
	);
});
</script>
