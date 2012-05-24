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

$color = 0;
$id_user = $config["id_user"];
echo "<h2>".__('Event history')."</h2>";

echo "<form method=post>";
echo "<table width=400 class=databox>";
echo "<tr><td><input type=text name=text value='$text'>";
echo "<td><input type=submit value='".__("Search")."'>";
echo "<input type=hidden name=offset value='$offset'>";
echo "</form></table>";


$where = "WHERE 1=1 ";

if ($text != "")
	$where .= "AND (ID_usuario LIKE '%$text%' OR accion LIKE '%$text%' OR descripcion LIKE '%$text%')";
	

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
