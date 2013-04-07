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
echo "<td width=250>";

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
echo '</td><td  width=300>';

//// This div is necessary for javascript actions. Dont touch ///
echo '<div style="font-size: 0px;" id="id_user">'.$config['id_user']."</div>";
/////////////////////////////////////////////////////////////////

echo '<a href="index.php?sec=users&sec2=operation/users/user_edit&id='.$config['id_user'].'" >';
if (dame_admin ($config['id_user']))
	echo '<img src="images/user_suit.png"> ';
else
	echo '<img src="images/user_green.png"> ';
echo ' <span style="font-weight: bold; color: #ffffff"">['.$config['id_user'].']</span></a>';

echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
echo '<a href="index.php?logout=1"><img src="images/lock.png"><span style="font-weight: bold; color: #ffffff"> '. __('Logout').'</span></a>';
echo '</td><td width=300 style="padding: 0px; margin: 0px;">';
echo "<form method=post action='index.php?sec2=operation/search'>";
echo "&nbsp;";
echo "<input style='height: 11px; font-size: 11px;' type=text name='search_string' size=20 value='$search_string'>";
echo "&nbsp;&nbsp;&nbsp;";
echo "<input class='sub search' style='height: 23px;' type=submit name='submit' size=45 value='".__('Search')."'>";
echo '</form>';
echo '</td></tr>';

echo '</table>';
?>
