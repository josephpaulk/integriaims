<?php
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2008-2010 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

global $config;
require_once ('include/functions_user.php');

$result_msg = "";

check_login ();

$id_grupo = (int) get_parameter ('id_grupo');

if (! give_acl ($config['id_user'], $id_grupo, "AR")) {
 	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access agenda of group ".$id_grupo);
	include ("general/noaccess.php");
	exit;
}

echo '<h1>' . __('Agenda').'</h1>';

echo "<table class='calendar_legend'>";
echo "<tr>";
echo "<td class='legend_color_box legend_project'></td>";
echo "<td>".__("Projects")."</td>";
echo "<td class='legend_color_box legend_task'></td>";
echo "<td>".__("Tasks")."</td>";
echo "<td class='legend_color_box legend_wo'></td>";
echo "<td>".__("Workorders")."</td>";
echo "<td class='legend_color_box legend_event'></td>";
echo "<td class='legend_last_box'>".__("Events")."</td>";
echo "</tr>";
echo "</table>";

echo "<div id='calendar'></div>";

$start_date = 1377986400;
$end_date = 1381615200;

?>

<link href='include/js/fullcalendar/fullcalendar.css' rel='stylesheet' />
<link href='include/js/fullcalendar/fullcalendar.print.css' rel='stylesheet' media='print' />
<script src='include/js/fullcalendar/fullcalendar.min.js'></script>

<script>

	$(document).ready(function() {
	
		var date = new Date();
		var d = date.getDate();
		var m = date.getMonth();
		var y = date.getFullYear();

		$('#calendar').fullCalendar({
			header: {
				left: 'today',
				center: 'prev,title,next',
				right: 'month,agendaWeek,agendaDay'
			},
			buttonText: {
				prev: '<img src="images/control_rewind_blue.png" title="<?php echo __("Prev");?>" class="calendar_arrow">',
   				next: '<img src="images/control_fastforward_blue.png" title="<?php echo __("Prev");?>" class="calendar_arrow">',
   			},
			editable: false,
			events: function(start, end, callback) {
				var date_aux = new Date(start);
        		var start_time = date_aux.getTime();

        		start_time = start_time/1000; //Convert from miliseconds to seconds
        			
        		date_aux = new Date(end);
        		var end_time = date_aux.getTime();

        		end_time = end_time/1000; //Convert from miliseconds to seconds

        		$.ajax({
            		url: 'ajax.php?page=include/ajax/calendar&get_events=1&ajax=1&start_date='+start_time+'&end_date='+end_time,
            		dataType: 'json',
            		type: "POST",
            		success: function(data) {
                		var events = [];
                		
                		$(data).each(function() {
                			
                			var obj = $(this);
                			var title_str = obj[0].name;
                			var start_str = obj[0].start;
                			var end_str = obj[0].end;
                			var bgColor = obj[0].bgColor;
                			var allDayEvent = obj[0].allDay;
                			var link = obj[0].url;

                			//Convert dates to JS object date
                			start_date = new Date(start_str);

                			var end_date = start_date;
                			if (end_str) {
                				end_date = new Date(end_str);                			
                			}

                    		events.push({title: title_str, start: start_date, end: end_date, color: bgColor, allDay: allDayEvent, url: link});
                		});
                		callback(events);
            		}
        		});
    		}
		});
		
	});

</script>