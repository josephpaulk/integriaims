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

// Load global vars

global $config;
check_login ();

require_once('include/functions_user.php');


echo "<h1>".__("Holidays calendar")."</h1>";
echo "<div id='calendar'></div>";

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
			firstDay: 1,
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
            		url: 'ajax.php?page=include/ajax/calendar&get_holidays=1&ajax=1&start_date='+start_time+'&end_date='+end_time,
            		dataType: 'json',
            		type: "POST",
            		success: function(data) {

                		var events = [];
                		
                		$(data).each(function() {
                			
                			var obj = $(this);
                			var title_str = obj[0].name;
                			var days = obj[0].dates;
                			var bgColor = obj[0].bgColor;

                			//Print holidays
                			days.forEach(function (element, index, array) {
                				start_date = new Date(element.start);
                				end_date = new Date(element.end);
                    			events.push({title: title_str, start: start_date, end: end_date, color: bgColor});
                			});
                		});
                		callback(events);
            		}
        		});
    		}
		});
		
	});

</script>