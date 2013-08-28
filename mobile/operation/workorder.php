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

class Workorder {
	
	private $id_workorder;
	private $title;
	private $assigned_user;
	private $priority;
	private $status;
	private $category;
	private $description;
	private $operation;
	
	private $acl = 'PR';
	private $permission = false;
	
	function __construct () {
		$system = System::getInstance();
		
		$this->id_workorder = (int) $system->getRequest('id_workorder', -1);
		$this->title = (string) $system->getRequest('title', "");
		$this->assigned_user = (string) $system->getRequest('assigned_user', $system->getConfig('id_user'));
		$this->priority = (int) $system->getRequest('priority', 2);
		$this->status = (int) $system->getRequest('status', 0);
		$this->category = (int) $system->getRequest('category', 0);
		$this->description = (string) $system->getRequest('description', "");
		// insert, update, delete, view or ""
		$this->operation = (string) $system->getRequest('operation', "");
		
		// ACL
		$this->permission = $this->checkPermission ($system->getConfig('id_user'), $this->acl, $this->operation, $this->id_workorder);
	}
	
	public function checkPermission ($id_user, $acl = 'PR', $operation = '', $id_workorder = -1) {
		$system = System::getInstance();
		
		$permission = false;
		if (dame_admin($id_user)) {
			$permission = true;
		} else {
			// Section access
			if ($system->checkACL($acl)) {
				// With this operations, the WU should have id
				if ( ($operation == "view" || $operation == "update" || $operation == "delete")
						&& $id_workorder > 0) {
					$workorder = get_db_row("ttodo", "id", $this->id_workorder);
					// The user should be the owner or the creator
					if ($system->getConfig('id_user') == $workorder['created_by_user']
							|| $system->getConfig('id_user') == $workorder['assigned_user']) {
						$permission = true;
					}
				} else {
					$permission = true;
				}
			}
		}
		if ( ($operation == "view" || $operation == "update" || $operation == "delete")
				&& $id_workorder < 0) {
			$permission = false;
		}
		
		return $permission;
	}
	
	public function setId ($id_workorder) {
		$this->id_workorder = $id_workorder;
	}
	
	private function setValues ($id_workorder, $title, $assigned_user, $priority, $status, $category, $description, $operation) {
		$this->id_workorder = $id_workorder;
		$this->title = $title;
		$this->assigned_user = $assigned_user;
		$this->priority = $priority;
		$this->status = $status;
		$this->category = $category;
		$this->description = $description;
		$this->operation = $operation;
	}
	
	public function insertWorkOrder ($id_user, $assigned_user, $title = "", $priority = 2, $status = 0, $category = 0, $description = "") {
		$system = System::getInstance();
		
		$sql = sprintf ("INSERT INTO ttodo (name, priority, assigned_user,
			created_by_user, progress, last_update, description, id_wo_category)
			VALUES ('%s', %d, '%s', '%s', %d, '%s', '%s', %d)",
			$title, $priority, $assigned_user, $id_user, $status, date("Y-m-d"),
			$description, $category);
		
