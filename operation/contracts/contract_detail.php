<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

check_login();

include_once('include/functions_crm.php');

if (defined ('AJAX')) {
	
	global $config;
	
	$upload_file = (bool) get_parameter('upload_file');
	if ($upload_file) {
		$result = array();
		$result["status"] = false;
		$result["filename"] = "";
		$result["location"] = "";
		$result["message"] = "";

		$upload_status = getFileUploadStatus("upfile");
		$upload_result = translateFileUploadStatus($upload_status);

		if ($upload_result === true) {
			$filename = $_FILES["upfile"]['name'];
			$extension = pathinfo($filename, PATHINFO_EXTENSION);
			$invalid_extensions = "/^(bat|exe|cmd|sh|php|php1|php2|php3|php4|php5|pl|cgi|386|dll|com|torrent|js|app|jar|
				pif|vb|vbscript|wsf|asp|cer|csr|jsp|drv|sys|ade|adp|bas|chm|cpl|crt|csh|fxp|hlp|hta|inf|ins|isp|jse|htaccess|
				htpasswd|ksh|lnk|mdb|mde|mdt|mdw|msc|msi|msp|mst|ops|pcd|prg|reg|scr|sct|shb|shs|url|vbe|vbs|wsc|wsf|wsh)$/i";
			
			if (!preg_match($invalid_extensions, $extension)) {
				$result["status"] = true;
				$result["location"] = $_FILES["upfile"]['tmp_name'];
				// Replace conflictive characters
				$filename = str_replace (" ", "_", $filename);
				$filename = filter_var($filename, FILTER_SANITIZE_URL);
				$result["name"] = $filename;

				$destination = sys_get_temp_dir().DIRECTORY_SEPARATOR.$result["name"];

				if (copy($result["location"], $destination))
					$result["location"] = $destination;
			} else {
				$result["message"] = __('Invalid extension');
			}
		} else {
			$result["message"] = $upload_result;
		}
		echo json_encode($result);
		return;
	}

	$remove_tmp_file = (bool) get_parameter('remove_tmp_file');
	if ($remove_tmp_file) {
		$result = false;
		$tmp_file_location = (string) get_parameter('location');
		if ($tmp_file_location) {
			$result = unlink($tmp_file_location);
		}
		echo json_encode($result);
		return;
	}
}

$id = (int) get_parameter ('id');
$id_company = (int) get_parameter ('id_company');

$section_read_permission = check_crm_acl ('contract', 'cr');
$section_write_permission = check_crm_acl ('contract', 'cw');
$section_manage_permission = check_crm_acl ('contract', 'cm');

if (!$section_read_permission && !$section_write_permission && !$section_manage_permission) {
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to the contracts section");
	include ("general/noaccess.php");
	exit;
}

$message = get_parameter('message', '');

if ($message != '') {
	echo "<h3 class='suc'>".__($message)."</h3>";
}
 
echo "<h1>".__('Contract management')."</h1>";

if ($id || $id_company) {
	
	if ($id && !$id_company) {
		$id_company = get_db_value('id_company', 'tcontract', 'id', $id);
	}
	
	if ($id) {
		$read_permission = check_crm_acl ('contract', 'cr', $config['id_user'], $id);
		$write_permission = check_crm_acl ('contract', 'cw', $config['id_user'], $id);
		$manage_permission = check_crm_acl ('contract', 'cm', $config['id_user'], $id);
		if (!$read_permission && !$write_permission && !$manage_permission) {
			audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to a contract");
			include ("general/noaccess.php");
			exit;
		}
	} elseif ($id_company) {
		$read_permission = check_crm_acl ('other', 'cr', $config['id_user'], $id_company);
		$write_permission = check_crm_acl ('other', 'cw', $config['id_user'], $id_company);
		$manage_permission = check_crm_acl ('other', 'cm', $config['id_user'], $id_company);
		if (!$read_permission && !$write_permission && !$manage_permission) {
			audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to a contract");
			include ("general/noaccess.php");
			exit;
		}
	}
}

$get_sla = (bool) get_parameter ('get_sla');
$get_company_name = (bool) get_parameter ('get_company_name');
$new_contract = (bool) get_parameter ('new_contract');
$create_contract = (bool) get_parameter ('create_contract');
$update_contract = (bool) get_parameter ('update_contract');
$delete_contract = (bool) get_parameter ('delete_contract');

