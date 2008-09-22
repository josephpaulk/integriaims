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
/*

Note about clean_input, clean_output and other string functions
----------------------------------------------------------------

ALL Data stored in database SHOULD have been parsed with clean_input()
to encode all conflictive characters, like <, >, & or ' and ".

ALL Data used to output in a different way than HTML render, (in PDF,
Graphs or HTML input controls ) SHOULD parse before with clean_output()
to decode HTML characters.

*/

/**
* Returns a single string with HTML characters decoded
*
* $input    string  Input string
*/

function ascii_output ($string){
	return clean_output ($string);
}

function clean_output ($string) {
	return html_entity_decode ($string, ENT_QUOTES, "UTF-8");
}

function remove_locale_chars ($string){
	$filtro0 = utf8_decode($string);
	$filtro1 = str_replace ('&aacute;',"a", $filtro0);
	$filtro2 = str_replace ('&eacute;',"e", $filtro1);
	$filtro3 = str_replace ('&iacute;',"i", $filtro2);
	$filtro4 = str_replace ('&oacute;',"o", $filtro3);
	$filtro5 = str_replace ('&uacute;',"u", $filtro4);
	$filtro6 = str_replace ('&ntilde;',"n", $filtro5);
	return $filtro6;
}

/**
* Clean input text
*
* This function clean a user string to be used of SQL operations or
* other kind of sensible string operations (like XSS)
* This replace all conflictive characters.
*
* $value	string	Input string to be cleaned*/

function clean_input ($value) {
	if (is_numeric ($value))
		return $value;
	if (is_array ($value)) {
		array_walk ($value, 'clean_input');
		return $value;
	}
	return htmlentities (utf8_decode ($value), ENT_QUOTES); 						
}

/**
* Returns a single string replacing new lines for <br> HTML tag
*
* $input    string  Input string
*/

function clean_output_breaks ($string){
	return preg_replace ('/\n/',"<br>", $string);
}

/**
* Search item in a given array.
*
* @exampleArray	array 	Source of items to search in
* $item		varchar	Data to search
*/

function array_in($exampleArray, $item){
	$result = 0;
	foreach ($exampleArray as $key => $value){
  		if ($value == $item){
   			$result = 1;
		}
  	}
	return $result;
}

function get_parameter ($name, $default = ""){
    $temp = give_parameter_get ($name, $default);
    if ($temp == $default){
        $temp = give_parameter_post ($name, $default);
    }
    return $temp;
}

function give_parameter_get ( $name, $default = "" ){
	$output = $default;
	if (isset ($_GET[$name])){
		$output = clean_input ($_GET[$name]);
	}
	return $output;
}

function give_parameter_post ( $name, $default = "" ){
	$output = $default;
	if (isset ($_POST[$name])){
		$output = clean_input ($_POST[$name]);
	}
	return $output;
}

// ---------------------------------------------------------------
// Esta funcion lee una cadena y la da "limpia", para su uso con
// parametros pasados a funcion de abrir fichero. Usados en sec y sec2
// ---------------------------------------------------------------

function parametro_limpio ($texto){
	// Metemos comprobaciones de seguridad para los includes de paginas pasados por parametro
	// Gracias Raul (http://seclists.org/lists/incidents/2004/Jul/0034.html)
	// Consiste en purgar los http:// de las cadenas
	$pos = strpos($texto,"://");	// quitamos la parte "fea" de http:// o ftp:// o telnet:// :-)))
	if ($pos <> 0)
	$texto = substr_replace($texto,"",$pos,+3);
	// limitamos la entrada de datos por parametros a 125 caracteres
	$texto = substr_replace($texto,"",125);
	$safe = preg_replace('/[^a-z0-9_\/]/i','',$texto);
	return $safe;
}



// ---------------------------------------------------------------
// no_permission () - Display no perm. access
// ---------------------------------------------------------------

