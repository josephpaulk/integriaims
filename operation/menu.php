<?php

// FRITS - the FRee Incident Tracking System
// =========================================
// Copyright (c) 2007 Sancho Lerena, slerena@openideas.info
// Copyright (c) 2007 Artica Soluciones Tecnologicas

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
?>

<?php
if (! isset($_SESSION["id_usuario"])) {
	return;
} 

?>
<div class="bg">
	<div class="imgl"><img src="images/upper-left-corner.gif" width="5" height="5" alt=""></div>
	<div class="tit">:: <?php echo $lang_label["operation_header"] ?> ::</div>
	<div class="imgr"><img src="images/upper-right-corner.gif" width="5" height="5" alt=""></div>
</div>
<div id="menuop">
<div id="op">
<?php


// Check access for incident
if (give_acl($_SESSION["id_usuario"], 0, "IR")==1) {
	if(isset($_GET["sec2"]) && ($_GET["sec2"] == "operation/incidents/incident" || $_GET["sec2"] == "operation/incidents/incident_detail"|| $_GET["sec2"] == "operation/incidents/incident_note")) {
		echo '<div id="op3s">';
	} else {
		echo '<div id="op3">';
	}
	echo '<ul class="mn"><li><a href="index.php?sec=incidencias&amp;sec2=operation/incidents/incident" class="mn">'.$lang_label["manage_incidents"].'</a></li></ul></div>';

	if (isset($_GET["sec"]) && $_GET["sec"] == "incidencias"){
		if(isset($_GET["sec2"]) && $_GET["sec2"] == "operation/incidents/incident_search") {
			echo "<div class='arrows'>";
		} else {
			echo "<div class='arrow'>";
		}
		echo "<ul class='mn'><li><a href='index.php?sec=incidencias&amp;sec2=operation/incidents/incident_search' class='mn'>".$lang_label["search_incident"]."</a></li></ul></div>";
/*
		if (isset($_GET["sec2"]) && $_GET["sec2"] == "operation/incidents/incident_statistics") {
			echo "<div class='arrows'>";
		} else {
			echo "<div class='arrow'>";
		}
		echo "<ul class='mn'><li><a href='index.php?sec=incidencias&amp;sec2=operation/incidents/incident_statistics' class='mn'>".$lang_label["statistics"]."</a></li></ul></div>";
*/
	}
}


// Rest of options, all with AR privilege
if (give_acl($_SESSION["id_usuario"], 0, "IR")==1) {

	// Users
	if(isset($_GET["sec2"]) && ($_GET["sec2"] == "operation/users/user" || $_GET["sec2"] == "operation/users/user_edit" )) {
		echo '<div id="op5s">';
	} else {
		echo '<div id="op5">';
	}
	echo '<ul class="mn"><li><a href="index.php?sec=usuarios&amp;sec2=operation/users/user" class="mn">'.$lang_label["view_users"].'</a></li></ul></div>';

	// User edit (submenu)
	if (isset($_GET["sec"]) && $_GET["sec"] == "usuarios"){
		if(isset($_GET["ver"]) && $_GET["ver"] == $_SESSION["id_usuario"]) {
			echo "<div class='arrows'>";
		} else {
			echo "<div class='arrow'>";
		}
		echo "<ul class='mn'><li><a href='index.php?sec=usuarios&amp;sec2=operation/users/user_edit&amp;ver=".$_SESSION["id_usuario"]."' class='mn'>".$lang_label["index_myuser"]."</a></li></ul></div>";

		// User statistic
		/*
		if(isset($_GET["sec2"]) && $_GET["sec2"] == "operation/users/user_statistics") {
			echo "<div class='arrows'>";
		} else {
			echo "<div class='arrow'>";
		}
		echo "<ul class='mn'><li><a href='index.php?sec=usuarios&amp;sec2=operation/users/user_statistics' class='mn'>".$lang_label["statistics"]."</a></li></ul></div>";
		*/
	}

	// Messages
	if(isset($_GET["sec2"]) && $_GET["sec2"] == "operation/messages/message" && !isset($_GET["nuevo_g"])) {
		echo '<div id="op7s">';
	} else {
		echo '<div id="op7">';
	}
	echo '<ul class="mn"><li><a href="index.php?sec=messages&amp;sec2=operation/messages/message" class="mn">'. $lang_label["messages"].'</a></li></ul></div>';

	// New message (submenu)
	if (isset($_GET["sec"]) && $_GET["sec"] == "messages"){
		if(isset($_GET["sec2"]) && isset($_GET["nuevo_g"])) {
			echo "<div class='arrows'>";
		} else {
			echo "<div class='arrow'>";
		}
		echo "<ul class='mn'><li><a href='index.php?sec=messages&amp;sec2=operation/messages/message&amp;nuevo_g' class='mn'>".$lang_label["messages_g"]."</a></li></ul></div>";
	}
}

?>		
</div>
</div>	
