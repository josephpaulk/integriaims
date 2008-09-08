<?php

// INTEGRIA IMS v1.2
// http://www.integriaims.com
// ===========================================================
// Copyright (c) 2007-2008 Sancho Lerena, slerena@gmail.com
// Copyright (c) 2007-2008 Artica, info@artica.es

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU Lesser General Public License (LGPL)
// as published by the Free Software Foundation; version 2
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

if (is_readable("include/config.php"))
	include "include/config.php";
else {
	$config["language_code"] = "en";
	$config["version"] = "1.2";
	$config["build_version"] = "N/A";
}

if (file_exists ("include/languages/language_".$config["language_code"].".php")) {
	include "include/languages/language_".$config["language_code"].".php";
} else {
	include "include/languages/language_en.php";
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>I N T E G R I A - Install error </title>
<meta http-equiv="expires" content="0">
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta name="resource-type" content="document">
<meta name="distribution" content="global">
<meta name="author" content="Sancho Lerena">
<meta name="copyright" content="This is GPL software. Created by Sancho Lerena and others">
<meta name="robots" content="index, follow">
<link rel="icon" href="images/integria.ico" type="image/ico">
<link rel="stylesheet" href="include/styles/integria.css" type="text/css">
</head>
<body>
<div align='center'>
<div id='login_f'>
	<h1 id="log_f" class="error">Bad permission for include/config.php</h1>
	<div>
		<img src="images/integria_white.png" border="0"></a><br><font size="1">
		<?php echo 'Integria '.$config["version"].' Build '.$config["build_version"]; ?>
		</font>
	</div>
	<div class="msg"><br><br>For security reasons, <i>config.php</i> must have restrictive permissions, and "other" users cannot read or write to it. It could be writed only for owner (usually www-data or http daemon user), normal operation is not possible until you change permissions for <i>include/config.php</i>file. Please do it, it's for your security.</div>
</div>
</div>
</body>
</html>