// Delete file
$delete_file = (bool) get_parameter ('delete_file');
if ($delete_file) {
	if ($manage_permission) {
		$id_attachment = get_parameter ('id_attachment');
		$filename = get_db_value ('filename', 'tattachment',
			'id_attachment', $id_attachment);
		$sql = sprintf ('DELETE FROM tattachment WHERE id_attachment = %d',
			$id_attachment);
		process_sql ($sql);
		$result_msg = '<h3 class="suc">'.__('Successfully deleted').'</h3>';
		if (!unlink ($config["homedir"].'attachment/'.$id_attachment.'_'.$filename))
			$result_msg = '<h3 class="error">'.__('Could not be deleted').'</h3>';
		
	} else {
		$result_msg = '<h3 class="error">'.__('You have no permission').'</h3>';
	}
	
	echo $result_msg;
}

if ($get_sla) {
	$sla = get_contract_sla ($id, false);
	
	if (defined ('AJAX')) {
		echo json_encode ($sla);
		return;
	}
}

if ($get_company_name) {
	$company = get_contract_company ($id, true);

	if (defined ('AJAX')) {
		echo json_encode (reset($company));
		return;
	}
}

// CREATE
if ($create_contract) {

	if (!$write_permission && !$manage_permission) {
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create a contract");
		require ("general/noaccess.php");
		exit;
	}

	$name = (string) get_parameter ('name');
	$contract_number = (string) get_parameter ('contract_number');
	$description = (string) get_parameter ('description');
	$date_begin = (string) get_parameter ('date_begin');
	$date_end = (string) get_parameter ('date_end');
	$private = (int) get_parameter ('private');
	$status = (int) get_parameter ('status', 1);
	$upfiles = (string) get_parameter('upfiles');
	
	$sql = sprintf ('INSERT INTO tcontract (name, contract_number, description, date_begin,
		date_end, id_company, private, status)
		VALUE ("%s", "%s", "%s", "%s", "%s", %d, %d, %d)',
		$name, $contract_number, $description, $date_begin, $date_end,
		$id_company, $private, $status);

	$id = process_sql ($sql, 'insert_id');
	if ($id === false)
		echo '<h3 class="error">'.__('Could not be created').'</h3>';
	else {
		//update last activity
		$datetime =  date ("Y-m-d H:i:s");
		$comments = __("Created contract by ".$config['id_user']);
		$sql_add = sprintf ('INSERT INTO tcompany_activity (id_company, written_by, date, description) VALUES (%d, "%s", "%s", "%s")', $id_company, $config["id_user"], $datetime, $comments);
		process_sql ($sql_add);
		$sql_activity = sprintf ('UPDATE tcompany SET last_update = "%s" WHERE id = %d', $datetime, $id_company);
		$result_activity = process_sql ($sql_activity);
		
		// ATTACH A FILE IF IS PROVIDED
		$upfiles = json_decode(safe_output($upfiles), true);

		if (!empty($upfiles)) {
			foreach ($upfiles as $file) {
				if (is_array($file)) {
					if ($file['description']) {
						$file_description = $file['description'];
					} else {
						$file_description = __('No description available');
					}
					$file_result = crm_attach_contract_file ($id, $file["location"], $file_description, $file["name"]);
					
					$file_tmp = sys_get_temp_dir().'/'.$file["name"];
					$size = filesize ($file_tmp);
					$filename_encoded = $file_result . "_" . $file["name"];
				
					// Copy file to directory and change name
					$file_target = $config["homedir"]."/attachment/".$filename_encoded;
			
					if (!(copy($file_tmp, $file_target))){
						echo "<h3 class=error>".__("Could not be attached")."</h3>";
					} else {
						// Delete temporal file
						echo "<h3 class=suc>".__("Successfully attached")."</h3>";
						$location = $file_target;
						unlink ($file_tmp);
					}
				}
			}
		}

		echo '<h3 class="suc">'.__('Successfully created').'</h3>';
		audit_db ($config['id_user'], $REMOTE_ADDR, "Contract created", "Contract named '$name' has been added");
	}
	$id = 0;
}

