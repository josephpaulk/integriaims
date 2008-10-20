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

?>
<div style='height: 60px'>
</div>
<center>
<div class='databox' id='login' style='height: 200px; padding-top: 20px;'>
	<div style='padding-top: 5px;' id='login_in' style='height: 100px;'>
<?php 
	$action = "index.php";
	$params = '';
	foreach ($_GET as $name => $value) {
		$params .= $name.'='.$value.'&';
	}
	if ($params != '')
		$action .= '?'.$params;
?>
		<form method="post" action="<?php echo $action ?>">
<?php
	print_input_hidden ('login', 1);
	foreach ($_POST as $name => $value)
		print_input_hidden ($name, $value);
?>
		<table cellpadding='4' cellspacing='1' width='400' class='blank'>
		<tr><td rowspan='3' align='left' style="border-right: solid 1px #678;">
			<a href="index.php">
			<img src="images/integria_white.png" border="0" alt="logo"></a>
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
</center>

<script type="text/javascript">
	document.getElementById('nick').focus();
</script>
