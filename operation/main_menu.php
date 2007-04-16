<?PHP

if (isset($_GET["sec"]))
	$sec = $_GET["sec"];
else
	$sec = "";

echo "<ul>";

if ($sec == "projects" )
	echo "<li id='current'><a href='index.php?sec=projects'>Project</a></li>";
else
	echo "<li><a href='index.php?sec=projects'>Project</a></li>";

if ($sec == "incidents" )
	echo "<li id='current'>";
else
	echo "<li>";
echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident'>Incidents</a></li>";

if ($sec == "assets" )
	echo "<li id='current'>";
else
	echo "<li>";
echo "<a href='index.php?sec=assets'>Assets</a></li>";

// Users
if ($sec == "users" )
	echo "<li id='current'>";
else
	echo "<li>";
echo "<a href='index.php?sec=users&sec2=operation/users/user'>Users</a></li>";

// Messages
if ($sec == "messages" )
	echo "<li id='current'>";
else
	echo "<li>";
echo "<a href='http://localhost/frits/index.php?sec=messages&sec2=operation/messages/message'>Messages</a></li>";

echo "</ul>";

?>