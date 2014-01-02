<?php

global $config;
enterprise_include ('include/functions_projects.php', true);

 /**
 * Get an SQL query with the accessible projects
 * by accessible companies.
 * NOT FULLY IMPLEMENTED IN OPENSOURCE version
 * Please visit http://integriaims.com for more information
 * 
 * @param id_user User ID
 * @param where_clause More filters for the WHERE clause of the query
 * @param disabled 1 to return the disabled projects
 * @param real Flag for use or not the admin permissions
 * 
 * @return string SQL query
*/
function get_projects_query ($id_user, $where_clause = "", $disabled = 0, $real = false) {
	
	$return = enterprise_hook ('get_projects_query_extra', array($id_user, $where_clause, $disabled, $real));
	if ($return !== ENTERPRISE_NOT_HOOK)
		return $return;
	return "SELECT *
			FROM tproject
			WHERE disabled=$disabled
				$where_clause
			ORDER BY name";
}

/**
 * Get an SQL query with the accessible tasks
 * NOT FULLY IMPLEMENTED IN OPENSOURCE version
 * Please visit http://integriaims.com for more information
 * 
 * @param id_user User ID
 * @param id_project Project Id
 * @param where_clause More filters for the WHERE clause of the query
 * @param disabled 1 to return the tasks of disabled projects
 * @param real Flag for use or not the admin and project manager permissions
 * 
 * @return string SQL query
*/
function get_tasks_query ($id_user, $id_project, $where_clause = "", $disabled = 0, $real = false) {
	
	$return = enterprise_hook ('get_tasks_query_extra', array($id_user, $id_project, $where_clause, $disabled, $real));
	if ($return !== ENTERPRISE_NOT_HOOK)
		return $return;
	return "SELECT *
			FROM ttask
			WHERE id_project=$id_project
				AND id_project=ANY(SELECT id
								  FROM tproject
								  WHERE disabled=$disabled)
				$where_clause
			ORDER BY name";
}

/**
 * Get the project or task accessibility
 *
 * @param id_user User ID
 * @param id_project Project Id. If false, only check the read flag
 * @param id_task Task Id. If true, check the project accessibitity
 * @param real Flag for use or not the admin and project manager permissions
 * @param search_in_hierarchy Flag for search inherited permissions
 * 
 * @return string SQL query
 * 
 * NOT FULLY IMPLEMENTED IN OPENSOURCE version
 * Please visit http://integriaims.com for more information
*/
function get_project_access ($id_user, $id_project = false, $id_task = false, $real = false, $search_in_hierarchy = false) {
	
	$permission = array();
	$permission['read'] = true;
	$permission['write'] = true;
	$permission['manage'] = true;
	
	$return = enterprise_hook ('get_project_access_extra', array($id_user, $id_project, $id_task, $real, $search_in_hierarchy));
	
	if ($return !== ENTERPRISE_NOT_HOOK)
		return $return;
	
	return $permission;
}

/**
 * Get the number of readable tasks of a project for an user
 *
 * @param id_user User ID
 * @param id_project Project Id
 * @param id_parent Only count the tasks with that parent
 * 
 * @return int Count of tasks
*/
function get_accesible_task_count ($id_user, $id_project, $id_parent = false) {
	
	if ($id_parent !== false) {
		$parent = "id_parent_task=$id_parent";
	} else {
		$parent = "1=1";
	}
	
	$sql = "SELECT id
			FROM ttask
			WHERE $parent
				AND id_project=$id_project";
	$count = 0;
	$new = true;
	while ($task = get_db_all_row_by_steps_sql($new, $result_project, $sql)) {
		$new = false;
		
		$task_access = get_project_access ($id_user, $id_project, $task['id'], false, true);
		if ($task_access['read']) {
			$count++;
		}
		
	}
	return $count;
}

/**
 * Get the if the user can manage almost one task
 *
 * @param id_user User ID
 * @param id_project Project Id. Check the tasks of one or all projects
 * 
 * @return boolean
 * 
 * NOT FULLY IMPLEMENTED IN OPENSOURCE version
 * Please visit http://integriaims.com for more information
*/
function manage_any_task ($id_user, $id_project = false, $permission_type = "manage") {
	
	$return = enterprise_hook ('manage_any_task_extra', array($id_user, $id_project, $permission_type));
	
	if ($return !== ENTERPRISE_NOT_HOOK)
		return $return;
	return true;
	
}

function get_workorder_acl ($id_workorder, $type = '', $id_user = false) {
	
	$return = enterprise_hook ('get_workorder_acl_extra', array($id_workorder, $type, $id_user));
	
	if ($return !== ENTERPRISE_NOT_HOOK)
		return $return;
	return true;
	
}