		$id_workorder = process_sql ($sql, "insert_id");
		if ($id_workorder) {
			return $id_workorder;
		}
		return false;
	}
	
	public function updateWorkOrder ($id_workorder, $assigned_user, $title = "",$priority = 2, $status = 0,$category = 0, $description = "") {
										
		$result = process_sql ("UPDATE ttodo SET id_wo_category = $category, assigned_user = '$assigned_user',
								priority = $priority, progress = $status, description = '$description',
								last_update = '".date("Y-m-d")."', name = '$title' WHERE id = $id_workorder");
		
		if ($result) {
			$email_notify = get_db_value ("email_notify", "ttodo", "id", $id_workorder);
			clean_cache_db();
			if ($email_notify) {
				mail_workorder ($id_workorder, 0);
			}
			return true;
		}
		return false;
	}
	
	public function deleteWorkOrder ($id_workorder) {
		$result = process_sql ("DELETE FROM ttodo WHERE id = $id_workorder");
		if ($result) {
			return true;
		}
		return false;
	}
	
	private function showWorkOrder ($message = "") {
		$system = System::getInstance();
		$ui = Ui::getInstance();
		
		$ui->createPage();
		
		$back_href = "index.php?page=workorders&filter_status=0&filter_owner=".$system->getConfig('id_user');
		if ($this->id_workorder < 0) {
			$title = __("Workorder");
		} else {
			$title = __("Workorder")."&nbsp;#".$this->id_workorder;
		}
		$ui->createDefaultHeader($title,
			$ui->createHeaderButton(
				array('icon' => 'back',
					'pos' => 'left',
					'text' => __('Back'),
					'href' => $back_href)));
		$ui->beginContent();
			
			// Message popup
			if ($message != "") {
				$options = array(
					'popup_id' => 'message_popup',
					'popup_content' => $message
					);
				
				$ui->contentAddHtml($ui->getPopupHTML($options));
				$ui->contentAddHtml("<script type=\"text/javascript\">
										$(document).on('pageshow', function() {
											$(\"#message_popup\").popup(\"open\");
										});
									</script>");
			}
			$options = array (
				'id' => 'form-workorder',
				'action' => "index.php?page=workorder",
				'method' => 'POST'
				);
			$ui->beginForm($options);
				// Title
				$options = array(
					'name' => 'title',
					'label' => __('Title'),
					'value' => $this->title,
					'placeholder' => __('Title')
					);
				$ui->formAddInputText($options);
				// Assigned user
				$options = array(
					'name' => 'assigned_user',
					'id' => 'text-assigned_user',
					'label' => __('Assigned user'),
					'value' => $this->assigned_user,
					'placeholder' => __('Assigned user'),
					'autocomplete' => 'off'
					);
				$ui->formAddInputText($options);
					// Assigned user autocompletion
					// List
					$ui->formAddHtml("<ul id=\"ul-autocomplete\" data-role=\"listview\" data-inset=\"true\"></ul>");
					// Autocomplete binding
					$ui->bindMobileAutocomplete("#text-assigned_user", "#ul-autocomplete");
				// Status
				$values = array();
				if (get_db_value("need_external_validation", "ttodo", "id", $this->id_workorder)) {
					$values = wo_status_array (0);
				} else {
					$values = wo_status_array (1);
				}
				$options = array(
					'name' => 'status',
					'title' => __('Status'),
					'label' => __('Status'),
					'items' => $values,
					'selected' => $this->status
					);
				$ui->formAddSelectBox($options);
				// Priority
				$values = array();
				$values = get_priorities();
				$options = array(
					'name' => 'priority',
					'title' => __('Priority'),
					'label' => __('Priority'),
					'items' => $values,
					'selected' => $this->priority
					);
				$ui->formAddSelectBox($options);
				// Category
				$workorders = get_db_all_rows_sql ("SELECT id, name FROM two_category ORDER BY name");
				$values = array();
				if ($workorders)
				foreach ($workorders as $workorder){
					$values[$workorder[0]] = $workorder[1];
				}
				array_unshift($values, __('Any'));
				$options = array(
					'name' => 'category',
					'title' => __('Category'),
					'label' => __('Category'),
					'items' => $values,
					'selected' => $this->category
					);
				$ui->formAddSelectBox($options);
				// Description
				$options = array(
						'name' => 'description',
						'label' => __('Description'),
						'value' => $this->description
						);
				$ui->formAddHtml($ui->getTextarea($options));
				// Hidden operation (insert or update+id)
				if ($this->id_workorder < 0) {
					$options = array(
						'type' => 'hidden',
						'name' => 'operation',
						'value' => 'insert'
						);
					$ui->formAddInput($options);
					// Submit button
					$options = array(
						'text' => __('Add'),
						'data-icon' => 'plus'
						);
					$ui->formAddSubmitButton($options);
				} else {
					$options = array(
						'type' => 'hidden',
						'name' => 'operation',
						'value' => 'update'
						);
					$ui->formAddInput($options);
					$options = array(
						'type' => 'hidden',
						'name' => 'id_workorder',
						'value' => $this->id_workorder
						);
					$ui->formAddInput($options);
					// Submit button
					$options = array(
						'text' => __('Update'),
						'data-icon' => 'refresh'
						);
					$ui->formAddSubmitButton($options);
				}
			$ui->endForm();
		$ui->endContent();
		// Foooter buttons
		// Add
		if ($this->id_workorder < 0) {
			$button_add = "<a onClick=\"$('#form-workorder').submit();\" data-role='button' data-icon='plus'>"
							.__('Add')."</a>\n";
		} else {
			$button_add = "<a onClick=\"$('#form-workorder').submit();\" data-role='button' data-icon='refresh'>"
							.__('Update')."</a>\n";
		}
		// Delete
		if ($this->id_workorder > 0) {
			$button_delete = "<a href='index.php?page=workorders&operation=delete&id_workorder=".$this->id_workorder."
									&filter_status=0&filter_owner=".$system->getConfig('id_user')."'
									data-role='button' data-icon='delete'>".__('Delete')."</a>\n";
		}
		$ui->createFooter("<div data-type='horizontal' data-role='controlgroup'>$button_add"."$button_delete</div>");
		$ui->showFooter();
		$ui->showPage();
		
	}
	
	public function show () {
		if ($this->permission) {
			$system = System::getInstance();
			$message = "";
			switch ($this->operation) {
				case 'insert':
					$result = $this->insertWorkOrder($system->getConfig('id_user'), $this->assigned_user,
													$this->title, $this->priority, $this->status,
													$this->category, $this->description);
					if ($result) {
						$this->id_workorder = $result;
						$message = "<h2 class='suc'>".__('Successfully created')."</h2>";
					} else {
						$message = "<h2 class='error'>".__('An error ocurred while creating the workorder')."</h2>";
					}
					break;
				case 'update':
					$result = $this->updateWorkOrder($this->id_workorder, $this->assigned_user, $this->title,
													$this->priority, $this->status,$this->category,
													$this->description);
					if ($result) {
						$message = "<h2 class='suc'>".__('Successfully updated')."</h2>";
					} else {
						$message = "<h2 class='error'>".__('An error ocurred while updating the workorder')."</h2>";
					}
					break;
				case 'delete':
					$result = $this->deleteWorkOrder($this->id_workorder);
					if ($result) {
						$this->id_workorder = -1;
						$message = "<h2 class='suc'>".__('Successfully deleted')."</h2>";
					} else {
						$message = "<h2 class='error'>".__('An error ocurred while deleting the workorder')."</h2>";
					}
					break;
				case 'view':
					$workorder = get_db_row ("ttodo", "id", $this->id_workorder);
					$this->setValues ($this->id_workorder, $workorder['name'], $workorder['assigned_user'], $workorder['priority'],
							$workorder['progress'], $workorder['id_wo_category'], $workorder['description'], 'view');
					break;
				default:
					if ($this->id_workorder > 0) {
						$workorder = get_db_row ("ttodo", "id", $this->id_workorder);
						$this->setValues ($this->id_workorder, $workorder['name'], $workorder['assigned_user'], $workorder['priority'],
								$workorder['progress'], $workorder['id_wo_category'], $workorder['description'], 'view');
					}
			}
			$this->showWorkOrder($message);
		}
		else {
			$this->showNoPermission();
		}
	}
	
	private function showNoPermission () {
		$system = System::getInstance();
		
		audit_db ($system->getConfig('id_user'), $REMOTE_ADDR, "ACL Violation",
			"Trying to access to workorder section");
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
