<?php

// Integria 1.1 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2008 Artica Soluciones Tecnologicas

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

// Offset adjustment
if (isset($_GET["offset"]))
	$offset=$_GET["offset"];
else
	$offset=0;

// Delete incident
if (isset($_GET["quick_delete"])) {
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
			echo "<h3 class='suc'>".lang_string ('del_incid_ok')."</h3>";
			audit_db($config["id_user"], $config["REMOTE_ADDR"], "Incident deleted","User ".$id_usuario." deleted incident #".$id_inc);
		} else {
			audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Forbidden","User ".$_SESSION["id_usuario"]." try to delete incident");
			echo "<h3 class='error'>".lang_string ('del_incid_no')."</h3>";
			no_permission();
		}
	}
}

$busqueda="";

$texto_form = get_parameter ("texto");
$incident_form = get_parameter ("incident_id");
$usuario_form = get_parameter("usuario");

// Search tokens for search filter
if ($texto_form != "")
	$busqueda = sprintf ('(titulo LIKE "%%%s" OR epilog LIKE "%%%s" OR descripcion LIKE "%%%s"',
			$texto_form, $texto_form);

if ($usuario_form != ""){
	if ($texto_form != "")
		$busqueda = $busqueda." and ";
	$busqueda= $busqueda." id_usuario = '".$usuario_form."' ";
}

if ($incident_form != "") {
	$busqueda = "id_incidencia = $incident_form";
}

// Filter tokens add to search
if ($busqueda != "")
	$sql1= "WHERE ".$busqueda;
else
	$sql1="";

$filter_estado = get_parameter ("estado", -1);
$filter_grupo = get_parameter ("grupo", -1);
$filter_prioridad = get_parameter ("prioridad", -1);

if (($filter_estado != -1) || ($filter_grupo != -1) || ($filter_prioridad != -1)) {
	if ($filter_estado != -1) {
		if ($sql1 == "")
			$sql1='WHERE estado='.$filter_estado;
		else
			$sql1 =$sql1.' AND estado='.$filter_estado;
	}
	if ($filter_prioridad != -1){
		if ($sql1 == "")
			$sql1='WHERE prioridad='.$filter_prioridad;
		else
			$sql1 =$sql1.' and prioridad='.$filter_prioridad;
	}
	if ($filter_grupo > 1){
		if ($sql1 == "")
			$sql1='WHERE id_grupo='.$filter_grupo ;
		else
			$sql1 =$sql1.' AND id_grupo='.$filter_grupo;
	}
}

$sql0="SELECT * FROM tincidencia ".$sql1." ORDER BY actualizacion DESC";
$sql1_count="SELECT COUNT(id_incidencia) FROM tincidencia ".$sql1;
$sql1=$sql0;
$sql1=$sql1." LIMIT $offset, ". $config["block_size"];

/* Tabs code */
echo '<div id="tabs">';

/* Tabs list */
echo '<ul style="height: 30px;" class="ui-tabs-nav">';
echo '<li class="ui-tabs-selected"><a href="#ui-tabs-3"><span>'.lang_string ('Search').'</span></a></li>';
echo '<li class="ui-tabs-disabled"><a href="index.php"><span>'.lang_string ('Details').'</span></a></li>';
echo '<li class="ui-tabs-disabled"><a href="index.php"><span>'.lang_string ('Tracking').'</span></a></li>';
echo '<li class="ui-tabs-disabled"><a href="index.php"><span>'.lang_string ('Inventory').'</span></a></li>';
echo '<li class="ui-tabs-disabled"><a href="index.php"><span>'.lang_string ('Contact').'</span></a></li>';
echo '<li class="ui-tabs-disabled"><a href="index.php"><span>'.lang_string ('Workunits').'</span></a></li>';
echo '<li class="ui-tabs-disabled"><a href="index.php"><span>'.lang_string ('Files').'</span></a></li>';
echo '</ul>';

/* Tabs first container is manually set, so it loads immediately */
echo '<div id="ui-tabs-3" class="ui-tabs-panel" style="display: block;">';

form_search_incident ();

unset ($table);
$table->class = 'hide result_table listing';
$table->width = '100%';
$table->id = 'incident_search_result_table';
$table->head = array ();
$table->head[0] = "Id";
$table->head[1] = lang_string ("incident");
$table->head[2] = lang_string ("group");
$table->head[3] = lang_string ("status")."<br />".lang_string("resolution");
$table->head[4] = lang_string ("priority");
$table->head[5] = lang_string ("Updated")."<br />".lang_string ("Started");
$table->head[6] = lang_string ("flags");
$table->style = array ();
$table->style[0] = '';

