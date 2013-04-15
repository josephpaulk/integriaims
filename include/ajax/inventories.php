<?php

global $config;

$get_external_data = get_parameter('get_external_data');

if ($get_external_data) {
	$table_name = get_parameter('table_name');
	
	$external_data = get_db_all_rows_in_table($table_name);
	
	if ($external_data !== false) {
	
		$table->class = 'listing';
		$table->width = '98%';
		$table->data = array ();
		$table->head = array ();
		
		$keys = array_keys($external_data[0]);
		
		$i = 0;
		foreach ($keys as $k=>$head) {
			$table->head[$i] =$head;
			$i++;
		}

	foreach ($external_data as $key => $ext_data) {

		$data_values = array_values($ext_data);

		foreach ($data_values as $k => $dat) {
			$data[$k] = $dat;
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
