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


echo "<div id='login_f'>";
echo '<a href="index.php"><img src="images/frits_logo.gif" border="0"></a><br><br>';
echo "<div align='center' class='databox' >";
echo "<h1>".$lang_label['err_auth']."</h1>";
echo "<div id='noa'><img src='images/noaccess.gif'></div>";
echo "<div align='center' >";

echo "</div><br><br>";
echo '<div class="databox"><br>'.$lang_label["err_auth_msg"]."<br><br></div>";
echo "</div></div>";