// UPDATE
if ($update_contract) { // if modified any parameter
	
	if (!$write_permission && !$manage_permission) {
		audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to update a contract");
		require ("general/noaccess.php");
		exit;
	}

	$name = (string) get_parameter ('name');
	$contract_number = (string) get_parameter ('contract_number');
	$description = (string) get_parameter ('description');
	$date_begin = (string) get_parameter ('date_begin');
	$date_end = (string) get_parameter ('date_end');
	$private = (int) get_parameter ('private');
	$status = (int) get_parameter ('status');
	$upfiles = (string) get_parameter('upfiles');


	$sql = sprintf ('UPDATE tcontract SET contract_number = "%s",
		description = "%s", name = "%s", date_begin = "%s",
		date_end = "%s", id_company = %d, private = %d, status = %d
		WHERE id = %d',
		$contract_number, $description, $name, $date_begin,
		$date_end, $id_company, $private, $status, $id);
	
	$result = process_sql ($sql);
	if ($result === false) {
		echo "<h3 class='error'>".__('Could not be updated')."</h3>";
	} else {
		//update last activity
		$datetime =  date ("Y-m-d H:i:s");
		$comments = __("Update contract ".$id. " by ".$config['id_user']);
		$sql_add = sprintf ('INSERT INTO tcompany_activity (id_company, written_by, date, description) VALUES (%d, "%s", "%s", "%s")', $id_company, $config["id_user"], $datetime, $comments);
		process_sql ($sql_add);
		$sql_activity = sprintf ('UPDATE tcompany SET last_update = "%s" WHERE id = %d', $datetime, $id_company);
		$result_activity = process_sql ($sql_activity);
		
	
		// ATTACH A FILE IF IS PROVIDED
		$upfiles = json_decode(safe_output($upfiles), true);
		if (!empty($upfiles)) {
			foreach ($upfiles as $file) {

				if (is_array($file)) {
					if ($file['description']) {
						$file_description = $file['description'];
					} else {
						$file_description = __('No description available');
					}
					$file_result = crm_attach_contract_file ($id, $file["location"], $file_description, $file["name"]);
					
					$file_tmp = sys_get_temp_dir().'/'.$file["name"];
					$size = filesize ($file_tmp);
					$filename_encoded = $file_result . "_" . $file["name"];
				
					// Copy file to directory and change name
					$file_target = $config["homedir"]."/attachment/".$filename_encoded;
			
					if (!(copy($file_tmp, $file_target))){
						echo "<h3 class=error>".__("Could not be attached")."</h3>";
					} else {
						// Delete temporal file
						echo "<h3 class=suc>".__("Successfully attached")."</h3>";
						$location = $file_target;
						unlink ($file_tmp);
					}
				}
			}
		}

		echo "<h3 class='suc'>".__('Successfully updated')."</h3>";
		audit_db ($config['id_user'], $REMOTE_ADDR, "Contract updated", "Contract named '$name' has been updated");
	}

	$id = 0;
}


