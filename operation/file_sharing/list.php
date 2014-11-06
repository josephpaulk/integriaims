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
	audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation", "Trying to access Downloads browser");
	require ("general/noaccess.php");
    exit;
}

require_once($config['homedir']."/operation/file_sharing/FileSharingFile.class.php");
require_once($config['homedir']."/operation/file_sharing/FileSharingPackage.class.php");

$user_is_admin = (bool) dame_admin($config['id_user']);

$id_user = get_parameter('id_user', $config['id_user']);

// If the user doesn't exist get the current user
$user_data = get_user($id_user);
if (empty($user_data))
	$id_user = $config['id_user'];

$user_is_admin = (bool) dame_admin($config['id_user']);

$sql = "SELECT id_attachment FROM tattachment
		WHERE id_usuario = '$id_user'
			AND file_sharing = 1
		ORDER BY timestamp DESC, id_attachment DESC";
$files_aux = get_db_all_rows_sql($sql);

if (empty($files_aux) || empty($files_aux[0])) {
	$files_aux = array();
}
$files = array();
foreach ($files_aux as $file_aux) {
	$id = $file_aux['id_attachment'];
	$file = new FileSharingPackage($id);
	$file->loadTrackingDownload(); // Load the downloads tracking info
	$files[] = $file->toArray();
}

?>

<div id="user_files" class="table user_files_table"></div>

<script src="<?php echo $config['base_url']; ?>/operation/file_sharing/FileSharingAdapters.js"></script>
<script src="<?php echo $config['base_url']; ?>/operation/file_sharing/FileSharingListController.js"></script>

<script type="text/javascript">

	var files = <?php echo json_encode($files); ?>;
	var $dialogContent = $("<div></div>");

	var listController = FileSharingListController.getController();
	listController.init({
		domSelector: "div#user_files",
		files: files,
		emptyMessage: "<h3 style='color: red;'><?php echo __('There are no files'); ?></h3>",
		reloadCallback: function () {
			$("div.file_sharing_data_item.date").attr("title", "<?php echo __('Modified'); ?>");
			$("div.file_sharing_data_item.size").attr("title", "<?php echo __('Size'); ?>");
			$("div.file_sharing_data_item.downloads")
				.attr("title", "<?php echo __('Downloads'); ?>")
				.append("<?php echo ' '.__('downloads'); ?>")
				.hover(function(e) {
					$(this).animate({
						backgroundColor: "#FFB366"
					}, 75);
				}, function(e) {
					$(this).animate({
						backgroundColor: "#FF9933"
					}, 75);
				})
				.css("cursor", "pointer")
				.click(function(e) {
					

					var fileDownloads = $(this).data("downloads");

					if (fileDownloads.length > 0) {
						var $dialogContentTable = $("<div></div>");
						$dialogContentTable
							.addClass("table")
							.addClass("file_sharing_downloads_tracking");

						var $dialogContentTableHead = $("<div></div>")
							.addClass("table_row")
							.addClass("table_head")
							.addClass("file_sharing_downloads_tracking");
						var $dialogContentTableHeadDate = $("<div><?php echo __('Date'); ?></div>");
						$dialogContentTableHeadDate
							.addClass("table_cell")
							.addClass("file_sharing_downloads_tracking");
						$dialogContentTableHead.append($dialogContentTableHeadDate);
						var $dialogContentTableHeadOrigin = $("<div><?php echo __('Origin'); ?></div>");
						$dialogContentTableHeadOrigin
							.addClass("table_cell")
							.addClass("file_sharing_downloads_tracking");
						$dialogContentTableHead.append($dialogContentTableHeadOrigin);

						$dialogContentTable.append($dialogContentTableHead);

						var $dialogContentTableCellDate;
						var $dialogContentTableCellOrigin;

						fileDownloads.forEach(function(element, index) {
							var $dialogContentTableRow = $("<div></div>");
							$dialogContentTableRow
								.addClass("table_row")
								.addClass("file_sharing_downloads_tracking");
							var $dialogContentTableCellDate = $("<div></div>");
							$dialogContentTableCellDate
								.addClass("table_cell")
								.addClass("file_sharing_downloads_tracking");
							var $dialogContentTableCellOrigin = $("<div></div>");
							$dialogContentTableCellOrigin
								.addClass("table_cell")
								.addClass("file_sharing_downloads_tracking");

							$dialogContentTableCellDate.append(element.timestamp);
							$dialogContentTableCellOrigin.append(element.data.remote_addr);

							$dialogContentTableRow
								.append($dialogContentTableCellDate)
								.append($dialogContentTableCellOrigin);
							$dialogContentTable.append($dialogContentTableRow);
						});

						$dialogContent.append($dialogContentTable);
					}

					$dialogContent.dialog({
							modal: true,
							height: 400,
							width: 450,
							title: "<?php echo __('Downloads'); ?>",
							overlay: {
									opacity: 0.5,
									background: "black"
								},
							close: function(event, ui) {
								$(this).empty();
							}
						})
				});

			$("div.file_sharing_data_item.delete")
				.attr("title", "<?php echo __('Delete'); ?>")
				.click(function (e) {
					e.preventDefault();

					if (confirm("<?php echo __('Are you sure?'); ?>")) {
						$(this).find("img").attr("src", "images/spinner.gif");
						$.ajax({
							url: 'ajax.php',
							data: {
								page: 'operation/file_sharing/manage',
								deletePackage: 1,
								id: $(this).data("attachmentID")
							},
							type: 'POST',
							dataType: 'json',
							complete: function () {
								$("div.file_sharing_data_item.delete>img").attr("src", "images/cross.png");
							},
							fail: function () {
								alert("<?php echo __('An error occurred while deleting the file'); ?>");
							},
							success: function (data) {
								if (data) {
									listController.removeFile($(this).data("attachmentID"));
								}
								else {
									alert("<?php echo __('An error occurred while deleting the file'); ?>");
								}
							}
						});
					}
				});
			$("div.file_sharing_data_item.download")
				.attr("title", "<?php echo __('Download'); ?>")
				.click(function (e) {
					e.preventDefault();

					var url = "<?php echo $config['base_url']; ?>/operation/common/download_file.php?type=file_sharing&key=" + $(this).data('publicKey');
					window.open(url);
				});
			$("div.file_sharing_data_item.clipboard")
				.attr("title", "<?php echo __('Copy to clipboard'); ?>")
				.click(function (e) {
					e.preventDefault();

					var url = "<?php echo $config['base_url']; ?>/operation/common/download_file.php?type=file_sharing&key=" + $(this).data('publicKey');
					window.prompt("<?php echo __('Copy to clipboard') . '\n' . __('Press Ctrl+C (CMD+C on Mac OS X) and then Enter'); ?>", url);
				});
		}
	});
	
</script>