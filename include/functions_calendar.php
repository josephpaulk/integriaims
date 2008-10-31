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

// PHP Calendar (version 2.3), written by Keith Devens
// http://keithdevens.com/software/php_calendar
// License: Artistic Licence

function generate_calendar_agenda ($year, $month, $days = array(), $day_name_length = 3, $month_href = NULL, $first_day = 0, $pn = array(), $id_user = "" ){
    global $config;

	$first_of_month = gmmktime(0,0,0,$month,1,$year);
	#remember that mktime will automatically correct if invalid dates are entered
	# for instance, mktime(0,0,0,12,32,1997) will be the date for Jan 1, 1998
	# this provides a built in "rounding" feature to generate_calendar()

	$day_names = array(); #generate all the day names according to the current locale
	for($n=0,$t=(3+$first_day)*86400; $n<7; $n++,$t+=86400) #January 4, 1970 was a Sunday
		$day_names[$n] = ucfirst(gmstrftime('%A',$t)); #%A means full textual day name

	list($month, $year, $month_name, $weekday) = explode(',',gmstrftime('%m,%Y,%B,%w',$first_of_month));
	$weekday = ($weekday + 7 - $first_day) % 7; #adjust for $first_day
	$title   = htmlentities(ucfirst($month_name)).'&nbsp;'.$year;


	#Begin calendar. Uses a real <caption>. See http://diveintomark.org/archives/2002/07/03
	@list($p, $pl) = each($pn); @list($n, $nl) = each($pn); #previous and next links, if applicable
	if($p) $p = ''.($pl ? '<a href="'.htmlspecialchars($pl).'">'.$p.'</a>' : $p).'&nbsp;';
	if($n) $n = '&nbsp;'.($nl ? '<a href="'.htmlspecialchars($nl).'">'.$n.'</a>' : $n);

	$calendar = "";
	$calendar = '<center><h2>'."\n".
	$calendar = $calendar .$p.($month_href ? '<a href="'.htmlspecialchars($month_href).'">'.$title.'</a>' : $title).$n."</center>";
	
	$calendar = $calendar . '</h2><table class="blank" border=1 cellpadding=10 cellspacing=0>'."\n";
	if($day_name_length){ #if the day names should be shown ($day_name_length > 0)
		#if day_name_length is >3, the full name of the day will be printed
		foreach($day_names as $d)
			$calendar .= '<th width=110 abbr="'.htmlentities($d).'">'.htmlentities($day_name_length < 4 ? substr($d,0,$day_name_length) : $d).'</th>';
		$calendar .= "</tr>\n<tr>";
	}
	$time = time();
	$today = date('j',$time);
	$today_m = date('n',$time);

	if($weekday > 0) $calendar .= '<td colspan="'.$weekday.'">&nbsp;</td>'; #initial 'empty' days
	for($day=1,$days_in_month=gmdate('t',$first_of_month); $day<=$days_in_month; $day++,$weekday++){
		if($weekday == 7){
			$weekday   = 0; #start a new week
			$calendar .= "</tr>\n<tr>";
		}
		if(isset($days[$day]) and is_array($days[$day])){
			@list($link, $classes, $content) = $days[$day];
			if(is_null($content))  $content  = $day;
			$calendar .= '<td'.($classes ? ' class="'.htmlspecialchars($classes).'">' : '>').
				($link ? '<b><a href="'.htmlspecialchars($link).'">'.$content.'</a>' : $content).'</b><br><br><br><br><br></td>';
		}

		if (($day == $today) && ($today_m == $month))
			$calendar .= "<td valign='top' style='background: #eeb; width: 110px; height: 90px;' >";				
		else 
			$calendar .= "<td valign='top' style='background: #f9f9f5; height: 90px; width: 110px;' >";
		$calendar .=  "<b>$day</b><br><br>";

		$mysql_time= "";
		$event_string = "";
		$event_privacy = 0;
		$event_alarm = 0;

		if ($day < 10)
			$day = "0".$day;
		$mysql_date = "$year-$month-$day";
		$sqlquery = "SELECT * FROM tagenda WHERE timestamp LIKE '$mysql_date%' ORDER BY timestamp ASC";
 		$res=mysql_query($sqlquery);
		while ($row=mysql_fetch_array($res)){
			$mysql_time = substr($row["timestamp"],11);
			$event_string = substr($row["content"],0,150);
			$event_public = $row["public"];
			$event_alarm = $row["alarm"];
			$event_user = $row["id_user"];
            if (($event_user == $config["id_user"]) OR ($event_public == 1)){
			    $calendar .= $mysql_time."&nbsp;";
			    if ($event_alarm > 0)
				    $calendar .= "<img src='images/bell.png'>";
			    if ($event_public > 0)
				    $calendar .= "<img src='images/user_comment.png'>";
			    $calendar .= "<A href='index.php?sec=agenda&sec2=operation/agenda/agenda&delete_event=".$row[0]."'><img src='images/cancel.gif' border=0></A>";
			    $calendar .= "<br><hr width=110><font size='1pt'>[$event_user] ".$event_string."</font><br><br>";
            }
		}
	}
	if($weekday != 7) $calendar .= '<td colspan="'.(7-$weekday).'">&nbsp;</td>'; #remaining "empty" days
	return $calendar."</tr>\n</table>\n";
}

