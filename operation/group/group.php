<?php
// Integria 1.1 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008 Artica Soluciones Tecnologicas
// Copyright (c) 2008 Esteban Sanchez, estebans@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

if (defined ('AJAX')) {
	$id_group = get_parameter('id_group');
	$id_user = get_parameter('id_user');

	$group = get_db_row_filter('tgrupo', array('id_grupo' => $id_group));
	//soft limit is open incidents.
	//hard limit is count all incidents.

	if (($group['hard_limit'] == 0) && ($group['soft_limit'] == 0)) {
		echo "correct"; //type
	}
	else {
		$countOpen = get_db_all_rows_sql('SELECT COUNT(*) AS c
			FROM tincidencia WHERE estado IN (1,2,3,4,5) AND id_grupo = ' . $id_group . ' AND id_creator = "' . $id_user . '"');
		$countAll = get_db_all_rows_sql('SELECT COUNT(*) AS c
			FROM tincidencia WHERE id_grupo = ' . $id_group . ' AND id_creator = "' . $id_user . '"');
		$countOpen = $countOpen[0]['c'];
		$countAll = $countAll[0]['c'];
		if (($group['soft_limit'] != 0) && ($group['soft_limit'] == $countOpen)) {
			echo "open_limit"; //type
			echo "//";
			echo __('Warning'); //title
			echo "//";
			echo __('You have ') . $countOpen . __(' open incidents, ') . $group['soft_limit'] . __(' over.'); //content
		}
		else if (($group['hard_limit'] != 0) && ($group['hard_limit'] == $countAll)) {
			echo "incident_limit"; //type
			echo "//";
			echo __('Alert'); //title
			echo "//";
			echo __('You are in the limit ') . $group['hard_limit'] . __(' incidents in this group.'); //content
		}
		else {
			echo "correct";
		}
	}

	return;
}
?>