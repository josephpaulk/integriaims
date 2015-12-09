<?php
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008-2010 Ártica Soluciones Tecnológicas
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

if (! give_acl ($config["id_user"], 0, "FRM")) {
	audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access Download Management");
	require ("general/noaccess.php");
	exit;
}

require_once ('include/functions_file_releases.php');

$op 			= get_parameter ("op", "");
$id 			= get_parameter ("id", 0);
$name 			= get_parameter ("name", "");
$description 	= get_parameter ("description", "");
$icon 			= get_parameter ("icon", "default.png");


// Database Creation
// ==================
if ($op == "insert") {

	$sql_insert = "INSERT INTO tdownload_type (name, description, icon) VALUES ('$name', '$description', '$icon')";

	$id = process_sql($sql_insert, "insert_id");	

	if (!$id)
		echo "<h3 class='error'>".__('Could not be created')."</h3>"; 
	else {
		echo "<h3 class='suc'>".__('Successfully created')."</h3>";
	}
	
}


// Database UPDATE
// ==================
if ($op == "update" && $id > 0) {
	
	$sql_update = "UPDATE tdownload_type SET name = '$name', description = '$description', icon = '$icon' WHERE id = $id";
	
	$result = process_sql($sql_update);
	
	if (!$result)
		echo "<h3 class='error'>".__('Could not be updated')."</h3>"; 
	else {
		echo "<h3 class='suc'>".__('Successfully updated')."</h3>";
	}
}


// Database DELETE
// ==================
if ($op == "delete" && $id > 0) {
	
	$sql_delete = "DELETE FROM tdownload_type WHERE id = $id";

	$result = process_sql($sql_delete);

	if ($result) {
		delete_type_file($id);
		echo "<h3 class='suc'>".__('Successfully deleted')."</h3>";
	} else {
		echo "<h3 class='error'>".__('Cannot be deteled')."</h3>";
	}
	$id = 0;
}


// CREATE form
if ($op == "new" || $id > 0) {

	if ($id > 0) {
		$type = get_db_row ("tdownload_type", "id", $id);
		$name = $type["name"];
		$description = $type["description"];
		$icon = $type["icon"];
		$type = null;

		echo "<h1>".__('Update existing type')."</h1>";
	} else {
		echo "<h1>".__('Create a new type')."</h1>";
	}

	$table = new stdClass;
	$table->width = '99%';
	$table->class = 'search-table-button';
	$table->data = array();
	$table->colspan = array();
	$table->colspan[1][0] = 2;
	$table->colspan[2][0] = 2;

	$table->data[0][0] = print_input_text ('name', $name, '', 50, 200, true, __('Name'));
	$files = list_files ('images/download_type/', "png", 1, true);
	$table->data[0][1] = print_select ($files, 'icon', $icon, '', '', "", true, 0, false, __('Icon'));
	$table->data[1][0] = print_textarea ("description", 5, 40, $description,'', true, __('Description'));

	if ($id > 0) {
		$table->data[2][0] = print_submit_button (__('Update'), 'upd_btn', false, 'class="sub upd"', true);
		$table->data[2][0] .= print_input_hidden ('id', $id, true);
		$table->data[2][0] .= print_input_hidden ('op', 'update', true);
	} else {
		$table->data[2][0] = print_submit_button (__('Create'), 'crt_btn', false, 'class="sub create"', true);
		$table->data[2][0] .= print_input_hidden ('op', 'insert', true);
	}

	echo "<form id='form-file_type' method='post' action='index.php?sec=download&sec2=operation/download/manage_types'>";
	echo print_table($table, true);
	echo "</form>";

}

// Show list of types
// =======================
else {

	echo "<h1>".__('File release type management')." &raquo; ".__('Defined types')."</h1>";

	$types = get_file_types(false, true);
	if (!$types) {
		$types = array();
		echo "<h3 class='error'>".__('No types found')."</h3>";
	} else {
		$table = new stdClass;
		$table->width = '99%';
		$table->class = 'listing';
		$table->head = array();
		$table->data = array();
		$table->colspan = array();

		$table->head[0] = __('Name');
		$table->head[1] = __('Icon');
		$table->head[2] = __('Items');
		$table->head[3] = __('Delete');

		foreach ($types as $type) {
			$data = array();

			$data[0] = "<a href='index.php?sec=download&sec2=operation/download/manage_types&id=".$type["id"]."'>"
				.$type["name"]."</a>";
			$data[1] = get_download_type_icon($type["id"]);
			$data[2] = (int)get_db_value ('COUNT(id_download)', 'tdownload_type_file', 'id_type', $type["id"]);
			$data[3] = "<a href='index.php?sec=download&sec2=operation/download/manage_types&op=delete&id=".$type["id"]."'
							onClick='if (!confirm(\" ".__('Are you sure?')."\")) return false;'>
							<img title='".__('Delete')."' border='0' src='images/cross.png'></a>";

			array_push ($table->data, $data);
		}
		
		print_table($table);		
	}
	echo '<div style="width:99%; text-align: right;">';
	echo "<form method=post action='index.php?sec=download&sec2=operation/download/manage_types'>";
	print_input_hidden ('op', 'new');
	print_submit_button (__('Create'), 'crt_btn', false, 'class="sub create"');
	echo "</form></div>";
	
} // end of list

?>

<script type="text/javascript" src="include/js/jquery.validate.js"></script>
<script type="text/javascript" src="include/js/jquery.validation.functions.js"></script>

<script  type="text/javascript">

// Form validation
trim_element_on_submit('#text-name');
validate_form("#form-file_type");
var rules, messages;
// Rules: #text-name
rules = {
	required: true,
	remote: {
		url: "ajax.php",
		type: "POST",
		data: {
			page: "include/ajax/remote_validations",
			search_existing_file_type: 1,
			file_type_name: function() { return $('#text-name').val() },
			file_type_id: function() { return $('#hidden-id').val() }
		}
	}
};
messages = {
	required: "<?php echo __('Name required')?>",
	remote: "<?php echo __('This type already exists')?>"
};
add_validate_form_element_rules('#text-name', rules, messages);

</script>
