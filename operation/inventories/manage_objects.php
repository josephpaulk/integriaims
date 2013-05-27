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
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access Object Management");
	require ("general/noaccess.php");
	exit;
}

include_once("include/functions_objects.php");

//**********************************************************************
// Get actions
//**********************************************************************

$id = (int) get_parameter ('id');
$create = (bool) get_parameter ('create');
$insert_object = (bool) get_parameter ('insert_object');
$update_object = (bool) get_parameter ('update_object');
$delete_object = (bool) get_parameter ('delete_object');
$get_icon = (bool) get_parameter ('get_icon');

//**********************************************************************
// Ajax
//**********************************************************************

if ($get_icon) {
	$icon = (string) get_db_value ('icon', 'tobject_type', 'id', $id);
	
	if (defined ('AJAX')) {
		echo $icon;
		return;
	}
}

//**********************************************************************
// Tabs
//**********************************************************************

echo '<div id="tabs">';

/* Tabs list */
echo '<ul class="ui-tabs-nav">';
echo '<li class="ui-tabs-selected"><a href="index.php?sec=inventory&sec2=operation/inventories/manage_objects"><span>'.__('Objects').'</span></a></li>';
if (!empty($id)) {
	echo '<li class="ui-tabs"><a href="index.php?sec=inventory&sec2=operation/inventories/manage_objects_types_list&id=' . $id . '"><span>'.__('Fields').'</span></a></li>';
}
echo '</ul>';
echo '</div>';

//**********************************************************************
// Actions
//**********************************************************************

// Creation
if ($insert_object) {
	$name = (string) get_parameter ("name");
	$icon = (string) get_parameter ("icon");
	$description = (string) get_parameter ("description");
	
	$sql = sprintf ('INSERT INTO tobject_type (name, description, icon) 
			VALUES ("%s", "%s", "%s")',
			$name, $description, $icon);
	$id = process_sql ($sql, 'insert_id');
	if (! $id) {
		echo '<h3 class="error">'.__('Could not be created').'</h3>';
	} else {
		echo '<h3 class="suc">'.__('Successfully created').'</h3>';
		insert_event ("OBJECT TYPE CREATED", $id, 0, $name);
	}
	$id = 0;
}

// Update
if ($update_object) {
	$name = (string) get_parameter ("name");
	$icon = (string) get_parameter ("icon");
	$description = (string) get_parameter ("description");
	
	$sql = sprintf ('UPDATE tobject_type SET name = "%s", icon = "%s",
		description = "%s"
		WHERE id = %s',
		$name, $icon, $description, $id);
		
	$result = process_sql ($sql);
	if (! $result) {
		echo "<h3 class='error'>".__('Could not be updated')."</h3>"; 
	} else {
		echo "<h3 class='suc'>".__('Successfully updated')."</h3>";
		insert_event ("PRODUCT UPDATED", $id, 0, $name);
	}
}

// Delete
if ($delete_object) {
	// Move parent who has this product to 0
	$sql = sprintf ('DELETE FROM tobject_type_field WHERE id_object_type = %d', $id);
	process_sql ($sql);
	
	$sql = sprintf ('DELETE FROM tobject_type WHERE id = %d', $id);
	$result = process_sql ($sql);

	if ($result)
		echo '<h3 class="suc">'.__("Successfully deleted").'</h3>';
	else
		echo '<h3 class="error">'.__("Could not be deleted").'</h3>';
		
	$id = 0;
}