// Original function
function generate_calendar ($year, $month, $days = array(), $day_name_length = 3, $month_href = NULL, $first_day = 0, $pn = array()){
	$first_of_month = gmmktime(0,0,0,$month,1,$year);
	#remember that mktime will automatically correct if invalid dates are entered
	# for instance, mktime(0,0,0,12,32,1997) will be the date for Jan 1, 1998
	# this provides a built in "rounding" feature to generate_calendar()

	$day_names = array(); #generate all the day names according to the current locale
	for($n=0,$t=(3+$first_day)*86400; $n<7; $n++,$t+=86400) #January 4, 1970 was a Sunday
		$day_names[$n] = ucfirst(gmstrftime('%A',$t)); #%A means full textual day name

	list($month, $year, $month_name, $weekday) = explode(',',gmstrftime('%m,%Y,%B,%w',$first_of_month));
	$weekday = ($weekday + 7 - $first_day) % 7; #adjust for $first_day
	$title   = htmlentities(ucfirst($month_name)).'&nbsp;'.$year;  #note that some locales don't capitalize month and day names

	#Begin calendar. Uses a real <caption>. See http://diveintomark.org/archives/2002/07/03
	@list($p, $pl) = each($pn); @list($n, $nl) = each($pn); #previous and next links, if applicable
	if($p) $p = '<span class="calendar-prev">'.($pl ? '<a href="'.htmlspecialchars($pl).'">'.$p.'</a>' : $p).'</span>&nbsp;';
	if($n) $n = '&nbsp;<span class="calendar-next">'.($nl ? '<a href="'.htmlspecialchars($nl).'">'.$n.'</a>' : $n).'</span>';
	$calendar = '<table class="blank calendar">'."\n".
		'<caption class="calendar-month">'.$p.($month_href ? '<a href="'.htmlspecialchars($month_href).'">'.$title.'</a>' : $title).$n."</caption>\n<tr>";

	if($day_name_length){ #if the day names should be shown ($day_name_length > 0)
		#if day_name_length is >3, the full name of the day will be printed
		foreach($day_names as $d)
			$calendar .= '<th abbr="'.htmlentities($d).'">'.htmlentities($day_name_length < 4 ? substr($d,0,$day_name_length) : $d).'</th>';
		$calendar .= "</tr>\n<tr>";
	}

	if($weekday > 0) $calendar .= '<td colspan="'.$weekday.'">&nbsp;</td>'; #initial 'empty' days
	for($day=1,$days_in_month=gmdate('t',$first_of_month); $day<=$days_in_month; $day++,$weekday++){
		if($weekday == 7){
			$weekday   = 0; #start a new week
			$calendar .= "</tr>\n<tr>";
		}
		if(isset($days[$day]) and is_array($days[$day])){
			@list($link, $classes, $content) = $days[$day];
			if(is_null($content))  $content  = $day;
			$calendar .= '<td'.($classes ? ' class="'.htmlspecialchars($classes).'">' : '>').
				($link ? '<a href="'.htmlspecialchars($link).'">'.$content.'</a>' : $content).'</td>';
		}
		else $calendar .= "<td>$day</td>";
	}
	if($weekday != 7) $calendar .= '<td colspan="'.(7-$weekday).'">&nbsp;</td>'; #remaining "empty" days

	return $calendar."</tr>\n</table>\n";
}



