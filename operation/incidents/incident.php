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

// Load global vars

$accion = "";
global $config;

if (check_login () != 0) {
	audit_db ("Noauth", $config["REMOTE_ADDR"], "No authenticated access","Trying to access incident viewer");
	require ("general/noaccess.php");
	exit;
}

$id_usuario =$_SESSION["id_usuario"];
if (! give_acl ($id_usuario, 0, "IR")) {
	audit_db ($id_usuario, $config["REMOTE_ADDR"], "ACL Violation", "Trying to access incident viewer");
	require ("general/noaccess.php");
	exit;
}

// Take input parameters
$id = (int) get_parameter ('id');

// Offset adjustment
if (isset($_GET["offset"]))
	$offset=$_GET["offset"];
else
	$offset=0;

// Delete incident
if (isset ($_GET["quick_delete"])) {
	$id_inc = $_GET["quick_delete"];
	$sql2="SELECT * FROM tincidencia WHERE id_incidencia=".$id_inc;
	$result2=mysql_query($sql2);
	$row2=mysql_fetch_array($result2);
	if ($row2) {
		$id_author_inc = $row2["id_usuario"];
		$email_notify = $row2["notify_email"];
		if ((give_acl($id_usuario, $row2["id_grupo"], "IM") ==1) OR ($_SESSION["id_usuario"] == $id_author_inc) ) {
			if ($email_notify == 1){
				// Email notify to all people involved in this incident
				mail_incident ($id_inc, $id_usuario, "", 0, 3);
			}
			borrar_incidencia($id_inc);
			echo "<h3 class='suc'>".__('Incident successfully deleted')."</h3>";
			audit_db($config["id_user"], $config["REMOTE_ADDR"], "Incident deleted","User ".$id_usuario." deleted incident #".$id_inc);
		} else {
			audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Forbidden","User ".$_SESSION["id_usuario"]." try to delete incident");
			echo "<h3 class='error'>".__('There was a problem deleting incident')."</h3>";
			no_permission();
		}
	}
}

/* Tabs code */
echo '<div id="tabs">';

/* Tabs list */
echo '<ul style="height: 30px;" class="ui-tabs-nav">';
if ($id) {
	echo '<li class="ui-tabs"><a href="#ui-tabs-1"><span>'.__('Search').'</span></a></li>';
	echo '<li class="ui-tabs-selected"><a href="ajax.php?page=operation/incidents/incident_detail&id='.$id.'"><span>'.__('Details').'</span></a></li>';
} else {
	echo '<li class="ui-tabs-selected"><a href="#ui-tabs-1"><span>'.__('Search').'</span></a></li>';
	echo '<li class="ui-tabs-disabled"><a href="index.php"><span>'.__('Details').'</span></a></li>';
}
echo '<li class="ui-tabs-disabled"><a href="index.php"><span>'.__('Tracking').'</span></a></li>';
echo '<li class="ui-tabs-disabled"><a href="index.php"><span>'.__('Inventory').'</span></a></li>';
echo '<li class="ui-tabs-disabled"><a href="index.php"><span>'.__('Contact').'</span></a></li>';
echo '<li class="ui-tabs-disabled"><a href="index.php"><span>'.__('Workunits').'</span></a></li>';
echo '<li class="ui-tabs-disabled"><a href="index.php"><span>'.__('Files').'</span></a></li>';
echo '</ul>';

/* Tabs first container is manually set, so it loads immediately */
echo '<div id="ui-tabs-1" class="ui-tabs-panel" style="display: '.($id ? 'none' : 'block').';">';

echo '<div class="result"></div>';

$table->id = 'saved_searches_table';
$table->width = '740px';
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
	AND section = "incidents"
	ORDER BY name',
	$config['id_user']);
$table->data[0][1] = print_select_from_sql ($sql, 'saved_searches', 0, '', __('Select'), 0, true);
$table->data[0][2] = __('Save current search');
$table->data[0][3] = print_input_text ('search_name', '', '', 10, 20, true);
$table->data[0][4] = print_submit_button (__('Save'), 'save-search', false, 'class="sub next"', true);

echo '<form id="saved-searches-form">';
print_table ($table);
echo '</form>';

form_search_incident ();

unset ($table);
$table->class = 'hide result_table listing';
$table->width = '100%';
$table->id = 'incident_search_result_table';
$table->head = array ();
$table->head[0] = "Id";
$table->head[1] = __('SLA');
$table->head[2] = __('Incident');
$table->head[3] = __('Group');
$table->head[4] = __('Status')." - <i>".__('Resolution')."</i>";
$table->head[5] = __('Priority');
$table->head[6] = __('Updated')." - <i>".__('Started')."</i>";
$table->head[7] = __('Flags');
$table->style = array ();
$table->style[0] = '';