function no_permission () {
	global $config;
	global $lang_label;
	require ($config["homedir"]."/include/languages/language_".$config["language_code"].".php");
	echo "<h3 class='error'>".__('no_permission_title')."</h3>";
	echo "<img src='".$config["base_url"]."/images/noaccess.gif' width='120'><br><br>";
	echo "<table width=550>";
	echo "<tr><td>";
	echo __('no_permission_text');
	echo "</table>";
	echo "<tr><td><td><td><td>";
	include $config["homedir"]."/general/footer.php";
	exit;
}

/** 
 * List files in a directory in the local path.
 * 
 * @param directory Local path.
 * @param stringSearch String to match the values.
 * @param searchHandler Pattern of files to match.
 * @param return Flag to print or return the list.
 * 
 * @return The list if $return parameter is true.
 */
function list_files ($directory, $stringSearch, $searchHandler, $return) {
	$errorHandler = false;
	$result = array ();
	if (! $directoryHandler = @opendir ($directory)) {
		echo ("<pre>\nerror: directory \"$directory\" doesn't exist!\n</pre>\n");
		return $errorHandler = true;
	}
	if ($searchHandler == 0) {
		while (false !== ($fileName = @readdir ($directoryHandler))) {
			$result[$fileName] = $fileName;
		}
	}
	if ($searchHandler == 1) {
		while(false !== ($fileName = @readdir ($directoryHandler))) {
			if(@substr_count ($fileName, $stringSearch) > 0) {
				$result[$fileName] = $fileName;
			}
		}
	}
	if (($errorHandler == true) &&  (@count ($result) === 0)) {
		echo ("<pre>\nerror: no filetype \"$fileExtension\" found!\n</pre>\n");
	} else {
		asort ($result);
		if ($return == 0) {
			return $result;
		}
		echo ("<pre>\n");
		print_r ($result);
		echo ("</pre>\n");
	}
}

/**
 * Add magnitude to a byte quantity.
 *
 * @param int $bytes Bytes to add magnitude
 *
 * @retval Bytes amount in KiB, MiB, GiB, etc.
 */
function byte_convert ($bytes) {
	$symbol = array ('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB');
	if ($bytes < 0)
		return '0 B';
	$exp = 0;
	$converted_value = 0;
	if ($bytes > 0) {
		$exp = floor (log ($bytes) / log (1024));
		$converted_value = ($bytes / pow(1024, floor ($exp)));
	}

	return sprintf ('%.2f '.$symbol[$exp], $converted_value );
}

