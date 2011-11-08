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

global $REMOTE_ADDR;
global $config;

check_login ();

// MAIN LIST OF PROJECTS GROUPS
echo "<h2>".__("Project overview")."</h2>";

// -------------
// Show headers
// -------------
echo "<table width='90%' class='listing'>";
echo "<tr>";
echo "<th>".__ ("Project group");
echo "<th>".__ ("Icon");
echo "<th>".__ ("# Projects");

// -------------
// Show DATA TABLE
// -------------

$project_groups = process_sql("SELECT * FROM tproject_group ORDER by name"); 

if($project_groups === false) {
	$project_groups = array();
}

$nogroup = array();
$nogroup["id"] = 0;
$nogroup["name"] = __('Without group');
$nogroup["icon"] = '../group.png';

$project_groups[] = $nogroup;
$first = true;

foreach($project_groups as $group){
	if (give_acl($config["id_user"], 0, "PR")){
		if($group['id'] == 0) {
			$prefix = 'last_';
		}
		elseif($first) {
			$prefix = 'first_';
			$first = false;
		}
		else {
			$prefix = '';
		}
		
		// Get projects info
		$projects = get_db_all_rows_sql ("SELECT id, name FROM tproject WHERE disabled = 0 AND id_project_group = ".$group["id"]);
		if($projects === false) {
			$projects = array();
		}
		
		$nprojects = count($projects);
		
		echo "<tr>";
		// Project group name
		echo "<td style='text-align:left; padding-bottom:0px; padding-top:0px;'>";
		echo "<a href='javascript:'><img id='btn_".$group["id"]."' class='btn_tree' src='images/".$prefix."closed.png' style='float:left'></a>";
		echo "<b><a href='index.php?sec=projects&sec2=operation/projects/project&search_id_project_group=".$group["id"]."'>".$group["name"]."</a></b>";
		echo "</td>";	
		
		// Project group
		echo "<td>";
		echo "<img src='images/project_groups_small/".$group["icon"]."'>";
		echo "</td>";	
			
		// Number of projects inside
		echo "<td id='nproj_".$group["id"]."'>";
		echo $nprojects;
		echo "</td>";
		echo "</tr>";
		
		// Projects inside
		foreach($projects as $project) {
			echo "<tr class='prj_".$group["id"]."' style='display:none'>";
			// Project name
			echo "<td style='text-align:left; padding-bottom:0px; padding-top:0px;' colspan='3'>";
			echo "<img src='images/branch.png' style='float:left'>";
			echo "<img src='images/award_star_bronze_1.png' style='float:left'>";
			echo "&nbsp;<b><a href='index.php?sec=projects&sec2=operation/projects/task&id_project=".$project["id"]."'>".$project["name"]."</a></b></td>";
			echo "</td>";	
			echo "</tr>";
		}
		
		if($nprojects == 0) {
			echo "<tr class='prj_".$group["id"]."' style='display:none'>";
			// Project name
			echo "<td style='text-align:left; padding-bottom:0px; padding-top:0px;' colspan='3'>";
			echo "<img src='images/branch.png' style='float:left'>";
			echo "&nbsp;".__('empty')."</td>";
			echo "</td>";	
			echo "</tr>";
		}
	}
}

echo "</table>";

?>

<script type="text/javascript">
$('.btn_tree').click(function() {
	id = $(this).attr('id');
	id = id.split('_');
	id = id[1];
	
	if($('.prj_'+id).css('display') == 'none') {
		show_branches(id);
		if($('#nproj_'+id).html() == 0) {
			hidden_branches(id);
		}
	}
	else {
		hidden_branches(id);
	}
	
	function show_branches(id) {
		$('.prj_'+id).fadeIn('slow');
		if(id == 0) {
			$('#btn_'+id).attr('src', 'images/closed.png');
		}
	}
	
	function hidden_branches(id) {
		$('.prj_'+id).fadeOut('fast');
		if(id == 0) {
			$('#btn_'+id).attr('src', 'images/last_closed.png');
		}
	}
});
</script>
