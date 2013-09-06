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

$read = true;

$read = enterprise_hook('crm_check_user_profile', array($config['id_user'], 'cr'));
$enterprise = false;

if ($read !== ENTERPRISE_NOT_HOOK) {
	$enterprise = true;
	if (!$read) {
		include ("general/noaccess.php");
		exit;
	}
} 

$id = (int) get_parameter ('id');

$table->width="100%";
$table->data = array();
$table->head = array();
$table->size = array();
$table->class="pipeline-table";

$table->size[0] = "20%";
$table->size[1] = "20%";
$table->size[2] = "20%";
$table->size[3] = "20%";
$table->size[4] = "20%";

$progress = lead_progress_array ();

$i = 0;
foreach ($progress as $k => $v) {
	//Only display open leads
	if ($k > 80) {
		break;
	}

	//Get statistics for $k status
	$leads = get_db_all_rows_filter ('tlead', array ('progress' => $k, "owner" => $config["id_user"]));

	if(!$leads) {
		$leads = array();
	}

	$num_leads = count($leads);

	$sql = sprintf("SELECT SUM(estimated_sale) as amount FROM tlead WHERE progress = %d AND owner = '%s'", $k, $config["id_user"]);

	$amount = process_sql($sql);

	$amount = $amount[0]["amount"];

	if (!$amount) {
		$amount = 0;
	}

	$table_header = "<table class='pipeline-header'>";
	$table_header .= "<tr>";
	$table_header .= "<td class='pipeline-header-title'>";
	$table_header .= $v;
	$table_header .= "</td>";
	$table_header .= "<td rowspan='2'>";
	$table_header .= "<div class='pipeline-arrow'></div>";
	$table_header .= "</td>";
	$table_header .= "</tr>";
	$table_header .= "<tr>";
	$table_header .= "<td class='pipeline-header-subtitle'>";
	$table_header .= $amount." ".$config["currency"]." ".__("from")." ".$num_leads." ".__("leads"); 
	$table_header .= "</td>";
	$table_header .= "</tr>";
	$table_header .="</table>";

	$table->head[$i] = $table_header;

	$lead_list = "<ul class='pipeline-list'>";

	foreach ($leads as $l) {
		$lead_list .= "<li class='pipeline-list'>";
		$lead_list .= "<a href='index.php?sec=customers&sec2=operation/leads/lead&tab=search&id=".$l["id"]."'>";


		$name = strtolower($l["fullname"]);	

		//Adjust text truncate for very long names
		$name_size = strlen(safe_output($l["fullname"]));

		$char_truncate = 23;
	
		if ($name_size > 23) {
			$char_truncate = 20;
		}	
		
		$name = ui_print_truncate_text($name, $char_truncate, false, true);
	
		$lead_list .= "<div class='pipeline-list-title'>".$name."</div>";
		$lead_list .= "<div class='pipeline-list-subtitle'>";
		$lead_list .= $l["estimated_sale"]." ".$config["currency"];

		$product_name = __("None");
		$product_icon = "misc.png";

		if ($l["id_category"]) {
			$product_name = get_db_value("name", "tkb_product", "id", $l["id_category"]);

			$product_icon = get_db_value("icon", "tkb_product", "id", $l["id_category"]);
		}

		$product_img = "<img class='pipeline-product-icon' src='images/products/".$product_icon."' title='".$product_name."' alt='".$product_name."'>";

		$lead_list .= $product_img;
		$lead_list .= "</div>";
		$lead_list .= "</a>";
		$lead_list .= "</li>";
	}

	$lead_list .= "</ul>";

	$table->data[2][$i] = $lead_list;
	$i++;
}

print_table($table);

?>
