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

// TODO
if ($sec == "todo" )
	echo "<li id='current'>";
else
	echo "<li>";
echo "<a href='index.php?sec=todo&sec2=operation/todo/todo'>".lang_string("todo")."</a></li>";


// Agenda
if ($sec == "agenda" )
	echo "<li id='current'>";
else
	echo "<li>";
echo "<a href='index.php?sec=agenda&sec2=operation/agenda/agenda'>".$lang_label["agenda"]."</a></li>";
echo "</ul>";

?>