print_table ($table);

echo '<div id="pager" class="hide pager">';
echo '<form>';
echo '<img src="images/control_start_blue.png" class="first" />';
echo '<img src="images/control_rewind_blue.png" class="prev" />';
echo '<input type="text" class="pagedisplay" />';
echo '<img src="images/control_fastforward_blue.png" class="next" />';
echo '<img src="images/control_end_blue.png" class="last" />';
echo '<select class="pagesize">';
echo '<option selected="selected" value="10">10</option>';
echo '<option value="20">20</option>';
echo '<option value="30">30</option>';
echo '<option  value="40">40</option>';
echo '</select>';
echo '</form>';
echo '</div>';

/* End of first tab container */
echo '</div>';


echo '</div>';
/* End of tabs code */

?>

<script type="text/javascript" src="include/js/jquery.metadata.js"></script>
<script type="text/javascript" src="include/js/jquery.tablesorter.js"></script>
<script type="text/javascript" src="include/js/jquery.tablesorter.pager.js"></script>
<script type="text/javascript" src="include/js/integria_incident_search.js"></script>

<script type="text/javascript">

var id_incident;
var old_incident = 0;

function tab_loaded (event, tab) {
	if (tab.index == 1) {
		if (id_incident == old_incident) {
			return;
		}
		/* In integria_incident_search.js */
		configure_incident_form (true, false);
		if ($("#incident-menu").css ('display') != 'none') {
			$("#incident-menu").slideUp ('normal', function () {
				configure_incident_side_menu (id_incident);
				$(this).slideDown ();
			});
		} else {
			configure_incident_side_menu (id_incident);
			$("#incident-menu").slideDown ();
		}
		old_incident = id_incident;
	}
}

function set_rows_click () {
	$("#incident_search_result_table tbody tr").click (function () {
		id_incident = this.id.split ("-").pop ();
		$("#tabs > ul").tabs ("url", 1, "ajax.php?page=operation/incidents/incident_detail&id=" + id_incident);
		$("#tabs > ul").tabs ("url", 2, "ajax.php?page=operation/incidents/incident_tracking&id=" + id_incident);
		$("#tabs > ul").tabs ("url", 3, "ajax.php?page=operation/incidents/incident_inventory_detail&id=" + id_incident);
		$("#tabs > ul").tabs ("url", 4, "ajax.php?page=operation/incidents/incident_inventory_contacts&id=" + id_incident);
		$("#tabs > ul").tabs ("url", 5, "ajax.php?page=operation/incidents/incident_workunits&id=" + id_incident);
		$("#tabs > ul").tabs ("url", 6, "ajax.php?page=operation/incidents/incident_files&id=" + id_incident);
		$("#tabs > ul").tabs ("enable", 1).tabs ("enable", 2).tabs ("enable", 3)
			.tabs ("enable", 4).tabs ("enable", 5).tabs ("enable", 6);
		$("#tabs > ul").tabs ("select", 1);
	});
}

$(document).ready (function () {
	$("#tabs > ul").tabs ({"load" : tab_loaded}).tabs ("disable", 1).tabs ("disable", 2);
	$("#search_incident_form").submit (function () {
		$("#incident_search_result_table").removeClass ("hide");
		values = get_form_input_values (this.id);
		values.push ({name: "page",
				value: "operation/incidents/incident_search"});

		$("table#incident_search_result_table tbody").fadeOut ('normal', function () {
			$(this).empty ();
			jQuery.post ("ajax.php",
				values,
				function (data, status) {
					$("table#incident_search_result_table tbody").empty ().append (data);
					set_rows_click ();
					$("table#incident_search_result_table").trigger ("update").tablesorterPager ({container: $("#pager")});
					$("table#incident_search_result_table tbody").fadeIn ();
					$("#pager").removeClass ("hide").fadeIn ();
				},
				"html"
				);
		});
		return false;
	});
	$("#incident_search_result_table tr th :eq(0)").addClass ("{sorter: 'text'}");
	$("#incident_search_result_table").tablesorter ({ cancelSelection : true});
	$("#button-inventory_name").click (function () {
		show_inventory_search_dialog ("<?php echo lang_string ("Search inventory") ?>",
					function (id, name) {
						$("#hidden-search_id_inventory").attr ("value", id);
						$("#button-inventory_name").attr ("value", name);
						$("#dialog").dialog ("close");
					}
		);
	});
	$("#link_create_incident").click (function () {
		show_add_incident_dialog ();
		return false;
	});
	
});
</script>

