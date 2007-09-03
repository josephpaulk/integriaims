<?php

// FRITS - the FRee Incident Tracking System
// =========================================
// Copyright (c) 2007 Sancho Lerena, slerena@openideas.info
// Copyright (c) 2007 Artica Soluciones Tecnologicas

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

global $config;
global $lang_label;

?>
<div align='center'>
	<div id='login'>	
		<div id="login_box" style='width:75%;padding-left: 40px;'>
			<center><?PHP echo $lang_label["logout_msg"]; ?></center>
		</div>
		<div id="logo_box">
			<center>
			<a href="index.php"><img src="images/topi_logo_small.jpg" border="0" alt="logo"></a>
			</center>
		</div>
		<div id="foot"><br><br><br><?php require("general/footer.php") ?></div>
	</div>
</div>
