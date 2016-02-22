<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2012 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;


check_login ();

if (! give_acl ($config["id_user"], 0, "VM")) {
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access campaign management");
	require ("general/noaccess.php");
	exit;
}

include("include/functions_crm.php");

$tab = get_parameter("tab", "list");
$id = get_parameter("id");

$title = "";
$subtitle = "";
switch ($tab) {
	case "list":
		$title = __("Campaigns");
		break;
	case "editor":
		$new = get_parameter("new");

		if ($new) {
			$title = __("Campaign creation");
		} else {
			$title = __("Campaign edition");
		}	
		break;
	case "stats":
		$title = __("Campaign statistics");
		break;
}

echo "<h1>".$title;

if (($tab == "editor" and $id) || $tab == "stats") {
	echo "<div id='button-bar-title'>";
	echo "<ul>";
	echo '<li>';
	echo '<a href="index.php?sec=customers&sec2=operation/campaign/campaign&tab=editor&id='.$id.'">'.print_image("images/application_edit.png", true, array("title" => __('Edit'))).'</a>';
	echo '</li>';
	echo '<li>';
	echo '<a href="index.php?sec=customers&sec2=operation/campaign/campaign&tab=stats&id='.$id.'">'.print_image("images/chart_bar_dark.png", true, array("title" => __('Statistics'))).'</a>';
	echo '</li>';
	echo "</ul>";
	echo "</div>";
}

echo "</h1>";

//Select view
switch ($tab) {
	case "list":
		include("campaign_list.php");
		break;
	case "editor":
		include("campaign_editor.php");
		break;
	case "stats":
		include("campaign_stats.php");
		break;
}

?>
