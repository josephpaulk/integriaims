<?PHP
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


check_login();

// Put here your logo
echo "<img src='images/integria_logo.png' border=0>";

// Adjust width with &nbsp; to fit your logo
echo "<div width=100%>";
echo "<span>";
echo "<a href='index.php'><img src='images/house.png' border=0>";
echo " ".__('Home')."</a>";



echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
$id_usuario = clean_input ($_SESSION["id_usuario"]);
echo "<a href='index.php?sec=users&sec2=operation/users/user_edit&amp;ver=".$_SESSION["id_usuario"]."'>";
if (dame_admin($id_usuario)==1)
	echo "<img src='images/user_suit.png'> ";
else
	echo "<img src='images/user_green.png'> ";
echo __('You are connected as').' [ <b><font color="#ffffff">'. $id_usuario. '</b></font> ]</a>';
echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
echo "<a href='index.php?bye=bye'><img src='images/lock.png'> ". __('Logout')."</a>";
echo "</span>";
echo "</div>";
?>
