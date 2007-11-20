<?php

// FRITS - the FRee Incident Tracking System
// =========================================
// Copyright (c) 2007 Sancho Lerena, slerena@openideas.info
// Copyright (c) 2007 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// PHP Calendar (version 2.3), written by Keith Devens
// http://keithdevens.com/software/php_calendar
//  see example at http://keithdevens.com/weblog
// License: http://keithdevens.com/software/license
// Modified by Sancho Lerena  <slerena@gmail.com>, 2007 (created generate_calendar_agenda modified function).

function generate_calendar_agenda ($year, $month, $days = array(), $day_name_length = 3, $month_href = NULL, $first_day = 0, $pn = array(), $id_user = "" ){
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
	
	$calendar = $calendar . '</h2><table border=1 cellpadding=10 cellspacing=0>'."\n";
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
		$sqlquery = "SELECT * FROM tagenda WHERE id_user = '$id_user' AND timestamp LIKE '$mysql_date%' ORDER BY timestamp ASC";
 		$res=mysql_query($sqlquery);
		while ($row=mysql_fetch_array($res)){
			$mysql_time = substr($row["timestamp"],11);
			$event_string = substr($row["content"],0,150);
			$event_privacy = $row["public"];
			$event_alarm = $row["alarm"];
			$event_user = $row["id_user"];
			$calendar .= $mysql_time."&nbsp;";
			if ($event_alarm > 0)
				$calendar .= "<img src='images/bell.png'>";
			if ($event_privacy > 0)
				$calendar .= "<img src='images/user_comment.png'>";
			$calendar .= "<img src='images/cancel.gif'>";
			$calendar .= "<br><hr width=110><font size='1pt'>[$event_user] ".$event_string."</font><br><br>";
		}
		

	}
	if($weekday != 7) $calendar .= '<td colspan="'.(7-$weekday).'">&nbsp;</td>'; #remaining "empty" days
	return $calendar."</tr>\n</table>\n";
}

// Original function
function generate_calendar($year, $month, $days = array(), $day_name_length = 3, $month_href = NULL, $first_day = 0, $pn = array()){
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
	
	$calendar = $calendar . '</h2><table border=1 cellpadding=10 cellspacing=0>'."\n";
	if($day_name_length){ #if the day names should be shown ($day_name_length > 0)
		#if day_name_length is >3, the full name of the day will be printed
		foreach($day_names as $d)
			$calendar .= '<th width=110 abbr="'.htmlentities($d).'">'.htmlentities($day_name_length < 4 ? substr($d,0,$day_name_length) : $d).'</th>';
		$calendar .= '<th width=110>'.lang_string("Week Total").'</th>';
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
					$calendar .= "<td><b><center><a  href='index.php?sec=users&sec2=operation/users/user_workunit_report&id=".$id_user."&timestamp_l=".$before_week."&timestamp_h=".$this_week."'>".$workhours." ".lang_string("hr")."</a></center></b></td>";
				} else {
					$calendar .= "<td> -- </td>";
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

		$sqlquery = "SELECT SUM(duration) FROM tworkunit WHERE id_user = '$id_user' AND timestamp LIKE '$mysql_date%'";
 		$res=mysql_query($sqlquery);
		if ($row=mysql_fetch_array($res)){
			$workhours = $row[0];
			if ($workhours > 0){

				$calendar .= "<center><a  href='index.php?sec=users&sec2=operation/users/user_workunit_report&id=".$id_user."&timestamp_l=".$mysql_date. " 00:00:00"."&timestamp_h=".$mysql_date."  23:59:59'>".$workhours." ".lang_string("hr")."</a></center>";

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
				$calendar .= "<td><b><center><a  href='index.php?sec=users&sec2=operation/users/user_workunit_report&id=".$id_user."&timestamp_l=".$before_week."&timestamp_h=".$this_week."'>".$workhours." ".lang_string("hr")."</a></center></b></td>";
			} else {
				$calendar .= "<td><center> -- </center></td>";
			}
		}
		$calendar .= "</tr>\n<tr>";
	}
	return $calendar."</tr>\n</table>\n";
}


?>

