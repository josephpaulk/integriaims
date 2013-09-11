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
	
	private $id_workorder;
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
		
		$this->id_workorder = (int) $system->getRequest('id_workorder', -1);
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
				if ($operation == "delete") {
					if ($id_workorder > 0) {
						$workorder_creator = get_db_value("created_by_user", "ttodo", "id", $this->id_workorder);
						if ($id_user == $workorder_creator) {
							$permission = true;
						}
					}
				} else {
					$permission = true;
				}
			}
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
	
	public function getWorkOrdersList ($href = "", $delete_button = true, $delete_href = "", $ajax = false) {
		$system = System::getInstance();
		$ui = Ui::getInstance();
		
		if ($href == "") {
			$href = "index.php?page=workorder&operation=view";
		}
		
		if (! $ajax) {
			$html = "<ul id='listview' class='ui-itemlistview' data-role='listview'>";
		}
		if ($this->getCountWorkorders() > 0) {
			$sql = $this->getWorkOrdersQuery();
			$new = true;
			while ( $workorder = get_db_all_row_by_steps_sql($new, $result_query, $sql) ) {
				$new = false;
				$html .= "<li>";
				$html .= "<a href='$href&id_workorder=".$workorder['id']."' class='ui-link-inherit'>";
					//$html .= $ui->getPriorityFlagImage($workorder['priority']);
					$html .= print_priority_flag_image ($workorder['priority'], true, "../", "priority-list ui-li-icon");
					$html .= "<h3 class='ui-li-heading'>".$workorder['name']."</h3>";
					$html .= "<p class='ui-li-desc'>".__('Owner').": ".$workorder['assigned_user'];
					$html .= "&nbsp;&nbsp;-&nbsp;&nbsp;".__('Creator').": ".$workorder['created_by_user']."</p>";
				$html .= "</a>";
				
				if ($delete_button) {
					if ($delete_href == "") {
						$delete_href = "index.php?page=workorders&operation=delete&filter_status=0&filter_owner=".$system->getConfig('id_user');
					}
					$options = array(
						'popup_id' => 'delete_popup_'.$workorder['id'],
						'delete_href' => "$delete_href&id_workorder=".$workorder['id']
						);
					$html .= $ui->getDeletePopupHTML($options);
					$html .= "<a data-icon=\"delete\" data-rel=\"popup\" href=\"#delete_popup_".$workorder['id']."\"></a>";
				}
				$html .= "</li>";
			}
		} else {
			$html .= "<li>";
			$html .= "<h3 class='error'>".__('There is no workorders')."</h3>";
			$html .= "</li>";
		}
		if (! $ajax) {
			$html .= "</ul>";
		}
		
		return $html;
	}
	
	public function addWorkOrdersLoader ($href = "") {
		$ui = Ui::getInstance();
		
		$script = "<script type=\"text/javascript\">
						var load_more_rows = 1;
						var page = 2;
						$(document).ready(function() {
							$(window).bind(\"scroll\", function () {
								
								if (load_more_rows) {
									if ($(this).scrollTop() + $(this).height()
										>= ($(document).height() - 100)) {
										
										load_more_rows = 0;
										
										postvars = {};
										postvars[\"action\"] = \"ajax\";
										postvars[\"page\"] = \"workorders\";
										postvars[\"method\"] = \"load_more_workorders\";
										postvars[\"offset\"] = page;
										postvars[\"href\"] = \"$href\";
										postvars[\"filter_search\"] = \"".$this->filter_search."\";
										postvars[\"filter_owner\"] = \"".$this->filter_owner."\";
										postvars[\"filter_creator\"] = \"".$this->filter_creator."\";
										postvars[\"filter_status\"] = ".$this->filter_status.";
										page++;
										
										$.post(\"index.php\",
											postvars,
											function (data) {
												if (data.length < 1) {
													$(\"#loading_rows\").hide();
												} else {
													$(\"#listview\").append(data).listview('refresh');
													load_more_rows = 1;
												}
											},
											\"html\");
									}
								}
							});
						});
					</script>";
		
		$ui->contentAddHtml($script);
	}
	
	private function showWorkOrders ($message = "") {
		$system = System::getInstance();
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
			
			$ui->contentBeginCollapsible(__('Filter'));
				$options = array(
					'action' => "index.php?page=workorders",
					'method' => 'POST',
					'data-ajax' => 'false'
					);
				$ui->beginForm($options);
					// Filter search
					$options = array(
						'name' => 'filter_search',
						'label' => __('Search'),
						'value' => $this->filter_search
						);
					$ui->formAddInputSearch($options);
					// Filter owner
					$options = array(
						'name' => 'filter_owner',
						'id' => 'text-filter_owner',
						'label' => __('Owner'),
						'value' => $this->filter_owner,
						'placeholder' => __('Owner'),
						'autocomplete' => 'off'
						);
					$ui->formAddInputText($options);
						// Owner autocompletion
						// List
						$ui->formAddHtml("<ul id=\"ul-autocomplete_owner\" data-role=\"listview\" data-inset=\"true\"></ul>");
						// Autocomplete binding
						$ui->bindMobileAutocomplete("#text-filter_owner", "#ul-autocomplete_owner");
					// Filter creator
					$options = array(
						'name' => 'filter_creator',
						'id' => 'text-filter_creator',
						'label' => __('Creator'),
						'value' => $this->filter_creator,
						'placeholder' => __('Creator'),
						'autocomplete' => 'off'
						);
					$ui->formAddInputText($options);
						// Creator autocompletion
						// List
						$ui->formAddHtml("<ul id=\"ul-autocomplete_creator\" data-role=\"listview\" data-inset=\"true\"></ul>");
						// Autocomplete binding
						$ui->bindMobileAutocomplete("#text-filter_creator", "#ul-autocomplete_creator");
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
						'text' => __('Apply filter'),
							 'data-icon' => 'search'
						);
					$ui->formAddSubmitButton($options);
				$form_html = $ui->getEndForm();
			$ui->contentCollapsibleAddItem($form_html);
			$ui->contentEndCollapsible("collapsible-filter", "d");
			// Workorder listing
			$html = $this->getWorkOrdersList("", false);
			$ui->contentAddHtml($html);
			if ($this->getCountWorkorders() > $system->getPageSize()) {
				$ui->contentAddHtml('<div style="text-align:center;" id="loading_rows">
										<img src="../images/spinner.gif">&nbsp;'
											. __('Loading...') .
										'</img>
									</div>');
				$this->addWorkOrdersLoader();
			}
		$ui->endContent();
		// Foooter buttons
		// New
		$button_new = "<a href='index.php?page=workorder' data-role='button'
							data-icon='plus'>".__('New')."</a>\n";
		// Pagination
		//~ $filter = "";
		//~ if ($this->filter_search != '') {
			//~ $filter .= "&filter_search=".$this->filter_search;
		//~ }
		//~ if ($this->filter_owner != '') {
			//~ $filter .= "&filter_owner=".$this->filter_owner;
		//~ }
		//~ if  ($this->filter_creator != '') {
			//~ $filter .= "&filter_creator=".$this->filter_creator;
		//~ }
		//~ if ($this->filter_status) {
			//~ $filter .= "&filter_status=".$this->filter_status;
		//~ }
		//~ $paginationCG = $ui->getPaginationControgroup("workorders$filter", $this->offset, $this->getNumPages());
		$ui->createFooter($button_new);
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
					$result = $workorder->deleteWorkOrder($this->id_workorder);
					unset($workorder);
					if ($result) {
						$this->id_workorder = -1;
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
			"Trying to access to workorders section");
		$error['title_text'] = __('You don\'t have access to this page');
		$error['content_text'] = __('Access to this page is restricted to 
			authorized users only, please contact to system administrator 
			if you need assistance. <br><br>Please know that all attempts 
			to access this page are recorded in security logs of Integria 
			System Database');
		$home = new Home();
		$home->show($error);
	}
	
	public function ajax ($method = false) {
		$system = System::getInstance();
		
		if (!$this->permission) {
			return;
		}
		else {
			switch ($method) {
				case 'load_more_workorders':
					if ($this->offset == 1 || $this->offset > $this->getNumPages()) {
						return;
					} else {
						$href = $system->getRequest('href', '');
						echo $this->getWorkOrdersList($href, false, "", true);
					}
					break;
			}
		}
	}
	
}

?>
