<?php

function check_workunit_permission ($id_workunit) {
	global $config;
	
	// Delete workunit with ACL / Project manager check
	$workunit = get_db_row ('tworkunit', 'id', $id_workunit);
	if ($workunit === false)
		return false;
	
	$id_user = $workunit["id_user"];
	$id_task = get_db_value ("id_task", "tworkunit_task", "id_workunit", $workunit["id"]);
	$id_project = get_db_value ("id_project", "ttask", "id", $id_task);
	if ($id_user != $config["id_user"]
		&& ! give_acl ($config["id_user"], 0,"PM")
		&& ! project_manager_check ($id_project))
		return false;
	
	return true;
}

function delete_task_workunit ($id_workunit) {
	global $config;
	
	if (! check_workunit_permission ($id_workunit))
		return false;
	
	$sql = sprintf ('DELETE FROM tworkunit
		WHERE id = %d', $id_workunit);
	process_sql ($sql);
	$sql = sprintf ('DELETE FROM tworkunit_task
		WHERE id_workunit = %d', $id_workunit);
	return (bool) process_sql ($sql);
}

function lock_task_workunit ($id_workunit) {
	global $config;
	
	if (! check_workunit_permission ($id_workunit))
		return false;
	$sql = sprintf ('UPDATE tworkunit SET locked = "%s" WHERE id = %d',
		$config['id_user'], $id_workunit);
	return (bool) process_sql ($sql);
}

?>
