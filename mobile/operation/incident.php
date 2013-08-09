<?php
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2012 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

class Incident {
	
	
	
	private $acl = 'IR';
	private $permission = false;
	
	function __construct () {
		$system = System::getInstance();
		
		
		
		// ACL
		if ($system->checkACL($this->acl)) {
			$this->permission = true;
		}
		
	}
	
	public function setId ($id) {
		$this->id = $id;
	}
	
	private function insertIncident () {
		
	}
	
	private function updateIncident () {
		
	}
	
	private function deleteIncident () {
		
	}
	
	private function showIncident () {
		
		$ui = Ui::getInstance();
		
		$ui->createPage();
		
		
	}
	
	public function show () {
		if ($this->permission) {
			$this->showIncidents();
		}
		else {
			$this->showNoPermission();
		}
	}
	
	private function showNoPermission () {
		$system = System::getInstance();
		
		audit_db ($system->getConfig('id_user'), $REMOTE_ADDR, "ACL Violation",
			"Trying to access to incident section");
		$error['title_text'] = __('You don\'t have access to this page');
		$error['content_text'] = __('Access to this page is restricted to 
			authorized users only, please contact to system administrator 
			if you need assistance. <br><br>Please know that all attempts 
			to access this page are recorded in security logs of Integria 
			System Database');
		$home = new Home();
		$home->show($error);
	}
	
	public function ajax ($parameter2 = false) {
		// Fill me in the future
	}
	
}

?>
