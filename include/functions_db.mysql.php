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


$sql_cache = array ('saved' => 0);

/** 
 * Get the first value of the first row of a table in the database.
 * 
 * @param string Field name to get
 * @param string Table to retrieve the data
 * @param string Field to filter elements
 * @param string Condition the field must have
 *
 * @return mixed Value of first column of the first row. False if there were no row.
 */
function get_db_value ($field, $table, $field_search = 1, $condition = 1) {
	if (is_int ($condition)) {
		$sql = sprintf ("SELECT %s FROM %s WHERE %s = %d LIMIT 1",
				$field, $table, $field_search, $condition);
	} else if (is_float ($condition) || is_double ($condition)) {
		$sql = sprintf ("SELECT %s FROM %s WHERE %s = %f LIMIT 1",
				$field, $table, $field_search, $condition);
	} else {
		$sql = sprintf ("SELECT %s FROM %s WHERE %s = '%s' LIMIT 1",
				$field, $table, $field_search, $condition);
	}
	$result = get_db_all_rows_sql ($sql);
	
	if ($result === false)
		return false;
	if ($field[0] == '`')
		$field = str_replace ('`', '', $field);
	return $result[0][$field];
}

/** 
 * Get the first row of a database query into a table.
 *
 * The SQL statement executed would be something like:
 * "SELECT * FROM $table WHERE $field_search = $condition"
 *
 * @param table Table to get the row
 * @param field_search Field to filter elementes
 * @param condition Condition the field must have.
 * 
 * @return The first row of a database query.
 */
function get_db_row ($table, $field_search, $condition) {
	
	if (is_int ($condition)) {
		$sql = sprintf ("SELECT * FROM `%s` WHERE `%s` = %d LIMIT 1", $table, $field_search, $condition);
	} else if (is_float ($condition) || is_double ($condition)) {
		$sql = sprintf ("SELECT * FROM `%s` WHERE `%s` = %f LIMIT 1", $table, $field_search, $condition);
	} else {
		$sql = sprintf ("SELECT * FROM `%s` WHERE `%s` = '%s' LIMIT 1", $table, $field_search, $condition);
	}
	$result = get_db_all_rows_sql ($sql);
		
	if($result === false) 
		return false;
	
	return $result[0];
}


/** 
 * Get the first value of the first row of a table in the database from an
 * array with filter conditions.
 *
 * Example:
<code>
get_db_value_filter ('name', 'talert_templates',
	array ('value' => 2, 'type' => 'equal'));
// Equivalent to:
// SELECT name FROM talert_templates WHERE value = 2 AND type = 'equal' LIMIT 1

get_db_value_filter ('description', 'talert_templates',
	array ('name' => 'My alert', 'type' => 'regex'), 'OR');
// Equivalent to:
// SELECT description FROM talert_templates WHERE name = 'My alert' OR type = 'equal' LIMIT 1
</code>
 * 
 * @param string Field name to get
 * @param string Table to retrieve the data
 * @param array Conditions to filter the element. See format_array_to_where_clause_sql()
 * for the format
 * @param string Join operator for the elements in the filter.
 *
 * @return mixed Value of first column of the first row. False if there were no row.
 */
function get_db_value_filter ($field, $table, $filter, $where_join = 'AND') {
	if (! is_array ($filter) || empty ($filter))
		return false;
	
	/* Avoid limit and offset if given */
	unset ($filter['limit']);
	unset ($filter['offset']);
	
	$sql = sprintf ("SELECT %s FROM %s WHERE %s LIMIT 1",
		$field, $table,
		format_array_to_where_clause_sql ($filter, $where_join));
	$result = get_db_all_rows_sql ($sql);
	
	if ($result === false)
		return false;
	
	return $result[0][$field];
}

/** 
 * Get the first row of an SQL database query.
 * 
 * @param string SQL select statement to execute.
 * 
 * @return mixed The first row of the result or false
 */
function get_db_row_sql ($sql) {
	$sql .= " LIMIT 1";
	$result = get_db_all_rows_sql ($sql);
	
	if($result === false) 
		return false;
	
	return $result[0];
}

