<?PHP

if (isset($_GET["sec"]))
	$sec = $_GET["sec"];
else
	$sec = "";

if (isset($_GET["sec2"]))
	$sec2 = $_GET["sec2"];
else
	$sec2 = "";

if ($sec == "projects"){
	echo "<h1>Projects</h1>";
	echo "<ul class='sidemenu'>";
	echo "<li><a href='index.php?sec=projects&sec2=operation/projects/project'>Project overview</a></li>";
	//echo "<li><a href=''>Project report</a></li>";
	echo "</ul>";
}		

if ($sec == "incidents"){
	echo "<h1>Incidents</h1>";
	echo "<ul class='sidemenu'>";
		if ($sec2 == "operation/incidents/incident")
			echo "<li id='sidesel'>";
		else	
			echo "<li>";
		echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident'>Incident overview</a></li>";
		if ($sec2 == "operation/incidents/incident_search")
			echo "<li id='sidesel'>";
		else	
			echo "<li>";
		echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident_search'>Search incident</a></li>";
	echo "</ul>";
}


if ($sec == "users"){
	echo "<h1>Users</h1>";
	echo "<ul class='sidemenu'>";
		echo "<li><a href='index.php?sec=users&amp;sec2=operation/users/user'>View users</a></li>";
		echo "<li><a href='index.php?sec=users&sec2=operation/users/user_edit&amp;ver=".$_SESSION["id_usuario"]."'>Edit my user</a></li>";
	echo "</ul>";
}


if ($sec == "messages"){
	echo "<h1>Messages</h1>";
	echo "<ul class='sidemenu'>";
		echo "<li><a href='index.php?sec=messages&sec2=operation/messages/message'>Read messages</a></li>";
		echo "<li><a href='index.php?sec=messages&amp;sec2=operation/messages/message&nuevo'>Write message</a></li>";
		echo "<li><a href='index.php?sec=messages&amp;sec2=operation/messages/message&nuevo_g'>Write to group</a></li>";
	echo "</ul>";
}

if ($sec == "assets"){
	echo "<h1>Assets</h1>";
	echo "<ul class='sidemenu'>";
		echo "<li><a href='index.html'>Asset overview</a></li>";
	echo "</ul>";
}

// LINKS

echo "<h1>".$lang_label["links_header"]."</h1>";
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
echo "</ul>";

// Banners

echo "<h1>Banners</h1>";
echo "<p>";
echo "<img src='images/minilogoartica.jpg'>";
echo "<br><br>";
echo "<img src='images/sflogo.png'>";
echo "<br><br>";
echo "</p>";


?>