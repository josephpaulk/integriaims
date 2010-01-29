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
<center>
<div class="databox" id="login">
	<div>
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
		<table width="400px" class="blank">
<?php if (isset ($login_failed)): ?>
		<tr>
		<h3 style="width: 230px;" class="error"><?php echo __('Login failed')?></h3>
		</tr>
<?php endif; ?>
		<tr>
		<td rowspan="3" style="text-align: left ;padding-right: 25px;">
			<a href="index.php">
			<img src="images/integria_white.png" alt="logo">
			</a><br />
			<?php echo $config["version"]; ?>
		</td>
		<td rowspan="3" width="5" />
		<td>
			<?php print_input_text ('nick', '', '', '', 50, false, __('User')); ?>
		</td>
		</tr>
		<tr>
		<td>
			<?php print_input_password ('pass', '', '', '', 50, false, __('Password')); ?>
		</td>
		</tr>
		<tr>
		<td align="right">
			<br>
			<?php print_submit_button (__('Login'), '', false, 'class="sub login"'); ?>
		</td>
		</tr>	    
		</table>
		<div style='height:15px'> </div>
		</form>
	</div>
</div>
</center>

<script type="text/javascript">
$(document).ready (function () {
	$("#text-nick").focus ();
});
</script>