/** 
 * Get the row of a table in the database using a complex filter.
 * 
 * @param string Table to retrieve the data (warning: not cleaned)
  * @param mixed Filters elements. It can be an indexed array
 * (keys would be the field name and value the expected value, and would be
 * joined with an AND operator) or a string, including any SQL clause (without
 * the WHERE keyword). Example:
<code>
Both are similars:
get_db_row_filter ('table', array ('disabled', 0));
get_db_row_filter ('table', 'disabled = 0');

Both are similars:
get_db_row_filter ('table', array ('disabled' => 0, 'history_data' => 0), 'name, description', 'OR');
get_db_row_filter ('table', 'disabled = 0 OR history_data = 0', 'name, description');
get_db_row_filter ('table', array ('disabled' => 0, 'history_data' => 0), array ('name', 'description'), 'OR');
</code>
 * @param mixed Fields of the table to retrieve. Can be an array or a coma
 * separated string. All fields are retrieved by default
 * @param string Condition to join the filters (AND, OR).
 *
 * @return mixed Array of the row or false in case of error.
 */
function get_db_row_filter ($table, $filter, $fields = false, $where_join = 'AND') {
	if (empty ($fields)) {
		$fields = '*';
	} else {
		if (is_array ($fields))
			$fields = implode (',', $fields);
		else if (! is_string ($fields))
			return false;
	}
	
	if (is_array ($filter))
		$filter = format_array_to_where_clause_sql ($filter, $where_join, ' WHERE ');
	else if (is_string ($filter))
		$filter = 'WHERE '.$filter;
	else
		$filter = '';
	
	$sql = sprintf ('SELECT %s FROM %s %s',
		$fields, $table, $filter);
	
	return get_db_row_sql ($sql);
}

/** 
 * Get a single field in the databse from a SQL query.
 *
 * @param string SQL statement to execute
 * @param mixed Field number or row to get, beggining by 0. Default: 0
 *
 * @return mixed The selected field of the first row in a select statement.
 */
function get_db_sql ($sql, $field = 0) {
	$result = get_db_all_rows_sql ($sql);
	if($result === false)
		return false;

	return $result[0][$field];
}

/**
 * Get all the result rows using an SQL statement.
 * 
 * @param string SQL statement to execute.
 *
 * @return mixed A matrix with all the values returned from the SQL statement or
 * false in case of empty result
 */
function get_db_all_rows_sql ($sql) {
	$return = process_sql ($sql);
	
	if (! empty ($return))
		return $return;
	//Return false, check with === or !==
	return false;
}

/** 
 * Get all the rows of a table in the database that matches a filter.
 * 
 * @param string Table to retrieve the data (warning: not cleaned)
 * @param mixed Filters elements. It can be an indexed array
 * (keys would be the field name and value the expected value, and would be
 * joined with an AND operator) or a string, including any SQL clause (without
 * the WHERE keyword). Example:
<code>
Both are similars:
get_db_all_rows_filter ('table', array ('disabled', 0));
get_db_all_rows_filter ('table', 'disabled = 0');

Both are similars:
get_db_all_rows_filter ('table', array ('disabled' => 0, 'history_data' => 0), 'name', 'OR');
get_db_all_rows_filter ('table', 'disabled = 0 OR history_data = 0', 'name');
</code>
 * @param mixed Fields of the table to retrieve. Can be an array or a coma
 * separated string. All fields are retrieved by default
 * @param string Condition of the filter (AND, OR).
 *
 * @return mixed Array of the row or false in case of error.
 */
function get_db_all_rows_filter ($table, $filter, $fields = false, $where_join = 'AND') {
	//TODO: Validate and clean fields
	if (empty ($fields)) {
		$fields = '*';
	} elseif (is_array ($fields)) {
		$fields = implode (',', $fields);
	} elseif (! is_string ($fields)) {
		return false;	
	}
	
	//TODO: Validate and clean filter options
	if (is_array ($filter)) {
		$filter = format_array_to_where_clause_sql ($filter, $where_join, ' WHERE ');
	} elseif (is_string ($filter)) {
		$filter = 'WHERE '.$filter;
	} else {
		$filter = '';
	}
	
	$sql = sprintf ('SELECT %s FROM %s %s', $fields, $table, $filter);
	
	return get_db_all_rows_sql ($sql);
}

/**
 * Error handler function when an SQL error is triggered.
 * 
 * @param int Level of the error raised (not used, but required by set_error_handler()).
 * @param string Contains the error message.
 *
 * @return bool True if error level is lower or equal than errno.
 */
