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

// Load global vars

	global $config;
	if (check_login() != 0) {
		audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access", "Trying to access event viewer");
		require ("general/noaccess.php");
		exit;
	}

    $days_f = array();
    $date = date('Y-m-d');
    $year = substr($date, 0,4);

	if (dame_admin ($config["id_user"]) == 0){
        $id_user_show = $config["id_user"];
        echo "<h2>".__('Annual report for user')." ". $id_user_show. "</h2>";
    } else {
    	$id_user_show = get_parameter ("id_user", $config["id_user"]);
	    echo "<h2>".__('Annual report for user')." ". $id_user_show. "</h2>";

		echo "<table cellpadding=4 cellspacing=4 class='blank' style='margin-left: 10px'>";
		echo "<tr><td>";
        echo "<form name='xx' method=post action='index.php?sec=users&sec2=operation/user_report/report_annual'><td>";
        // Show user
        combo_user_visible_for_me ($id_user, "id_user", 0, "AR");
		echo "<td>";
        echo "<input type=submit value=go class='sub upd'>";
        echo "</form></table>";
    }

    echo "<table class='button'><tr>";
    echo "<td style='background-color: #FFFF80;'>";
    echo return_vacations_user ($id_user_show, $year). "</td><td>".__('Vacations days');
    
    echo "<td style='background-color: #98FF8B;'>";
    echo return_daysworked_user ($id_user_show, $year). "</td><td>".__('Days worked (projects)');

    echo "<td style='background-color: #FF7BFE;'>";
    echo return_daysworked_incident_user ($id_user_show, $year). "</td><td>".__('Days worked (incidents)');

    echo "</table>";
    
    echo "<table>";
    echo "<tr>";
    for ($ax = 1; $ax < 13; $ax++){
        if (fmod($ax-1,3) == 0)
            echo "<tr>";
        echo "<td valign=top>";
        echo generate_small_work_calendar ($year, $ax, $days_f, 3, 0, "en", $id_user_show);
         
    }
    echo "</table>";


?>

