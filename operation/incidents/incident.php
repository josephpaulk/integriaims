<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2011 Ártica Soluciones Tecnológicas
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

check_login ();

if (! give_acl ($config['id_user'], 0, "IR")) {
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access incident viewer");
	require ("general/noaccess.php");
	exit;
}

// Take input parameters
$id = (int) get_parameter ('id');
$do_search = (bool) get_parameter ('do_search');
/* Search new incidents if no other search is forced and no id is set */
$do_search_news = false;
if (! $do_search && ! $id) {
	$do_search_news = true;
}
// Delete incident
if (isset ($_POST["quick_delete"])) {
	$id_inc = $_POST["quick_delete"];
	$sql2="SELECT * FROM tincidencia WHERE id_incidencia=".$id_inc;
	$result2=mysql_query($sql2);
	$row2=mysql_fetch_array($result2);
	if ($row2) {
		$id_author_inc = $row2["id_usuario"];
		$email_notify = $row2["notify_email"];
		if (give_acl ($config['id_user'], $row2["id_grupo"], "IM") || $config['id_user'] == $id_author_inc) {
			borrar_incidencia($id_inc);
			if ($email_notify == 1){
				// Email notify to all people involved in this incident
				mail_incident ($id_inc, $config['id_user'], "", 0, 3);
			}

			echo "<h3 class='suc'>".__('Incident successfully deleted')."</h3>";
			audit_db($config["id_user"], $config["REMOTE_ADDR"], "Incident deleted","User ".$config['id_user']." deleted incident #".$id_inc);
		} else {
			audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Forbidden","User ".$config['id_user']." try to delete incident");
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
$table->width = '90%';
$table->class = 'search-table';
$table->size = array ();
$table->size[0] = '120px';
$table->style = array ();
$table->style[0] = 'font-weight: bold';
$table->style[2] = 'font-weight: bold';
$table->data = array ();
$table->data[0][0] = __('Custom searches');
$sql = sprintf ('SELECT id, name FROM tcustom_search
	WHERE id_user = "%s"
	AND section = "incidents"
	ORDER BY name',
	$config['id_user']);
$table->data[0][1] = print_select_from_sql ($sql, 'saved_searches', 0, '', __('Select'), 0, true);
$table->data[0][1] .= '<a href="ajax.php" style="display:none" id="delete_custom_search">';
$table->data[0][1] .= '<img src="images/cross.png" /></a>';
$table->data[0][2] = __('Save current search');
$table->data[0][3] = print_input_text ('search_name', '', '', 10, 20, true);
$table->data[0][4] = print_submit_button (__('Save'), 'save-search', false, 'class="sub next"', true);

echo '<form id="saved-searches-form">';
print_table ($table);
echo '</form>';

form_search_incident ();

unset ($table);

/* Loading message is always shown at first because we run a default search */
echo '<div id="loading">'.__('Loading');
echo '... <img src="images/wait.gif" /></div>';

$table->class = 'hide result_table listing';
$table->width = '100%';
$table->id = 'incident_search_result_table';
$table->head = array ();
$table->head[0] = __('ID');
$table->head[1] = __('SLA');
$table->head[2] = __('Incident');
$table->head[3] = __('Group');
$table->head[4] = __('Status')."<br /><em>".__('Resolution')."</em>";
$table->head[5] = __('Priority');
$table->head[6] = __('Updated')."<br /><em>".__('Started')."</em>";
$table->head[7] = __('Details');
if ($config["show_creator_incident"] == 1)
	$table->head[8] = __('Creator');	
if ($config["show_owner_incident"] == 1)
	$table->head[9] = __('Owner');	
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
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
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
			if (!confirm ("<?php echo __('Are you sure?'); ?>"))
				return false;
			jQuery.get (
				$(this).attr ("href"),
				null,
				function (data) {
					result_msg (data);
					$(tr).hide ().empty ();
				}
			);
			
			return false;
		});
	}
	
	$(".result").empty ();
}

function check_incident (id) {
	values = Array ();
	values.push ({name: "page",
		value: "operation/incidents/incident_detail"});
	values.push ({name: "id",
		value: id});
	values.push ({name: "check_incident",
		value: 1});
	jQuery.get ("ajax.php",
		values,
		function (data, status) {
			if (data == 1) {
				show_incident_details (id);
			} else {
				result_msg_error ("<?php echo __('Unable to load incident')?> #" + id);
			}
		},
		"html"
	);
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
	if (tabs.data ("selected.tabs") == 1) {
		$("#tabs > ul").tabs ("load", 1);
	} else {
		$("#tabs > ul").tabs ("select", 1);
	}
}

var tabs;
var first_search = false;

$(document).ready (function () {
	tabs = $("#tabs > ul").tabs ({"load" : tab_loaded});
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
		if (this.value == 0) {
			$("#delete_custom_search").hide ();
			return;
		}
		$("#delete_custom_search").show ();
		
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
	
	$("#delete_custom_search").click (function () {
		id_search = $("#saved_searches").attr ("value");
		values = Array ();
		values.push ({name: "page", value: "operation/incidents/incident_search"});
		values.push ({name: "delete_custom_search", value: 1});
		values.push ({name: "id_search", value: id_search});
		
		jQuery.get ("ajax.php",
			values,
			function (data, status) {
				result_msg (data);
				$("#delete_custom_search").hide ();
				$("#saved_searches").attr ("value", 0);
				$("option[value="+id_search+"]", "#saved_searches").remove ();
			},
			"html"
		);
		return false;
	});
	
	$("#goto-incident-form").submit (function () {
		id = $("#text-id", this).attr ("value");
		check_incident (id);
		return false;
	});
	
	configure_incident_search_form (<?php echo $config['block_size']?>,
		function (id, name) {
			check_incident (id);
		},
		function (form) {
			val = get_form_input_values (form);
			
			val.push ({name: "page",
					value: "operation/incidents/incident_search"});
			val.push ({name: "show_stats",
					value: 1});
			$("#incident-stats").hide ().empty ();
			jQuery.post ("ajax.php",
				val,
				function (data, status) {
					$("#incident-stats").empty ().append (data).slideDown ();
					if (first_search) {
						$("#search_status").attr ("value", 0);
						first_search = false;
					}
					$(".incident_link").click (function () {
						id = this.id.split ("_").pop ();
						check_incident (id);
						return false;
					});
				},
				"html"
			);
		}
	);
<?php if ($do_search_news) : ?>
	$("#search_status").attr ("value", -10);
	$("#search_incident_form").submit ();

<?php elseif ($do_search) : ?>
	$("#search_incident_form").submit ();
<?php endif; ?>
});
</script>
