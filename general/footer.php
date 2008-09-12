<?PHP
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

?>
<p align='center'>

<?PHP
global $config;
global $lang_label;

echo 'Integria '.$config["version"].' Build '.$config["build_version"].'<br>';

if (isset($_SESSION['id_usuario'])) {
	echo '<a target="_new" href="general/license/integria_info_'.$config["language_code"].'.html">'.
	$lang_label["gpl_notice"].'</a><br>';
	if (isset($_SERVER['REQUEST_TIME'])) {
		$time = $_SERVER['REQUEST_TIME'];
	} else {
		$time = time();
	}
	echo $lang_label["gen_date"]." ".date("D F d, Y H:i:s", $time)."<br>";
}
?>
</p>
