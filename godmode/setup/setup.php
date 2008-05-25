<?php 

// Integria 1.0 - http://integria.sourceforge.net
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

if (isset($_GET["update"])){
	$block_size=$_POST["block_size"];
	$language_code=$_POST["language_code"];
	
	$result2=mysql_query("UPDATE tconfig SET VALUE='".$config["block_size"]."' WHERE TOKEN='block_size'");
	$result2=mysql_query("UPDATE tconfig SET VALUE='".$config["language_code"]."' WHERE TOKEN='language_code'");
}	

echo "<h2>".$lang_label["setup_screen"]."</h2>";
echo "<h3>".$lang_label["general_config"]."</h3>";
echo "<form name='setup' method='POST' action='index.php?sec=godmode&sec2=godmode/setup/setup&update=1'>";
echo '<table width="500" cellpadding="4" cellspacing="4" class=databox_color>';
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
		
echo '<tr><td class="datos2">'.$lang_label["block_size"];
echo '<td class="datos2"><input type="text" name="block_size" size=5 value="'.$config["block_size"].'">';
// 
echo "<tr><td colspan='3' align='right'>";
echo '<input type="submit" class="sub upd" value="'.$lang_label["update"].'">';
echo "</table>";

?>