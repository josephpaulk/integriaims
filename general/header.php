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

check_login();

// I prefer to make the layout with tables here.
echo "<table width=100% cellpadding=0 cellspacing=0 style='margin: 0px; padding:0px;'>";
echo "<tr>";
echo "<td width=180>";

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
echo "</td><td>";

echo '<a href="index.php?sec=users&sec2=operation/users/user_edit&id='.$config['id_user'].'" >';
if (dame_admin ($config['id_user']))
	echo '<img src="images/user_suit.png"> ';
else
	echo '<img src="images/user_green.png"> ';
echo __('You are connected as').' <span style="font-wieght: bold; color: #ffffff"">['.$config['id_user'].']</span></a>';
echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
echo '<a href="index.php?logout=1"><img src="images/lock.png"> '. __('Logout').'</a>';
echo '</td></tr>';
echo '</table>';
?>
