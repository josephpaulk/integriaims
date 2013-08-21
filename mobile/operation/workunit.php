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

class Workunit {
	
	private $id;
	private $id_task;
	private $id_incident;
	private $date;
	private $duration;
	private $description;
	private $operation;
	
	private $acl = 'PR';
	private $permission = false;
	
	function __construct () {
		$system = System::getInstance();
		
		$this->id = (int) $system->getRequest('id', -1);
		$this->id_task = $system->getRequest('id_task', false);
		$this->id_incident = (int) $system->getRequest('id_incident', -1);
		$this->date = (string) $system->getRequest('date', date ("Y-m-d"));
		$this->duration = (float) $system->getRequest('duration', $system->getConfig('pwu_defaultime'));
		$this->description = (string) $system->getRequest('description', "");
		// insert, update, delete, view or ""
		$this->operation = (string) $system->getRequest('operation', "");
		
		// ACL
		$this->permission = $this->checkPermission($system->getConfig('id_user'), $this->acl,
											$this->operation, $this->id, $this->id_task,
											$this->id_incident);
		//$this->permission = false;
	}
	
	public function checkPermission ($id_user, $acl = 'PR', $operation = '', $id = -1, $id_task = -1, $id_incident = -1) {
		$system = System::getInstance();
		
		$permission = false;
		if (dame_admin($id_user)) {
			$permission = true;
			
		} else {
			// Section access
			if ($system->checkACL($acl)) {
				// WU for task
				if ($id_task !== false && $id_task > 0) {
					if ( include_once ($system->getConfig('homedir')."/include/functions_projects.php") ) {
						$task_access = get_project_access ($id_user, 0, $id_task, false, true);
						// Task access
						if ($task_access["write"]) {
							// If the WU exists, should belong to the user
							if ($operation != "" && $operation != "insert") {
								$user_wu = get_db_value("id_user", "tworkunit", "id", $id);
								if ($user_wu == $id_user) {
									$permission = true;
								}
							} else {
								$permission = true;
							}
						}
					}
				// WU for incident
				} elseif ($id_incident > 0) {
					// Incident access
					if ($system->checkACL('IW') || $system->checkACL('IM')) {
						// If the WU exists, should belong to the user
						if ($operation != "" && $operation != "insert") {
							$user_wu = get_db_value("id_user", "tworkunit", "id", $id);
							if ($user_wu == $id_user) {
								$permission = true;
							}
						} else {
							$permission = true;
						}
					}
				} else {
					$permission = true;
				}
			}
		}
		// With this operations, the WU should have id
		if ( ($operation == "view" || $operation == "update" || $operation == "delete")
				&& $id < 0) {
			$permission = false;
		}
		
		return $permission;
	}
	
	public function setId ($id) {
		$this->id = $id;
	}
	
	private function setValues ($id, $id_task, $id_incident, $date, $duration, $description, $operation) {
		$this->id = $id;
		$this->id_task = $id_task;
		$this->id_incident = $id_incident;
		$this->date = $date;
		$this->duration = $duration;
		$this->description = $description;
		$this->operation = $operation;
	}
	
	public function insertWorkUnit ($id_user, $date, $duration = 4, $description = "", $id_task = false, $id_incident = -1) {
		$system = System::getInstance();
		
		$sql = sprintf ("INSERT INTO tworkunit 
				(timestamp, duration, id_user, description) 
				VALUES ('%s', %.2f, '%s', '%s')",
				$date, $duration, $id_user, $description);
		$id_workunit = process_sql ($sql, "insert_id");
		
		if ($id_workunit) {
			if ($id_task !== false && $id_task !== 0) {
				$sql = sprintf ("INSERT INTO tworkunit_task 
						(id_task, id_workunit) VALUES (%d, %d)",
						$id_task, $id_workunit);
				$result = process_sql ($sql, 'insert_id');
				if ($result) {
					include_once ($system->getConfig('homedir')."/include/functions_tasks.php");
					set_task_completion ($this->id_task);
					return $id_workunit;
				}
			} elseif ($id_incident > 0) {
				$sql = sprintf ("INSERT INTO tworkunit_incident 
						(id_incident, id_workunit) VALUES (%d, %d)",
						$id_incident, $id_workunit);
				$result = process_sql ($sql, 'insert_id');
				if ($result) {
					return $id_workunit;
				}
			} else {
				return $id_workunit;
			}
		}
		
		return false;
	}
	
