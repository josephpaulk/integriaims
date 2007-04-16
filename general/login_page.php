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

<div align='center' style='margin-top: 150px;' >
	<h1><?php echo $lang_label['welcome_title']; ?></h1>
	<div style='width: 350px;'>
		<div style="float: right;padding-right: 10px;margin-top: 8px;width: 140px;">
			<form method="post" action="index.php?login=1">
				<div>Login</div>
				<input class="login" type="text" name="nick" value="">
				<div>Password</div>
				<input class="login" type="password" name="pass" value="">
				<br>
				<input type="submit" class="sub next" value="Login now">
				
			</form>
		</div>
		<div style="margin-top: 25px;padding-left: 5px; height: 170px;" class='databox_color'>
			<a href="index.php">
			<img style='margin-top: 30px;' src="images/frits_logo.gif" border="0" alt="logo"></a><br>
			<?php echo $frits_version; ?>
		</div>
	</div>
	
	<div id="foot" align='center'>
		<center>
		<?php require("general/footer.php") ?>
		</center>
	</div>

</div>