function sql_error_handler ($errno, $errstr) {
	global $config;
	
	/* If debug is activated, this will also show the backtrace */
	if (debug ($errstr))
		return false;
	
	if (error_reporting () <= $errno)
		return false;
	echo "<strong>SQL error</strong>: ".$errstr."<br />\n";
	return true;
}

/**
 * Add a database query to the debug trace.
 * 
 * This functions does nothing if the config['debug'] flag is not set. If a
 * sentence was repeated, then the 'saved' counter is incremented.
 *
 * @param string SQL sentence.
 * @param mixed Query result. On error, error string should be given.
 * @param int Affected rows after running the query.
 * @param mixed Extra parameter for future values.
 */
function add_database_debug_trace ($sql, $result = false, $affected = false, $extra = false) {
	global $config;
	
	if (! isset ($config['debug']))
		return false;
	
	if (! isset ($config['db_debug']))
		$config['db_debug'] = array ();
	
	if (isset ($config['db_debug'][$sql])) {
		$config['db_debug'][$sql]['saved']++;
		return;
	}
	
	$var = array ();
	$var['sql'] = $sql;
	$var['result'] = $result;
	$var['affected'] = $affected;
	$var['saved'] = 0;
	$var['extra'] = $extra;
	
	$config['db_debug'][$sql] = $var;
}

/**
 * This function comes back with an array in case of SELECT
 * in case of UPDATE, DELETE etc. with affected rows
 * an empty array in case of SELECT without results
 * Queries that return data will be cached so queries don't get repeated
 *
 * @param string SQL statement to execute
 *
 * @param string What type of info to return in case of INSERT/UPDATE.
 *		'affected_rows' will return mysql_affected_rows (default value)
 *		'insert_id' will return the ID of an autoincrement value
 *		'info' will return the full (debug) information of a query
 *
 * @return mixed An array with the rows, columns and values in a multidimensional array or false in error
 */
function process_sql ($sql, $rettype = "affected_rows") {
	global $config;
	global $sql_cache;
	
	$retval = array();
	
	if ($sql == '')
		return false;
	
	if (empty ($config['mysql_result_type']))
		$config['mysql_result_type'] = MYSQL_BOTH;
	
	if (! empty ($sql_cache[$sql])) {
		$retval = $sql_cache[$sql];
		$sql_cache['saved']++;
		add_database_debug_trace ($sql);
	} else {
		$start = microtime (true);
		$result = mysql_query ($sql);
		$time = microtime (true) - $start;
		if ($result === false) {
			$backtrace = debug_backtrace ();
			$error = sprintf ('%s (\'%s\') in <strong>%s</strong> on line %d',
				mysql_error (), $sql, $backtrace[0]['file'], $backtrace[0]['line']);
			add_database_debug_trace ($sql, mysql_error ());
			set_error_handler ('sql_error_handler');
			trigger_error ($error);
			restore_error_handler ();
			return false;
		} elseif ($result === true) {
			if ($rettype == "insert_id") {
				$result = mysql_insert_id ();
			} elseif ($rettype == "info") {
				$result = mysql_info ();
			} else {
				$result = mysql_affected_rows ();
			}
			
			add_database_debug_trace ($sql, $result, mysql_affected_rows (),
				array ('time' => $time));
			return $result;
		} else {
			add_database_debug_trace ($sql, 0, mysql_affected_rows (), 
				array ('time' => $time));
			while ($row = mysql_fetch_array ($result, $config['mysql_result_type'])) {
				array_push ($retval, $row);
			}
			$sql_cache[$sql] = $retval;
			mysql_free_result ($result);
		}
	}
	
	if (! empty ($retval))
		return $retval;
	//Return false, check with === or !==
	return false;
}

/**
 * Get all the rows in a table of the database.
 * 
 * @param string Database table name.
 * @param string Field to order by.
 *
 * @return mixed A matrix with all the values in the table
 */
function get_db_all_rows_in_table ($table, $order_field = "") {
	if ($order_field != "") {
		return get_db_all_rows_sql ("SELECT * FROM `".$table."` ORDER BY ".$order_field);
	} else {	
		return get_db_all_rows_sql ("SELECT * FROM `".$table."`");
	}
}

/**
 * Get all the rows in a table of the databes filtering from a field.
 * 
 * @param string Database table name.
 * @param string Field of the table.
 * @param string Condition the field must have to be selected.
 * @param string Field to order by.
 *
 * @return mixed A matrix with all the values in the table that matches the condition in the field or false
 */
