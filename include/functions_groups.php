<?php 

// INTEGRIA IMS v2.0
// http://www.integriaims.com
// ===========================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2008 Esteban Sanchez, estebans@artica.es
// Copyright (c) 2007-2008 Artica, info@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License
// (LGPL) as published by the Free Software Foundation; version 2

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Lesser General Public License for more details.

/**
 * Sends an email to a group.
 *
 * If the group doesn't have an email configured, the email is only sent
 * to the default user.
 *
 * @param int Group id.
 * @param string Email subject.
 * @param string Email body.
 */
function send_group_email ($id_group, $subject, $body) {
	$group = get_db_row ("tgrupo", "id_grupo", $id_group);
	$name = $group['nombre'];
	$email = $group['email'];
	/* If the group has no email, use the email of the risponsable */
	if ($email == '') {
		$email = get_user_email ($group['id_user_default']);
	}
	
	integria_sendmail ($email, $subject, $body);
}

/**
 * Selects all groups (array (id => name)) or groups filtered
 *
 * @param mixed Array with filter conditions to retrieve groups or false.  
 *
 * @return array List of all groups
 */
function group_get_groups ($filter = false) {
	if ($filter === false) { 
		$grupos = get_db_all_rows_in_table ("tgrupo", "nombre");
	}
	else {
		$grupos = get_db_all_rows_filter ("tgrupo", $filter);
	}
	//$return = array ();
	if ($grupos === false) {
		return $return;
	}
	foreach ($grupos as $grupo) {
		$return[$grupo["id_grupo"]] = $grupo["nombre"];
	}
	return $return;
}

function print_groups_table ($groups) {
	
	enterprise_include("include/functions_groups.php");
	$return = enterprise_hook ('print_groups_table_extra', array($groups));
	if ($return === ENTERPRISE_NOT_HOOK){
		$table->width = '99%';
		$table->class = 'listing';
		$table->head = array ();
		$table->head[0] = __('Icon');
		$table->head[1] = __('Name');
		$table->head[2] = __('Parent');
		$table->head[3] = __('Delete');
		$table->data = array ();
		$table->align = array ();
		$table->align[3] = 'center';
		$table->style = array ();
		$table->style[1] = 'font-weight: bold';
		$table->size = array ();
		$table->size[0] = '40px';
		$table->size[3] = '40px';
		
		if ($groups === false)
			$groups = array ();
			foreach ($groups as $group) {
			$data = array ();
			
			$data[0] = '';
			if ($group['icon'] != '')
				$data[0] = '<img src="images/groups_small/'.$group['icon'].'" />';
				
			if ($group["id_grupo"] != 1) {
				$data[1] = '<a href="index.php?sec=users&sec2=godmode/grupos/configurar_grupo&id='.
					$group['id_grupo'].'">'.$group['nombre'].'</a>';
			} else {
				$data[1] = $group["nombre"];
			}
			$data[2] = dame_nombre_grupo ($group["parent"]);
			
			//Group "all" is special not delete and no update
			if ($group["id_grupo"] != 1) {
				$data[3] = '<a href="index.php?sec=users&
						sec2=godmode/grupos/lista_grupos&
						id_grupo='.$group["id_grupo"].'&
						delete_group=1&id='.$group["id_grupo"].
						'" onClick="if (!confirm(\''.__('Are you sure?').'\')) 
						return false;">
						<img src="images/cross.png"></a>';
			} else {
				$data[3] = "";
			}
			array_push ($table->data, $data);
		}
		print_table ($table);
	}
}

?>
