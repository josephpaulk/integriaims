<?php

// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2013 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

check_login ();

if (! $id) {
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to a lead forward");
	include ("general/noaccess.php");
	exit;
}

$write_permission = check_crm_acl ('lead', 'cw', $config['id_user'], $id);
$manage_permission = check_crm_acl ('lead', 'cm', $config['id_user'], $id);
if (!$write_permission && !$manage_permission) {
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access to a lead forward");
	include ("general/noaccess.php");
	exit;
}

if (defined ('AJAX')) {
	ob_clean();

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

				$ds = DIRECTORY_SEPARATOR;
				$destination = $config["homedir"].$ds."attachment".$ds."tmp".$ds.$result["name"];

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

$lead = get_db_row('tlead','id',$id);
$user = get_db_row("tusuario", "id_usuario", $config["id_user"]);
$company_user = get_db_sql ("select name FROM tcompany where id = ". $user["id_company"]);

$from = get_parameter ("from", $user["direccion"]);
$to = get_parameter ("to", "");
$subject = get_parameter ("subject", "");
$mail = get_parameter ("mail", "");
$send = (int) get_parameter ("send",0);
$cco = get_parameter ("cco", "");

// Send mail
if ($send) {
	if (($subject != "") AND ($from != "") AND ($to != "")) {
		echo "<h3 class='suc'>".__('Mail queued')."</h3>";

		// ATTACH A FILE IF IS PROVIDED
		$upfiles = (string) get_parameter("upfiles");
		$upfiles = json_decode(safe_output($upfiles));
		if (!empty($upfiles)) {
			// Save the attachments
			$bad_files = array();
			foreach ($upfiles as $key => $attachment) {
				$size = filesize ($attachment);
				$filename = basename($attachment);

				$values = array(
						'id_lead' => $id,
						'id_usuario' => $config["id_user"],
						'filename' => $filename,
						'description' => __('Mail attachment'),
						'timestamp' => date('Y-m-d H:i:s'),
						'size' => $size
					);
				$id_attachment = process_sql_insert('tattachment', $values);

				if ($id_attachment) {
					// Copy file to directory and change name
					$ds = DIRECTORY_SEPARATOR;
					$filename_encoded = $id_attachment . "_" . $filename;
					$file_target = $config["homedir"].$ds."attachment".$ds.$filename_encoded;

					if (!copy($attachment, $file_target)) {
						$bad_files[] = $key;
						unlink ($attachment);
						process_sql_delete('tattachment', array('id_attachment' => $id_attachment));
					}
				} else {
					$bad_files[] = $key;
					unlink ($attachment);
				}
			}
			foreach ($bad_files as $index) {
				unset($upfiles[$index]);
			}

			$upfiles = implode( ",", $upfiles);
		} else {
			$upfiles = false;
		}

		integria_sendmail ($to, $subject, $mail, $upfiles, "", $from, true);

		if ($cco != "")
			integria_sendmail ($cco, $subject, $mail, $upfiles, "", $from, true);

		$datetime =  date ("Y-m-d H:i:s");	
		// Update tracking
		$sql = sprintf ('INSERT INTO tlead_history (id_lead, id_user, timestamp, description) VALUES (%d, "%s", "%s", "%s")', $id, $config["id_user"], $datetime, "Forwarded lead by mail to $to");
		process_sql ($sql);

		// Update activity
		$comments = __("Forwarded lead by mail to $to"). "&#x0d;&#x0a;" . $mail; // this adds &#x0d;&#x0a; 
		$sql = sprintf ('INSERT INTO tlead_activity (id_lead, written_by, creation, description) VALUES (%d, "%s", "%s", "%s")', $id, $config["id_user"], $datetime, $comments);
		process_sql ($sql);

	} else {
		echo "<h3 class='error'>".__('Could not be created')."</h3>";
	}
}


// Mark with case ID
$subject = __("Lead forward"). " [#$id] : " . $lead["company"] . " / ". $lead["country"] ;



$mail = __("Hello");
$mail .= "\n\n";
$mail .= __("Lead details"). ":\n\n";
$mail .= " ".__("Name") . ": ". $lead["fullname"] . "\n";
$mail .= " ".__("Company") . ": ". $lead["company"] . "\n";
$mail .= " ".__("Position") . ": ". $lead["position"] . "\n";
$mail .= " ".__("Country") . ": ". $lead["country"] . "\n";
$mail .= " ".__("Language") . ": ". $lead["id_language"] . "\n";
$mail .= " ".__("Email") . ": ". $lead["email"] . "\n";
$mail .= " ".__("Phone") . ": ". $lead["phone"] . " / " .$lead["mobile"]. "\n";
$mail .= " ".__("Comments") . ": ". $lead["description"] . "\n";
$mail .= "\n\n";
$mail .= "--";
$mail .= "\n\t".$user["nombre_real"];
$mail .= "\n\t".$user["direccion"];
$mail .= "\n\t".$company_user;


$table->width = "99%";
$table->class = "search-table-button";
$table->data = array ();
$table->size = array ();
$table->style = array ();
$table->style[0] = 'font-weight: bold';

$table->colspan[1][0] = 3;
$table->colspan[2][0] = 3;
$table->colspan[3][0] = 3;
$table->colspan[4][0] = 3;

$table->data[0][0] = print_input_text ("from", $from, "", 30, 100, true, __('From'));
$table->data[0][1] = print_input_text ("to", $to, "", 30, 100, true, __('To'));
$table->data[0][2] = print_input_text ("cco", $cco, "", 30, 100, true, __('Send a copy to'));
$table->data[1][0] = print_input_text ("subject", $subject, "", 130, 100, true, __('Subject'));
$table->data[2][0] = print_textarea ("mail", 10, 1, $mail, 'style="height:350px;"', true, __('E-mail'));

$html = "<div id=\"lead_files\" class=\"fileupload_form\">";
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
$table->data[3][0] = print_container('attachment_upload_container', __('Attachments'), $html, 'closed', true, false);

$table->data[4][0] = print_submit_button (__('Send email'), 'apply_btn', false, 'class="sub upd"', true);
$table->data[4][0] .= print_input_hidden ('id', $id, true);
$table->data[4][0] .= print_input_hidden ('send', 1, true);


echo '<form method="post" id="lead_mail_go">';
print_table ($table);
echo "</form>";

?>

<script type="text/javascript" src="include/js/jquery.validate.js"></script>
<script type="text/javascript" src="include/js/jquery.validation.functions.js"></script>
<script type="text/javascript" src="include/js/jquery.fileupload.js"></script>
<script type="text/javascript" src="include/js/jquery.iframe-transport.js"></script>
<script type="text/javascript" src="include/js/jquery.knob.js"></script>

<script type="text/javascript" >

function form_upload () {
	// Input will hold the JSON String with the files data
	var input_upfiles = $('input#upfiles');
	// Javascript array will hold the files data
	var upfiles = [];

	function updateInputArray() {
		input_upfiles.val(JSON.stringify(upfiles));
	}

	$('#drop_file #browse_button').click(function() {
		// Simulate a click on the file input button to show the file browser dialog
		$("#file-upfile").click();
	});

	// Initialize the jQuery File Upload plugin
	$('#lead_files').fileupload({
		
		url: 'ajax.php?page=operation/leads/lead&op=forward&upload_file=true&id='+<?php echo $id; ?>,
		
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

				// Add the new element
				upfiles.push(result.location);
				updateInputArray();
				// Add a listener to remove the item in case of removing the list item
				data.context.find('span').click(function() {
					var index = upfiles.indexOf(result.location);
					if (index > -1) {
						$.ajax({
							type: 'POST',
							url: 'ajax.php',
							data: {
								page: "operation/incidents/lead",
								op: "forward",
								remove_tmp_file: true,
								location: upfiles[index],
								id: "<?php echo $id; ?>"
							}
						});
						upfiles.splice(index, 1);
						updateInputArray();
					}
				});
				
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
						'<div class="incident_file_form"></div>'+
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
		var item = tpl.appendTo($('#lead_files ul'));
		item.find('input').val(progress).change();

		return item;
	}
}

form_upload();

validate_form("#lead_mail_go");
// Rules: #text-from
rules = {
	required: true,
	email: true
};
messages = {
	required: "<?php echo __('Email from required')?>",
	email: "<?php echo __('Invalid email')?>"
};
add_validate_form_element_rules('#text-from', rules, messages);
// Rules: #text-to
rules = {
	required: true,
	email: true
};
messages = {
	required: "<?php echo __('Email to required')?>",
	email: "<?php echo __('Invalid email')?>"
};
add_validate_form_element_rules('#text-to', rules, messages);
// Rules: #text-cco
rules = {
	email: true
};
messages = {
	email: "<?php echo __('Invalid email')?>"
};
add_validate_form_element_rules('#text-cco', rules, messages);

</script>
