<?php 

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// Load global vars
global $config;

if (check_login() != 0) {
    audit_db("Noauth",$config["REMOTE_ADDR"], "No authenticated access","Trying to access setup ");
    require ("general/noaccess.php");
    exit;
}
    
if (dame_admin($config["id_user"]) == 0){
    audit_db("ACL Violation",$config["REMOTE_ADDR"], "No administrator access","Trying to access setup");
    require ("general/noaccess.php");
    exit;
}

$update = get_parameter ("update",0);

if ($update == 1){
	$config["block_size"] = get_parameter ("block_size", 20);
	$config["language_code"] = get_parameter ("language_code", "en");
	$config["notification_period"] = get_parameter ("notification_period", 86400);
	$config["currency"] = get_parameter ("currency", "€");
	$config["hours_perday"] = get_parameter ("hours_perday", "8");
	$config["sitename"] = get_parameter ("sitename", "Integria IMS");
	$config["FOOTER_EMAIL"] = get_parameter ("FOOTER_EMAIL", "");
	$config["HEADER_EMAIL"] = get_parameter ("HEADER_EMAIL", "");

	$result2 = mysql_query("UPDATE tconfig SET VALUE='".$config["block_size"]."' WHERE TOKEN='block_size'");
	$result2 = mysql_query("UPDATE tconfig SET VALUE='".$config["language_code"]."' WHERE TOKEN='language_code'");
    $result2 = mysql_query("UPDATE tconfig SET VALUE='".$config["notification_period"]."' WHERE TOKEN='notification_period'");

	$result2 = mysql_query ("UPDATE tconfig SET VALUE='".$config["hours_perday"]."' WHERE TOKEN='hours_perday'");

	$result2 = mysql_query ("UPDATE tconfig SET VALUE='".$config["currency"]."' WHERE TOKEN='currency'");

	$result2 = mysql_query ("UPDATE tconfig SET VALUE='".$config["FOOTER_EMAIL"]."' WHERE TOKEN='FOOTER_EMAIL'");

	$result2 = mysql_query ("UPDATE tconfig SET VALUE='".$config["HEADER_EMAIL"]."' WHERE TOKEN='HEADER_EMAIL'");

	$result2 = mysql_query ("UPDATE tconfig SET VALUE='".$config["sitename"]."' WHERE TOKEN='sitename'");

}	

echo "<h2>".$lang_label["setup_screen"]."</h2>";
echo "<h3>".$lang_label["general_config"]."</h3>";

echo "<form name='setup' method='POST' action='index.php?sec=godmode&sec2=godmode/setup/setup&update=1'>";

echo '<table width="550" class="databox">';

echo '<tr><td class="datos">'.$lang_label["language_code"];
echo '<td class="datos"><select name="language_code" onChange="javascript:this.form.submit();" width="180px">';

$sql="SELECT * FROM tlanguage";
$result=mysql_query($sql);
$result2=mysql_query("SELECT * FROM tlanguage WHERE id_language = '".$config["language_code"]."'");
if ($row2=mysql_fetch_array($result2)){
	echo '<option value="'.$row2["id_language"].'">'.$row2["name"];
}
while ($row=mysql_fetch_array($result)){
	echo "<option value=".$row["id_language"].">".$row["name"];
}
echo '</select>';
		
echo '<tr><td>'.__("block_size");
echo '<td>';
print_input_text ("block_size", $config["block_size"], '', 5, 0, false, false);

echo '<tr><td>'.__("Notification period");
echo '<td>';
print_input_text ("notification_period", $config["notification_period"], '', 7, 0, false, false);
echo integria_help("notification_period");

// NEW 1.2

echo '<tr><td>'.__("Currency");
echo '<td>';
print_input_text ("currency", $config["currency"], '', 5, 0, false, false);

echo '<tr><td>'.__("Sitename");
echo '<td>';
print_input_text ("sitename", $config["sitename"], '', 15, 0, false, false);


echo '<tr><td>'.__("Hours per day");
echo '<td>';
print_input_text ("hours_perday", $config["hours_perday"], '', 5, 0, false, false);


echo '<tr><td colspan=2>';
print_textarea ("HEADER_EMAIL", 5, 40, $config["HEADER_EMAIL"],'', false, __("Email header"));

echo '<tr><td colspan=2>';
print_textarea ("FOOTER_EMAIL", 5, 40, $config["FOOTER_EMAIL"],'', false, __("Email footer"));


echo "</table>";
 



echo "<div style='width:550px' class=button>";
echo '<input type="submit" class="sub upd" value="'.$lang_label["update"].'">';
echo "</div>";


?>
