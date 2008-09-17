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

/* Tabs code */
echo '<div id="tabs">';

/* Tabs list */
echo '<ul style="height: 30px;" class="ui-tabs-nav">';
echo '<li class="ui-tabs-selected"><a href="#ui-tabs-3"><span>'.lang_string ('Search').'</span></a></li>';
echo '<li class="ui-tabs-disabled"><a href="index.php"><span>'.lang_string ('Details').'</span></a></li>';
echo '</ul>';

/* Tabs first container is manually set, so it loads immediately */
echo '<div id="ui-tabs-3" class="ui-tabs-panel" style="display: block;">';

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
	$("#tabs > ul").tabs ("url", 2, "ajax.php?page=operation/inventories/incident_tracking&id=" + id);
	$("#tabs > ul").tabs ("url", 3, "ajax.php?page=operation/inventories/incident_inventory_detail&id=" + id);
	$("#tabs > ul").tabs ("url", 4, "ajax.php?page=operation/inventories/incident_inventory_contacts&id=" + id);
	$("#tabs > ul").tabs ("url", 5, "ajax.php?page=operation/inventories/incident_workunits&id=" + id);
	$("#tabs > ul").tabs ("url", 6, "ajax.php?page=operation/inventories/incident_files&id=" + id);
	$("#tabs > ul").tabs ("enable", 1).tabs ("enable", 2).tabs ("enable", 3)
		.tabs ("enable", 4).tabs ("enable", 5).tabs ("enable", 6);
	$("#tabs > ul").tabs ("select", 1);
}

$(document).ready (function () {
	$("#tabs > ul").tabs ({"load" : tab_loaded}).tabs ("disable", 1).tabs ("disable", 2);
	configure_inventory_search_form (10, incident_row_clicked);
});
</script>
