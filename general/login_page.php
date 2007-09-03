<?php

// TOPI
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
<!--
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
			<img style='margin-top: 30px;' src="images/topi_logo_small.jpg" border="0" alt="logo"></a><br>
			<?php echo $config["version"]; ?>
		</div>
	</div>
	
	<div id="foot" align='center'>
		<center>
		<?php require("general/footer.php") ?>
		</center>
	</div>

</div>
-->
<div class='databox' id='login'>
	<h1 id='log'><?php echo $lang_label['welcome_title']; ?></h1>
	<div class='databox' id='login_in'>
		<form method="post" action="index.php?login=1">
		<table cellpadding='4' cellspacing='1' width='400'>
		<tr><td rowspan='3' align='left' style="border-right: solid 1px #678;">
			<a href="index.php">
			<img src="images/topi_logo_small.jpg" border="0" alt="logo"></a>
			<?php echo $config["version"]; ?>
		<td rowspan='3' width='5'>
		<td class='f9b'>
			Login <br>
			<input class="login" type="text" name="nick" id="nick" value="">
		</td></tr>
		<tr><td class='f9b'>
			Password <br>
			<input class="login" type="password" name="pass" value="">
		</td></tr>
		<tr><td align='center'>
			<input type="submit" class="sub next" value="Login">
		</td></tr>
		</table>
		</form>
	</div>

</div>

<script type="text/javascript">
	document.getElementById('nick').focus();
</script>