function pagination ($count, $url, $offset ) {
	global $config;
	require ($config["homedir"]."/include/languages/language_".$config["language_code"].".php");
	$block_size = $config["block_size"];

	/* 	URL passed render links with some parameter
			&offset - Offset records passed to next page
	  		&counter - Number of items to be blocked
	   	Pagination needs $url to build the base URL to render links, its a base url, like
	   " http://pandora/index.php?sec=godmode&sec2=godmode/admin_access_logs "

	*/
	$block_limit = 15; // Visualize only $block_limit blocks
	if ($count > $block_size){
		// If exists more registers than I can put in a page, calculate index markers
		$index_counter = ceil($count/$block_size); // Number of blocks of block_size with data
		$index_page = ceil($offset/$block_size)-(ceil($block_limit/2)); // block to begin to show data;
		if ($index_page < 0)
			$index_page = 0;

		// This calculate index_limit, block limit for this search.
		if (($index_page + $block_limit) > $index_counter)
			$index_limit = $index_counter;
		else
			$index_limit = $index_page + $block_limit;

		// This calculate if there are more blocks than visible (more than $block_limit blocks)
		if ($index_counter > $block_limit )
			$paginacion_maxima = 1; // If maximum blocks ($block_limit), show only 10 and "...."
		else
			$paginacion_maxima = 0;

		// This setup first block of query
		if ( $paginacion_maxima == 1)
			if ($index_page == 0)
				$inicio_pag = 0;
			else
				$inicio_pag = $index_page;
		else
			$inicio_pag = 0;

		echo "<div><p>";
		// Show GOTO FIRST button
		echo '<a href="'.$url.'&offset=0">';
		echo "<img src='".$config["base_url"]."/images/control_start_blue.png' border=0 valign='bottom'>";
		echo "</a>";
		echo "&nbsp;";
		// Show PREVIOUS button
		if ($index_page > 0){
			$index_page_prev= ($index_page-(floor($block_limit/2)))*$block_size;
			if ($index_page_prev < 0)
				$index_page_prev = 0;
			echo '<a href="'.$url.'&offset='.$index_page_prev.'"><img src="'.$config["base_url"].'/images/control_rewind_blue.png" border=0 valign="bottom"></a>';
		}
		echo "&nbsp;";echo "&nbsp;";
		// Draw blocks markers
		// $i stores number of page
		for ($i = $inicio_pag; $i < $index_limit; $i++) {
			$inicio_bloque = ($i * $block_size);
			$final_bloque = $inicio_bloque + $block_size;
			if ($final_bloque > $count){ // if upper limit is beyond max, this shouldnt be possible !
				$final_bloque = ($i-1)*$block_size + $count-(($i-1) * $block_size);
			}
			echo "<span>";

			$inicio_bloque_fake = $inicio_bloque + 1;
			// To Calculate last block (doesnt end with round data,
			// it must be shown if not round to block limit)
			echo '<a href="'.$url.'&offset='.$inicio_bloque.'">';
			if ($inicio_bloque == $offset)
				echo "<b>[ $i ]</b>";
			else
				echo "[ $i ]";
			echo '</a> ';
			echo "</span>";
		}
		echo "&nbsp;";echo "&nbsp;";
		// Show NEXT PAGE (fast forward)
		// Index_counter stores max of blocks
		if (($paginacion_maxima == 1) AND (($index_counter - $i) > 0)) {
				$prox_bloque = ($i+ceil($block_limit/2))*$block_size;
				if ($prox_bloque > $count)
					$prox_bloque = ($count -1) - $block_size;
				echo '<a href="'.$url.'&offset='.$prox_bloque.'">';
				echo "<img border=0 valign='bottom' src='images/control_fastforward_blue.png'></a> ";
				$i = $index_counter;
		}
		// if exists more registers than i can put in a page (defined by $block_size config parameter)
		// get offset for index calculation
		// Draw "last" block link, ajust for last block will be the same
		// as painted in last block (last integer block).
		if (($count - $block_size) > 0){
			$myoffset = floor(($count-1)/ $block_size)* $block_size;
			echo '<a href="'.$url.'&offset='.$myoffset.'">';
			echo "<img border=0 valign='bottom' src='images/control_end_blue.png'>";
			echo "</a>";
		}
	// End div and layout
	echo "</p></div>";
	}
}

// Render data in a fashion way :-)
function format_numeric ( $number, $decimals=1, $dec_point=".", $thousands_sep=",") {
	if (is_numeric($number)){
		if ($number == 0)
			return 0;
		// If has decimals
		if (fmod($number , 1)> 0)
			return number_format ($number, $decimals, $dec_point, $thousands_sep);
		else
			return number_format ($number, 0, $dec_point, $thousands_sep);
	} else
 	return 0;
}

function __ ($string){
		return lang_string ($string);
}

function lang_string ($string) {
	global $config;
	global $lang_label;
	require (realpath ($config["homedir"]."/include/languages/language_".$config["language_code"].".php"));
	
	if (isset ($lang_label[$string]))
		return $lang_label[$string];
	return $string;
}

function render_priority ($pri){
	global $config;
	require ($config["homedir"]."/include/languages/language_".$config["language_code"].".php");
	switch ($pri){
		case 0: return __("very low");
		case 1: return __("low");
		case 2: return __("medium");
		case 3: return __("high");
		case 4: return __("very high");
		default: return __("other");
	}
}

