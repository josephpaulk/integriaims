<?php
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2012 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// Failed login ??

if (isset ($login_failed)) {
	
	echo '<div class="databox_login_msg" >';
	echo "<div style='vertical-align: top; position: relative; left: 0px; top: 0px; padding: 0px; margin: 0px; width: 320px;'>";
	echo '<h3 class="error">';
	echo __('Login failed');
	echo '</h3>';

	echo __("If you have lost or does not remember your password");
	echo " <a href='index.php?recover=$nick'>";
	echo "<b>click here</b>";
	echo "</a> ";
	echo "for sending you instructions to your mailbox on how to change your password.";
	$nick = get_parameter ($nick);

	echo '</div></div>';
}



echo '<div class="databox_login" id="login">';
?>

<?php 
	$action = "index.php";
	$params = '';
	foreach ($_GET as $name => $value) {
		$params .= $name.'='.$value.'&';
	}
	if ($params != '')
		$action .= '?'.$params;

	echo '<form method="post" action="'.$action.'">';

	print_input_hidden ('login', 1);
	foreach ($_POST as $name => $value)
		print_input_hidden ($name, $value);

	echo "<div id='login_form_data'>";
	echo "<table border=0>";
	echo "<tr>";
	echo "<td style='padding-left: 15px; _padding-left: 0px; width: 250px'>";
	echo "<a href='index.php'>";
	if (isset($config["site_logo"]))
		echo '<img src="images/'.$config['site_logo'].'" alt="logo">';
	else
		echo '<img src="images/loginlogo.png" alt="logo">';
	echo '</a>';
	echo '</td>';
	echo "<td valign=top style='padding-top: 18px; width: 80px; line-height: 10px;'>";
	echo print_input_text_extended ("nick", '', "nick", '', '', '', false, '', 'class="login"', true);
	echo "<br><br>";
	echo print_input_text_extended ("pass", '', "pass", '', '', '', false, '', 'class="login"', true, true);

	echo "<tr><td colspan=2 style='padding-left: 270px;'>";
	
	echo print_input_image ("Login", "images/loginbutton.png", 'Login');

	echo '</td></tr>';
	echo "</table>";	
	echo "</div>";
	
?>
		</form>
</div>


<?php
echo '<div id="bottom_logo">';
echo "<img src='images/loginbacklogo.png'>";
echo "</div>";

echo '<div id="ver_num">';
echo $config["version"];
echo "</div>";


?>


<script type="text/javascript" language="javascript">
document.getElementById('nick').focus();
</script>