// Original function
function generate_small_work_calendar ($year, $month, $days = array(), $day_name_length = 3, $first_day = 0, $pn = array(), $id_user = ""){
	$first_of_month = gmmktime(0,0,0,$month,1,$year);
	#remember that mktime will automatically correct if invalid dates are entered
	# for instance, mktime(0,0,0,12,32,1997) will be the date for Jan 1, 1998
	# this provides a built in "rounding" feature to generate_calendar()

	$day_names = array(); #generate all the day names according to the current locale
	for($n=0,$t=(3+$first_day)*86400; $n<7; $n++,$t+=86400) #January 4, 1970 was a Sunday
		$day_names[$n] = ucfirst(gmstrftime('%A',$t)); #%A means full textual day name

	list($month, $year, $month_name, $weekday) = explode(',',gmstrftime('%m,%Y,%B,%w',$first_of_month));
	$weekday = ($weekday + 7 - $first_day) % 7; #adjust for $first_day
	$title   = htmlentities(ucfirst($month_name)).'&nbsp;'.$year;  #note that some locales don't capitalize month and day names

	#Begin calendar. Uses a real <caption>. See http://diveintomark.org/archives/2002/07/03
	@list($p, $pl) = each($pn); @list($n, $nl) = each($pn); #previous and next links, if applicable
	if($p) $p = '<span class="calendar-prev">'.($pl ? '<a href="'.htmlspecialchars($pl).'">'.$p.'</a>' : $p).'</span>&nbsp;';
	if($n) $n = '&nbsp;<span class="calendar-next">'.($nl ? '<a href="'.htmlspecialchars($nl).'">'.$n.'</a>' : $n).'</span>';
	$calendar = '<table class="calendar">'."\n".
		'<caption class="calendar-month">'.$p.'<a href="index.php?sec=users&sec2=operation/user_report/monthly&month='.$month.'&year='.$year.'&id='.$id_user.'">'.$title.'</a>'.$n."</caption>\n<tr>";

	if($day_name_length){ #if the day names should be shown ($day_name_length > 0)
		#if day_name_length is >3, the full name of the day will be printed
		foreach($day_names as $d)
			$calendar .= '<th abbr="'.htmlentities($d).'">'.htmlentities($day_name_length < 4 ? substr($d,0,$day_name_length) : $d).'</th>';
		$calendar .= "</tr>\n<tr>";
	}

	if($weekday > 0) $calendar .= '<td class="calendar" colspan="'.$weekday.'">&nbsp;</td>'; #initial 'empty' days
	for($day=1,$days_in_month=gmdate('t',$first_of_month); $day<=$days_in_month; $day++,$weekday++){
		if($weekday == 7){
			$weekday   = 0; #start a new week
			$calendar .= "</tr>\n<tr>";
		}

		// Show SUM workunits for that day (GREEN) - standard wu
		$sqlquery = "SELECT SUM(tworkunit.duration) FROM tworkunit, tworkunit_task WHERE tworkunit_task.id_workunit = tworkunit.id AND tworkunit_task.id_task > 0 AND id_user = '$id_user' AND timestamp >= '$year-$month-$day 00:00:00' AND timestamp <= '$year-$month-$day 23:59:59' ";
		$normal = 0;
		$res=mysql_query($sqlquery);
		if ($row=mysql_fetch_array($res)){
			$workhours_a = $row[0];
			if ($workhours_a > 0){
				$normal = 1;
            }
		}

		// Show SUM workunits for that day (YELLOW) - holidays
		$sqlquery = "SELECT SUM(tworkunit.duration) FROM tworkunit, tworkunit_task WHERE tworkunit_task.id_workunit = tworkunit.id AND tworkunit_task.id_task =-1 AND id_user = '$id_user' AND timestamp >= '$year-$month-$day 00:00:00' AND timestamp <= '$year-$month-$day 23:59:59' ";

		$res=mysql_query($sqlquery);
		if ($row=mysql_fetch_array($res)){
			$workhours_b = $row[0];
			if ($workhours_b > 0){
				$normal = 2;
            }
		}

		// Show SUM workunits for that day (MAGENDA) - task wu
		$sqlquery = "SELECT SUM(tworkunit.duration) FROM tworkunit, tworkunit_incident WHERE tworkunit_incident.id_workunit = tworkunit.id AND tworkunit_incident.id_incident != -1 AND id_user = '$id_user' AND timestamp >= '$year-$month-$day 00:00:00' AND timestamp <= '$year-$month-$day 23:59:59' ";

		$res=mysql_query($sqlquery);
		if ($row=mysql_fetch_array($res)){
			$workhours_c = $row[0];
			if ($workhours_c > 0){
				$normal = 3;
            }
		}
		
		$mylink = "index.php?sec=users&sec2=operation/users/user_workunit_report&id=$id_user&timestamp_l=$year-$month-$day 00:00:00&timestamp_h=$year-$month-$day 23:59:59";

	    if ($normal == 0)
    		$calendar .= "<td class='calendar'>$day</td>";
        elseif ($normal == 1)
            $calendar .= "<td class='calendar' style='background-color: #98FF8B;'><a href='$mylink' title='$workhours_a'>$day</A></td>";
        elseif ($normal == 2)
            $calendar .= "<td class='calendar' style='background-color: #FFFF80;'><a href='$mylink' title='$workhours_b'>$day</a></td>";
        elseif ($normal == 3) {
            $total_wu = $workhours_a + $workhours_c;
            $calendar .= "<td class='calendar' style='background-color: #FF7BFE;'><a href='$mylink' title='$total_wu'>$day</a></td>";
        }
	}
	if($weekday != 7) $calendar .= '<td class=calendar" colspan="'.(7-$weekday).'">&nbsp;</td>'; #remaining "empty" days

	return $calendar."</tr>\n</table>\n";
}