print_table ($table);

print_table_pager ();

echo '<div id="incident-stats"></div>';

/* End of first tab container */
echo '</div>';

echo '</div>';
/* End of tabs code */

?>

<script type="text/javascript" src="include/js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language']; ?>.js"></script>
<script type="text/javascript" src="include/js/jquery.metadata.js"></script>
<script type="text/javascript" src="include/js/jquery.tablesorter.js"></script>
<script type="text/javascript" src="include/js/jquery.tablesorter.pager.js"></script>
<script type="text/javascript" src="include/js/integria_incident_search.js"></script>

<script type="text/javascript">

var id_incident;
var old_incident = 0;

function tab_loaded (event, tab) {
	/* Details tab */
	if (tab.index == 1) {
		/* In integria_incident_search.js */
		configure_incident_form (true, false);
		
		if (id_incident == old_incident) {
			return;
		}
		if ($(".incident-menu").css ('display') != 'none') {
			$(".incident-menu").slideUp ('normal', function () {
				configure_incident_side_menu (id_incident, true);
				$(this).slideDown ();
			});
		} else {
			configure_incident_side_menu (id_incident, true);
			$(".incident-menu").slideDown ();
		}
		old_incident = id_incident;
	}
	/* Files tab */
	if (tab.index == 6) {
		$("#table_file_list td a.delete").click (function () {
			tr = $(this).parents ("tr");
			if (!confirm ("<?php echo __('Are you sure?');?>"))
				return false;
			console.log ();
			jQuery.get (
				$(this).attr ("href"),
				null,
				function (data) {
					result_msg (data);
					$(tr).fadeOut ('normal', function () {
						$(this).empty ();
					});
				}
			);
			
			return false;
		});
	}
	
	$(".result").empty ();
}

function show_incident_details (id) {
	id_incident = id;
	$("#tabs > ul").tabs ("url", 1, "ajax.php?page=operation/incidents/incident_detail&id=" + id);
	$("#tabs > ul").tabs ("url", 2, "ajax.php?page=operation/incidents/incident_tracking&id=" + id);
	$("#tabs > ul").tabs ("url", 3, "ajax.php?page=operation/incidents/incident_inventory_detail&id=" + id);
	$("#tabs > ul").tabs ("url", 4, "ajax.php?page=operation/incidents/incident_inventory_contacts&id=" + id);
	$("#tabs > ul").tabs ("url", 5, "ajax.php?page=operation/incidents/incident_workunits&id=" + id);
	$("#tabs > ul").tabs ("url", 6, "ajax.php?page=operation/incidents/incident_files&id=" + id);
	$("#tabs > ul").tabs ("enable", 1).tabs ("enable", 2).tabs ("enable", 3)
		.tabs ("enable", 4).tabs ("enable", 5).tabs ("enable", 6);
	$("#tabs > ul").tabs ("select", 1);
}

$(document).ready (function () {
	$("#tabs > ul").tabs ({"load" : tab_loaded});
<?php if ($id) : ?>
	old_incident = id_incident = <?php echo $id ?>;
	configure_incident_side_menu (id_incident, false);
	$(".incident-menu").slideDown ();
	show_incident_details (<?php echo $id; ?>);
<?php endif; ?>
	
	$("#saved-searches-form").submit (function () {
		search_values = get_form_input_values ('search_incident_form');
		
		values = get_form_input_values (this);
		values.push ({name: "page", value: "operation/incidents/incident_search"});
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
		values = Array ();
		values.push ({name: "page", value: "operation/incidents/incident_search"});
		values.push ({name: "get_custom_search_values", value: 1});
		values.push ({name: "id_search", value: this.value});
		
		jQuery.get ("ajax.php",
			values,
			function (data, status) {
				load_form_values ("search_incident_form", data);
				$("#search_incident_form").submit ();
			},
			"json"
		);
	});
	
	$("#search_incident_form").submit (function () {
		$("#saved_searches_table td:gt(1)").fadeIn ();
	});
	
	$("#goto-incident-form").submit (function () {
		id = $("#text-id", this).attr ("value");
		show_incident_details (id);
		if (old_incident)
			$("#tabs > ul").tabs ("load", 1);
		return false;
	});
	
	configure_incident_search_form (<?php echo $config['block_size']?>,
		function (id, name) {
			show_incident_details (id);
		},
		function (form) {
			val = get_form_input_values (form);
			val.push ({name: "page",
					value: "operation/incidents/incident_search"});
			val.push ({name: "show_stats",
					value: 1});
			$("#incident-stats").fadeOut ('normal', function () {
				$(this).empty ();
				jQuery.post ("ajax.php",
					val,
					function (data, status) {
						$("#incident-stats").empty ().append (data).slideDown ();
					},
					"html"
					);
		});
	});
});
</script>

