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


global $config;

?>

<div class='databox' id='login'>
	<h1 id='log'><?php echo $lang_label['err_auth']; ?></h1>
	<div class='databox' id='login_in'>
		<table cellpadding='4' cellspacing='1' width='400'>
		<tr><td rowspan='3' align='left' style="border-right: solid 1px #678;">
			<a href="index.php">
			<img src="images/topi_logo_small.jpg" border="0" alt="logo"></a>
			<?php echo $config["version"]; ?>
		<td rowspan='3' width='5'>
		<td class='f9b'>
		<?PHP
			echo "<center><img src='images/noaccess.gif'></center>";	
			echo '<div class="databox"><br>'.$lang_label["err_auth_msg"]."<br><br></div>";
		?>
		</td></tr>
		</table>
		</form>
	</div>

</div>