function generate_work_calendar ($year, $month, $days = array(), $day_name_length = 3, $month_href = NULL, $first_day = 0, $pn = array(), $id_user = "" ){

	$first_of_month = gmmktime(0,0,0,$month,1,$year);
	#remember that mktime will automatically correct if invalid dates are entered
	# for instance, mktime(0,0,0,12,32,1997) will be the date for Jan 1, 1998
	# this provides a built in "rounding" feature to generate_calendar()

	$day_names = array(); #generate all the day names according to the current locale
	for($n=0,$t=(3+$first_day)*86400; $n<7; $n++,$t+=86400) #January 4, 1970 was a Sunday
		$day_names[$n] = ucfirst(gmstrftime('%A',$t)); #%A means full textual day name

	list($month, $year, $month_name, $weekday) = explode(',',gmstrftime('%m,%Y,%B,%w',$first_of_month));
	$weekday = ($weekday + 7 - $first_day) % 7; #adjust for $first_day
	$title   = htmlentities(ucfirst($month_name)).'&nbsp;'.$year;


	#Begin calendar. Uses a real <caption>. See http://diveintomark.org/archives/2002/07/03
	@list($p, $pl) = each($pn); @list($n, $nl) = each($pn); #previous and next links, if applicable
	if($p) $p = ''.($pl ? '<a href="'.htmlspecialchars($pl).'">'.$p.'</a>' : $p).'&nbsp;';
	if($n) $n = '&nbsp;'.($nl ? '<a href="'.htmlspecialchars($nl).'">'.$n.'</a>' : $n);

	$calendar = "";
	$calendar = '<center><h2>'."\n".
	$calendar = $calendar .$p.($month_href ? '<a href="'.htmlspecialchars($month_href).'">'.$title.'</a>' : $title).$n."</center>";
	
	$calendar = $calendar . '</h2><table class="blank" border=1 cellpadding=10 cellspacing=0>'."\n";
	if($day_name_length){ #if the day names should be shown ($day_name_length > 0)
		#if day_name_length is >3, the full name of the day will be printed
		foreach($day_names as $d)
			$calendar .= '<th width=70 abbr="'.htmlentities($d).'">'.htmlentities($day_name_length < 4 ? substr($d,0,$day_name_length) : $d).'</th>';
		$calendar .= '<th width=70>'.__('Week Total').'</th>';
		$calendar .= "</tr>\n<tr>";
	}
	$time = time();
	$today = date('j',$time);
	$today_m = date('n',$time);

	if($weekday > 0) $calendar .= '<td colspan="'.$weekday.'">&nbsp;</td>'; #initial 'empty' days
	for($day=1,$days_in_month=gmdate('t',$first_of_month); $day<=$days_in_month; $day++,$weekday++){
		if($weekday == 7){
			$weekday   = 0; #start a new week
			$ending_week = "$year-$month-$day 00:00:00";
			if ($day < 8) {
				$before_week = date('Y-m-d H:i:s',strtotime("$year-$month-1"));
			} else {
				$before_week = date('Y-m-d H:i:s',strtotime("$ending_week - 1 week"));
			}
			$this_week = date('Y-m-d H:i:s',strtotime("$year-$month-$day"));

			// Show SUM for that week
			$sqlquery = "SELECT SUM(duration) FROM tworkunit WHERE id_user = '$id_user' AND timestamp < '$this_week' AND timestamp >= '$before_week'";
 			$res=mysql_query($sqlquery);
			if ($row=mysql_fetch_array($res)){
				$workhours = $row[0];
				if ($workhours > 0){
					$calendar .= "<td style='background-color: #e3e9e9;'><b><center><a  href='index.php?sec=users&sec2=operation/users/user_workunit_report&id=".$id_user."&timestamp_l=".$before_week."&timestamp_h=".$this_week."'>".$workhours." ".__('Hours')."</a></center></b></td>";
				} else {
					$calendar .= "<td style='background-color: #e3e9e9;'><center> -- </center></td>";
				}
			}		
			$calendar .= "</tr>\n<tr>";
		}
		if(isset($days[$day]) and is_array($days[$day])){
			@list($link, $classes, $content) = $days[$day];
			if (is_null($content))
				$content  = $day;
			$calendar .= '<td'.($classes ? ' class="'.htmlspecialchars($classes).'">' : '>').
				($link ? '<b><a href="'.htmlspecialchars($link).'">'.$content.'</a>' : $content).'</b><br><br><br><br><br></td>';
		}

		if (($day == $today) && ($today_m == $month))
			$calendar .= "<td valign='top' style='background: #eeb; width: 70px; height: 70px;' >";				
		else { // standard day
        if (is_working_day("$year-$month-$day") == 1)
    		$calendar .= "<td valign='top' style='background: #f9f9f5; height: 70px; width: 70px;' >";
        else
            $calendar .= "<td valign='top' style='background: #e9e9e5; height: 70px; width: 70px;' >";
            
        }
		$calendar .=  "<b><a href='index.php?sec=users&sec2=operation/users/user_spare_workunit&givendate=$year-$month-$day'>$day</A</b><br><br>";

		$mysql_time= "";
		$event_string = "";
		$event_privacy = 0;
		$event_alarm = 0;

		if ($day < 10)
			$day = "0".$day;
		$mysql_date = "$year-$month-$day";

		$sqlquery = "SELECT SUM(duration) FROM tworkunit WHERE id_user = '$id_user' AND timestamp LIKE '$mysql_date%'";
 		$res=mysql_query($sqlquery);
		if ($row=mysql_fetch_array($res)){
			$workhours = $row[0];
			if ($workhours > 0){

				$calendar .= "<center><a  href='index.php?sec=users&sec2=operation/users/user_workunit_report&id=".$id_user."&timestamp_l=".$mysql_date. " 00:00:00"."&timestamp_h=".$mysql_date."  23:59:59'>".$workhours." ".__('Hours')."</a></center>";

				//$calendar .= "<a  href='index.php?sec=users&sec2=operation/users/user_workunit_report'>".$workhours." <img border=0 src='images/award_star_silver_1.png'></a>";
			}
		}
		

	}
	if($weekday != 7) { // remaining "empty" days
		$calendar .= '<td colspan="'.(7-$weekday).'">&nbsp;</td>'; 
		$day --;
		$ending_week = "$year-$month-$day 23:59:59";
		$weekday_offset = 7 - $weekday;
		$before_week = date('Y-m-d H:i:s',strtotime("$ending_week - $weekday days"));	
		$this_week = date('Y-m-d H:i:s',strtotime("$year-$month-$day 23:59.00"));
		// Show SUM for that week
		$sqlquery = "SELECT SUM(duration) FROM tworkunit WHERE id_user = '$id_user' AND timestamp < '$this_week' AND timestamp > '$before_week'";
		$res=mysql_query($sqlquery);
		if ($row=mysql_fetch_array($res)){
			$workhours = $row[0];
			if ($workhours > 0){
				$calendar .= "<td><b><center><a  href='index.php?sec=users&sec2=operation/users/user_workunit_report&id=".$id_user."&timestamp_l=".$before_week."&timestamp_h=".$this_week."'>".$workhours." ".__('Hours')."</a></center></b></td>";
			} else {
				$calendar .= "<td style='background-color: #e3e9e9;'><center> -- </center></td>";
			}
		}
		$calendar .= "</tr>\n<tr>";
	}
	return $calendar."</tr>\n</table>\n";
}


