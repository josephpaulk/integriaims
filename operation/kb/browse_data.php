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

	// Review form
	// ===========
	if (!isset($_GET["view"])){
		return 0;
	}

	$id = get_parameter ("view",-1);
	$row = get_db_row ("tkb_data", "id", $id);
	$data = $row["data"];
	$title = $row["title"];
	$id_product = $row["id_product"];
	$id_category = $row["id_category"];
	$timestamp = $row["timestamp"];
	if ($id_product > 0)
		$product = get_db_value ("name", "tkb_product", "id", $id_product);
	else 
		$product = "";
	if ($id_category > 0)
		$category = get_db_value ("name", "tkb_category", "id", $id_category);
	else
		$category = "";
	
	echo "<h2>".__('KB article review')."</h2>";	
    echo "<h3>$product </h3>";

	$avatar = get_db_value ("avatar", "tusuario", "id_usuario", $id_user);

	// Show data

	// Title header
	echo "<div class='notetitle' style='height: 50px;'>"; 
	echo "<table class='blank' border=0 width='100%' cellspacing=0 cellpadding=0 style='background: transparent; line-height: 12px; border: 0px; margin-left: 0px;margin-top: 0px;'>";
	echo "<tr><td rowspan=3 width='7%'>";
	echo "<img src='images/avatars/".$avatar."_small.png'>";
	
	echo "<td width='50%'><b>";
	echo __('Title')." </b> : ";
	echo $title;

	echo "<td> <b>";
    echo __('Product')." </b> : ";
    echo $product;
    
    	

	echo "<tr>";
	echo "<td>";
	echo "<a href='index.php?sec=users&sec2=operation/users/user_edit&ver=$id_user'>";
	echo "<b>".$id_user."</b>";
	echo "</a>";
	echo " ".__("Write on $timestamp");
	echo "<td>";
	echo "<b>";
	echo __('Category')." </b> : ";
	echo $category;

	echo "</table>";
	echo "</div>";

	// Body
	echo "<div class='notebody'>";
	echo "<table class='blank' width='100%' cellpadding=0 cellspacing=0>";
	echo "<tr><td valign='top'>";
	echo clean_output_breaks($data);
	echo "</table>";
	echo "</div>";


	// Show list of attachments
	$sql1 = "SELECT * FROM tattachment WHERE id_kb = $id ORDER BY description";
	$result = mysql_query($sql1);
	if (mysql_num_rows($result) > 0){
		echo "<h3>".__('Attachment list')."</h3>";
		echo "<table cellpadding=4 cellspacing=4 class=databox width=500>";	
		echo "<tr>";
		echo "<th width=200>" . __('Filename');
		echo "<th>" . __('Description');

		$color=0;
		while ($row=mysql_fetch_array($result)){
			if ($color == 1){
				$tdcolor = "datos";
				$color = 0;
				}
			else {
				$tdcolor = "datos2";
				$color = 1;
			}
			echo "<tr>";
			echo "<td class=$tdcolor>";
			echo "<img src='images/disk.png'>&nbsp;";
			$attach_id = $row["id_attachment"];
			$filelink= "attachment/".$row["id_attachment"]."_".$row["filename"];
			echo "<a href='$filelink'>";
			echo $row["filename"];
			echo "</A>";
			echo "<td class=$tdcolor>";
			echo $row["description"];
		}
		echo "</table>";
	}
?>
