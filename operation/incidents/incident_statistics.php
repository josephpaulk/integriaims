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

if (comprueba_login() == 0) {
	$iduser=$_SESSION['id_usuario'];
	if (give_acl($id_user, 0, "IR")==1) {	
		echo "<h2>".__('Incident management')."</h2>";
		echo "<h3>".__('Statistics')."<a href='help/".$help_code."/chap4.php#44' target='_help' class='help'>&nbsp;<span>".__('Help')."</span></a></h3>";
?>
<img src="reporting/fgraph.php?tipo=estado_incidente" border=0>
<br><br>
<img src="reporting/fgraph.php?tipo=prioridad_incidente" border=0>
<br><br>
<img src="reporting/fgraph.php?tipo=group_incident" border=0>
<br><br>
<img src="reporting/fgraph.php?tipo=user_incident" border=0>
<br><br>
<img src="reporting/fgraph.php?tipo=source_incident" border=0>
<br><br>
<?php
	} else {
			require ("general/noaccess.php");
			audit_db($id_user,$REMOTE_ADDR, "ACL Violation","Trying to access Incident section");
        }
}
?>
