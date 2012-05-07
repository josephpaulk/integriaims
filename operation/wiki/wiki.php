<?php
// INTEGRIA - the ITIL Management System
// http://integria.sourceforge.net
// ==================================================
// Copyright (c) 2007-2010 Ártica Soluciones Tecnológicas
// http://www.artica.es  <info@artica.es>

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// Load global vars
global $config;
$id_user = $config["id_user"];

if (check_login() != 0) {
	audit_db("Noauth", $config["REMOTE_ADDR"], "No authenticated access", 
		"Trying to access monthly report");
	require ("general/noaccess.php");
	
	exit;
}

if (! give_acl ($config['id_user'], $id_grupo, "WR")) {
 	// Doesn't have access to this page
	audit_db ($config['id_user'], $config["REMOTE_ADDR"], "ACL Violation", "Trying to access agenda of group ".$id_grupo);
	include ("general/noaccess.php");
	
	exit;
}

require_once("include/wiki/lionwiki_lib.php");

$translation_strings = array();
$translation_strings['title_text'] = __('Admin Pages');
$translation_strings['delete_text'] = __('Delete');
$translation_strings['correct_text'] = __('Correct delete page ');
$translation_strings['incorrect_text'] = __('Incorrect delete page ');

$conf_plugin_dir = 'include/wiki/plugins/';
$conf_var_dir = 'var/';
if (isset($config['wiki_plugin_dir']))
	$conf_plugin_dir = $config['wiki_plugin_dir'];
if (isset($config['conf_var_dir']))
	$conf_var_dir = $config['conf_var_dir'];

$conf['wiki_title'] = 'Wiki';
$conf['self'] = 'index.php?sec=wiki&sec2=operation/wiki/wiki' . '&';
$conf['plugin_dir'] = $conf_plugin_dir;
$conf['var_dir'] = $conf_var_dir;
$conf['custom_style'] = file_get_contents ($config["homedir"]."/include/styles/wiki.css");
$conf['fallback_template'] = $conf['custom_style']. '
 
<div id="wiki_view">
	<table width="100%" cellpadding="0">
		<tr><td style="border-bottom: 1px solid #ccc;" colspan="3"><h2>{PAGE_TITLE}</h2></td></tr>
		<tr>
			<td colspan="3">
				{<div style="color:#F25A5A;font-weight:bold;"> ERROR </div>}
				{CONTENT} {<div style="background: #EBEBED"> plugin:TAG_LIST </div>}
				{plugin:TOOLBAR_TEXTAREA}
				{CONTENT_FORM} {RENAME_INPUT <br/><br/>} {CONTENT_TEXTAREA}
				{EDIT_SUMMARY_TEXT} {EDIT_SUMMARY_INPUT} {CONTENT_SUBMIT} {CONTENT_PREVIEW}</p>{/CONTENT_FORM}
			</td>
		</tr>
		<tr><td colspan="3"><hr/></td></tr>
		<tr>
			<td> {LAST_CHANGED_TEXT}: {LAST_CHANGED}</td>
			<td></td>
			<td></td>
		</tr>
	</table>
</div>';

// Main call to render wiki
lionwiki_show($conf);
?>
