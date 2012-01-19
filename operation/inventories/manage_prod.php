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

if (! give_acl ($config["id_user"], 0, "KM")) {
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access KB Management");
	require ("general/noaccess.php");
	exit;
}

$id = (int) get_parameter ('id');
$create = (bool) get_parameter ('create');
$insert_product = (bool) get_parameter ('insert_product');
$update_product = (bool) get_parameter ('update_product');
$delete_product = (bool) get_parameter ('delete_product');
$get_icon = (bool) get_parameter ('get_icon');

if ($get_icon) {
	$icon = (string) get_db_value ('icon', 'tkb_product', 'id', $id);
	
	if (defined ('AJAX')) {
		echo $icon;
		return;
	}
}

// Database Creation
// ==================
if ($insert_product) {
	$name = (string) get_parameter ("name");
	$parent = (int) get_parameter ("product");
	$icon = (string) get_parameter ("icon");
	$description = (string) get_parameter ("description");
	
	$sql = sprintf ('INSERT INTO tkb_product (name, description, parent, icon) 
			VALUES ("%s", "%s", %d, "%s")',
			$name, $description, $parent, $icon);
	$id = process_sql ($sql, 'insert_id');
	if (! $id) {
		echo '<h3 class="error">'.__('Could not be created').'</h3>';
	} else {
		echo '<h3 class="suc">'.__('Successfully created').'</h3>';
		insert_event ("PRODUCT CREATED", $id, 0, $name);
	}
	$id = 0;
}

// Database UPDATE
if ($update_product) {
	$name = (string) get_parameter ("name");
	$parent = (int) get_parameter ("product");
	$icon = (string) get_parameter ("icon");
	$description = (string) get_parameter ("description");
	
	$sql = sprintf ('UPDATE tkb_product SET name = "%s", icon = "%s",
		description = "%s", parent = %d
		WHERE id = %s',
		$name, $icon, $description, $parent, $id);
	$result = process_sql ($sql);
	if (! $result) {
		echo "<h3 class='error'>".__('Could not be updated')."</h3>"; 
	} else {
		echo "<h3 class='suc'>".__('Successfully updated')."</h3>";
		insert_event ("PRODUCT UPDATED", $id, 0, $name);
	}
}


// Database DELETE
// ==================
if ($delete_product) {
	// Move parent who has this product to 0
	$sql = sprintf ('UPDATE tkb_product SET parent = 0 WHERE parent = %d', $id);
	process_sql ($sql);
	
	$sql = sprintf ('DELETE FROM tkb_product WHERE id = %d', $id);
	$result = process_sql ($sql);

	if ($result)
		echo '<h3 class="suc">'.__("Successfully deleted").'</h3>';
	else
		echo '<h3 class="error">'.__("Could not be deleted").'</h3>';
		
	unset ($id);
}

if ($create || $id) {
	if ($create) {
		$icon = "";
		$description = "";
		$name = "";
		$id = -1;
		$parent = -1;
	} else {
		$product = get_db_row ("tkb_product", "id", $id);
		$description = $product["description"];
		$name = $product["name"];
		$icon = $product["icon"];
		$parent = $product["parent"];
	}

	echo "<h2>".__('Product management')."</h2>";
	if ($id == -1) {
		echo "<h3>".__('Create a new product')."</h3>";
	} else {
		echo "<h3>".__('Update existing product')."</h3>";
	}
	
	$table->width = '90%';
	$table->class = 'databox';
	$table->colspan = array ();
	$table->colspan[0][0] = 2;
	$table->colspan[2][0] = 2;
	$table->data = array ();
	
	$table->data[0][0] = print_input_text ('name', $name, '', 45, 100, true, __('Name'));
	
	$files = list_files ('images/products/', "png", 1, 0);
	$table->data[1][0] = print_select ($files, 'icon', $icon, '', __('None'), "", true, false, false, __('Icon'));
	$table->data[1][0] .= print_product_icon ($id, true);
	$table->data[1][1] = combo_kb_products ($parent, 1, __('Product'), true);
	$table->data[2][0] = print_textarea ('description', 10, 50, $description, '',
		true, __('Description'));
	
	echo '<form method="post">';
	print_table ($table);
	echo '<div class="button" style="width: '.$table->width.'">';
	if ($id == -1) {
		print_submit_button (__('Create'), 'crt_btn', false, 'class="sub next"');
		print_input_hidden ('insert_product', 1);
	} else {
		print_submit_button (__('Update'), 'upd_btn', false, 'class="sub upd"');
		print_input_hidden ('id', $id);
		print_input_hidden ('update_product', 1);
	}
	echo "</div></form>";
}

// Show list of product
// =======================
if (! $id && ! $create) {
	echo "<h2>".__('Product management')."</h2>";
	$products = get_db_all_rows_in_table ('tkb_product', 'parent, name');
	
	$table->width = '90%';
	
	if ($products !== false) {
		echo "<h3>".__('Defined products')."</h3>";
		
		$table->class = 'listing';
		$table->data = array ();
		$table->head = array ();
		$table->head[0] = __('Name');
		$table->head[1] = __('Parent');
		$table->head[2] = __('Description');
		$table->head[3] = __('Items');
		$table->head[4] = __('Delete');
		$table->style = array ();
		$table->style[0] = 'font-weight: bold';
		$table->align = array ();
		$table->align[4] = 'center';
		
		echo '<table width="90%" class="listing">';
		foreach ($products as $product) {
			$data = array ();
			
			$data[0] = print_product_icon ($product['id'], true);
			$data[0] .= ' <a href="index.php?sec=inventory&sec2=operation/inventories/manage_prod&id='.
				$product['id'].'">'.$product['name'].'</a>';
			$data[1] = get_db_value ('name', 'tkb_product', 'id', $product["parent"]);
			$data[2] = substr ($product["description"], 0, 200);
			$data[3] = get_db_value ('COUNT(id)', 'tkb_data', 'id_product', $product['id']);
			$data[4] = '<a href=index.php?sec=inventory&sec2=operation/inventories/manage_prod&delete_product=1&id='.
				$product["id"].' onClick="if (!confirm(\''.__('Are you sure?').'\'))
				return false;"><img src="images/cross.png"></a>';
			
			array_push ($table->data, $data);
		}
		print_table ($table);
	}
	
	echo '<div class="button" style="width: '.$table->width.'">';
	echo '<form method="post">';
	print_input_hidden ('create', 1);
	print_submit_button (__('Create'), 'crt_btn', false, 'class="sub next"');
	echo "</form></div>";
} // end of list

?>

<script type="text/javascript">
$(document).ready (function () {
	$("#icon").change (function () {
		data = this.value;
		$("#product-icon").fadeOut ('normal', function () {
			$("#product-icon").attr ("src", "images/products/"+data).fadeIn ();
		});
	})
});

</script>
