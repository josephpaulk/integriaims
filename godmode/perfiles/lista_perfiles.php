<?php

// Integria 1.0 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2008 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.


// Load globar vars
global $config;
check_login();

if (give_acl($config["id_user"], 0, "UM")==0) {
    audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access User Management");
    require ("general/noaccess.php");
    exit;
}

$id_user = $config["id_user"];
        

// Profile deletion
if (isset($_GET["delete_profile"])){ // if any parameter is modified
	$id_perfil= get_parameter ("delete_profile",0);
	// Delete profile
	$query_del1="DELETE FROM tprofile WHERE id = '".$id_perfil."'";
	$query_del2="DELETE FROM tusuario_perfil WHERE id_perfil = '".$id_perfil."'";
	$resq1=mysql_query($query_del1);
		if (! $resq1)
			echo "<h3 class='error'>".$lang_label["delete_profile_no"]."</h3>";
		else
			echo "<h3 class='suc'>".$lang_label["delete_profile_ok"]."</h3>";
	$resq1=mysql_query($query_del2);
	unset($id_perfil); // forget it to show list
}
// Profile creation
elseif (isset($_GET["new_profile"])){ // create a new profile
	$id_perfil = -1;
	$name = "";
	$ir = 0;
	$iw = 0;
	$im = 0;
	$um = 0;
	$dm = 0;
	$fm = 0;
	$ar = 0;
    $aw = 0;
    $am = 0;
    $pr = 0;
    $pm = 0;
    $pw = 0;
    $tw = 0;
    $tm = 0;

} elseif (isset($_GET["edit_profile"])){ // Edit profile (read data to show in form)
	// Profile edit
	$id_perfil= get_parameter ("edit_profile",0);
	$query_del1="SELECT * FROM tprofile WHERE id = '".$id_perfil."'";
	$resq1=mysql_query($query_del1);
	$rowq1=mysql_fetch_array($resq1);
	if (!$rowq1){
		echo "<h3 class='error'>".$lang_label["profile_error"]."</h3>";
		echo "</table>";
		include ("general/footer.php");
		exit;
	}
	else

	$name = $rowq1["name"];
	$ir = $rowq1["ir"];
    $iw = $rowq1["iw"];
    $im = $rowq1["im"];
    $um = $rowq1["um"];
    $dm = $rowq1["dm"];
    $fm = $rowq1["fm"];
    $ar = $rowq1["ar"];
    $aw = $rowq1["aw"];
    $am = $rowq1["am"];
    $pr = $rowq1["pr"];
    $pw = $rowq1["pw"];
    $pm = $rowq1["pm"];
    $tw = $rowq1["tw"];
    $tm = $rowq1["tm"];

} elseif (isset($_GET["update_data"])){ // Update or Create a new record (writes on DB)
	// Profile edit
	$ir = get_parameter ("ir", 0);
	$iw = get_parameter ("iw", 0);
	$im = get_parameter ("im", 0);
	$um = get_parameter ("um", 0);
	$dm = get_parameter ("dm", 0);
	$fm = get_parameter ("fm", 0);
    $ar = get_parameter ("ar", 0);
    $aw = get_parameter ("aw", 0);
    $am = get_parameter ("am", 0);
    $pr = get_parameter ("pr", 0);
    $pm = get_parameter ("pm", 0);
    $pw = get_parameter ("pw", 0);
    $tw = get_parameter ("tw", 0);
    $tm = get_parameter ("tm", 0);
	$id_perfil = get_parameter ("id_perfil", 0);
	$name = get_parameter ("name" , "");
	
	// update or insert ??
	
	if ($id_perfil == -1) { // INSERT
		$query = "INSERT INTO tprofile (name,ir,iw,im,um,dm,fm,ar,aw,am,pr,pw,pm,tw,tm) VALUES 
		('$name', $ir, $iw, $im, $um, $dm, $fm, $ar, $aw, $am, $pr, $pw, $pm, $tw, $tm)";
        //echo "DEBUG: ".$query;
		$res=mysql_query($query);
		if ($res)
			echo "<h3 class='suc'>".$lang_label["create_profile_ok"]."</h3>";
		else {
			echo "<h3 class='error'>".$lang_label["create_profile_no"]."</h3>";
		}

	} else { // UPDATE
		$query ="UPDATE tprofile SET 
		name = '$name',
        ir = $ir,
        iw = $iw,
        im = $im,
        um = $um,
        dm = $dm,
        fm = $fm,
        ar = $ar,
        aw = $aw,
        am = $am,
        pr = $pr,
        pm = $pm,
        pw = $pw,
        tw = $tw,
        tm = $tm 
		WHERE id = $id_perfil ";
        //echo "DEBUG: ".$query;
		$res=mysql_query($query);
		echo "<h3 class='suc'>".$lang_label['profile_upd']."</h3>";
	}
	unset($id_perfil);
}

