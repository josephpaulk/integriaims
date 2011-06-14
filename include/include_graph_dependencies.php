<?php

// INTEGRIA IMS v2.0
// http://www.integriaims.com
// ===========================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2008 Esteban Sanchez, estebans@artica.es
// Copyright (c) 2007-2011 Artica, info@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License
// (LGPL) as published by the Free Software Foundation; version 2

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.

	/* Function that includes all graph dependencies */
	function include_graphs_dependencies($homeurl = '') {
		include_once($homeurl . 'include/functions.php');
		include_once($homeurl . 'include/functions_html.php');
		
		include_once($homeurl . 'include/graphs/functions_fsgraph.php');
		include_once($homeurl . 'include/graphs/functions_gd.php');
		include_once($homeurl . 'include/graphs/functions_utils.php');
	}
	
?>