// FORM (Update / Create)
if ($id || $new_contract) {
	if ($new_contract) {
		
		if (!$section_write_permission && !$section_manage_permission) {
			audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to create a contract");
			require ("general/noaccess.php");
			exit;
		}
		
		$name = "";
		$contract_number = "";
		$date_begin = date('Y-m-d');
		$date_end = $date_begin;
		$id_sla = "";
		$description = "";
		$private = 0;
		$status = 1;
	} else {
		
		if (!$read_permission && !$write_permission && !$manage_permission) {
			audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to update a contract");
			require ("general/noaccess.php");
			exit;
		}
		
		$contract = get_db_row ("tcontract", "id", $id);
		$name = $contract["name"];
		$contract_number = $contract["contract_number"];
		$id_company = $contract["id_company"];
		$date_begin = $contract["date_begin"];
		$date_end   = $contract["date_end"];
		$description = $contract["description"];
		$id_sla = $contract["id_sla"];
		$private = $contract["private"];
		$status = $contract["status"];
	}
	
	$table->width = '99%';
	$table->colspan = array ();
	$table->colspan[4][0] = 2;
	$table->data = array ();
	
	if ($new_contract || ($id && ($write_permission || $manage_permission))) {
		
		$table->class = 'search-table-button';
		
		$params = array();
		$params['input_id'] = 'id_company';
		$params['input_name'] = 'id_company';
		$params['input_value'] = $id_company;
		$params['title'] = __('Company');
		$params['return'] = true;
		$table->data[0][0] = print_company_autocomplete_input($params);

		$table->data[0][1] = print_input_text ('name', $name, '', 40, 100, true, __('Contract name'));
		$table->data[1][0] = print_input_text ('contract_number', $contract_number, '', 40, 100, true, __('Contract number'));
		$table->data[1][1] = print_checkbox ('private', '1', $private, true, __('Private')). print_help_tip (__("Private contracts are visible only by users of the same company"), true);		
			
		$table->data[2][0] = print_input_text ('date_begin', $date_begin, '', 15, 20, true, __('Begin date'));
		$table->data[2][1] = print_input_text ('date_end', $date_end, '', 15, 20, true, __('End date'));
		
		if ($id_company) {
			$table->data[3][0] .= "&nbsp;&nbsp;<a href='index.php?sec=customers&sec2=operation/companies/company_detail&id=$id_company'>";
			$table->data[3][0] .= "<img src='images/company.png'></a>";
		}
		
		$table->data[3][1] = print_select (get_contract_status(), 'status', $status, '', '', '', true, 0, false,  __('Status'));

		$table->data[4][0] = print_textarea ("description", 14, 1, $description, '', true, __('Description'));
		
		// Optional file update
		$html = "";
		$html .= "<div id=\"contract_files\" class=\"fileupload_form\" method=\"post\" enctype=\"multipart/form-data\">";
		$html .= 	"<div id=\"drop_file\" style=\"padding:0px 0px;\">";
		$html .= 		"<table width=\"99%\">";
		$html .= 			"<td width=\"45%\">";
		$html .= 				__('Drop the file here');
		$html .= 			"<td>";
		$html .= 				__('or');
		$html .= 			"<td width=\"45%\">";
		$html .= 				"<a id=\"browse_button\">" . __('browse it') . "</a>";
		$html .= 			"<tr>";
		$html .= 		"</table>";
		$html .= 		"<input name=\"upfile\" type=\"file\" id=\"file-upfile\" class=\"sub file\" />";
		$html .= 		"<input type=\"hidden\" name=\"upfiles\" id=\"upfiles\" />"; // JSON STRING
		$html .= 	"</div>";
		$html .= 	"<ul></ul>";
		$html .= "</div>";

		$table_description = new stdClass;
		$table_description->width = '99%';
		$table_description->id = 'contract_file_description';
		$table_description->class = 'search-table-button';
		$table_description->data = array();
		$table_description->data[0][0] = print_textarea ("file_description", 3, 40, '', '', true, __('Description'));
		$table_description->data[1][0] = print_submit_button (__('Add'), 'crt_btn', false, 'class="sub create"', true);
		$html .= "<div id='contract_file_description_table_hook' style='display:none;'>";
		$html .= print_table($table_description, true);
		$html .= "</div>";

		$table->colspan[5][0] = 4;
		$table->data[5][0] = print_container('file_upload_container', __('File upload'), $html, 'closed', true, false);

		if ($id) {
			$button = print_submit_button (__('Update'), 'update_btn', false, 'class="sub upd"', true);
			$button .= print_input_hidden ('id', $id, true);
			$button .= print_input_hidden ('update_contract', 1, true);
			
			$table->data['button'][1] = $button;
			$table->colspan['button'][1] = 2;
		} else {
			$button = print_submit_button (__('Create'), 'create_btn', false, 'class="sub create"', true);
			$button .= print_input_hidden ('create_contract', 1, true);
			
			$table->data['button'][1] = $button;
			$table->colspan['button'][1] = 2;
		}
	}
	else {
		
		$table->class = 'search-table';

		$table->data[0][0] = "<b>".__('Contract name')."</b><br>$name<br>";
		if($contract_number == '') {
			$contract_number = '<i>-'.__('Empty').'-</i>';
		}		
		$table->data[1][0] = "<b>".__('Contract number')."</b><br>$contract_number<br>";
		
		$table->data[1][1] = "<b>".__('Status')."</b><br>".get_contract_status_name($status)."<br>";
		
		$table->data[2][0] = "<b>".__('Begin date')."</b><br>$date_begin<br>";
		$table->data[2][1] = "<b>".__('End date')."</b><br>$date_end<br>";
		
		$company_name = get_db_value('name','tcompany','id',$id_company);
		
		$table->data[3][0] = "<b>".__('Company')."</b><br>$company_name";
		
		$table->data[3][0] .= "&nbsp;&nbsp;<a href='index.php?sec=customers&sec2=operation/companies/company_detail&id=$id_company'>";
		$table->data[3][0] .= "<img src='images/company.png'></a>";
		
		$sla_name = get_db_value('name','tsla','id',$id_sla);
		
		$table->data[3][1] = "<b>".__('SLA')."</b><br>$sla_name<br>";
		if($description == '') {
			$description = '<i>-'.__('Empty').'-</i>';
		}		
		$table->data[3][1] = "<b>".__('Description')."</b><br>$description<br>";
	}
	
	echo '<form id="contract_form" method="post" action="index.php?sec=customers&sec2=operation/contracts/contract_detail">';
	print_table ($table);
	echo "</form>";
	
	if ($id && ($write_permission || $manage_permission)) {
		//File list		
		echo "<h2>".__('Files')."</h2>";

		// Files attached to this contract
		$files = crm_get_contract_files ($id);
		if ($files === false) {
			$files = array();
			echo '<h4 id="no_files_message">'.__('No files were added to the contract').'</h4>';
			$hidden = "style=\"display:none;\"";
		}

		echo "<div style='width: 98%; margin: 0 auto;'>";
		echo "<table id='table-incident_files' $hidden class=listing cellpadding=0 cellspacing=0 width='100%'>";
		echo "<tr>";
		echo "<th>".__('Filename');
		echo "<th>".__('Timestamp');
		echo "<th>".__('Description');
		echo "<th>".__('ID user');
		echo "<th>".__('Size');

		if ($manage_permission) {
			echo "<th>".__('Delete');
		}

		foreach ($files as $file) {

			$link = "operation/common/download_file.php?id_attachment=".$file["id_attachment"]."&type=contract";

			$real_filename = $config["homedir"]."/attachment/".$file["id_attachment"]."_".rawurlencode ($file["filename"]);    

			echo "<tr>";
			echo "<td valign=top>";
			echo '<a target="_blank" href="'.$link.'">'. $file['filename'].'</a>';
			echo "<td valign=top class=f9>".$file['timestamp'];
			echo "<td valign=top class=f9>". $file["description"];
			echo "<td valign=top>". $file["id_usuario"];
			echo "<td valign=top>". byte_convert ($file['size']);

			// Delete attachment
			if ($manage_permission) {
				echo "<td>". '<a class="delete" name="delete_file_'.$file["id_attachment"].'" href="index.php?sec=customers&sec2=operation/contracts/contract_detail&id='.$id.'&id_attachment='.$file["id_attachment"].'&delete_file=1">
				<img src="images/cross.png"></a>';
			}

		}

		echo "</table>";
		echo "</div>";
	}
} else {
	
	// Contract listing
	$search_text = (string) get_parameter ('search_text');
	$search_company_role = (int) get_parameter ('search_company_role');
	$search_date_end = get_parameter ('search_date_end');
	$search_date_begin = get_parameter ('search_date_begin');
	$search_date_begin_beginning = get_parameter ('search_date_begin_beginning');
	$search_date_end_beginning = get_parameter ('search_date_end_beginning');
	$search_status = (int) get_parameter ('search_status', 1);
	$search_expire_days = (int) get_parameter ('search_expire_days');

	$search_params = "search_text=$search_text&search_company_role=$search_company_role&search_date_end=$search_date_end&search_date_begin=$search_date_begin&search_date_begin_beginning=$search_date_begin_beginning&search_date_end_beginning=$search_date_end_beginning&search_status=$search_status&search_expire_days=$search_expire_days";
	
	$where_clause = "WHERE 1=1";
	
	if ($search_text != "") {
		$where_clause .= sprintf (' AND (id_company IN (SELECT id FROM tcompany WHERE name LIKE "%%%s%%") OR 
			name LIKE "%%%s%%" OR 
			contract_number LIKE "%%%s%%")', $search_text, $search_text, $search_text);
	}
	
	if ($search_company_role) {
		$where_clause .= sprintf (' AND id_company IN (SELECT id FROM tcompany WHERE id_company_role = %d)', $search_company_role);
	}
	
	if ($search_date_end != "") {
		$where_clause .= sprintf (' AND date_end <= "%s"', $search_date_end);
	}
	
	if ($search_date_begin != "") {
		$where_clause .= sprintf (' AND date_end >= "%s"', $search_date_begin);
	}
		
	if ($search_date_end_beginning != "") {
		$where_clause .= sprintf (' AND date_begin <= "%s"', $search_date_end_beginning);
	}
	
	if ($search_date_begin_beginning != "") {
		$where_clause .= sprintf (' AND date_begin >= "%s"', $search_date_begin_beginning);
	}
	
	if ($search_status >= 0) {
		$where_clause .= sprintf (' AND status = %d', $search_status);
	}
	
	if ($search_expire_days > 0) {
		// Comment $today_date to show contracts that expired yet
		$today_date = date ("Y/m/d");
		$expire_date = date ("Y/m/d", strtotime ("now") + $search_expire_days * 86400);
		$where_clause .= sprintf (' AND (date_end < "%s" AND date_end > "%s")', $expire_date, $today_date);
	}
	
	echo '<form action="index.php?sec=customers&sec2=operation/contracts/contract_detail" method="post">';
	
	echo "<table width=99% class='search-table'>";
	echo "<tr>";
	
	echo "<td colspan=2>";
	echo print_input_text ("search_text", $search_text, "", 38, 100, true, __('Search'));
	echo "</td>";
	
	echo "<td>";
	echo print_select (get_company_roles(), 'search_company_role',
		$search_company_role, '', __('All'), 0, true, false, false, __('Company roles'));	
	echo "</td>";
	
	echo "<td>";
	echo print_select (get_contract_status(), 'search_status',
		$search_status, '', __('Any'), -1, true, false, false, __('Status'));	
	echo "</td>";
	
	echo "<td>";
	echo print_select (get_contract_expire_days(), 'search_expire_days',
		$search_expire_days, '', __('None'), 0, true, false, false, __('Out of date'));	
	echo "</td>";
	
	echo "</tr>";
	
	echo "<tr>";
	
	echo "<td>";
	echo print_input_text ('search_date_begin_beginning', $search_date_begin_beginning, '', 15, 20, true, __('Begining From'));
	echo "<a href='#' class='tip'><span>". __('Date format is YYYY-MM-DD')."</span></a>";
	echo "</td>";
	
	echo "<td>";
	echo print_input_text ('search_date_end_beginning', $search_date_end_beginning, '', 15, 20, true, __('Begining To'));
	echo "<a href='#' class='tip'><span>". __('Date format is YYYY-MM-DD')."</span></a>";
	echo "</td>";
	
	echo "<td>";
	echo print_input_text ('search_date_begin', $search_date_begin, '', 15, 20, true, __('Ending From'));
	echo "<a href='#' class='tip'><span>". __('Date format is YYYY-MM-DD')."</span></a>";
	echo "</td>";
	
	echo "<td>";
	echo print_input_text ('search_date_end', $search_date_end, '', 15, 20, true, __('Ending To'));
	echo "<a href='#' class='tip'><span>". __('Date format is YYYY-MM-DD')."</span></a>";	
	echo "</td>";
	
	echo "<td valign=bottom align='right'>";
	echo print_submit_button (__('Search'), "search_btn", false, 'class="sub search"', true);
	// Delete new lines from the string
	$where_clause = str_replace(array("\r", "\n"), '', $where_clause);
	echo print_button(__('Export to CSV'), '', false, 'window.open(\'include/export_csv.php?export_csv_contracts=1&where_clause=' . str_replace('"', "\'", $where_clause) . '\')', 'class="sub csv"', true);
	echo "</td>";
	echo "</tr>";
	
	echo "</table>";
	
	echo '</form>';
		
	$contracts = crm_get_all_contracts ($where_clause);

	$contracts = print_array_pagination ($contracts, "index.php?sec=customers&sec2=operation/contracts/contract_detail&$search_params");

	if ($contracts !== false) {
		
		$table->width = "99%";
		$table->class = "listing";
		$table->cellspacing = 0;
		$table->cellpadding = 0;
		$table->tablealign="left";
		$table->data = array ();
		$table->size = array ();
		$table->style = array ();
		$table->colspan = array ();
		$table->style[3]= "font-size: 8px";
		$table->style[4]= "font-size: 8px";
		$table->style[1]= "font-size: 9px";
		$table->head[0] = __('Name');
		$table->head[1] = __('Contract number');
		$table->head[2] = __('Company');
		$table->head[3] = __('Begin');
		$table->head[4] = __('End');
		if ($section_write_permission || $section_manage_permission) {
			$table->head[5] = __('Privacy');
			$table->head[6] = __('Delete');
		}
		$counter = 0;
		
		foreach ($contracts as $contract) {
			
			$data = array ();
			
			$data[0] = "<a href='index.php?sec=customers&sec2=operation/contracts/contract_detail&id="
				.$contract["id"]."'>".$contract["name"]."</a>";
			$data[1] = $contract["contract_number"];
			$data[2] = "<a href='index.php?sec=customers&sec2=operation/companies/company_detail&id=".$contract["id_company"]."'>";
			$data[2] .= get_db_value ('name', 'tcompany', 'id', $contract["id_company"]);
			$data[2] .= "</a>";
			
			$data[3] = $contract["date_begin"];
			$data[4] = $contract["date_end"] != '0000-00-00' ? $contract["date_end"] : "-";
			
			if ($section_write_permission || $section_manage_permission) {
				// Delete
				if($contract["private"]) {
					$data[5] = __('Private');
				}
				else {
					$data[5] = __('Public');
				}

				$data[6] = "<a href='#' onClick='javascript: show_validation_delete(\"delete_contract\",".$contract["id"].",0,0,\"".$search_params."\");'><img src='images/cross.png'></a>";
			}
			array_push ($table->data, $data);
		}	
		print_table ($table);
	}
	
	if ($section_write_permission || $section_manage_permission) {
		echo '<form method="post" action="index.php?sec=customers&sec2=operation/contracts/contract_detail">';
		echo '<div style="width: '.$table->width.'; text-align: right;">';
		print_submit_button (__('Create'), 'new_btn', false, 'class="sub create"');
		print_input_hidden ('new_contract', 1);
		echo '</div>';
		echo '</form>';
	}
}

