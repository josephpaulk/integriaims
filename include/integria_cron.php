<?php

// INTEGRIA IMS v1.2
// http://www.integriaims.com
// ===========================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2008 Artica, info@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License (LGPL)
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// NOTICE!.
// YOU NEED TO SETUP THIS VARIABLE TO YOUR INTEGRIA BASE DIR
$config["homedir"] = "/var/www/integria/";
// ===========================================================================

include $config["homedir"]."include/config.php";
include $config["homedir"]."include/languages/language_".$config["language_code"].".php";
require $config["homedir"]."include/functions.php"; // Including funcions.
require $config["homedir"]."include/functions_db.php";
require $config["homedir"]."include/functions_calendar.php";

$config["id_user"] = "System";

$now=date("Y-m-d H:i:s");
$now_unix = date("U");
$compare_timestamp = date ("Y-m-d H:i:s", $now_unix -  $config["notification_period"]);

// SLA Max Response time check
// ===========================
// For each record in tgroup manager
$sql1 = "SELECT * FROM tgroup_manager";
$result1 = mysql_query ($sql1);
while ($row1 = mysql_fetch_array ($result1)){
	// Take incidents for this group and "new" status
    $sla_min_response_limit = date ("Y-m-d H:i:s", $now_unix - ($row1["max_response_hr"]*3600));
    $sql2 = "SELECT * FROM tincidencia WHERE id_grupo = ".$row1["id_group"]." AND estado = 1 AND inicio < '$sla_min_response_limit'";
    $result2 = mysql_query ($sql2);
    while ($row2 = mysql_fetch_array ($result2)){
        // And now verify that there is no other notification sent in $config["notification_period"]
        $sql3 = "SELECT COUNT(*) FROM tevent WHERE type = 'SLA_MAX_RESPONSE_NOTIFY' AND id_item = ".$row2["id_incidencia"]." AND timestamp > '$compare_timestamp'";
        $result3 = mysql_query ($sql3);
        $row3 = mysql_fetch_array ($result3);
        // There is any notification for this interval, if not, raise one
        if ($row3[0] == 0){
            $owner = $row2["id_usuario"];
            $owner_name = give_db_sqlfree_field ("SELECT nombre_real FROM tusuario WHERE id_usuario= '$owner'");
            $group_name = give_db_sqlfree_field ("SELECT nombre FROM tgrupo WHERE id_grupo = ". $row1["id_group"]);
            $destination_mail = give_db_sqlfree_field ("SELECT direccion FROM tusuario WHERE id_usuario = '$owner'");

            $url = $config["base_url"]."/index.php?sec=incidents&sec2=operation/incidents/incident_detail&id=".$row2["id_incidencia"];
            $subject = "Incident #".$row2["id_incidencia"]."[".substr($row2["titulo"],0,20)."...] need inmediate status change (SLA Min.Response Time)";
            $text = "Hello $owner_name \n\n";
            $human_notification_period = give_human_time($row1["max_response_hr"]*3600);
            $text .= "Our SLA policy for incidents of group '$group_name', incidents should be update this status in less than a specific time. For this group this time is configured to ".$human_notification_period."\n\n";
            $text .= "Please connect as soon as possible and update status for this incident. You also could click on following URL: \n\n$url";
            
            insert_event ("SLA_MAX_RESPONSE_NOTIFY", $row2["id_incidencia"]);
            topi_sendmail ($destination_mail, $subject, $text);
        }
    }
}


