<?php

// Integria 2.0 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2011 Artica Soluciones Tecnologicas
// Copyright (c) 2008 Esteban Sanchez

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// Load global vars

check_login ();

require_once ('include/functions_incidents.php');

echo "<div id='incident-search-content'>";
echo "<h1>".__('Incident search');
echo "<div id='button-bar-title'>";
echo "<ul>";
echo "<li>";
echo "<a id='stats_form_submit' href='#'>".__("Search statistics")."</a>";
echo "</li>";
echo "</ul>";
echo "</div>";
echo "</h1>";

$search_form = (bool) get_parameter ('search_form');
$create_custom_search = (bool) get_parameter ('save-search');
$delete_custom_search = (bool) get_parameter ('delete_custom_search');
$id_search = get_parameter ('saved_searches');

//Filter auxiliar array 
$filter_form = false;

/* Create a custom saved search*/
if ($create_custom_search && !$id_search) {
	$form_values = get_parameter ('form_values');
	$search_name = (string) get_parameter ('search_name');
	
	$result = create_custom_search ($search_name, 'incidents', $filter);
	
	if ($result === false) {
		echo '<h3 class="error">'.__('Could not create custom search').'</h3>';
	} else {
		echo '<h3 class="suc">'.__('Custom search saved').'</h3>';
	}
}

/* Get a custom search*/
if ($id_search && !$delete_custom_search) {
	
	$search = get_custom_search ($id_search, 'incidents');
	
	if ($search) { 
	
		if ($search["form_values"]) {
	
			$filter = unserialize($search["form_values"]);
			$filter_form = $filter;
			
			echo '<h3 class="suc">'.sprintf(__('Custom search "%s" loaded'), $search["name"]).'</h3>';
		} else {
			echo '<h3 class="error">'.sprintf(__('Could not load "%s" custom search'), $search["name"]).'</h3>';	
		}
	} else {
		echo '<h3 class="error">'.__('Could not load custom search').'</h3>';
	}
}

/* Delete a custom saved search via AJAX */
if ($delete_custom_search) {

	$sql = sprintf ('DELETE FROM tcustom_search
		WHERE id_user = "%s"
		AND id = %d',
		$config['id_user'], $id_search);
	$result = process_sql ($sql);
	if ($result === false) {
		echo '<h3 class="error">'.__('Could not delete custom search').'</h3>';
	} else {
		echo '<h3 class="suc">'.__('Custom search deleted').'</h3>';
	}
}

//FORM AND TABLE TO MANAGE CUSTOM SEARCHES
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
$table->data[0][1] = print_select_from_sql ($sql, 'saved_searches', $id_search, '', __('Select'), 0, true);

//If a custom search was selected display cross
if ($id_search) {
	$table->data[0][1] .= '<a href="index.php?sec=incidents&sec2=operation/incidents/incident_search&delete_custom_search=1&saved_searches='.$id_search.'">';
	$table->data[0][1] .= '<img src="images/cross.png" /></a>';
}
$table->data[0][2] = __('Save current search');
$table->data[0][3] = print_input_text ('search_name', '', '', 10, 20, true);
$table->data[0][4] = print_submit_button (__('Save'), 'save-search', false, 'class="sub next"', true);

echo '<form id="saved-searches-form" method="post" action="index.php?sec=incidents&sec2=operation/incidents/incident_search">';
foreach ($filter as $key => $value) {
	print_input_hidden ("search_".$key, $value);
}
print_table ($table);
echo '</form>';

/* Show search form via AJAX */

form_search_incident (false, $filter_form);

echo '<div id="pager" class="hide pager">';
echo '<form>';
echo '<img src="images/control_start_blue.png" class="first" />';
echo '<img src="images/control_rewind_blue.png" class="prev" />';
echo '<input type="text" class="pager pagedisplay" size=5 />';
echo '<img src="images/control_fastforward_blue.png" class="next" />';
echo '<img src="images/control_end_blue.png" class="last" />';
echo '<select class="pager pagesize" style="display:none">';
echo '<option selected="selected" value="5">5</option>';
echo '</select>';
echo '</form>';
echo '</div>';
	
$incidents = filter_incidents ($filter);

$params = "";

foreach ($filter as $key => $value) {
	$params .= "&search_".$key."=".$value;
}

//We need this auxiliar variable to use later for footer pagination
$incidents_aux = $incidents;

$incidents = print_array_pagination ($incidents_aux, "index.php?sec=incidents&sec2=operation/incidents/incident_search".$params);

$statuses = get_indicent_status ();
$resolutions = get_incident_resolutions ();