echo "<div class= 'dialog ui-dialog-content' title='".__("Delete")."' id='item_delete_window'></div>";
?>

<script type="text/javascript" src="include/js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
<script type="text/javascript" src="include/js/jquery.validate.js"></script>
<script type="text/javascript" src="include/js/jquery.validation.functions.js"></script>
<script type="text/javascript" src="include/js/integria_date.js"></script>
<script type="text/javascript" src="include/js/jquery.fileupload.js"></script>
<script type="text/javascript" src="include/js/jquery.iframe-transport.js"></script>
<script type="text/javascript" src="include/js/jquery.knob.js"></script>
<script type="text/javascript" src="include/js/integria_crm.js"></script>

<script type="text/javascript">
	
add_ranged_datepicker ("#text-date_begin", "#text-date_end", null);
add_ranged_datepicker ("#text-search_date_begin_beginning", "#text-search_date_end_beginning", null);
add_ranged_datepicker ("#text-search_date_begin", "#text-search_date_end", null);

$(document).ready (function () {

	var idUser = "<?php echo $config['id_user'] ?>";
	bindCompanyAutocomplete ('id_company', idUser);
	
	$("#id_group").change (function() {
		refresh_company_combo();
	});
	
	if ($("#search_expire_days").val() > 0) {
		disable_dates();
	}
	
	$("#search_expire_days").change (function() {
		if ($("#search_expire_days").val() > 0) {
			disable_dates();
		} else {
			enable_dates();
		}
	});
	
	// Init the file upload
	form_upload();
	
});

