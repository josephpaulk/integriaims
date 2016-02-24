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

echo "<h2>".__('Newsboard management')."</h2>";

$operation = get_parameter ("operation","");
if ($operation == "create")
	echo "<h4>".__('Create Newsboard')."</h4>";
if ($operation == "" || $operation == "insert")
	echo "<h4>".__('List Newsboard')."</h4>";

// ---------------
// CREATE newsboard
// ---------------

if ($operation == "insert") {
	$title = (string) get_parameter ("title");
	$content = (string) get_parameter ("content"); 
	$date = date('Y-m-d H:i:s', time()); //current datetime
	$id_group = (int) get_parameter ("id_group", 0);
	$expire = (int) get_parameter ("expire");
	$expire_date = get_parameter ("expire_date");
	$expire_date = date('Y-m-d', strtotime($expire_date));
	$expire_time = get_parameter ("expire_time");
	$expire_timestamp = "$expire_date $expire_time";
	$creator = $config['id_user'];

	if (!$expire)
		$expire_timestamp = "0000-00-00 00:00:00";

	$sql = sprintf ('INSERT INTO tnewsboard (title, content, `date`, id_group, expire, `expire_timestamp`, creator)
		VALUES ("%s","%s","%s",%d,%d,"%s","%s")',
		$title, $content, $date, $id_group, $expire, $expire_timestamp, $creator);
		
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


// CREATE new newsboard(form)
if ($operation == "create") {
    $title = "";
    $content = "";
    $expire = 0;
    $date = date('Y-m-d', time() + 604800); //one week later
	$time = date('H:i:s', time());
  
	$table = new StdClass();
	$table->width = '100%';
	$table->class = 'search-table-button';
	$table->colspan = array ();
	$table->colspan[1][0] = 2;
	$table->colspan[2][0] = 4;
	$table->colspan[3][0] = 2;
	$table->colspan[4][0] = 2;
	
	$table->data = array ();
	
	$table->data[1][0] = print_input_text ('title', $title, '', 60, 100, true,
		__('Title'));

	$table->data[2][0] = print_textarea ("content", 10, 1, $content, '', true, __('Contents'));

	$all_groups = group_get_groups();
	$table->data[0][0] = print_select ($all_groups, "id_group", $id_grupo, '', '', 0, true, false, false, __('Group'));
	
	$table->data[0][1] = print_checkbox ('expire', 1, false, true,  __('Expire'));
	
	$table->data[0][2] = "<div style='display:inline-block;'>" . print_input_text ('expire_date', $date, '', 11, 2, true, __('Date')) . "</div>";
	$table->data[0][2] .= "&nbsp;";
	$table->data[0][2] .= "<div style='display:inline-block;'>" . print_input_text ('expire_time', $time, '', 7, 20, true, __('Time')) . "</div>";
		
	$button = print_submit_button (__('Create'), 'crt', false, 'class="sub create"', true);
	$button .= print_input_hidden ('operation', 'insert', true);
	
	$table->data['button'][0] = $button;
	$table->colspan['button'][0] = 4;
	
	echo '<form method="post" action="index.php?sec=godmode&sec2=godmode/setup/newsboard">';
	print_table ($table);
	echo '</form>';
}

// -------------------------
// TODO VIEW of my OWN items
// -------------------------
if ($operation == "") {
	
	$sql = sprintf ('SELECT * FROM tnewsboard');
	$todos = get_db_all_rows_sql ($sql);
	if ($todos === false)
		$todos = array ();

	echo '<table class="listing" width="100%">';
	echo "<th>".__('Title');
	echo "<th>".__('Expire');
	echo "<th>".__('Expire date');
	echo "<th>".__('Delete');

	foreach ($todos as $todo) {
		
		echo "<tr><td valign=top>";
		echo "<b>".$todo["title"]."</b>";
    
		echo "<td valign=top>";
		if ($todo['expire']) {
			echo __('Yes');
		} else {
			echo __('No');
		}
		
	    echo "<td valign=top>";
	    if ($todo["expire_timestamp"] == "0000-00-00 00:00:00"){
			echo __('No expiration date');
		} else {
			echo $todo["expire_timestamp"];
		}
		echo '<td align="center" valign=top>';
		echo '<a href="index.php?sec=godmode&sec2=godmode/setup/newsboard&operation=delete&id='.$todo["id"].'" onClick="if (!confirm(\' '.__('Are you sure?').'\')) return false;"><img border=0 src="images/cross.png"></a>';

        echo "<tr><td colspan=3 style='border-bottom: 1px solid #acacac'>";
	echo clean_output ($todo["content"]);
	}
	echo "</table>";


    echo '<form method="post" action="index.php?sec=godmode&sec2=godmode/setup/newsboard&operation=create">';
	echo '<div style="width: 100%; text-align: right;">';
	print_submit_button (__('Create'), 'crt', false, 'class="sub create');
	echo '</form></div>';

} // Fin bloque else

?>
<script type="text/javascript" src="include/js/jquery.ui.slider.js"></script>
<script type="text/javascript" src="include/js/jquery.ui.datepicker.js"></script>
<script type="text/javascript" src="include/languages/date_<?php echo $config['language_code']; ?>.js"></script>
<script type="text/javascript" src="include/js/integria_date.js"></script>
<script type="text/javascript" src="include/js/tiny_mce/tiny_mce.js"></script>

<script type="text/javascript">
$(document).ready (function () {
/*
	$("#textarea-description").TextAreaResizer ();
*/
	tinyMCE.init({
			mode : "exact",
			elements: "textarea-content",
			theme : "advanced",
			theme_advanced_toolbar_location : "top",
			theme_advanced_toolbar_align : "left",
			theme_advanced_buttons1 : "bold,italic, |, image, link, |, cut, copy, paste, |, undo, redo, |, forecolor, |, fontsizeselect, |, justifyleft, justifycenter, justifyright",
			theme_advanced_buttons2 : "",
			theme_advanced_buttons3 : "",
			convert_urls : false,
			theme_advanced_statusbar_location : "none"
		});
		
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

	$("#checkbox-expire").click(function() {
		check_expire();
	});
});

check_expire();

add_datepicker ("#text-expire_date");

function check_expire() {
	if ($("#checkbox-expire").is(":checked")) {
		$('#label-text-expire_date').css('visibility', '');
		$('#label-text-expire_time').css('visibility', '');
		$('#text-expire_date').css('visibility', '');
		$('#text-expire_time').css('visibility', '');
	}
	else {
		$('#label-text-expire_date').css('visibility', 'hidden');
		$('#label-text-expire_time').css('visibility', 'hidden');
		$('#text-expire_date').css('visibility', 'hidden');
		$('#text-expire_time').css('visibility', 'hidden');
	}
}
</script>
