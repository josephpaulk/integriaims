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

// Load global vars
global $config;
include_once('include/functions_setup.php');

check_login ();

if (defined ('AJAX')) {

	$onchange_template = get_parameter('onchange_template', 0);
	$onchange_actions  = get_parameter('onchange_actions', 0);
	
	if($onchange_template){
		$template_name = get_parameter('template_name');	
		$full_filename = "include/mailtemplates/" . $template_name;
		$data[0] = html_entity_decode(file_get_contents ($full_filename));

		$template_name_substr = substr($template_name,0,-4);
		$data[1] = get_db_value('id_group', 'temail_template', 'name', $template_name_substr);
		$data[2] = get_db_value('template_action', 'temail_template', 'name', $template_name_substr);
		$data[3] = get_db_value('predefined_templates', 'temail_template', 'name', $template_name_substr);

	echo json_encode($data);
	return;
	}
}	
if (! dame_admin ($config["id_user"])) {
	audit_db ("ACL Violation", $config["REMOTE_ADDR"], "No administrator access", "Trying to access setup");
	require ("general/noaccess.php");
	exit;
}

$is_enterprise = false;
if (file_exists ("enterprise/load_enterprise.php")) {
	$is_enterprise = true;
}
	
/* Tabs list */
print_setup_tabs('mailtemplates', $is_enterprise);

//values for defect
$create_template = get_parameter('create_template', 0);
$update_template = get_parameter('update_template', 0);
$template_name   = get_parameter('template_name', '');
$action_template = get_parameter('template_action', 0);
$template_group  = get_parameter('template_group', 0);
$data            = get_parameter('data', '');
$edit            = get_parameter('edit', 0);

//function create templates fixed
function get_template_action () {
	$action_template[0]  = __('Create incident body'); //[incident_create.tpl] => incident_create.tpl
	$action_template[1]  = __('Create incident subject'); //[incident_subject_create.tpl] => incident_subject_create.tpl
	
	$action_template[2]  = __('Close incident body'); //[incident_close.tpl] => incident_close.tpl
	$action_template[3]  = __('Close incident subject'); //[incident_subject_close.tpl] => incident_subject_close.tpl
	
	$action_template[4]  = __('Attach incident subject'); //[incident_subject_attach.tpl] => incident_subject_attach.tpl
	
	$action_template[5]  = __('Delete incident subject'); //[incident_subject_delete.tpl] => incident_subject_delete.tpl
	
	$action_template[6]  = __('New WU incident subject'); //[incident_subject_new_wu.tpl] => incident_subject_new_wu.tpl
	
	$action_template[7]  = __('Update WU incident body'); //[incident_update_wu.tpl] => incident_update_wu.tpl

	$action_template[8]  = __('Update incident subject'); //[incident_subject_update.tpl] => incident_subject_update.tpl
	$action_template[9]  = __('Update incident body'); //[incident_update.tpl] => incident_update.tpl
	
	$action_template[10] = __('SLA max inactivity time incident body'); //[incident_sla_max_inactivity_time.tpl] => incident_sla_max_inactivity_time.tpl
	$action_template[11] = __('SLA max inactivity time incident subject'); //[incident_sla_max_inactivity_time_subject.tpl] => incident_sla_max_inactivity_time_subject.tpl
	$action_template[12] = __('SLA max response time incident body'); //[incident_sla_max_response_time.tpl] => incident_sla_max_response_time.tpl
    $action_template[13] = __('SLA max response time incident subject'); //[incident_sla_max_response_time_subject.tpl] => incident_sla_max_response_time_subject.tpl
    
    $action_template[14] = __('SLA min response time incident body'); //[incident_sla_min_response_time.tpl] => incident_sla_min_response_time.tpl
    $action_template[15] = __('SLA min response time incident subject'); //[incident_sla_min_response_time_subject.tpl] => incident_sla_min_response_time_subject.tpl
    
    //$action_template[16] = __('SLA WU create project subject'); //[project_subject_wucreate.tpl] => project_subject_wucreate.tpl
    //$action_template[17] = __('SLA WU create project body'); //[project_wu_create.tpl] => project_wu_create.tpl
    
    //$action_template[18] = __('SLA WU update project subject'); //[project_subject_wuupdate.tpl] => project_subject_wuupdate.tpl
    //$action_template[19] = __('SLA WU update project body'); //[project_wu_update.tpl] => project_wu_update.tpl
	
	return $action_template;
}

//update template
if ($update_template) {

	$id = get_parameter('id', '');
	$update_values["name"] = substr(get_parameter('template_name', ''), 0, -4);
	$update_values["id_group"] = get_parameter('template_group', 0);
	$update_values['template_action'] = get_parameter('template_action', '');
	$template_name = $update_values["name"];

	$data =  unsafe_string (str_replace ("\r\n", "\n", get_parameter("template_content","")));
	$file = "include/mailtemplates/".$template_name.".tpl";

	$fileh = fopen ($file, "wx");
	
	if (fwrite ($fileh, $data)){
		$predefined_templates = get_db_value('predefined_templates', 'temail_template', 'name', $update_values["name"]);
    	if($predefined_templates == 0){
    		$id = get_db_value('id', 'temail_template', 'name', $update_values["name"]);
    		$result = process_sql_update('temail_template', $update_values, array('id'=>$id));
		} else {
			$result = 1;
		}
		if($result != false){
			echo ui_print_success_message (__('File successfully updated'), '', true, 'h3', true);
		} else {
			echo ui_print_error_message(__('Problem updating file'), '', true, 'h3', true);
		}
    } else {
    	echo ui_print_error_message(__('Problem updating file ff'), '', true, 'h3', true);
    }
	fclose ($fileh);
}

