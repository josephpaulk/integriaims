<?php

// Pandora FMS - the Free monitoring system
// ========================================
// Copyright (c) 2004-2007 Sancho Lerena, slerena@gmail.com
// Main PHP/SQL code development and project architecture and management
// Copyright (c) 2004-2007 Raul Mateos Martin, raulofpandora@gmail.com
// CSS and some PHP additions
// Copyright (c) 2006-2007 Jonathan Barajas, jonathan.barajas[AT]gmail[DOT]com
// Javascript Active Console code.
// Copyright (c) 2006 Jose Navarro <contacto@indiseg.net>
// Additions to Pandora FMS 1.2 graph code and new XML reporting template management
// Copyright (c) 2005-2007 Artica Soluciones Tecnologicas, info@artica.es
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
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
	<div class="tit">:: <?php echo $lang_label["godmode_header"] ?> ::</div>
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
		echo '<ul class="mn"><li><a href="index.php?sec=gusuarios&amp;sec2=godmode/usuarios/lista_usuarios" class="mn">'.$lang_label["manage_users"].'</a></li></ul></div>';
	}
	if ( (give_acl($id_user, 0, "PM")==1)){
		if (isset($_GET["sec2"]) && $_GET["sec2"] == "godmode/perfiles/lista_perfiles"){
			echo '<div id="god4s">';
		}
		else echo '<div id="god4">';
		echo '<ul class="mn"><li><a href="index.php?sec=gperfiles&amp;sec2=godmode/perfiles/lista_perfiles" class="mn">'.$lang_label["manage_profiles"].'</a></li></ul></div>';
		

		if (isset($_GET["sec2"]) && ($_GET["sec2"] == "godmode/grupos/lista_grupos" || $_GET["sec2"] == "godmode/grupos/configurar_grupo")){
			echo "<div class='arrowgs'>";
		}
		else
			echo "<div class='arrowg'>";
		echo "<ul class='mn'><li><a href='index.php?sec=gagente&amp;sec2=godmode/grupos/lista_grupos' class='mn'>".$lang_label["manage_groups"]."</a></li></ul></div>";


		if (isset($_GET["sec2"]) && $_GET["sec2"] == "godmode/admin_access_logs"){
			echo '<div id="god6s">';
		}
		else echo '<div id="god6">';
		echo '<ul class="mn"><li><a href="index.php?sec=glog&amp;sec2=godmode/admin_access_logs" class="mn">'.$lang_label["system_audit"].'</a></li></ul></div>';
		
		if (isset($_GET["sec2"]) && $_GET["sec2"] == "godmode/setup/setup"){
			echo '<div id="god7s">';
		}
		else echo '<div id="god7">';
		echo '<ul class="mn"><li><a href="index.php?sec=gsetup&amp;sec2=godmode/setup/setup" class="mn">'.$lang_label["setup_screen"].'</a></li></ul></div>';
		
		if (isset($_GET["sec"]) && $_GET["sec"] == "gsetup"){
			if (isset($_GET["sec2"]) && $_GET["sec2"] == "godmode/setup/links"){
				echo "<div class='arrowgs'>";
			}
			else echo "<div class='arrowg'>";
			echo "<ul class='mn'><li><a href='index.php?sec=gsetup&amp;sec2=godmode/setup/links' class='mn'>".$lang_label["setup_links"]."</a></li></ul></div>";
		}
	}
	if ((give_acl($id_user, 0, "DM")==1)){
		if (isset($_GET["sec2"]) && $_GET["sec2"] == "godmode/db/db_main"){
			echo '<div id="god8s">';
		}
		else echo '<div id="god8">';
		echo '<ul class="mn"><li><a href="index.php?sec=gdbman&amp;sec2=godmode/db/db_main" class="mn">'.$lang_label["db_maintenance"].'</a></li></ul></div>';
		
		if (isset($_GET["sec"]) && $_GET["sec"] == "gdbman"){
			if (isset($_GET["sec2"]) && ($_GET["sec2"] == "godmode/db/db_info" || $_GET["sec2"] == "godmode/db/db_info_data")){
				echo "<div class='arrowgs'>";
			}
			else echo "<div class='arrowg'>";
			echo "<ul class='mn'><li><a href='index.php?sec=gdbman&amp;sec2=godmode/db/db_info' class='mn'>".$lang_label["db_info"]."</a></li></ul></div>";
			
			if (isset($_GET["sec2"]) && $_GET["sec2"] == "godmode/db/db_purge"){
				echo "<div class='arrowg'>";
			}
			else echo "<div class='arrowg'>";			
			echo "<ul class='mn'><li><a href='index.php?sec=gdbman&amp;sec2=godmode/db/db_purge' class='mn'>".$lang_label["db_purge"]."</a></li></ul></div>";
			
			
			if (isset($_GET["sec2"]) && $_GET["sec2"] == "godmode/db/db_audit"){
				echo "<div class='arrowgs'>";
			}
			else echo "<div class='arrowg'>";			
			echo "<ul class='mn'><li><a href='index.php?sec=gdbman&amp;sec2=godmode/db/db_audit' class='mn'>".$lang_label["db_audit"]."</a></li></ul></div>";
			

		}
	}
	?>
	</div>
</div>
<?php
	} // end verify access to this menu
}
?>