<?php 

// Integria 2.0 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

// Load global vars
require("include/config.php");
//require("include/functions.php");
//require("include/functions_db.php");
if (comprueba_login() == 0) 
	if ((give_acl($id_user, 0, "DM")==1) or (dame_admin($id_user)==1)) {
 	// Todo for a good DB maintenance 
 	/* 
 		- Delete too on datos_string and and datos_inc tables 
 		
 		- A function to "compress" data, and interpolate big chunks of data (1 month - 60000 registers) 
 		  onto a small chunk of interpolated data (1 month - 600 registers)
 		
 		- A more powerful selection (by Agent, by Module, etc).
 	*/
		
	echo "<h2>".__('Database Maintenance')."</h2>";
	echo "<h3>".__('Database Information')."<a href='help/".$help_code."/chap8.php#81' target='_help' class='help'>&nbsp;<span>".__('Help')."</span></a></h3>";
	echo "<table border=0>";
	echo "<tr><td><img src='reporting/fgraph.php?tipo=db_agente_modulo'><br>";
	echo "<tr><td><br>";
	echo "<tr><td><img src='reporting/fgraph.php?tipo=db_agente_paquetes'><br>";
	echo "<br><br><a href='index.php?sec=gdbman&sec2=godmode/db/db_info_data'>".__('Press here to get DB info as text')."</a>";
	echo "</table>";
	} 
	else {
		audit_db($id_user,$REMOTE_ADDR, "ACL Violation","Trying to access Database Management Info");
		require ("general/noaccess.php");
	}
?>