// This functions calculates the next date only using business days
// First parameter is entered in YYYY-MM-DD, and second is hours

function calcdate_business ($datecalc, $duedays) {
    $datecalc = strtotime ($datecalc);
    $i = 1;
    while ($i <= $duedays) {
        $datecalc += 86400; // Add a day.
        $date_info  = getdate( $datecalc );
        if (($date_info["wday"] == 0) or ($date_info["wday"] == 6) )  {
            $datecalc += 86400; // Add a day.
            continue;
        }
        $i++;
    }
    return date ("Y-m-d", $datecalc);
}

// This functions calculates the previous date only using business days
// First parameter is entered in YYYY-MM-DD, and second is hours

function calcdate_business_prev ($datecalc, $duedays) {
	$datecalc = strtotime ($datecalc);
	$i = 1;
	while ($i <= $duedays) {
		$datecalc -= 86400; // Add a day.
		$date_info  = getdate( $datecalc );
		if (($date_info["wday"] == 0) or ($date_info["wday"] == 6) )  {
			$datecalc -= 86400; // Add a day.
			continue;
		}
		$i++;
	}
	return date ("Y-m-d", $datecalc);
}

function is_working_day ($datecalc) {
	$datecalc = strtotime ($datecalc);
	$date1 = getdate($datecalc);
	if (($date1["wday"] == 0) OR ($date1["wday"] == 6))
		return 0;
	else
		return 1;

	// TODO: Add check for returning special days that are non-working
}