function get_db_all_rows_field_filter ($table, $field, $condition, $order_field = "") {
	if (is_int ($condition) || is_bool ($condition)) {
		$sql = sprintf ("SELECT * FROM `%s` WHERE `%s` = %d", $table, $field, $condition);
	} else if (is_float ($condition) || is_double ($condition)) {
		$sql = sprintf ("SELECT * FROM `%s` WHERE `%s` = %f", $table, $field, $condition);
	} else {
		$sql = sprintf ("SELECT * FROM `%s` WHERE `%s` = '%s'", $table, $field, $condition);
	}

	if ($order_field != "")
		$sql .= sprintf (" ORDER BY %s", $order_field);
	return get_db_all_rows_sql ($sql);
}

/**
 * Get all the rows in a table of the databes filtering from a field.
 * 
 * @param string Database table name.
 * @param string Field of the table.
 *
 * @return mixed A matrix with all the values in the table that matches the condition in the field
 */
function get_db_all_fields_in_table ($table, $field = '', $condition = '', $order_field = '') {
	$sql = sprintf ("SELECT * FROM `%s`", $table);
	if ($condition != '') {
		$sql .= sprintf (" WHERE `%s` = '%s'", $field, $condition);
	}
	
	if ($order_field != "")
		$sql .= sprintf (" ORDER BY %s", $order_field);
	
	return get_db_all_rows_sql ($sql);
}

/**
 * Formats an array of values into a SQL string.
 *
 * This function is useful to generate an UPDATE SQL sentence from a list of
 * values. Example code:
 *
 * <code>
  $values = array ();
  $values['name'] = "Name";
  $values['description'] = "Long description";
  $sql = 'UPDATE table SET '.format_array_to_update_sql ($values).' WHERE id=1';
  echo $sql;
  </code>
 * Will return:
   <code>
  UPDATE table SET `name` = "Name", `description` = "Long description" WHERE id=1
   </code>
 *
 * @param array Values to be formatted in an array indexed by the field name.
 *
 * @return string Values joined into an SQL string that can fits into an UPDATE
 * sentence.
 */
function format_array_to_update_sql ($values) {
	$fields = array ();
	
	foreach ($values as $field => $value) {
		if (is_numeric ($field)) {
			array_push ($fields, $value);
			continue;
		}
		
		if ($value === NULL) {
			$sql = sprintf ("`%s` = NULL", $field);
		} elseif (is_int ($value) || is_bool ($value)) {
			$sql = sprintf ("`%s` = %d", $field, $value);
		} elseif (is_float ($value) || is_double ($value)) {
			$sql = sprintf ("`%s` = %f", $field, $value);
		} else {
			/* String */
			if (isset ($value[0]) && $value[0] == '`')
				/* Don't round with quotes if it references a field */
				$sql = sprintf ("`%s` = %s", $field, $value);
			else
				$sql = sprintf ("`%s` = '%s'", $field, $value);
		}
		array_push ($fields, $sql);
	}
	
	return implode (", ", $fields);
}

/**
 * Formats an array of values into a SQL where clause string.
 *
 * This function is useful to generate a WHERE clause for a SQL sentence from
 * a list of values. Example code:
<code>
$values = array ();
$values['name'] = "Name";
$values['description'] = "Long description";
$values['limit'] = $config['block_size']; // Assume it's 20
$sql = 'SELECT * FROM table WHERE '.format_array_to_where_clause_sql ($values);
echo $sql;
</code>
 * Will return:
 * <code>
 * SELECT * FROM table WHERE `name` = "Name" AND `description` = "Long description" LIMIT 20
 * </code>
 *
 * @param array Values to be formatted in an array indexed by the field name.
 * There are special parameters such as 'limit' and 'offset' that will be used
 * as ORDER, LIMIT and OFFSET clauses respectively. Since LIMIT and OFFSET are
 * numerics, ORDER can receive a field name or a SQL function and a the ASC or
 * DESC clause. Examples:
<code>
$values = array ();
$values['value'] = 10;
$sql = 'SELECT * FROM table WHERE '.format_array_to_where_clause_sql ($values);
// SELECT * FROM table WHERE VALUE = 10

$values = array ();
$values['value'] = 10;
$values['order'] = 'name DESC';
$sql = 'SELECT * FROM table WHERE '.format_array_to_where_clause_sql ($values);
// SELECT * FROM table WHERE VALUE = 10 ORDER BY name DESC

</code>
 * @param string Join operator. AND by default.
 * @param string A prefix to be added to the string. It's useful when limit and
 * offset could be given to avoid this cases:
<code>
$values = array ();
$values['limit'] = 10;
$values['offset'] = 20;
$sql = 'SELECT * FROM table WHERE '.format_array_to_where_clause_sql ($values);
// Wrong SQL: SELECT * FROM table WHERE LIMIT 10 OFFSET 20

$values = array ();
$values['limit'] = 10;
$values['offset'] = 20;
$sql = 'SELECT * FROM table WHERE '.format_array_to_where_clause_sql ($values, 'AND', 'WHERE');
// Good SQL: SELECT * FROM table LIMIT 10 OFFSET 20

$values = array ();
$values['value'] = 5;
$values['limit'] = 10;
$values['offset'] = 20;
$sql = 'SELECT * FROM table WHERE '.format_array_to_where_clause_sql ($values, 'AND', 'WHERE');
// Good SQL: SELECT * FROM table WHERE value = 5 LIMIT 10 OFFSET 20
</code>
 *
 * @return string Values joined into an SQL string that can fits into the WHERE
 * clause of an SQL sentence.
 */
