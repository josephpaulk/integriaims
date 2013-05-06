<?php

// Integria IMS - http://integriaims.com
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

global $config;

include_once("include/functions_incidents.php");


$get_incidents_search = get_parameter('get_incidents_search', 0);
$get_incident_name = get_parameter('get_incident_name', 0);

if ($get_incidents_search) {
	
	$filter = array ();
	$filter['string'] = (string) get_parameter ('search_string');
	$filter['priority'] = (int) get_parameter ('search_priority', -1);
	$filter['id_group'] = (int) get_parameter ('search_id_group', 1);
	$filter['status'] = (int) get_parameter ('search_status', -10);
	$filter['id_product'] = (int) get_parameter ('search_id_product');
	$filter['id_company'] = (int) get_parameter ('search_id_company');
	$filter['id_inventory'] = (int) get_parameter ('search_id_inventory');
	$filter['serial_number'] = (string) get_parameter ('search_serial_number');
	$filter['sla_fired'] = (bool) get_parameter ('search_sla_fired');
	$filter['id_incident_type'] = (int) get_parameter ('search_id_incident_type');
	$filter['id_user'] = (string) get_parameter ('search_id_user', '');
	$filter['id_incident_type'] = (int) get_parameter ('search_id_incident_type');
	$filter['id_user'] = (string) get_parameter ('search_id_user', '');
	$filter['first_date'] = (string) get_parameter ('search_first_date');
	$filter['last_date'] = (string) get_parameter ('search_last_date');	
	
	$ajax = get_parameter("ajax");
	
	$filter_form = false;
	
	form_search_incident (false, $filter_form);
	
	incidents_search_result($filter,$ajax);
}

if ($get_incident_name) {
	$id = get_parameter("id");
	
	$name = get_db_value ("titulo", "tincidencia", "id_incidencia", $id);
	
	echo $name;
}

?>