// ---------------------------------------------------------------
// Return string with time-threshold in secs, mins, days or weeks
// ---------------------------------------------------------------
// $flag_hide_zero Used to hide 0's in higher periods
function give_human_time ($int_seconds, $flag_hide_zero = true) {
	$key_suffix = 's';
	$periods = array (
		'year'   => 31556926,
		'month'  => 2629743,
		'day'    => 86400,
		'hour'   => 3600,
		'minute' => 60,
		'second' => 1
	);

	// do the loop thang
	foreach ($periods as $key => $length) {
		// calculate
		$temp = floor ($int_seconds / $length);

		// determine if temp qualifies to be passed to output
		if (!$flag_hide_zero || $temp > 0) {
			// store in an array
			$build[] = $temp.' '.$key.($temp!=1?'s':null);

			// set flag to false, to allow 0's in lower periods
			$flag_hide_zero = false;
		}

		// get the remainder of seconds
		$int_seconds = fmod($int_seconds, $length);
	}

	// return output, if !empty, implode into string, else output $if_reached
	return ( !empty($build)?implode(', ', $build):__('Unknown'));
}

function human_time_comparation ($timestamp) {
	global $config;
	$render = "";
	$now = time ();
	$time = strtotime ($timestamp);
	$seconds = abs ($time - $now);
	
	if ($seconds < 60)
		$render = format_numeric ($seconds, 0)." ".__('seconds');
	
	if ($seconds < 3600) {
		$minutes = format_numeric ($seconds / 60, 0);
		$seconds = format_numeric ($seconds % 60, 0);
		if ($seconds == 0)
			$render = $minutes.' '.__('minutes');
		$seconds = sprintf ("%02d", $seconds);
		$render = $minutes.':'.$seconds.' '.__('minutes');
	}
	if ($seconds > 3600 && $seconds < 86400)
		$render = format_numeric ($seconds / 3600, 0)." ".__('hours');
	
	if ($seconds > 86400 && $seconds < 2592000)
		$render = format_numeric ($seconds / 86400, 0)." ".__('days');
	
	if ($seconds > 2592000 && $seconds < 15552000)
		$render = format_numeric ($seconds / 2592000, 0)." ".__('months');
	
	$direction = ($now < $time) ? '> ' : '< ';
	return $direction.$render;
}


