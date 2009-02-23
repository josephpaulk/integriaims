<?php

// INTEGRIA IMS 
// http://www.integriaims.com
// ===========================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2008 Artica, info@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License (LGPL)
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
	
global $config;

	echo "<center>";
	echo '<div style="margin-top: 100px; width: 450px;">';
	echo '<h2>'.__('You don\'t have access to this page').'</h2>';
	echo "<p align='center'>";
	echo "<img src='".$config["base_url"]."/images/noaccess.gif'>";
	echo "<p>". __('Access to this page is restricted to authorized users only, please contact system administrator if you need assistance. <br><br>Please know that all attempts to access this page are recorded in security logs of Integria System Database'). "</p>";
	echo "</p>";
	echo "</div>";
	echo "</center>";
	exit;

?>
