<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2012 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

include ("config.php");
error_reporting(E_ALL & ~E_NOTICE);
ini_set("display_errors", 0);

$operation = get_parameter ("operation", "");
$id = get_parameter ("id", "");

if ($operation == "")
	return;

if ($operation == "read"){
	$issue = get_db_row ("tnewsletter_content", "id", $id);
	$id_newsletter = $issue["id_newsletter"];
	$now = date ("Y-m-d H:i:s");
	
	echo safe_output($issue["html"]);
	
	// Update tracking system
	$sql = "INSERT INTO tnewsletter_tracking (id_newsletter, id_newsletter_content, status, datetime) VALUES ($id_newsletter, $id, 0, '$now')";;
	$result = mysql_query ($sql);
	return;
}
echo "<html><head>";
echo "</head>";
echo "<body style='padding: 15px; margin: 20px;'>";

if ($operation == "subscribe") {

	$newsletter = get_db_row ("tnewsletter", "id", $id);
	
	// safe exit
	if (!isset($newsletter["id"]))
		return;
	
	$now = date ("Y-m-d H:i:s");
	echo "<form method=post action='".$config["base_url"]."/include/newsletter.php'>";
	echo "<h3>";
	echo __("Subscription form for "). " ". $newsletter["name"];
	echo "</h3>";
	
	echo "<p style='width: 500px'><i>";
	echo $newsletter["description"];
	echo "</p></i>";
	
	echo "<table class=databox width=500>";
	echo "<tr><td>".__("Name (optional)");
	echo "<td>";
	echo "<input type=text name='name' size=22>";
	echo "<tr><td>".__("Email");
	echo "<td>";
	echo "<input type=text name='email' size=25>";

	$bool = rand(1,1000);
	
	echo "<td>";
	echo "<input type=submit value='".__("Subscribe me")."'>";	
	echo "<input type=hidden name='validation1' value='".md5($config["dbpass"].$bool)."'>";
	echo "<input type=hidden name='validation2' value='$bool'>";
	echo "<input type=hidden name='operation' value='subscribe_data'>";
	echo "<input type=hidden name='newsletter' value='$id'>";
	
	echo "</table></form>";
	return;
}

if ($operation == "subscribe_data"){

	$validation1 = get_parameter ("validation1");
	$validation2 = get_parameter ("validation2");
	$newsletter = get_parameter ("newsletter");
	$name = get_parameter ("name");
	$email = get_parameter ("email");
	$now = date ("Y-m-d H:i:s");
	
	echo "<h3>". __("Thanks for your subscription. You should receive an email to confirm you have been subscribed to this newsletter")."</h3>";
	
	if ($validation1 == md5($config["dbpass"].$validation2)){
	
		// check if already subscribed
		
		$count = get_db_sql ("SELECT COUNT(id) FROM tnewsletter_address WHERE email = '".$email."' AND id_newsletter = $newsletter");
		
		if (($count == 0) && (check_email_address(safe_output($email)))) {
			$sql = "INSERT INTO tnewsletter_address (id_newsletter, email, name, datetime, status) VALUES ($newsletter, '$email', '$name', '$now',0)";	
			$result = mysql_query ($sql);
			if ($result) {
			
				$newsletter_name = get_db_sql ("SELECT name FROM tnewsletter WHERE id = $newsletter");

				$text .= __("Welcome to")." ".$newsletter_name. " ".__("newsletter"). "\n\n";
				$text .= __("Please use this URL to de-subscribe yourself from this newsletter:")."\n\n";
				$text .= $config["base_url"]."/include/newsletter.php?operation=desubscribe&id=$newsletter";
				$text .= "\n\n".__("Thank you");
				
				integria_sendmail ($email, "Newsletter subscription - $newsletter_name", $text);
			}			
				
		} 
	} 

	return;
}


if ($operation == "desubscribe") {

	$newsletter = get_db_row ("tnewsletter", "id", $id);
	
	// safe exit
	if (!isset($newsletter["id"]))
		return;
	
	$now = date ("Y-m-d H:i:s");
	echo "<form method=post action='".$config["base_url"]."/include/newsletter.php'>";
	echo "<h3>";
	echo __("De-subscription form for "). " ". $newsletter["name"];
	echo "</h3>";
	
	echo "<p style='width: 500px'><i>";
	echo $newsletter["description"];
	echo "</p></i>";
	echo "<p style='width: 500px'>";
	echo __("Please enter here the email address which you're registered on this newsletter");
	echo "</p>";
		
	echo "<table class=databox width=500>";
	echo "<tr><td>".__("Email");
	echo "<td>";
	echo "<input type=text name='email' size=25>";

	$bool = rand(1,1000);
	
	echo "<td>";
	echo "<input type=submit value='".__("Desubscribe me")."'>";	
	echo "<input type=hidden name='validation1' value='".md5($config["dbpass"].$bool)."'>";
	echo "<input type=hidden name='validation2' value='$bool'>";
	echo "<input type=hidden name='operation' value='desubscribe_data'>";
	echo "<input type=hidden name='newsletter' value='$id'>";
	
	echo "</table></form>";
	return;
}


if ($operation == "desubscribe_data"){

	$validation1 = get_parameter ("validation1");
	$validation2 = get_parameter ("validation2");
	$newsletter = get_parameter ("newsletter");
	$email = get_parameter ("email");
	$now = date ("Y-m-d H:i:s");
		
	if ($validation1 == md5($config["dbpass"].$validation2)){
	
		// check if already subscribed
		
		$count = get_db_sql ("SELECT COUNT(id) FROM tnewsletter_address WHERE status = 0 AND email = '".$email."' AND id_newsletter = $newsletter");
		
		if ($count > 0) {
			$sql = "UPDATE tnewsletter_address SET status=1 WHERE id_newsletter = $newsletter AND email = '".$email."'";	
	
			$result = mysql_query ($sql);
			if ($result) {
				sleep(5); // Robot protection
				echo "<h3>".__("You has been desubscribed. Thanks!")."</h3>";			
			}			
				
		} else {
			sleep(5); // Robot protection
			echo "<h3>".__("There is nobody registered with that address")."</h3>";
		} 
	} 

	return;
}


?>

