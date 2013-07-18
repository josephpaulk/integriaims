<?php

function mail_workorder ($id_wo, $mode){
	global $config;
	
	$wo = get_db_row ("ttodo", "id", $id_wo);
	
	// Only send mails when creator is different than owner
	if ($wo['assigned_user'] == $wo['created_by_user'])
		return;
		
	$MACROS["_sitename_"] = $config['sitename'];
	$MACROS["_wo_id_"] = $wo['id'];
	$MACROS["_wo_name_"] = $wo['name'];
	$MACROS["_wo_last_update_"] = $wo['last_update'];
	$MACROS["_wo_created_by_user_"] = $wo['created_by_user'];
	$MACROS["_wo_assigned_user_"] = $wo['assigned_user'];
	$MACROS["_wo_progress_"] = $wo['progress'];
	$MACROS["_wo_priority_"] = $wo['priority'];
	$MACROS["_wo_description_"] = wordwrap($wo["description"], 70, "\n");
	$MACROS["_wo_url_"] = $config["base_url"]."/index.php?sec=projects&sec2=operation/workorders/wo&operation=view&id=$id_wo";

	// Send email for assigned and creator of this workorder
	$email_creator = get_user_email ($wo['created_by_user']);
	$email_assigned = get_user_email ($wo['assigned_user']);

	switch ($mode) {
		case 0: // WO update
			$text = template_process ($config["homedir"]."/include/mailtemplates/wo_update.tpl", $MACROS);
			$subject = template_process ($config["homedir"]."/include/mailtemplates/wo_subject_update.tpl", $MACROS);
			break;
		
		case 1: // WO creation
			$text = template_process ($config["homedir"]."/include/mailtemplates/wo_create.tpl", $MACROS);
			$subject = template_process ($config["homedir"]."/include/mailtemplates/wo_subject_create.tpl", $MACROS);
			break;
			
/*
		case 3: // WO deleted 
			$text = template_process ($config["homedir"]."/include/mailtemplates/wo_delete.tpl", $MACROS);
			$subject = template_process ($config["homedir"]."/include/mailtemplates/wo_subject_delete.tpl", $MACROS);
			break;
*/
	}

	$msg_code = "WO#$id_wo";
	$msg_code .= "/".substr(md5($id_wo . $config["smtp_pass"] . $wo["assigned_user"]),0,5);
	$msg_code .= "/" . $wo["assigned_user"];;
	
	integria_sendmail ($email_assigned, $subject, $text, false, $msg_code);
	
	$msg_code = "WO#$id_wo";
	$msg_code .= "/".substr(md5($id_wo . $config["smtp_pass"] . $wo["created_by_user"]),0,5);
    $msg_code .= "/".$wo["created_by_user"];

	integria_sendmail ($email_creator, $subject, $text, false, $msg_code);

}

?>