//**********************************************************************
// Object edition form
//**********************************************************************
if ($create || $id) {
	if ($create) {
		$icon = "";
		$description = "";
		$name = "";
		$id = -1;
	} else {
		$object = get_db_row ("tobject_type", "id", $id);
		$description = $object["description"];
		$name = $object["name"];
		$icon = $object["icon"];
	}

	echo "<h2>".__('Object management')."</h2>";
	/*if ($id == -1) {
		echo "<h3>".__('Create a new object')."</h3>";
	} else {
		echo "<h3>".__('Update existing object')."</h3>";
	}*/
	
	$table->width = '90%';
	$table->class = 'databox';
	$table->colspan = array ();
	$table->colspan[0][0] = 2;
	$table->colspan[2][0] = 2;
	$table->data = array ();
	
	$table->data[0][0] = print_input_text ('name', $name, '', 45, 100, true, __('Name'));
	
	$files = list_files ('images/objects/', "png", 1, 0);
	$table->data[1][0] = print_select ($files, 'icon', $icon, '', __('None'), "", true, false, false, __('Icon'));
	$table->data[1][0] .= objects_get_icon ($id, true);
	$table->data[2][0] = print_textarea ('description', 10, 50, $description, '',
		true, __('Description'));
	
	echo '<form id="form-manage_objects" method="post">';
	print_table ($table);
	echo '<div class="button" style="width: '.$table->width.'">';
	if ($id == -1) {
		print_submit_button (__('Create'), 'crt_btn', false, 'class="sub next"');
		print_input_hidden ('insert_object', 1);
	} else {
		print_submit_button (__('Update'), 'upd_btn', false, 'class="sub upd"');
		print_input_hidden ('id', $id);
		print_input_hidden ('update_object', 1);
	}
	echo "</div></form>";
}

//**********************************************************************
// List objects
//**********************************************************************
if (! $id && ! $create) {
	echo "<h2>".__('Object management')."</h2>";
	$objects = get_db_all_rows_in_table ('tobject_type', 'name');
	
	$table->width = '90%';
	
	if ($objects !== false) {
		echo "<h3>".__('Defined objects')."</h3>";
		
		$table->class = 'listing';
		$table->data = array ();
		$table->head = array ();
		$table->head[0] = __('Name');
		$table->head[1] = __('Description');
		$table->head[2] = __('Items');
		$table->head[3] = __('Actions');
		$table->style = array ();
		$table->style[0] = 'font-weight: bold';
		$table->align = array ();
		
		echo '<table width="90%" class="listing">';
		foreach ($objects as $object) {
			$data = array ();
			
			$data[0] = objects_get_icon ($object['id'], true);
			$data[0] .= ' <a href="index.php?sec=inventory&sec2=operation/inventories/manage_objects&id='.
				$object['id'].'">'.$object['name'].'</a>';
			$data[1] = substr ($object["description"], 0, 200);
			$data[2] = objects_count_fields($object['id']);
			$data[3] = '<a title=' . __("Fields") . ' href=index.php?sec=inventory&sec2=operation/inventories/manage_objects_types_list&id='.
				$object["id"].'><img src="images/page_white_text.png"></a>';
			$data[3] .= '&nbsp;<form style="display:inline;" method="post" onsubmit="if (!confirm(\''.__('Are you sure?').'\'))
				return false;">';
			$data[3] .= print_input_hidden ('delete_object', 1, true);
			$data[3] .= print_input_hidden ('id', $object["id"], true);
			$data[3] .= print_input_image ('delete', 'images/cross.png', 1, '', true, '',array('title' => __('Delete')));
			$data[3] .= '</form>';
			
			array_push ($table->data, $data);
		}
		print_table ($table);
	} else {
		echo "<h4>".__('No objects')."</h4>";
	}
	
	echo '<div class="button" style="width: '.$table->width.'">';
	echo '<form method="post">';
	print_input_hidden ('create', 1);
	print_submit_button (__('Create'), 'crt_btn', false, 'class="sub next"');
	echo "</form></div>";
} // end of list

?>

<script type="text/javascript" src="include/js/jquery.validate.js"></script>
<script type="text/javascript" src="include/js/jquery.validation.functions.js"></script>

<script type="text/javascript">
$(document).ready (function () {
	$("#icon").change (function () {
		data = this.value;
		$("#product-icon").fadeOut ('normal', function () {
			$("#product-icon").attr ("src", "images/products/"+data).fadeIn ();
		});
	})
});


// Form validation
trim_element_on_submit('#text-name');
validate_form("#form-manage_objects");
var rules, name;
// Rules: #text-name
rules = {
	required: true,
	remote: {
		url: "ajax.php",
        type: "POST",
        data: {
			page: "include/ajax/remote_validations",
			search_existing_object_type: 1,
			object_type_name: function() { return $('#text-name').val() },
			object_type_id: "<?php echo $id?>"
        }
	}
};
messages = {
	required: "<?php echo __('Name required')?>",
	remote: "<?php echo __('This object type already exists')?>"
};
add_validate_form_element_rules('#text-name', rules, messages);

</script>
