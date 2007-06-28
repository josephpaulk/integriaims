<?php

// Pandora FMS - the Free monitoring system
// ========================================
// Copyright (c) 2004-2007 Sancho Lerena, slerena@openideas.info
// Copyright (c) 2005-2007 Artica Soluciones Tecnologicas
// Copyright (c) 2004-2007 Raul Mateos Martin, raulofpandora@gmail.com
// Copyright (c) 2006-2007 Jose Navarro jose@jnavarro.net
// Copyright (c) 2006-2007 Jonathan Barajas, jonathan.barajas[AT]gmail[DOT]com

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
// Load global vars
require("include/config.php");

if (check_login() != 0) {
 	audit_db("Noauth",$config["REMOTE_ADDR"], "No authenticated access","Trying to access event viewer");
	require ("general/noaccess.php");
	exit;
}

$id_user=$_SESSION['id_usuario'];
echo "<h2>".$lang_label["incident_search"]."</h2>";
echo "<div style='width:645'>";
echo "<div style='float:right;'><img src='images/zoom.png' width=32 height=32 class='bot' align='left'></div>";
?>

<table width="500" class='databox_color'>
<form name="busqueda" method="post" action="index.php?sec=incidents&sec2=operation/incidents/incident">
<tr>
<td class="datos2"><?php echo $lang_label["user"] ?>
<td class="datos2">
<select name="usuario" class="w120">
<option>--
<?php
	$sql_1="SELECT * FROM tusuario_perfil WHERE id_usuario = '$id_user'";
	$result_1=mysql_query($sql_1);
	
	while ($row_1=mysql_fetch_array($result_1)){
		$sql_2="SELECT * FROM tusuario_perfil WHERE id_grupo = ".$row_1["id_grupo"];
		$result_2=mysql_query($sql_2);
		while ($row_2=mysql_fetch_array($result_2)){
			if (give_acl($row_2["id_usuario"], $row_2["id_grupo"], "IR")==1)
				echo "<option>".$row_2["id_usuario"];
		}
	}
	echo "</select>";

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