function format_array_to_where_clause_sql ($values, $join = 'AND', $prefix = false) {
	$fields = array ();
	
	if (! is_array ($values)) {
		return '';
	}
	
	$query = '';
	$limit = '';
	$offset = '';
	$order = '';
	$group = '';
	if (isset ($values['limit'])) {
		$limit = sprintf (' LIMIT %d', $values['limit']);
		unset ($values['limit']);
	}
	
	if (isset ($values['offset'])) {
		$offset = sprintf (' OFFSET %d', $values['offset']);
		unset ($values['offset']);
	}
	
	if (isset ($values['order'])) {
		$order = sprintf (' ORDER BY %s', $values['order']);
		unset ($values['order']);
	}
	
	if (isset ($values['group'])) {
		$group = sprintf (' GROUP BY %s', $values['group']);
		unset ($values['group']);
	}
	
	$i = 1;
	$max = count ($values);
	foreach ($values as $field => $value) {
		if (is_numeric ($field)) {
			/* User provide the exact operation to do */
			$query .= $value;
			
			if ($i < $max) {
				$query .= ' '.$join.' ';
			}
			$i++;
			continue;
		}
		
		if ($field[0] != "`") {
			$field = "`".$field."`";
		}
		
		if (is_null ($value)) {
			$query .= sprintf ("%s IS NULL", $field);
		} elseif (is_int ($value) || is_bool ($value)) {
			$query .= sprintf ("%s = %d", $field, $value);
		} else if (is_float ($value) || is_double ($value)) {
			$query .= sprintf ("%s = %f", $field, $value);
		} elseif (is_array ($value)) {
			$query .= sprintf ('%s IN ("%s")', $field, implode ('", "', $value));
		} else {
			$query .= sprintf ("%s = '%s'", $field, $value);
		}
		
		if ($i < $max) {
			$query .= ' '.$join.' ';
		}
		$i++;
	}
	
	return (! empty ($query) ? $prefix: '').$query.$group.$order.$limit.$offset;
}

/**
 * Inserts strings into database
 *
 * The number of values should be the same or a positive integer multiple as the number of rows
 * If you have an associate array (eg. array ("row1" => "value1")) you can use this function with ($table, array_keys ($array), $array) in it's options
 * All arrays and values should have been cleaned before passing. It's not neccessary to add quotes.
 *
 * @param string Table to insert into
 * @param mixed A single value or array of values to insert (can be a multiple amount of rows)
 *
 * @return mixed False in case of error or invalid values passed. Affected rows otherwise
 */
function process_sql_insert ($table, $values) {
	 //Empty rows or values not processed
	if (empty ($values))
		return false;
	
	$values = (array) $values;
		
	$query = sprintf ("INSERT INTO `%s` ", $table);
	$fields = array ();
	$values_str = '';
	$i = 1;
	$max = count ($values);
	foreach ($values as $field => $value) { //Add the correct escaping to values
		if ($field[0] != "`") {
			$field = "`".$field."`";
		}
		
		array_push ($fields, $field);
		
		if (is_null ($value)) {
			$values_str .= "NULL";
		} elseif (is_int ($value) || is_bool ($value)) {
			$values_str .= sprintf ("%d", $value);
		} else if (is_float ($value) || is_double ($value)) {
			$values_str .= sprintf ("%f", $value);
		} else {
			$values_str .= sprintf ("'%s'", $value);
		}
		
		if ($i < $max) {
			$values_str .= ",";
		}
		$i++;
	}
	
	$query .= '('.implode (', ', $fields).')';
	
	$query .= ' VALUES ('.$values_str.')';
	
	return process_sql ($query, 'insert_id');
}