// ----------------------------------------
// Here we print the result of the search
// ----------------------------------------
echo '<table width="100%" cellpadding="0" cellspacing="0" border="0px" class="result_table listing" id="incident_search_result_table">';

echo '<thead>';
echo "<tr>";
echo "<th>";
echo "</th>";
echo "<th>";
echo __('ID');
echo "</th>";
echo "<th>";
echo __('SLA');
echo "</th>";
echo "<th>";
echo __('Incident');
echo "</th>";
echo "<th>";
echo __('Group')."<br><i>".__('Company')."</i>";
echo "</th>";
echo "<th>";
echo __('Status')."<br><i>".__('Resolution')."</i>";
echo "</th>";
echo "<th>";
echo __('Priority');
echo "</th>";
echo "<th>";
echo __('Updated')."<br><i>".__('Started')."</i>";
echo "</th>";
echo "<th>";
echo __('Flags');
echo "</th>";

if ($config["show_creator_incident"] == 1)
	echo "<th>";
	echo __('Creator');	
	echo "</th>";
if ($config["show_owner_incident"] == 1)
	echo "<th>";
	echo __('Owner');	
	echo "</th>";

echo "</tr>";
echo '</thead>';
echo "<tbody>";

if ($incidents == false) {
	echo '<tr><td colspan="11">'.__('Nothing was found').'</td></tr>';
} else {

	foreach ($incidents as $incident) {
		/* We print the rows directly, because it will be used in a sortable
		   jQuery table and it only needs the rows */

		if ($incident["estado"] < 3 )
			$tr_status = 'class="red"';
		elseif ($incident["estado"] < 7 )
			$tr_status = 'class="yellow"';
		else
			$tr_status = 'class="green"';

		echo '<tr '.$tr_status.' id="incident-'.$incident['id_incidencia'].'"';

		echo " style='border-bottom: 1px solid #ccc;' >";
		echo '<td>';
		print_checkbox_extended ('incidentcb-'.$incident['id_incidencia'], $incident['id_incidencia'], false, '', '', 'class="cb_incident"');
		echo '</td>';
		echo '<td>';
		echo '<strong><a href="index.php?sec=incidents&sec2=operation/incidents/incident&id='.$incident['id_incidencia'].'">#'.$incident['id_incidencia'].'</a></strong></td>';
		
		// SLA Fired ?? 
		if ($incident["affected_sla_id"] != 0)
			echo '<td width="25"><img src="images/exclamation.png" /></td>';
		else
			echo '<td></td>';
		
		echo '<td><strong><a href="index.php?sec=incidents&sec2=operation/incidents/incident&id='.$incident['id_incidencia'].'">'.$incident['titulo'].'</a></strong></td>';
		echo '<td>'.get_db_value ("nombre", "tgrupo", "id_grupo", $incident['id_grupo']);
		if ($config["show_creator_incident"] == 1){	
			$id_creator_company = get_db_value ("id_company", "tusuario", "id_usuario", $incident["id_creator"]);
			if($id_creator_company != 0) {
				$company_name = (string) get_db_value ('name', 'tcompany', 'id', $id_creator_company);	
				echo "<br><span style='font-size:11px;font-style:italic'>$company_name</span>";
			}
		}
		echo '</td>';
		$resolution = isset ($resolutions[$incident['resolution']]) ? $resolutions[$incident['resolution']] : __('None');

		echo '<td class="f9"><strong>'.$statuses[$incident['estado']].'</strong><br /><em>'.$resolution.'</em></td>';

		// priority
		echo '<td>';
		print_priority_flag_image ($incident['prioridad']);
		$last_wu = get_incident_lastworkunit ($incident["id_incidencia"]);
		if ($last_wu["id_user"] == $incident["id_creator"]){
			echo "<br><img src='images/comment.gif'>";
		}

		echo '</td>';
		
		echo '<td class="f9">'.human_time_comparation ($incident["actualizacion"]).'<br /><em>';
		echo human_time_comparation ($incident["inicio"]).'</em></td>';
		
		/* Workunits */
		echo '<td class="f9">';
		if ($incident["id_task"] > 0){
			$id_project = get_db_value ("id_project", "ttask", "id", $incident["id_task"]);
			$id_task = $incident["id_task"] ;
			echo "<a href='index.php?sec=projects&sec2=operation/projects/task_detail&id_project=$id_project&id_task=$id_task&operation=view'><img src='images/bricks.png' border=0></a>";
		}
		$timeused = get_incident_workunit_hours ($incident["id_incidencia"]);
		$incident_wu = $in_wu = get_incident_count_workunits ($incident["id_incidencia"]);
		if ($incident_wu > 0) {
			echo '<img src="images/award_star_silver_1.png" title="'.$timeused.' Hr / '.$incident_wu.' WU">';
		}

		/* Files */
			$files = get_number_files_incident ($incident["id_incidencia"]);
			if ($files)
					echo '&nbsp;<img src="images/disk.png"
							title="'.$files.' '.__('Files').'" />';
			
			/* Mail notification */
			$mail_check = get_db_value ('notify_email', 'tincidencia',
									'id_incidencia', $incident["id_incidencia"]);
			if ($mail_check > 0)
					echo '&nbsp;<img src="images/email_go.png"
							title="'.__('Mail notification').'" />';

		echo "&nbsp;";
		/* People involved in the incident  */
			$people = people_involved_incident ($incident["id_incidencia"]);
			print_help_tip (implode ('&nbsp;', $people), false, 'tip_people');


		/* Last WU */
		echo "<br>";
		if ($incident_wu > 0){
			echo "($incident_wu) ";
		}

		if ($last_wu["id_user"] == $incident["id_creator"]){
			echo "<b>".$last_wu["id_user"]."</b>&nbsp;";
		} else {
			echo $last_wu["id_user"];
		}
		echo '</td>';
		
		if ($config["show_creator_incident"] == 1){	
			echo "<td class='f9'>";
			$incident_creator = $incident["id_creator"];
			echo substr($incident_creator,0,12);
			echo "</td>";
		}
		
		if ($config["show_owner_incident"] == 1){	
			echo "<td class='f9'>";
			$incident_owner = $incident["id_usuario"];
			echo substr($incident_owner,0,12);
			echo "</td>";
		}
		
		echo '</tr>';
	}
}
echo "</tbody>";
echo "</table>";

