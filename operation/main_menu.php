<?PHP

if (isset($_GET["sec"]))
	$sec = $_GET["sec"];
else
	$sec = "";

echo "<ul>";

// Project
if ($sec == "projects" )
	echo "<li id='current'>";
else
	echo "<li>";
echo "<a href='index.php?sec=projects&sec2=operation/projects/project'>Project</a></li>";

// Incident
if ($sec == "incidents" )
	echo "<li id='current'>";
else
	echo "<li>";
echo "<a href='index.php?sec=incidents&sec2=operation/incidents/incident'>Incidents</a></li>";

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
echo "<a href='index.php?sec=messages&sec2=operation/messages/message'>".$lang_label["messages"]."</a></li>";


// Agenda
if ($sec == "agenda" )
	echo "<li id='current'>";
else
	echo "<li>";
echo "<a href='index.php?sec=agenda&sec2=operation/agenda/agenda'>".$lang_label["agenda"]."</a></li>";
echo "</ul>";

?>