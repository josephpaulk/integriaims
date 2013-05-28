<?php

global $config;

check_login ();

$id = (int) get_parameter ('id');

$is_enterprise = false;

if (file_exists ("enterprise/include/functions_inventory.php")) {
	require_once ("enterprise/include/functions_inventory.php");
	$is_enterprise = true;
}

if ($is_enterprise) {
	$read_permission = inventory_check_acl($config['id_user'], $id);
	
	if (!$read_permission) {
		audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to inventory ".$id);
		include "general/error_perms.php";
		exit;
	}
}

echo '<h3>'.__('Inventory object tracking').' #'.$id.'</h3>';

//**********************************************************************
// Tabs
//**********************************************************************

echo '<div id="tabs">';

/* Tabs list */
echo '<ul class="ui-tabs-nav">';
echo '<li class="ui-tabs"><a href="index.php?sec=inventory&sec2=operation/inventories/inventory_detail&id=' . $id . '"><span>'.__('Details').'</span></a></li>';
echo '<li class="ui-tabs"><a href="index.php?sec=inventory&sec2=operation/inventories/inventory_relationship&id=' . $id . '"><span>'.__('Relationships').'</span></a></li>';
echo '<li class="ui-tabs"><a href="index.php?sec=inventory&sec2=operation/inventories/inventory_incidents&id=' . $id . '"><span>'.__('Incidents').'</span></a></li>';
echo '<li class="ui-tabs"><a href="index.php?sec=inventory&sec2=operation/inventories/inventory_contacts&id=' . $id . '"><span>'.__('Contacts').'</span></a></li>';
echo '<li class="ui-tabs-selected"><a href="index.php?sec=inventory&sec2=operation/inventories/inventory_tracking&id=' . $id . '"><span>'.__('Tracking').'</span></a></li>';

echo '</ul>';
echo '</div>';


if (! $id) {
	require ("general/noaccess.php");
	exit;
}

$trackings = get_db_all_rows_field_filter ('tinventory_track', 'id_inventory', $id, 'timestamp DESC');

if ($trackings !== false) {
	$table->width = "98%";
	$table->class = 'listing';
	$table->data = array ();
	$table->head = array ();
	$table->head[1] = __('Description');
	$table->head[2] = __('User');
	$table->head[3] = __('Date');
	
	foreach ($trackings as $tracking) {
		$data = array ();
		
		$data[0] = $tracking['description'];
		$data[1] = dame_nombre_real ($tracking['id_user']);
		$data[2] = $tracking['timestamp'];
		
		array_push ($table->data, $data);
	}
	echo "<center>";
	print_table ($table);
	echo "</center>";
} else {
	echo __('No data available');
}
?>
