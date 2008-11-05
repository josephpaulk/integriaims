<?php

// Sponsors / Banner
echo "<div class='portlet' >";
echo "<h3 class='system'>".__('Links')."</h3>";
echo "<ul class='sidemenu'>";

$sql1='SELECT * FROM tlink ORDER BY name';
$result=mysql_query($sql1);
if ($row=mysql_fetch_array($result)){
	$sql1='SELECT * FROM tlink ORDER BY name';
	$result2=mysql_query($sql1);
	while ($row2=mysql_fetch_array($result2)){
		echo "<li><a href='".$row2["link"]."' target='_new' class='mn'>".$row2["name"]."</a></li>";
	}
}
echo "</ul></div>";


// Banners
echo '<div class="portlet">';
echo "<h3 class='system'>".__('Our sponsors')."</h3>";
echo "<p>";
echo "<img src='images/minilogoartica.jpg'>";
echo "<br><br>";
echo "<img src='images/sflogo.png'>";
echo "<br><br>";
echo "</p>";
echo "</div>";


?>
