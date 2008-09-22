<?php

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


// Load global vars

global $config;

if (check_login() != 0) {
    audit_db("Noauth",$config["REMOTE_ADDR"], "No authenticated access","Trying to access event viewer");
    require ("general/noaccess.php");
    exit;
}
    
if (give_acl($config["id_user"], 0, "IR") != 1){
    // Doesn't have access to this page
    audit_db ($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access to project detail page");
    include ("general/noaccess.php");
    exit;
}

$id_user = give_parameter_post ("user_form", $config["id_user"]);
$completion = give_parameter_post ("completion", 100);
$project_kind = give_parameter_post ("project_kind", "defined_end");

echo "<form name='xx' method=post action='index.php?sec=projects&sec2=operation/projects/project_tree'>";
// Show user
combo_user_visible_for_me ($id_user, "user_form", 0, "PR");

// Show completion level
echo "<select name='completion'>";
if ($completion == -1)
    echo "<option value=-1>All";
if ($completion == 100)
    echo "<option value=100>Not finished";
if ($completion == 666)
    echo "<option value=666>Done";

echo "<option value=-1>All";
echo "<option value=100>Not finished";
echo "<option value=666>Done";
echo "</select>";

// Project kind (all time or defined end)
echo "<select name='project_kind'>";
if ($project_kind == "all")
    echo "<option value=all>All projects";
if ($completion == "defined_end")
    echo "<option value='defined_end'>Defined end";

echo "<option value='defined_end'>Defined end";
echo "<option value='all'>All projects";
echo "</select>";


echo "<input type=submit value=go class='sub upd'>";
echo "</form>";

if ($id_user != ""){
    $mapfilename = $config["base_url"]. "/attachment/tmp/$id_user.projectall.map";

    echo "<A HREF='$mapfilename'>";
    echo "<img border=0  src='include/functions_graph.php?type=all_project_tree&project_kind=$project_kind&id_user=$id_user&completion=$completion' ISMAP></A>";
}

?>
