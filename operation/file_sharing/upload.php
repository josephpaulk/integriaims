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

	if (defined("AJAX"))
    	return;
    else
    	exit;
}

require_once($config['homedir']."/operation/file_sharing/FileSharingFile.class.php");
require_once($config['homedir']."/operation/file_sharing/FileSharingPackage.class.php");

// AJAX START
if (defined("AJAX")) {
	ob_clean();

	$get_uniqid = (bool) get_parameter("getUniqid");

	if ($get_uniqid) {
		$uniqid = uniqid();

		echo $uniqid;
		return;
	}

	$upload_file = (bool) get_parameter("uploadFile");

	if ($upload_file) {
		$result = array(
				'status' => false,
				'message' => '',
				'file' => ''
			);

		$tmp_id = get_parameter("uniqid");

		$upload_status = getFileUploadStatus("upfile");
		$upload_result = translateFileUploadStatus($upload_status);
		
		if ($upload_result) {

			$tmp_dir = sys_get_temp_dir() . "/" . $tmp_id;
			if (!file_exists($tmp_dir) && !is_dir($tmp_dir)) {
				mkdir($tmp_dir);
			}
			
			$path = $_FILES['upfile']['tmp_name'];
			// The package files will be saved in [user temp dir]/tmp_id
			$destination = $tmp_dir."/".$_FILES['upfile']['name'];
			// files.txt will have the names of every file of the package
			if (!move_uploaded_file($path, $destination)) {
				unlink($path);
				$result["message"] = __("The file was not stored succesfully.");
			}
			else {

				// The admin can manage the file uploads as any user
				$user_is_admin = (bool) dame_admin($config['id_user']);
				if ($user_is_admin) {
					$id_user = get_parameter("id_user", $config['id_user']);
					// If the user doesn't exist get the current user
					$user_data = get_user($id_user);
					if (empty($user_data))
						$id_user = $config['id_user'];
				}
				else {
					$id_user = $config['id_user'];
				}

				$file_data = array(
						'uploader' => $id_user,
						'created' => time()
					);
				$file = new FileSharingFile($file_data);
				$file->loadFileInfo($destination);

				$result["file"] = $file->toArray(true);
				$result["status"] = true;
			}
		}
		else {
			$result["message"] = $upload_result;
		}

		echo json_encode($result);
		return;
	}

	$build_package = (bool) get_parameter("buildPackage");

	if ($build_package) {
		$result = array(
				'status' => false,
				'message' => '',
				'package' => array()
			);

		$package_data = (array) get_parameter("package");

		if (!empty($package_data)) {
			// The admin can manage the file uploads as any user
			$user_is_admin = (bool) dame_admin($config['id_user']);
			if ($user_is_admin) {
				$id_user = get_parameter("id_user", $config['id_user']);
				// If the user doesn't exist get the current user
				$user_data = get_user($id_user);
				if (empty($user_data))
					$id_user = $config['id_user'];
				$package_data['uploader'] = $id_user;
			}
			else {
				$id_user = $config['id_user'];
				$package_data['uploader'] = $id_user;
			}

			$package = new FileSharingPackage($package_data);
			$save_res = $package->save();
			if ($save_res['status']) {
				$result['status'] = true;
				$result['package'] = $package->toArray();
			}
			else {
				$result['message'] = __("There was an error while building the package");
			}
		}
		else {
			$result['message'] = __("There was an error with the received package information");
		}

		echo json_encode($result);
		return;
	}

	return;
}
// AJAX END

if (!isset($upload_mb)) {
	$max_upload = (float) ini_get('upload_max_filesize');
	$max_post = (float) ini_get('post_max_size');
	$memory_limit = (float) ini_get('memory_limit');
	$upload_mb = min($max_upload, $max_post, $memory_limit);
}

?>

<form id="form-file_releases" class="file_sharing fileupload_form" method="post" enctype="multipart/form-data">
	<div id="drop_file" style="padding:0px;margin-bottom: 10px;border: 5px solid rgba(0, 0, 0, 0);" >
		<table width="100%">
			<tr>
				<td>
					<?php echo __('Drop the files here or'); ?>
				</td>
			</tr>
			<tr>
				<td>
					<a id="browse_button">
						<?php echo __('browse it') ?>
					</a>
				</td>
			</tr>
		</table>
		<input name="upfile" type="file" id="file-upfile" class="sub file" />
	</div>
	<ul></ul>
</form>

<script src="include/js/jquery.fileupload.js"></script>
<script src="include/js/jquery.iframe-transport.js"></script>
<script src="include/js/jquery.knob.js"></script>
<script src="<?php echo $config['base_url']; ?>/operation/file_sharing/FileSharingAdapters.js"></script>
<script src="<?php echo $config['base_url']; ?>/operation/file_sharing/FileSharingListController.js"></script>

