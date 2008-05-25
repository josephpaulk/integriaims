<?php

// Integria 1.1 - http://integria.sourceforge.net
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

if (dame_admin($config["id_user"] == 0)) {
    audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access incident / group manager");
    require ("general/noaccess.php");
    exit;
}

echo "<h2>".lang_string ("Global incident SLA assignment")."</h2>";

$create = get_parameter ("create", 0);
$insert = get_parameter ("insert", 0);
$delete = get_parameter ("delete", 0);

if ($delete != 0){
    $id_group = $delete;
    $sql = "DELETE FROM tgroup_manager WHERE id_group = $delete";
    $resq1=mysql_query($sql);
    if ($resq1)
        echo "<h3 class='suc'>".lang_string ("Assigment deleted succesfully")."</h3>";
    else {
        echo "<h3 class='error'>".lang_string ("Assigment cannot be deleted succesfully")."</h3>";
        echo $sql;
    }
}

if ($insert != 0){
    $id_group = get_parameter ("group",1);
    $id_user  = get_parameter ("user_form","");
    $email = get_parameter ("email", 0);
    $sla_min_rt = get_parameter ("sla_min_rt", 48);
    $sla_max_rt = get_parameter ("sla_max_rt", 144);
    $sla_max_oi = get_parameter ("sla_max_oi", 5);

    $sql = "INSERT INTO tgroup_manager (id_group, id_user, forced_email, max_response_hr, max_resolution_hr, max_active) VALUES ($id_group, '$id_user', $email, '$sla_min_rt', '$sla_max_rt', '$sla_max_oi')";
    $resq1=mysql_query($sql);
    echo "<h3 class='suc'>".lang_string ("Assigment added succesfully")."</h3>";
}

if ($create != 0){
    echo "<table cellpadding=4 cellspacing=4 width=600 class='databox_color'>";
    echo "<form name=x action='index.php?sec=godmode&sec2=godmode/setup/incident&insert=1' method=post>";

    echo "<tr><td class=datos>";
    echo lang_string ("Group");
    echo "<td class=datos>";
    combo_groups ("-1", "IR"); // form called "group"

    echo "<tr><td class=datos>";
    echo lang_string ("Default user");
    echo "<td class=datos>";
    combo_user_visible_for_me ("", "user_form", 0, "IR"); // form called user_form

    echo "<tr><td class=datos>";
    echo lang_string ("Mail enabled");
    echo "<td class=datos>";
    echo "<input type=checkbox value=1 checked name='email'>";

    echo "<tr><td class=datos>";
    echo lang_string ("SLA Min. Response time (hr)");
    echo "<td class=datos>";
    echo "<input type=text size=15 name=sla_min_rt value=24>";

    echo "<tr><td class=datos>";
    echo lang_string ("SLA Max. Resolution time (hr)");
    echo "<td class=datos>";
    echo "<input type=text size=15 name=sla_max_rt value=120>";

    echo "<tr><td class=datos>";
    echo lang_string ("SLA Max. Opened incidents");
    echo "<td class=datos>";
    echo "<input type=text size=15 name=sla_max_oi value=4>";
    echo "</table>";
    echo "<table cellpadding=4 cellspacing=4 width=600 class='databox_color'>";
    echo "<tr><td align='right'>";
    echo "<input type=submit class='sub upd' value='Go'>";
    echo "</table>";

}


// Show table unless create mode is active
if ($create == 0){
    echo "<table cellpadding=4 cellspacing=4 width=800 class='databox_color'>";
    echo "<th>".$lang_label["group_name"]."</th>";
    echo "<th>".lang_string ("Default user")."</th>";
    echo "<th>".lang_string ("Mail")."</th>";
    echo "<th>".lang_string ("SLA Min. Response")."</th>";
    echo "<th>".lang_string ("SLA Max. Resolution")."</th>";
    echo "<th>".lang_string ("SLA Max. Opened")."</th>";
    echo "<th>".$lang_label["delete"]."</th>";
    $sql1='SELECT * FROM tgroup_manager ORDER BY id_group';
    $result=mysql_query($sql1);
    $color=0;
    while ($row=mysql_fetch_array($result)){
        if ($color == 1){
            $tdcolor = "datos";
            $color = 0;
            }
        else {
            $tdcolor = "datos2";
            $color = 1;
        }
        if ($row["id_group"] != 1){
            echo "
            <tr>
                <td class='$tdcolor'>
                <b><a href='index.php?sec=users&
                sec2=godmode/grupos/configurar_grupo&
                id_grupo=".$row["id_group"]."'>".give_db_sqlfree_field ("SELECT nombre FROM tgrupo WHERE id_grupo = ".$row["id_group"])."</a></b></td>";
            echo "<td class='$tdcolor' align='center'>";
            echo $row["id_user"];

            echo "<td class='$tdcolor' align='center'>";
            if ($row["forced_email"] == 1)
                echo "Yes";
            else
                echo "No";

            echo "<td class='$tdcolor' align='center'>";
            echo $row["max_response_hr"]. " hr";

            echo "<td class='$tdcolor' align='center'>";
            echo $row["max_resolution_hr"]. " hr";

            echo "<td class='$tdcolor' align='center'>";
            echo $row["max_active"];

            echo "<td class='$tdcolor' align='center'>";
            echo "<a href='index.php?sec=godmode&sec2=godmode/setup/incident&delete=".$row["id_group"]."'
                    onClick='if (!confirm(\' ".$lang_label["are_you_sure"]."\')) 
                    return false;'>
                    <img border='0' src='images/cross.png'></a>";
                
                echo "</td></tr>";
        }
    }
    echo "</table>";
    echo "<table cellpadding=4 cellspacing=4 width=800 class='databox_color'>";
    echo "<form method=post action='index.php?sec=godmode&sec2=godmode/setup/incident&create=1'>";
    echo "<tr><td align='right'>";
    echo "<input type='submit' class='sub next' name='crt' value='".$lang_label["create_group"]."'>";
    echo "</form></td></tr></table>";
}

	
?>