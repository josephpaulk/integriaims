<?PHP
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2010 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.


global $config;

echo    '<center>
        <div style="width:550px; padding-top: 100px;">
        <div style="margin:15px; background: #fff;">
		<table width="450px" cellpadding=4 cellspacing=4 class="blank">
        <tr><td>
        <a href="index.php">
        <img src="images/integria_white.png" alt="logo">
        </a>
		<br />'.$config["version"].'</td>
		<td width=50><td>';

$recover = get_parameter ("recover", "");
$hash = get_parameter ("hash", "");

echo '<h3 class="error">';
echo __('Password recovery');
echo    '</h3>';

if (($recover == "") AND ($hash == "")){
    // THis NEVER should happen. Anyway, a nice msg for hackers ;)
    echo "Don't try to hack this form. All information is sent to the user by mail";
}

elseif ($hash == ""){

    $randomhash = md5($config["sitename"].rand(0,100).$recover);
    $email = get_db_sql ("SELECT direccion FROM tusuario WHERE disabled = 0 AND id_usuario = '$recover'");
    $subject ="Password recovery for ".$config["sitename"];
    $text = "Integria has received a request for password reset from IP Address ".$_SERVER['REMOTE_ADDR'].". Enter this validation code for reset your password: $randomhash";

    if ($email != ""){
        integria_sendmail ($email, $subject, $text);
        process_sql ("UPDATE tusuario SET pwdhash = '$randomhash' WHERE id_usuario = '$recover'");
    }

    // Doesnt show a error message (not valid email or not valid user 
    // to don't give any clues on valid users

    echo __("Integria IMS has sent you an email with instructions on how to change your password. ");
    echo __("Enter here the validation code you should have received by mail");
    echo '</tr><tr>';
    echo "<tr><td colspan=2>";
    echo "<form method=post>";
    print_input_text ('hash', '', '', '', 50, false, __('Validation code'));
    echo "<td>";

    print_submit_button (__('Validate'), '', false, 'class="sub next"');
    echo "</form>";
} else {
    $check = get_db_sql ("SELECT id_usuario FROM tusuario WHERE id_usuario = '$recover' AND pwdhash = '$hash'");
    if ($check == $recover){
        $newpass =  substr(md5($config["sitename"].rand(0,100).$recover),0,6);
        echo __("Your new password is");
        echo " : <b>";
        echo $newpass."</b><br><br>";
        echo "<a href='index.php'>";
        echo __("Click here to login");
        echo "</A>";
        process_sql ("UPDATE tusuario SET password = md5('$newpass') WHERE id_usuario = '$recover'");
    } else {
        echo __("Invalid validation code");
    }
}

echo '</td>
		</tr>	    
		</table>
		<div style="height:15px"> </div>
		</form>
	</div>
</div>
</center>';
?>

