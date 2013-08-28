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
	
	private $id_incident;
	private $title;
	private $description;
	private $group_id;
	
	private $id_creator;
	private $status;
	private $priority;
	private $resolution;
	private $id_task;
	private $sla_disabled;
	private $id_incident_type;
	private $email_copy;
	private $email_notify;
	private $id_parent;
	private $epilog;
	
	private $operation;
	private $tab;
	
	private $acl = 'IR';
	private $permission = false;
	
	function __construct () {
		$system = System::getInstance();
		
		$this->id_incident = (int) $system->getRequest('id_incident', 0);
		$this->title = (string) $system->getRequest('title', "");
		$this->description = (string) $system->getRequest('description', "");
		$this->group_id = (int) $system->getRequest('group_id', -1);
		if ($this->group_id == -1) {
			// GET THE FIRST KNOWN GROUP OF THE USER
			$user_groups = get_user_groups($system->getConfig('id_user'));
			$group_id = reset(array_keys($user_groups));
			$this->group_id = $group_id;
			unset($group_id);
		}
		
		$this->id_creator = (string) $system->getRequest('id_creator', $system->getConfig('id_user'));
		$this->status = (int) $system->getRequest('status', 1);
		$this->priority = (int) $system->getRequest('priority', 2);
		$this->resolution = (int) $system->getRequest('resolution', 0);
		$this->id_task = (int) $system->getRequest('id_task', 0);
		$this->sla_disabled = (int) $system->getRequest('sla_disabled', 0);
		$this->id_incident_type = (int) $system->getRequest('id_incident_type', 0);
		$this->email_copy = (string) $system->getRequest('email_copy', "");
		$this->email_notify = (int) $system->getRequest('email_notify', -1);
		if ($this->email_notify == -1) {
			$this->email_notify = (int) get_db_value ("forced_email", "tgrupo", "id_grupo", $this->group_id);
		}
		$this->id_parent = (int) $system->getRequest('id_parent', 0);
		$this->epilog = (string) $system->getRequest('epilog', "");
		
		// insert, update, delete, view or ""
		$this->operation = (string) $system->getRequest('operation', "");
		// view, files, workorders or ""
		$this->tab = (string) $system->getRequest('tab', "view");
		
		// ACL
		if ($system->checkACL($this->acl)) {
			$this->permission = true;
		}
		
	}
	
	public function setId ($id_incident) {
		$this->id_incident = $id_incident;
	}
	
	public function insertIncident ($title, $description, $group_id, $id_creator = "", $status = 1, $priority = 2, $resolution = 0, $id_task = 0, $sla_disabled = 0, $id_incident_type = 0, $email_copy = "", $email_notify = -1, $id_parent = 0, $epilog = "") {
		$system = System::getInstance();
		
		if ($id_creator == "") {
			$id_creator = $system->getConfig('id_user');
		}
		if ($email_notify == -1) {
			$email_notify = get_db_value ("forced_email", "tgrupo", "id_grupo", $this->group_id);
		}
		if ($id_parent == 0) {
			$idParentValue = 'NULL';
		}
		else {
			$idParentValue = sprintf ('%d', $id_parent);
		}
		
		$user_responsible = get_group_default_user ($group_id);
		$id_user_responsible = $user_responsible['id_usuario'];
		
		$id_inventory = get_group_default_inventory($group_id, true);
		
		// DONT use MySQL NOW() or UNIXTIME_NOW() because 
		// Integria can override localtime zone by a user-specified timezone.
		$timestamp = print_mysql_timestamp();
		
		$sql = "INSERT INTO tincidencia
				(inicio, actualizacion, titulo, descripcion, id_usuario,
				estado, prioridad, id_grupo, id_creator, notify_email, id_task,
				resolution, id_incident_type, sla_disabled, email_copy, epilog)
				VALUES ('$timestamp', '$timestamp', '$title', '$description',
				'$id_user_responsible', $status, $priority, $group_id, '$id_creator',
				$email_notify, $id_task, $resolution, $id_incident_type, $sla_disabled,
				'$email_copy', '$epilog')";
				
		$id_incident = process_sql ($sql, 'insert_id');
		
		if ($id_incident !== false) {
			
			if ( include_once ($system->getConfig('homedir')."/include/functions_incidents.php") ) {
				/* Update inventory objects in incident */
				update_incident_inventories ($id_incident, array($id_inventory));
			}
			
			audit_db ($config["id_user"], $config["REMOTE_ADDR"],
				"Incident created",
				"User ".$config['id_user']." created incident #".$id_incident);
			
			incident_tracking ($id_incident, INCIDENT_CREATED);

			// Email notify to all people involved in this incident
			if ($email_notify) {
				mail_incident ($id_incident, $usuario, "", 0, 1);
			}
			
			return $id_incident;
		}
	}
	
	public function updateIncident () {
		
	}
	
	public function deleteIncident ($id_incident) {
		
		$error = false;
		
		// tincident_contact_reporters
		$sql_delete = "DELETE FROM tincident_contact_reporters
					   WHERE id_incident = $id_incident";
		$res = process_sql ($sql_delete);
		
		if ($res === false) {
			$error = true;
		}
		
		// tincident_field_data
		$res = $sql_delete = "DELETE FROM tincident_field_data
					   WHERE id_incident = $id_incident";
		$res = process_sql ($sql_delete);
		
		if ($res === false) {
			$error = true;
		}
		
		// tincident_inventory
		$sql_delete = "DELETE FROM tincident_inventory
					   WHERE id_incident = $id_incident";
		$res = process_sql ($sql_delete);
		
		if ($res === false) {
			$error = true;
		}
		
		// tincident_sla_graph
		$sql_delete = "DELETE FROM tincident_sla_graph
					   WHERE id_incident = $id_incident";
		$res = process_sql ($sql_delete);
		
		if ($res === false) {
			$error = true;
		}
		
		// tincident_stats
		$sql_delete = "DELETE FROM tincident_stats
					   WHERE id_incident = $id_incident";
		$res = process_sql ($sql_delete);
		
		if ($res === false) {
			$error = true;
		}
		
		// tincident_track
		$sql_delete = "DELETE FROM tincident_track
					   WHERE id_incident = $id_incident";
		$res = process_sql ($sql_delete);
		
		if ($res === false) {
			$error = true;
		}
		
		// tworkunit
		$sql_delete = "DELETE FROM tworkunit
					   WHERE id = ANY(SELECT id_workunit
									  FROM tworkunit_incident
									  WHERE id_incident = $id_incident)";
		$res = process_sql ($sql_delete);
		
		if ($res === false) {
			$error = true;
		}
		
		// tattachment
		$sql_delete = "DELETE FROM tattachment
					   WHERE id_incidencia = $id_incident";
		$res = process_sql ($sql_delete);
		
		if ($res === false) {
			$error = true;
		}
		
		if (! $error) {
			// tincidencia
			$sql_delete = "DELETE FROM tincidencia
						   WHERE id_incidencia = ".$incident["id_incidencia"];
			process_sql ($sql_delete);
		}
		
		return !$error;
	}
	
	public function getIncidentSimpleForm ($action = "index.php?page=incident", $method = "POST") {
		$ui = Ui::getInstance();
		
		$options = array (
			'id' => 'form-incident',
			'action' => $action,
			'method' => $method
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
		// Description
		$options = array(
				'name' => 'description',
				'label' => __('Description'),
				'value' => $this->description
				);
		$ui->formAddHtml($ui->getTextarea($options));
		// Hidden operation (insert or update+id)
		if ($this->id_incident <= 0) {
			$options = array(
				'type' => 'hidden',
				'name' => 'operation',
				'value' => 'insert_incident'
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
				'value' => 'update_incident'
				);
			$ui->formAddInput($options);
			$options = array(
				'type' => 'hidden',
				'name' => 'id',
				'value' => $this->id_incident
				);
			$ui->formAddInput($options);
			// Submit button
			$options = array(
					'text' => __('Update'),
					'data-icon' => 'refresh'
					);
			$ui->formAddSubmitButton($options);
		}
		
		return $ui->getEndForm();
	}
	
	private function showIncidentSimpleForm ($message = "") {
		$ui = Ui::getInstance();
		
		$ui->createPage();
		
		$back_href = "index.php?page=incidents";
		$ui->createDefaultHeader(__("New incident"),
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
			// Form
			$ui->contentAddHtml($this->getIncidentSimpleForm());
			
		$ui->endContent();
		// Foooter buttons
		// Add
		if ($this->id_incident <= 0) {
			$button_add = "<a onClick=\"$('#form-incident').submit();\" data-role='button' data-icon='plus'>"
							.__('Add')."</a>\n";
		} else {
			$button_add = "<a onClick=\"$('#form-incident').submit();\" data-role='button' data-icon='refresh'>"
							.__('Update')."</a>\n";
		}
		// Delete
		if ($this->id_incident > 0) {
			$button_delete = "<a href='index.php?page=incidents&operation=delete_incident&id_incident=".$this->id_incident."'
									data-role='button' data-icon='delete'>".__('Delete')."</a>\n";
		}
		$ui->createFooter("<div data-type='horizontal' data-role='controlgroup'>$button_add"."$button_delete</div>");
		$ui->showFooter();
		$ui->showPage();
	}
	
	private function getIncidentDetail () {
		$system = System::getInstance();
		$ui = Ui::getInstance();
		
		$incident = get_db_row ("tincidencia", "id_incidencia", $this->id_incident);
		if (! $incident) {
			$ui->contentAddHtml("<h2 class=\"error\">".__('Incident not found')."</h2>");
		} else {
			if ( include_once ($system->getConfig('homedir')."/include/functions_incidents.php") ) {
				$resolution = incidents_get_incident_resolution_text($incident['id_incidencia']);
				$priority = incidents_get_incident_priority_text($incident['id_incidencia']);
				//$priority_image = print_priority_flag_image ($incident['prioridad'], true);
				$group = incidents_get_incident_group_text($incident['id_incidencia']);
				$status = incidents_get_incident_status_text($incident['id_incidencia']);
				$type = incidents_get_incident_type_text($incident['id_incidencia']);
				$description = $incident['descripcion'];
			} else {
				$resolution = $incident['resolution'];
				$priority = $incident['prioridad'];
				$group = $incident['id_grupo'];
				$status = $incident['estado'];
				$type = $incident['id_incident_type'];
				$description = $incident['descripcion'];
			}
			$html = "<h3>".__('Status')."</h3>";
			$html .= "<p>".$status."</p>";
			$html .= "<h3>".__('Group')."</h3>";
			$html .= "<p>".$group."</p>";
			$html .= "<h3>".__('Priority')."</h3>";
			$html .= "<p>".$priority."</p>";
			$html .= "<h3>".__('Resolution')."</h3>";
			$html .= "<p>".$resolution."</p>";
			$html .= "<h3>".__('Type')."</h3>";
			$html .= "<p>".$type."</p>";
			if ($description != "") { 
				$html .= "<h3>".__('Description')."</h3>";
				$html .= "<p>".$description."</p>";
			}
		}
		
		return $html;
	}
	
	private function getFilesQuery ($columns = "*", $order_by = "timestamp, id_usuario, filename") {
		$system = System::getInstance();
		
		$sql = "SELECT $columns
				FROM tattachment
				WHERE id_incidencia = '".$this->id_incident."'";
		if ($order_by != "") {
			$sql .= " ORDER BY $order_by";
		}
		
		return $sql;
	}
	
	private function getCountFiles () {
		$sql = $this->getFilesQuery("COUNT(id_attachment)", "");
		$count = get_db_sql($sql);
		
		return $count;
	}
	
	private function getFilesList ($href = "", $delete_button = true, $delete_href = "") {
		$system = System::getInstance();
		$ui = Ui::getInstance();
		
		if ($href == "") {
			$href = "index.php?page=incident&tab=file&id_incident=".$this->id_incident;
		}
		
		$html = "<ul class='ui-itemlistview' data-role='listview' data-count-theme='e'>";
		if ($this->getCountFiles() > 0) {
			$sql = $this->getFilesQuery();
			$new = true;
			while ( $file = get_db_all_row_by_steps_sql($new, $result_query, $sql) ) {
				$new = false;
				$html .= "<li>";
				$html .= "<a href='../operation/incidents/incident_download_file.php?id_attachment=".$file['id_attachment']."' class='ui-link-inherit' target='_blank'>";
					$html .= "<h3 class='ui-li-heading'><img src='../images/attach.png'>&nbsp;".$file['filename']."</img></h3>";
					$html .= "<p class='ui-li-desc'>".$file['description']."</p>";
					$html .= "<span class=\"ui-li-aside\">".round($file['size']/1024,2)."&nbsp;KB</span>";
				$html .= "</a>";
				
				if ($delete_button) {
					if ($delete_href == "") {
						$delete_href = "index.php?page=incident&tab=files&operation=delete_file";
						$delete_href .= "&id_incident=".$this->id_incident;
					}
					$options = array(
						'popup_id' => 'delete_popup_'.$file['id_attachment'],
						'delete_href' => $delete_href."&id_file=".$file['id_attachment']
						);
					$html .= $ui->getDeletePopupHTML($options);
					$html .= "<a data-icon=\"delete\" data-rel=\"popup\" href=\"#delete_popup_".$file['id_attachment']."\"></a>";
				}
				$html .= "</li>";
			}
		} else {
			$html .= "<li>";
			$html .= "<h3 class='error'>".__('There is no files')."</h3>";
			$html .= "</li>";
		}
		$html .= "</ul>";
		
		return $html;
	}
	
	private function getFileForm ($action = "index.php?page=incident&tab=file", $method = "POST") {
		$system = System::getInstance();
		$ui = Ui::getInstance();
		
		$options = array (
			'id' => 'form-incident_file',
			'action' => $action,
			'method' => $method,
			'enctype' => 'multipart/form-data',
			//'size' => '40',
			'data-ajax' => 'false'
			);
		$ui->beginForm($options);
			// Hidden id_incident
			$options = array(
				'type' => 'hidden',
				'name' => 'id_incident',
				'value' => $this->id_incident
				);
			$ui->formAddInput($options);
			// File
			$options = array(
				'type' => 'file',
				'name' => 'file',
				'label' => __('File')
				);
			$ui->formAddInput($options);
			// Description
			$options = array(
					'name' => 'description_file',
					'label' => __('Description'),
					'value' => $this->description_file
					);
			$ui->formAddHtml($ui->getTextarea($options));
			// Hidden operation (insert or update+id)
			if ($this->id_file < 0 || !isset($this->id_file)) {
				$options = array(
					'type' => 'hidden',
					'name' => 'operation',
					'value' => 'insert_file'
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
					'value' => 'update_file'
					);
				$ui->formAddInput($options);
				$options = array(
					'type' => 'hidden',
					'name' => 'id_file',
					'value' => ''
					);
				$ui->formAddInput($options);
				// Submit button
				$options = array(
					'text' => __('Update'),
					'data-icon' => 'refresh'
					);
				$ui->formAddSubmitButton($options);
			}
			
		return $ui->getEndForm();
	}
	
	private function showIncident ($tab = "view", $message = "") {
		$system = System::getInstance();
		$ui = Ui::getInstance();
		
		$ui->createPage();
		
		// Header options
		$header_title = __("Incident")."&nbsp;#".$this->id_incident;
		$left_href = "index.php?page=incidents";
		$header_left_button = $ui->createHeaderButton(
				array('icon' => 'back',
					'pos' => 'left',
					'text' => __('Back'),
					'href' => $left_href
					)
			);
		$right_href = "index.php?page=home";
		$header_right_button = $ui->createHeaderButton(
				array('icon' => 'home',
					'pos' => 'right',
					'text' => __('Home'),
					'href' => $right_href
					)
			);
		
		// Content
		$selected_tab_detail = "";
		$selected_tab_workunit = "";
		$selected_tab_file = "";
		
		$ui->beginContent();
			// Message popup
			if ($message != "") {
				$options = array(
					'popup_id' => 'message_popup',
					'popup_content' => $message
					);
				$ui->addPopup($options);
				$ui->contentAddHtml("<script type=\"text/javascript\">
										$(document).on('pageshow', function() {
											$(\"#message_popup\").popup(\"open\");
										});
									</script>");
			}
			switch ($tab) {
				case 'detail':
					$selected_tab_detail = "class=\"ui-btn-active ui-state-persist\"";
					$ui->contentAddHtml($this->getIncidentDetail());
					
					// Header options
					$right_href = "index.php?page=home"; // Edit in the future
					$header_right_button = $ui->createHeaderButton(
							array('icon' => 'home',
								'pos' => 'right',
								'text' => __('Home'),
								'href' => $right_href
								)
						);
					break;
				case 'edit':
					break;
				case 'workunits':
					$selected_tab_workunit = "class=\"ui-btn-active ui-state-persist\"";
					$workunits = new Workunits();
					$href = "index.php?page=incident&tab=workunit&id_incident=".$this->id_incident;
					$delete_button = false;
					$delete_href = "";
					
					// Workunits listing
					$html = $workunits->getWorkUnitsList($href, $delete_button, $delete_href);
					$ui->contentAddHtml($html);
					if ($workunits->getCountWorkUnits() > $system->getPageSize()) {
						$ui->contentAddHtml('<div style="text-align:center;" id="loading_rows">
												<img src="../images/spinner.gif">&nbsp;'
													. __('Loading...') .
												'</img>
											</div>');
						
						$workunits->addWorkUnitsLoader($href);
					}
					unset($workunits);
					
					// Header options
					$right_href = "index.php?page=incident&tab=workunit&id_incident=".$this->id_incident;
					$header_right_button = $ui->createHeaderButton(
							array('icon' => 'add',
								'pos' => 'right',
								'text' => __('New'),
								'href' => $right_href
								)
						);
					break;
				case 'workunit':
					$selected_tab_workunit = "class=\"ui-btn-active ui-state-persist\"";
					$workunit = new Workunit();
					$action = "index.php?page=incident&tab=workunit";
					$ui->contentAddHtml($workunit->getWorkUnitForm($action, "POST"));
					unset($workunit);
					
					// Header options
					if ($id_workunit = $system->getRequest('id_workunit', false)) {
						$header_title = __("Workunit")."&nbsp;#".$id_workunit;
					} else {
						$header_title = __("Workunit");
					}
					$right_href = "index.php?page=incident&tab=workunits&id_incident=".$this->id_incident;
					$header_right_button = $ui->createHeaderButton(
							array('icon' => 'grid',
								'pos' => 'right',
								'text' => __('List'),
								'href' => $right_href
								)
						);
					break;
				case 'files':
					$selected_tab_file = "class=\"ui-btn-active ui-state-persist\"";
					$ui->contentAddHtml($this->getFilesList());
					
					// Header options
					$right_href = "index.php?page=incident&tab=file&id_incident=".$this->id_incident;
					$header_right_button = $ui->createHeaderButton(
							array('icon' => 'add',
								'pos' => 'right',
								'text' => __('New'),
								'href' => $right_href
								)
						);
					break;
				case 'file':
					$selected_tab_file = "class=\"ui-btn-active ui-state-persist\"";
					$ui->contentAddHtml($this->getFileForm());
					
					// Header options
					$header_title = __("File");
					$right_href = "index.php?page=incident&tab=files&id_incident=".$this->id_incident;
					$header_right_button = $ui->createHeaderButton(
							array('icon' => 'grid',
								'pos' => 'right',
								'text' => __('List'),
								'href' => $right_href
								)
						);
					break;
				default:
					$tab = 'detail';
					$selected_tab_detail = "class=\"ui-btn-active ui-state-persist\"";
					$ui->contentAddHtml($this->getIncidentDetail());
			}
		$ui->endContent();
		
		// Header
		$ui->createHeader($header_title, $header_left_button, $header_right_button);
		// Navigation bar
		$tab_detail = "<a href='index.php?page=incident&tab=view&id_incident=".$this->id_incident."' $selected_tab_detail data-role='button' data-icon='info'>"
			.__('Info')."</a>\n";
		$tab_workunit = "<a href='index.php?page=incident&tab=workunits&id_incident=".$this->id_incident."' $selected_tab_workunit data-role='button' data-icon='star'>"
			.__('Workunit')."</a>\n";
		$tab_file = "<a href='index.php?page=incident&tab=files&id_incident=".$this->id_incident."' $selected_tab_file data-role='button' data-icon='plus'>"
			.__('Files')."</a>\n";
		$buttons = array ($tab_detail, $tab_workunit, $tab_file);
		$ui->addNavBar($buttons);
		$ui->showPage();
	}
	
	public function show () {
		if ($this->permission) {
			$system = System::getInstance();
			
			$message = "";
			switch ($this->operation) {
				case 'insert_incident':
					$result = $this->insertIncident($this->title, $this->description, $this->group_id,
													$this->id_creator, $this->status, $this->priority,
													$this->resolution, $this->id_task, $this->sla_disabled,
													$this->id_incident_type, $this->email_copy,
													$this->email_notify, $this->id_parent, $this->epilog);
					if ($result) {
						$this->id_incident = $result;
						$message = "<h2 class='suc'>".__('Successfully created')."</h2>";
					} else {
						$message = "<h2 class='error'>".__('An error ocurred while creating the incident')."</h2>";
					}
					$incidents = new Incidents();
					$incidents->show($message);
					//$this->showIncidentSimpleForm($message);
					break;
				case 'insert_workunit':
					if ($this->id_incident > 0) {
						$date_workunit = (string) $system->getRequest('date_workunit', date ("Y-m-d"));
						$duration_workunit = (float) $system->getRequest('duration_workunit', $system->getConfig('iwu_defaultime'));
						$description_workunit = (string) $system->getRequest('description_workunit', "");
						$workunit = new Workunit();
						$result = $workunit->insertWorkUnit($system->getConfig('id_user'), $date_workunit,
															$duration_workunit, $description_workunit, false, $this->id_incident);
						unset($workunit);
						if ($result) {
							$message = "<h2 class='suc'>".__('Successfully created')."</h2>";
						} else {
							$message = "<h2 class='error'>".__('An error ocurred while adding the workunit')."</h2>";
						}
						$this->showIncident($this->tab, $message);
					}
					break;
				case 'insert_file':
					if ($this->id_incident > 0) {
						
						switch ($_FILES['file']['error']) {
							case UPLOAD_ERR_OK:
								if ( include_once ($system->getConfig('homedir')."/include/functions_incidents.php") ) {
									include_once ($system->getConfig('homedir')."/include/functions_workunits.php");
									
									$filename = $_FILES['file']['name'];
									$filename = str_replace (" ", "_", $filename);
									$correct_file_path = sys_get_temp_dir()."/$filename";
									$file_tmp = $_FILES['file']['tmp_name'];
									if (rename($file_tmp, $correct_file_path)) {
										$file_path = $correct_file_path;
									} else {
										$file_path = $file_tmp;
									}
									$description_file = (string) $system->getRequest('description_file', '');
									
									$result = attach_incident_file ($this->id_incident, $file_path, $description_file);
									
									if (preg_match("/".__('File added')."/i", $result)) {
										$message = "<h2 class='suc'>".__('File added')."</h2>";
									} else {
										$message = "<h2 class='error'>".__('An error ocurred while uploading the file')."</h2>";
									}
								} else {
									$message = "<h2 class='error'>".__('Upload error')."</h2>";
								}
								break;
							case UPLOAD_ERR_INI_SIZE:
								$message = "<h2 class='error'>".__('The file exceeds the maximum size')."</h2>";
								break;
							case UPLOAD_ERR_FORM_SIZE:
								$message = "<h2 class='error'>".__('The file exceeds the maximum size')."</h2>";
								break;
							case UPLOAD_ERR_PARTIAL:
								$message = "<h2 class='error'>".__('The uploaded file was only partially uploaded')."</h2>";
								break;
							case UPLOAD_ERR_NO_FILE:
								$message = "<h2 class='error'>".__('No file was uploaded')."</h2>";
								break;
							case UPLOAD_ERR_NO_TMP_DIR:
								$message = "<h2 class='error'>".__('Missing a temporary folder')."</h2>";
								break;
							case UPLOAD_ERR_CANT_WRITE:
								$message = "<h2 class='error'>".__('Failed to write file to disk')."</h2>";
								break;
							case UPLOAD_ERR_EXTENSION:
								$message = "<h2 class='error'>".__('File upload stopped by extension')."</h2>";
								break;
							
							default:
								$message = "<h2 class='error'>".__('Unknown upload error')."</h2>";
								break;
						}
						$this->showIncident($this->tab, $message);
					}
					break;
				case 'update_incident':
					$this->showIncidentSimpleForm();
					break;
				case 'update_workunit':
					if ($this->id_incident > 0) {
						$id_workunit = (int) $system->getRequest('id_workunit', -1);
						$date_workunit = (string) $system->getRequest('date_workunit', date ("Y-m-d"));
						$duration_workunit = (float) $system->getRequest('duration_workunit', $system->getConfig('iwu_defaultime'));
						$description_workunit = (string) $system->getRequest('description_workunit', "");
						$workunit = new Workunit();
						$result = $workunit->updateWorkUnit($id_workunit, $system->getConfig('id_user'), $date_workunit,
															$duration_workunit, $description_workunit, false, $this->id_incident);
						unset($workunit);
						if ($result) {
							$message = "<h2 class='suc'>".__('Successfully updated')."</h2>";
						} else {
							$message = "<h2 class='error'>".__('An error ocurred while updating the workunit')."</h2>";
						}
						$this->showIncident($this->tab, $message);
					}
					break;
				case 'delete_incident':
					$result = $this->deleteIncident($this->id_incident);
					if ($result) {
						$this->id_incident = -1;
						$message = "<h2 class='suc'>".__('Successfully deleted')."</h2>";
					} else {
						$message = "<h2 class='error'>".__('An error ocurred while deleting the incident')."</h2>";
					}
					break;
					$this->showIncidentSimpleForm($message);
				case 'delete_file':
					$this->showIncident($this->tab, $message);
					break;
				default:
					if ($this->id_incident > 0) {
						$this->showIncident($this->tab);
					} else {
						$this->showIncidentSimpleForm();
					}
			}
		}
		else {
			$this->showNoPermission();
		}
	}
	
	private function showNoPermission ($error = false) {
		$system = System::getInstance();
		
		audit_db ($system->getConfig('id_user'), $REMOTE_ADDR, "ACL Violation",
			"Trying to access to incident section");
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
