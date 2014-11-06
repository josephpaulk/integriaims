<?php
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2010 Ártica Soluciones Tecnológicas
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

if (!give_acl($config["id_user"], 0, "FRR")) {
	audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation", "Trying to access to File Sharing");
	require ("general/noaccess.php");

	if (defined('AJAX')) {
	    return;
	}
    else {
    	exit;
    }
}

require_once($config['homedir']."/operation/file_sharing/FileSharingFile.class.php");
require_once($config['homedir']."/operation/file_sharing/FileSharingPackage.class.php");

if (defined('AJAX')) {
	ob_clean();

	$delete_package = (bool) get_parameter("deletePackage");
	if ($delete_package) {
		$result = array(
				"status" => false,
				"message" => ''
			);
		
		$id = (int) get_parameter("id");

		if (!empty($id)) {
			$user_is_admin = (bool) dame_admin($config['id_user']);

			$package = new FileSharingPackage($id);

			if ($package->getUploader() == $config['id_user'] || $user_is_admin) {
				$result['status'] = $package->delete();

				if (!$result['status']) {
					$result['message'] = __("An error occurred while deleting the file");
				}
			}
			else {
				audit_db($config["id_user"],$config["REMOTE_ADDR"],
					"ACL Violation", "Trying to delete a Shared File without permission");
				$result['message'] = __("You don't have permisison to delete this file");
			}
		}
		else {
			$result['message'] = __("Empty ID");
		}

		echo json_encode($result);
		return;
	}
	
	return;
}

echo "<h1>" . __('File sharing') . "</h1>";

$id_user = get_parameter('id_user', $config['id_user']);

// If the user doesn't exist get the current user
$user_data = get_user($id_user);
if (empty($user_data))
	$id_user = $config['id_user'];

$user_is_admin = (bool) dame_admin($config['id_user']);

if ($user_is_admin) {
	// PHP upload parameters info
	$max_upload = (float) ini_get('upload_max_filesize');
	$max_post = (float) ini_get('post_max_size');
	$memory_limit = (float) ini_get('memory_limit');
	$upload_mb = min($max_upload, $max_post, $memory_limit);

	echo "<div class=\"upload-params-info\">";
	echo sprintf(__("The users can upload files with a maximum size of %sMB"), $upload_mb) . ". " . __("To increase this value, change your server settings") . ".";
	echo "</div>";

	// Select user
	echo "<div>";
	echo "<form method=post action='index.php?sec=file_sharing&sec2=operation/file_sharing/manage'>";

	$table = new StdClass();
	$table->id = "cost_form";
	$table->class = "search-table";
	$table->data = array();

	$row = array();
	$row[] = print_input_text_extended ('id_user', $id_user, 'text-id_user', '', 50, 250, false, '', '', true, '')
		. print_help_tip (__("Type at least two characters to search"), true);
	$row[] = print_submit_button (__('Go'), 'sub_btn', false, 'class="next sub"', true);

	$table->data[] = $row;

	print_table ($table);
	unset($table);

	echo "</form>";
	echo "</div>";
}

?>

<div id="file_sharing_table" class="table">
	<div class="table_row">
		<div id="file_sharing_list_cell" class="table_cell">
			<?php require($config['homedir']."/operation/file_sharing/list.php"); ?>
		</div>
		<div id="file_sharing_upload_cell" class="table_cell">
			<?php require($config['homedir']."/operation/file_sharing/upload.php"); ?>
		</div>
	</div>
</div>

<script type="text/javascript" src="include/js/jquery.ui.autocomplete.js"></script>

<script type="text/javascript">
	$(document).ready (function () {
		if (<?php echo json_encode($user_is_admin); ?>) {
			var idUser = "<?php echo $config['id_user'] ?>";
			bindAutocomplete ('#text-id_user', idUser);
		}
	});
</script>