// Header
echo '<h2>'.$lang_label["profile_title"].'</h2>';
if (isset($_GET["new_profile"]))
    echo '<h3>'.$lang_label["create_profile"].'</h3>';
elseif (isset($_GET["edit_profile"]))
    echo '<h3>'.$lang_label["update_profile"].'</h3>';
else 
    '<h3>'.$lang_label["definedprofiles"].'</h3>';

// Form to manage date
if (isset ($id_perfil)){ // There are values defined, let's show form with data for INSERT or UPDATE
	echo "<table width='400' cellpadding='3' cellspacing='3'>";
	echo "<form method='post' action='index.php?sec=gperfiles&sec2=godmode/perfiles/lista_perfiles&update_data'>";
	echo "<input type=hidden name=id_perfil value='".$id_perfil."'>";
	echo "<tr><td class=datos>".$lang_label["profile_name"]."<td class=datos><input name='name' type=text size='27' value='".$name."'>";
	
	echo "<tr><td class=datos2>".$lang_label["incident_view"]."<td class=datos2><input name='ir' type=checkbox class='chk' value='1' ";
	if ($ir == 1) echo "checked"; echo ">";
	
	echo "<tr><td class=datos>".$lang_label["incident_edit"]."<td class=datos><input name='iw' type=checkbox class='chk' value='1' ";
	if ($iw == 1) echo "checked";echo ">";
	
	echo "<tr><td class=datos2>".$lang_label["manage_incidents"]."<td class=datos2><input name='im' type=checkbox class='chk' value='1' ";
	if ($im == 1) echo "checked";echo ">";
	
	echo "<tr><td class=datos>".lang_string ("agenda_read")."<td class=datos><input name='ar' type=checkbox class='chk' value='1' ";
	if ($ar == 1) echo "checked";echo ">";
	
	echo "<tr><td class=datos2>".lang_string ("agenda_write")."<td class=datos2><input name='aw'  type=checkbox class='chk' value='1' ";
	if ($aw == 1) echo "checked";echo ">";
	
	echo "<tr><td class=datos>".lang_string ("agenda_management")."<td class=datos><input name='am'  type=checkbox class='chk' value='1' ";
	if ($am == 1) echo "checked";echo ">";
	
    echo "<tr><td class=datos2>".lang_string ("user_management")."<td class=datos2><input name='um'  type=checkbox class='chk' value='1' ";
    if ($um == 1) echo "checked";echo ">";
    
    echo "<tr><td class=datos>".lang_string ("database_management")."<td class=datos><input name='dm'  type=checkbox class='chk' value='1' ";
    if ($dm == 1) echo "checked";echo ">";

    echo "<tr><td class=datos2>".lang_string ("framework_management")."<td class=datos2><input name='fm'  type=checkbox class='chk' value='1' ";
    if ($fm == 1) echo "checked";echo ">";

    echo "<tr><td class=datos>".lang_string ("project_read")."<td class=datos><input name='pr'  type=checkbox class='chk' value='1' ";
    if ($pr == 1) echo "checked";echo ">";

    echo "<tr><td class=datos2>".lang_string ("project_write")."<td class=datos2><input name='pw'  type=checkbox class='chk' value='1' ";
    if ($pw == 1) echo "checked";echo ">";

    echo "<tr><td class=datos>".lang_string ("project_management")."<td class=datos><input name='pm'  type=checkbox class='chk' value='1' ";
    if ($pm == 1) echo "checked";echo ">";

    echo "<tr><td class=datos2>".lang_string ("task_write")."<td class=datos2><input name='tw' type=checkbox class='chk' value='1' ";
    if ($tw == 1) echo "checked";echo ">";

    echo "<tr><td class=datos>".lang_string ("task_management")."<td class=datos><input name='tm' type=checkbox class='chk' value='1' ";
    if ($tm == 1) echo "checked";echo ">";
	
	if (isset($_GET["new_profile"])){
        echo "<tr><td colspan='3' align='right'><input name='crtbutton' type='submit' class='sub' value='".$lang_label["create"]."'>";
    }
    if (isset($_GET["edit_profile"])){
        echo "<tr><td colspan='3' align='right'><input name='uptbutton' type='submit' class='sub' value='" .$lang_label["update"]."'>";
    }
	echo "</table>";
	

// ====================
// VIEW LIST OF DATA
// ====================

} else { 
	$color=1;
	echo '<table cellpadding=3 cellspacing=3 border=0>';
	$query_del1="SELECT * FROM tprofile";
	$resq1=mysql_query($query_del1);
    echo "<tr>";
    echo "<th width='180px'><font size=1>".$lang_label["profiles"];
    echo "<th width='40px'><font size=1>IR";
    echo "<th width='40px'><font size=1>IW";
    echo "<th width='40px'><font size=1>IM";
    echo "<th width='40px'><font size=1>UM";
    echo "<th width='40px'><font size=1>DM";
    echo "<th width='40px'><font size=1>FM";
    echo "<th width='40px'><font size=1>AR";
    echo "<th width='40px'><font size=1>AW";
    echo "<th width='40px'><font size=1>AM";
    echo "<th width='40px'><font size=1>PR";
    echo "<th width='40px'><font size=1>PW";
    echo "<th width='40px'><font size=1>PM";
    echo "<th width='40px'><font size=1>TW";
    echo "<th width='40px'><font size=1>TM";
	echo "<th width='40px'>".$lang_label["delete"]."</th></tr>";
	while ($rowq1=mysql_fetch_array($resq1)){
		$id_perfil = $rowq1["id"];
		$name = $rowq1["name"];
        $ir = $rowq1["ir"];
        $iw = $rowq1["iw"];
        $im = $rowq1["im"];
        $um = $rowq1["um"];
        $dm = $rowq1["dm"];
        $fm = $rowq1["fm"];
        $ar = $rowq1["ar"];
        $aw = $rowq1["aw"];
        $am = $rowq1["am"];
        $pr = $rowq1["pr"];
        $pw = $rowq1["pw"];
        $pm = $rowq1["pm"];
        $tw = $rowq1["tw"];
        $tm = $rowq1["tm"];
		if ($color == 1){
			$tdcolor = "datos";
			$color = 0;
		}
		else {
			$tdcolor = "datos2";
			$color = 1;
		}
		echo "<td class='$tdcolor'><a href='index.php?sec=users&sec2=godmode/perfiles/lista_perfiles&edit_profile=".$id_perfil."'><b>".$name."</b></a>";
		
		echo "<td class='$tdcolor'>";
		if ($ir == 1) echo "<img src='images/ok.png' border=0>";
			
		echo "<td class='$tdcolor'>";
		if ($iw == 1) echo "<img src='images/ok.png' border=0>";
			
		echo "<td class='$tdcolor'>";
		if ($im == 1) echo "<img src='images/ok.png' border=0>";
			
		echo "<td class='$tdcolor'>";
		if ($um == 1) echo "<img src='images/ok.png' border=0>";
			
		echo "<td class='$tdcolor'>";
		if ($dm == 1) echo "<img src='images/ok.png' border=0>";
			
		echo "<td class='$tdcolor'>";
		if ($fm == 1) echo "<img src='images/ok.png' border=0>";
			
		echo "<td class='$tdcolor'>";
		if ($ar == 1) echo "<img src='images/ok.png' border=0>";
			
		echo "<td class='$tdcolor'>";
		if ($aw == 1) echo "<img src='images/ok.png' border=0>";
			
		echo "<td class='$tdcolor'>";
		if ($am == 1) echo "<img src='images/ok.png' border=0>";

        echo "<td class='$tdcolor'>";
        if ($pr == 1) echo "<img src='images/ok.png' border=0>";

        echo "<td class='$tdcolor'>";
        if ($pw == 1) echo "<img src='images/ok.png' border=0>";

        echo "<td class='$tdcolor'>";
        if ($pm == 1) echo "<img src='images/ok.png' border=0>";
			
        echo "<td class='$tdcolor'>";
        if ($tw == 1) echo "<img src='images/ok.png' border=0>";
        echo "<td class='$tdcolor'>";
        if ($tm == 1) echo "<img src='images/ok.png' border=0>";


		echo "<td class='$tdcolor' align='center'><a href='index.php?sec=users&sec2=godmode/perfiles/lista_perfiles&delete_profile=".$id_perfil."' onClick='if (!confirm(\' ".$lang_label["are_you_sure"]."\')) return false;'><img border='0' src='images/cross.png'></a></td></tr>";
		
	}
	echo "</div></td></tr>";
	echo "<tr><td colspan='12' align='right'>";
	echo "<form method=post action='index.php?sec=gperfiles&sec2=godmode/perfiles/lista_perfiles&new_profile=1'>";
	echo "<input type='submit' class='sub next' name='crt' value='".$lang_label["create_profile"]."'>";
	echo "</form></table>";
}
	
?>