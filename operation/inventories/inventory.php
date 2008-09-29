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

if (check_login () != 0) {
 	audit_db ("Noauth", $config["REMOTE_ADDR"], "No authenticated access",
 		"Trying to access inventory listing");
	require ("general/noaccess.php");
	exit;
}

$id = (int) get_parameter ('id');

/* Tabs code */
echo '<div id="tabs">';

/* Tabs list */
echo '<ul style="height: 30px;" class="ui-tabs-nav">';
if ($id) {
	echo '<li class="ui-tabs"><a href="#ui-tabs-1"><span>'.lang_string ('Search').'</span></a></li>';
	echo '<li class="ui-tabs-selected"><a href="ajax.php?page=operation/inventories/inventory_detail&id='.$id.'"><span>'.lang_string ('Details').'</span></a></li>';
} else {
	echo '<li class="ui-tabs-selected"><a href="#ui-tabs-1"><span>'.lang_string ('Search').'</span></a></li>';
	echo '<li class="ui-tabs-disabled"><a href="index.php"><span>'.lang_string ('Details').'</span></a></li>';
}

echo '<li class="ui-tabs-disabled"><a href="index.php"><span>'.lang_string ('Incidents').'</span></a></li>';
echo '<li class="ui-tabs-disabled"><a href="index.php"><span>'.lang_string ('Contracts').'</span></a></li>';
echo '<li class="ui-tabs-disabled"><a href="index.php"><span>'.lang_string ('Workunits').'</span></a></li>';
echo '</ul>';

/* Tabs first container is manually set, so it loads immediately */
echo '<div id="ui-tabs-1" class="ui-tabs-panel" style="display: '.($id ? 'none' : 'block').';">';

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
var old_inventory;

function tab_loaded (event, tab) {
	if (tab.index == 1) {
		configure_inventory_form (true);
	}
}

function incident_row_clicked (id, name) {
	$("#tabs > ul").tabs ("url", 1, "ajax.php?page=operation/inventories/inventory_detail&id=" + id);
	$("#tabs > ul").tabs ("url", 2, "ajax.php?page=operation/inventories/inventory_incidents&id=" + id);
	$("#tabs > ul").tabs ("url", 3, "ajax.php?page=operation/inventories/inventory_contracts&id=" + id);
	$("#tabs > ul").tabs ("url", 4, "ajax.php?page=operation/inventories/inventory_workunits&id=" + id);
	$("#tabs > ul").tabs ("enable", 1).tabs ("enable", 2).tabs ("enable", 3)
		.tabs ("enable", 4);
	$("#tabs > ul").tabs ("select", 1);
}

$(document).ready (function () {
	$("#tabs > ul").tabs ({"load" : tab_loaded});

<?php if ($id) : ?>
	$("#tabs > ul").tabs ("url", 1, "ajax.php?page=operation/inventories/inventory_detail&id=" + <?php echo $id; ?>);
	$("#tabs > ul").tabs ("url", 2, "ajax.php?page=operation/inventories/inventory_incidents&id=" + <?php echo $id; ?>);
	$("#tabs > ul").tabs ("url", 3, "ajax.php?page=operation/inventories/inventory_contracts&id=" + <?php echo $id; ?>);
	$("#tabs > ul").tabs ("url", 4, "ajax.php?page=operation/inventories/inventory_workunits&id=" + <?php echo $id; ?>);
	$("#tabs > ul").tabs ("enable", 1).tabs ("enable", 2).tabs ("enable", 3)
		.tabs ("enable", 4);
	$("#tabs > ul").tabs ("select", 1);
<?php endif; ?>
	configure_inventory_search_form (10, incident_row_clicked);
});
</script>
