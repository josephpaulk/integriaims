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


global $config;
global $lang_label;

?>
<div align='center'>
	<div id='login' style='padding: 15px'>	
		<div id="login_box" style='width:75%;padding-left: 40px;'>
			<center><?PHP echo $lang_label["logout_msg"]; ?></center>
		</div>
		<div id="logo_box">
			<center>
			<a href="index.php"><img src="images/integria_white.png" border="0" alt="logo"></a>
			</center>
		</div>
<?php
	//	echo '<div id="foot"><br><br><br>'.require("general/footer.php").'</DIV>';
?>
	</div>
</div>
