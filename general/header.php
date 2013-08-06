<?PHP
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

check_login();

// We need to strip HTML entities if we want to use in a sql search
$search_string = safe_output (get_parameter ("search_string",""));

// I prefer to make the layout with tables here, it's more exact and 
// doesnt depend of CSS interpretation. Please DO NOT TOUCH.

echo "<table class='table_header' border=0 cellpadding=0 cellspacing=0>";
echo "<tr>";
echo "<td id='logo_container'>";

// Custom logo per group
if ($config["enteprise"] == 1){
	$banner = "";
    $mygroup = get_first_group_of_user ($config["id_user"]);
    if ($mygroup != "")
        $banner = get_db_sql ("SELECT banner FROM tgrupo WHERE id_grupo = ".$mygroup);
	if ($banner != "")
		echo '<a href="index.php"><img src="images/group_banners/'.$banner.'" title="'.__('Home').'"/></a>';	
	else
		echo '<a href="index.php"><img src="images/'.$config["header_logo"].'" title="'.__('Home').'"/></a>';
} else { 
	echo '<a href="index.php"><img src="images/'.$config["header_logo"].'" title="'.__('Home').'"/></a>';
}
echo '</td><td class="header_menu">';
echo '<div id="menu">';
require ("operation/main_menu.php");
echo '</div>';
echo '</td>';

echo '<td class="header_search">';
echo "<form method=post action='index.php?sec2=operation/search'>";
echo "<input id='global_search' type=text name='search_string' size=20 value='$search_string'>";
echo '</form>';
echo '</td><td class="header_icons">';

//// This div is necessary for javascript actions. Dont touch ///
echo '<div style="font-size: 0px; display: inline;" id="id_user">'.$config['id_user']."</div>";
/////////////////////////////////////////////////////////////////

$got_alerts = 0;
$check_cron_exec = check_last_cron_execution ();
$check_email_queue = check_email_queue();

if (!$check_cron_exec || !$check_email_queue) {
	$got_alerts = 1;
	echo '<a href: >'.print_image('images/header_warning.png', true, array("onclick" => "openAlerts()","alt" => __('Warning'), "id" => "alerts", 'title' => __('Warning'))).'</a>';
}

echo '<a href="index.php?sec=users&sec2=operation/users/user_edit&id='.$config['id_user'].'" >';

$avatar = get_db_value ('avatar', 'tusuario', 'id_usuario', $config["id_user"]);
if (!$avatar) {
	if (dame_admin ($config['id_user']))
		echo print_image('images/header_suit.png', true, array("alt" => $config['id_user'], 'title' => $config['id_user']));
	else
		echo print_image('images/header_user.png', true, array("alt" => $config['id_user'], 'title' => $config['id_user']));
} else {
	echo print_image('images/avatars/'.$avatar.'_small.png', true, array("alt" => $config['id_user'], 'title' => $config['id_user']));
}

echo '</a>';

echo '<a href="index.php?logout=1">' . print_image('images/header_logout.png', true, array("alt" => __('Logout'), 'title' => __('Logout'))) . '</a>';

if (isset($config["id_user"]) && dame_admin($config["id_user"]) && $show_setup != MENU_HIDDEN) {

	echo '<a href="index.php?sec=godmode&sec2=godmode/setup/setup" id="setup_link"><img src="images/header_setup.png" title="' . __('Setup') . '"></a>';
}

echo '</td></tr>';

echo '</table>';

echo "<div class= 'dialog ui-dialog-content' id='alert_window'></div>";

?>

<script type="text/javascript" src="include/js/integria_header.js"></script>
<script type="text/javascript" src="include/js/integria.js"></script>

<script type="text/javascript">

$(document).ready (function () {
	
	<?php
		if ($got_alerts) {
	?>
			pulsate ($("#alerts"));
	<?php
		}
	?>
		
});
</script>
