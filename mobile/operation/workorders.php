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

class Workorders {
	
	private $id;
	private $offset;
	private $operation;
	private $filter_search;
	private $filter_owner;
	private $filter_creator;
	private $filter_status;
	
	private $acl = 'PR';
	private $permission = false;
	
	function __construct () {
		$system = System::getInstance();
		
		$this->id = (int) $system->getRequest('id', -1);
		$this->offset = (int) $system->getRequest('offset', 1);
		$this->operation = (string) $system->getRequest('operation', '');
		$this->filter_search = (string) $system->getRequest('filter_search', '');
		$this->filter_owner = (string) $system->getRequest('filter_owner', '');
		$this->filter_creator = (string) $system->getRequest('filter_creator', '');
		$this->filter_status = (int) $system->getRequest('filter_status', 0);
		
		if ($this->offset > $this->getNumPages()) {
			$this->offset = $this->getNumPages();
		}
		if ($this->offset < 1) {
			$this->offset = 1;
		}
		
		// ACL
		$this->permission = $this->checkPermission ($system->getConfig('id_user'), $this->acl);
	}
	
	public function checkPermission ($id_user, $acl = 'PR') {
		$system = System::getInstance();
		
		$permission = false;
		if (dame_admin($id_user)) {
			$permission = true;
			
		} else {
			// Section access
			if ($system->checkACL($acl)) {
				$permission = true;
			}
		}
		// With this operations, the WU should have id
		if ($operation == "delete" && $id < 0) {
			$permission = false;
		}
		
		return $permission;
	}
	
	private function getWorkOrdersQuery ($columns = "*", $order_by = "priority DESC, name", $limit = true) {
		$system = System::getInstance();
		
		$filter = "";
		if ($this->filter_search != '') {
			$filter .= " AND name LIKE '%".$this->filter_search."%' ";
		}
		if ($this->filter_owner != '') {
			$filter .= " AND assigned_user = '".$this->filter_owner."' ";
		}
		if  ($this->filter_creator != '') {
			$filter .= " AND created_by_user = '".$this->filter_creator."' ";
		}
		if ($this->filter_status >= 0) {
			$filter .= " AND progress = ".$this->filter_status;
		}
		if (dame_admin($system->getConfig('id_user'))) {
			$sql = "SELECT $columns
					FROM ttodo
					WHERE 1=1
					$filter";
		} else {
			$sql = "SELECT $columns
					FROM ttodo
					WHERE (assigned_user = '".$system->getConfig('id_user')."'
						OR created_by_user = '".$system->getConfig('id_user')."')
						$filter";
		}
		if ($order_by != "") {
			$sql .= " ORDER BY $order_by";
		}
		if ($limit) {
			$sql .= " LIMIT ".(int)(($this->offset -1) * $system->getPageSize()).", ".(int)$system->getPageSize();
		}
		
		return $sql;
	}
	
	public function getCountWorkorders () {
		$sql = $this->getWorkOrdersQuery("COUNT(id)", "", false);
		$count = get_db_sql($sql);
		
		return $count;
	}
	
	public function getNumPages () {
		$system = System::getInstance();
		
		$num_pages = ceil( $this->getCountWorkorders() / $system->getPageSize() );
		return $num_pages;
	}
	
	public function getWorkOrdersList () {
		$system = System::getInstance();
		
		$html = "<ul class='ui-itemlistview' data-role='listview'>";
		$sql = $this->getWorkOrdersQuery();
		$new = true;
		while ( $workorder = get_db_all_row_by_steps_sql($new, $result_query, $sql) ) {
			$new = false;
			$html .= "<li>";
			$html .= "<a href='index.php?page=workorder&operation=view&id=".$workorder['id']."' class='ui-link-inherit'>";
				$html .= print_priority_flag_image($workorder['priority'], true, "ui-li-icon", "../");
				$html .= "<h3 class='ui-li-heading'>".$workorder['name']."</h3>";
				$html .= "<p class='ui-li-desc'>".__('Owner').": ".$workorder['created_by_user'];
				$html .= "&nbsp;&nbsp;-&nbsp;&nbsp;".__('Creator').": ".$workorder['assigned_user']."</p>";
			$html .= "</a>";
			$html .= "<a data-icon='delete' href='index.php?page=workorders&operation=delete&id=".$workorder['id']."
											&filter_status=0&filter_owner=".$system->getConfig('id_user')."'></a>";
			$html .= "</li>";
		}
		$html .= "</ul>";
		
		return $html;
	}
	
