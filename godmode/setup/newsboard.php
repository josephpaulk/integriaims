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

check_login ();
	
if (! dame_admin ($config["id_user"])) {
	audit_db ("ACL Violation", $config["REMOTE_ADDR"], __("No administrator access"), __("Trying to access newsboard setup"));
	require ("general/noaccess.php");
	exit;
}

$operation = get_parameter ("operation");

// ---------------
// CREATE newsboard
// ---------------

if ($operation == "insert") {
	$title = (string) get_parameter ("title");
	$content = (string) get_parameter ("content"); 
	$timestamp = (string) get_parameter ("timestamp");
	$nodate = (int) get_parameter ("nodate", 0);

	if ($nodate == 1)
		$timestamp = "0000-00-00 00:00:00";

	$sql = sprintf ('INSERT INTO tnewsboard (title, content, `date`)
		VALUES ("%s","%s","%s")',
		$title, $content, $timestamp);
	$id = process_sql ($sql, 'insert_id');
	if (! $id)
		echo '<h3 class="error">'.__('Not created. Error inserting data').'</h3>';
	else {
		echo '<h3 class="suc">'.__('Successfully created').'</h3>'; 
	}
	$operation = "";
}


// ---------------
// DELETE newsboard
// ---------------
if ($operation == "delete") {
	$id = get_parameter ("id");
	$sql_delete= "DELETE FROM tnewsboard WHERE id = $id";
	$result=mysql_query($sql_delete);
	if (! $result)
		echo "<h3 class='error'>".__('Not deleted. Error deleting data')."</h3>";
	else
		echo "<h3 class='suc'>".__('Successfully deleted')."</h3>";
	$operation = "";
}


// CREATE new todo (form)
if ($operation == "create") {
    $title = "";
    $content = "";

	$table->width = '90%';
	$table->class = 'databox';
	$table->colspan = array ();
	$table->colspan[1][0] = 2;
	$table->colspan[2][0] = 2;
	$table->colspan[3][0] = 2;
	$table->colspan[4][0] = 2;
	$table->data = array ();
	
	$table->data[1][0] = print_input_text ('title', $title, '', 40, 100, true,
		__('Title'));
	
	$table->data[2][0] = print_textarea ('content', 10, 50, $content, '', true,
		__('Contents'));

	$timestamp = date ('Y-m-d H:i:s');
	$table->data[0][0] = print_input_text ('timestamp', $timestamp, '', 20,20, true, __('Timestamp'));
	$table->data[0][1] = print_checkbox ('nodate', 1, false, true,  __('Show always'));
	
	echo '<form method="post" action="index.php?sec=godmode&sec2=godmode/setup/newsboard">';
	print_table ($table);

	echo '<div class="button" style="width: '.$table->width.'">';
	print_submit_button (__('Create'), 'crt', false, 'class="sub next"');
	print_input_hidden ('operation', 'insert');
	echo '</form></div>';
}

// -------------------------
// TODO VIEW of my OWN items
// -------------------------
if ($operation == "") {
	echo "<h1>".__('Newsboard management')."</h1>";

	$sql = sprintf ('SELECT * FROM tnewsboard');
	$todos = get_db_all_rows_sql ($sql);
	if ($todos === false)
		$todos = array ();

	echo '<table class="listing" width="90%">';
	echo "<th>".__('Title');
	echo "<th>".__('Date');
	echo "<th>".__('Delete');

	foreach ($todos as $todo) {
		
		echo "<tr><td valign=top>";
		echo "<b>".$todo["title"]."</b>";
    
	    echo "<td valign=top>";
		echo $todo["date"];
    	
		echo '<td align="center" valign=top>';
		echo '<a href="index.php?sec=godmode&sec2=godmode/setup/newsboard&operation=delete&id='.$todo["id"].'" onClick="if (!confirm(\' '.__('Are you sure?').'\')) return false;"><img border=0 src="images/cross.png"></a>';

        echo "<tr><td colspan=3 style='border-bottom: 1px solid #acacac'>";
	echo clean_output_breaks ($todo["content"]);
	}
	echo "</table>";


    echo '<form method="post" action="index.php?sec=godmode&sec2=godmode/setup/newsboard">';
	echo '<div class="button" style="width: 90%;">';
	print_submit_button (__('Create'), 'crt', false, 'class="sub next"');
	print_input_hidden ('operation', 'create');
	echo '</form></div>';

} // Fin bloque else

?>
<script type="text/javascript" src="include/js/jquery.ui.slider.js"></script>

<script type="text/javascript">
$(document).ready (function () {
	$("#textarea-description").TextAreaResizer ();
	$("#slider").slider ({
		min: 0,
		max: 100,
		stepping: 5,
		slide: function (event, ui) {
			$("#progress").empty ().append (ui.value+"%");
		},
		change: function (event, ui) {
			$("#hidden-progress").attr ("value", ui.value);
		}
	});
<?php if ($progress)
	echo '$("#slider").slider ("moveTo", '.$progress.');';
?>
});
</script>
