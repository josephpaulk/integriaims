<?php

global $config;

$get_external_data = get_parameter('get_external_data');

if ($get_external_data) {
	$table_name = get_parameter('table_name');
	$id_table = (string) get_parameter('id_table');
	$element_name = get_parameter('element_name');
	$id_object_type_field = get_parameter('id_object_type_field');
	
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
			if ($head == $id_table)
				$pos_id = $i+1;
			$i++;
		}
		
		foreach ($external_data as $key => $ext_data) {
			$j = 0;
			foreach ($ext_data as $k => $dat) {
				
				if (array_key_exists($k, $fields)) {
					if ($j == $pos_id) {
						$data[$j] = "<a href='javascript: enviar(" . $dat . ", " . $element_name . ", " . $id_object_type_field . ")'>".$dat."</a>";
					} else {
						$data[$j] = $dat;
					}
					
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