//create template
if ($create_template) {
	$insert_values["name"] = get_parameter('template_name', '');
	$insert_values["id_group"] = get_parameter('template_group', 0);
	$insert_values['template_action'] = get_parameter('template_action', '');
	$template_name = $insert_values["name"];
	$template_group = $insert_values["id_group"];

	$data =  unsafe_string (str_replace ("\r\n", "\n", get_parameter("template_content", "")));
	$file = "include/mailtemplates/".$template_name.".tpl";
	$fileh = fopen ($file, "w");

	if (fwrite ($fileh, $data)){
		$template_id = process_sql_insert("temail_template", $insert_values);
		if ($template_id != false) {
			echo ui_print_success_message (__('File successfully created'), '', true, 'h3', true);
		} else {
			echo ui_print_error_message(__('Problem creating file'), '', true, 'h3', true);
		}
	} else {
		echo ui_print_error_message(__('Problem creating file ff'), '', true, 'h3', true);
	}
	fclose ($fileh);
	chmod($file, 0777);
}

echo '<a href="index.php?sec=godmode&sec2=godmode/setup/setup_mailtemplates">'.__("Create") . integria_help ("macros", true).'</a>';
echo "&nbsp;&nbsp;&nbsp;";
echo '<a href="index.php?sec=godmode&sec2=godmode/setup/setup_mailtemplates&edit=1">'.__("Edit") . integria_help ("macros", true).'</a>';

$table = new StdClass();
$table->width = '100%';
$table->class = 'search-table-button';
$table->colspan = array ();
$table->colspan[1][0] = 3;
$table->data = array ();

if ($update_template || $edit) {
	$templatelist = get_template_files ('');
	$table->data[0][0] = print_select ($templatelist, 'template_name', $template_name, '', '', '',  true, 0, true, __('Name'),false, "");
} else {
	$table->data[0][0] = print_input_text ('template_name', $template_name, '', 60, 100, true, __('Name'), false);
}

$groups = get_db_all_rows_sql ("SELECT id_grupo, nombre FROM tgrupo ORDER BY nombre");
if ($groups == false) {
	$groups = array();
}
$user_groups = array();
foreach ($groups as $group) {
	$user_groups[$group['id_grupo']] = $group['nombre']; 
}

$templatelist = get_template_action();
$table->data[0][1] = print_select ($templatelist, 'template_action', $action_template,'', '', '',  true, 0, true, __('Actions'),false, "");

$table->data[0][2] = print_select ($user_groups, "template_group", $template_group, '', '', 0, true, false, false, __('Group'), false) . "<div id='group_spinner'></div>"; 

$table->data[1][0] = print_textarea ("template_content", 30, 44, $data,'', true, __('Template contents'));

if ($update_template || $edit) {
	$url = "index.php?sec=godmode&sec2=godmode/setup/setup_mailtemplates&update_template=1";
} else {
	$url = "index.php?sec=godmode&sec2=godmode/setup/setup_mailtemplates&create_template=1";
}
echo "<form name='setup' method='post' action='".$url."'>";
print_table ($table);
	
	echo "<div class='button-form'>";
		if ($update_template || $edit) {
			print_submit_button (__('Update'), 'action2', false, 'class="sub upd"');
		} else {
			print_submit_button (__('Create'), 'action2', false, 'class="sub upd"');
		}
	echo "</div>";
echo '</form>';
//////////////////////	FIN NUEVO

function get_template_files ($field) {
	$base_dir = 'include/mailtemplates';
	$files = list_files ($base_dir, ".tpl", 1, 0);
	
	$retval = array ();
	foreach ($files as $file) {
		$retval[$file] = $file;
	}
	return $retval;
}

?>


<script type="text/javascript" src="include/js/tinymce/tinymce.min.js"></script>
<script type="text/javascript" src="include/js/tinymce/jquery.tinymce.min.js "></script>
<script type="text/javascript">
tinymce.init({
    selector: 'textarea',
    fontsize_formats: "8pt 9pt 10pt 11pt 12pt 26pt 36pt",
    force_br_newlines : true,
    force_p_newlines : false,
    forced_root_block : false,
    plugins: [
    	'advlist autolink lists link image charmap print preview anchor',
    	'searchreplace visualblocks code fullscreen',
    	'insertdatetime media table contextmenu paste code'
  	],
  	menubar: false,
  	toolbar: 'undo redo | styleselect | bold italic fontsizeselect | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image',
  	content_css: 'include/js/tinymce/integria.css',
});

</script>

<script type="text/javascript">
$(document).ready (function () {
	onchange_template();
	$("#template_name").change(onchange_template);
	$("textarea").TextAreaResizer ();
});

function onchange_template() {
	var template_name = $("#template_name").val();
	$.ajax({
		type: "POST",
		url: "ajax.php",
		data: "page=godmode/setup/setup_mailtemplates&onchange_template=1&template_name="+template_name,
		dataType: "json",
		success: function(data){
			console.log(data);
			tinymce.get('textarea-template_content').focus();
			tinyMCE.activeEditor.setContent(data[0]);
			$('#template_group').val(data[1]);
			$('#template_action').val(data[2]);
			if (data[3] == 1){
				$('#template_group').attr('disabled', true);
				$('#template_action').attr('disabled', true);
			} else {
				$('#template_group').attr('disabled', false);
				$('#template_action').attr('disabled', false);
			}
		}
	});
}
</script>