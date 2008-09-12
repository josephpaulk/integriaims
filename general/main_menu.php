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


global $config;

?>
<br>
<a href="index.php"><img src="images/integria_logo.png" border="0" alt="logo"></a>
<div id='ver'><?php echo $config["version"]; ?></div>

<?php 
require ("operation/menu.php");
if (! isset ($_SESSION["id_usuario"])) {
	echo '<div class="f10">' . $lang_label["not_connected"];
	echo '<br /><br />';
	echo '<form method="post" action="index.php?login=1">';
	echo '<div class="f9b">Login</div><input class="login" type="text" name="nick">';
	echo '<div class="f9b">Password</div><input class="login" type="password" name="pass">';
	echo '<div><input name="login" type="submit" class="sub" value="' . $lang_label["login"] .'"></div>';
	echo '<br />IP: <b class="f10">' . $REMOTE_ADDR . '</b><br /></div>';
	
} else {
	$iduser = $_SESSION['id_usuario'];
	require ("godmode/menu.php");
	require ("links_menu.php");
}
?>
