<?php

global $config;

$get_external_data = get_parameter('get_external_data');

if ($get_external_data) {
	$table_name = get_parameter('table_name');
	
	$sql_ext = "SHOW COLUMNS FROM ".$table_name;
	$desc_ext = get_db_all_rows_sql($sql_ext);
	
	$fields = array();
	foreach ($desc_ext as $key=>$ext) {
		$fields[$ext['Field']] = $ext['Field'];
	}
	
	$external_data = get_db_all_rows_in_table($table_name);
	
	if ($external_data !== false) {
	
		$table->class = 'listing';
		$table->width = '98%';
		$table->data = array ();
		$table->head = array ();
		
		$keys = array_keys($fields);
		
		$i = 0;
		foreach ($keys as $k=>$head) {
			$table->head[$i] =$head;
			$i++;
		}
		
		foreach ($external_data as $key => $ext_data) {
			$j = 0;
			foreach ($ext_data as $k => $dat) {

				if (array_key_exists($k, $fields)) {
					$data[$j] = $dat;
				}
				$j++;
			}
			array_push ($table->data, $data);
		}

		print_table ($table);
	} else {
		echo "<h4>".__("No data to show")."</h4>";
	}
	return;
}

?>
