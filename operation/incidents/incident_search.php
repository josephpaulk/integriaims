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
 	audit_db("Noauth",$config["REMOTE_ADDR"], "No authenticated access","Trying to access event viewer");
	require ("general/noaccess.php");
	exit;
}

$id_user=$config['id_user'];

echo "<h2>".$lang_label["incident_search"]."</h2>";
echo "<div style='width:645'>";
echo "<div style='float:right;'><img src='images/zoom.png' width=32 height=32 class='bot' align='left'></div>";
?>

<table width="500" class='databox_color'>
<form name="busqueda" method="post" action="index.php?sec=incidents&sec2=operation/incidents/incident">
<tr>
<td class="datos2"><?php echo $lang_label["user"] ?>
<td class="datos2">
<?PHP

echo combo_user_visible_for_me ($id_user, 'usuario', 1, "");

?>
<tr><td class="datos"><?php echo $lang_label["incident_id"] ?>
<td class="datos"><input type="text" size="10" name="incident_id"></tr>

<tr><td class="datos2"><?php echo $lang_label["free_text_search"] ?>
<td class="datos2"><input type="text" size="45" name="texto"></tr>

<tr><td class="datos" colspan="2"><i><?php echo $lang_label["free_text_search_msg"] ?></i></td></tr>
</table>

<?php

echo '<table width="500"><tr><td align=right>';
echo "<input name='uptbutton' type='submit' class='sub next' value='".$lang_label["search"]."'></p>";
echo "</form></table>
</div>
</div>";

?>
