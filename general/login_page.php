<?php

// INTEGRIA - OpenSource Management for the Enterprise
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007 Sancho Lerena, slerena@gmail.com

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

?>

<center>
<div class='databox' id='login'>
	<h1 id='log'><?php echo $lang_label['welcome_title']; ?></h1>
	<div class='databox' id='login_in'>
		<form method="post" action="index.php?login=1">

<?PHP
        echo "<input type='hidden' name='prelogin_url' value='".$_SERVER['REQUEST_URI']."'>";
?>
		<table cellpadding='4' cellspacing='1' width='400'>
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