function disable_dates () {
	$("#text-search_date_begin_beginning").prop('disabled', true);
	$("#text-search_date_end_beginning").prop('disabled', true);
	$("#text-search_date_begin").prop('disabled', true);
	$("#text-search_date_end").prop('disabled', true);
}

function enable_dates () {
	$("#text-search_date_begin_beginning").prop('disabled', false);
	$("#text-search_date_end_beginning").prop('disabled', false);
	$("#text-search_date_begin").prop('disabled', false);
	$("#text-search_date_end").prop('disabled', false);
}

function toggle_advanced_fields () {
	
	$("#advanced_fields").toggle();
}

function refresh_company_combo () {
	
	var group = $("#id_group").val();
	
	values = Array ();
	values.push ({name: "page",
		value: "operation/contracts/contract_detail"});
	values.push ({name: "group",
		value: group});
	values.push ({name: "get_group_combo",
		value: 1});
	jQuery.get ("ajax.php",
		values,
		function (data, status) {
			$("#id_company").remove();
			$("#label-id_company").after(data);
		},
		"html"
	);

}

function form_upload () {
	// Input will hold the JSON String with the files data
	var input_upfiles = $('input#upfiles');
	// JSON Object will hold the files data
	var upfiles = {};

	$('#drop_file #browse_button').click(function() {
		// Simulate a click on the file input button to show the file browser dialog
		$("#file-upfile").click();
	});

	// Initialize the jQuery File Upload plugin
	$('#contract_files').fileupload({
		
		url: 'ajax.php?page=operation/contracts/contract_detail&upload_file=true',
		
		// This element will accept file drag/drop uploading
		dropZone: $('#drop_file'),

		// This function is called when a file is added to the queue;
		// either via the browse button, or via drag/drop:
		add: function (e, data) {
			data.context = addListItem(0, data.files[0].name, data.files[0].size);

			// Automatically upload the file once it is added to the queue
			data.context.addClass('working');
			var jqXHR = data.submit();
		},

		progress: function(e, data) {

			// Calculate the completion percentage of the upload
			var progress = parseInt(data.loaded / data.total * 100, 10);

			// Update the hidden input field and trigger a change
			// so that the jQuery knob plugin knows to update the dial
			data.context.find('input').val(progress).change();

			if (progress >= 100) {
				data.context.removeClass('working');
				data.context.removeClass('error');
				data.context.addClass('loading');
			}
		},

		fail: function(e, data) {
			// Something has gone wrong!
			data.context.removeClass('working');
			data.context.removeClass('loading');
			data.context.addClass('error');
		},
		
		done: function (e, data) {
			
			var result = JSON.parse(data.result);
			
			if (result.status) {
				data.context.removeClass('error');
				data.context.removeClass('loading');
				data.context.addClass('working');

				// Increase the counter
				if (upfiles.length == undefined) {
					upfiles.length = 0;
				} else {
					upfiles.length += 1;
				}
				var index = upfiles.length;
				// Create the new element
				upfiles[index] = {};
				upfiles[index].name = result.name;
				upfiles[index].location = result.location;
				// Save the JSON String into the input
				input_upfiles.val(JSON.stringify(upfiles));

				// FORM
				addForm (data.context, index);
				
			} else {
				// Something has gone wrong!
				data.context.removeClass('working');
				data.context.removeClass('loading');
				data.context.addClass('error');
				if (result.message) {
					var info = data.context.find('i');
					info.css('color', 'red');
					info.html(result.message);
				}
			}
		}

	});

	// Prevent the default action when a file is dropped on the window
	$(document).on('drop_file dragover', function (e) {
		e.preventDefault();
	});

	function addListItem (progress, filename, filesize) {
		var tpl = $('<li>'+
						'<input type="text" id="input-progress" value="0" data-width="55" data-height="55"'+
						' data-fgColor="#FF9933" data-readOnly="1" data-bgColor="#3e4043" />'+
						'<p></p>'+
						'<span></span>'+
						'<div class="contract_file_form"></div>'+
					'</li>');
		
		// Append the file name and file size
		tpl.find('p').text(filename);
		if (filesize > 0) {
			tpl.find('p').append('<i>' + formatFileSize(filesize) + '</i>');
		}

		// Initialize the knob plugin
		tpl.find('input').val(0);
		tpl.find('input').knob({
			'draw' : function () {
				$(this.i).val(this.cv + '%')
			}
		});

		// Listen for clicks on the cancel icon
		tpl.find('span').click(function() {

			if (tpl.hasClass('working') || tpl.hasClass('error') || tpl.hasClass('suc')) {

				if (tpl.hasClass('working') && typeof jqXHR != 'undefined') {
					jqXHR.abort();
				}

				tpl.fadeOut();
				tpl.slideUp(500, "swing", function() {
					tpl.remove();
				});

			}

		});
		
		// Add the HTML to the UL element
		var item = tpl.appendTo($('#contract_files ul'));
		item.find('input').val(progress).change();

		return item;
	}

	function addForm (item, array_index) {
		
		item.find(".contract_file_form").html($("#contract_file_description_table_hook").html());

		item.find("#submit-crt_btn").click(function(e) {
			e.preventDefault();
			
			$(this).prop('value', "<?php echo __('Update'); ?>");
			$(this).removeClass('create');
			$(this).addClass('upd');

			// Add the description to the array
			upfiles[array_index].description = item.find("#textarea-file_description").val();	
			// Save the JSON String into the input
			input_upfiles.val(JSON.stringify(upfiles));
		});

		// Listen for clicks on the cancel icon
		item.find('span').click(function() {
			// Remove the element from the array
			upfiles[array_index] = {};
			// Save the JSON String into the input
			input_upfiles.val(JSON.stringify(upfiles));
			// Remove the tmp file
			$.ajax({
				type: 'POST',
				url: 'ajax.php',
				data: {
					page: "operation/contracts/contract_detail",
					remove_tmp_file: true,
					location: upfiles[array_index].location
				}
			});
		});

	}

}

