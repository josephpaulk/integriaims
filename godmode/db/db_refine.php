<?php

// Integria 2.0 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

// Load global vars
require("include/config.php");
if (comprueba_login() == 0) 
	$id_user = $_SESSION["id_usuario"];
 	if ((give_acl($id_user, 0, "DM")==1) or (dame_admin($id_user)==1)) {
		if ((isset($_GET["operacion"])) AND (! isset($_POST["update_agent"]))){
			// DATA COPY
			if (isset($_POST["eliminar"])) {
				echo "<h2>".__('Delete Data')."</h2>";
				// First checkings
				
				$max = $_POST["max"];
				$min = $_POST["min"];
				if ($max == $min){
					echo "<h3 class='error'>ERROR ".__('Maximum equal to minimum')."</h3>";
					echo "</table>";
					include ("general/footer.php");
					exit;
				}
				$origen_modulo = $_POST["origen_modulo"];
			 	if (count($origen_modulo) <= 0){
					echo "<h3 class='error'>ERROR: ".__('No modules has been selected')."</h3>";
					echo "</table>";
					include ("general/footer.php");
					exit; 
				}
	
				// Origen (agent)
				$id_origen = $_POST["origen"];
				
				// Copy
				for ($a=0;$a <count($origen_modulo); $a++){ // For every agent selected
					$id_agentemodulo = $origen_modulo[$a];
					echo "<br><br>".__('Filtering data module')."<b> [".dame_nombre_modulo_agentemodulo($id_agentemodulo)."]</b>";
					$sql1='DELETE FROM tagente_datos WHERE id_agente_modulo = '.$origen_modulo[$a].' AND ( datos < '.$min.' OR  datos > '.$max.' )';
					$result1=mysql_query($sql1);
					//echo "<br>DEBUG DELETE $sql1 <br>";
				} 
			} //if copia modulos o alertas
		} else { // Form view
			?>
			<h2><?php echo __('Database Maintenance'); ?></h2>
			<h3><?php echo __('Database Debug'); ?><a href='help/<?php echo $help_code?>/chap8.php#831' target='_help' class='help'>&nbsp;<span><?php echo __('Help') ?></span></a></h3> 
			<form method="post" action="index.php?sec=gdbman&sec2=godmode/db/db_refine&operacion=1">
			<table width='500' border='0' cellspacing='3' cellpadding='5'>
			<tr>
			<td class="datost"><b><?php echo __('Source agent'); ?></b><br><br>
			<select name="origen" class="w130">
			<?php
			if ( (isset($_POST["update_agent"])) AND (isset($_POST["origen"])) ) {
				echo "<option value=".$_POST["origen"].">".dame_nombre_agente($_POST["origen"]);
			}
			// Show combo with agents
			$sql1='SELECT * FROM tagente';
			$result=mysql_query($sql1);
			while ($row=mysql_fetch_array($result)){
				if ( (isset($_POST["update_agent"])) AND (isset($_POST["origen"])) ){
					if ( $_POST["origen"] != $row["id_agente"])
						echo "<option value=".$row["id_agente"].">".$row["nombre"];
				}
				else
					echo "<option value=".$row["id_agente"].">".$row["nombre"];
			}
			echo '</select>&nbsp;&nbsp;<input type=submit name="update_agent" class=sub value="'.__('Get Info').'"><br><br>';
			echo "<b>".__('Modules')."</b><br><br>";
			echo "<select name='origen_modulo[]' size=5 multiple=yes class='w130'>";
			if ( (isset($_POST["update_agent"])) AND (isset($_POST["origen"])) ) {
				// Populate Module/Agent combo
				$agente_modulo = $_POST["origen"];
				$sql1="SELECT * FROM tagente_modulo WHERE id_agente = ".$agente_modulo;
				$result = mysql_query($sql1);
				while ($row=mysql_fetch_array($result)){
			 		echo "<option value=".$row["id_agente_modulo"].">".$row["nombre"];	
				}
			}
			echo "</select>";
			?>
			<td class="datost"><b><?php echo __('Purge data out these limits'); ?></b><br><br>
				<table cellspacing=3 cellpadding=3 border=0>
				<tr class=datos><td><?php echo __('Minimum'); ?><td><input type="text" name="min" size=4 value=0>
				<tr class=datos><td><?php echo __('Maximum'); ?><td><input type="text" name="max" size=4 value=0>	
				<tr><td></td></tr>
				<tr><td class="bot" colspan="2" align="right">
				<input type=submit name="eliminar" class=sub value="<?php echo __('Delete').'" onClick="if (!confirm("'.__('Are you sure?').'")) return false;>'; ?>
				</table>
			</td></tr>

			</table>
			
			<?php
		}
	} else {
		audit_db($id_user,$REMOTE_ADDR, "ACL Violation","Trying to access Database Debug Admin section");
		require ("general/noaccess.php");
	}
?>
