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
if (comprueba_login() == 0){
	$id_user = $_SESSION["id_usuario"];
	if ( (give_acl($id_user, 0, "LM")==1) OR (give_acl($id_user, 0, "AW")==1 ) OR (give_acl($id_user, 0, "PM")==1) OR (give_acl($id_user, 0, "DM")==1) OR (give_acl($id_user, 0, "UM")==1 )){

?>

<div class="bg3">
	<div class="imgl"><img src="images/upper-left-corner.gif" width="5" height="5" alt=""></div>
	<div class="tit">:: <?php echo __('Administration') ?> ::</div>
	<div class="imgr"><img src="images/upper-right-corner.gif" width="5" height="5" alt=""></div>
</div>
<div id="menug">
	<div id="god">
	
<?PHP



	if ((give_acl($id_user, 0, "UM")==1)){
		if (isset($_GET["sec2"]) && ($_GET["sec2"] == "godmode/usuarios/lista_usuarios" || $_GET["sec2"] == "godmode/usuarios/configurar_usuarios")){
			echo '<div id="god3s">';
		}
		else echo '<div id="god3">';
		echo '<ul class="mn"><li><a href="index.php?sec=gusuarios&amp;sec2=godmode/usuarios/lista_usuarios" class="mn">'.__('Manage Users').'</a></li></ul></div>';
	}
	if ( (give_acl($id_user, 0, "PM")==1)){
		if (isset($_GET["sec2"]) && $_GET["sec2"] == "godmode/perfiles/lista_perfiles"){
			echo '<div id="god4s">';
		}
		else echo '<div id="god4">';
		echo '<ul class="mn"><li><a href="index.php?sec=gperfiles&amp;sec2=godmode/perfiles/lista_perfiles" class="mn">'.__('Manage Profiles').'</a></li></ul></div>';
		

		if (isset($_GET["sec2"]) && ($_GET["sec2"] == "godmode/grupos/lista_grupos" || $_GET["sec2"] == "godmode/grupos/configurar_grupo")){
			echo "<div class='arrowgs'>";
		}
		else
			echo "<div class='arrowg'>";
		echo "<ul class='mn'><li><a href='index.php?sec=gagente&amp;sec2=godmode/grupos/lista_grupos' class='mn'>".__('Manage Groups')."</a></li></ul></div>";


		if (isset($_GET["sec2"]) && $_GET["sec2"] == "godmode/admin_access_logs"){
			echo '<div id="god6s">';
		}
		else echo '<div id="god6">';
		echo '<ul class="mn"><li><a href="index.php?sec=glog&amp;sec2=godmode/admin_access_logs" class="mn">'.__('System Audit Log').'</a></li></ul></div>';
		
		if (isset($_GET["sec2"]) && $_GET["sec2"] == "godmode/setup/setup"){
			echo '<div id="god7s">';
		}
		else echo '<div id="god7">';
		echo '<ul class="mn"><li><a href="index.php?sec=gsetup&amp;sec2=godmode/setup/setup" class="mn">'.__('Integria Setup').'</a></li></ul></div>';
		
		if (isset($_GET["sec"]) && $_GET["sec"] == "gsetup"){
			if (isset($_GET["sec2"]) && $_GET["sec2"] == "godmode/setup/links"){
				echo "<div class='arrowgs'>";
			}
			else echo "<div class='arrowg'>";
			echo "<ul class='mn'><li><a href='index.php?sec=gsetup&amp;sec2=godmode/setup/links' class='mn'>".__('Links')."</a></li></ul></div>";
		}
	}
	if ((give_acl($id_user, 0, "DM")==1)){
		if (isset($_GET["sec2"]) && $_GET["sec2"] == "godmode/db/db_main"){
			echo '<div id="god8s">';
		}
		else echo '<div id="god8">';
		echo '<ul class="mn"><li><a href="index.php?sec=gdbman&amp;sec2=godmode/db/db_main" class="mn">'.__('DB Maintenance').'</a></li></ul></div>';
		
		if (isset($_GET["sec"]) && $_GET["sec"] == "gdbman"){
			if (isset($_GET["sec2"]) && ($_GET["sec2"] == "godmode/db/db_info" || $_GET["sec2"] == "godmode/db/db_info_data")){
				echo "<div class='arrowgs'>";
			}
			else echo "<div class='arrowg'>";
			echo "<ul class='mn'><li><a href='index.php?sec=gdbman&amp;sec2=godmode/db/db_info' class='mn'>".__('DB Information')."</a></li></ul></div>";
			
			if (isset($_GET["sec2"]) && $_GET["sec2"] == "godmode/db/db_purge"){
				echo "<div class='arrowg'>";
			}
			else echo "<div class='arrowg'>";			
			echo "<ul class='mn'><li><a href='index.php?sec=gdbman&amp;sec2=godmode/db/db_purge' class='mn'>".__('Database Purge')."</a></li></ul></div>";
			
			
			if (isset($_GET["sec2"]) && $_GET["sec2"] == "godmode/db/db_audit"){
				echo "<div class='arrowgs'>";
			}
			else echo "<div class='arrowg'>";			
			echo "<ul class='mn'><li><a href='index.php?sec=gdbman&amp;sec2=godmode/db/db_audit' class='mn'>".__('Database Audit')."</a></li></ul></div>";
			

		}
	}
	?>
	</div>
</div>
<?php
	} // end verify access to this menu
}
?>