// Form validation
trim_element_on_submit('#text-search_text');
trim_element_on_submit('#text-name');
trim_element_on_submit('#text-contract_number');
validate_form("#contract_form");
var rules, messages;
// Rules: #text-name
rules = {
	required: true,
	remote: {
		url: "ajax.php",
        type: "POST",
        data: {
			page: "include/ajax/remote_validations",
			search_existing_contract: 1,
			id_company: function() { return $('#id_company').val() },
			contract_name: function() { return $('#text-name').val() },
			contract_id: "<?php echo $id?>"
        }
	}
};
messages = {
	required: "<?php echo __('Name required')?>",
	remote: "<?php echo __('This contract already exists')?>"
};
add_validate_form_element_rules('#text-name', rules, messages);
// Rules: #text-contract_number
rules = {
	required: true,
	remote: {
		url: "ajax.php",
        type: "POST",
        data: {
			page: "include/ajax/remote_validations",
			search_existing_contract_number: 1,
			contract_number: function() { return $('#text-contract_number').val() },
			contract_id: "<?php echo $id?>"
        }
	}
};
messages = {
	required: "<?php echo __('Contract number required')?>",
	remote: "<?php echo __('This contract number already exists')?>"
};
add_validate_form_element_rules('#text-contract_number', rules, messages);

</script>