function topi_sendmail ( $destination, $msg_subject = "[INTEGRIA] Automatic email notification", $msg_text) {
	global $config;
	if ($destination != ""){
	$msg_text = ascii_output ($msg_text);
	$msg_subject = ascii_output ($msg_subject);
		$real_text = $config["HEADER_EMAIL"].$msg_text."\n\n".$config["FOOTER_EMAIL"];
		mail ($destination, $msg_subject, $real_text);
	}
}

function topi_rndcode ($length=6) {
	$chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
	$code = "";
	$clen = strlen($chars) - 1;  //a variable with the fixed length of chars correct for the fence post issue
	while (strlen($code) < $length) {
		$code .= $chars[mt_rand(0,$clen)];  //mt_rand's range is inclusive - this is why we need 0 to n-1
	}
	return $code;
}

/* Given a local URL, compose a internet valid URL
   with quicklogin HASH data, and enter it on DB
*/

function topi_quicksession ($url, $id_user = "") {
	global $config;
	if ($id_user == "")
		$id_user = $config["id_user"];
	$today=date('Y-m-d H:i:s');

	// Build quicksession data and URL
	$id_user = $config["id_user"];
	$cadena = topi_rndcode(16).$id_user.$today;
	$cadena_md5 = substr(md5($cadena),1,8);
	$param = "&quicksession=$cadena_md5&quickuser=$id_user";
	$myurl = $config["base_url"].$url.$param;
	//Insert quicksession data in DB
	$sql = "INSERT INTO tquicksession (id_user, timestamp, pwdhash) VALUES ('$id_user', '$today', '$cadena_md5')";
	mysql_query($sql);
	return $myurl;
}


function return_value ($var) {
	if (isset($var))
		return $var;
	return "";
}

function maxof ($a, $b) {
	return max ($a, $b);
}

function get_indicent_priorities () {
	$incidents = array ();

	$incidents[0] = __('informative');
	$incidents[1] = __('low');
	$incidents[2] = __('medium');
	$incidents[3] = __('serious');
	$incidents[4] = __('very_serious');
	$incidents[10] = __('maintenance');

	return $incidents;
}

// This both functions need to be updated to use values FROM datatabase, not fixed ones

function get_indicent_status () {
	$status = array ();

	$status[1] = __('status_new');
	$status[2] = __('status_unconfirmed');
	$status[3] = __('status_assigned');
	$status[4] = __('status_reopened');
	$status[5] = __('status_verified');
	$status[6] = __('status_resolved');
	$status[7] = __('status_closed');

	return $status;
}

function get_incident_resolution () {
	$status = array ();

	$status[1] = lang_string ('Fixed');
	$status[2] = lang_string ('Invalid');
	$status[3] = lang_string ('Wont fix');
	$status[4] = lang_string ('Duplicate');
	$status[5] = lang_string ('Works for me');
	$status[6] = lang_string ('Incomplete');
	$status[7] = lang_string ('Expired');
    $status[8] = lang_string ('Moved');
	$status[9] = lang_string ('In process');

	return $status;
}
function print_priority_flag_image ($priority, $return = false) {
	$output = '';
	
	$output .= '<img class="priority-color" height="15" width="30" ';
	switch ($priority) {
	case 0:
		// Informative
		$output .= 'src="images/pixel_gray.png" title="'.__('Informative').'" ';
		break;
	case 1:
		// Low
		$output .= 'src="images/pixel_green.png" title="'.__('Low').'" ';
		break;
	case 2:
		// Medium
		$output .= 'src="images/pixel_yellow.png" title="'.__('Medium').'" ';
		break;
	case 3:
		// Serious
		$output .= 'src="images/pixel_orange.png" title="'.__('Serious').'" ';
		break;
	case 4:
		// Very serious
		$output .= 'src="images/pixel_red.png" title="'.__('Very serious').'" ';
		break;
	case 10:
		// Maintance
		$output .= 'src="images/pixel_blue.png" title="'.__('Maintance').'" ';
		break;
	default:
		// Default
		$output .= 'src="images/pixel_gray.png" title="'.__('Unknown').'" ';
	}

	$output .= ' />';
	if ($return)
		return $output;
	echo $output;
}

?>
