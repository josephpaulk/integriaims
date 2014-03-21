<?php

// Integria IMS - http://integriaims.com
// ==================================================
// Copyright (c) 2008-2010 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;

check_login ();

$id = (int) get_parameter ('id');
$incident_creator = get_db_value ("id_creator", "tincidencia", "id_incidencia", $id);

$incident = get_db_row ('tincidencia', 'id_incidencia', $id);

//user with IR and incident creator see the information
$check_acl = enterprise_hook("incidents_check_incident_acl", array($incident));
$external_check = enterprise_hook("manage_external", array($incident));

if (($check_acl !== ENTERPRISE_NOT_HOOK && !$check_acl) || ($external_check !== ENTERPRISE_NOT_HOOK && !$external_check)) {
 	// Doesn't have access to this page
	audit_db ($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation",
		'Trying to access files of ticket #'.$id." '".$titulo."'");
	include ("general/noaccess.php");
	exit;
}

if (!$id) {
	audit_db ($config['id_user'], $REMOTE_ADDR, "ACL Violation",
		"Trying to access files of ticket #".$id);
	include ("general/noaccess.php");
	exit;
}

//Upload new file
$filename = get_parameter ('upfile', false);
if ((give_acl ($config['id_user'], $id_grupo, "IR") || 
	$config['id_user'] == $incident_creator) 
	&& (bool)$filename) {
	$result_msg = '<h3 class="error">'.__('No file was attached').'</h3>';
	/* if file */
	if ($filename != "") {
		$file_description = get_parameter ("file_description",
				__('No description available'));
		
		// Insert into database
		$filename_real = safe_output ( $filename ); // Avoid problems with blank spaces
		$file_temp = sys_get_temp_dir()."/$filename_real";
		$file_new = str_replace (" ", "_", $filename_real); // Replace blank spaces
		$file_new = filter_var($file_new, FILTER_SANITIZE_URL); // Replace conflictive characters
		$filesize = filesize($file_temp); // In bytes

		$sql = sprintf ('INSERT INTO tattachment (id_incidencia, id_usuario,
				filename, description, size)
				VALUES (%d, "%s", "%s", "%s", %d)',
				$id, $config['id_user'], $file_new, $file_description, $filesize);

		$id_attachment = process_sql ($sql, 'insert_id');
		incident_tracking ($id, INCIDENT_FILE_ADDED);
		echo '<h3 class="suc">'.__('File added').'</h3>';
		// Email notify to all people involved in this incident
		if ($email_notify == 1) {
			if ($config["email_on_incident_update"] == 1){
				mail_incident ($id, $config['id_user'], 0, 0, 2);
			}
		}
		
		// Copy file to directory and change name
		$file_target = $config["homedir"]."/attachment/".$id_attachment."_".$file_new;
		
		if (! copy ($file_temp, $file_target)) {
			echo '<h3 class="error">'.__('File cannot be saved. Please contact Integria administrator about this error').'</h3>';
			$sql = sprintf ('DELETE FROM tattachment
					WHERE id_attachment = %d', $id_attachment);
			process_sql ($sql);
		} else {
			// Delete temporal file
			unlink ($file_temp);

			$link = "<a target='_blank' href='operation/common/download_file.php?type=incident&id_attachment=".$id_attachment."'>".$filename."</a>";

			// Adding a WU noticing about this
			$nota = "Automatic WU: Added a file to this issue. Filename uploaded: ". $link;
			$public = 1;
			$timestamp = print_mysql_timestamp();
			$timeused = "0";
			$sql = sprintf ('INSERT INTO tworkunit (timestamp, duration, id_user, description, public) VALUES ("%s", %.2f, "%s", "%s", %d)', $timestamp, $timeused, $config['id_user'], $nota, $public);

			$id_workunit = process_sql ($sql, "insert_id");
			$sql = sprintf ('INSERT INTO tworkunit_incident (id_incident, id_workunit) VALUES (%d, %d)', $id, $id_workunit);
			process_sql ($sql);

			$sql = sprintf ('UPDATE tincidencia SET actualizacion = "%s" WHERE id_incidencia = %d', $timestamp, $id);
			process_sql ($sql);
		}
	}  else {
		//~ $error = $_FILES['userfile']['error'];
		$error = 4;
		switch ($error) {
		case 1:
			$result_msg = '<h3 class="error">'.__('File is too big').'</h3>';
			break;
		case 3:
			$result_msg = '<h3 class="error">'.__('File was partially uploaded. Please try again').'</h3>';
			break;
		case 4:
			$result_msg = '<h3 class="error">'.__('No file was uploaded').'</h3>';
			break;
		default:
			$result_msg = '<h3 class="error">'.__('Generic upload error').'(Code: '.$_FILES['userfile']['error'].')</h3>';
		}
		
		echo $result_msg;
	}
}