function get_workorders ($where_clause = "", $order_by = "") {
	
	$sql = "SELECT * FROM ttodo ".$where_clause." ".$order_by;
	
	$return = enterprise_hook ('get_workorders_extra', array($where_clause, $order_by));
	
	if ($return !== ENTERPRISE_NOT_HOOK)
		return $return;
	return get_db_all_rows_sql ($sql);
}

function project_number_task_user ($id_project, $id_user) {

	$sql = sprintf("SELECT id FROM ttask WHERE id_project= %d", $id_project);

	$tasks = get_db_all_rows_sql($sql);

	if ($tasks == false) {
		return 0;
	}

	$clause = "";

	foreach ($tasks as $t) {
		$clause .= $t["id"].",";
	}

	$clause = "(".substr($clause,0,-1).")";


	$sql = sprintf ('SELECT COUNT(id) FROM trole_people_task WHERE 
					id_task IN %s AND id_user = "%s"', $clause, $id_user);

	return (int) get_db_sql ($sql);
}

function projects_get_cost_task_by_profile ($id_task, $id_profile=false, $have_cost=false) {
	if ($id_profile) {
		if ($have_cost) {
			$sql = "SELECT id_profile, SUM(duration) as total_duration FROM tworkunit, tworkunit_task
					WHERE tworkunit_task.id_task = $id_task 
					AND id_profile= $id_profile
					AND have_cost = 1
					AND tworkunit_task.id_workunit = tworkunit.id 
					GROUP BY id_profile";
		} else {
			$sql = "SELECT id_profile, SUM(duration) as total_duration FROM tworkunit, tworkunit_task
					WHERE tworkunit_task.id_task = $id_task 
					AND id_profile= $id_profile
					AND tworkunit_task.id_workunit = tworkunit.id 
					GROUP BY id_profile";
		}
	} else { //all profiles
		if ($have_cost) {
			$sql = "SELECT id_profile, SUM(duration) as total_duration FROM tworkunit, tworkunit_task
					WHERE tworkunit_task.id_task = $id_task 
					AND have_cost = 1
					AND tworkunit_task.id_workunit = tworkunit.id 
					GROUP BY id_profile";
		} else {
			$sql = "SELECT id_profile, SUM(duration) as total_duration FROM tworkunit, tworkunit_task
					WHERE tworkunit_task.id_task = $id_task 
					AND tworkunit_task.id_workunit = tworkunit.id 
					GROUP BY id_profile";
		}
	}

	$duration = get_db_row_sql ($sql);

	$total = 0;
	
	if ($duration != false) {
			$role_info = get_db_row_sql ("SELECT name, cost FROM trole WHERE id = ".$duration['id_profile']);

			if ($role_info != false) {
				$cost_per_hour = $role_info['cost'];
				$profile_name = $role_info['name'];
				$total = $cost_per_hour * $duration['total_duration'];
			}
	}
	return $total;
}

function projects_get_project_profiles ($id_project) {
	
	$project_profiles = get_db_all_rows_sql ("SELECT distinct(id_role), trole.name 
			FROM trole_people_project, trole
			WHERE id_project=$id_project
			AND trole.id=trole_people_project.id_role");
			
	$task_profiles = get_db_all_rows_sql ("SELECT distinct(id_role), trole.name 
			FROM trole_people_task, trole
			WHERE trole_people_task.id_task IN (SELECT id FROM ttask WHERE id_project=$id_project)
			AND trole.id=trole_people_task.id_role");
	
	if ($project_profiles == false) {
		$project_profiles = array();
	}
	if ($task_profiles == false) {
		$task_profiles = array();
	}
	
	$results = array_merge($project_profiles, $task_profiles);
	
	if (!empty($results)) {
		foreach ($results as $result) {
			$all_profiles[$result['id_role']]['id_role'] = $result['id_role'];
			$all_profiles[$result['id_role']]['name'] = $result['name'];
		}
	}
	
	return $all_profiles;
	
}

function projects_get_cost_by_profile ($id_project, $have_cost=false) {
	
	$total_per_profile = array();
	
	$project_profiles = projects_get_project_profiles ($id_project);		
	$project_tasks = get_db_all_rows_sql("SELECT * FROM ttask WHERE id_project = $id_project");
	
	if ($project_profiles) {
		foreach ($project_profiles as $profile) {
			foreach ($project_tasks as $task) {
				$total_per_profile[$profile['name']] += projects_get_cost_task_by_profile ($task['id'], $profile['id_role'], $have_cost);
			}
		}
	}
	return $total_per_profile;
}
?>