<script type="text/javascript">

	$(document).ready (function () {
		bind_file_upload();
	});

	function bind_file_upload () {

		// The admin can manage the file uploads as any user
		var userID = "<?php echo (dame_admin($config['id_user']) ? get_parameter('id_user', $config['id_user']) : $config['id_user']); ?>";

		// The jQuery object of the html list
		var $fileList = $('#form-file_releases ul');

		//
		var tmpPackage = new FileSharingPackage({
			uploader: userID,
			filename: '',
			exists: false,
			files: []
		});

		var finalPackage = new FileSharingPackage({
			uploader: userID,
			filename: '',
			exists: false,
			files: []
		});

		$('#drop_file #browse_button').click(function() {
			// Simulate a click on the file input button to show the file browser dialog
			$("#file-upfile").click();
		});

		// Initialize the jQuery File Upload plugin
		$('#form-file_releases').fileupload({
			
			url: 'ajax.php',

			// This element will accept file drag/drop uploading
			dropZone: $('#drop_file'),

			// This function is called when a file is added to the queue;
			// either via the browse button, or via drag/drop:
			add: function (e, data) {
				if (data.files[0].size / 1000000 <= <?php echo json_encode($upload_mb); ?>) {
					if ($('#drop_file').is(":visible"))
						data.context = addListItem(data);
				}
				else {
					alert("<?php echo __('Warning') . '. ' . sprintf(__('The file size should be smaller than %sMB'), $upload_mb); ?>");
				}
			},

			progressall: function(e, data) {
				// Calculate the completion percentage of the upload
				var progress = parseInt(data.loaded / data.total * 100, 10);

				// Update the hidden input field and trigger a change
				// so that the jQuery knob plugin knows to update the dial
				$("li#submit-controls")
					.find('input#input-progress')
						.val(progress)
						.change();
			},

			fail: function(e, data) {
				// Something has gone wrong!
				abortUploads();
				if (data.textStatus != "abort")
					alert("<?php echo __('An error occurred uploading a file'); ?>");
			},
			
			done: function (e, data) {
				try {
					var result = JSON.parse(data.result);

					if (result.status == true) {
						// Add the uploaded file to the final package structure
						finalPackage.files.push(result.file);

						// All files uploaded
						if (finalPackage.files.length == tmpPackage.files.length) {
							createPackage(finalPackage);
							finalPackage = new FileSharingPackage({
								uploader: userID,
								filename: '',
								exists: false,
								files: []
							});
						}
					}
					else {
						// Something has gone wrong!
						abortUploads();
						alert("<?php echo __('Error'); ?>. " + result.message);
					}
				}
				catch (error) {
					// Something has gone wrong!
					abortUploads();
					alert("<?php echo __('An error occurred uploading a file'); ?>");
				}
			}

		});

		// Prevent the default action when a file is dropped on the window
		$(document).on('drop_file dragover', function (e) {
			e.preventDefault();
		});

		function addListItem (data) {
			var filename = data.files[0].name;
			var filesize = data.files[0].size;

			var fsFileIndex = -1;
			var fsFile = new FileSharingFile({
				uploader: userID,
				basename: filename,
				size: filesize,
				exists: false
			});

			var $liItem = $("<li class=\"submit-item\"></li>");
			$liItem
				.data('file', filename)
				.append("<p></p>")
				.append("<span></span>")
				.addClass('working');

			// Append the file name and file size
			$liItem.find('p').text(filename);
			if (filesize > 0) {
				$liItem.find('p').append('<i>' + formatFileSize(filesize) + '</i>');
			}

			// Listen for clicks on the cancel icon
			$liItem.find('span').click(function() {
				var jqXHR = tmpPackage.files[fsFileIndex].jqXHR;

				if (typeof jqXHR != 'undefined') {
					jqXHR.abort();
				}
				
				$liItem.fadeOut();
				$liItem.slideUp(500, "swing", function() {
					$liItem.remove();
				});

				if (fsFileIndex > -1) {
					tmpPackage.files.splice(fsFileIndex, 1);
				}

				if (tmpPackage.files.length <= 0)
					removeSubmitControls();
			});

			if (tmpPackage.files.length <= 0)
				addSubmitControls();
			
			// Add the HTML to the UL element
			$liItem.hide();
			var item = $liItem.appendTo($fileList);
			$liItem.slideDown();

			fsFile.data = data;
			fsFile.htmlItem = item;
			fsFileIndex = tmpPackage.files.push(fsFile) - 1;
			tmpPackage.size += filesize;

			return item;
		}

		function startUploads () {
			if (typeof tmpPackage.files != 'undefined' && tmpPackage.files.length > 0) {
				// Disable the file add
				$('#drop_file').slideUp();

				// Set package name
				finalPackage.filename = $('li#submit-controls').find('input#input-name').val();

				if (finalPackage.filename.length <= 0
						&& tmpPackage.files.length == 1
						&& typeof tmpPackage.files[0].basename != 'undefined') 
					finalPackage.filename = tmpPackage.files[0].basename;

				// Get uniqid
				$.ajax({
					type: 'POST',
					url: 'ajax.php',
					data: {
						page: "operation/file_sharing/upload",
						getUniqid: 1
					},
					dataType: "text",
					success: function (data) {
						var uniqid = "tmpIntegriaFileSharing" + data;

						// Add extra information to the upload form
						$('#form-file_releases').fileupload({
							formData: {
								page: 'operation/file_sharing/upload',
								uploadFile: 1,
								uniqid: uniqid,
								id_user: userID
							}
						});

						// Upload all files
						tmpPackage.files.forEach(function(element, index) {
							tmpPackage.files[index].jqXHR = element.data.submit();
							element.htmlItem
								.find('span')
									.remove();
						});
						
						$('li#submit-controls')
							.find('input#input-name')
								.prop('disabled', true);
						$('li#submit-controls')
							.find('input#input-submit')
								.remove();
						$('li#submit-controls')
							.find('img#input-spinner')
								.show();
					}
				});
			}
		}

		function abortUploads () {
			if (typeof tmpPackage.files != 'undefined' && tmpPackage.files.length > 0) {
				$fileList.fadeOut();
				$fileList.slideUp(500, "swing", function() {
					$fileList.empty();
					$fileList.slideDown(10, "swing", function() {
						$fileList.fadeIn();
					});
				});
				tmpPackage.files.forEach(function(element, index) {
					var jqXHR = element.jqXHR;
					if (typeof jqXHR != 'undefined') {
						jqXHR.abort();
					}
				});
				tmpPackage.files = [];
			}
			$('#drop_file').slideDown();
		}

		function addSubmitControls () {
			var $liSubmit = $("<li></li>");
			$liSubmit.attr("id", "submit-controls");

			var $inputProgress = $("<input />");
			$inputProgress
				.val(0)
				.attr("type", "text")
				.attr("id", "input-progress")
				.attr("data-readOnly", "1")
				.attr("data-fgColor", "#FF9933")
				.attr("data-bgColor", "#3e4043")
				.data("width", 55)
				.data("height", 55);

			var $inputName = $("<input />");
			$inputName
				.val('')
				.attr("type", "text")
				.attr("id", "input-name")
				.attr("placeholder", "<?php echo __('Package name'); ?>");

			var $inputButton = $("<input />");
			$inputButton
				.val("<?php echo __('Upload'); ?>")
				.attr("type", "button")
				.attr("id", "input-submit");

			var $inputSpinner = $("<img />");
			$inputSpinner
				.attr("id", "input-spinner")
				.attr("src", "<?php echo $config['base_url'] . '/images/spinner.gif' ?>")
				.hide();

			var $inputStatus = $("<span></span>");

			$liSubmit
				.append($inputProgress)
				.append($inputName)
				.append($inputButton)
				.append($inputSpinner)
				.append($inputStatus)
				.addClass('working');

			// Initialize the knob plugin
			$liSubmit.find('input#input-progress').knob({
				'draw' : function () {
					$(this.i).val(this.cv + '%')
				}
			});

			$liSubmit.find('input#input-submit').click(function() {
				startUploads();
			});

			// Listen for clicks on the cancel icon
			$liSubmit.find('span').click(function() {
				if ($liSubmit.hasClass('working') || $liSubmit.hasClass('error') || $liSubmit.hasClass('suc')) {
					removeSubmitControls();
				}
			});
			
			// Add the HTML to the UL element
			$liSubmit.hide();
			var item = $liSubmit.prependTo($fileList);
			$liSubmit.slideDown();

			return item;
		}

		function removeSubmitControls () {
			abortUploads();
			var $submitControls = $("li#submit-controls");
			$submitControls.fadeOut();
			$submitControls.slideUp(500, "swing", function() {
				$submitControls.remove();
			});
		}

		// Builds the final package and adds it to the list
		function createPackage (fsPackage) {
			$.ajax({
				type: 'POST',
				url: 'ajax.php',
				data: {
					page: "operation/file_sharing/upload",
					buildPackage: 1,
					package: fsPackage,
					id_user: userID
				},
				dataType: "json",
				complete: function (data) {
					removeSubmitControls();
				},
				success: function (data) {
					if (typeof data.status != 'undefined' && data.status == true) {
						if (typeof listController != 'undefined')
							listController.addFile(data.package);
					}
					else {
						if (typeof data.message != 'undefined' && data.message.length > 0) {
							alert(data.message);
						}
						else {
							alert("<?php __('There was an error while building the package'); ?>");
						}
					}
				}
			});
		}

	}

</script>