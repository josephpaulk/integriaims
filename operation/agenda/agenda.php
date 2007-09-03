<?php

// TOPI - the Open Tracking System for the Enterprise
// ==================================================
// Copyright (c) 2007 Sancho Lerena, slerena@gmail.com

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.


?>

<script language="javascript">

	/* Function to hide/unhide a specific Div id */
	function toggleDiv (divid){
		if (document.getElementById(divid).style.display == 'none'){
			document.getElementById(divid).style.display = 'block';
		} else {
			document.getElementById(divid).style.display = 'none';
		}
	}
</script>

<?PHP

global $config;
include ("include/calendar.php");

$result_msg = "";

if (check_login() != 0) {
 	audit_db("Noauth",$config["REMOTE_ADDR"], "No authenticated access","Trying to access event viewer");
	require ("general/noaccess.php");
	exit;
}

if (isset($_GET["id_grupo"]))
	$id_grupo = $_GET["id_grupo"];
else
	$id_grupo = 0;

$id_user=$_SESSION['id_usuario'];
if (give_acl($id_user, $id_grupo, "IR") != 1){
 	// Doesn't have access to this page
	audit_db($id_user,$config["REMOTE_ADDR"], "ACL Violation","Trying to access to incident ".$id_inc." '".$titulo."'");
	include ("general/noaccess.php");
	exit;
}


// Add item
if (isset($_GET["create_item"])){
	$description = give_parameter_post ("description");
	$time = give_parameter_post ("time");
	$date = give_parameter_post ("date");
	$public = give_parameter_post ("public",0);
	$alarm = give_parameter_post ("alarm",0);
	$sql1 = "INSERT INTO tagenda (public, alarm, timestamp, id_user, content) VALUES 
			($public, '$alarm', '$date $time', '$id_user', '$description')";
	echo $sql1;
	$res1=mysql_query($sql1);
}

// Delete note
if (isset($_GET["delete_event"])){
	$id = give_parameter_get ("delete_event",0);
	$event_user = give_db_value ("id_user", "tagenda", "id", $id);
	
	if ($event_user == $id_user) { 
		// Only admins (manage incident) or owners can modify incidents, including their notes
		$query = "DELETE FROM tagenda WHERE id = ".$id;
		mysql_query($query);
	}
}

// Get parameters for actual Calendar show
$time = time();
$month = give_parameter_get ( "month", date('n', $time));
$year = give_parameter_get ( "year", date('y', $time));

$today = date('j',$time);
$days_f = array();
$first_of_month = gmmktime(0,0,0,$month,1,$year);
$days_in_month=gmdate('t',$first_of_month);
$locale = $config["language_code"];

// Calculate PREV button
if ($month == 1){
	$month_p = 12;
	$year_p = $year -1;
} else {
	$month_p = $month -1;
	$year_p = $year;
}

// Calculate NEXT button
if ($month == 12){
	$month_n = 1;
	$year_n = $year +1;
} else {
	$month_n = $month +1;
	$year_n = $year;
}

// Next month mini calendar
echo "<div align='right' style='float: right;'>";
echo generate_calendar ($year_n, $month_n, $days_f, 3, NULL, $locale);
echo "</div>";

echo "<div style='float: left;'>";
echo generate_calendar ($year_p, $month_p, $days_f, 3, NULL, $locale);
echo "</div>";


// Space to skip blocks
echo "<div style='height: 170px'> </div>";


$start_date = $mydate_sql = date("Y-m-d", time());

$pn = array('&laquo;'=>"index.php?sec=agenda&sec2=operation/agenda/agenda&month=$month_p&year=$year_p", '&raquo;'=>"index.php?sec=agenda&sec2=operation/agenda/agenda&month=$month_n&year=$year_n");

echo generate_calendar_agenda ($year, $month, $days_f, 3, NULL, $locale, $pn, $id_user);


// Legend for icons
	echo "<div style='float: right;'>";
	echo "<h3>".$lang_label["legend"]."</h3>";
	echo "<table width=150 cellspacing=10 border=0 class='databox'>";
	echo "<tr><td valign='top'>";
	echo "<img src='images/user_comment.png'>";
	echo "<td valign='top'>";
	echo "&nbsp;".$lang_label["public"];
	echo "<tr><td valign='top'>";
	echo "<img src='images/cancel.gif'>";
	echo "<td valign='top'>";
	echo "&nbsp;".$lang_label["delete"];
	echo "<tr><td valign='top'>";
	echo "<img src='images/bell.png'>";
	echo "<td valign='top'>";
	echo "&nbsp;".$lang_label["alert"];
	echo "</table>";
	echo "</div>";


// Add item control
	?>
		<h3><img src='images/note.png'>&nbsp;&nbsp;
		<a href="javascript:;" onmousedown="toggleDiv('calendar_control');">
	<?PHP
	echo $lang_label["add_agenda_entry"]."</A></h3>";

	echo "<div id='calendar_control' style='display:none'>";
	echo "<form method='post' action='index.php?sec=agenda&sec2=operation/agenda/agenda&month=$month&year=$year&create_item=1' enctype='multipart/form-data'>";
	echo "<table cellpadding=3 cellspacing=3 border=0 width='400' class='databox_color'>";
	echo '<td class="datos">'.$lang_label["description"].'</td>';
	echo '<td class="datos" colspan=3><input type="text" name="description" size=45>';
	
	echo '<tr><td class="datos2">'.$lang_label["public"].'</td>';
	echo '<td class="datos2"><input type="checkbox" name="public" value=1>';

	echo '<td class="datos2">'.$lang_label["alarm"].'</td>';
	echo '<td class="datos2"><select name="alarm">';
	echo "<option value=0>".$lang_label["N/A"];
	echo "<option value=60> 1 ".$lang_label["hour"];
	echo "<option value=120> 2 ".$lang_label["hour"];
	echo "<option value=240> 4 ".$lang_label["hour"];
	echo "<option value=1440> 1 ".$lang_label["day"];
	echo "</select>";



	echo '<tr><td class="datos">'.$lang_label["date"].'</td>';
	echo '<td class="datos">';
	echo "<input type='text' id='date' name='date' size=10 value='$start_date'> <img src='images/calendar_view_day.png' onclick='scwShow(scwID(\"date\"),this);'> ";

	echo '<td class="datos">'.$lang_label["time"].'</td>';
	echo '<td class="datos"><input type="text" name="time" value="00:00:00" size=12>';
	
	echo '<tr><td colspan="4" align="right"><input type="submit" class="sub next" value="'.$lang_label["upload"].'">';
	echo "</td></tr></table></form></div><br>";
	

	
?>