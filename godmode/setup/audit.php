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

global $config;

check_login ();

if (! give_acl ($config["id_user"], 0, "IM")) {
	audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access Audit Log viewer");
	require ("general/noaccess.php");
	exit;
}

$text = get_parameter ("text", "");
$action = get_parameter ("action", "");
$date_from = get_parameter ("date_from", "");
$date_to = get_parameter ("date_to", "");

$color = 0;
$id_user = $config["id_user"];
echo "<h1>".__('Audit log')."</h1>";

$where = 'WHERE 1=1 ';

if ($text != "")
	$where .= sprintf ('AND (ID_usuario LIKE "%%%s%%" OR accion LIKE "%%%s%%" OR descripcion LIKE "%%%s%%" OR IP_origen LIKE "%%%s%%" OR extra_info LIKE "%%%s%%")', $text, $text, $text, $text, $text);
	
if ($action != "") {
	$where .= sprintf (' AND accion ="%s"', $action);
}

if ($date_from != "") {
	$timestamp_from = strtotime($date_from);
	$where .= sprintf(' AND utimestamp >= %d',$timestamp_from);
}

if ($date_to != "") {
	$timestamp_to = strtotime($date_to);
	$where .= sprintf(' AND utimestamp <= %d',$timestamp_to);
}

$actions_arr = get_db_all_rows_sql ("SELECT DISTINCT(accion) FROM tsesion ORDER BY accion ASC");

if ($actions_arr == false) {
	$actions_arr = array();
}
foreach ($actions_arr as $act) {
	$actions[$act['accion']] = $act['accion'];
}

$table_search->width = '99%';
$table_search->class = 'search-table';
$table_search->style = array ();
$table_search->colspan = array ();
$table_search->style[0] = 'font-weight: bold';
$table_search->style[1] = 'font-weight: bold';
$table_search->style[2] = 'font-weight: bold';
$table_search->style[3] = 'font-weight: bold';
$table_search->data = array ();
$table_search->data[0][0] = __('Search');
$table_search->data[0][1] = print_input_text ("text", $text, "", 25, 100, true);
$table_search->data[0][2] = __('Action');
$table_search->data[0][3] = print_select ($actions, 'action', $action, '', __('Any'), '', true, false, true, '');
$table_search->data[1][0] = __('Date from');
$table_search->data[1][1] = print_input_text ('date_from', $date_from, '', 10, 20, true);
$table_search->data[1][2] = __('Date to');
$table_search->data[1][3] = print_input_text ('date_to', $date_to, '', 10, 20, true);
$table_search->data[2][0] = "";
$table_search->data[2][1] = "";
$table_search->data[2][2] = "";
$table_search->data[2][3] = print_submit_button (__('Search'), 'search_btn', false, 'class="sub search"', true);
$where_clause = $where;
$where_clause = str_replace(array("\r", "\n"), '', $where_clause);
$table_search->data[2][3] .= print_button(__('Export to CSV'), '', false, 'window.open(\'include/export_csv.php?export_csv_audit=1&where_clause=' . str_replace('"', "\'", $where_clause) . '\')', 'class="sub csv"', true);
$table_search->colspan[2][0] = 3;


echo "<form method=post>";
print_table($table_search);
echo "</form>";
	

// Pagination
$offset = (int) get_parameter ("offset");
$total_events = get_db_sql ("SELECT COUNT(ID_sesion) FROM tsesion $where");
pagination ($total_events, "index.php?sec=godmode&sec2=godmode/setup/audit&text=$text", $offset);

$table->width = '99%';
$table->class = 'listing';
$table->head = array ();
$table->head[0] = __('Accion');
$table->head[1] = __('User');
$table->head[2] = __('IP');
$table->head[3] = __('Description');
$table->head[4] = __('Extra info');
$table->head[5] = __('Timestamp');
$table->data = array ();

$sql = sprintf ('SELECT * FROM tsesion %s
	ORDER by utimestamp
	DESC LIMIT %d, %d',
	$where, $offset, $config["block_size"]);
		
$events = get_db_all_rows_sql ($sql);
if ($events === false)
	$events = array ();
foreach ($events as $event) {
	$data = array ();
	
	$data[0] = $event["accion"];
	$data[1] = $event["ID_usuario"];
	$data[2] = $event["IP_origen"];
	$data[3] = $event["descripcion"];
	$data[4] = $event["extra_info"];
	$data[5] = $event["fecha"];
	
	array_push ($table->data, $data);
}
print_table ($table);
?>

<script type="text/javascript" src="include/js/jquery.ui.slider.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
<script type="text/javascript" src="include/js/integria_date.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.autocomplete.js"></script>
<script type="text/javascript" src="include/js/integria_incident_search.js"></script>

<script type="text/javascript">

add_ranged_datepicker ("#text-date_from", "#text-date_to", null);

</script>
