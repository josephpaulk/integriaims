<?PHP
// Integria 1.0 - http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2008 Artica Soluciones Tecnologicas

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

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