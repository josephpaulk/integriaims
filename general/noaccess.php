<?php
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

	
global $config;

	echo "<center>";
	echo '<div style="margin-top: 100px; width: 450px;">';
	echo '<h2 style="font-size:22px !important; font-weight: bold; margin-top:32px;">'.__('You don\'t have access to this page').'</h2>';
	echo "<p align='center'>";
	echo "<img src='".$config["base_url"]."/images/noaccess.gif'>";
	echo "<p style='font-size:18px; font-weight: normal; line-height: 1.5em;'>". __('Access to this page is restricted to authorized users only, please contact system administrator if you need assistance. <br><br>Please know that all attempts to access this page are recorded in security logs of Integria System Database.'). "</p>";
	echo "</p>";
	echo "</div>";
	echo "</center>";
	exit;

?>
