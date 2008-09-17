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

?>
<center>
<br>
<div id='login' style='padding: 10px'>
	<h1 id='log'><?php echo $lang_label['err_auth']; ?></h1>
	<div style='padding: 10px' id='login_in'>
		<table cellpadding='4' cellspacing='4' width='400' class='blank'>
		<tr><td rowspan='3' align='left' style="border-right: solid 1px #678;">
			<a href="index.php">
			<img src="images/integria_white.png" border="0" alt="logo"></a>
			<?php echo $config["version"]; ?>
		<td rowspan='3' width='5'>
		<td class='f9b'>
		<?PHP
			echo "<center><img src='images/noaccess.gif'></center>";	
			echo '<div><br>'.$lang_label["err_auth_msg"]."<br><br></div>";
		?>
		</td></tr>
		</table>
		</form>
	</div>
</div>
</center>