function working_days ($month = "", $year = "" ){
	if (($month == "") OR ($year == "")){
		$date = date('Y-m-d');
		$year = substr($date, 0,4);
		$month = substr($date, 5, 2);
	}
	
	$d_daysinmonth = date('t', mktime(0,0,0,$month,1,$year));  // how many days in month
	$full_weeks = ceil ($d_daysinmonth / 7);
	$festive_days = floor(($d_daysinmonth / 7) * 2);
	$total_working_days = $d_daysinmonth - $festive_days;
	$total_working_hours = $total_working_days * 8;
	return $total_working_days;
}

function working_weeks_combo () {
	$date = date('Y-m-d');
	$year = substr($date, 0,4);
	$month = substr($date, 5, 2);
	$day = substr($date, 8, 2);

	$d_daysinmonth = date('t', mktime(0,0,0,$month,1,$year));  // how many days in month
	$full_weeks = ceil ($d_daysinmonth / 7);
	$d_firstdow = date('w', mktime(0,0,0,$month,'1',$year));     // FIRST falls on what day of week (0-6)
	$ajuste = $d_firstdow -1;
	if ($ajuste >= 0)
		$new_date = date('Y-m-d', strtotime("$year-$month-01 - $ajuste days"));
	else {
		$ajuste = $ajuste * -1;
		$new_date = date('Y-m-d', strtotime("$year-$month-01 + $ajuste days"));
	}

	echo '<select name="working_week">';
	for ($ax=0; $ax < $full_weeks; $ax++){
		echo "<option>".date('Y-m-d', strtotime($new_date. "+ $ax week"));
	}
	echo "</select>";
}


function first_working_week (){
	$date = date('Y-m-d');
	$year = substr($date, 0,4);
	$month = substr($date, 5, 2);
	$day = substr($date, 8, 2);
	$d_daysinmonth = date('t', mktime(0,0,0,$month,1,$year));  // how many days in month
	$full_weeks = ceil ($d_daysinmonth / 7);
	$d_firstdow = date('w', mktime(0,0,0,$month,'1',$year));     // FIRST falls on what day of week (0-6)
	$ajuste = $d_firstdow -1;
	if ($ajuste >= 0)
		$new_date = date('Y-m-d', strtotime("$year-$month-01 - $ajuste days"));
	else {
		$ajuste = $ajuste * -1;
		$new_date = date('Y-m-d', strtotime("$year-$month-01 + $ajuste days"));
	}
	return $new_date;
}

function week_start_day (){
	return date('Y-m-d', date('U')-(date('w')+6)%7*86400);
}

function getmonth ($m = 0) {
	return (($m==0 ) ? date ("F") : date ("F", mktime (0, 0, 0, $m)));
}

?>