	public function updateWorkUnit ($id, $id_user, $date, $duration = 4, $description = "", $id_task = false, $id_incident = -1) {
		$system = System::getInstance();
		
		$sql = sprintf ("UPDATE tworkunit
			SET timestamp = '%s', duration = %.2f, description = '%s',
			id_user = '%s' WHERE id = %d",
			$date, $duration, $description,
			$id_user, $id);
		$result = process_sql ($sql);
		
		$old_id_task = get_db_value("id_task", "tworkunit_task", "id_workunit", $id);
		if ($old_id_task !== false && $old_id_task != $id_task) {
			process_sql ("DELETE FROM tworkunit_task WHERE id_workunit = ".$id);
		}
		$old_id_incident = get_db_value("id_incident", "tworkunit_incident", "id_workunit", $id);
		if ($old_id_incident && $old_id_incident != $id_incident) {
			process_sql ("DELETE FROM tworkunit_incident WHERE id_workunit = ".$id);
		}
		
		if ($id_task !== false && $id_task !== 0) {
			$sql = sprintf ("INSERT INTO tworkunit_task 
					(id_task, id_workunit) VALUES (%d, %d)",
					$id_task, $id);
			$result = process_sql ($sql, 'insert_id');
			if ($result) {
				include_once ($system->getConfig('homedir')."/include/functions_tasks.php");
				set_task_completion ($id_task);
				return true;
			}
		} elseif ($id_incident > 0) {
			$sql = sprintf ("INSERT INTO tworkunit_incident 
					(id_incident, id_workunit) VALUES (%d, %d)",
					$id_incident, $id);
			$result = process_sql ($sql, 'insert_id');
			if ($result) {
				return true;
			}
		} else {
			return true;
		}
		
		return false;
	}
	
	public function deleteWorkUnit ($id) {
		
		$result = process_sql ("DELETE FROM tworkunit WHERE id = ".$id);
		if ($result) {
			$id_task = get_db_value("id_task", "tworkunit_task", "id_workunit", $id);
			$id_incident = get_db_value("id_incident", "tworkunit_incident", "id_workunit", $id);
			if ($id_task) {
				$result = process_sql ("DELETE FROM tworkunit_task WHERE id_workunit = ".$id);
				if ($result) {
					$system = System::getInstance();
					include_once ($system->getConfig('homedir')."/include/functions_tasks.php");
					set_task_completion ($this->id_task);
					return true;
				}
			} elseif ($id_incident) {
				$result = process_sql ("DELETE FROM tworkunit_incident WHERE id_workunit = ".$id);
				if ($result) {
					return true;
				}
			} else {
				return true;
			}
		}
		
		return false;
	}
	