/**
 * Updates a database record.
 *
 * All values should be cleaned before passing. Quoting isn't necessary.
 * Examples:
 *
 * <code>
process_sql_update ('table', array ('field' => 1), array ('id' => $id));
process_sql_update ('table', array ('field' => 1), array ('id' => $id, 'name' => $name));
process_sql_update ('table', array ('field' => 1), array ('id' => $id, 'name' => $name), 'OR');
process_sql_update ('table', array ('field' => 2), 'id in (1, 2, 3) OR id > 10');
 * <code>
 *
 * @param string Table to insert into
 * @param array An associative array of values to update
 * @param mixed An associative array of field and value matches. Will be joined
 * with operator specified by $where_join. A custom string can also be provided.
 * If nothing is provided, the update will affect all rows.
 * @param string When a $where parameter is given, this will work as the glue
 * between the fields. "AND" operator will be use by default. Other values might
 * be "OR", "AND NOT", "XOR"
 *
 * @return mixed False in case of error or invalid values passed. Affected rows otherwise
 */
function process_sql_update ($table, $values, $where = false, $where_join = 'AND') {
	$query = sprintf ("UPDATE `%s` SET %s",
		$table,
		format_array_to_update_sql ($values));
	
	if ($where) {
		if (is_string ($where)) {
			// No clean, the caller should make sure all input is clean, this is a raw function
			$query .= " WHERE ".$where;
		} else if (is_array ($where)) {
			$query .= format_array_to_where_clause_sql ($where, $where_join, ' WHERE ');
		}
	}
	
	return process_sql ($query);
}

/**
 * Delete database records.
 *
 * All values should be cleaned before passing. Quoting isn't necessary.
 * Examples:
 *
 * <code>
process_sql_delete ('table', array ('id' => 1));
// DELETE FROM table WHERE id = 1
process_sql_delete ('table', array ('id' => 1, 'name' => 'example'));
// DELETE FROM table WHERE id = 1 AND name = 'example'
process_sql_delete ('table', array ('id' => 1, 'name' => 'example'), 'OR');
// DELETE FROM table WHERE id = 1 OR name = 'example'
process_sql_delete ('table', 'id in (1, 2, 3) OR id > 10');
// DELETE FROM table WHERE id in (1, 2, 3) OR id > 10
 * <code>
 *
 * @param string Table to insert into
 * @param array An associative array of values to update
 * @param mixed An associative array of field and value matches. Will be joined
 * with operator specified by $where_join. A custom string can also be provided.
 * If nothing is provided, the update will affect all rows.
 * @param string When a $where parameter is given, this will work as the glue
 * between the fields. "AND" operator will be use by default. Other values might
 * be "OR", "AND NOT", "XOR"
 *
 * @return mixed False in case of error or invalid values passed. Affected rows otherwise
 */
function process_sql_delete ($table, $where, $where_join = 'AND') {
	if (empty ($where))
		/* Should avoid any mistake that lead to deleting all data */
		return false;
	
	$query = sprintf ("DELETE FROM `%s` WHERE ", $table);
	
	if ($where) {
		if (is_string ($where)) {
			/* FIXME: Should we clean the string for sanity? 
			 Who cares if this is deleting data... */
			$query .= $where;
		} else if (is_array ($where)) {
			$query .= format_array_to_where_clause_sql ($where, $where_join);
		}
	}
	
	return process_sql ($query);
}

/**
 * Get row by row the DB by SQL query. The first time pass the SQL query and
 * rest of times pass none for iterate in table and extract row by row, and
 * the end return false.
 * 
 * @param bool $new Default true, if true start to query.
 * @param resource $result The resource of mysql for access to query.
 * @param string $sql 
 * @return mixed The row or false in error.
 */

function get_db_all_row_by_steps_sql($new = true, &$result, $sql = null) {
	if ($new == true)
		$result = mysql_query($sql);
	
	return mysql_fetch_assoc($result);
}


?>