	private function showWorkOrders ($message = "") {
		$ui = Ui::getInstance();
		
		$ui->createPage();
		
		$back_href = 'index.php?page=home';
		$ui->createDefaultHeader(__("Workorders"),
			$ui->createHeaderButton(
				array('icon' => 'back',
					'pos' => 'left',
					'text' => __('Back'),
					'href' => $back_href)));
		$ui->beginContent();
			// Message
			if ($message != "") {
				$ui->contentAddHtml($message);
			}
			$ui->contentBeginCollapsible(__('Filter'));
				$ui->beginForm("index.php?page=workorders", "post", "form_wo");
					// Filter owner
					$options = array(
						'name' => 'filter_search',
						'label' => __('Search'),
						'value' => $this->filter_search
						);
					$ui->formAddInputSearch($options);
					// Filter owner
					$options = array(
						'name' => 'filter_owner',
						'label' => __('Owner'),
						'value' => $this->filter_owner,
						'placeholder' => __('Owner')
						);
					$ui->formAddInputText($options);
					// Filter creator
					$options = array(
						'name' => 'filter_creator',
						'label' => __('Creator'),
						'value' => $this->filter_creator,
						'placeholder' => __('Creator')
						);
					$ui->formAddInputText($options);
					// Filter status
					$values = array();
					$values[-1] = __('Any');
					$values[0] = __('Pending');
					$values[1] = __('Finished');
					$values[2] = __('Validated');
					$options = array(
						'name' => 'filter_status',
						'title' => __('Status'),
						'label' => __('Status'),
						'items' => $values,
						'selected' => $this->filter_status
						);
					$ui->formAddSelectBox($options);
					$options = array(
						'name' => 'submit_button',
						'text' => __('Apply filter')
						);
					$ui->formAddSubmitButton($options);
				$form_html = $ui->getEndForm();
			$ui->contentCollapsibleAddItem($form_html);
			$ui->contentEndCollapsible();
			// Workorder listing
			$html = $this->getWorkOrdersList();
			$ui->contentAddHtml($html);
		$ui->endContent();
		// Foooter buttons
		// New
		$button_new = "<a href='index.php?page=workorder' data-role='button'
							data-icon='plus'>".__('New')."</a>\n";
		// Pagination
		$filter = "";
		if ($this->filter_search != '') {
			$filter .= "&filter_search=".$this->filter_search;
		}
		if ($this->filter_owner != '') {
			$filter .= "&filter_owner=".$this->filter_owner;
		}
		if  ($this->filter_creator != '') {
			$filter .= "&filter_creator=".$this->filter_creator;
		}
		if ($this->filter_status) {
			$filter .= "&filter_status=".$this->filter_status;
		}
		if ($this->offset <= 1) {
			$button_first = "<a class='ui-disabled' data-role='button'
							data-icon='back' data-theme='b' data-iconpos='notext'>".__('First')."</a>\n";
			$button_back = "<a class='ui-disabled' data-role='button'
							data-icon='arrow-l' data-theme='b' data-iconpos='notext'>".__('Back')."</a>\n";
		} else {
			$button_first = "<a href='index.php?page=workorders$filter&offset=1' data-role='button'
							data-icon='back' data-theme='b' data-iconpos='notext'>".__('First')."</a>\n";
			$button_back = "<a href='index.php?page=workorders$filter&offset=".($this->offset -1)."' data-role='button'
							data-icon='arrow-l' data-theme='b' data-iconpos='notext'>".__('Back')."</a>\n";
		}
		if ($this->offset >= $this->getNumPages()) {
			$button_last = "<a class='ui-disabled' data-role='button'
							data-icon='forward' data-theme='b' data-iconpos='notext'>".__('Last')."</a>\n";
			$button_forward = "<a class='ui-disabled' data-role='button'
								data-icon='arrow-r' data-theme='b' data-iconpos='notext'>".__('Forward')."</a>\n";
		} else {
			$button_last = "<a href='index.php?page=workorders$filter&offset=".$this->getNumPages()."' data-role='button'
							data-icon='forward' data-theme='b' data-iconpos='notext'>".__('Last')."</a>\n";
			$button_forward = "<a href='index.php?page=workorders$filter&offset=".($this->offset +1)."' data-role='button'
								data-icon='arrow-r' data-theme='b' data-iconpos='notext'>".__('Forward')."</a>\n";
		}
		$ui->createFooter("$button_new<div  style='float:right; padding-right:25px;' data-type='horizontal' data-role='controlgroup'>
								$button_first
								$button_back
								$button_forward
								$button_last
							</div>");
		$ui->showFooter();
		$ui->showPage();
		
	}
	
	public function show () {
		if ($this->permission) {
			$system = System::getInstance();
			$message = "";
			switch ($this->operation) {
				case 'delete':
					$workorder = new Workorder();
					$result = $workorder->deleteWorkOrder($this->id);
					if ($result) {
						$this->id = -1;
						$message = "<h2 class='suc'>".__('Successfully deleted')."</h2>";
					} else {
						$message = "<h2 class='error'>".__('An error ocurred while deleting the workorder')."</h2>";
					}
					break;
			}
			$this->showWorkOrders($message);
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