$incidents = print_array_pagination ($incidents_aux, "index.php?sec=incidents&sec2=operation/incidents/incident_search".$params);

echo "<br>";
echo sprintf(__('Max incidents shown: %d'),$config['limit_size']);
echo print_help_tip (sprintf(__('You can change this value by changing %s parameter in setup'),"<b>".__("Max. Incidents by search")."</b>", true));

/* Add a form to carry filter between statistics and search views */
echo '<form id="stats_form" method="post" action="index.php?sec=incidents&sec2=operation/incidents/incident_search&option=stats" style="clear: both">';
foreach ($filter as $key => $value) {
	print_input_hidden ("search_".$key, $value);
}
echo "</form>";

echo '<br><h2 class="incident_dashboard" onclick="toggleDiv (\'massive-oper-incidents\')">'.__('Massive operations over selected items').'</h2>';
echo "<div id='massive-oper-incidents' style='display:none'>";

$table->class = 'result_table listing';
$table->width = '100%';
$table->id = 'incident_massive';
$table->data = array();
$table->style = array ();

$table->head[0] = __('Status');
$table->head[1] = __('Priority');
$table->head[2] = __('Resolution');
$table->head[3] = __('Assigned user');
$table->data[0][0] = combo_incident_status (-1, 0, 0, true, true);
$table->data[0][1] = print_select (get_priorities (),'mass_priority', -1, '', __('Select'), -1, true);
$table->data[0][2] = combo_incident_resolution ($resolution, false, true, true);
$table->data[0][3] = print_select_from_sql('SELECT id_usuario, nombre_real FROM tusuario;', 'mass_assigned_user', '0', '', __('Select'), -1, true);

print_table ($table);

echo "<div style='width:".$table->width."'>";
print_submit_button (__('Update selected items'), 'massive_update', false, 'class="sub next" style="float:right;');
echo "</div>";
echo "</div>";


?>

<script type="text/javascript" src="include/js/jquery.metadata.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
<script type="text/javascript" src="include/js/integria_incident_search.js"></script>


<script>
//Javascript search form configuration

$(document).ready(function () {
	$("#stats_form_submit").click(function (event) {
		event.preventDefault();
		$("#stats_form").submit();
	});
	
	$("a.show_advanced_search").click (function () {
		table = $("#search_incident_form").children ("table");
		$("tr", table).show ();
		$(this).remove ();
		return false;
	});
	
	$("#text-search_first_date").datepicker ({
		beforeShow: function () {
			return {
				maxDate: $("#text-search_last_date").datepicker ("getDate")
			};
		}
	});
	
	$("#text-search_last_date").datepicker ({
		beforeShow: function () {
			return {
				minDate: $("#text-search_first_date").datepicker ("getDate")
			};
		}
	});
	
	$("#saved_searches").change(function() {
		$("#saved-searches-form").submit();
	});
	
	//JS for massive operations
	$(".cb_incident").click(function(event) {
		event.stopPropagation();
	});	
	
	$("#submit-massive_update").click(function(event) {
		process_massive_updates();
	});	
});
</script>