// Delete file
$delete_file = (bool) get_parameter ('delete_file');
if ($delete_file) {
	if (give_acl ($config['id_user'], $id_grupo, "IM")) {
		$id_attachment = get_parameter ('id_attachment');
		$filename = get_db_value ('filename', 'tattachment',
			'id_attachment', $id_attachment);
		$sql = sprintf ('DELETE FROM tattachment WHERE id_attachment = %d',
			$id_attachment);
		process_sql ($sql);
		$result_msg = '<h3 class="suc">'.__('Successfully deleted').'</h3>';
		if (!unlink ($config["homedir"].'attachment/'.$id_attachment.'_'.$filename))
			$result_msg = '<h3 class="error">'.__('Could not be deleted').'</h3>';
		incident_tracking ($id, INCIDENT_FILE_REMOVED);
		
	} else {
		$result_msg = '<h3 class="error">'.__('You have no permission').'</h3>';
	}
	
	echo $result_msg;
}

if (!$clean_output) {
	echo '<div id="upload_result"></div>';

	//echo "<h3>".__('Add file')."</h3>";

	echo "<div id='upload_control' style='width: 80%;margin: 0 auto;'>";

	$table->width = '100%';
	$table->data = array ();


	$table->data[0][0] = "<strong>".__("File formats supported")."</strong>";
	$table->data[0][0] .= print_help_tip (__("Please note that you cannot upload .php or .pl files, as well other source code formats. Please compress that files prior to upload (using zip or gz)"), true);
	$table->data[1][0] = print_textarea ('file_description', 8, 1, '', "style='resize:none'", true, __('Description'));


	$action = 'index.php?sec=incidents&sec2=operation/incidents/incident_dashboard_detail&id='.$id.'&tab=files#incident-operations';

	$into_form = print_table ($table, true);
	$into_form .= '<div class="button" style="width: '.$table->width.'">';
	$into_form .= print_button (__('Upload'), 'upload', false, '', 'class="sub next"', true);
	$into_form .= '</div>';
	$into_form .= print_input_hidden ('id', $id, true);
	$into_form .= print_input_hidden ('upload_file', 1, true);

	// Important: Set id 'form-add-file' to form. It's used from ajax control
	print_input_file_progress($action, $into_form, 'id="form-add-file"', 'sub', 'button-upload');

	echo '</div>';
}


if ($clean_output) {
	echo '<h1 class="ticket_clean_report_title">'.__("Files")."</h1>";
} else {
	echo "<h3>".__('Files')."</h3>";
}

// Files attached to this incident
$files = get_incident_files ($id);
if ($files === false) {
	echo '<h4>'.__('No files were added to the incidence').'</h4>';
	return;
}

echo "<div style='width: 90%; margin: 0 auto;'>";
echo "<table class=listing cellpadding=0 cellspacing=0 width='100%'>";
echo "<tr>";
echo "<th>".__('Filename');
echo "<th>".__('Timestamp');
echo "<th>".__('Description');
echo "<th>".__('ID user');
echo "<th>".__('Size');

if (give_acl ($config['id_user'], $incident['id_grupo'], "IM") && !$clean_output) {
	echo "<th>".__('Delete');
}

foreach ($files as $file) {

     $link = "operation/common/download_file.php?id_attachment=".$file["id_attachment"]."&type=incident";

     $real_filename = $config["homedir"]."/attachment/".$file["id_attachment"]."_".rawurlencode ($file["filename"]);    

    echo "<tr>";
    echo "<td valign=top>";
	echo '<a target="_blank" href="'.$link.'">'. $file['filename'].'</a>';

    $stat = stat ($real_filename);
    echo "<td valign=top class=f9>".date ("Y-m-d H:i:s", $stat['mtime']);

    echo "<td valign=top class=f9>". $file["description"];
    echo "<td valign=top>". $file["id_usuario"];
    echo "<td valign=top>". byte_convert ($file['size']);

	// Delete attachment
	if (give_acl ($config['id_user'], $incident['id_grupo'], 'IM') && !$clean_output) {
		    echo "<td>". '<a class="delete" name="delete_file_'.$file["id_attachment"].'" href="index.php?sec=incidents&sec2=operation/incidents/incident_dashboard_detail&id='.$id.'&tab=files&id_attachment='.$file["id_attachment"].'&delete_file=1#incident-operations">
			<img src="images/cross.png"></a>';
	}

}

echo "</table>";
echo "</div>";
?>

<script type="text/javascript">
//~ $('a[name^="delete_file_"]').click(function() {
	//~ id_attachment = $(this).attr('name').split('_')[2];
	//~ row = $(this).parent().parent();
	//~ 
	//~ values = Array ();
	//~ values.push ({name: "page", value: "operation/incidents/incident_detail"});
	//~ values.push ({name: "delete_file", value: 1});
	//~ values.push ({name: "id_attachment", value: id_attachment});
	//~ values.push ({name: "id", value: <?php echo $id_incident; ?>});
	//~ 
	//~ jQuery.get ("ajax.php",
		//~ values,
		//~ function (data, status) {
			//~ // If the return is succesfull we hide the deleted file row
			//~ if(data.search('class="error"') == -1) {
				//~ row.hide();
			//~ }
			//~ $(".result").html(data);
		//~ },
		//~ "html"
	//~ );
	//~ return false;
//~ 
//~ });
</script>
