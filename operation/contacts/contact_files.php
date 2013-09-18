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

check_login ();

include_once('include/functions_crm.php');

$id = (int) get_parameter ('id');

$contact = get_db_row ('tcompany_contact', 'id', $id);

$read = check_crm_acl ('other', 'cr', $config['id_user'], $contact['id_company']);
if (!$read) {
	audit_db($config["id_user"], $config["REMOTE_ADDR"], "ACL Violation","Trying to access to contact files without permission");
	include ("general/noaccess.php");
	exit;
}

//Upload new file
$filename = get_parameter ('upfile', false);
if (give_acl ($config['id_user'], 0, "CR") && (bool)$filename) {
        $result_msg = '<h3 class="error">'.__('No file was attached').'</h3>';
        /* if file */
        if ($filename != "") {
                $file_description = get_parameter ("file_description",
                                __('No description available'));

                // Insert into database
                $filename_real = safe_output ( $filename ); // Avoid problems with blank spaces
                $file_temp = sys_get_temp_dir()."/$filename_real";
                $file_new = str_replace (" ", "_", $filename_real);
                $filesize = filesize($file_temp); // In bytes

                $sql = sprintf ('INSERT INTO tattachment (id_contact, id_usuario,
                                filename, description, size)
                                VALUES (%d, "%s", "%s", "%s", %d)',
                                $id, $config['id_user'], $file_new, $file_description, $filesize);

                $id_attachment = process_sql ($sql, 'insert_id');
                
		echo '<h3 class="suc">'.__('File added').'</h3>';

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

        } else {
                $result_msg = '<h3 class="error">'.__('You have no permission').'</h3>';
        }

        echo $result_msg;
}

echo '<div id="upload_result"></div>';

//echo "<h3>".__('Add file')."</h3>";

echo "<div id='upload_control' style='width: 80%;margin: 0 auto; clear:both'>";

$table->width = '100%';
$table->data = array ();


$table->data[0][0] = "<strong>".__("File formats supported")."</strong>";
$table->data[0][0] .= print_help_tip (__("Please note that you cannot upload .php or .pl files, as well other source code formats. Please compress that files prior to upload (using zip or gz)"), true);
$table->data[1][0] = print_textarea ('file_description', 8, 1, '', "style='resize:none'", true, __('Description'));

$action = 'index.php?sec=customers&sec2=operation/contacts/contact_detail&id='.$id.'&op=files';

$into_form = print_table ($table, true);
$into_form .= '<div class="button" style="width: '.$table->width.'">';
$into_form .= print_button (__('Upload'), 'upload', false, '', 'class="sub next"', true);
$into_form .= '</div>';
$into_form .= print_input_hidden ('id', $id, true);
$into_form .= print_input_hidden ('upload_file', 1, true);

// Important: Set id 'form-add-file' to form. It's used from ajax control
print_input_file_progress($action, $into_form, 'id="form-add-file"', 'sub', 'button-upload');

echo '</div>';

$files = crm_get_contact_files ($id);

echo "<h3>".__('Files')."</h3>";

if (!$files) {
	echo '<h3 class="error">'.__("This contact doesn't have any file associated").'</h3>';
} else {

	$table->class = "listing";
	$table->width = "99%";
	$table->head[0] = __("Filname");
	$table->head[1] = __("Timestamp");
	$table->head[2] = __("Description");
	$table->head[3] = __("Size");

	if (give_acl ($config['id_user'], 0, "CW")) {
        	$table->head[4] = __('Delete');
	}
	
	$table->data = array();

	foreach ($files as $f) {
		$data = array();

     	$link = "operation/common/download_file.php?id_attachment=".$f["id_attachment"]."&type=contact";

        $data[0] = '<a target="_blank" href="'.$link.'">'. $f['filename'].'</a>';	
		
        $real_filename = $config["homedir"]."/attachment/".$f["id_attachment"]."_".rawurlencode ($f["filename"]);    
        
        $stat = stat ($real_filename);
        $data[1] = date ("Y-m-d H:i:s", $stat['mtime']);

		$data[2] = $f["description"];

		$data[3] = byte_convert ($f['size']);

	        if (give_acl ($config['id_user'], 0, "CW")) {
                	$data[4] = '<a class="delete" name="delete_file_'.$f["id_attachment"].'" href="index.php?sec=customers&sec2=operation/contacts/contact_detail&id='.$id.'&op=files&id_attachment='.$f["id_attachment"].'&delete_file=1"><img src="images/cross.png"></a>';
        	}

		array_push($table->data, $data);
	}

	print_table($table);
}
?>
