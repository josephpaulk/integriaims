<?php

echo "<div width=100%>";
echo "<span>";

$nav_sec = give_parameter_get("sec","main");
$nav_sec2 = give_parameter_get("sec2","");

echo "Your location: ";

switch ($nav_sec) {
	case "incidents": $nav_sec_label = $lang_label["incidents"];
					break;
	case "projects": $nav_sec_label = $lang_label["projects"];
					break;
	case "agenda": $nav_sec_label = $lang_label["agenda"];
					break;
}

switch ($nav_sec2) {
	case "operation/incidents/incident": $nav_sec2_label = $lang_label["overview"];
					break;
}

echo "<b><a href='index.php'><img src='images/home_icon.gif' border=0> &nbsp;".$lang_label["home"]."</A>";
if (isset($nav_sec_label)){
	echo " -&gt; ";
	echo "<font color='#ffffff'>".$nav_sec_label."</font>";
	if (isset($nav_sec2_label)){
		echo " -&gt; ";
		echo "<font color='#ffffff'>".$nav_sec2_label."</font>";
	}
}
echo "</b>";
echo "</span>";
echo "</div>";

?>