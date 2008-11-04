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


global $config;
check_login();

if (give_acl($config["id_user"], 0, "KR")==0) {
    audit_db($config["id_user"],$config["REMOTE_ADDR"], "ACL Violation","Trying to access KB Browser");
    require ("general/noaccess.php");
    exit;
}

// Show list of items
// =======================

echo "<h2>".__('KB Data management')."</h2>";	
echo "<h3>".__('Defined data')."</a></h3>";


// Search parameter 
$free_text = get_parameter ("free_text", "");
$product = get_parameter ("product", 0);
$category = get_parameter ("category", 0);

// Search filters
echo '<form method="post">';
echo '<table width="90%" class="blank">';
echo "<tr>";
echo "<td>";
echo __('Product types');
echo "<td>";
combo_kb_products ($product, 0);
echo "<td>";
echo __('Categories');
echo "<td>";
combo_kb_categories ($category);

echo "<tr>";
echo "<td>";
echo __('Search');
echo "<td>";
echo "<input type=text name='free_text' size=25 value='$free_text'>";

echo "<td >";
echo "<input type=submit class='sub search' value='".__('Search')."'>";


echo "</td></tr></table></form>";

// Search filter processing
// ========================

$sql_filter = "WHERE 1=1 ";

if ($free_text != "")
	$sql_filter .= "AND title LIKE '%$free_text%' OR data LIKE '%$free_text%'";

if ($product != 0)
	$sql_filter .= "AND id_product = $product ";

if ($category != 0)
	$sql_filter .= "AND id_category = $category ";


$sql1 = "SELECT * FROM tkb_data $sql_filter ORDER BY title, id_category, id_product";
$color =0;
if ($result=mysql_query($sql1)){
	echo '<table width="90%" class="listing">';

	echo "<th>".__('Title')."</th>";
	echo "<th>".__('Timestamp')."</th>";
	echo "<th>".__('Category')."</th>";
	echo "<th>".__('Product')."</th>";
	echo "<th>".__('File')."</th>";
	echo "<th>".__('User')."</th>";
	while ($row=mysql_fetch_array($result)){
		echo "<tr>";
		// Name
		echo "<td valign='top'><b><a href='index.php?sec=kb&sec2=operation/kb/browse_data&view=".$row["id"]."'>".$row["title"]."</a></b></td>";

		// Timestamp
		echo "<td class='f9' valign='top'>";
		echo $row["timestamp"];

		// Category
		echo "<td>";
		echo get_db_sql ("SELECT name FROM tkb_category WHERE id = ".$row["id_category"]);

		// Product
		echo "<td>";
		echo get_db_sql ("SELECT name FROM tkb_product WHERE id = ".$row["id_product"]);

		// Attach ?
		echo "<td>";
		if (get_db_sql ("SELECT count(*) FROM tattachment WHERE id_kb = ".$row["id"]) != 0)
			echo "<img src='images/disk.png'>";

		// User
		echo "<td class='f9' >";
		echo $row["id_user"];

	}
	echo "</table>";
}			

?>
