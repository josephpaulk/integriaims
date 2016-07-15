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

$campaign = get_db_row("tcampaign", "id", $id);

//echo '<div class="under_tabs_info">'.__("Campaign").': '.$campaign["title"].'</div>';
$table = new stdClass();
$table->class = 'blank';
$table->width = '100%';
$table->data = array ();
$table->valign[0] = "top";
$table->valign[1] = "top";
$table->size = array();
$table->size[0] = "50%";
$table->size[1] = "50%";

//Calculate leads funnel data
$leads_funnel = crm_get_total_leads_funnel("WHERE id_campaign = ".$id);
if(!isset($read)){
	$read = '';
}
if ($read && $enterprise) {
	$leads_funnel = crm_get_user_leads($config['id_user'], $leads_funnel);
}

if ($leads_funnel != false) {
	$data = array();

	$lead_progress = lead_progress_array();

	$total_leads = count($total_leads_array);
	
	foreach ($lead_progress as $key => $name) {
		$data[$key] = array("title" => $name, "completion" => 0);
	}

	//Calculate total number of leads
	$total_leads = 0;
	foreach ($leads_funnel as $lf) {

		if ($lf["progress"] < 100 ||$lf["progress"] == 200) {
			$total_leads = $total_leads + $lf["total_leads"];
		}
	} 

	foreach ($leads_funnel as $lf) {
		$completion = ($lf["total_leads"] / $total_leads) * 100;

		if ($total_leads <= 0) {
			$completion = 0;
		} else {
			$completion = ($lf["total_leads"] / $total_leads) * 100;
		}

		$data[$lf["progress"]]["completion"] = $completion;
		$data[$lf["progress"]]["amount"] = $lf["amount"];
		$data[$lf["progress"]]["total_leads"] = $lf["total_leads"];
	}

	$leads_funnel_content = funnel($data, $config["font"], $ttl);
} else {
	$leads_funnel_content = "<td style='padding-top: 151px; padding-bottom: 151px'>";
	$leads_funnel_content .= __('No data to show');
	$leads_funnel_content .= "</td>";
}

//Print lead's funnel
$leads_country_content = '<tr>' . $leads_funnel_content . '</tr>';
$table->data[0][0] = print_container('funnel', __('Leads Funnel'), $leads_country_content, 'open', true, true, "container_simple_title", "container_simple_div",1,"less_widht");

//ROI
$total_revenue = 0;
if (isset($data[200]["amount"])) {
	$total_revenue = $data[200]["amount"];
}

$expenses = $campaign["expenses"];
if($expenses != 0){
	$roi = (($total_revenue-$expenses) / $expenses) * 100;
} else {
	$roi = 0;
}

//$leads_conversion_rate = "<table class='conversion_rate'>";
$leads_conversion_rate = "<tr>";
$leads_conversion_rate .= "<td class='conversion_value'>";
$leads_conversion_rate .= sprintf("%.2f %%",$roi);
$leads_conversion_rate .= "</td>";
$leads_conversion_rate .= "</tr>";
$leads_conversion_rate .= "<tr>";
$leads_conversion_rate .= "<td>";
$leads_conversion_rate .= __("Total revenue")."<br><br>";
$leads_conversion_rate .= $total_revenue." ".$config["currency"];
$leads_conversion_rate .= "</td>";
$leads_conversion_rate .= "</tr>";
//$leads_conversion_rate .= "</table>";

//$leads_conversion_rate = '<br><div class="pie_frame">' . $leads_conversion_rate . '</div>';
$table->data[0][1] = print_container('conversion_rate', __('ROI'), $leads_conversion_rate, 'open', true, true, "container_simple_title", "container_simple_div no_border",1,"less_widht");

//Email statistics

$email_camp_stats = crm_get_campaign_email_stats($campaign["id"]);

//$email_stats = "<table class='details_table alternate'>";
$email_stats = "<tr>";
$email_stats .= "<td>";
$email_stats .= "<strong>".__("Emails sent")."</strong>";
$email_stats .= "</td>";
$email_stats .= "<td style='text-align:right'>";
$email_stats .= sprintf("%d",$email_camp_stats["sent"]);
$email_stats .= "</td>";
$email_stats .= "</tr>";
$email_stats .= "<tr>";
$email_stats .= "<td>";
$email_stats .= "<strong>".__("Total reads")."</strong>";
$email_stats .= "</td>";
$email_stats .= "<td style='text-align:right'>";
$email_stats .= sprintf("%d",$email_camp_stats["reads"]);
$email_stats .= "</td>";
$email_stats .= "</tr>";
$email_stats .= "<tr>";
$email_stats .= "<td>";
$email_stats .= "<strong>".__("Interest rate")."</strong>".print_help_tip (__("This value could be greater than 100%, it could happend if, for example, the same person reads the email several times, so your mail is very interesting."), true);
$email_stats .= "</td>";
$email_stats .= "<td style='text-align:right'>";
$email_stats .= sprintf("%.2f %%",$email_camp_stats["ratio"]);
$email_stats .= "</td>";
$email_stats .= "</tr>";
//$email_stats .= "</table>";

//$email_stats = '<br><div>' . $email_stats . '</div>';
$table->data[1][0] = print_container('newsletter_rate', __('Newsletter statistics'), $email_stats, 'open', true, true, "container_simple_title", "container_simple_div",1,"less_widht");

if(!isset($total_leads)){
	$total_leads = 0;
}
//$lead_stats = "<table class='details_table alternate'>";
$lead_stats = "<tr>";
$lead_stats .= "<td>";
$lead_stats .= "<strong>".__("Total leads")."</strong>";
$lead_stats .= "</td>";
$lead_stats .= "<td style='text-align:right'>";
$lead_stats .= sprintf("%d",$total_leads);
$lead_stats .= "</td>";
$lead_stats .= "</tr>";
$lead_stats .= "<tr>";
$lead_stats .= "<td>";
$lead_stats .= "<strong>".__("Total success")."</strong>";
$lead_stats .= "</td>";
$lead_stats .= "<td style='text-align:right'>";
if(!isset($data[200]["total_leads"])){
	$data[200]["total_leads"] = 0;
}
$leads_success = $data[200]["total_leads"];

$lead_stats .= sprintf("%d",$leads_success);
$lead_stats .= "</td>";
$lead_stats .= "</tr>";
$lead_stats .= "<tr>";
$lead_stats .= "<td>";
$lead_stats .= "<strong>".__("Conversion rate")."</strong>";
$lead_stats .= "</td>";
$lead_stats .= "<td style='text-align:right'>";

if ($total_leads) {
	$conversion_rate = ($leads_success/$total_leads)*100;
} else {
	$conversion_rate = 0;
}

$lead_stats .= sprintf("%.2f %%",$conversion_rate);
$lead_stats .= "</td>";
$lead_stats .= "</tr>";
//$lead_stats .= "</table>";

//lead_stats = '<br><div style="padding-left: 20px;">' . $lead_stats . '</div>';
$table->data[1][1] = print_container('lead_rate', __('Lead statistics'), $lead_stats, 'open', true, true, "container_simple_title", "container_simple_div",1,"less_widht");


print_table($table);

?>