// SLA Max resolution time check
// ===========================
// For each record in tgroup manager
$sql1 = "SELECT * FROM tgroup_manager";
$result1 = mysql_query ($sql1);
while ($row1 = mysql_fetch_array ($result1)){
	// Take incidents for this group and "new" status
    $sla_limit = date ("Y-m-d H:i:s", $now_unix - ($row1["max_resolution_hr"]*3600));
    // if incident is not closed (6 or 7 in status)
    $sql2 = "SELECT * FROM tincidencia WHERE id_grupo = ".$row1["id_group"]." AND estado NOT IN (6,7) AND inicio < '$sla_limit'";
    $result2 = mysql_query ($sql2);
    while ($row2 = mysql_fetch_array ($result2)){
        // And now verify that there is no other notification sent in $config["notification_period"]
        $sql3 = "SELECT COUNT(*) FROM tevent WHERE type = 'SLA_MAX_RESOLUTION_NOTIFY' AND id_item = ".$row2["id_incidencia"]." AND timestamp > '$compare_timestamp'";
        $result3 = mysql_query ($sql3);
        $row3 = mysql_fetch_array ($result3);
        // There is any notification for this interval, if not, raise one
        if ($row3[0] == 0){
            $owner = $row2["id_usuario"];
            $owner_name = give_db_sqlfree_field ("SELECT nombre_real FROM tusuario WHERE id_usuario= '$owner'");
            $group_name = give_db_sqlfree_field ("SELECT nombre FROM tgrupo WHERE id_grupo = ". $row1["id_group"]);
            $destination_mail = give_db_sqlfree_field ("SELECT direccion FROM tusuario WHERE id_usuario = '$owner'");

            $human_notification_period = give_human_time($row1["max_resolution_hr"]*3600);
            $url = $config["base_url"]."/index.php?sec=incidents&sec2=operation/incidents/incident_detail&id=".$row2["id_incidencia"];
            $subject = "Incident #".$row2["id_incidencia"]."[".substr($row2["titulo"],0,20)."...] need  to be closed (SLA Max. Resolution Time)";
            $text = "Hello $owner_name \n\n";
            $text .= "Our SLA policy for incidents of group '$group_name', says that incidents should be closed (with resolution or not) in less than a specific time. For this group this time is configured to ".$human_notification_period."\n\n";
            $text .= "Please connect as soon as possible and close this incident. You also could click on following URL: \n\n$url";
            
            insert_event ("SLA_MAX_RESOLUTION_NOTIFY", $row2["id_incidencia"]);
            topi_sendmail ($destination_mail, $subject, $text);
            // echo "DEBUG: Enviando el siguiente mail\nDestino: $destination_mail\nSubject: $subject\nTexto: $text\n";
        }
    }
}


// Nº Max of opened incidents for this group reached


// SLA Max resolution time check
// ===========================
// For each record in tgroup manager
$sql1 = "SELECT * FROM tgroup_manager";
$result1 = mysql_query ($sql1);
while ($row1 = mysql_fetch_array ($result1)){
    $group_responsible = $row1["id_user"];
    $group_max_opened = $row1["max_active"];
	$owner_name = give_db_sqlfree_field ("SELECT nombre_real FROM tusuario WHERE id_usuario= '$group_responsible'");
	$group_name = give_db_sqlfree_field ("SELECT nombre FROM tgrupo WHERE id_grupo = ". $row1["id_group"]);
	$destination_mail = give_db_sqlfree_field ("SELECT direccion FROM tusuario WHERE id_usuario = '$group_responsible'");
	// if incident is not closed (6 or 7 in status)
    $sql2 = "SELECT COUNT(*) FROM tincidencia WHERE id_grupo = ".$row1["id_group"]." AND estado NOT IN (6,7)";
    $result2 = mysql_query ($sql2);
    $row2 = mysql_fetch_array ($result2);
    $human_notification_period = give_human_time($config["notification_period"]);
    if ($row2[0] > $group_max_opened){
		$sql3 = "SELECT COUNT(*) FROM tevent WHERE type = 'SLA_MAX_OPEN_NOTIFY' AND id_item = ".$row1["id_group"]." AND timestamp > '$compare_timestamp'";
		$result3 = mysql_query ($sql3);
		$row3 = mysql_fetch_array ($result3);
		if ($row3[0] == 0){
            $url = $config["base_url"]."/index.php?sec=incidents&sec2=operation/incidents/incident";
            $subject = "Too much opened incidents in group $group_name (SLA Max. Opened Incidents)";
            $text = "Hello $owner_name \n\n";
            $text .= "Our SLA policy for incidents of group '$group_name', is that is not possible to have more than $group_max_opened opened incidents. Now you have ".$row2[0]. " non-closed incidents. Please, connect integria and try to close some incidents (with resolution or not). Next notification will occur in ".$human_notification_period."\n\n";
            $text .= "Please connect INTEGRIA as soon as possible. You also could click on following URL: \n\n$url";
            
            insert_event ("SLA_MAX_OPEN_NOTIFY", $row1["id_group"]);
            topi_sendmail ($destination_mail, $subject, $text);
            // echo "DEBUG: Enviando el siguiente mail\nDestino: $destination_mail\nSubject: $subject\nTexto: $text\n";
        }
    }
}



?>