<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2013 Ártica Soluciones Tecnológicas
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

enterprise_include('include/functions_crm.php');
include_once('include/functions_crm.php');

$read = true;
$write = true;
$manage = true;
$write_permission = true;
$manage_permission = true;
$read_permission = true;
	
$read = enterprise_hook('crm_check_user_profile', array($config['id_user'], 'cr'));
$write = enterprise_hook('crm_check_user_profile', array($config['id_user'], 'cw'));
$manage = enterprise_hook('crm_check_user_profile', array($config['id_user'], 'cm'));
$enterprise = false;

if ($read !== ENTERPRISE_NOT_HOOK) {
	$enterprise = true;
	if (!$read) {
		include ("general/noaccess.php");
		exit;
	}
} 

$id = (int) get_parameter ('id');
$tab = (string) get_parameter("tab");

$title = "";

switch ($tab) {
	case "pipeline":
		$title = __('Lead pipeline');
		break;
	case "search":
		$title = __('Lead search');
		break;
	case "statistics":
		$title =__("Lead search statistics");
		break;		
	default:
		$title = __('Lead pipeline');
}

// Listing of contacts

$new = get_parameter("new");
$delete = get_parameter("delete");

//Don't show header if we are creating a new lead
if ((!$new && !$id && ($tab != "statistics")) || $delete) {

	echo "<div id='lead-search-content'>";
	echo "<h1>".$title;
	echo "<div id='button-bar-title'>";
	echo "<ul>";
	echo "<li>";
	echo "<a href='index.php?sec=customers&sec2=operation/leads/lead&tab=pipeline'>".print_image ("images/icon_lead.png", true, array("title" => __("Lead pipeline")))."</a>";
	echo "</li>";
	echo "<li>";
	echo "<a href='index.php?sec=customers&sec2=operation/leads/lead&tab=search'>".print_image ("images/zoom.png", true, array("title" => __("Search leads")))."</a>";
	echo "</li>";

	if ($tab == "search") {
		echo "<li>";
		echo "<a id='lead_stats_form_submit' href='javascript: changeAction();'>".print_image ("images/chart_bar_dark.png", true, array("title" => __("Search statistics")))."</a>";
		echo "</li>";		
	}
	echo "</ul>";
	echo "</div>";
	echo "</h1>";
}
	
switch ($tab) {
	case "pipeline":
		include("lead_pipeline.php");
		break;
	case "search":
		include("lead_detail.php");
		break;
	case "statistics":
		include("lead_statistics.php");
		break;
	default:
		include("lead_pipeline.php");
}

</script>