	public function showWorkUnit ($message = "") {
		$system = System::getInstance();
		$ui = Ui::getInstance();
		
		$ui->createPage();
		
		if ($this->id_incident > 0) {
			$back_href = 'index.php?page=incidents&id_incident='.$this->id_incident;
		} else {
			$back_href = 'index.php';
		}
		$ui->createDefaultHeader(__("Workunit"),
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
			
			$ui->beginForm("index.php?page=workunit", "post", "form_wu");
				// Date
				$options = array(
					'name' => 'date',
					'label' => __('Date'),
					'value' => $this->date,
					'placeholder' => __('Date')
					);
				$ui->formAddInputDate($options);
				// Hours
				$options = array(
					'name' => 'duration',
					'label' => __('Hours'),
					'type' => 'number',
					'step' => 'any',
					'min' => '0.01',
					'value' => $this->duration,
					'placeholder' => __('Hours')
					);
				$ui->formAddInput($options);
				
				// Tasks combo or hidden id_incident
				if ($this->id_incident < 0) {
					
					$sql = "SELECT ttask.id, tproject.name, ttask.name
							FROM ttask, trole_people_task, tproject
							WHERE ttask.id_project = tproject.id
								AND tproject.disabled = 0
								AND ttask.id = trole_people_task.id_task
								AND trole_people_task.id_user = '".$system->getConfig('id_user')."'
							ORDER BY tproject.name, ttask.name";
					if (dame_admin ($system->getConfig('id_user'))) {
						$sql = "SELECT ttask.id, tproject.name, ttask.name 
								FROM ttask, trole_people_task, tproject
								WHERE ttask.id_project = tproject.id
									AND tproject.disabled = 0
								ORDER BY tproject.name, ttask.name";
					}
					$tasks = get_db_all_rows_sql ($sql);
					
					$values[-3] = "(*) ".__('Not justified');
					$values[-2] = "(*) ".__('Not working for disease');
					$values[-1] = "(*) ".__('Vacations');
					$values[0] =  __('N/A');
					if ($tasks) {
						foreach ($tasks as $task){
							$values[$task[0]] = array('optgroup' => $task[1], 'name' => $task[2]);
						}
					}
					$selected = ($this->id_task === false) ? 0 : $this->id_task;
					$options = array(
						'name' => 'id_task',
						'title' => __('Task'),
						'label' => __('Task'),
						'items' => $values,
						'selected' => $selected
						);
					$ui->formAddSelectBox($options);
				} else {
					$options = array(
						'type' => 'hidden',
						'name' => 'id_incident',
						'value' => $this->id_incident
						);
					$ui->formAddInput($options);
				}
				// Description
				$options = array(
						'name' => 'description',
						'label' => __('Description'),
						'value' => $this->description
						);
				$ui->formAddHtml($ui->getTextarea($options));
				// Hidden operation (insert or update+id)
				if ($this->id < 0) {
					$options = array(
						'type' => 'hidden',
						'name' => 'operation',
						'value' => 'insert'
						);
					$ui->formAddInput($options);
				} else {
					$options = array(
						'type' => 'hidden',
						'name' => 'operation',
						'value' => 'update'
						);
					$ui->formAddInput($options);
					$options = array(
						'type' => 'hidden',
						'name' => 'id',
						'value' => $this->id
						);
					$ui->formAddInput($options);
				}
			$ui->endForm();
		$ui->endContent();
		// Foooter buttons
		// Add
		if ($this->id < 0) {
			$button_add = "<a onClick=\"$('#form_wu').submit();\" data-role='button' data-icon='plus'>"
							.__('Add')."</a>\n";
		} else {
			$button_add = "<a onClick=\"$('#form_wu').submit();\" data-role='button' data-icon='refresh'>"
							.__('Update')."</a>\n";
		}
		// Delete
		if ($this->id > 0) {
			$button_delete = "<a href='index.php?page=workunit&operation=delete&id=".$this->id."' 
									data-role='button' data-icon='delete'>".__('Delete')."</a>\n";
		}
		$ui->createFooter("<div data-type='horizontal' data-role='controlgroup'>$button_add"."$button_delete</div>");
		$ui->showFooter();
		$ui->showPage();
	}
	
	public function show () {
		$system = System::getInstance();
		
		if ($this->permission) {
			$message = "";
			switch ($this->operation) {
				case 'insert':
					$result = $this->insertWorkUnit($system->getConfig('id_user'),
													$this->date, $this->duration,
													$this->description, $this->id_task,
													$this->id_incident);
					if ($result) {
						$this->id = $result;
						$message = "<h2 class='suc'>".__('Successfully created')."</h2>";
					} else {
						$message = "<h2 class='error'>".__('An error ocurred while creating the workorder')."</h2>";
					}
					break;
				case 'update':
					$result = $this->updateWorkUnit($this->id, $system->getConfig('id_user'),
													$this->date, $this->duration,
													$this->description, $this->id_task,
													$this->id_incident);
					if ($result) {
						$message = "<h2 class='suc'>".__('Successfully updated')."</h2>";
					} else {
						$message = "<h2 class='error'>".__('An error ocurred while updating the workorder')."</h2>";
					}
					break;
				case 'delete':
					$result = $this->deleteWorkUnit($this->id);
					if ($result) {
						$this->id = -1;
						$message = "<h2 class='suc'>".__('Successfully deleted')."</h2>";
					} else {
						$message = "<h2 class='error'>".__('An error ocurred while deleting the workunit')."</h2>";
					}
					break;
				case 'view':
					$workunit = get_db_row ("tworkorder", "id", $this->id);
					$id_task = get_db_value ("id_task", "tworkorder_task", "id_workorder", $this->id);
					$id_incident = get_db_value ("id_incident", "tworkorder_incident", "id_workorder", $this->id);
					$this->setValues ($this->id, $id_task, $id_incident, $workunit['timestamp'],
							$workunit['duration'], $workunit['description'], 'view');
					break;
				default:
					if ($this->id > 0) {
						$workunit = get_db_row ("tworkorder", "id", $this->id);
						$id_task = get_db_value ("id_task", "tworkorder_task", "id_workorder", $this->id);
						$id_incident = get_db_value ("id_incident", "tworkorder_incident", "id_workorder", $this->id);
						$this->setValues ($this->id, $id_task, $id_incident, $workunit['timestamp'],
								$workunit['duration'], $workunit['description'], 'view');
					}
			}
			$this->showWorkUnit($message);
		}
		else {
			switch ($this->operation) {
				case 'insert':
					$error['title_text'] = __('You can\'t insert this workunit');
					$error['content_text'] = __('You have done an operation that
						surpass your permissions. Is possible that you can\'t add a
						workunit to this task. Please contact to system administrator 
						if you need assistance. <br><br>Please know that all attempts 
						to access this page are recorded in security logs of Integria 
						System Database');
					break;
				case 'update':
					$error['title_text'] = __('You can\'t update this workunit');
					$error['content_text'] = __('You have done an operation that
						surpass your permissions. Is possible that you can\'t add a
						workunit to this task. Please contact to system administrator 
						if you need assistance. <br><br>Please know that all attempts 
						to access this page are recorded in security logs of Integria 
						System Database');
					break;
				case 'delete':
					$error['title_text'] = __('You can\'t delete this workunit');
					$error['content_text'] = __('You have done an operation that surpass
						your permissions. Please contact to system administrator 
						if you need assistance. <br><br>Please know that all attempts 
						to access this page are recorded in security logs of Integria 
						System Database');
					break;
			}
			$this->showNoPermission($error);
		}
	}
	
	public function showNoPermission ($error = false) {
		$system = System::getInstance();
		
		audit_db ($system->getConfig('id_user'), $REMOTE_ADDR, "ACL Violation",
			"Trying to access to workunit section");
		if (! $error) {
			$error['title_text'] = __('You don\'t have access to this page');
			$error['content_text'] = __('Access to this page is restricted to 
				authorized users only, please contact to system administrator 
				if you need assistance. <br><br>Please know that all attempts 
				to access this page are recorded in security logs of Integria 
				System Database');
		}
		$home = new Home();
		$home->show($error);
	}
	
	public function ajax ($parameter2 = false) {
		// Fill me in the future
	}
	
}

?>
