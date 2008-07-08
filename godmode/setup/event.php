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

check_login();

if (give_acl($config["id_user"], 0, "KM")==0) {
    audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access KB Management");
    require ("general/noaccess.php");
    exit;
}

$color = 0;
$id_user = $config["id_user"];
echo "<h2>".lang_string ("Event history")."</h2>";	

// Pagination
$offset = get_parameter ("offset",0);
$total_events = give_db_sqlfree_field ("SELECT COUNT(id) FROM tevent");
pagination ($total_events, "index.php?sec=godmode&sec2=godmode/setup/event", $offset);

echo "<table cellpadding=4 cellspacing=4 width=710 class='databox'>";
echo "<th>".lang_string ("Type")."</th>";
echo "<th>".lang_string ("User")."</th>";
echo "<th>".lang_string ("Extended Info")."</th>";
echo "<th>".lang_string ("Timestamp")."</th>";

$sql_1="SELECT * FROM tevent ORDER by timestamp DESC LIMIT $offset,".$config["block_size"];
$result_1=mysql_query($sql_1);
while ($row=mysql_fetch_array($result_1)){
    if ($color == 1){
			$tdcolor = "datos";
			$color = 0;
		}
	else {
		$tdcolor = "datos2";
		$color = 1;
	}
	echo "<tr>";
    echo "<td class='$tdcolor' valign='top'>";
    echo $row["type"];
    echo "<td class='$tdcolor' valign='top'>";
    echo $row["id_user"];
    echo "<td class='$tdcolor' valign='top'>";
    switch ($row["type"]){
        case "SLA_MAX_RESOLUTION_NOTIFY":
            echo lang_string("Incident"). " :" .give_db_sqlfree_field ("SELECT titulo FROM tincidencia WHERE id_incidencia = ". $row["id_item"]);
            break;
        case "SLA_MAX_RESPONSE_NOTIFY":
            echo lang_string("Incident"). " :" . give_db_sqlfree_field ("SELECT titulo FROM tincidencia WHERE id_incidencia = ". $row["id_item"]);
            break;
        case "SLA_MAX_OPEN_NOTIFY":
            echo lang_string("Group"). " :" .give_db_sqlfree_field ("SELECT nombre FROM tgrupo WHERE id_grupo = ". $row["id_item"]);
            break;
        default:
            echo $row["id_item3"];
    }
    echo "<td class='".$tdcolor."f9' valign='top' >";
    echo $row["timestamp"];
}
echo "</table>";
?>
